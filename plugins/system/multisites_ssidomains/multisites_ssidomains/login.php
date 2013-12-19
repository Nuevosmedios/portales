<?php
/**
 * @file       login.php
 * @brief      Single Sign In Domains share the Login between different domains.
 *             So that the users can remain logged when they change of domains.
 * @version    1.0.8
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
 * - V1.0.0    24-OCT-2011: Initial version
 * - V1.0.1    29-OCT-2011: Add verification of a checksum to reduce risk of forgery
 * - V1.0.2    06-NOV-2011: Add debugging traces.
 * - V1.0.3    08-NOV-2011: Remove the "session name" in the parameter to increase the security.
 *                          Recompute the "session name" based on the application name and secret value.
 * - V1.0.4    09-NOV-2011: Add processing of the joomla 1.6, 1.7 cookie_domain and cookie_path "configuration.php" parameter.
 * - V1.0.5    16-NOV-2011: Fix the joomla 1.6, 1.7 cookie_domain and cookie_path processing
 * - V1.0.8    18-MAR-2013: Add joomla 3.0 compatibility
 */

$filename = dirname( __FILE__).DIRECTORY_SEPARATOR.'multisites_ssidomains.cfg.php';
if ( file_exists( $filename)) {
   include( $filename);
}
define( 'SSI_LIVETIME', 900);          // Remove the comment to dynamically compute the "livetime" value based on the Joomla "configuration.php" file of the slave site
// If the configuration were not loaded and that the SSI_VERIFY_CHECKSUM is not present
if ( !isset( $SSI_VERIFY_CHECKSUM)) {
   // Then set the default value to true.
   $SSI_VERIFY_CHECKSUM = true;
}

// Do not display anything upon screen.
// Use the debug_messages to save all the message to display at the end of the script
$debug_messages = array();

// If the parameter (data) is present
// And the optional (checksum) is also present
if ( !empty( $_GET['d']) && (    empty( $SSI_VERIFY_CHECKSUM)                          // No checksum verification
                            || (!empty( $SSI_VERIFY_CHECKSUM) && !empty( $_GET['cs'])) // Checksum verification required and checksum is present
                            )
   )
{
   error_reporting(0);
   
   // If the livetime is defined and there is no checksum verification
   if ( defined( 'SSI_LIVETIME') && empty( $SSI_VERIFY_CHECKSUM)) {
      // Use the hardcoded value to speedup the processing
   	$lifetime = SSI_LIVETIME;
   }
   // If the livetime is not present or Checksum verification is requested,
   else {
      // then compute dynamically the livetime value based on the "configuration.php" file of the website (master or slave).

      // ---- Retreive the website "configuration.php" file
      define('_JEXEC', 1);
      define('DS', DIRECTORY_SEPARATOR);
      
      for ( $jpath_root = dirname( __FILE__); !empty( $jpath_root) && basename( $jpath_root) != 'plugins'; $jpath_root = dirname( $jpath_root));
      $jpath_root = dirname( $jpath_root);
      define( 'JPATH_BASE', $jpath_root);
      define('JPATH_ROOT',	JPATH_BASE);
      define('JPATH_SITE',			JPATH_ROOT);
      define('JPATH_ADMINISTRATOR',	JPATH_ROOT.DS.'administrator');
      define('JPATH_LIBRARIES',		JPATH_ROOT.DS.'libraries');
      define('JPATH_PLUGINS',			JPATH_ROOT.DS.'plugins'  );
   
      // -- Call JMS to get the info on the website
      define( 'JPATH_MUTLISITES_COMPONENT', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
      @include_once( JPATH_MUTLISITES_COMPONENT .DS. 'multisites_path.cfg.php');
      if ( !defined( 'JPATH_MULTISITES')) define( 'JPATH_MULTISITES', JPATH_ROOT .DS. 'multisites');
      define( '_EDWIN2WIN_', true);
      @include( JPATH_ROOT .DS. 'includes' .DS. 'defines_multisites.php');
      if ( !defined( 'JPATH_CONFIGURATION'))  define( 'JPATH_CONFIGURATION', 	JPATH_ROOT );

      // ---- Get the Joomla Version
      // If Joomla 2.5, 3.0 & 3.1
      if ( is_file( JPATH_LIBRARIES.DS.'cms'.DS.'version'.DS.'version.php')) {
         $j15 = false;
      }
      // If Joomla 1.7
      else if ( is_file( JPATH_SITE.DS.'includes'.DS.'version.php')) {
         $j15 = false;
      }
      // If joomla 1.5 or 1.6
      else {
         @include( JPATH_LIBRARIES.DS.'joomla'.DS.'version.php');
      }
      if ( class_exists( 'JVersion')) {
         $jversion = new JVersion;
         if ( version_compare( $jversion->RELEASE, '1.6') >= 0) { $j15 = false; }
         else                                                   { $j15 = true; }
      }
      
      // ---- Load the "configuration.php" to get some config values
      $lifetime = 900;
      @include( JPATH_CONFIGURATION.DS.'configuration.php');
      if ( class_exists( 'JConfig')) {
         $config = new JConfig();
         
   		$lifetime = isset( $config->lifetime) ? $config->lifetime * 60 : 900;
   		$secret   = isset( $config->secret)   ? $config->secret: null;
         // Sensistive trace
         if ( !empty( $ssi_trace)) { $debug_messages[] = '//ssidomain_login_trace : Loaded Config from path : '.JPATH_CONFIGURATION; }
      }
      else {
         if ( !empty( $ssi_debug)) { $debug_messages[] = '//ssidomain_login_debug : "Config not loaded!";'; }
      }
   }
   
   $result = 'false';

   // When the data is present in the URL
   $d = $_GET['d'];
   if ( !empty( $d)) {
      // Proceed with the Login that is encoded to the reduce risk of exploit by hackers.
eval( gzinflate( base64_decode( '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')));
   }

   header('Content-Type: text/javascript');
   if ( !empty( $debug_messages)) {
      echo implode( "\n", $debug_messages);
      echo "\n";
   }
   // reply with a "valid" javascript statement
   echo "ssidomain_login = $result;";
}
?>
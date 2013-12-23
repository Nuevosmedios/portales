<?php
/**
 * @file       site.php
 * @brief      Interface used by the Article sharing to select a website.
 *
 * @version    1.2.96
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  JMS Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2012 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.5  20-DEC-2008: Save the current value of the site id to allow customized the article links, ...
 * - V1.2.34 17-JUL-2010: Add multisites selection
 * - V1.2.55 16-JUN-2011: Fix the way that the "lastSiteValue" is saved.
 *                        On Joomla 1.5, this is a string but on Joomla 1.6, this is an array.
 *                        So convert the array into a string.
 * - V1.2.62 18-AUG-2011: Add the possibility to get the list of site filtered on the site
 *                        that share the users with the current site
 * - V1.2.68 29-MAY-2012: Add joomla 2.5 compatibility
 * - V1.2.96 01-NOV-2012: Give the possiblity to customize the Site rendering (List, Text or Hidden)
 *                        depending on a $_GLOBAL['MULTISITES_ELT_SITE'] value present in the "multisites.cfg.php" file.
 *                        Fix joomla 2.5 compatibility (<select name) value
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();


require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'models' .DS. 'manage.php');
require_once( dirname( __FILE__) .DS. 'compat16.php');
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'multisites.cfg.php');

/**
 * Renders a category element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class MultisitesElementSite extends MultisitesElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Site';
	
   //------------ getLastSiteValue ---------------
	function &getLastSiteValue()
	{
   	static $value = '';
   	return $value;
	}

   //------------ setLastSiteValue ---------------
	function setLastSiteValue( $newValue)
	{
	   $value =& MultisitesElementSite::getLastSiteValue();
	   // If the new value is an array (case of Joomla 1.6)
      if ( !empty( $newValue) && is_array( $newValue)) {
         // Convert the value into a string
         $newValue = trim( implode(" ", $newValue));
      }
	   $value =  $newValue;
	}

   //------------ fetchElement ---------------
	function fetchElement($name, $value, &$node, $control_name)
	{
	   MultisitesElementSite::setLastSiteValue( $value);
	   
	   // Check if there is a "class" attribute in the <param ... class="xxx" />
		$class		= $this->getAttribute( $node, 'class');
		if (!$class) {
			$class = "inputbox";
		}

	   // Check if there is a "addScript" attribute in the <param ... addScript="xxx" />
		$addScript = $this->getAttribute( $node, 'addscript');
		if ( !empty( $addScript)) {
   		$document = & JFactory::getDocument();
   		$document->addScript( $addScript);
		}

	   // Check if there is a "onchange" attribute in the <param ... onchange="xxx" />
		$onchange = $this->getAttribute( $node, 'onchange');
		if ( !empty( $onchange)) {
   	   $onchange = ' onchange="' . $onchange .'"';
		}
		else {
   	   $onchange = '';
		}

	   // Check if there is a "multiple" attribute in the <param ... multiple="multiple" />
		$multiple = $this->getAttribute( $node, 'multiple');
		if ( !empty( $multiple)) {
   	   $multiple = ' multiple="' . $multiple .'"';
   	   $control_multiple = '[]';
		}
		else {
   	   $multiple = '';
   	   $control_multiple = '';
		}

	   // Check if there is a "size" attribute in the <param ... size="5" />
		$size = $this->getAttribute( $node, 'size');
		if ( !empty( $size)) {
   	   $size = ' size="' . $size .'"';
		}
		else {
   	   $size = '';
		}

	   // Check if there is a "size" attribute in the <param ... size="5" />
		$size_text = $this->getAttribute( $node, 'size_text');
		if ( !empty( $size_text)) {
   	   $size_text = ' size="' . $size_text .'"';
		}
		else {
   	   $size_text = '';
		}
		
		// Now research the list of sites to display in the combo box
		$model =  new MultisitesModelManage();
		$sites = $model->getSites();

	   // Check if there is a "withSharedUserOnly" attribute in the <param ... withSharedUserOnly="true" />
	   $addMaster = true;
		$withSharedUserOnly = $this->getAttribute( $node, 'withSharedUserOnly') == 'true';
      if ( $withSharedUserOnly) {
         $physical_user_site  = $this->getPhysicalUserSite( $model);
         // If the current site is a 'slave site' connected on the master, then add the <Master DB>
         if ( defined( 'MULTISITES_ID') && $physical_user_site == ':master_db:') {}
         else {
   	      $addMaster = false;
   	   }
      }


	   $rows = array();
	   if ( isset( $sites)) {
   	   foreach( $sites as $site) {
   	      // If there is DB defined to this site
   	      if ( isset( $site->db)  && isset( $site->dbprefix)
   	        && !empty( $site->db) && !empty( $site->dbprefix)
   	         )
   	      {
   	         if ( $withSharedUserOnly) {
   	            // If the current site is a slave, exclude it from the list
   	            if ( defined( 'MULTISITES_ID') && $site->id == MULTISITES_ID) {}
   	            // If this is the physical site, then it is not required to redo the process
   	            else if ( $site->id == $physical_user_site) {
         	         $rows[ strtolower( $site->sitename)] = $site;
   	            }
   	            // If this is another slave site
   	            else {
   	               // Get its physical "#__user" site location and check this is the expected one.
      	            $slavesite_user_location = $this->getPhysicalUserSite( $model, $site->id);
      	            if ( empty( $slavesite_user_location) || $slavesite_user_location == $physical_user_site) {
            	         $rows[ strtolower( $site->sitename)] = $site;
            	      }
   	            }
   	         }
   	         else {
         	      $rows[ strtolower( $site->sitename)] = $site;
         	   }
   	      }
   	   }
   	   ksort( $rows);
	   }
		

	   $opt = array();
		if ( empty( $multiple)) {
		   $opt[] = JHTML::_('select.option', '0', '- '.JText::_('Select Site').' -');
		}
		
		if ( $addMaster) {
         $opt[] = JHTML::_('select.option', ':master_db:', '< Master Site >');
      }
	   foreach( $rows as $site) {
   		$opt[] = JHTML::_('select.option', $site->id, $site->sitename . ' | '. $site->id);
	   }

		// If Joomla 1.6, control_name is already ok
		if ( version_compare( JVERSION, '1.6') >= 0) {
		   $select_name = $this->name;
		}
		// If Joomla 1.5, 
		else {
		   $select_name = $control_name.'['.$name.']'.$control_multiple;
		}
		
		if ( isset( $GLOBALS['MULTISITES_ELT_SITE'])) {
		   $site_id = !defined( 'MULTISITES_ID') ? ':master_db:' : MULTISITES_ID;
		   
		   // If the site must be rendered with "text" field
		   if (    isset( $GLOBALS['MULTISITES_ELT_SITE']['text'])
		     && is_array( $GLOBALS['MULTISITES_ELT_SITE']['text'])
		     && in_array( $site_id, $GLOBALS['MULTISITES_ELT_SITE']['text']))
		   {
		      $onchange = str_replace( 'options[selectedIndex].value', 'value', $onchange);
      		return '<input type="text" name="' . $select_name. ' id="' . $control_name . $name . '" value="' . $value . '" ' . $class . $size_text . $onchange . ' />';
		   }
		   
		   // If the site must be rendered with "hidden" field
		   if (    isset( $GLOBALS['MULTISITES_ELT_SITE']['hidden'])
		     && is_array( $GLOBALS['MULTISITES_ELT_SITE']['hidden'])
		     && in_array( $site_id, $GLOBALS['MULTISITES_ELT_SITE']['hidden']))
		   {
      		return $value.'<input type="hidden" name="' . $select_name. ' id="' . $control_name . $name . '" value="' . $value . '" ' . $class . ' />';
		   }
		}
		
		// Default, use the "list"
		return JHTML::_( 'select.genericlist',  $opt, $select_name,
		                 'class="'.$class.'"' .$multiple .$size . $onchange, 
		                 'value', 'text', 
		                 $value, $control_name.$name );
	}

   //------------ getPhysicalUserSite ---------------
   /**
    * @brief Return the DB connection parameter where the users is physically stored.
    * So this resolve all the potential "view" to retreive the physical "table"
    */
   function getPhysicalUserSite( $modelManage, $site_id = null)
   {
      $result = null;
      $filters = array();
      
      if ( empty( $site_id)) {
         // Get 'this' site  connection
         $db  =& JFactory::getDBO();
         $cnf =& JFactory::getConfig();
         if ( empty( $db->_dbserver))  { $db->_dbserver  = $cnf->getValue('config.host'); }
         if ( empty( $db->_dbname))    { $db->_dbname    = $cnf->getValue('config.db'); }
      }
      else {
         // Get a temporary DB connection
         $db =& Jms2WinFactory::getMultiSitesDBO( $site_id, true);
      }
      
      if ( MultisitesDatabase::_isView( $db, '#__users')) {
         $from = MultisitesDatabase::getViewFrom( $db, '#__users');
         if ( !empty( $from)) {
            $filters['host']    = $db->_dbserver;
            $parts = explode( '.', $from);
            if ( count( $parts) > 1) {
               $filters['db']      = trim( $parts[0], '`"\'');   // remove special characters (quotes)
               $filters['dbprefix'] = str_replace( 'users', '', trim( $parts[1], '`"\''));
            }
            // When there is no table name
            else {
               $filters['db']      = $db->_dbname;
               $filters['dbprefix'] = str_replace( 'users', '', trim( $parts[0], '`"\''));
            }
            
            // Search for the site ID that match the criteria
            $modelManage->setFilters( $filters);
            $sites =& $modelManage->getSites( true);
            // If not found
            if ( empty($sites)) {
               // Perhaps this is the master ?
               $config =& Jms2WinFactory::getMasterConfig();
               if ( $filters['host']      == $config->getValue('config.host')
                 && $filters['db']        == $config->getValue('config.db')
                 && $filters['dbprefix']  == $config->getValue('config.dbprefix')
                  )
               {
                  $result = ':master_db:'; 
               }
            }
            // Check there is at least one solution
            else if ( is_array( $sites) && count( $sites) >= 1) {
               // In case where there are several solutions (case of "share whole site"),
               // take the first one and verify this is a physical table
               // Use the foreach because there is a key in the array
               foreach( $sites as $site) {
                  $result = $this->getPhysicalUserSite( $modelManage, $site->id);
                  break;
               }
            }
         }
         // Error
         else {
            $result = null;
         }
      }
      // If this is a physical table
      else {
         /*
         $filters['host']     = $db->_dbserver;
         $filters['db']       = $db->_dbname;
         $filters['dbprefix'] = $db->getPrefix();
         */
         
         if ( !empty( $site_id)) { $result = $site_id; }
         else {
            // If "this" is a slave site then
            if ( defined( 'MULTISITES_ID')) {
               $result = MULTISITES_ID; // Use the current site ID
            }
            // Otherwise, this is the master
            else {
               $result = ':master_db:';
            }
         }
      }
      
      return $result;
   }


} // End Class


// ===========================================================
//             Joomla 1.5 / 1.6 compatibility
// ===========================================================

// Joomla 1.5
class JElementSite extends MultisitesElementSite {}

// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) { 
   class JFormFieldSite extends JElementSite
   {
   	protected $type = 'Site';
   }
}

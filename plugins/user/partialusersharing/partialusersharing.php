<?php
/**
 * @file       partialusersharing.php
 * @brief      Propagate the "#__user_usergroup_map" information into the list of slave sites selected by the administrator.
 * @version    1.2.00
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
 * - V1.0.0    17-AUG-2011: Initial version
 * - V1.2.0    14-MAR-2013: Add Joomla 3.0 compatibility and no more compatible with JMS 1.2.x. Requires JMS 1.3.07 or higher.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.folder');

// Check that the "Multisites" components is installed.
// Otherwise disable code of this plugin
if ( !defined( 'DS'))   { define('DS', DIRECTORY_SEPARATOR); }
if ( JFolder::exists( JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_multisites'))
require_once( JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
{
   // ===========================================================
   //             plgUserPartialUserSharing class
   // ===========================================================
   class plgUserPartialUserSharing extends JPlugin {
   
      //------------ Constructor ---------------
   	function plgUserPartialUserSharing(& $subject, $config)
   	{
   		parent::__construct($subject, $config);
   		$this->_db =& JFactory::getDBO();
   	}
   	
      // ===============================
      //       JOOMLA 1.5 (Not yet really implemented - just copied from joomla 1.6 to get inspired)
      // ===============================
      //------------ onAfterStoreUser ---------------
   	function onAfterStoreUser($user, $isnew, $succes, $msg)
   	{
   	   // Only process new users
   		if ( $isnew ) {
         	$site_ids 	= (array)$this->params->get( 'site_ids', array());
      		if ( empty( $site_ids)) {
               return true;
      		}
   			
      		// Content Items only
      		$query = 'SELECT * FROM #__core_acl_groups_aro_map WHERE user_id='.$user->id; // Invalid syntax (user_id) does not exists
      		$this->_db->setQuery($query, 0, $count);
      		$rows = $this->_db->loadObjectList();
         	
         	if ( !empty( $rows) && is_array( $rows)) {
            	foreach( $site_ids as $site_id) {
               	// Perform a temporary connection on the site_id DB
               	$db =& Jms2WinFactory::getMultiSitesDBO( $site_id, true);
                  if ( empty( $db)) {
                     // echo "MultisitesContent DB is empty for the site [$site_id] <br/>";
                  	continue;
                  	// return $results;
                  }
                  
                  // For each rows present in the "current" website, add them into the "slave site".
                  foreach( $rows as $row) {
                     $db->insertObject( '#__core_acl_groups_aro_map', $row);
                  }
      
            	} // Next site_id
            }
   		}
			return true;
   	}

      //------------ onAfterStoreUser ---------------
   	function onAfterDeleteUser($user, $succes, $msg)
   	{
      	$site_ids 	= (array)$this->params->get( 'site_ids', array());
   		if ( empty( $site_ids)) {
            return true;
   		}
   
      	// For each slave sites
      	if ( !empty( $rows) && is_array( $rows)) {
      	   // Propagate the delete

      		$query = 'DELETE FROM #__core_acl_groups_aro_map WHERE user_id='.$user->id; // Invalid syntax (user_id) does not exists

         	foreach( $site_ids as $site_id) {
            	// Perform a temporary connection on the site_id DB
            	$db =& Jms2WinFactory::getMultiSitesDBO( $site_id, true);
               if ( empty( $db)) {
                  // echo "MultisitesContent DB is empty for the site [$site_id] <br/>";
               	continue;
               	// return $results;
               }
               
         		// Execute the delete for this site ID
         		$db->setQuery( $query );
         		$db->query();
         	} // Next site_id
         }
   	}

      // ===============================
      //       JOOMLA 1.6 and 1.7
      // ===============================
      //------------ onAfterStoreUser ---------------
   	function onUserAfterSave($user, $isnew, $succes, $msg)
   	{
   	   // Only process new users
   		if ( $isnew ) {
         	$site_ids 	= (array)$this->params->get( 'site_ids', array());
      		if ( empty( $site_ids)) {
               return true;
      		}
   			
      		// Retreive the usergroup_map info from the current website
      		$query = 'SELECT * FROM #__user_usergroup_map WHERE user_id='.$user['id'];
      		$this->_db->setQuery($query, 0, $count);
      		$rows = $this->_db->loadObjectList();
         	
         	if ( !empty( $rows) && is_array( $rows)) {
            	foreach( $site_ids as $site_id) {
               	// Perform a temporary connection on the site_id DB
               	$db =& Jms2WinFactory::getMultiSitesDBO( $site_id, true);
                  if ( empty( $db)) {
                     // echo "MultisitesContent DB is empty for the site [$site_id] <br/>";
                  	continue;
                  	// return $results;
                  }
                  
                  // For each rows present in the "current" website, add them into the "slave site".
                  foreach( $rows as $row) {
                     $db->insertObject( '#__user_usergroup_map', $row);
                  }
      
            	} // Next site_id
            }
   		}
			return true;
   	}
   	
      //------------ onUserAfterDelete ---------------
   	function onUserAfterDelete($user, $succes, $msg)
   	{
      	$site_ids 	= (array)$this->params->get( 'site_ids', array());
   		if ( empty( $site_ids)) {
            return true;
   		}
   
      	// For each slave sites
      	if ( !empty( $rows) && is_array( $rows)) {
      	   // Propagate the delete

      		$query = 'DELETE FROM #__user_usergroup_map WHERE user_id='.$user['id'];

         	foreach( $site_ids as $site_id) {
            	// Perform a temporary connection on the site_id DB
            	$db =& Jms2WinFactory::getMultiSitesDBO( $site_id, true);
               if ( empty( $db)) {
                  // echo "MultisitesContent DB is empty for the site [$site_id] <br/>";
               	continue;
               	// return $results;
               }
               
         		// Execute the delete for this site ID
         		$db->setQuery( $query );
         		$db->query();
         	} // Next site_id
         }
   	}
   } // End class
} // End check that Multisites component are installed.

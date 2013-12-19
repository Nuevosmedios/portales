<?php
/**
 * @file       multisitesusersite.php
 * @brief      Save the Multisites Site ID where a joomla user were created.
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
 * - V1.0.0    10-SEP-2011: Initial version
 * - V1.1.0    01-JUN-2012: Add checking if a record already exists to avoid duplicating records in the DB
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
{
   require_once( JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
   // ===========================================================
   //             plgSystemMultisitesUserSite class
   // ===========================================================
   class plgSystemMultisitesUserSite extends JPlugin {
   
      //------------ Constructor ---------------
   	function plgSystemMultisitesUserSite(& $subject, $config)
   	{
   		parent::__construct($subject, $config);
   		$this->_db = new J2WinDatabase( JFactory::getDBO());
   	}
   	
      // ================================================
      //       Internal functions common to all Joomla
      // ================================================

      //------------ _db_recExists ---------------
   	function _db_recExists( $table, $object, $fieldCondition=' AND ')
   	{
   		// Initialise variables.
   		$fields = array();
   		$values = array();
   
   		// Create the base insert statement.
   		$statement = 'SELECT * FROM ' . $this->_db->nameQuote($table) . ' WHERE ';
   		
   
   		// Iterate over the object variables to build the query fields and values.
   		$where = '';
   		$sep = '';
   		$whereEnd = '';
   		if ( !empty( $object->site_id)) {
      		$where    = 'site_id = '. $this->_db->quote( $object->site_id);
      		$sep      = ' AND (';
      		$whereEnd =  ')';
   		}
   		foreach (get_object_vars($object) as $k => $v)
   		{
   		   if ( $k == 'site_id') { continue; }
   		   
   			// Only process non-null scalars.
   			if (is_array($v) or is_object($v) or $v === null)
   			{
   				continue;
   			}
   
   			// Ignore any internal fields.
   			if ($k[0] == '_')
   			{
   				continue;
   			}
   
   			// Put the where values..
   			$where .= $sep . $this->_db->nameQuote($k) .'='. $this->_db->quote($v);
   			$sep = $fieldCondition;
   		}
   		
   		$where .= $whereEnd;
   
   		// Set the query and execute the insert.
   		$this->_db->setQuery( $statement.$where, 0, 1);
   		$row = $this->_db->loadObject();
   		if ( empty( $row))
   		{
   			return false;
   		}
   		
   		return true;
   	}

      //------------ _addUser ---------------
   	function _addUser($user_id, $userDefined=array(), $createTable=true)
   	{
			$row = new stdClass();
   	   $row->user_id  = $user_id;
   	   $row->site_id  = defined( 'MULTISITES_ID') ? MULTISITES_ID : ':master_db:';
   	   
   	   // If there are additional User Defined fields
   	   if (!empty( $userDefined)) {
   	      foreach( $userDefined as $key => $value) {
   	         $row->$key = $value;
   	      }
   	   }
   	   
   	   // Check if an similar record already exists
   	   if ( $this->_db_recExists( '#__multisites_users', $row)) {
            return true;
   	   }
   	   
   	   // If this is the first time that the user is added, set it automatically as the default (home)
   	   $query = 'SELECT count( user_id) as nbUsers FROM #__multisites_users WHERE user_id=' .$user_id;
   		$this->_db->setQuery( $query );
   		$nbUsers = $this->_db->loadResult( 'nbUsers');
   		if ( empty( $nbUsers)) {
   		   $row->home = 1;
   		}

         // If the user can not inserted and we can try to create the mysql table,
         $result = $this->_db->insertObject( '#__multisites_users', $row);
         if ( !$result && $createTable) {
            //  this is probably due to the '#__multisites_users' that does not exists. So create the table
      		$query = 'CREATE TABLE IF NOT EXISTS `#__multisites_users` ('
                   . ' `user_id` int(10) unsigned NOT NULL,'
                   . ' `site_id` varchar(100) NOT NULL,'
                   . ' `home` tinyint(1) NOT NULL DEFAULT \'0\''
                   . ' PRIMARY KEY  (`user_id`, `site_id`)'
                   . ') ENGINE=MyISAM  DEFAULT CHARSET=utf8'
                   ;
      		$this->_db->setQuery( $query );
      		$this->_db->query();
      		$result = $this->_addUser( $user_id, $userDefined, false);
         }

   		return $result;
   	}

      //------------ _updateUser ---------------
      /**
       * @brief Update a record based on the keyList.
       * @param keyList is an array with the parameters that must be used in the where clause.
       */
   	function _updateUser( $keyList, $field2Update=array(), $fieldCondition=' OR ')
   	{
   	   if ( empty( $keyList) && !is_array( $keyList)) {
   	      return true;
   	   }
   	   
			$row = new stdClass();
   	   $row->site_id  = defined( 'MULTISITES_ID') ? MULTISITES_ID : ':master_db:';
   	   
   	   // Use the key list to build the where clause
	      foreach( $keyList as $key => $value) {
	         $row->$key = $value;
	      }
   	   
   	   // If record is found,
   	   if ( $this->_db_recExists( '#__multisites_users', $row, $fieldCondition)) {
   	      // retreive the record found
   	      $object = $this->_db->loadObject();

   	      // Update the fields
   	      foreach( $field2Update as $key => $value) {
   	         $object->$key = $value;
   	      }
            $result = $this->_db->updateObject( '#__multisites_users', $object, 'id');
   	   }
   	   // When not found
   	   else {
   	      // Add the field2update
   	      foreach( $field2Update as $key => $value) {
   	         $row->$key = $value;
   	      }
            $result = $this->_db->insertObject( '#__multisites_users', $row);
         }

   		return $result;
   	}

      //------------ _deleteUser ---------------
   	function _deleteUser($user_id)
   	{
   		$query = 'DELETE FROM #__multisites_users WHERE user_id='.$user_id;

   		// Execute the delete for this site ID
   		$this->_db->setQuery( $query );
   		$this->_db->query();
   		return true;
   	}


      // ===============================
      //       JOOMLA 1.5
      // ===============================
      //------------ onAfterStoreUser ---------------
   	function onAfterStoreUser($user, $isnew, $succes, $msg)
   	{
   	   return $this->_addUser( $user['id']);
   	}

      //------------ onAfterStoreUser ---------------
   	function onAfterDeleteUser($user, $succes, $msg)
   	{
   	   return $this->_deleteUser( $user['id']);
   	}

      // ===============================
      //       JOOMLA 1.6 and 1.7
      // ===============================
      //------------ onAfterStoreUser ---------------
   	function onUserAfterSave($user, $isnew, $succes, $msg)
   	{
   	   return $this->_addUser( $user['id']);
   	}
   	
      //------------ onUserAfterDelete ---------------
   	function onUserAfterDelete($user, $succes, $msg)
   	{
   	   return $this->_deleteUser( $user['id']);
   	}
   } // End class
} // End check that Multisites component are installed.

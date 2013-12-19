<?php
/**
 * @file       multisites_users.php
 * @version    1.2.00
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.0.00 02-OCT-2011: File creation
 * - V1.2.00 14-MAR-2013: Add Joomla 3.0 compatibility and no more compatible with JMS 1.2.x. It requires JMS 1.3.07 or higher.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );


// ===========================================================
//             TableMultisitesUserSite class
// ===========================================================
/**
 * @brief   Description of the "#__multisites_users" table
 */
class TableMultisites_Users extends JTable
{
	// Primary key
	var $id                 = null;
   // Key
	var $user_id            = null;
	var $site_id 				= null;
	var $home 				   = 0;
	// Joomla Management fields
	var $checked_out 		   = 0;
	var $checked_out_time   = 0;
	var $created_dt 	      = 0;  //created date
	var $created_by 			= null;// userid
	var $modified_dt 	      = 0;
	var $modified_by 			= null;

   // -------------- Constructor --------------
	/**
	* @param database A database connector object
	*/
	function __construct(&$db)
	{
		parent::__construct( '#__multisites_users', 'id', $db );

		$now =& JFactory::getDate();
	        if ( method_exists( $now, 'toMySQL'))   { $this->set( 'created_dt', $now->toMySQL() ); }   // Joomla 1.5 -> 2.5
	   else if ( method_exists( $now, 'toSql'))     { $this->set( 'created_dt', $now->toSql() ); }     // Joomla 3.0
		

		$user = JFactory::getUser();
		$this->set( 'created_by', $user->get( 'id'));
	}
	
   // -------------- Store --------------
	public function store($updateNulls = false)
	{
		// If the 'Home' flag is enabled
		if ($this->home!='0') {
		   // Clear the home flag for all the sites of the user
   		$query = 'UPDATE #__multisites_users'
   		       . ' SET home = \'0\''
   		       . ' WHERE user_id = '.(int) $this->user_id
   		       . '   AND home = \'1\''
   		       ;
   		$this->_db->setQuery( $query);
         $this->_db->query();
		}
		
		// Update the modification user id and date/time
		$now =& JFactory::getDate();
	        if ( method_exists( $now, 'toMySQL'))   { $this->set( 'modified_dt', $now->toMySQL() ); }  // Joomla 1.5 -> 2.5
	   else if ( method_exists( $now, 'toSql'))     { $this->set( 'modified_dt', $now->toSql() ); }    // Joomla 3.0
		

		$user = JFactory::getUser();
		$this->set( 'modified_by', $user->get( 'id'));
		
	   return parent::store( $updateNulls);
	}
}

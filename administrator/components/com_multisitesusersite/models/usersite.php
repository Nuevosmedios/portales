<?php
/**
 * @file       ListUserSite.php
 * @version    1.2.00
 * @author     Edwin CHERONT     (info@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2013 Edwin2Win sprlu - all right reserved.
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
 * - V1.0.0    18-APR-2011: Initial version
 * - V1.2.00   14-MAR-2013: Add Joomla 3.0 compatibility and no more compatible with JMS 1.2.x. It requires JMS 1.3.07 or higher.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'legacy.php');


// ===========================================================
//             MultisitesUserSiteModelUserSite class
// ===========================================================
/**
 * @brief Read all the extensions definition present in the Jms Multi Sites tool menu.
 * This consists in the processing of the "dbtables.xml", "dbsharing.xml" and "dbsharing_16.xml".
 */
class MultisitesUserSiteModelUserSite extends J2WinModel
{
   // Private members
   var $_modelName = 'ListUserSite';
   
   // Public members
   var $id              = 'fake';

   // =====================================
   //             LIST
   // =====================================

   var $_countAll = 0;
   
   function &getAllUserSite()
   {
	   $filters = $this->getState( 'filters');
	   
   	$db =& JFactory::getDBO();

		// Call the "User Defined" plugins to get additional filtering and left join
		JPluginHelper::importPlugin('system');
		$dispatcher = &JDispatcher::getInstance();
		$additionalConditions = array();
		$additionalSelect = array();
		$additionalJoin = array();
      $dispatcher->trigger('onModelUserSite_getAllUserSite',array( $filter_search, &$additionalConditions, &$additionalSelect, &$additionalJoin));

   	$where = '';
   	$orderby = '';
      $conditions = '';
      $andStr = '';
	   // Extract the filtering selection
	   if ( !is_null($filters)) {
	      // If there is a filtering on the Affiliate User ID
	      if ( !empty( $filters['user_ids']) && $filters['user_ids'] != '[unselected]') {
	         $filter_user_id = $filters['user_ids'];
	         $conditions .= $andStr . "(mus.user_id=" . (int)$filter_user_id .")";
	         $andStr = ' AND ';
	      }
	      if ( !empty( $filters['site_ids']) && $filters['site_ids'] != '[unselected]') {
	         $filter_site_id = $filters['site_ids'];
	         $conditions .= $andStr . "(mus.site_id=" . $db->Quote( $filter_site_id) .")";
	         $andStr = ' AND ';
	      }

   		if ( !empty( $filters) && !empty( $filters['search'])) {
	         $filter_search = strtoupper( $filters['search']);
	         $conditions .= $andStr 
	                     .  '('
	                     . '(UCASE( u.username) like "%' .$db->getEscaped( $filter_search). '%")'
	                     . ' OR (UCASE( u.name) like "%' .$db->getEscaped( $filter_search). '%")'
	                     . ' OR (UCASE( mus.site_id) like "%' .$db->getEscaped( $filter_search). '%")'
	                     . implode( '', $additionalConditions)
	                     .  ')'
	                     ;
	         $andStr = ' AND ';
	      }
	      
         // Add the condition to the clause WHERE
         if ( !empty( $conditions)) {
            if ( empty( $where)) $where  = " WHERE $conditions";
            else                 $where .= " AND ($conditions)";
         }


   	   // ORDER BY
   	   if ( !empty( $filters['order'])) {
   	      $orderby = ' ORDER BY ' . $filters['order'];
   	      if ( !empty( $filters['order_Dir'])) {
      	      $orderby .= ' '. $filters['order_Dir'];
   	      }
   	   }
	   }
	   
		// get all records joined with Joomla Users only
		$query = 'SELECT mus.*, u.name, u.username'
		       . implode( '', $additionalSelect)
				 . ' FROM #__multisites_users as mus'
				 . ' LEFT JOIN #__users as u ON u.id = mus.user_id'
		       . implode( '', $additionalJoin)
		       . $where
		       . $orderby
		       ;

      $dispatcher->trigger('onModelUserSite_BeforeQuery',array( &$query));
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		
		$this->_countAll = count( $rows);
		return $rows;
   }

   //------------ getCountAll ---------------
   /**
    * @return the total number of records.
    */
   function getCountAll()
   {
      return $this->_countAll;
   }

   //------------ setFilters ---------------
   function setFilters( &$filters)
   {
      $this->setState( 'filters', $filters);
   }

   //------------ removeFilters ---------------
   function removeFilters()
   {
      $this->setState( 'filters', null);
   }
   
   // =====================================
   //             RECORD
   // =====================================

   //------------ getRecord ---------------
   function getRecord($id=null, $checkout=true)
   {
	   $table =& JTable::getInstance('Multisites_Users', 'Table');
	   
	   // load the row from the db table
	   if(!is_null($id)) {
	      if ( !$table->load( $id )) {
				$this->setError($table->getError());
	         return null;
	      }
	      // If want to check the record
	      if ( $checkout) {
      		$user = JFactory::getUser();

   			// Check if this is the user having previously checked out the row.
   			if ($table->checked_out > 0 && $table->checked_out != $user->get('id')) {
   				$this->setError(JText::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH'));
   				return null;
   			}
   
   			// Attempt to check the row out.
   			if (!$table->checkout($user->get('id'))) {
   				$this->setError($table->getError());
   				return null;
   			}
	      }
	   }
      return $table;
   }
   
   //------------ saveRecord ---------------
   /**
    * @brief Save the record based on the "POST" values.
    */
   function saveRecord()
   {
      //require_once('administrator'.DS.'.components'.DS.'com_MultisitesUserSite'.DS.'tables'.DS.'MultisitesUserSite.php');
	   // Check for request forgeries
	   JRequest::checkToken() or jexit( 'Invalid Token' );
	
	   // Initialize variables
	   $db      =& JFactory::getDBO();
	   $table   =& JTable::getInstance('Multisites_Users', 'Table');
	   $post    = JRequest::get( 'post' );
	   
	   if (!$table->bind( $post )) {
	      JError::raiseError(500, $table->getError() );
	   }
	
	   // pre-save checks
	   if (!$table->check()) {
	      JError::raiseError(500, $table->getError() );
	   }
	
	   // save the changes
	   if (!$table->store()) {
	      JError::raiseError(500, $table->getError() );
	   }
	   $table->checkin();
   }
  
   //------------ deleteRecord ---------------
   /**
    * @brief Delete a record based on its id
    */
   function deleteRecord($id)
   {
      JRequest::checkToken() or jexit( 'Invalid Token' );
   
      // Initialize variables
      $table   =& JTable::getInstance('Multisites_Users', 'Table');
      
      if ( version_compare( JVERSION, '3.0') >= 0) {} // Function removed
      // Joomla 1.5 -> 2.5
      else {
         // Check if the record can be deleted
         if (!$table->canDelete($id)) {
            JError::raiseError(500, $table->getError() );
         }
      }
      if (!$table->delete($id)) {
         JError::raiseError(500, $table->getError() );
      }
   }


   //------------ setHome ---------------
   /**
    * @param   pks = array of 'id's
    */
	function setHome(&$pks)
	{
		// Initialise variables.
		$db	 =& $this->getDBO();
	   $table =& JTable::getInstance('Multisites_Users', 'Table');
		$pks   = (array)$pks;

		foreach ($pks as $i => $id)
		{
   	   // load the row from the db table
   	   if(!empty($id)) {
   	      // Retreive the user_id corresponding to the selected record
   	      if ( $table->load( $id )) {
   	         $table->set( 'home', 1);
   	         $table->store();
         	}
         }
   
   		unset($pks[$i]);
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

   //------------ setHome ---------------
	/**
	 * Custom clean cache method
	 *
	 * @since	1.6
	 */
	function cleanCache()
	{
      if ( version_compare( JVERSION, '1.6') >= 0) {
   		parent::cleanCache('com_multisitesusersite');
   		parent::cleanCache('_system');
   	}
	}


} // End class

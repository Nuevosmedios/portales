<?php
/**
 * @file       view.php
 * @version    1.2.00
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
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
 * - V1.0.0  19-APR-2011 : File creation
 * - V1.0.0  31-MAY-2012 : Add basic ACL for joomla 2.5
 * - V1.2.00 14-MAR-2013: Add Joomla 3.0 compatibility and no more compatible with JMS 1.2.x. It requires JMS 1.3.07 or higher.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'legacy.php');


// ===========================================================
//             MultisitesUserSiteViewUserSite class
// ===========================================================
class MultisitesUserSiteViewUserSite extends J2WinView
{
   // Private members
   var $_formName   = 'List';
   var $_lcFormName = 'list';

   // =====================================
   //             LIST
   // =====================================

   //------------ ListUserSite ---------------
   function listUserSite($tpl=null)
   {
      // JRequest::setVar( 'hidemainmenu', 1 );

      $mainframe  = &JFactory::getApplication();
      $option     = JRequest::getCmd('option');

      $this->setLayout( 'list');
      
      // retreive the filters and parameters that limit the query
      $filters = &$this->_getFilters();
      $this->assignRef('filters', $filters);
      
      // assign filter to the model
      $model = &$this->getModel();
      $model->setFilters( $filters);
      
      $rows = &$this->get('AllUserSite');
      $this->assignRef('rows',       $rows);
      $msg = &$this->get( 'Error');
      if ( !empty( $msg)) {
         $mainframe->enqueueMessage( $msg);
      } 

      /*
       * Set toolbar items for the page
       */
      $formName   = $this->_formName;
      $lcFormName = $this->_lcFormName;

      J2WinToolBarHelper::title( JText::_( "User/Site management" ), 'config.png' );
      J2WinToolBarHelper::addNewX( "newusersite" );
      J2WinToolBarHelper::editListX( "editusersite");
      J2WinToolBarHelper::customX( "deleteusersite", 'delete.png', 'delete_f2.png', 'Delete', true );
		J2WinToolBarHelper::makeDefault('setDefault', 'MULTISITESUSERSITE_TOOLBAR_SET_HOME');
      
      if ( version_compare( JVERSION, '1.7') >= 0) {
         // Options button.
         if (JFactory::getUser()->authorise('core.admin', 'com_multisitesusersite')) {
         	J2WinToolBarHelper::preferences('com_multisitesusersite');
         }
      }
      if ( version_compare( JVERSION, '3.0') >= 0) { 
		   JFactory::getDocument()->addStyleSheet('components/com_multisites/css/list.css');
         JFactory::getDocument()->addScript("components/com_multisites/assets/j30/ischecked.js");
         JFactory::getDocument()->addScriptDeclaration( 'function checkAll( n) { return Joomla.checkAll( n); }');
		}
      
		$lists		= &$this->_getViewLists( $filters, true, true);
		$pagination	= &$this->_getPagination( $filters, $this->get('CountAll'));


		// Assign view variable with will be used by the template
		$this->assignRef('pagination',   $pagination);
		$this->assignRef('lists',        $lists);
		$this->assignRef('limitstart',   $limitstart);
		$this->assignRef('option',       $option);

      JHTML::_('behavior.tooltip');

      parent::display($tpl);
   }
   
   //------------ _getFilters ---------------
   /**
    * @brief Return all the filter values posted by the "display" form (the list) and also store the values into the registry for later use.
    * The filter values are used by the model to filter, sort and limit the records that must be displayed in the list.
    */
   function &_getFilters()
   {
      $mainframe  = &JFactory::getApplication();
      $option = JRequest::getCmd('option');
      $filters = array();

      $client                 = JRequest::getWord( 'filter_ListUserSite', 'ListUserSite' );

      // Retreive search filter
      $search       = $mainframe->getUserStateFromRequest( "$option.$client.search",    'search',           '',         'string' );
      $filters['search']     = JString::strtolower( $search);


      // Retreive filter combo values
      $filters['user_ids']     = $mainframe->getUserStateFromRequest( "$option.$client.filter_user_ids", 'filter_user_ids',    '[unselected]',         'string' );
      $filters['site_ids']    = $mainframe->getUserStateFromRequest( "$option.$client.filter_site_ids",  'filter_site_ids',   '[unselected]',         'string' );

      // Retreive selected sort column and direction
      $filters['order']      = $mainframe->getUserStateFromRequest( "$option.$client.filter_order",     'filter_order',       '',         'cmd' );
      $filters['order_Dir']  = $mainframe->getUserStateFromRequest( "$option.$client.filter_order_Dir", 'filter_order_Dir',   '',         'word' );
            // Retreive the limit for display
      $filters['limit']       = $mainframe->getUserStateFromRequest( 'global.list.limit',                'limit',             $mainframe->getCfg('list_limit'), 'int' );
		$filters['limitstart']  = JRequest::getVar('limitstart', '0', '', 'int');

      return $filters;
   }

   //------------ _getViewLists ---------------
   /**
    * @return the lists[] array containing all the combo and filters used in the view.
    */
   function &_getViewLists( &$filters, $facultative_status=false, $onChangeStatus=false)
   {

      // build list of users
      $lists['user_ids']   = JHTML::_('list.users',  'filter_user_ids', $filters['user_ids'], 1, 'onchange="document.adminForm.submit();"', 'name', 0 );

      // build list of site IDs
      $model = &$this->getModel( 'manage');
		$sites = $model->getSites();
		$lists['site_ids']	= MultisitesHelper::getSiteIdsList( $sites, $filters['site_ids'], 'filter_site_ids', 'Sites', 'onchange="document.adminForm.submit();"');

		// table ordering
		$lists['order_Dir']	= $filters['order_Dir'];
		$lists['order']		= $filters['order'];

		// search filter
		$lists['search']     = $filters['search'];

		return $lists;
   }
   

   //------------ _getPagination ---------------
	function &_getPagination( &$filters, $total=0)
	{
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $total, $filters['limitstart'], $filters['limit'] );
		return $pagination;
	}


   // =====================================
   //             RECORD
   // =====================================

   //------------ editRecord ---------------
   function editRecord($id=null, $tpl=null)
   {
      JRequest::setVar( 'hidemainmenu', 1 );

      $mainframe  = &JFactory::getApplication();
      $option     = JRequest::getCmd('option');

      $this->setLayout( 'detail');
      
      // Get record
      $model = &$this->getModel();
      $row = &$model->getRecord($id);
      $this->assignRef('row', $row);
      $msg = &$this->get( 'Error');
      if ( !empty( $msg)) {
         $mainframe->enqueueMessage( $msg);
      } 

      /*
       * Set toolbar items for the page
       */
      J2WinToolBarHelper::title( JText::_( "Detail" ), 'config.png' );

      //save close help
      J2WinToolBarHelper::custom( "saveusersite", 'save.png', 'save_f2.png', 'Save', false );
      J2WinToolBarHelper::cancel( 'listusersite');
//      J2WinToolBarHelper::help( 'screen.' .$this->_lcFormName. 'usersite.new', true );

      if ( version_compare( JVERSION, '3.0') >= 0) { 
		   JFactory::getDocument()->addStyleSheet('components/com_multisites/css/list.css');
		}
      
      $this->assignRef('option',    $option);
      
      $lists      = &$this->_getRecordLists( $filters, $row);
      $this->assignRef('lists',        $lists);
      
      JHTML::_('behavior.tooltip');

      parent::display($tpl);
   }
   
  //------------ _getRecordLists ---------------
   /**
    * @return the lists[] array containing all the combo and filters used in the view.
    */
   function &_getRecordLists( &$filters, &$row)
   {
      $lists = array();

      // build list of users
      $lists['user_id']          = JHTML::_('list.users',  'user_id', $row->user_id, 0, NULL, 'name', 0 );

      // build list of site IDs
      $model = &$this->getModel( 'manage');
		$sites = $model->getSites();
		$lists['site_id']	= MultisitesHelper::getSiteIdsList( $sites, $row->site_id, 'site_id', '', '');


   	JPluginHelper::importPlugin('system');
   	$dispatcher = &JDispatcher::getInstance();
      $dispatcher->trigger('onViewUserSite_getRecordLists', array( &$this, &$lists));

      return $lists;
   }


   //------------ deleteVideo ---------------
   
   function deleteUserSite(){
      // JRequest::setVar( 'hidemainmenu', 1 );

      $mainframe  = &JFactory::getApplication();
      $option     = JRequest::getCmd('option');
   }  


} // End class

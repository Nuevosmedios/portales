<?php
/**
 * @file       controller.php
 * @brief      Multisites User Site administration.
 *
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
 * - V1.0.0 03-SEP-2011: File creation
 * - V1.1.3 02-DEC-2011: Add "user manual" option in the menu
 * - V1.2.0 14-MAR-2013: Add Joomla 3.0 compatibility and no more compatible with JMS 1.2.x
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );


// ===========================================================
//             MultisitesUserSiteController class
// ===========================================================
/**
 * @brief Multi Sites User Site controler.
 */
class MultisitesUserSiteController extends J2WinController
{

   // =====================================
   //        Manage Multisites USERSITE
   // =====================================

   // -------------- restricted --------------
	/**
	 * @brief Display a restricted page to give the access to the option menu
	 */
	function restricted()
	{
	   echo JText::_('Access forbidden! Only the website where the data is physically stored can manage the users permissions');
      if ( version_compare( JVERSION, '1.7') >= 0) {
         // Options button.
         if (JFactory::getUser()->authorise('core.admin', 'com_multisitesusersite')) {
         	J2WinToolBarHelper::preferences('com_multisitesusersite');
         }
      }
//      $this->addSubmenu( JRequest::getWord('task', 'restricted'));
	}

   
   // -------------- listusersite --------------
	/**
	 * @brief List the User/Sites present in the DB
	 */
	function listusersite()
	{
      $model   =& $this->getModel( 'UserSite' );
      $view    =& $this->getView( 'UserSite');
      $view->setModel( $model, true );

   	// Get the Jms Multi Sites manage model
   	$modelManage   =& $this->getModel( 'manage',     'MultisitesModel' );
      $view->setModel( $modelManage);
      
      $view->listUserSite();
      $this->addSubmenu( JRequest::getWord('task', 'listusersite'));
	}

   // -------------- newusersite --------------
   /**
    * @brief Create a new User/Site record
    */
   function newusersite()
   {
      $model   =& $this->getModel( 'UserSite' );
      $view    =& $this->getView( 'UserSite');
      $view->setModel( $model, true );

   	// Get the Jms Multi Sites manage model
   	$modelManage   =& $this->getModel( 'manage',     'MultisitesModel' );
      $view->setModel( $modelManage);

      $view->editRecord();
      $this->addSubmenu( JRequest::getWord('task', 'newusersite'));
   }

   // -------------- editusersite --------------
   function editusersite()
   {
      $model   =& $this->getModel( 'UserSite' );
      $view    =& $this->getView( 'UserSite');
      $view->setModel( $model, true );

   	// Get the Jms Multi Sites manage model
   	$modelManage   =& $this->getModel( 'manage',     'MultisitesModel' );
      $view->setModel( $modelManage);
      
      $id = JRequest::getInt( 'id');
		
		// If a specific ID is provided (case of a link)
		if ( !empty( $id)) {
         $view->editRecord($id);
		}
		// When there is no ID, verify the Checkbox field
		else {
   		$cid	= JRequest::getVar('cid', array(), '', 'array');
   		// Make sure the ids are integers
   		JArrayHelper::toInteger($cid);
   		
   		// Process the first ID
   		if ( !empty( $cid)) {
      		foreach( $cid as $id) {
               $view->editRecord($id);
               break;
            }
         }
      }
      $this->addSubmenu(JRequest::getWord('task', 'editusersite'));
   }
   
   // -------------- saveusersite --------------
   function saveusersite()
   {
      $model   =& $this->getModel( 'UserSite' );
      $model->saveRecord();
      $msg = $model->getError();
      if ( empty( $msg)) {
         $msg = JText::_( 'Record successfully saved');
      }
      
      $Itemid = JRequest::getInt('Itemid');
      $this->setRedirect( 'index.php?option=com_multisitesusersite&task=&Itemid='.$Itemid, $msg);
   	
   }
   
   // -------------- applyusersite --------------
   function applyusersite()
   {
      $model   =& $this->getModel( 'UserSite' );
      $model->saveRecord();
      $msg = $model->getError();
      if ( empty( $msg)) {
         $msg = JText::_( 'Record successfully saved');
      }

      $view    =& $this->getView( 'UserSite');
      $view->setModel( $model, true );
      $view->editRecord($id);
      $this->addSubmenu(JRequest::getWord('task', 'editusersite'));
   }

   // --------------  deleteusersite --------------
   function deleteusersite()
   {
      $model   =& $this->getModel( 'UserSite' );

      // If a specific ID is provided (case of a link)
      $id = JRequest::getInt('id');
		if ( !empty( $id)) {
         $model->deleteRecord($id);
         $msg = $model->getError();
		}
		// When there is no ID, verify the Checkbox field
		else {
   		$cid	= JRequest::getVar('cid', array(), '', 'array');
   		// Make sure the ids are integers
   		JArrayHelper::toInteger($cid);
   		
   		// Process the first ID
   		if ( !empty( $cid)) {
   		   $msg = '';
      		foreach( $cid as $id) {
               $model->deleteRecord($id);
               $msg .= $model->getError();
            }
         }
      }
      
      if ( empty( $msg)) {
         $msg = JText::_( 'Record successfully deleted');
      }
      $Itemid = JRequest::getInt('Itemid');
      $this->setRedirect( 'index.php?option=com_multisitesusersite&task=&Itemid='.$Itemid, $msg);
   }


   // -------------- setDefault --------------
   /**
    * @brief Set the user default site.
    */
	function setDefault()
	{
		// Check for request forgeries
		JRequest::checkToken('default') or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid	= JRequest::getVar('cid', array(), '', 'array');
//		$cid	= JRequest::getVar('id', array(), '', 'array');
		$data	= array('setDefault' => 1, 'unsetDefault' => 0);
		$task 	= $this->getTask();
		$value	= JArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid)) {
			JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_ITEM_SELECTED'));
		} else {
			// Get the model.
         $model   =& $this->getModel( 'UserSite' );

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Publish the items.
			if (!$model->setHome($cid, $value)) {
				JError::raiseWarning(500, $model->getError());
			} else {
				if ($value == 1) {
					$ntext = 'MULTISITESUSERSITE_SET_HOME';
				}
				else {
					$ntext = 'MULTISITESUSERSITE_UNSET_HOME';
				}
				$this->setMessage( JText::_( $ntext));
			}
		}

      $option     = JRequest::getCmd('option');
		$this->setRedirect(JRoute::_( 'index.php?option='.$option, false));
	}



   // =====================================
   //             SUB MENU
   // =====================================

   //------------ addSubmenu ---------------
   /**
    * Generic function that display the submenu
    */
   function addSubmenu($vName)
   {
      // If Joomla 1.5
      if ( version_compare( JVERSION, '1.5') <= 0) {
         // Don't do anything
         return;
      }
      
      // If Joomla 1.6, build the submenu based on the menu definition present in the DB

      // Retreive all the submenu
      $option     = JRequest::getCmd('option');
      $db =& JFactory::getDBO();
      $query = "SELECT c.title, c.link, c.alias FROM #__menu as p"
             . " LEFT JOIN #__menu as c ON c.parent_id=p.id"
             . " WHERE p.level = 1 AND p.type='component' AND p.title='$option'"
             . " ORDER BY c.id"
             ;
      $db->setQuery( $query );
      $rows = $db->loadObjectList();
      if ( empty( $rows)) {
         return;
      }

      // Load the "system" menu
      $lang = JFactory::getLanguage();
         $lang->load($option.'.sys', JPATH_ADMINISTRATOR, null, false, false)
      || $lang->load($option.'.sys', JPATH_ADMINISTRATOR.'/components/'.$option, null, false, false)
      || $lang->load($option.'.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
      || $lang->load($option.'.sys', JPATH_ADMINISTRATOR.'/components/'.$option, $lang->getDefault(), false, false);
      
      foreach ($rows as $row) {
         // Extract the task value present in the link
         $pos = strpos( $row->link, '?');
         if ( $pos === false) {
            $param_url = $row->link;
         }
         else {
            $param_url = substr( $row->link, $pos+1);
         }
         $params_array = explode( '&', $param_url);
         foreach( $params_array as $param) {
            $keyvalues = explode( '=', $param);
            if ( $keyvalues[0] == 'task') {
               $menuTask = $keyvalues[1];
            }
         }
         
         // If it was not possible to get the task parameter, use the "alias" as name
         if ( empty( $menuTask)) {
            $menuTask = $row->alias;
         }
         
         JSubMenuHelper::addEntry(
            JText::_( strtoupper( $row->title)),
            $row->link,
            $vName == $menuTask
         );
      }
   }

	// =====================================
	//             USER MANUAL
	// =====================================
 
   //------------ usersManual ---------------
   /**
    * @brief Redirect to the online User Manual.
    */
	function usersManual()
	{
   	$option = JRequest::getCmd('option');
		$mainframe	= &JFactory::getApplication();
      $version = $this->_getVersion();
      $url = 'http://www.jms2win.com/index.php?option=com_docman&task=findkey&keyref='.$option.'.usersmanual&version='.$version;
      $mainframe->redirect( $url);
		$this->addSubmenu(JRequest::getWord('task', 'about'));
	}



	// =====================================
	//             ABOUT
	// =====================================

   //------------ _getVersion ---------------
   /**
    * @brief Retreive the version number of this component.
    */
	function _getVersion()
	{
	   jimport( 'joomla.application.helper');
	   $version = "unknown";
	   $filename = dirname(__FILE__) .DS. MULTISITES_MANIFEST_FILENAME;
		if ($data = JApplicationHelper::parseXMLInstallFile($filename)) {
		   // If the version is present
		   if (isset($data['version']) && !empty($data['version'])) {
		      $version = $data['version'];
		   }
		}
		return $version;
	}


   //------------ about ---------------
	function about()
	{
       $model          =& $this->getModel( 'registration', 'Edwin2WinModel' );
	   $latestVersion  = $model->getLatestVersion();

		JToolBarHelper::title( JText::_( 'About Multisite User Site'), 'config.png' );
		$yyyy = date( 'Y');
?>
<h3>Multisites User Site</h3>
<p>Version 
<?php
$getLatestURL = '';
$version = $this->_getVersion();
if ( !empty( $latestVersion['version'])) {
   if ( version_compare( $version, $latestVersion['version']) < 0) {
      echo '<font color="red">' . $version .'</font>';
      $getLatestURL = ' <a href="http://www.jms2win.com/get-latest-version">Get Latest Version</a>';
   }
   else {
      echo '<font color="green">' . $version .'</font>';
   }
   echo ' <em>(' . JText::_( 'Latest available') . ': ' . $latestVersion['version'] . ')</em>';
}
else {
   echo $version;
}
// If is registered and there is a new version
if ( $model->isRegistered() && !empty( $getLatestURL)) {
   echo '<br/>' . $getLatestURL;
}

?></p>
<img src="components/com_multisites/images/multisites_logo.jpg" alt="Joomla Multi Sites" />
<h3>Copyright</h3>
<p>Copyright 2008-<?php echo $yyyy; ?> Edwin2Win sprlu<br/>
Rue des robiniers, 107<br/>
B-7024 Ciply<br/>
Belgium
</p>
<p>All rights reserved.</p>
<a href="http://www.jms2win.com">www.jms2win.com</a>
<?php

      // Product Info
      $regInfo =  $model->getRegistrationInfo();
      if ( empty( $regInfo) || empty( $regInfo['product_id'])) {}
      else {
         echo '<br/>' . JText::_( 'Product ID') . ' :' . $regInfo['product_id'];;
      }
      
   	// If not registered
		if ( !$model->isRegistered()) {
      	$view    =& $this->getView(  'registration', '', 'Edwin2WinView');
      	$view->setModel( $model, true );
      	$view->registrationButton();
		}
		MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
	} // End about

   //------------ registered ---------------
   /**
    * @brief When the status is OK, this save the registered informations.
    * This task is called by the redirect url parameters for the registration button.
    */
	function registered()
	{
   	$option = JRequest::getCmd('option');
   	$model   =& $this->getModel( 'registration',     'Edwin2WinModel' );
   	$view    =& $this->getView(  'registration', '', 'Edwin2WinView');
   	$view->setModel( $model, true );
   	$msg = $view->registered( false);
		$this->setRedirect( 'index.php?option=' . $option . '&task=manage', $msg);
	}
	


} // End class


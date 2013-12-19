<?php
/**
 * @file       controller.php
 * @brief      Multi Sites articles sharing administration allows to purge the cache of the converted com_content files.
 *
 * @version    1.1.8
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
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
 * - V1.0.0 25-NOV-2008: File creation
 * - V1.0.1 05-JAN-2009: Remove Warning: Call-time pass-by-reference has been deprecated on line 268 and 309
 *                       $replyStr   = $elt->fetchElement($name, $value, $node, $control_name);
 * - V1.0.2 12-JAN-2009: Fix problem with AJAX routines that call Category and Section element with first
 *                       capital letter. The name must be in lower case due to unix case sensitivity and because
 *                       the joomla files are present in lower case.
 * - V1.0.7 25-JUN-2009: Fix URL jms2win.com in the about form.
 * - V1.1.0 18-MAY-2011: Add Joomla 1.6 compatibility
 * - V1.1.2 18-AUG-2011: Fix the refresh of category list under joomla 1.5
 * - V1.1.5 16-MAY-2012: Add possibility in Ajax to add the "All Categories" that is used by Joomla 2.5 featured
 * - V1.1.6 02-JUN-2012: Add basic ACL for joomla 2.5
 * - V1.1.7 04-DEC-2012: Fix joomla 1.5 detection for the "add SubMenu"
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');



// ===========================================================
//             ContentController_j15 class
// ===========================================================
/**
 * @brief Multi Sites Article Sharing controler.
 */
class ContentController_j15 extends JController
{
	// =====================================
	//             DUPLICATED FROM com_content/controller
	// =====================================
	/**
	 * Articles element
	 */
	function element()
	{
      // Connect to DB site_id
		$site_id = JRequest::getString( 'site_id', null);
		
		if ( !empty( $site_id)) {
         require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' 
                       .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
   		$dbSite  =& Jms2WinFactory::getMultisitesDBO( $site_id);
   		$db      =& JFactory::getDBO();
   		$saveDB  = $db;
   		$db      = $dbSite;
   	}

		$model	= &$this->getModel( 'element' );
		$view	= &$this->getView( 'element');
		$view->setModel( $model, true );
		$view->display();

	   // Restore the current DB
	   if ( isset( $saveDB)) {
   	   $db = $saveDB;
	   }
	}

   //------------ addSubmenu ---------------
   /**
    * Generic function that display the submenu
    */
	function addSubmenu($vName)
	{
      // If Joomla 1.6 or higher
      if ( version_compare( JVERSION, '1.6') >= 0) {}
      // If Joomla 1.5
      else {
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
		$lang	= JFactory::getLanguage();
			$lang->load($option.'.sys', JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($option.'.sys', JPATH_ADMINISTRATOR.'/components/'.$option, null, false, false)
		||	$lang->load($option.'.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
		||	$lang->load($option.'.sys', JPATH_ADMINISTRATOR.'/components/'.$option, $lang->getDefault(), false, false);
      
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
	//             PURGE CACHE
	// =====================================

   //------------ purgeCache ---------------
   /**
    * Purge the files and folders that were created with the converted JMS codes.
    *
    * The converted files and folders are prefixed with "multisites." ...
    */
	function purgeCache()
	{
		JToolBarHelper::title( JText::_( 'Purge Cache'), 'config.png' );
		
   	$option = JRequest::getCmd('option');
	   $rc = true;
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
	   
		// Remove all the files "multisites.*" in administator folder
		$path = JPATH_COMPONENT_ADMINISTRATOR;
		$files = JFolder::files($path, 'multisites\..', false, true, array());
		if (count($files)) {
			if (JFile::delete($files) !== true) {
				// JFile::delete throws an error
				$rc = false;
			}
		}

		// Remove all the Folders "multisites.*" in administator folder
		$path = JPATH_COMPONENT_ADMINISTRATOR;
		$folders = JFolder::folders($path, 'multisites\..', false, true, array());
		foreach( $folders as $folder) {
			if (JFolder::delete($folder) !== true) {
				// JFolder::delete throws an error
				$rc = false;
			}
		}


		// Remove all the files "multisites.*" in the front-end folder
		$path = JPATH_SITE .DS. 'components' .DS. $option;
		$files = JFolder::files($path, 'multisites\..', false, true, array());
		if (count($files)) {
			if (JFile::delete($files) !== true) {
				// JFile::delete throws an error
				$rc = false;
			}
		}

		// Remove all the Folders "multisites.*" in the front-end folder
		$path = JPATH_SITE .DS. 'components' .DS. $option;
		$folders = JFolder::folders($path, 'multisites\..', false, true, array());
		foreach( $folders as $folder) {
			if (JFolder::delete($folder) !== true) {
				// JFolder::delete throws an error
				$rc = false;
			}
		}
		
		if ( $rc) {
		   echo JText::_( 'Purge successfull');
		}
		else {
		   echo JText::_( 'Some error occurs during the purge');
		}
		$this->addSubmenu(JRequest::getWord('task', 'about'));
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
		JToolBarHelper::title( JText::_( 'Multi Sites Content'), 'config.png' );

      if ( version_compare( JVERSION, '2.5') >= 0) { 
         // Options button.
         if (JFactory::getUser()->authorise('core.admin', 'com_multisitescontent')) {
         	JToolBarHelper::preferences('com_multisitescontent');
         }
      }
?>
<h3>Multi Sites Articles sharing</h3>
<p>Version <?php echo $this->_getVersion(); ?><br/>
<p>Share the Sections, categories and articles.<br/>
This is the Multi Sites version of Joomla menu type "Articles".<br/>
It can only be used to display sections, categories and articles.<br/>
It can <b>NOT</b> be used to edit or manage those sections, categories and articles.
</p>
<h3>Copyright</h3>
<p>Copyright 2008 Edwin2Win sprlu<br/>
Rue des robiniers, 107<br/>
B-7024 Ciply<br/>
Belgium
</p>
<p>All rights reserved.</p>
<a href="http://www.jms2win.com">www.jms2win.com</a>
<?php
   	$model =& $this->getModel( 'registration', 'Edwin2WinModel' );

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

		$this->addSubmenu(JRequest::getWord('task', 'about'));
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
		$this->setRedirect( 'index.php?option=' . $option . '&task=about', $msg);
		$this->addSubmenu(JRequest::getWord('task', 'about'));
	}


	// =====================================
	//             AJAX services
	// =====================================
   
   // -------------- ajaxGetCategoryList ------------------------------
   /**
    * @brief Return the combo box with the list of all categories present in the site ID.
    */
   function ajaxGetCategoryList()
   {
      // Connect to DB site_id
		$site_id = JRequest::getString( 'site_id', null);

      require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
      require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'multisitesfactory.php');
		$db      =& Jms2WinFactory::getMultisitesDBO( $site_id);
		// Set current site DBO as the default one and save the previous value
		$sav_db  =& MultisitesFactory::setDBO( $db);

		// Call Joomla standard Catalog element to get the list of categories
      jimport( 'joomla.html.parameters' );
		$params	= new JParameter('');
		$elt     = $params->loadElement( 'category');
		if ( empty( $elt) || $elt === false) {
   	   $replyStr   = 'ERROR: Unable to load the Category Element!';
		}
		else {
   		$control_name = JRequest::getString( 'control_name', null);
         if ( empty( $control_name)) {
            $control_name = 'urlparams';
         }
   		$name = JRequest::getString( 'name', null);
         if ( empty( $name)) {
            $name = 'id';
         }
         
         jimport( 'joomla.utilities.simplexml' );
         $fake       = new JSimpleXML();              // Force the class to load the JSimpleXMLElement class present in the same source.
         $node       = new JSimpleXMLElement( 'dummy');
         if ( version_compare( JVERSION, '1.6') >= 0) { $node->addAttribute( 'scope', 'com_content'); }
         else                                         { $node->addAttribute( 'scope', 'content'); }
         $value      = '';   // Default selected values
   	   $replyStr   = $elt->fetchElement($name, $value, $node, $control_name);

		   // If Joomla 2.5 show_all_categories is true
   		$show_all_categories = JRequest::getString( 'show_all_categories', null);
         if ( !empty( $show_all_categories) && ($show_all_categories=='true' || $show_all_categories=='1')) {
            $pos = strpos( $replyStr, '<option ');
            if ( $pos === false) {}
            else {
               $replyStr = substr( $replyStr, 0, $pos)
                         . '<option value="0">' . JText::_('JOPTION_ALL_CATEGORIES') . '</option>'
                         . substr( $replyStr, $pos)
                         ;
            }
         }

		   // If Joomla 1.6 show_root is true
   		$name = JRequest::getString( 'show_root', null);
         if ( !empty( $name) && $name=='true') {
            $pos = strpos( $replyStr, '<option ');
            if ( $pos === false) {}
            else {
               $replyStr = substr( $replyStr, 0, $pos)
                         . '<option value="0">' . JText::_('JGLOBAL_ROOT') . '</option>'
                         . substr( $replyStr, $pos)
                         ;
            }
         }
		}

	   // Restore the current DB
		MultisitesFactory::setDBO( $sav_db);

		jexit( $replyStr);
   }

   // -------------- ajaxGetSectionList ------------------------------
   /**
    * @brief Return the combo box with the list of all Section present in the site ID.
    */
   function ajaxGetSectionList()
   {
      // Connect to DB site_id
		$site_id = JRequest::getString( 'site_id', null);

      require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' 
                    .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
		$dbSite  =& Jms2WinFactory::getMultisitesDBO( $site_id);
		$db      =& JFactory::getDBO();
		$saveDB  = $db;
		$db      = $dbSite;

		// Call Joomla standard Catalog element to get the list of categories
      jimport( 'joomla.html.parameters' );
		$params	= new JParameter('');
		$elt     = $params->loadElement( 'section');
		if ( empty( $elt) || $elt === false) {
   	   $replyStr   = 'ERROR: Unable to load the Section Element!';
		}
		else {
   		$control_name = JRequest::getString( 'control_name', null);
         if ( empty( $control_name)) {
            $control_name = 'urlparams';
         }
   		$name = JRequest::getString( 'name', null);
         if ( empty( $name)) {
            $name = 'id';
         }
         
         jimport( 'joomla.utilities.simplexml' );
         $node       = new JSimpleXMLElement( 'dummy');
         $value      = '';   // Default selected values
   	   $replyStr   = $elt->fetchElement($name, $value, $node, $control_name);
		}

		
	   // Restore the current DB
	   $db = $saveDB;

		jexit( $replyStr);
   }

} // End class


// ===========================================================
//             ContentController_j16 class
// ===========================================================
/**
 * @brief Multi Sites Article Sharing controller for Joomla 1.6.
 */
class ContentController_j16 extends ContentController_j15
{
	/**
	 * @brief Joomla 1.6 display function.
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/content.php';

		// Load the submenu.
		ContentHelper::addSubmenu(JRequest::getCmd('view', 'articles'));

		$view		= JRequest::getCmd('view', 'articles');
		$layout 	= JRequest::getCmd('layout', 'articles');
		$id			= JRequest::getInt('id');

		// Check for edit form.
		if ($view == 'article' && $layout == 'edit' && !$this->checkEditId('com_content.edit.article', $id)) {
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_content&view=articles', false));

			return false;
		}

		parent::display();

		return $this;
	}

	// =====================================
	//             AJAX services 1.6
	// =====================================
   
   // -------------- ajaxGetCategoriesList 1.6 ------------------------------
   /**
    * @brief Return the combo box with the list of all categories present in the site ID.
    */
   function ajaxGetCategoriesList()
   {
      // Connect to DB site_id
		$site_id = JRequest::getString( 'site_id', null);

      require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
      require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'multisitesfactory.php');
		$db      =& Jms2WinFactory::getMultisitesDBO( $site_id);
		// Set current site DBO as the default one and save the previous value
		$sav_db  =& MultisitesFactory::setDBO( $db);

		// Call Joomla standard Catalog element to get the list of categories
      jimport( 'joomla.html.parameters' );
		$params	= new JParameter('');
		$elt     = $params->loadElement( 'category');
		if ( empty( $elt) || $elt === false) {
   	   $replyStr   = 'ERROR: Unable to load the Category Element!';
		}
		else {
   		$control_name = JRequest::getString( 'control_name', null);
         if ( empty( $control_name)) {
            $control_name = 'urlparams';
         }
   		$name = JRequest::getString( 'name', null);
         if ( empty( $name)) {
            $name = 'id';
         }
         
         jimport( 'joomla.utilities.simplexml' );
         $fake       = new JSimpleXML();              // Force the class to load the JSimpleXMLElement class present in the same source.
         $node       = new JSimpleXMLElement( 'dummy');
         if ( version_compare( JVERSION, '1.6') >= 0) { $node->addAttribute( 'scope', 'com_content'); }
         else                                         { $node->addAttribute( 'scope', 'content'); }
         $value      = '';   // Default selected values
   	   $replyStr   = $elt->fetchElement($name, $value, $node, $control_name);

		   // If Joomla 1.6 show_root is true
   		$name = JRequest::getString( 'show_root', null);
         if ( !empty( $name) && $name=='true') {
            $pos = strpos( $replyStr, '<option ');
            if ( $pos === false) {}
            else {
               $replyStr = substr( $replyStr, 0, $pos)
                         . '<option value="0">' . JText::_('JGLOBAL_ROOT') . '</option>'
                         . substr( $replyStr, $pos)
                         ;
            }
         }
		}

	   // Restore the current DB
		MultisitesFactory::setDBO( $sav_db);

		jexit( $replyStr);
   }

   // -------------- ajaxGetSectionList ------------------------------
   /**
    * @brief Return the combo box with the list of all Section present in the site ID.
    */
   function ajaxGetSectionList()
   {
	   $replyStr   = 'ERROR: Not available in joomla 1.6!';

		jexit( $replyStr);
   }


} // End class

// ===========================================================
//             ContentController class
// ===========================================================
/**
 * @brief Declare the appropriate controller depending on Joomla version.
 * In Joomla 1.6, the display function is declared public (PHP 5 syntax) with 2 arguments.
 * In Joomla 1.5, the display function use (PHP 4 syntax) with 1 arguments.
 */

if ( version_compare( JVERSION, '1.6') >= 0) { 
   eval( 'class ContentController extends ContentController_j16 { }') ;
}
else                                         { 
   eval( 'class ContentController extends ContentController_j15 { }') ;
}

<?php

if ( !defined( 'DS'))   { define('DS', DIRECTORY_SEPARATOR); }

// --- J2WinFactory ---
if ( !class_exists( 'J2WinFactory')) {
   class J2WinFactory {
      public static function & getApplication($id = null, $config = array(), $prefix = 'J') {
         static $instance;
   		if (!is_object($instance)) {
            // Joomla 2.5, return a copy
            if ( version_compare( JVERSION, '1.6') >= 0) { $instance = JFactory::getApplication($id, $config, $prefix); }
            // Joomla 1.5, return a reference
            else                                         { $instance = &JFactory::getApplication($id, $config, $prefix); }
         }
   		return $instance;
      }
   	public static function &getLanguage() {
   		static $instance;
   
   		if (!is_object($instance)) {
            // Joomla 2.5, return a copy
            if ( version_compare( JVERSION, '1.6') >= 0) { $instance = JFactory::getLanguage(); }
            // Joomla 1.5, return a reference
            else                                         { $instance = &JFactory::getLanguage(); }
   		}
   		return $instance;
      }
   } // End class
}

// --- J2WinController ---
if ( !class_exists( 'J2WinController')) {
   // J3.0
   if ( file_exists( JPATH_LIBRARIES.'/legacy/controller/legacy.php'))  { jimport('legacy.controller.legacy'); }
   if ( class_exists( 'JControllerLegacy'))                             { eval( 'class J2WinController extends JControllerLegacy {};') ; }
   else {
      // J1.5 -> J2.5
      jimport('joomla.application.component.controller');
      if ( class_exists( 'JController'))           { eval( 'class J2WinController extends JController{};') ; }
   }
}


// --- J2WinModel ---
if ( !class_exists( 'J2WinModel')) {
   // J3.0
   if ( file_exists( JPATH_LIBRARIES.'/legacy/model/legacy.php')) { jimport('legacy.model.legacy'); }
   if ( class_exists( 'JModelLegacy'))                            { eval( 'class J2WinModel extends JModelLegacy {};') ; }
   else {
      // J1.5 -> J2.5
      jimport('joomla.application.component.model');
      if ( class_exists( 'JModel'))                { eval( 'class J2WinModel extends JModel{};') ; }
   }
}

// --- J2WinView ---
if ( !class_exists( 'J2WinView')) {
   // J3.0
   if ( file_exists( JPATH_LIBRARIES.'/legacy/view/legacy.php'))  { jimport('legacy.view.legacy'); }
   if ( class_exists( 'JViewLegacy'))                             { eval( 'class J2WinView extends JViewLegacy {};') ; }
   else {
      // J1.5 -> J2.5
      jimport('joomla.application.component.view');
      if ( class_exists( 'JView'))                 { eval( 'class J2WinView extends JView{};') ; }
   }
}

// --- J2WinUtility  ---
if ( !class_exists( 'J2WinUtility')) {
   jimport('joomla.utilities.utility');
   class J2WinUtility extends JUtility {
   	public static function getToken2Win( $forceNew = false) {
   	   // J1.5 -> 2.5
   	   if ( method_exists( 'JUtility', 'getToken'))          { return JUtility::getToken( $forceNew); }
   	   // J3.0
   	   else if ( method_exists( 'JSession', 'getFormToken')) { return JSession::getFormToken( $forceNew); }
   	   return 0;
   	}
   	public static function isOSWindows() {
   	   return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
   	}
   }  // End class
}

// --- J2WinToolBarHelper ---
if ( !class_exists( 'J2WinToolBarHelper')) {
   require_once( JPATH_ROOT.'/administrator/includes/toolbar.php');
   // J1.5 -> 2.5
   if ( method_exists( 'JToolBarHelper', 'customX')) { 
      class J2WinToolBarHelper extends JToolBarHelper {}
   }
   // J3.0
   else {
      class J2WinToolBarHelper extends JToolBarHelper {
      	public static function customX($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
      	{
      		self::custom($task, $icon, $iconOver, $alt, $listSelect);
      	}
      	public static function addNewX($task = 'add', $alt = 'JTOOLBAR_NEW')
      	{
      		self::addNew($task, $alt);
      	}
      	public static function editListX($task = 'edit', $alt = 'JTOOLBAR_EDIT')
      	{
      		self::editList($task, $alt);
      	}
      }
   }
}


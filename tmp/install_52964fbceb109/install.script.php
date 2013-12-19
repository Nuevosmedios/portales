<?php
/**
 * @file       install.script.php
 * @version    1.1.1
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms  Multi Sites
 *             Single Joomla! 1.5.x AND 1.6.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2011 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.0 23-MAY-2011: Initial version.
 * - V1.1.1 09-JUN-2011: Fix the detection of the JMS version on Joomla 1.6 when JMS has a version >= 1.2.54
 */


// Dont allow direct linking
defined( '_JEXEC' ) or die();

jimport('joomla.filesystem.folder');

// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) {
   // Update the source directory to use THIS root installation directory
   $this->parent->setPath( 'source', dirname( __FILE__));

   // Convert Joomla 1.5 language files into Joomla 1.6 syntax
   include_once( dirname( __FILE__) .DS. 'install.language_j16.php');
}
else {
   // Dummy class
   class MultisitesConvertLanguage {};
}

// ------- Compute Manifest File Name -----
// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) {
   if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {
      if ( is_file( __FILE__.DS.'extension.xml')) {
         define( 'MULTISITES_MANIFEST_FILENAME', 'extension.xml');
      }
      else {
         define( 'MULTISITES_MANIFEST_FILENAME', substr( basename( dirname( __FILE__)), 4).'.xml');
      }
   }
}
// If Joomla 1.5
else {
   if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {
      define( 'MULTISITES_MANIFEST_FILENAME', 'install.xml');
   }
}

// ------- Save version for registration -----
// Save the version number of the extension to use it later during the registration
// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) { $myManifestVersion =& $this->manifest->version; 
                                               $GLOBALS['installManifest'] = &$this->manifest;
                                             }
// If Joomla 1.5
else                                         { $myManifestVersion =& $this->manifest->getElementByPath('version'); }
$GLOBALS['installManifestVersion'] = JFilterInput::clean($myManifestVersion->data(), 'cmd');


// ===========================================================
//             Com_MultisitesContentInstallerScript class
// ===========================================================
class Com_MultisitesContentInstallerScript extends MultisitesConvertLanguage {

   //------------ preflight ---------------
	function preflight($type, $parent)  { return true;	}


   //------------ install ---------------
   /**
    * @brief Check that Jms Multisites is installed and suggest to register the extension when not already registered.
    * @remarks Is called by Joomla 1.5 and 1.6.
    */
	function install($parent) {
		$mainframe =& JFactory::getApplication();

      // Check if JMS 1.2.x is installed.
      jimport( 'joomla.application.helper');
      $version = null;
      $jmsfolder = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'; 
      if ( !JFolder::exists( $jmsfolder)){
         $msg = JText::_( 'Please install the Joomla Multi Sites extension version 1.2.49 or higher before this one');
   		$mainframe->enqueueMessage( $msg, 'error');
   	   return false;
      }

      // Compute the JMS manifest file name
      if ( version_compare( JVERSION, '1.6') >= 0) {
         // If JMS 1.2.54 or higher
         $filename = $jmsfolder.DS. 'multisites.xml';
         if ( is_file( $filename)) {}
         else {
            $filename = $jmsfolder.DS. 'extension.xml';
         }
      }
      // If Joomla 1.5
      else {
         $filename = $jmsfolder.DS. 'install.xml';
      }
      
      // If the manifest file is not present
      if ( !is_file( $filename)) {
         $msg = JText::_( 'Jms Multi Sites manifest file does not exists');
   		$mainframe->enqueueMessage( $msg, 'error');
   	   return false;
      }

   	if ($data = JApplicationHelper::parseXMLInstallFile($filename)) {
   	   // If the version is present
   	   if (isset($data['version']) && !empty($data['version'])) {
   	      $version = trim( $data['version']);
   	   }
   	   else {
            $msg = JText::_( 'Unable to retreive the version of Joomla Multi Sites');
      		$mainframe->enqueueMessage( $msg, 'error');
   	      return false;
   	   }
   	}
   	else {
         $msg = JText::_( 'Unable to read the Joomla Multi Sites manifest file');
   		$mainframe->enqueueMessage( $msg, 'error');
   	   return false;
   	}
   
   	// If there is no version number
   	if ( empty( $version)) {
         $msg = JText::_( 'Joomla Multi Sites version is empty');
   		$mainframe->enqueueMessage( $msg, 'error');
   	   return false;
   	}
   	$vers = explode( '.', $version);
   	// If JMS version >= 1.2.49
   	if ( ($vers[0] == 1 && $vers[1] == 2 && $vers[2] >= 49)
   	  || ($vers[0] == 1 && $vers[1]  > 2)
   	  ||  $vers[0]  > 1)
   	{
   	   // OK
   	}
   	else {
   	   $msg = JText::_( 'Invalid Joomla Multi Sites version - Require JMS 1.2.49 or higher');
   		$mainframe->enqueueMessage( $msg, 'error');
   	   return false;
   	}
   
      
      // Retreive the component name
      // If Joomla 1.6, 
      if ( version_compare( JVERSION, '1.6') >= 0) {
         $name = $parent->get( 'element');

         // convert Joomla 1.5.x language INI file to replace all the special characters with their html equivalent value
      	MultisitesConvertLanguage::files();
      }
      // Joomla 1.5
      else {
         $name = basename( dirname( __FILE__));
      }
   
      $path = JPATH_ADMINISTRATOR.DS.'components'.DS.$name;
      require_once( $path.DS.'models'.DS.'registration.php' );
      require_once( $path.DS.'views'.DS.'registration'.DS.'view.php' );
   
      // Load the language file of this component.
   	$lang =& JFactory::getLanguage();
   	$lang->load( $name);
      
      // Check if this component is registered
      $model = new Edwin2WinModelRegistration();
      if ( !$model->isRegistered()) {
         $view = new Edwin2WinViewRegistration( array('base_path' => $path) );
      	$view->setModel( $model, true );
      	$redirect_url = JURI::base()."index.php?option=$name&task=registered";
      	$view->registrationButton( $redirect_url);
      }


      // If Joomla 1.6, 
      if ( version_compare( JVERSION, '1.6') >= 0) {
         // Cleanup the asset table in case where the extension was already installed
         // This will allow creating a new entry
   		$table	= JTable::getInstance('Asset');
   		$table->load(array( 'parent_id' => 1, 'name'=>$name));
   		if ($table->id)   { $success = $table->delete(); }
   	}

      return true;
	}

   //------------ update ---------------
	function update($parent)      { self::install($parent);	}     // use PHP 4.3 syntax compatible

   //------------ uninstall ---------------
	function uninstall($parent)   { return true; }

   //------------ postflight ---------------
   /**
    * @brief In Joomla 1.6, copy the manifest with the name of the extension
    *        to be able processed by the "Discovered" function
    */
	function postflight($type, $parent) {
	   // In case of Joomla 1.6, duplicate the manifest file with the name of the extension to be used by the discovered function
      if ( version_compare( JVERSION, '1.6') >= 0) {
         if ( is_file( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml'))
         {
   			jimport( 'joomla.filesystem.file' );
   			JFile::copy( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml',
   			             JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.$this->get( 'name').'.xml'
   			            );
         }
      }
      
      return true;
	}
}
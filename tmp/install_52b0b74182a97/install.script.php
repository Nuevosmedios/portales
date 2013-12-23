<?php
/**
 * @file       install.script.php
 * @version    1.1.0 (MultisitesContent)
 *             1.2.65 (Jms Multisites)
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
 * - V1.1.0  23-MAY-2011: Initial version.
 * - V1.2.53 02-JUN-2011: Fix bug in postflight when a old "extension.xml" is present.
 * - V1.2.54 02-JUN-2011: Compute the new Joomla 1.6 manifest file name.
 *                        On Joomla 1.6, remove the deprecated "extension.xml" manifest file.
 * - V1.2.55 16-JUN-2011: Fix warning message on the manifest file name detection.
 * - V1.2.65 22-SEP-2010: Add Joomla menu cleanup in case where joomla create ghost menu in case of error during the install.
 *                        So here, we implemented a cleanup of Joomla during the uninstall.
 */


// Dont allow direct linking
defined( '_JEXEC' ) or die();

if ( !defined( 'JPATH_MULTISITES')) {
   define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}


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
      // If deprecated file is present
      if ( is_file( dirname(__FILE__).DS.'extension.xml')) {
         define( 'MULTISITES_MANIFEST_FILENAME', 'extension.xml');
      }
      // If joomla 1.6 manifest file using the extension name is present
      else if ( !empty( $this->name) && is_file( dirname(__FILE__).DS.$this->name.'.xml') ) {
         define( 'MULTISITES_MANIFEST_FILENAME', $this->name.'.xml');
      }
      // Otherwise, use the directory name
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
//             Com_MultisitesInstallerScript class
// ===========================================================
class Com_MultisitesInstallerScript extends MultisitesConvertLanguage {

   //------------ preflight ---------------
	function preflight($type, $parent)
	{
      if ( version_compare( JVERSION, '1.6') >= 0) {
   		// Remove the deprecated extension.xml
         if ( is_file( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml'))
         {
   			jimport( 'joomla.filesystem.file' );
            // remove the "extension.xml" to keep the manifest file that has the name of the extension <name>
            JFile::delete( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml');
         }
      }
	   return true;
	}


   //------------ install ---------------
   /**
    * @brief Backup the current Joomla core files that could be patched by MultiSites components.
    *
    * Called by the Component Installer, this function is used to backup all files that could be patched
    * by the Joomla! MultiSites component.\n
    * This backup will be used by Uninstall script to restore the Joomla core files.
    * @remarks Is called by Joomla 1.5 and 1.6.
    */
	function install($parent)
	{
		$mainframe =& JFactory::getApplication();
      $backdir = 'backup_on_install';
   
      // Increase the maximum time limit of 60 second (just in case where the upload took too much time)
      @set_time_limit( 60);
         
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
      require_once( $path.DS.'controller.php' );
      require_once( $path.DS.'models'.DS.'registration.php' );
      require_once( $path.DS.'views'.DS.'registration'.DS.'view.php' );
      
      // Load the language file of this component.
   	$lang =& JFactory::getLanguage();
   	$lang->load( $name);
      

      // If a configuration file are exists. In this case, do nothing.
      // Otherwise create a "multisites.cfg.php" configuration based on the "distibution" configuration.
      jimport('joomla.filesystem.file');
      if ( !JFile::exists( $path.DS.'multisites.cfg.php')
        &&  JFile::exists( $path.DS.'multisites.cfg-dist.php')
         )
      {
         JFile::copy( $path.DS.'multisites.cfg-dist.php',
                      $path.DS.'multisites.cfg.php'
                    );
                      
      }
      
      include_once( $path.DS.'multisites.cfg.php' );


      // Backup the core joomla files.
      require_once( $path.DS.'models'.DS.'patches.php' );
      $patches = new MultisitesModelPatches();
      $backlist = $patches->backup( $backdir);
      if ( $backlist === false) {
         $msg = $patches->getError();
         echo JText::sprintf( 'INSTALL_BACKUP_ERROR', $msg);
         $backup_rc = false;
      }
      else {
         $backup_rc = true;
      }
      
      // If the backup theorically succeed,
      if ( $backup_rc) {
         // Verify the backup to ensure there is no missing files.
         $missingFiles = $patches->checkBackup( $backdir);
         if ( count($missingFiles) > 0) {
            $msg = '';
            foreach($missingFiles as $missingFile) {
               $msg .= "- $missingFile<br/>";
            }
            echo JText::sprintf( 'INSTALL_CHECKBACKUP_ERROR', $msg);
            return false;
         }
      }
      
      // Create the root Multisites directory where all the 'slave' site configuration will be stored.
      JFolder::create( JPATH_MULTISITES);
      if ( !JFolder::exists( JPATH_MULTISITES)) {
      	$msg = JPATH_MULTISITES;
         echo JText::sprintf( 'INSTALL_MULTISITE_DIR_ERROR', $msg);
      }
   
      
      // Create an index.html file to hide the list of directories present in the /multisites directory
      JFile::copy( $path.DS.'index.html', JPATH_MULTISITES .DS. 'index.html');
      
      // Finally report success installation
      $fullbackdir = $path.DS.$backdir;
      echo JText::sprintf('INSTALL_BACKUP_SUCCESS', $fullbackdir);
   
      // remove older patches definition when present
      $cleanupPatches = $patches->cleanupPatches();
      if ( !empty( $cleanupPatches)) {
         echo JText::sprintf('INSTALL_CLEANUP_PATCHES', implode( '</li><li>', $cleanupPatches));
      }
   
   
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
	function uninstall($parent) {
      // If Joomla 1.6
      if ( version_compare( JVERSION, '1.6') >= 0) {
   	   // Check if there is "ghost" extension present.
   	   // In the case where there is a ghost menu (component_id=0) then remove the "ghost" extension to fix a bug in joomla
   		$db		= JFactory::getDBO();
   		$query   = 'SELECT id from #__menu WHERE title=' . $db->Quote( $parent->get( 'element')) . ' AND component_id=0';
   		$db->setQuery( $query );
   		$menu_id = $db->loadResult();
   		if ( !empty( $menu_id)) {
   		   // When there is a ghost
   		   // -- Remove the children --
      		$query   = 'DELETE from #__menu WHERE parent_id=' . $menu_id;
      		$db->setQuery( $query );
      		$db->query();
   		   // -- Remove the parent --
      		$query   = 'DELETE from #__menu WHERE id=' . $menu_id;
      		$db->setQuery( $query );
      		$db->query();
   		}
      }
	   return true;
	}

   //------------ postflight ---------------
   /**
    * @brief In Joomla 1.6, copy the manifest with the name of the extension
    *        to be able processed by the "Discovered" function
    */
	function postflight($type, $parent) {
	   // In case of Joomla 1.6, duplicate the manifest file with the name of the extension to be used by the discovered function
      if ( version_compare( JVERSION, '1.6') >= 0) {
			jimport( 'joomla.filesystem.file' );
         // If both "extension.xml" and the <name>.xml file exist,
         if ( is_file( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml')
           && is_file( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.$parent->get( 'name').'.xml')
            )
         {
            // remove the "extension.xml" to keep the manifest file that has the name of the extension <name>
            JFile::delete( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml');
         }
         // If the "extension.xml" exists and not the one with the <name> of the extension,
         else if ( is_file( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml')
                && !is_file( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.$parent->get( 'name').'.xml')
                 )
         {
            //  rename the "extension.xml" into the <name>.xml
   			JFile::move( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml',
   			             JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.$parent->get( 'name').'.xml'
   			            );
         }
         // Otherwise, this mean that the manifest file is already called <name>.xml where <name> is the name of the extension
      }
      
      return true;
	}
}
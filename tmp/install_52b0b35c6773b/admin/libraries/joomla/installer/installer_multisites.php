<?php
/**
 * @file       installer_multisites.php
 * @version    1.2.95
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
 * - V1.1.0 11-OCT-2008: Add to install template when it has its own directory
 *          14-NOV-2008: Bug fix in computation of the extention path (case of joom!fish that must become joomfish)
 * - V1.1.5 16-DEC-2008: Fix bug in install specific template.
 *                       Use a special template installer to specify the "root" path of the templates folder.
 *                       A templates folder must be terminated by the word "templates".
 *                       So JMS will ignore the last word of the path when installing the templates.
 *                       If in JMS you give {site_dir}/toto this will install {site_dir}/templates.
 * - V1.1.19 02-APR-2009: Fix bug when there are several master manifest files with similar names.
 *                      In this case, it could happen that JMS select the wrong manifest file.
 *                      Add a check on the extension name to ensure identify the correct manifest file.
 * - V1.2.14 05-DEC-2009: Add Joomla 1.6 alpha 2 compatibility.
 * - V1.2.15 19-DEC-2009: Add PHP 4.x compatibility.
 * - V1.2.17 30-DEC-2009: Remove a warning message reported by PHP 5.0 in case where manfiest is not found.
 * - V1.2.47 01-FEB-2011: Add the Joomla 1.6 "uninstall" adapters to avoid removing files.
 * - V1.2.58 16-JUL-2011: Add the possibility to process some extension name extensions to allow re-installing them
 *                        from a slave site. This help proceeding with the upgrade of these extensions.
 *                        ie. "Community" match "JomSocial"
 * - V1.2.63 27-AUG-2011: Fix the detection of the Joomla version in case where this source is called before
 *                        any Joomla initialization
 * - V1.2.65 23-SEP-2011: Remove a warning message that could appear during the re-install of an extension into a slave site.
 * - V1.2.73 28-JAN-2012: Add the possibility to ignore the manifest version to allow upgrade of slave sites before the master.
 * - V1.2.77 19-FEB-2012: Update the "version.php" file path for compatibility with Joomla 2.5.
 * - V1.2.78 19-FEB-2012: Fix the extension name recognition in Joomla 1.7 or higher to avoid adding the "com_" 
 *                        in the front of an extension name when it is already present.
 *                        This avoid for example to have "com_com_kunena" instead of "com_kunena"
 * - V1.2.84 05-APR-2012: Fix the module extension name recognition in Joomla 2.5 or higher
 * - V1.2.95 13-SEP-2012: Fix the possibility to ignore the version checking during the installation.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.installer.installer');

@include_once( dirname( dirname( dirname( dirname( __FILE__)))).DS.'multisites.cfg.php' );

// ===========================================================
//             JInstallerMultisites class
// ===========================================================
/**
 * @brief This class is a wrapper to the standard Joomla Installer.
 *
 * It replaces the Joomla Installer instance.
 *
 * When the administrator Install an Extension, the install() function is called.\n
 * This check if the intallation of the extension has EXACTLY the same version number than the one
 * previously installed on the 'Master' site.
 *
 * When the administrator wants to Uninstall an extension, the uninstall() function is called.\n
 * This implementation totaly replace the Joomla uninstall function.\n
 * In fact, this is a copy of the Joomla uninstall function where all the deletion of the files and folders
 * are removed.
 * In addition, the "<uninstallfile>" statement present in the manifest file is ignored.
 * Therefore, the uninstall extension specific function is not called (to avoid deletion of the files or folder)
 * that could be present in this function.
 * A side effect could be an incompleted cleanup of the database when specific implementation is in charged of this cleanup.
 */
class JInstallerMultisites_j15 extends JInstaller
{
   //------------ getInstance ---------------
   /**
    * @brief Create a Multi Sites instance.
    */
	function &_getInstance()
	{
	   // Call the standard Joomla Installer instance.
	   $instance = & parent::getInstance();
	   // And replace the standard Joomla Installer instance by the Multi Site one.
		$instance = new JInstallerMultisites();

		return $instance;
	}


   //------------ getSlaveManifest ---------------
   /**
    * @brief Get the manifest of the extension that is currently installing.
    *
    * As this class is only created for a 'Slave' site, this will retreive the manifest
    * present in the package, folder, url.
    */
   function &getSlaveManifest( $path=null)
   {
      $result = false;
		if ($path && JFolder::exists($path)) {
			$this->setPath('source', $path);
		} else {
			$this->abort(JText::_('Install path does not exist'));
			return $result;
		}
		
      if ( !$this->setupInstall()) {
			$this->abort(JText::_('Unable to load the manifest XML file'));
			return $result;
      }

		return $this->getManifest();
   }
   
   //------------ _getRoot ---------------
   /**
    * Get the XML Root Document Element
    */
   function &_getRoot( &$slave_manifest)
   {
		if ( is_a( $slave_manifest, 'JSimpleXML') && !empty( $slave_manifest->document)) {
			$root		   =& $slave_manifest->document;
		}
		else if ( is_a( $slave_manifest, 'JXMLElement') ) {
			$root		   =& $slave_manifest;
		}
		else {
			$root		   =& $slave_manifest;
		}
		return $root;
   }

   //------------ _cnvNameExceptions ---------------
   /**
    * @brief Some extensions like JomSocial have a public and internal name.
    * @return Return the "install" package name based on the manifest name
    */
	function _cnvDirNameExceptions( $dir)
   {
	   $fname = dirname( dirname( dirname( dirname( __FILE__)))).DS.'patches'.DS.'patch_exception.php';
	   @include( $fname);
	   if ( isset( $dir_exceptions)) {
	      $key = strtolower( str_replace( DS, '/', $dir));
	      if ( !empty( $dir_exceptions[ $key])) {
	         return str_replace( '/', DS, $dir_exceptions[ $key]);
	      }
	   }
	   return $dir;
   }
   
   //------------  getExtName15 ---------------
   /**
    * @brief Creates the relative path corresponding to the installation type and extension name.
    * @param slave_manifest  The 'slave' manifest XML file that must be used to determine the extension name and directory.
    * @return
    * - Generally return a string with the extension name;
    * - In case of plugins, this return an array with the directory path and the manifest file name (without extension XML).
    */
	function getExtName15( $slave_manifest)
   {
		$root		   =& $slave_manifest->document;
		
		$inst_type	= $root->attributes('type');

		$elt = $root->getElementByPath('name');
		if ( $elt === false)                            return false;
		$name = JFilterInput::clean( $elt->data(), 'cmd');
		
	   switch( $inst_type) {
	      case 'language':
	      case 'languages':
      	   $ext_name = 'language'.DS. strtolower( str_replace(" ", "", $name));
      	   break;
	      case 'module':
	      case 'modules':
      		// Retreive the module name.
      		// This is the first line <file module="modul name" >
      		$element =& $root->getElementByPath('files');
      		if (is_a($element, 'JSimpleXMLElement') && count($element->children())) {
      			$files =& $element->children();
      			foreach ($files as $file) {
      				if ($file->attributes('module')) {
      					$mname = $file->attributes('module');
      					break;
      				}
      			}
      		}
      		if ( !empty( $mname)) {
         	   $ext_name = 'modules' .DS. $mname;
      		}
      		else {
      		   $ext_name = null;
      		}

      	   break;
	      case 'plugin':
	      case 'plugins':
      		// Retreive the plugin name
      		// The path is plugins/<group>
      		// The manifest name is retreived from <file plugins='manifest name'>
      		$element =& $root->getElementByPath('files');
      		if (is_a($element, 'JSimpleXMLElement') && count($element->children())) {
      			$files =& $element->children();
      			foreach ($files as $file) {
      				if ($file->attributes( 'plugin')) {
      					$pname = $file->attributes( 'plugin');
      					break;
      				}
      			}
      		}
      		$group = $root->attributes('group');
      		if (!empty ($pname) && !empty($group)) {
      		   $ext_name = array( 'dir'      => 'plugins'.DS.$group,
      		                      'manifest' => $pname);
      		} else {
      		   $ext_name = null;
      		}
      	   break;
	      case 'xmap_ext':
      		// Retreive the xmpa_ext name
      		// The manifest name is retreived from <file xmpa_ext='manifest name'>
      		$element =& $root->getElementByPath('files');
      		if (is_a($element, 'JSimpleXMLElement') && count($element->children())) {
      			$files =& $element->children();
      			foreach ($files as $file) {
      				if ($file->attributes( 'xmap_ext')) {
      					$pname = $file->attributes( 'xmap_ext');
      					break;
      				}
      			}
      		}
      		if (!empty ($pname)) {
      		   $ext_name = array( 'dir'      => 'administrator'.DS.'components'.DS.'com_xmap'.DS.'extensions',
      		                      'manifest' => $pname);
      		} else {
      		   $ext_name = null;
      		}
      	   break;
	      case 'template':
	      case 'templates':
      	   $ext_name = 'templates'.DS. strtolower( str_replace(" ", "", $name));
      	   break;
	      case 'component':
	      case 'components':
	      default:
      	   $ext_name = 'components' .DS. 'com_' . strtolower( str_replace(" ", "", $name));
      	   break;
	   }
	   
	   $ext_name = $this->_cnvDirNameExceptions( $ext_name);
	   
	   return $ext_name;
	}

   //------------  getExtName16 ---------------
   /**
    * @brief Creates the relative path corresponding to the installation type and extension name.
    * @param slave_manifest  The 'slave' manifest XML file that must be used to determine the extension name and directory.
    * @return
    * - Generally return a string with the extension name;
    * - In case of plugins, this return an array with the directory path and the manifest file name (without extension XML).
    */
	function getExtName16( $slave_manifest)
	{
		$root		=& $this->_getRoot( $slave_manifest);
		
		$inst_type	= $this->xmlGetAttribue( $root, 'type');

		$str = $this->xmlGetElementByPath( $root,'name');
		if ( $str === false)                            return false;
		$name = JFilterInput::clean( $str, 'cmd');
		
	   switch( $inst_type) {
	      case 'language':
	      case 'languages':
      	   $ext_name = 'language'.DS. strtolower( str_replace(" ", "", $name));
      	   break;
	      case 'module':
	      case 'modules':
      		// Retreive the module name.
      		// This is the first line <file module="modul name" >
      		if ( isset( $root->files)) { $element =& $root->files; }
      		else                       { $element = null; }
      		if (is_a($element, 'JXMLElement') && count($element->children())) {
      			$files =& $element->children();
      			foreach ($files as $file) {
      				$attr = $file->getAttribute( 'module');
      				if ( !empty( $attr)) {
      					$mname = $attr;
      					break;
      				}
      			}
      		}
      		if ( !empty( $mname)) {
         	   $ext_name = 'modules' .DS. $mname;
      		}
      		else {
      		   $ext_name = null;
      		}

      	   break;
	      case 'plugin':
	      case 'plugins':
      		// Retreive the plugin name
      		// The path is plugins/<group>
      		// The manifest name is retreived from <file plugins='manifest name'>
      		if ( isset( $root->files)) { $element =& $root->files; }
      		else                       { $element = null; }
      		if (is_a($element, 'JXMLElement') && count($element->children())) {
      			$files =& $element->children();
      			foreach ($files as $file) {
      				$attr = $file->getAttribute( 'plugin');
      				if ( !empty( $attr)) {
      					$pname = $attr;
      					break;
      				}
      			}
      		}
      		$group = $root->getAttribute( 'group');
      		if (!empty ($pname) && !empty($group)) {
      		   $ext_name = array( 'dir'      => 'plugins'.DS.$group.DS.$pname,
      		                      'manifest' => $pname);
      		} else {
      		   $ext_name = null;
      		}
      	   break;
	      case 'xmap_ext':
      		// Retreive the xmpa_ext name
      		// The manifest name is retreived from <file xmpa_ext='manifest name'>
      		if ( isset( $root->files)) { $element =& $root->files; }
      		else                       { $element = null; }
      		if (is_a($element, 'JXMLElement') && count($element->children())) {
      			$files =& $element->children();
      			foreach ($files as $file) {
      				$attr = $file->getAttribute( 'xmap_ext');
      				if ( !empty( $attr)) {
      					$pname = $attr;
      					break;
      				}
      			}
      		}
      		if (!empty ($pname)) {
      		   $ext_name = array( 'dir'      => 'administrator'.DS.'components'.DS.'com_xmap'.DS.'extensions',
      		                      'manifest' => $pname);
      		} else {
      		   $ext_name = null;
      		}
      	   break;
	      case 'template':
	      case 'templates':
      	   $ext_name = 'templates'.DS. strtolower( str_replace(" ", "", $name));
      	   break;
	      case 'component':
	      case 'components':
	      default:
      	   $ext_name = strtolower( str_replace(" ", "", $name));
      	   // In case where the extension name already contain "com_",
      	   if ( substr( $ext_name, 0, 4) == 'com_')  {}                                     // Do not add it twice
      	   else                                      { $ext_name = 'com_' . $ext_name; }    // Otherwise, add com_ in front of the name
      	   $ext_name = 'components' .DS. $ext_name;
      	   break;
	   }
	   
	   $ext_name = $this->_cnvDirNameExceptions( $ext_name);
	   
	   return $ext_name;
	}

   //------------  getExtName ---------------
	function getExtName( $slave_manifest)
	{
		if ( version_compare( JVERSION, '1.6') >= 0) {
		   return $this->getExtName16( $slave_manifest);
		}
	   return $this->getExtName15( $slave_manifest);
	}


   //------------ loadMasterManifest ---------------
   /**
    * Load the corresponding master manifest having the same type and name than the slave one.
    */
   function loadMasterManifest( $ext_name)
   {
		jimport('joomla.filesystem.folder');
		
		// If this is an array, this means that this is a plugins
		if ( is_array( $ext_name)) {
		   $adminDir =
   		$siteDir  = JPATH_SITE .DS. $ext_name['dir'];
   		$pattern  = $ext_name['manifest'] . '.xml$';
   		$slave_ext_name   = $ext_name['manifest'];
		}
		else {
   		$adminDir = JPATH_ADMINISTRATOR .DS. $ext_name;
   		$siteDir  = JPATH_SITE .DS. $ext_name;
   		$pattern  = '.xml$';
   		$slave_ext_name   = $ext_name;
		}
      
		 /* Get the component folder and list of xml files in folder */
		$folder = $adminDir;
		if (JFolder::exists($folder)) {
			$xmlFilesInDir = JFolder::files($folder, $pattern);
		} else {
			$folder = $siteDir;
			if (JFolder::exists($folder)) {
				$xmlFilesInDir = JFolder::files($folder, $pattern);
			} else {
				$xmlFilesInDir = null;
			}
		}

		if (count($xmlFilesInDir))
		{
			foreach ($xmlFilesInDir as $xmlfile)
			{
				if ($data = JApplicationHelper::parseXMLInstallFile($folder.DS.$xmlfile)) {
				   // If there is only one occurence, use this one
				   if ( count($xmlFilesInDir) <= 1) {
   				   break;
				   }
				   else {
            		$master_xml = & JFactory::getXMLParser('Simple');
            		if ($master_xml->loadFile($folder.DS.$xmlfile)) {
                     $master_ext_name = $this->getExtName( $master_xml);
               		if ( is_array( $master_ext_name)) {
               		   if ( $master_ext_name['manifest'] == $slave_ext_name) {
               		      break;
               		   }
               		}
               		else {
               		   if ( $master_ext_name == $slave_ext_name) {
               		      break;
               		   }
               		}
            		}
            		// Error
            		unset( $data);
				   }
				}
			}
		}
		
		// If the corresponding manifest information is NOT found
		if ( !isset( $data)) {
		   // Error
		   return false;
		}
		
		return $data;
   }

   //------------ xmlGetAttribue ---------------
   function xmlGetAttribue( $xmlelement, $attribute)
   {
		if ( method_exists( $xmlelement, 'getAttribute')) {
			return $xmlelement->getAttribute( $attribute);
		}
		else if ( is_a( $xmlelement, 'JSimpleXML') && !empty( $xmlelement->document)) {
			return $this->xmlGetAttribue($xmlelement->document, $attribute);
		}
		return $xmlelement->attributes( $attribute);
   }

   //------------ xmlGetElementByPath ---------------
   function xmlGetElementByPath( $root, $path)
   {
   	$elt = false;
   	if ( method_exists( $root, 'getElementByPath')) {
			// Joomla 1.5
			$elt = $root->getElementByPath( $path);
   	}
   	// Joomla 1.6
		else if ( isset( $root->$path)) {
			$elt = $root->$path;
		}
		if ( $elt === false) {
			return false;
		}
		return $elt->data();
   }
   
   
   //------------ compareWithMaster ---------------
   /**
    * Use the slave manifest to retreive the version number of the extension in the MASTER site installation.
    */
   function compareWithMaster( $slave_manifest)
   {
		$mainframe	= &JFactory::getApplication();
		
      // Retreive extension type, name and version
		$root		=& $this->_getRoot( $slave_manifest);
		
		$slave_name = $this->xmlGetElementByPath($root, 'name');
		if ( $slave_name === false)		{ return false; }

		$slave_version = $this->xmlGetElementByPath( $root, 'version');
		if ( $slave_version === false)	{ return false; }

      $ext_name = $this->getExtName( $slave_manifest);
      if ( empty( $ext_name))                         return false;
		
		$data = $this->loadMasterManifest( $ext_name);
		if ( $data ===  false)
		{
		   // In case of "package" that contain component, modules zip file, of course the package can not be found in the master.
		   if ( defined( 'MULTISITES_IGNORE_MANIFEST_VERSION') && MULTISITES_IGNORE_MANIFEST_VERSION) {
         	return true;
         }
		   // This extension is not found in the master site.
		   // Please install it first in the master site before installing it into the slave site
   		$mainframe->enqueueMessage( JText::_('MSJINSTALL_EXT_NOTFOUND'));
   		return false;
		}
		
		// Compare the name and version
	   $rc = true;
		if ( $slave_name != $data['name']) {
		   $fname = dirname( dirname( dirname( dirname( __FILE__)))).DS.'patches'.DS.'patch_exception.php';
		   @include( $fname);
		   if ( isset( $extname_exceptions) && strtolower( $extname_exceptions[ strtolower( $slave_name)]) == strtolower( $data['name'])) {}
		   else {
      		$mainframe->enqueueMessage( JText::sprintf( 'MSJINSTALL_EXT_DIF_NAME', $slave_name, $data['name']));
   		   $rc = false;
   		}
		}
		if ( $slave_version != $data['version']) {
		   // If the version number can be ignored
		   if ( defined( 'MULTISITES_IGNORE_MANIFEST_VERSION') && MULTISITES_IGNORE_MANIFEST_VERSION) {
      		$mainframe->enqueueMessage( JText::sprintf( 'MSJINSTALL_EXT_DIF_VERSION_IGNORED', $slave_version, $data['version']));
      		
      		$slave_manifest_path = $this->getPath('source')
      		                     . $this->getPath('manifest')
      		                     ;
      		$bak_manifest_path   = $this->getPath('source')
      		                     . $this->getPath('manifest')
      		                     . '.original.xml'
      		                     ;
      		// Keep a copy of the original manifest file that will be overwriten by the new installation
      		JFile::copy( $slave_manifest_path, $bak_manifest_path);
      		define( 'MULTISITES_PREVIOUS_MANIFEST_FILE', $bak_manifest_path);
		   }
		   else {
      		$mainframe->enqueueMessage( JText::sprintf( 'MSJINSTALL_EXT_DIF_VERSION', $slave_version, $data['version']));
   		   $rc = false;
   		}
		}

		return $rc;
   }
   
   
   //------------ isValidVersion ---------------
   /**
    * Check if the extension has exactly the same version than the one installed by the master
    */
   function isValidVersion( $path=null)
   {
      $slave_manifest =& JInstallerMultisites::getSlaveManifest( $path);
      if ( empty( $slave_manifest)) {
         return false;
      }

		// If the slave site wants to install a template and there is a specific template (themes) directory
		$root		   =& $slave_manifest;
		$inst_type	= $this->xmlGetAttribue( $root, 'type');
      if ( $inst_type == 'template') {
   		if ( defined( 'MULTISITES_ID')) {
            if ( defined( 'MULTISITES_ID_PATH'))   { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
            else                                   { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DS.'config_multisites.php'; }
            
            @include($filename);
            if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['templates_dir'])) {
      			if (!$this->setAdapterMultisites($inst_type)) {
      				return false;
      			}
               return true;
            }
         }
      }
         
      return JInstallerMultisites::compareWithMaster( $slave_manifest);
   }


   //------------ getPath ---------------
   /**
    * @brief Intercept the getPath to simulate a "fake" installation directory
    *        and therefore allow the slave site proceed with the install like in the master.
    */
	function getPath($name, $default=null)
	{
	   $fake_suffix = '';
	   // In case of Joomla 1.6 or higher
	   if ( version_compare( JVERSION, '1.6') >= 0)
	   {
	      // When this is called from a JInstallerComponent::install()
	      $stack = debug_backtrace();
	      if ( !empty( $stack[1]['class']) && $stack[1]['class']=='JInstallerComponent') {
	         // And that is in the statement that consist in checking if a directory exists
	         // to perform an update or a fresh install
	         // see line 198: 
	         // if (file_exists($this->parent->getPath('extension_site')) || file_exists($this->parent->getPath('extension_administrator'))) {
   	      if ( !empty( $this->_fake_extension_site) && $name=='extension_site') {
   	         // return a fake path
   	         $this->_fake_extension_site = false;
         	   $fake_suffix = '_fake';
   	      }
   	      else if ( isset( $this->_fake_extension_administrator) && $name=='extension_administrator') {
   	         if ( $this->_fake_extension_administrator==1) {
      	         // return a fake path for the second call
            	   $fake_suffix = '_fake';
   	         }
   	         $this->_fake_extension_administrator++;
   	      }
	      }
	   }
	   return parent::getPath($name, $default).$fake_suffix;
	}

   //------------ install ---------------
   /**
    * Wrapper for JInstaller::install in aim to check that slave site install exactly the same version than the one installed on the master site.
    * Otherwise, the installation fail.
    */
	function install($path=null)
	{
      // Ensure that the language file is loaded for this component.
   	$lang =& JFactory::getLanguage();
   	$lang->load( 'com_multisites');
   	
	   // When the version is valid then enable the overwrite attribute to allow re-installing the extension and populate the database
	   if ( $this->isValidVersion( $path)) {
      	$this->setOverwrite( true);

   	   // In case of Joomla 1.6 or 1.7
      	if ( version_compare( JVERSION, '1.6') >= 0)
   	   {
   	      // Try simulate that the directories are not present to allow overwriting
   	      // the existing PHP code and build DB and admin menus
      	   $this->_fake_extension_site = true;
      	   $this->_fake_extension_administrator=0;
   	   }
      	
      	$result = parent::install($path);
      	
      	// In case where an upgrade were installed in the slave site, restore the manifest file to allow reapply the upgrade
      	if ( defined( MULTISITES_PREVIOUS_MANIFEST_FILE)) {
      		$slave_manifest_path = $this->getPath('source')
      		                     . $this->getPath('manifest')
      		                     ;
      		// restore the original manifest file to allow redo an upgrade in another slave site.
      		JFile::copy( MULTISITES_PREVIOUS_MANIFEST_FILE, $slave_manifest_path);
      	}
      	
      	return $result;
	   }
	   return false;
	}

 
   //------------ setAdapterMultisites ---------------
 	/**
	 * @brief Plug the appropriate adapter depending on extension name.
	 *
	 * This replace the standard Joomla! adapters.
	 *
	 * @note
	 * The specific Mutli Sites adapter are only plugged for the Uninstall processing.
	 * In all the other case, the standard Joomla adapters are used.
	 */
	function setAdapterMultisites($name, $adapter = null)
	{
		if (!is_object($adapter))
		{
			// Try to load the adapter object
         if ( version_compare( JVERSION, '1.6') >= 0) { 
			   require_once(dirname(__FILE__).DS.'adapters16'.DS.strtolower($name).'.php');
         }
         else {
			   require_once(dirname(__FILE__).DS.'adapters'.DS.strtolower($name).'.php');
			}
			$class = 'JInstaller'.ucfirst($name).'Multisites';
			if (!class_exists($class)) {
				return false;
			}
			$adapter = new $class($this);
			$adapter->setParent( $this);
		}
		$this->_adapters[$name] =& $adapter;
		return true;
	}

 
   //------------ uninstall ---------------
   /**
    * Only implement the database cleanup. Don't remove the files and folders.
    */
	function uninstall($type, $identifier, $cid=0)
	{
      // Ensure that the language file is loaded for this component.
   	$lang =& JFactory::getLanguage();
   	$lang->load( 'com_multisites');

		if (!isset($this->_adapters[$type]) || !is_object($this->_adapters[$type])) {
			if (!$this->setAdapterMultisites($type)) {
				return false;
			}
		}
		if (is_object($this->_adapters[$type])) {
			return $this->_adapters[$type]->uninstall($identifier, $cid);
		}
		return false;
	}

} // End class


// ===========================================================
//             JInstallerMultisites class
// ===========================================================
/**
 * @brief Declare static or not (nothing) depending on Joomla 1.5 or 1.6 or 1.7
 */

if ( defined( 'JVERSION')) {
   if ( version_compare( JVERSION, '1.6') >= 0) { $jms2win_php4_static = 'public static '; }
   else                                         { $jms2win_php4_static = ''; }
}
// Try detect the Joomla version number
else {
   // If Joomla 2.5 directory
   if ( file_exists( dirname(dirname(dirname(dirname(dirname(dirname(dirname( __FILE__))))))).DS.'libraries'.DS.'cms'.DS.'version'.DS.'version.php')) {
      $jms2win_php4_static = 'public static ';
      // Ensure that the JLog is loaded
      jimport('joomla.log.log');
   }
   // If Joomla 1.7 directory
   else if ( file_exists( dirname(dirname(dirname(dirname(dirname(dirname(dirname( __FILE__))))))).DS.'includes'.DS.'version.php')) {
      $jms2win_php4_static = 'public static ';
      // Ensure that the JLog is loaded
      jimport('joomla.log.log');
   }
   // If Joomla 1.5 or 1.6
   else {
      // Read the Joomla version PHP source to search for '1.6' pattern
      $jms2win_vers_content = file_get_contents(dirname(dirname(dirname(dirname(dirname(dirname(dirname( _FILE__))))))).DS.'libraries'.DS.'joomla'.DS.'version.php');
      if ( strpos( '1.6', $jms2win_vers_content) !== false) { $jms2win_php4_static = 'public static '; }
      else                                                  { $jms2win_php4_static = ''; }
      unset( $jms2win_vers_content);
   }
}

eval( 'class JInstallerMultisites extends JInstallerMultisites_j15 { '
    . $jms2win_php4_static . 'function &getInstance() { return JInstallerMultisites_j15::_getInstance() ; }'
    . '}'
    ) ;
unset( $jms2win_php4_static);

<?php
/**
 * @file       install.script.php
 * @version    1.1.0 (MultisitesContent)
 *             1.2.55 (Jms Multisites)
 *             1.2.00 (MultisitesUserSite)
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms  Multi Sites
 *             Single Joomla! 1.5.x AND 1.6.x installation using multiple configuration (One for each 'slave' sites).
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
 * - V1.1.0  23-MAY-2011: Initial version.
 * - V1.2.53 02-JUN-2011: Fix bug in postflight when a old "extension.xml" is present.
 * - V1.2.54 02-JUN-2011: Compute the new Joomla 1.6 manifest file name.
 *                        On Joomla 1.6, remove the deprecated "extension.xml" manifest file.
 * - V1.2.55 16-JUN-2011: Fix warning message on the manifest file name detection.
 * - V1.1.01 24-OCT-2011: Add the possibility to process additional extensions
 * - V1.2.00 14-MAR-2013: Add Joomla 3.0 compatibility and no more compatible with JMS 1.2.x
 */


// Dont allow direct linking
defined( '_JEXEC' ) or die();

require_once( dirname( __FILE__).DIRECTORY_SEPARATOR.'legacy.php');

if ( !defined( 'JPATH_MULTISITES')) {
   define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}


jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

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
                                               if ( isset( $row)) { $GLOBALS['uninstall_row'] = $row; }
                                               if ( isset( $id))  { $GLOBALS['uninstall_id']  = $id; }
                                             }
// If Joomla 1.5
else                                         { $myManifestVersion =& $this->manifest->getElementByPath('version'); }

if ( method_exists( $myManifestVersion, 'data'))   { $GLOBALS['installManifestVersion'] = JFilterInput::clean($myManifestVersion->data(), 'cmd'); }
// Joomla 3.0
else                                               { $GLOBALS['installManifestVersion'] = JFilterInput::clean( (string)$myManifestVersion, 'cmd'); }



// ===========================================================
//             Com_MultisitesUserSiteInstallerScript class
// ===========================================================
class Com_MultisitesUserSiteInstallerScript extends MultisitesConvertLanguage {

   //------------ displayResults ---------------
	function displayResults( $action, $parent, $rows, $pageTitle, $colTitles = array( 'name'   => 'name',
	                                                                'title'  => 'title', 
	                                                                'status' => 'status') )
   {
      if ( $action == 'uninstall') {
         $msg_success = 'MULTISITESUSERSITE_UNINSTALL_ADDITIONAL_SUCCESS';
         $msg_error   = 'MULTISITESUSERSITE_UNINSTALL_ADDITIONAL_ERROR';
      }
      else {
         $msg_success = 'MULTISITESUSERSITE_INSTALL_ADDITIONAL_SUCCESS';
         $msg_error   = 'MULTISITESUSERSITE_INSTALL_ADDITIONAL_ERROR';
      }
?>
		<div class="additional-results">
		<h3><?php echo JText::_( $pageTitle); ?></h3>
		<table class="adminlist">
			<thead>
				<tr>
<?php if ( !empty( $colTitles[ 'group'])) { ?>
   				<th class="group"><?php echo JText::_( $colTitles['group']); ?></th>
<?php } ?>
					<th class="name"><?php echo JText::_( $colTitles['name']); ?></th>
					<th class="title"><?php echo JText::_( $colTitles['title']); ?></th>
					<th class="status"><?php echo JText::_( $colTitles['status']); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
<?php if ( !empty( $colTitles[ 'group'])) { ?>
					<td colspan="4">&nbsp;</td>
<?php } else { ?>
					<td colspan="3">&nbsp;</td>
<?php } ?>
				</tr>
			</tfoot>
			<tbody>
<?php       foreach ( $rows as $i => $row) { ?>
					<tr class="row<?php echo $i++ % 2; ?>">
<?php if ( !empty( $colTitles[ 'group'])) { ?>
						<td class="group"><?php echo $row['group']; ?></td>
<?php } ?>
						<td class="name"><?php echo $row['name']; ?></td>
						<td class="title"><?php echo $row['title']; ?></td>
						<td class="status">
                     <?php echo JText::_( ($row['status'] == 1) ? $msg_success : $msg_error); ?>
						</td>
					</tr>
<?php       } ?>
			</tbody>
		</table>
		</div>
<?php
	}

   //------------ displayStatus ---------------
	function displayStatus( $action, $parent, $status)
	{
	   if ( !empty( $status)) {
	      // Display "modules" results
	      if ( !empty( $status->modules)) {
	         $this->displayResults( $action, $parent, $status->modules, 'Modules');
	      }
	      // Display "plugins" results
	      if ( !empty( $status->plugins)) {
	         $this->displayResults( $action, $parent, $status->plugins, 'Plugins', array( 'name'   => 'name',
                  	                                                                    'title'  => 'title', 
                  	                                                                    'status' => 'status',
                  	                                                                    'group'  =>'group'));
	      }
	   }
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
		else if ( is_a( $xmlelement, 'SimpleXMLElement')) {
		   $attrs = $xmlelement->attributes();
		   if ( !empty( $attrs) && !empty( $attrs[$attribute])) {
		      return (string)$attrs[$attribute];
		   }
			return  '';
		}
		return $xmlelement->attributes( $attribute);
   }

   //------------ processAdditionalExtensions ---------------
   /**
    * @brief Install the additional extension that might be declared in the manifest file
    * @notes
        <additional>
          <module name="mod_test1" folder="install/modules/mod_test1" client="site">Module title</module>
          <module name="mod_test2" client="administrator" />
          <plugin name="plgSystemTest3" group="system">System - Test3</plugin>
        </additional>
    */
	function processAdditionalExtensions($action, $thisObject)
	{
	   $db =& JFactory::getDBO();

   	$status = new JObject();
   	$status->modules = array();
   	$status->plugins = array();

      $parent   = $thisObject->get( 'parent');
      $src      = $parent->getPath('source');
      $manifest = $parent->getManifest();

      // Get the list of additional extensions to install
   	$exts = array();
      if ( version_compare( JVERSION, '1.6') >= 0) {
//      	$exts = &$manifest->xpath('additional');
         if ( isset( $manifest->additional[0])) {
            $exts = $manifest->additional[0]->children();
         }
      }
      else {
      	$additional = &$manifest->document->getElementByPath('additional');
      	if (is_a( $additional, 'JSimpleXMLElement')) {
      		$exts = $additional->children();
      	}
      }

      // If there are additional extensions to install
      if ( !empty( $exts)) {
         foreach ( $exts as $ext) {
      		// Get a NEW installer instance like the one that were created.
      		$classname = get_class( $parent);
      		$installer = new $classname;     // new JInstaller(); or new JInstallerMultisites()
      		
      		// read the attributes
      		     if ( method_exists( $ext, 'name'))      { $extType = $ext->name(); }     // Joomla 1.5 -> 2.5
      		else if ( method_exists( $ext, 'getName'))   { $extType = $ext->getName(); }  // Joomla 3.0
      		else                                         { $extType = $ext->name; }       // If we are lucky
      		
      		$extTypeS= $extType.'s';
      		$name	   = $this->xmlGetAttribue( $ext, 'name');
      		$group   = $this->xmlGetAttribue( $ext, 'group');
      		if ( !empty( $group)) { $plugin_folder = ' AND folder='.$db->Quote( $group); }
      		else                  { $plugin_folder = ''; }
      		$title   = method_exists( $ext, 'data') ? $ext->data()    // Joomla 1.5 -> 2.5
      		                                        : (string)$ext;   // Joomla 3.0

      		$jversion   = $this->xmlGetAttribue( $ext, 'jversion');
      		if ( !empty( $jversion)) {
      			if ( preg_match( '#([a-z|<|>|=]*)\s?([0-9|\.]*)#i', $jversion, $match)) {
      			   $cond = trim($match[1]);
      			   $vers = trim($match[2]);
      			   
      			   // If the joomla version match
      			   if ( version_compare( JVERSION, $vers, $cond)) {
      			      // then process the additional extension
      			   }
      			   // If not match
      			   else {
      			      // Then skip the record and continue with the next one
      			      continue;
      			   }
      			}
      		}


      		// ---- INSTALL ----
      		if ( $action == 'install') {
      		   $folder  = $this->xmlGetAttribue( $ext, 'folder');
         		if ( empty( $folder)) {
            		if ( !empty( $group))   { $folder = DS.'admin'.DS.'install'.DS.$extTypeS.DS.$group.DS.$name; }
            		else                    { $folder = DS.'admin'.DS.'install'.DS.$extTypeS.DS.$name; }
         		}
         		$path = $src.$folder;
         		$rc = $installer->install( $path);
         		array_push( $status->$extTypeS, array( 'name'=>$name, 'title' => $title, 'group'=>$group, 'status'=>$rc, 'ext'=>$ext));
         		if ( $rc) {
            		$publish = false;
         		   $attr    = $this->xmlGetAttribue( $ext, 'publish');
            		if ( !empty( $attr)) {
            		   if ( $attr == 1 || strtolower( $attr) == 'true') {
            		      $publish = true;
            		   }
            		}
            		// If the extension must be published
            		if ( $publish) {
      					// Joomla 1.6, 1.7
      					if ( version_compare( JVERSION, '1.6') >= 0) {
                     	// publish the extension
                     	$query = "UPDATE #__extensions SET enabled='1'"
                     	       . ' WHERE type='.$db->Quote( $extType)
      						       . ' AND element = '.$db->Quote( $name)
      						       . $plugin_folder
      						       ;
                     }
                     else {
      						     if ( $extType == 'plugin') { $query = "UPDATE #__plugins SET published='1' WHERE element=".$db->Quote( $name). $plugin_folder; }
         				   else if ( $extType == 'module') { $query = "UPDATE #__modules SET published='1' WHERE module=".$db->Quote( $name); }
                     }
                  	$db->setQuery( $query );
                  	$db->query();
                  }
         		}
      		}
      		// ---- UNINSTALL ----
      		else if ( $action == 'uninstall') {
					// Retreive the extension ID and client ID that must be uninstalled
					// Joomla 1.6, 1.7
					if ( version_compare( JVERSION, '1.6') >= 0) {
						$query = 'SELECT client_id, extension_id as id, folder, element FROM #__extensions WHERE type='.$db->Quote( $extType)
						       . ' AND element = '.$db->Quote( $name)
						       . $plugin_folder
						       ;
					}
					// Joomla 1.5
					else {
						     if ( $extType == 'plugin') { $query = 'SELECT client_id, id, folder, element FROM #__plugins WHERE element='.$db->Quote( $name). $plugin_folder; }
   				   else if ( $extType == 'module') { $query = 'SELECT client_id, id, module as element FROM #__modules WHERE module='.$db->Quote( $name); }
					}
					// query extension id and client id
					$db->setQuery( $query);
					$row = $db->loadObject();
					if ( !empty( $row)) {
   					$id         = !empty( $row->id) ? $row->id : 0;
   					$client_id  = !empty( $row->client_id) ? $row->client_id : 0;
   					
				      // Verify that the extension is present on the disk (and not only defined in the DB)
				      if ( $extType == 'plugin') {
				         // Verify that the plugin manifest file is present on the disk
               		if ( file_exists( JPATH_PLUGINS . DS . $row->folder . DS . $row->element .DS. $row->element.'.xml') // J1.6
               		  || file_exists( JPATH_PLUGINS . DS . $row->folder . DS . $row->element.'.xml') // J1.5
               		   )
               		{}
               		// When not present,
               		else {
               		   // Skip the uninstall to avoid reporting error message
               		   continue;
               		}
				      }
				      else if ( $extType == 'module') {
				         // Verify that the plugin manifest file is present on the disk
               		if ( is_dir( JPATH_SITE . DS . 'modules' .DS. $row->element) // FRONT END
               		  || is_dir( JPATH_ADMINISTRATOR .DS. 'modules' .DS. $row->element) // BACK END
               		   )
               		{}
               		// When not present,
               		else {
               		   // Skip the uninstall to avoid reporting error message
               		   continue;
               		}
				      }
   					
            		$rc = $installer->uninstall( $extType, $id, $client_id);
            		array_push( $status->$extTypeS, array( 'name'=>$name, 'title' => $title, 'group'=>$group, 'status'=>$rc, 'ext'=>$ext));
            	}
         	   // OK: The extension is not installed
            	else {
            	   // Don't do anything
            		// $rc = null;
            		// array_push( $status->$extTypeS, array( 'name'=>$name, 'title' => $title, 'status'=>$rc, 'ext'=>$ext));
            	}
      		}
      		// Error
      		else {
      		}
         }
         // Display the addition extension status
         $this->displayStatus($action, $parent, $status);
      }
         
	}


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

      // If a configuration file is already present then do nothing.
      // Otherwise create a "multisitesusersite.cfg.php" configuration based on the "distibution" configuration.
      jimport('joomla.filesystem.file');
      if ( !JFile::exists( $path.DS.'multisitesusersite.cfg.php')
        &&  JFile::exists( $path.DS.'multisitesusersite.cfg-dist.php')
         )
      {
         JFile::copy( $path.DS.'multisitesusersite.cfg-dist.php',
                      $path.DS.'multisitesusersite.cfg.php'
                    );
                      
      }
      
      @include_once( $path.DS.'multisitesusersite.cfg.php' );


	   // Start to process the additional Extension to install
      $this->processAdditionalExtensions( 'install', $parent);

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

      // Load the language file of this component.
      $name = basename( dirname( __FILE__));
   	$lang =& JFactory::getLanguage();
   	$lang->load( $name);


	   // Start to process the additional Extension to uninstall
      $this->processAdditionalExtensions( 'uninstall', $parent);
      
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
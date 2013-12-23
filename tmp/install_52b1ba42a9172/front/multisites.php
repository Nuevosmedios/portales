<?php
/**
 * @file       multisites.php
 * @brief      Front-end that allow to create dynamic slave sites.
 * @version    1.3.00 RC1
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
 * - V1.1.0 11-OCT-2008: File creation
 * - V1.2.20 02-FEB-2010: Add reading the multisites.cfg.php configuration file to allow force
 *                        used hardcoded values when creating a slave site.
 * - V1.2.54 02-JUN-2010: Compute the new Joomla 1.6 manifest file name.
 * - V1.2.71 28-DEC-2011: In Joomla 1.7, always authorize the user to access the component to allow anonymous user create site.
 * - V1.2.87 22-APR-2012: Add possibility to have specific Layout controller to perform additional tasks.
 * - V1.2.90 07-JUN-2012: Fix layout computation value under joomla 2.5
 * - V1.2.95 15-AUG-2012: Fix layout computation value when complex layout is selected with sub-parameters
 * - V1.3.00b5 10-SEP-2012: Disable the "PHP Strict" error reporting to avoid displaying STRICT message
 *                          Add compatibility with Joomla 3.0 beta1
 * - V1.3.00RC1 10-OCT-2012: Add Joomla 3.0 compatibility
 * - V1.2.96 26-OCT-2012: Add partial JMS 1.2/1.3 front-end compatibility (legacy)
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if ( file_exists( JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'legacy.php')) {
   require_once( JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'legacy.php');
}
jimport('joomla.filesystem.file');

// Disable the STRICT error reporting
$oldErrorReporting = error_reporting( JFactory::getApplication()->getCfg('error_reporting') & ~E_STRICT);

$isAutorised = false;
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
   $isAutorised = true;
}
// If Joomla 1.5
else {
   if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {
      define( 'MULTISITES_MANIFEST_FILENAME', 'install.xml');
   }

   // Define the group of users that in addition of the owner can show and edit the slave sites
   $auth =& JFactory::getACL();
   $auth->addACL('com_multisites', 'edit', 'users', 'super administrator');
   $auth->addACL('com_multisites', 'edit', 'users', 'administrator');

   $isAutorised = true;
}

if ( $isAutorised) {
   @include_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'multisites.cfg.php' );

   // --- Compute the controller location depending on the Layout selected ---
   $ctrl_dir  = dirname(__FILE__);
   
	// If a "Multisite Create Site" module ID is present,
	$module_id = JRequest::getInt('module_id', 0);
	if ( !empty( $module_id)) {
	   // read the parameters from the module
		$table = JTable::getInstance('Module');
		if ( $table->load( $module_id)) {
         if ( version_compare( JVERSION, '1.6') >= 0) {
      		// Get module parameters
      		$params = new JRegistry;
      		$params->loadString( $table->params);
         }
   		else {
		      $params = new JParameter( $table->params );
   		}
   		$ctrl_dir = JPATH_SITE.DS.'modules'.DS.$table->module;
		}
	}
	// When there is no "module" parameters
	if ( empty( $params)) {	
	   // read the parameters from the component
	   $params	   = JFactory::getApplication()->getParams();
	}
	
   // Default controller
   $ctrl_filename = dirname(__FILE__).DS.'controller.php';
   $classname     = 'MultisitesController';

	// Try finding a specific "controller" depending on the selected layout
	$layout = $params->get('jmslayout');
	if ( is_array( $layout)) {
	   if ( !empty( $layout['value'])) {
         $layout = $layout['value'];
	   }
	   else {
   	   $layout = implode( '', $layout);
	   }
	}
   else if ( is_object( $layout) && !empty( $layout->value)) {
      $layout = $layout->value;
   }
	if ( empty( $layout) || $layout == ':select:' || $layout == ':default:') {
	   // Do nothing
	}
	else {
	   // Use the module / templates / layout / controller.php file
	   $filename = $ctrl_dir.DS.'templates'.DS.$layout.DS.'controller.php';
      // If the specific controller exists
      if ( JFile::exists( $filename)) {
         $ctrl_filename = $filename;
   	   $classname = 'MultisitesController'.ucfirst( $layout);
      }
	}

   // Create the controller
   require_once( $ctrl_filename);
   $controller = new $classname();
   $controller->execute( JRequest::getCmd( 'task' ));
   $controller->redirect();
}

error_reporting( $oldErrorReporting);
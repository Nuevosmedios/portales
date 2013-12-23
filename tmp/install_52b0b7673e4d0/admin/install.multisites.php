<?php
/**
 * @file       install.multisites.php
 * @version    1.2.53
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2011 Edwin2Win sprlu - all right reserved.
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
 * - V1.0.4 10-AUG-2008: Add a check to verify that 'multisites' directory is created.
 * - V1.0.5 19-AUG-2008: Collect the extension version because the manifest files is not yet saved in the target directory.
 * - V1.0.7 22-AUG-2008: Replace native mkdir and copy by Joomla JFolder and JFile in aim to reduce permission problems.
 * - V1.1.21 20-APR-2009: Increase the execution time limit in case where the upload took too much time.
 * - V1.2.0 RC5 25-JUL-2009: Add the creation of an index.html file into the /multisites directory to hide the list of slave sites.
 * - V1.2.0 07-AUG-2009: Include also the controler to allow retreive the JMS version number in the patch definition.
 * - V1.2.23 08-MAR-2010: Cleanup (remove) older Joomla patches to reduce the size of package.
 * - V1.2.29 30-MAY-2010: Avoid replacing the "mutisites.cfg.php" when already present.
 * - V1.2.30 02-JUN-2010: Fix for Joomla 1.6 beta1 compatibility.
 * - V1.2.32 02-JUN-2010: Add Joomla 1.5 Language file conversion to be compatible with for Joomla 1.6 beta3.
 *                        Hide a warning on set_time_limit() when the call to this function not allowed by a server
 *                        that have the safe mode enabled.
 * - V1.2.34 17-JUL-2010: Modify the Joomla 1.5 to 1.6 language conversion to use the "_QQ_" special character corresponding to Quote (") in Joomla 1.6.
 * - V1.2.36 03-SEP-2010: Improve langage conversion to avoid convert first and last quote with _QQ_
 * - V1.2.47 08-FEB-2011: Fix language conversion in Joomla 1.6 that didn't processed the administrator files.
 * - V1.2.53 02-JUN-2011: Refactor for Joomla 1.6 compatibility.
 *                        All the code is moved in install.script.php that is the new joomla 1.6 standard.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );


// If Joomla 1.6, do nothing
if ( version_compare( JVERSION, '1.6') >= 0) {
   // Do nothing
}
// If Joomla 1.5, implement the com_install that redirect to Joomla 1.6 install.script.php
else {
	// Emulate J1.6 installer
	include_once(dirname(__FILE__).'/install.script.php');

	//------------ com_install ---------------
   /**
    * @brief redirect to the new Joomla 1.6 implementation.
    */
   function com_install()
   {
      // Retreive the component name
      $name = basename( dirname( __FILE__));
      
      $classInstallerScript = $name . 'InstallerScript';
      if ( class_exists( $classInstallerScript)) {
         $j16Installer = new $classInstallerScript();
      	if ( method_exists( $classInstallerScript, 'preflight'))    { $j16Installer->preflight( 'install', null ); }
      	if ( method_exists( $classInstallerScript, 'install'))      { $j16Installer->install ( null ); }
      	if ( method_exists( $classInstallerScript, 'postflight'))   { $j16Installer->postflight( 'install', null ); }
      }
   }
} // Joomla 1.5
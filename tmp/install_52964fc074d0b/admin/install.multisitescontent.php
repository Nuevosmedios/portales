<?php
/**
 * @file       install.multisitescontent.php
 * @version    1.1.0
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms  Multi Sites
 *             Single Joomla! 1.5.x AND 1.6.x installation using multiple configuration (One for each 'slave' sites).
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
 * - V1.0.0 05-DEC-2008: Initial version
 * - V1.1.0 24-MAY-2011: Refactor for Joomla 1.6 compatibility
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
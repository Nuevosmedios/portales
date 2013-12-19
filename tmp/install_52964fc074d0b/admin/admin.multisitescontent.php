<?php
/**
 * @file       admin.multisitescontent.php
 * @version    1.1.6
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms Multi Sites
 *             With a single Joomla! 1.5.x, create as many joomla configuration as you have sites.
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
 * - V1.0.0 10-NOV-2008: Initial version
 * - V1.1.0 09-MAR-2011: Add Joomla 1.6 compatibility
 * - V1.1.6 02-JUN-2012: Added basic ACL for Joomla 2.5
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if ( version_compare( JVERSION, '1.7') >= 0) { 
   // Access check.
   if (!JFactory::getUser()->authorise('core.manage', 'com_multisitescontent')) {
   	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
   }
}

if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {
   // If Joomla 1.6
   if ( file_exists( dirname( __FILE__).DS.'extension.xml')) {
      define( 'MULTISITES_MANIFEST_FILENAME', 'extension.xml');
   }
   else {
      // 1.6 based on extension name
      $extname = substr( basename( dirname( __FILE__)), 4);
      if ( file_exists( dirname( __FILE__).DS.$extname.'.xml')) {
         define( 'MULTISITES_MANIFEST_FILENAME', $extname.'.xml');
      }
      // If Joomla 1.5
      else {
         define( 'MULTISITES_MANIFEST_FILENAME', 'install.xml');
      }
   }
}


if ( !defined( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR')) {
   define( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR',
            JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');

require_once( JPATH_COMPONENT.DS.'controller.php' );
if ( version_compare( JVERSION, '1.6') >= 0) {}
// Joomla 1.5
else {
   require_once( JPATH_COMPONENT.DS.'helper.php' );
   
   // Set the helper directory
   JHTML::addIncludePath( JPATH_COMPONENT.DS.'helpers' );
}

$controller = new ContentController( array('default_task' => 'about') );
$controller->execute( JRequest::getCmd( 'task' ) );
$controller->redirect();
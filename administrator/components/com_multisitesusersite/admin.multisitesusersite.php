<?php
/**
 * @file       admin.multisitesusersite.php
 * @version    1.2.0
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms Multi Sites
 *             With a single Joomla! 1.5.x, create as many joomla configuration as you have sites.
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
 * - V1.1.0 21-SEP-2011: Initial version
 * - V1.1.4 31-MAY-2012: Add basic ACL
 * - V1.2.0 14-MAR-2013: Add Joomla 3.0 compatibility and no more compatible with JMS 1.2.x
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( dirname( __FILE__).DIRECTORY_SEPARATOR.'legacy.php');

jimport('joomla.filesystem.file');

@include_once( dirname( __FILE__).DS.'multisitesusersite.cfg.php' );

$isAutorised = false;
// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) {
   if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {
      define( 'MULTISITES_MANIFEST_FILENAME', substr( basename( dirname( __FILE__)), 4).'.xml');
   }
   if ( version_compare( JVERSION, '1.7') >= 0) {
      if (!JFactory::getUser()->authorise('core.manage', 'com_multisitesusersite')) {
      	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
      }
      $isAutorised = true;
   }
   else {
   	if ( JFactory::getUser()->authorize('com_multisitesusersite.listusersite')) {
         $isAutorised = true;
   	}
   }
}
// If Joomla 1.5
else {
   if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {
      define( 'MULTISITES_MANIFEST_FILENAME', 'install.xml');
   }
   // Define the group of users that can access the back-end
   $auth =& JFactory::getACL();
   $auth->addACL('com_multisitesusersite', 'listusersite', 'users', 'super administrator');
   
   // If not Super Administrator or not on the master website (the component is not registered).
   $user = & JFactory::getUser();
	$option = JRequest::getCmd('option');
   $result = &JComponentHelper::getComponent( $option,  true);
   if ( !$user->authorize( 'com_multisitesusersite', 'listusersite')
     || !$result->enabled) {
      $mainframe  = &JFactory::getApplication();
   	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
   }
   else {
      $isAutorised = true;
   }
}

if ( $isAutorised) {
   // Check if the access is only allowed to websites that own the table
   // or if this is autorized to both "table" and "views"
   if ( defined( 'MULTISITESUSERSITE_CFG_RESTRICT_ON_TABLE') && MULTISITESUSERSITE_CFG_RESTRICT_ON_TABLE) {
      $db    =& JFactory::getDBO();
      $table = $db->getPrefix().'multisites_users';
      $query = "SHOW TABLE STATUS LIKE '$table'";
      $db->setQuery( $query );
      $obj = $db->loadObject();
      if ( !empty( $obj) && !empty( $obj->Comment) && strtoupper( substr($obj->Comment, 0, 4)) == 'VIEW') {
         if ( version_compare( JVERSION, '1.7') >= 0) {
            // Always execute the task=restricted
            JRequest::setVar( 'task', 'restricted', 'GET');
         }
         // Joomla 1.5, 1.6
         else {
            $mainframe  = &JFactory::getApplication();
         	$mainframe->redirect('index.php', JText::_('Access forbidden! Only the website where the data is physically stored can manage the users permissions'), 'Error');
         }
      }
   }
   
   require_once( JPATH_COMPONENT.DS.'controller.php' );
   require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'helpers'.DS.'helper.php' );
   
   require_once( JPATH_COMPONENT.DS.'controller.php' );
   
   $controller = new MultisitesUserSiteController( array('default_task' => 'listusersite') );
   $controller->execute( JRequest::getCmd( 'task' ) );
   $controller->redirect();
}
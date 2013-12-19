<?php
// file: admin.multisites.php.
// copyright : (C) 2008-2012 Edwin2Win sprlu - all right reserved.
// author: www.jms2win.com - info@jms2win.com
/* license: 
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
*/
?><?php


defined( '_JEXEC' ) or die( 'Restricted access' );
require_once( dirname( __FILE__).DIRECTORY_SEPARATOR.'legacy.php');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

$oldErrorReporting = error_reporting( JFactory::getApplication()->getCfg('error_reporting') & ~E_STRICT);
$isAutorised = false;

if ( version_compare( JVERSION, '1.6') >= 0) {
if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {
if ( is_file( dirname( __FILE__).DS.'extension.xml')) {
define( 'MULTISITES_MANIFEST_FILENAME', 'extension.xml');
}
else {
define( 'MULTISITES_MANIFEST_FILENAME', substr( basename( dirname( __FILE__)), 4).'.xml');
}
}
if ( version_compare( JVERSION, '1.7') >= 0) {

if (!JFactory::getUser()->authorise('core.manage', 'com_multisites')) {
return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
else {
$isAutorised = true;
}
}
else {
$user = & JFactory::getUser();
if ($user->authorize('com_multisites.manage')) {
$isAutorised = true;
}
}
}

else {
if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {
define( 'MULTISITES_MANIFEST_FILENAME', 'install.xml');
}

$auth =& JFactory::getACL();
$auth->addACL('com_multisites', 'manage', 'users', 'super administrator');



$user = & JFactory::getUser();
$option = JRequest::getCmd('option');
$result = &JComponentHelper::getComponent( $option, true);
if ( !$user->authorize( 'com_multisites', 'manage')
|| !$result->enabled) {
$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
}
else {
$isAutorised = true;
}
}
if ( $isAutorised) {
include_once( JPATH_COMPONENT.DS.'multisites.cfg.php' );
require_once( JPATH_COMPONENT.DS.'controller.php' );
require_once( JPATH_COMPONENT.DS.'helpers'.DS.'helper.php' );
$controllerName = JRequest::getCmd('controller', 'dummy');

if ( JFile::exists( dirname( __FILE__) .DS. $controllerName .DS. 'index.php')) {
require_once(dirname(__FILE__) .DS. $controllerName .DS. 'index.php');
}

else if ( JFile::exists( dirname( __FILE__) .DS. 'controllers' .DS. $controllerName. '.php')) {
require_once (JPATH_COMPONENT.DS.'controllers'.DS.$controllerName.'.php');
$classname = $controllerName.'Controller';
$controller = new $classname( array('default_task' => 'display') );
$controller->execute( JRequest::getCmd('task' ));
$controller->redirect();
}

else {

$task = JRequest::getCmd( 'task', 'manage');



$files = JFolder::files( dirname(__FILE__).DS.'controllers', '\.php$', false, true);
if ( !empty( $files)) {
foreach( $files as $file) {
require_once( $file);
$ctrlVariant = basename( $file, '.php');
$className = 'MultisitesController'.ucfirst( $ctrlVariant);
if ( class_exists( $className)) {
if ( method_exists( $className, $task)) {
$controller = new $className();
break;
}
}
}
}

if ( !isset( $controller)) {

$controller = new MultisitesController( array('default_task' => 'manage') );
}
$controller->registerTask('apply', 'save');
$controller->execute( $task);
$controller->redirect();
}
}
error_reporting( $oldErrorReporting);

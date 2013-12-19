<?php
// file: legacy.php.
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

if ( !defined( 'DS')) { define('DS', DIRECTORY_SEPARATOR); }

if ( !class_exists( 'J2WinFactory')) {
class J2WinFactory {
public static function & getApplication($id = null, $config = array(), $prefix = 'J') {
static $instance;
if (!is_object($instance)) {

if ( version_compare( JVERSION, '1.6') >= 0) { $instance = JFactory::getApplication($id, $config, $prefix); }

else { $instance = &JFactory::getApplication($id, $config, $prefix); }
}
return $instance;
}
public static function &getLanguage() {
static $instance;
if (!is_object($instance)) {

if ( version_compare( JVERSION, '1.6') >= 0) { $instance = JFactory::getLanguage(); }

else { $instance = &JFactory::getLanguage(); }
}
return $instance;
}
} 
}

if ( !class_exists( 'J2WinController')) {

if ( file_exists( JPATH_LIBRARIES.'/legacy/controller/legacy.php')) { jimport('legacy.controller.legacy'); }
if ( class_exists( 'JControllerLegacy')) { eval( 'class J2WinController extends JControllerLegacy {};') ; }
else {

jimport('joomla.application.component.controller');
if ( class_exists( 'JController')) { eval( 'class J2WinController extends JController{};') ; }
}
}

if ( !class_exists( 'J2WinModel')) {

if ( file_exists( JPATH_LIBRARIES.'/legacy/model/legacy.php')) { jimport('legacy.model.legacy'); }
if ( class_exists( 'JModelLegacy')) { eval( 'class J2WinModel extends JModelLegacy {};') ; }
else {

jimport('joomla.application.component.model');
if ( class_exists( 'JModel')) { eval( 'class J2WinModel extends JModel{};') ; }
}
}

if ( !class_exists( 'J2WinView')) {

if ( file_exists( JPATH_LIBRARIES.'/legacy/view/legacy.php')) { jimport('legacy.view.legacy'); }
if ( class_exists( 'JViewLegacy')) { eval( 'class J2WinView extends JViewLegacy {};') ; }
else {

jimport('joomla.application.component.view');
if ( class_exists( 'JView')) { eval( 'class J2WinView extends JView{};') ; }
}
}

if ( !class_exists( 'J2WinUtility')) {
jimport('joomla.utilities.utility');
class J2WinUtility extends JUtility {
public static function getToken2Win( $forceNew = false) {

if ( method_exists( 'JUtility', 'getToken')) { return JUtility::getToken( $forceNew); }

else if ( method_exists( 'JSession', 'getFormToken')) { return JSession::getFormToken( $forceNew); }
return 0;
}
public static function isOSWindows() {
return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
}
} 
}

if ( !class_exists( 'J2WinToolBarHelper')) {
require_once( JPATH_ROOT.'/administrator/includes/toolbar.php');

if ( method_exists( 'JToolBarHelper', 'customX')) {
class J2WinToolBarHelper extends JToolBarHelper {}
}

else {
class J2WinToolBarHelper extends JToolBarHelper {
public static function customX($task = '', $icon = '', $iconOver = '', $alt = '', $listSelect = true)
{
self::custom($task, $icon, $iconOver, $alt, $listSelect);
}
public static function addNewX($task = 'add', $alt = 'JTOOLBAR_NEW')
{
self::addNew($task, $alt);
}
public static function editListX($task = 'edit', $alt = 'JTOOLBAR_EDIT')
{
self::editList($task, $alt);
}
}
}
}

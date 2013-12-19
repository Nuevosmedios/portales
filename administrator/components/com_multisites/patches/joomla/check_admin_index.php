<?php
// file: check_admin_index.php.
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

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );
function jms2win_checkAdminIndex_olderVersion()
{

if ( version_compare( JVERSION, '1.6') >= 0) {
return false;
}

$filename = JPath::clean( JPATH_COMPONENT_ADMINISTRATOR.DS.'install.xml');
jimport( 'joomla.application.helper');
if ($data = JApplicationHelper::parseXMLInstallFile($filename)) {

if (isset($data['version']) && !empty($data['version'])) {
$version = explode( '.', $data['version']);
}
}

if ( empty( $version)
|| ((int)$version[0] <= 1 && (int)$version[1] <= 1 && (int)$version[2] < 17)
)
{
return true;
}
return false;
}


function jms2win_checkAdminIndex( $model, $file, $args=array())
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[NOK]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'MULTISITES_');
if ($pos === false) $wrapperIsPresent = false;
else {
$wrapperIsPresent = true;
}
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';

if ( jms2win_checkAdminIndex_olderVersion())
{
$result .= JText::_( '<font color="red" size="3"><b>IT IS REQUIRED TO UPDATE WITH A JMS VERSION 1.1.17 or HIGHER.</b></font>');
$result .= JText::_( '|You can <font color="red">go on wwww.jms2win.com, login with your account and go in the menu "Get Latest Version".</font>');
$result .= JText::_( '|<font color="red">Select the Joomla Multi Sites and click on the "Get Latest Version" button in the top right, to receive a new download ID.</font>');
$result .= JText::_( '|This update is required because it also needs an update of the JMS core');
$result .= '|';
}
$result .= JText::_( 'Use the slave site deployed directory as administrator directory when present. Otherwise use the master website directory');
$result .= '|[ACTION]';
$result .= '|Replace 2 lines by 28 lines in aim to use the slave site deploy directory instead of the master directory';
$result .= '|This allow for example to manage the specific media or image directory from the back-end';
}

else {

$pos = strpos( $str, 'MULTISITES_ID_PATH');
if ($pos === false) {
$rc = '[NOK]';
$result .= JText::_( 'Use the slave site deployed directory as administrator directory when present. Otherwise use the master website directory');
$result .= '|[ACTION]';
$result .= '|Replace the previous patch <1.2.35 by a new one. This replace 26 lines by 28 lines in aim to use the slave site deploy directory instead of the master directory';
$result .= '|This allow for example to manage the specific media or image directory from the back-end';
}
else {

if ( !empty( $args['version'])) {
$pos = strpos( $str, $args['version']);
if ($pos === false) {
$rc = '[NOK]';
$result .= JText::_( 'An update is available.');
$result .= '|[ACTION]';
$result .= '|Install the new version';
}
}
}
}
return $rc .'|'. $result;
}


function jms2win_actionAdminIndex( $model, $file)
{

if ( jms2win_checkAdminIndex_olderVersion())
{
return true;
}


$filename = JPath::clean( JPATH_ROOT.DS.'includes'.DS.'multisites.php');
if ( !file_exists( $filename)) {
return true;
}

$str = file_get_contents( $filename);
$pos = strpos( $str, "'JMS2WIN_VERSION'");
if ($pos === false) {

$model->_deployPatches();
}

$str = file_get_contents( $filename);
$pos = strpos( $str, "'JMS2WIN_VERSION'");
if ($pos === false) {
$mainframe = &JFactory::getApplication();
$msg = JText::_( 'Dependency with "multisites.php" is not present');
$mainframe->enqueueMessage($msg, 'error');
return true;
}



include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( 'patch_admin_index.php');
if ( $patchStr === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}

$p1 = strpos( $content, 'MULTISITES_');
if ( $p1 === false) {}
else {

$content = jms2win_removePatch( $content);
}





$p1 = strpos( $content, '\'JPATH_BASE\'');
if ( $p1 === false) {
return false;
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

$p2 = strpos( $content, 'DIRECTORY_SEPARATOR', $p1);
if ( $p2 === false) {
return false;
}

for ( $p3=$p2; $content[$p3] != "\n"; $p3++);


$result = substr( $content, 0, $p0)
. $patchStr
. substr( $content, $p3+1);

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

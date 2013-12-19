<?php
// file: check_masterconfig.php.
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


function jms2win_checkMasterConfig( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[NOK]|File Not Found';
}
$str = file_get_contents( $filename);
$result = "";
$rc = '[OK]';

$pos = strpos( $str, 'MULTISITES_');
if ($pos === false) {
$rc = '[NOK]';
$result .= JText::_( 'The routing wrapper is not present in this MASTER configuration file.');
$result .= '|[ACTION]';
$result .= '|Add 16 lines containing the routing wrapper to the slave site.';
}
else {

$pos = strpos( $str, 'Jms2Win::matchSlaveSite');
if ($pos === false) {
$rc = '[NOK]';
$result .= JText::_( 'The routing wrapper Version 1.0.x is present in this MASTER configuration file and must be replaces by the version 1.1.x.');
$result .= '|[ACTION]';
$result .= '|Replace the older wrapper (12 lines) by the new wrapper (16 lines) that add routing with domain containing sub-directories.';
}
else {

$p2 = strpos( $str, 'JPATH_ROOT');
if ($p2 === false) {
$rc = '[NOK]';
$result .= JText::_( 'The routing wrapper Version 1.1.x is present in this MASTER configuration file and must be replaces by the version 1.2.14 or higher.');
$result .= '|[ACTION]';
$result .= '|Replace 2 lines to use JPATH_ROOT when present.';
}

else {
$p2 = strpos( $str, 'Jms2Win::matchSlaveSite', $pos+1);
if ($p2 === false) {}
else {
$rc = '[NOK]';
$result .= JText::_( 'The double Multisites routing wrapper is detected in the MASTER configuration file and must be fixed.');
$result .= '|[ACTION]';
$result .= '|You can either go in your <a href="index.php?option=com_config">global configuration</a> and resave the configuration.';
$result .= '|OR try installing the patch here.';
$result .= '|If you just have this file in error, we recommend that you try resaving the <a href="index.php?option=com_config">Global Configuration</a> first.';
}
}
}
}
return $rc .'|'. $result;
}


function jms2win_actionMasterConfig( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( 'patch_masterconfig.php');
if ( $patchStr === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}






$p0 = strpos( $content, 'MULTISITES_ID');
if ( $p0 === false) {}
else {
$p1 = strpos( $content, 'Jms2Win::matchSlaveSite');
if ( $p1 === false) {

for ( ; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

$p2 = strpos( $content, 'class ', $p0);
if ( $p2 === false) {
return false;
}

$p3 = strpos( $content, '}', $p2);
if ( $p3 === false) {
return false;
}

$content = substr( $content, 0, $p0)
. substr( $content, $p2, $p3-$p2)
. substr( $content, $p3+1)
;
}
else {
$p1 = strpos( $content, 'JPATH_ROOT');
if ( $p1 === false) {

$content = jms2win_removePatch( $content);
}

$p0 = strpos( $content, 'MULTISITES_ID');
if ( $p0 === false) {}
else {


$p2 = strpos( $content, 'class JConfig', $p0);
if ( $p2 === false) {
return false;
}

$p3 = strpos( $content, '}', $p2);
if ( $p3 === false) {
return false;
}

$content = '<'.'?php'."\n"
. substr( $content, $p2, $p3-$p2+1)
. "\n"
. '?'.'>'."\n"
;
}
}
}



$p0 = strpos( $content, 'class');
if ( $p0 === false) {
return false;
}

for ( ; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

$p1 = strpos( $content, '?'.'>', $p0);
if ( $p1 === false) {


$p1 = strlen( $content);
}
$closeIf = "//_jms2win_begin\n"
. "}\n"
. "//_jms2win_end\n"
;


$result = substr( $content, 0, $p0)
. $patchStr
. substr( $content, $p0, $p1-$p0)
. $closeIf
. substr( $content, $p1);


jimport('joomla.client.helper');
JClientHelper::setCredentialsFromRequest('ftp');
$ftp = JClientHelper::getCredentials('ftp');

jimport('joomla.filesystem.path');
if (!$ftp['enabled'] && JPath::isOwner($filename) && !JPath::setPermissions($filename, '0644')) {
JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php writable');
}

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}

if ( !$ftp['enabled'] && JPath::isOwner($filename) && !JPath::setPermissions($filename, '0444')) {
JError::raiseNotice('SOME_ERROR_CODE', 'Could not make configuration.php unwritable');
}
return true;
}

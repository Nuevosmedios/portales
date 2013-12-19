<?php
// file: check_savecfg.php.
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


function jms2win_checkACESEFSaveCfg( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[IGNORE]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'MULTISITES_ID');
if ($pos === false) {

$pos = strpos( $str, 'JFile::write');
if ($pos === false) {

return '[IGNORE]|File Not Found';
}

else {
$wrapperIsPresent = false;
}
}
else {
$wrapperIsPresent = true;
}
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'The Multi Sites specific "configuration.php" saving for each websites is not present.');
$result .= '|[ACTION]';
$result .= '|Replace 6 lines by 34 lines to save specific configuration.php file for each slave site.';
}
return $rc .'|'. $result;
}


function jms2win_actionACESEFSaveCfg_old( $model, $file)
{
$parts = explode( DS, dirname(__FILE__));
array_pop( $parts );
$patch_dir = implode( DS, $parts );
include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( '..' .DS. 'acesef' .DS. 'patch_savecfg.php');
if ( $patchStr === false) {
return false;
}
$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p1 = strpos( $content, '_save_configuration');
if ( $p1 === false) {
return false;
}

$p2 = strpos( $content, '->loadArray', $p1);
if ( $p2 === false) {
return false;
}

$p3 = strpos( $content, 'db->query', $p2);
if ( $p3 === false) {
return false;
}

$p4 = strpos( $content, "\n", $p3);
if ( $p4 === false) {
return false;
}
$p4++;

$p6 = strpos( $content, 'JFile::write', $p4);
if ( $p6 === false) {
return false;
}

$p7 = strpos( $content, "\n", $p6);
if ( $p3 === false) {
return false;
}


$result = substr( $content, 0, $p4)
. $patchStr
. substr( $content, $p7)
;

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}


function jms2win_actionACESEFSaveCfg_15( $model, $file)
{
$parts = explode( DS, dirname(__FILE__));
array_pop( $parts );
$patch_dir = implode( DS, $parts );
include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( '..' .DS. 'acesef' .DS. 'patch_savecfg15.php');
if ( $patchStr === false) {
return false;
}
$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p1 = strpos( $content, 'function save');
if ( $p1 === false) {
return false;
}

$p3 = strpos( $content, 'AceDatabase::query', $p1);
if ( $p3 === false) {
return false;
}

$p4 = strpos( $content, "\n", $p3);
if ( $p4 === false) {
return false;
}
$p4++;

$p6 = strpos( $content, 'JFile::write', $p4);
if ( $p6 === false) {
return false;
}

$p7 = strpos( $content, "\n", $p6);
if ( $p3 === false) {
return false;
}


$result = substr( $content, 0, $p4)
. $patchStr
. substr( $content, $p7)
;

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}


function jms2win_actionACESEFSaveCfg( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}

$pos = strpos( $content, 'AcesefConfig');
if ( $pos === false) {
return jms2win_actionACESEFSaveCfg_old( $model, $file);
}
return jms2win_actionACESEFSaveCfg_15( $model, $file);
}
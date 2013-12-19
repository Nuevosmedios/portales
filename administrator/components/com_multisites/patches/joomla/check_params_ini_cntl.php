<?php
// file: check_params_ini_cntl.php.
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


function jms2win_checkParams_ini_cntl( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[NOK]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'MULTISITES_');
if ($pos === false) $wrapperIsPresent = false;
else {
$pos = strpos( $str, '//_jms2win_begin v1.2.37');
if ($pos === false) {
$wrapperIsPresent = false;

$filename = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'backup'.DS.$file);
$file_bak = dirname( $filename) .DS . 'bak.'. basename($filename);
if ( JFile::exists( $filename) && !JFile::exists( $file_bak)) {
JFile::copy( $filename, $file_bak);
}
}
else {
$wrapperIsPresent = true;
}
}
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'The customisation of "params.ini" file for the slave sites is not present into the template controller');
$result .= '|[ACTION]';
$result .= '|Replace the statement that READ  the "params.ini" file name by 10 lines that allow reading a specific slave site "params_[id].ini" file';
$result .= '|Replace the statement that WRITE the "params.ini" file name by 13 lines that allow writing a specific slave site "params_[id].ini" file';
$result .= '|Add 18 lines 11 times to allow using a specific template "basedir" directory when a specific template (themes) folder is specified';
}
return $rc .'|'. $result;
}


function jms2win_actionParams_ini_cntl( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr_1 = jms2win_loadPatch( 'patch_params_ini_cntl_1.php');
if ( $patchStr_1 === false) {
return false;
}
$patchStr_2 = jms2win_loadPatch( 'patch_params_ini_cntl_2.php');
if ( $patchStr_2 === false) {
return false;
}
$patchStr_3 = jms2win_loadPatch( 'patch_params_ini_cntl_3.php');
if ( $patchStr_3 === false) {
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
$pos = strpos( $content, '//_jms2win_begin v1.2.36');
if ( $p1 === false) { $jms1236 = false; }
else { $jms1236 = true;}

$content = jms2win_removePatch( $content);

if ( $jms1236) {

$p1 = strpos( $content, 'function previewTemplate');

if ( $p1 === false) {

$file_bak = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'backup'.DS.$file);
$file_bak = dirname( $file_bak) .DS . 'bak.'. basename($file_bak);
if ( JFile::exists( $file_bak)) {
$content = file_get_contents( $file_bak);
if ( $content === false) {
return false;
}
}

else {
$file_bak = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'backup_on_install'.DS.$file);
$content = file_get_contents( $file_bak);
if ( $content === false) {
return false;
}
}

$p1 = strpos( $content, 'MULTISITES_');
if ( $p1 === false) {}
else {
$content = jms2win_removePatch( $content);
}
}
}
}





$p1 = strpos( $content, '\'params.ini\'');
if ( $p1 === false) {
return false;
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);

for ( $p2=$p1; $content[$p2] != "\n"; $p2++);

$p4 = strpos( $content, '\'params.ini\'', $p2);
if ( $p4 === false) {
return false;
}

for ( $p3=$p4; $p3 > 0 && $content[$p3] != "\n"; $p3--);
$p0++;

for ( $p5=$p4; $content[$p5] != "\n"; $p5++);



$pos = strpos( $content, 'MULTISITES_');
if ( $pos === false) {
$res_1 = substr( $content, 0, $p0)
. $patchStr_1
. substr( $content, $p2+1, $p3-$p2)
. $patchStr_2
. substr( $content, $p5+1)
;
}
else {
$res_1 = $content;
}

$prev_pos = 0;
$result = '';
while( true)
{

$p1 = strpos( $res_1, 'JApplicationHelper::getClientInfo', $prev_pos);
if ( $p1 === false) {
$result .= substr( $res_1, $prev_pos);
break;
}

for ( $p2=$p1; $res_1[$p2] != "\n"; $p2++);
$result .= substr( $res_1, $prev_pos, $p2-$prev_pos+1)
. $patchStr_3
;
$prev_pos = $p2+1;
}

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

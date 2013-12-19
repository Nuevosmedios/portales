<?php
// file: check_saveacecfg.php.
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


function jms2win_checkACESEFSaveAceCfg( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[IGNORE]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'AcesefConfig');
if ( $pos === false) {
return '[IGNORE]|File Not Found';
}

$pos = strpos( $str, 'MULTISITES_ID');
if ($pos === false) $wrapperIsPresent = false;
else $wrapperIsPresent = true;
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'Add the Multisites wrapper for the Global Configuration and Ace Configuration that might be modified by this source');
$result .= '|[ACTION]';

$pos = strpos( $str, 'AcesefUtility::storeConfig');
if ($pos === false) {
$result .= '|Replace 3 statements, that may write updates in the configurations, by 31+(2*32) lines containing the routing wrapper to the slave site.';
}

else {
$result .= '|Replace 1 statements, that may write updates in the configurations, by 31 lines containing the routing wrapper to the slave site.';
}
}
return $rc .'|'. $result;
}


function jms2win_actionACESEFSaveAceCfg( $model, $file)
{
$parts = explode( DS, dirname(__FILE__));
array_pop( $parts );
$patch_dir = implode( DS, $parts );
include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
$patchStr_1 = jms2win_loadPatch( '..' .DS. 'acesef' .DS. 'patch_acecfg_1.php');
if ( $patchStr_1 === false) {
return false;
}
$patchStr_2 = jms2win_loadPatch( '..' .DS. 'acesef' .DS. 'patch_acecfg_2.php');
if ( $patchStr_2 === false) {
return false;
}
$patchStr_3 = $patchStr_2;

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}






$p1 = strpos( $content, 'JFile::write');
if ( $p1 === false) {
return false;
}

for ( $p0 = $p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

for ( $p2=$p1; $content[$p2] != "\n"; $p2++);


$p4 = strpos( $content, 'JFile::write', $p2);
if ( $p4 === false) {
$p4 = strpos( $content, 'AcesefUtility::storeConfig', $p2);
if ( $p4 === false) {
return false;
}
$patchStr_2='';
}

for ( $p3 = $p4; $p3 > 0 && $content[$p3] != "\n"; $p3--);
$p3++;

for ( $p5=$p4; $content[$p5] != "\n"; $p5++);
if ( empty( $patchStr_2)) {
$patchStr_2 = substr( $content, $p3, $p5-$p3);
}


$p7 = strpos( $content, 'JFile::write', $p5);
if ( $p7 === false) {
$p7 = strpos( $content, 'AcesefUtility::storeConfig', $p5);
if ( $p7 === false) {
return false;
}
$patchStr_3='';
}

for ( $p6 = $p7; $p6 > 0 && $content[$p6] != "\n"; $p6--);
$p6++;

for ( $p8=$p7; $content[$p8] != "\n"; $p8++);
if ( empty( $patchStr_3)) {
$patchStr_3 = substr( $content, $p6, $p8-$p6);
}


$result = substr( $content, 0, $p0)
. $patchStr_1
. substr( $content, $p2, $p3-$p2)
. $patchStr_2
. substr( $content, $p5, $p6-$p5)
. $patchStr_3
. substr( $content, $p8)
;

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

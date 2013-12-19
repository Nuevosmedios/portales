<?php
// file: check_jconfig16.php.
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


function jms2win_checkJConfig16( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[NOK]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'MULTISITES_');
if ($pos === false) $wrapperIsPresent = false;
else $wrapperIsPresent = true;;
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'The wrapper for the Master configuration is not present in Joomla administration');
$result .= '|[ACTION]';
$result .= '|Add 28 lines containing the wrapper to insert into the master configuration.php file';
$result .= '|Update 1 line to save the appropriate configuration content';
}
return $rc .'|'. $result;
}


function jms2win_actionJConfig16( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr_1 = jms2win_loadPatch( 'patch_jconfig16_1.php');
if ( $patchStr_1 === false) {
return false;
}
$patchStr_2 = jms2win_loadPatch( 'patch_jconfig16.php');
if ( $patchStr_2 === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p2 = strpos( $content, '$config->toString');
if ( $p2 === false) {
return false;
}

for ( $p0=$p2; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

$p3 = strpos( $content, '{', $p2);
if ( $p3 === false) {
return false;
}

for ( $p6=$p3; $content[$p6] != "\n"; $p6++);

$j254 = strpos( $content, 'function writeConfigFile');
if ( $j254 === false) {



$p11 = strpos( $content, 'JFile::write', $p6);
if ( $p11 === false) {
return false;
}

for ( $p10=$p11; $p10 > 0 && $content[$p10] != "\n"; $p10--);
$p10++;

$p12 = strpos( $content, '$config->toString', $p11);
if ( $p12 === false) {
return false;
}

$p13 = strpos( $content, 'JConfig', $p12);
if ( $p13 === false) {
return false;
}

$p14 = strpos( $content, ')', $p13+1);
if ( $p14 === false) {
return false;
}

$p15 = strpos( $content, ')', $p14+1);
if ( $p15 === false) {
return false;
}

for ( $p16=$p15; $content[$p16] != "\n"; $p16++);


$result = substr( $content, 0, $p0)
. $patchStr_1
. substr( $content, $p6+1, $p10-($p6+1))
. $patchStr_2
. substr( $content, $p16+1)
;
}

else {


$result = substr( $content, 0, $p0)
. $patchStr_1
. substr( $content, $p6+1)
;
}

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

<?php
// file: check_libsession.php.
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


function jms2win_checkLibSession( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[NOK]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'MULTISITES_COOKIE_DOMAINS');
if ($pos === false) {

$pos = strpos( $str, '//_jms2win');
if ($pos === false) $wrapperIsPresent = false;
else $wrapperIsPresent = true;
}
else $wrapperIsPresent = true;
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'The single sign-in patch for sub-domain is not present');
$result .= '|[ACTION]';
$result .= '|Add 12 lines and replace 1 line by 15 lines to accept that sub-domain share the same session information for a single sign-in';
}
return $rc .'|'. $result;
}


function jms2win_actionLibSession15( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr_1 = jms2win_loadPatch( 'patch_libsession_1.php');
if ( $patchStr_1 === false) {
return false;
}
$patchStr_2 = jms2win_loadPatch( 'patch_libsession_2.php');
if ( $patchStr_2 === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p1 = strpos( $content, 'session_start');
if ( $p1 === false) {
return false;
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

$p3 = strpos( $content, 'setcookie', $p1);
if ( $p3 === false) {
return false;
}

for ( $p2=$p3; $p2 > 0 && $content[$p2] != "\n"; $p2--);
$p2++;

for ( $p4=$p3; $content[$p4] != "\n"; $p4++);
$p4++;

$patchStr_3 = $patchStr_1;
$p6 = strpos( $content, 'session_start', $p4);
if ( $p6 === false) {

$p6 = strpos( $content, 'session_regenerate_id', $p4);
if ( $p6 === false) {
return false;
}
$patchStr_3 = '';
}

for ( $p5=$p6; $p5 > 0 && $content[$p5] != "\n"; $p5--);
$p5++;


$result = substr( $content, 0, $p0)
. $patchStr_1
. substr( $content, $p0, $p2-$p0)
. $patchStr_2
. substr( $content, $p4, $p5-$p4)
. $patchStr_3
. substr( $content, $p5+1);

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}


function jms2win_actionLibSession16( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr_1 = jms2win_loadPatch( 'patch_libsession16_1.php');
if ( $patchStr_1 === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p1 = strpos( $content, 'session_start');
if ( $p1 === false) {
return false;
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

$patchStr_3 = $patchStr_1;
$p6 = strpos( $content, 'session_start', $p1+1);
if ( $p6 === false) {

$p6 = strpos( $content, 'session_regenerate_id', $p4);
if ( $p6 === false) {
return false;
}
$patchStr_3 = '';
}

for ( $p5=$p6; $p5 > 0 && $content[$p5] != "\n"; $p5--);
$p5++;


$result = substr( $content, 0, $p0)
. $patchStr_1
. substr( $content, $p0, $p5-$p0)
. $patchStr_3
. substr( $content, $p5+1);

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}


function jms2win_actionLibSession( $model, $file)
{
if ( version_compare( JVERSION, '1.6') >= 0) {
return jms2win_actionLibSession16( $model, $file);
}
else {
return jms2win_actionLibSession15( $model, $file);
}
}
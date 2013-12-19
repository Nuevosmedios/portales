<?php
// file: check_legacy15getinstance.php.
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


function jms2win_checkLegacy15GetInstance( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[NOK]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'function &getInstance()');
if ($pos === false) $legacyIsPresent = false;
else {
$legacyIsPresent = true;
if ( version_compare( JVERSION, '3.0') >= 0) {}

else if ( strpos( $str, '$parentXmlfiles') !== false) {

if ( strpos( $str, '//_jms2win') === false) {
$legacyIsPresent = false;
}
}
}
$result = "";
$rc = '[OK]';
if ( !$legacyIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'Install the Joomla 1.5 legacy API for the getinstance() to return a reference.');
if ( version_compare( JVERSION, '3.0') >= 0) {}
else {
$result .= JText::_( 'Also remove the bug tracker 30206');
}
$result .= '|[ACTION]';
$result .= '|Replace "function getInstance()" by "function &getInstance()"';
$result .= '|This allow get the reference of the instance created instead of a copy.';
if ( version_compare( JVERSION, '3.0') >= 0) {}
else {
$result .= '|When present, this remove the bug tracker 30206';
$result .= '|http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=30206';
}
}
return $rc .'|'. $result;
}


function jms2win_actionLegacy15GetInstance( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( 'patch_legacy15getinstance.php');
if ( $patchStr === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}


$content = str_replace( 'function getInstance()', 'function &getInstance()', $content);
if ( version_compare( JVERSION, '3.0') >= 0) {
$result = $content;
}
else {

$p1 = strpos( $content, '$parentXmlfiles');
if ( $p1 === false) {
$result = $content;
}
else {

for ( $p0 = $p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

for ( $p2=$p1; $content[$p2] != "\n"; $p2++);


$result = substr( $content, 0, $p0)
. $patchStr
. substr( $content, $p2)
;
}
}

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

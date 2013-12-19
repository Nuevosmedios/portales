<?php
// file: check_libconfig.php.
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


function jms2win_checkJRELibCfg( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[IGNORE]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'MULTISITES_ID');
if ($pos === false) $wrapperIsPresent = false;
else $wrapperIsPresent = true;
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'The wrapper that must be added in the JRE master configuration is not present.');
$result .= '|[ACTION]';
$result .= '|Add 19 lines to write the wrapper into the master JRE configuration file.';
}
return $rc .'|'. $result;
}


function jms2win_actionJRELibCfg( $model, $file)
{
$parts = explode( DS, dirname(__FILE__));
array_pop( $parts );
$patch_dir = implode( DS, $parts );
include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
$patchStr_1 = jms2win_loadPatch( '..' .DS. 'jrecache' .DS. 'patch_libconfig_1.php');
if ( $patchStr_1 === false) {
return false;
}
$patchStr_2 = jms2win_loadPatch( '..' .DS. 'jrecache' .DS. 'patch_libconfig_2.php');
if ( $patchStr_2 === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p0 = strpos( $content, 'saveConfig');
if ( $p0 === false) {
return false;
}

$p1 = strpos( $content, '"<'.'?php', $p0);
if ( $p1 === false) {
return false;
}

$p2 = strpos( $content, "\n", $p1);
if ( $p2 === false) {
return false;
}
$p2++;

$p4 = strpos( $content, '"?'.'>"', $p2);
if ( $p4 === false) {
return false;
}

for ( $p3=$p4; $p3 > 0 && $content[$p3] != "\n"; $p3--);
$p3++;


$result = substr( $content, 0, $p2)
. $patchStr_1
. substr( $content, $p2, $p3-$p2)
. $patchStr_2
. substr( $content, $p3)
;

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

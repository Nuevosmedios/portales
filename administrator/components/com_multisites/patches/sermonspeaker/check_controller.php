<?php
// file: check_controller.php.
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


function jms2win_checkSermonController( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[IGNORE]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'saveConfig()');
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
$result .= JText::_( 'The configuration wrapper is not present in the MASTER SermonSpeaker configuration menu.');
$result .= '|[ACTION]';
$result .= '|Add 54 lines containing the two routing wrapper to redirect on the two configuration files specific to each slave site.';
}
return $rc .'|'. $result;
}


function jms2win_actionSermonController( $model, $file)
{
$parts = explode( DS, dirname(__FILE__));
array_pop( $parts );
$patch_dir = implode( DS, $parts );
include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
$patchStr_1 = jms2win_loadPatch( '..' .DS. 'sermonspeaker' .DS. 'patch_controller_1.php');
if ( $patchStr_1 === false) {
return false;
}
$patchStr_2 = jms2win_loadPatch( '..' .DS. 'sermonspeaker' .DS. 'patch_controller_2.php');
if ( $patchStr_2 === false) {
return false;
}
$patchStr_2b = jms2win_loadPatch( '..' .DS. 'sermonspeaker' .DS. 'patch_controller_2b.php');
if ( $patchStr_2b === false) {
return false;
}
$patchStr_3 = jms2win_loadPatch( '..' .DS. 'sermonspeaker' .DS. 'patch_controller_3.php');
if ( $patchStr_3 === false) {
return false;
}
$patchStr_4 = jms2win_loadPatch( '..' .DS. 'sermonspeaker' .DS. 'patch_controller_4.php');
if ( $patchStr_4 === false) {
return false;
}
$patchStr_4b = jms2win_loadPatch( '..' .DS. 'sermonspeaker' .DS. 'patch_controller_4b.php');
if ( $patchStr_4b === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p0 = strpos( $content, 'saveConfig()');
if ( $p0 === false) {
return false;
}

$p2 = strpos( $content, '$sermonresults', $p0);
if ( $p2 === false) {
return false;
}

for ( $p1 = $p2; $p1 > 0 && $content[$p1] != "\n"; $p1--);
$p1++;

$p4 = strpos( $content, 'class sermonConfig', $p2);
if ( $p4 === false) {
return false;
}

for ( $p3 = $p4; $p3 > 0 && $content[$p3] != "\n"; $p3--);
$p3++;

$p6 = strpos( $content, '} \n?', $p4);
if ( $p6 === false) {
return false;
}

for ( $p5 = $p6; $p5 > 0 && $content[$p5] != "\n"; $p5--);
$p5++;

$p8 = strpos( $content, '$cache', $p6);
if ( $p8 === false) {
return false;
}

for ( $p7 = $p8; $p7 > 0 && $content[$p7] != "\n"; $p7--);
$p7++;

$p10 = strpos( $content, 'class sermonCastConfig', $p8);
if ( $p10 === false) {
return false;
}

for ( $p9 = $p10; $p9 > 0 && $content[$p9] != "\n"; $p9--);
$p9++;

$p12 = strpos( $content, '} \n?', $p10);
if ( $p12 === false) {
return false;
}

for ( $p11 = $p12; $p11 > 0 && $content[$p11] != "\n"; $p11--);
$p11++;


$result = substr( $content, 0, $p1)
. $patchStr_1
. substr( $content, $p1, $p3-$p1)
. $patchStr_2
. substr( $content, $p3, $p5-$p3)
. $patchStr_2b
. substr( $content, $p5, $p7-$p5)
. $patchStr_3
. substr( $content, $p7, $p9-$p7)
. $patchStr_4
. substr( $content, $p9, $p11-$p9)
. $patchStr_4b
. substr( $content, $p11)
;

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

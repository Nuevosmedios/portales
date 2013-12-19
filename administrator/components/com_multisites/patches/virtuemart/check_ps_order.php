<?php
// file: check_ps_order.php.
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


function jms2win_checkVMPlgUpdStatus( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[IGNORE]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'JPluginHelper::importPlugin');
if ($pos === false) $wrapperIsPresent = false;
else $wrapperIsPresent = true;
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'The code to call the Plugin VirtueMart onOrderStatusUpdate is not present.');
$result .= '|[ACTION]';
$result .= '|Add 3 lines containing the call to a VirtueMart plugin onOrderStatusUpdate.';
}
return $rc .'|'. $result;
}


function jms2win_actionVMPlgUpdStatus( $model, $file)
{
$parts = explode( DS, dirname(__FILE__));
array_pop( $parts );
$patch_dir = implode( DS, $parts );
include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( '..' .DS. 'virtuemart' .DS. 'patch_ps_order.php');
if ( $patchStr === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}







$prev_pos = 0;
while( true) {

$p1 = strpos( $content, 'ENABLE_DOWNLOADS', $prev_pos);
if ( $p1 === false) {
return false;
}

$p2 = strpos( $content, "'1'", $p1);
if ( $p2 === false) {
return false;
}

$p3 = strpos( $content, ")", $p2);
if ( $p3 === false) {
return false;
}
$str = trim( substr( $content, $p2+3, $p3-($p2+3)));
if ( !empty( $str)) {
$prev_pos = $p3;
}
else {

break;
}
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;


$result = substr( $content, 0, $p0)
. $patchStr
. substr( $content, $p0);

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

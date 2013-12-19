<?php
// file: check_module_tpl.php.
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


function jms2win_checkModule_Tpl( $model, $file)
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
$result .= JText::_( 'The usage of specific themes folder is not present in the module management');
$result .= '|[ACTION]';
$result .= '|Add 17 lines to allow using a specific template directory when specified for a slave site';
}

else {

$pos = strpos( $str, 'MULTISITES_ID_PATH');
if ($pos === false) {
$rc = '[NOK]';
$result .= JText::_( 'The usage of specific themes folder is not present in the module management');
$result .= '|[ACTION]';
$result .= '|Replace the previous patch <1.2.35 by a new one. This Add 18 lines to allow using a specific template directory when specified for a slave site';
}
}
return $rc .'|'. $result;
}


function jms2win_actionModule_Tpl( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( 'patch_module_tpl.php');
if ( $patchStr === false) {
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

$content = jms2win_removePatch( $content);
}





$p1 = strpos( $content, 'JApplicationHelper::getClientInfo');
if ( $p1 === false) {
return false;
}

for ( $p2=$p1; $content[$p2] != "\n"; $p2++);
$p2++;


$result = substr( $content, 0, $p2)
. $patchStr
. substr( $content, $p2+1);

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

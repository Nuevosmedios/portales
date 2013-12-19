<?php
// file: check_tpl_basedir.php.
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


function jms2win_checkTpl_basedir( $model, $file)
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
$result .= JText::_( 'The install "template" does not contain the patch that allow the installion of a template into a specific folder');
$result .= '|[ACTION]';
$result .= '|Add 35 lines to provide another "basedir" folder when a specific "themes" folder is specified';
}
return $rc .'|'. $result;
}


function jms2win_actionTpl_basedir( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr_1 = jms2win_loadPatch( 'patch_tpl_basedir_1.php');
if ( $patchStr_1 === false) {
return false;
}
$patchStr_2 = jms2win_loadPatch( 'patch_tpl_basedir_2.php');
if ( $patchStr_2 === false) {
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





$p1 = strpos( $content, 'JFolder::folders');
if ( $p1 === false) {
return false;
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);

$p2 = strpos( $content, '$template->baseDir', $p1);
if ( $p2 === false) {
return false;
}

for ( $p3=$p2; $content[$p3] != "\n"; $p3++);


$p4 = strpos( $content, 'getClientInfo', $p3);

$p6 = strpos( $content, 'JFolder::folders', $p4);
if ( $p6 === false) {
return false;
}

for ( $p5=$p6; $p5 > 0 && $content[$p5] != "\n"; $p5--);

$p7 = strpos( $content, '$template->baseDir', $p6);
if ( $p7 === false) {
return false;
}

for ( $p8=$p7; $content[$p8] != "\n"; $p8++);


$result = substr( $content, 0, $p0)
. $patchStr_1
. substr( $content, $p3+1, $p5-$p3)
. $patchStr_2
. substr( $content, $p8+1);

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

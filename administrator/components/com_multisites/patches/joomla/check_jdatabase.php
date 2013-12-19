<?php
// file: check_jdatabase.php.
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


function jms2win_checkJDatabase( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[NOK]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, '//_jms2win');
if ($pos === false) $wrapperIsPresent = false;
else {
$pos = strpos( $str, 'protected function replacePrefix');
if ($pos === false) { $wrapperIsPresent = true; }
else { $wrapperIsPresent = false; }
}
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'Add a get instance on foreign adapters after that joomla 1.7 protected the access to the constructor of the adapters.');
$result .= JText::_( 'Make piblic the protected function replacePrefix.');
$result .= '|[ACTION]';
$result .= '|Add 1 line to get new adapter instance and update 1 line to make the protected replacePrefix as public.';
}
return $rc .'|'. $result;
}


function jms2win_actionJDatabase( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( 'patch_jdatabase.php');
if ( $patchStr === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}

$content = jms2win_removePatch( $content);




$content = str_replace( 'protected function replacePrefix', 'public function replacePrefix', $content);

$p0 = strpos( $content, 'class JDatabase');
if ( $p0 === false) {
return false;
}

for ( $p2=$p0; $content[$p2] != "{"; $p2++);

for ( $p3=$p2; $content[$p3] != "\n"; $p3++);
$p3++;


$result = substr( $content, 0, $p3)
. $patchStr
. substr( $content, $p3);

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

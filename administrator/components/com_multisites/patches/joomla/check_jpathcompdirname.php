<?php
// file: check_jpathcompdirname.php.
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


function jms2win_checkJPathCompDirname( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[IGNORE]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'MULTISITES_ID');
if ($pos === false) {

if ( strpos( $str, 'dirname') !== false) {
$wrapperIsPresent = false;
}
else {
return '[IGNORE]|dirname pattern is not present';
}
}
else {
$wrapperIsPresent = true;
}
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'Fix the JPATH_COMPONENT computation used by ajax call. Replace the dirname by JPATH_BASE./components/XXXXX path.');
$result .= '|[ACTION]';
$result .= '|Replace 1 line containing the new define value.';
}
return $rc .'|'. $result;
}


function jms2win_actionJPathCompDirname( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( 'patch_jpathcompdirname.php');
if ( $patchStr === false) {
return false;
}

$path = $file;
while( !empty( $path) && basename( dirname( dirname( $path))) != 'components') {
$path = dirname( $path);
}
if ( basename( dirname( dirname( $path))) == 'components') {
$option = basename( dirname( $path));
}
if ( empty( $option)) {
$option = basename( dirname( $file));
}
$patchStr = str_replace( '{option}', $option, $patchStr);

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p1 = strpos( $content, 'dirname');
if ( $p1 === false) {
return false;
}

for ( $p0 = $p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

for ( $p2=$p1; $content[$p2] != "\n"; $p2++);
$p2++;


$result = substr( $content, 0, $p0)
. $patchStr
. substr( $content, $p2)
;

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

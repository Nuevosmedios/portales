<?php
// file: check_index.php.
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


function jms2win_checkJREIndex( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[IGNORE]|File Not Found';
}
$str = file_get_contents( $filename);

$jrepos = strpos( $str, '_JRE_FRAMEWORK');
if ($jrepos === false) {
return '[IGNORE]|File Not Found';
}
$result = "";

$pos = strpos( $str, "'defines.php'");
if ($pos === false) {
$rc = '[NOK]';
$result .= JText::_( 'Application error. The word \'define.php\' is not found.');
$result .= '|[ACTION]';
$result .= '|contact the support.';
}
else {
$rc = '[OK]';



if ( $jrepos < $pos) {
$rc = '[NOK]';
$result .= JText::_( 'The JRE patch must be placed after the joomla initialisation to allow JMS detect the site ID.');
$result .= '|[ACTION]';
$result .= '|Move 3 standard joomla initialisation lines before the JRE patches.';
}
}
return $rc .'|'. $result;
}


function jms2win_actionJREIndex( $model, $file)
{

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p1 = strpos( $content, '_JRE_FRAMEWORK');
if ( $p1 === false) {
return false;
}

for ( $p0 = $p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

$p3 = strpos( $content, 'JPATH_BASE', $p1);
if ( $p3 === false) {
return false;
}

for ( $p2 = $p3; $p2 > 0 && $content[$p2] != "\n"; $p2--);
$p2++;

$p4 = strpos( $content, 'require_once', $p3);
if ( $p4 === false) {
return false;
}

for ( $p5=$p4; $content[$p5] != "\n"; $p5++);
$p5++;


$result = substr( $content, 0, $p0)
. substr( $content, $p2, $p5-$p2)
. substr( $content, $p0, $p2-$p0)
. substr( $content, $p5)
;

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

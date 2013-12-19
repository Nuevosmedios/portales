<?php
// file: check_jconfig.php.
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


function jms2win_checkJConfig( $model, $file)
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
$result .= JText::_( 'The wrapper for the Master configuration is not present in Joomla administration');
$result .= '|[ACTION]';
$result .= '|Add 28 lines containing the wrapper to insert into the master configuration.php file';
$result .= '|Update 1 line to save the appropriate configuration content';
}
else {

$p1 = strpos( $str, 'JPATH_ROOT', $pos);
if ($p1 === false) {
$rc = '[NOK]';
$result .= JText::_( 'The wrapper for the Master configuration is not present in Joomla administration');
$result .= '|[ACTION]';
$result .= '|replace 2 lines in master configuration.php file to use JPATH_ROOT when present';
}
}
return $rc .'|'. $result;
}


function jms2win_actionJConfig( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( 'patch_jconfig.php');
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


$p1 = strpos( $content, 'JFile::write');
if ( $p1 === false) {
return false;
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

for ( $p2=$p1; $content[$p2] != "\n"; $p2++);
$content = substr( $content, 0, $p0)
. "if (JFile::write(\$fname, \$config->toString('PHP', 'config', array('class' => 'JConfig')))) {\n"
. substr( $content, $p2+1);
}





$p1 = strpos( $content, 'JFile::write');
if ( $p1 === false) {
return false;
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

$p2 = strpos( $content, '$config->toString', $p1);
if ( $p2 === false) {
return false;
}

$p3 = strpos( $content, 'JConfig', $p2);
if ( $p3 === false) {
return false;
}

$p4 = strpos( $content, ')', $p3+1);
if ( $p4 === false) {
return false;
}

$p5 = strpos( $content, ')', $p4+1);
if ( $p5 === false) {
return false;
}

for ( $p6=$p5; $content[$p6] != "\n"; $p6++);


$result = substr( $content, 0, $p0)
. $patchStr
. substr( $content, $p6+1);

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

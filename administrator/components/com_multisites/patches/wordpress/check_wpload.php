<?php
// file: check_wpload.php.
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


function jms2win_checkWPwpload( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[IGNORE]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, '//_jms2win_begin');
if ($pos === false) $wrapperIsPresent = false;
else $wrapperIsPresent = true;;
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'The standard joomla 2.5 initialization is not present when loading Wordpress.');
$result .= '|[ACTION]';
$result .= '|Fix the bug to perform a standard joomla 2.5 initialization.';
}
return $rc .'|'. $result;
}


function jms2win_actionWPwpload( $model, $file)
{
$patch_dir = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'patches';

include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
$patchStr_1 = jms2win_loadPatch( 'patch_wpload_1.php', dirname( __FILE__));
if ( $patchStr_1 === false) {
return false;
}
$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p4 = strpos( $content, 'configuration.php');
if ( $p4 === false) {
return false;
}

for ( $p1=$p4; $p1 > 0 && substr( $content, $p1, 2) != "//"; $p1--);
$p3++;

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;
$p7 = strpos( $content, 'defines.php', $p4);
if ( $p7 === false) {
return false;
}
$p8 = strpos( $content, "\n", $p7);
if ( $p8 === false) {
return false;
}
$p8++;


$result = substr( $content, 0, $p0)
. str_replace( '{original_code}', substr( $content, $p0, $p8-$p0), $patchStr_1)
. substr( $content, $p8);

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

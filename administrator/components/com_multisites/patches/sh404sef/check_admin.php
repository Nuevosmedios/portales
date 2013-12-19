<?php
// file: check_admin.php.
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


function jms2win_checkSH404Admin( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[IGNORE]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, '/shCacheContent.php');
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
$result .= JText::_( 'The Multi Sites specific "shCacheContent.php" cache for each websites is not present.');
$result .= '|[ACTION]';
$result .= '|Add 6 lines to purge the specific "shCacheContent.php" cache for each slave site.';
}
return $rc .'|'. $result;
}


function jms2win_actionSH404Admin( $model, $file)
{
$parts = explode( DS, dirname(__FILE__));
array_pop( $parts );
$patch_dir = implode( DS, $parts );
include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( '..' .DS. 'sh404sef' .DS. 'patch_admin.php');
if ( $patchStr === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p1 = strpos( $content, 'function purge');
if ( $p1 === false) {
return false;
}

$p3 = strpos( $content, 'shCacheContent.php', $p1);
if ( $p3 === false) {
return false;
}

for ( $p2=$p3; $p2 > 0 && $content[$p2] != "\n"; $p2--);

$p5 = strpos( $content, 'file_exists', $p3);
if ( $p5 === false) {
return false;
}

for ( $p4=$p5; $p4 > 0 && $content[$p4] != "\n"; $p4--);


$result = substr( $content, 0, $p2+1)
. $patchStr
. substr( $content, $p2, $p4-$p2+1)
. $patchStr
. substr( $content, $p4)
;

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

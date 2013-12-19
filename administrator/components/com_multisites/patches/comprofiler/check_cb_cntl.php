<?php
// file: check_cb_cntl.php.
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


function jms2win_checkCB_cntl( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[IGNORE]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, '/ue_config.php');
if ( $pos === false) {
return '[IGNORE]|File Not Found';
}

$pos = strpos( $str, 'MULTISITES_');
if ($pos === false) $wrapperIsPresent = false;
else {
$wrapperIsPresent = true;
}
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'The customisation of "ue_config.php" file for the slave sites is not present into the CB controller');
$result .= '|[ACTION]';
$result .= '|Replace 2 statements that define the "ue_config.php" file name by 2*18 lines that use the slave site ID into the name of the configuration "ue_config_{site_id}.php" file';
}
return $rc .'|'. $result;
}


function jms2win_actionCB_cntl( $model, $file)
{
$parts = explode( DS, dirname(__FILE__));
array_pop( $parts );
$patch_dir = implode( DS, $parts );
include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( '..' .DS. 'comprofiler' .DS. 'patch_cb_cntl.php');
if ( $patchStr === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p1 = strpos( $content, '/ue_config.php');
if ( $p1 === false) {
return false;
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);

for ( $p2=$p1; $content[$p2] != "\n"; $p2++);

$p4 = strpos( $content, '/ue_config.php', $p2);
if ( $p4 === false) {
return false;
}

for ( $p3=$p4; $p3 > 0 && $content[$p3] != "\n"; $p3--);
$p0++;

for ( $p5=$p4; $content[$p5] != "\n"; $p5++);


$result = substr( $content, 0, $p0)
. $patchStr
. substr( $content, $p2+1, $p3-$p2)
. $patchStr
. substr( $content, $p5+1)
;

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

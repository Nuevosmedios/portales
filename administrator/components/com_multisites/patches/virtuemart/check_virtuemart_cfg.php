<?php
// file: check_virtuemart_cfg.php.
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


function jms2win_checkVMCfgWrapper( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[IGNORE]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'MULTISITES_ID');
if ($pos === false) $wrapperIsPresent = false;
else $wrapperIsPresent = true;
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'The routing wrapper is not present in the VirtueMart MASTER configuration file.');
$result .= '|[ACTION]';
$result .= '|Add 5 lines containing the routing wrapper to the slave site.';
}
return $rc .'|'. $result;
}


function jms2win_actionVMCfgWrapper( $model, $file)
{
$parts = explode( DS, dirname(__FILE__));
array_pop( $parts );
$patch_dir = implode( DS, $parts );
include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( '..' .DS. 'virtuemart' .DS. 'patch_virtuemart_cfg.php');
if ( $patchStr === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}






$p1 = strpos( $content, 'global $mosConfig_absolute_path');
if ( $p1 === false) {
return false;
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

$p3 = strpos( $content, "define( 'URL'", $p1);
if ( $p3 === false) {
return false;
}

for ( $p2=$p3; $p2 > 0 && $content[$p2] != "\n"; $p2--);
$p2++;

$p4 = strpos( $content, "SECUREURL", $p3);
if ( $p4 === false) {
return false;
}

$p5 = strpos( $content, "\n", $p4);
if ( $p5 === false) {
return false;
}

$p7 = strpos( $content, '?>', $p5);
if ( $p7 === false) {
return false;
}

for ( $p6=$p7; $p6 > 0 && $content[$p6] != "\n"; $p6--);
$p6++;


$result = substr( $content, 0, $p0)
. $patchStr
. substr( $content, $p0, $p2-$p0)

. "if ( defined( 'MULTISITES_HOST')) {\n"
. "   define( 'URL', 'http://'.MULTISITES_HOST.'/' );\n"
. "   define( 'SECUREURL', 'http://'.MULTISITES_HOST.'/' );\n"
. "} else {\n"
. substr( $content, $p2, $p5-$p2)
. "\n}\n"
. substr( $content, $p5, $p6-$p5)
. "}\n"
. substr( $content, $p6)
;

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

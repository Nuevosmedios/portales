<?php
// file: check_config.php.
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


function jms2win_checkVMConfig( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[IGNORE]|File Not Found';
}
$str = file_get_contents( $filename);


$ifdef_ms_host = preg_match( '#'
. 'if'
. '([[:space:]])*'
. '\('
. '([[:space:]])*'
. 'defined'
. '\('
. '([[:space:]])*'
. '\'MULTISITES_HOST\''
. '([[:space:]])*'
. '\)'
. '([[:space:]])*'
. '\)'
. '#',
$str);


$ifdef_ms_id = preg_match( '#'
. 'if'
. '([[:space:]])*'
. '\('
. '([[:space:]])*'
. 'defined'
. '\('
. '([[:space:]])*'
. '\'MULTISITES_ID\''
. '([[:space:]])*'
. '\)'
. '([[:space:]])*'
. '\)'
. '#',
$str);
$result = "";
$rc = '[OK]';
$sep = "";
$updateLine = 0;

if ( $ifdef_ms_id) {}
else {

if ( $ifdef_ms_host) {
$rc = '[NOK]';
$result .= JText::_( 'JMS contain an older VirtueMart patch that must be replaced.');
$result .= '|The new patch save the VirtueMart configuration file into a specific slave one and a wrapper is added into the master VM configuration file.';

$result .= '|[ACTION]';
$result .= '|replace 2 lines by 27 lines to write the configuration file into a slave specific file and to compute the master site wrapper code.';
$result .= '|Add 4 lines to insert the wrapper into the master virtuemart configuration file.';
}

else {
$rc = '[NOK]';
$result .= JText::_( 'The VirtueMart configuration file wrapper is not present.');
$result .= '|[ACTION]';
$result .= '|replace 2 lines by 17 lines to write the configuration file into a slave specific file and to compute the master site wrapper code.';
$result .= '|Add 4 lines to insert the wrapper into the master virtuemart configuration file.';
}
}
return $rc .'|'. $result;
}


function jms2win_actionVMConfig_remove_v1_0_0( &$content)
{






$p1 = strpos( $content, 'MULTISITES_HOST');
if ( $p1 === false) {
return false;
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;


$p2 = strpos( $content, 'else', $p1);
if ( $p2 === false) {
return false;
}

for ( $p3=$p2; $content[$p3] != "{"; $p3++);

for ( $p4=$p3; $content[$p4] != "}"; $p4++);
$content = substr( $content, 0, $p0)
. substr( $content, $p3+1, $p4-$p3-1)
. substr( $content, $p4+1);
}


function jms2win_actionVMConfig_v1_2_14( $model, $file, &$content)
{
$parts = explode( DS, dirname(__FILE__));
array_pop( $parts );
$patch_dir = implode( DS, $parts );

include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( '..' .DS. 'virtuemart' .DS. 'patch_config.php');
if ( $patchStr === false) {
return false;
}

$patchStr14_1 = jms2win_loadPatch( '..' .DS. 'virtuemart' .DS. 'patch_config14_1.php');
if ( $patchStr14_1 === false) {
return false;
}
$patchStr14_2 = jms2win_loadPatch( '..' .DS. 'virtuemart' .DS. 'patch_config14_2.php');
if ( $patchStr14_2 === false) {
return false;
}





$p1 = strpos( $content, 'is_writable');
if ( $p1 === false) {


$p1 = strpos( $content, 'fopen');
if ( $p1 === false) {
return false;
}
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

$p3 = strpos( $content, 'return false', $p1);
if ( $p3 === false) {
return false;
}

$tmp = substr( $content, $p0, $p3-$p0);
$tpos = strpos( $tmp, 'ADMINPATH');
if ( $tpos === false) {
return false;
}

for ( $p2=$p3; $p2 > 0 && $content[$p2] != "\n"; $p2--);
$p2++;

$p5 = strpos( $content, 'global \\$mosConfig_absolute_path', $p3);
if ( $p5 === false) {
return false;
}

for ( $p4=$p5; $p4 > 0 && $content[$p4] != "\n"; $p4--);
$p4++;

$p6 = strpos( $content, 'define( \'URL\',', $p5);
if ( $p6 === false) {
return false;
}

$p7 = strpos( $content, 'getEscaped', $p6);
if ( $p7 === false) {
return false;
}

$p8 = strpos( $content, ';', $p7);
if ( $p8 === false) {
return false;
}

$p9 = strpos( $content, "\n", $p8);
if ( $p9 === false) {
return false;
}
$saveLines = substr( $content, $p6, $p9-$p6+1);

$p11 = strpos( $content, '$config .= "?>";', $p9);
if ( $p11 === false) {
return false;
}

for ( $p10=$p11; $p10 > 0 && $content[$p10] != "\n"; $p10--);
$p10++;




$p13 = strpos( $content, 'file_put_contents', $p11);
if ( $p13 === false) {

$result = substr( $content, 0, $p0)
. $patchStr
. substr( $content, $p2, $p4-$p2)
. "\$master_wrapper\n"
. substr( $content, $p4, $p6-$p4)

. "\$master_url_wrapper\n"
. $saveLines
. "\$master_url_wrapper_end\n"
. substr( $content, $p9, $p10-$p9)

. "         \$config .= \$master_wrapper_end;\n"
. substr( $content, $p10);
}

else {

for ( $p12=$p13; $p12 > 0 && $content[$p12] != "\n"; $p12--);
$p12++;

$p14 = strpos( $content, "\n", $p13);
if ( $p14 === false) {
return false;
}
$result = substr( $content, 0, $p0)
. $patchStr14_1
. substr( $content, $p2, $p4-$p2)
. "\$master_wrapper\n"
. substr( $content, $p4, $p6-$p4)

. "\$master_url_wrapper\n"
. $saveLines
. "\$master_url_wrapper_end\n"
. substr( $content, $p9, $p10-$p9)

. "         \$config .= \$master_wrapper_end;\n"
. substr( $content, $p10, $p12-$p10)
. $patchStr14_2
. substr( $content, $p14);
}

jimport('joomla.filesystem.file');
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

function jms2win_actionVMConfig( $model, $file)
{

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}

if ( strstr( $content, 'MULTISITES_ID') === false
&& strstr( $content, 'MULTISITES_HOST') !== false) {
jms2win_actionVMConfig_remove_v1_0_0( $content);
}
return jms2win_actionVMConfig_v1_2_14( $model, $file, $content);
}
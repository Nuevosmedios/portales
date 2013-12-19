<?php
// file: check_instdefines.php.
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


function jms2win_checkInstallDefines( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[NOK]|File Not Found';
}
$str = file_get_contents( $filename);

$multisites_found = false;
if ( preg_match( '#defines_multisites.php#i', $str)
|| preg_match( '#defines_md.php#i', $str))
{
$multisites_found = true;
}

$ifdef_config = preg_match( '#'
. 'if'
. '([[:space:]])*'
. '\('
. '([[:space:]])*'
. '!defined'
. '([[:space:]])*'
. '\('
. '([[:space:]])*'
. '\'JPATH_CONFIGURATION\''
. '([[:space:]])*'
. '\)'
. '([[:space:]])*'
. '\)'
. '#',
$str);

$ifdef_install = preg_match( '#'
. 'if'
. '([[:space:]])*'
. '\('
. '([[:space:]])*'
. '!defined'
. '([[:space:]])*'
. '\('
. '([[:space:]])*'
. '\'JPATH_INSTALLATION\''
. '([[:space:]])*'
. '\)'
. '([[:space:]])*'
. '\)'
. '#',
$str);
$if_crosscheck = preg_match( '#file_get_contents#', $str);
$result = "";
$rc = '[OK]';
$sep = "";
$addLine = 0;
$updateLine = 0;
if ( !$if_crosscheck || !$multisites_found) {
$rc = '[NOK]';
$result .= $sep . JText::_( 'PATCHES_MS_DEF_NOTFOUND');
$sep = '|';
$addLine+=16;
}
if ( !$ifdef_config) {
$rc = '[NOK]';
$result .= $sep . JText::_( 'PATCHES_CONF_REDEF_ERR');
$sep = '|';
$updateLine++;
}
if ( !$ifdef_install) {
$rc = '[NOK]';
$result .= $sep . JText::_( 'PATCHES_INST_REDEF_ERR');
$sep = '|';
$updateLine++;
}

if ( $addLine!=0 || $updateLine != 0) {
$result .= '|[ACTION]';
if ( $addLine>0) {
$result .= '|Add ' .$addLine. ' line';
if ( $addLine>1) {
$result .= 's'; 
}
}
if ( $updateLine>0) {
$result .= '|Update ' .$updateLine. ' line';
if ( $updateLine>1) {
$result .= 's'; 
}
}
}
return $rc .'|'. $result;
}

function jms2win_actionInstallDefines( $model, $file)
{
return $model->_deployPatches();
}

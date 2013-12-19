<?php
// file: check_ifdirpresent.php.
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


function jms2win_checkIfDirPresent( $model, $file)
{


$downloadInfo = false;
$download_action = '';
if ( class_exists( 'MultisitesModelPatches') && method_exists( 'MultisitesModelPatches', 'checkInstallationDownloaded')) {
$downloadInfo = MultisitesModelPatches::checkInstallationDownloaded();
if ( $downloadInfo === true) {}
else {
$download_action = '|'.JText::sprintf( 'PATCHES_ACT_DOWNLOADINSTALL', $downloadInfo['url'], $downloadInfo['destination']);
}
}
$dir = JPath::clean( JPATH_ROOT.DS.$file);
if ( !is_dir( $dir)) {
return '[NOK]|' . JText::_( 'PATCHES_DIR_NOTFOUND')
. '|[ACTION]'
. $download_action
.'|' . JText::_( 'PATCHES_ACT_RENAME')
;
}
$filename = JPath::clean( JPATH_ROOT.DS.$file. '/index.php');
if ( !file_exists( $filename)) {
return '[NOK]|' . JText::_( 'PATCHES_MISSING_INDEX')
. '|[ACTION]'
. $download_action
. '|' . JText::_( 'PATCHES_RESTORE_DIR')
;
}
if ( version_compare( JVERSION, '3.1') >= 0) {
$filename = JPath::clean( JPATH_ROOT.DS.$file. '/application');
}

else {
$filename = JPath::clean( JPATH_ROOT.DS.$file. '/includes');
}
if ( !is_dir( $filename)) {
return '[NOK]|' . JText::_( 'PATCHES_MISSING_INDEX')
. '|[ACTION]'
. $download_action
. '|' . JText::_( 'PATCHES_RESTORE_DIR')
;
}
return '[OK]|' . JText::_( 'PATCHES_DIR_PRESENT');
}


function jms2win_actionIfDirPresent( $model, $dir)
{
$parts = explode( '/', $dir );
if ( $parts[0] == 'installation') {
return $model->_restoreInstallation();
}
return true;
}

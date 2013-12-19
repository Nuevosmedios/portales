<?php
// file: check_ps_checkout.php.
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


function jms2win_checkVMPlgAfterOrder( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[IGNORE]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'JPluginHelper::importPlugin');
if ($pos === false) $wrapperIsPresent = false;
else $wrapperIsPresent = true;
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'The code to allow using the Joomla Multi Sites bridge with VirtueMart is not present.');
$result .= '|[ACTION]';
$result .= '|Add 5 lines containing the call to a MultiSites / VirtueMart plugin onAfterOrderCreate.';
}
return $rc .'|'. $result;
}


function jms2win_actionVMPlgAfterOrder( $model, $file)
{
$parts = explode( DS, dirname(__FILE__));
array_pop( $parts );
$patch_dir = implode( DS, $parts );
include_once( $patch_dir .DS. 'joomla' .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( '..' .DS. 'virtuemart' .DS. 'patch_ps_checkout.php');
if ( $patchStr === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}






$p0 = strpos( $content, '$this->_SHIPPING->save_rate_info');
if ( $p0 === false) {
return false;
}

$p1 = strpos( $content, '}', $p0);
if ( $p1 === false) {
return false;
}

$p2 = strpos( $content, "\n", $p1);
if ( $p2 === false) {
return false;
}


$result = substr( $content, 0, $p2)
. $patchStr
. substr( $content, $p2);

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

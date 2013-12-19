<?php
// file: check_libuser17.php.
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


function jms2win_checkLibUser17( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[NOK]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, '//_jms2win');
if ($pos === false) { $wrapperIsPresent = false; }
else { $wrapperIsPresent = true; }
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'Fix a bug present in joomla 1.6 and 1.7 that seems have declined to fix.<br/>'
. 'See Joomla bug tracker where they decline to fix it as they are not able to reproduce it.<br/>'
. 'http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_id=8103&tracker_item_id=24879');
$result .= '|[ACTION]';
$result .= '|Add 3 lines to return the default value when joomla has not correctly initialized the users parameters.';
}
return $rc .'|'. $result;
}


function jms2win_actionLibUser17( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( 'patch_libuser17.php');
if ( $patchStr === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p1 = strpos( strtolower( $content), '$this->_params->get');
if ( $p1 === false) {
return false;
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;


$result = substr( $content, 0, $p0)
. $patchStr
. substr( $content, $p0);

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

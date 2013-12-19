<?php
// file: check_content_hlp_route.php.
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


function jms2win_checkContentHelperRoute( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[NOK]|File Not Found';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, '!class_exists');
if ($pos === false) $wrapperIsPresent = false;
else $wrapperIsPresent = true;;
$result = "";
$rc = '[OK]';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'The wrapper for the Articles Sharing is not present');
$result .= '|[ACTION]';
$result .= '|Add 4 lines containing the wrapper to avoid the redefinition of the route when the Article Sharing is present';
}
return $rc .'|'. $result;
}


function jms2win_actionContentHelperRoute( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr = jms2win_loadPatch( 'patch_content_hlp_route.php');
if ( $patchStr === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}





$p0 = strpos( $content, 'class');
if ( $p0 === false) {
return false;
}

for ( ; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

$p1 = strpos( $content, '?>', $p0);
if ( $p1 === false) {
$p1 = strlen( $content);
}
$closeIf = "//_jms2win_begin\n"
. "}\n"
. "//_jms2win_end\n"
;


$result = substr( $content, 0, $p0)
. $patchStr
. substr( $content, $p0, $p1-$p0)
. $closeIf
. substr( $content, $p1);

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

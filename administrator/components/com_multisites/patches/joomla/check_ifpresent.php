<?php
// file: check_ifpresent.php.
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


function jms2win_checkIfPresent( $model, $file, $args=array())
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[NOK]|File Not Found'
.'|[ACTION]|Add the file';
}
$str = file_get_contents( $filename);

$pos = strpos( $str, 'MultisitesLetterTree::getLetterTreeDir');
if ($pos === false) { $wrapperIsPresent = false; }
else { $wrapperIsPresent = true; }
$result = "";
$rc = '[OK]|File is present';
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'The new Multisites directory structure to allow creating several thousand of slave sites from the front-end is not present');
$jms_vers = MultisitesController::_getVersion();
if ( version_compare( $jms_vers, '1.2.30') < 0) {
$result .= '|[ACTION]';
$result .= '|Download the <a href="http://www.jms2win.com/get-latest-version">latest jms version</a>.';
$result .= '|JMS version 1.2.30 or higher is required to install this patch.';
}
else {
$result .= '|[ACTION]';
$result .= '|Install the new multisite detection';
}
}
else {

if ( $wrapperIsPresent && !empty( $args)) {

if ( !empty( $args['version'])) {
$pos = strpos( $str, $args['version']);
if ($pos === false) {
$rc = '[NOK]';
$result .= JText::_( 'An update is available.');
$result .= '|[ACTION]';
$result .= '|Install the new version';
}
}
}
}
return $rc .'|'. $result;
}

function jms2win_actionIfPresent( $model, $file)
{
return $model->_deployPatches();
}

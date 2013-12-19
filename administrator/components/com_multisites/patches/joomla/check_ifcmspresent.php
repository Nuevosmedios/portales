<?php
// file: check_ifcmspresent.php.
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


function jms2win_checkIfCMSPresent( $model, $file, $args=array())
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
if ( !file_exists( dirname( dirname( __FILE__)).'/lib_cms.zip')) {
return '[IGNORE]|File Not Found';
}
return '[NOK]|File Not Found'
.'|[ACTION]|Add the file';
}
$result = "";
$rc = '[OK]|File is present';
$wrapperIsPresent = file_exists( $filename);
if ( !$wrapperIsPresent) {
$rc = '[NOK]';
$result .= JText::_( 'The libraries/cms/schema is not present');
}
return $rc .'|'. $result;
}

function jms2win_actionIfCMSPresent( $model, $file)
{
return $model->_deployPatches( 'lib_cms.zip');
}

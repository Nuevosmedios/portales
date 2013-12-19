<?php
// file: settings.php.
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


defined('_JEXEC') or die( 'Restricted access' );




class MultisitesControllerSettings extends J2WinController
{





function showSettings()
{
$model =& $this->getModel( 'settings' );
$view =& $this->getView( 'settings');
$view->setModel( $model, true );
$view->showSettings();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function saveSettings()
{
$option = JRequest::getCmd('option');

JRequest::checkToken() or jexit( 'Invalid Token' );
$model =& $this->getModel( 'settings' );
$view =& $this->getView( 'settings');
$view->setModel( $model, true );
$msg = $view->saveSettings();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
$this->setRedirect( 'index.php?task=manage&option=' . $option, $msg);
}





function ajaxMaxmindDownload()
{

JRequest::checkToken( 'get') or jexit( 'Invalid Token' );
$results = array( 'type' => 'maxmind_download');
$file = JRequest::getWord('file', null);
$geofile = dirname( dirname( __FILE__)).DS.'classes'.DS.'geoipdownload.php';
if ( JFile::exists( $geofile)) {
@include_once( $geofile);
if ( class_exists( 'GeoipDownload')) {
if ( $file == 'icc') {
GeoipDownload::download_ICC();
$filename = dirname( dirname( __FILE__)).DS.'classes'.DS.'geoip.dat';
if ( !JFile::exists( $filename)) {
$results['maxmind_date_str'] = JText::_( 'File not present');
}
else {
$results['maxmind_date_str'] = strftime( '%d-%b-%Y %H:%M:%S', filemtime( $filename));
}
}
else if ( $file == 'city') {
GeoipDownload::download_City();
$filename = dirname( dirname( __FILE__)).DS.'classes'.DS.'geolitecity.dat';
if ( !JFile::exists( $filename)) {
$results['maxmind_date_str'] = JText::_( 'File not present');
}
else {
$results['maxmind_date_str'] = strftime( '%d-%b-%Y %H:%M:%S', filemtime( $filename));
}
}
}
}
else {
$results['maxmind_date_str'] = JText::_( 'SETTINGS_GEOIP_MAXMIND_DOWNLOAD_CLASS_MISSING');
}
jexit( json_encode ( $results));
}
} 

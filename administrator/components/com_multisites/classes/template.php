<?php
// file: template.php.
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




class Jms2WinTemplate
{
var $id = ''; 
var $groupName = ''; 
var $sku = ''; 
var $title = ''; 
var $description = ''; 
var $validity = ''; 
var $validity_unit = ''; 
var $maxsite = ''; 
var $expireurl = ''; 
var $redirect1st = ''; 
var $ignoreMasterIndex= ''; 
var $fromSiteID = ''; 
var $fromDB = ''; 
var $toSiteID = ''; 
var $toDomains = array(); 
var $toSiteName = ''; 
var $continents = array(); 
var $countries = array(); 
var $regions = ''; 
var $states = ''; 
var $cities = ''; 
var $zipcodes = ''; 
var $fromLongitude = ''; 
var $fromLatitude = ''; 
var $toLongitude = ''; 
var $toLatitude = ''; 
var $metro = ''; 
var $area = ''; 
var $browser_types = array(); 
var $browser_langs = ''; 
var $shareDB = ''; 
var $adminUserID = ''; 
var $adminUserName = ''; 
var $adminUserLogin = ''; 
var $adminUserEmail = ''; 
var $adminUserPsw = ''; 
var $toDBType = ''; 
var $toDBHost = ''; 
var $toDBName = ''; 
var $toDBUser = ''; 
var $toDBPsw = ''; 
var $toPrefix = ''; 
var $setDefaultJLang = ''; 
var $setDefaultTemplate = ''; 
var $setDefaultMenu = ''; 
var $deploy_dir = ''; 
var $deploy_create = ''; 
var $alias_link = ''; 
var $media_dir = ''; 
var $images_dir = ''; 
var $templates_dir = ''; 
var $tmp_dir = ''; 
var $toFTP_enable = ''; 
var $toFTP_host = ''; 
var $toFTP_port = ''; 
var $toFTP_user = ''; 
var $toFTP_psw = ''; 
var $toFTP_rootpath = ''; 
var $symboliclinks = array(); 
var $dbsharing = array(); 
var $success = false; 

function Jms2WinTemplate()
{
$this->success = false;
}

function getTemplateFilename()
{
$filename = JPATH_MULTISITES .DS. 'config_templates.php';
return $filename;
}


function load( $id)
{
$this->success = false;
$templates = array();
$filename = Jms2WinTemplate::getTemplateFilename();
@include( $filename);

if ( isset( $templates[$id])) {
$this->id = $id;

foreach( $templates[$id] as $key => $value) {
$this->$key = $value;
}
$this->success = true;
}
return $this->success;
}
}

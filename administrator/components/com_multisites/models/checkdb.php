<?php
// file: checkdb.php.
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
jimport('joomla.filesystem.file');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'application' .DS. 'component' .DS. 'model2win.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'site.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'utils.php');
require_once( dirname( __FILE__) .DS. 'manage.php');


@include_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'multisites_path.cfg.php');

if ( !defined( 'JPATH_MULTISITES')) {
define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}




class MultisitesModelCheckDB extends JModel2Win
{


function &getSites()
{
static $instance;
if ( isset( $instance)) {
return $instance;
}

$manage = new MultisitesModelManage();
$rows =& $manage->getSites();
$instance = $rows;
return $instance;
}


function _preprocessAction_GetDBInfo( &$site, $enteredvalues)
{
$site->preprocessGetDBInfo( $enteredvalues);
}


function preprocessAction( &$sites, $action, $enteredvalues=array())
{


$fn = '_preprocessAction_'.$action;
if ( empty( $sites)
|| !method_exists( $this, $fn))
{

return;
}
for( $i=0; $i<count( $sites); $i++) {
$this->$fn( $sites[$i], $enteredvalues);
}
}


function _doAction_GetDBInfo( $enteredvalues)
{
$site_id = !empty( $enteredvalues['site_id']) ? $enteredvalues['site_id'] : ':master_db:';
$site = MultisitesUtils::getSiteInfo( $site_id);

$dbinfo = $site->getDBInfo( $enteredvalues);
return $dbinfo;
}


function _doAction_FixDB( $enteredvalues)
{
$site_id = !empty( $enteredvalues['site_id']) ? $enteredvalues['site_id'] : ':master_db:';
$site = MultisitesUtils::getSiteInfo( $site_id);
$dbinfo = $site->fixDB( $enteredvalues);
return $dbinfo;
}


function _doAction_FixUncheckedDB( $enteredvalues)
{
$site_id = !empty( $enteredvalues['site_id']) ? $enteredvalues['site_id'] : ':master_db:';
$site = MultisitesUtils::getSiteInfo( $site_id);
$dbinfo = $site->fixUncheckedDB( $enteredvalues);
return $dbinfo;
}


function _doAction_DownloadLatestJoomla( $enteredvalues)
{
include_once( dirname( dirname( __FILE__)).'/classes/updater.php');
return MultisitesUpdater::downloadLatestJoomla();
}

function downloadLatestJoomla() { return $this->_doAction_DownloadLatestJoomla( array()); }

function downloadPackage( $url) {
include_once( dirname( dirname( __FILE__)).'/classes/updater.php');
return MultisitesUpdater::downloadPackage( $url, '', false);
}


function getDownloadedPackages()
{
require_once( dirname( dirname( __FILE__)).'/classes/updater.php');
return MultisitesUpdater::getDownloadedPackages();
}


function getAvailablePackages()
{
require_once( dirname( dirname( __FILE__)).'/classes/updater.php');
return MultisitesUpdater::getAvailablePackages();
}


function _doAction( $action, $enteredvalues)
{
$results = array();
$site_id = !empty( $enteredvalues['site_id']) ? $enteredvalues['site_id'] : ':master_db:';
$site = MultisitesUtils::getSiteInfo( $site_id);
if ( method_exists( $site, $action)) {
$results = $site->$action( $enteredvalues);
}

else if ( ($results['errors'] = $site->updaterAction( $action, $enteredvalues) ) !== false) {
if ( empty( $results['errors'])) {
$results['subresult'] = JText::_( 'Done successfully');
}
}
else {
$results['errors'] = array();
$results['errors'][] = JText::sprintf( 'Action [%s] not defined! Either implement an action into MultisitesModelCheckDB class or Site class', $action);
}
return $results;
}


function doAction( $enteredvalues)
{
$results = array();
$action = !empty( $enteredvalues['action']) ? $enteredvalues['action'] : 'getdbinfo';
$fn = '_doAction_'.ucfirst( $action);
if ( method_exists( $this, $fn)) {
$results = $this->$fn( $enteredvalues);
}
else {
$results = $this->_doAction( $action, $enteredvalues);
}
return $results;
}

function getInstallLatestJoomla_Actions()
{
return array( 'checkDownloadJoomla' => JText::_( 'Downloading joomla package'),
'extractJoomla' => JText::_( 'Extracting joomla'),
'removeInstallation' => JText::_( 'Removing the /installation directory'),
'fixConfiguration' => JText::_( 'Fixing the "configuration.php" file'),
'fixPluginsDirs' => JText::_( 'Converting joomla 1.5 plugins directory structure into Joomla 2.5 directory structure')
);
}


function getSchemas( $filters)
{
require_once( dirname( dirname( __FILE__)).'/libraries/cms/schema/database.php');
$sites = &$this->getSites();
$rows = MultisitesSchemaDatabase::getSchemas( $filters, $sites);
return $rows;
}
} 

<?php
// file: tools.php.
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
jimport( 'joomla.application.component.model' );
jimport('joomla.filesystem.file');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'site.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'template.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'dbsharing.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'dbtables.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'utils.php');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'application' .DS. 'component' .DS. 'model2win.php');
require_once( dirname( __FILE__) .DS. 'manage.php');


@include_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'multisites_path.cfg.php');

if ( !defined( 'JPATH_MULTISITES')) {
define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}




class MultisitesModelTools extends JModel2Win
{


function &getSiteDependencies( $with_parent_site_id = null)
{




$model = & JModel2Win::getInstance( 'Manage', 'MultisitesModel');
$sites = & $model->getSites();

$indice = array();
$sys_sites = array();

$master_site = new Site();
$master_site->id = ':master_db:';
$sys_sites[] = $master_site;
$indice[ $master_site->id] = - count( $sys_sites);
for( $i = 0; $i<count($sites); $i++) {
$indice[ $sites[$i]->id] = $i;
}

for( $i = 0; $i<count($sites); $i++) {
$site = & $sites[$i];

if ( isset( $fromSiteID)) { unset( $fromSiteID); }
if ( isset( $template)) { unset( $template); }

if ( !empty( $site->toPrefix)
&& !empty( $site->fromTemplateID)) {

if ( $site->fromTemplateID == ':master_db:') {
$fromSiteID = ':master_db:';

}

else {
$template = & $site->getTemplate();

}
}

if ( !empty( $template) && !empty( $template->fromSiteID)) {
$fromSiteID = $template->fromSiteID;


if ( !isset( $indice[ "$fromSiteID"])) {
$fromSiteID = ':orphan:';

if( !isset( $indice[ "$fromSiteID"])) {

$orphan = new Site();
$orphan->id = $fromSiteID;
$indice[ $orphan->id] = - count( $sys_sites);
$sys_sites[] = $orphan;
}
}
}

if ( !empty( $fromSiteID)) {
$x = $indice[ $fromSiteID];
if ( $x < 0) {
$sites[$i]->_treeParentSite = & $sys_sites[ (- $x) - 1] ;
}
else {
$sites[$i]->_treeParentSite = & $sites[ $x] ;
}
if ( !isset( $site->_treeParentSite->_treeChildren)) {
$site->_treeParentSite->_treeChildren = array();
}
$site->_treeParentSite->_treeChildren[$site->id] = & $site;


}
}

$tree = array();
foreach( $indice as $key => $i) {
if ( $i<0) {
$i = -$i;
$i--;
$site = & $sys_sites[$i];
}
else {
$site = & $sites[$i];
}
if ( empty( $with_parent_site_id)) {
if ( empty( $site->_treeParentSite)) {
$tree[ $site->id] = $site;
}
}
else {

if ( !empty( $site->_treeParentSite) && $site->_treeParentSite->id == $with_parent_site_id ) {
$tree[ $site->id] = $site;
}
}
}
ksort( $tree);
return $tree;
}


function dumpTree( $tree, $leadingspaces = '')
{
if ( empty( $tree)) {
return;
}
foreach( $tree as $site) {
echo "\n<br />";
echo $leadingspaces . 'site : ' . $site->id;
if ( !empty( $site->_treeParentSite)) {
echo ' parentSiteID = ' .$site->_treeParentSite->id;
}
if ( !empty( $site->_treeChildren)) {
echo ' children:';
MultisitesModelTools::dumpTree( $site->_treeChildren, $leadingspaces . '&nbsp;');
}
}
}


function &_getComponent( $site_id, $option)
{
static $none = array();

$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
if ( empty( $db)) {
return $none;
}
if ( version_compare( JVERSION, '1.6') >= 0) {
$query = "SELECT extension_id as id, name, element as 'option', client_id, protected as iscore"
. ' FROM #__extensions'
. ' WHERE type = "component" AND protected=0 AND element='. $db->Quote( $option)
. ' ORDER BY name'
;
$db->setQuery($query);
$rows = $db->loadObjectList();
}
else {
$query = 'SELECT *'
. ' FROM #__components as c'
. ' WHERE parent = 0 AND iscore = 0 AND c.option = ' . $db->Quote( $option)
. ' ORDER BY name'
;
$db->setQuery($query);
$row = $db->loadObject();
}
return $row;
}


function &_getComponents( $site_id)
{
static $none = array();

$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
if ( empty( $db)) {
return $none;
}
if ( version_compare( JVERSION, '1.6') >= 0) {
$query = "SELECT extension_id as id, name, element as 'option', client_id, protected as iscore"
. ' FROM #__extensions'
. ' WHERE type = "component"'
. ' AND protected=0'
. ' GROUP BY name'
. ' ORDER BY name'
;
$db->setQuery($query);
$rows = $db->loadObjectList();
}
else {
$query = 'SELECT *' .
' FROM #__components' .
' WHERE parent = 0 AND iscore = 0' .

' ORDER BY name';
$db->setQuery($query);
$rows = $db->loadObjectList();
}
return $rows;
}


function _fillComponents( &$components, $column, &$rows)
{
for( $i = 0; $i<count($rows); $i++) {
$row = & $rows[$i];
$extName = $row->option;
if ( !isset( $components[$extName])) {
$components[$extName] = array();
}
$components[$extName][$column] = $row;
if ( !isset( $components[$extName][4])) {
$shareInfos = & $this->dbsharing->getShareInfos( $row->option);
if ( !empty( $shareInfos)) {
$components[$extName][4] = $shareInfos;
}
}
if ( !isset( $components[$extName][5])) {
$tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
if ( !empty( $tablesInfos)) {
$components[$extName][5] = $tablesInfos;
}
}
}
}


function &_getModule( $site_id, $module)
{
static $none = array();

$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
if ( empty( $db)) {
return $none;
}
if ( version_compare( JVERSION, '1.6') >= 0) {
$query = 'SELECT element as module, client_id'
. ' FROM #__extensions'
. ' WHERE type = "module"'
. ' AND element = ' . $db->Quote( $module)
. ' AND protected=0'
;
}
else {
$query = 'SELECT module, client_id'
. ' FROM #__modules'
. ' WHERE module = ' . $db->Quote( $module)
. ' AND iscore=0'
;
}
$db->setQuery($query, 0, 1);
$rows = $db->loadObjectList();
$n = count($rows);
for ($i = 0; $i < $n; $i ++) {
$row = & $rows[$i];

if ($row->client_id == "1") {
$moduleBaseDir = JPATH_ADMINISTRATOR.DS."modules";
} else {
$moduleBaseDir = JPATH_SITE.DS."modules";
}

$xmlfile = $moduleBaseDir . DS . $row->module .DS. $row->module.".xml";
if (file_exists($xmlfile))
{
if ($data = JApplicationHelper::parseXMLInstallFile($xmlfile)) {
foreach($data as $key => $value) {
$row->$key = $value;
}
}
}
}
if ( $n >0) {
return $rows[0];
}
$none = array();
return $none;
}


function &_getModules( $site_id)
{
static $none = array();

$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
if ( empty( $db)) {
return $none;
}
if ( version_compare( JVERSION, '1.6') >= 0) {
$query = 'SELECT extension_id as id, name, folder, element as module, client_id, protected as iscore'
. ' FROM #__extensions'
. ' WHERE type = "module"'
. ' GROUP BY name'
. ' ORDER BY name'
;
$db->setQuery($query);
$rows = $db->loadObjectList();
}
else {
$query = 'SELECT module, client_id, iscore' .
' FROM #__modules' .
' WHERE module LIKE "mod_%" ' .
' GROUP BY module, client_id' .
' ORDER BY module, client_id';
$db->setQuery($query);
$rows = $db->loadObjectList();
}
$n = count($rows);
for ($i = 0; $i < $n; $i++) {
$row = & $rows[$i];

if ( $i>0 && $rows[$i-1]->module == $row->module) {

if ( $row->iscore == 1) {

$row->todelete = true;
continue;
}
}

if ($row->client_id == "1") {
$moduleBaseDir = JPATH_ADMINISTRATOR.DS."modules";
} else {
$moduleBaseDir = JPATH_SITE.DS."modules";
}

$xmlfile = $moduleBaseDir . DS . $row->module .DS. $row->module.".xml";
if (file_exists($xmlfile))
{
if ($data = JApplicationHelper::parseXMLInstallFile($xmlfile)) {
foreach($data as $key => $value) {
$row->$key = $value;
}
}
}
else {
$row->todelete = true;
}
}
return $rows;
}


function _fillModules( &$modules, $column, &$rows)
{
for( $i = 0; $i<count($rows); $i++) {
$row = & $rows[$i];
if ( isset( $row->todelete) && $row->todelete) {
continue;
}
$tablesInfos = & $this->dbtables->getTablesInfos( $row->module);

$extName = $row->module;
if ( !isset( $modules[$extName])) {

if ( $row->iscore != 1) {
$modules[$extName] = array();
}

else if ( !empty( $tablesInfos)) {

if ( $column <= 0) {
$modules[$extName] = array();
}
else {


$foundName = '';
foreach( $modules as $moduleArray) {
if ( isset( $moduleArray[0])) {
if ( isset( $moduleArray[0]->module) && $moduleArray[0]->module == $row->module) {
$foundName = $moduleArray[0]->module;
break;
}
}
}

if ( empty( $foundName)) {

$modules[$extName] = array();
}

else {
$extName = $foundName;
}
}
}
else {

continue;
}
}
$modules[$extName][$column] = $row;
if ( !isset( $modules[$extName][4])) {
$shareInfo = & $this->dbsharing->getShareInfos( $row->module);
if ( !empty( $shareInfos)) {
$modules[$extName][4] = $shareInfos;
}
}
if ( !isset( $modules[$extName][5])) {

if ( !empty( $tablesInfos)) {
$modules[$extName][5] = $tablesInfos;
}
}
}
}


function &_getPlugin( $site_id, $folder, $element = null)
{
static $none = array();

if ( empty( $element)) {

$parts = explode( '/', $folder);
if ( count( $parts) == 2) {
$folder = $parts[0];
$element = $parts[1];
}
}

$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
if ( empty( $db)) {
return $none;
}
if ( version_compare( JVERSION, '1.6') >= 0) {
$query = 'SELECT extension_id as id, name, folder, element, client_id'
. ' FROM #__extensions'
. ' WHERE type = "plugin"'
. '   AND state > -1'
. '   AND folder =' . $db->Quote( $folder)
. '   AND element =' . $db->Quote( $element)
;
}
else {
$query = 'SELECT id, name, folder, element, client_id'
. ' FROM #__plugins'
. ' WHERE iscore=0'
. '   AND folder =' . $db->Quote( $folder)
. '   AND element =' . $db->Quote( $element)
;
}
$db->setQuery($query);
$rows = $db->loadObject();
return $rows;
}


function &_getPlugins( $site_id)
{
static $none = array();

$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
if ( empty( $db)) {
return $none;
}
if ( version_compare( JVERSION, '1.6') >= 0) {
$query = 'SELECT extension_id as id, name, folder, element, client_id, 0 as iscore'
. ' FROM #__extensions'
. ' WHERE type = "plugin"'
. ' GROUP BY name'
. ' ORDER BY name'
;
$db->setQuery($query);
$rows = $db->loadObjectList();
}
else {
$query = 'SELECT id, name, folder, element, client_id, iscore' .
' FROM #__plugins' .
' GROUP BY name' .
' ORDER BY name';
$db->setQuery($query);
$rows = $db->loadObjectList();
}
return $rows;
}


function _fillPlugins( &$plugins, $column, &$rows)
{
for( $i = 0; $i<count($rows); $i++) {
$row = & $rows[$i];
$option = $row->folder .'/'. $row->element;
$tablesInfos = & $this->dbtables->getTablesInfos( $option);

if ( !isset( $plugins[$option])) {


if ( $row->iscore != 1 || !empty( $tablesInfos)) {
$plugins[$option] = array();
}
else {

continue;
}
}
$plugins[$option][$column] = $row;
if ( !isset( $plugins[$option][4])) {
$shareInfos = & $this->dbsharing->getShareInfos( $option);
if ( !empty( $shareInfos)) {
$plugins[$option][4] = $shareInfos;
}
}
if ( !isset( $plugins[$option][5])) {

if ( !empty( $tablesInfos)) {
$plugins[$option][5] = $tablesInfos;
}
}
}
}


function &_getTemplate( $site_id, $name, $element = null)
{
static $none = array();
if ( version_compare( JVERSION, '1.6') >= 0) {}
else {
return $none;
}

$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
if ( empty( $db)) {
return $none;
}
$query = 'SELECT extension_id as id, name, folder, element, client_id'
. ' FROM #__extensions'
. ' WHERE type = "template"'
. '   AND state > -1'
. '   AND name =' . $db->Quote( $name)
;
if ( !empty( $name)) {
$query .= '   AND name =' . $db->Quote( $name);
}
if ( !empty( $element)) {
$query .= '   AND element =' . $db->Quote( $element);
}
$db->setQuery($query);
$rows = $db->loadObject();
return $rows;
}


function &_getTemplates( $site_id)
{
static $none = array();

if ( version_compare( JVERSION, '1.6') >= 0) {}
else {

return $none;
}

$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
if ( empty( $db)) {
return $none;
}
$query = 'SELECT extension_id as id, name, folder, element, client_id, 0 as iscore'
. ' FROM #__extensions'
. ' WHERE type = "template"'
. ' GROUP BY name'
. ' ORDER BY name'
;
$db->setQuery($query);
$rows = $db->loadObjectList();
return $rows;
}


function _fillTemplates( &$templates, $column, &$rows)
{
for( $i = 0; $i<count($rows); $i++) {
$row = & $rows[$i];
$option = $row->element;
$tablesInfos = & $this->dbtables->getTablesInfos( $option);

if ( !isset( $templates[$option])) {


if ( $row->iscore != 1 || !empty( $tablesInfos)) {
$templates[$option] = array();
}
else {

continue;
}
}
$templates[$option][$column] = $row;
if ( !isset( $templates[$option][4])) {
$shareInfos = & $this->dbsharing->getShareInfos( $option);
if ( !empty( $shareInfos)) {
$templates[$option][4] = $shareInfos;
}
}
if ( !isset( $templates[$option][5])) {
if ( !empty( $tablesInfos)) {
$templates[$option][5] = $tablesInfos;
}
}
}
}


function &_getLanguage( $site_id, $name, $element = null)
{
static $none = array();
if ( version_compare( JVERSION, '1.6') >= 0) {}
else {
return $none;
}

$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
if ( empty( $db)) {
return $none;
}
$query = 'SELECT extension_id as id, name, folder, element, client_id'
. ' FROM #__extensions'
. ' WHERE type = "language"'
. '   AND state > -1'
. '   AND name =' . $db->Quote( $name)
;
if ( !empty( $name)) {
$query .= '   AND name =' . $db->Quote( $name);
}
if ( !empty( $element)) {
$query .= '   AND element =' . $db->Quote( $element);
}
$db->setQuery($query);
$rows = $db->loadObject();
return $rows;
}


function &_getLanguages( $site_id)
{
static $none = array();

if ( version_compare( JVERSION, '1.6') >= 0) {}
else {

return $none;
}

$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
if ( empty( $db)) {
return $none;
}
$query = 'SELECT extension_id as id, name, folder, element, client_id, 0 as iscore'
. ' FROM #__extensions'
. ' WHERE type = "language"'
. ' GROUP BY name'
. ' ORDER BY name'
;
$db->setQuery($query);
$rows = $db->loadObjectList();
return $rows;
}


function _fillLanguages( &$languages, $column, &$rows)
{
for( $i = 0; $i<count($rows); $i++) {
$row = & $rows[$i];
$option = $row->element;
$tablesInfos = & $this->dbtables->getTablesInfos( $option);

if ( !isset( $languages[$option])) {


if ( $row->iscore != 1 || !empty( $tablesInfos)) {
$languages[$option] = array();
}
else {

continue;
}
}
$languages[$option][$column] = $row;
if ( !isset( $languages[$option][4])) {
$shareInfos = & $this->dbsharing->getShareInfos( $option);
if ( !empty( $shareInfos)) {
$languages[$option][4] = $shareInfos;
}
}
if ( !isset( $languages[$option][5])) {
if ( !empty( $tablesInfos)) {
$languages[$option][5] = $tablesInfos;
}
}
}
}


function &_getExtName( $site_id, $option, $type=null)
{
$result = $option;
if ( !empty( $type)) {
$fn = '_get'. ucfirst( $type);
if ( method_exists( $this, $fn)) {
$obj = $this->$fn( $site_id, $option);
if ( !empty( $obj)) {
$result = $obj->name;
return $result;
}
}
}

if ( strncmp( $option, 'com_', 4) == 0) {
$obj = $this->_getComponent( $site_id, $option);
if ( !empty( $obj)) {
$result = $obj->name;
}
}

else if ( strncmp( $option, 'mod_', 4) == 0) {
$obj = $this->_getModule( $site_id, $option);
if ( !empty( $obj)) {
$result = $obj->name;
}
}

else {
$obj = $this->_getPlugin( $site_id, $option);
if ( !empty( $obj)) {
$result = $obj->name;
}
}
return $result;
}


function &getExtensions( $site_id)
{
$extensions = array();
$components = array();
$modules = array();
$plugins = array();
$templates = array();
$languages = array();
$this->dbsharing = & Jms2WinDBSharing::getInstance();
$this->dbtables = & Jms2WinDBTables::getInstance();

$components_master = & $this->_getComponents( ':master_db:');
$this->_fillComponents( $components, 0, $components_master);
$modules_master = & $this->_getModules( ':master_db:');
$this->_fillModules( $modules, 0, $modules_master);
$plugins_master = & $this->_getPlugins( ':master_db:');
$this->_fillPlugins( $plugins, 0, $plugins_master);
$templates_master = & $this->_getTemplates( ':master_db:');
$this->_fillTemplates( $templates, 0, $templates_master);
$languages_master = & $this->_getLanguages( ':master_db:');
$this->_fillLanguages( $languages, 0, $languages_master);
if ( $site_id != ':master_db:') {

$components_site = & $this->_getComponents( $site_id);
$this->_fillComponents( $components, 2, $components_site);
$modules_site = & $this->_getModules( $site_id);
$this->_fillModules( $modules, 2, $modules_site);
$plugins_site = & $this->_getPlugins( $site_id);
$this->_fillPlugins( $plugins, 2, $plugins_site);
$templates_site = & $this->_getTemplates( $site_id);
$this->_fillTemplates( $templates, 2, $templates_site);
$languages_site = & $this->_getLanguages( $site_id);
$this->_fillLanguages( $languages, 2, $languages_site);

$site = & Site::getInstance( $site_id);
$fromSiteID = $site->getFromSiteID();
if ( $fromSiteID != ':master_db:') {
$components_templates = & $this->_getComponents( $fromSiteID);
$this->_fillComponents( $components, 1, $components_templates);
$modules_templates = & $this->_getModules( $fromSiteID);
$this->_fillModules( $modules, 1, $modules_templates);
$plugins_templates = & $this->_getPlugins( $fromSiteID);
$this->_fillPlugins( $plugins, 1, $plugins_templates);
$templates_templates = & $this->_getTemplates( $fromSiteID);
$this->_fillTemplates( $templates, 1, $templates_templates);
$languages_templates = & $this->_getLanguages( $fromSiteID);
$this->_fillLanguages( $languages, 1, $languages_templates);
}
}
$lang = JFactory::getLanguage();

$results_components = array();
foreach( $components as $key => $value) {
$extname = $key;
if ( version_compare( JVERSION, '1.6') >= 0) {
$lang->load("$extname.sys", JPATH_ADMINISTRATOR, null, false, false)
|| $lang->load("$extname.sys", JPATH_ADMINISTRATOR, $lang->getDefault(), false, false);
$lang->load("$extname.sys", JPATH_SITE, null, false, false)
|| $lang->load("$extname.sys", JPATH_SITE, $lang->getDefault(), false, false);
$title = JText::_( $extname);
if ( $title == $extname) { $title = null; }
}
else {
$lang->load( $extname);
$title = JText::_( $extname);
if ( $title == $extname) { $title = null; }
}
if ( empty( $title) && !empty( $value[0]) && !empty( $value[0]->name) && $value[0]->name!=$extname) { $title = $value[0]->name; }
if ( empty( $title)) { $title = $this->dbtables->getExtensionDescription( $key, 'name'); }
if ( empty( $title)) { $title = $key; }
$results_components[$title] = &$components[$key];
}
ksort($results_components);

$results_modules = array();
foreach( $modules as $key => $value) {
$extname = $key;
if ( version_compare( JVERSION, '1.6') >= 0) {
$lang->load("$extname.sys", JPATH_ADMINISTRATOR, null, false, false)
|| $lang->load("$extname.sys", JPATH_ADMINISTRATOR, $lang->getDefault(), false, false);
$lang->load("$extname.sys", JPATH_SITE, null, false, false)
|| $lang->load("$extname.sys", JPATH_SITE, $lang->getDefault(), false, false);
$title = JText::_( $extname);
if ( $title == $extname) { $title = null; }
}
else {
$lang->load( $extname);
$title = JText::_( $extname);
if ( $title == $extname) { $title = null; }
}
if ( empty( $title) && !empty( $value[0]) && !empty( $value[0]->name) && $value[0]->name!=$extname) { $title = $value[0]->name; }
if ( empty( $title)) { $title = $this->dbtables->getExtensionDescription( $key, 'name'); }
if ( empty( $title)) { $title = $key; }
$results_modules[$title] = &$modules[$key];
}
ksort($results_modules);

$results_plugins = array();
foreach( $plugins as $key => $value) {
$extname = $key;
if ( version_compare( JVERSION, '1.6') >= 0) {
$lang->load("$extname.sys", JPATH_ADMINISTRATOR, null, false, false)
|| $lang->load("$extname.sys", JPATH_ADMINISTRATOR, $lang->getDefault(), false, false);
$lang->load("$extname.sys", JPATH_SITE, null, false, false)
|| $lang->load("$extname.sys", JPATH_SITE, $lang->getDefault(), false, false);
$title = JText::_( $extname);
if ( $title == $extname) { $title = null; }
}
else {
$lang->load( $extname);
$title = JText::_( $extname);
if ( $title == $extname) { $title = null; }
}
if ( empty( $title) && !empty( $value[0]) && !empty( $value[0]->name) && $value[0]->name!=$extname) { $title = $value[0]->name; }
if ( empty( $title)) { $title = $this->dbtables->getExtensionDescription( $key, 'name'); }
if ( empty( $title)) { $title = $key; }
$results_plugins[$title] = &$plugins[$key];
}
ksort($results_plugins);

$results_templates = array();
foreach( $templates as $key => $value) {
$extname = $key;
if ( version_compare( JVERSION, '1.6') >= 0) {
$lang->load("$extname.sys", JPATH_ADMINISTRATOR, null, false, false)
|| $lang->load("$extname.sys", JPATH_ADMINISTRATOR, $lang->getDefault(), false, false);
$lang->load("$extname.sys", JPATH_SITE, null, false, false)
|| $lang->load("$extname.sys", JPATH_SITE, $lang->getDefault(), false, false);
$title = JText::_( $extname);
if ( $title == $extname) { $title = null; }
}
else {
$lang->load( $extname);
$title = JText::_( $extname);
if ( $title == $extname) { $title = null; }
}
if ( empty( $title) && !empty( $value[0]) && !empty( $value[0]->name) && $value[0]->name!=$extname) { $title = $value[0]->name; }
if ( empty( $title)) { $title = $this->dbtables->getExtensionDescription( $key, 'name'); }
if ( empty( $title)) { $title = $key; }
$results_templates[$title] = &$templates[$key];
}
ksort($results_templates);

$results_languages = array();
foreach( $languages as $key => $value) {
$extname = $key;
if ( version_compare( JVERSION, '1.6') >= 0) {
$lang->load("$extname.sys", JPATH_ADMINISTRATOR, null, false, false)
|| $lang->load("$extname.sys", JPATH_ADMINISTRATOR, $lang->getDefault(), false, false);
$lang->load("$extname.sys", JPATH_SITE, null, false, false)
|| $lang->load("$extname.sys", JPATH_SITE, $lang->getDefault(), false, false);
$title = JText::_( $extname);
if ( $title == $extname) { $title = null; }
}
else {
$lang->load( $extname);
$title = JText::_( $extname);
if ( $title == $extname) { $title = null; }
}
if ( empty( $title) && !empty( $value[0]) && !empty( $value[0]->name) && $value[0]->name!=$extname) { $title = $value[0]->name; }
if ( empty( $title)) { $title = $this->dbtables->getExtensionDescription( $key, 'name'); }
if ( empty( $title)) { $title = $key; }
$results_languages[$title] = &$languages[$key];
}
ksort($results_languages);
$extensions['Components'] = & $results_components;
$extensions['Modules'] = & $results_modules;
$extensions['Plugins'] = & $results_plugins;
$extensions['Templates'] = & $results_templates;
$extensions['Languages'] = & $results_languages;
return $extensions;
}


function &getSiteInfo( $site_id)
{
$site = & Site::getInstance( $site_id);
$site->fromSiteID = $site->getFromSiteID();
$site->mysql_version = Jms2WinFactory::getDBOVersion( $site_id);
$site->mysql_sharing = Jms2WinFactory::isCreateView( $site_id);
return $site;
}


function _getTablesInfo( & $matrix, $column, $site_id)
{
$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
if ( empty( $db)) {
return;
}
$srcPrefix = $db->getPrefix();
$srcPrefix_len = strlen($srcPrefix);
$dbprefix = str_replace('_' , '\_', $srcPrefix);

$db->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
$rows = $db->loadResultArray();
if ( empty( $rows)) {
return;
}

foreach( $rows as $table) {

$like = str_replace('_' , '\_', $table);
$query = "SHOW TABLE STATUS LIKE '$like'";
$db->setQuery( $query );
$obj = $db->loadObject();
if ( !empty( $obj)) {

if ( !empty( $obj->Comment) && strtoupper( substr($obj->Comment, 0, 4)) == 'VIEW') {
$obj->_isView = true;
$obj->_viewFrom = MultisitesDatabase::getViewFrom( $db, $table);
}

else {
$obj->_isView = false;
}
$tablename = '#__' . substr($table, $srcPrefix_len);

if ( !isset( $matrix[$tablename])) {
$matrix[$tablename] = array();
}
$matrix[$tablename][$column] = $obj;
}
}
}


function &getListOfTables( $site_id)
{
static $tables;
if ( !isset( $tables)) {
$tables = array();
$this->_tablespattern = array();
$this->_getTablesInfo( $tables, 0, ':master_db:');
if ( $site_id != ':master_db:') {

$this->_getTablesInfo( $tables, 2, $site_id);

$site = & Site::getInstance( $site_id);
$fromSiteID = $site->getFromSiteID();
if ( $fromSiteID != ':master_db:') {
$this->_getTablesInfo( $tables, 1, $fromSiteID);
}
}

$this->dbtables = & Jms2WinDBTables::getInstance();
foreach( $tables as $key => $columns) {
$tables[$key][3] = & $this->dbtables->getTable( $key);

if ( !empty($tables[$key][2])) {

if ( !empty( $tables[$key][3])) {
$tablepatterns = & $this->dbtables->getMatchingKeys( $tables[$key][3]);
foreach( $tablepatterns as $tablepattern) {

if ( !isset( $this->_tablespattern[$tablepattern])) {
$this->_tablespattern[$tablepattern] = array();
}
$this->_tablespattern[$tablepattern][] = $tables[$key][2];
}
}
}
}
}
return $tables;
}


function hasChildren( $site_id)
{

$model = & JModel2Win::getInstance( 'Manage', 'MultisitesModel');
$sites = & $model->getSites();

for( $i = 0; $i<count($sites); $i++) {
$site = & $sites[$i];
if ( isset( $fromSiteID)) { unset( $fromSiteID); }
if ( isset( $template)) { unset( $template); }

if ( !empty( $site->toPrefix)
&& !empty( $site->fromTemplateID)) {

if ( $site->fromTemplateID == ':master_db:') {
$fromSiteID = ':master_db:';
}

else {
$template = & $site->getTemplate();

if ( !empty( $template) && !empty( $template->fromSiteID)) {
$fromSiteID = $template->fromSiteID;
}
}
}

if ( !empty( $fromSiteID)) {
if ( $fromSiteID == $site_id) {
return true;
}
}
}
return false;
}


function &getTableUsingPattern( $tablepattern)
{
static $none = null;
if ( !empty( $this->_tablespattern[$tablepattern])) {
return $this->_tablespattern[$tablepattern];
}

if ( substr( $tablepattern, -1) == '%') {
$tablepattern = substr( $tablepattern, 0, strlen( $tablepattern)-1);
if ( !empty( $this->_tablespattern[$tablepattern])) {
return $this->_tablespattern[$tablepattern];
}
}
return $none;
}


function convertTreeIntoList( $tree, &$_list) {
foreach ( $tree as $key => $node) {
$_list[$key] = & $tree[$key];
if ( !empty( $node->_treeChildren)) {
$this->convertTreeIntoList( $node->_treeChildren, $_list);
}
}
}


function &getActionsToDo( $enteredvalues)
{
$results = array();
$site_id = & $enteredvalues['site_id'];
$site = & Site::getInstance( $site_id);
$fromSiteID = $site->getFromSiteID(); 
$extName_site_id = $fromSiteID;
if ( empty( $fromSiteID)) {
$extName_site_id = $site_id;
}

$this->dbsharing = & Jms2WinDBSharing::getInstance();
$this->dbtables = & Jms2WinDBTables::getInstance();

$rows = array();


$actions = array();
if ( !empty( $enteredvalues['comActions'])) {
$actions = & $enteredvalues['comActions'];
}

for ( $i=count($actions)-1; $i>=0; $i--) {
if ( $actions[$i] == '[unselected]') {
unset( $actions[$i]);
}
}
foreach( $actions as $action) {
$row = new stdClass();
$list = explode( '|', $action);
$row->option = $list[1]; 
if ( !empty( $site->sitename)) { $row->sitename = $site->sitename; }
$row->name = $this->_getExtName( $extName_site_id, $row->option);
$cmds = explode( '.', $list[0]);
$row->action = $cmds[0]; 
$row->fromSiteID = '';
$row->overwrite = false;
if ( count( $cmds) >= 2) {

if ( $cmds[1] == 'master') {
$row->fromSiteID = ':master_db:'; 
}

else {
$row->fromSiteID = $fromSiteID; 
}
}
if ( $row->action == 'share') {
$row->shareInfos = & $this->dbsharing->getShareInfos( $row->option);
}
else {
$row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
}
$rows[$row->option] = $row;
}

$actions = array();
if ( !empty( $enteredvalues['modActions'])) {
$actions = & $enteredvalues['modActions'];
}

for ( $i=count($actions)-1; $i>=0; $i--) {
if ( $actions[$i] == '[unselected]') {
unset( $actions[$i]);
}
}
foreach( $actions as $action) {
$row = new stdClass();
$list = explode( '|', $action);
$row->option = $list[1]; 
if ( !empty( $site->sitename)) { $row->sitename = $site->sitename; }
$row->name = $this->_getExtName( $extName_site_id, $row->option);
$cmds = explode( '.', $list[0]);
$row->action = $cmds[0]; 
$row->fromSiteID = '';
$row->overwrite = false;
if ( count( $cmds) >= 2) {

if ( $cmds[1] == 'master') {
$row->fromSiteID = ':master_db:'; 
}

else {
$row->fromSiteID = $fromSiteID; 
}
}
if ( $row->action == 'share') {
$row->shareInfos = & $this->dbsharing->getShareInfos( $row->option);
}
else {
$row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
}
$rows[$row->option] = $row;
}

$actions = array();
if ( !empty( $enteredvalues['plgActions'])) {
$actions = & $enteredvalues['plgActions'];
}

for ( $i=count($actions)-1; $i>=0; $i--) {
if ( $actions[$i] == '[unselected]') {
unset( $actions[$i]);
}
}
foreach( $actions as $action) {
$row = new stdClass();
$list = explode( '|', $action);
$row->option = $list[1]; 
if ( !empty( $site->sitename)) { $row->sitename = $site->sitename; }
$row->name = $this->_getExtName( $extName_site_id, $row->option);
$cmds = explode( '.', $list[0]);
$row->action = $cmds[0]; 
$row->fromSiteID = '';
$row->overwrite = false;
if ( count( $cmds) >= 2) {

if ( $cmds[1] == 'master') {
$row->fromSiteID = ':master_db:'; 
}

else {
$row->fromSiteID = $fromSiteID; 
}
}
if ( $row->action == 'share') {
$row->shareInfos = & $this->dbsharing->getShareInfos( $row->option);
}
else {
$row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
}
$rows[$row->option] = $row;
}

$actions = array();
if ( !empty( $enteredvalues['tmplActions'])) {
$actions = & $enteredvalues['tmplActions'];
}

for ( $i=count($actions)-1; $i>=0; $i--) {
if ( $actions[$i] == '[unselected]') {
unset( $actions[$i]);
}
}
foreach( $actions as $action) {
$row = new stdClass();
$list = explode( '|', $action);
$row->type = 'template';
$row->option = $list[1]; 
if ( !empty( $site->sitename)) { $row->sitename = $site->sitename; }
$row->name = $this->_getExtName( $extName_site_id, $row->option, $row->type);
$cmds = explode( '.', $list[0]);
$row->action = $cmds[0]; 
$row->fromSiteID = '';
$row->overwrite = false;
if ( count( $cmds) >= 2) {

if ( $cmds[1] == 'master') {
$row->fromSiteID = ':master_db:'; 
}

else {
$row->fromSiteID = $fromSiteID; 
}
}
if ( $row->action == 'share') {
$row->shareInfos = & $this->dbsharing->getShareInfos( $row->option);
}
else {
$row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
}
$rows[$row->option] = $row;
}

$actions = array();
if ( !empty( $enteredvalues['langActions'])) {
$actions = & $enteredvalues['langActions'];
}

for ( $i=count($actions)-1; $i>=0; $i--) {
if ( $actions[$i] == '[unselected]') {
unset( $actions[$i]);
}
}
foreach( $actions as $action) {
$row = new stdClass();
$list = explode( '|', $action);
$row->type = 'language';
$row->option = $list[1]; 
if ( !empty( $site->sitename)) { $row->sitename = $site->sitename; }
$row->name = $this->_getExtName( $extName_site_id, $row->option, $row->type);
$cmds = explode( '.', $list[0]);
$row->action = $cmds[0]; 
$row->fromSiteID = '';
$row->overwrite = false;
if ( count( $cmds) >= 2) {

if ( $cmds[1] == 'master') {
$row->fromSiteID = ':master_db:'; 
}

else {
$row->fromSiteID = $fromSiteID; 
}
}
if ( $row->action == 'share') {
$row->shareInfos = & $this->dbsharing->getShareInfos( $row->option);
}
else {
$row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
}
$rows[$row->option] = $row;
}

if ( !empty( $rows)) {
$results[$site_id] = $rows;
}



$propagations = & $enteredvalues['comPropagates'];
$overwrites = & $enteredvalues['comOverwrites'];
if ( !empty( $propagations)) {

$childrenTree = & $this->getSiteDependencies( $site_id);
$sites = array();
$this->convertTreeIntoList( $childrenTree, $sites);

foreach( $propagations as $indice => $option) {

if ( !empty( $rows[$option])) {

$row = $rows[$option];
}
else {
$row = new stdClass();
$row->action = 'table'; 
$row->fromSiteID = $site_id; 
$row->option = $option;
$row->name = $this->_getExtName( $extName_site_id, $row->option);
$row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
}
if ( !empty( $overwrites[$indice]) && $overwrites[$indice] == $option) {
$row->overwrite = true;
}
else {
$row->overwrite = false;
}

foreach( $sites as $site) {
if ( empty( $results[$site->id])) {
$results[$site->id] = array();
}
$results[$site->id][$option] = $row;
}
}
}


$propagations = & $enteredvalues['modPropagates'];
$overwrites = & $enteredvalues['modOverwrites'];
if ( !empty( $propagations)) {

$childrenTree = & $this->getSiteDependencies( $site_id);
$sites = array();
$this->convertTreeIntoList( $childrenTree, $sites);

foreach( $propagations as $indice => $option) {

if ( !empty( $rows[$option])) {

$row = $rows[$option];
}
else {
$row = new stdClass();
$row->action = 'table'; 
$row->fromSiteID = $site_id; 
$row->option = $option;
$row->name = $this->_getExtName( $extName_site_id, $row->option);
$row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
}
if ( !empty( $overwrites[$indice]) && $overwrites[$indice] == $option) {
$row->overwrite = true;
}
else {
$row->overwrite = false;
}

foreach( $sites as $site) {
if ( empty( $results[$site->id])) {
$results[$site->id] = array();
}
$results[$site->id][$option] = $row;
}
}
}


$propagations = & $enteredvalues['plgPropagates'];
$overwrites = & $enteredvalues['plgOverwrites'];
if ( !empty( $propagations)) {

$childrenTree = & $this->getSiteDependencies( $site_id);
$sites = array();
$this->convertTreeIntoList( $childrenTree, $sites);

foreach( $propagations as $indice => $option) {

if ( !empty( $rows[$option])) {

$row = $rows[$option];
}
else {
$row = new stdClass();
$row->action = 'table'; 
$row->fromSiteID = $site_id; 
$row->option = $option;
$row->name = $this->_getExtName( $extName_site_id, $row->option);
$row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
}
if ( !empty( $overwrites[$indice]) && $overwrites[$indice] == $option) {
$row->overwrite = true;
}
else {
$row->overwrite = false;
}

foreach( $sites as $site) {
if ( empty( $results[$site->id])) {
$results[$site->id] = array();
}
$results[$site->id][$option] = $row;
}
}
}


$propagations = & $enteredvalues['tmplPropagates'];
$overwrites = & $enteredvalues['tmplOverwrites'];
if ( !empty( $propagations)) {

$childrenTree = & $this->getSiteDependencies( $site_id);
$sites = array();
$this->convertTreeIntoList( $childrenTree, $sites);

foreach( $propagations as $indice => $option) {

if ( !empty( $rows[$option])) {

$row = $rows[$option];
}
else {
$row = new stdClass();
$row->action = 'table'; 
$row->fromSiteID = $site_id; 
$row->option = $option;
$row->name = $this->_getExtName( $extName_site_id, $row->option);
$row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
}
if ( !empty( $overwrites[$indice]) && $overwrites[$indice] == $option) {
$row->overwrite = true;
}
else {
$row->overwrite = false;
}

foreach( $sites as $site) {
if ( empty( $results[$site->id])) {
$results[$site->id] = array();
}
$results[$site->id][$option] = $row;
}
}
}


$propagations = & $enteredvalues['langPropagates'];
$overwrites = & $enteredvalues['langOverwrites'];
if ( !empty( $propagations)) {

$childrenTree = & $this->getSiteDependencies( $site_id);
$sites = array();
$this->convertTreeIntoList( $childrenTree, $sites);

foreach( $propagations as $indice => $option) {

if ( !empty( $rows[$option])) {

$row = $rows[$option];
}
else {
$row = new stdClass();
$row->action = 'table'; 
$row->fromSiteID = $site_id; 
$row->option = $option;
$row->name = $this->_getExtName( $extName_site_id, $row->option);
$row->tablesInfos = & $this->dbtables->getTablesInfos( $row->option);
}
if ( !empty( $overwrites[$indice]) && $overwrites[$indice] == $option) {
$row->overwrite = true;
}
else {
$row->overwrite = false;
}

foreach( $sites as $site) {
if ( empty( $results[$site->id])) {
$results[$site->id] = array();
}
$results[$site->id][$option] = $row;
}
}
}
return $results;
}


function &doAction( $enteredvalues)
{
$errors = array( 'Invalid action');
$action = $enteredvalues['action'];
$option = $enteredvalues['option'];
$overwrite = $enteredvalues['overwrite'];
$fromSiteID = $enteredvalues['fromSiteID'];
$toSiteID = $enteredvalues['site_id'];
if ( !empty( $fromSiteID)) {
$fromDB =& Jms2WinFactory::getMultiSitesDBO( $fromSiteID);
}
$toDB =& Jms2WinFactory::getMultiSitesDBO( $toSiteID);
$toConfig =& Jms2WinFactory::getMultiSitesConfig( $site_id);
if ( $action == 'table') {

$this->dbtables = & Jms2WinDBTables::getInstance();
$tablesInfos = & $this->dbtables->getTablesInfos( $option);

$tablePatterns = array();
foreach( $tablesInfos as $xmlTable) {
$name = $xmlTable->attributes( 'name');
if ( !empty( $name)) {
$tablePatterns[$name] = $name;
}
}
$errors = MultisitesDatabase::copyDbTablePatterns( $fromDB, $toDB, $toConfig, $tablePatterns, true, $overwrite);
if ( !empty( $errors)) {
return $errors;
}

$errors = MultisitesDatabase::installNewExtension( $fromDB, $toDB, $option, $overwrite);
}
else if ( $action == 'share' || $action == 'view') {
$this->dbsharing = & Jms2WinDBSharing::getInstance();
$shareInfo = & $this->dbsharing->getShareInfos( $option);

$tablesInfos = & $this->dbsharing->getTables( $shareInfo);

$tablePatterns = array();
foreach( $tablesInfos as $xmlTable) {
$name = $xmlTable->attributes( 'name');
if ( !empty( $name)) {
$tablePatterns[$name] = $name;
}
}
$toConfig =& Jms2WinFactory::getMultiSitesConfig( $toSiteID);
$errors = MultisitesDatabase::createViews( $fromDB, $toDB, array( 'table' => $tablePatterns), $toConfig, $toSiteID);
if ( !empty( $errors)) {
return $errors;
}

$errors = MultisitesDatabase::installNewExtension( $fromDB, $toDB, $option, $overwrite);
}
else if ( $action == 'uninstall') {

$this->dbtables = & Jms2WinDBTables::getInstance();
$tablesInfos = & $this->dbtables->getTablesInfos( $option);

$tablePatterns = array();
foreach( $tablesInfos as $xmlTable) {
$name = $xmlTable->attributes( 'name');
if ( !empty( $name)) {
$tablePatterns[] = $name;
}
}
$errors = MultisitesDatabase::dropTablePatterns( $toDB, $tablePatterns);
if ( !empty( $errors)) {
return $errors;
}
$errors = MultisitesDatabase::uninstallExtension( $toDB, $option);
}
return $errors;
}


function &doActions( $enteredvalues)
{
$errors = array( 'Invalid action');
$toSiteID = $enteredvalues['site_id'];
$nbActions = $enteredvalues['nbActions'];
$actions = $enteredvalues['actions'];
$options = $enteredvalues['options'];
$types = $enteredvalues['types'];
$overwrites = $enteredvalues['overwrites'];
$fromSiteIDs = $enteredvalues['fromSiteIDs'];
$toDB =& Jms2WinFactory::getMultiSitesDBO( $toSiteID);
$toConfig =& Jms2WinFactory::getMultiSitesConfig( $toSiteID);

$this->dbtables = & Jms2WinDBTables::getInstance();
$this->dbsharing = & Jms2WinDBSharing::getInstance();
$uninstallPatterns = array(); 
$uninstallOptions = array(); 
$sharePatterns = array(); 
$tablePatterns = array(); 
$installOptions = array(); 
for ( $i=0; $i<$nbActions; $i++) {
$action = $actions[$i];
$option = $options[$i];
$type = !empty( $types[$i]) ? $types[$i] : null;
if ( empty( $fromSiteIDs[$i])) {
$fromSiteID = ':master_db:';
}
else {
$fromSiteID = $fromSiteIDs[$i];
}
if ( !empty( $overwrites[$i])) {
$overwrite = $overwrites[$i];
}
else {
$overwrite = false;
}
if ( $action == 'table') {

$tablesInfos = & $this->dbtables->getTablesInfos( $option);

foreach( $tablesInfos as $xmlTable) {
$name = $xmlTable->attributes( 'name');
if ( !isset( $tablePatterns[$fromSiteID])) {
$tablePatterns[$fromSiteID] = array();
}
if ( !empty( $name)) {
$tablePatterns[$fromSiteID][$name] = $name;
}
}
if ( !isset( $installOptions[$fromSiteID])) {
$installOptions[$fromSiteID] = array();
}
$installOptions[$fromSiteID][$option] = array( 'overwrite' => $overwrite, 'type' => $type);
}
else if ( $action == 'share' || $action == 'view') {
$shareInfo = & $this->dbsharing->getShareInfos( $option);

$tablesInfos = & $this->dbsharing->getTables( $shareInfo);

foreach( $tablesInfos as $xmlTable) {
$name = $xmlTable->attributes( 'name');
if ( !isset( $sharePatterns[$fromSiteID])) {
$sharePatterns[$fromSiteID] = array();
$sharePatterns[$fromSiteID]['table'] = array();
}
if ( !empty( $name)) {
$sharePatterns[$fromSiteID]['table'][$name] = $name;
}
}
if ( !isset( $installOptions[$fromSiteID])) {
$installOptions[$fromSiteID] = array();
}
$installOptions[$fromSiteID][$option] = array( 'overwrite' => $overwrite, 'type' => $type);
}
else if ( $action == 'uninstall') {

$tablesInfos = & $this->dbtables->getTablesInfos( $option);

foreach( $tablesInfos as $xmlTable) {
$name = $xmlTable->attributes( 'name');
if ( !empty( $name)) {
$drop = $xmlTable->attributes( 'drop');

if ( $name == '[none]') {} 

else if ( !empty( $drop) && strtolower( $drop) == 'no') {} 
else {
$uninstallPatterns[$name] = $name;
}
}
}
$uninstallOptions[$option] = $type;
}
} 


if ( !empty( $uninstallPatterns)) {
$errors = MultisitesDatabase::dropTablePatterns( $toDB, $uninstallPatterns);
if ( !empty( $errors)) {
return $errors;
}
}

if ( !empty( $uninstallOptions)) {
$errors = array();
foreach( $uninstallOptions as $option => $type) {
$results = MultisitesDatabase::uninstallExtension( $toDB, $option, $type);
$errors = array_merge( $errors, $results);
}
if ( !empty( $errors)) {
return $errors;
}
}


foreach( $sharePatterns as $fromSiteID => $fromSiteIDPatterns) {
$fromDB =& Jms2WinFactory::getMultiSitesDBO( $fromSiteID);
$errors = MultisitesDatabase::createViews( $fromDB, $toDB, $fromSiteIDPatterns, $toConfig, $toSiteID);
if ( !empty( $errors)) {
return $errors;
}
}


foreach( $tablePatterns as $fromSiteID => $fromSiteIDPatterns) {
$fromDB =& Jms2WinFactory::getMultiSitesDBO( $fromSiteID);
$errors = MultisitesDatabase::copyDbTablePatterns( $fromDB, $toDB, $toConfig, $fromSiteIDPatterns, false, $overwrite);
if ( !empty( $errors)) {
return $errors;
}
}


foreach( $installOptions as $fromSiteID => $values) {
$fromDB = & Jms2WinFactory::getMultiSitesDBO( $fromSiteID);
$errors = array();
foreach( $installOptions[$fromSiteID] as $option => $params) {
$overwrite = $params['overwrite'];
$type = $params['type'];

$results = MultisitesDatabase::installNewExtension( $fromDB, $toDB, $option, $overwrite, $type);
$errors = array_merge( $errors, $results);
}
}
return $errors;
}
} 

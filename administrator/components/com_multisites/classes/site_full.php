<?php
// file: site_full.php.
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
require_once( dirname( dirname( __FILE__)) .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');



class SiteFull extends JObject
{


function getJ15_Components( &$db, $extension_id=null)
{
if ( !empty( $extension_id)) { $and_extension_id = ' AND c.id='.$extension_id; }
else { $and_extension_id = ''; }
$query = 'SELECT id as extension_id, c.name, "component" as type, c.option as element, "" as folder, 1 as client_id FROM #__components as c WHERE parent=0 AND c.id>33 AND c.iscore=0'.$and_extension_id.' ORDER BY c.id';
$db->setQuery( $query);
$components = $db->loadObjectList();
return $components;
}


function getComponents_j25( &$db, $extension_id=null)
{
if ( !empty( $extension_id)) { $and_extension_id = ' AND e.extension_id='.$extension_id; }
else { $and_extension_id = ''; }
$query = 'SELECT e.extension_id, e.name, e.type, e.element, e.folder, e.client_id FROM #__extensions as e LEFT JOIN #__schemas as s ON e.extension_id=s.extension_id WHERE s.extension_id IS NULL AND e.extension_id >= 10000 AND e.client_id=1 AND `type` = "component" '.$and_extension_id.' ORDER BY e.extension_id';
$db->setQuery( $query);
$components = $db->loadObjectList();
return $components;
}

function getTableStructure( &$db, $tableName)
{
$db->setQuery( "SHOW CREATE TABLE `$tableName`");
$assoc = $db->loadAssoc();
if ( !empty( $assoc)) {
return $assoc['Create Table'];
}
return $false;
}

function getComponentDBStuctures( $site_id, $tablePatterns)
{
$db =& Jms2WinFactory::getMultiSitesDBO( $site_id);
$results = array();
foreach( $tablePatterns as $pattern) {
$like = str_replace('_' , '\_', $db->replacePrefix( $pattern) );
$db->setQuery( 'SHOW TABLES LIKE \''.$like.'\'' );
$tables = $db->loadResultArray();
if ( !empty( $tables)) {
foreach($tables as $table) {
$results[] = $this->getTableStructure( $db, $table);
}
}
}
return $results;
}


function &_getRoot( &$slave_manifest)
{
if ( is_a( $slave_manifest, 'JSimpleXML') && !empty( $slave_manifest->document)) {
$root =& $slave_manifest->document;
}
else if ( is_a( $slave_manifest, 'JXMLElement') ) {
$root =& $slave_manifest;
}
else {
$root =& $slave_manifest;
}
return $root;
}

function xmlGetAttribue( $xmlelement, $attribute)
{
if ( method_exists( $xmlelement, 'getAttribute')) {
return $xmlelement->getAttribute( $attribute);
}
else if ( is_a( $xmlelement, 'JSimpleXML') && !empty( $xmlelement->document)) {
return $this->xmlGetAttribue($xmlelement->document, $attribute);
}
return $xmlelement->attributes( $attribute);
}

function ignoreSQLDetection( $dmrow)
{
static $instances;
if ( !isset( $instances)) {
require_once( dirname(__FILE__).'/site_ignoresqldetection.php');
$instances = $ignoreExtensions;
}
$folder = !empty( $dmrow->folder) ? $dmrow->folder.'|' : '';
$key="$dmrow->type$folder|$dmrow->element";
if ( !empty( $instances[$key])) {
return $instances[$key];
}
return false;
}


function loadManifest( &$dmrow)
{
jimport('joomla.filesystem.folder');

if ( JFolder::exists( $dmrow->jpath_extension)) {
$xmlFilesInDir = JFolder::files( $dmrow->jpath_extension, '\.xml$', false, true);
}
if ( !empty($xmlFilesInDir)) {
$type_conversion = array( 'component' => 'com_',
'module' => 'mod_',
'plugin' => 'plg_');
foreach ($xmlFilesInDir as $filename) {
if ($data = JApplicationHelper::parseXMLInstallFile( $filename)) {

if ( count($xmlFilesInDir) <= 1) {
$dmrow->manifest_filename = $filename;
break;
}

else {
$data_name = strtolower( JFilterInput::getInstance()->clean((string) $data['name'], 'cmd'));
if ( !empty( $type_conversion[$dmrow->type])) { $com_data_name = $type_conversion[$dmrow->type].$data_name; }
else { $com_data_name = 'com_'.$data_name; }
$dm_name = strtolower( JFilterInput::getInstance()->clean((string) $dmrow->name, 'cmd'));
if ( $data['type'] == $dmrow->type
&& ($data_name == $dm_name || $com_data_name == $dm_name)
&& (empty( $dmrow->folder) || $data['group'] == $dmrow->folder)
)
{
$dmrow->manifest_filename = $filename;
break;
}
unset( $data);
}
}
}
}
if ( !empty( $data)) {
$dmrow->code_version = $data['version'];
if ( $this->ignoreSQLDetection( $dmrow)) {}
else {

require_once( dirname( __FILE__).'/dbtables.php');
$tables = array();
$dbtables =& Jms2WinDBTables::getInstance();
if ( !empty( $dmrow->folder)) { $tablesInfo =& $dbtables->getTablesInfos( $dmrow->folder.'/'.$dmrow->element); }
else { $tablesInfo =& $dbtables->getTablesInfos( $dmrow->element); }
if ( !empty( $tablesInfo)) {
foreach( $tablesInfo as $tableInfo) {
$pattern = trim( $tableInfo->attributes( 'name'));
if ( !empty( $pattern) && $pattern != '[none]') {
$tables[] = $pattern;
}
}
$tables = array_unique( $tables);
}

$dmrow->master_structures = $this->getComponentDBStuctures( ':master_db:', $tables);

$fromSiteID = $this->getFromSiteID();
if ( !empty( $fromSiteID)) {
$dmrow->template_structures = $this->getComponentDBStuctures( $fromSiteID, $tables);
}
$xml =& Jms2WinFactory::getXMLParser('Simple');
if ( $xml->loadFile( $dmrow->manifest_filename)) {
$root =& $this->_getRoot( $xml);

if ( !empty( $root->install[0]->queries)) {
$dmrow->install_queries = array();
foreach( $root->install[0]->queries as $inst_queries) {
if ( !empty( $inst_queries->query)) {
foreach( $inst_queries->query as $query) {
$data = trim( $query->data());
if ( !empty( $data)) {
$dmrow->install_queries[] = $data;
}
}
}
}
}

if ( !empty( $root->install[0]->sql[0]->file)) {
$dmrow->install_sqlfiles = array();
foreach( $root->install[0]->sql[0]->file as $file) {
$obj = new stdClass();
$obj->filename = trim( $file->data());
$driver = $this->xmlGetAttribue( $file, 'driver');
if ( !empty( $driver)) { $obj->driver = $driver; }
$charset = $this->xmlGetAttribue( $file, 'charset');
if ( !empty( $charset)) { $obj->charset = $charset; }

$dmrow->install_sqlfiles[] = $obj;
}
}


if ( empty( $dmrow->install_queries) && empty( $dmrow->install_sqlfiles)) {
$dmrow->discover_sqlfiles = JFolder::files( $dmrow->jpath_extension, '\.sql$', true, true);

if ( !empty( $dmrow->discover_sqlfiles)) {
for ( $i=0; $i<count( $dmrow->discover_sqlfiles); $i++) {
if ( preg_match( '#uninstall#i', $dmrow->discover_sqlfiles[$i])) {
unset( $dmrow->discover_sqlfiles[$i]);
}
}

$dmrow->discover_sqlfiles = array_values( $dmrow->discover_sqlfiles);
}
}
}
}

if ( !empty( $dmrow->master_structures)
|| !empty( $dmrow->template_structures)
|| !empty( $dmrow->install_queries)
|| !empty( $dmrow->install_sqlfiles)
|| !empty( $dmrow->discover_sqlfiles)
)
{

$dmrow->version_id = $dmrow->code_version;
}
else {
$dmrow->version_id = 'No DB';
}
}
}


function getExtensionInfo_component( &$dmrow, $infoTasks=array())
{
$dmrow->jpath_extension = JPath::clean( $this->jpath_root.'/administrator/components/'.$dmrow->element);
if ( empty( $infoTasks)) {
$this->loadManifest( $dmrow);
}
}


function getExtensionInfo_module( &$dmrow, $infoTasks=array(), $front_end=true)
{
$dmrow->jpath_extension = JPath::clean( $this->jpath_root.'/modules/'.$dmrow->element);
if ( empty( $infoTasks)) {
$this->loadManifest( $dmrow);
}
}


function getExtensionInfo_plugin( &$dmrow, $infoTasks=array())
{
$dmrow->jpath_extension = JPath::clean( $this->jpath_root.'/plugins/'.$dmrow->folder.'/'.$dmrow->element);
if ( empty( $infoTasks)) {
$this->loadManifest( $dmrow);
}
}
} 



class SiteVariant extends SiteFull {}

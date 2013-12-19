<?php
// file: dbtables.php.
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
jimport( 'joomla.filesystem.path');
require_once( dirname( dirname( __FILE__)) .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
require_once( dirname( __FILE__) .DS. 'treesearch.php');




class Jms2WinDBTables
{
var $success = false; 
var $_xml = null;

static function &getInstance()
{
static $instance;
if (!is_object($instance))
{
$instance = new Jms2WinDBTables();
}
return $instance;
}

function Jms2WinDBTables()
{
$this->success = false;
}

function getConfigFilename()
{
$filename = dirname( dirname( __FILE__))
.DS. 'patches'
.DS. 'sharing'
.DS. 'dbtables.xml'
;
return $filename;
}

function getXML()
{
return $this->_xml;
}


function _computeParents( &$node)
{

if ( $node->name() == 'table') {

$name = $node->attributes( 'name');
$this->_indexTablePatterns->add( $name, $node);
}
$option = $node->attributes( 'option');
if ( !empty( $option)) {
if ( !isset( $this->_extOptions[$option])) {
$this->_extOptions[$option] = array();
}
$this->_extOptions[$option][] = $node;
}
if ( empty( $node->_children)) {
return;
}
for ($i=count($node->_children)-1;$i>=0;$i--) {
$child = & $node->_children[$i];
if ( !isset( $child->_parent)) {
$child->_parent = & $node;
}
$this->_computeParents( $child);
}
}

function isLoaded()
{
if ( isset( $this->_xml)) {
return true;
}
return false;
}


function load()
{

if ( isset( $this->_xml)) {
$this->success = true;
return $this->success;
}
$this->success = false;
$this->_xml = null;
$xmlpath = $this->getConfigFilename();
$xmlpath_cache = $xmlpath.'.cache';

if ( file_exists($xmlpath))
{

if ( file_exists( $xmlpath_cache)) {
$dt_xmlpath = filemtime( $xmlpath);
$dt_xmlpath_cache = filemtime( $xmlpath_cache);

if ( $dt_xmlpath_cache > $dt_xmlpath) {

$xml =& Jms2WinFactory::getXMLParser('Simple');

$cache_content = file_get_contents( $xmlpath_cache);
$obj = unserialize( $cache_content);

if ( $obj !== false && !empty( $obj->success)) {

foreach( get_object_vars( $obj) as $k => $v) {
$this->$k = $v;
}
return $this->success;
}
}
}

$xml =& Jms2WinFactory::getXMLParser('Simple');
if ( $xml->loadFile($xmlpath)) {

JPluginHelper::importPlugin('multisites');
$results = JFactory::getApplication()->triggerEvent('onDBTableLoaded', array ( & $xml));
$this->_xml =& $xml->document;
$this->_extOptions = array();
$this->_indexTablePatterns = new Jms2WinTreeSearch();
$this->_computeParents( $this->_xml);
$this->success = true;
$cache_content = serialize( $this);
file_put_contents( $xmlpath_cache, $cache_content);
}
}
return $this->success;
}


function &getTable( $aTablePattern)
{
if ( !$this->isLoaded()) {
$this->load();
}
return $this->_indexTablePatterns->getKey( $aTablePattern);
}


function &getMatchingKeys( $aSolution)
{
if ( empty( $aSolution)) {
return array();
}
return $this->_indexTablePatterns->getKeyString( $aSolution);
}


function & getPath()
{
$path = array();
$node = $this;
$path[] = $node;
while( !empty( $node->_parent)) {
array_unshift ($path, $node);
$node = $node->_parent;
}
return $path;
}


function getPathName( $sep = '/', $ignoreLeaf = false)
{
$pathStr = '';
$node = $this;
do {
$name = $node->attributes( 'name');
if ( empty( $name)) {
$name = $node->name();
}
if ( $ignoreLeaf) {
$ignoreLeaf = false;
}
else {
$pathStr = $sep . $name . $pathStr;
}
$node = $node->_parent;
} while( !empty( $node));
return $pathStr;
}


function _collectTable( &$node, &$tables)
{

if ( $node->name() == 'table') {
$tables[] = $node;
}
if ( empty( $node->_children)) {
return;
}
for ($i=count($node->_children)-1;$i>=0;$i--) {
$child = & $node->_children[$i];
$this->_collectTable( $child, $tables);
}
}


function &getTablesInfos( $option)
{
$tables = array();
if ( !$this->isLoaded()) {
$this->load();
}
if ( isset( $this->_extOptions[$option])) {
foreach( $this->_extOptions[$option] as $xml) {
$this->_collectTable( $xml, $tables);
}
}
return $tables;
}


function &getExtensionDescription( $option, $attribute=null)
{
$result = null;
if ( !empty( $this->_extOptions[$option])) {
if ( !empty( $attribute)) {
$result = $this->_extOptions[$option][0]->attributes( $attribute);
}
else {
$result = &$this->_extOptions[$option];
}
}
return $result;
}
}

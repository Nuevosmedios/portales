<?php
// file: dbsharing.php.
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




class Jms2WinDBSharing
{
var $success = false; 
var $_xml = null;

function &getInstance()
{
static $instance;
if (!is_object($instance))
{
$instance = new Jms2WinDBSharing();
}
return $instance;
}

function Jms2WinDBSharing()
{
$this->success = false;
}

function getConfigFilename()
{
static $instance;
if ( !empty( $instance)) {
return $instance;
}
if ( version_compare( JVERSION, '1.6') >= 0) {
$parts = explode( '.', JVERSION);
$suffix = $parts[0].$parts[1];
for ( ; $suffix > 10; $suffix--) {
$instance = dirname( dirname( __FILE__))
.DS. 'patches'
.DS. 'sharing'
.DS. 'dbsharing_'.$suffix.'.xml'
;
if ( file_exists( $instance)) {
return $instance;
}
}
$instance = null;
}
else {
$instance = dirname( dirname( __FILE__))
.DS. 'patches'
.DS. 'sharing'
.DS. 'dbsharing.xml'
;
}
return $instance;
}

function getXML()
{
return $this->_xml;
}


function getSharedTables( $dbsharing)
{
$results = array();
$results['table'] = array();
$results['tableexcluded'] = array();
$xml = & $this->_xml;
$params =& $xml->getElementByPath('params');
foreach( $dbsharing as $key => $value) {

foreach( $params->children() as $param) {

if ( $param->attributes( 'name') == $key) {
$type = $param->attributes( 'type');
if ( $type == 'checkbox') {
$tables = $param->getElementByPath('tables');
if ( !empty( $tables)) {
foreach( $tables->children() as $table) {
$name = $table->attributes( 'name');
if ( $table->name() == 'tableexcluded') { $results['tableexcluded'][$name] = $name; }
else if ( $table->name() == 'tablewhere') { $results['table'][$name] = $name;
$results['tablewhere'][$name] = $table->attributes( 'where');
}
else { $results['table'][$name] = $name; }

foreach( $table->children() as $xmlqueries) {
if ( $xmlqueries->name() == 'queries') {

$results['tablequeries'][$name][] = $xmlqueries;
}
}
}
}
}

else {

foreach( $param->children() as $option) {
if ( $option->attributes( 'value') == $value) {
$tables = $option->getElementByPath('tables');
if ( !empty( $tables)) {
foreach( $tables->children() as $table) {
$name = $table->attributes( 'name');
if ( $table->name() == 'tableexcluded') { $results['tableexcluded'][$name] = $name; }
else if ( $table->name() == 'tablewhere') { $results['table'][$name] = $name;
$results['tablewhere'][$name] = $table->attributes( 'where');
}
else { $results['table'][$name] = $name; }

foreach( $table->children() as $xmlqueries) {
if ( $xmlqueries->name() == 'queries') {

$results['tablequeries'][$name][] = $xmlqueries;
}
}
}
}
break;
}
} 
}
break;
}
} 
}
return $results;
}


function _getTables( &$children, &$tables)
{
if ( empty( $children)) {
return;
}
foreach( $children as $child) {
if ( $child->name() == 'table') {
$tables[] = $child;
}
else if ( $child->name() == 'tableexcluded') {
$child->excluded = true;
$tables[] = $child;
}
else if ( $child->name() == 'tablewhere') {
$tables[] = $child;
}
$smallChildren = $child->children();
Jms2WinDBSharing::_getTables( $smallChildren, $tables);
}
}


function &getTables( $xmlnode)
{
$tables = array();
Jms2WinDBSharing::_getTables( $xmlnode, $tables);
return $tables;
}


function cleanupOnCondition( &$node)
{
for ($i=count($node->_children)-1;$i>=0;$i--) {
$child = & $node->_children[$i];
$condition = $child->attributes( 'condition');
if ( !empty($condition)) {
$path = str_replace( '{root}', JPATH_ROOT, $condition);
if ( !file_exists( JPath::clean( $path))) {
$node->removeChild( $child);
}
}
else {
$node->_children[$i] = $this->cleanupOnCondition( $child);
}
}
return( $node);
}


function _indexExtensions( &$node)
{
for ($i=count($node->_children)-1;$i>=0;$i--) {
$child = & $node->_children[$i];
$condition = $child->attributes( 'condition');
if ( !empty($condition)) {
$parts = explode( '/', $condition);
$n = count( $parts);
if ( $n > 2) {
$dir = $parts[ $n - 2];
if ( $dir == 'components'
|| $dir == 'modules'
) {
$ext_name = $parts[ $n - 1];
}
else if ( $parts[ $n - 3] == 'plugins') {
$ext_name = $dir .DS. $parts[ $n - 1];
}
}
if ( !isset( $this->_extensions[$ext_name])) {
$this->_extensions[$ext_name] = array();
}
$this->_extensions[$ext_name][] = & $child;
}
else {
$this->_indexExtensions( $child);
}
}
}


function indexExtensions( &$node)
{
$this->_extensions = array();
$this->_indexExtensions( $this->_xml);
}


function &getShareInfos( $ext_name)
{
if ( !$this->isLoaded()) {
$this->load();
}
if ( isset( $this->_extensions[ $ext_name])) {
return $this->_extensions[ $ext_name];
}
$none = array();
return $none;
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

if ( file_exists($xmlpath))
{
$xml =& Jms2WinFactory::getXMLParser('Simple');
if ($xml->loadFile($xmlpath)) {

JPluginHelper::importPlugin('multisites');
$mainframe = &JFactory::getApplication();
$results = $mainframe->triggerEvent('onDBSharingLoaded', array ( & $xml));
$this->_xml =& $xml->document;
$this->cleanupOnCondition( $this->_xml);
$this->indexExtensions( $this->_xml);
$this->success = true;
}
}
return $this->success;
}
}

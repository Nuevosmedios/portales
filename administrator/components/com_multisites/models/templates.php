<?php
// file: templates.php.
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
require_once( dirname( dirname( __FILE__)) .DS. 'classes' .DS. 'site.php');
require_once( dirname( dirname( __FILE__)) .DS. 'classes' .DS. 'template.php');
require_once( dirname( dirname( __FILE__)) .DS. 'classes' .DS. 'utils.php');


@include_once( dirname( dirname( __FILE__)) .DS. 'multisites_path.cfg.php');

if ( !defined( 'JPATH_MULTISITES')) {
define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'application' .DS. 'component' .DS. 'model2win.php');




class MultisitesModelTemplates extends JModel2Win
{

var $_modelName = 'templates';
var $_template = null;
var $_countAll = 0;


function getTemplateFilename()
{
$filename = JPATH_MULTISITES .DS. 'config_templates.php';
return $filename;
}


function &getTemplates()
{

$filters = $this->getState( 'filters');
if ( !is_null($filters)) {

if ( isset($filters['hosts']) && $filters['host'] != '[unselected]') {
$filter_host = $filters['host'];
}

if ( $filters['db'] != '[unselected]') {
$filter_db = $filters['db'];
}
}

$rows = array();
$filename = $this->getTemplateFilename();
@include( $filename);
$loadExtraInfo = false;
if ( isset( $templates)) {

if ( !empty( $filter_host) || !empty( $filter_db)) {
foreach( $templates as $key => $template) {
$site = new Site();
$site->load( $template['fromSiteID']);
$template['fromHost'] = $site->host;
$template['fromDB'] = $site->db;
$template['fromPrefix'] = $site->dbprefix;
if ( !empty( $filter_host) && !empty( $filter_db)){
if ( $template['fromHost'] == $filter_host
&& $template['fromDB'] == $filter_db
)
{
$rows[$key] = $template;
}
}
if ( !empty( $filter_host) && empty( $filter_db)){
if ( $template['fromHost'] == $filter_host)
{
$rows[$key] = $template;
}
}
else if ( empty( $filter_host) && !empty( $filter_db)){
if ( $template['fromDB'] == $filter_db)
{
$rows[$key] = $template;
}
}
}
}
else {
$rows = $templates;
$loadExtraInfo = true;
}
}
$this->_countAll = count( $rows);

foreach( $rows as $key => $row) {
$rows[$key]['id'] = $key;
}

if ( !is_null($filters)) {
if ( !empty( $filters['order'])) {
$colname = $filters['order'];
$sortedrows = array();
$i = 0;
foreach( $rows as $row){
$colValue = isset( $row[$colname]) ? $row[$colname] : '';
$key = $colValue . '.' . substr( "00".strval($i++), -3);
$sortedrows[$key] = $row;
}

if ( !empty( $filters['order_Dir']) && $filters['order_Dir'] =='desc') {
krsort($sortedrows);
$rows = $sortedrows;
}

else {
ksort($sortedrows);
$rows = $sortedrows;
}
}
}

if ( !is_null($filters)) {

if ( $filters['limit'] > 0) {

$rows = array_slice( $rows, $filters['limitstart'], $filters['limit'] );
}
}

if ( $loadExtraInfo) {
foreach( $rows as $key => $row) {
$site = new Site();
$site->load( $row['fromSiteID']);
$rows[$key]['fromDB'] = $site->db;
$rows[$key]['fromPrefix'] = $site->dbprefix;
}
}
return $rows;
}


function getCountAll()
{
return $this->_countAll;
}

function setFilters( &$filters)
{
$this->setState( 'filters', $filters);
}

function removeFilters()
{
$this->setState( 'filters', null);
}


function getCurrentRecord()
{
if ($this->_template == null) {
$this->_template = new Jms2WinTemplate();

if ($id = JRequest::getVar('id', false, '', 'string')) {
$this->_template->load($id);
}

else {
$cid = JRequest::getVar('cid', array(), '', 'array');

if ( !empty( $cid)) {
foreach( $cid as $id) {
$this->_template->load($id);
break;
}
}
}
}
return $this->_template;
}


function getNewRecord()
{
if ($this->_template == null) {
$this->_template = new Jms2WinTemplate();
}
return $this->_template;
}


function write( $templates)
{
$filename = $this->getTemplateFilename();

$config = "<?php\n";
$config .= "if( !defined( '_JEXEC' )) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); \n\n";
$config .= '$templates = array();'
. "\n";
foreach( $templates as $key => $values) {
$config .= "\$templates['$key'] = array(\n";
$leadingSpaces = '                           ';
$sep2 = $leadingSpaces;
foreach( $values as $key2 => $value) {
if ( is_array( $value)) {
$config .= $sep2 . "'$key2' => array( " . MultisitesUtils::CnvArray2Str( $leadingSpaces.'     ', $value) . ")";
}
else {
$config .= $sep2 . "'$key2' => '" . addslashes($value) ."'";
}
$sep2 = ",\n" . $leadingSpaces;
}
$config .= ");\n";
}
if ( !JFile::write( $filename, $config)) {
$this->setError( JText::sprintf( 'TEMPLATE_WRITE_ERR', $filename) );
return false;
}
return true;
}


function save( $enteredvalues, $reset=false)
{

$templates = array();
$filename = $this->getTemplateFilename();
@include( $filename);
if ( $reset && !empty( $enteredvalues['id'])) {
unset( $templates[ $enteredvalues['id']]);
}

foreach( $enteredvalues as $key => $value) {
if ( strstr('*id*isnew*', $key)) {}
else {
$templates[ $enteredvalues['id']][$key] = $value;
}
}
ksort( $templates);
return $this->write( $templates);
}


function canDelete( $id = null)
{

$template = new Jms2WinTemplate();
if ( empty( $id)) {
$id = JRequest::getString('id');
}
if (!empty( $id)) {
if ( !$template->load($id)) {
$this->setError( JText::_( 'TEMPLATE_NOT_FOUND' ) );
return false;
}
return true;
}
return false;
}


function delete( $id = null)
{
if ( empty( $id)) {
$id = JRequest::getString('id');
}
if ( empty( $id)) {
return false;
}

$templates = array();
$filename = $this->getTemplateFilename();
@include( $filename);

if ( isset( $templates[ $id])) {

unset( $templates[ $id]);
return $this->write( $templates);
}
return true;
}
} 

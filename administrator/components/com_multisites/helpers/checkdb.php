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




class MultisitesHelperCheckDB
{

static function getExtensionTypes( $filter_exttype, $default, $onChange='')
{

if ( version_compare( JVERSION, '1.6') < 0) {
return '';
}
$db = JFactory::getDBO();
$query = $db->getQuery(true);
$query->select('type')->from('#__extensions');
$db->setQuery($query);
$types = array_unique($db->loadColumn());
if ( !empty( $default)) {
$options[] = JHTML::_('select.option', '[unselected]', '- '. $default.' -');
}
foreach($types as $type)
{
$options[] = JHtml::_('select.option', $type, $type);
}
$return = JHtml::_('select.genericlist', $options, 'filter_exttype',
'class="inputbox" size="1" '.$onChange,
'value', 'text', $filter_exttype);
return $return;
}


static function getDownloadedPackages( &$model, $fieldname, $fieldvalue, $default, $onChange='')
{
$rows = $model->getDownloadedPackages();
if ( !empty( $rows)) {
asort( $rows);
}
$opt = array();
if ( !empty( $default)) {
$opt[] = JHTML::_('select.option', '[unselected]', '- '. $default.' -');
}
foreach( $rows as $dbtype) {
$opt[] = JHTML::_('select.option', $dbtype, $dbtype);
}
$list = JHTML::_( 'select.genericlist', $opt, $fieldname,
'class="inputbox" size="1" '.$onChange,
'value', 'text',
$fieldvalue);
return $list;
}


static function getAvailablePackages( &$model, $filter_availablepackages, $default, $onChange='')
{
$rows = $model->getAvailablePackages();
if ( empty( $rows)) {
return '';
}
asort( $rows);
$opt = array();
if ( !empty( $default)) {
$opt[] = JHTML::_('select.option', '[unselected]', '- '. $default.' -');
}
foreach( $rows as $key => $value) {
$keys = explode( '|', $key);
$opt[] = JHTML::_('select.option', $value, $keys[0]);
}
$list = JHTML::_( 'select.genericlist', $opt, 'filter_availablepackages',
'class="inputbox" size="1" '.$onChange,
'value', 'text',
"$filter_availablepackages");
return $list;
}


static function getDBTypeList( &$model, $filter_dbtype, $default, $onChange='')
{
$rows = JDatabase::getConnectors();
if ( empty( $rows)) {
return '';
}
asort( $rows);
$opt = array();
if ( !empty( $default)) {
$opt[] = JHTML::_('select.option', '[unselected]', '- '. $default.' -');
}
foreach( $rows as $dbtype) {
$opt[] = JHTML::_('select.option', $dbtype, $dbtype);
}
$list = JHTML::_( 'select.genericlist', $opt, 'filter_dbtype',
'class="inputbox" size="1" '.$onChange,
'value', 'text',
"$filter_dbtype");
return $list;
}


static function getSchemaList( &$model, $fieldname, $fieldvalue, $default, $onChange='onchange="document.adminForm.submit();"', $filters=array( 'dbtype' => 'mysql'))
{
$rows = $model->getSchemas( $filters);
if ( empty( $rows)) {
return '';
}
ksort( $rows);
$opt = array();
if ( !empty( $default)) {
if ( is_array( $default)) {
foreach( $default as $key => $value) {
$opt[] = JHTML::_('select.option', $key, $value);
}
}
else {
$opt[] = JHTML::_('select.option', '[unselected]', '- '. $default.' -');
}
}
foreach( $rows as $key => $value) {
$opt[] = JHTML::_('select.option', $key, str_replace( '|', ' / ', $key));
}
$list = JHTML::_( 'select.genericlist', $opt, $fieldname,
'class="inputbox" size="1" '.$onChange,
'value', 'text',
$fieldvalue);
return $list;
}


static function getLegacyModeList( $fieldname, $fieldvalue, $onChange='')
{
$rows = array( '[unselected]' => '- '. JText::_( 'Legacy mode') .' -',
'legacy' => JText::_( 'Legacy'),
'legacy_table' => JText::_( 'Legacy table'),
'fix_table' => JText::_( 'Fix table'),
'fix_insert' => JText::_( 'Fix insert'),
'fix_replace' => JText::_( 'Fix replace'),
'ignore_drop_column' => JText::_( 'Ignore DROP COLUMN')
);
$opt = array();
foreach( $rows as $key => $value) {
$opt[] = JHTML::_('select.option', $key, $value);
}
$list = JHTML::_( 'select.genericlist', $opt, $fieldname.'[]',
'class="inputbox" multiple="multiple" size="1"'
.' onfocus="this.setAttribute(\'size\', '.count($opt).');"'
.' onblur="this.setAttribute(\'size\', 1);"'
.$onChange
.' title="'.JText::_( 'Legacy mode means that CREATE TABLE structure is VERIFIED and that DROP COLUMN are IGNORED and only new columns are added or modified with greater size').'"',
'value', 'text',
$fieldvalue);
return $list;
}
} 

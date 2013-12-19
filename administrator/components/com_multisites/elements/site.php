<?php
// file: site.php.
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


defined('JPATH_BASE') or die();
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'models' .DS. 'manage.php');
require_once( dirname( __FILE__) .DS. 'compat16.php');
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'multisites.cfg.php');

class MultisitesElementSite extends MultisitesElement
{

var $_name = 'Site';

function &getLastSiteValue()
{
static $value = '';
return $value;
}

function setLastSiteValue( $newValue)
{
$value =& MultisitesElementSite::getLastSiteValue();

if ( !empty( $newValue) && is_array( $newValue)) {

$newValue = trim( implode(" ", $newValue));
}
$value = $newValue;
}

function fetchElement($name, $value, &$node, $control_name)
{
MultisitesElementSite::setLastSiteValue( $value);
 $class = $this->getAttribute( $node, 'class');
if (!$class) {
$class = "inputbox";
}
 $addScript = $this->getAttribute( $node, 'addscript');
if ( !empty( $addScript)) {
$document = & JFactory::getDocument();
$document->addScript( $addScript);
}
 $onchange = $this->getAttribute( $node, 'onchange');
if ( !empty( $onchange)) {
$onchange = ' onchange="' . $onchange .'"';
}
else {
$onchange = '';
}
 $multiple = $this->getAttribute( $node, 'multiple');
if ( !empty( $multiple)) {
$multiple = ' multiple="' . $multiple .'"';
$control_multiple = '[]';
}
else {
$multiple = '';
$control_multiple = '';
}
 $size = $this->getAttribute( $node, 'size');
if ( !empty( $size)) {
$size = ' size="' . $size .'"';
}
else {
$size = '';
}
 $size_text = $this->getAttribute( $node, 'size_text');
if ( !empty( $size_text)) {
$size_text = ' size="' . $size_text .'"';
}
else {
$size_text = '';
}

$model = new MultisitesModelManage();
$sites = $model->getSites();
 $addMaster = true;
$withSharedUserOnly = $this->getAttribute( $node, 'withSharedUserOnly') == 'true';
if ( $withSharedUserOnly) {
$physical_user_site = $this->getPhysicalUserSite( $model);
 if ( defined( 'MULTISITES_ID') && $physical_user_site == ':master_db:') {}
else {
$addMaster = false;
}
}
$rows = array();
if ( isset( $sites)) {
foreach( $sites as $site) {

if ( isset( $site->db) && isset( $site->dbprefix)
&& !empty( $site->db) && !empty( $site->dbprefix)
)
{
if ( $withSharedUserOnly) {

if ( defined( 'MULTISITES_ID') && $site->id == MULTISITES_ID) {}

else if ( $site->id == $physical_user_site) {
$rows[ strtolower( $site->sitename)] = $site;
}

else {

$slavesite_user_location = $this->getPhysicalUserSite( $model, $site->id);
if ( empty( $slavesite_user_location) || $slavesite_user_location == $physical_user_site) {
$rows[ strtolower( $site->sitename)] = $site;
}
}
}
else {
$rows[ strtolower( $site->sitename)] = $site;
}
}
}
ksort( $rows);
}
$opt = array();
if ( empty( $multiple)) {
$opt[] = JHTML::_('select.option', '0', '- '.JText::_('Select Site').' -');
}
if ( $addMaster) {
$opt[] = JHTML::_('select.option', ':master_db:', '< Master Site >');
}
foreach( $rows as $site) {
$opt[] = JHTML::_('select.option', $site->id, $site->sitename . ' | '. $site->id);
}

if ( version_compare( JVERSION, '1.6') >= 0) {
$select_name = $this->name;
}

else {
$select_name = $control_name.'['.$name.']'.$control_multiple;
}
if ( isset( $GLOBALS['MULTISITES_ELT_SITE'])) {
$site_id = !defined( 'MULTISITES_ID') ? ':master_db:' : MULTISITES_ID;

if ( isset( $GLOBALS['MULTISITES_ELT_SITE']['text'])
&& is_array( $GLOBALS['MULTISITES_ELT_SITE']['text'])
&& in_array( $site_id, $GLOBALS['MULTISITES_ELT_SITE']['text']))
{
$onchange = str_replace( 'options[selectedIndex].value', 'value', $onchange);
return '<input type="text" name="' . $select_name. ' id="' . $control_name . $name . '" value="' . $value . '" ' . $class . $size_text . $onchange . ' />';
}

if ( isset( $GLOBALS['MULTISITES_ELT_SITE']['hidden'])
&& is_array( $GLOBALS['MULTISITES_ELT_SITE']['hidden'])
&& in_array( $site_id, $GLOBALS['MULTISITES_ELT_SITE']['hidden']))
{
return $value.'<input type="hidden" name="' . $select_name. ' id="' . $control_name . $name . '" value="' . $value . '" ' . $class . ' />';
}
}

return JHTML::_( 'select.genericlist', $opt, $select_name,
'class="'.$class.'"' .$multiple .$size . $onchange,
'value', 'text',
$value, $control_name.$name );
}


function getPhysicalUserSite( $modelManage, $site_id = null)
{
$result = null;
$filters = array();
if ( empty( $site_id)) {

$db =& JFactory::getDBO();
$cnf =& JFactory::getConfig();
if ( empty( $db->_dbserver)) { $db->_dbserver = $cnf->getValue('config.host'); }
if ( empty( $db->_dbname)) { $db->_dbname = $cnf->getValue('config.db'); }
}
else {

$db =& Jms2WinFactory::getMultiSitesDBO( $site_id, true);
}
if ( MultisitesDatabase::_isView( $db, '#__users')) {
$from = MultisitesDatabase::getViewFrom( $db, '#__users');
if ( !empty( $from)) {
$filters['host'] = $db->_dbserver;
$parts = explode( '.', $from);
if ( count( $parts) > 1) {
$filters['db'] = trim( $parts[0], '`"\''); 
$filters['dbprefix'] = str_replace( 'users', '', trim( $parts[1], '`"\''));
}

else {
$filters['db'] = $db->_dbname;
$filters['dbprefix'] = str_replace( 'users', '', trim( $parts[0], '`"\''));
}

$modelManage->setFilters( $filters);
$sites =& $modelManage->getSites( true);

if ( empty($sites)) {
 $config =& Jms2WinFactory::getMasterConfig();
if ( $filters['host'] == $config->getValue('config.host')
&& $filters['db'] == $config->getValue('config.db')
&& $filters['dbprefix'] == $config->getValue('config.dbprefix')
)
{
$result = ':master_db:';
}
}

else if ( is_array( $sites) && count( $sites) >= 1) {



foreach( $sites as $site) {
$result = $this->getPhysicalUserSite( $modelManage, $site->id);
break;
}
}
}

else {
$result = null;
}
}

else {

if ( !empty( $site_id)) { $result = $site_id; }
else {

if ( defined( 'MULTISITES_ID')) {
$result = MULTISITES_ID; 
}

else {
$result = ':master_db:';
}
}
}
return $result;
}
} 




class JElementSite extends MultisitesElementSite {}

if ( version_compare( JVERSION, '1.6') >= 0) {
class JFormFieldSite extends JElementSite
{
protected $type = 'Site';
}
}

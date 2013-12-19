<?php
// file: domains.php.
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




class MultisitesElementDomains extends MultisitesElement
{

var $_name = 'Domains';

function &getLastDomainsValue()
{
static $value = '';
return $value;
}

function setLastDomainsValue( $newValue)
{
$value =& MultisitesElementDomains::getLastDomainsValue();

if ( !empty( $newValue) && is_array( $newValue)) {

$newValue = trim( implode(" ", $newValue));
}
$value = $newValue;
}

function fetchElement($name, $value, &$node, $control_name)
{
MultisitesElementDomains::setLastDomainsValue( $value);
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
 $addMaster = $this->getAttribute( $node, 'addMaster');
if ( !empty( $addMaster)) {
$addMaster = true;
}
else {
$addMaster = false;
}
 $size = $this->getAttribute( $node, 'size');
if ( !empty( $size)) {
$size = ' size="' . $size .'"';
}
else {
$size = '';
}

$model = new MultisitesModelManage();
$sites = $model->getSites();
$rows = array();
if ( !empty( $sites)) {
foreach( $sites as $site) {
foreach( $site->indexDomains as $domain) {

$uri = new JURI( $domain);
$host_url = $uri->toString( array('host', 'port', 'path'));
$rows[ $host_url] = $site;
}
}
ksort( $rows);
}
$opt = array();
if ( empty( $multiple)) {
$opt[] = JHTML::_('select.option', '0', '- '.JText::_('Select Domains').' -');
}
if ( !empty( $addMaster)) {
$opt[] = JHTML::_('select.option', ':master_db:', '< Master Site >');
}
foreach( $rows as $domain => $site) {
$opt[] = JHTML::_('select.option', $domain, $domain);
}

if ( substr( $control_name, -2) == '[]') {
$select_name = $control_name;
}

else {
$select_name = $control_name.'['.$name.']'.$control_multiple;
}
return JHTML::_( 'select.genericlist', $opt, $select_name,
'class="'.$class.'"' .$multiple .$size . $onchange,
'value', 'text',
$value, $control_name.$name );
}
} 




if ( version_compare( JVERSION, '1.6') >= 0) {
class JFormFieldDomains extends MultisitesElementDomains
{
protected $type = 'Domains';
}
}

else {
class JElementDomains extends MultisitesElementDomains {}
}
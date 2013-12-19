<?php
// file: component.php.
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


defined('JPATH_BASE') or die;
jimport('joomla.base.adapterinstance');

class JInstallerComponentMultisites extends JAdapterInstance
{
protected $manifest = null;
protected $name = null;
protected $element = null;
protected $oldAdminFiles = null;
protected $oldFiles = null;
protected $manifest_script = null;
protected $install_script = null;
public function setParent( &$parent) { $this->parent =& $parent; }

public function loadLanguage($path=null)
{
$source = $this->parent->getPath('source');
if (!$source) {
$this->parent->setPath('source', ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE).'/components/'.$this->parent->extension->element);
}
$this->manifest = $this->parent->getManifest();
$name = strtolower(JFilterInput::getInstance()->clean((string)$this->manifest->name, 'cmd'));
if (substr($name, 0, 4)=="com_") {
$extension = $name;
}
else {
$extension = "com_$name";
}
$lang = JFactory::getLanguage();
$source = $path ? $path : ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE).'/components/'.$extension;
if ($this->manifest->administration->files) {
$element = $this->manifest->administration->files;
}
else if ($this->manifest->files) {
$element = $this->manifest->files;
}
else {
$element = null;
}
if ($element) {
$folder = (string)$element->attributes()->folder;
if ($folder && file_exists("$path/$folder")) {
$source = "$path/$folder";
}
}
$lang->load($extension.'.sys', $source, null, false, false)
|| $lang->load($extension.'.sys', JPATH_ADMINISTRATOR, null, false, false)
|| $lang->load($extension.'.sys', $source, $lang->getDefault(), false, false)
|| $lang->load($extension.'.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false);
}

public function uninstall($id)
{

$db = $this->parent->getDbo();
$row = null;
$retval = true;


$row = JTable::getInstance('extension');
if (!$row->load((int) $id)) {
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_ERRORUNKOWNEXTENSION'));
return false;
}
 
if ($row->protected) {
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_WARNCORECOMPONENT'));
return false;
}

$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR.DS.'components'.DS.$row->element));
$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE.DS.'components'.DS.$row->element));
$this->parent->setPath('extension_root', $this->parent->getPath('extension_administrator')); 


$this->parent->setPath('source', $this->parent->getPath('extension_administrator'));


$this->parent->findManifest();
$this->manifest = $this->parent->getManifest();
if (!$this->manifest) {

$this->_removeAdminMenus($row);

JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_ERRORREMOVEMANUALLY'));

return false;
}

$name = strtolower(JFilterInput::getInstance()->clean((string)$this->manifest->name, 'cmd'));
if (substr($name, 0, 4)=="com_") {
$element = $name;
}
else {
$element = "com_$name";
}
$this->set('name', $name);
$this->set('element', $element);

$this->loadLanguage(JPATH_ADMINISTRATOR.'/components/'.$element);
if ($msg != '') {
$this->parent->set('extension_message', $msg);
}




if (isset($this->manifest->uninstall->sql)) {
$utfresult = $this->parent->parseSQLFiles($this->manifest->uninstall->sql);
if ($utfresult === false) {

JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_SQL_ERROR', $db->stderr(true)));
$retval = false;
}
}
$this->_removeAdminMenus($row);


$query = $db->getQuery(true);
$query->delete()->from('#__schemas')->where('extension_id = '. $id);
$db->setQuery($query);
$db->query();

$asset = JTable::getInstance('Asset');
if ($asset->loadByName($element)) {
$asset->delete();
}

$update = JTable::getInstance('update');
$uid = $update->find(
array(
'element' => $row->element,
'type' => 'component',
'client_id' => '',
'folder' => ''
)
);
if ($uid) {
$update->delete($uid);
}

if (trim($row->element)) {

$row->delete($row->extension_id);
unset ($row);
return $retval;
}
else {

JError::raiseWarning(100, 'JLIB_INSTALLER_ERROR_COMP_UNINSTALL_NO_OPTION');
return false;
}
}

protected function _removeAdminMenus(&$row)
{

$db = $this->parent->getDbo();
$table = JTable::getInstance('menu');
$id = $row->extension_id;

$query = $db->getQuery(true);
$query->select('id');
$query->from('#__menu');
$query->where('`client_id` = 1');
$query->where('`component_id` = '.(int) $id);
$db->setQuery($query);
$ids = $db->loadResultArray();

if ($error = $db->getErrorMsg() || empty($ids)){
JError::raiseWarning('', JText::_('JLIB_INSTALLER_ERROR_COMP_REMOVING_ADMIN_MENUS_FAILED'));
if ($error && $error != 1) {
JError::raiseWarning(100, $error);
}
return false;
}
else {

foreach($ids as $menuid){
if (!$table->delete((int) $menuid)) {
$this->setError($table->getError());
return false;
}
}

$table->rebuild();
}
return true;
}

protected function _rollback_menu()
{
return true;
}
}

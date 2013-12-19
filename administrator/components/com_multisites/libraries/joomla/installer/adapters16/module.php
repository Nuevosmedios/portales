<?php
// file: module.php.
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

class JInstallerModuleMultisites extends JAdapterInstance
{

protected $route = 'Install';
protected $manifest = null;
protected $manifest_script = null;
protected $name = null;
protected $element = null;
protected $scriptElement = null;
public function setParent( &$parent) { $this->parent =& $parent; }

public function loadLanguage($path=null)
{
$source = $this->parent->getPath('source');
if (!$source) {
$this->parent->setPath('source', ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/'.$this->parent->extension->element);
}
$this->manifest = $this->parent->getManifest();
if ($this->manifest->files) {
$element = $this->manifest->files;
$extension = '';
if (count($element->children())) {
foreach ($element->children() as $file)
{
if ((string)$file->attributes()->module) {
$extension = strtolower((string)$file->attributes()->module);
break;
}
}
}
if ($extension) {
$lang = JFactory::getLanguage();
$source = $path ? $path : ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/'.$extension ;
$folder = (string)$element->attributes()->folder;
if ($folder && file_exists("$path/$folder")) {
$source = "$path/$folder";
}
$client = (string)$this->manifest->attributes()->client;
$lang->load($extension . '.sys', $source, null, false, false)
|| $lang->load($extension . '.sys', constant('JPATH_' . strtoupper($client)), null, false, false)
|| $lang->load($extension . '.sys', $source, $lang->getDefault(), false, false)
|| $lang->load($extension . '.sys', constant('JPATH_' . strtoupper($client)), $lang->getDefault(), false, false);
}
}
}

public function uninstall($id)
{

$row = null;
$retval = true;
$db = $this->parent->getDbo();


$row = JTable::getInstance('extension');
if (!$row->load((int) $id) || !strlen($row->element)) {
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_ERRORUNKOWNEXTENSION'));
return false;
}
 
if ($row->protected) {
JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_WARNCOREMODULE', $row->name));
return false;
}

jimport('joomla.application.helper');
$element = $row->element;
$client = JApplicationHelper::getClientInfo($row->client_id);
if ($client === false) {
$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_UNKNOWN_CLIENT', $row->client_id));
return false;
}
$this->parent->setPath('extension_root', $client->path.DS.'modules'.DS.$element);
$this->parent->setPath('source', $this->parent->getPath('extension_root'));


$this->parent->findManifest();
$this->manifest = $this->parent->getManifest();

$this->loadLanguage(($row->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/'.$element);



$utfresult = $this->parent->parseSQLFiles($this->manifest->uninstall->sql);
if ($utfresult === false) {

JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_SQL_ERROR', $db->stderr(true)));
$retval = false;
}

$query = $db->getQuery(true);
$query->delete()->from('#__schemas')->where('extension_id = '. $row->extension_id);
$db->setQuery($query);
$db->Query();

$query = 'SELECT `id`' .
' FROM `#__modules`' .
' WHERE module = '.$db->Quote($row->element) .
' AND client_id = '.(int)$row->client_id;
$db->setQuery($query);
try
{
$modules = $db->loadResultArray();
}
catch(JException $e)
{
$modules = array();
}
 if (count($modules))
{

JArrayHelper::toInteger($modules);
$modID = implode(',', $modules);

$query = 'DELETE' .
' FROM #__modules_menu' .
' WHERE moduleid IN ('.$modID.')';
$db->setQuery($query);
try
{
$db->query();
}
catch(JException $e)
{
JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_EXCEPTION', $db->stderr(true)));
$retval = false;
}

$query = 'DELETE' .
' FROM #__modules' .
' WHERE id IN ('.$modID.')';
$db->setQuery($query);
try
{
$db->query();
}
catch (JException $e)
{
JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_EXCEPTION', $db->stderr(true)));
$retval = false;
}
}

$row->delete($row->extension_id);
$query = 'DELETE FROM `#__modules` WHERE module = '.$db->Quote($row->element) . ' AND client_id = ' . $row->client_id;
$db->setQuery($query);
try
{
$db->Query(); 
}
catch(JException $e)
{

}
unset ($row);
return $retval;
}
}

<?php
// file: plugin.php.
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

class JInstallerPluginMultisites extends JAdapterInstance
{

var $route = 'install';
protected $manifest = null;
protected $manifest_script = null;
protected $name = null;
protected $scriptElement = null;
protected $oldFiles = null;
public function setParent( &$parent) { $this->parent =& $parent; }

public function loadLanguage($path=null)
{
$source = $this->parent->getPath('source');
if (!$source) {
$this->parent->setPath('source', JPATH_PLUGINS . '/'.$this->parent->extension->folder.'/'.$this->parent->extension->element);
}
$this->manifest = $this->parent->getManifest();
$element = $this->manifest->files;
if ($element)
{
$group = strtolower((string)$this->manifest->attributes()->group);
$name = '';
if (count($element->children()))
{
foreach ($element->children() as $file)
{
if ((string)$file->attributes()->plugin)
{
$name = strtolower((string)$file->attributes()->plugin);
break;
}
}
}
if ($name)
{
$extension = "plg_${group}_${name}";
$lang = JFactory::getLanguage();
$source = $path ? $path : JPATH_PLUGINS . "/$group/$name";
$folder = (string)$element->attributes()->folder;
if ($folder && file_exists("$path/$folder"))
{
$source = "$path/$folder";
}
$lang->load($extension . '.sys', $source, null, false, false)
|| $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, false)
|| $lang->load($extension . '.sys', $source, $lang->getDefault(), false, false)
|| $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false);
}
}
}

public function uninstall($id)
{

$row = null;
$retval = true;
$db = $this->parent->getDbo();


$row = JTable::getInstance('extension');
if (!$row->load((int) $id))
{
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_ERRORUNKOWNEXTENSION'));
return false;
}
 
if ($row->protected)
{
JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_WARNCOREPLUGIN', $row->name));
return false;
}

if (trim($row->folder) == '')
{
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_FOLDER_FIELD_EMPTY'));
return false;
}

if (is_dir(JPATH_PLUGINS.DS.$row->folder.DS.$row->element)) {

$this->parent->setPath('extension_root', JPATH_PLUGINS.DS.$row->folder.DS.$row->element);
}
else {

$this->parent->setPath('extension_root', JPATH_PLUGINS.DS.$row->folder);
}



$manifestFile = $this->parent->getPath('extension_root').DS.$row->element.'.xml';
if ( ! file_exists($manifestFile))
{
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_INVALID_NOTFOUND_MANIFEST'));
return false;
}
$xml = JFactory::getXML($manifestFile);
$this->manifest = $xml;

if (!$xml)
{
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_LOAD_MANIFEST'));
return false;
}

if ($xml->getName() != 'install' && $xml->getName() != 'extension')
{
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_INVALID_MANIFEST'));
return false;
}

$this->parent->setPath('source', JPATH_PLUGINS .'/'.$row->folder.'/'.$row->element);
$this->loadLanguage(JPATH_PLUGINS .'/'.$row->folder.'/'.$row->element);




$utfresult = $this->parent->parseSQLFiles($xml->{strtolower($this->route)}->sql);
if ($utfresult === false)
{

$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PLG_UNINSTALL_SQL_ERROR', $db->stderr(true)));
return false;
}

$query = $db->getQuery(true);
$query->delete()->from('#__schemas')->where('extension_id = '. $row->extension_id);
$db->setQuery($query);
$db->Query();

$row->delete($row->extension_id);
unset ($row);
if ($msg) {
$this->parent->set('extension_message',$msg);
}
return $retval;
}
}

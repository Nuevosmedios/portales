<?php
// file: package.php.
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
jimport('joomla.database.query');
jimport('joomla.installer.packagemanifest');

class JInstallerPackageMultisites extends JAdapterInstance
{

protected $route = 'install';
public function setParent( &$parent) { $this->parent =& $parent; }

function uninstall($id)
{

$row = null;
$retval = true;
$row = JTable::getInstance('extension');
$row->load($id);
$manifestFile = JPATH_MANIFESTS.DS.'packages' . DS . $row->get('element') .'.xml';
$manifest = new JPackageManifest($manifestFile);

$this->parent->setPath('extension_root', JPATH_MANIFESTS.DS.'packages'.DS.$manifest->packagename);

if (!file_exists($manifestFile))
{
 JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MISSINGMANIFEST'));
return false;
}
$xml =JFactory::getXML($manifestFile);

if (!$xml)
{
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_LOAD_MANIFEST'));
return false;
}

if ($xml->getName() != 'install' && $xml->getName() != 'extension')
{
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_INVALID_MANIFEST'));
return false;
}
$error = false;
foreach ($manifest->filelist as $extension)
{
$tmpInstaller = new JInstaller();
$id = $this->_getExtensionID($extension->type, $extension->id, $extension->client, $extension->group);
$client = JApplicationHelper::getClientInfo($extension->client,true);
if ($id)
{
if(!$tmpInstaller->uninstall($extension->type, $id, $client->id)) {
$error = true;
JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_NOT_PROPER', basename($extension->filename)));
}
} else {
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_UNKNOWN_EXTENSION'));
}
}

if (!$error) {
$row->delete();
}
else {
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MANIFEST_NOT_REMOVED'));
}

return $retval;
}
private function _getExtensionID($type, $id, $client, $group)
{
$db = $this->parent->getDbo();
$result = $id;
$query = new JDatabaseQuery();
$query->select('extension_id');
$query->from('#__extensions');
$query->where('type = '. $db->Quote($type));
$query->where('element = '. $db->Quote($id));
switch($type)
{
case 'plugin':

$query->where('folder = '. $db->Quote($group));
break;
case 'library':
case 'package':
case 'component':


break;
case 'language':
case 'module':
case 'template':

$client = JApplicationHelper::getClientInfo($client, true);
$query->where('client_id = '. (int)$client->id);
break;
}
$db->setQuery($query);
$result = $db->loadResult();


return $result;
}
}

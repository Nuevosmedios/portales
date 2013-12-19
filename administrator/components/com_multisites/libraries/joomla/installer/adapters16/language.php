<?php
// file: language.php.
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

class JInstallerLanguageMultisites extends JAdapterInstance
{

protected $_core = false;
public function setParent( &$parent) { $this->parent =& $parent; }

public function uninstall($eid)
{

$extension = JTable::getInstance('extension');
$extension->load($eid);

$client = JApplicationHelper::getClientInfo($extension->get('client_id'));

$element = $extension->get('element');
if (empty($element))
{
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_ELEMENT_EMPTY'));
return false;
}

$params = JComponentHelper::getParams('com_languages');
if ($params->get($client->name)==$element) {
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_DEFAULT'));
return false;
}

$path = $client->path.DS.'language'.DS.$element;

$this->parent->setPath('source', $path);

$extension->delete();

$db = JFactory::getDbo();
$query=$db->getQuery(true);
$query->from('#__users');
$query->select('*');
$db->setQuery($query);
$users = $db->loadObjectList();
if($client->name == 'administrator') {
$param_name = 'admin_language';
} else {
$param_name = 'language';
}
$count = 0;
foreach ($users as $user) {
$registry = new JRegistry;
$registry->loadJSON($user->params);
if ($registry->get($param_name)==$element) {
$registry->set($param_name,'');
$query=$db->getQuery(true);
$query->update('#__users');
$query->set('params='.$db->quote($registry));
$query->where('id='.(int)$user->id);
$db->setQuery($query);
$db->query();
$count = $count + 1;
}
}
if (!empty($count)) {
JError::raiseNotice(500, JText::plural('JLIB_INSTALLER_NOTICE_LANG_RESET_USERS', $count));
}

return true;
}
}

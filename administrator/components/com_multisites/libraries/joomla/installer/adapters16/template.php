<?php
// file: template.php.
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
require_once(JPATH_LIBRARIES
.DS.'joomla'
.DS.'installer'
.DS.'adapters'
.DS.'template.php');

class JInstallerTemplateMultisites extends JInstallerTemplate
{
protected $name = null;
protected $element = null;
protected $route = 'install';
function __construct(&$parent)
{
$db = JFactory::getDBO();
parent::__construct( $parent, $db);
}
public function setParent( &$parent) { $this->parent =& $parent; }

public function uninstall($id)
{

$retval = true;


$row = JTable::getInstance('extension');
if (!$row->load((int) $id) || !strlen($row->element)) {
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_ERRORUNKOWNEXTENSION'));
return false;
}
 
if ($row->protected) {
JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_WARNCORETEMPLATE', $row->name));
return false;
}
$name = $row->element;
$clientId = $row->client_id;

if (!$name) {
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_ID_EMPTY'));
return false;
}

$db = $this->parent->getDbo();
$query = 'SELECT COUNT(*) FROM #__template_styles'.
' WHERE home = 1 AND template = '.$db->Quote($name);
$db->setQuery($query);
if ($db->loadResult() != 0) {
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_DEFAULT'));
return false;
}

$client = JApplicationHelper::getClientInfo($clientId);
if (!$client) {
JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_INVALID_CLIENT'));
return false;
}
$this->parent->setPath('extension_root', $client->path.DS.'templates'.DS.strtolower($name));
$this->parent->setPath('source', $this->parent->getPath('extension_root'));

$this->parent->findManifest();
$manifest = $this->parent->getManifest();
if (!($manifest instanceof JXMLElement)) {

$row->delete($row->extension_id);
unset($row);
JError::raiseWarning(100, JTEXT::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_INVALID_NOTFOUND_MANIFEST'));
return false;
}

$query = 'UPDATE #__menu INNER JOIN #__template_styles'.
' ON #__template_styles.id = #__menu.template_style_id'.
' SET #__menu.template_style_id = 0'.
' WHERE #__template_styles.template = '.$db->Quote(strtolower($name)).
' AND #__template_styles.client_id = '.$db->Quote($clientId);
$db->setQuery($query);
$db->Query();
$query = 'DELETE FROM #__template_styles'.
' WHERE template = '.$db->Quote($name).
' AND client_id = '.$db->Quote($clientId);
$db->setQuery($query);
$db->Query();
$row->delete($row->extension_id);
unset($row);
return $retval;
}
}

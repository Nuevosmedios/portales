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


defined('JPATH_BASE') or die();




class JInstallerModuleMultisites extends JObject
{

function __construct(&$parent)
{
$this->parent =& $parent;
}
public function setParent( &$parent) { $this->parent =& $parent; }

function uninstall( $id, $clientId )
{

$row = null;
$retval = true;
$db =& $this->parent->getDBO();


$row = & JTable::getInstance('module');
if ( !$row->load((int) $id) ) {
JError::raiseWarning(100, JText::_('ERRORUNKOWNEXTENSION'));
return false;
}
 
if ($row->iscore) {
JError::raiseWarning(100, JText::_('Module').' '.JText::_('Uninstall').': '.JText::sprintf('WARNCOREMODULE', $row->name)."<br />".JText::_('WARNCOREMODULE2'));
return false;
}

jimport('joomla.application.helper');
$client =& JApplicationHelper::getClientInfo($row->client_id);
if ($client === false) {
$this->parent->abort(JText::_('Module').' '.JText::_('Uninstall').': '.JText::_('Unknown client type').' ['.$row->client_id.']');
return false;
}
$this->parent->setPath('extension_root', $client->path.DS.'modules'.DS.$row->module);

$this->parent->setPath('source', $this->parent->getPath('extension_root'));
$manifest =& $this->parent->getManifest();
if (!is_a($manifest, 'JSimpleXML')) {

JError::raiseWarning(100, 'Module Uninstall: Package manifest file invalid or not found');
return false;
}

$query = 'SELECT `id`' .
' FROM `#__modules`' .
' WHERE module = '.$db->Quote($row->module) .
' AND client_id = '.(int)$row->client_id;
$db->setQuery($query);
$modules = $db->loadResultArray();
 if (count($modules)) {
JArrayHelper::toInteger($modules);
$modID = implode(',', $modules);
$query = 'DELETE' .
' FROM #__modules_menu' .
' WHERE moduleid IN ('.$modID.')';
$db->setQuery($query);
if (!$db->query()) {
JError::raiseWarning(100, JText::_('Module').' '.JText::_('Uninstall').': '.$db->stderr(true));
$retval = false;
}
}

$row->delete($row->id);
unset ($row);
return $retval;
}
}

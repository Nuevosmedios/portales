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


defined('JPATH_BASE') or die( 'Restricted access' );




class JInstallerComponentMultisites extends JObject
{

function __construct(&$parent)
{
$this->parent =& $parent;
}
public function setParent( &$parent) { $this->parent =& $parent; }

function uninstall($id, $clientId)
{

$db =& $this->parent->getDBO();
$row = null;
$retval = true;


$row = & JTable::getInstance('component');
if ( !$row->load((int) $id) || !trim($row->option) ) {
JError::raiseWarning(100, JText::_('ERRORUNKOWNEXTENSION'));
return false;
}
 
if ($row->iscore) {
JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::sprintf('WARNCORECOMPONENT', $row->name)."<br />".JText::_('WARNCORECOMPONENT2'));
return false;
}

$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR.DS.'components'.DS.$row->option));
$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE.DS.'components'.DS.$row->option));


$this->parent->setPath('source', $this->parent->getPath('extension_administrator'));

$manifest =& $this->parent->getManifest();
if (!is_a($manifest, 'JSimpleXML')) {

$this->_removeAdminMenus($row);

JError::raiseWarning(100, JText::_('ERRORREMOVEMANUALLY'));

return false;
}

$this->manifest =& $manifest->document;


$result = $this->parent->parseQueries($this->manifest->getElementByPath('uninstall/queries'));
if ($result === false) {

JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::_('SQL Error')." ".$db->stderr(true));
$retval = false;
} elseif ($result === 0) {


$utfresult = $this->parent->parseSQLFiles($this->manifest->getElementByPath('uninstall/sql'));
if ($utfresult === false) {

JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::_('SQLERRORORFILE')." ".$db->stderr(true));
$retval = false;
}
}
$this->_removeAdminMenus($row);
return $retval;
}

function _removeAdminMenus(&$row)
{

$db =& $this->parent->getDBO();
$retval = true;

$sql = 'DELETE ' .
' FROM #__components ' .
'WHERE parent = '.(int)$row->id;
$db->setQuery($sql);
if (!$db->query()) {
JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.$db->stderr(true));
$retval = false;
}

if (!$row->delete($row->id)) {
JError::raiseWarning(100, JText::_('Component').' '.JText::_('Uninstall').': '.JText::_('Unable to delete the component from the database'));
$retval = false;
}
return $retval;
}
}
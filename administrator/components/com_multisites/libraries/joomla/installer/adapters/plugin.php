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


defined('JPATH_BASE') or die();




class JInstallerPluginMultisites extends JObject
{

function __construct(&$parent)
{
$this->parent =& $parent;
}
public function setParent( &$parent) { $this->parent =& $parent; }

function uninstall($id, $clientId )
{

$row = null;
$retval = true;
$db =& $this->parent->getDBO();


$row = & JTable::getInstance('plugin');
if ( !$row->load((int) $id) ) {
JError::raiseWarning(100, JText::_('ERRORUNKOWNEXTENSION'));
return false;
}
 
if ($row->iscore) {
JError::raiseWarning(100, JText::_('Plugin').' '.JText::_('Uninstall').': '.JText::sprintf('WARNCOREPLUGIN', $row->name)."<br />".JText::_('WARNCOREPLUGIN2'));
return false;
}

if (trim($row->folder) == '') {
JError::raiseWarning(100, JText::_('Plugin').' '.JText::_('Uninstall').': '.JText::_('Folder field empty, cannot remove files'));
return false;
}

$this->parent->setPath('extension_root', JPATH_ROOT.DS.'plugins'.DS.$row->folder);

$manifestFile = JPATH_ROOT.DS.'plugins'.DS.$row->folder.DS.$row->element.'.xml';
if (file_exists($manifestFile))
{
$xml =& JFactory::getXMLParser('Simple');

if (!$xml->loadFile($manifestFile)) {
JError::raiseWarning(100, JText::_('Plugin').' '.JText::_('Uninstall').': '.JText::_('Could not load manifest file'));
return false;
}

$root =& $xml->document;
if ($root->name() != 'install' && $root->name() != 'mosinstall') {
JError::raiseWarning(100, JText::_('Plugin').' '.JText::_('Uninstall').': '.JText::_('Invalid manifest file'));
return false;
}
} else {
JError::raiseWarning(100, 'Plugin Uninstall: Manifest File invalid or not found');
return false;
}

$row->delete($row->id);
unset ($row);
return $retval;
}
}
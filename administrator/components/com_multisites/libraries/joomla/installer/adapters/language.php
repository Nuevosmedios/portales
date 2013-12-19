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


defined('JPATH_BASE') or die();




class JInstallerLanguageMultisites extends JObject
{

var $_core = false;

function __construct(&$parent)
{
$this->parent =& $parent;
}
public function setParent( &$parent) { $this->parent =& $parent; }

function uninstall($tag, $clientId)
{
$mainframe = &JFactory::getApplication();
$mainframe->enqueueMessage( JText::_( 'MSJINSTALL_LANG_CANNOT_REMOVE') );
return false;
}
}
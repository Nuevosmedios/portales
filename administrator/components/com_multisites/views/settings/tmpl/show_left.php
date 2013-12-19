<?php
// file: show_left.php.
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
?><?php defined('_JEXEC') or die('Restricted access');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'multisitesdb.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
?>
<div class="pane-sliders">
<div class="panel">
<h3 id="settings-info" class="title"><span><?php echo JText::_( 'General information'); ?></span></h3>
<div class="body">
<?php
 
$this->displayFieldForm( $this->row, $this->lists,
array( 'product_id' => array( 'type' => 'label', 'label' => 'Product ID')
)
);

if ( !J2WinUtility::isOSWindows() || MultisitesHelper::isSymbolicLinks()) {
if ( MultisitesHelper::isSymbolicLinks()) { $this->row->symlink = JText::_( 'SETTINGS_SYMLINK_OK'); }
else { $this->row->symlink = JText::_( 'SETTINGS_SYMLINK_NOT_OK'); }
$this->displayFieldForm( $this->row, $this->lists,
array( 'symlink' => array( 'type' => 'label', 'label' => 'SETTINGS_SYMLINK_LBL')
)
);
}

$this->row->dbVersion = Jms2WinFactory::getDBOVersion();
$db =& JFactory::getDBO();
if ( MultisitesDatabase::_isViewSupported($db)) {
$this->row->dbVersion .= ' ' . JText::_( 'SETTINGS_MYSQL_VIEW_SUPPORTED');
}
$this->displayFieldForm( $this->row, $this->lists,
array( 'dbVersion' => array( 'type' => 'label', 'label' => 'SETTINGS_MYSQL_VERS')
)
);
if ( JFile::exists( dirname( __FILE__).'/show_quota.php')) { @include( dirname( __FILE__).'/show_quota.php'); }
?>
</div>
</div>
</div>
<?php
// file: extensions.php.
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
?><?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
if ( file_exists( JPATH_LIBRARIES.'/joomla/html/pane.php')) { jimport('joomla.html.pane'); }
else { require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR.'/libraries/joomla/html/pane.php'); }
$pane =& JPane::getInstance();
echo $pane->startPane( 'pane' );
echo $pane->startPanel( JText::_( 'Detail'), 'paneldetail' );
echo $this->loadTemplate('detail');
echo $pane->endPanel();
echo $pane->startPanel( JText::_( 'Components'), 'panelcomponents' );
echo $this->loadTemplate('components');
echo $pane->endPanel();
echo $pane->startPanel( JText::_( 'Modules'), 'panelmodules' );
echo $this->loadTemplate('modules');
echo $pane->endPanel();
echo $pane->startPanel( JText::_( 'Plugins'), 'panelplugins' );
echo $this->loadTemplate('plugins');
echo $pane->endPanel();
if ( version_compare( JVERSION, '1.6') >= 0) {
echo $pane->startPanel( JText::_( 'Templates'), 'paneltemplates' );
echo $this->loadTemplate('templates');
echo $pane->endPanel();
echo $pane->startPanel( JText::_( 'Languages'), 'panellanguages' );
echo $this->loadTemplate('languages');
echo $pane->endPanel();
}
echo $pane->startPanel( JText::_( 'Tables'), 'paneltables' );
echo $this->loadTemplate('tables');
echo $pane->endPanel();
echo $pane->endPane();
?>

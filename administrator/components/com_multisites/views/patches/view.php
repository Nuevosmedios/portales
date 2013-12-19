<?php
// file: view.php.
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


defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.filesystem.path');
require_once( JPath::clean( JPATH_COMPONENT_ADMINISTRATOR.'/libraries/joomla/application/component/view2win.php'));
@include_once( dirname( __FILE__).'/view_variant.php');
if ( !class_exists( 'MultisitesViewPatchesVariant')) {
class MultisitesViewPatchesVariant extends JView2Win {}
}




class MultisitesViewPatches extends MultisitesViewPatchesVariant
{
var $_formName = 'Patches';
var $_lcFormName = 'patches';


function check($tpl=null)
{
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
$this->setLayout( 'check');

$document = & JFactory::getDocument();
$document->setTitle( JText::_( 'PATCHES_VIEW_DEFAULT_TITLE'));
$document->addStylesheet( str_replace( '/index.php', '', JURI::base( true))."/components/$option/css/patches.css");

$model = &$this->getModel();
$patches_status = &$this->get('PatchesStatus');
$can_install = $model->canInstall();
$isPartialInstall = $model->somePatchesInstalled();
$allPatchesVersion= $model->getAllPatchesVersion();

J2WinToolBarHelper::title( JText::_( 'PATCHES_VIEW_DEFAULT_TITLE'), 'config.png');

if ( defined( 'MULTISITES_ID')) { }
else {
if ( $can_install) {
J2WinToolBarHelper::customX( 'doInstallPatches', 'apply.png', 'apply_f2.png', 'Install', false );

if ( $isPartialInstall) {
J2WinToolBarHelper::customX( 'doUninstallPatches', 'delete.png', 'delete_f2.png', 'Uninstall', false );
}
}
else {
J2WinToolBarHelper::customX( 'doUninstallPatches', 'delete.png', 'delete_f2.png', 'Uninstall', false );
}
}
J2WinToolBarHelper::cancel();
J2WinToolBarHelper::help( 'screen.patches.install', true );

$this->assignAds();
$this->assign('id', '');
$this->assign('can_install', $can_install);
$this->assignRef('patches_status', $patches_status);
$this->assignRef('allPatchesVersion', $allPatchesVersion);
$this->assignRef('option', $option);
JHTML::_('behavior.tooltip');
parent::display($tpl);
}
} 

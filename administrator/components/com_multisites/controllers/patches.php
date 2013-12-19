<?php
// file: patches.php.
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


defined('_JEXEC') or die( 'Restricted access' );
require_once( dirname( dirname( __FILE__)).DS.'controller.php');




class MultisitesControllerPatches extends MultisitesController
{





function checkPatches()
{
$model =& $this->getModel( 'patches' );
$view =& $this->getView( 'patches');
$view->setModel( $model, true );
$jmsVersion = $this->_getVersion();
$jmsVersionNbr = $this->_getVersion_NumberOnly( $jmsVersion);
$view->assign('jmsVersion', $jmsVersion);
$view->assign('jmsVersionNbr', $jmsVersionNbr);
$modelReg =& $this->getModel( 'registration', 'Edwin2WinModel' );
$latestVersion = $modelReg->getLatestVersion();
if ( !empty( $latestVersion['version'])) {
$latestVersion['versionNbr'] = $this->_getVersion_NumberOnly( $latestVersion['version']);
}
$view->assign('latestVersion', $latestVersion);
$view->check();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function doInstallPatches()
{
$option = JRequest::getCmd('option');

JRequest::checkToken() or jexit( 'Invalid Token' );
$model =& $this->getModel( 'patches' );
if (!$model->canInstall()) {
JError::raiseWarning( 500, $model->getError() );
$this->setRedirect( 'index.php?option=' . $option );
return false;
}
$renamed_install_dir = JRequest::getString( 'ren_inst_dir');
$err = null;
if (!$model->install( $renamed_install_dir)) {
$err = $model->getError();
}

$this->setRedirect( 'index.php?option=' . $option . '&task=checkpatches', $err );
}


function doUninstallPatches()
{
$option = JRequest::getCmd('option');

JRequest::checkToken() or jexit( 'Invalid Token' );
$model =& $this->getModel( 'patches' );
$err = null;
if (!$model->uninstall()) {

}

$this->setRedirect( 'index.php?option=' . $option . '&task=checkpatches', $err );
}
} 

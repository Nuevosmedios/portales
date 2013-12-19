<?php
// file: manage.php.
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




class MultisitesControllerManage extends MultisitesController
{








function editSite()
{
$model =& $this->getModel( 'Manage' );
$view =& $this->getView( 'Manage');
$view->setModel( $model, true );

$modelTemplates =& $this->getModel( 'Templates' );
$view->setModel( $modelTemplates);

$country =& $this->getModel( 'Country');
$view->setModel( $country);
$view->editForm('edit',null);
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function addSite()
{
$model =& $this->getModel( 'Manage' );
$view =& $this->getView( 'Manage');
$view->setModel( $model, true );

$modelTemplates =& $this->getModel( 'Templates' );
$view->setModel( $modelTemplates);

$country =& $this->getModel( 'Country');
$view->setModel( $country);
$view->editForm('new',null);
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function addLikeSite()
{
$model =& $this->getModel( 'Manage' );
$view =& $this->getView( 'Manage');
$view->setModel( $model, true );

$modelTemplates =& $this->getModel( 'Templates' );
$view->setModel( $modelTemplates);
$view->editForm('newLike',null);
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function saveSite()
{
$option = JRequest::getCmd('option');

JRequest::checkToken() or jexit( 'Invalid Token' );
$model =& $this->getModel( 'Manage' );
$view =& $this->getView( 'Manage');
$view->setModel( $model, true );
$msg = $view->saveSite();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
$this->setRedirect( 'index.php?option=' . $option, $msg);
}


function deleteSite()
{
$model =& $this->getModel( 'Manage' );
$view =& $this->getView( 'Manage');
$view->setModel( $model, true );
$view->deleteForm();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function doDeleteSite()
{
$option = JRequest::getCmd('option');

JRequest::checkToken() or jexit( 'Invalid Token' );
$id = JRequest::getVar( 'id', false, '', 'string' );
if ($id === false) {
JError::raiseWarning( 500, JText::_( 'Invalid ID provided' ) );
$this->setRedirect( 'index.php?option=' . $option );
return false;
}
$model =& $this->getModel( 'Manage' );
if (!$model->canDelete()) {
JError::raiseWarning( 500, $model->getError() );
$this->setRedirect( 'index.php?option=' . $option );
return false;
}
$err = null;
if (!$model->delete()) {
$err = $model->getError();
}

$model->createMasterIndex();
$this->setRedirect( 'index.php?option=' . $option, $err );
}
} 

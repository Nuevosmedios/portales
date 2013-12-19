<?php
// file: templates.php.
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




class MultisitesControllerTemplates extends J2WinController
{





function templates()
{
$model =& $this->getModel( 'Templates' );
$view =& $this->getView( 'Templates');
$view->setModel( $model, true );

$modelManage =& $this->getModel( 'Manage');
$view->setModel( $modelManage);

$country =& $this->getModel( 'Country');
$view->setModel( $country);
$view->display();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function editTemplate()
{
$model =& $this->getModel( 'Templates' );
$view =& $this->getView( 'Templates');
$view->setModel( $model, true );

$modelManage =& $this->getModel( 'Manage');
$view->setModel( $modelManage);

$modelSharing =& $this->getModel( 'dbsharing');
$view->setModel( $modelSharing);

$country =& $this->getModel( 'Country');
$view->setModel( $country);
$view->editForm('edit',null);
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function addTemplate()
{
$model =& $this->getModel( 'Templates' );
$view =& $this->getView( 'Templates');
$view->setModel( $model, true );

$modelManage =& $this->getModel( 'Manage');
$view->setModel( $modelManage);

$modelSharing =& $this->getModel( 'dbsharing');
$view->setModel( $modelSharing);

$country =& $this->getModel( 'Country');
$view->setModel( $country);
$view->editForm('new',null);
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function addLikeTemplate()
{
$model =& $this->getModel( 'Templates' );
$view =& $this->getView( 'Templates');
$view->setModel( $model, true );

$modelManage =& $this->getModel( 'Manage');
$view->setModel( $modelManage);

$modelSharing =& $this->getModel( 'dbsharing');
$view->setModel( $modelSharing);
$view->editForm('newLike',null);
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function saveTemplate()
{
$option = JRequest::getCmd('option');

JRequest::checkToken() or jexit( 'Invalid Token' );
$model =& $this->getModel( 'Templates' );
$view =& $this->getView( 'Templates');
$view->setModel( $model, true );
$msg = $view->saveTemplate();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
$this->setRedirect( 'index.php?task=templates&option=' . $option, $msg);
}


function deleteTemplate()
{
$errors = array();
$option = JRequest::getCmd('option');

JRequest::checkToken() or jexit( 'Invalid Token' );
$id = JRequest::getVar( 'id', null, '', 'string' );
$model =& $this->getModel( 'Templates' );
if ( empty( $id)) {
$cid = JRequest::getVar('cid', array(), '', 'array');
if ( !empty( $cid)) {
if ( count( $cid) == 1) {

foreach( $cid as $id) {
$view =& $this->getView( 'Templates');
$view->setModel( $model, true );
$view->deleteForm();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
return;
}
}

else {
$msg = '';
foreach( $cid as $id) {
if (!$model->canDelete( $id)) {
$errors[] = $model->getError();
}
else {
if (!$model->delete( $id)) {
$errors[] = $model->getError();
}
}
}
$err = null;
if ( empty( $errors)) {
$err = JText::_( 'Record(s) successfully deleted');
}
else {
$err = implode( '</li><li>', $errors);
}
$this->setRedirect( 'index.php?task=templates&option=' . $option, $err );
}
}

else {
JError::raiseWarning( 500, JText::_( 'Invalid ID provided' ) );
$this->setRedirect( 'index.php?task=templates&option=' . $option );
return false;
}
}

else {
$view =& $this->getView( 'Templates');
$view->setModel( $model, true );
$view->deleteForm();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}
}


function doDeleteTemplate()
{
$option = JRequest::getCmd('option');

JRequest::checkToken() or jexit( 'Invalid Token' );
$id = JRequest::getVar( 'id', false, '', 'string' );
if ($id === false) {
JError::raiseWarning( 500, JText::_( 'Invalid ID provided' ) );
$this->setRedirect( 'index.php?task=templates&option=' . $option );
return false;
}
$model =& $this->getModel( 'Templates' );
if (!$model->canDelete()) {
JError::raiseWarning( 500, $model->getError() );
$this->setRedirect( 'index.php?task=templates&option=' . $option );
return false;
}
$err = null;
if (!$model->delete()) {
$err = $model->getError();
}
$this->setRedirect( 'index.php?task=templates&option=' . $option, $err );
}
} 

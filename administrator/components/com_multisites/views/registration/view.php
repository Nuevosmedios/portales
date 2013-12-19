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
require_once( dirname( dirname( dirname( __FILE__))).'/legacy.php');




class Edwin2WinViewRegistration extends J2WinView
{


function _getComponentName()
{
return $this->get('ExtensionName');
}


function donateButton($redirect_url='', $tpl=null)
{
$this->setLayout( 'donate');
$action = $this->get( 'URL');
$clientInfo = &$this->get('ClientInfo');

if ( empty($redirect_url)) {

$redirect_url = JURI::base()."index.php?option=$option&task=donate";
}

$this->assignRef('action', $action);
$this->assign('message', JText::_('EDWIN2WIN_DONATION'));
$this->assign('option', $this->_getComponentName());
$this->assignRef('clientInfo', $clientInfo);
$this->assign('btnToolTipMsg', JText::_('EDWIN2WIN_DONATION_BTN_TTMSG'));
$this->assign('btnAltMsg', JText::_('EDWIN2WIN_DONATION_BTN_ALTMSG'));
JHTML::_('behavior.tooltip');

parent::display($tpl);
}

function donate($tpl=null)
{
}


function registrationButton($redirect_url='', $tpl=null)
{
$option = JRequest::getCmd('option');
$this->setLayout( 'registration');
$action = $this->get( 'URL');
$clientinfo = &$this->get('ClientInfo');
$productname = $this->get('ExtensionName');
$productversion = $this->get('ExtensionVersion');
$joomlaversion = $this->get('JoomlaVersion');
$regInfo = &$this->get('RegistrationInfo');
if ( Edwin2WinModelRegistration::getForceRegistration()) {
$product_id = '';
}
else if ( isset($regInfo['product_id'])) {

if ( substr( $regInfo['product_id'], 0, 4) == 'HTTP'
|| strpos( $regInfo['product_id'], 'Set-Cookie') !== false
) {
$product_id = '';
}
else {
$product_id = trim( $regInfo['product_id']);
}
}
else {
$product_id = '';
}

if ( empty($redirect_url)) {

$redirect_url = JURI::base()."index.php?option=$option&task=registered";
}

$this->assignRef('action', $action);
$this->assign('message', JText::_('EDWIN2WIN_REGISTRATION'));
$this->assign('option', $this->_getComponentName());
$this->assignRef('clientinfo', $clientinfo);
$this->assignRef('productname', $productname);
$this->assignRef('productversion', $productversion);
$this->assignRef('joomlaversion', $joomlaversion);
$this->assignRef('regInfo', $regInfo);
$this->assignRef('product_id', $product_id);
$this->assignRef('redirect_url', $redirect_url);
$this->assign('btnToolTipMsg', JText::_('EDWIN2WIN_REGISTRATION_BTN_TTMSG'));
$this->assign('btnAltMsg', JText::_('EDWIN2WIN_REGISTRATION_BTN_ALTMSG'));
JHTML::_('behavior.tooltip');

parent::display($tpl);
}

function registered($displayForm=true,$tpl=null)
{

$inputValues['status'] = JRequest::getString( 'status');
$inputValues['product_key'] = JRequest::getString( 'product_key');
if ( isset( $_REQUEST['product_id'])) {
$inputValues['product_id'] = JRequest::getString( 'product_id');
}

$model = $this->getModel();
$isOK = $model->registerInfo( $inputValues);
$error = $model->getError();
if ( $isOK) {
$this->setLayout( 'registered_ok');
$msg = JText::_('EDWIN2WIN_REGISTERED_OK');
}
else {
$this->setLayout( 'registered_err');
$msg = JText::_('EDWIN2WIN_REGISTERED_ERR')
. $error;
}
if ( $displayForm) {

$this->assignRef('inputValues', $inputValues);
$this->assign('isOK', $isOK);
$this->assignRef('msg', $msg);
$this->assignRef('error', $error);
JHTML::_('behavior.tooltip');

parent::display($tpl);
return null;
}
else {
return $msg;
}
}
} 

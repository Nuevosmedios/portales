<?php
// file: checkdb.php.
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




class MultisitesControllerCheckDB extends J2WinController
{





function checkDB()
{
$model =& $this->getModel( 'CheckDB' );
$view =& $this->getView( 'CheckDB');
$view->setModel( $model, true );
$view->display();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));

}


function fixDB()
{
if ( version_compare( JVERSION, '2.5') >= 0) {
$model =& $this->getModel( 'CheckDB' );
$view =& $this->getView( 'CheckDB');
$view->setModel( $model, true );
$view->fixDB();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}
else {
echo JText::_( 'Functionality is only available under Joomla 2.5');
}
}


function fixUncheckedDB()
{
if ( version_compare( JVERSION, '2.5') >= 0) {
$model =& $this->getModel( 'CheckDB' );
$view =& $this->getView( 'CheckDB');
$view->setModel( $model, true );
$view->fixUncheckedDB();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}
else {
echo JText::_( 'Functionality is only available under Joomla 2.5');
}
}


function deleteJoomlaFiles()
{
$model =& $this->getModel( 'CheckDB' );
$view =& $this->getView( 'CheckDB');
$view->setModel( $model, true );
$view->deleteJoomlaFiles();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function downloadLatestJoomla()
{
$option = JRequest::getCmd('option');
$model =& $this->getModel( 'CheckDB' );
$errors = $model->downloadLatestJoomla();
$err = null;
if ( !empty( $errors)) {
$err = implode( '</li><li>', $errors);
}
else {
$err = JText::_( 'Joomla package is sucessfully downloaded');
}
$this->setRedirect( 'index.php?option=' . $option . '&task=checkDB', $err);
}


function installLatestJoomla()
{
$model =& $this->getModel( 'CheckDB' );
$view =& $this->getView( 'CheckDB');
$view->setModel( $model, true );
$view->installLatestJoomla();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function deleteSymLinks()
{
$model =& $this->getModel( 'CheckDB' );
$view =& $this->getView( 'CheckDB');
$view->setModel( $model, true );
$view->deleteSymLinks();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function refreshSymLinks()
{
$model =& $this->getModel( 'CheckDB' );
$view =& $this->getView( 'CheckDB');
$view->setModel( $model, true );
$view->refreshSymLinks();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}





function ajaxDownloadPackage()
{
require_once( JPATH_COMPONENT .DS. 'helpers' .DS. 'checkdb.php');


$url = JRequest::getString( 'url');
$selected_value = JRequest::getString( 'selected_value');
if ( empty( $url)) {
$lists = array( 'type' => 'downloadpackage',
'errors' => array( JText::_( 'URL required')));
}
else {

$model =& $this->getModel( 'CheckDB' );
$lists = array( 'type' => 'downloadpackage');
$lists['errors'] = $model->downloadPackage( $url);
if ( empty( $lists['errors'])) {
$lists['downloadedpackages'] = MultisitesHelperCheckDB::getDownloadedPackages( $model, 'filter_downloadedpackages', $selected_value, JText::_( 'Downloaded packages'), '');
}
}
$result = json_encode ( $lists);
jexit( $result);
}


function ajaxCheckDB()
{



$model =& $this->getModel( 'CheckDB' );
$view =& $this->getView( 'CheckDB');
$view->setModel( $model, true );
$lists = $view->ajaxCheckDB();
if ( empty( $lists)) {
$lists = array( 'type' => 'checkdb',
'errors' => array( JText::_( 'Result is empty')));
}


$ev_list = '$list_stringOnly = '
. preg_replace( '#([A-Za-z0-9\_]+::__set_state)\(array\(#',
'(array(\'__classname\' => \'$1\',',
var_export( $lists, true)
)
. ';'
;
eval( $ev_list);
$result = json_encode ( $list_stringOnly);
jexit( $result);
}
} 

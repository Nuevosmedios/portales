<?php
// file: tools.php.
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




class MultisitesControllerTools extends J2WinController
{





function tools()
{
$model =& $this->getModel( 'Tools' );
$view =& $this->getView( 'Tools');
$view->setModel( $model, true );
$view->display();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}


function applyTools()
{
$model =& $this->getModel( 'Tools' );
$view =& $this->getView( 'Tools');
$view->setModel( $model, true );
$view->applyTools();
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
}





function ajaxToolsGetSite()
{



$model =& $this->getModel( 'Tools' );
$view =& $this->getView( 'Tools');
$view->setModel( $model, true );
$result = $view->getSiteExtensions();
jexit( $result);
}


function ajaxToolsApply()
{


$enteredvalues = array();
$enteredvalues['site_id'] = JRequest::getString('site_id', null);;
$enteredvalues['nbActions'] = JRequest::getInt('nbActions', 0);;
$enteredvalues['actions'] = JRequest::getVar( 'action', array(), 'request', 'array' );
$enteredvalues['fromSiteIDs'] = JRequest::getVar( 'fromSiteID', array(), 'request', 'array' );
$enteredvalues['options'] = JRequest::getVar( 'opt', array(), 'request', 'array' );
$enteredvalues['types'] = JRequest::getVar( 'type', array(), 'request', 'array' );
$enteredvalues['overwrites'] = JRequest::getVar( 'overwrite', array(), 'request', 'array' );

$model =& $this->getModel( 'Tools' );
$errors = $model->doActions( $enteredvalues);
if ( empty($errors)) {
jexit( '[OK]');
}
jexit( '[NOK]|' . implode( '|', $errors));
}
} 

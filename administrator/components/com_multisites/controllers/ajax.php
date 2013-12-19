<?php
// file: ajax.php.
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




class MultisitesControllerAjax extends J2WinController
{





function ajaxGetUsersList()
{

JRequest::checkToken( 'get') or jexit( 'Invalid Token' );
$site_id = JRequest::getString( 'site_id', null);
if ( $site_id == '[unselected]') {
$site_id = null;
JRequest::setVar( 'site_id', $site_id);
}
$fromSite = null;
if ( !empty( $site_id)) {
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS. 'classes' .DS. 'site.php');
$fromSite = & Site::getInstance( $site_id);
}
$lists['userslist'] = MultisitesHelper::getUsersList( $site_id);
$lists['setDefaultTemplate'] = MultisitesHelper::getJoomlaTemplateList( $fromSite);
$lists['setDefaultMenu'] = MultisitesHelper::getMenuItemsList( $site_id);
$lists['setDefaultJLang'] = MultisitesHelper::getJLanguageList( $fromSite);
$replyStr = json_encode ( $lists);
jexit( $replyStr);
}


function ajaxGetTemplate()
{



$model =& $this->getModel( 'Templates' );
$template = $model->getCurrentRecord();
if (!$template) {
jexit( '<error>' . JText::_( 'TEMPLATE_NOT_FOUND') . '</error>');
}
$lists['type'] = 'template';
foreach( array('id',

'contients_ids',
'countries_ids',
'regions',
'states',
'cities',
'zipcodes',
'fromLongitude',
'toLongitude',
'fromLatitude',
'toLatitude',
'metro',
'area',
'geoip_ignoreundefined',
'geoip_ignorepattern',
'geoip_ignoretimeout',

'browser_types',
'browser_langs',
'browser_ignorepattern',
'browser_ignoretimeout',

'toDBHost',
'toDBName',
'toDBUser',
'toDBPsw',
'toPrefix',
'toSiteName',
'newAdminEmail',
'newAdminPsw',
'setDefaultJLang',
'setDefaultTemplate',
'setDefaultMenu',

'deploy_dir',
'deploy_create',
'alias_link',
'media_dir',
'images_dir',
'templates_dir',


'toFTP_host',
'toFTP_port',
'toFTP_user',
'toFTP_psw',
'toFTP_rootpath') as $field)
{
$lists[$field] = '';
if ( !empty($template->$field)) {

if ( is_array( $template->$field)) {
$lists[$field] = '<ul class="hasTip" title="'.JText::_('Default value').'">';
foreach( $template->$field as $key => $value) {
$lists[$field] = '<li>'.$value. '</li>';
}
$lists[$field] .= '</ul>';
}

else {
$lists[$field] = $template->$field;
}
}
}

if ( isset($template->redirect1st)) {
if ( !empty( $template->redirect1st)) { $lists['redirect1st'] = JText::_( 'Yes'); }
else { $lists['redirect1st'] = JText::_( 'No'); }
}
else { $lists['redirect1st'] = JText::_( 'Default'); }

if ( isset($template->geoip_ignoreundefined)) {
if ( !empty( $template->geoip_ignoreundefined)) { $lists['geoip_ignoreundefined'] = JText::_( 'Yes'); }
else { $lists['geoip_ignoreundefined'] = JText::_( 'No'); }
}
else { $lists['geoip_ignoreundefined'] = JText::_( 'Default'); }

if ( isset($template->toFTP_enable)) {
if ( !empty( $template->toFTP_enable)) { $lists['toFTP_enable'] = JText::_( 'Yes'); }
else { $lists['toFTP_enable'] = JText::_( 'No'); }
}
else { $lists['toFTP_enable'] = JText::_( 'Default'); }

if ( !empty( $template->setDefaultMenu) && !empty( $template->fromSiteID)) {
$fromSiteID = $template->fromSiteID;
if ( $fromSiteID == '[unselected]') {
$fromSiteID = '';
}
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'
.DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
$db =& Jms2WinFactory::getMultisitesDBO( $fromSiteID);

if ( version_compare( JVERSION, '1.6') >= 0) {
$query = 'SELECT title from #__menu'
. ' WHERE id = '. $template->setDefaultMenu
;
}

else {
$query = 'SELECT name from #__menu'
. ' WHERE id = '. $template->setDefaultMenu
;
}
$db->setQuery($query);
$lists['setDefaultMenu'] = $db->loadResult() . ' ('.$template->setDefaultMenu.')';
}
$result = json_encode ( $lists);
jexit( $result);
}
} 

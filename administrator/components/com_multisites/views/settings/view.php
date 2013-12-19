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
jimport('joomla.application.component.view');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'application' .DS. 'component' .DS. 'view2win.php');




class MultisitesViewSettings extends JView2Win
{

var $_formName = 'Settings';
var $_lcFormName = 'settings';


function showSettings($tpl=null)
{

$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
$this->_layout = 'show';
$table = &$this->get('Settings');
$this->assignRef('row', $table);
$msg = &$this->get( 'Error');
if ( !empty( $msg)) {
$mainframe->enqueueMessage( $msg);
}

$formName = $this->_formName;
$lcFormName = $this->_lcFormName;
J2WinToolBarHelper::title( JText::_( "Settings" ), 'config.png' );
J2WinToolBarHelper::custom( "saveSettings", 'save.png', 'save_f2.png', 'Save', false );
J2WinToolBarHelper::cancel( 'manage');
J2WinToolBarHelper::help( 'screen.settings.show', true );
$this->assignAds();
$this->assignRef('option', $option);
JHTML::_('behavior.tooltip');
parent::display($tpl);
}

function assignAds()
{
if ( !defined('_EDWIN2WIN_')) { define('_EDWIN2WIN_', true); }
require_once( JPATH_COMPONENT.DS.'classes'.DS.'http.php' );
require_once( JPATH_COMPONENT.DS.'models'.DS.'registration.php' );

$isRegistered =& Edwin2WinModelRegistration::isRegistered();
if ( !$isRegistered) { $ads =& Edwin2WinModelRegistration::getAds(); }
else { $ads = ''; }
$this->assignRef('ads', $ads);
}


function saveSettings($tpl=null)
{
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');

$enteredvalues = array();

$enteredvalues['jpath_multisites'] = JRequest::getString('jpath_multisites', null);

$dir_rights_owner = JRequest::getInt('dir_rights_owner', null);
$dir_rights_group = JRequest::getInt('dir_rights_group', null);
$dir_rights_other = JRequest::getInt('dir_rights_other', null);
$enteredvalues['dir_rights'] = ((intval( $dir_rights_owner) & 7) << 6)
| ((intval( $dir_rights_group) & 7) << 3)
| ( intval( $dir_rights_other) & 7)
;
$enteredvalues['tld_parsing'] = JRequest::getBool('tld_parsing', false);
$enteredvalues['letter_tree'] = JRequest::getBool('letter_tree', false);
$enteredvalues['refresh_disabled'] = JRequest::getBool('refresh_disabled', false);
$enteredvalues['cookie_domain'] = JRequest::getBool('cookie_domain', false);
$enteredvalues['ignore_ext_version']= JRequest::getBool('ignore_ext_version', false);
$enteredvalues['db_grant_host'] = JRequest::getString('db_grant_host', null);
$enteredvalues['db_root_user'] = JRequest::getString('db_grant_host', null);
$enteredvalues['db_root_psw'] = JRequest::getString('db_root_psw', null);
$enteredvalues['joomla_download_url'] = JRequest::getString('joomla_download_url', null);
$enteredvalues['home_dir'] = JRequest::getString('home_dir', null);
$enteredvalues['public_dir'] = JRequest::getString('public_dir', null);
$enteredvalues['config_prefix_dir'] = JRequest::getString('config_prefix_dir', null);
$enteredvalues['autoinc_dir'] = JRequest::getString('autoinc_dir', null);
$enteredvalues['elt_site'] = array();

$elt_site_text = JRequest::getString('elt_site_text', null);
if ( !empty( $elt_site_text)) {
$elt_site_text_array = preg_split( "#[ ,\n]#", $elt_site_text);
for( $i=0; $i<count( $elt_site_text_array); $i++) {
$elt_site_text_array[$i] = trim( $elt_site_text_array[$i]);
if ( empty( $elt_site_text_array[$i])) {
unset( $elt_site_text_array[$i]);
}
}
if ( !empty( $elt_site_text_array)) {
$enteredvalues['elt_site']['text'] = $elt_site_text_array;
}
}

$elt_site_hidden = JRequest::getString('elt_site_hidden', null);
if ( !empty( $elt_site_hidden)) {
$elt_site_hidden_array = preg_split( "#[ ,\n]#", $elt_site_hidden);

for( $i=0; $i<count( $elt_site_hidden_array); $i++) {
$elt_site_hidden_array[$i] = trim( $elt_site_hidden_array[$i]);
if ( empty( $elt_site_hidden_array[$i])) {
unset( $elt_site_hidden_array[$i]);
}
}
if ( !empty( $elt_site_hidden_array)) {
$enteredvalues['elt_site']['hidden'] = $elt_site_hidden_array;
}
}

$enteredvalues['geoip_logfile'] = JRequest::getString('geoip_logfile', null);
$enteredvalues['maxmind_key_country'] = JRequest::getString('maxmind_key_country', null);
$enteredvalues['maxmind_key_city'] = JRequest::getString('maxmind_key_city', null);
$enteredvalues['maxmind_icc_enabled'] = JRequest::getBool('maxmind_icc_enabled', false);
$enteredvalues['maxmind_city_enabled'] = JRequest::getBool('maxmind_city_enabled', false);
$enteredvalues['quova_apikey'] = JRequest::getString('quova_apikey', null);
$enteredvalues['quova_secret'] = JRequest::getString('quova_secret', null);

$enteredvalues['browser_logfile'] = JRequest::getString('browser_logfile', null);

$model = $this->getModel();
if ( !$model->save( $enteredvalues, true)) {
$msg = $model->getError();
JError::raiseWarning( 500, $msg);
return $msg;
}
$cache = JFactory::getCache();
$cache->clean();
$msg = JText::_( 'SETTINGS_SAVED');
return $msg;
}
} 

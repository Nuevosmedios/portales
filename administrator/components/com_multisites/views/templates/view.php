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
require_once( JPATH_COMPONENT .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
require_once( JPATH_COMPONENT .DS. 'libraries' .DS. 'joomla' .DS. 'application' .DS. 'component' .DS. 'view2win.php');




class MultisitesViewTemplates extends JView2Win
{

var $_formName = 'Template';
var $_lcFormName = 'template';


function display($tpl=null)
{
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
$this->setLayout( 'default');

$formName = $this->_formName;
$lcFormName = $this->_lcFormName;
J2WinToolBarHelper::title( JText::_( 'TEMPLATE_LIST_TITLE' ), 'config.png' );
J2WinToolBarHelper::addNewX( "add$formName" );
J2WinToolBarHelper::customX( "addLike$formName", 'copy.png', 'copy.png', 'New Like', true );
J2WinToolBarHelper::editListX( "edit$formName");
J2WinToolBarHelper::customX( "delete$formName", 'delete.png', 'delete_f2.png', 'Delete', true );
J2WinToolBarHelper::help( 'screen.' .$lcFormName. 'manager', true);
$document = & JFactory::getDocument();
$document->setTitle(JText::_('TEMPLATE_LIST_TITLE'));
if ( version_compare( JVERSION, '3.0') >= 0) {
$document->addStyleSheet('components/com_multisites/css/list.css');
$document->addScript("components/com_multisites/assets/j30/ischecked.js");
$document->addScript("components/com_multisites/assets/j30/checkall.js");
}

$filters = &$this->_getFilters();

$model = &$this->getModel();
$model->setFilters( $filters);
$templates = &$this->get('Templates');
$this->assignRef('templates', $templates);
if ( !empty( $templates->countries_ids)) {
$filters['countries_ids'] = $templates->countries_ids;
}
else {
$filters['countries_ids'] = array();
}
if ( !empty( $templates->continents_ids)) {
$filters['continents_ids'] = $templates->continents_ids;
}
else {
$filters['continents_ids'] = array();
}
$lists = &$this->_getViewLists( $filters);
$pagination = &$this->_getPagination( $filters, $this->get('CountAll'));

$this->assignAds();
$this->assignRef('pagination', $pagination);
$this->assignRef('lists', $lists);
$this->assignRef('limitstart', $limitstart);
$this->assignRef('option', $option);
JHTML::_('behavior.tooltip');

parent::display($tpl);
}

function getTemplateToolTips( $id, $template)
{
$groupName = (!empty( $template['groupName']))
? '<tr><td nowrap=\'nowrap\'>Group name:</td<td>'. htmlspecialchars( $template['groupName']). '</td</tr>'
: '';
$title = (!empty( $template['title']))
? '<tr><td>Title:</td<td>'.htmlspecialchars( $template['title']). '</td</tr>'
: '';
$description = (!empty( $template['description']))
? '<tr valign=\'top\'><td>Description:</td><td>'. htmlspecialchars($template['description']). '</td</tr>'
: '';
$deploy_dir = '';
if ( $this->canShowDeployDir()) {
$deploy_dir = '<li>Deploy dir: '.$template['deploy_dir']. '</li>';
}
$media_dir = (!empty( $template['media_dir']))
? '<li>Media dir: '.$template['media_dir']. '</li>'
: '';
$images_dir = (!empty( $template['images_dir']))
? '<li>Images dir: '.$template['images_dir']. '</li>'
: '';
$templates_dir = (!empty( $template['templates_dir']))
? '<li>Templates dir: '.$template['templates_dir']. '</li>'
: '';
$tmp_dir = (!empty( $template['tmp_dir']))
? '<li>Temporary dir: '.$template['tmp_dir']. '</li>'
: '';
$result = JText::_( 'Edit the template' )
. '::'
. $id
. '<table border=\'0\'>'
. $groupName
. $title
. $description
. '</table>'
. '<ul>'
. $deploy_dir
. $media_dir
. $images_dir
. $templates_dir
. $tmp_dir
. '</ul>'
;
return $result;
}


function &_getFilters()
{
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
$filters = array();

$client = JRequest::getWord( 'filter_client', 'template' );

$search = $mainframe->getUserStateFromRequest( "$option.$client.search", 'search', '', 'string' );
$filters['search'] = JString::strtolower( $search );

$filters['sitename'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_sitename", 'filter_sitename', '[unselected]', 'string' );
$filters['host'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_host", 'filter_host', '[unselected]', 'string' );
$filters['db'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_db", 'filter_db', '[unselected]', 'string' );
$filters['site_ids'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_site_ids", 'filter_site_ids', '[unselected]', 'string' );
$filters['users'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_users", 'filter_users', '[unselected]', 'string' );

$filters['order'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_order", 'filter_order', '', 'cmd' );
$filters['order_Dir'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_order_Dir", 'filter_order_Dir', '', 'word' );

$filters['limit'] = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
$filters['limitstart'] = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
return $filters;
}

function &_getViewLists( &$filters, $row = null)
{
$fromSite = null;
$fromSiteID = '';
$model =& $this->getModel( 'Manage');
if ( is_object( $model)) {
$sites = $model->getSites();

$lists['sitename'] = MultisitesHelper::getSiteNameList( $sites, $filters['sitename']);
$lists['dbserver'] = MultisitesHelper::getDBServerList( $sites, $filters['host']);
$lists['dbname'] = MultisitesHelper::getDBNameList( $sites, $filters['db']);
$lists['site_ids'] = MultisitesHelper::getSiteIdsList( $sites, $filters['site_ids']);

}
if ( !empty( $row) && !empty( $row->fromSiteID)) {
$fromSiteID = $row->fromSiteID;
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS. 'classes' .DS. 'site.php');
$fromSite = & Site::getInstance( $fromSiteID);
}
else {
$fromSiteID = '';
$fromSite = null;
}
$continents_ids =
$countries_ids =
$browser_types =
$setDefaultTemplate =
$setDefaultMenu =
$setDefaultJLang = '[unselected]';
if ( !empty( $row)) {
if ( !empty( $row->continents)) { $continents_ids = $row->continents; }
if ( !empty( $row->countries)) { $countries_ids = $row->countries; }
if ( !empty( $row->browser_types)) { $browser_types = $row->browser_types; }
if ( !empty( $row->setDefaultTemplate)) { $setDefaultTemplate= $row->setDefaultTemplate; }
if ( !empty( $row->setDefaultMenu)) { $setDefaultMenu = $row->setDefaultMenu; }
if ( !empty( $row->setDefaultJLang)) { $setDefaultJLang = $row->setDefaultJLang; }
}
$lists['continents'] = MultisitesHelper::getContinentsIdsList( $continents_ids);
$country =& $this->getModel( 'Country');
if ( is_object( $country)) {
$countries = $country->getCountries();
$lists['countries'] = MultisitesHelper::getCountriesIdsList( $countries, $countries_ids);
}
$lists['browser_types'] = MultisitesHelper::getBrowserTypesList( $browser_types);
$lists['setDefaultTemplate'] = MultisitesHelper::getJoomlaTemplateList( $fromSite, $setDefaultTemplate);
$lists['setDefaultMenu'] = MultisitesHelper::getMenuItemsList( $fromSiteID, $setDefaultMenu);
$lists['setDefaultJLang'] = MultisitesHelper::getJLanguageList( $fromSite, $setDefaultJLang);

$lists['order_Dir'] = $filters['order_Dir'];
$lists['order'] = $filters['order'];

$lists['search'] = $filters['search'];
return $lists;
}

function &_getPagination( &$filters, $total=0)
{
jimport('joomla.html.pagination');
$pagination = new JPagination( $total, $filters['limitstart'], $filters['limit'] );
return $pagination;
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


function canShowDeployDir()
{
return MultisitesHelper::isSymbolicLinks();
}


function deleteForm($tpl=null)
{
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
$this->setLayout( 'delete');
JRequest::setVar( 'hidemainmenu', 1 );

J2WinToolBarHelper::title( JText::_( 'Template confirm' ) . ': <small><small>[ '. JText::_( 'Delete' ) .' ]</small></small>', 'config.png' );
J2WinToolBarHelper::custom( 'doDeleteTemplate', 'delete.png', 'delete_f2.png', 'Delete', false );
J2WinToolBarHelper::cancel( 'templates');
J2WinToolBarHelper::help( 'screen.templatemanager.delete', true );

$template = &$this->get('CurrentRecord');
$document = & JFactory::getDocument();
$document->setTitle('Confirm Delete template: ' . $template->id);

$this->assignAds();
$this->assignRef('template', $template);
$this->assignRef('option', $option);
parent::display($tpl);
}


function editForm($edit,$tpl=null)
{
JRequest::setVar( 'hidemainmenu', 1 );
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
$this->setLayout( 'edit');
if($edit == 'edit' || $edit == 'newLike')
$table = &$this->get('CurrentRecord');
else
$table = &$this->get('NewRecord');
$this->assignRef('row', $table);

$formName = $this->_formName;
$lcFormName = $this->_lcFormName;
$isNew = (($table->id == '') || ($edit == 'newLike'));
if ( $isNew) {
$text = JText::_('New');
}
else {
$text = JText::_('Edit');
}

J2WinToolBarHelper::title( JText::_( 'TEMPLATE_VIEW_EDT_TITLE' ).': <small><small>[ '. $text.' ]</small></small>', 'config.png' );
J2WinToolBarHelper::custom( "save$formName", 'save.png', 'save_f2.png', 'Save', false );
J2WinToolBarHelper::cancel( 'templates');
J2WinToolBarHelper::help( 'screen.' .$lcFormName. 'manager.new', true );
$document = & JFactory::getDocument();
$document->setTitle(JText::_('TEMPLATE_VIEW_EDT_TITLE'));
if ( version_compare( JVERSION, '1.6') >= 0) { JHTML::_('behavior.framework'); }
else { JHTML::_('behavior.mootools'); }
$document->addScript('components/com_multisites/assets/dbsharing.js');
$document->addStyleSheet('components/com_multisites/assets/dbsharing.css');
if ( version_compare( JVERSION, '3.0') >= 0) {
$document->addStyleSheet('components/com_multisites/css/tabs.css');
}
$document->addScript('components/com_multisites/assets/inputtree.js');
JHTML::stylesheet('mootree.css');

$filters = &$this->_getFilters();
$filters['site_ids'] = $table->fromSiteID;
if ( !empty( $table->fromSiteID) && $table->fromSiteID != '[unselected]') {
if ( !empty( $table->shareDB) && $table->shareDB) {
$style_showDBFields = 'style="display:none;"';
}
else {
$style_showDBFields = '';
}
}
else {
$style_showDBFields = 'style="display:none;"';
}
$this->assignRef('style_showDBFields', $style_showDBFields);
if ( isset( $table->toFTP_enable) && ($table->toFTP_enable == '0' || $table->toFTP_enable == '1')) {
$style_showFTPFields = '';
}

else {
$style_showFTPFields = 'style="display:none;"';
}
$this->assignRef('style_showFTPFields', $style_showFTPFields);
$model = &$this->getModel();
$templates = &$this->get('Templates');
$this->assignRef('templates', $templates);
$lists = &$this->_getViewLists( $filters, $table);
$symbolicLinks = $this->_computeSymbolicLinks( $table);
$this->assignRef('symbolicLinks', $symbolicLinks);
$this->assignAds();
$this->assignRef('lists', $lists);
$this->assign('isnew', $isNew);
$this->assign('isCreateView', Jms2WinFactory::isCreateView( $table->fromSiteID));
$modelSharing = &$this->getModel( 'dbsharing');
$xml = $modelSharing->getDBSharing();
$treeparams =& $xml->getElementByPath('params');
$this->assignRef('treeparams' , $treeparams);
$this->assign('ignoreUL', false);
$this->assign('tree_id', ' id="dbsharing-tree"');
$this->assign('node_id' , 0);
$this->assignRef('option', $option);
JHTML::_('behavior.tooltip');
parent::display($tpl);
}


function _computeSymbolicLinks( $template)
{
$modelManage = &$this->getModel('Manage');
$symbolicLinks = $modelManage->compute_default_links();
foreach ( $template->symboliclinks as $key => $symbolicLink) {


if ( isset( $symbolicLinks[$key])) {
$symbolicLinks[$key] = $symbolicLink;
}
}
return $symbolicLinks;
}


function isActionEditable( $action)
{
if ( in_array( $action, array( 'copy', 'unzip', 'SL', 'dirlinks', 'special'))) {
return true;
}
return false;
}


function _getSymbolicLinks()
{

$SL_actions = JRequest::getVar( 'SL_actions', '', 'default', 'array');
$SL_names = JRequest::getVar( 'SL_names', '', 'default', 'array');
$SL_files = JRequest::getVar( 'SL_files', '', 'default', 'array');
$SL_readOnly= JRequest::getVar( 'SL_readOnly', '', 'default', 'array');
$symbolicLinks = array();
foreach( $SL_actions as $i => $action) {

if ( $this->isActionEditable( $action)
&& !empty( $SL_files[$i])) {
if ( isset( $SL_readOnly[$i]) && $SL_readOnly[$i]=='true') {
$symbolicLinks[$SL_names[$i]] = array( 'action' => $action,
'file' => $SL_files[$i],
'readOnly' => true
);
}
else {
$symbolicLinks[$SL_names[$i]] = array( 'action' => $action,
'file' => $SL_files[$i]
);
}
}
else {
if ( isset( $SL_readOnly[$i]) && $SL_readOnly[$i]=='true') {
$symbolicLinks[$SL_names[$i]] = array( 'action' => $action,
'readOnly' => true
);
}
else {
$symbolicLinks[$SL_names[$i]] = array( 'action' => $action);
}
}
}
return $symbolicLinks;
}


function _getSharing()
{
$results = array();
if ( !empty( $_REQUEST['params'])) {

foreach( $_REQUEST['params'] as $key => $value) {
if ( substr( $key, 0, 5) == 'dbsh_') {
$results[ $key] = JFilterInput::clean( $value, 'cmd');
}
}
}
return $results;
}


function saveTemplate($tpl=null)
{
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');

$id = JRequest::getString('id', false);
if ( $id === false || empty( $id)) {
$msg = JText::_( 'Please provide a template identifier' );
$this->setError( $msg);
return $msg;
}
$enteredvalues = array();
$enteredvalues['id'] = $id;
$fromSiteID = JRequest::getString('filter_site_ids', null);
if ( $fromSiteID == '[unselected]') {
$fromSiteID = null;
}
$enteredvalues['fromSiteID'] = $fromSiteID;
$enteredvalues['groupName'] = JRequest::getString('groupName', null);
$enteredvalues['sku'] = JRequest::getString('sku', null);
$enteredvalues['title'] = JRequest::getString('title', null);
$enteredvalues['description'] = isset( $_REQUEST[ 'description']) ? stripslashes( $_REQUEST[ 'description']) : '';
$enteredvalues['validity'] = JRequest::getInt('validity', null);
$enteredvalues['validity_unit'] = JRequest::getString('validity_unit', null);
$enteredvalues['maxsite'] = JRequest::getInt('maxsite', null);
$enteredvalues['expireurl'] = '';
$urls = MultisitesHelper::getDomainList( 'expireurl');
if ( !empty( $urls)) {
$enteredvalues['expireurl'] = $urls[0];
}
$enteredvalues['toDomains'] = MultisitesHelper::getDomainList( 'toDomains');
$enteredvalues['redirect1st'] = (JRequest::getInt('redirect1st', 0)==1) ? true : false;
$enteredvalues['ignoreMasterIndex'] = (JRequest::getInt('ignoreMasterIndex', 0)==1) ? true : false;

$enteredvalues['continents'] = JRequest::getVar('continent_ids', array(), '', 'array');
$enteredvalues['countries'] = JRequest::getVar('countries_ids', array(), '', 'array');
$enteredvalues['regions'] = JRequest::getString('regions', null);
$enteredvalues['states'] = JRequest::getString('states', null);
$enteredvalues['cities'] = JRequest::getString('cities', null);
$enteredvalues['zipcodes'] = JRequest::getString('zipcodes', null);
$enteredvalues['fromLongitude'] = JRequest::getString('fromLongitude', null);
$enteredvalues['fromLatitude'] = JRequest::getString('fromLatitude', null);
$enteredvalues['toLongitude'] = JRequest::getString('toLongitude', null);
$enteredvalues['toLatitude'] = JRequest::getString('toLatitude', null);
$enteredvalues['metro'] = JRequest::getString('metro', null);
$enteredvalues['area'] = JRequest::getString('area', null);
$enteredvalues['geoip_ignoreundefined'] = JRequest::getString('geoip_ignoreundefined', null);
$enteredvalues['geoip_ignorepattern'] = JRequest::getString('geoip_ignorepattern', null);
$enteredvalues['geoip_ignoretimeout'] = JRequest::getInt('geoip_ignoretimeout', 86400);
$enteredvalues['sortOrdering'] = JRequest::getString('sortOrdering', null);

$enteredvalues['browser_types'] = JRequest::getVar('browser_types', array(), '', 'array');
$enteredvalues['browser_langs'] = JRequest::getString('browser_langs', null);
$enteredvalues['browser_ignorepattern'] = JRequest::getString('browser_ignorepattern', null);
$enteredvalues['browser_ignoretimeout'] = JRequest::getInt('browser_ignoretimeout', 86400);

$str = JRequest::getString('toSiteID', null);
$enteredvalues['toSiteID'] = (string) preg_replace( '/[^A-Z0-9_\.\-{}]/i', '', $str);
$enteredvalues['shareDB'] = JRequest::getBool('shareDB');
$enteredvalues['adminUserID'] = JRequest::getInt('adminUserID', null);
$enteredvalues['adminUserLogin'] = JRequest::getString('adminUserLogin', null);
$enteredvalues['adminUserName'] = JRequest::getString('adminUserName', null);
$enteredvalues['adminUserEmail'] = JRequest::getString('adminUserEmail', null);
$enteredvalues['adminUserPsw'] = JRequest::getString('adminUserPsw', null);
$toDBHost = JRequest::getCmd('toDBHost', null);
if ( !empty( $toDBHost)) {
$enteredvalues['toDBHost'] = $toDBHost;
}
$str = JRequest::getString('toDBName', null);
if ( !empty( $str)) {
$enteredvalues['toDBName'] = (string) preg_replace( '/[^A-Z0-9_\.\-{}]/i', '', $str);
}
$str = JRequest::getVar( 'toDBUser', null, 'default', 'username');
if ( !empty( $str)) {
$enteredvalues['toDBUser'] = (string) preg_replace( '/[^A-Za-z0-9_\.\,\;\:\=\-\+\*\/\@\#\$\£!\(\){}\[\]§]/i',
'',
$str);
}
$str = JRequest::getVar( 'toDBPsw', null, 'default', 'password', 2);
if ( !empty( $str)) {
$enteredvalues['toDBPsw'] = (string) preg_replace( '/[^A-Za-z0-9_\.\,\;\:\=\-\+\*\/\&\@\#\$\£!\(\){}\[\]§]/i',
'',
$str);
}
$str = JRequest::getString('toPrefix', null);
$enteredvalues['toPrefix'] = (string) preg_replace( '/[^A-Z0-9_\-{}]/i', '', $str);
$enteredvalues['toSiteName'] = JRequest::getString('toSiteName', null);
$enteredvalues['setDefaultJLang'] = JRequest::getString('setDefaultJLang', null);
$enteredvalues['setDefaultTemplate'] = JRequest::getString('setDefaultTemplate', null);
$enteredvalues['setDefaultMenu'] = JRequest::getString('setDefaultMenu', null);
$deploy_dir = JRequest::getString('deploy_dir', null);
$deploy_create = JRequest::getString('deploy_create', null);
$alias_link = JRequest::getString('alias_link', null);
$delete_dir = JRequest::getString('delete_dir', null);
$media_dir = JRequest::getString('media_dir', null);
$images_dir = JRequest::getString('images_dir', null);
$templates_dir = JRequest::getString('templates_dir', null);
$log_dir = JRequest::getString('log_dir', null);
$tmp_dir = JRequest::getString('tmp_dir', null);
$cache_dir = JRequest::getString('cache_dir', null);
if ( !empty( $deploy_dir)) { $enteredvalues['deploy_dir'] = $deploy_dir; }
if ( !empty( $deploy_create)) { $enteredvalues['deploy_create'] = $deploy_create; }
if ( !empty( $alias_link)) { $enteredvalues['alias_link'] = $alias_link; }
if ( !empty( $delete_dir)) { $enteredvalues['delete_dir'] = $delete_dir; }
if ( !empty( $media_dir)) { $enteredvalues['media_dir'] = $media_dir; }
if ( !empty( $images_dir)) { $enteredvalues['images_dir'] = $images_dir; }
if ( !empty( $templates_dir)) { $enteredvalues['templates_dir'] = $templates_dir; }
if ( !empty( $log_dir)) { $enteredvalues['log_dir'] = $log_dir; }
if ( !empty( $tmp_dir)) { $enteredvalues['tmp_dir'] = $tmp_dir; }
if ( !empty( $cache_dir)) { $enteredvalues['cache_dir'] = $cache_dir; }

if ( empty( $fromSiteID)) {

$enteredvalues['media_dir'] = null;
$enteredvalues['images_dir'] = null;
}
$toFTP_enable = JRequest::getString('toFTP_enable', null);
$toFTP_host = JRequest::getString('toFTP_host', null);
$toFTP_port = JRequest::getInt('toFTP_port', null);
$toFTP_user = JRequest::getString('toFTP_user', null);
$toFTP_psw = JRequest::getString('toFTP_psw', null);
$toFTP_rootpath = JRequest::getString('toFTP_rootpath', null);
if ( !is_null( $toFTP_enable)) { $enteredvalues['toFTP_enable'] = $toFTP_enable; }
if ( !empty( $toFTP_host)) { $enteredvalues['toFTP_host'] = $toFTP_host; }
if ( !empty( $toFTP_port)) { $enteredvalues['toFTP_port'] = $toFTP_port; }
if ( !empty( $toFTP_user)) { $enteredvalues['toFTP_user'] = $toFTP_user; }
if ( !empty( $toFTP_psw)) { $enteredvalues['toFTP_psw'] = $toFTP_psw; }
if ( !empty( $toFTP_rootpath)) { $enteredvalues['toFTP_rootpath'] = $toFTP_rootpath; }
$enteredvalues['isnew'] = (JRequest::getInt('isnew', 0)==1) ? true : false;
$enteredvalues['symboliclinks'] = $this->_getSymbolicLinks();

if ( !empty( $fromSiteID)) {
$enteredvalues['dbsharing'] = $this->_getSharing();
}

$this->assignAds();
$this->assignRef('id', $id);
$this->assign('isnew', $enteredvalues['isnew']);

foreach( $enteredvalues as $key => $value) {
if ( empty( $enteredvalues[$key]) || $enteredvalues[$key]=='[unselected]') {
unset( $enteredvalues[$key]);
}
}

$model = $this->getModel();
if ( !$model->save( $enteredvalues, true)) {
$msg = $model->getError();
JError::raiseWarning( 500, $msg);
return $msg;
}
$msgid = ($this->isnew) ? 'TEMPLATE_CREATED' : 'TEMPLATE_UPDATED';
$msg = JText::sprintf( $msgid, $this->id);
$cache = JFactory::getCache();
$cache->clean();
return $msg;
}

function getDBSharingLevel($param, $ignoreUL = false)
{
$this->tree_id = null;
$txt = null;
if (count($param->children())) {
$tmp = $this->treeparams;
$this->treeparams = $param;
$ignoreUL_save = $this->ignoreUL;
$this->ignoreUL = $ignoreUL;
$txt = $this->loadTemplate('sharing');
$this->treeparams = $tmp;
$this->ignoreUL = $ignoreUL_save;
}
return $txt;
}
} 

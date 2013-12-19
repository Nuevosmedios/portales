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
require_once( JPATH_COMPONENT .DS. 'classes' .DS. 'template.php');
@include_once( dirname( __FILE__).'/view_variant.php');
if ( !class_exists( 'MultisitesViewManageVariant')) {
class MultisitesViewManageVariant extends JView2Win {}
}




class MultisitesViewManage extends MultisitesViewManageVariant
{

var $_formName = 'Site';
var $_lcFormName = 'site';


function display($tpl=null)
{
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
$this->setLayout( 'default');

$formName = $this->_formName;
$lcFormName = $this->_lcFormName;
$site_title = '';
if ( version_compare( JVERSION, '1.6') >= 0) {
$sitename = JFactory::getApplication()->getCfg( 'sitename');
if ( !empty( $sitename)) {
$site_title = '<span style="font-size:12px;padding-left:10px; ">: '.$sitename.'</span>';
}
}
J2WinToolBarHelper::title( JText::_( 'SITE_LIST_TITLE').$site_title, 'config.png' );
J2WinToolBarHelper::addNewX( "add$formName" );
J2WinToolBarHelper::customX( "addLike$formName", 'copy.png', 'copy.png', 'New Like', true );
J2WinToolBarHelper::editListX( "edit$formName");
J2WinToolBarHelper::customX( "delete$formName", 'delete.png', 'delete_f2.png', 'Delete', true );
J2WinToolBarHelper::help( 'screen.' .$lcFormName. 'manager', true);
if ( version_compare( JVERSION, '2.5') >= 0) {

if (JFactory::getUser()->authorise('core.admin', 'com_multisites')) {
J2WinToolBarHelper::preferences('com_multisites');
}
}
$document = & JFactory::getDocument();
$document->setTitle(JText::_('SITE_LIST_TITLE'));
if ( version_compare( JVERSION, '3.0') >= 0) {
$document->addStyleSheet('components/com_multisites/css/list.css');
$document->addScript("components/com_multisites/assets/j30/ischecked.js");
$document->addScript("components/com_multisites/assets/j30/checkall.js");
}

$filters = &$this->_getFilters();

$model = &$this->getModel();
$model->setFilters( $filters);
$sites = &$this->get('Sites');
$this->assignRef('sites', $sites);
$allSites = &$this->get('AllSites');
$this->assignRef('allSites', $allSites);

$version = new JVersion();
$joomlaversion = $version->getShortVersion();
$jvers = explode( '.', $joomlaversion);
if ( count( $jvers) >= 3
&& $jvers[0] == '1' && $jvers[1] == '5'
&& (!is_numeric( $jvers[2]) || (int)$jvers[2] < 3)
)
{
$db = JFactory::getDBO();
$dbPrefix = $db->getPrefix();
if ( $dbPrefix == 'jos_') {
$msg = JText::_('SITE_VIEW_JOS_PREFIX');
$mainframe->enqueueMessage($msg, 'notice');
}
}

$modelPatches =& $this->getModel( 'Patches' );
$isPatchesInstalled = $modelPatches->isPatchesInstalled();
if (!$isPatchesInstalled) {
$msg = JText::_('SITE_VIEW_INSTALLPATCHES');
$mainframe->enqueueMessage($msg, 'notice');
}
$lists = &$this->_getViewLists( $filters, true, true);
$pagination = &$this->_getPagination( $filters, $this->get('CountAll'));

$this->assignAds();
$this->assignRef('pagination', $pagination);
$this->assignRef('lists', $lists);
$this->assignRef('limitstart', $limitstart);
$this->assignRef('option', $option);
JHTML::_('behavior.tooltip');

parent::display($tpl);
}


function &_getFilters()
{
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
$filters = array();

$client = JRequest::getWord( 'filter_client', 'site' );

$search = $mainframe->getUserStateFromRequest( "$option.$client.search", 'search', '', 'string' );
$filters['search'] = JString::strtolower( $search );

$filters['status'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_status", 'filter_status', '[unselected]', 'string' );
$filters['sitename'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_sitename", 'filter_sitename', '[unselected]', 'string' );
$filters['host'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_host", 'filter_host', '[unselected]', 'string' );
$filters['db'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_db", 'filter_db', '[unselected]', 'string' );
$filters['site_ids'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_site_ids", 'filter_site_ids', '[unselected]', 'string' );
$filters['owner_id'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_owner_id", 'filter_owner_id', '[unselected]', 'string' );


$filters['order'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_order", 'filter_order', '', 'cmd' );
$filters['order_Dir'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_order_Dir", 'filter_order_Dir', '', 'word' );

$filters['limit'] = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
$filters['limitstart'] = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );
return $filters;
}

function &_getViewLists( &$filters, $facultative_status=false, $onChangeStatus=false, $row = null)
{

if ( !empty( $row)) { $lists['status'] = MultisitesHelper::getAllStatusList( 'status', $row->status, $facultative_status, $onChangeStatus); }
else { $lists['status'] = MultisitesHelper::getAllStatusList( 'filter_status', $filters['status'], $facultative_status, $onChangeStatus); }
$lists['sitename'] = MultisitesHelper::getSiteNameList( $this->allSites, $filters['sitename']);
$lists['dbserver'] = MultisitesHelper::getDBServerList( $this->allSites, $filters['host']);
$lists['dbname'] = MultisitesHelper::getDBNameList( $this->allSites, $filters['db']);
$lists['site_ids'] = MultisitesHelper::getSiteIdsList( $this->allSites, $filters['site_ids']);
$lists['owner_id'] = MultisitesHelper::getSiteOwnerList( $this->allSites, $filters['owner_id']);

if ( !empty( $this->_models[strtolower('Templates' )])) {
$model =& $this->getModel( 'Templates');
}
else {
$model = null;
}
if ( is_object( $model)) {
$templates =& $model->getTemplates();
$lists['templates'] = MultisitesHelper::getTemplatesList( $templates, $this->template->id);
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
if ( !empty( $row->setDefaultTemplate)) { $setDefaultTemplate = $row->setDefaultTemplate; }
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
$lists['setDefaultTemplate'] = MultisitesHelper::getJoomlaTemplateList( $fromSite, $setDefaultTemplate, $row);
$lists['setDefaultMenu'] = MultisitesHelper::getMenuItemsList( $fromSiteID, $setDefaultMenu, $row);
$lists['setDefaultJLang'] = MultisitesHelper::getJLanguageList( $fromSite, $setDefaultJLang, $row);

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


function deleteForm($tpl=null)
{
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
$this->setLayout( 'delete');
JRequest::setVar( 'hidemainmenu', 1 );

$site = &$this->get('CurrentRecord');
$site_dir = &$this->get('SiteDir');
$template = &$site->getTemplate();

J2WinToolBarHelper::title( JText::_( 'SITE_DELETE_TITLE' ) . ': <small><small>[ '. JText::_( 'Delete' ) .' ]</small></small>', 'config.png' );
J2WinToolBarHelper::custom( 'doDeleteSite', 'delete.png', 'delete_f2.png', 'Delete', false );
J2WinToolBarHelper::cancel();
J2WinToolBarHelper::help( 'screen.sitemanager.delete', true );
$document = & JFactory::getDocument();
$document->setTitle( JText::sprintf( 'SITE_DELETE_CONFIRM_SITE', $site->sitename) );

$this->assignAds();
$this->assignRef('site', $site);
$this->assignRef('site_dir', $site_dir);
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
if($edit == 'edit' || $edit == 'newLike') {
$table = &$this->get('CurrentRecord');
$template = new Jms2WinTemplate();
$template->load( $table->fromTemplateID);
}
else {
$table = &$this->get('NewRecord');
$template = new Jms2WinTemplate();
}
$this->assignRef('row', $table);
$this->assignRef('template', $template);

$formName = $this->_formName;
$lcFormName = $this->_lcFormName;
$isNew = (($table->id == '') || ($edit == 'newLike'));
if ( $isNew) {
$text = JText::_('SITE_EDIT_TITLE_NEW');
}
else {
$text = JText::_('SITE_EDIT_TITLE_EDIT');
}
J2WinToolBarHelper::title( JText::_( 'SITE_EDIT_TITLE' ).': <small><small>[ '. $text.' ]</small></small>', 'config.png' );
J2WinToolBarHelper::custom( "save$formName", 'save.png', 'save_f2.png', 'Save', false );
J2WinToolBarHelper::cancel( 'manage');
J2WinToolBarHelper::help( 'screen.' .$lcFormName. 'manager.new', true );

$filters = &$this->_getFilters();
$model = &$this->getModel();
$sites = &$this->get('Sites');
$this->assignRef('sites', $sites);
$allSites = &$this->get('AllSites');
$this->assignRef('allSites', $allSites);
$lists = &$this->_getViewLists( $filters, false, false, $this->row);

if ( !empty( $table) && !empty( $table->dbprefix)) { $style_showConfigFields = ''; } 
else { $style_showConfigFields = 'style="display:none;"'; }

if ( empty( $table->fromTemplateID) || $table->fromTemplateID == '[unselected]') {

$style_shareCheckBox = 'style="display:none;"';
$table->shareDB = '';
}
else {

$style_shareCheckBox = ''; 
}
$style_shareDB = ''; 
if ( $isNew) {
if ( !empty( $template->shareDB)) {
$table->shareDB = $template->shareDB;
}
}
if ( !empty( $table->shareDB) && $table->shareDB == true) {
$style_showDBFields = 'style="display:none;"';
}
else {
$style_shareDB = '';
$style_showDBFields = '';

if ( !empty( $template->toPrefix) || !empty( $table->toPrefix)) {

$style_shareDB = '';
$style_showDBFields = '';
}
else {

$style_shareDB = 'style="display:none;"';
$style_showDBFields = 'style="display:none;"';
}
}

if ( isset( $table->toFTP_enable) && ($table->toFTP_enable == '0' || $table->toFTP_enable == '1')) {
$style_showFTPFields = '';
}

else {

if ( isset( $template->toFTP_enable) && ($template->toFTP_enable == '0' || $template->toFTP_enable == '1')) {
$style_showFTPFields = '';
}

else {
$style_showFTPFields = 'style="display:none;"';
}
}
$this->assignRef('style_showConfigFields',$style_showConfigFields); 
$this->assignRef('style_shareCheckBox', $style_shareCheckBox);
$this->assignRef('style_shareDB', $style_shareDB);
$this->assignRef('style_showDBFields', $style_showDBFields);
$this->assignRef('style_showFTPFields', $style_showFTPFields);
$this->assignAds();
$this->assignRef('lists', $lists);
$this->assign('isnew', $isNew);
$this->assignRef('option', $option);
JHTML::_('behavior.tooltip');
parent::display($tpl);
}


function saveSite($tpl=null)
{
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');

$id = JRequest::getString('id', false);

$id = (string) preg_replace('/[^A-Z0-9_\.\-@]/i', '', $id);
$id = ltrim($id, '.');
if ( $id === false) {
$msg = JText::_( 'Please provide a site id' );
$this->setError( $msg);
return $msg;
}
$enteredvalues = array();
$enteredvalues['id'] = $id;
$enteredvalues['status'] = JRequest::getString('status', null);
$enteredvalues['payment_ref'] = JRequest::getString('payment_ref', null);
$enteredvalues['expiration'] = JRequest::getString('expiration', null);
$enteredvalues['owner_id'] = JRequest::getInt('owner_id');
$enteredvalues['site_prefix'] = JRequest::getString('site_prefix', null);
$enteredvalues['site_alias'] = JRequest::getString('site_alias', null);
$enteredvalues['siteComment'] = isset( $_REQUEST[ 'siteComment']) ? stripslashes( $_REQUEST[ 'siteComment']) : '';
$enteredvalues['domains'] = MultisitesHelper::getDomainList( 'domains');
$enteredvalues['redirect1st'] = JRequest::getString('redirect1st', null);
$enteredvalues['ignoreMasterIndex'] = JRequest::getString('ignoreMasterIndex', null);

$enteredvalues['fromTemplateID'] = JRequest::getString('fromTemplateID', null);

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
$enteredvalues['geoip_ignoreundefined']= JRequest::getString('geoip_ignoreundefined', null);
$enteredvalues['geoip_ignorepattern'] = JRequest::getString('geoip_ignorepattern', null);
$enteredvalues['geoip_ignoretimeout'] = JRequest::getInt('geoip_ignoretimeout', null);
$enteredvalues['sortOrdering'] = JRequest::getString('sortOrdering', null);

$enteredvalues['browser_types'] = JRequest::getVar('browser_types', array(), '', 'array');
$enteredvalues['browser_langs'] = JRequest::getString('browser_langs', null);
$enteredvalues['browser_ignorepattern'] = JRequest::getString('browser_ignorepattern', null);
$enteredvalues['browser_ignoretimeout'] = JRequest::getInt('browser_ignoretimeout', null);
$enteredvalues['shareDB'] = JRequest::getBool('shareDB');
$enteredvalues['toDBType'] = JRequest::getCmd('toDBType', null);
$enteredvalues['toDBHost'] = JRequest::getCmd('toDBHost', null);
$enteredvalues['toDBName'] = (string) preg_replace( '/[^A-Z0-9_\.\-{}]/i', '',
JRequest::getString('toDBName', ''));
$enteredvalues['toDBUser'] = (string) preg_replace( '/[^A-Za-z0-9_\.\,\;\:\=\-\+\*\/\@\#\$\£!\(\){}\[\]§]/i', '',
JRequest::getVar( 'toDBUser', '', 'default', 'username', 2));
$enteredvalues['toDBPsw'] = (string) preg_replace( '/[^A-Za-z0-9_\.\,\;\:\=\-\+\*\/\&\@\#\$\£!\(\){}\[\]§]/i', '',
JRequest::getVar( 'toDBPsw', '', 'default', 'password', 2));
$enteredvalues['toPrefix'] = JRequest::getString('toPrefix', null);
$enteredvalues['toSiteName'] = JRequest::getString('toSiteName', null);
$enteredvalues['toMetaDesc'] = JRequest::getString('toMetaDesc', null);
$enteredvalues['toMetaKeys'] = JRequest::getString('toMetaKeys', null);
$enteredvalues['newAdminEmail'] = JRequest::getString('newAdminEmail', null);
$enteredvalues['newAdminPsw'] = JRequest::getString('newAdminPsw', null);
$enteredvalues['setDefaultJLang'] = JRequest::getString('setDefaultJLang', null);
$enteredvalues['setDefaultTemplate'] = JRequest::getString('setDefaultTemplate', null);
$enteredvalues['setDefaultMenu'] = JRequest::getString('setDefaultMenu', null);

$enteredvalues['deploy_dir'] = JRequest::getString('deploy_dir', null);
$enteredvalues['deploy_create'] = JRequest::getString('deploy_create', null);
$enteredvalues['alias_link'] = JRequest::getString('alias_link', null);
$enteredvalues['delete_dir'] = JRequest::getString('delete_dir', null);
$enteredvalues['media_dir'] = JRequest::getString('media_dir', null);
$enteredvalues['images_dir'] = JRequest::getString('images_dir', null);
$enteredvalues['templates_dir'] = JRequest::getString('templates_dir', null);
$enteredvalues['tmp_dir'] = JRequest::getString('tmp_dir', null);

$enteredvalues['toFTP_enable'] = JRequest::getString('toFTP_enable', null);
$enteredvalues['toFTP_host'] = JRequest::getString('toFTP_host', null);
$enteredvalues['toFTP_port'] = JRequest::getInt('toFTP_port', null);
$enteredvalues['toFTP_user'] = JRequest::getString('toFTP_user', null);
$enteredvalues['toFTP_psw'] = JRequest::getString('toFTP_psw', null);
$enteredvalues['toFTP_rootpath'] = JRequest::getString('toFTP_rootpath', null);
$enteredvalues['isnew'] = (JRequest::getInt('isnew', 0)==1) ? true : false;

$site_dir = JPATH_ROOT; 

$this->assignAds();
$this->assignRef('id', $id);
$this->assignRef('site_dir', $site_dir);
$this->assignRef('domains', $enteredvalues['domains']);
$this->assign('isnew', $enteredvalues['isnew']);

foreach( $enteredvalues as $key => $value) {
if ( empty( $enteredvalues[$key]) || $enteredvalues[$key]=='[unselected]') {
unset( $enteredvalues[$key]);
}
}

$model = $this->getModel();
if ( !$model->deploySite( $enteredvalues)) {
$msg = $model->getError();
if ( empty( $msg)) {
$msg = JText::_('SITE_SAVE_DEPLOY_ERR');
}
JError::raiseWarning( 500, $msg);
return '';
}

if ( !empty( $enteredvalues['ignoreMasterIndex'])) {}

else {
$model->createMasterIndex();
}
$msgid = ($this->isnew) ? 'SITE_DEPLOYED' : 'SITE_UPDATED';
if ( !empty( $enteredvalues['indexDomains'])) {
$domainStr = implode(",", $enteredvalues['indexDomains']);
}
else {
$domainStr = implode(",", $this->domains);
}




$deploy_dir = &$this->get('DeployDir');
if ( empty( $deploy_dir)) {

$deploy_dir = JPATH_ROOT;
}
$msg = JText::sprintf( $msgid, $this->id, $domainStr, $deploy_dir);
return $msg;
}

function getSiteToolTips( $site)
{
if ( !empty( $site->site_dir)) {
$site_dir = $site->site_dir;
}
else {
$site_dir = JPATH_ROOT;
}
$str = $site->sitename . '<br/><br/>'
. '<i><b>' . JText::_( 'DNS mapping') . ' :</b></i><br/>'
. '- ' . $site_dir;
if ( !empty( $site->deploy_dir)) {
$str .= '<br/>'
. '- ' . $site->deploy_dir;
}
if ( !empty( $site->fromTemplateID)) {
$str .= '<br/>'
. '<i><b>' . JText::_( 'SITE_EDIT_TEMPLATES') . ' :</b></i> '
. $site->fromTemplateID;
$template = & $site->getTemplate();
if ( !empty( $template->fromSiteID)) {
$str .= '&nbsp;(&nbsp;' . $template->fromSiteID . '&nbsp;)';
}
}
return JText::_( 'Edit Site' ). '::' . $str ;
}
} 

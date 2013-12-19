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

require_once( JPATH_COMPONENT .DS. 'libraries' .DS. 'joomla' .DS. 'application' .DS. 'component' .DS. 'view2win.php');
require_once( JPATH_COMPONENT .DS. 'helpers' .DS. 'checkdb.php');




class MultisitesViewCheckDB extends JView2Win
{

var $_formName = 'CheckDB';
var $_lcFormName = 'checkdb';


function display($tpl=null)
{
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
$this->setLayout( 'default');

$formName = $this->_formName;
$lcFormName = $this->_lcFormName;
J2WinToolBarHelper::title( JText::_( 'CHECKDB_LIST_TITLE'), 'config.png' );
include( dirname(__FILE__).DS.'toolbar.php');
$document = & JFactory::getDocument();
$document->setTitle(JText::_('CHECKDB_LIST_TITLE'));
if ( version_compare( JVERSION, '1.6') >= 0) { JHTML::_('behavior.framework'); }
else { JHTML::_('behavior.mootools'); }
$document->addStylesheet( str_replace( '/index.php', '', JURI::base( true))."/components/$option/css/checkdb.css");
$document->addScriptDeclaration( 'var g_checkdb_option = "'.$option.'";');
$document->addScript("components/$option/assets/checkdb.js");
if ( version_compare( JVERSION, '3.0') >= 0) {
$document->addStyleSheet('components/com_multisites/css/list.css');
$document->addScript("components/com_multisites/assets/j30/ischecked.js");
$document->addScript("components/com_multisites/assets/j30/checkall.js");
}
$cid = JRequest::getVar('cid', array(), '', 'array');
$this->assignRef( 'cid', $cid);

$filters = &$this->_getFilters();

$model = &$this->getModel();
$model->setState( 'filters', $filters);
$this->assignRef( 'model', $model);
$sites =& $model->getSites();

$master_site = & Site::getInstance( ':master_db:');
$master_site->sitename = 'Master website associated to the multisites directory defined in the settings';

$this_site = & Site::getInstance( ':this_site:');
$this_site->sitename = 'THIS WEBSITE IS CONSIDERED AS THE MASTER';

if ( $master_site->host == $this_site->host
&& $master_site->db == $this_site->db
&& $master_site->dbprefix == $this_site->dbprefix
)
{

array_unshift( $sites, $master_site);
}

else {
array_unshift( $sites, $master_site);
array_unshift( $sites, $this_site);
}

$action = !empty( $this->action) ? $this->action : 'getdbinfo';
$model->preprocessAction( $sites, $action);
$this->assignRef('sites', $sites);
$lists = &$this->_getViewLists( $filters, $model);
$pagination = &$this->_getPagination( $filters, count( $sites));

$this->assignRef('pagination', $pagination);
$this->assignRef('lists', $lists);
$this->assignRef('limitstart', $limitstart);
$this->assignRef('option', $option);
JHTML::_('behavior.tooltip');

parent::display($tpl);
}


function fixDB()
{
$this->assign( 'action', 'fixDB');
$this->display();
}


function fixUncheckedDB()
{
$this->assign( 'action', 'fixUncheckedDB');
$this->display();
}


function deleteJoomlaFiles()
{
$this->assign( 'action', 'deleteJoomlaFiles');
$cid = JRequest::getVar('cid', array(), '', 'array');
$this->assignRef( 'cid', $cid);
$this->display();
}


function downloadLatestJoomla()
{
$this->assign( 'action', 'downloadLatestJoomla');
$cid = JRequest::getVar('cid', array(), '', 'array');
$this->assignRef( 'cid', $cid);
$this->display();
}


function installLatestJoomla()
{
$model = &$this->getModel();
$installActions = $model->getInstallLatestJoomla_Actions();
$this->assign( 'action', $installActions);
$cid = JRequest::getVar('cid', array(), '', 'array');
$this->assignRef( 'cid', $cid);
$this->display();
}


function deleteSymLinks()
{
$this->assign( 'action', 'deleteSymLinks');
$cid = JRequest::getVar('cid', array(), '', 'array');
$this->assignRef( 'cid', $cid);
$this->display();
}


function refreshSymLinks()
{
$this->assign( 'action', 'refreshSymLinks');
$cid = JRequest::getVar('cid', array(), '', 'array');
$this->assignRef( 'cid', $cid);
$this->display();
}


function &_getFilters()
{
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
$filters = array();
$client = JRequest::getWord( 'filter_client', 'checkdb' );

$search = $mainframe->getUserStateFromRequest( "$option.$client.search", 'search', '', 'string' );
$filters['search'] = JString::strtolower( $search );
$filters['exttype'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_exttype", 'filter_exttype', '[unselected]', 'string' );
$filters['downloadedpackages'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_downloadedpackages", 'filter_downloadedpackages', '[unselected]', 'string' );
$filters['availablepackages'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_availablepackages", 'filter_availablepackages', '[unselected]', 'string' );
$filters['dbtype'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_dbtype", 'filter_dbtype', '[unselected]', 'string' );
$filters['schema'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_schema", 'filter_schema', '[unselected]', 'string' );

$filters['order'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_order", 'filter_order', '', 'cmd' );
$filters['order_Dir'] = $mainframe->getUserStateFromRequest( "$option.$client.filter_order_Dir", 'filter_order_Dir', '', 'word' );

$filters['limit'] = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
$filters['limitstart'] = JRequest::getInt( 'limitstart', 0);
return $filters;
}

function &_getViewLists( &$filters, &$model)
{
$lists = array();
$lists['exttype'] = MultisitesHelperCheckDB::getExtensionTypes( $filters['exttype'], JText::_( 'Extension type'), '');
$lists['downloadedpackages'] = MultisitesHelperCheckDB::getDownloadedPackages( $model, 'filter_downloadedpackages', $filters['downloadedpackages'], JText::_( 'Downloaded packages'), '');
$lists['availablepackages'] = MultisitesHelperCheckDB::getAvailablePackages( $model, $filters['availablepackages'], JText::_( 'Available for download'), '');
$lists['dbtype'] = MultisitesHelperCheckDB::getDBTypeList( $model, $filters['dbtype'], JText::_( 'DB types'), '');
$lists['schema'] = MultisitesHelperCheckDB::getSchemaList( $model, 'filter_schema', $filters['schema'],
array( '[unselected]' => '- '.JText::_( 'Automatic').' -',
'[ignore]' => '- '.JText::_( 'Ignore').' -'
),
'');

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


function schemainfo( $key)
{
$labels = array( 'unchecked' => 'AJAXCHECKDB_SCHEMAINFO_UNCHECKED',
'ok' => 'AJAXCHECKDB_SCHEMAINFO_OK',
'error' => 'AJAXCHECKDB_SCHEMAINFO_ERROR',
'skipped' => 'AJAXCHECKDB_SCHEMAINFO_SKIPPED'
);
if ( !empty( $this->datamodel->subActionResult['status'][$key])) {
$this->assign( 'schema_key', $key);
$this->assign( 'schema_label', $labels[$key]);
$this->assignRef( 'schema_result', $this->datamodel->subActionResult['status'][$key]);
echo $this->loadTemplate( 'datamodel_schemainfo');
}
}


function getDownloadedPackages( $datamodel)
{

$rowNbr = !empty( $this->tr_id) ? substr( $this->tr_id, 7) : -1;
$field_id = 'downloadedpackages['.$this->site->id.']['.$datamodel->extension_id.']';
$downloadpackage = '';
if ( isset( $datamodel->downloadpackage)) {
$downloadpackage = $datamodel->downloadpackage;
}
else if ( !empty( $this->enteredvalues['extension_id']) && $datamodel->extension_id == $this->enteredvalues['extension_id']
&& !empty( $this->enteredvalues['downloadpackage'])
)
{
$downloadpackage = $this->enteredvalues['downloadpackage'];
}
return MultisitesHelperCheckDB::getDownloadedPackages( $this->model,
$field_id,
$downloadpackage,
JText::_( 'automatic'),
'onchange="selectedPackage( this, \''.$rowNbr.'\');"');
}


function getSchemaList( $datamodel)
{

$rowNbr = !empty( $this->tr_id) ? substr( $this->tr_id, 7) : -1;
$field_id = 'schema['.$this->site->id.']['.$datamodel->extension_id.']';
$schemavalue = '';
if ( isset( $datamodel->schema)) {
$schemavalue = $datamodel->schema;
}
else if ( !empty( $this->enteredvalues['extension_id']) && $datamodel->extension_id == $this->enteredvalues['extension_id']
&& !empty( $this->enteredvalues['schema'])
)
{
$schemavalue = $this->enteredvalues['schema'];
}
$filters = array( 'dbtype' => $this->site->dbtype,
'type' => $datamodel->type,
'element' => $datamodel->element,
'folder' => (!empty( $datamodel->folder) ? $datamodel->folder : '')
);
return MultisitesHelperCheckDB::getSchemaList( $this->model,
$field_id,
$schemavalue,
array( '[unselected]' => '- '.JText::_( 'Default rule').' -',
'[usersql]' => '- '.JText::_( 'Free SQL only').' -',
'[ignore]' => '- '.JText::_( 'Ignore').' -'
),
'onchange="selectedSchema( this, \''.$rowNbr.'\');"',
$filters
);
}


function getLegacyModeList( $datamodel)
{

$rowNbr = !empty( $this->tr_id) ? substr( $this->tr_id, 7) : -1;
$field_id = 'legacymode['.$this->site->id.']['.$datamodel->extension_id.']';
$legacymodevalue = '';
if ( isset( $datamodel->legacymode)) {
$legacymodevalue = $datamodel->legacymode;
}
else if ( !empty( $this->enteredvalues['extension_id']) && $datamodel->extension_id == $this->enteredvalues['extension_id']
&& !empty( $this->enteredvalues['legacymode'])
)
{
$legacymodevalue = $this->enteredvalues['legacymode'];
}
return MultisitesHelperCheckDB::getLegacyModeList( $field_id, $legacymodevalue,
'onchange="selectedLegacyMode( this, \''.$rowNbr.'\');"');
}


function ajaxCheckDB()
{
$option = JRequest::getCmd('option');
$this->assignRef('option', $option);
$this->setLayout( 'ajaxcheckdb');
$enteredvalues = array();
$enteredvalues['site_id'] = JRequest::getString('site_id', null);
$enteredvalues['action'] = JRequest::getString('action', null);
$enteredvalues['extension_id'] = JRequest::getInt('extension_id', null);
$enteredvalues['downloadpackage'] = JRequest::getString('downloadpackage', null);
$enteredvalues['schema'] = JRequest::getString('schema', null);
$enteredvalues['legacysql'] = JRequest::getBool('legacysql', null);
$enteredvalues['legacymode'] = JRequest::getString('legacymode', null);
if ( !empty( $enteredvalues['legacymode'])) { $enteredvalues['legacymode'] = explode( '|', $enteredvalues['legacymode']); }
$enteredvalues['usersql'] = JRequest::getString('usersql', null);
if ( !empty( $enteredvalues['usersql'])) { $enteredvalues['usersql'] = base64_decode( $enteredvalues['usersql']); }
$enteredvalues['subresult_id'] = JRequest::getString('subresult_id', null);
$enteredvalues['tr_id'] = JRequest::getString('tr_id', null);

if ( empty( $enteredvalues['site_id']) && empty( $enteredvalues['action'])) {
$lists = array( 'type' => 'checkdb', 'errors' => array( JText::_( 'Missing Site ID and action')));
return $lists;
}

$this->assignRef('action', $enteredvalues['action']);
if ( !empty( $enteredvalues['tr_id'])) { $this->assign( 'tr_id', $enteredvalues['tr_id']); }
$this->assign( 'enteredvalues', $enteredvalues);
$model = &$this->getModel();
$this->assignRef( 'model', $model);

$lists = $model->doAction( $enteredvalues);
if ( empty($lists)) {
$lists = array( 'type' => 'checkdb', 'dmresult' => JText::_( 'No data'));
}
else if ( !empty( $lists['errors'])) {
if ( empty( $lists['type'])) {
$lists = array_merge( array( 'type' => 'checkdb'), $lists);
}
}

else {
if ( empty( $lists['type'])) {
$lists = array_merge( array( 'type' => 'checkdb'), $lists);
}
if ( empty( $lists['datamodel'])) {
$dmresult = JText::_( 'No datamodel');
}
else {

if ( !empty( $enteredvalues['extension_id']) && !empty( $enteredvalues['subresult_id'])
&& count( $lists['datamodel']) == 1 && $lists['datamodel'][0]->extension_id == $enteredvalues['extension_id']
)
{
$this->assignRef( 'site', $lists['site']);
$this->assignRef( 'datamodel', $lists['datamodel'][0]);
$subresult = $this->loadTemplate( 'datamodel_subresult');
}
else {
$dmresult = '<table class="datamodel">'
. $this->loadTemplate( 'datamodel_thead')
. '<tbody>'
;
$this->assignRef( 'site', $lists['site']);
foreach( $lists['datamodel'] as $datamodel) {
$this->assignRef( 'datamodel', $datamodel);
$dmresult .= $this->loadTemplate( 'datamodel');
}
$dmresult .= '</tbody>'
. '</table>'
;
}
}
if ( !empty( $dmresult)) { $lists['dmresult'] = $dmresult; }
if ( !empty( $subresult)) { $lists['subresult'] = $subresult; }
}

if ( isset( $lists['site'])) {
unset( $lists['site']);
}
if ( !empty( $enteredvalues['subresult_id'])) {
$lists['subresult_id'] = $enteredvalues['subresult_id'];
}
return $lists;
}
} 

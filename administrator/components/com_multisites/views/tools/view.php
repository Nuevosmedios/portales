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
require_once( JPath::clean( JPATH_COMPONENT_ADMINISTRATOR.'/libraries/joomla/application/component/view2win.php'));




class MultisitesViewTools extends JView2Win
{

var $_formName = 'Tools';
var $_lcFormName = 'tools';


function display($tpl=null)
{
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
$this->setLayout( 'default');

$formName = $this->_formName;
$lcFormName = $this->_lcFormName;
J2WinToolBarHelper::title( JText::_( 'TOOLS_VIEW_TITLE' ), 'config.png' );

J2WinToolBarHelper::apply( "apply$formName", JText::_( 'Execute'));
J2WinToolBarHelper::cancel( 'manage');
J2WinToolBarHelper::help( 'screen.' .$lcFormName. 'manager', true );
$document = & JFactory::getDocument();
$document->setTitle(JText::_('TOOLS_VIEW_TITLE'));
if ( version_compare( JVERSION, '1.6') >= 0) { JHTML::_('behavior.framework'); }
else { JHTML::_('behavior.mootools'); }
$document->addScript('components/com_multisites/assets/treesites.js');
$document->addStyleSheet('components/com_multisites/assets/treesites.css');
$document->addScript('components/com_multisites/assets/inputtree.js');
JHTML::stylesheet('mootree.css');
if ( version_compare( JVERSION, '3.0') >= 0) {
$document->addStyleSheet('components/com_multisites/css/tabs.css');
$document->addStyleSheet('components/com_multisites/css/list.css');
}
$document->addScript( JURI::root(true). '/media/system/js/tabs.js' );
$this->assignAds();

$treeSites = &$this->get('SiteDependencies');

$this->assignRef('treeSites', $treeSites);
$this->assign('node_id' , 0);
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

function getChildrenTree( $sites, $tree_id = '')
{
if ( empty( $sites)) {
return '';
}
$txt = "<ul $tree_id>";
foreach( $sites as $site) {
$this->assignRef( 'site', $site);
$this->assign('tree_id', $tree_id);
$str = $this->loadTemplate('site');
$child_txt = '';
if ( !empty( $site->_treeChildren)) {
$child_txt = $this->getChildrenTree( $site->_treeChildren);
}
$txt .= str_replace( "{__children__}", $child_txt, $str);
}
$txt .= '</ul>';
return $txt;
}

function applyTools()
{
JRequest::setVar( 'hidemainmenu', 1 );
$mainframe = &JFactory::getApplication();
$option = JRequest::getCmd('option');
$this->setLayout( 'apply');

$formName = $this->_formName;
$lcFormName = $this->_lcFormName;
J2WinToolBarHelper::title( JText::_( 'TOOLS_VIEW_TITLE_APPLY' ), 'config.png' );
J2WinToolBarHelper::cancel( 'tools');
$document = & JFactory::getDocument();
$document->setTitle(JText::_('TOOLS_VIEW_TITLE_APPLY'));
if ( version_compare( JVERSION, '1.6') >= 0) { JHTML::_('behavior.framework'); }
else { JHTML::_('behavior.mootools'); }
$document->addScript('components/com_multisites/assets/toolapply.js');
$document->addStyleSheet('components/com_multisites/assets/toolapply.css');
$this->assignAds();
$enteredvalues = array();
$enteredvalues['site_id'] = JRequest::getString('site_id', null);;
$enteredvalues['comActions'] = JRequest::getVar( 'acom', null, 'post', 'array' );
$enteredvalues['comPropagates'] = JRequest::getVar( 'ccom', array(), 'post', 'array' );
$enteredvalues['comOverwrites'] = JRequest::getVar( 'cow', array(), 'post', 'array' );
$enteredvalues['modActions'] = JRequest::getVar( 'amod', null, 'post', 'array' );
$enteredvalues['modPropagates'] = JRequest::getVar( 'cmod', array(), 'post', 'array' );
$enteredvalues['modOverwrites'] = JRequest::getVar( 'cmow', array(), 'post', 'array' );
$enteredvalues['plgActions'] = JRequest::getVar( 'aplg', null, 'post', 'array' );
$enteredvalues['plgPropagates'] = JRequest::getVar( 'cplg', array(), 'post', 'array' );
$enteredvalues['plgOverwrites'] = JRequest::getVar( 'cpow', array(), 'post', 'array' );
$enteredvalues['tmplActions'] = JRequest::getVar( 'atmpl', null, 'post', 'array' );
$enteredvalues['tmplPropagates'] = JRequest::getVar( 'ctmpl', array(), 'post', 'array' );
$enteredvalues['tmplOverwrites'] = JRequest::getVar( 'ctow', array(), 'post', 'array' );
$enteredvalues['langActions'] = JRequest::getVar( 'alan', null, 'post', 'array' );
$enteredvalues['langPropagates'] = JRequest::getVar( 'clan', array(), 'post', 'array' );
$enteredvalues['langOverwrites'] = JRequest::getVar( 'clow', array(), 'post', 'array' );
$model = & $this->getModel();
$sites = $model->getActionsToDo( $enteredvalues);
$this->assignRef('sites', $sites);
$this->assignRef('option', $option);
JHTML::_('behavior.tooltip');
parent::display();
}




function getSiteExtensions()
{
$this->setLayout( 'extensions');
$site_id = JRequest::getString( 'site_id', '');
$model = & $this->getModel();
$site_info = & $model->getSiteInfo( $site_id);
$tablesInfo = & $model->getListOfTables( $site_id);


$extensions = & $model->getExtensions( $site_id);
$this->assignRef('site_info', $site_info);
$this->assignRef('extensions', $extensions);
$this->assignRef('tablesInfo', $tablesInfo);
$result = $this->loadTemplate();
return $result;
}

function _isExtensionSite( $colNumber)
{
foreach( $this->extensions as $categories) {
foreach( $categories as $extension) {
if ( isset( $extension[$colNumber])) {
return true;
}
}
}
return false;
}

function _isMaster()
{
if ( $this->site_info->id == ':master_db:') {
return true;
}
return false;
}

function _hasChildren()
{
$model = & $this->getModel();
return $model->hasChildren( $this->site_info->id);
}


function _getToolTips( $columns, $rowNbr)
{
$extension = & $columns[0];

if ( !empty( $extension->option)) {
$option = $extension->option;
}

else if ( !empty( $extension->module)) {
$option = $extension->module;
}

else if ( !empty( $extension->folder) && !empty( $extension->element)) {
$option = $extension->folder . '/' . $extension->element;
}

else {
return '';
}
$result = "<b>Option:</b> $option";

if ( !empty( $columns[5])) {
$tablepatterns = array();
foreach( $columns[5] as $xmltable) {
$tablepattern = $xmltable->attributes( 'name');
if ( $tablepattern == '[none]') {}
else {
$tablepatterns[] = $tablepattern;
}
}
if ( !empty( $tablepatterns)) {
$result .= '<br /><b>' . JText::_( 'TOOLS_VIEW_TABLE_PATTERNS') . ":</b>\n"
. "<ul>\n"
. '<li>' . implode( "</li>\n<li>", $tablepatterns) . "</li>\n"
. "</ul>\n"
;
}
}

if ( !empty( $columns[4])) {
$tablepatterns = array();
$tables = Jms2WinDBSharing::getTables( $columns[4]);
foreach( $tables as $xmltable) {
$tablepattern = $xmltable->attributes( 'name');
if ( $tablepattern == '[none]') {}
else {
$tablepatterns[] = $tablepattern;
}
}
if ( !empty( $tablepatterns)) {
$result .= '<br /><b>' . JText::_( 'TOOLS_VIEW_SHARED_PATTERNS') . ":</b>\n"
. "<ul>\n"
. '<li>' . implode( "</li>\n<li>", $tablepatterns) . "</li>\n"
. "</ul>\n"
;
}
}
return $result;
}

function _getTableType( $columns, $rowNbr, $ext_type=null)
{
$result = '-';
$result = '<img src="components/com_multisites/images/minus.png" title="' . JText::_( 'TOOLS_VIEW_INSTALL_TABLES') . '" />';

if ( empty( $columns[2])) {
if ( !empty( $ext_type) && in_array( $ext_type, array( 'template', 'language'))) {}

else if ( empty( $columns[5]) && empty( $columns[4])) {
$result = '<span class="editlinktip hasDynTip"'
. ' title="' . JText::_( 'TOOLS_VIEW_TABLES_UNDEFINED_IN_JMS') .'"'
. '>X</span>';
$result = '<img src="components/com_multisites/images/missing.png" title="' . JText::_( 'TOOLS_VIEW_TABLES_UNDEFINED_IN_JMS') . '" />';
$result = '<span class="editlinktip hasDynTip"'
. ' title="' . JText::_( 'TOOLS_VIEW_TABLES_UNDEFINED_IN_JMS') .'"'
. '><img src="components/com_multisites/images/missing.png" title="Undefined extension" /></span>';
}

else if ( !empty( $columns[4])) {
$result = '+';
$result = '<img src="components/com_multisites/images/plus.png" title="' . JText::_( 'TOOLS_VIEW_INSTALL_SHARE_TABLES') . '" />';
}
}

else {
$model = & $this->getModel();
$viewCount = 0;
$tableCount = 0;
$noneCount = 0; 
$result = '';

if ( !empty( $columns[5])) {


foreach( $columns[5] as $xmltable) {
$tablepattern = $xmltable->attributes( 'name');
if ( $tablepattern == '[none]') {
$noneCount++;
}
else {
$tables = $model->getTableUsingPattern( $tablepattern);
if ( !empty( $tables)) {
foreach( $tables as $table) {
if ( $table->_isView) {
$viewCount++;
$viewFrom = $table->_viewFrom;

if ( $tableCount > 0) {
break;
}
}
else {
$tableCount++;

if ( $viewCount > 0) {
break;
}
}
} 
}
}
} 
}

if ( !empty( $columns[4])) {



}
if ( $tableCount > 0) {
$result .= '<img src="components/com_multisites/images/table.png" title="' . JText::_( 'TOOLS_VIEW_SPECIFIC_TABLES') . '" />';
}
if ( $viewCount > 0) {
$str = '';
if ( !empty( $viewFrom)) {
$str = " from $viewFrom";
}
$result .= '<img src="components/com_multisites/images/view.png" title="' . JText::_( 'TOOLS_VIEW_SHARED_TABLES') . $str . '" />';
}
if ( empty( $result)) {
if ( $noneCount > 0) {
$result .= '<img src="components/com_multisites/images/tocgreen.png" title="' . JText::_( 'TOOLS_VIEW_NO_TABLES') . '" />';
}
else if ( !empty( $ext_type) && in_array( $ext_type, array( 'template', 'language')) ) {
$result .= '<img src="components/com_multisites/images/notable.png" title="' . JText::_( 'TOOLS_VIEW_NO_TABLES') . '" />';
}
else {
$result .= '<img src="components/com_multisites/images/tocred.png" title="' . JText::_( 'TOOLS_VIEW_NO_TABLES') . '" />';
}
}
} 
return $result;
}


function _getComponentAction( $isTemplate, $option, $columns, $fieldname, $rowNbr, $forceInstall=false)
{
$o = array();

if ( empty( $columns[2])) {
$o[] = '<OPTION value="[unselected]">&nbsp;</OPTION>';
$className = '';

if ( $isTemplate) {
if ( !empty( $columns[5]) || $forceInstall) {
$label = JText::_( "install from template");
$labelMaster = JText::_( "install from master");
$className = 'class="install"';
}
else {
$label = JText::_( "define from template");
$labelMaster = JText::_( "define from master");
$className = 'class="define"';
}

if ( !empty( $columns[1])) {
$o[] = '<OPTION value="table.template|'.$option.'"'.$className.'>'.$label.'</OPTION>';
}

if ( !empty( $columns[4])) {

if ( !empty( $columns[1])) {
$o[] = '<OPTION value="share.template|'.$option.'" class="share">'.JText::_( "Share from template").'</OPTION>';
}
}
$o[] = '<OPTION value="table.master|'.$option.'"'.$className.'>'.$labelMaster.'</OPTION>';

if ( !empty( $columns[4])) {
$o[] = '<OPTION value="share.master|'.$option.'" class="share">'.JText::_( "Share from master").'</OPTION>';
}
}

else {
if ( !empty( $columns[5]) || $forceInstall) {
$label = JText::_( "install extension");
$className = ' class="install"';
}
else {
$label = JText::_( "define extension");
$className = ' class="define"';
}
$o[] = '<OPTION value="table.master|'.$option.'"'.$className.'>'.$label.'</OPTION>';

if ( !empty( $columns[4])) {
$o[] = '<OPTION value="share.master|'.$option.'" class="share">'.JText::_( "Share installation").'</OPTION>';
}
}
}

else {
$o[] = '<OPTION value="[unselected]">&nbsp;</OPTION>';
$o[] = '<OPTION value="uninstall|'.$option.'">'.JText::_( "un-install").'</OPTION>';
}
$onChange = '';

$list = '<select name="'. $fieldname.'[]"'
. ' id="'. $fieldname.'_'.$rowNbr .'"'
. ' class="actionlist"'
. ' size="1"'
. $onChange .'>'
. implode( "\n", $o)
. '</select>';
return $list;
}
} 

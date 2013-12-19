<?php
// file: helper.php.
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




class MultisitesHelper
{


static function isSymbolicLinks()
{
static $instance;

if (!isset( $instance )) {

if ( false && J2WinUtility::isOSWindows()) {
$instance = false;
}

else if ( !function_exists( 'symlink')) {


$instance = false;
}

else {
jimport( 'joomla.filesystem.path');
jimport( 'joomla.filesystem.folder');
jimport( 'joomla.filesystem.file');

$instance = false;

$app =& JFactory::getApplication();

$link = uniqid('symlink');
$sav_dir = getcwd();
$tmp_dir = $app->getCfg('tmp_path');


if ( !JFolder::exists( $tmp_dir) || !is_writable( $tmp_dir)) {

$tmp_dir = JPATH_ROOT.DS.'tmp';
}

if ( JFolder::exists( $tmp_dir) && is_writable( $tmp_dir))
{
chdir( $tmp_dir);

$log_dir = $app->getCfg( 'log_path');

if ( !JFolder::exists( $log_dir)) {

$log_dir = JPATH_ROOT.DS.'logs';
}


if ( !JFolder::exists( $log_dir)) {

$tmp_fname = JPath::clean( $tmp_dir .DS. uniqid('symlink_file') . '.txt');
$fp = fopen( $tmp_fname, "w");
if ( !empty( $fp)) {
fputs( $fp, 'this file can be deleted');
fclose( $fp);
$log_dir = $tmp_fname;
}
}

if ( JFolder::exists( $log_dir) || JFile::exists( $log_dir)) {

if ( function_exists( 'symlink') && @symlink( $log_dir, $link)) {

$fullname = $tmp_dir .DS. $link;

if ( JFolder::exists( $fullname) || JFile::exists( $fullname)) {
$instance = true;
if ( J2WinUtility::isOSWindows()) {


if ( JFolder::exists( $fullname)) {
@rmdir( $fullname);
}

if ( JFile::exists( $fullname)) {
@unlink( $fullname);
}
}

if ( JFolder::exists( $fullname) || JFile::exists( $fullname)) {

JFile::delete( $fullname);
}
}
}

if ( isset( $tmp_fname)) {

JFile::delete( $tmp_fname);
}
}

chdir( $sav_dir);
}
}
}
return $instance;
}


function getSiteNameList( &$sites, $filter_sitename)
{
$rows = array();
if ( isset( $sites)) {
foreach( $sites as $site) {
$rows[ $site->sitename] = $site;
}
ksort( $rows);
}
$opt = array();
$opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Select site').' -');
foreach( $rows as $site) {
$opt[] = JHTML::_('select.option', $site->id, $site->sitename);
}
$list = JHTML::_( 'select.genericlist', $opt, 'filter_sitename',
'class="inputbox" size="1" onchange="document.adminForm.submit( );"',
'value', 'text',
"$filter_sitename");
return $list;
}


function getDBServerList( &$sites, $filter_host)
{
$rows = array();
if ( isset( $sites)) {
foreach( $sites as $site) {
$host = trim( $site->host);
if ( !empty( $host)) {
$rows[ $host] = $host;
}
}
ksort( $rows);
}
$opt = array();
$opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Select server').' -');
foreach( $rows as $host) {
$opt[] = JHTML::_('select.option', $host, JText::_( $host));
}
$list = JHTML::_( 'select.genericlist', $opt, 'filter_host',
'class="inputbox" size="1" onchange="document.adminForm.submit( );"',
'value', 'text',
"$filter_host");
return $list;
}


function getDBNameList( &$sites, $filter_dbname)
{
$rows = array();
if ( isset( $sites)) {
foreach( $sites as $site) {
$dbname = trim( $site->db);
if ( !empty( $dbname)) {
$rows[ $dbname] = $dbname;
}
}
ksort( $rows);
}
$opt = array();
$opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Select db').' -');
foreach( $rows as $dbname) {
$opt[] = JHTML::_('select.option', $dbname, $dbname);
}
$list = JHTML::_( 'select.genericlist', $opt, 'filter_db',
'class="inputbox" size="1" onchange="document.adminForm.submit( );"',
'value', 'text',
"$filter_dbname");
return $list;
}


function getSiteIdsList( &$sites, $filter_site_ids,
$fieldname='filter_site_ids',
$unselectedTitle='Template Site',
$onchange=' onchange="getUserList(this.options[selectedIndex].value);"')
{
$rows = array();
if ( isset( $sites)) {
foreach( $sites as $site) {

if ( isset( $site->db) && isset( $site->dbprefix)
&& !empty( $site->db) && !empty( $site->dbprefix)
)
{
$value = $site->id
. ' ( ' . $site->db
. ', ' . $site->dbprefix
. ' )'
;
$rows[ $site->id] = $value;
}
}
ksort( $rows);
}
$opt = array();
if ( !empty( $unselectedTitle)) {
$opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_($unselectedTitle).' -');
}

$db =& JFactory::getDBO();
$dbname =& JFactory::getApplication()->getCfg( 'db');
$str = ' ( ' . $dbname . ', ' . $db->getPrefix() . ' )';
$opt[] = JHTML::_('select.option', ':master_db:', '< Master Site >' . $str);
foreach( $rows as $key => $value) {
$opt[] = JHTML::_('select.option', $key, $value);
}
$list = JHTML::_( 'select.genericlist', $opt, $fieldname,
'class="inputbox" size="1"'.$onchange,
'value', 'text',
"$filter_site_ids");
return $list;
}


function getTemplatesList( &$templates, &$selected_value, $front_end=false, $filter_GroupName=null, $instance_suffix='')
{

$filter_GroupName = (array) $filter_GroupName;

$firstOpt = false;
if ( empty( $selected_value)) {

$firstOpt = true;
}
$rows = array();
if ( isset( $templates)) {
foreach( $templates as $id => $template) {
$groupName = !empty( $template['groupName']) ? $template['groupName'] : '';
$title = !empty( $template['title']) ? $template['title'] : '';
$fromSiteID = !empty( $template['fromSiteID']) ? $template['fromSiteID'] : '';
$toSiteID = !empty( $template['toSiteID']) ? $template['toSiteID'] : '';
$fromDB = !empty( $template['fromDB']) ? $template['fromDB'] : '';
$fromPrefix = !empty( $template['fromPrefix']) ? $template['fromPrefix'] : '';
$toPrefix = !empty( $template['toPrefix']) ? $template['toPrefix'] : '';
$toDomains = !empty( $template['toDomains']) ? $template['toDomains'] : '';

if ( empty( $filter_GroupName)) {
if ( $front_end) {

if ( empty( $toSiteID) || empty( $toDomains)) {

}
else {
$rows[ $id] = "$title";
}
}
else {
$rows[ $id] = "$id ( $fromDB : $fromPrefix => $toPrefix )";
}
}
else if ( in_array( $groupName, $filter_GroupName)) {
if ( $front_end) {

if ( empty( $toSiteID) || empty( $toDomains)) {

}
else {
$rows[ $id] = "$title";
}
}
else {
$rows[ $id] = "$id ( $fromDB : $fromPrefix => $toPrefix )";
}
}
}
ksort( $rows);
}

if ( $front_end && empty( $rows)) {

return '';
}
$db =& JFactory::getDBO();
$dbPrefix = $db->getPrefix();
$opt = array();
if ( $front_end) {

if ( $firstOpt) {
foreach( $rows as $key => $value) {
$selected_value = $key;
break;
}
}
}
else {
$opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Fresh slave site').' -');
$opt[] = JHTML::_('select.option', ':master_db:', '- '.JText::_('Master DB'). " ( prefix='$dbPrefix' ) -");
}
if ( !empty( $rows)) {
foreach( $rows as $key => $value) {
$opt[] = JHTML::_('select.option', $key, $value);
}
}
if ( empty( $opt)) {

return '';
}
$list = JHTML::_( 'select.genericlist', $opt, 'fromTemplateID',
'class="inputbox" size="1" onchange="refreshTemplateDir(this.options[selectedIndex].value, \''.$instance_suffix.'\');"',
'value', 'text',
"$selected_value");
return $list;
}


function getSitesUsersList( &$sites, $filter_users)
{
$rows = array();
if ( isset( $sites)) {
foreach( $sites as $site) {

if ( isset( $site->db) && isset( $site->dbprefix)
&& !empty( $site->db) && !empty( $site->dbprefix)
)
{
$db =& Jms2WinFactory::getSlaveDBO( $site->id);
$query = "SELECT id, username FROM #__users ORDER BY username";
$db->setQuery( $query );
$users = $db->loadObjectList();
foreach( $users as $user) {
$key = $site->id.'|'.$user->id;
$value = $site->id . ' / ' . $user->username;
$rows[ $key] = $value;
}
}
}
ksort( $rows);
}

if ( count($rows) <= 0) {

return '';
}
$opt = array();
$opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Sites and Users').' -');
foreach( $rows as $key => $value) {
$opt[] = JHTML::_('select.option', $key, $value);
}
$list = JHTML::_( 'select.genericlist', $opt, 'filter_users',
'class="inputbox" size="1"',
'value', 'text',
"$filter_users");
return $list;
}


function getUsersList( $site_id, $selected_value = '[unselected]')
{
$rows = array();
require_once( JPATH_COMPONENT .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
if ( $site_id == ':master_db:') {
$db =& Jms2WinFactory::getMasterDBO();
}
else {
$db =& Jms2WinFactory::getSlaveDBO( $site_id);
}
if ( empty( $db)) {
return '';
}
$query = "SELECT id, username FROM #__users ORDER BY username";
$db->setQuery( $query );
$users = $db->loadObjectList();
if ( empty( $users)) {
return '';
}
foreach( $users as $user) {
$key = $user->id;
$value = $user->username;
$rows[ $key] = $value;
}
asort( $rows);

if ( count($rows) <= 0) {

return '';
}
$opt = array();
$opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Users').' -');
foreach( $rows as $key => $value) {
$opt[] = JHTML::_('select.option', $key, $value);
}
$list = JHTML::_( 'select.genericlist', $opt, 'adminUserID',
'class="inputbox" size="1"',
'value', 'text',
"$selected_value");
if ( !empty( $list)) {
$list .= JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_ADMIN_USER_TTIPS'));
}
return $list;
}


function getOwnerList( $selected_value = '[unselected]', $fieldname = 'owner_id')
{
$rows = array();
require_once( JPATH_COMPONENT .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
$db =& JFactory::getDBO();
if ( empty( $db)) {
return '';
}
$query = "SELECT id, username FROM #__users ORDER BY username";
$db->setQuery( $query );
$users = $db->loadObjectList();
foreach( $users as $user) {
$key = $user->id;
$value = $user->username;
$rows[ $key] = $value;
}
asort( $rows);

if ( count($rows) <= 0) {

return '';
}
$opt = array();
$opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Owners').' -');
foreach( $rows as $key => $value) {
$opt[] = JHTML::_('select.option', $key, $value);
}
$list = JHTML::_( 'select.genericlist', $opt, $fieldname,
'class="inputbox" size="1"',
'value', 'text',
"$selected_value");
return $list;
}


function getSiteOwnerList( $sites, $selected_value, $title='Select owner')
{
$db =& JFactory::getDBO();
$opt = array();
$opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_( $title).' -');
$owner_ids = array();
if ( !empty( $sites) && is_array( $sites)) {
foreach( $sites as $site) {
if ( !empty( $site->owner_id)) {
$owner_ids[$site->owner_id] = $site->owner_id;
}
}
foreach( $owner_ids as $owner_id) {
$query = 'SELECT name FROM #__users WHERE id=' . $owner_id
. ' LIMIT 1';
$db->setQuery( $query );
$user_name = $db->loadResult();
if ( !empty( $user_name)) {
$opt[] = JHTML::_('select.option', $owner_id, $user_name);
}
}
}
$list = JHTML::_( 'select.genericlist', $opt, 'filter_owner_id',
'class="inputbox" size="1" onchange="document.adminForm.submit( );"',
'value', 'text',
"$selected_value");
return $list;
}


function getActionsList( $field_name, $filename, $symbolicLink, $source_fieldname)
{
jimport( 'joomla.utilities.utility.php');
if ( empty( $symbolicLink['action'])) {
$selected_value = '[unselected]';
}
else {
$selected_value = $symbolicLink['action'];
}

if ( $filename == 'installation') {

if ( !MultisitesHelper::isSymbolicLinks()) {

$rows = array( 'ignore' => JText::_( 'TEMPLATE_ACTION_IGNORE'),
'copy' => JText::_( 'TEMPLATE_ACTION_COPY'),
'unzip' => JText::_( 'TEMPLATE_ACTION_UNZIP')
);
}
else {
$rows = array( 'dirlinks'=> JText::_( 'TEMPLATE_ACTION_DIRLINKS'),
'ignore' => JText::_( 'TEMPLATE_ACTION_IGNORE'),
'copy' => JText::_( 'TEMPLATE_ACTION_COPY'),
'unzip' => JText::_( 'TEMPLATE_ACTION_UNZIP')
);
}
}
else if ( $filename == 'images'
|| $filename == 'templates') {
$rows = array( 'special' => JText::_( 'TEMPLATE_ACTION_SPECIAL'),
'copy' => JText::_( 'TEMPLATE_ACTION_COPY'),
'ignore' => JText::_( 'TEMPLATE_ACTION_IGNORE'),
'unzip' => JText::_( 'TEMPLATE_ACTION_UNZIP')
);
}
else if ( $filename == ':limited:') {

if ( !MultisitesHelper::isSymbolicLinks()) {

$rows = array( 'ignore' => JText::_( 'TEMPLATE_ACTION_IGNORE'),
'copy' => JText::_( 'TEMPLATE_ACTION_COPY'),
'special' => JText::_( 'TEMPLATE_ACTION_SPECIAL'),
'unzip' => JText::_( 'TEMPLATE_ACTION_UNZIP')
);
}
else {
$rows = array( 'SL' => JText::_( 'TEMPLATE_ACTION_SL'),
'dirlinks'=> JText::_( 'TEMPLATE_ACTION_DIRLINKS'),
'ignore' => JText::_( 'TEMPLATE_ACTION_IGNORE'),
'copy' => JText::_( 'TEMPLATE_ACTION_COPY'),
'special' => JText::_( 'TEMPLATE_ACTION_SPECIAL'),
'unzip' => JText::_( 'TEMPLATE_ACTION_UNZIP')
);
}
}

else {

if ( !MultisitesHelper::isSymbolicLinks()) {

$rows = array( 'ignore' => JText::_( 'TEMPLATE_ACTION_IGNORE'),
'copy' => JText::_( 'TEMPLATE_ACTION_COPY'),
'unzip' => JText::_( 'TEMPLATE_ACTION_UNZIP'),
'mkdir' => JText::_( 'TEMPLATE_ACTION_MKDIR')
);
}
else {
$rows = array( 'SL' => JText::_( 'TEMPLATE_ACTION_SL'),
'ignore' => JText::_( 'TEMPLATE_ACTION_IGNORE'),
'copy' => JText::_( 'TEMPLATE_ACTION_COPY'),
'unzip' => JText::_( 'TEMPLATE_ACTION_UNZIP'),
'mkdir' => JText::_( 'TEMPLATE_ACTION_MKDIR'),
'dirlinks'=> JText::_( 'TEMPLATE_ACTION_DIRLINKS')
);
}
}

if ( $filename == 'htaccess.txt' || $filename == '.htaccess') {
$rows['rewrite'] = JText::_( 'TEMPLATE_ACTION_REWRITEBASE');
}
$opt = array();

foreach( $rows as $key => $value) {
$opt[] = JHTML::_('select.option', $key, $value);
}

if ( version_compare( JVERSION, '1.6') >= 0) {
$list = JHTML::_( 'select.genericlist', $opt, "$field_name",
array( 'list.attr' => 'class="inputbox" size="1" onchange="enableSource(this.options[selectedIndex].value,\'' . $source_fieldname . '\');"',
'option.key' => 'value',
'option.text' => 'text',
'list.select' => "$selected_value",
'option.text.toHtml' => false
)
);
}
else {
$list = JHTML::_( 'select.genericlist', $opt, "$field_name",
'class="inputbox" size="1" onchange="enableSource(this.options[selectedIndex].value,\'' . $source_fieldname . '\');"',
'value', 'text',
"$selected_value");
}
return $list;
}


function getDomainList( $field)
{
jimport( 'joomla.environment.uri' );
$result = array();
$lines = preg_split( "#[ ,\n]#", $_REQUEST[ $field]);
foreach( $lines as $line)
{
$str = trim($line);
if ( strlen($str)>0)
{

$s = strtolower( $str);
if ( (strncmp( $s, 'http://', 7) == 0)
|| (strncmp( $s, 'https://', 8) == 0)
) {}
else {
$str = 'http://' . $str;
}
$uri = new JURI( $str);
$host = $uri->getHost();
if ( empty( $host)) {
$result[] = $str;
}
else {
$url = $uri->toString( array('scheme', 'user', 'pass', 'host', 'port', 'path'));

while ( substr( $url, -1) == '/') {
$url = substr( $url, 0, strlen( $url)-1);
}
$result[] = $url;
}
}
}
return $result;
}


function getFilterActionsCombo( $nbrows)
{
$default_value = 'show';

$opt = array();
$opt[] = JHTML::_('select.option', 'show', JText::_( 'Show all'));
$opt[] = JHTML::_('select.option', 'hide', JText::_( 'Hide ignored'));
$list = JHTML::_( 'select.genericlist', $opt, "filter_actions",
'class="inputbox" size="1" onchange="filterActions(this.options[selectedIndex].value, ' . $nbrows . ');"',
'value', 'text',
"$default_value");
return $list;
}


function tooltipsKeywords()
{
jimport( 'joomla.utilities.utility.php');
$deploy_dir = '';
if ( MultisitesHelper::isSymbolicLinks()) {
$deploy_dir = '<li>' . JText::_( 'TEMPLATE_KW_DEPLOY_DIR') . '</li>';
}
$title = JText::_( 'TEMPLATE_KW')
.'::';
$tooltip = '<ul>'
. '<li>' . JText::_( 'TEMPLATE_KW_USER_LOGIN') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_USER_NAME') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_USER_ID') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_USER_NAME') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_USER_LOGIN') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_USER_EMAIL') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_USER_EMAIL_LEFT') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_USER_EMAIL_LEFT_ALNUM') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_USER_EMAIL_RIGHT') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_USER_EMAIL_RIGHT_N') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_USER_PSW') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_NEW_ADMIN_EMAIL') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_NEW_ADMIN_EMAIL_LEFT') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_NEW_ADMIN_EMAIL_LEFT_ALNUM') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_NEW_ADMIN_EMAIL_RIGHT') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_NEW_ADMIN_EMAIL_RIGHT_N') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ID') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ID_UCFIRST') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ID_UPPERCASE') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ID_N') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ID_N_PLUS') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ID_LETTERS') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ID_LEFT') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ID_LEFT_ALNUM') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ID_RIGHT') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ID_RIGHT_N') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ALIAS') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ALIAS_LEFT') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ALIAS_LEFT_ALNUM') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ALIAS_RIGHT') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_ALIAS_RIGHT_N') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_ROOT') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_MULTISITES') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_DIR') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_REL_SITE_DIR') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_DOMAIN') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_URL') . '</li>'
. $deploy_dir
. '<li>' . JText::_( 'TEMPLATE_KW_SITE_PREFIX') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_RND_PSW_6_TO_10') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_RND_PSW') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_RND_ALNUM_6_TO_10') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_RND_ALNUM') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_RND_WORD_6_TO_10') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_RND_WORD') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_RND_PREFIX_6_TO_10') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_RND_PREFIX') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_AUTOINC_OFFSET') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_AUTOINC') . '</li>'
. '<li>' . JText::_( 'TEMPLATE_KW_RESET') . '</li>'
. '</ul>';
$style = 'style="text-decoration: none; color: #333;"';
$image = JURI::root(true).'/administrator/components/com_multisites/images/idea.png';
$text = '<img src="'. $image .'" border="0" alt="'. JText::_( 'Tooltip' ) .'"/>';
$tip = '<span class="editlinktip hasTip" title="'.$title.$tooltip.'" '. $style .'>'. $text .'</span>';
return $tip;
}


function getValidityUnits( $field_name='validity_unit', $selected_value = '[unselected]')
{

$rows = array( 'days' => JText::_( 'days'),
'months' => JText::_( 'months'),
'years' => JText::_( 'years')
);
$opt = array();
foreach( $rows as $key => $value) {
$opt[] = JHTML::_('select.option', $key, $value);
}
$list = JHTML::_( 'select.genericlist', $opt, "$field_name",
'class="inputbox" size="1"',
'value', 'text',
"$selected_value");
return $list;
}


function getAllStatusList( $field_name='status', $selected_value = '[unselected]', $facultative=false, $filter=false)
{

$rows = array( 'Confirmed' => JText::_( 'Confirmed'),
'Pending' => JText::_( 'Pending'),
'Cancelled' => JText::_( 'Cancelled'),
'Refunded' => JText::_( 'Refunded')
);
$opt = array();
if ( $facultative) {
$opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Status').' -');
}
foreach( $rows as $key => $value) {
$opt[] = JHTML::_('select.option', $key, $value);
}
$onchange = '';
if ( $filter) {
$onchange = ' onchange="document.adminForm.submit( );"';
}
$list = JHTML::_( 'select.genericlist', $opt, "$field_name",
'class="inputbox" size="1"' . $onchange,
'value', 'text',
"$selected_value");
return $list;
}


function getOwnerName( $owner_id)
{
if (empty( $owner_id)) {
return '';
}
$db =& JFactory::getDBO();
$query = 'SELECT name FROM #__users WHERE id=' . (int)$owner_id;
$db->setQuery( $query );
$result = $db->loadResult();
return $result;
}


function getTemplateAdminName( $template)
{
if ( empty( $template) || empty( $template->adminUserID) || $template->adminUserID<=0) {
return '';
}
$db =& Jms2WinFactory::getMultiSitesDBO( $template->fromSiteID);
if ( empty( $db)) {
return '';
}
$query = 'SELECT name, username FROM #__users WHERE id=' . (int)$template->adminUserID;
$db->setQuery( $query );
$row = $db->loadObject();
$result = '';
if ( !empty( $row)) {
$result = $row->name . ' (' . $row->username . ')';
}
return $result;
}


function getRadioYesNoDefault( $field_name='yesnodefault', $selected_value = '[unselected]', $onchange_action='', $defaultOption = true)
{
if ( !isset( $selected_value)) { $selected_value = '[unselected]'; }
else if ( is_bool( $selected_value)) {

$selected_value = $selected_value ? '1' : '0';
}

else if ( is_string( $selected_value) && strlen( $selected_value)<=0) {
$selected_value = '[unselected]';
}

$rows = array( '1' => JText::_( 'Yes'),
'0' => JText::_( 'No')
);
if ( $defaultOption) {
$rows['[unselected]'] = JText::_( 'Default');
}
$opt = array();
foreach( $rows as $key => $value) {
$opt[] = JHTML::_('select.option', $key, $value);
}
$onchange = '';
if ( !empty( $onchange_action)) {
$onchange = ' onchange="' . $onchange_action . '"';
}
$list = JHTML::_( 'select.radiolist', $opt, "$field_name",
'class="inputbox"' . $onchange,
'value', 'text',
"$selected_value",
"$field_name");
return $list;
}
static function addSubmenu($vName)
{

if ( version_compare( JVERSION, '1.6') >= 0) {}

else {

return;
}


$option = JRequest::getCmd('option');
$db =& JFactory::getDBO();
$query = "SELECT c.title, c.link, c.alias FROM #__menu as p"
. " LEFT JOIN #__menu as c ON c.parent_id=p.id AND p.component_id = c.component_id"
. " LEFT JOIN #__extensions as x ON x.extension_id=p.component_id"
. " WHERE p.level = 1 AND x.type='component' AND x.element LIKE '$option'"
. " ORDER BY c.id"
;
$db->setQuery( $query );
$rows = $db->loadObjectList();
if ( empty( $rows)) {
return;
}

$lang = JFactory::getLanguage();
$lang->load($option.'.sys', JPATH_ADMINISTRATOR, null, false, false)
|| $lang->load($option.'.sys', JPATH_ADMINISTRATOR.'/components/'.$option, null, false, false)
|| $lang->load($option.'.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
|| $lang->load($option.'.sys', JPATH_ADMINISTRATOR.'/components/'.$option, $lang->getDefault(), false, false);
foreach ($rows as $row) {

$pos = strpos( $row->link, '?');
if ( $pos === false) {
$param_url = $row->link;
}
else {
$param_url = substr( $row->link, $pos+1);
}
$params_array = explode( '&', $param_url);
foreach( $params_array as $param) {
$keyvalues = explode( '=', $param);
if ( $keyvalues[0] == 'task') {
$menuTask = $keyvalues[1];
}
}

if ( empty( $menuTask)) {
$menuTask = $row->alias;
}
JSubMenuHelper::addEntry(
JText::_( strtoupper( $row->title)),
$row->link,
$vName == $menuTask
);
}
}


function getContinentsIdsList( $selected_value = '[unselected]', $control_name = 'continent_ids',
$multiple='multiple', $size='4', $onchange='', $class = "inputbox", $maximizeSize=40, $frame_id='continents_frame')
{
$rows = array( 'AF'=>'Africa',
'AN'=>'Antartica',
'AS'=>'Asia',
'EU'=>'Europe',
'NA'=>'Nord America',
'OC'=>'Australia',
'SA'=>'South America'
);

if ( empty($rows)) {

return '';
}
asort( $rows);
$opt = array();

foreach( $rows as $key => $value) {
$opt[] = JHTML::_('select.option', $key, JText::_( $value));
}
if ( !empty( $onchange)) { $onchange = ' onchange="' . $onchange .'"'; }
else { $onchange = ''; }
if ( !empty( $multiple)) {
$multiple = ' multiple="' . $multiple .'"';
$control_multiple = '[]';
}
else {
$multiple = '';
$control_multiple = '';
}
if ( !empty( $size)) { $size = ' size="' . $size .'"'; }
else { $size = ''; }

if ( substr( $control_name, -2) == '[]') {
$select_name = $control_name;
}

else {
$select_name = $control_name.$control_multiple;
}
$list = JHTML::_( 'select.genericlist', $opt, $select_name,
'class="'.$class.'"' .$multiple .$size . $onchange,
'value', 'text',
$selected_value, $control_name );
if ( !empty( $maximizeSize)) {
$list = '<div id="'.$control_name.'_toggle" class="maximize"><a href="javascript://" onclick="toggleSelectListSize(\''.$control_name.'\', '.$maximizeSize.', \''.$frame_id.'\'); ">'
. '<span class="show">'.JText::_( 'Maximize').'</span>'
. '<span class="hide" style="display:none;">'.JText::_( 'Minimize').'</span>'
. '</a></div>'
. $list
;
}
return $list;
}


function getCountriesIdsList( $rows, $selected_value = '[unselected]', $control_name = 'countries_ids',
$multiple='multiple', $size='4', $onchange='', $class = "inputbox", $maximizeSize=40, $frame_id='countries_frame')
{

if ( empty($rows)) {

return '';
}
asort( $rows);
$opt = array();

foreach( $rows as $key => $value) {
$opt[] = JHTML::_('select.option', $key, $value);
}
if ( !empty( $onchange)) { $onchange = ' onchange="' . $onchange .'"'; }
else { $onchange = ''; }
if ( !empty( $multiple)) {
$multiple = ' multiple="' . $multiple .'"';
$control_multiple = '[]';
}
else {
$multiple = '';
$control_multiple = '';
}
if ( !empty( $size)) { $size = ' size="' . $size .'"'; }
else { $size = ''; }

if ( substr( $control_name, -2) == '[]') {
$select_name = $control_name;
}

else {
$select_name = $control_name.$control_multiple;
}
$list = JHTML::_( 'select.genericlist', $opt, $select_name,
'class="'.$class.'"' .$multiple .$size . $onchange,
'value', 'text',
$selected_value, $control_name );
if ( !empty( $maximizeSize)) {
$list = '<div id="'.$control_name.'_toggle" class="maximize"><a href="javascript://" onclick="toggleSelectListSize(\''.$control_name.'\', '.$maximizeSize.', \''.$frame_id.'\'); ">'
. '<span class="show">'.JText::_( 'Maximize').'</span>'
. '<span class="hide" style="display:none;">'.JText::_( 'Minimize').'</span>'
. '</a></div>'
. $list
;
}
return $list;
}


function getBrowserTypesList( $selected_value = '[unselected]', $control_name = 'browser_types',
$multiple='multiple', $size='4', $onchange='', $class = "inputbox", $maximizeSize=40, $frame_id='browser_types_frame')
{
@include( dirname( dirname( __FILE__)).DS.'data'.DS.'browser_types.cfg.php');

if ( empty( $browser_types)) {

return '';
}
$rows = $browser_types;
asort( $rows);
$opt = array();

foreach( $rows as $key => $value) {
if ( is_array( $value) && !empty( $value['label'])) {
$value = $value['label'];
}
$opt[] = JHTML::_('select.option', $key, $value);
}
if ( !empty( $onchange)) { $onchange = ' onchange="' . $onchange .'"'; }
else { $onchange = ''; }
if ( !empty( $multiple)) {
$multiple = ' multiple="' . $multiple .'"';
$control_multiple = '[]';
}
else {
$multiple = '';
$control_multiple = '';
}
if ( !empty( $size)) { $size = ' size="' . $size .'"'; }
else { $size = ''; }

if ( substr( $control_name, -2) == '[]') {
$select_name = $control_name;
}

else {
$select_name = $control_name.$control_multiple;
}
$list = JHTML::_( 'select.genericlist', $opt, $select_name,
'class="'.$class.'"' .$multiple .$size . $onchange,
'value', 'text',
$selected_value, $control_name );
if ( !empty( $maximizeSize)) {
$list = '<div id="'.$control_name.'_toggle" class="maximize"><a href="javascript://" onclick="toggleSelectListSize(\''.$control_name.'\', '.$maximizeSize.', \''.$frame_id.'\'); ">'
. '<span class="show">'.JText::_( 'Maximize').'</span>'
. '<span class="hide" style="display:none;">'.JText::_( 'Minimize').'</span>'
. '</a></div>'
. $list
;
}
return $list;
}


function getJoomlaTemplateList_j15( $fromSite, $site=null)
{

if ( !empty( $site) && !empty( $site->deploy_dir) && JFolder::exists( $site->deploy_dir.DS.'templates')) {
$template_dir = $site->deploy_dir.DS.'templates';
}

else if ( !empty( $fromSite)) {
if ( !empty( $fromSite->templates_dir)) { $template_dir = $fromSite->templates_dir; }
else if ( !empty( $fromSite->deploy_dir)) { $template_dir = $fromSite->deploy_dir.DS.'templates'; }
}

else if ( !empty( $site)) {
if ( !empty( $site->templates_dir)) { $template_dir = $site->templates_dir; }
else if ( !empty( $site->deploy_dir)) { $template_dir = $site->deploy_dir.DS.'templates'; }
}
if ( empty( $template_dir)) {
$template_dir = JPATH_ROOT.DS.'templates';
}
$rows = JFolder::files( $template_dir, 'templateDetails.xml', true, true);
$results = array();
if ( !empty( $rows)) {
foreach( $rows as $row) {
$results[] = basename( dirname( $row));
}
}
return $results;
}


function getJoomlaTemplateList_j16( $fromSite, $site=null)
{

if ( !empty( $site->id) && !empty( $site->db)) {
$fromSiteID = $site->id;
}

else if ( !empty( $fromSite)) {
$fromSiteID = $fromSite->id;
}
else if ( !empty( $site)) {
$fromSiteID = $site->id;
}

if ( empty( $fromSiteID)) {

$fromSiteID = ':master_db:';
}
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'
.DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
$db =& Jms2WinFactory::getMultisitesDBO( $fromSiteID);
if ( empty( $db)) {
return array();
}
$query = 'SELECT * from #__template_styles ORDER BY template';
$db->setQuery($query);
$rows = $db->loadObjectList();
$results = array();
foreach( $rows as $row) {
$results[] = $row->template;
}
return $results;
}


function getJoomlaTemplateList( $fromSite, $selected_value = '[unselected]',
$site=null,
$control_name = 'setDefaultTemplate',
$multiple='', $size='1', $onchange='', $class = "inputbox")
{
if ( version_compare( JVERSION, '1.6') >= 0) { $rows = MultisitesHelper::getJoomlaTemplateList_j16( $fromSite, $site); }
else { $rows = MultisitesHelper::getJoomlaTemplateList_j15( $fromSite, $site); }

if ( empty($rows)) {

return '';
}
asort( $rows);
$opt = array();
$opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Select a template').' -');
foreach( $rows as $row) {
$opt[] = JHTML::_('select.option', $row, $row);
}
if ( !empty( $onchange)) { $onchange = ' onchange="' . $onchange .'"'; }
else { $onchange = ''; }
if ( !empty( $multiple)) {
$multiple = ' multiple="' . $multiple .'"';
$control_multiple = '[]';
}
else {
$multiple = '';
$control_multiple = '';
}
if ( !empty( $size)) { $size = ' size="' . $size .'"'; }
else { $size = ''; }

if ( substr( $control_name, -2) == '[]') {
$select_name = $control_name;
}

else {
$select_name = $control_name.$control_multiple;
}
$list = JHTML::_( 'select.genericlist', $opt, $select_name,
'class="'.$class.'"' .$multiple .$size . $onchange,
'value', 'text',
$selected_value, $control_name );
return $list;
}


function getMenuItemsList( $fromSiteID, $selected_value = '[unselected]',
$site=null,
$control_name = 'setDefaultMenu',
$multiple='', $size='1', $onchange='', $class = "inputbox")
{

if ( !empty( $site->id) && !empty( $site->db)) { $fromSiteID = $site->id; }

else if ( !empty( $fromSiteID)) {}


else if ( !empty( $site)) { $fromSiteID = $site->id; }
else {
return '';
}
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'
.DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
$db =& Jms2WinFactory::getMultisitesDBO( $fromSiteID);
if ( empty( $db)) {
return '';
}

if ( version_compare( JVERSION, '1.6') >= 0) {
$query = 'SELECT * from #__menu WHERE client_id=0 AND parent_id>0 ORDER BY title';
}

else {
$query = 'SELECT * from #__menu ORDER BY name';
}
$db->setQuery($query);
$rows = $db->loadObjectList();

if ( empty($rows)) {

return '';
}
asort( $rows);
$opt = array();
$opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Select a menu item').' -');
foreach( $rows as $row) {
$menu_title = !empty( $row->name) ? $row->name : ''; 
$menu_title .= !empty( $row->title) ? $row->title : ''; 
$opt[] = JHTML::_('select.option', $row->id, $menu_title . ' ('.$row->id.')');
}
if ( !empty( $onchange)) { $onchange = ' onchange="' . $onchange .'"'; }
else { $onchange = ''; }
if ( !empty( $multiple)) {
$multiple = ' multiple="' . $multiple .'"';
$control_multiple = '[]';
}
else {
$multiple = '';
$control_multiple = '';
}
if ( !empty( $size)) { $size = ' size="' . $size .'"'; }
else { $size = ''; }

if ( substr( $control_name, -2) == '[]') {
$select_name = $control_name;
}

else {
$select_name = $control_name.$control_multiple;
}
$list = JHTML::_( 'select.genericlist', $opt, $select_name,
'class="'.$class.'"' .$multiple .$size . $onchange,
'value', 'text',
$selected_value, $control_name );
return $list;
}


function getJLanguageList_j15( $fromSite, $site=null)
{

if ( !empty( $site) && !empty( $site->deploy_dir) && JFolder::exists( $site->deploy_dir.DS.'language')) {
$lang_dir = $site->deploy_dir.DS.'language';
}

else if ( !empty( $fromSite)) {
if ( !empty( $fromSite->deploy_dir)) { $lang_dir = $fromSite->deploy_dir.DS.'language'; }
}
else if ( !empty( $site)) {
if ( !empty( $site->deploy_dir)) { $lang_dir = $site->deploy_dir.DS.'language'; }
}

if ( empty( $lang_dir) || !JFolder::exists( $lang_dir)) {
$lang_dir = JPATH_ROOT.DS.'language';
}
$rows = JFolder::files( $lang_dir, '.*\.com_banners\.ini', true, true);

$results = array();
foreach( $rows as $row) {
$results[] = basename( dirname( $row));
}
return $results;
}


function getJLanguageList_j16( $fromSite, $site=null)
{

if ( !empty( $site->id) && !empty( $site->db)) {
$fromSiteID = $site->id;
}

else if ( !empty( $fromSite)) {
$fromSiteID = $fromSite->id;
}
else if ( !empty( $site)) {
$fromSiteID = $site->id;
}

if ( empty( $fromSiteID)) {

$fromSiteID = ':master_db:';
}
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'
.DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
$db =& Jms2WinFactory::getMultisitesDBO( $fromSiteID);
if ( empty( $db)) {
return array();
}
$query = 'SELECT lang_code from #__languages ORDER BY lang_code';
$db->setQuery($query);
$rows = $db->loadObjectList();

$results = array();
foreach( $rows as $row) {
$results[] = $row->lang_code;
}
return $results;
}


function getJLanguageList( $fromSite, $selected_value = '[unselected]',
$site=null,
$control_name = 'setDefaultJLang',
$multiple='', $size='1', $onchange='', $class = "inputbox")
{
if ( version_compare( JVERSION, '1.6') >= 0) { $rows = MultisitesHelper::getJLanguageList_j16( $fromSite, $site); }
else { $rows = MultisitesHelper::getJLanguageList_j15( $fromSite, $site); }

if ( empty($rows)) {

return '';
}
asort( $rows);
$opt = array();
$opt[] = JHTML::_('select.option', '[unselected]', '- '.JText::_('Select a language').' -');
foreach( $rows as $row) {
$opt[] = JHTML::_('select.option', $row, $row);
}
if ( !empty( $onchange)) { $onchange = ' onchange="' . $onchange .'"'; }
else { $onchange = ''; }
if ( !empty( $multiple)) {
$multiple = ' multiple="' . $multiple .'"';
$control_multiple = '[]';
}
else {
$multiple = '';
$control_multiple = '';
}
if ( !empty( $size)) { $size = ' size="' . $size .'"'; }
else { $size = ''; }

if ( substr( $control_name, -2) == '[]') {
$select_name = $control_name;
}

else {
$select_name = $control_name.$control_multiple;
}
$list = JHTML::_( 'select.genericlist', $opt, $select_name,
'class="'.$class.'"' .$multiple .$size . $onchange,
'value', 'text',
$selected_value, $control_name );
return $list;
}
} 

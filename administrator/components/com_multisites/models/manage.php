<?php
// file: manage.php.
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
jimport( 'joomla.filesystem.path');
jimport( 'joomla.filesystem.archive');
jimport( 'joomla.filesystem.folder');
jimport( 'joomla.filesystem.file');
jimport( 'joomla.utility.string');
if ( !defined( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR')) {
define( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR',
JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'legacy.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'lettertree.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'site.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'multisitesdb.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'utils.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'tld2win.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'helpers' .DS. 'helper.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'patches' .DS. 'patch_plugins.php');

if ( !defined( 'JPATH_MULTISITES')) {
define( 'MUTLISITES_PATCHES_NOTINSTALLED', true);


@include_once( dirname( dirname( __FILE__)) .DS. 'multisites_path.cfg.php');

if ( !defined( 'JPATH_MULTISITES')) {
define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}
}
if ( !defined( 'MULTISITES_DIR_RIGHTS')) {
define( 'MULTISITES_DIR_RIGHTS', 0755);
}


if ( !defined( 'MULTISITES_REDIRECT_FTP')) {
define( 'MULTISITES_REDIRECT_FTP', false);
}
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'application' .DS. 'component' .DS. 'model2win.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'filesystem' .DS. 'jms2winfolder.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'filesystem' .DS. 'jms2winfile.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'filesystem' .DS. 'jms2winpath.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'client' .DS. 'jms2winftp.php');

if ( JFile::exists( dirname( __FILE__).DS.'manage_variant.php')) { include_once( dirname( __FILE__).'/manage_variant.php'); }
if ( !class_exists( 'MultisitesModelManageVariant')) {

class MultisitesModelManageVariant extends JModel2Win {
function canCreateSlave( $enteredvalues, $front_end = false) { return !$front_end; }
function _deleteWebsiteID() { return true; }
function _getWebsiteID( $enteredvalues) { return 0; }
function getCookieDomains( $master_domain, $site_dependencies, &$sites, $i, $aHost, $master_userTablename = null) { return ''; }
function &_getTemplate( $enteredvalues) { static $instance = null; return $instance; }
}
}




class MultisitesModelManage extends MultisitesModelManageVariant
{

var $_modelName = 'manage';
var $_cache_sites = null;
var $_site = null;
var $_countAll = 0;


function _getSites_Recursive( &$filters, $path, &$rows, $is_multisites_root, $site_id_prefix='')
{

$dir = JPath::clean($path);


if ( !is_dir($dir) || (is_link( $dir) && !$is_multisites_root)) {

return false;
}
if ($handle = opendir( $dir)) {
while (false !== ($file = readdir($handle))) {
if ($file != "." && $file != "..") {
$filename = $dir . DS. $file;

if ( is_link( $filename)) {}

else if ( is_dir( $filename)) {

$site_id = $site_id_prefix . $file;
if ( Site::is_Site( '', $filename)) {

$site = new Site();
$site->load( $site_id);

$selected = true;
if ( !empty($filters['host']) && $site->host != $filters['host']) {
$selected = false;
}
if ( !empty( $filters['db']) && $site->db != $filters['db']) {
$selected = false;
}
if ( !empty( $filters['dbprefix']) && $site->dbprefix != $filters['dbprefix']) {
$selected = false;
}
if ( !empty( $filters['status']) && $site->status != $filters['status']) {
$selected = false;
}
if ( !empty( $filters['owner_id']) && $site->owner_id != $filters['owner_id']) {
$selected = false;
}

if ( !empty( $filters['parentSiteId']) && (empty( $site->parentSiteId) || $site->parentSiteId != $filters['parentSiteId'])) {
$selected = false;
}
if ( !empty( $filters['groupName'])) {
$template = & $site->getTemplate();



if ( empty( $template) || !isset( $template->groupName) || $template->groupName != $filters['groupName']) {
$selected = false;
}
}

if ( !empty( $filters['parentSiteId']) && ( empty( $site->parentSiteId) || $site->parentSiteId != $filters['parentSiteId']) ) {
$selected = false;
}

if ( !empty( $filters['search'])) {
$search = $filters['search'];
$posFile = strpos( JString::strtolower( $site_id), $search);
$posSitename = strpos( JString::strtolower( $site->sitename), $search);
$posDomains = strpos( JString::strtolower( implode( '|', $site->domains)), $search);
$posIndexDomains = strpos( JString::strtolower( implode( '|', $site->indexDomains)), $search);
$posHost = strpos( JString::strtolower( $site->host), $search);
$posDB = strpos( JString::strtolower( $site->db), $search);
$posDbPrefix = strpos( JString::strtolower( $site->dbprefix), $search);
if ( $posFile === false
&& $posSitename === false
&& $posSitename === false
&& $posIndexDomains === false
&& $posHost === false
&& $posDB === false
&& $posDbPrefix === false
)
{
$selected = false;
}
}
if ( $selected) {
$rows[] = $site;
}
} 

if ( defined( 'MULTISITES_LETTER_TREE') && MULTISITES_LETTER_TREE) {
$len = strlen( $file);

if ( $len == 1) {
$this->_getSites_Recursive( $filters, $filename, $rows, false, $site_id);
}

else if ( $len >1 ) {

if ( strpos( $file, '.') === false) {}
else {
$this->_getSites_Recursive( $filters, $filename, $rows, false, $site_id);
}
}
}
}
}
}
closedir($handle);
}
}


function &getAllSites( $cleanFilter=array(), $forceRefresh=false)
{

if ( is_null( $this->_cache_sites) || $forceRefresh) {
$this->_cache_sites = array();
$dir = JPATH_MULTISITES;
if ( JFolder::exists( $dir))
{
$this->_getSites_Recursive( $cleanFilter, $dir, $this->_cache_sites, true);
}
}
return $this->_cache_sites;
}


function &getSites( $forceRefresh=false)
{
$filters = $this->getState( 'filters');
$cleanFilter = array();

if ( !is_null($filters)) {

if ( !empty($filters['host']) && $filters['host'] != '[unselected]') {
$cleanFilter['host'] = $filters['host'];
}

if ( !empty( $filters['db']) && $filters['db'] != '[unselected]') {
$cleanFilter['db'] = $filters['db'];
}

if ( !empty( $filters['dbprefix']) && $filters['dbprefix'] != '[unselected]') {
$cleanFilter['dbprefix'] = $filters['dbprefix'];
}

if ( !empty( $filters['status']) && $filters['status'] != '[unselected]') {
$cleanFilter['status'] = $filters['status'];
}

if ( !empty( $filters['owner_id']) && $filters['owner_id'] != '[unselected]') {
$cleanFilter['owner_id'] = $filters['owner_id'];
}

if ( !empty( $filters['groupName']) && $filters['groupName'] != '[unselected]') {
$cleanFilter['groupName'] = $filters['groupName'];
}

if ( !empty( $filters['parentSiteId']) && $filters['parentSiteId'] != '[unselected]') {
$cleanFilter['parentSiteId'] = $filters['parentSiteId'];
}

if ( !empty( $filters['search'])) {
$cleanFilter['search'] = JString::strtolower( $filters['search']);
}
}

$rows = $this->getAllSites( $cleanFilter, $forceRefresh);
$this->_countAll = count( $rows);

if ( !empty($filters) && empty( $filters['order'])) {
$filters['order'] = 'id';
$filters['order_Dir'] = 'asc';
}

if ( !is_null($filters)) {
if ( !empty( $filters['order'])) {
$colname = $filters['order'];
$sortedrows = array();
$i = 0;
foreach( $rows as $row){

if ( $colname == 'expiration') {

$expiration = strtotime( $row->$colname);
$expiration_str = strftime( '%Y-%m-%d', $expiration);
$key = $expiration_str . '.' . substr( "00".strval($i++), -3);
}
else {
$key = $row->$colname . '.' . substr( "00".strval($i++), -3);
}
$sortedrows[$key] = $row;
}

if ( !empty( $filters['order_Dir']) && $filters['order_Dir'] =='desc') {
krsort($sortedrows);
$rows = $sortedrows;
}

else {
ksort($sortedrows);
$rows = $sortedrows;
}
}
}

if ( !is_null($filters)) {

if ( !empty( $filters['limit']) && $filters['limit'] > 0) {

$rows = array_slice( $rows, $filters['limitstart'], $filters['limit'] );
}
}
return $rows;
}


function getCountAll()
{
return $this->_countAll;
}

function setFilters( &$filters)
{
$this->setState( 'filters', $filters);
}

function removeFilters()
{
$this->setState( 'filters', null);
}


function createMasterIndex()
{
jimport( 'joomla.environment.uri' );
if ( JFile::exists( dirname( __FILE__).DS.'manage_geoloc.php')) { include_once( dirname( __FILE__).DS.'manage_geoloc.php'); }
if ( JFile::exists( dirname( __FILE__).DS.'manage_browser.php')) { include_once( dirname( __FILE__).DS.'manage_browser.php'); }

$master_domain = '';
$master_userTablename = null;
$site_dependencies = array();
if ( !defined( 'MULTISITES_ID')) {

$uri = JFactory::getURI();
$master_domain = $uri->getHost();
$db = & Jms2WinFactory::getMasterDBO( true);
if ( !empty( $db)) {
$path = array( $db->_dbname, $db->getPrefix().'users');
$path = MultisitesDatabase::backquote( $path);
$master_userTablename = implode( '.', $path);
$site_dependencies[$master_userTablename][] = -1;
}
}

$sites = $this->getSites( true);



for( $i=0; $i<count( $sites); $i++) {
$thisUserTablename = $sites[$i]->getThisUserTablename();
if ( !empty($thisUserTablename)) {
$site_dependencies[$thisUserTablename][] = $i;
}
}

for( $i=0; $i<count( $sites); $i++) {
$fromUserTablename = $sites[$i]->getFromUserTablename();
if ( !empty( $fromUserTablename)) {
$site_dependencies[$fromUserTablename][] = $i;
}
}
$md_hostalias = array();
for( $i=0; $i< count( $sites); $i++) {
$site = & $sites[$i];



if ( empty( $site->status)) {}
else if ($site->status == 'Confirmed') {

if ( $site->isExpired() && empty( $site->expireurl)) {

continue;
}

}
else {

continue;
}

if ( !empty( $site->indexDomains)) {
$domains = $site->indexDomains;
}
else {

$domains = $site->domains;
}

$redirecturl = '';
foreach( $domains as $domain) {

$s = strtolower( $domain);
if ( (strncmp( $s, 'http://', 7) == 0)
|| (strncmp( $s, 'https://', 8) == 0)
) {}
else {
$domain = 'http://' . $domain;
}
$uri = new JURI( $domain);
$myHost = $uri->getHost();
if ( empty( $myHost)) {
$parts = explode( '/', $domain);
$myHost = $parts[0];
if ( !empty( $myHost)) {
$uri->setHost( $myHost);
}
}

if ( !empty( $myHost)) {

$url = $uri->toString( array('scheme', 'user', 'pass', 'host', 'port', 'path'));

$url = rtrim( $url, '/');

if ( !empty( $url)) {

$host = strtolower( $myHost);
if ( !isset( $md_hostalias[$host])) {
$md_hostalias[$host] = array();
}
$cookie_domains = MultisitesModelManage::getCookieDomains( $master_domain, $site_dependencies, $sites, $i, $host);
$site_detail = array( 'url' => $url, 'site_id' => $site->id);
if ( !empty( $site->expiration)) { $site_detail['expiration'] = $site->expiration; }
if ( !empty( $site->expireurl)) { $site_detail['expireurl'] = $site->expireurl; }
if ( !empty( $cookie_domains)) { $site_detail['cookie_domains'] = $cookie_domains; }
if ( !empty( $site->site_dir) && $site->site_dir != JPATH_MULTISITES .DS. $site->id) {
$site_detail['site_dir'] = $site->site_dir;
}

if ( empty( $redirecturl)) {

if ( !empty( $site->redirect1st)) {

$redirecturl = $url;
}
}
else {

$site_detail['redirecturl'] = $redirecturl;
}
if ( function_exists('geoloc_CreateMasterIndex')) { geoloc_CreateMasterIndex( $site_detail, $site); }
if ( function_exists('browser_CreateMasterIndex')) { browser_CreateMasterIndex( $site_detail, $site); }
$md_hostalias[$host][] = $site_detail;
}
}
} 
} 


foreach( $md_hostalias as $key =>$hostalias) {
$sortedDomain = array();
$i = 0;
foreach( $hostalias as $domains) {

$lenDom = substr( '000' . strlen( $domains['url']), -3);
$keyDom = $lenDom
. $domains['url'] . '_' . substr( '0000'.$i, -4);
$sortedDomain[$keyDom] = $domains;
$i++;
}
ksort( $sortedDomain, SORT_STRING);

$md_hostalias[$key] = array_values( $sortedDomain);
}
$master_root_path = JPATH_ROOT;

if ( defined( 'MULTISITES_ID') && defined( 'MULTISITES_MASTER_ROOT_PATH' )) {





if ( JFile::exists( MULTISITES_MASTER_ROOT_PATH .DS. 'configuration.php')) {
$master_root_path = MULTISITES_MASTER_ROOT_PATH;
}
}

$master_cookie_domains = MultisitesModelManage::getCookieDomains( $master_domain, $site_dependencies, $sites, -1, $master_domain, $master_userTablename);

$config = "<?php\n";
$config .= "if( !defined( '_EDWIN2WIN_' ) && !defined( '_JEXEC' )) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); \n\n";
$config .= "if( !defined( 'MULTISITES_MASTER_ROOT_PATH' )) {\n"
. "   define( 'MULTISITES_MASTER_ROOT_PATH', '" . $master_root_path . "');\n"
. "}\n\n";
if ( !empty( $master_cookie_domains)) {
$config .= "if( !defined( 'MULTISITES_MASTER_COOKIE_DOMAINS' )) {\n"
. "   define( 'MULTISITES_MASTER_COOKIE_DOMAINS', '" . implode( '|', $master_cookie_domains) . "');\n"
. "}\n\n";
}


if ( defined( 'MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_COUNTRY'))
{
$config .= "if( !defined( 'MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_COUNTRY_CFG' )) {\n"
. "   define( 'MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_COUNTRY_CFG', '".MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_COUNTRY."');\n"
. "}\n\n";
}

if ( defined( 'MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_CITY'))
{
$config .= "if( !defined( 'MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_CITY_CFG' )) {\n"
. "   define( 'MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_CITY_CFG', '".MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_CITY."');\n"
. "}\n\n";
}

if ( defined( 'MULTISITES_GEOIP_MAXMIND_ICC_ENABLED') && MULTISITES_GEOIP_MAXMIND_ICC_ENABLED
&& JFile::exists( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'geoip.dat')
)
{
$config .= "if( !defined( 'MULTISITES_GEOIP_MAXMIND_ICC_CFG' )) {\n"
. "   define( 'MULTISITES_GEOIP_MAXMIND_ICC_CFG', true);\n"
. "}\n\n";
}

if ( defined( 'MULTISITES_GEOIP_MAXMIND_CITY_ENABLED') && MULTISITES_GEOIP_MAXMIND_CITY_ENABLED
&& JFile::exists( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'geolitecity.dat')
)
{
$config .= "if( !defined( 'MULTISITES_GEOIP_MAXMIND_CITY_CFG' )) {\n"
. "   define( 'MULTISITES_GEOIP_MAXMIND_CITY_CFG', true);\n"
. "}\n\n";
}


if ( defined( 'MULTISITES_GEOIP_QUOVA_APIKEY'))
{
$config .= "if( !defined( 'MULTISITES_GEOIP_QUOVA_APIKEY_CFG' )) {\n"
. "   define( 'MULTISITES_GEOIP_QUOVA_APIKEY_CFG', '".MULTISITES_GEOIP_QUOVA_APIKEY."');\n"
. "}\n\n";
}

if ( defined( 'MULTISITES_GEOIP_QUOVA_SECRET'))
{
$config .= "if( !defined( 'MULTISITES_GEOIP_QUOVA_SECRET_CFG' )) {\n"
. "   define( 'MULTISITES_GEOIP_QUOVA_SECRET_CFG', '".MULTISITES_GEOIP_QUOVA_SECRET."');\n"
. "}\n\n";
}

if ( defined( 'MULTISITES_GEOIP_LOGFILE'))
{
$config .= "if( !defined( 'MULTISITES_GEOIP_LOGFILE_CFG' )) {\n"
. "   define( 'MULTISITES_GEOIP_LOGFILE_CFG', '".addslashes( MULTISITES_GEOIP_LOGFILE)."');\n"
. "}\n\n";
}

if ( defined( 'MULTISITES_BROWSER_LOGFILE'))
{
$config .= "if( !defined( 'MULTISITES_BROWSER_LOGFILE_CFG' )) {\n"
. "   define( 'MULTISITES_BROWSER_LOGFILE_CFG', '".addslashes( MULTISITES_BROWSER_LOGFILE)."');\n"
. "}\n\n";
}
$config .= "\$md_hostalias = array( ";
$sep='';
foreach( $md_hostalias as $key => $domains) {
$config .= $sep . "'$key' => array( ";
if ( count( $domains) > 1) {
$sep2 = "\n                            ";
}
else {
$sep2 = '';
}

for( $i=count($domains)-1; $i>=0; $i--) {
$site = $domains[$i];
$domain = $site['url'];
$site_id = $site['site_id'];
$site_dir_str = '';
if ( !empty( $site['site_dir'])) {
$site_dir_str = ", 'site_dir' => '" . $site['site_dir'] ."'";
}
$additionalParams = '';
foreach( $site as $key => $value) {

if ( in_array( $key, array( 'url', 'site_id', 'site_dir', 'redirecturl', 'expiration', 'cookie_domains'))) {}
else {
if ( !empty( $value)) {
if ( is_array( $value)) { $additionalParams .= ", '".$key."' => array( ".MultisitesUtils::CnvArray2Str( '', $value).')'; }
else {
$value = trim( $value);
if ( !empty( $value)) { $additionalParams .= ", '".$key."' => '".addslashes($value)."'"; }
}
}
}
}
if ( !empty( $site['redirecturl'])) {
$redirecturl = $site['redirecturl'];
$config .= $sep2 . "array( 'url' => '$domain', 'site_id' => '$site_id'$site_dir_str$additionalParams, 'redirecturl' => '$redirecturl')" ;
}
else if ( !empty( $site['expiration'])) {
$expiration = $site['expiration'];
if ( !empty( $site['expireurl'])) { $expireurl_str = ", 'expireurl' => '" . $site['expireurl'] . "'"; }
else { $expireurl_str = ''; }
if ( !empty( $site['cookie_domains'])) {
$cookie_domains = $site['cookie_domains'];
$config .= $sep2 . "array( 'url' => '$domain', 'site_id' => '$site_id'$site_dir_str$additionalParams, 'expiration' => '$expiration' $expireurl_str, 'cookie_domains' => array( " . MultisitesUtils::CnvArray2Str( '', $cookie_domains) . ") )" ;
}
else {
$config .= $sep2 . "array( 'url' => '$domain', 'site_id' => '$site_id'$site_dir_str$additionalParams, 'expiration' => '$expiration' $expireurl_str)" ;
}
}
else {
if ( !empty( $site['cookie_domains'])) {
$cookie_domains = $site['cookie_domains'];
$config .= $sep2 . "array( 'url' => '$domain', 'site_id' => '$site_id'$site_dir_str$additionalParams, 'cookie_domains' => array( " . MultisitesUtils::CnvArray2Str( '', $cookie_domains) . ") )" ;
}
else {
$config .= $sep2 . "array( 'url' => '$domain', 'site_id' => '$site_id'$site_dir_str$additionalParams)" ;
}
}
$sep2 = ",\n                            ";
}
$config .= ')';
$sep = ",\n                       ";
}
$config .= ");\n";
$config .= "?>";

$filename = JPath::clean( JPATH_MULTISITES. '/config_multisites.php');
JFile::write( $filename, $config);

$filename = JPath::clean( JPATH_MULTISITES. '/index.html');
if ( !JFile::exists( $filename)) {

JFile::copy( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR.DS.'index.html',
JPATH_MULTISITES .DS. 'index.html');
}
}


function getCurrentRecord()
{
if ($this->_site == null) {
$this->_site = new Site();
$id = JRequest::getString('id', false);

$id = (string) preg_replace('/[^A-Z0-9_\.\-@]/i', '', $id);
$id = ltrim($id, '.');
if ( !empty( $id)) {
$this->_site->load($id);
}
}
return $this->_site;
}


function getNewRecord()
{
if ($this->_site == null) {
$this->_site = new Site();
}
return $this->_site;
}


function canDelete()
{
$site_dir = $this->getSiteDir();
if ( !JFolder::exists( $site_dir)) {

$site_dir = null;
$site_dir = $this->getSiteDir( '', true);
if ( !JFolder::exists( $site_dir)) {
$this->setError( JText::_( 'SITE_NOT_FOUND' ) );
return false;
}
}
return true;
}

function _deleteFolderLinks($path)
{

if ( ! $path ) {

JError::raiseWarning(500, 'MultisitesModelManage::_deleteFolderLinks: '.JText::_('Attempt to delete base directory') );
return false;
}

jimport('joomla.client.helper');
$FTPOptions = JClientHelper::getCredentials('ftp');

$path = JPath::clean($path);
 if (!is_dir($path)) {
JError::raiseWarning(21, 'MultisitesModelManage::_deleteFolderLinks: '.JText::_('Path is not a folder').' '.$path);
return false;
}

$files = JFolder::files($path, '.', false, true, array());
if (count($files)) {
jimport('joomla.filesystem.file');
if (JFile::delete($files) !== true) {

return false;
}
}
if ($FTPOptions['enabled'] == 1) {

jimport('joomla.client.ftp');
$ftp = & JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);
}

$folders = JFolder::folders($path, '.', false, true, array());
foreach ($folders as $folder) {
$checkDeleted = true;

if ( is_link( $folder)) {
$file = $folder;

if ( J2WinUtility::isOSWindows() && @rmdir( $file)) {

}
else if (@unlink($file)) {

} elseif ($FTPOptions['enabled'] == 1) {
$file = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');
if (!$ftp->delete($file)) {

return false;
}
} else {
$filename = basename($file);
JError::raiseWarning('SOME_ERROR_CODE', JText::_('Delete failed') . ": '$filename'");
return false;
}
}
else {


$foldername = basename( $folder);
if ( defined( 'MULTISITES_LETTER_TREE') && MULTISITES_LETTER_TREE) {
$len = strlen( $foldername);

if ( $len == 1) {
$checkDeleted = false;
}

else if ( $len <= 3) {

if ( strpos( $file, '.') === false) {
if (MultisitesModelManage::_deleteFolderLinks($folder) !== true) {

return false;
}
}
else {
$checkDeleted = false;
}
}

else if (MultisitesModelManage::_deleteFolderLinks($folder) !== true) {

return false;
}
}

else if (MultisitesModelManage::_deleteFolderLinks($folder) !== true) {

return false;
}
}
clearstatcache();

if ( $checkDeleted && file_exists( $folder)) {

$filename = basename($folder);
JError::raiseWarning('SOME_ERROR_CODE', JText::_('Delete failed') . ": '$filename'");
return false;
}
}



$files = JFolder::files($path, '.', false, true, array());
$folders = JFolder::folders($path, '.', false, true, array());
if ( count( $files) <= 0
&& count( $folders) <= 0) {


if (@rmdir($path)) {
$ret = true;
} elseif ($FTPOptions['enabled'] == 1) {

$path = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $path), '/');

$ret = $ftp->delete($path);
} else {
JError::raiseWarning('SOME_ERROR_CODE', 'JFolder::delete: '.JText::_('Could not delete folder').' '.$path);
$ret = false;
}
}
else {
$ret = true;
}
return $ret;
}


function _deleteDBTables()
{
$site_id = JRequest::getCmd('id');
$deleteDB = JRequest::getBool('deleteDB');
if ( !$deleteDB) {
return true;
}
$db =& Jms2WinFactory::getSlaveDBO( $site_id);
if ( empty( $db)) {
return false;
}
return MultisitesDatabase::deleteDBTables( $db);
}


function delete()
{
$err = array();
$rc = true;
JPluginHelper::importPlugin('multisites');
$mainframe = &JFactory::getApplication();
$site_id = JRequest::getCmd('id');
$results = $mainframe->triggerEvent('onBeforeDeleteSiteID', array ( $site_id));

if ( !$this->_deleteDBTables()) {
$err[] = JText::sprintf( 'SITE_DELETEDB_ERR');
}
$dir = getcwd();
$deploy_dir = $this->getDeployDir();
$alias_link = $this->getAliasLink();
$delete_dir = $this->getDeleteDir();
$site_dir = $this->getSiteDir();
$this->_deleteWebsiteID();


if ( !empty( $deploy_dir) && $deploy_dir != $site_dir) {

if ( strtolower( substr( dirname( __FILE__), 0, strlen( $deploy_dir))) == strtolower( $deploy_dir)) {
$err[] = JText::sprintf( 'SITE_DELETE_FORBIDEN', $deploy_dir);
}
else if ( JFolder::exists( $deploy_dir)) {

if ( !$this->_deleteFolderLinks( $deploy_dir)) {
$err[] = JText::sprintf( 'SITE_DELETE_ERR', $deploy_dir);
}
}
}

if ( JFolder::exists( $site_dir)) {

if ( !$this->_deleteFolderLinks( $site_dir)) {
$err[] = JText::sprintf( 'SITE_DELETE_ERR', $site_dir);
}
}
if ( !empty( $alias_link) && is_link( $alias_link)) {

if (@unlink($alias_link)) {

}

else {
$err[] = JText::sprintf( 'SITE_DELETE_ERR', $alias_link);
}
}

if ( !empty( $delete_dir)) {

if ( strtolower( substr( dirname( __FILE__), 0, strlen( $delete_dir))) == strtolower( $delete_dir)) {
$err[] = JText::sprintf( 'SITE_DELETE_FORBIDEN', $delete_dir);
}
else if ( JFolder::exists( $delete_dir)) {

if ( !$this->_deleteFolderLinks( $delete_dir)) {
$err[] = JText::sprintf( 'SITE_DELETE_ERR', $delete_dir);
}
}
}
chdir( $dir);
if ( !empty( $err)) {
$this->setError( implode( '</li><li>', $err));
return false;
}
return true;
}


var $_site_dir = null;
function &getSiteDir( $id='', $force_flat_dir = false)
{

if ( $this->_site_dir == null) {
if ( $id == '') {
$id = JRequest::getCmd('id');
}

if ( defined( 'MULTISITES_LETTER_TREE') && MULTISITES_LETTER_TREE && !$force_flat_dir) {

$id_path = MultisitesLetterTree::getLetterTreeDir( $id);
$this->_site_dir = JPath::clean( JPATH_MULTISITES .DS. $id_path);
}

else {
$this->_site_dir = JPath::clean( JPATH_MULTISITES .DS. $id);
}
}
return $this->_site_dir;
}


var $_deploy_dir = null;
function &getDeployDir( $id='')
{
if ( $this->_deploy_dir == null) {
if ( $id == '') {
$id = JRequest::getCmd('id');
}
$this->_deploy_dir = '';
$site_dir =& $this->getSiteDir( $id);
$filename = $site_dir.DS.'config_multisites.php';
@include($filename);
if ( isset( $config_dirs) && !empty( $config_dirs)) {
if ( !empty( $config_dirs['deploy_dir'])) {
$this->_deploy_dir = JPath::clean( $config_dirs['deploy_dir']);
}
}
}
return $this->_deploy_dir;
}


var $_alias_link = null;
function &getAliasLink( $id='')
{
if ( $this->_alias_link == null) {
if ( $id == '') {
$id = JRequest::getCmd('id');
}
$this->_alias_link = '';
$site_dir =& $this->getSiteDir( $id);
$filename = $site_dir.DS.'config_multisites.php';
@include($filename);
if ( !empty( $config_dirs) && !empty( $config_dirs['alias_link'])) {
$this->_alias_link = JPath::clean( $config_dirs['alias_link']);
}
}
return $this->_alias_link;
}


var $_delete_dir = null;
function &getDeleteDir( $id='')
{
if ( $this->_delete_dir == null) {
if ( $id == '') {
$id = JRequest::getCmd('id');
}
$this->_delete_dir = '';
$site_dir =& $this->getSiteDir( $id);
$filename = $site_dir.DS.'config_multisites.php';
@include($filename);
if ( isset( $config_dirs) && !empty( $config_dirs)) {
if ( !empty( $config_dirs['delete_dir'])) {
$this->_delete_dir = JPath::clean( $config_dirs['delete_dir']);
}
}
}
return $this->_delete_dir;
}


function compute_default_links()
{
$site_links = array();
$master_dir = JPATH_ROOT;

foreach( JFolder::folders( $master_dir, '.', false, false, array('.svn', 'CVS', 'vssver.scc',
'cache', 
'images', 
'installation', 
'logs', 
'templates', 
'tmp' 
)) as $folder)
{
$site_links[$folder] = array( 'action' => 'SL');
}

$site_links['cache'] = array( 'action' => 'mkdir', 'readOnly' => true);
$site_links['logs'] = array( 'action' => 'mkdir', 'readOnly' => true);
$site_links['images'] = array( 'action' => 'special');
$site_links['templates'] = array( 'action' => 'copy');
$site_links['tmp'] = array( 'action' => 'mkdir', 'readOnly' => true);
if ( JFolder::exists( $master_dir .DS. 'installation')) {
$site_links['installation'] = array( 'action' => 'dirlinks');
}
ksort( $site_links);

foreach( JFolder::files( $master_dir, '.', false, false, array('.svn', 'CVS', 'vssver.scc',
'CHANGELOG.php',
'configuration.php',
'configuration.php-dist',
'index.php',
'index2.php',
'COPYRIGHT.php',
'CREDITS.php',
'INSTALL.php',
'LICENSE.php',
'LICENSES.php'
),
array()) as $file)
{
$site_links[$file] = array( 'action' => 'SL');
}
$site_links['index.php'] = array( 'action' => 'redirect', 'readOnly' => true);
$site_links['index2.php'] = array( 'action' => 'redirect', 'readOnly' => true);
return $site_links;
}


function symlink( $target_path, $link)
{

if ( !MultisitesHelper::isSymbolicLinks()) {
return false;
}

if ( is_link( $link)) {

$cur_path = readlink( $link);
if ( $cur_path === false) {


$full_path = getcwd() .DS. $link;
$cur_path = readlink( $full_path);
if ( $cur_path === false) {
return false;
}
}
if ( $cur_path == $target_path) {
return true;
}
return false;
}
if ( !function_exists( 'symlink')) {
return false;
}
return symlink( $target_path, $link);
}


function _getSourcePath( $targetname, $sourcename, $site_id, $site_dir, $deploy_dir, $dbInfo)
{

if ( empty( $sourcename)) {

$source = JPath::clean( JPATH_ROOT .DS. $targetname);
}
else {
$str = MultisitesDatabase::evalStr( $sourcename, $site_id, $site_dir, $deploy_dir, $dbInfo);

$c = substr( $str, 0, 1);
jimport( 'joomla.utilities.utility.php');
if ( $c == '\\' || $c == '/'
|| (J2WinUtility::isOSWindows() && substr( $str, 1, 1) == ':')) {
$source = JPath::clean( $str);
}

else if ( in_array( substr( $str, 0, 3), array( '../', '..\\'))) {
$source = JPath::clean( $str);
}

else {

$source = JPath::clean( JPATH_ROOT .DS. $str);
}
}

$filename = basename( $targetname);
$source = str_replace( '{filename}', $filename, $source);
return $source;
}


function _getTargetPath( $site_dir, $deploy_dir, $name)
{
if ( !empty( $deploy_dir)) {
$target = JPath::clean( $deploy_dir .DS. $name);
}
else {
$target = JPath::clean( $site_dir .DS. $name);
}
return $target;
}


function _deployLinks( $config_dirs, $site_id, $site_dir, $deploy_dir, $dbInfo, $domains, $indexDomains)
{

$errors = array();

if ( isset( $config_dirs['symboliclinks']) && !empty( $config_dirs['symboliclinks'])) {
$site_links = $config_dirs['symboliclinks'];
}
else {

if ( empty( $config_dirs['deploy_dir'])) {

return $errors;
}

$site_links = $this->compute_default_links();
}
$shell_script = "#!/bin/bash\n";
$sav_dir = getcwd();
if ( isset( $site_links)) {

if ( version_compare( JVERSION, '1.6') >= 0) {

if ( empty( $site_links['defines.php'])) {

$site_links['defines.php'] = array( 'action' => 'special');
}
}
foreach( $site_links as $name => $site_link) {
$action = $site_link['action'];

if ( $action == 'rewrite')
{


$targetDomain = '';
if ( !empty( $indexDomains)) {
$targetDomain = $indexDomains[0];
}
if ( empty( $targetDomain) && !empty( $domains)) {
$targetDomain = $domains[0];
}
if ( empty( $targetDomain)) {
$action == 'copy';
}
else {

$pos = strpos( $targetDomain, '://');
if ( $pos === false) {
$rewriteBase = $targetDomain;
}
else {
$rewriteBase = substr( $targetDomain, $pos+3);
}

$pos = strpos( $rewriteBase, '/');
if ( $pos === false) {}
else {
$rewriteBase = substr( $rewriteBase, $pos);
}

$rewriteBase = rtrim( $rewriteBase, '/');

if ( empty( $rewriteBase)) {
$rewriteBase = '/';
}

$srcfile = !empty( $site_link['file'])
? $site_link['file']
: null;
$source = $this->_getSourcePath( $name, $srcfile, $site_id, $site_dir, $deploy_dir, $dbInfo);
$content = JFile::read( $source);

$content = preg_replace( "/RewriteBase [^\n]+/",
"RewriteBase ".$rewriteBase,
$content );

$target = $this->_getTargetPath( $site_dir, $deploy_dir, $name);
JFile::write( $target, $content);
}
}

if ( $action == 'mkdir')
{
if ( empty( $deploy_dir)) {
$dir = JPath::clean( $site_dir .DS. $name);
$shell_script .= "cd $site_dir\n"
. "mkdir -p $name\n"
;
}
else {
$dir = JPath::clean( $deploy_dir .DS. $name);
$shell_script .= "cd $deploy_dir\n"
. "mkdir -p $name\n"
;
}

if ( !JFolder::exists( $dir)) {

if ( ! JFolder::create( $dir, MULTISITES_DIR_RIGHTS)
|| ! JFolder::exists( $dir))
{
$errors[] = JText::sprintf( 'SITE_DEPLOY_MKDIR_ERR', $dir);
}
}
}

else if ( $action == 'copy')
{
$srcfile = !empty( $site_link['file'])
? $site_link['file']
: null;
$source = $this->_getSourcePath( $name, $srcfile, $site_id, $site_dir, $deploy_dir, $dbInfo);


$target = $this->_getTargetPath( $site_dir, $deploy_dir, $name);
$result = true;
if ( is_dir( $source)) {

if ( !JFolder::exists( $target)) {
$shell_script .= "cp $source $target\n";

$result = JFolder::copy( $source, $target);
}
}
else {

if ( !JFile::exists( $target)) {
$shell_script .= "cp $source $target\n";

$result = JFile::copy( $source, $target);
}
}
if ( $result === false ) {
$errors[] = JText::sprintf( 'SITE_DEPLOY_COPY_ERR', $source);


$action = 'SL';
}
}

else if ( $action == 'unzip')
{
$archivename = $this->_getSourcePath( $name, $site_link['file'], $site_id, $site_dir, $deploy_dir, $dbInfo);


$source = JPath::clean( $this->_getTargetPath( $site_dir, $deploy_dir, $name));
$arr = explode( DS, $source);
$link = $arr[count($arr)-1];
array_pop( $arr);
$source_dir = implode( DS, $arr);
chdir( $source_dir);
$dir = getcwd();
$shell_script .= "cd $dir\n"
. "rm -R $link\n"
. "cp $archivename _tmp.tar.gz\n"
. "gunzip _tmp.tar.gz\n"
. "tar -xvf _tmp.tar\n"
. "rm _tmp.tar\n"
;
$result = JArchive::extract( $archivename, $dir);
if ( $result === false ) {
$errors[] = JText::sprintf( 'SITE_DEPLOY_UNZIP_FILE_ERR', $archivename);


$action = 'SL';
}
}

else if ( $action == 'redirect')
{

$target_path = JPath::clean( JPATH_ROOT .DS. $name);

$filename = $this->_getTargetPath( $site_dir, $deploy_dir, $name);
$content = "<?php\n"
. "// Don't use a Symbolic Link because that crash the website.\n"
. "// Just include the original file to redirect the processing.\n"
. "//include( '$target_path');\n"
. "// Evaluate the original include file to redirect to keep the __FILE__ value.\n"
. "\$filename = '$target_path';\n"
. '$handle = fopen ($filename, "r");' . "\n"
. '$contents = fread ($handle, filesize ($filename));' . "\n"
. 'fclose ($handle);' . "\n"
. 'unset($handle);' . "\n"
. 'eval("?>" . $contents);' . "\n"
;
JFile::write( $filename, $content);
}

else if ( $action == 'special')
{

if ( !MultisitesHelper::isSymbolicLinks()) {


}

else {

$target = $this->_getTargetPath( $site_dir, $deploy_dir, $name);
if ( JFolder::exists( $target)) {

}

else {

$action = 'SL';
}
}
}

else if ( $action == 'dirlinks')
{

if ( !MultisitesHelper::isSymbolicLinks()) {


}

else {

$target = $this->_getTargetPath( $site_dir, $deploy_dir, $name);
if ( !JFolder::exists( $target)) {

if ( ! JFolder::create( $target)
|| ! JFolder::exists( $target))
{
$errors[] = JText::sprintf( 'SITE_DEPLOY_DIRLINK_ERR', $target);
}
}

if ( JFolder::exists( $target)) {
$source_dir = $this->_getSourcePath( $name, null, $site_id, $site_dir, $deploy_dir, $dbInfo);
if ( !$this->_deployDirLinks( $source_dir, $target, array( 'index.php', 'index2.php', 'index3.php'))) {
$errors[] = $this->getError();
}
}
}
}

if ( $action == 'SL')
{
$target_path = JPath::clean( JPATH_ROOT .DS. $name);

$source = JPath::clean( $this->_getTargetPath( $site_dir, $deploy_dir, $name));
$arr = explode( DS, $source);
$link = $arr[count($arr)-1];

array_pop( $arr);
$source_dir = implode( DS, $arr);
chdir( $source_dir);
$shell_script .= "cd $source_dir\n"
. "ln -s $target_path $link\n"
;

if ( !$this->symlink( $target_path, $link)) {
$errors[] = JText::sprintf( 'SITE_DEPLOY_SYMLINK_ERR', $link, $target_path);
}
}
} 
}

if ( !J2WinUtility::isOSWindows()) {
$filename = JPath::clean( $deploy_dir .DS. 'symbolic_links.sh');


}
chdir( $sav_dir);
return $errors;
}


function _createEmptyFolder( $dir)
{
if ( !Jms2WinFolder::exists( $dir)) {
Jms2WinFolder::create( $dir);

@chmod( $dir, MULTISITES_DIR_RIGHTS);
}
$index_php = $dir .DS. 'index.php';
$index_html = $dir .DS. 'index.html';
if ( !Jms2WinFile::exists( $index_php)
&& !Jms2WinFile::exists( $index_html)) {
$content = '<html><body bgcolor="#FFFFFF"></body></html>';
Jms2WinFile::write( $index_html, $content);
}
}


function _checkEmptyFolders( $site_dir)
{
$pathroot_len = strlen( JPATH_ROOT);
$path = $site_dir;
while( strlen( $path) > $pathroot_len) {
$this->_createEmptyFolder( $path);
$path = dirname( $path);
}

$index_php = $site_dir.DS.'index.php';
$index_html = $site_dir.DS.'index.html';
if ( JFile::exists( $index_php) && JFile::exists( $index_html)) {

$str = file_get_contents( $index_html);
if ( strlen( $str) < 50) {

JFile::delete( $index_html);
}
}
}


function _real_path( $path)
{
$result = realpath( $path);

if ( $result === false) {

$parts = preg_split('/\/|\\\\/', $path);
$n = count( $parts);
for ( $i=0; $i<$n; ) {
if ( $parts[$i] == '..') {
if ( $i>0 && $parts[$i-1] != '..') {

for ( $j=$i+1; $j<$n; $j++) {
$parts[$j-2]=$parts[$j];
}
array_pop($parts);
array_pop($parts);
$n = count( $parts);
$i--;
}
else {
$i++;
}
}
else {
$i++;
}
}
$result = implode( DS, $parts);
}
return $result;
}


function duplicateDBandConfig( $enteredvalues, $dbInfo, $site_id, $site_dir=null, $deploy_dir = null, $makeDB = true)
{




$sharedTables = array();
if ( empty( $site_dir)) {
$site_dir = JPATH_MULTISITES .DS. $site_id;
}

$template = null;
$sharedTables = array( 'table' => array());
if ( !empty( $dbInfo['fromTemplateID'])) {
if ( $dbInfo['fromTemplateID'] == '[unselected]'
|| $dbInfo['fromTemplateID'] == ':master_db:') {
$fromSiteID = ':master_db:';
}
else {
$template = new Jms2WinTemplate();
$template->load( $dbInfo['fromTemplateID']);
$fromSiteID = $template->fromSiteID;

if ( !empty( $template->dbsharing)) {
$dbsharing = new Jms2WinDBSharing();
if ( $dbsharing->load()) {
$sharedTables = $dbsharing->getSharedTables( $template->dbsharing);
}
}
}
$fromConfig =& Jms2WinFactory::getMultiSitesConfig( $fromSiteID);
}
else if ( isset( $dbInfo['fromSiteID'])) {
$fromSiteID = $dbInfo['fromSiteID'];
$fromConfig =& Jms2WinFactory::getMultiSitesConfig( $fromSiteID);
}
else {
$fromSiteID = null;
$fromConfig =& Jms2WinFactory::getMasterConfig();
}
if ( empty( $fromConfig)) {
return array( JText::sprintf( 'SITE_DEPLOY_CONFIG_NOT_FOUND', $fromSiteID));
}

$toConfig =& Jms2WinFactory::getMultiSitesConfig( $site_id);
if ( empty( $toConfig)) {

if ( !$makeDB) {

return array();
}

$toConfig = clone( $fromConfig);
}

if ( isset( $dbInfo['toSiteName'])) {
$sitename = htmlspecialchars( $dbInfo['toSiteName']);
$toConfig->setValue( 'config.sitename', $sitename);

if ( !empty( $enteredvalues['ignoreFromName'])) {}
else {
$toConfig->setValue( 'config.fromname', $sitename);
}
}

if ( isset( $dbInfo['toMetaDesc'])) {
$MetaDesc = htmlspecialchars( $dbInfo['toMetaDesc']);
$toConfig->setValue( 'config.MetaDesc', $MetaDesc);
}

if ( isset( $dbInfo['toMetaKeys'])) {
$MetaKeys = htmlspecialchars( $dbInfo['toMetaKeys']);
$toConfig->setValue( 'config.MetaKeys', $MetaKeys);
}

$toConfig->setValue( 'config.live_site', '');

if ( !empty( $deploy_dir))
{

$log_path = $deploy_dir .DS. 'logs';
$tmp_path = $deploy_dir .DS. 'tmp';
$real_log_path = $this->_real_path( $log_path);
if ( $real_log_path === false) {
$real_log_path = $log_path;
}
$real_tmp_path = $this->_real_path( $tmp_path);
if ( $real_tmp_path === false) {
$real_tmp_path = $tmp_path;
}
}
else {
$log_path = $site_dir .DS. 'logs';
$tmp_path = $site_dir .DS. 'tmp';
$real_log_path = $log_path;
$real_tmp_path = $tmp_path;
}
$toConfig->setValue( 'config.log_path', $real_log_path);
$this->_createEmptyFolder( $log_path);
$toConfig->setValue( 'config.tmp_path', $real_tmp_path);
$this->_createEmptyFolder( $tmp_path);


if ( !empty( $dbInfo['shareDB']) && $dbInfo['shareDB']) {

if ( empty( $deploy_dir)) {
$fname = $site_dir .DS. 'configuration.php';
}
else {
$fname = $deploy_dir .DS. 'configuration.php';
}



if ( version_compare( JVERSION, '1.6') >= 0) {
$configStr = $toConfig->toString('PHP', array('class' => 'JConfig'));
}
else {
$configStr = $toConfig->toString('PHP', 'config', array('class' => 'JConfig'));
}
if ( !Jms2WinFile::write($fname, $configStr)) {
return array( JText::sprintf( 'Error writing configuration file [%s]', $fname));
}

return null;
}


if ( !empty( $dbInfo['toDBHost'])) {
$toDBHost = MultisitesDatabase::evalStr( $dbInfo['toDBHost'], $site_id, $site_dir, $deploy_dir, $dbInfo);
}
else if ( isset( $template) && !empty( $template->toDBHost)) {
$toDBHost = MultisitesDatabase::evalStr( $template->toDBHost, $site_id, $site_dir, $deploy_dir, $dbInfo);
}
if ( !empty( $toDBHost)) {
$toConfig->setValue( 'config.host', $toDBHost);
}

if ( !empty( $dbInfo['toDBName'])) {
$toDBName = MultisitesDatabase::evalStr( $dbInfo['toDBName'], $site_id, $site_dir, $deploy_dir, $dbInfo);
}
else if ( isset( $template) && !empty( $template->toDBName)) {
$toDBName = MultisitesDatabase::evalStr( $template->toDBName, $site_id, $site_dir, $deploy_dir, $dbInfo);
}
if ( !empty( $toDBName)) {
$toConfig->setValue( 'config.db', $toDBName);
}

if ( !empty( $dbInfo['toDBUser'])) {
$toDBUser = MultisitesDatabase::evalStr( $dbInfo['toDBUser'], $site_id, $site_dir, $deploy_dir, $dbInfo);
}
else if ( isset( $template) && !empty( $template->toDBUser)) {
$toDBUser = MultisitesDatabase::evalStr( $template->toDBUser, $site_id, $site_dir, $deploy_dir, $dbInfo);
}
if ( !empty( $toDBUser)) {
$toConfig->setValue( 'config.user', $toDBUser);
}

if ( !empty( $dbInfo['toDBPsw'])) {
$toDBPsw = MultisitesDatabase::evalStr( $dbInfo['toDBPsw'], $site_id, $site_dir, $deploy_dir, $dbInfo);
}
else if ( isset( $template) && !empty( $template->toDBPsw)) {
$toDBPsw = MultisitesDatabase::evalStr( $template->toDBPsw, $site_id, $site_dir, $deploy_dir, $dbInfo);
}
if ( !empty( $toDBPsw)) {
$toConfig->setValue( 'config.password', $toDBPsw);
}

if ( isset( $dbInfo['toPrefix'])) {
$toPrefix = MultisitesDatabase::evalStr( $dbInfo['toPrefix'], $site_id, $site_dir, $deploy_dir, $dbInfo);
}
else if ( isset( $template) && !empty( $template->toPrefix)) {
$toPrefix = MultisitesDatabase::evalStr( $template->toPrefix, $site_id, $site_dir, $deploy_dir, $dbInfo);
}
if ( !empty( $toPrefix)) {
$toConfig->setValue( 'config.dbprefix', $toPrefix);
}

if ( isset( $dbInfo['toFTP_enable']) && ($dbInfo['toFTP_enable']=='0' || $dbInfo['toFTP_enable']=='1')) {

if ( isset( $dbInfo['toFTP_enable'])) {
$toFTP_enable = MultisitesDatabase::evalStr( $dbInfo['toFTP_enable'], $site_id, $site_dir, $deploy_dir, $dbInfo);
}
else if ( isset( $template) && !empty( $template->toFTP_enable)) {
$toFTP_enable = MultisitesDatabase::evalStr( $template->toFTP_enable, $site_id, $site_dir, $deploy_dir, $dbInfo);
}
$toConfig->setValue( 'config.ftp_enable', $toFTP_enable);

if ( isset( $dbInfo['toFTP_host'])) {
$toFTP_host = MultisitesDatabase::evalStr( $dbInfo['toFTP_host'], $site_id, $site_dir, $deploy_dir, $dbInfo);
}
else if ( isset( $template) && !empty( $template->toFTP_host)) {
$toFTP_host = MultisitesDatabase::evalStr( $template->toFTP_host, $site_id, $site_dir, $deploy_dir, $dbInfo);
}
if ( isset( $toFTP_host)) {
$toConfig->setValue( 'config.ftp_host', $toFTP_host);
}

if ( isset( $dbInfo['toFTP_port'])) {
$toFTP_port = MultisitesDatabase::evalStr( $dbInfo['toFTP_port'], $site_id, $site_dir, $deploy_dir, $dbInfo);
}
else if ( isset( $template) && !empty( $template->toFTP_port)) {
$toFTP_port = MultisitesDatabase::evalStr( $template->toFTP_port, $site_id, $site_dir, $deploy_dir, $dbInfo);
}
if ( isset( $toFTP_port)) {
$toConfig->setValue( 'config.ftp_port', $toFTP_port);
}

if ( isset( $dbInfo['toFTP_user'])) {
$toFTP_user = MultisitesDatabase::evalStr( $dbInfo['toFTP_user'], $site_id, $site_dir, $deploy_dir, $dbInfo);
}
else if ( isset( $template) && !empty( $template->toFTP_user)) {
$toFTP_user = MultisitesDatabase::evalStr( $template->toFTP_user, $site_id, $site_dir, $deploy_dir, $dbInfo);
}
if ( isset( $toFTP_user)) {
$toConfig->setValue( 'config.ftp_user', $toFTP_user);
}

if ( isset( $dbInfo['toFTP_psw'])) {
$toFTP_psw = MultisitesDatabase::evalStr( $dbInfo['toFTP_psw'], $site_id, $site_dir, $deploy_dir, $dbInfo);
}
else if ( isset( $template) && !empty( $template->toFTP_psw)) {
$toFTP_psw = MultisitesDatabase::evalStr( $template->toFTP_psw, $site_id, $site_dir, $deploy_dir, $dbInfo);
}
if ( isset( $toFTP_psw)) {
$toConfig->setValue( 'config.ftp_pass', $toFTP_psw);
}

if ( isset( $dbInfo['toFTP_rootpath'])) {
$toFTP_rootpath = MultisitesDatabase::evalStr( $dbInfo['toFTP_rootpath'], $site_id, $site_dir, $deploy_dir, $dbInfo);
}
else if ( isset( $template) && !empty( $template->toFTP_rootpath)) {
$toFTP_rootpath = MultisitesDatabase::evalStr( $template->toFTP_rootpath, $site_id, $site_dir, $deploy_dir, $dbInfo);
}
if ( isset( $toFTP_rootpath)) {
$toConfig->setValue( 'config.ftp_root', $toFTP_rootpath);
}
}

if ( empty( $deploy_dir)) {
$fname = $site_dir .DS. 'configuration.php';
}
else {
$fname = $deploy_dir .DS. 'configuration.php';
}

if ( version_compare( JVERSION, '1.6') >= 0) {
$configStr = $toConfig->toString('PHP', array('class' => 'JConfig'));
}
else {
$configStr = $toConfig->toString('PHP', 'config', array('class' => 'JConfig'));
}
if ( !Jms2WinFile::write($fname, $configStr)) {
return array( JText::sprintf( 'Error writing configuration file [%s]', $fname));
}

if ( !$makeDB) {

return $errors;
}



if ( !empty( $fromSiteID)) {
 if ( ($toConfig->getValue( 'config.dbtype') == $fromConfig->getValue( 'config.dbtype'))
&& ($toConfig->getValue( 'config.host') == $fromConfig->getValue( 'config.host'))
&& ($toConfig->getValue( 'config.db') == $fromConfig->getValue( 'config.db'))
)
{} 

else {
$errors = MultisitesDatabase::makeDB( $fromConfig, $toConfig);
if ( !empty( $errors )) {
return $errors;
}
}

if ( $fromSiteID == ':master_db:') {
$fromDB =& Jms2WinFactory::getMasterDBO();
}
else {
$fromDB =& Jms2WinFactory::getSlaveDBO( $fromSiteID);
}
}
else {
$fromDB =& Jms2WinFactory::getMasterDBO();
}
$toDB =& Jms2WinFactory::getSlaveDBO( $site_id);
if ( empty( $toDB)) {
return array( JText::_( 'Unable to connect on the "to" DB'));
}



$errors = MultisitesDatabase::copyDBSharing( $fromDB, $toDB, $sharedTables, $toConfig, $site_id, $enteredvalues, $template);
if ( empty( $errors )) {



JPluginHelper::importPlugin('multisites');
$results = JFactory::getApplication()->triggerEvent('onAfterCopyDB', array ( $fromDB, $toDB, $toConfig));
$errors = MultisitesDatabase::configureDB( $fromSiteID, $fromDB, $toDB, $enteredvalues, $site_id, $site_dir, $deploy_dir, $dbInfo, $template);
}

return $errors;
}


function _deployDirLinks( $source_dir, $target_dir, $wrapper_files)
{
$from_dir = JPath::clean( $source_dir);
$to_dir = JPath::clean( $target_dir);

if ( isset( $from_dir) && $to_dir != $from_dir) {

if ( !MultisitesHelper::isSymbolicLinks()) {

if ( !Jms2WinFolder::copy( $from_dir, $to_dir)) {
$this->setError( JText::sprintf( 'Unable to replicate the directory "%s" into "%s', $from_dir, $to_dir));
return false;
}
}

else {
$excluding_patterns = array('.svn', 'CVS', 'vssver.scc');
if ( !empty( $wrapper_files)) {
$excluding_patterns = array_merge( $excluding_patterns, $wrapper_files);
}
$folders = Jms2WinFolder::folders( $from_dir, '.', false, false, $excluding_patterns);
$savDir = getcwd();
chdir( $to_dir);

if ( !is_array( $folders)) {

}

else foreach( $folders as $link)
{

if ( !JFolder::exists( $to_dir .DS. $link)) {
$target_path = $from_dir .DS. $link;

if ( !$this->symlink( $target_path, $link)) {

$to_path = $to_dir .DS. $link;

if ( !JFolder::copy( $target_path, $to_path)) {
$this->setError( JText::sprintf( 'Unable to replicate the directory "%s" into "%s', $from_dir, $to_dir));
chdir( $savDir);
return false;
}
}
}
}
chdir( $savDir);
$files = Jms2WinFolder::files( $from_dir, '.', false, false, $excluding_patterns);
$savDir = getcwd();
chdir( $to_dir);

if ( !is_array( $files)) {

}

foreach( $files as $link)
{

if ( !Jms2WinFile::exists( $to_dir .DS. $link)) {
$target_path = $from_dir .DS. $link;

if ( !$this->symlink( $target_path, $link)) {

$to_path = $to_dir .DS. $link;

if ( !Jms2WinFile::copy( $target_path, $to_path)) {
$this->setError( JText::sprintf( 'Unable to replicate the directory "%s" into "%s', $from_dir, $to_dir));
chdir( $savDir);
return false;
}
}
}
}
chdir( $savDir);

foreach( $wrapper_files as $filename) {
$from_filename = $from_dir .DS. $filename;
$to_filename = $to_dir .DS. $filename;

if ( Jms2WinFile::exists( $from_filename) && !JFile::exists( $to_filename)) {

$content = "<?php\n"
. "// Don't use a Symbolic Link because the links maybe wrong.\n"
. "// Just include the original file to redirect the processing.\n"
. "//include( '$from_filename');\n"
. "// Evaluate the original include file to redirect to keep the __FILE__ value.\n"
. "\$filename = '$from_filename';\n"
. '$handle = fopen ($filename, "r");' . "\n"
. '$contents = fread ($handle, filesize ($filename));' . "\n"
. 'fclose ($handle);' . "\n"
. 'unset($handle);' . "\n"
. 'eval("?>" . $contents);' . "\n"
;
Jms2WinFile::write( $to_filename, $content);
}
}


$to_index_html = $to_dir .DS. 'index.html';
$to_index_php = $to_dir .DS. 'index.html';
if ( !Jms2WinFile::exists( $to_index_html) && !JFile::exists( $to_index_php)) {
$content = '<html><body bgcolor="#FFFFFF"></body></html>';
JFile::write( $to_index_html, $content);
}
}
}
return true;
}


function _deployTemplates_special( $templates_dir, $template, $site_id, $site_dir, $deploy_dir, $dbInfo, $force_copy = false)
{

if ( Jms2WinFolder::exists( $templates_dir)) {

return true;
}
$to_dir = Jms2WinPath::clean( $templates_dir);

$fromSiteID = $template->fromSiteID;
$filename = JPATH_MULTISITES.DS.$fromSiteID.DS.'config_multisites.php';
if ( !file_exists( $filename))
{
if ( class_exists( 'MultisitesLetterTree')) {

$lettertree_dir = MultisitesLetterTree::getLetterTreeDir( $fromSiteID);
if( !empty( $lettertree_dir)) {
$filename = $site_dir.DIRECTORY_SEPARATOR.'config_multisites.php';
}
}
}
@include($filename);

if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['templates_dir'])) {

$from_dir = Jms2WinPath::clean( $config_dirs['templates_dir']);
}
else {

$from_dir = Jms2WinPath::clean( JPATH_ROOT .DS. 'templates');
}

if ( !empty( $template)
&& !empty( $template->symboliclinks)
&& !empty( $template->symboliclinks['templates'])
&& !empty( $template->symboliclinks['templates']['file']))
{
$srcfile = $template->symboliclinks['templates']['file'];
$from_dir = $this->_getSourcePath( 'templates', $srcfile, $site_id, $site_dir, $deploy_dir, $dbInfo);
}

if ( isset( $from_dir) && $to_dir != $from_dir) {

if ( !MultisitesHelper::isSymbolicLinks() || $force_copy) {

$this->_createEmptyFolder( $to_dir);
$folders = Jms2WinFolder::folders( $from_dir, '.', false, false, array('.svn', 'CVS', 'vssver.scc'));
$savDir = getcwd();
chdir( $to_dir);
foreach( $folders as $link)
{

$from_path = $from_dir .DS. $link;
$to_path = $to_dir .DS. $link;

if ( !Jms2WinFolder::copy( $from_path, $to_path)) {
$this->setError( JText::sprintf( 'Unable to copy the template directory "%s" into "%s', $from_path, $to_path));
chdir( $savDir);
return false;
}
}
chdir( $savDir);
}

else {
$this->_createEmptyFolder( $to_dir);
$folders = Jms2WinFolder::folders( $from_dir, '.', false, false, array('.svn', 'CVS', 'vssver.scc'));
$savDir = getcwd();
chdir( $to_dir);
foreach( $folders as $link)
{
$target_path = $from_dir .DS. $link;

if ( !$this->symlink( $target_path, $link)) {

$to_path = $to_dir .DS. $link;

if ( !Jms2WinFolder::copy( $target_path, $to_path)) {
$this->setError( JText::sprintf( 'Unable to replicate the template directory "%s" into "%s', $from_dir, $to_dir));
chdir( $savDir);
return false;
}
}
}
chdir( $savDir);
}
}
return true;
}


function _deployTemplates( $templates_dir, $template, $site_id, $site_dir, $deploy_dir, $dbInfo)
{
if ( empty( $template)) {
return $this->_deployTemplates_special( $templates_dir, $template, $site_id, $site_dir, $deploy_dir, $dbInfo);
}

if ( !empty( $template->symboliclinks)
&& !empty( $template->symboliclinks['templates'])
&& !empty( $template->symboliclinks['templates']['action'])) {
$action = $template->symboliclinks['templates']['action'];
if ( $action == 'copy') {
return $this->_deployTemplates_special( $templates_dir, $template, $site_id, $site_dir, $deploy_dir, $dbInfo, true);
}
else if ( $action == 'unzip') {
return true;
}
}

return $this->_deployTemplates_special( $templates_dir, $template, $site_id, $site_dir, $deploy_dir, $dbInfo);
}


function _calcConfigDirs( $enteredvalues, $site_id, $site_dir, $dbInfo, $template)
{
$config_dirs = array();




$deploy_dir = null;

if ( MultisitesHelper::isSymbolicLinks()) {

if ( isset( $enteredvalues['deploy_dir']) && !empty( $enteredvalues['deploy_dir'])) {

$deploy_dir = $enteredvalues['deploy_dir'];
}
else if ( isset( $template) && !empty( $template->deploy_dir)) {

$deploy_dir = $template->deploy_dir;
}
}
if ( isset( $deploy_dir) && !empty( $deploy_dir)) {
$config_dirs['deploy_dir'] = JPath::clean( MultisitesDatabase::evalStr( $deploy_dir, $site_id, $site_dir, null, $dbInfo));

if ( !empty( $config_dirs['deploy_dir'])) {

$deploy_dir = $config_dirs['deploy_dir'];
}
}

if ( !empty( $enteredvalues['alias_link'])) {

$alias_link = $enteredvalues['alias_link'];
}
else if ( isset( $template) && !empty( $template->alias_link)) {

$alias_link = $template->alias_link;
}

if ( !empty( $alias_link)) {
$path = trim( JPath::clean( MultisitesDatabase::evalStr( $alias_link, $site_id, $site_dir, $deploy_dir, $dbInfo)));

if ( !empty( $path)) {

$config_dirs['alias_link'] = strtolower( $path);
}
else {
return false;
}
}

if ( !empty( $enteredvalues['delete_dir'])) {

$delete_dir = $enteredvalues['delete_dir'];
}
else if ( isset( $template) && !empty( $template->delete_dir)) {

$delete_dir = $template->delete_dir;
}

if ( !empty( $delete_dir)) {
$path = trim( JPath::clean( MultisitesDatabase::evalStr( $delete_dir, $site_id, $site_dir, $deploy_dir, $dbInfo)));

if ( !empty( $path)) {

$config_dirs['delete_dir'] = strtolower( $path);
}
}

if ( isset( $enteredvalues['templates_dir']) && !empty( $enteredvalues['templates_dir'])) {

$templates_dir = JPath::clean( $enteredvalues['templates_dir']);
}
else if ( isset( $template) && !empty( $template->templates_dir)) {

$templates_dir = JPath::clean( $template->templates_dir);
}
if ( isset( $templates_dir)) {
$path = JPath::clean( MultisitesDatabase::evalStr( $templates_dir, $site_id, $site_dir, $deploy_dir, $dbInfo));

if ( $this->_deployTemplates( $path, $template, $site_id, $site_dir, $deploy_dir, $dbInfo)) {

$config_dirs['templates_dir'] = $path;
}
else {
return false;
}
}

if ( isset( $enteredvalues['cache_dir']) && !empty( $enteredvalues['cache_dir'])) {

$cache_dir = Jms2WinPath::clean( $enteredvalues['cache_dir']);
}
else if ( isset( $template) && !empty( $template->cache_dir)) {

$cache_dir = Jms2WinPath::clean( $template->cache_dir);
}
if ( isset( $cache_dir)) {
$config_dirs['cache_dir'] = Jms2WinPath::clean( MultisitesDatabase::evalStr( $cache_dir, $site_id, $site_dir, $deploy_dir, $dbInfo));
}
else {
if ( !empty( $deploy_dir)) {
if ( Jms2WinFolder::exists( $deploy_dir)) {
$path = Jms2WinPath::clean( $deploy_dir .DS. 'cache');
$this->_createEmptyFolder( $path);
$config_dirs['cache_dir'] = $path;
}
}
else {
$path = Jms2WinPath::clean( $site_dir .DS. 'cache');
$this->_createEmptyFolder( $path);
$config_dirs['cache_dir'] = $path;
}
}
if ( !empty( $template)) {
$config_dirs['symboliclinks'] = $template->symboliclinks;
$config_dirs['dbsharing'] = $template->dbsharing;
}
return $config_dirs;
}


function &_getSiteInfo( $enteredvalues)
{
static $instance;
if ( empty( $instance)) {
$siteInfo = array();
$siteInfo['site_prefix'] = !empty( $enteredvalues['site_prefix'])
? $enteredvalues['site_prefix']
: '';
$siteInfo['site_alias'] = !empty( $enteredvalues['site_alias'])
? $enteredvalues['site_alias']
: '';
$siteInfo['newAdminEmail'] = !empty( $enteredvalues['newAdminEmail'])
? $enteredvalues['newAdminEmail']
: '';
$instance = $siteInfo;
}
return $instance;
}


function getSiteID( $enteredvalues)
{
$template = MultisitesModelManage::_getTemplate( $enteredvalues);
$siteInfo = MultisitesModelManage::_getSiteInfo( $enteredvalues);


$id = !empty( $enteredvalues['id'])
? $enteredvalues['id']
: '';
if ( empty( $id) && !empty( $template) && !empty( $template->fromSiteID)) {

$str = $template->toSiteID;
$id = MultisitesDatabase::evalStr( $str, null, null, null, $siteInfo);
}
if ( empty($id)) {
$this->setError( JText::_( 'SITE_PROVIDE_ID'));
return false;
}
return $id;
}


function writeSite( $site_dir, $domains, $indexDomains, $newDBInfo, $config_dirs, $enteredvalues=array())
{

$config = "<?php\n";





$config .= "\$domains = array( '" . implode( "' , '", $domains) . "');\n";
$config .= "\$indexDomains = array( '" . implode( "' , '", $indexDomains) . "');\n";
if ( !empty( $newDBInfo)) {
$config .= '$newDBInfo = array( ';
$sep = '';
foreach( $newDBInfo as $key => $value) {
if ( is_array( $value)) {
$config .= $sep . "'$key' => " . 'array( ' . MultisitesUtils::CnvArray2Str( '          ', $value) . ')';
}
else {
$config .= $sep . "'$key' => '" . addslashes($value) ."'";
}
$sep = ', ';
}
$config .= ");\n";
}
if ( !empty( $config_dirs)) {
$config .= '$config_dirs = array( ';
$sep = '';
foreach( $config_dirs as $key => $value) {
if ( is_array( $value)) {
$config .= $sep . "'$key' => " . 'array( ' . MultisitesUtils::CnvArray2Str( '          ', $value) . ')';
}
else {
$config .= $sep . "'$key' => '" . addslashes($value) ."'";
}
$sep = ', ';
}
$config .= ");\n";
}
if ( !empty($enteredvalues) && !empty( $enteredvalues['userdefs'])) {
$config .= '$userdefs = array( ';
$sep = '';
foreach( $enteredvalues['userdefs'] as $key => $value) {
if ( is_array( $value)) {
$config .= $sep . "'$key' => " . 'array( ' . MultisitesUtils::CnvArray2Str( '          ', $value) . ')';
}
else {
$config .= $sep . "'$key' => '" . addslashes($value) ."'";
}
$sep = ', ';
}
$config .= ");\n";
}
$config .= "?>";
$filename = $site_dir .DS. 'config_multisites.php';
return JFile::write( $filename, $config);
}


function _countWebSites( $owner_id = null)
{

if ( empty( $owner_id) || $owner_id <= 0) {
return 0;
}
$count = 0;

$sites = $this->getSites();
foreach( $sites as $site) {



if ( empty( $site->status) || $site->status == 'Confirmed') {
if ( !empty( $site->owner_id) && $site->owner_id == $owner_id) {
$count++;
}
}
}
return $count;
}


function _isDeployedFTPEnabled( $newDBInfo)
{
if ( !defined( 'MULTISITES_REDIRECT_FTP') || !(MULTISITES_REDIRECT_FTP)) {
return false;
}

if ( isset( $newDBInfo['toFTP_enable']) && $newDBInfo['toFTP_enable'] == 0) {
return false;
}

else if ( isset( $newDBInfo['toFTP_enable']) && $newDBInfo['toFTP_enable'] == 1) {
return true;
}


jimport('joomla.client.helper');
$ftpOptions = JClientHelper::getCredentials('ftp');
if ($ftpOptions['enabled'] == 1) {
return true;
}
return false;
}


function _saveFTPInfos( &$sav_FTPInfos)
{
$config =& JFactory::getConfig();

if ( method_exists( $config, 'getValue')) {
$sav_FTPInfos['toFTP_enable'] = $config->getValue('config.ftp_enable');
$sav_FTPInfos['toFTP_host'] = $config->getValue('config.ftp_host');
$sav_FTPInfos['toFTP_port'] = $config->getValue('config.ftp_port');
$sav_FTPInfos['toFTP_user'] = $config->getValue('config.ftp_user');
$sav_FTPInfos['toFTP_psw'] = $config->getValue('config.ftp_pass');
$sav_FTPInfos['toFTP_rootpath']= $config->getValue('config.ftp_root');
}

else {
$sav_FTPInfos['toFTP_enable'] = $config->get('ftp_enable');
$sav_FTPInfos['toFTP_host'] = $config->get('ftp_host');
$sav_FTPInfos['toFTP_port'] = $config->get('ftp_port');
$sav_FTPInfos['toFTP_user'] = $config->get('ftp_user');
$sav_FTPInfos['toFTP_psw'] = $config->get('ftp_pass');
$sav_FTPInfos['toFTP_rootpath']= $config->get('ftp_root');
}
}


function _setNewFTPInfos( $newFTPInfos)
{
if ( empty( $newFTPInfos)) {
return;
}
$config =& JFactory::getConfig();

if ( method_exists( $config, 'getValue')) {
$orig_ftp_enable = JFactory::getConfig()->getValue('config.ftp_enable');
$orig_ftp_root = JFactory::getConfig()->getValue('config.ftp_root');
}

else {
$orig_ftp_enable = JFactory::getConfig()->get('ftp_enable');
$orig_ftp_root = JFactory::getConfig()->get('ftp_root');
}

if ( method_exists( $config, 'setValue')) {
JFactory::getConfig()->setValue('config.ftp_enable', $newFTPInfos['toFTP_enable']);
JFactory::getConfig()->setValue('config.ftp_host', $newFTPInfos['toFTP_host']);
JFactory::getConfig()->setValue('config.ftp_port', $newFTPInfos['toFTP_port']);
JFactory::getConfig()->setValue('config.ftp_user', $newFTPInfos['toFTP_user']);
JFactory::getConfig()->setValue('config.ftp_pass', $newFTPInfos['toFTP_psw']);

}

else {
JFactory::getConfig()->set('ftp_enable', $newFTPInfos['toFTP_enable']);
JFactory::getConfig()->set('ftp_host', $newFTPInfos['toFTP_host']);
JFactory::getConfig()->set('ftp_port', $newFTPInfos['toFTP_port']);
JFactory::getConfig()->set('ftp_user', $newFTPInfos['toFTP_user']);
JFactory::getConfig()->set('ftp_pass', $newFTPInfos['toFTP_psw']);
}

jimport('joomla.client.helper');
$ftpOptions = JClientHelper::getCredentials('ftp', true);
$ftp = &Jms2WinFTP::getInstance(
$ftpOptions['host'], $ftpOptions['port'], null,
$ftpOptions['user'], $ftpOptions['pass'],
$newFTPInfos['toFTP_dir'],
$newFTPInfos['toFTP_rootpath'],
$orig_ftp_enable,
$orig_ftp_root
);
}
function _restoreFTPInfos( $newFTPInfos)
{
if ( empty( $newFTPInfos)) {
return;
}

jimport('joomla.client.helper');
$ftpOptions = JClientHelper::getCredentials('ftp');
$ftp = &JFTP::getInstance(
$ftpOptions['host'], $ftpOptions['port'], null,
$ftpOptions['user'], $ftpOptions['pass']
);
if ( is_a( $instance, 'Jms2WinFTP')) {
$ftp->restoreOriginalInstance();
}

$config = JFactory::getConfig();

if ( method_exists( $config, 'setValue')) {
JFactory::getConfig()->setValue('config.ftp_enable', $newFTPInfos['toFTP_enable']);
JFactory::getConfig()->setValue('config.ftp_host', $newFTPInfos['toFTP_host']);
JFactory::getConfig()->setValue('config.ftp_port', $newFTPInfos['toFTP_port']);
JFactory::getConfig()->setValue('config.ftp_user', $newFTPInfos['toFTP_user']);
JFactory::getConfig()->setValue('config.ftp_pass', $newFTPInfos['toFTP_psw']);
}

else {
JFactory::getConfig()->set('ftp_enable', $newFTPInfos['toFTP_enable']);
JFactory::getConfig()->set('ftp_host', $newFTPInfos['toFTP_host']);
JFactory::getConfig()->set('ftp_port', $newFTPInfos['toFTP_port']);
JFactory::getConfig()->set('ftp_user', $newFTPInfos['toFTP_user']);
JFactory::getConfig()->set('ftp_pass', $newFTPInfos['toFTP_psw']);
}
$ftpOptions = JClientHelper::getCredentials('ftp', true);
}


function createDir( $path = '', $mode = 0755)
{

if ( defined( 'MULTISITES_LETTER_TREE') && MULTISITES_LETTER_TREE) {

$parts = explode( DS, $path);
$dir_parts = array();
for ( $i = 0; $i<count( $parts); $i++) {
$dir_parts[] = $parts[$i];
if ( ($i%10)==0) {
if ( count( $dir_parts) > 1) {
$dir = implode( DS, $dir_parts);
JFolder::create( $dir, $mode);
}
}
}
}
return JFolder::create( $path, $mode);
}


function deploySite( &$enteredvalues, $front_end = false)
{
if ( JFile::exists( dirname( __FILE__).DS.'manage_geoloc.php')) { include_once( dirname( __FILE__).DS.'manage_geoloc.php'); }
if ( JFile::exists( dirname( __FILE__).DS.'manage_browser.php')) { include_once( dirname( __FILE__).DS.'manage_browser.php'); }

if ( !$this->canCreateSlave($enteredvalues, $front_end)) {
$err = $this->getError();
if ( empty( $err)){
$this->setError( JText::_( 'SITE_DEPLOY_CANNOT_CREATE'));
}
return false;
}

if ( !JFolder::exists( JPATH_MULTISITES)) {

if ( ! JFolder::create( JPATH_MULTISITES, MULTISITES_DIR_RIGHTS)
|| ! JFolder::exists( JPATH_MULTISITES))
{
$this->setError( JText::sprintf( 'SITE_MSDIR_NOTFOUND', JPATH_MULTISITES));
return false;
}

@chmod( JPATH_MULTISITES, MULTISITES_DIR_RIGHTS);
}
$template = MultisitesModelManage::_getTemplate( $enteredvalues);
$siteInfo = MultisitesModelManage::_getSiteInfo( $enteredvalues);
$id = MultisitesModelManage::getSiteID( $enteredvalues);
if ( $id === false) {
$this->setError( JText::_( 'SITE_DEPLOY_SITE_ID_ERR'));
return false;
}
$enteredvalues['id'] = $id;
if ( !empty( $enteredvalues['force_flat_dir']) && $enteredvalues['force_flat_dir']) { $force_flat_dir = true; }
else { $force_flat_dir = false; }
if ( $force_flat_dir) {}

else {

if ( Site::is_Site( $id)) {

$force_flat_dir = true;
}
}
$site_dir = &$this->getSiteDir( $id, $force_flat_dir);

if ( !empty( $enteredvalues['owner_id'])
&& !empty( $template) && !empty( $template->maxsite)) {
$count = $this->_countWebSites( $enteredvalues['owner_id']);
if ( $count >= $template->maxsite) {
$this->setError( JText::sprintf( 'SITE_MAXSITE_REACHED', $template->maxsite, $count));
return false;
}
}


if ( empty( $enteredvalues['domains']) && !empty( $template) && !empty( $template->toDomains)) {
$enteredvalues['domains'] = array();

foreach( $template->toDomains as $domain) {

if ( empty( $siteInfo['site_prefix'])
&& strstr( $domain, '{site_prefix}') !== false) {
continue;
}

if ( empty( $siteInfo['site_alias'])
&& strstr( $domain, '{site_alias}') !== false) {
continue;
}
$str = MultisitesDatabase::evalStr( $domain, $id, $site_dir, null, $siteInfo);
$enteredvalues['domains'][] = $str;
}
}
if ( empty($enteredvalues['domains'])) {
$this->setError( JText::_( 'SITE_DOMAIN_MISSING'));
return false;
}

$enteredvalues['indexDomains'] = array();
foreach( $enteredvalues['domains'] as $domain) {
$str = MultisitesDatabase::evalStr( $domain, $id, $site_dir, null, $siteInfo);
$enteredvalues['indexDomains'][] = str_replace( "\\", '/', $str);
}

$month_str = array( 0, 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
if ( $front_end
&& empty( $enteredvalues['expiration']) && !empty($template->validity) ) {
$validity = $template->validity;

if ( $template->validity_unit == 'years') {
$expiration = strtotime("+$validity years");
}

else if ( $template->validity_unit == 'months') {
$expiration = strtotime("+$validity months");
}

else {
$expiration = strtotime("+$validity days");
}
if ( !empty( $expiration)) {


$expiration_str = strftime( '%d-%m-%Y', $expiration);
$expiration_arr = explode( '-', $expiration_str);
$expiration_arr[1] = $month_str[ (int)$expiration_arr[1]];
$expiration_str = implode( '-', $expiration_arr);
$enteredvalues['expiration'] = $expiration_str;
}
}

if ( !empty( $enteredvalues['expiration'])) {
$expiration_arr = explode( '-', $enteredvalues['expiration']);

if ( strlen( $expiration_arr[0]) == 4) {

$expiration_str = $expiration_arr[2]
. '-'
. $month_str[(int)$expiration_arr[1]]
. '-'
. $expiration_arr[0];
$enteredvalues['expiration'] = $expiration_str;
}
}

if ( !empty( $enteredvalues['expiration']) && !empty($template->expireurl)) {
$enteredvalues['expireurl'] = MultisitesDatabase::evalStr( $template->expireurl, $id, $site_dir, null, $siteInfo);
}
if ( empty( $enteredvalues['redirect1st']) && !empty($template->redirect1st) ) {
$enteredvalues['redirect1st'] = $template->redirect1st;
}
if ( empty( $enteredvalues['ignoreMasterIndex']) && !empty($template->ignoreMasterIndex) ) {
$enteredvalues['ignoreMasterIndex'] = $template->ignoreMasterIndex;
}
if ( empty( $enteredvalues['shareDB']) && !empty($template->shareDB) ) {
$enteredvalues['shareDB'] = $template->shareDB;
}

if ( empty( $enteredvalues['toDBHost']) && !empty($template->toDBHost) ) {
$enteredvalues['toDBHost'] = MultisitesDatabase::evalStr( $template->toDBHost, $id, $site_dir, null, $siteInfo);
}
if ( empty( $enteredvalues['toDBName']) && !empty($template->toDBName) ) {
$enteredvalues['toDBName'] = MultisitesDatabase::evalStr( $template->toDBName, $id, $site_dir, null, $siteInfo);
}

if ( !empty( $enteredvalues['toDBName'])) {
$str = (string) preg_replace('/[^A-Z0-9_]/i', '', $enteredvalues['toDBName']);
$enteredvalues['toDBName'] = trim( $str);
}
if ( empty( $enteredvalues['toDBUser']) && !empty($template->toDBUser) ) {
$enteredvalues['toDBUser'] = MultisitesDatabase::evalStr( $template->toDBUser, $id, $site_dir, null, $siteInfo);
}
if ( empty( $enteredvalues['toDBPsw']) && !empty($template->toDBPsw) ) {
$enteredvalues['toDBPsw'] = MultisitesDatabase::evalStr( $template->toDBPsw, $id, $site_dir, null, $siteInfo);
}

if ( empty( $enteredvalues['toPrefix']) && !empty($template->toPrefix) ) {

$enteredvalues['toPrefix'] = MultisitesDatabase::evalStr( $template->toPrefix, $id, $site_dir, null, $siteInfo);
}

if ( !empty( $enteredvalues['toPrefix'])) {
$str = (string) preg_replace('/[^A-Z0-9_]/i', '', MultisitesDatabase::evalStr( $enteredvalues['toPrefix'], $id, $site_dir, null, $siteInfo));
$enteredvalues['toPrefix'] = trim( $str);
}

if ( $front_end && empty( $enteredvalues['toPrefix'])) {
$this->setError( JText::_( 'SITE_TABLE_PREFIX_MANDATORY'));
return false;
}
if ( empty( $enteredvalues['toSiteName']) && !empty($template->toSiteName) ) {
$enteredvalues['toSiteName'] = MultisitesDatabase::evalStr( $template->toSiteName, $id, $site_dir, null, $siteInfo);
}
if ( empty( $enteredvalues['emailToAddress']) && !empty($template->emailToAddress) ) {

$enteredvalues['emailToAddress'] = $template->emailToAddress;
}
if ( empty( $enteredvalues['setDefaultJLang']) && !empty($template->setDefaultJLang) ) {
$enteredvalues['setDefaultJLang'] = $template->setDefaultJLang;
}
if ( empty( $enteredvalues['setDefaultTemplate']) && !empty($template->setDefaultTemplate) ) {
$enteredvalues['setDefaultTemplate'] = $template->setDefaultTemplate;
}
if ( empty( $enteredvalues['setDefaultMenu']) && !empty($template->setDefaultMenu) ) {
$enteredvalues['setDefaultMenu'] = $template->setDefaultMenu;
}
if ( function_exists( 'geoloc_CheckValues')) { geoloc_CheckValues( $enteredvalues, $template); }
if ( function_exists( 'browser_CheckValues')) { browser_CheckValues( $enteredvalues, $template); }

$ftpInfos = array();
if ( isset( $enteredvalues['toFTP_enable'])
&& ($enteredvalues['toFTP_enable']=='0' || $enteredvalues['toFTP_enable']=='1'))
{
$ftpInfos['toFTP_enable'] = $enteredvalues['toFTP_enable'];
if ( !empty( $enteredvalues['toFTP_host'])) {

$ftpInfos['toFTP_host'] = MultisitesDatabase::evalStr( $enteredvalues['toFTP_host'], $id, $site_dir, null, $siteInfo);
}
else if ( !empty($template->toFTP_host) ) {

$ftpInfos['toFTP_host'] = MultisitesDatabase::evalStr( $template->toFTP_host, $id, $site_dir, null, $siteInfo);
}
if ( !empty( $enteredvalues['toFTP_port'])) {

$ftpInfos['toFTP_port'] = MultisitesDatabase::evalStr( $enteredvalues['toFTP_port'], $id, $site_dir, null, $siteInfo);
}
else if ( !empty($template->toFTP_port) ) {

$ftpInfos['toFTP_port'] = MultisitesDatabase::evalStr( $template->toFTP_port, $id, $site_dir, null, $siteInfo);
}
if ( !empty( $enteredvalues['toFTP_user'])) {

$ftpInfos['toFTP_user'] = MultisitesDatabase::evalStr( $enteredvalues['toFTP_user'], $id, $site_dir, null, $siteInfo);
}
else if ( !empty($template->toFTP_user) ) {

$ftpInfos['toFTP_user'] = MultisitesDatabase::evalStr( $template->toFTP_user, $id, $site_dir, null, $siteInfo);
}
if ( !empty( $enteredvalues['toFTP_psw'])) {

$ftpInfos['toFTP_psw'] = MultisitesDatabase::evalStr( $enteredvalues['toFTP_psw'], $id, $site_dir, null, $siteInfo);
}
else if ( !empty($template->toFTP_psw) ) {

$ftpInfos['toFTP_psw'] = MultisitesDatabase::evalStr( $template->toFTP_psw, $id, $site_dir, null, $siteInfo);
}
if ( !empty( $enteredvalues['toFTP_rootpath'])) {

$ftpInfos['toFTP_rootpath'] = MultisitesDatabase::evalStr( $enteredvalues['toFTP_rootpath'], $id, $site_dir, null, $siteInfo);
}
else if ( !empty($template->toFTP_rootpath) ) {

$ftpInfos['toFTP_rootpath'] = MultisitesDatabase::evalStr( $template->toFTP_rootpath, $id, $site_dir, null, $siteInfo);
}
}

else if ( isset($template->toFTP_enable) && ($template->toFTP_enable=='0' || $template->toFTP_enable=='1') ) {
$ftpInfos['toFTP_enable'] = MultisitesDatabase::evalStr( $template->toFTP_enable, $id, $site_dir, null, $siteInfo);
if ( !empty($template->toFTP_host) ) {

$ftpInfos['toFTP_host'] = MultisitesDatabase::evalStr( $template->toFTP_host, $id, $site_dir, null, $siteInfo);
}
if ( !empty($template->toFTP_port) ) {

$ftpInfos['toFTP_port'] = MultisitesDatabase::evalStr( $template->toFTP_port, $id, $site_dir, null, $siteInfo);
}
if ( !empty($template->toFTP_user) ) {

$ftpInfos['toFTP_user'] = MultisitesDatabase::evalStr( $template->toFTP_user, $id, $site_dir, null, $siteInfo);
}
if ( !empty($template->toFTP_psw) ) {

$ftpInfos['toFTP_psw'] = MultisitesDatabase::evalStr( $template->toFTP_psw, $id, $site_dir, null, $siteInfo);
}
if ( !empty($template->toFTP_rootpath) ) {

$ftpInfos['toFTP_rootpath'] = MultisitesDatabase::evalStr( $template->toFTP_rootpath, $id, $site_dir, null, $siteInfo);
}
}

else {}

if ( empty( $enteredvalues['status'])) {
$enteredvalues['status'] = 'Pending';
}

if ( !empty( $enteredvalues['isnew']) && $enteredvalues['isnew'] && Site::is_Site( '', $site_dir)) {
$this->setError( JText::sprintf( 'SITE_ID_EXISTS', $site_dir));
$enteredvalues['error_code'] = 'SITE_ID_EXISTS';
return false;
}

if ( !JFolder::exists( $site_dir) && !$this->createDir( $site_dir, MULTISITES_DIR_RIGHTS)) {
$this->setError( JText::sprintf( 'SITE_CREATE_ID_ERR', $site_dir));
return false;
}

@chmod( $site_dir, MULTISITES_DIR_RIGHTS);

$website_id = '';
if ( $front_end) {
$website_id = $this->_getWebsiteID( $enteredvalues);
if ( $website_id === false) {
$this->setError( JText::_( 'SITE_DEPLOY_GETWEBSITEID_ERR'));
return false;
}
$enteredvalues['website_id'] = $website_id;
}
$newDBInfo = array();
if ( !empty( $enteredvalues['status'])) { $newDBInfo['status'] = $enteredvalues['status']; }
if ( !empty( $enteredvalues['payment_ref'])) { $newDBInfo['payment_ref'] = $enteredvalues['payment_ref']; }
if ( !empty( $enteredvalues['expiration'])) { $newDBInfo['expiration'] = $enteredvalues['expiration']; }
if ( !empty( $enteredvalues['expireurl'])) { $newDBInfo['expireurl'] = $enteredvalues['expireurl']; }
if ( !empty( $enteredvalues['owner_id'])) { $newDBInfo['owner_id'] = $enteredvalues['owner_id']; }
if ( !empty( $enteredvalues['fromTemplateID'])) { $newDBInfo['fromTemplateID']= $enteredvalues['fromTemplateID']; }
if ( !empty( $enteredvalues['site_prefix'])) { $newDBInfo['site_prefix'] = $enteredvalues['site_prefix']; }
if ( !empty( $enteredvalues['site_alias'])) { $newDBInfo['site_alias'] = $enteredvalues['site_alias']; }
if ( !empty( $enteredvalues['siteComment'])) { $newDBInfo['siteComment'] = $enteredvalues['siteComment']; }
if ( !empty( $enteredvalues['toSiteName'])) { $newDBInfo['toSiteName'] = $enteredvalues['toSiteName']; }
if ( !empty( $enteredvalues['toMetaDesc'])) { $newDBInfo['toMetaDesc'] = $enteredvalues['toMetaDesc']; }
if ( !empty( $enteredvalues['toMetaKeys'])) { $newDBInfo['toMetaKeys'] = $enteredvalues['toMetaKeys']; }
if ( !empty( $enteredvalues['redirect1st'])) { $newDBInfo['redirect1st'] = $enteredvalues['redirect1st']; }

if ( function_exists( 'geoloc_setNewDBInfo')) { geoloc_setNewDBInfo( $enteredvalues, $newDBInfo); }

if ( function_exists( 'browser_setNewDBInfo')) { browser_setNewDBInfo( $enteredvalues, $newDBInfo); }

if ( !empty( $enteredvalues['shareDB'])) { $newDBInfo['shareDB'] = $enteredvalues['shareDB']; }
if ( !empty( $enteredvalues['toDBHost'])) { $newDBInfo['toDBHost'] = $enteredvalues['toDBHost']; }
if ( !empty( $enteredvalues['toDBName'])) { $newDBInfo['toDBName'] = $enteredvalues['toDBName']; }
if ( !empty( $enteredvalues['toDBUser'])) { $newDBInfo['toDBUser'] = $enteredvalues['toDBUser']; }
if ( !empty( $enteredvalues['toDBPsw'])) { $newDBInfo['toDBPsw'] = $enteredvalues['toDBPsw']; }
if ( !empty( $enteredvalues['toPrefix'])) { $newDBInfo['toPrefix'] = $enteredvalues['toPrefix']; }
if ( !empty( $enteredvalues['website_id'])) { $newDBInfo['website_id'] = $enteredvalues['website_id']; }

if ( !empty( $enteredvalues['setDefaultJLang'])) { $newDBInfo['setDefaultJLang'] = $enteredvalues['setDefaultJLang']; }
if ( !empty( $enteredvalues['setDefaultTemplate'])){ $newDBInfo['setDefaultTemplate']= $enteredvalues['setDefaultTemplate']; }
if ( !empty( $enteredvalues['setDefaultMenu'])) { $newDBInfo['setDefaultMenu'] = $enteredvalues['setDefaultMenu']; }

if ( !empty( $enteredvalues['media_dir'])) { $newDBInfo['media_dir'] = $enteredvalues['media_dir']; }
if ( !empty( $enteredvalues['images_dir'])) { $newDBInfo['images_dir'] = $enteredvalues['images_dir']; }
if ( !empty( $enteredvalues['templates_dir'])) { $newDBInfo['templates_dir'] = $enteredvalues['templates_dir']; }
if ( !empty( $enteredvalues['cache_dir'])) { $newDBInfo['cache_dir'] = $enteredvalues['cache_dir']; }
if ( !empty( $enteredvalues['tmp_dir'])) { $newDBInfo['tmp_dir'] = $enteredvalues['tmp_dir']; }

if ( isset( $ftpInfos['toFTP_enable']) && $ftpInfos['toFTP_enable']!='')
{ $newDBInfo['toFTP_enable'] = $ftpInfos['toFTP_enable']; }
if ( !empty( $ftpInfos['toFTP_host'])) { $newDBInfo['toFTP_host'] = $ftpInfos['toFTP_host']; }
if ( !empty( $ftpInfos['toFTP_port'])) { $newDBInfo['toFTP_port'] = $ftpInfos['toFTP_port']; }
if ( !empty( $ftpInfos['toFTP_user'])) { $newDBInfo['toFTP_user'] = $ftpInfos['toFTP_user']; }
if ( !empty( $ftpInfos['toFTP_psw'])) { $newDBInfo['toFTP_psw'] = $ftpInfos['toFTP_psw']; }
if ( !empty( $ftpInfos['toFTP_rootpath'])) { $newDBInfo['toFTP_rootpath']= $ftpInfos['toFTP_rootpath']; }
$config_dirs = $this->_calcConfigDirs( $enteredvalues, $id, $site_dir, $newDBInfo, $template);
if ( $config_dirs === false) {
$this->setError( JText::_( 'SITE_DEPLOY_CONFIG_DIR_ERR'));
return false;
}

if ( !empty( $config_dirs['deploy_dir'])
&& !empty( $config_dirs['alias_link'])
&& MultisitesHelper::isSymbolicLinks()
)
{

$cur_alias_link = $this->getAliasLink( $id);

if ( is_link( $config_dirs['alias_link'])) {

$cur_path = readlink( $config_dirs['alias_link']);
if ( $cur_path === false) {
}

else if ( $cur_path != $config_dirs['deploy_dir']) {
if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }
$this->_setNewFTPInfos( $sav_FTPInfos);
$this->setError( JText::_( 'SITE_DEPLOY_ALIAS_CREATION_ERROR'));
return false;
}
}
}

$this->writeSite( $site_dir,
$enteredvalues['domains'], $enteredvalues['indexDomains'],
$newDBInfo, $config_dirs,
$enteredvalues);

$sav_FTPInfos = array();
if ( $this->_isDeployedFTPEnabled( $ftpInfos)) {

$this->_saveFTPInfos( $sav_FTPInfos);

if ( !empty( $config_dirs['deploy_dir'])) {
$ftpInfos['toFTP_dir'] = $config_dirs['deploy_dir'];
}
else {
$ftpInfos['toFTP_dir'] = $site_dir;
}
$this->_setNewFTPInfos( $ftpInfos);

foreach( $ftpInfos as $key => $value) {
$enteredvalues[$key] = $value;
}

$ftp = &Jms2WinFTP::getInstance(
$ftpInfos['toFTP_host'], $ftpInfos['toFTP_port'], null,
$ftpInfos['toFTP_user'], $ftpInfos['toFTP_psw']
);
$ftproot_perms = $ftp->fileperms( $ftpInfos['toFTP_rootpath']);
 if ( !empty( $ftproot_perms)) { $ftp->chmod( $ftpInfos['toFTP_rootpath'], '0777', false); }
}

if ( !empty( $config_dirs['deploy_dir'])) {
$deploy_dir = $config_dirs['deploy_dir'];

if ( !Jms2WinFolder::exists( $deploy_dir)
&& ( !empty( $enteredvalues['deploy_create']) || !empty($template->deploy_create))
) {
Jms2WinFolder::create( $deploy_dir);
}

if ( !Jms2WinFolder::exists( $deploy_dir) ) {
if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }
$this->_setNewFTPInfos( $sav_FTPInfos);
$this->setError( JText::sprintf( 'SITE_DEPLOY_DEPLOY_DIR_NOTFOUND', $deploy_dir));
return false;
}
if ( strtolower( rtrim( JPath::clean( $deploy_dir), '/')) == strtolower( rtrim( JPath::clean( JPATH_ROOT), '/'))) {
if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }
$this->_setNewFTPInfos( $sav_FTPInfos);
$this->setError( JText::_( 'SITE_DEPLOY_DEPLOY_DIR_ROOT'));
return false;
}
}

if ( !empty( $config_dirs['alias_link'])) {

if ( !empty( $deploy_dir) && Jms2WinFolder::exists( $deploy_dir)) {

$dir = dirname( $config_dirs['alias_link']);
if ( !JFolder::exists( $dir)) {

JFolder::create( $dir);
}
if ( !$this->symlink( $deploy_dir, $config_dirs['alias_link'])) {
if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }
$this->_setNewFTPInfos( $sav_FTPInfos);
$this->setError( JText::_( 'SITE_DEPLOY_ALIAS_CREATION_ERROR2'));
return false;
}
}

if ( !empty( $cur_alias_link) && $config_dirs['alias_link'] != $cur_alias_link) {

if (@unlink( $cur_alias_link)) {}
}
}

if ( !empty( $newDBInfo)) {
$error = array();

if ( (!empty( $newDBInfo['fromTemplateID']) || !empty( $newDBInfo['fromSiteID']))
&& !empty( $newDBInfo['toPrefix'])
)
{
if ( !empty( $deploy_dir)) {
$error = $this->duplicateDBandConfig( $enteredvalues, $newDBInfo, $id, $site_dir, $deploy_dir);
}
else {
$error = $this->duplicateDBandConfig( $enteredvalues, $newDBInfo, $id, $site_dir);
}
}

else {
if ( !empty( $deploy_dir)) {
$error = $this->duplicateDBandConfig( $enteredvalues, $newDBInfo, $id, $site_dir, $deploy_dir, false);
}
else {
$error = $this->duplicateDBandConfig( $enteredvalues, $newDBInfo, $id, $site_dir, null, false);
}
}
if ( !empty( $error)) {
if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }
$this->_setNewFTPInfos( $sav_FTPInfos);
$this->setError( implode( '<br/>', $error));
return false;
}
}



if ( !MultisitesHelper::isSymbolicLinks()) {
$deploy_dir = null;
}

else
{

if ( !isset( $deploy_dir)) {

$deploy_dir = JPath::clean( $site_dir);
$this->_deploy_dir = $deploy_dir; 
}

if ( !Jms2WinFolder::exists( $deploy_dir)) {

if ( !Jms2WinFolder::create( $deploy_dir)) {

if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }
$this->_setNewFTPInfos( $sav_FTPInfos);
$this->setError( JText::sprintf( 'SITE_DEPLOY_ERR', $deploy_dir));
return false;
}
}
}

$errors = $this->_deployLinks( $config_dirs, $id, $site_dir, $deploy_dir, $newDBInfo, $enteredvalues['domains'], $enteredvalues['indexDomains']);

$this->_checkEmptyFolders( $site_dir);
if ( !empty( $ftproot_perms)) { Jms2WinPath::chmod( $ftpInfos['toFTP_rootpath'], $ftproot_perms); }

if ( !empty( $errors)) {
$this->_setNewFTPInfos( $sav_FTPInfos);
$this->setError( implode( '</li><li>', $errors));
return false;
}
$this->_setNewFTPInfos( $sav_FTPInfos);
$this->setError( JText::_( 'Success' ));
return true;
}
} 

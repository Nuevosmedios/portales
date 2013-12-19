<?php
// file: site.php.
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
@include_once( dirname( __FILE__) .DIRECTORY_SEPARATOR. 'lettertree.php');
@include_once( dirname( dirname( __FILE__)) .DIRECTORY_SEPARATOR. 'libraries' .DIRECTORY_SEPARATOR. 'joomla' .DIRECTORY_SEPARATOR. 'jms2winfactory.php');

@include_once( dirname( __FILE__).'/site_full.php');
if ( !class_exists( 'SiteVariant')) {
class SiteVariant extends JObject {
function getJ15_Components( &$db) { return null; }
function getComponents_j25( &$db) { return null; }
}
}




class Site extends SiteVariant
{
var $parentSiteId = ''; 
var $id = ''; 
var $site_prefix = ''; 
var $site_alias = ''; 
var $siteComment = ''; 
var $status = ''; 
var $payment_ref = ''; 
var $expiration = ''; 
var $redirect1st = ''; 
var $ignoreMasterIndex= ''; 
var $owner_id = ''; 
var $sitename = ''; 
var $domains = array(); 
var $indexDomains = array(); 
var $fromTemplateID = ''; 
var $toSiteName = ''; 
var $toMetaDesc = ''; 
var $toMetaKeys = ''; 
var $shareDB = ''; 
var $toDBType = ''; 
var $toDBHost = ''; 
var $toDBName = ''; 
var $toDBUser = ''; 
var $toDBPsw = ''; 
var $toPrefix = ''; 
var $newAdminEmail = ''; 
var $newAdminPsw = ''; 
var $deploy_dir = ''; 
var $deploy_create = ''; 
var $alias_link = ''; 
var $media_dir = ''; 
var $images_dir = ''; 
var $templates_dir = ''; 
var $tmp_dir = ''; 
var $host = ''; 
var $db = ''; 
var $dbprefix = ''; 
var $user = ''; 
var $password = ''; 
var $toFTP_enable = ''; 
var $toFTP_host = ''; 
var $toFTP_port = ''; 
var $toFTP_user = ''; 
var $toFTP_psw = ''; 
var $toFTP_rootpath = ''; 
var $_success = false; 
var $_template = null; 
var $_newExtensions = null; 

static function &getInstance( $site_id)
{
static $instances;
if (!isset( $instances )) {
$instances = array();
}
if ( empty( $instances[$site_id]))
{
$site = new Site();
if ( $site_id == ':master_db:') {
if ( class_exists( 'Jms2WinFactory')) { $config = & Jms2WinFactory::getMasterConfig(); }
else { $config = & JFactory::getConfig(); }
$site->id = ':master_db:';

if ( method_exists( $config, 'getValue')) {
$site->dbtype = $config->getValue( 'config.dbtype');
$site->host = $config->getValue( 'config.host');
$site->db = $config->getValue( 'config.db');
$site->dbprefix = $config->getValue( 'config.dbprefix');
$site->user = $config->getValue( 'config.user');
$site->password = $config->getValue( 'config.password');
$site->sitename = $config->getValue( 'config.sitename');
}

else {
$site->dbtype = $config->get( 'dbtype');
$site->host = $config->get( 'host');
$site->db = $config->get( 'db');
$site->dbprefix = $config->get( 'dbprefix');
$site->user = $config->get( 'user');
$site->password = $config->get( 'password');
$site->sitename = $config->get( 'sitename');
}
if( defined( 'MULTISITES_MASTER_ROOT_PATH' )) {
$site->jpath_root = MULTISITES_MASTER_ROOT_PATH;
}
else {
$site->jpath_root = dirname( JPATH_MULTISITES);
}
}
else if ( $site_id == ':this_site:') {
$app = JFactory::getApplication();
$site->id = ':this_site:';
$site->dbtype = $app->getCfg( 'dbtype');
$site->host = $app->getCfg( 'host');
$site->db = $app->getCfg( 'db');
$site->dbprefix = $app->getCfg( 'dbprefix');
$site->user = $app->getCfg( 'user');
$site->password = $app->getCfg( 'password');
$site->sitename = $app->getCfg( 'sitename');
$site->jpath_root = JPATH_ROOT;
}
else {
$site->load( $site_id);
}
$instances[$site_id] = & $site;
}
return $instances[$site_id];
}

function Site()
{
$this->_success = false;
}


function isExpired()
{

if ( empty( $this->expiration)) {
return false;
}
$expiration = strtotime( $this->expiration);
$now = strtotime( 'now');
$expiration_str = strftime( '%Y-%m-%d', $expiration);
$now_str = strftime( '%Y-%m-%d', $now);
if ( $expiration_str < $now_str) {
return true;
}
return false;
}


function _countTables( $site_id, $forceRecount=false)
{
static $instances;
if (!isset( $instances )) {
$instances = array();
}
if ( empty( $instances[$site_id]) || $forceRecount)
{

$db = & Jms2WinFactory::getMultiSitesDBO( $site_id, true, false);
if ( empty( $db)) {
return null;
}
$dbprefix = str_replace('_' , '\_', $db->getPrefix());
$db->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
$tables = $db->loadResultArray();
if ( empty( $tables)) {
return null;
}
$instances[$site_id] = count( $tables);
}
return $instances[$site_id];
}


function &getTemplate( $templateID = null, $forceRefresh = false)
{
static $none = null;

if ( !empty( $this->_template) && !$forceRefresh) {

return $this->_template;
}

if ( empty( $templateID)) {
$templateID = $this->fromTemplateID;
}
if ( empty( $templateID) || $templateID == ':master_db:') {
return $none;
}
$this->_template = new Jms2WinTemplate();
$this->_template->load( $this->fromTemplateID);
return $this->_template;
}


function isNewExtensions( $forceCheck = false)
{

if ( !is_null( $this->_newExtensions) && !$forceCheck) {
return $this->_newExtensions;
}

$this->_newExtensions = false;

if ( defined( 'MULTISITES_REFRESH_DISABLED') && MULTISITES_REFRESH_DISABLED) {
return $this->_newExtensions; 
}

if ( empty( $this->fromTemplateID)) {
return $this->_newExtensions; 
}
if ( $this->fromTemplateID == ':master_db:') {

$fromCount = $this->_countTables( $this->fromTemplateID);
if ( empty( $fromCount)) {
return $this->_newExtensions; 
}
}
else {
$template = & $this->getTemplate();

$fromCount = $this->_countTables( $template->fromSiteID);
if ( empty( $fromCount)) {
return $this->_newExtensions; 
}
}

$toCount = $this->_countTables( $this->id);
if ( empty( $toCount) || $fromCount == $toCount) {
return $this->_newExtensions; 
}
$this->_newExtensions = true;
return $this->_newExtensions;
}


function load( $sitename)
{
$this->_success = false;
if ( empty( $sitename)) {
return $this->_success;
}
$this->id = $sitename;
$this->sitename = $sitename;

$this->site_dir = JPATH_MULTISITES .DS. $sitename;
$filename = $this->site_dir .DS. 'config_multisites.php';
if ( file_exists( $filename)) {}

else {
if ( class_exists( 'MultisitesLetterTree')) {

$lettertree_dir = MultisitesLetterTree::getLetterTreeDir( $sitename);
if( !empty( $lettertree_dir)) {
$site_dir = JPATH_MULTISITES.DIRECTORY_SEPARATOR.$lettertree_dir;
$filename = $site_dir.DIRECTORY_SEPARATOR.'config_multisites.php';
if ( file_exists( $filename)) {
$this->site_dir = $site_dir;
}
}
}
}
if ( file_exists( $filename))
{
include $filename;
if ( isset( $domains)) {
$this->domains = $domains;
}
if ( isset( $indexDomains)) {
$this->indexDomains = $indexDomains;
}


if ( empty( $deploy_dir) && !empty( $config_dirs['deploy_dir'])) {
$deploy_dir = $config_dirs['deploy_dir'];
}
if ( isset( $deploy_dir)) {
$this->deploy_dir = $deploy_dir;
}

if ( empty( $delete_dir) && !empty( $config_dirs['delete_dir'])) {
$delete_dir = $config_dirs['delete_dir'];
}
if ( isset( $delete_dir)) {
$this->delete_dir = $delete_dir;
}
if ( isset( $newDBInfo)) {
foreach( $newDBInfo as $key => $value) {
$this->$key = $value;
}
}

if ( isset( $userdefs) && is_array( $userdefs)) {
$this->userdefs = array();
foreach( $userdefs as $key => $value) {
$this->userdefs[$key] = base64_decode( $value);
}
}
}
if ( empty( $deploy_dir) && !empty( $this->deploy_dir)) {
$deploy_dir = $this->deploy_dir;
}

if ( !empty( $deploy_dir)) {

$c = substr( $deploy_dir, -1);
if ( $c == '\\' || $c== '/') {
$config = $deploy_dir . 'configuration.php';
}
else {
$config = $deploy_dir .DS. 'configuration.php';
}
$this->jpath_root = dirname( $config);

if ( !is_dir( $this->jpath_root.DS.'administrator')) {


@include( JPATH_MULTISITES.DS.'config_multisites.php');
if( defined( 'MULTISITES_MASTER_ROOT_PATH' )) {
$this->jpath_root = MULTISITES_MASTER_ROOT_PATH;
}
else {
$this->jpath_root = dirname( JPATH_MULTISITES);
}
}
}

else {
$config = $this->site_dir .DS. 'configuration.php';

@include( JPATH_MULTISITES.DS.'config_multisites.php');
if( defined( 'MULTISITES_MASTER_ROOT_PATH' )) {
$this->jpath_root = MULTISITES_MASTER_ROOT_PATH;
}
else {
$this->jpath_root = dirname( JPATH_MULTISITES);
}
}
if ( file_exists( $config)) {
$handle = fopen( $config, "r");
if ($handle) {
while (!feof($handle)) {
$line = fgets($handle, 4096);


$line = trim( $line);

if ( (strpos( $line, 'var') === false) 
&& (strpos( $line, 'public') === false) 
)
{}
else {

$line = trim( str_replace( array( 'var', 'public') , array( '', ''), $line));

$posEQ = strpos( $line, '=');
if ( $posEQ === false) {}
else {

$varname = trim( substr( $line, 0, $posEQ), " \t\n\r\0\x0B;\$=");

$value = trim( substr( $line, $posEQ+1), " \t\n\r\0\x0B;");
$value = trim( $value, "'\"");
if ( $varname == 'sitename') {
$this->sitename =
$this->toSiteName = $value;
}
else if ( $varname == 'MetaDesc') {
$this->toMetaDesc = $value;
}
else if ( $varname == 'MetaKeys') {
$this->toMetaKeys = $value;
}
else if ( $varname == 'dbtype') {
$this->dbtype = $value;
}
else if ( $varname == 'host') {
$this->host = $value;
}
else if ( $varname == 'db') {
$this->db = $value;
}
else if ( $varname == 'dbprefix') {
$this->dbprefix = $value;
}
else if ( $varname == 'user') {
$this->user = $value;
}
else if ( $varname == 'password') {
$this->password = $value;
}
else {
$key = 'jconfig_'.$varname;
$this->$key = $value;
}
}
}
}
fclose($handle);
$this->_success = true;

}
}
return $this->_success;
}


function loadArray( $sitename)
{
if ( !$this->load( $sitename)) {
return null;
}

$enteredvalues = $this->getProperties();
foreach ( $enteredvalues as $key => $value) {
if ( empty( $value)) {
unset( $enteredvalues[$key]);
}
}
return $enteredvalues;
}


static function is_Site( $aName, $path=null)
{
$rc = false;
if ( empty( $path)) {
$path = JPATH_MULTISITES .DS. $aName;
}
if ( is_dir( $path))
{

$config = $path .DS. 'configuration.php';
if ( file_exists( $config)) {
return true;
}

$config = $path .DS. 'config_multisites.php';
if ( file_exists( $config)) {
return true;
}


$index = $path .DS. 'index.php';
if ( file_exists( $index)) {
$str = file_get_contents( $index);


if ( preg_match( '/MULTISITES_DIR/i', $str, $matches)) {
return true;
}
}
}
return false;
}


function getFromSiteID()
{

if ( isset( $this->fromSiteID)) {
return $this->fromSiteID;
}
$fromSiteID = null;

if ( !empty( $this->toPrefix)
&& !empty( $this->fromTemplateID)) {

if ( $this->fromTemplateID == ':master_db:') {
$fromSiteID = ':master_db:';
}

else {
$template = & $this->getTemplate();

if ( !empty( $template) && !empty( $template->fromSiteID)) {
$fromSiteID = $template->fromSiteID;
}
}
}
$this->fromSiteID = $fromSiteID;
return $fromSiteID;
}


function withUserSharing()
{

if ( !isset( $this->_withUserSharing)) {

$this->_withUserSharing = false;

$db = & Jms2WinFactory::getMultiSitesDBO( $this->id, true, false);
if ( !empty( $db)) {

if ( MultisitesDatabase::_isView( $db, $db->getPrefix().'users')) {
$this->_withUserSharing = true;
}
}
}
return $this->_withUserSharing;
}


function getThisUserTablename()
{

if ( !isset( $this->_thisUserTablename)) {

$this->_thisUserTablename = '';

$db = & Jms2WinFactory::getMultiSitesDBO( $this->id, true, false);
if ( !empty( $db)) {
$path = array( $db->_dbname, $db->getPrefix().'users');
$path = MultisitesDatabase::backquote( $path);
$this->_thisUserTablename = implode( '.', $path);
}
}
return $this->_thisUserTablename;
}


function getFromUserTablename()
{

if ( !isset( $this->_fromUserTablename)) {

$this->_fromUserTablename = '';

$db = & Jms2WinFactory::getMultiSitesDBO( $this->id, true, false);
if ( !empty( $db)) {

$tablename = $db->getPrefix().'users';
if ( MultisitesDatabase::_isView( $db, $tablename)) {
$fromName = MultisitesDatabase::getViewFrom( $db, $tablename);
if ( !empty( $fromName)) {

$pos = strpos( $fromName, '`.`');

if ( $pos === false) {

$path = array( $db->_dbname);
$path = MultisitesDatabase::backquote( $path);
$path[] = $fromName;
$this->_fromUserTablename = implode( '.', $path);
}

else {
$this->_fromUserTablename = $fromName;
}
}

else {
$fromSiteID = $this->getFromSiteID();
if ( !empty( $fromSiteID)) {
$fromdb = & Jms2WinFactory::getMultiSitesDBO( $fromSiteID, true, false);
if ( !empty( $fromdb)) {
$path = array( $fromdb->_dbname);
$path = MultisitesDatabase::backquote( $path);
$path[] = MultisitesDatabase::backquote( $fromdb->getPrefix().'users');
$this->_fromUserTablename = implode( '.', $path);
}
}
}
}
}
}
return $this->_fromUserTablename;
}


function getJoomlaFileVersion()
{
$jpath_root = $this->jpath_root;
$j15_versdir = $jpath_root.'/libraries/joomla/version.php';
$j16_versdir = $jpath_root.'/libraries/joomla/version.php';
$j17_versdir = $jpath_root.'/includes/version.php';
$j25_versdir = $jpath_root.'/libraries/cms/version/version.php';
if ( file_exists( $j15_versdir)) { $j_versdir = $j15_versdir; }
else if ( file_exists( $j16_versdir)) { $j_versdir = $j16_versdir; }
else if ( file_exists( $j17_versdir)) { $j_versdir = $j17_versdir; }
else if ( file_exists( $j25_versdir)) { $j_versdir = $j25_versdir; }

$jversion = new stdclass;
if ( file_exists( $j_versdir)) {
$handle = fopen( $j_versdir, "r");
if ($handle) {
while (!feof($handle)) {
$line = fgets($handle, 4096);



$line = trim( $line);

$pos = strpos( $line, 'var'); 
if ( $pos === false) {
$pos = strpos( $line, 'public'); 
}
if ( $pos === false) { $leading = null;}
else {
$leading = substr( $line, 0, $pos);
$leading = trim( $leading);
}

if ( $pos === false) {}

else if ( strlen( $leading) > 0) {} 

else {

$line = trim( str_replace( array( 'var', 'public') , array( '', ''), $line));

$posEQ = strpos( $line, '=');
if ( $posEQ === false) {}
else {

$varname = trim( substr( $line, 0, $posEQ), " \t\n\r\0\x0B;\$=");

$value = trim( substr( $line, $posEQ+1), " \t\n\r\0\x0B;");
$value = trim( $value, "'\"");
$jversion->$varname = $value;
}
}
}
fclose($handle);
}
}
$this->jversion = $jversion;
if ( empty( $jversion->RELEASE) || empty( $jversion->DEV_LEVEL)) {
return '';
}
return $jversion->RELEASE . '.' . $jversion->DEV_LEVEL;
}


function getExtensionInfo_file( &$dmrow, $infoTasks=array())
{
if ( $dmrow->extension_id == 700) {
$dmrow->code_version = $this->getJoomlaFileVersion();
if ( empty( $infoTasks) && !empty( $dmrow->jpath_root)) {
$dmrow->jpath_extension = $dmrow->jpath_root;
}
}
}


function getExtensionInfo( &$dmrow, $infoTasks=array())
{
$fn = 'getExtensionInfo_'.$dmrow->type;
if ( method_exists( $this, $fn)) {
$this->$fn( $dmrow, $infoTasks);
}
}


function getDBInfo_checkSchema( $db, $dmrow, $enteredvalues)
{


include_once( dirname( dirname( __FILE__)).'/libraries/cms/schema/database.php');
return MultisitesSchemaDatabase::checkSchema( $this, $db, $dmrow, $enteredvalues);
}


function getDBInfo_fixSchema( $db, $dmrow, $enteredvalues)
{


include_once( dirname( dirname( __FILE__)).'/libraries/cms/schema/database.php');
return MultisitesSchemaDatabase::fixSchema( $this, $db, $dmrow, $enteredvalues);
}


function getDBInfo_fixUncheckedSchema( $db, $dmrow, $enteredvalues)
{


include_once( dirname( dirname( __FILE__)).'/libraries/cms/schema/database.php');
return MultisitesSchemaDatabase::fixUncheckedSchema( $this, $db, $dmrow, $enteredvalues);
}


function doAction( $subaction=null, $enteredvalues=array())
{
$results = array();

$results['site'] = $this;
$results['host'] = $this->host;
$results['db'] = $this->db;
$results['dbprefix'] = $this->dbprefix;
$results['user'] = $this->user;
$results['password'] = $this->password;
$results['datamodel'] = array();
$extension_id = !empty( $enteredvalues['extension_id']) ? $enteredvalues['extension_id'] : null;


$db = & Jms2WinFactory::getMultiSitesDBO( $this->id, true, false);
if ( !empty( $db)) {

$dbprefix = str_replace('_' , '\_', $db->getPrefix());
$db->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'schemas\'' );

$dbschema = $db->loadResultArray();
if ( empty( $dbschema)) {
$rows = array();
if ( empty( $extension_id) || $extension_id == -15) {

$row = new stdclass();
$row->extension_id = -15;
$row->name = 'Joomla files';
$row->type = 'file';
$row->element = 'joomla';

$row->code_version = $this->getJoomlaFileVersion();
$row->version_id = '1.5';
if ( !empty( $row->code_version) && version_compare( JVERSION, '1.5') == 0) { $row->version_id = $row->code_version; }
$rows[] = $row;
}
$components = $this->getJ15_Components( $db, $extension_id);
if ( !empty( $components)) {
$rows = array_merge( $rows, $components);
}
}

else {
if ( !empty( $extension_id)) { $where = ' WHERE s.extension_id='.$extension_id; }
else { $where = ''; }

$query = 'SELECT s.*, e.name, e.type, e.element, e.folder, e.client_id FROM #__schemas as s LEFT JOIN #__extensions as e ON e.extension_id=s.extension_id '
. $where
.' ORDER BY s.extension_id';
$db->setQuery( $query);
$rows = $db->loadObjectList();
$components = $this->getComponents_j25( $db, $extension_id);




if ( empty( $rows) && empty( $extension_id)) {
$row = new stdclass();
$row->extension_id = 700;
$row->name = 'Joomla files';
$row->type = 'file';
$row->element = 'joomla';
$row->version_id = '1.6';
$rows[] = $row;
if ( !empty( $components)) {
$rows = array_merge( $rows, $components);
}
}

else {
if ( !empty( $components)) {
$rows = array_merge( $rows, $components);
}
}
}

for( $i=0; $i<count( $rows); $i++) {
$this->getExtensionInfo( $rows[$i]);
$rows[$i]->subActionResult = null;
if ( !empty( $subaction)) {
$fn = 'getDBInfo_'.$subaction;
if ( method_exists( $this, $fn)) {
$rows[$i]->subActionResult = $this->$fn( $db, $rows[$i], $enteredvalues);
}
}
}
$results['datamodel'] = $rows;
}
return $results;
}


function preprocessGetDBInfo( $enteredvalues)
{
$row = $this->doAction();
if ( !empty( $row) && !empty( $row['datamodel'])) {
$this->preprocess_datamodel = $row['datamodel'];
}
}


function getDBInfo( $enteredvalues)
{
return $this->doAction( 'checkSchema', $enteredvalues);
}


function fixDB( $enteredvalues)
{
return $this->doAction( 'fixSchema', $enteredvalues);
}


function fixUncheckedDB( $enteredvalues)
{
return $this->doAction( 'fixUncheckedSchema', $enteredvalues);
}


function updaterAction( $action, $enteredvalues)
{
include_once( dirname( __FILE__).'/updater.php');
if ( class_exists( 'MultisitesUpdater')) {
if ( method_exists( 'MultisitesUpdater', $action)) {
return MultisitesUpdater::$action( $this->jpath_root, $enteredvalues);
}
}
return false;
}


function deleteJoomlaFiles()
{
include_once( dirname( dirname( __FILE__)).'/libraries/joomla/filesystem/jms2winfolder.php');
return Jms2WinFolder::deleteJoomlaFiles( $this->jpath_root);
}


function deleteSymLinks()
{
include_once( dirname( dirname( __FILE__)).'/libraries/joomla/filesystem/jms2winfolder.php');
return Jms2WinFolder::deleteSymLinks( $this->jpath_root);
}


function refreshSymLinks()
{
include_once( dirname( dirname( __FILE__)).'/libraries/joomla/filesystem/jms2winfolder.php');

$results = Jms2WinFolder::deleteSymLinks( $this->jpath_root);

$enteredvalues = get_object_vars( $this);

require_once( dirname( dirname( __FILE__)).'/models/manage.php');
$model = new MultisitesModelManage();
if ( !$model->deploySite( $enteredvalues)) {
$msg = $model->getError();
if ( empty( $msg)) {
$results[] = $msg;
}
}
return $results;
}
} 

<?php
// file: jms2winfactory.php.
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


@include_once( JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_multisites' .DIRECTORY_SEPARATOR. 'multisites_path.cfg.php');

if ( !defined( 'JPATH_MULTISITES')) {
define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}
jimport( 'joomla.filesystem.file');
@include_once( JPATH_ROOT.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_multisites' .DIRECTORY_SEPARATOR. 'classes' .DIRECTORY_SEPARATOR. 'lettertree.php');

require_once( dirname( __FILE__).'/database/j2windb.php');



class Jms2WinRegistry extends JRegistry
{

public function getValue($path, $default = null)
{

if ( !method_exists( 'JRegistry', 'getValue')) {
$parts = explode( '.', $path);
if ( count( $parts) == 2 && $parts[0]=='config') { return parent::get( $parts[1], $default); }
$result = null;
while ( !empty( $parts) && empty( $result)) {
$result = parent::get( implode( '.', $parts), $default);
array_shift( $parts);
}
return $result;
}

return parent::getValue( $path, $default);
}

public function setValue($path, $value)
{

if ( !method_exists( 'JRegistry', 'setValue')) {
$parts = explode('.', $path);
if (count($parts) > 1)
{
unset($parts[0]);
$path = implode('.', $parts);
}
return parent::set( $path, $value);
}
return parent::setValue( $path, $value);
}
}




if ( !class_exists( 'Jms2WinError')) {
jimport('joomla.error.error');

if ( method_exists( 'JError', 'isError')) {
class Jms2WinError extends JError {}
}

else {
class Jms2WinError extends JError {
public static function isError(& $object)
{

return $object instanceof Exception;
}
}
}
}



class Jms2WinFactory
{

public static function &getDBO()
{
static $instance;
if ( !isset( $instance)) {
$instance = new J2WinDB( JFactory::getDBO());
}
return $instance;
}


static function &_createMasterConfig( $site_id, $file, $type = 'PHP')
{
jimport('joomla.registry.registry');
jimport('joomla.filter.filterinput');

if ( empty( $site_id)) {
$MULTISITES_FORCEMASTER = true;
}

$new_config_class = 'JConfig_' . JFilterInput::getInstance()->clean( $site_id, 'alnum');
if ( class_exists( $new_config_class)) {

}
else {

jimport('joomla.filesystem.file');
$data = JFile::read($file);
if ( $data === false) {
jexit( "jms2winfactory.php: Unable to read configuration file [$file]");
}
if ( empty( $data)) {
jexit( "jms2winfactory.php: Empty configuration file content [$file]");
}

$script = str_replace( 'JConfig', $new_config_class, $data);
if ( empty( $script)) {
jexit( "jms2winfactory.php: Empty configuration SCRIPT for file [$file]");
}
else {

$p1 = strpos( $script, '?'.'>');
if ( $p1 === false) {
$script .= "\n"
. '?' . '>'
. "\n";
}
}
eval('?>' . $script . '<?php ');


if ( !class_exists( $new_config_class)) {

if ( defined( 'JPATH_ROOT') && defined( 'JPATH_BASE')
&& JPATH_ROOT != JPATH_BASE
)
{

jexit( "jms2winfactory.php: Class [$new_config_class] not found in configuration file [$file]<br />Data = [" . htmlspecialchars($data) . "]<br />Script = [" . htmlspecialchars( $script) . "]");
}
else {
jexit( "jms2winfactory.php: Class [$new_config_class] not found in configuration file [$file]");
}
}
}

$config = new $new_config_class();

$registry = new Jms2WinRegistry('config');

$registry->loadObject($config);
return $registry;
}


static function &getThisConfig($file = null, $type = 'PHP')
{
static $instance;
if (!is_object($instance))
{
if ($file === null) {


$file= dirname( dirname( dirname( dirname( dirname( dirname( __FILE__)))))). DS. 'configuration.php';
}

$instance =& Jms2WinFactory::_createMasterConfig( '', $file, $type);
}
return $instance;
}


static function &getMasterConfig($file = null, $type = 'PHP')
{
static $instance;
if (!is_object($instance))
{
if ($file === null) {


$file= dirname( dirname( dirname( dirname( dirname( dirname( __FILE__)))))). DS. 'configuration.php';

if ( defined( 'MULTISITES_MASTER_ROOT_PATH')) {
$file = MULTISITES_MASTER_ROOT_PATH .DS. 'configuration.php';

if ( !JFile::exists( $file)) {
$file = JPATH_ROOT .DS. 'configuration.php';
}
}
else {
$file = JPATH_ROOT .DS. 'configuration.php';
}
}

$instance =& Jms2WinFactory::_createMasterConfig( '', $file, $type);
}
return $instance;
}


static function &getSlaveConfig( $site_id, $type = 'PHP')
{
static $instances;
if (!isset( $instances )) {
$instances = array();
}
if ( empty( $instances[$site_id]))
{
jimport('joomla.filesystem.file');

$site_dir = JPATH_MULTISITES .DS. $site_id ;
$filename = $site_dir .DS. 'config_multisites.php';
if ( !JFile::exists( $filename)) {

if ( class_exists( 'MultisitesLetterTree')) {

$lettertree_dir = MultisitesLetterTree::getLetterTreeDir( $site_id);
if( !empty( $lettertree_dir)) {
$site_dir = JPATH_MULTISITES.DIRECTORY_SEPARATOR.$lettertree_dir;
$filename = $site_dir.DIRECTORY_SEPARATOR.'config_multisites.php';
if ( !file_exists( $filename)) {
$instances[$site_id] = null;
return $instances[$site_id];
}
}
}
}

@include( $filename);

if ( empty( $deploy_dir) && !empty( $config_dirs['deploy_dir'])) {
$deploy_dir = $config_dirs['deploy_dir'];
}
if ( !empty( $deploy_dir)) {

$filename = $deploy_dir .DS. 'configuration.php';

if ( !JFile::exists( $filename)) {

$filename = $site_dir .DS. 'configuration.php';
}
}
else {
$filename = $site_dir .DS. 'configuration.php';
}

if ( !JFile::exists( $filename)) {
$instances[$site_id] = null;
return $instances[$site_id];
}

$instances[$site_id] =& Jms2WinFactory::_createMasterConfig( $site_id, $filename, $type);
}
return $instances[$site_id];
}


static function &getMultiSitesConfig( $site_id = null)
{
if ( empty( $site_id) || $site_id == ':master_db:') {
return Jms2WinFactory::getMasterConfig();
}
return Jms2WinFactory::getSlaveConfig( $site_id);
}


static function &_getSingleDBInstance( $options = array() )
{

static $instances;
if (!isset( $instances )) {
$instances = array();
}
$driver = array_key_exists( 'driver', $options) ? $options['driver'] : 'mysql';
$driver = preg_replace('/[^A-Z0-9_\.-]/i', '', $driver);
$host = array_key_exists('host', $options) ? $options['host'] : 'localhost';
$database = array_key_exists('database', $options) ? $options['database'] : null;
$user = array_key_exists('user', $options) ? $options['user'] : '';
$password = array_key_exists('password',$options) ? $options['password'] : '';
$prefix = array_key_exists('prefix', $options) ? $options['prefix'] : 'jos_';
$select = array_key_exists('select', $options) ? $options['select'] : true;

if (empty($instances[$driver]))
{
$path = dirname( __FILE__)
.DS.'database'
.DS.'database'.DS.$driver.'.php';
if (file_exists($path)) {
require_once($path);
} else {
JError::raiseError(500, JTEXT::_('Unable to load Database Driver:') .$driver);

return null;
}
$adapter = 'MultisitesDatabase'.$driver;

if ( version_compare( JVERSION, '3.0') >= 0
&& method_exists( 'J2WinDatabase', 'getAdapter'))
{
$instance = J2WinDatabase::getAdapter( $adapter, $options);
}

else if ( method_exists( 'JDatabase', 'getAdapter')) {
$instance = JDatabase::getAdapter( $adapter, $options);
}
else if ( version_compare( JVERSION, '1.7') >= 0) {
JError::raiseError(500, 'JDatabase patch is not installed. Please go in <a href="index.php?option=com_multisites&task=checkpatches">check patches</a> and install the patches');

return null;
}

if ( empty( $instance)) {
$instance = new $adapter($options);
}
if ( $error = $instance->getErrorMsg() )
{
$instance->setErrorInfo( 2, $error . " DB [$database]");
return $instance;
}
$instance->_user = $user;
$instances[$driver] = & $instance;
}

else {
$instance = & $instances[ $driver];
$success = true;

if ( $instance->_dbserver == $host && $instance->_user == $user) {

if ( $instance->_dbname == $database) {

$instance->setErrorInfo( 0, '');
}

else {

if ( !$instance->connected()) {
$success = $instance->setNewConnection( $host, $user, $password, $select, $database);
}
else {
if ( $select ) {
try { $success = $instance->select($database); }
catch (Exception $e) { $success = false; }
}
}
}
}

else {

$success=$instance->setNewConnection( $host, $user, $password, $select, $database);
if ( $success) {
$instance->_dbserver = $host;
$instance->_user = $user;
}
}
if ( $success) {
$instance->setPrefix( $prefix);
}
}
return $instance;
}


static function &_createDBO_BasedOnConfig( $conf, $tempConnection=false, $abortOnError=true)
{
jimport( 'joomla.database.database');
jimport( 'joomla.database.table' );
$host = $conf->getValue('config.host');
$user = $conf->getValue('config.user');
$password = $conf->getValue('config.password');
$database = $conf->getValue('config.db');
$prefix = $conf->getValue('config.dbprefix');
$driver = $conf->getValue('config.dbtype');
$debug = $conf->getValue('config.debug');
$options = array ( 'driver' => $driver,
'host' => $host,
'user' => $user,
'password' => $password,
'database' => $database,
'prefix' => $prefix
);
if ( $tempConnection) {
$db =& Jms2WinFactory::_getSingleDBInstance( $options );
}
else {
$db =& J2WinDatabase::getInstance( $options );
}
if ( Jms2WinError::isError($db) ) {
jexit('Database Error: ' . $db->toString() );
}

if ( method_exists( $db, 'connect')) { $db->connect(); }
if ( !$db->connected()) {
if ( $abortOnError) {
$optionMsg = '';
foreach( $options as $key => $value) {

if ( $key == 'password') {}
else {
$optionMsg .= '<br/>' . "$key => $value";
}
}

JError::raiseError(500 , 'Jms2WinFactory::_createDBO_BasedOnConfig: Could not connect to database <br/>' . 'DB Error number: '.$db->getErrorNum().' - '.$db->getErrorMsg() . $optionMsg );
}
$db->debug( $debug );
return $db;
}
$db->debug( $debug );
$db->_dbserver = $host;
$db->_dbname = $database;
return $db;
}


static function &getMasterDBO( $tempConnection=false, $abortOnError=true)
{
static $instance;
if (!is_object($instance))
{

$conf =& Jms2WinFactory::getMasterConfig();
$debug = $conf->getValue('config.debug');
if ( $tempConnection) {
$db = & Jms2WinFactory::_createDBO_BasedOnConfig( $conf, $tempConnection);
if ( $db->getErrorNum() > 0) {
$none = null;
return $none;
}
$db->debug($debug);
return $db;
}
$instance = & Jms2WinFactory::_createDBO_BasedOnConfig( $conf);
$instance->debug($debug);
}
return $instance;
}


static function &getMultiSitesDBO( $aSite_id = null, $tempConnection=false, $abortOnError=true)
{
static $this_db;

if ( empty( $aSite_id)) {

$site_id = JRequest::getString('site_id', null);

if ( $site_id=='Array') {
$arr = JRequest::getVar('site_id', null, 'get', 'array');
if ( !empty( $arr) && is_array($arr) && count( $arr)>0) {
$site_id = $arr[0];
}
}
}
else {
if ( is_array( $aSite_id)) {
$site_id = $aSite_id[0];
}
else {
$site_id = $aSite_id;
}
}
if ( empty( $site_id) || $site_id == ':master_db:' || $site_id == '[unselected]') {
return Jms2WinFactory::getMasterDBO( $tempConnection);
}
else if ( $site_id == ':this_site:') {
$this_db = JFactory::getDBO();

if ( method_exists( $this_db, 'connect')) { $this_db->connect(); }
return $this_db;
}
return Jms2WinFactory::getSlaveDBO( $site_id, $tempConnection, $abortOnError);
}


static function &getSlaveDBO( $site_id, $tempConnection=false, $abortOnError=true)
{
static $instances;
static $none = null;
if (!isset( $instances )) {
$instances = array();
}
if ( empty( $instances[$site_id]))
{

$conf =& Jms2WinFactory::getSlaveConfig( $site_id);
if ( empty( $conf)) {
$instances[$site_id] = null;
return $instances[$site_id];
}
$debug = $conf->getValue('config.debug');
if ( $tempConnection) {
$db = & Jms2WinFactory::_createDBO_BasedOnConfig( $conf, $tempConnection, $abortOnError);
if ($db->getErrorNum() > 0) {

$instances[$site_id] = null;
return $instances[$site_id];
}
$db->debug($debug);
return $db;
}
$instances[$site_id] = & Jms2WinFactory::_createDBO_BasedOnConfig( $conf, false, $abortOnError);
$instances[$site_id]->debug($debug);
}
return $instances[$site_id];
}


static function &getSlaveRootPath( $site_id)
{
$filename = JPATH_MULTISITES .DS. $site_id .DS. 'config_multisites.php';
@include( $filename);
if ( isset( $config_dirs)) {
if ( !empty( $config_dirs['deploy_dir'])) {
return $config_dirs['deploy_dir'];
}
}

else {
if ( class_exists( 'MultisitesLetterTree')) {

$lettertree_dir = MultisitesLetterTree::getLetterTreeDir( $site_id);
if( !empty( $lettertree_dir)) {
$site_dir = JPATH_MULTISITES.DIRECTORY_SEPARATOR.$lettertree_dir;
$filename = $site_dir.DIRECTORY_SEPARATOR.'config_multisites.php';
@include( $filename);
if ( isset( $config_dirs)) {
if ( !empty( $config_dirs['deploy_dir'])) {
return $config_dirs['deploy_dir'];
}
}
}
}
}

return JPATH_ROOT;
}


static function &getMasterUser($id = null)
{
jimport('joomla.user.user');
if(is_null($id))
{
$session =& JFactory::getSession();
$instance =& $session->get('user');
if (!is_a($instance, 'JUser')) {


$db =& JFactory::getDBO();
$saveDB = $db;
$dbMaster =& Jms2WinFactory::getMasterDBO();
$db = $dbMaster; 
$instance =& JUser::getInstance();

$db = $saveDB;
}
}
else
{


$db =& JFactory::getDBO();
$saveDB = $db;
$dbMaster =& Jms2WinFactory::getMasterDBO();
$db = $dbMaster; 
$instance =& JUser::getInstance($id);

$db = $saveDB;
}
return $instance;
}


function import( $multisites_path,
$original_path,
$filename,
$searchReplace = array(),
$writeResult = true)
{
jimport('joomla.filesystem.file');

if ( !empty( $searchReplace)) {
$force_Recompute = false;

$jmsFilename = $multisites_path .DS. 'multisites.' . $filename;



$version = new JVersion();
$cur_joomlaversion = $version->getShortVersion();
$config_filename = $multisites_path .DS. 'multisites.cfg.php';
@include( $config_filename);

if ( empty( $joomla_vers)) {

$joomla_vers = $cur_joomlaversion;
$converted_files = array();
$force_Recompute = true;
}

else {

if ( $joomla_vers != $cur_joomlaversion) {

$joomla_vers = $cur_joomlaversion;
$converted_files = array();
$force_Recompute = true;
}
else {


if ( empty( $converted_files[$jmsFilename])
|| $converted_files[$jmsFilename] != $cur_joomlaversion
)
{

$force_Recompute = true;
}
}
}

if ( !$force_Recompute && JFile::exists( $jmsFilename)) {
require_once( $jmsFilename);
}

else {
$search = array_keys( $searchReplace);
$replace = array_values( $searchReplace);
$fullname = $original_path .DS. $filename;
$content = JFile::read( $fullname);
$content = str_replace( $search, $replace, $content);
if ( !empty( $content)) {
if ( $writeResult) {
$success = JFile::write( $jmsFilename, $content);
}

if ( $writeResult && $success) {

require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'utils.php');
$converted_files[$jmsFilename] = $cur_joomlaversion;
$config = "<?php\n";
$config .= "if( !defined( '_JEXEC' )) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); \n\n";
$config .= "\$joomla_vers     = '$joomla_vers';\n";
$config .= "\$converted_files = array( " . MultisitesUtils::CnvArray2Str( '     ', $converted_files) . ");\n";
$config .= "?>";
JFile::write( $config_filename, $config);

require_once( $jmsFilename);
}

else {

$str = trim( $content);
if ( substr( $str, 0, 5) == '<?php') {
if ( substr( $str, -2) == '?>') {
eval('?>' . $content . '<?php ');
}
else {
eval('?>' . $content);
}
}
else {
eval( $content);
}
}
}
}
}

else {
$fullname = $original_path .DS. $filename;
require_once( $fullname);
}
}


static function getDBOVersion( $site_id = ':master_db:')
{
$rows = array();
if ( $site_id == ':master_db:') {
$db =& Jms2WinFactory::getMasterDBO();
}
else {
$db =& Jms2WinFactory::getSlaveDBO( $site_id);
}
if ( empty( $db)) {
return '';
}
$query = "SELECT Version() AS version";
$db->setQuery( $query );
$db->setQuery( $query );
$version = $db->loadResult();
return $version;
}


static function isCreateView( $site_id = ':master_db:')
{
$result = false;
if ( empty( $site_id)) {
$site_id = ':master_db:';
}
require_once( JPATH_COMPONENT .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
$versStr = Jms2WinFactory::getDBOVersion( $site_id);
if ( !empty( $versStr)) {

$vers = explode( '-', $versStr);
if ( !empty( $vers)) {

$version = $vers[0];
$vers = explode( '.', $version);
if ( !empty( $vers)) {
$v = intval( $vers[0]);

if ( $v >= 5) {
$result = true;
}

}
}
}
return $result;
}


static function getSiteDomainName( $site_id, $master_domain='', &$_host_)
{

if ( $site_id == ':master_db:') {

if ( method_exists('MultiSites','isLocalHost') && MultiSites::isLocalHost()) {
$domain = '';
}
else {
if ( empty( $master_domain)) {
if ( defined( 'MULTISITES_MASTER_DOMAIN')) {
$master_domain = MULTISITES_MASTER_DOMAIN;
}
}
$domain = $master_domain;
}
}
else {
require_once( dirname( dirname( dirname( __FILE__))) .DS. 'classes' .DS. 'site.php');
$site = & Site::getInstance( $site_id);
if ( !empty( $site->indexDomains)) {
$domain = $site->indexDomains[0];
}
else if ( !empty( $site->domains)) {
$domain = $site->domains[0];
}
}


if ( !empty( $domain)) {
$pos = strpos( $domain, 'http://');
if ( $pos === false) {
$pos = strpos( $domain, 'https://');
if ( $pos === false) {
$domain = 'http://' . $domain;
}
}
}

if ( !empty( $domain)) {
$domain = rtrim( $domain, '/') . '/';
}
if ( method_exists('MultiSites','isLocalHost') && MultiSites::isLocalHost()) {
$pos = strpos( $domain, '://');
if ( $pos === false) {
$_host_ = '&_host_='.rtrim( $domain, '/');
}
else {
$_host_ = '&_host_='.rtrim( substr( $domain, $pos+3), '/');
}
$domain = '';
}
return $domain;
}


static function isGeoLocalisation()
{
if ( defined( 'MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_COUNTRY')
|| defined( 'MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_CITY')
|| defined( 'MULTISITES_GEOIP_MAXMIND_ICC_ENABLED')
|| defined( 'MULTISITES_GEOIP_MAXMIND_CITY_ENABLED')

|| defined( 'MULTISITES_GEOIP_QUOVA_APIKEY')
|| defined( 'MULTISITES_GEOIP_QUOVA_SECRET')
)
{
return true;
}
return false;
}


public static function &getXMLParser($type = '', $options = array())
{
$doc = null;

if ( method_exists( 'JFactory', 'getXMLParser')) {
$doc = JFactory::getXMLParser( $type, $options);
}

else if ( strtolower($type) == 'simple') {

require_once( dirname( __FILE__).'/utilities/simplexml.php');
$doc = new JSimpleXML;
}
return $doc;
}
} 

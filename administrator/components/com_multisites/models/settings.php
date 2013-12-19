<?php
// file: settings.php.
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
jimport('joomla.filesystem.file');
if ( !defined( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR')) {
define( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR',
JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'application' .DS. 'component' .DS. 'model2win.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'models' .DS. 'registration.php');




class MultisitesModelSettings extends JModel2Win
{

var $_modelName = 'settings';

var $id = 'fake';
var $product_id = null;
var $website_count = null;
var $website_quota = null;

static function getMaxmind_ICC_Date() { $filename = JPATH_MULTISITES_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'geoip.dat';
if ( JFile::exists( $filename)) { return filemtime( $filename); }
return '';
}
static function getMaxmind_City_Date() { $filename = JPATH_MULTISITES_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'geolitecity.dat';
if ( JFile::exists( $filename)) { return filemtime( $filename); }
return '';
}


function &getSettings()
{
$this->quota_url =& Edwin2WinModelRegistration::getURL();
$regInfo = Edwin2WinModelRegistration::getRegistrationInfo();
if ( empty( $regInfo) || empty( $regInfo['product_id'])) {
$this->setError( JText::_( 'You must register the product to access the settings'));
return false;
}
$this->product_id = $regInfo['product_id'];
$result = $this->getWebsiteQuota( $this->product_id);
if ( !empty( $result) && $result !== false && is_array( $result)) {
$this->website_count = $result['website_count'];
$this->website_quota = $result['website_quota'];
}
else {
$this->website_count = 0;
$this->website_quota = 0;
}

$this->jpath_multisites = defined( 'JPATH_MULTISITES') ? JPATH_MULTISITES : '';
$this->dir_rights = defined( 'MULTISITES_DIR_RIGHTS') ? MULTISITES_DIR_RIGHTS : 0755;
$this->tld_parsing = defined( 'MULTISITES_TLD_PARSING') ? MULTISITES_TLD_PARSING : false;
$this->letter_tree = defined( 'MULTISITES_LETTER_TREE') ? MULTISITES_LETTER_TREE : false;
$this->refresh_disabled = defined( 'MULTISITES_REFRESH_DISABLED') ? MULTISITES_REFRESH_DISABLED : false;
$this->cookie_domain = defined( 'MULTISITES_COOKIE_DOMAIN') ? MULTISITES_COOKIE_DOMAIN : false;
$this->ignore_ext_version = defined( 'MULTISITES_IGNORE_MANIFEST_VERSION') ? MULTISITES_IGNORE_MANIFEST_VERSION : false;
$this->db_grant_host = defined( 'MULTISITES_DB_GRANT_HOST') ? MULTISITES_DB_GRANT_HOST : '';
$this->db_root_user = defined( 'MULTISITES_DB_ROOT_USER') ? MULTISITES_DB_ROOT_USER : '';
$this->db_root_psw = defined( 'MULTISITES_DB_ROOT_PSW') ? MULTISITES_DB_ROOT_PSW : '';
$this->joomla_download_url = defined( 'MULTISITES_JOOMLA_DOWNLOAD_URL') ? MULTISITES_JOOMLA_DOWNLOAD_URL : '';
$this->home_dir = defined( 'MULTISITES_HOME_DIR') ? MULTISITES_HOME_DIR : '';
$this->public_dir = defined( 'MULTISITES_PUBLIC_DIR') ? MULTISITES_PUBLIC_DIR : '';
$this->config_prefix_dir = defined( 'MULTISITES_CONFIG_PREFIX_DIR') ? MULTISITES_CONFIG_PREFIX_DIR : '';
$this->autoinc_dir = defined( 'MULTISITES_AUTOINC_DIR') ? MULTISITES_AUTOINC_DIR : '';
$this->elt_site_text = !empty( $GLOBALS['MULTISITES_ELT_SITE']['text']) ? $GLOBALS['MULTISITES_ELT_SITE']['text'] : array();
$this->elt_site_hidden = !empty( $GLOBALS['MULTISITES_ELT_SITE']['hidden']) ? $GLOBALS['MULTISITES_ELT_SITE']['hidden'] : array();

$this->geoip_logfile = defined( 'MULTISITES_GEOIP_LOGFILE') ? MULTISITES_GEOIP_LOGFILE : '';
$this->maxmind_key_country = defined( 'MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_COUNTRY') ? MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_COUNTRY : '';
$this->maxmind_key_city = defined( 'MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_CITY') ? MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_CITY : '';
$this->maxmind_icc_enabled = defined( 'MULTISITES_GEOIP_MAXMIND_ICC_ENABLED') ? MULTISITES_GEOIP_MAXMIND_ICC_ENABLED : false;
$this->maxmind_city_enabled= defined( 'MULTISITES_GEOIP_MAXMIND_CITY_ENABLED') ? MULTISITES_GEOIP_MAXMIND_CITY_ENABLED : false;
$this->quova_apikey = defined( 'MULTISITES_GEOIP_QUOVA_APIKEY') ? MULTISITES_GEOIP_QUOVA_APIKEY : '';
$this->quova_secret = defined( 'MULTISITES_GEOIP_QUOVA_SECRET') ? MULTISITES_GEOIP_QUOVA_SECRET : '';
$this->maxmind_icc_date = MultisitesModelSettings::getMaxmind_ICC_Date();
$this->maxmind_city_date = MultisitesModelSettings::getMaxmind_City_Date();

$this->browser_logfile = defined( 'MULTISITES_BROWSER_LOGFILE') ? MULTISITES_BROWSER_LOGFILE : '';
return $this;
}

function getWebsiteQuota( $product_id)
{

$vars = array( 'option' => 'com_pay2win',
'task' => 'jms.getWebSiteQuota',
'product_id' => $product_id
);
$data = '';
$url =& Edwin2WinModelRegistration::getURL();
if ( empty( $url)) {
$this->setError( JText::_( 'Unable to know where to get a Website Quota'));
return false;
}
$result = HTTP2Win::request( $url, $vars);
if ( $result === false) {
$this->setError( JText::_( 'The Website Quota cannot be retreived'));
}
else {
$status = HTTP2Win::getLastHttpCode();

if ( $status == '200') {
$data =& HTTP2Win::getLastData();
if ( strncmp( $data, '[OK]', 4) == 0) {

$arr = explode( '|', $data);
$result = array();
$result['website_count'] = $arr[1];
$result['website_quota'] = $arr[2];
return $result;
}
else if ( strncmp( $data, '[ERR]', 5) == 0) {

$arr = explode( '|', $data);
$err_level = $arr[1];
$website_id = $arr[2];
$err_code = $arr[3];

$err_code_key = 'JMS2WIN_ERR_WQ_'.$err_code;
$user_msg = JText::_( $err_code_key);
if ( $user_msg == $err_code_key && !empty($arr[4]) ) {
$msg = $arr[4];
}
else {
$msg = $user_msg;
}
if ( !empty( $msg)) {
$this->setError( $msg);
}

return array();
}
else {
$this->setError( "Unexpected reply when getting the Website Quota. Returned data=[".$data."]");
}
}
}
return false;
}


function save( $enteredvalues)
{

$jpath_multisites = !empty( $enteredvalues['jpath_multisites']) ? "   define( 'JPATH_MULTISITES', '".$enteredvalues['jpath_multisites']."');"
: ''
;
$str = '<?php'."\n"
. "if ( !defined( 'JPATH_MULTISITES')) {\n"
. $jpath_multisites."\n"
. "}\n"
. "if ( !defined( 'JPATH_MULTISITES')) {\n"
. "   if ( !defined( JPATH_ROOT)) {\n"
. "      define( 'JPATH_MULTISITES', dirname( dirname( dirname( dirname( __FILE__)))).DIRECTORY_SEPARATOR.'multisites');\n"
. "   }\n"
. "   else {\n"
. "      define( 'JPATH_MULTISITES', JPATH_ROOT.DIRECTORY_SEPARATOR.'multisites');\n"
. "   }\n"
. "}\n"
;
$filename = dirname( dirname( __FILE__)).'/multisites_path.cfg.php';
JFile::write( $filename, $str);

$str = '<?php'."\n";
if ( !empty( $enteredvalues['dir_rights'])) { $str .= "define( 'MULTISITES_DIR_RIGHTS', ".sprintf( '0%o', $enteredvalues['dir_rights']).");\n"; }
else { $str .= "define( 'MULTISITES_DIR_RIGHTS', 0755);\n"; }
if ( !empty( $enteredvalues['tld_parsing'])) { $str .= "define( 'MULTISITES_TLD_PARSING', true);\n"; }
if ( !empty( $enteredvalues['letter_tree'])) { $str .= "define( 'MULTISITES_LETTER_TREE', true);\n"; }
if ( !empty( $enteredvalues['refresh_disabled'])) { $str .= "define( 'MULTISITES_REFRESH_DISABLED', true);\n"; }
if ( !empty( $enteredvalues['cookie_domain'])) { $str .= "define( 'MULTISITES_COOKIE_DOMAIN', true);\n"; }
if ( !empty( $enteredvalues['ignore_ext_version'])){ $str .= "define( 'MULTISITES_IGNORE_MANIFEST_VERSION', true);\n"; }
if ( !empty( $enteredvalues['db_grant_host'])) { $str .= "define( 'MULTISITES_DB_GRANT_HOST', '".$enteredvalues['db_grant_host']."');\n"; }
if ( !empty( $enteredvalues['db_root_user'])) { $str .= "define( 'MULTISITES_DB_ROOT_USER', '".$enteredvalues['db_root_user']."');\n"; }
if ( !empty( $enteredvalues['db_root_psw'])) { $str .= "define( 'MULTISITES_DB_ROOT_PSW', '".$enteredvalues['db_root_psw']."');\n"; }
if ( !empty( $enteredvalues['elt_site'])) { $str .= '$GLOBALS[\'MULTISITES_ELT_SITE\'] = array( '
. (!empty( $enteredvalues['elt_site']['text']) ? "'text' => array( '".implode( "', '", $enteredvalues['elt_site']['text'])."'), "
: '')
. (!empty( $enteredvalues['elt_site']['hidden']) ? "'hidden' => array( '".implode( "', '", $enteredvalues['elt_site']['hidden'])."'), "
: '')
. "'eol' => null);\n"
;
}
if ( !empty( $enteredvalues['joomla_download_url'])) { $str .= "define( 'MULTISITES_JOOMLA_DOWNLOAD_URL', '".$enteredvalues['joomla_download_url']."');\n"; }
if ( !empty( $enteredvalues['home_dir'])) { $str .= "define( 'MULTISITES_HOME_DIR', '".$enteredvalues['home_dir']."');\n"; }
if ( !empty( $enteredvalues['public_dir'])) { $str .= "define( 'MULTISITES_PUBLIC_DIR', '".$enteredvalues['public_dir']."');\n"; }
if ( !empty( $enteredvalues['config_prefix_dir'])) { $str .= "define( 'MULTISITES_CONFIG_PREFIX_DIR', '".$enteredvalues['config_prefix_dir']."');\n"; }
if ( !empty( $enteredvalues['autoinc_dir'])) { $str .= "define( 'MULTISITES_AUTOINC_DIR', '".$enteredvalues['autoinc_dir']."');\n"; }
if ( !empty( $enteredvalues['geoip_logfile'])) { $str .= "define( 'MULTISITES_GEOIP_LOGFILE', '".$enteredvalues['geoip_logfile']."');\n"; }
if ( !empty( $enteredvalues['maxmind_key_country'])) { $str .= "define( 'MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_COUNTRY', '".$enteredvalues['maxmind_key_country']."');\n"; }
if ( !empty( $enteredvalues['maxmind_key_city'])) { $str .= "define( 'MULTISITES_GEOIP_MAXMIND_LICENSE_KEY_CITY', '".$enteredvalues['maxmind_key_city']."');\n"; }
if ( !empty( $enteredvalues['maxmind_icc_enabled'])) { $str .= "define( 'MULTISITES_GEOIP_MAXMIND_ICC_ENABLED', true);\n"; }
if ( !empty( $enteredvalues['maxmind_city_enabled'])) { $str .= "define( 'MULTISITES_GEOIP_MAXMIND_CITY_ENABLED', true);\n"; }
if ( !empty( $enteredvalues['quova_apikey'])) { $str .= "define( 'MULTISITES_GEOIP_QUOVA_APIKEY', '".$enteredvalues['quova_apikey']."');\n"; }
if ( !empty( $enteredvalues['quova_secret'])) { $str .= "define( 'MULTISITES_GEOIP_QUOVA_SECRET', '".$enteredvalues['quova_secret']."');\n"; }
if ( !empty( $enteredvalues['browser_logfile'])) { $str .= "define( 'MULTISITES_BROWSER_LOGFILE', '".$enteredvalues['browser_logfile']."');\n"; }
$filename = dirname( dirname( __FILE__)).'/multisites.cfg.php';
JFile::write( $filename, $str);
return true;
}
} 

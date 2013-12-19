<?php
// file: registration.php.
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


if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC') && !defined( '_EDWIN2WIN_') ) {
die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
}
if ( !defined( 'DS')) define('DS', DIRECTORY_SEPARATOR);


if ( class_exists( 'J2WinModel')) { eval( 'class Reg2WinModel extends J2WinModel {};') ; }

else if ( function_exists('jimport')) {
jimport( 'joomla.application.component.model' );
if ( file_exists( JPATH_LIBRARIES.'/legacy/model/legacy.php')) { jimport('legacy.model.legacy'); }
if ( class_exists( 'JModelLegacy')) { eval( 'class Reg2WinModel extends JModelLegacy {};') ; }
else if ( class_exists( 'JModel')) { eval( 'class Reg2WinModel extends JModel {};') ; }
}

else {

class Reg2WinModel { var $fake= true; }
}
if ( !class_exists( 'HTTP2Win')) {
if ( !defined('_EDWIN2WIN_')) { define('_EDWIN2WIN_', true); }
if ( file_exists( dirname( __FILE__) .DS. 'http.php')) {
@include( dirname( __FILE__) .DS. 'http.php');
}
else {
@include( dirname( dirname( __FILE__)) .DS. 'classes' .DS. 'http.php');
}
}



if ( !class_exists( 'Edwin2WinModelRegistration')) {
class Edwin2WinModelRegistration extends Reg2WinModel
{


function &getURL()
{
static $instance;
if (!isset( $instance )) {
$filename = dirname( __FILE__) .DS. 'registration_inc.php';
@include_once( $filename);
if ( defined( 'EDWIN2WIN_REGISTRATION_URL')) {
$instance = EDWIN2WIN_REGISTRATION_URL;
}
else {
$instance = 'http://www.2win.lu/index.php';
}
}
return $instance;
}


function setURL( $newUrl)
{
$url =& Edwin2WinModelRegistration::getURL();
$url = $newUrl;
}


function &_getExtVers()
{
static $instance;
if (!isset( $instance )) {
$instance = '';
}
return $instance;
}


function &_getExtName()
{
static $instance;
if (!isset( $instance )) {
$instance = '';
}
return $instance;
}


function setExtensionInfo( $newName, $newVersion)
{
$name =& Edwin2WinModelRegistration::_getExtName();
$name = $newName;
$vers =& Edwin2WinModelRegistration::_getExtVers();
$vers = $newVersion;
}

function _getRegInfo_Filename()
{
return dirname(__FILE__).DS.'info'.DS.'data.php';
}

function writeRegistrationInfo( $values)
{
$filename = Edwin2WinModelRegistration::_getRegInfo_Filename();
$dir = dirname( $filename);

if ( function_exists('jimport')) {
jimport('joomla.filesystem.folder');
if ( !JFolder::exists( $dir)) {
JFolder::create( $dir, 0755);
}
}
else {
if ( !is_dir( $dir)) {
mkdir( $dir, 0755);
}
}
$config = "<?php\n";
$config .= "if ( !defined( '_JEXEC') && !defined( '_VALID_MOS') && !defined( '_EDWIN2WIN_')) die( 'Restricted access' ); \n\n";
$config .= "\$data = array( ";
$sep='';
foreach( $values as $key => $value) {
$config .= $sep . "'$key' => '$value'" ;
$sep = ",\n               ";
}
$config .= ");\n";
$config .= "?>";

if ( function_exists('jimport')) {
jimport('joomla.filesystem.file');
return JFile::write( $filename, $config);
}
else {
$fp = fopen( $filename, "w");
if ( $fp)
{
fputs($fp, $config, strlen($config));
fclose ($fp);
return true;
}
}
return false;
}

function getRegistrationInfo( $prev_product_id = null)
{
$data = array();
$filename = Edwin2WinModelRegistration::_getRegInfo_Filename();
if ( !file_exists($filename)) {

Edwin2WinModelRegistration::_createRegistrationInfo( $prev_product_id);
}

if ( !file_exists($filename)) {

return $data;
}

@include( $filename);
if ( !empty( $data) && !isset($data['dir'])) {
$data['dir'] = dirname( __FILE__);
}

if ( !isset( $data['product_id']) || empty($data['product_id'])
|| substr( $data['product_id'], 0, 4) == 'HTTP'
|| strpos( $data['product_id'], 'Set-Cookie') !== false
) {

$data['product_id'] = Edwin2WinModelRegistration::_getProductID( $prev_product_id);

if ( isset($data['product_id']) && !empty($data['product_id'])) {

Edwin2WinModelRegistration::writeRegistrationInfo( $data);

$data = array();

@include( $filename);
}
}
return $data;
}

function _removeFile( $filename)
{

if ( function_exists('jimport')) {
jimport('joomla.filesystem.file');
if ( JFile::exists( $filename)) {
JFile::delete( $filename);
}
}

else {
if ( file_exists($filename)) {
unlink( $filename);
}
}
}

function _movedInstall()
{

$data = Edwin2WinModelRegistration::getRegistrationInfo();
$filename = Edwin2WinModelRegistration::_getRegInfo_Filename();
Edwin2WinModelRegistration::_removeFile( $filename);

$cur_product_id = isset( $data['product_id'])
? $data['product_id']
: null;
Edwin2WinModelRegistration::getRegistrationInfo( $cur_product_id);
}

function _sendUpdateVersion( $product_key, $version)
{
$vars = array( 'option' => 'com_pay2win',
'task' => 'updproductvers',
'product_key' => $product_key,
'productversion' => $version
);
$data = '';
$url =& Edwin2WinModelRegistration::getURL();
$result = HTTP2Win::request( $url, $vars);
if ( $result === false) {
}
else {
$status = HTTP2Win::getLastHttpCode();

if ( $status == '200') {
$data =& HTTP2Win::getLastData();
return $data;
}
}

return null;
}

function _sendUpdateJoomlaVersion( $product_key, $jvers)
{
$vars = array( 'option' => 'com_pay2win',
'task' => 'updjoomlavers',
'product_key' => $product_key,
'joomlaversion' => $jvers
);
$data = '';
$url =& Edwin2WinModelRegistration::getURL();
$result = HTTP2Win::request( $url, $vars);
if ( $result === false) {
}
else {
$status = HTTP2Win::getLastHttpCode();

if ( $status == '200') {
$data =& HTTP2Win::getLastData();
return $data;
}
}

return null;
}


function &getForceRegistration()
{
static $instance;
if (!isset( $instance )) {
$instance = false;
}
return $instance;
}


function setForceRegistration( $newValue)
{
$value = &Edwin2WinModelRegistration::getForceRegistration();
$value = $newValue;
return $value;
}


function _isRegistered()
{
$data = Edwin2WinModelRegistration::getRegistrationInfo();
if ( !isset($data['dir'])
|| (isset($data['dir']) && $data['dir'] != dirname( __FILE__))) {
Edwin2WinModelRegistration::_movedInstall();
return false;
}
if ( isset($data['product_id'])) { $product_id = $data['product_id'];}
else { $product_id = ''; }
if ( isset($data['product_key'])) { $product_key = $data['product_key'];}
else { $product_key = ''; }
if ( !empty( $product_id) && !empty( $product_key)) {
$decoded = RSA::decrypt( $product_key, 7877, 56360411);
if ( $decoded == $product_id) {
$rc = true;
Edwin2WinModelRegistration::setForceRegistration( false);



if ( isset($data['product_version'])) { $reg_version = $data['product_version'];}
else { $reg_version = ''; }
$cur_version = Edwin2WinModelRegistration::getExtensionVersion();
if ( $cur_version != $reg_version) {

$reply = Edwin2WinModelRegistration::_sendUpdateVersion( $product_key, $cur_version);
if ( !empty( $reply) && $reply == '[OK]') {

$data['product_version'] = $cur_version;
Edwin2WinModelRegistration::writeRegistrationInfo( $data);
}
else {
Edwin2WinModelRegistration::setForceRegistration( true);
$rc = false;
}
}

if ( isset($data['joomla_version'])) { $reg_version = $data['joomla_version'];}
else { $reg_version = ''; }
$cur_version = Edwin2WinModelRegistration::getJoomlaVersion();
if ( $cur_version != $reg_version) {

$reply = Edwin2WinModelRegistration::_sendUpdateJoomlaVersion( $product_key, $cur_version);
if ( !empty( $reply) && $reply == '[OK]') {

$data['joomla_version'] = $cur_version;
Edwin2WinModelRegistration::writeRegistrationInfo( $data);
}
else {
Edwin2WinModelRegistration::setForceRegistration( true);
$rc = false;
}
}
return $rc;
}
}
return false;
}


function &isRegistered()
{
static $instance;
if (!isset( $instance )) {
$instance = Edwin2WinModelRegistration::_isRegistered();
}
return $instance;
}

function _getProductID( $prev_product_id)
{
$name = Edwin2WinModelRegistration::getExtensionName();
$version = Edwin2WinModelRegistration::getExtensionVersion();
$jvers = Edwin2WinModelRegistration::getJoomlaVersion();
$clientInfo = Edwin2WinModelRegistration::getClientInfo();
$vars = array( 'option' => 'com_pay2win',
'task' => 'getproductid',
'productname' => $name,
'productversion' => $version,
'joomlaversion' => $jvers,
'clientinfo' => $clientInfo);
if ( !empty( $prev_product_id)) {
$vars['prevproductid'] = $prev_product_id;
}
$data = '';
$url =& Edwin2WinModelRegistration::getURL();
if ( empty( $url)) {
return false;
}
$result = HTTP2Win::request( $url, $vars);
if ( $result === false) {
}
else {
$status = HTTP2Win::getLastHttpCode();

if ( $status == '200') {
$data =& HTTP2Win::getLastData();
return $data;
}
}

return '1-2WIN-V4H-1PZB-T8B-CE8';
}

function generateProductID( $prev_product_id)
{
$values = array();
$values['product_id'] = Edwin2WinModelRegistration::_getProductID( $prev_product_id);
if ( isset( $values['product_id'])) {
$values['product_version'] = Edwin2WinModelRegistration::getExtensionVersion();
$values['joomla_version'] = Edwin2WinModelRegistration::getJoomlaVersion();
$values['dir'] = dirname( __FILE__);
Edwin2WinModelRegistration::writeRegistrationInfo( $values);
}
}

function _createRegistrationInfo( $prev_product_id)
{
Edwin2WinModelRegistration::generateProductID( $prev_product_id);
}


function registerInfo( $inputValues)
{

if ( isset( $inputValues['status']) && !empty($inputValues['status'])
&& isset( $inputValues['product_key']) && !empty( $inputValues['product_key'])
)
{

if ( $inputValues['status'] == 'OK') {

$data = Edwin2WinModelRegistration::getRegistrationInfo();

if ( empty($data)
|| (!empty( $inputValues['product_id']) && $data['product_id'] != $inputValues['product_id']))
{
if ( !empty( $inputValues['product_id']) ) {
$data['product_id'] = $inputValues['product_id'];
$data['product_version'] = Edwin2WinModelRegistration::getExtensionVersion();
$data['joomla_version'] = Edwin2WinModelRegistration::getJoomlaVersion();
$data['dir'] = dirname( __FILE__);
}
else {
$this->setError( JText::_('Unable to retreive the registration information'));
return false;
}
}
$data['product_key'] = $inputValues['product_key'];
if ( !Edwin2WinModelRegistration::writeRegistrationInfo( $data)) {
$this->setError( JText::_('Unable to update the registration information'));
return false;
}

$instance =& Edwin2WinModelRegistration::isRegistered();
$instance = Edwin2WinModelRegistration::_isRegistered();
if ( $instance) {
return true;
}
$this->setError( JText::_('Invalid product key received! Please contact your distributor or re-saler.'));
return false;
}
}

$data = Edwin2WinModelRegistration::getRegistrationInfo();
$data['dir'] = '-retry-';
if ( !Edwin2WinModelRegistration::writeRegistrationInfo( $data)) {}
else {

$instance =& Edwin2WinModelRegistration::isRegistered();
$instance = Edwin2WinModelRegistration::_isRegistered();
}
$this->setError( JText::_('Missing registration information. Retry and if the problem continues, contact our support'));
return false;
}


function &getClientInfo()
{
$data = array();
$host = (isset($_SERVER["HTTP_HOST"])) ? $_SERVER["HTTP_HOST"] : '';

if ( class_exists( 'JFactory')) {
$user = JFactory::getUser();
$name = $user->name;
$email = $user->email;
}

else if ( class_exists( 'joomlaVersion')){
global $my, $mosConfig_mailfrom;
$name = $my->username;
$email = $mosConfig_mailfrom;
}

else if ( defined( 'STORE_OWNER_EMAIL_ADDRESS')) {
$name = STORE_OWNER;
$email = STORE_OWNER_EMAIL_ADDRESS;
}
$msg = "<root>"
. "<host>$host</host>"
. "<name>$name</name>"
. "<email>$email</email>"
. "</root>";

$encoded = '[B64]'.base64_encode($msg);
return $encoded;
}


function getExtensionName()
{
$extName = Edwin2WinModelRegistration::_getExtName();
if ( !empty( $extName)) {
return $extName;
}

if ( defined( 'DIR_WS_INCLUDES')) {
$dir = dirname( __FILE__ );
$parts = explode( DS, $dir);
$modulename = $parts[count($parts)-1];
return $modulename;
}

$dir = dirname( __FILE__ );
$parts = explode( DS, $dir);

$previousName = '';
for ( $i=count($parts)-1; $i>=0; $i--) {
if ( $parts[$i] == 'components' || $parts[$i] == 'modules') {
if ( !empty($previousName)) {
return $previousName;
}
break;
}
$previousName = $parts[$i];
}

$option = JRequest::getCmd('option');
return $option;
}


function getExtensionPath()
{
$dir = dirname( __FILE__ );
$parts = explode( DS, $dir);

$path = '';
for ( $i=count($parts)-1; $i>=0; $i--) {
if ( $parts[$i] == 'components' || $parts[$i] == 'modules') {
if ( !empty($path)) {
return $path;
}
break;
}
$path = implode( DS, $parts);
array_pop( $parts );
}

return null;
}


function getExtensionVersion()
{
$version = "unknown";

if ( defined( 'DIR_WS_INCLUDES')) {
$vers = Edwin2WinModelRegistration::_getExtVers();
if ( !empty( $vers)) {
$version = $vers;
}
else {
$dir = dirname( __FILE__ );
$parts = explode( DS, $dir);
$modulename = $parts[count($parts)-1];
if (method_exists( $modulename, 'getVersion')) {
$fn = $modulename . '::getVersion';
$version = $fn();
}
}
return $version;
}

$path = Edwin2WinModelRegistration::getExtensionPath();
$filename = $path .DS. 'install.xml';

if ( function_exists('jimport')) {

if ( !file_exists( $filename)) {

$filename = $path .DS. 'extension.xml'; 
if ( !file_exists( $filename)) {
$extname = basename( $path);
if ( substr( $extname, 0, 4) == 'com_' || substr( $extname, 0, 4) == 'mod_' ) {
$filename = $path .DS. substr( $extname, 4).'.xml';
}
else {
$filename = $path .DS. $extname.'.xml';
}
}
}
jimport( 'joomla.application.helper');
$data = null;
if ( file_exists( $filename)) {
$data = JApplicationHelper::parseXMLInstallFile($filename);
}
if ( !empty( $data)) {

if (isset($data['version']) && !empty($data['version'])) {
$version = $data['version'];
}
}
else {



if ( isset( $GLOBALS['installManifestVersion'])) {
return $GLOBALS['installManifestVersion'];
}
}
}

else if ( class_exists( 'DOMIT_Lite_Document')){

$xmlDoc = new DOMIT_Lite_Document();
$xmlDoc->resolveErrors( true );

if ( file_exists( $filename) && $xmlDoc->loadXML( $filename, false, true )) {

$root = &$xmlDoc->documentElement;
$element = &$root->getElementsByPath('version', 1);
$version = $element ? $element->getText() : '';
}
else {



if ( isset( $GLOBALS['installManifestVersion'])) {
return $GLOBALS['installManifestVersion'];
}
}
}
return $version;
}


function getLatestVersion()
{
static $results;

if ( !empty( $results)) {
return $results;
}
$results = array();
$name = Edwin2WinModelRegistration::getExtensionName();
$version = Edwin2WinModelRegistration::getExtensionVersion();
$vers = explode( '.', $version);
if ( count( $vers) >= 2) {
$product = $name
. '_v' . $vers[0] .'_'. $vers[1];
}
else {
$product = $name;
}
$url = "http://update.jms2win.com/latestversion_$product.xml";
$data = '';

if(function_exists('curl_init') && function_exists('curl_exec')) {
$ch = @curl_init();
@curl_setopt($ch, CURLOPT_URL, $url);
@curl_setopt($ch, CURLOPT_HEADER, 0);

@curl_setopt($ch, CURLOPT_FAILONERROR, 1);
@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

@curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$data = @curl_exec($ch);
@curl_close($ch);
}

if(function_exists('fsockopen') && $data == '') {
$errno = 0;
$errstr = '';

$fsock = @fsockopen("update.jms2win.com", 80, $errno, $errstr, 5);
if ($fsock) {
@fputs($fsock, "GET /latestversion_$product.xml HTTP/1.1\r\n");
@fputs($fsock, "HOST: update.jms2win.com\r\n");
@fputs($fsock, "Connection: close\r\n\r\n");

@stream_set_blocking($fsock, 1);
@stream_set_timeout($fsock, 5);
$get_info = false;
while (!@feof($fsock))
{
if ($get_info)
{
$data .= @fread($fsock, 1024);
}
else
{
if (@fgets($fsock, 1024) == "\r\n")
{
$get_info = true;
}
}
}
@fclose($fsock);

if(!strstr($data, '<?xml version="1.0" encoding="utf-8"?><update>')) {
$data = '';
}
}
}

if (function_exists('fopen') && ini_get('allow_url_fopen') && $data == '') {

ini_set('default_socket_timeout', 5);
$handle = @fopen ($url, 'r');

@stream_set_blocking($handle, 1);
@stream_set_timeout($handle, 5);
$data = @fread($handle, 1000);
@fclose($handle);
}

if( $data && strstr($data, '<?xml version="1.0" encoding="utf-8"?><update>') ) {
require_once( dirname( dirname( __FILE__)) .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
$xml = & Jms2WinFactory::getXMLParser('Simple');
$xml->loadString($data);
foreach( $xml->document as $key => $value) {
if ( substr( $key, 0, 1) == '_') {}
else {
$node = & $xml->document->$key;
$results[$key] = & $node[0]->data();
}
}
}
return $results;
}


function getJoomlaVersion()
{
$joomlaversion = '';

if ( class_exists( 'JVersion')) {
$version = new JVersion();
$joomlaversion = $version->getShortVersion();
}

else if ( class_exists( 'joomlaVersion')) {
$version = new joomlaVersion();
$joomlaversion = $version->getShortVersion();
}

else if ( defined( 'DIR_WS_INCLUDES') && defined( 'PROJECT_VERSION')) {
$joomlaversion = str_replace("osCommerce Online Merchant", "osc", PROJECT_VERSION);
}
return $joomlaversion;
}


function _getAds()
{
$name = Edwin2WinModelRegistration::getExtensionName();
$version = Edwin2WinModelRegistration::getExtensionVersion();
$jvers = Edwin2WinModelRegistration::getJoomlaVersion();
$clientInfo = Edwin2WinModelRegistration::getClientInfo();
$vars = array( 'productname' => $name,
'productversion' => $version,
'joomlaversion' => $jvers,
'clientinfo' => $clientInfo);
$regInfo = Edwin2WinModelRegistration::getRegistrationInfo();
if ( !empty( $regInfo['product_id'])) {
$product_id = trim( $regInfo['product_id']);
if ( !empty( $product_id)) {
$vars['product_id'] = $product_id;
}
}
$data = '';
$result = HTTP2Win::request( 'http://tools.2win.lu/ads/index.php', $vars);
if ( $result === false) {
}
else {
$status = HTTP2Win::getLastHttpCode();

if ( $status == '200') {
$data =& HTTP2Win::getLastData();
return $data;
}
}

if ( empty( $data)) {

$data = '<a href="http://www.edwin2win.com"><img src="http://tools.2win.lu/ads/images/edwin2win_banner.gif" border="0"></a>';
}
return $data;
}


function &getAds()
{
static $instance;
if (!isset( $instance )) {
$instance = Edwin2WinModelRegistration::_getAds();
}
return $instance;
}
} 

class RSA {

function encrypt($m, $e, $n) {
$asci = array ();
for ($i=0; $i<strlen($m); $i+=3) {
$tmpasci="1";
for ($h=0; $h<3; $h++) {
if ($i+$h <strlen($m)) {
$tmpstr = ord (substr ($m, $i+$h, 1)) - 30;
if (strlen($tmpstr) < 2) {
$tmpstr ="0".$tmpstr;
}
} else {
break;
}
$tmpasci .=$tmpstr;
}
array_push($asci, $tmpasci."1");
}

$coded = '';
for ($k=0; $k< count ($asci); $k++) {
$resultmod = RSA::powmod($asci[$k], $e, $n);
$coded .= $resultmod." ";
}
return trim($coded);
}

function powmod($base, $exp, $modulus) {
$accum = 1;
$i = 0;
$basepow2 = $base;
while (($exp >> $i)>0) {
if ((($exp >> $i) & 1) == 1) {
$accum = RSA::modulus( RSA::multiply($accum, $basepow2) , $modulus);
}
$basepow2 = RSA::modulus( RSA::multiply($basepow2, $basepow2) , $modulus);
$i++;
}
return $accum;
}
function multiply($a,$b){
$a = ''.$a;
$b = ''.$b;
$b_length = strlen($b);
$value = '';
$temp=0;
for($i=1;$i<=$b_length;$i++){
$b2 = $b[$b_length-$i];
$mul = $a*$b2+$temp;
$temp = substr( $mul, 0, strlen( $mul)-1);
$value = ($mul%10).$value;
}
$value = $temp.$value;
return $value;
}
function modulus( $g, $m) {
$rem = 0;
$div = 0;
$s = ''.$g;
for ( $i = 0; $i < strlen( $s); $i++) {
$d = substr( $s, $i, 1);
$div = ($rem * 10) + $d;
$rem = $div % $m;
}
return $rem;
}

function decrypt($c, $d, $n) {

$decryptarray = explode(" ", $c);
for ($u=0; $u<count ($decryptarray); $u++) {
if ($decryptarray[$u] == "") {
array_splice($decryptarray, $u, 1);
}
}

$deencrypt = '';
for ($u=0; $u< count($decryptarray); $u++) {
$resultmod = RSA::powmod($decryptarray[$u], $d, $n);

$deencrypt.= substr ($resultmod,1,strlen($resultmod)-2);
}

$resultd = '';
for ($u=0; $u<strlen($deencrypt); $u+=2) {
$c = substr ($deencrypt, $u, 2);
$resultd .= chr($c + 30);
}
return $resultd;
}
} 
}

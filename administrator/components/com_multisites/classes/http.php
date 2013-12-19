<?php
// file: http.php.
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


if ( !defined('_JEXEC') && !defined('_EDWIN2WIN_') ) die( 'Restricted access' );
require_once( dirname( __FILE__) .DIRECTORY_SEPARATOR. 'debug.php');
Debug2Win::setFileName( 'http.error.php');



if ( !class_exists( 'HTTP2Win')) {
class HTTP2Win
{


static function &getProxyInfo()
{
static $instance;
if (!is_object( $instance )) {
$instance = array();
}
return $instance;
}


static function setProxyInfo( $url, $user='', $password='')
{
$proxy = HTTP2Win::getProxyInfo();

if( trim( $url) != '') {
if( !stristr($url, 'http')) {
$proxy['host'] = $url;
$proxy['scheme'] = 'http';
$proxy['port'] = 80;
} else {
$proxy = parse_url( $url);
if( !isset( $proxy['scheme'] )) $proxy['scheme'] = 'http';
if( !isset( $proxy['port'] )) $proxy['port'] = 80;
}
}
else {

$proxy = array();
return;
}
$proxy['user'] = $user;
$proxy['password'] = $password;
}


static function &getLastHttpCode()
{
static $instance;
if (!isset( $instance )) {
$instance = '-1';
}
return $instance;
}


static function setLastHttpCode( $newCode)
{

$code =& HTTP2Win::getLastHttpCode();

$code = (string)$newCode;
}


static function &getLastHeaderSize()
{
static $instance;
if (!isset( $instance )) {
$instance = -1;
}
return $instance;
}


static function setLastHeaderSize( $newHeaderSize)
{

$headerSize =& HTTP2Win::getLastHeaderSize();

$headerSize = $newHeaderSize;
}


static function &getLastResult()
{
static $instance;
if (!isset( $instance )) {
$instance = '';
}
return $instance;
}


static function setLastResult( $newResult)
{

$result =& HTTP2Win::getLastResult();

$result = $newResult;
}

static function getLastData()
{

$result =& HTTP2Win::getLastResult();

$headerSize = HTTP2Win::getLastHeaderSize();
if ( $headerSize > 0) {

return substr( $result, $headerSize);
}



$len = strlen( $result);
$nl = 0;
$linelen = 0;
for ( $i=0; $i<$len; $i++) {
$c = substr( $result, $i, 1);
if ( $c == "\r") { }
else if ( $c == "\n") {
if ( $linelen <= 0) {
$nl++;
}
$linelen = 0;
}
else {

if ( $nl > 0) {

return substr( $result, $i);
}
$linelen++;
}
}

return $result;
}


static function curl_redir_exec($ch)
{
static $curl_loops = 0;
static $curl_max_loops = 20;
if ($curl_loops++ >= $curl_max_loops)
{
$curl_loops = 0;
return FALSE;
}
curl_setopt($ch, CURLOPT_HEADER, true);

$data = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($http_code == 301 || $http_code == 302)
{

$header_and_data = explode("\n\n", $data, 2);
$matches = array();
preg_match('/Location:(.*?)\n/', $$header_and_data[0], $matches);
$url = @parse_url(trim(array_pop($matches)));
if (!$url)
{

$curl_loops = 0;
return $data;
}
$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
if (!$url['scheme']) $url['scheme'] = $last_url['scheme'];
if (!$url['host']) $url['host'] = $last_url['host'];
if (!$url['path']) $url['path'] = $last_url['path'];
$new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:'');
curl_setopt($ch, CURLOPT_URL, $new_url);
Debug2Win::debug('Redirecting to '. $new_url);
return HTTP2Win::curl_redir_exec($ch);
} else {
$curl_loops=0;
return $data;
}
}


static function request( $url, $vars=array(), $method='GET', $headers=array(), $fileToSaveData=null )
{
Debug2Win::debug_start( ">> request() - START");
$urlParts = parse_url( $url );
if( !isset( $urlParts['port'] )) $urlParts['port'] = 80;
if( !isset( $urlParts['scheme'] )) $urlParts['scheme'] = 'http';

$proxy =& HTTP2Win::getProxyInfo();

$urlencoded = "";
while (list($key,$value) = each($vars))
$urlencoded.= urlencode($key) . "=" . urlencode($value) . "&";
$urlencoded = substr($urlencoded,0,-1);
$content_length = strlen($urlencoded);
if ( $method == 'POST') {
$postData = $urlencoded;
}

else {


if ( !strstr( $url, '?')) {
$url .= '?' . $urlencoded;
}
else {
$url .= '&' . $urlencoded;
}
}

if( function_exists( "curl_init" ))
{
Debug2Win::debug( 'Using the cURL library for communicating with '.$urlParts['host'] );
$CR = curl_init();
curl_setopt($CR, CURLOPT_URL, $url);

curl_setopt($CR, CURLOPT_TIMEOUT, 30 );

@curl_setopt($CR, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($CR, CURLOPT_MAXREDIRS, 10);
if( !empty( $headers )) {

curl_setopt($CR, CURLOPT_HTTPHEADER, $headers);
}
curl_setopt($CR, CURLOPT_FAILONERROR, true);
if( isset( $postData)) {
curl_setopt($CR, CURLOPT_POSTFIELDS, $postData );
curl_setopt($CR, CURLOPT_POST, 1);
}
if( is_resource($fileToSaveData)) {
curl_setopt($CR, CURLOPT_FILE, $fileToSaveData );
} else {
curl_setopt($CR, CURLOPT_RETURNTRANSFER, 1);
}
 if( !empty($proxy) ) {
Debug2Win::debug( 'Setting up proxy: '.$proxy['host'].':'.$proxy['port']);

curl_setopt($CR, CURLOPT_PROXY, $proxy['host'] );
curl_setopt($CR, CURLOPT_PROXYPORT, $proxy['port']);

if( trim( $proxy['user']) != '') {
Debug2Win::debug( 'Using proxy authentication!' );
curl_setopt($CR, CURLOPT_PROXYUSERPWD, $proxy['user'].':'.$proxy['password']);
}
}
if( $urlParts['scheme'] == 'https') {


curl_setopt($CR, CURLOPT_SSL_VERIFYPEER, 0);

}
$result = HTTP2Win::curl_redir_exec( $CR );
$error = curl_error( $CR );
if( !empty( $error ) && stristr( $error, '502') && !empty( $proxy)) {
Debug2Win::debug( 'Switching to NTLM authenticaton.');
curl_setopt( $CR, CURLOPT_PROXYAUTH, CURLAUTH_NTLM );
$result = HTTP2Win::curl_redir_exec( $CR );
$error = curl_error( $CR );
}
$http_code = curl_getinfo( $CR, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo( $CR, CURLINFO_HEADER_SIZE);

curl_close( $CR );
if( !empty( $error )) {
Debug2Win::debug( $error );
Debug2Win::debug_stop( "<< request() - STOP");
return false;
}
else {
HTTP2Win::setLastResult( $result);
HTTP2Win::setLastHttpCode( $http_code);
HTTP2Win::setLastHeaderSize( $headerSize);
Debug2Win::debug_stop( "<< request() - STOP");
return $result;
}
}

else
{



if( !empty( $proxy)) {

if( $proxy['scheme'] == 'https'){
$protocol = 'ssl';
}
else {
$protocol = 'http';
}
$fp = @fsockopen("$protocol://".$proxy['host'], $proxy['port'], $errno, $errstr, $timeout = 30);
}

else {
$server = $urlParts['host'];
$port = $urlParts['port'];

if( $urlParts['scheme'] == 'https' || $port == 443){
$protocol = 'ssl';
$fp = @fsockopen("ssl://".$server, $port, $errno, $errstr, $timeout = 30);
}
else {
$protocol = $urlParts['scheme'];
$fp = @fsockopen( $server, $port, $errno, $errstr, $timeout = 30);
}
}

if(!$fp){

Debug2Win::debug( "Possible server error! - $errstr ($errno)\n" );
Debug2Win::debug_stop( "<< request() - STOP");
return false;
}
else {
Debug2Win::debug( 'Connection opened to '.$urlParts['host']);
}


if( isset( $postData))
{
Debug2Win::debug('Now posting the variables.' );

if( !empty( $proxy)) {
fputs($fp, "POST ".$urlParts['host'].':'.$urlParts['port'].$urlParts['path']." HTTP/1.0\r\n");
fputs($fp, "Host: ".$proxyURL['host']."\r\n");
if( trim( $proxy['user'])!= '') {
fputs($fp, "Proxy-Authorization: Basic " . base64_encode ($proxy['user'].':'.$proxy['password']) . "\r\n\r\n");
}
}
else {
fputs($fp, 'POST '.$urlParts['path']." HTTP/1.0\r\n");
fputs($fp, 'Host:'. $urlParts['host']."\r\n");
}
fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
fputs($fp, "Content-length: ".strlen($postData)."\r\n");
fputs($fp, "Connection: close\r\n\r\n");
fputs($fp, $postData . "\r\n\r\n");
}

else {
if( !empty( $proxy)) {
fputs($fp, "GET ".$urlParts['host'].':'.$urlParts['port'].$urlParts['path']." HTTP/1.0\r\n");
fputs($fp, "Host: ".$proxy['host']."\r\n");
if( trim( $proxy['user'])!= '') {
fputs($fp, "Proxy-Authorization: Basic " . base64_encode ($proxy['user'].':'.$proxy['password']) . "\r\n\r\n");
}
}
else {

$user_agent = "http2win/1.0";
$request_uri = $url;
$pos = strpos( $url, $urlParts['host']);
if ( $pos>0) {
$pos += strlen( $urlParts['host']);
$pos2 = strpos( $url, '/', $pos);
if ( $pos2>0) {
$request_uri = substr( $url, $pos2);
}
}
$header_Line1 = "GET " . $request_uri . " HTTP/1.0";
$myHeaders = $header_Line1 . "\r\n"
. "Accept: text/*\r\n"
. "Accept-Language: en-us\r\n"
. "User-Agent: " . $user_agent . "\r\n"
. "Host: " . $urlParts['host'] . "\r\n"
. "Connection: Keep-Alive\r\n"
. "\r\n";
fputs($fp, $myHeaders);
}
}

foreach( $headers as $header ) {
fputs($fp, $header."\r\n");
}
$data = "";
while (!feof($fp)) {
$data .= @fgets ($fp, 4096);
}
fclose( $fp );

if ( trim($data) == '' ) {
Debug2Win::debug('An error occured while communicating with the server '.$urlParts['host'].'. It didn\'t reply (correctly). Please try again later, thank you.' );
Debug2Win::debug_stop( "<< request() - STOP");
return false;
}
$result = trim( $data );
HTTP2Win::setLastResult( $result);

$lines = explode( "\n", $result);
if ( $lines === false) {
HTTP2Win::setLastHttpCode( -3);
}
else {

$arr = explode( ' ', trim( $lines[0]));
if ( $arr === false || count($arr) < 2) {
HTTP2Win::setLastHttpCode( -2);
}
else {
HTTP2Win::setLastHttpCode( intval($arr[1]));
}
}
if( is_resource($fileToSaveData )) {
fwrite($fileToSaveData, $result );
Debug2Win::debug_stop( "<< request() - STOP");
return true;
} else {
Debug2Win::debug_stop( "<< request() - STOP");
return $result;
}
}
Debug2Win::debug('FATAL ERROR - Check programmation - This statment should never be executed.' );
Debug2Win::debug_stop( "<< request() - STOP");
return false;
} 
} 
}

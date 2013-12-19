<?php
// file: manage_basic.php.
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
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'dbsharing.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'classes' .DS. 'template.php');



class MultisitesModelManageBasic extends JModel2Win {

function canCreateSlave( $enteredvalues, $front_end = false)
{

if ( !$front_end) {
return true;
}

$this->setError( JText::_( 'Functionality not present in your product edition'));
return false;
}

function _getWebsiteID( $enteredvalues)
{
return 0;
}


function _deleteWebsiteID()
{
$site_id = JRequest::getCmd('id');
$site = new Site();
$site->load( $site_id);

if ( empty( $site->website_id)) {
return true;
}

require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'models' .DS. 'registration.php');
$regInfo = Edwin2WinModelRegistration::getRegistrationInfo();
if ( empty( $regInfo) || empty( $regInfo['product_id']) || empty( $regInfo['product_key'])) {
$this->setError( JText::_( 'You must register the product to delete a website created from the front-end'));
return false;
}
$product_id = $regInfo['product_id'];

$website_id = $site->website_id;
$vars = array( 'option' => 'com_pay2win',
'task' => 'jms.deleteWebSite',
'product_id' => $product_id,
'site_id' => $site_id,
'website_id' => $website_id
);
$data = '';
$url =& Edwin2WinModelRegistration::getURL();
if ( empty( $url)) {
$this->setError( JText::_( 'Unable to know where to post the website to delete'));
return false;
}
$result = HTTP2Win::request( $url, $vars);
if ( $result === false) {

}
else {
$status = HTTP2Win::getLastHttpCode();

if ( $status == '200') {
$data =& HTTP2Win::getLastData();
if ( strncmp( $data, '[OK]', 4) == 0) {
return true;
}
else if ( strncmp( $data, '[ERR]', 5) == 0) {

$arr = explode( '|', $data);
$err_level = $arr[1];
$website_id = $arr[2];
$err_code = $arr[3];

$err_code_key = 'JMS2WIN_ERR_FE_'.$err_code;
$user_msg = JText::_( $err_code_key);
if ( $user_msg == $err_code_key) {
$msg = $arr[4];
}
else {
$msg = $user_msg;
}
if ( !empty( $msg)) {
$this->setError( $msg);
}

if ( $err_level == 'W') {
return true;
}

return false;
}
else {
$this->setError( "Unable to mark the website as deleted into Joomla Multi Sites. Returned data=[".$data."]");
}
}
}

return false;
}


function getCookieSubdomain( $hosts)
{
$results = array( '');
$reverse_cookie = array();
$match_depth = 0;
$firstHost = true;
foreach( $hosts as $host) {

$tlds = &TLD2Win::getInstance();
$parts = $tlds->splitHost( $host);
if ( !$firstHost) {

if ( count( $parts) < 2) {
continue;
}

if ( count( $parts) == 4
&& is_numeric( $parts[0])
&& is_numeric( $parts[1])
&& is_numeric( $parts[2])
&& is_numeric( $parts[3])
)
{

$results[] = $host; 
continue;
}
}
$firstHost = false;
$reverse_domain = array_reverse( $parts);
if ( empty( $reverse_cookie)) {
$reverse_cookie = $reverse_domain;
$match_depth = count( $reverse_cookie);
}
else {

for ( $i=0; $i<$match_depth && $i<count($reverse_domain); $i++) {
if ( $reverse_cookie[$i] == $reverse_domain[$i]) {}
else {
if ( $i>1) {
if ( $i < $match_depth) {
$match_depth = $i;
}
}
break;
}
}

if ( $i<2) {
$results[] = $host; 
}
}
}

if ( !empty( $reverse_cookie) && $match_depth >= 2) {
while( count( $reverse_cookie) > $match_depth) {
array_pop( $reverse_cookie);
}
$parts = array_reverse( $reverse_cookie);

if ( count($parts) <= 4) {
$allNumeric = true;
for( $i=0;$i<count($parts); $i++) {
$allNumeric &= is_numeric( $parts[$i]);
}
if ( $allNumeric) {
if ( count( $results) <= 1) {
return array();
}
return $results;
}
}
$cookie_subdomain = '.' . implode( '.', $parts);
$results[0] = $cookie_subdomain;
return $results;
}
if ( count( $results) <= 1) {
return array();
}
return $results;
}


function getCookieDomains( $master_domain, $site_dependencies, &$sites, $i, $aHost, $master_userTablename = null)
{
if ( defined( 'MULTISITES_COOKIE_DOMAIN') && !MULTISITES_COOKIE_DOMAIN) {
return '';
}
$shared_site = false;

if ( $i < 0) {
$userTablename = $master_userTablename;
if ( !empty( $userTablename)) {

if ( isset( $site_dependencies[$userTablename])) {

if ( count( $site_dependencies[$userTablename]) > 1) {
$shared_site = true;
}
}
}
}
else {
if ( !isset( $sites[$i])) {
return '';
}
$site = & $sites[$i];

$userTablename = $site->getThisUserTablename();
if ( !empty( $userTablename)) {

if ( isset( $site_dependencies[$userTablename])) {

if ( count( $site_dependencies[$userTablename]) > 1) {
$shared_site = true;
}
}
}
if ( !$shared_site) {

$userTablename = $site->getFromUserTablename();
if ( !empty( $userTablename)) {

if ( isset( $site_dependencies[$userTablename])) {

if ( count( $site_dependencies[$userTablename]) > 1) {
$shared_site = true;
}
}
}
}
}
if ( !$shared_site) {
return '';
}

$hosts = array();
$hosts[] = $aHost;
foreach( $site_dependencies[$userTablename] as $indice) {

if ( $indice < 0) {
if ( !empty( $master_domain)) {
$hosts[] = $master_domain;
}
}

else {
$site = & $sites[ $indice];

if ( !empty( $site->indexDomains)) {
$domains = $site->indexDomains;
}
else {

$domains = $site->domains;
}
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
$host = strtolower( $myHost);
$hosts[] = $host;
}
}
}
}
return MultisitesModelManage::getCookieSubdomain( $hosts);
}


function &_getTemplate( $enteredvalues)
{
static $instance;
if ( empty( $instance)) {
$template = null;
if ( !empty( $enteredvalues['fromTemplateID'])) {
$template = new Jms2WinTemplate();
$template->load( $enteredvalues['fromTemplateID']);
}
$instance = $template;
}
return $instance;
}
}

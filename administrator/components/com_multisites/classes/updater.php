<?php
// file: updater.php.
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
require_once( dirname( dirname( __FILE__)) .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
require_once( dirname( __FILE__) .DS. 'http.php');




class MultisitesUpdater extends JObject
{

static function getJoomlaDownloadURL()
{
if ( defined( 'MULTISITES_JOOMLA_DOWNLOAD_URL')) {
return MULTISITES_JOOMLA_DOWNLOAD_URL;
}

return 'http://joomlacode.org/gf/download/frsrelease/17173/74758/Joomla_2.5.6-Stable-Full_Package.zip';

}

static function extractPackage( $source, $destination)
{
$errors = array();
$extractdir = JPath::clean( $destination);
$result = JArchive::extract( $source, $extractdir);
if ( $result === false ) {
$errors[] = 'Error while extracting '.$source.' into '.$extractdir;
}
return $errors;
}

static function downloadPackage( $downloadURL, $destination='', $extract=true)
{
$errors = array();
jimport('joomla.installer.helper');
$url = $downloadURL;
$downloadFile = basename( $downloadURL);
if ( empty( $destination)) {
$destination = MultisitesUpdater::getDownloadDirectory();
}

$p_file = JInstallerHelper::downloadPackage($url, $downloadFile);
if ( $p_file === false) {
$errors[] = 'Unable to download joomla from URL '.$url;
return $errors;
}
$tmp_dest = JFactory::getApplication()->getCfg('tmp_path');
$filename = $tmp_dest.DS.$p_file;


$content = file_get_contents( $filename);
if ( stristr( $content, '<html')) {
$errors[] = 'Error : The downloaded the file is not a tar.gz - url: '.$url;
return $errors;
}

if ( $extract) {
$errors = MultisitesUpdater::extractPackage( $filename, $destination);
}

else {
$dir = dirname( $destination);
if ( !JFolder::exists( $destination) && !JFolder::create( $destination)) {
$errors[] = 'Unable to create the destination '.$destination;
return $errors;
}
if ( !JFile::move( $filename, $destination.DS.$downloadFile)) {
$errors[] = 'Unable to move the file "'.$filename.'" into the directory '.$destination;
return $errors;
}
}
return $errors;
}


static function removeInstallation( $jpath_root)
{
$errors = array();
$inst_dir = JPath::clean( $jpath_root . '/installation');

if ( !JFolder::exists($inst_dir)) {

return $errors;
}

$del_dir = JPath::clean( $jpath_root . '/installation_to_delete');

if ( JFolder::exists( $del_dir)) {
for ( $i=1; ; $i++) {
$del_dir = JPath::clean( $jpath_root . '/installation_to_delete_' . $i);
if ( !JFolder::exists( $del_dir)) {
break;
}
}
}
if ( !rename( $inst_dir, $del_dir)) {
$errors = JText::_( 'Unable to remove the directory') . ' '.$inst_dir;
}
return $errors;
}


static function fixConfiguration( $destination)
{
$errors = array();
jimport('joomla.filesystem.file');
$filename = $destination.DS.'configuration.php';
$config_str = JFile::read( $filename);

$fix_offset = false;
$offset = JFactory::getApplication()->getCfg('offset');
$exp = '#var\s+\$offset\s?=\s?\'[0-9]+\';#i';
if ( preg_match( $exp, $config_str)) {
$config_str = preg_replace( $exp, "public \$offset = '$offset';", $config_str);
$fix_offset = true;
}


$p1 = strpos( $config_str, 'class JConfig');
if ( $p1 === false) {
$errors[] = JText::_( 'Unable to find the class JConfig');
return $errors;
}

$p2 = strpos( $config_str, '{', $p1);
if ( $p2 === false) {
$errors[] = JText::_( 'Unable to find the "{" character after the class JConfig');
return $errors;
}

$p3 = strpos( $config_str, "\n", $p2);
if ( $p3 === false) {
$errors[] = JText::_( 'Unable to find the new line just after the class JConfig {');
return $errors;
}
$params2Check = array( '$display_offline_message' => "public \$display_offline_message = '1';",
'$offline_image' => "public \$offline_image = '';",
'$captcha' => "public \$captcha = '0';",
'$offset' => "public \$offset = '$offset';",
'$MetaVersion' => "public \$MetaVersion = '0';",
'$robots' => "public \$robots = '';",
'$unicodeslugs' => "public \$unicodeslugs = '0';\n"
);
$patch_str = '';
foreach( $params2Check as $key => $value) {
if ( strpos( $config_str, $key) === false) {
$patch_str .= $value."\n";
}
}
if ( empty( $patch_str) && !$fix_offset) {

return $errors;
}

$config_str = substr( $config_str, 0, $p3+1)
. $patch_str
. substr( $config_str, $p3+1)
;


$ftp = JClientHelper::getCredentials('ftp', true);

if (!$ftp['enabled'] && JPath::isOwner( $filename) && !JPath::setPermissions( $filename, '0644'))
{
$errors[] = JText::_( 'Unable to set writable the configuration.php file');
return $errors;
}

if ( !JFile::write( $filename, $config_str)) {
$errors[] = JText::_( 'Unable to write the "configuration.php" file!');
return $errors;
}
return $errors;
}

static function getDownloadDirectory()
{
if ( defined( 'MULTISITES_DOWNLOAD_DIR')) { return MULTISITES_DOWNLOAD_DIR; }
return dirname( dirname( __FILE__)).DS.'download';
}

static function xmlGetAttribue( $xmlelement, $attribute)
{
if ( method_exists( $xmlelement, 'getAttribute')) {
return $xmlelement->getAttribute( $attribute);
}
else if ( is_a( $xmlelement, 'JSimpleXML') && !empty( $xmlelement->document)) {
return $this->xmlGetAttribue($xmlelement->document, $attribute);
}
return $xmlelement->attributes( $attribute);
}

static function getAvailablePackages()
{
static $instance;
if ( isset( $instance)) {
return $instance;
}
$results = array();

$xmlURL = 'http://update.jms2win.com/availablepackages.xml';
$rc = HTTP2Win::request( $xmlURL);
if ( $rc === false) {
}
else {
$status = HTTP2Win::getLastHttpCode();

if ( $status == '200') {
$data = HTTP2Win::getLastData();
}
}
$xml =& Jms2WinFactory::getXMLParser('Simple');
if ( !empty( $data) && $xml->loadString( $data)) {
$node =& $xml->document;
if ( $node->name() == 'packages') {
if ( !empty( $node->_children)) {
for ($i=count($node->_children)-1;$i>=0;$i--) {
$child = & $node->_children[$i];
if ( $child->name() == 'download') {
$keys = array();
$url = MultisitesUpdater::xmlGetAttribue( $child, 'url');
$keys['filename'] = basename( $url);
$keys['type'] = MultisitesUpdater::xmlGetAttribue( $child, 'type');
$keys['name'] = MultisitesUpdater::xmlGetAttribue( $child, 'name');
$keys['element'] = MultisitesUpdater::xmlGetAttribue( $child, 'element');
$keys['folder'] = MultisitesUpdater::xmlGetAttribue( $child, 'folder');
$keys['version'] = MultisitesUpdater::xmlGetAttribue( $child, 'version');
$results[ implode( '|', $keys)] = $url;
}
}
}
}
}


if ( empty( $results)) {
$url = MultisitesUpdater::getJoomlaDownloadURL();
$results[ basename( $url)] = $url;
}
$instance = $results;
return $instance;
}

static function getDownloadedPackages()
{
static $instance;
if ( isset( $instance)) {
return $instance;
}
$dir = MultisitesUpdater::getDownloadDirectory();
if ( JFolder::exists( $dir)) {
$files = JFolder::files( $dir, '.', false, false, array('.svn', 'CVS', '.htaccess', 'vssver.scc'));
}
else {
$files = array();
}
$instance = $files;
return $instance;
}


static function downloadLatestJoomla( $url='')
{
$errors = array();

if ( empty( $url)) {
$url = MultisitesUpdater::getJoomlaDownloadURL();
}
if ( empty( $url)) {
$errors[] = 'Unable to find the Joomla download URL';
return $errors;
}
$download_dir = MultisitesUpdater::getDownloadDirectory();
$errors = MultisitesUpdater::downloadPackage( $url, $download_dir, false);
return $errors;
}


static function getJoomlaDownloadPath()
{
$url = MultisitesUpdater::getJoomlaDownloadURL();
$download_dir = MultisitesUpdater::getDownloadDirectory();
if ( !empty( $url)) {
$downloadFile = basename( $url);
return $download_dir.DS.$downloadFile;
}
return false;
}


static function isJoomlaDownloaded()
{
$joomla_path = MultisitesUpdater::getJoomlaDownloadPath();
if ( $joomla_path !== false && JFile::exists( $joomla_path)) {
return true;
}
return false;
}

static function checkDownloadJoomla()
{
$errors = array();

if ( !MultisitesUpdater::isJoomlaDownloaded()) {

$errors = MultisitesUpdater::downloadLatestJoomla();
}
return $errors;
}

static function extractJoomla( $destination)
{
$errors = array();

if ( !MultisitesUpdater::isJoomlaDownloaded()) {
$errors[] = 'Joomla package is not yet downloaded';
return $errors;
}
$joomla_path = MultisitesUpdater::getJoomlaDownloadPath();
if ( $joomla_path === false) {
$errors[] = 'Unable to retreive the joomla download path';
return $errors;
}

@set_time_limit(1200);
@ini_set('max_execution_time', 1200);
$errors = MultisitesUpdater::extractPackage( $joomla_path, $destination);
return $errors;
}

static function installLatestJoomla( $destination)
{
$errors = array();

if ( !MultisitesUpdater::isJoomlaDownloaded()) {
$errors[] = 'Joomla package is not yet downloaded';
return $errors;
}
$joomla_path = MultisitesUpdater::getJoomlaDownloadPath();
if ( $joomla_path === false) {
$errors[] = 'Unable to retreive the joomla download path';
return $errors;
}

@set_time_limit(1200);
@ini_set('max_execution_time', 1200);
$errors = MultisitesUpdater::extractPackage( $joomla_path, $destination);
MultisitesUpdater::removeInstallation( $destination);
MultisitesUpdater::fixConfiguration( $destination);
return $errors;
}


static function getJoomlaVersion()
{
$url = MultisitesUpdater::getJoomlaDownloadURL();
$filename = basename( $url);
$vers = null;
if ( preg_match( '#([a-zA-Z]+)[\-|\_]([0-9\.]*)#i', $filename, $match)) {
$vers = $match[2];
}
return $vers;
}


static function fixPluginsDirs( $destination)
{
$errors = array();
$errors[] = 'TO DO';
return $errors;
}
} 
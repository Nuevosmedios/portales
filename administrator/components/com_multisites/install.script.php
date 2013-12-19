<?php
// file: install.script.php.
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


defined( '_JEXEC' ) or die();
require_once( dirname( __FILE__).DIRECTORY_SEPARATOR.'legacy.php');
if ( !defined( 'JPATH_MULTISITES')) {
define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

if ( version_compare( JVERSION, '1.6') >= 0) {

$this->parent->setPath( 'source', dirname( __FILE__));

include_once( dirname( __FILE__) .DS. 'install.language_j16.php');
}
else {

class MultisitesConvertLanguage {};
}


if ( version_compare( JVERSION, '1.6') >= 0) {
if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {

if ( is_file( dirname(__FILE__).DS.'extension.xml')) {
define( 'MULTISITES_MANIFEST_FILENAME', 'extension.xml');
}

else if ( !empty( $this->name) && is_file( dirname(__FILE__).DS.$this->name.'.xml') ) {
define( 'MULTISITES_MANIFEST_FILENAME', $this->name.'.xml');
}

else {
define( 'MULTISITES_MANIFEST_FILENAME', substr( basename( dirname( __FILE__)), 4).'.xml');
}
}
}

else {
if ( !defined( 'MULTISITES_MANIFEST_FILENAME')) {
if ( is_file( dirname(__FILE__).DS.'z_install.xml')) {
define( 'MULTISITES_MANIFEST_FILENAME', 'z_install.xml');
}
else {
define( 'MULTISITES_MANIFEST_FILENAME', 'install.xml');
}
}
}
if ( !defined( 'JPATH_MUTLISITES_COMPONENT')) {
define( 'JPATH_MUTLISITES_COMPONENT', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}



if ( version_compare( JVERSION, '1.6') >= 0) { $myManifestVersion =& $this->manifest->version;
$GLOBALS['installManifest'] = &$this->manifest;
}

else { $myManifestVersion =& $this->manifest->getElementByPath('version'); }
if ( method_exists( $myManifestVersion, 'data')) { $GLOBALS['installManifestVersion'] = JFilterInput::clean($myManifestVersion->data(), 'cmd'); }

else { $GLOBALS['installManifestVersion'] = JFilterInput::clean( (string)$myManifestVersion, 'cmd'); }

if ( file_exists( dirname(__FILE__).DIRECTORY_SEPARATOR.'install.script.geoloc.php')) {
@include_once( dirname(__FILE__).DIRECTORY_SEPARATOR.'install.script.geoloc.php');
}



class Com_MultisitesInstallerScript extends MultisitesConvertLanguage {

function preflight($type, $parent)
{
if ( version_compare( JVERSION, '1.6') >= 0) {

if ( is_file( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml'))
{
jimport( 'joomla.filesystem.file' );
 JFile::delete( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml');
}
}
return true;
}

function calcDefaultDir()
{
$results = array();


if ( strpos( JPATH_ROOT, '/home') !== false
&& strpos( JPATH_ROOT, '/public_html') !== false
)
{
$results['search'] = array();
$results['replace']= array();
$parts = explode( DS, trim( JPATH_ROOT, '/\\'));
if ( count( $parts) > 2) {
$home_dir = DS.$parts[0].DS.$parts[1];
$results['search'][] = '{home_dir}';
$results['replace'][] = $home_dir;
}
$results['search'][] = '{public_dir}';
$results['replace'][] = '/public_html';
}

else if ( strpos( JPATH_ROOT, '/httpdocs') !== false ) {
$results['search'] = array();
$results['replace']= array();
if ( strpos( JPATH_ROOT, '/var/www/vhosts') !== false) {
$parts = explode( DS, trim( JPATH_ROOT, '/\\'));
if ( count( $parts) > 4) {
$home_dir = DS.$parts[0].DS.$parts[1].DS.$parts[2].DS.$parts[3];
$results['search'][] = '{home_dir}';
$results['replace'][] = $home_dir;
}
}
$results['search'][] = '{public_dir}';
$results['replace'][] = '/httpdocs';
}


else if ( preg_match( '#^(/home(.*)/html)#', JPATH_ROOT, $match)) {
$results['search'] = array();
$results['replace']= array();
$results['search'][] = '{home_dir}';
$results['replace'][] = $match[1];
$results['search'][] = '{public_dir}';
$results['replace'][] = '/html';
}
return $results;
}


function install($parent)
{
$mainframe =& J2WinFactory::getApplication();
$backdir = 'backup_on_install';

@set_time_limit( 60);


if ( version_compare( JVERSION, '1.6') >= 0) {
$name = $parent->get( 'element');

MultisitesConvertLanguage::files();
}

else {
$name = basename( dirname( __FILE__));
}
$path = JPATH_ADMINISTRATOR.DS.'components'.DS.$name;

$lang =& J2WinFactory::getLanguage();
$lang->load( $name);



jimport('joomla.filesystem.file');
if ( !JFile::exists( $path.DS.'multisites.cfg.php')
&& JFile::exists( $path.DS.'multisites.cfg-dist.php')
)
{
JFile::copy( $path.DS.'multisites.cfg-dist.php',
$path.DS.'multisites.cfg.php'
);
$searchReplace = self::calcDefaultDir();
if ( !empty( $searchReplace)) {
$content = JFile::read( $path.DS.'multisites.cfg.php');
$content = str_replace( $searchReplace['search'], $searchReplace['replace'], $content);
$content = str_replace( array( '{home_dir}', '{public_dir}'), array( '', ''), $content);
JFile::write( $path.DS.'multisites.cfg.php', $content);
}
}
include_once( $path.DS.'multisites.cfg.php' );




if ( !JFile::exists( $path.DS.'multisites_path.cfg.php')
&& JFile::exists( $path.DS.'multisites_path.cfg-dist.php')
)
{
JFile::copy( $path.DS.'multisites_path.cfg-dist.php',
$path.DS.'multisites_path.cfg.php'
);
}
@include_once( $path.DS.'multisites_path.cfg.php' );

if ( !defined( 'JPATH_MULTISITES')) {
define( 'JPATH_MULTISITES', JPATH_ROOT.DS.'multisites');
}



if ( !JFile::exists( $path.DS.'multisites_userexit.php')
&& JFile::exists( $path.DS.'multisites_userexit-dist.php')
)
{
JFile::copy( $path.DS.'multisites_userexit-dist.php',
$path.DS.'multisites_userexit.php'
);
}



if ( !JFile::exists( $path.DS.'models'.DS.'info'.'data.php')
&& JFile::exists( $path.DS.'models'.DS.'info'.'data-dist.php')
)
{

$str = JFile::read( $path.DS.'models'.DS.'info'.'data-dist.php');
if ( strpos( $str, 'fake') !== false) {}

else {
JFile::copy( $path.DS.'models'.DS.'info'.'data-dist.php',
$path.DS.'models'.DS.'info'.'data.php'
);
}
}

require_once( $path.DS.'models'.DS.'patches.php' );
$patches = new MultisitesModelPatches();
$backlist = $patches->backup( $backdir);
if ( $backlist === false) {
$msg = $patches->getError();
echo JText::sprintf( 'INSTALL_BACKUP_ERROR', $msg);
$backup_rc = false;
}
else {
$backup_rc = true;
}

if ( $backup_rc) {

$missingFiles = $patches->checkBackup( $backdir);
if ( count($missingFiles) > 0) {
$msg = '';
foreach($missingFiles as $missingFile) {
$msg .= "- $missingFile<br/>";
}
echo JText::sprintf( 'INSTALL_CHECKBACKUP_ERROR', $msg);
return false;
}
}

JFolder::create( JPATH_MULTISITES);
if ( !JFolder::exists( JPATH_MULTISITES)) {
$msg = JPATH_MULTISITES;
echo JText::sprintf( 'INSTALL_MULTISITE_DIR_ERROR', $msg);
}

JFile::copy( $path.DS.'index.html', JPATH_MULTISITES .DS. 'index.html');



if ( !JFile::exists( JPATH_MULTISITES.DS.'config_templates.php')
&& JFile::exists( $path.DS.'config_templates-dist.php')
)
{
JFile::copy( $path.DS.'config_templates-dist.php',
JPATH_MULTISITES.DS.'config_templates.php'
);
}

$fullbackdir = $path.DS.$backdir;
echo JText::sprintf('INSTALL_BACKUP_SUCCESS', $fullbackdir);

$cleanupPatches = $patches->cleanupPatches();
if ( !empty( $cleanupPatches)) {
echo JText::sprintf('INSTALL_CLEANUP_PATCHES', implode( '</li><li>', $cleanupPatches));
}

if ( class_exists( 'Com_MultisitesGeolocInstallerScript')) {
if ( method_exists( 'Com_MultisitesGeolocInstallerScript', 'install')) {
Com_MultisitesGeolocInstallerScript::install( $parent, $path);
}
}

@include( dirname(__FILE__).'/install.script.variant.php');

if ( version_compare( JVERSION, '1.6') >= 0) {


$table = JTable::getInstance('Asset');
$table->load(array( 'parent_id' => 1, 'name'=>$name));
if ($table->id) { $success = $table->delete(); }
}
return true;
}

function update($parent) { self::install($parent); } 


function uninstall( $parent) {
require_once( JPATH_MUTLISITES_COMPONENT.DS.'controller.php' );
require_once( JPATH_MUTLISITES_COMPONENT.DS.'models' .DS.'patches.php' );
$rc = true;
$backdir = 'backup_on_install';

$lang =& J2WinFactory::getLanguage();
$lang->load( 'com_multisites');

$patches = new MultisitesModelPatches();
$patches->uninstall();


$Path2Installation = JPATH_ROOT .DS. 'installation';
$missingFiles = $patches->checkRestore();
if ( count($missingFiles) > 0) {
$msg = '';
foreach($missingFiles as $missingFile) {


if ( $missingFile == 'installation/includes/defines.php') {

if ( JFolder::exists( $Path2Installation)) {
JFolder::delete( $Path2Installation);
}
}

else if ( $missingFile == 'administrator/components/com_multisites/' .MULTISITES_MANIFEST_FILENAME) {}
else if ( $missingFile == 'libraries/joomla/filesystem/folder.php') {}
else if ( $missingFile == 'administrator/defines.php') {}
else if ( $missingFile == 'defines.php') {}
else {
$msg .= "- $missingFile<br/>";
$rc = false;
}
}

if ( $rc == false) {
echo JText::sprintf( 'INSTALL_CHECKRESTORE_ERROR', $msg);
}
}

$filename = JPATH_ROOT.DS.'includes'.DS.'defines_multisites.php';
if ( JFile::exists( $filename)) {
JFile::delete( $filename);
}

$filename = JPATH_ROOT.DS.'includes'.DS.'multisites.php';
if ( JFile::exists( $filename)) {
JFile::delete( $filename);
}

$filename = JPATH_ROOT.DS.'includes'.DS.'multisites_userexit.php';
if ( JFile::exists( $filename)) {
JFile::delete( $filename);
}

if ( JFolder::exists( $Path2Installation)) {

echo JText::sprintf( 'INSTALL_RENAME_INSTALL_DIR', $msg);
}

if ( version_compare( JVERSION, '1.6') >= 0) {

$filename = JPATH_SITE.DS.'defines.php';
if ( JFile::exists( $filename)) {
JFile::delete( $filename);
}

$filename = JPATH_ADMINISTRATOR.DS.'defines.php';
if ( JFile::exists( $filename)) {
JFile::delete( $filename);
}
}

if ( class_exists( 'Com_MultisitesGeolocInstallerScript')) {
if ( method_exists( 'Com_MultisitesGeolocInstallerScript', 'uninstall')) {
Com_MultisitesGeolocInstallerScript::uninstall( $parent);
}
}

$filename = JPATH_ROOT.DS.'includes'.DS.'multisites_browser.php';
if ( JFile::exists( $filename)) {
JFile::delete( $filename);
}

if ( version_compare( JVERSION, '1.6') >= 0) {


$db = JFactory::getDBO();
$query = 'SELECT id from #__menu WHERE title=' . $db->Quote( $parent->get( 'element')) . ' AND component_id=0';
$db->setQuery( $query );
$menu_id = $db->loadResult();
if ( !empty( $menu_id)) {


$query = 'DELETE from #__menu WHERE parent_id=' . $menu_id;
$db->setQuery( $query );
$db->query();

$query = 'DELETE from #__menu WHERE id=' . $menu_id;
$db->setQuery( $query );
$db->query();
}
}
return $rc;
}


function postflight($type, $parent) {

if ( version_compare( JVERSION, '1.6') >= 0) {
jimport( 'joomla.filesystem.file' );

if ( is_file( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml')
&& is_file( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.$parent->get( 'name').'.xml')
)
{
 JFile::delete( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml');
}

else if ( is_file( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml')
&& !is_file( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.$parent->get( 'name').'.xml')
)
{

JFile::move( JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.'extension.xml',
JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.$parent->get( 'name').'.xml'
);
}


$manifest_filename = JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.basename( $parent->get( 'parent')->getPath( 'manifest'));
$ext_manifest_filename = JPATH_ADMINISTRATOR .DS.'components'.DS.$parent->get( 'element').DS.$parent->get( 'name').'.xml';
if ( $manifest_filename != $ext_manifest_filename) {

JFile::copy( $manifest_filename, $ext_manifest_filename);

JFile::delete( $manifest_filename);
}
}
return true;
}
}
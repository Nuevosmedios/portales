<?php
// file: installer_multisites.php.
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
jimport('joomla.installer.installer');
@include_once( dirname( dirname( dirname( dirname( __FILE__)))).DS.'multisites.cfg.php' );
require_once( dirname( dirname( __FILE__)).DS.'jms2winfactory.php' );




class JInstallerMultisites_j15 extends JInstaller
{


function &_getInstance()
{

$instance = & parent::getInstance();

$instance = new JInstallerMultisites();
return $instance;
}


function &getSlaveManifest( $path=null)
{
$result = false;
if ($path && JFolder::exists($path)) {
$this->setPath('source', $path);
} else {
$this->abort(JText::_('Install path does not exist'));
return $result;
}
if ( !$this->setupInstall()) {
$this->abort(JText::_('Unable to load the manifest XML file'));
return $result;
}
return $this->getManifest();
}


function &_getRoot( &$slave_manifest)
{
if ( is_a( $slave_manifest, 'JSimpleXML') && !empty( $slave_manifest->document)) {
$root =& $slave_manifest->document;
}
else if ( is_a( $slave_manifest, 'JXMLElement') ) {
$root =& $slave_manifest;
}
else {
$root =& $slave_manifest;
}
return $root;
}


function _cnvDirNameExceptions( $dir)
{
$fname = dirname( dirname( dirname( dirname( __FILE__)))).DS.'patches'.DS.'patch_exception.php';
@include( $fname);
if ( isset( $dir_exceptions)) {
$key = strtolower( str_replace( DS, '/', $dir));
if ( !empty( $dir_exceptions[ $key])) {
return str_replace( '/', DS, $dir_exceptions[ $key]);
}
}
return $dir;
}


function getExtName15( $slave_manifest)
{
$root =& $slave_manifest->document;
$inst_type = $root->attributes('type');
$elt = $root->getElementByPath('name');
if ( $elt === false) return false;
$name = JFilterInput::clean( $elt->data(), 'cmd');
switch( $inst_type) {
case 'language':
case 'languages':
$ext_name = 'language'.DS. strtolower( str_replace(" ", "", $name));
break;
case 'module':
case 'modules':

 $element =& $root->getElementByPath('files');
if (is_a($element, 'JSimpleXMLElement') && count($element->children())) {
$files =& $element->children();
foreach ($files as $file) {
if ($file->attributes('module')) {
$mname = $file->attributes('module');
break;
}
}
}
if ( !empty( $mname)) {
$ext_name = 'modules' .DS. $mname;
}
else {
$ext_name = null;
}
break;
case 'plugin':
case 'plugins':

  $element =& $root->getElementByPath('files');
if (is_a($element, 'JSimpleXMLElement') && count($element->children())) {
$files =& $element->children();
foreach ($files as $file) {
if ($file->attributes( 'plugin')) {
$pname = $file->attributes( 'plugin');
break;
}
}
}
$group = $root->attributes('group');
if (!empty ($pname) && !empty($group)) {
$ext_name = array( 'dir' => 'plugins'.DS.$group,
'manifest' => $pname);
} else {
$ext_name = null;
}
break;
case 'xmap_ext':

 $element =& $root->getElementByPath('files');
if (is_a($element, 'JSimpleXMLElement') && count($element->children())) {
$files =& $element->children();
foreach ($files as $file) {
if ($file->attributes( 'xmap_ext')) {
$pname = $file->attributes( 'xmap_ext');
break;
}
}
}
if (!empty ($pname)) {
$ext_name = array( 'dir' => 'administrator'.DS.'components'.DS.'com_xmap'.DS.'extensions',
'manifest' => $pname);
} else {
$ext_name = null;
}
break;
case 'template':
case 'templates':
$ext_name = 'templates'.DS. strtolower( str_replace(" ", "", $name));
break;
case 'component':
case 'components':
default:
$ext_name = 'components' .DS. 'com_' . strtolower( str_replace(" ", "", $name));
break;
}
$ext_name = $this->_cnvDirNameExceptions( $ext_name);
return $ext_name;
}


function getExtName16( $slave_manifest)
{
$root =& $this->_getRoot( $slave_manifest);
$inst_type = $this->xmlGetAttribue( $root, 'type');
$str = $this->xmlGetElementByPath( $root,'name');
if ( $str === false) return false;
$name = JFilterInput::clean( $str, 'cmd');
switch( $inst_type) {
case 'language':
case 'languages':
$ext_name = 'language'.DS. strtolower( str_replace(" ", "", $name));
break;
case 'module':
case 'modules':

 if ( isset( $root->files)) { $element =& $root->files; }
else { $element = null; }
if (is_a($element, 'JXMLElement') && count($element->children())) {
$files =& $element->children();
foreach ($files as $file) {
$attr = $file->getAttribute( 'module');
if ( !empty( $attr)) {
$mname = $attr;
break;
}
}
}
if ( !empty( $mname)) {
$ext_name = 'modules' .DS. $mname;
}
else {
$ext_name = null;
}
break;
case 'plugin':
case 'plugins':

  if ( isset( $root->files)) { $element =& $root->files; }
else { $element = null; }
if (is_a($element, 'JXMLElement') && count($element->children())) {
$files =& $element->children();
foreach ($files as $file) {
$attr = $file->getAttribute( 'plugin');
if ( !empty( $attr)) {
$pname = $attr;
break;
}
}
}
$group = $root->getAttribute( 'group');
if (!empty ($pname) && !empty($group)) {
$ext_name = array( 'dir' => 'plugins'.DS.$group.DS.$pname,
'manifest' => $pname);
} else {
$ext_name = null;
}
break;
case 'xmap_ext':

 if ( isset( $root->files)) { $element =& $root->files; }
else { $element = null; }
if (is_a($element, 'JXMLElement') && count($element->children())) {
$files =& $element->children();
foreach ($files as $file) {
$attr = $file->getAttribute( 'xmap_ext');
if ( !empty( $attr)) {
$pname = $attr;
break;
}
}
}
if (!empty ($pname)) {
$ext_name = array( 'dir' => 'administrator'.DS.'components'.DS.'com_xmap'.DS.'extensions',
'manifest' => $pname);
} else {
$ext_name = null;
}
break;
case 'template':
case 'templates':
$ext_name = 'templates'.DS. strtolower( str_replace(" ", "", $name));
break;
case 'component':
case 'components':
default:
$ext_name = strtolower( str_replace(" ", "", $name));

if ( substr( $ext_name, 0, 4) == 'com_') {} 
else { $ext_name = 'com_' . $ext_name; } 
$ext_name = 'components' .DS. $ext_name;
break;
}
$ext_name = $this->_cnvDirNameExceptions( $ext_name);
return $ext_name;
}

function getExtName( $slave_manifest)
{
if ( version_compare( JVERSION, '1.6') >= 0) {
return $this->getExtName16( $slave_manifest);
}
return $this->getExtName15( $slave_manifest);
}


function loadMasterManifest( $ext_name)
{
jimport('joomla.filesystem.folder');

if ( is_array( $ext_name)) {
$adminDir =
$siteDir = JPATH_SITE .DS. $ext_name['dir'];
$pattern = $ext_name['manifest'] . '.xml$';
$slave_ext_name = $ext_name['manifest'];
}
else {
$adminDir = JPATH_ADMINISTRATOR .DS. $ext_name;
$siteDir = JPATH_SITE .DS. $ext_name;
$pattern = '.xml$';
$slave_ext_name = $ext_name;
}

$folder = $adminDir;
if (JFolder::exists($folder)) {
$xmlFilesInDir = JFolder::files($folder, $pattern);
} else {
$folder = $siteDir;
if (JFolder::exists($folder)) {
$xmlFilesInDir = JFolder::files($folder, $pattern);
} else {
$xmlFilesInDir = null;
}
}
if (count($xmlFilesInDir))
{
foreach ($xmlFilesInDir as $xmlfile)
{
if ($data = JApplicationHelper::parseXMLInstallFile($folder.DS.$xmlfile)) {

if ( count($xmlFilesInDir) <= 1) {
break;
}
else {
$master_xml = & Jms2WinFactory::getXMLParser('Simple');
if ($master_xml->loadFile($folder.DS.$xmlfile)) {
$master_ext_name = $this->getExtName( $master_xml);
if ( is_array( $master_ext_name)) {
if ( $master_ext_name['manifest'] == $slave_ext_name) {
break;
}
}
else {
if ( $master_ext_name == $slave_ext_name) {
break;
}
}
}

unset( $data);
}
}
}
}

if ( !isset( $data)) {

return false;
}
return $data;
}

function xmlGetAttribue( $xmlelement, $attribute)
{
if ( method_exists( $xmlelement, 'getAttribute')) {
return $xmlelement->getAttribute( $attribute);
}
else if ( is_a( $xmlelement, 'JSimpleXML') && !empty( $xmlelement->document)) {
return $this->xmlGetAttribue($xmlelement->document, $attribute);
}
return $xmlelement->attributes( $attribute);
}

function xmlGetElementByPath( $root, $path)
{
$elt = false;
if ( method_exists( $root, 'getElementByPath')) {

$elt = $root->getElementByPath( $path);
}

else if ( isset( $root->$path)) {
$elt = $root->$path;
}
if ( $elt === false) {
return false;
}
if ( method_exists( $elt, 'data')) {
return $elt->data();
}
if ( $elt instanceof SimpleXMLElement) {
return (string) $elt;
}
return false;
}


function compareWithMaster( $slave_manifest)
{
$mainframe = &JFactory::getApplication();

$root =& $this->_getRoot( $slave_manifest);
$slave_name = $this->xmlGetElementByPath($root, 'name');
if ( $slave_name === false) { return false; }
$slave_version = $this->xmlGetElementByPath( $root, 'version');
if ( $slave_version === false) { return false; }
$ext_name = $this->getExtName( $slave_manifest);
if ( empty( $ext_name)) return false;
$data = $this->loadMasterManifest( $ext_name);
if ( $data === false)
{

if ( defined( 'MULTISITES_IGNORE_MANIFEST_VERSION') && MULTISITES_IGNORE_MANIFEST_VERSION) {
return true;
}


$mainframe->enqueueMessage( JText::_('MSJINSTALL_EXT_NOTFOUND'));
return false;
}

$rc = true;
if ( $slave_name != $data['name']) {
$fname = dirname( dirname( dirname( dirname( __FILE__)))).DS.'patches'.DS.'patch_exception.php';
@include( $fname);
if ( isset( $extname_exceptions) && strtolower( $extname_exceptions[ strtolower( $slave_name)]) == strtolower( $data['name'])) {}
else {
$mainframe->enqueueMessage( JText::sprintf( 'MSJINSTALL_EXT_DIF_NAME', $slave_name, $data['name']));
$rc = false;
}
}
if ( $slave_version != $data['version']) {

if ( defined( 'MULTISITES_IGNORE_MANIFEST_VERSION') && MULTISITES_IGNORE_MANIFEST_VERSION) {
$mainframe->enqueueMessage( JText::sprintf( 'MSJINSTALL_EXT_DIF_VERSION_IGNORED', $slave_version, $data['version']));
$slave_manifest_path = $this->getPath('source')
. $this->getPath('manifest')
;
$bak_manifest_path = $this->getPath('source')
. $this->getPath('manifest')
. '.original.xml'
;

JFile::copy( $slave_manifest_path, $bak_manifest_path);
define( 'MULTISITES_PREVIOUS_MANIFEST_FILE', $bak_manifest_path);
}
else {
$mainframe->enqueueMessage( JText::sprintf( 'MSJINSTALL_EXT_DIF_VERSION', $slave_version, $data['version']));
$rc = false;
}
}
return $rc;
}


function isValidVersion( $path=null)
{
$slave_manifest =& JInstallerMultisites::getSlaveManifest( $path);
if ( empty( $slave_manifest)) {
return false;
}

$root =& $slave_manifest;
$inst_type = $this->xmlGetAttribue( $root, 'type');
if ( $inst_type == 'template') {
if ( defined( 'MULTISITES_ID')) {
if ( defined( 'MULTISITES_ID_PATH')) { $filename = MULTISITES_ID_PATH.DIRECTORY_SEPARATOR.'config_multisites.php'; }
else { $filename = JPATH_MULTISITES.DS.MULTISITES_ID.DS.'config_multisites.php'; }
@include($filename);
if ( isset( $config_dirs) && !empty( $config_dirs) && !empty( $config_dirs['templates_dir'])) {
if (!$this->setAdapterMultisites($inst_type)) {
return false;
}
return true;
}
}
}
return JInstallerMultisites::compareWithMaster( $slave_manifest);
}


function getPath($name, $default=null)
{
$fake_suffix = '';

if ( version_compare( JVERSION, '1.6') >= 0)
{

$stack = debug_backtrace();
if ( !empty( $stack[1]['class']) && $stack[1]['class']=='JInstallerComponent') {




if ( !empty( $this->_fake_extension_site) && $name=='extension_site') {

$this->_fake_extension_site = false;
$fake_suffix = '_fake';
}
else if ( isset( $this->_fake_extension_administrator) && $name=='extension_administrator') {
if ( $this->_fake_extension_administrator==1) {

$fake_suffix = '_fake';
}
$this->_fake_extension_administrator++;
}
}
}
return parent::getPath($name, $default).$fake_suffix;
}


function install($path=null)
{

$lang =& JFactory::getLanguage();
$lang->load( 'com_multisites');

if ( $this->isValidVersion( $path)) {
$this->setOverwrite( true);

if ( version_compare( JVERSION, '1.6') >= 0)
{


$this->_fake_extension_site = true;
$this->_fake_extension_administrator=0;
}
$result = parent::install($path);

if ( defined( MULTISITES_PREVIOUS_MANIFEST_FILE)) {
$slave_manifest_path = $this->getPath('source')
. $this->getPath('manifest')
;

JFile::copy( MULTISITES_PREVIOUS_MANIFEST_FILE, $slave_manifest_path);
}
return $result;
}
return false;
}


function setAdapterMultisites($name, $adapter = null)
{
if (!is_object($adapter))
{

if ( version_compare( JVERSION, '1.6') >= 0) {
require_once(dirname(__FILE__).DS.'adapters16'.DS.strtolower($name).'.php');
}
else {
require_once(dirname(__FILE__).DS.'adapters'.DS.strtolower($name).'.php');
}
$class = 'JInstaller'.ucfirst($name).'Multisites';
if (!class_exists($class)) {
return false;
}
$adapter = new $class($this);
$adapter->setParent( $this);
}
$this->_adapters[$name] =& $adapter;
return true;
}


function uninstall($type, $identifier, $cid=0)
{

$lang =& JFactory::getLanguage();
$lang->load( 'com_multisites');
if (!isset($this->_adapters[$type]) || !is_object($this->_adapters[$type])) {
if (!$this->setAdapterMultisites($type)) {
return false;
}
}
if (is_object($this->_adapters[$type])) {
return $this->_adapters[$type]->uninstall($identifier, $cid);
}
return false;
}
} 




if ( defined( 'JVERSION')) {
if ( version_compare( JVERSION, '1.6') >= 0) { $jms2win_php4_static = 'public static '; }
else { $jms2win_php4_static = ''; }
}

else {

if ( file_exists( dirname(dirname(dirname(dirname(dirname(dirname(dirname( __FILE__))))))).DS.'libraries'.DS.'cms'.DS.'version'.DS.'version.php')) {
$jms2win_php4_static = 'public static ';

jimport('joomla.log.log');
}

else if ( file_exists( dirname(dirname(dirname(dirname(dirname(dirname(dirname( __FILE__))))))).DS.'includes'.DS.'version.php')) {
$jms2win_php4_static = 'public static ';

jimport('joomla.log.log');
}

else {

$jms2win_vers_content = file_get_contents(dirname(dirname(dirname(dirname(dirname(dirname(dirname( _FILE__))))))).DS.'libraries'.DS.'joomla'.DS.'version.php');
if ( strpos( '1.6', $jms2win_vers_content) !== false) { $jms2win_php4_static = 'public static '; }
else { $jms2win_php4_static = ''; }
unset( $jms2win_vers_content);
}
}
eval( 'class JInstallerMultisites extends JInstallerMultisites_j15 { '
. $jms2win_php4_static . 'function &getInstance() { return JInstallerMultisites_j15::_getInstance() ; }'
. '}'
) ;
unset( $jms2win_php4_static);

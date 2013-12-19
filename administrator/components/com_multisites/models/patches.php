<?php
// file: patches.php.
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
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
if ( !defined( 'JPATH_MUTLISITES_COMPONENT')) {
define( 'JPATH_MUTLISITES_COMPONENT', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}
if ( !defined( 'MULTISITES_DIR_RIGHTS')) {
define( 'MULTISITES_DIR_RIGHTS', 0755);
}
require_once( JPATH_MUTLISITES_COMPONENT .DS. 'libraries' .DS. 'joomla' .DS. 'application' .DS. 'component' .DS. 'model2win.php');




class MultisitesModelPatches extends JModel2Win
{

var $_modelName = 'patches';

var $_files2patch = array();
var $_corefunction2backup = array(); 
var $_patchesVersion = '';


function &_getPatchList()
{
static $instance;
if ( !isset($instance)) {
$this->_loadExternalPatches();
$instance = $this->_files2patch;
}
return $instance;
}

function _loadExternalPatches()
{
static $isAlreadyLoaded;
$mainframe = &J2WinFactory::getApplication();

if ( isset( $isAlreadyLoaded)) {

return;
}
$isAlreadyLoaded = true;

include_once( JPATH_MUTLISITES_COMPONENT.DS.'patches' .DS. 'patch_collection.php');
$this->_patchesVersion = array();
if ( isset( $patchesVersion)) {
$this->_patchesVersion[] = $patchesVersion;
}

if ( isset( $corefiles2backup) && is_array( $corefiles2backup)) {
$this->_corefunction2backup = array_merge( $this->_corefunction2backup, $corefiles2backup);
}
JPluginHelper::importPlugin('multisites');
$plugins_patchesVersion = $patchesVersion; 
$results = $mainframe->triggerEvent('coreFunctions2Backup', array ( & $plugins_patchesVersion, & $this->_corefunction2backup));

if ( isset( $files2patch) && is_array( $files2patch)) {
$this->_files2patch = array_merge( $this->_files2patch, $files2patch);
}
$results = $mainframe->triggerEvent('files2Patch', array ( & $plugins_patchesVersion, & $this->_files2patch));
if ( !empty( $plugins_patchesVersion)) {

if ( !empty( $patchesVersion)) {
$plugins_patchesVersion = substr( $plugins_patchesVersion, strlen( $patchesVersion));
$plugins_patchesVersion = trim( $plugins_patchesVersion);
$plugins_patchesVersion = trim( $plugins_patchesVersion, '-');
$plugins_patchesVersion = trim( $plugins_patchesVersion);
}
if ( !empty( $plugins_patchesVersion)) {
$this->_patchesVersion[] = $plugins_patchesVersion;
}
}
}


function getCoreFunctionList()
{
$this->_loadExternalPatches();
return $this->_corefunction2backup;
}


function getPatchesVersion()
{
$this->_loadExternalPatches();
if ( !empty( $this->_patchesVersion)) {
return $this->_patchesVersion[0]; 
}
return '';
}


function getAllPatchesVersion()
{
$this->_loadExternalPatches();
return $this->_patchesVersion;
}

function _checkPermissions( $file)
{

$parts = explode( DIRECTORY_SEPARATOR, dirname( __FILE__));
array_pop( $parts );
$jmsFileName = implode( DIRECTORY_SEPARATOR, $parts ) .DIRECTORY_SEPARATOR. 'admin.multisites.php';
$jmsStat = stat( $jmsFileName);
$filename = JPath::clean( JPATH_ROOT.DS.$file);

if ( !JFile::exists( $filename)) {

$filename = dirname( $filename);

if ( !JFolder::exists( $filename)) {

return true;
}
}
$myFileStat = stat( $filename);

if ( $jmsStat['uid'] == $myFileStat['uid']) {

$myFilePerms = fileperms( $filename);
if ( ($myFilePerms & 0x0080) == 0x0080) {
if ( !is_writable( $filename)) {
return 'PATCHES_IS_NOT_WRITABLE';
}
return true;
}
return 'PATCHES_SAME_OWNER_CANNOT_WRITE';
}

else if ( $jmsStat['gid'] == $myFileStat['gid']) {

$myFilePerms = fileperms( $filename);
if ( ($myFilePerms & 0x0010) == 0x0010) {
if ( !is_writable( $filename)) {
return 'PATCHES_IS_NOT_WRITABLE';
}
return true;
}
return 'PATCHES_SAME_GROUP_CANNOT_WRITE';
}

$myFilePerms = fileperms( $filename);
if ( ($myFilePerms & 0x0002) == 0x0002) {
if ( !is_writable( $filename)) {
return 'PATCHES_IS_NOT_WRITABLE';
}
return true;
}
return 'PATCHES_WORLD_CANNOT_WRITE';
}

function _check( $action, $file, $args=array())
{
$fnCheck = '';
if ( is_string( $action)) {
$fnCheck = $action;
}
else if ( is_array( $action)) {
if ( isset( $action['check'])) {
$fnCheck = $action['check'];
}
}
if ( empty( $fnCheck)) {
return '[NOK]|*** ERROR ***| unable to find check action for : ' . var_export( $action);
}

$fn = '_check' . ucfirst(strtolower( $fnCheck));
if ( method_exists( $this, $fn)) {
$status = $this->$fn( $file, $args);

if ( strncmp( $status, '[NOK]', 5) == 0) {
$rc = $this->_checkPermissions( $file);
if ( is_string( $rc)) {
$status = '[NOK]|' . JText::_( $rc) . substr( $status, 5);
}
}
return $status;
}

$this->_loadExternalPatches();
$fn = 'jms2win_check' . ucfirst(strtolower( $fnCheck));
if ( function_exists( $fn)) {
$status = $fn( $this, $file, $args);

if ( strncmp( $status, '[NOK]', 5) == 0) {
$rc = $this->_checkPermissions( $file);
if ( is_string( $rc)) {
$status = '[NOK]|' . JText::_( $rc) . substr( $status, 5);
}
}
return $status;
}

return '[NOK]|*** ERROR ***| Check function "' . $fnCheck . '" does not exists.';
}

function _action( $action, $file, $args=array())
{
$fnAction = '';
if ( is_string( $action)) {
$fnAction = $action;
}
else if ( is_array( $action)) {
if ( isset( $action['action'])) {
$fnAction = $action['action'];
}
}
if ( empty( $fnAction)) {
return '[NOK]|*** ERROR ***| unable to find the action for : ' . var_export( $action);
}

$fn = '_check' . ucfirst(strtolower( $fnAction));
if ( method_exists( $this, $fn)) {
$status = $this->$fn( $file, $args);
return $status;
}

$this->_loadExternalPatches();
$fn = 'jms2win_action' . ucfirst(strtolower( $fnAction));
if ( function_exists( $fn)) {
$status = $fn( $this, $file, $args);
return $status;
}

return '[NOK]|*** ERROR ***| Check function "' . $fnAction . '" does not exists.';
}

static function getInstallationArchivename()
{
$archivename = '';
if ( version_compare( JVERSION, '1.6') >= 0) {
$parts = explode( '.', JVERSION);
$jvers = array_shift($parts);
$jvers .= array_shift($parts);
$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/installation_j'.$jvers.'.zip');
}
else {
$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/installation.tar.gz');
}
return $archivename;
}


static function checkInstallationDownloaded()
{

$archivename = MultisitesModelPatches::getInstallationArchivename();


if ( !class_exists( 'Edwin2WinModelRegistration')) {
return true;
}
$jmsversion = Edwin2WinModelRegistration::getExtensionVersion();
$latestversion = Edwin2WinModelRegistration::getLatestVersion();
$parts = explode( '.', $jmsversion);
$short_vers = array_shift( $parts);
$short_vers .= '_'.array_shift( $parts);
$downloadInfo = array( 'url' => 'http://update.jms2win.com/download/com_multisites/v'.$short_vers.'/patches/'.basename( $archivename),
'destination' => dirname( $archivename)
);

if ( empty( $latestversion)) {

if ( JFile::exists( $archivename)) {
return true;
}
return $downloadInfo;
}

$archiveKey = str_replace( array( '.tar.gz', '.zip'), '', basename( $archivename));
$downloadFile = false;
if ( !empty( $latestversion[$archiveKey])) {

if ( !JFile::exists( $archivename)) {
$downloadFile = true;
}

else {

$archivename_dt = filemtime( $archivename);
$archivename_dt_Str = strftime( '%Y-%m-%d', $archivename_dt);

if ( $archivename_dt_Str < $latestversion[$archiveKey]) {
$downloadFile = true;
}
}
}

else {

if ( JFile::exists( $archivename)) {
return true;
}
$downloadFile = true;
}

if ( $downloadFile) {
@include_once( dirname( dirname( __FILE__)) .DS. 'classes'.DS.'updater.php');
if ( !class_exists( 'MultisitesUpdater')) {
return $downloadInfo;
}
$errors = MultisitesUpdater::downloadPackage( $downloadInfo['url'], $downloadInfo['destination'], false);
if ( !empty( $errors)) {
return $downloadInfo;
}
}
return true;
}


function _restoreInstallation()
{
if ( isset( $this->_renamed_install_dir)
&& strlen( $this->_renamed_install_dir) > 0)
{
$path = JPath::clean( JPATH_ROOT.DS.$this->_renamed_install_dir);
if ( !JFolder::exists( $path)) {
$this->setError( JText::sprintf( 'PATCHES_RENAME_INSTALLDIR_NOTFOUND', $path));
return false;
}
$installdir = JPath::clean( JPATH_ROOT.'/installation');
JFolder::move( $path, $installdir);
}
else
{
$extractdir = JPATH_ROOT;
$archivename = MultisitesModelPatches::getInstallationArchivename();
if ( !JFile::exists( $archivename)) {
$this->setError( JText::_( 'PATCHES_DEPLOY_ERR'));
return true;
}
$result = JArchive::extract( $archivename, $extractdir);
if ( $result === false ) {

if ( version_compare( JVERSION, '3.0.0') == 0) {
if ( JFolder::exists( $extractdir.DS.'installation')) {
return true;
}
}
$this->setError( JText::_( 'PATCHES_DEPLOY_ERR'));
return false;
}
}
return true;
}


function _deployPatches( $patchfile='patches_files.tar.gz')
{

$version = new JVersion();
$shortVers = $version->getShortVersion();
if (version_compare( $shortVers, '1.5.10', '>=')) {
$jversdir = DS. 'j' . $version->getShortVersion();
$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'patches' . $jversdir .DS. $patchfile);
if ( !JFile::exists( $archivename)) {

if ( substr( $patchfile, -7) == '.tar.gz') {

$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'patches' . $jversdir .DS. substr( $patchfile, 0, strlen($patchfile)-7) . '.zip');
if ( !JFile::exists( $archivename)) {
$archivename = ''; 
}
}
else {
$archivename = ''; 
}
}
}

if ( empty( $archivename)) {

if ( version_compare( JVERSION, '2.5') >= 0) {
$patchfile_j25 = str_replace( array( '.tar.gz', '.zip'),
array( '_j25.tar.gz', '_j25.zip'),
$patchfile
);

$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'.$patchfile_j25);
if ( !JFile::exists( $archivename)) {
$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'.$patchfile_j25);

if ( substr( $patchfile_j25, -7) == '.tar.gz') {

$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'. substr( $patchfile_j25, 0, strlen($patchfile_j25)-7) . '.zip');
if ( !JFile::exists( $archivename)) {
$archivename = null;
}
}
}
}

else if ( version_compare( JVERSION, '1.7') >= 0) {
$patchfile_j17 = str_replace( array( '.tar.gz', '.zip'),
array( '_j17.tar.gz', '_j17.zip'),
$patchfile
);

$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'.$patchfile_j17);
if ( !JFile::exists( $archivename)) {
$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'.$patchfile_j17);

if ( substr( $patchfile_j17, -7) == '.tar.gz') {

$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'. substr( $patchfile_j17, 0, strlen($patchfile_j17)-7) . '.zip');
if ( !JFile::exists( $archivename)) {
$archivename = null;
}
}
}
}
else {

if ( version_compare( JVERSION, '1.6') >= 0) {
$patchfile_j16 = str_replace( array( '.tar.gz', '.zip'),
array( '_j16.tar.gz', '_j16.zip'),
$patchfile
);

$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'.$patchfile_j16);
if ( !JFile::exists( $archivename)) {
$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'.$patchfile_j16);

if ( substr( $patchfile_j16, -7) == '.tar.gz') {

$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'. substr( $patchfile_j16, 0, strlen($patchfile_j16)-7) . '.zip');
if ( !JFile::exists( $archivename)) {
$archivename = null;
}
}
}
}
}

if ( empty( $archivename)) {

$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'.$patchfile);
if ( !JFile::exists( $archivename)) {
$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'.$patchfile);

if ( substr( $patchfile, -7) == '.tar.gz') {

$archivename = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches/'. substr( $patchfile, 0, strlen($patchfile)-7) . '.zip');
}
}
}
}
$extractdir = JPATH_ROOT;
$result = JArchive::extract( $archivename, $extractdir);
if ( $result === false ) {
$this->setError( JText::_( 'PATCHES_DEPLOYPATCHES_ERR'));
return false;
}
return true;
}
var $_canInstall = null;


function isPatchesInstalled()
{
$patchlist = $this->_getPatchList();
foreach( $patchlist as $file => $value) {
if ( is_array( $value)) {

$fnCheck = array_shift( $value);
}

else {
$fnCheck = $value; $value = array();
}
$status = $this->_check( $fnCheck, $file, $value);
if ( strncmp( $status, '[NOK]', 5) == 0) {
return false;
}
}
return true;
}


function somePatchesInstalled()
{
$patchlist = $this->_getPatchList();
foreach( $patchlist as $file => $value) {
if ( is_array( $value)) {

$fnCheck = array_shift( $value);
}

else {
$fnCheck = $value; $value = array();
}
$status = $this->_check( $fnCheck, $file, $value);
if ( strncmp( $status, '[OK]', 4) == 0) {
return true;
}
}
return false;
}


function getPatchesStatus()
{
$this->_canInstall = false;
$result = array();
$patchlist = $this->_getPatchList();
foreach( $patchlist as $file => $value) {
if ( is_array( $value)) {

$fnCheck = array_shift( $value);
}

else {
$fnCheck = $value; $value = array();
}
$status = $this->_check( $fnCheck, $file, $value);
if ( strncmp( $status, '[NOK]', 5) == 0) {
$this->_canInstall = true;
}

if ( strncmp( $status, '[IGNORE]', 8) == 0) {
}
else {
$result = array_merge( $result, array( $file => $status));
}
}
return $result;
}


function canInstall()
{

if ( defined( 'MULTISITES_ID')) {
return false;
}
if ( $this->_canInstall == null) {
$this->getPatchesStatus();
}
return $this->_canInstall;
}


function isFn2Backup( $fn)
{
if ( $fn == 'ifPresent' || $fn == 'ifDirPresent' || $fn == 'JMSVers' ){
return false;
}
return true;
}

function isCoreJoomla( $fn)
{
if ( in_array( $fn, $this->getCoreFunctionList())) {
return true;
}
return false;
}

function file_copy( $src, $dest)
{
if ( JFile::exists( $src)) {
return JFile::copy($src, $dest);
}
return false;
}


function backup( $bakdir='backup', $removeBackupDir = false)
{
$this->_backup_files[] = array();
$backup_dir = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. $bakdir);
if ( $removeBackupDir) {

if( JFolder::exists( $backup_dir)
&& !JFolder::delete( $backup_dir)) {
$this->setError( JText::sprintf( 'PATCHES_REMOVE_BAKDIR_ERR', $bakdir));
return false;
}
}

if ( !JFolder::exists( $backup_dir)) {

if ( !JFolder::create( $backup_dir, MULTISITES_DIR_RIGHTS)) {
$this->setError( JText::sprintf( 'PATCHES_CREATE_BAKDIR_ERR', $bakdir));
return false;
}
}

$patchlist = $this->_getPatchList();
foreach( $patchlist as $file => $value) {
if ( is_array( $value)) {

$fnCheck = array_shift( $value);
}

else {
$fnCheck = $value; $value = array();
}
if ( $this->isFn2Backup( $fnCheck)) {
$src = JPath::clean( JPATH_ROOT .DS. $file);
$dest = JPath::clean( $backup_dir .DS. $file);


$status = $this->_check( $fnCheck, $file, $value);
if ( strncmp( $status, '[OK]', 4) == 0
&& JFile::exists( $dest)
)
{

continue;
}

$dest_folder = dirname( $dest);
if ( !JFolder::create( $dest_folder, MULTISITES_DIR_RIGHTS)) {
$this->setError( JText::sprintf( 'PATCHES_CREATE_DEST_FOLDER_ERR', $dest_folder));
return false;
}

if (!$this->file_copy($src, $dest)) {

if ( preg_match( '#^installation#', $file) || !$this->isCoreJoomla( $fnCheck)) { }
else if ( preg_match( '#^administrator/defines#', $file)) { }
else if ( preg_match( '#^defines#', $file)) { }
else {
$this->setError( JText::_( 'PATCHES_BACKUP_ERR'));
return false;
}
}
else {
$this->_backup_files[] = $dest;
}
}
}
return true;
}


function checkBackup( $bakdir='backup')
{
$result = array();
$backup_dir = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. $bakdir);
$patchlist = $this->_getPatchList();
foreach( $patchlist as $file => $value) {
if ( is_array( $value)) {

$fnCheck = array_shift( $value);
}

else {
$fnCheck = $value; $value = array();
}
if ( $this->isFn2Backup( $fnCheck)) {
$filename = JPath::clean( $backup_dir .DS. $file);
if ( !JFile::exists( $filename)) {

if ( preg_match( '#^installation#', $file) || !$this->isCoreJoomla( $fnCheck)) { }
else if ( preg_match( '#^administrator/defines#', $file)) { }
else if ( preg_match( '#^defines#', $file)) { }
else {
$result[] = $filename;
}
}
}
}
return $result;
}


function cleanupPatches()
{
$results = array();
$patchList = array( 'j1.5.3', 'j1.5.4', 'j1.5.5', 'j1.5.6', 'j1.5.7', 'j1.5.8', 'j1.5.9',
'j1.5.10', 'j1.5.11', 'j1.5.12', 'j1.5.12', 'j1.5.13', 'j1.5.14', 'j1.5.15', 'j1.5.16', 'j1.5.17', 'j1.5.18', 'j1.5.19', 'j1.5.20',
'j1.6.0', 'j1.6.1', 'j1.6.2'
);
foreach( $patchList as $patchdir) {
$dir = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'patches' .DS . $patchdir);
if ( JFolder::exists( $dir)) {
if ( JFolder::delete( $dir)) {
$results[] = $patchdir;
}
}
}
return $results;
}


function restore( $bakdir='backup')
{
$result = array();
$rc = true;
$backup_dir = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. $bakdir);
$patchlist = $this->_getPatchList();
foreach( $patchlist as $file => $value) {
if ( is_array( $value)) {

$fnCheck = array_shift( $value);
}

else {
$fnCheck = $value; $value = array();
}
if ( $this->isFn2Backup( $fnCheck)) {
$src = JPath::clean( $backup_dir .DS. $file);
$dest = JPath::clean( JPATH_ROOT .DS. $file);

if ( !JFile::exists( $src)) {
$this->setError( JText::_('PATCHES_BAKFILE_MISSING'));
$rc = false;
}
else {

if ( !is_writable( $dest)) {


$curPermission = @ decoct(@ fileperms( $dest) & 0777);

if ( JPath::isOwner( $dest) && !JPath::setPermissions( $dest, '0644')) {
$rc = false;
continue;
}
}

if (!$this->file_copy($src, $dest)) {
$this->setError( JText::_('PATCHES_RESTORE_ERR'));
$rc = false;
}

if ( isset( $curPermission)) {

JPath::setPermissions( $dest, $curPermission);
unset( $curPermission);
}
}
}
}
return $rc;
}


function checkRestore()
{
$result = array();
$patchlist = $this->_getPatchList();
foreach( $patchlist as $file => $value) {
if ( is_array( $value)) {

$fnCheck = array_shift( $value);
}

else {
$fnCheck = $value; $value = array();
}
$fnUcFirst = ucfirst(strtolower( $fnCheck));

if ( $this->isFn2Backup( $fnUcFirst))
{
$status = $this->_check( $fnCheck, $file, $value);

if ( strncmp( $status, '[OK]', 4) == 0) {
$result[] = $file;
}
}
}
return $result;
}


function installPatches()
{
$this->_patch_file_err = '';
$patchlist = $this->_getPatchList();
foreach( $patchlist as $file => $value) {
if ( is_array( $value)) {

$fnCheck = array_shift( $value);
}

else {
$fnCheck = $value; $value = array();
}

$status = $this->_check( $fnCheck, $file, $value);
if ( strncmp( $status, '[NOK]', 5) == 0) {

if ( !$this->_action( $fnCheck, $file, $value)) {
$this->_patch_file_err = $file;
return false;
}
}
}
return true;
}


function install( $renamed_install_dir='')
{
$mainframe = &J2WinFactory::getApplication();

if ( !$this->backup()) {
return false;
}

$missingFiles = $this->checkBackup();
if ( count( $missingFiles) > 0) {
$msg = JText::_( 'PATCHES_MISSING_FILES');
foreach( $missingFiles as $filename) {
$msg .= '</li><li>' . $filename;
}
$mainframe->enqueueMessage($msg, 'error');
return false;
}
$this->_renamed_install_dir = $renamed_install_dir;

if ( !$this->installPatches()) {
if ( !empty( $this->_patch_file_err)) {
$msg = JText::sprintf( 'PATCHES_ERROR_FILE', $this->_patch_file_err);
$mainframe->enqueueMessage($msg, 'error');
}
$this->restore();
$this->_removeInstallation();
return false;
}



if ( !$this->installPatches()) {
$this->restore();
$this->_removeInstallation();
return false;
}
return true;
}


function _undoPatches()
{
$mainframe = &J2WinFactory::getApplication();
$result = array();

if ( !$this->somePatchesInstalled()) {

return $result;
}

if ( $this->restore()) {


$result = $this->checkRestore();
if ( count( $result) <= 0) {
return $result;
}
}

if ( $this->restore( 'backup_on_install')) {

$result = $this->checkRestore();
if ( count( $result) <= 0) {
return $result;
}
}

if ( $this->_deployPatches( 'restore_files.tar.gz')) {

$result = $this->checkRestore();
if ( count( $result) <= 0) {
return $result;
}
}
return $result;
}


function _removeFiles()
{
$result = array();
$patchlist = $this->_getPatchList();
foreach( $patchlist as $file => $value) {
if ( is_array( $value)) {

$fnCheck = array_shift( $value);
}

else {
$fnCheck = $value; $value = array();
}
if ( $fnCheck == 'ifPresent') {
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( JFile::exists( $filename)) {
JFile::delete( $filename);
}
if ( JFile::exists( $filename)) {
$result[] = $filename;
}
}
else if ( $fnCheck == 'ifCMSPresent') {
$dir = dirname( JPath::clean( JPATH_ROOT.DS.$file));
if ( JFolder::exists( $dir)) {
JFolder::delete( $dir);
}
if ( JFolder::exists( $dir)) {
$result[] = $dir;
}
}
}
return $result;
}


function _removeInstallation()
{
$inst_dir = JPath::clean( JPATH_ROOT . '/installation');

if ( !JFolder::exists($inst_dir)) {

return true;
}

$del_dir = JPath::clean( JPATH_ROOT . '/installation_to_delete');

if ( JFolder::exists( $del_dir)) {
for ( $i=1; ; $i++) {
$del_dir = JPath::clean( JPATH_ROOT . '/installation_to_delete_' . $i);
if ( !JFolder::exists( $del_dir)) {
break;
}
}
}
return rename( $inst_dir, $del_dir);
}


function uninstall()
{
$rc = true;
$err = '';

$result = $this->_undoPatches();
if ( count( $result)>0) {
$err .= JText::_('PATCHES_UNDOPATCHES_ERR');
foreach( $result as $filename) {
$err .= '</li><li>'
. $filename;
}
$rc = false;
}

$result = $this->_removeFiles();
if ( count( $result)>0) {
$err .= JText::_('PATCHES_REMOVEFILES_ERR');
foreach( $result as $filename) {
$err .= '</li><li>'
. $filename;
}
$rc = false;
}

if ( !$this->_removeInstallation()) {
if ($err!=null) {
$err .= '</li><li>';
}
$err .= JText::_('PATCHES_REMOVEINSTALL_ERR');
$rc = false;
}
if ( $err != '') {
$this->setError( $err);
return false;
}
return true;
}


function searchInstallationDirectories()
{
$result = array();
$dirs = JFolder::folders( JPATH_ROOT);
foreach( $dirs as $dir) {
$filename = JPATH_ROOT .DS. $dir .DS. 'localise.xml';
if ( JFile::exists( $filename)) {
$result[] = $dir;
}
}
return $result;
}


function checkUpdates( $url, $reginfo, $ignoreVersion=false)
{
$option = JRequest::getCmd('option');
jimport('joomla.installer.helper');
if ( !isset( $reginfo['product_key'])) {
return JText::_( 'PATCHES_UPDATE_NEEDREG_ERR');
}
if ( $ignoreVersion) {
$version = '1';
}
else {

$version = $this->getPatchesVersion();
}
$vars = array( 'option' => 'com_docman',
'task' => 'downloadkey',
'keyref' => $option.'.checkupdates',
'version' => $version,
'product_key' => $reginfo['product_key']);

$urlencoded = "";
while (list($key,$value) = each($vars))
$urlencoded.= urlencode($key) . "=" . urlencode($value) . "&";
$urlencoded = substr($urlencoded,0,-1);

if ( !strstr( $url, '?')) {
$url .= '?' . $urlencoded;
}
else {
$url .= $urlencoded;
}

$p_file = JInstallerHelper::downloadPackage($url, 'jms2win_checkupdates.zip');
if ( $p_file === false) {

return false;
}
$config =& JFactory::getConfig();
$tmp_dest = $config->get('tmp_path');
$filename = $tmp_dest.DS.$p_file;


$content = file_get_contents( $filename);
if ( stristr( $content, '<html')) {
return JText::_( 'PATCHES_UPDATE_DOWNLOAD_ERR');
}

$extractdir = JPath::clean( JPATH_MUTLISITES_COMPONENT.'/patches');
$result = JArchive::extract( $filename, $extractdir);
if ( $result === false ) {
return JText::_( 'PATCHES_UPDATE_ERR');
}
return JText::_( 'PATCHES_UPDATE_SUCCESS');
}
} 

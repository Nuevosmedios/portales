<?php
// file: debug.php.
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


if( !defined( '_EDWIN2WIN_' ) && !defined( '_JEXEC' ) ) die( 'Restricted access.' );




if ( !class_exists( 'Debug2Win'))
{
class Debug2Win
{





static function &_getDebugLevel()
{
static $instance;
if (!isset( $instance )) {
$instance = 0;
}
return $instance;
}

static function &_getDebugPrefix()
{
static $instance;
if (!isset( $instance )) {
$instance = "";
}
return $instance;
}

static function _setDebugPrefix( $new_prefix='')
{
$prefix =& Debug2Win::_getDebugPrefix();
$prefix = $new_prefix;
}







static function &getFileName()
{
static $instance;
if (!isset( $instance )) {
$instance = 'debug.log.php';
}
return $instance;
}

static function setFileName( $filename)
{
$instance =& Debug2Win::getFileName();
$instance = $filename;
}

static function &isDebug()
{
static $instance;
if (!isset( $instance )) {
$instance = false;
}
return $instance;
}

static function enableDebug()
{
$instance =& Debug2Win::isDebug();
$instance = true;
}

static function disableDebug()
{
$instance =& Debug2Win::isDebug();
$instance = false;
}

static function &isStandalone()
{
static $instance;
if (!isset( $instance )) {
$instance = false;
}
return $instance;
}


static function enableStandalone()
{
$instance =& Debug2Win::isStandalone();
$instance = true;
}

static function disableStandalone()
{
$instance =& Debug2Win::isStandalone();
$instance = false;
}

static function debug($message, $blockComment=false)
{
$isDebug =& Debug2Win::isDebug();
if ( !$isDebug) {
return;
}
$filename = Debug2Win::getFileName();
$isStandalone =& Debug2Win::isStandalone();
$prefix =& Debug2Win::_getDebugPrefix();
$level =& Debug2Win::_getDebugLevel();

if ( !$isStandalone && function_exists( 'jimport')) {

jimport('joomla.error.log');
$log = &JLog::getInstance( $filename);
$comment = $prefix . str_repeat(" ", $level * 3) . $message;
$log->addEntry(array('comment' => $comment));
return;
}

$FTPOptions = array();
if ( defined( 'JPATH_LIBRARIES')) {

if ( file_exists( JPATH_LIBRARIES.DS.'import.php')) {
require_once( JPATH_LIBRARIES.DS.'import.php');
}

else {
require_once( JPATH_LIBRARIES.DS.'joomla'.DS.'import.php');
}
jimport( 'joomla.filesystem.path' );
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.filesystem.file' );

jimport('joomla.client.helper');
$parts = explode( DIRECTORY_SEPARATOR, dirname( __FILE__));
array_pop( $parts );
array_pop( $parts );
array_pop( $parts );
array_pop( $parts );
$parts[] = 'configuration.php';
$configname = implode( DIRECTORY_SEPARATOR, $parts );
$configdata = JFile::read( $configname);
$p1 = strpos( $configdata, 'class JConfig {');
$config = array();
if ( $p1 === false) { }
else {
$p1 += 15;
$statements = substr( $configdata, $p1);
$str = str_replace( 'var ', '', $statements);
$lines = explode( "\n", $str);
foreach ($lines as $line) {
$variable = explode( '=', $line);
if ( count( $variable) == 2) {
$value = trim($variable[1]);
$value = ltrim( $value, "'");
$value = rtrim( $value, "';");
$config[trim($variable[0])] = $value;
}
}
}

if ( isset( $config['$ftp_enable'])) { $FTPOptions['enabled'] = $config['$ftp_enable']; }
if ( isset( $config['$ftp_host'])) { $FTPOptions['host'] = $config['$ftp_host']; }
if ( isset( $config['$ftp_port'])) { $FTPOptions['port'] = $config['$ftp_port']; }
if ( isset( $config['$ftp_user'])) { $FTPOptions['user'] = $config['$ftp_user']; }
if ( isset( $config['$ftp_pass'])) { $FTPOptions['pass'] = $config['$ftp_pass']; }
if ( isset( $config['$ftp_root'])) { $FTPOptions['root'] = $config['$ftp_root']; }
}


$dir = dirname( __FILE__);
$log_path = $dir .DIRECTORY_SEPARATOR. 'logs';
if ( !is_dir($log_path)) {
if ( class_exists( 'JFolder')) {
JFolder::create( $log_path);
}
else {
mkdir( $log_path, 0755);
}
}

$myFilename = $log_path .DIRECTORY_SEPARATOR. $filename;

if ( !empty( $FTPOptions) && $FTPOptions['enabled'] == 1) {

jimport('joomla.client.ftp');
$ftp = & JFTP::getInstance($FTPOptions['host'], $FTPOptions['port'], null, $FTPOptions['user'], $FTPOptions['pass']);
if ( !file_exists( $myFilename)){
$buffer = "<?php\r\ndie( 'Access forbidden');\r\n";
}
else {
$buffer = JFile::read( $myFilename);
}
if ( $blockComment) {
$buffer .= "/*" . $prefix . str_repeat(" ", $level * 3) . $message . "*/\r\n";
}
else {
$buffer .= "//" . $prefix . str_repeat(" ", $level * 3) . $message . "\r\n";
}

$myFilename = JPath::clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $myFilename), '/');
$ret = $ftp->write($myFilename, $buffer);
}

else {
if ( !file_exists( $myFilename)){
$handle = fopen( $myFilename, "a+");


fwrite($handle, "<?php\r\ndie( 'Access forbidden');\r\n");
}
else {
$handle = fopen( $myFilename, "a+");
}
if ( $blockComment) {
fwrite($handle, "/*" . $prefix . str_repeat(" ", $level * 3) . $message . "*/\r\n");
}
else {
fwrite($handle, "//" . $prefix . str_repeat(" ", $level * 3) . $message . "\r\n");
}
fclose($handle);
}
}


static function debug_Start($message, $dbg_prefix="")
{
$level = Debug2Win::_getDebugLevel();
Debug2Win::_setDebugPrefix( $dbg_prefix);
Debug2Win::debug( $message);
$level++;
}

static function debug_Stop($message)
{
$level = Debug2Win::_getDebugLevel();
$level--;
Debug2Win::debug( $message);
}
} 
}

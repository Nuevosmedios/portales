<?php
// file: jms2winpath.php.
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



class Jms2WinPath extends JPath
{


function ftpFileDetails( $path)
{
$results = array();

jimport('joomla.client.helper');
$ftpOptions = JClientHelper::getCredentials('ftp');
if ($ftpOptions['enabled'] == 1) {

jimport('joomla.client.ftp');
$ftp = &JFTP::getInstance(
$ftpOptions['host'], $ftpOptions['port'], null,
$ftpOptions['user'], $ftpOptions['pass']
);

$parts = preg_split('/\/|\\\\/', $path);
$name = array_pop($parts);
$parent = implode( DS, $parts);
$list = $ftp->listDetails( $parent);
foreach( $list as $row) {
if ( $row['name'] == $name) {
return $row;
}
}
}
return $results;
}


function fileperms( $path, $forceFTP=false)
{

$perms = @fileperms( $path);
if ( ($perms === false && !empty( $path))
|| ($forceFTP && !empty( $path))
)
{
$row = Jms2WinPath::ftpFileDetails( $path);
if ( !empty( $row)) {
$rights = $row['rights']; 
$owner = 0;
if ( substr( $rights, 1, 1) == 'r') { $owner |= 0x04; }
if ( substr( $rights, 2, 1) == 'w') { $owner |= 0x02; }
if ( substr( $rights, 3, 1) == 'x') { $owner |= 0x01; }
$group = 0;
if ( substr( $rights, 4, 1) == 'r') { $group |= 0x04; }
if ( substr( $rights, 5, 1) == 'w') { $group |= 0x02; }
if ( substr( $rights, 6, 1) == 'x') { $group |= 0x01; }
$world = 0;
if ( substr( $rights, 7, 1) == 'r') { $world |= 0x04; }
if ( substr( $rights, 8, 1) == 'w') { $world |= 0x02; }
if ( substr( $rights, 9, 1) == 'x') { $world |= 0x01; }
$perms = '0'.$owner.$group.$world;
}
}
return $perms;
}


function chmod( $path, $mode)
{

if ($path == '') {
$path = '.';
}

if (is_int($mode)) {
$mode = decoct($mode);
}

if (!@ chmod($path, octdec($mode))) {

jimport('joomla.client.helper');
$ftpOptions = JClientHelper::getCredentials('ftp');
if ($ftpOptions['enabled'] == 1) {

jimport('joomla.client.ftp');
$ftp = &JFTP::getInstance(
$ftpOptions['host'], $ftpOptions['port'], null,
$ftpOptions['user'], $ftpOptions['pass']
);
if ( $ftp->chmod($path, $mode)) {
return true;
}
return false;
}

else {
return false;
}
}
return true;
}


function _write_file_content( $filename, $content)
{
$fp = fopen( $filename, "w");
if ( !empty( $fp)) {
fputs( $fp, $content);
fclose( $fp);
return true;
}
return false;
}


function getServerInfo()
{
static $serverInfos;
if (isset( $serverInfos )) {
return $serverInfos;
}
$serverInfos = array();

$tmp = uniqid('multisites_') . '.txt';;
$ssp = ini_get('session.save_path');
$jtp = getcwd() .DIRECTORY_SEPARATOR.'tmp';
$cur = getcwd();
$content = 'this file can be deleted';

$dir = Jms2WinPath::_write_file_content( "/tmp/$tmp", $content) ? '/tmp' : false;
if ( $dir === false) { if ( Jms2WinPath::_write_file_content( $ssp.DIRECTORY_SEPARATOR.$tmp, $content)) { $dir = $ssp; }}
if ( $dir === false) { if ( Jms2WinPath::_write_file_content( $jtp.DIRECTORY_SEPARATOR.$tmp, $content)) { $dir = $jtp; }}
if ( $dir === false) { if ( Jms2WinPath::_write_file_content( $cur.DIRECTORY_SEPARATOR.$tmp, $content)) { $dir = $cur; }}

if (($dir !== false))
{
$return = -1;
$test = $dir.DIRECTORY_SEPARATOR.$tmp;

$serverInfos['owner_id'] = fileowner($test);
if ( function_exists( 'posix_getpwuid')) {
$userinfo = posix_getpwuid( $serverInfos['owner_id']);
if ( !empty( $userinfo['name'])) {
$serverInfos['owner_name'] = $userinfo['name'];
}
}

$serverInfos['group_id'] = filegroup($test);
if ( function_exists( 'posix_getgrgid')) {
$groupid = getmygid();
$groupinfo = posix_getgrgid($groupid);
if ( !empty( $groupinfo['name'])) {
$serverInfos['group_name'] = $groupinfo['name'];
}
}

if ( file_exists( $test)) {
unlink( $test);
}
}
return $serverInfos;
}
} 

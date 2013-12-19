<?php
// file: jms2winfile.php.
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
require_once( dirname( __FILE__).DS. 'jms2winpath.php');
define( 'PUBLIC2WIN', 'public static');



class Jms2WinFile_php4 extends JFile
{


function _exists( $filename)
{
if ( parent::exists($filename)) {
return true;
}
if ( !defined( 'MULTISITES_REDIRECT_FTP') || !(MULTISITES_REDIRECT_FTP)) {
return false;
}

jimport('joomla.client.helper');
$ftpOptions = JClientHelper::getCredentials('ftp');
if ($ftpOptions['enabled'] == 1)
{

jimport('joomla.client.ftp');
$ftp = &JFTP::getInstance(
$ftpOptions['host'], $ftpOptions['port'], null,
$ftpOptions['user'], $ftpOptions['pass']
);

$parts = preg_split('/\/|\\\\/', $filename);
$name = array_pop($parts);
$parent = implode( DS, $parts);
$results = $ftp->listDetails( $parent);

if ( !empty( $results)) {
foreach( $results as $row) {
if ( $row['name'] == $name) {
$flag = substr( $row['rights'], 0, 1); 

if ( $flag == '-') {
return true;
}

else if ( $flag == 'l') {

return true;
}
return false;
}
}
}
}
return false;
}


function isFilePresentIn_open_basedir( $filename)
{

$env_open_basedir = ini_get('open_basedir');
if ( empty( $env_open_basedir)) {
return true;
}

$obd = explode( ':', $env_open_basedir);
foreach( $obd as $dir) {
if ( substr( $filename, 0, strlen( $dir)) == $dir) {
return true;
}
}
return false;
}


function isFTP_writable( $filename)
{


jimport('joomla.client.helper');
$ftpOptions = JClientHelper::getCredentials('ftp');
if ($ftpOptions['enabled'] == 1)
{
$infos = Jms2WinPath::ftpFileDetails( $filename);
if ( !empty( $infos)) {
$rights = $infos['rights']; 
$user = $infos['user']; 

if ( !empty( $user) && $user == $ftpOptions['user']) {
if ( substr( $rights, 2, 1) == 'w') {
return true;
}
}

else if ( !empty( $group)) {
if ( substr( $rights, 5, 1) == 'w') {
return true;
}
}

else {
if ( substr( $rights, 8, 1) == 'w') {
return true;
}
}
}
}
return false;
}


function is_writable( $filename)
{

if ( is_writable( $filename)) {
$result = true;

$serverInfos = Jms2WinPath::getServerInfo();
if ( !empty( $serverInfos)) {

if ( fileowner( $filename) == $serverInfos['owner_id']) {
if ( Jms2WinFile::isFilePresentIn_open_basedir( $filename)) {
return true;
}
}

else if ( filegroup( $filename) == $serverInfos['group_id']) {
$mode = @fileperms( $path);

if (is_int($mode)) {
$mode = decoct($mode);
}
$group = (int)substr( $mode, 2, 1);

if ( ($group & 0x02) == 0x02) {
if ( Jms2WinFile::isFilePresentIn_open_basedir( $filename)) {
return true;
}
}
}

else {
$mode = @fileperms( $path);

if (is_int($mode)) {
$mode = decoct($mode);
}
$world = (int)substr( $mode, 3, 1);

if ( ($world & 0x02) == 0x02) {
if ( Jms2WinFile::isFilePresentIn_open_basedir( $filename)) {
return true;
}
}
}


$result = false;
}


jimport('joomla.client.helper');
$ftpOptions = JClientHelper::getCredentials('ftp');
if ($ftpOptions['enabled'] == 1) {
return Jms2WinFile::isFTP_writable( $filename);
}
return $result;
}

else {
jimport('joomla.client.helper');
$ftpOptions = JClientHelper::getCredentials('ftp');
if ($ftpOptions['enabled'] == 1) {
return Jms2WinFile::isFTP_writable( $filename);
}
return false;
}
}
} 




if ( version_compare( JVERSION, '1.6') >= 0) { $jms2win_php4_static = 'public static '; }
else { $jms2win_php4_static = ''; }
eval( 'class Jms2WinFile extends Jms2WinFile_php4 { '
. $jms2win_php4_static . 'function exists($path) { return Jms2WinFile::_exists($path); }'
. '}'
) ;
unset( $jms2win_php4_static);

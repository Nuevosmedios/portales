<?php
// file: jms2winfolder.php.
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




class Jms2WinFolder_php4 extends JFolder
{


static function _exists($file)
{
if ( parent::exists($file)) {
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

$parts = preg_split('/\/|\\\\/', $file);
$name = array_pop($parts);
$parent = implode( '/', $parts);
$results = $ftp->listDetails( $parent);

if ( !empty( $results)) {

foreach( $results as $row) {
if ( $row['name'] == $name) {
$flag = substr( $row['rights'], 0, 1); 

if ( $flag == 'd') {
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


static function deleteJoomlaFiles( $rootPath)
{
jimport('joomla.filesystem.path');
jimport('joomla.filesystem.file');

@include( dirname(__FILE__).'/joomlafiles.php');
if ( empty( $joomlaFilesOrDirs) || !is_array( $joomlaFilesOrDirs)) {
return 'List of joomla files or directories does not exists! File "'.dirname(__FILE__).'/joomlafiles.php'.'" is missing';
}
$errors = array();
foreach( $joomlaFilesOrDirs as $fileOrDir) {
$wildcard = basename( $fileOrDir) == '*';
if ( $wildcard) {
$path = rtrim( $rootPath, '/\\') .'/'.ltrim( dirname( $fileOrDir), '/\\');
JFolder::delete( $path);
}
else {
$path = JPath::clean( rtrim( $rootPath, '/\\') .'/'.ltrim( $fileOrDir, '/\\'));

if ( is_link( $path)) {
JFile::delete( $path);
}

else if ( is_dir( $path)) {

$files = JFolder::files($path, '.', false, false, array());
$folders = JFolder::folders($path, '.', false, true, array());
if (!empty($files) || !empty( $folders)) {
$errors[] = 'Directory is not empty! Cannot delete directory : "'.$path.'"';
}

else {
JFolder::delete( $path);
}
}

else {
JFile::delete( $path);
}
}
}
return $errors;
}


static function deleteSymLinks( $rootPath)
{
$path = $rootPath;
$files = JFolder::files($path, '.', false, true, array());
foreach( $files as $file) {
if ( is_link( $file)) {

@unlink( $file);

if ( is_link( $file) || is_file( $file) || is_dir( $file)) {

JFile::delete( $file);
}
}

else if ( in_array( basename( $file), array( 'index.php', 'index2.php'))) {
$content = JFile::read( $file);
if ( strpos( $content, 'eval(') !== false) {

JFile::delete( $file);
}
}
}
$folders = JFolder::folders($path, '.', false, true, array());
foreach( $folders as $folder) {

if ( is_link( $folder)) {

@unlink($folder);

if ( is_link( $folder) || is_file( $folder) || is_dir( $folder)) {
JFile::delete( $folder);
}
}

else if ( is_dir( $folder)) {

Jms2WinFolder::deleteSymLinks( $folder);
}
}

$files = JFolder::files($path, '.', false, true, array());
$folders = JFolder::folders($path, '.', false, true, array());
if ( empty($files) && empty( $folders)) {
JFolder::delete( $path);
}
}
} 




if ( version_compare( JVERSION, '1.6') >= 0) { $jms2win_php4_static = 'public static '; }
else { $jms2win_php4_static = ''; }
eval( 'class Jms2WinFolder extends Jms2WinFolder_php4 { '
. $jms2win_php4_static . 'function exists($path) { return Jms2WinFolder::_exists($path); }'
. '}'
) ;
unset( $jms2win_php4_static);

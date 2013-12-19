<?php
// file: jms2winftp.php.
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
jimport('joomla.client.ftp');




if ( !defined( 'MULTISITES_REDIRECT_FTP') || !(MULTISITES_REDIRECT_FTP)) {
class Jms2WinFTP extends JFTP {
function fileperms( $path) { return false; }
function fileDetails( $path) { $none = array(); return $none; }
}
}

else {
class Jms2WinFTP extends JFTP
{


function &getInstance($host = '127.0.0.1', $port = '21', $options = null, $user = null, $pass = null, $path_root=null, $ftp_root=null,
$orig_ftp_enable=null, $orig_ftp_root=null)
{
$instance =& parent::getInstance( $host, $port, $options, $user, $pass);

if ( is_a( $instance, 'Jms2WinFtp')) {}

else {

$orig_instance = $instance;
if ( is_null( $orig_ftp_root)) {
$config =& JFactory::getConfig();
$orig_ftp_root = $config->getValue('config.ftp_root');
}

$instance = new Jms2WinFtp($options);

$instance->_orig_instance =& $orig_instance;
$instance->_orig_host = $host;
$instance->_orig_port = $port;
$instance->_orig_options = $options;
$instance->_orig_user = $user;
$instance->_orig_pass = $pass;
$instance->_orig_ftp_enable = $orig_ftp_enable;
$instance->_orig_ftp_root = $orig_ftp_root;




$instance->_new_dir = $path_root;
$instance->_new_real_dir = $this->_real_path( $path_root);
$instance->_new_ftp = $ftp_root;
}
return $instance;
}


function restoreOriginalInstance()
{
if ( !empty( $this->_orig_instance)) {
$instance =& parent::getInstance( $this->_orig_host, $this->_orig_port, $this->_orig_options, $this->_orig_user, $this->_orig_pass);
if ( is_a( $instance, 'Jms2WinFTP')) {
$instance =& $this->_orig_instance;
}
}
}


function _real_path( $path)
{
$result = realpath( $path);

if ( $result === false) {

$parts = preg_split('/\/|\\\\/', $path);
$n = count( $parts);
for ( $i=0; $i<$n; ) {
if ( $parts[$i] == '..') {
if ( $i>0 && $parts[$i-1] != '..') {

for ( $j=$i+1; $j<$n; $j++) {
$parts[$j-2]=$parts[$j];
}
array_pop($parts);
array_pop($parts);
$n = count( $parts);
$i--;
}
else {
$i++;
}
}
else {
$i++;
}
}
$result = implode( DS, $parts);
}
return $result;
}

function _replace_maxlen( $maxlen_array, $replace_str, $path)
{
$max_indice = 0;
for( $i=1; $i<count( $maxlen_array); $i++) {
if ( strlen( $maxlen_array[ $i]) > strlen( $maxlen_array[ $max_indice])) {
$max_indice = $i;
}
}
$result = str_replace( $maxlen_array[ $max_indice], $replace_str, $path);
return $result;
}


function normalizedPath( $path, $normalize=true)
{
if ( !$normalize) {
return $path;
}
$result = $path;

$clean_path = JPath::clean( $path);
$clean_orig_dir = JPath::clean( JPATH_ROOT);
if ( !empty( $this->orig_ftp_root)) { $clean_orig_ftp = JPath::clean( $this->orig_ftp_root); }
else { $clean_orig_ftp = null; }

if ( !empty( $this->_new_ftp)) { $clean_new_ftp = JPath::clean( $this->_new_ftp); }
else { $clean_new_ftp = null; }
if ( !empty( $this->_new_dir)) { $clean_new_dir = JPath::clean( $this->_new_dir); }
else { $clean_new_dir = null; }

$is_orig_ftp = false;
if ( !empty( $clean_orig_ftp)) {
if ( substr( $clean_path, 0, strlen( $clean_orig_ftp)) == $clean_orig_ftp) {
$is_orig_ftp = true;
}
}
$is_orig_dir = false;
if ( !empty( $clean_orig_dir)) {
if ( substr( $clean_path, 0, strlen( $clean_orig_dir)) == $clean_orig_dir) {
$is_orig_dir = true;
}
}
$is_new_ftp = false;
if ( !empty( $clean_new_ftp)) {
if ( substr( $clean_path, 0, strlen( $clean_new_ftp)) == $clean_new_ftp) {
$is_new_ftp = true;
}
}
$is_new_dir = false;
if ( !empty( $clean_new_dir)) {
if ( substr( $clean_path, 0, strlen( $clean_new_dir)) == $clean_new_dir) {
$is_new_dir = true;
}
}


if ( !$is_orig_ftp) {

if ( !$is_orig_dir) {

if ( !$is_new_ftp) {

if ( !$is_new_dir) {

return( $path);
}

else {

$result = str_replace( $clean_new_dir, $clean_new_ftp, $clean_path);
}
}

else {

if ( !$is_new_dir) {

return( $path);
}

else {

$result = Jms2WinFTP::_replace_maxlen( array( $clean_new_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
}
}
}

else {
if ( !$is_new_ftp) {

if ( !$is_new_dir) {

$result = str_replace( $clean_orig_dir, $clean_new_ftp, $clean_path);
}

else {

$result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_dir, $clean_new_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
}
}
else {

if ( !$is_new_dir) {

$result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
}

else {

$result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_dir, $clean_new_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
}
}
}
}

else {

if ( !$is_orig_dir) {

if ( !$is_new_ftp) {

if ( !$is_new_dir) {

$str = str_replace( $clean_orig_ftp, $clean_orig_dir, $clean_path);
if ( substr( $str, 0, strlen( $clean_new_dir)) == $clean_new_dir) {

$result = str_replace( $clean_new_dir, $clean_new_ftp, $str);
}
else {

$result = str_replace( $clean_orig_ftp, $clean_new_ftp, $clean_path);
}
}

else {

$result = str_replace( $clean_new_dir, $clean_new_ftp, $clean_path);
}
}

else {

if ( !$is_new_dir) {

return( $path);
}

else {

$result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_ftp, $clean_new_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
}
}
}

else {
if ( !$is_new_ftp) {

if ( !$is_new_dir) {

$result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_ftp, $clean_orig_dir), $clean_new_ftp, $clean_path);
}

else {

$result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_ftp, $clean_orig_dir, $clean_new_dir), $clean_new_ftp, $clean_path);
}
}
else {

if ( !$is_new_dir) {

$result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_ftp, $clean_orig_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
}

else {

$result = Jms2WinFTP::_replace_maxlen( array( $clean_orig_ftp, $clean_orig_dir, $clean_new_dir, $clean_new_ftp), $clean_new_ftp, $clean_path);
}
}
}
}

$parts = preg_split('/\/|\\\\/', $result);
$result = implode( '/', $parts);
return $result;

}


function fileDetails( $path)
{
$none = array();
if ( empty( $path )) {
return $none;
}

$parts = preg_split('/\/|\\\\/', $path);
$name = array_pop($parts); 
$dir = implode( '/', $parts); 
$list = parent::listDetails( $dir);
foreach( $list as $row) {
if ( $row['name'] == $name) {
return $row;
}
}
return $none;
}


function fileperms( $path)
{
$result = false;
$row = Jms2WinFTP::fileDetails( $path);
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
$result = '0'.$owner.$group.$world;
}
return $result;
}
function chmod($path, $mode, $normalize=true) { return parent::chmod( $this->normalizedPath( $path, $normalize), $mode); }
function create($path, $normalize=true) { return parent::create( $this->normalizedPath( $path, $normalize)); }
function mkdir($path, $normalize=true) {
$arr = debug_backtrace();
return parent::mkdir( $this->normalizedPath( $path, $normalize));
}
function listDetails($path = null, $type = 'all') { return parent::listDetails( $this->normalizedPath( $path), 'all'); }
} 
}

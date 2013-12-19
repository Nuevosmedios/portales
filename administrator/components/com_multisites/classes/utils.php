<?php
// file: utils.php.
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
@include_once( dirname( __FILE__).DIRECTORY_SEPARATOR. 'lettertree.php');




class MultisitesUtils
{


static function CnvArray2Str( $leadingSpaces, $arr)
{
$result = '';
$sep = '';
$inline=true;
foreach( $arr as $key => $value) {
if ( is_array( $value)) {
$inline = false;
if ( is_int( $key)) {
$result .= $sep . "array( " . MultisitesUtils::CnvArray2Str( $leadingSpaces.'  ', $value) . ")";
}
else {
$result .= $sep . "'" . addslashes($key) . "' => array( " . MultisitesUtils::CnvArray2Str( $leadingSpaces.'  ', $value) . ")";
}
}
else {
if ( is_int( $key)) {
$result .= $sep . "'" . addslashes($value) . "'";
}
else {
$result .= $sep . "'" . addslashes($key) . "' => '" . addslashes($value) . "'";
}
}
if ( $inline) {
$sep = ", ";
}
else {
$sep = ",\n" . $leadingSpaces;
}
}
return $result;
}


static function &getSiteInfo( $site_id)
{
require_once( dirname( __FILE__) .DS. 'site.php');
$site = & Site::getInstance( $site_id);
return $site;
}


static function updateSiteInfo( $site_id, $values)
{
$domains = null;

$newDBInfo = null;
$config_dirs = null;

$site_dir = JPATH_MULTISITES .DS. $site_id;
$filename = $site_dir .DS. 'config_multisites.php';
if ( !file_exists( $filename))
{
if ( class_exists( 'MultisitesLetterTree')) {

$lettertree_dir = MultisitesLetterTree::getLetterTreeDir( $site_id);
if( !empty( $lettertree_dir)) {
$site_dir = JPATH_MULTISITES.DIRECTORY_SEPARATOR.$lettertree_dir;
$filename = $site_dir.DIRECTORY_SEPARATOR.'config_multisites.php';
}
}
}
if ( file_exists( $filename))
{
include $filename;

foreach( $values as $key => $value) {
$newDBInfo[$key] = $value;
}

if ( empty( $indexDomains)) {

$indexDomains = $domains;
}

require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS. 'models' .DS. 'manage.php');
return MultisitesModelManage::writeSite( $site_dir, $domains, $indexDomains, $newDBInfo, $config_dirs);
}
}


static function updateStatus( $key, $value, $newStatus, $apply2AllSites = false)
{
$result = false;
$updateIndex = false;

require_once( dirname( dirname( __FILE__)).DS. 'models' .DS. 'manage.php');
@include_once( dirname( dirname( __FILE__)).DS.'multisites.cfg.php' );
$model = new MultisitesModelManage();
$sites = $model->getSites();

foreach( $sites as $site) {

if ( isset( $site->$key) && !empty( $site->$key) && $site->$key == $value) {
$site_id = $site->id;
if ( class_exists( 'Debug2Win')) {
Debug2Win::debug( "- $key [$value] found in site id [$site_id]");
}
$curStatus = !empty( $site->status)
? $site->status
: '';

if ( $curStatus != $newStatus) {

$values = array();
$values['status'] = $newStatus;
MultisitesUtils::updateSiteInfo( $site_id, $values);
$updateIndex = true;
}
$result = true;
if ( $apply2AllSites) {

}
else {
break;
}
}
}

if ( $updateIndex) {

$model->createMasterIndex();
}
return $result;
}


function createMasterIndex()
{

require_once( dirname( dirname( __FILE__)).DS. 'models' .DS. 'manage.php');
$model = new MultisitesModelManage();
$model->createMasterIndex();
}
}

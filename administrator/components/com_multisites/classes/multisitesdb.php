<?php
// file: multisitesdb.php.
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
@include_once( dirname( __FILE__) .DIRECTORY_SEPARATOR. 'lettertree.php');
require_once( dirname( dirname( __FILE__)) .DIRECTORY_SEPARATOR. 'libraries'.DIRECTORY_SEPARATOR.'joomla'.DIRECTORY_SEPARATOR.'database'.DIRECTORY_SEPARATOR.'j2windb.php');
require_once( dirname( __FILE__) .DIRECTORY_SEPARATOR. 'multisitesdblight.php');




class MultisitesDatabase extends MultisitesDatabaseLight
{


static function generatePassword( $aPasswordLen=10)
{

$initStr = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_.,;:=-+*/@#$£!(){}[]<>§';

mt_srand((double)microtime()*1000000);
$rndStr = "";
for( ; strlen($initStr)>0; )
{
$len = strlen( $initStr);
$iPos = mt_rand( 0, $len-1);
$rndStr .= substr( $initStr, $iPos, 1);

if ($iPos == 0) {
$initStr = substr( $initStr, 1);
}

else if ( $iPos == ($len - 1)) {
$initStr = substr( $initStr, 0, $iPos);
}

else {
$initStr = substr( $initStr, 0, $iPos) . substr( $initStr, $iPos+1);
}
}

$len = strlen( $rndStr);
$psw = "";
for ( $i=0; $i<$aPasswordLen; $i++) {
$iPos = mt_rand( 0, $len-1);
$psw .= substr( $rndStr, $iPos, 1);
}
return $psw;
}


static function generatePasswordAlphaNumeric( $aPasswordLen=10)
{

$initStr = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

mt_srand((double)microtime()*1000000);
$rndStr = "";
for( ; strlen($initStr)>0; )
{
$len = strlen( $initStr);
$iPos = mt_rand( 0, $len-1);
$rndStr .= substr( $initStr, $iPos, 1);

if ($iPos == 0) {
$initStr = substr( $initStr, 1);
}

else if ( $iPos == ($len - 1)) {
$initStr = substr( $initStr, 0, $iPos);
}

else {
$initStr = substr( $initStr, 0, $iPos) . substr( $initStr, $iPos+1);
}
}

$len = strlen( $rndStr);
$psw = "";
for ( $i=0; $i<$aPasswordLen; $i++) {
$iPos = mt_rand( 0, $len-1);
$psw .= substr( $rndStr, $iPos, 1);
}
return $psw;
}


static function generateRndWord( $aLen=10)
{

$prefix = '';
$chars = range('a', 'z');
$charsUC = range('A', 'Z');

$symbols = array_merge($chars, $charsUC);
shuffle($symbols);
for($i = 0; $i < $aLen; $i++) {
$prefix .= $symbols[$i];
}
return $prefix;
}


static function generateRndPrefix( $aPrefixLen=10)
{

$prefix = '';
$chars = range('a', 'z');
$numbers = range(0, 9);

shuffle($chars);
$prefix .= $chars[0];

$symbols = array_merge($numbers, $chars);
shuffle($symbols);
for($i = 1; $i < $aPrefixLen; $i++) {
$prefix .= $symbols[$i];
}
return $prefix;
}


static function generateAutoInc()
{
static $instance;

if ( isset( $instance)) {
return $instance;
}

if (defined( 'MULTISITES_AUTOINC_DIR')) { $autoinc_dir = MULTISITES_AUTOINC_DIR; }
else { $autoinc_dir = dirname( __FILE__); }

$filename = $autoinc_dir .DS. 'autoinc.dat';
$fp = fopen( $filename, 'a');
if ( $fp != false) {
fseek( $fp, 0, SEEK_END);
fputs( $fp, '.');
$instance = ftell( $fp);
fclose( $fp);
return $instance;
}

return -1;
}


static function evalStr( $str, $site_id, $site_dir, $deploy_dir, $dbInfo)
{

$pos = strpos( $site_id, '@');
if ( $pos === false) {
$site_id_left = $site_id_left_alnum = '';
$site_id_right_search = $site_id_right_replace = $site_id_right = '';
}
else {
list( $site_id_left, $site_id_right) = explode( '@', $site_id);
$site_id_left_alnum = preg_replace('/[^A-Za-z0-9\-]/i', '', $site_id_left);

$site_id_right_search = array();
$site_id_right_replace = array_reverse( explode( '.', $site_id_right));
if ( !empty($site_id_right_replace) && is_array( $site_id_right_replace)) {
for ( $i=0; $i<count($site_id_right_replace); $i++) {
$site_id_right_search[$i] = '{site_id_right-'.($i+1).'}';
}
}
}
$user = JFactory::getUser();
$user_id = $user->id;
$user_login = $user->username;
$user_name = $user->name;
$user_email = $user->email;
list( $user_email_left, $user_email_right) = explode( '@', $user_email);
$user_email_left_alnum = preg_replace('/[^A-Za-z0-9\-]/i', '', $user_email_left);

$user_email_right_search = array();
$user_email_right_replace = array_reverse( explode( '.', $user_email_right));
if ( !empty($user_email_right_replace) && is_array( $user_email_right_replace)) {
for ( $i=0; $i<count($user_email_right_replace); $i++) {
$user_email_right_search[$i] = '{user_email_right-'.($i+1).'}';
}
}
if ( empty( $deploy_dir)) {
$deploy_dir = $site_dir;
}
$site_id_letters = $site_id;
if ( class_exists( 'MultisitesLetterTree')) {

$lettertree_dir = MultisitesLetterTree::getLetterTreeDir( $site_id);
if( !empty( $lettertree_dir)) {
$site_id_letters = $lettertree_dir;
}
}

$site_id_search = array();
$site_id_replace = array_reverse( explode( '.', $site_id));
if ( !empty($site_id_replace) && is_array( $site_id_replace)) {
for ( $i=0; $i<count($site_id_replace); $i++) {
$site_id_search[$i] = '{site_id-'.($i+1).'}';
}
}

$site_id_searchPlus = array();
$site_id_replacePlus = array();
if ( !empty($site_id_replace) && is_array( $site_id_replace)) {
for ( $i=0; $i<count($site_id_replace); $i++) {
$site_id_searchPlus[$i] = '{site_id-'.($i+1).'+}';
$site_id_replacePlus[$i] = implode( '.', array_reverse( array_slice( $site_id_replace, $i)));
}
}
$site_prefix = (isset( $dbInfo['site_prefix']))
? $dbInfo['site_prefix']
: '';
if ( empty( $dbInfo['site_alias'])) {
$site_alias = '';
$site_alias_left = $site_alias_left_alnum = '';
$site_alias_right = '';
$site_alias_right_search = '';
$site_alias_right_replace = '';
}
else {
$site_alias = $dbInfo['site_alias'];
list( $site_alias_left, $site_alias_right) = explode( '@', $site_alias);
$site_alias_left_alnum = preg_replace('/[^A-Za-z0-9\-]/i', '', $site_alias_left);

$site_alias_right_search = array();
$site_alias_right_replace = array_reverse( explode( '.', $site_alias_right));
if ( !empty($site_alias_right_replace) && is_array( $site_alias_right_replace)) {
for ( $i=0; $i<count($site_alias_right_replace); $i++) {
$site_alias_right_search[$i] = '{site_alias_right-'.($i+1).'}';
}
}
}
$site_domain = (isset( $dbInfo['site_domain']))
? $dbInfo['site_domain']
: '';
if ( empty( $dbInfo['newAdminEmail'])) {
$new_admin_email = '';
$new_admin_email_left = $new_admin_email_left_alnum = '';
$new_admin_email_right = '';
$new_admin_email_right_search = '';
$new_admin_email_right_replace = '';
}
else {
$new_admin_email = $dbInfo['newAdminEmail'];
list( $new_admin_email_left, $new_admin_email_right) = explode( '@', $new_admin_email);
$new_admin_email_left_alnum = preg_replace('/[^A-Za-z0-9\-]/i', '', $new_admin_email_left);

$new_admin_email_right_search = array();
$new_admin_email_right_replace = array_reverse( explode( '.', $new_admin_email_right));
if ( !empty($new_admin_email_right_replace) && is_array( $new_admin_email_right_replace)) {
for ( $i=0; $i<count($new_admin_email_right_replace); $i++) {
$new_admin_email_right_search[$i] = '{new_admin_email_right-'.($i+1).'}';
}
}
}
$home_dir = defined( 'MULTISITES_HOME_DIR') ? MULTISITES_HOME_DIR : '';
$public_dir = defined( 'MULTISITES_PUBLIC_DIR') ? MULTISITES_PUBLIC_DIR : '';
$jpath_master = dirname( dirname( dirname( dirname( dirname(__FILE__)))));
if ( defined( 'JPATH_ROOT')) {
$jpath_root = JPATH_ROOT;
}
else {
$jpath_root = $jpath_master;
}

@include_once( dirname( dirname( __FILE__)) .DIRECTORY_SEPARATOR. 'multisites_path.cfg.php');
$jpath_multisites = defined( 'JPATH_MULTISITES')
? JPATH_MULTISITES
: $jpath_root .DIRECTORY_SEPARATOR.'multisites';
$jroot_len = strlen( $jpath_root);
if ( strncmp( $site_dir, $jpath_root, $jroot_len) == 0) {
$rel_site_dir = substr( $site_dir, $jroot_len+1);
}
else {
$rel_site_dir = $site_dir;
}

$uri = JURI::getInstance(JURI::base());
$url_path = rtrim( $uri->toString( array('path')) , '/\\');

if ( JPATH_BASE == JPATH_ADMINISTRATOR
&& substr($url_path, -14) == '/administrator'
)
{

$url_path = substr( $url_path, 0, strlen( $url_path) - 14);
}
$site_url = $uri->toString( array( 'host', 'port') )
. rtrim($url_path, '/\\');

$rnd_psw_6 = MultisitesDatabase::generatePassword( 6);
$rnd_psw_7 = MultisitesDatabase::generatePassword( 7);
$rnd_psw_8 = MultisitesDatabase::generatePassword( 8);
$rnd_psw_9 = MultisitesDatabase::generatePassword( 9);
$rnd_psw_10 = MultisitesDatabase::generatePassword( 10);
$rnd_psw = $rnd_psw_8;

$rnd_alnum_6 = MultisitesDatabase::generatePasswordAlphaNumeric( 6);
$rnd_alnum_7 = MultisitesDatabase::generatePasswordAlphaNumeric( 7);
$rnd_alnum_8 = MultisitesDatabase::generatePasswordAlphaNumeric( 8);
$rnd_alnum_9 = MultisitesDatabase::generatePasswordAlphaNumeric( 9);
$rnd_alnum_10 = MultisitesDatabase::generatePasswordAlphaNumeric( 10);
$rnd_alnum = $rnd_alnum_8;

$rnd_word_6 = MultisitesDatabase::generateRndWord( 6);
$rnd_word_7 = MultisitesDatabase::generateRndWord( 7);
$rnd_word_8 = MultisitesDatabase::generateRndWord( 8);
$rnd_word_9 = MultisitesDatabase::generateRndWord( 9);
$rnd_word_10 = MultisitesDatabase::generateRndWord( 10);
$rnd_word = $rnd_word_8;

$rnd_prefix_6 = MultisitesDatabase::generateRndPrefix( 6);
$rnd_prefix_7 = MultisitesDatabase::generateRndPrefix( 7);
$rnd_prefix_8 = MultisitesDatabase::generateRndPrefix( 8);
$rnd_prefix_9 = MultisitesDatabase::generateRndPrefix( 9);
$rnd_prefix_10 = MultisitesDatabase::generateRndPrefix( 10);
$rnd_prefix = $rnd_prefix_8;


if ( preg_match_all('#{autoinc(\+([0-9]+))?}#', $str, $matches)) {
$autoinc_search = array();
$autoinc_replace = array();
$autoinc = MultisitesDatabase::generateAutoInc();

foreach( $matches[0] as $key=>$value) {
$offset = $matches[2][$key];
$autoinc_search[] = $value;
$autoinc_replace[] = $offset + $autoinc;
}
}
else {
$autoinc_search = '{autoinc}';
$autoinc_replace = '';
}
$res = $str;
$res = str_replace( array( '{home}', '{home_dir}', '{public}', '{public_dir}'),
array( $home_dir, $home_dir, $public_dir, $public_dir),
$res);
$res = str_replace( '{deploy_dir}', $deploy_dir, $res);
$res = str_replace( '{multisites}', $jpath_multisites, $res);
$res = str_replace( '{JPATH_MULTISITES}', $jpath_multisites, $res);
$res = str_replace( '{rel_site_dir}', $rel_site_dir, $res);
$res = str_replace( '{master}', $jpath_master, $res);
$res = str_replace( '{root}', $jpath_root, $res);
$res = str_replace( '{JPATH_ROOT}', $jpath_root, $res);
$res = str_replace( '{site_alias}', $site_alias, $res);
$res = str_replace( array( '{site_alias_left}', '{site_alias_left_alnum}', '{site_alias_right}'),
array( $site_alias_left, $site_alias_left_alnum, $site_alias_right),
$res);
if ( !empty( $site_alias_right_search)) {
$res = str_replace( $site_alias_right_search, $site_alias_right_replace, $res);
}
$res = str_replace( '{site_dir}', $site_dir, $res);
$res = str_replace( '{site_domain}', $site_domain, $res);
$res = str_replace( '{site_id}', $site_id, $res);
$res = str_replace( '{Site_id}', ucfirst( $site_id), $res);
$res = str_replace( '{SITE_ID}', strtoupper( $site_id),$res);
$res = str_replace( $site_id_searchPlus, $site_id_replacePlus,$res);
$res = str_replace( $site_id_search, $site_id_replace, $res);
$res = str_replace( '{site_id_left}', $site_id_left, $res);
$res = str_replace( '{site_id_left_alnum}',$site_id_left_alnum,$res);
$res = str_replace( '{site_id_right}', $site_id_right, $res);
if ( !empty( $site_id_right_search)) {
$res = str_replace( $site_id_right_search, $site_id_right_replace, $res);
}
$res = str_replace( '{site_id_letters}', $site_id_letters, $res);
$res = str_replace( '{site_prefix}', $site_prefix, $res);
$res = str_replace( '{site_url}', $site_url, $res);
$res = str_replace( '{user_id}', $user_id, $res);
$res = str_replace( '{user_login}', $user_login, $res);
$res = str_replace( '{user_name}', $user_name, $res);
$res = str_replace( '{user_email}', $user_email, $res);
$res = str_replace( array( '{user_email_left}', '{user_email_left_alnum}', '{user_email_right}'),
array( $user_email_left, $user_email_left_alnum, $user_email_right),
$res);
$res = str_replace( $user_email_right_search, $user_email_right_replace, $res);
$res = str_replace( '{new_admin_email}', $new_admin_email, $res);
$res = str_replace( array( '{new_admin_email_left}', '{new_admin_email_left_alnum}', '{new_admin_email_right}'),
array( $new_admin_email_left, $new_admin_email_left_alnum, $new_admin_email_right),
$res);
if ( !empty( $new_admin_email_right_search)) {
$res = str_replace( $new_admin_email_right_search, $new_admin_email_right_replace, $res);
}

$res = str_replace( '{rnd_psw_6}', $rnd_psw_6, $res);
$res = str_replace( '{rnd_psw_7}', $rnd_psw_7, $res);
$res = str_replace( '{rnd_psw_8}', $rnd_psw_8, $res);
$res = str_replace( '{rnd_psw_9}', $rnd_psw_9, $res);
$res = str_replace( '{rnd_psw_10}', $rnd_psw_10, $res);
$res = str_replace( '{rnd_psw}', $rnd_psw, $res);
$res = str_replace( array( '{rnd_alnum_6}', '{rnd_alnum_7}', '{rnd_alnum_8}', '{rnd_alnum_9}', '{rnd_alnum_10}', '{rnd_alnum}'),
array( $rnd_alnum_6, $rnd_alnum_7, $rnd_alnum_8, $rnd_alnum_9, $rnd_alnum_10, $rnd_alnum),
$res);
$res = str_replace( array( '{rnd_word_6}', '{rnd_word_7}', '{rnd_word_8}', '{rnd_word_9}', '{rnd_word_10}', '{rnd_word}'),
array( $rnd_word_6, $rnd_word_7, $rnd_word_8, $rnd_word_9, $rnd_word_10, $rnd_word),
$res);
$res = str_replace( array( '{rnd_prefix_6}', '{rnd_prefix_7}', '{rnd_prefix_8}', '{rnd_prefix_9}', '{rnd_prefix_10}', '{rnd_prefix}'),
array( $rnd_prefix_6, $rnd_prefix_7, $rnd_prefix_8, $rnd_prefix_9, $rnd_prefix_10, $rnd_prefix),
$res);
$res = str_replace( $autoinc_search, $autoinc_replace, $res);
$res = str_replace( '{reset}', '', $res);
JPluginHelper::importPlugin('multisites');
$results = JFactory::getApplication()->triggerEvent('onKeywordResolution', array ( $str, & $res, $site_id, $site_dir, $deploy_dir, $dbInfo, $jpath_multisites, $site_url));
return $res;
}


static function deleteDBTables( &$db)
{
$dbprefix = str_replace('_' , '\_', $db->getPrefix());



$db->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
$tables = $db->loadResultArray();

if ( !empty( $tables)) {
foreach($tables as $table)
{
$query = "DROP TABLE IF EXISTS $table;";
$db->setQuery($query);
$db->query();
}
}

$db->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
$tables = $db->loadResultArray();

if ( !empty( $tables)) {
foreach($tables as $table)
{
$query = "DROP VIEW IF EXISTS $table;";
$db->setQuery($query);
$db->query();
}
}

return true;
}


static function copyDbTablesIntoPrefix( &$db, $toPrefix, $tableNames, $structureOnly = true, $dropIfExists = false, $copyDataOnlyIfNotExists = true)
{
$errors = array();
$fromPrefix = $db->getPrefix();

foreach( $tableNames as $tableName)
{

$like = str_replace('_' , '\_', $toPrefix.$tableName);
$query = "SHOW TABLES LIKE '$like'";
$db->setQuery($query);
if ($db->loadResult() == null) {
$alreadyExists = false;
}
else {
$alreadyExists = true;
}

if ($alreadyExists && $dropIfExists) {
$query = "DROP TABLE IF EXISTS $tableName;";
$db->setQuery($query);
$db->query();
}

$query = "CREATE TABLE IF NOT EXISTS "
. "$toPrefix$tableName LIKE $fromPrefix$tableName";
$db->setQuery( $query );
$result = $db->query();
if ( !$result) {
$msg = "Error query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
continue;
}

$doReplace = true;
if ($structureOnly) {

$doReplace = false;
}

else if ($alreadyExists && $copyDataOnlyIfNotExists) {

$doReplace = false;
}

if ($doReplace) {

$query = "REPLACE into $toPrefix$tableName SELECT * FROM $fromPrefix$tableName;";
$db->setQuery($query);
if (!$db->query())
{
$msg = "Error query [$query]. DB Error: " . $db->getErrorMsg() . " Result = " . var_export($result, TRUE);
$errors[] = $msg;
}
}
}
return $errors;
}


static function backquote($a_name, $do_it = true)
{
if (! $do_it) {
return $a_name;
}
if (is_array($a_name)) {
$result = array();
foreach ($a_name as $key => $val) {
$result[$key] = MultisitesDatabase::backquote($val);
}
return $result;
}

if (strlen($a_name) && $a_name !== '*') {
return '`' . str_replace('`', '``', $a_name) . '`';
} else {
return $a_name;
}
}

static function nameQuote( $db, $table)
{

if ( method_exists( $db, 'nameQuote')) { return $db->nameQuote( $table); }

return $db->quoteName( $table);
}


static function _isViewSupported( &$db)
{
$result = false;

$query = "SELECT Version() AS version";
$db->setQuery( $query );
$versStr = $db->loadResult();

if ( !empty( $versStr)) {

$vers = explode( '-', $versStr);
if ( !empty( $vers)) {

$version = $vers[0];
$vers = explode( '.', $version);
if ( !empty( $vers)) {

if ( intval( $vers[0]) >= 5) {
$result = true;
}

}
}
}
return $result;
}


static function _isView( &$db, $table)
{

if ( !MultisitesDatabase::_isViewSupported( $db)) {
return false;
}




if ( empty( $db->_dbname)) {
$db->setQuery( 'SELECT DATABASE()');
$db->_dbname = $db->loadResult();
}
$dbName = MultisitesDatabase::backquote($db->_dbname);

$table = $db->replacePrefix( (string)$table);
$like = str_replace('_' , '\_', $table);
$query = "SHOW TABLE STATUS FROM $dbName LIKE '$like'";
$db->setQuery( $query );
$obj = $db->loadObject();
if ( !empty( $obj) && !empty( $obj->Comment) && strtoupper( substr($obj->Comment, 0, 4)) == 'VIEW') {
return true;
}
return false;
}


static function getViewFrom( &$db, $table, $allowCreateView=false, $keepFromOnly=true)
{
$query = "SHOW CREATE VIEW $table";
$db->setQuery( $query );
$row = $db->loadAssoc();
if ( !empty( $row) && !empty( $row['Create View'])) {
$createView = $row['Create View'];
$pos = strpos( strtoupper( $createView), 'SELECT');
if ($pos === false) {}
else {
$str = substr( $createView, $pos);
$arr = explode( ' ', $str);
$arr_lc = explode( ' ', strtolower( $str));

$fromPos = array_search( 'from', $arr_lc);
$arrFrom = array_slice( $arr, $fromPos+1);
$arrFrom_lc = array_values( array_slice( $arr_lc, $fromPos+1));

if ( count( $arrFrom_lc) > 1) {


$cntValues = array_count_values( $arrFrom);
if ( empty( $cntValues['from']) && !empty( $cntValues['where']) && $cntValues['where'] ==1 && $keepFromOnly) {

$wherePos = array_search( 'where', $arrFrom_lc);
$arrFrom = array_slice( $arrFrom, 0, $wherePos);
}

else {
if( $allowCreateView) {

return $createView;
}

else if ($keepFromOnly) {
$wherePos = array_search( 'where', $arrFrom_lc);
if ( !empty( $wherePos)) {
$arrFrom = array_slice( $arrFrom, 0, $wherePos);
}
}
}
}
$from = implode( ' ', $arrFrom);

if ( empty( $from)) {

$from = $arr[count( $arr) -1];
}
return $from;
}
}
return null;
}


static function _getViewQuery( &$db, $table, $allowCreateView=false)
{
$from = MultisitesDatabase::getViewFrom( $db, $table, $allowCreateView);
if ( !empty($from)) {

if ( strpos( $from, 'CREATE ALGORITHM=UNDEFINED') === false) {
$select = "SELECT * FROM $from";
return $select;
}
else {
return $from;
}
}
return null;
}


static function _getCreateTable( &$db, $tableName)
{
$query = "SHOW CREATE TABLE `#__$tableName`";
$db->setQuery( $query);
$db->query();
$result = $db->loadAssocList();
$sql = $result[0]['Create Table'];

$sql = str_replace( $db->getPrefix(), '#__', $sql );

$sql = str_replace( "\n", " ", $sql ) . ";\n";
return $sql;
}


static function _replaceObject( &$db, $table, &$object, $keyName = NULL )
{
$fmtsql = 'REPLACE INTO '.MultisitesDatabase::nameQuote( $db, $table).' ( %s ) VALUES ( %s ) ';
$fields = array();
foreach (get_object_vars( $object ) as $k => $v) {
if (is_array($v) or is_object($v) or $v === NULL) {
continue;
}
if ($k[0] == '_') { 
continue;
}
$fields[] = MultisitesDatabase::nameQuote( $db, $k );
if ( version_compare( JVERSION, '3.0') >= 0) {
$values[] = $db->Quote( $v );
}
else {
$values[] = $db->isQuoted( $k ) ? $db->Quote( $v ) : (int) $v;
}
}
$db->setQuery( sprintf( $fmtsql, implode( ",", $fields ) , implode( ",", $values ) ) );
if (!$db->query()) {
return false;
}
$id = $db->insertid();
if ($keyName && $id) {
$object->$keyName = $id;
}
return true;
}


static function _copyExternalTable( &$fromdb, &$todb, $tableName)
{
$errors = array();

$toPrefix = $todb->getPrefix();
$like = str_replace('_' , '\_', $toPrefix.$tableName);
$query = "SHOW TABLES LIKE '$like'";
$todb->setQuery($query);
$result = $todb->loadResult();
if ( empty( $result)) {

$query = MultisitesDatabase::_getCreateTable( $fromdb, $tableName);
$todb->setQuery( $query );
$result = $todb->query();
if ( !$result) {
$msg = "Create external Table Error query [$query]. DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
return $errors;
}
}

$query = "select * from `#__$tableName`";
$fromdb->setQuery( $query);
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}

foreach( $rows as $row) {
if (!MultisitesDatabase::_replaceObject( $todb, "#__$tableName", $row)) {
$msg = "Error while copying data of the table '#__$tableName'. Stopped on record with data : " . var_export( $row, true);
$errors[] = $msg;
break;
}
}
return $errors;
}


static function copyDbTablesOrViews( &$fromdb, &$todb, $toConfig, $tableNames, $structureOnly = true, $dropIfExists = false, $copyDataOnlyIfNotExists = true)
{
$errors = array();
if ( !is_object( $fromdb)) {
$msg = '"From DB" connection does not exists';
$errors[] = $msg;
return $errors;
}
$fromDBName = MultisitesDatabase::backquote($fromdb->_dbname);
$fromPrefix = $fromdb->getPrefix();
$toPrefix = $todb->getPrefix();
$db = & $todb;

foreach( $tableNames as $tableName)
{


if ( $tableName[0] == '=') {
$fromTableName =
$toTableName = substr( $tableName, 1);

if ( $todb->_dbname == $fromdb->_dbname) {

continue;
}
}
else {
$fromTableName = $fromPrefix.$tableName;
$toTableName = $toPrefix.$tableName;
}
$like = str_replace('_' , '\_', $toTableName);
$query = "SHOW TABLES LIKE '$like'";
$db->setQuery($query);
if ($db->loadResult() == null) {
$alreadyExists = false;
}
else {
$alreadyExists = true;
}

if ($alreadyExists && $dropIfExists) {
$query = "DROP TABLE IF EXISTS $toTableName;";
$db->setQuery($query);
$db->query();
$query = "DROP VIEW IF EXISTS $toTableName;";
$db->setQuery($query);
$db->query();
}

if ( MultisitesDatabase::_isView( $fromdb, $fromTableName)) {

$select = MultisitesDatabase::_getViewQuery( $fromdb, $fromTableName, true);
if ( !empty( $select)) {

if ( strpos( $select, 'CREATE ALGORITHM=UNDEFINED') === false) {
$query = "CREATE OR REPLACE VIEW $toTableName"
. " AS $select"
;
}

else {

if ( MultisitesDatabase::_isView( $todb, $toPrefix.$tableName)) {
continue;
}


$query = str_replace( $fromPrefix, $toPrefix, $select);
}
$todb->setQuery( $query );
$result = $todb->query();
if ( !$result) {
$err_num = $todb->getErrorNum();

if ( $err_num == 1142
|| $err_num == 1449
)
{
$msg1 = "Table/View(1) Error [$err_num] query [$query]. DB Error: " . $todb->getErrorMsg();

$table_name = MultisitesDatabase::backquote( $fromdb->_dbname)
. '.'
. MultisitesDatabase::backquote( $fromTableName)
;
$err = MultisitesDatabase::_createUser( $fromdb, $toConfig, $table_name);

$todb->setQuery( $query );
$result = $todb->query();
if ( !$result) {
$errors[] = $msg1; 
if ( !empty( $err)) {
$errors = array_merge($errors, $err); 
}
$msg = "Table/View Error retrying query [$query]. DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}
}
else {
$msg = "Table/View Error [$err_num] query [$query]. DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}
}
}
else {
$msg = "Unable to replicate the view from [$fromTableName]";
$errors[] = $msg;
continue;
}
}

else {


$doReplace = true;
if ($structureOnly) {

$doReplace = false;
}

else if ($alreadyExists && $copyDataOnlyIfNotExists) {

$doReplace = false;
}

$query = "CREATE TABLE IF NOT EXISTS "
. "$toTableName LIKE $fromDBName." . MultisitesDatabase::backquote( $fromTableName);
$db->setQuery( $query );
$result = $db->query();
if ( !$result) {
$err_num = $todb->getErrorNum();

if ( $err_num == 1142
|| $err_num == 1449
)
{


$sub_errors = MultisitesDatabase::_copyExternalTable( $fromdb, $todb, $tableName, $doReplace);
$errors = array_merge($errors, $sub_errors);
}
else {
$msg = "Create table error [$err_num] on query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}
}

else {

if ($doReplace) {

$query = "REPLACE into $toTableName SELECT * FROM $fromDBName." . MultisitesDatabase::backquote( $fromTableName) . ';';
$db->setQuery($query);
if (!$db->query())
{
$msg = "Error query [$query]. DB Error: " . $db->getErrorMsg() . " Result = " . var_export($result, TRUE);
$errors[] = $msg;
}
}
}
} 
}
return $errors;
}


static function copyDbTablePatterns( &$fromdb, &$todb, $toConfig, $tablePatterns, $structureOnly = true, $dropIfExists = false, $copyDataOnlyIfNotExists = true, $skip=NULL)
{
$errors = array();
$srcPrefix = $fromdb->getPrefix();
$srcPrefix_len = strlen($srcPrefix);
$dbprefix = str_replace('_' , '\_', $srcPrefix);

$tables = array();
foreach( $tablePatterns as $tablePattern) {
if ( $tablePattern == '[none]') {
continue;
}

$str = $fromdb->replacePrefix( $tablePattern);
$dbprefix = str_replace('_' , '\_', $str);

$fromdb->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
$rows = $fromdb->loadResultArray();
if ( empty( $rows)) {
$errors[] = JText::_( 'SITE_DEPLOY_NO_TABLES');
}
else {

if ( strpos( $tablePattern, '#__') === false) {

$fakePrefix = str_repeat( '_', $srcPrefix_len)
. '='
;
foreach( $rows as &$row) {
$row = $fakePrefix.$row;
}
$tables = array_merge( $tables, $rows);
}
else {
$tables = array_merge( $tables, $rows);
}
}
}

$tocopy = array();
foreach($tables as $table)
{

$tablename = substr($table, $srcPrefix_len);

if (!empty($skip) && in_array($tablename, $skip))
continue;

$tocopy[]=$tablename;
}
if ( empty( $tocopy)) {
$errors[] = JText::_( 'SITE_DEPLOY_TO_COPY_ERR');
}

$errors = MultisitesDatabase::copyDbTablesOrViews( $fromdb, $todb, $toConfig,
$tocopy, $structureOnly);
}


static function dropTablePatterns( &$db, $tablePatterns)
{
$errors = array();
foreach( $tablePatterns as $tablePattern) {
if ( $tablePattern == '[none]') {
continue;
}

$str = $db->replacePrefix( $tablePattern);
if ( empty( $str)) {
$msg = "Unable to get resolved the pattern [$tablePattern]. Joomla return an empty string. Check that the patch on the DB is installed.";
$errors[] = $msg;
continue;
}
$dbprefix = str_replace('_' , '\_', $str);

$db->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
$tables = $db->loadResultArray();

if ( !empty( $tables)) {
foreach($tables as $table)
{
$query = "DROP TABLE IF EXISTS $table;";
$db->setQuery($query);
$db->query();
if ( !$db->query()) {
$msg = "Error droping table query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}
}
}

$db->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
$tables = $db->loadResultArray();

if ( !empty( $tables)) {
foreach($tables as $table)
{
$query = "DROP VIEW IF EXISTS $table;";
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error droping view query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}
}
}
} 
return $errors;
}

static function parseQueries( $element, $fromdb, $todb, $toConfig, $site_id, $enteredvalues=array(), $template=null)
{
if (!is_a($element, 'JSimpleXMLElement') || !count($element->children())) {

return 0;
}

$queries = $element->children();
if (count($queries) == 0) {

return 0;
}
$fromPrefix = $fromdb->getPrefix();
$toPrefix = $todb->getPrefix();
$adminUserID = (!empty( $template) && !empty( $template->adminUserID)) ? $template->adminUserID : '0';
$adminUserLogin = (!empty( $template) && !empty( $template->adminUserLogin)) ? $template->adminUserLogin : '';

$result = 0;
foreach ($queries as $xmlquery)
{
$query = $xmlquery->data();
$query = ' ' . str_replace( array( '{toPrefix}', '{fromPrefix}', '#__', '{site_id}', '{adminUserID}', '{adminUserLogin}'),
array( $toPrefix, $fromPrefix, $toPrefix, $site_id, $adminUserID, $adminUserLogin),
$query
);
$todb->setQuery( $query);
if (!$todb->query()) {}
else { $result++; }
}
return $result;
}


static function createViews( $fromdb, $todb, &$sharedTables, $toConfig, $site_id, $enteredvalues=array(), $template=null)
{
$views = array();
$errors = array();
$fromDBName = MultisitesDatabase::backquote($fromdb->_dbname);
$fromPrefix = $fromdb->getPrefix();
$fromPrefix_len = strlen($fromPrefix);
$toPrefix = $todb->getPrefix();
$toPrefix_len = strlen($toPrefix);


foreach( $sharedTables['table'] as $tableName) {
if ( $tableName == '[none]') {
continue;
}

$tableName = str_replace('#__' , '', $tableName);

$like = str_replace('_' , '\_', $fromPrefix.$tableName);
$fromdb->setQuery( 'SHOW TABLES LIKE \''.$like.'\'' );
$tables = $fromdb->loadResultArray();
foreach($tables as $table)
{

$viewname = substr($table, $fromPrefix_len);

if ( !empty( $sharedTables['tableexcluded']['#__'.$viewname])) {}

else {
$views[$viewname] = $viewname;
}
}
}

$sharedTables['views'] = $views;
foreach( $sharedTables['views'] as $tableName) {

$like = str_replace('_' , '\_', $toPrefix.$tableName);
$query = "SHOW TABLES LIKE '$like'";
$todb->setQuery($query);
if ($todb->loadResult() == null) {
$alreadyExists = false;
}
else {
$alreadyExists = true; 
}

if ( !$alreadyExists) {
$userName = MultisitesDatabase::_getDBUserName( $toConfig);
if ( !empty( $sharedTables['tablewhere']['#__'.$tableName])) {
$query = "CREATE OR REPLACE"
. " VIEW $toPrefix$tableName"
. " AS SELECT * FROM $fromDBName." . MultisitesDatabase::backquote( $fromPrefix.$tableName);
if ( MultisitesDatabase::_isView( $fromdb, $fromPrefix.$tableName)) {
$from = MultisitesDatabase::getViewFrom( $fromdb, $fromPrefix.$tableName, true);
if ( !empty($from)) {

if ( strpos( $from, 'CREATE ALGORITHM=UNDEFINED') === false) {

$query = "CREATE OR REPLACE"
. " VIEW $toPrefix$tableName"
. " AS SELECT * FROM ".$from;
}

else {

$fromUC = strtoupper( $from);
$p0 = strpos( $fromUC, 'SELECT');
if ( $p0 === false) {}
else {
$p1 = strpos( $fromUC, ' FROM ', $p0);
if ( $p1 === false) {}
else {
$p2 = strpos( $fromUC, ' ', $p1+5);
if ( $p2 === false) {}
else {
$parts = explode( ' ', trim( substr( $from, $p2)));
$fromArray = explode( '.', str_replace( array('`', '\''), array('', ''), $parts[0]));
$physicalFromDBName = '';
if ( count( $fromArray) > 1) {
$physicalFromDBName = MultisitesDatabase::backquote( $fromArray[0])
. '.'
;
$physicalTableName = MultisitesDatabase::backquote( $fromArray[1]);
}
else {
$physicalTableName = MultisitesDatabase::backquote( $fromArray[0]);
}
$query = "CREATE OR REPLACE"
. " VIEW $toPrefix$tableName"
. " AS SELECT * FROM ".$physicalFromDBName.$physicalTableName;
}
}
}
}
}
}
$query .= ' ' . str_replace( array( '{toPrefix}', '{fromPrefix}', '#__', '{site_id}'),
array( $toPrefix, $fromPrefix, $toPrefix, $site_id),
$sharedTables['tablewhere']['#__'.$tableName]
);
}
else {
$query = "CREATE OR REPLACE"
. " VIEW $toPrefix$tableName"
. " AS SELECT * FROM $fromDBName." . MultisitesDatabase::backquote( $fromPrefix.$tableName);
}
$todb->setQuery( $query );
$result = $todb->query();
if ( !$result) {
$err_num = $todb->getErrorNum();

if ( $err_num == 1142
|| $err_num == 1449
)
{
$msg1 = "Error(1) [$err_num] query [$query]. DB Error: " . $todb->getErrorMsg();

$table_name = MultisitesDatabase::backquote( $fromdb->_dbname)
. '.'
. MultisitesDatabase::backquote( $fromPrefix.$tableName)
;
$err = MultisitesDatabase::_createUser( $fromdb, $toConfig, $table_name);

$todb->setQuery( $query );
$result = $todb->query();
if ( !$result) {
$errors[] = $msg1; 
$err_num = $todb->getErrorNum();
if ( !empty( $err)) {
$errors = array_merge($errors, $err); 
}
$msg = "Error [$err_num] retrying query [$query]. DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}
}
else {
$msg = "Error [$err_num] query [$query]. DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}
}

if ( !empty( $sharedTables['tablequeries']['#__'.$tableName])) {
foreach( $sharedTables['tablequeries']['#__'.$tableName] as $xmlqueries) {
$condition = $xmlqueries->attributes( 'if');
if ( !empty( $condition)) {
if ( isset( $test)) {
unset( $test);
}

eval( $condition);
}

if ( empty( $condition) || !empty( $test)) {
MultisitesDatabase::parseQueries( $xmlqueries,
$fromdb, $todb, $toConfig, $site_id, $enteredvalues, $template
);
}
}
}
}
} 
return $errors;
}


static function copyDB( $sourcedb, $targetdb, $toConfig, $skip=NULL, $structureOnly=false)
{
$errors = array();


$srcPrefix = $sourcedb->getPrefix();
$srcPrefix_len = strlen($srcPrefix);
$dbprefix = str_replace('_' , '\_', $srcPrefix);

$tocopy = array();
$sourcedb->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
$tables = $sourcedb->loadResultArray();
if ( empty( $tables)) {
$errors[] = JText::_( 'SITE_DEPLOY_NO_TABLES');
}
if (( count( $tables) > 0) && (!ini_get('safe_mode'))){


set_time_limit( count( $tables) * 5);
}
foreach($tables as $table)
{

$tablename = substr($table, $srcPrefix_len);

if (!empty($skip) && in_array($tablename, $skip))
continue;

$tocopy[]=$tablename;
}
if ( empty( $tocopy)) {
$errors[] = JText::_( 'SITE_DEPLOY_TO_COPY_ERR');
}


$errors = MultisitesDatabase::copyDbTablesOrViews( $sourcedb, $targetdb, $toConfig,
$tocopy, $structureOnly);


return $errors;
}


static function _ComponentSubMenu( $fromdb, $todb, $fromParentID, $toParentID)
{
$errors = array();
$db = $fromdb;
$query = 'SELECT * FROM #__components WHERE parent=' .(int) $fromParentID;
$db->setQuery($query);
$rows = $db->loadObjectList();
if ( !empty( $rows)) {
foreach( $rows as $row) {
$query = 'REPLACE INTO #__components'
. ' VALUES( 0, '.$todb->Quote($row->name).', '.$todb->Quote($row->link).', '.(int) $row->menuid.','
. ' '.(int) $toParentID.', '.$todb->Quote($row->admin_menu_link).', '.$todb->Quote($row->admin_menu_alt).','
. ' '.$todb->Quote($row->option).', '.(int) $row->ordering.', '.$todb->Quote($row->admin_menu_img).','
. ' '.(int) $row->iscore.', '.$todb->Quote($row->params).', '.(int) $row->enabled.' )'
;
$todb->setQuery($query);
if (!$todb->query()) {
$msg = "Install component sub-menu error query [$query]. DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
}
else {
$menuid = $todb->insertid();
$sub_errors = MultisitesDatabase::_ComponentSubMenu( $fromdb, $todb, $row->id, $menuid);
$errors = array_merge($errors, $sub_errors);
}
}
}
return $errors;
}


static function installNewComponents_j15( $fromdb, $todb, $component = null, $overwrite = false)
{
$errors = array();

if ( empty( $component)) {

$query = "SELECT t.option FROM #__components AS t"
. " WHERE t.parent = 0"
;
$todb->setQuery( $query );
$rows = $todb->loadResultArray();
if ( !empty( $rows)) {
$optionList = "'" . implode( "','", $rows) . "'";
$where = " WHERE f.parent = 0 AND f.option NOT IN ($optionList)";
}
else {
$where = " WHERE f.parent = 0";
}

$query = "SELECT * FROM #__components AS f"
. $where
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

else {

if ( $overwrite) { }

else {

$query = "SELECT t.option FROM #__components AS t"
. " WHERE t.option = '$component' AND t.parent = 0"
;
$todb->setQuery( $query );
$result = $todb->loadResult();

if ( !empty( $result)) {

return $errors;
}
}

$query = "SELECT * FROM #__components AS f"
. " WHERE f.option = '$component' AND f.parent = 0"
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

$db = & $todb;
foreach( $rows as $row) {
$exists = 0;

$query = 'REPLACE INTO #__components'
. ' VALUES( '.$exists .', '.$db->Quote($row->name).', '.$db->Quote($row->link).', '.(int) $row->menuid.','
. ' '.(int) $row->parent.', '.$db->Quote($row->admin_menu_link).', '.$db->Quote($row->admin_menu_alt).','
. ' '.$db->Quote($row->option).', '.(int) $row->ordering.', '.$db->Quote($row->admin_menu_img).','
. ' '.(int) $row->iscore.', '.$db->Quote($row->params).', '.(int) $row->enabled.' )'
;
$db->setQuery($query);
if (!$db->query()) {
$msg = "Install component 'root' menu error query [$query]. DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}

$menuid = $exists ? $exists : $db->insertid();
$sub_errors = MultisitesDatabase::_ComponentSubMenu( $fromdb, $todb, $row->id, $menuid);
$errors = array_merge($errors, $sub_errors);
}
return $errors;
}


static function _ComponentSubMenu_j16( $fromdb, $todb, $fromComponentID, $fromParentID, $toComponentID, $toParentID)
{
$errors = array();
$db = $fromdb;
$query = 'SELECT * FROM #__menu WHERE component_id=' .(int)$fromComponentID . ' AND parent_id=' .(int) $fromParentID;
$db->setQuery($query);
$rows = $db->loadObjectList();
if ( !empty( $rows)) {
foreach( $rows as $row) {
$query = 'REPLACE INTO #__menu'
. ' VALUES( 0, '.$todb->Quote($row->menutype)
.', '.$todb->Quote( $row->title)
.', '.$todb->Quote( $row->alias)
.', '.$todb->Quote( $row->note)
.', '.$todb->Quote( $row->path)
.', '.$todb->Quote( $row->link)
.', '.$todb->Quote( $row->type)
.', '.(int) $row->published
.', '.(int) $toParentID
.', '.(int) $row->level
.', '.(int) $toComponentID
;
if ( version_compare( JVERSION, '3.0') >= 0) {} 

else {
$query .= ', '.(int) $row->ordering;
}
$query .= ', '.(int) $row->checked_out
.', '.$todb->Quote( $row->checked_out_time)
.', '.(int) $row->browserNav
.', '.(int) $row->access
.', '.$todb->Quote( $row->img)
.', '.(int) $row->template_style_id
.', '.$todb->Quote( $row->params )
.', '.(int) $row->lft
.', '.(int) $row->rgt
.', '.(int) $row->home
.', '.$todb->Quote( $row->language)
.', '.(int) $row->client_id
.' )'
;
$todb->setQuery($query);
if (!$todb->query()) {
$msg = "Install extension menu and sub-menu error query [$query]. DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
}
else {
$menuid = $todb->insertid();
$sub_errors = MultisitesDatabase::_ComponentSubMenu_j16( $fromdb, $todb, $fromComponentID, $row->id, $toComponentID, $menuid);
$errors = array_merge($errors, $sub_errors);
}
}
}
return $errors;
}


public static function installNewComponents_j16( $fromdb, $todb, $component = null, $overwrite = false)
{
$errors = array();

if ( empty( $component)) {

$query = "SELECT DISTINCT t.element as 'option' FROM #__extensions AS t"
. ' WHERE type="component"'
;
$todb->setQuery( $query );
$rows = $todb->loadResultArray();
if ( !empty( $rows)) {
$optionList = "'" . implode( "','", $rows) . "'";
$where = " WHERE type=\"component\" AND f.element NOT IN ($optionList)";
}
else {
$where = ' WHERE type="component"';
}

$query = "SELECT * FROM #__extensions AS f"
. $where
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

else {

if ( $overwrite) { }

else {

$query = "SELECT t.element FROM #__extensions AS t"
. " WHERE t.type=\"component\" AND t.element = '$component'"
;
$todb->setQuery( $query );
$result = $todb->loadResult();

if ( !empty( $result)) {

return $errors;
}
}

$query = "SELECT * FROM #__extensions AS f"
. " WHERE f.type=\"component\" AND f.element = '$component'"
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

$db = & $todb;
foreach( $rows as $row) {
$exists = 0;

$query = 'REPLACE INTO #__extensions'
. ' VALUES( '.$exists
.', '.$db->Quote($row->name)
.', '.$db->Quote($row->type)
.', '.$db->Quote($row->element)
.', '.$db->Quote($row->folder)
.', '.(int) $row->client_id
.', '.(int) $row->enabled
.', '.(int) $row->access
.', '.(int) $row->protected
.', '.$db->Quote($row->manifest_cache)
.', '.$db->Quote($row->params)
.', '.$db->Quote($row->custom_data)
.', '.$db->Quote($row->system_data)
.', '.(int) $row->checked_out
.', '.$db->Quote($row->checked_out_time)
.', '.(int) $row->ordering
.', '.(int) $row->state
.' )'
;
$db->setQuery($query);
if (!$db->query()) {
$msg = "Install extension 'root' menu error query [$query]. DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}

$menuid = $exists ? $exists : $db->insertid();
$sub_errors = MultisitesDatabase::_ComponentSubMenu_j16( $fromdb, $todb, $row->extension_id, 1, $menuid, 1);
$errors = array_merge($errors, $sub_errors);
}
return $errors;
}


static function installNewComponents( $fromdb, $todb, $component = null, $overwrite = false)
{
if ( version_compare( JVERSION, '1.6') >= 0) {
return MultisitesDatabase::installNewComponents_j16( $fromdb, $todb, $component, $overwrite);
}
return MultisitesDatabase::installNewComponents_j15( $fromdb, $todb, $component, $overwrite);
}


static function _ModulesMenu( $fromdb, $todb, $fromModuleID, $toModuleID)
{
$errors = array();

$query = "SELECT * FROM #__modules_menu"
. " WHERE moduleid = " . (int)$fromModuleID
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}

foreach( $rows as $row) {
if (!$todb->insertObject( '#__modules_menu', $row, 'id')) {
$msg = "Install modules menu error query [$query]. DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}
}
return $errors;
}


static function installNewModules_j15( $fromdb, $todb, $module = null, $overwrite = false)
{
$errors = array();

if ( empty( $module)) {

$query = "SELECT DISTINCT module FROM #__modules";
$todb->setQuery( $query );
$rows = $todb->loadResultArray();
if ( !empty( $rows)) {
$optionList = "'" . implode( "','", $rows) . "'";
$where = " WHERE f.module NOT IN ($optionList)";
}
else {
$where = '';
}

$query = "SELECT * FROM #__modules AS f"
. $where
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

else {

if ( $overwrite) { }

else {

$query = "SELECT module FROM #__modules AS t"
. " WHERE t.module = '$module'"
;
$todb->setQuery( $query );
$result = $todb->loadResult();

if ( !empty( $result)) {

return $errors;
}
}

$query = "SELECT * FROM #__modules AS f"
. " WHERE f.module = '$module'"
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

$db = & $todb;
foreach( $rows as $row) {
$row->id = null;
$row->checked_out = null;
$row->checked_out_time = null;
if (!MultisitesDatabase::_replaceObject( $todb, '#__modules', $row, 'id')) {
$msg = "Install modules DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}

$moduleid = $row->id;

$sub_errors = MultisitesDatabase::_ModulesMenu( $fromdb, $todb, $row->id, $moduleid);
$errors = array_merge($errors, $sub_errors);
}
return $errors;
}


public static function installNewModules_j16( $fromdb, $todb, $module = null, $overwrite = false)
{
$errors = array();

if ( empty( $module)) {

$query = 'SELECT DISTINCT element as module FROM #__extensions WHERE type="module"';
$todb->setQuery( $query );
$rows = $todb->loadResultArray();
if ( !empty( $rows)) {
$optionList = "'" . implode( "','", $rows) . "'";
$where = " WHERE type=\"module\" AND f.element NOT IN ($optionList)";
}
else {
$where = '';
}

$query = "SELECT * FROM #__extensions AS f"
. $where
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

else {

if ( $overwrite) { }

else {

$query = "SELECT element as module FROM #__extensions AS t"
. " WHERE type=\"module\" AND t.element = '$module'"
;
$todb->setQuery( $query );
$result = $todb->loadResult();

if ( !empty( $result)) {

return $errors;
}
}

$query = "SELECT * FROM #__extensions AS f"
. " WHERE type=\"module\" AND f.element = '$module'"
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

$db = & $todb;
foreach( $rows as $row) {
$row->extension_id = null;
$row->checked_out = null;
$row->checked_out_time = null;
if (!MultisitesDatabase::_replaceObject( $todb, '#__extensions', $row, 'extension_id')) {
$msg = "Install j1.6 modules DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}

$moduleid = $row->id;

$sub_errors = MultisitesDatabase::_ModulesMenu( $fromdb, $todb, $row->id, $moduleid);
$errors = array_merge($errors, $sub_errors);
}
return $errors;
}


static function installNewModules( $fromdb, $todb, $module = null, $overwrite = false)
{
if ( version_compare( JVERSION, '1.6') >= 0) {

$errors = MultisitesDatabase::installNewModules_j16( $fromdb, $todb, $module, $overwrite);
if ( !empty( $errors)) {
return $errors;
}

}
return MultisitesDatabase::installNewModules_j15( $fromdb, $todb, $module, $overwrite);
}


static function installNewPlugins_j15( $fromdb, $todb, $folder = null, $element = null, $overwrite = false)
{
$errors = array();

if ( empty( $folder) && empty( $element)) {

$query = "SELECT CONCAT_WS( '/', folder, element) as opt FROM #__plugins AS t";
$todb->setQuery( $query );
$rows = $todb->loadResultArray();
if ( !empty( $rows)) {
$optionList = "'" . implode( "','", $rows) . "'";
$where = " WHERE CONCAT_WS( '/', folder, element) NOT IN ($optionList)";
}
else {
$where = '';
}

$query = "SELECT * FROM #__plugins AS f"
. $where
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

else {

if ( $overwrite) { }

else {

$query = "SELECT folder, element FROM #__plugins AS t"
. " WHERE t.folder = '$folder' AND t.element = '$element'"
;
$todb->setQuery( $query );
$result = $todb->loadResult();

if ( !empty( $result)) {

return $errors;
}
}

$query = "SELECT * FROM #__plugins AS f"
. " WHERE f.folder = '$folder' AND f.element = '$element'"
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

$db = & $todb;
foreach( $rows as $row) {

$row->id = null;
$row->checked_out = null;
$row->checked_out_time = null;
if (!MultisitesDatabase::_replaceObject( $todb, '#__plugins', $row, 'id')) {
$msg = "Install plugins DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}
}
return $errors;
}


public static function installNewPlugins_j16( $fromdb, $todb, $folder = null, $element = null, $overwrite = false)
{
$errors = array();

if ( empty( $folder) && empty( $element)) {

$query = "SELECT CONCAT_WS( '/', folder, element) as opt FROM #__extensions AS t WHERE type=\"plugin\"";
$todb->setQuery( $query );
$rows = $todb->loadResultArray();
if ( !empty( $rows)) {
$optionList = "'" . implode( "','", $rows) . "'";
$where = " WHERE type=\"plugin\" AND CONCAT_WS( '/', folder, element) NOT IN ($optionList)";
}
else {
$where = ' WHERE type="plugin"';
}

$query = "SELECT * FROM #__extensions AS f"
. $where
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

else {

if ( $overwrite) { }

else {

$query = "SELECT folder, element FROM #__extensions AS t"
. " WHERE type=\"plugin\" AND t.folder = '$folder' AND t.element = '$element'"
;
$todb->setQuery( $query );
$result = $todb->loadResult();

if ( !empty( $result)) {

return $errors;
}
}

$query = "SELECT * FROM #__extensions AS f"
. " WHERE f.type=\"plugin\" AND f.folder = '$folder' AND f.element = '$element'"
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

$db = & $todb;
foreach( $rows as $row) {

$row->extension_id = null;
$row->checked_out = null;
$row->checked_out_time = null;
if (!MultisitesDatabase::_replaceObject( $todb, '#__extensions', $row, 'extension_id')) {
$msg = "Install_j1.6 plugins DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}
}
return $errors;
}


static function installNewPlugins( $fromdb, $todb, $folder = null, $element = null, $overwrite = false)
{
if ( version_compare( JVERSION, '1.6') >= 0) {
return MultisitesDatabase::installNewPlugins_j16( $fromdb, $todb, $folder, $element, $overwrite);
}
return MultisitesDatabase::installNewPlugins_j15( $fromdb, $todb, $folder, $element, $overwrite);
}


static function _TemplateStyle( $fromdb, $todb, $fromTemplate, $toTemplate, $client_id=0)
{
$errors = array();

$query = "SELECT * FROM #__template_styles"
. " WHERE template = " . $fromdb->Quote( $fromTemplate)
. ' AND client_id='. $client_id
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}

foreach( $rows as $row) {
$row->id = null;
if (!$todb->insertObject( '#__template_styles', $row, 'id')) {
$msg = "Install template style error query [$query]. DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}
}
return $errors;
}


public static function installNewTemplates_j16( $fromdb, $todb, $template = null, $overwrite = false)
{
$errors = array();

if ( empty( $template)) {

$query = 'SELECT DISTINCT element FROM #__extensions WHERE type="template"';
$todb->setQuery( $query );
$rows = $todb->loadResultArray();
if ( !empty( $rows)) {
$optionList = "'" . implode( "','", $rows) . "'";
$where = " WHERE type=\"template\" AND f.element NOT IN ($optionList)";
}
else {
$where = '';
}

$query = "SELECT * FROM #__extensions AS f"
. $where
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

else {

if ( $overwrite) { }

else {

$query = "SELECT element FROM #__extensions AS t"
. " WHERE type=\"template\" AND t.element = '$template'"
;
$todb->setQuery( $query );
$result = $todb->loadResult();

if ( !empty( $result)) {

return $errors;
}
}

$query = "SELECT * FROM #__extensions AS f"
. " WHERE type=\"template\" AND f.element = '$template'"
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

$db = & $todb;
foreach( $rows as $row) {
$row->id = null;
$row->checked_out = null;
$row->checked_out_time = null;
if (!MultisitesDatabase::_replaceObject( $todb, '#__extensions', $row, 'id')) {
$msg = "Install j1.6 templates DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}

$sub_errors = MultisitesDatabase::_TemplateStyle( $fromdb, $todb, $row->element, $row->element);
$errors = array_merge($errors, $sub_errors);
}
return $errors;
}


static function installNewTemplates( $fromdb, $todb, $template = null, $overwrite = false)
{
if ( version_compare( JVERSION, '1.6') >= 0) {
return MultisitesDatabase::installNewTemplates_j16( $fromdb, $todb, $template, $overwrite);
}
return array();
}


public static function installNewLanguages_j16( $fromdb, $todb, $language = null, $overwrite = false)
{
$errors = array();

if ( empty( $language)) {

$query = 'SELECT DISTINCT element FROM #__extensions WHERE type="language"';
$todb->setQuery( $query );
$rows = $todb->loadResultArray();
if ( !empty( $rows)) {
$optionList = "'" . implode( "','", $rows) . "'";
$where = " WHERE type=\"language\" AND f.element NOT IN ($optionList)";
}
else {
$where = '';
}

$query = "SELECT * FROM #__extensions AS f"
. $where
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

else {

if ( $overwrite) { }

else {

$query = "SELECT element FROM #__extensions AS t"
. " WHERE type=\"language\" AND t.element = '$language'"
;
$todb->setQuery( $query );
$result = $todb->loadResult();

if ( !empty( $result)) {

return $errors;
}
}

$query = "SELECT * FROM #__extensions AS f"
. " WHERE type=\"language\" AND f.element = '$language'"
;
$fromdb->setQuery( $query );
$rows = $fromdb->loadObjectList();
if ( empty( $rows)) {
return $errors;
}
}

$db = & $todb;
foreach( $rows as $row) {
$row->id = null;
$row->checked_out = null;
$row->checked_out_time = null;
if (!MultisitesDatabase::_replaceObject( $todb, '#__extensions', $row, 'id')) {
$msg = "Install j1.6 languages DB Error: " . $todb->getErrorMsg();
$errors[] = $msg;
continue;
}
}
return $errors;
}


static function installNewLanguages( $fromdb, $todb, $language = null, $overwrite = false)
{
if ( version_compare( JVERSION, '1.6') >= 0) {
return MultisitesDatabase::installNewLanguages_j16( $fromdb, $todb, $language, $overwrite);
}
return array();
}


function verifyExtensions_j16( $todb)
{
$errors = array();

$fromdb =& Jms2WinFactory::getMasterDBO();


$query = "SELECT t.element FROM #__extensions AS t GROUP BY t.type, t.element, t.client_id"
. ' HAVING count( t.element)>=2 AND type="component" AND t.client_id=1'
;
$todb->setQuery( $query );
$rows = $todb->loadObjectList();
if ( !empty( $rows)) {

foreach( $rows as $row) {

$query = 'SELECT t.extension_id FROM #__extensions AS t'
.' LEFT JOIN #__menu AS m ON m.component_id = t.extension_id'
.' WHERE m.id IS NULL AND t.type = "component" AND t.element='.$todb->Quote( $row->element)
;
$todb->setQuery( $query );
$ext2DelRows = $todb->loadObjectList();
if ( !empty( $ext2DelRows)) {
foreach( $ext2DelRows as $ext2DelRow) {
$todb->setQuery( 'DELETE FROM #__extensions WHERE extension_id='.$ext2DelRow->extension_id);
$todb->query();
}
}
}
}



$query = 'SELECT t.extension_id, t.element, t.manifest_cache FROM #__extensions AS t WHERE type="component" AND t.client_id=1';
$todb->setQuery( $query );
$rows = $todb->loadObjectList();
if ( !empty( $rows)) {

foreach( $rows as $row) {

$query = 'SELECT f.extension_id, f.manifest_cache FROM #__extensions AS f WHERE type="component" AND f.client_id=1 AND f.element = '.$fromdb->quote( $row->element);
$fromdb->setQuery( $query );
$fromRows = $fromdb->loadObjectList();
if ( !empty( $fromRows)) {
foreach( $fromRows as $fromRow) {
if ( $row->manifest_cache != $fromRow->manifest_cache) {
$row->manifest_cache = $fromRow->manifest_cache;
$todb->updateObject( '#__extensions', $row, 'extension_id');
break;
}
}
}
}
}

$query = 'SELECT m.id, t.element, m.title, m.path, m.alias, m.img FROM #__extensions AS t, #__menu AS m WHERE m.component_id=t.extension_id AND t.type="component" AND t.client_id=1 AND t.extension_id>=10000';
$todb->setQuery( $query );
$rows = $todb->loadObjectList();
if ( !empty( $rows)) {

foreach( $rows as $row) {

$query = 'SELECT m.alias, m.img FROM #__extensions AS f, #__menu AS m'
.' WHERE m.component_id=f.extension_id AND f.type="component" AND f.client_id=1'
.' AND f.element='.$fromdb->quote( $row->element)
.' AND m.title='.$fromdb->quote( $row->title)
.' AND m.path='.$fromdb->quote( $row->path)
;
$fromdb->setQuery( $query );
$fromRows = $fromdb->loadObjectList();
if ( !empty( $fromRows)) {
foreach( $fromRows as $fromRow) {
if ( $row->alias != $fromRow->alias
|| $row->img != $fromRow->img
)
{
$row->alias = $fromRow->alias;
$row->img = $fromRow->img;
unset( $row->element);
$todb->updateObject( '#__menu', $row, 'id');
}
break;
}
}
}
}
return $errors;
}


function verifyExtensions( $todb)
{
$errors = array();
if ( version_compare( JVERSION, '1.6') >= 0) {
return MultisitesDatabase::verifyExtensions_j16( $todb);
}
return $errors;
}


static function installNewExtension( $fromdb, $todb, $extension = null, $overwrite = false, $type=null)
{
$errors = array();
$component = null;
$module = null;
$folder = null;
$element = null;
$template = null;
$language = null;
if ( !empty( $extension)) {
if ( !empty( $type)) {
if ( $type == 'plugin') {
list( $folder, $element) = explode( '/', $extension);
}
else {
$$type = $extension; 
}
}
else if ( strncmp( $extension, 'com_', 4) == 0) {
$component = $extension;
}
else if ( strncmp( $extension, 'mod_', 4) == 0) {
$module = $extension;
}
else {
$parts = explode( '/', $extension);
if (count( $parts) == 2) {
$folder = $parts[0];
$element = $parts[1];
}
}
}

if ( empty( $extension) || !empty( $component)) {
$errors = MultisitesDatabase::installNewComponents( $fromdb, $todb, $component, $overwrite);
}

if ( empty( $extension) || !empty( $module)) {
$errorsModules = MultisitesDatabase::installNewModules( $fromdb, $todb, $module, $overwrite);
$errors = array_merge($errors, $errorsModules);
}

if ( empty( $extension) || !empty( $folder)) {
$errorsPlugins = MultisitesDatabase::installNewPlugins( $fromdb, $todb, $folder, $element, $overwrite);
$errors = array_merge($errors, $errorsPlugins);
}

if ( empty( $extension) || !empty( $template)) {
$errorsTemplates = MultisitesDatabase::installNewTemplates( $fromdb, $todb, $template, $overwrite);
$errors = array_merge($errors, $errorsTemplates);
}

if ( empty( $extension) || !empty( $language)) {
$errorsLanguage = MultisitesDatabase::installNewLanguages( $fromdb, $todb, $language, $overwrite);
$errors = array_merge($errors, $errorsLanguage);
}
MultisitesDatabase::verifyExtensions( $todb);
return $errors;
}


static function uninstallComponent_j15( $db, $component)
{
$errors = array();

$query = "SELECT id FROM #__components as c"
. ' WHERE c.parent = 0 AND c.option=' . $db->Quote( $component)
;
$db->setQuery( $query );
$id = $db->loadResult();
if ( !empty( $id)) {

$query = 'DELETE FROM #__components WHERE parent = '.(int)$id;
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error deleting component query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}

$query = 'DELETE FROM #__components WHERE id = '.(int)$id;
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error 2 deleting component query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}
}
return $errors;
}


public static function uninstallComponent_j16( $db, $component)
{
$errors = array();

$query = "SELECT c.extension_id, m.id as menu_id FROM #__extensions as c"
. ' LEFT JOIN #__menu as m ON component_id=extension_id'
. ' WHERE c.type="component" AND c.element=' . $db->Quote( $component)
;
$db->setQuery( $query );
$row = $db->loadObject();
if ( !empty( $row)) {
$extension_id = $row->extension_id;
$menu_id = $row->menu_id;

if ( !empty( $menu_id)) {
$query = 'DELETE FROM #__menu WHERE parent_id = '.(int)$menu_id;
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error_1.6 deleting component submenu query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}
}

if ( !empty( $extension_id)) {
$query = 'DELETE FROM #__menu WHERE component_id = '.(int)$extension_id;
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error_1.6 2 deleting root menu query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}
}

if ( !empty( $extension_id)) {
$query = 'DELETE FROM #__extensions WHERE extension_id = '.(int)$extension_id;
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error_1.6 2 deleting component query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}
}
}
return $errors;
}


static function uninstallComponent( $db, $component)
{
if ( version_compare( JVERSION, '1.6') >= 0) {
return MultisitesDatabase::uninstallComponent_j16( $db, $component);
}
return MultisitesDatabase::uninstallComponent_j15( $db, $component);
}


static function uninstallModule_j15( $db, $module)
{
$errors = array();

$query = 'SELECT id FROM #__modules'
. ' WHERE module=' .$db->Quote($module)
;
$db->setQuery( $query );
$modules = $db->loadResultArray();
if (count($modules)) {
JArrayHelper::toInteger($modules);
$modID = implode(',', $modules);
$query = 'DELETE' .
' FROM #__modules_menu' .
' WHERE moduleid IN ('.$modID.')';
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error deleting modules_menu query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}
}
$query = 'DELETE FROM `#__modules` WHERE module = '.$db->Quote($module);
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error deleting modules query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}
return $errors;
}


public static function uninstallModule_j16( $db, $module)
{
$errors = array();


$query = 'SELECT id FROM #__modules'
. ' WHERE module=' .$db->Quote($module)
;
$db->setQuery( $query );
$modules = $db->loadResultArray();
$modules = $db->loadResultArray();
if (count($modules)) {
JArrayHelper::toInteger($modules);
$modID = implode(',', $modules);
$query = 'DELETE' .
' FROM #__modules_menu' .
' WHERE moduleid IN ('.$modID.')';
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error deleting modules_menu query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}
}
$query = 'DELETE FROM `#__modules` WHERE module = '.$db->Quote($module);
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error_1.6 deleting modules query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}

$query = 'DELETE FROM `#__schemas` WHERE extension_id IN ( SELECT extension_id FROM #__extensions WHERE element=' .$db->Quote($module) . ')';
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error_1.6 deleting schema query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}

$query = 'DELETE FROM `#__extensions` WHERE type="module" AND element=' .$db->Quote($module);
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error_1.6 deleting extension module query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}
return $errors;
}


static function uninstallModule( $db, $module)
{
if ( version_compare( JVERSION, '1.6') >= 0) {
return MultisitesDatabase::uninstallModule_j16( $db, $module);
}
return MultisitesDatabase::uninstallModule_j15( $db, $module);
}


static function uninstallPlugin_j15( $db, $folder, $element)
{
$errors = array();
$query = 'DELETE FROM #__plugins'
. ' WHERE folder=' . $db->Quote($folder)
. '   AND element=' . $db->Quote($element)
;
$db->setQuery($query);
$db->query();
return $errors;
}


public static function uninstallPlugin_j16( $db, $folder, $element)
{
$errors = array();

$query = 'DELETE FROM `#__schemas` WHERE extension_id IN ( SELECT extension_id FROM #__extensions WHERE folder=' . $db->Quote($folder)
. ' AND element=' . $db->Quote($element) . ')';
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error_1.6 deleting schema query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}
$query = 'DELETE FROM #__extensions'
. ' WHERE folder=' . $db->Quote($folder)
. '   AND element=' . $db->Quote($element)
;
$db->setQuery($query);
$db->query();
return $errors;
}


static function uninstallPlugin( $db, $folder, $element)
{
if ( version_compare( JVERSION, '1.6') >= 0) {
return MultisitesDatabase::uninstallPlugin_j16( $db, $folder, $element);
}
return MultisitesDatabase::uninstallPlugin_j15( $db, $folder, $element);
}


public static function uninstallTemplates_j16( $db, $template, $clientId=0)
{
$errors = array();
$type = 'template';

$query = 'DELETE FROM `#__schemas` WHERE extension_id IN ( SELECT extension_id FROM #__extensions WHERE type=' . $db->Quote($type)
. ' AND element=' . $db->Quote($template)
. ')';
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error_1.6 deleting schema query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}

$query = 'UPDATE #__menu INNER JOIN #__template_styles' . ' ON #__template_styles.id = #__menu.template_style_id'
. ' SET #__menu.template_style_id = 0' . ' WHERE #__template_styles.template = ' . $db->Quote(strtolower($template))
. ' AND #__template_styles.client_id = ' . $db->Quote($clientId);
$db->setQuery($query);
$db->execute();

$query = 'DELETE FROM #__template_styles`'
. ' WHERE template=' . $db->Quote($template)
;
$db->setQuery($query);
$db->query();

$query = 'DELETE FROM #__extensions'
. ' WHERE type=' . $db->Quote($type)
. '   AND element=' . $db->Quote($template)
;
$db->setQuery($query);
$db->query();
return $errors;
}


public static function uninstallExtension_j16( $db, $type, $element, $folder=null)
{
$errors = array();
if ( !empty( $folder)) { $q_folder = ' AND folder='. $db->Quote($folder); }
else { $q_folder = ''; }

$query = 'DELETE FROM `#__schemas` WHERE extension_id IN ( SELECT extension_id FROM #__extensions WHERE type=' . $db->Quote($type)
. ' AND element=' . $db->Quote($element)
. $q_folder
. ')';
$db->setQuery($query);
if ( !$db->query()) {
$msg = "Error_1.6 deleting schema query [$query]. DB Error: " . $db->getErrorMsg();
$errors[] = $msg;
}
$query = 'DELETE FROM #__extensions'
. ' WHERE type=' . $db->Quote($type)
. '   AND element=' . $db->Quote($element)
. $q_folder
;
$db->setQuery($query);
$db->query();
return $errors;
}


static function uninstallExtension( $db, $extension, $type=null)
{
$errors = array();
$component = null;
$module = null;
$plugin = null; $folder = null;
$template = null;
$language = null;
if ( !empty( $extension)) {
if ( !empty( $type)) {
if ( $type == 'plugin') {
list( $folder, $element) = explode( '/', $extension);
}
else {
$$type = $extension; 
}
}
else if ( strncmp( $extension, 'com_', 4) == 0) {
$component = $extension;
}
else if ( strncmp( $extension, 'mod_', 4) == 0) {
$module = $extension;
}
else {
$parts = explode( '/', $extension);
if (count( $parts) == 2) {
$folder = $parts[0];
$element = $parts[1];
}
}
}

if ( !empty( $component)) {
$errors = MultisitesDatabase::uninstallComponent( $db, $component);
}

if ( !empty( $module)) {
$errorsModules = MultisitesDatabase::uninstallModule( $db, $module);
$errors = array_merge($errors, $errorsModules);
}

if ( !empty( $folder)) {
$errorsPlugins = MultisitesDatabase::uninstallPlugin( $db, $folder, $element);
$errors = array_merge($errors, $errorsPlugins);
}

if ( !empty( $template)) {
$errorsTemplates = MultisitesDatabase::uninstallTemplates_j16( $db, $template);
$errors = array_merge($errors, $errorsTemplates);
}

if ( !empty( $language)) {
$errorsLanguages = MultisitesDatabase::uninstallExtension_j16( $db, 'language', $language);
$errors = array_merge($errors, $errorsLanguages);
}
return $errors;
}


static function copyDBSharing( $sourcedb, $targetdb, $sharedTables, $toConfig, $site_id, $enteredvalues, $template)
{
$errors = array();
if ( empty( $sourcedb)) {
$msg = 'Invalid "From" DB connection';
$errors[] = $msg;
}
if ( empty( $targetdb)) {
$msg = 'Invalid "To" DB connection';
$errors[] = $msg;
}
if ( !empty( $errors)) {
return $errors;
}

$errors = MultisitesDatabase::createViews( $sourcedb, $targetdb, $sharedTables, $toConfig, $site_id, $enteredvalues, $template);
if ( !empty( $errors)) {
return $errors;
}

$errors = MultisitesDatabase::copyDB( $sourcedb, $targetdb, $toConfig, $sharedTables['views']);
if ( !empty( $errors)) {
return $errors;
}


$errors = MultisitesDatabase::installNewExtension( $sourcedb, $targetdb);
return $errors;
}


static function _notifyEmail( $user, $enteredvalues, $template)
{
$me = & JFactory::getUser();
$adminEmail = $me->get('email');
$adminName = $me->get('name');
if ( !empty( $enteredvalues['senderEmail'])) {
$adminEmail = $enteredvalues['senderEmail'];
$adminEmail = str_replace( array( '{user_email}', '{user_name}', '{site_name}', '{user_login}'),
array( $user->get('email'), $user->get('name'), $SiteName, $user->get('username')),
$adminEmail);

$adminEmail = MultisitesDatabase::evalStr( $adminEmail, $site_id, '', null, $enteredvalues);
}
if ( !empty( $enteredvalues['senderName'])) {
$adminName = $enteredvalues['senderName'];
$adminName = str_replace( array( '{user_email}', '{user_name}', '{site_name}', '{user_login}'),
array( $user->get('email'), $user->get('name'), $SiteName, $user->get('username')),
$adminName);

$adminName = MultisitesDatabase::evalStr( $adminName, $site_id, '', null, $enteredvalues);
}
$site_id = $enteredvalues['id'];
$SiteName = $enteredvalues['toSiteName'];
$SiteURL = $enteredvalues['indexDomains'][0];

if ( empty( $enteredvalues['emailSubject'])) { $subject = JText::_('SITE_DEPLOY_EMAIL_SUBJECT'); }
else { $subject = $enteredvalues['emailSubject']; }
$subject = str_replace( array( '{user_name}', '{site_name}', '{site_url}', '{user_login}', '{user_psw}'),
array( $user->get('name'), $SiteName, $SiteURL, $user->get('username'), $user->password_clear),
$subject);

$subject = MultisitesDatabase::evalStr( $subject, $site_id, '', null, $enteredvalues);

if ( empty( $enteredvalues['emailBody'])) { $body = JText::_('SITE_DEPLOY_EMAIL_BODY'); }
else { $body = $enteredvalues['emailBody']; }
$message = str_replace( array( '{user_name}', '{site_name}', '{site_url}', '{user_login}', '{user_psw}'),
array( $user->get('name'), $SiteName, $SiteURL, $user->get('username'), $user->password_clear),
$body);

$message = MultisitesDatabase::evalStr( $message, $site_id, '', null, $enteredvalues);

$to_email_address = $user->get('email');
if ( !empty( $enteredvalues['emailToAddress'])) {
$to_email_address = $enteredvalues['emailToAddress'];
$to_email_address = str_replace( array( '{user_name}', '{site_name}', '{user_login}'),
array( $user->get('name'), $SiteName, $user->get('username')),
$to_email_address);

$to_email_address = MultisitesDatabase::evalStr( $to_email_address, $site_id, '', null, $enteredvalues);
}
JUtility::sendMail( $adminEmail, $adminName, $to_email_address, $subject, $message );
}


static function _updateAdminUser( $toDB, $enteredvalues, $template)
{
require_once( JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_multisites' .DIRECTORY_SEPARATOR. 'libraries' .DIRECTORY_SEPARATOR. 'joomla' .DIRECTORY_SEPARATOR. 'multisitesfactory.php');

if ( empty( $enteredvalues['newAdminPsw']) && empty( $enteredvalues['newAdminEmail'])
&& (empty( $template) || (empty( $template->adminUserLogin) && empty( $template->adminUserName) && empty( $template->adminUserEmail) && empty( $template->adminUserPsw)) )
&& empty( $enteredvalues['notifyUser'])
)
{

return;
}
$notifyUser = false;
if ( !empty( $enteredvalues['notifyUser'])) {
$notifyUser = $enteredvalues['notifyUser'];
}

$sav_db =& MultisitesFactory::setDBO( $toDB);
$db =& JFactory::getDBO();

if ( !empty( $template) && !empty( $template->adminUserID)) {

$user_id = $template->adminUserID;
}
else {

$query = 'SELECT id, username FROM #__users'
. ' WHERE gid=25 AND block=0'
. ' ORDER BY id';
$db->setQuery( $query );
$row = $db->loadObject();
$user_id = $row->id;
}
$user = new JUser($user_id);
if ( !empty( $enteredvalues['newAdminPsw'])) {
$newAdminPsw = $enteredvalues['newAdminPsw'];
$params['password'] = $newAdminPsw;
$params['password2'] = $newAdminPsw;
}
else if ( !empty( $template) && !empty( $template->adminUserPsw)) {
if ( $template->adminUserPsw == '{user_psw}') {
$curuser =& JFactory::getUser();
$user->set( 'password', $curuser->password);
}
else {
$newAdminPsw = MultisitesDatabase::evalStr( $template->adminUserPsw, '', '', '', array());
$params['password'] = $newAdminPsw;
$params['password2'] = $newAdminPsw;

$notifyUser = true;
}
}
if ( !empty( $enteredvalues['newAdminEmail'])) {
$params['email'] = $enteredvalues['newAdminEmail'];
}
else if ( !empty( $template)) {
$curuser =& JFactory::getUser();
if ( !empty( $template->adminUserLogin)) {
$params['username'] = str_replace( '{user_login}',
$curuser->username,
$template->adminUserLogin
);
}
if ( !empty( $template->adminUserName)) {
$params['name'] = str_replace( '{user_name}',
$curuser->name,
$template->adminUserName
);
}
if ( !empty( $template->adminUserEmail)) {
$params['email'] = str_replace( '{user_email}',
$curuser->email,
$template->adminUserEmail
);
}
}

if ( !empty( $params)) {
$user->bind( $params);
if ( version_compare( JVERSION, '1.6') >= 0) {
$user->save( true);
}
else {

$my =& JFactory::getUser();
$sav_gid = $my->gid;
$my->gid = 25;
$user->save( true);
$my->gid = $sav_gid; 
}
}

MultisitesFactory::setDBO( $sav_db);
if ( $notifyUser) {
MultisitesDatabase::_notifyEmail( $user, $enteredvalues, $template);
}
}


static function _config_MediaSettings( $fromSiteID, $fromDB, $toDB, $enteredvalues, $site_id, $site_dir, $deploy_dir, $dbInfo, $template)
{
jimport( 'joomla.registry.registry');
jimport( 'joomla.filesystem.path');
require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');




$doUpdate = false;


if ( version_compare( JVERSION, '1.6') >= 0) {
$table =& JTable::getInstance('extension', 'JTable', array( 'dbo' => $fromDB));

$table->load( array( 'type' => 'component', 'element' => 'com_media') );
} else {
$table =& JTable::getInstance('component', 'JTable', array( 'dbo' => $fromDB));
$table->loadByOption( 'com_media' );

}
$fromMediaParams = array();
$registry = new JRegistry();
if ( version_compare( JVERSION, '3.0') >= 0) {
$registry->loadString( $table->params);
}
else if ( version_compare( JVERSION, '1.6') >= 0) {
$registry->loadJSON( $table->params);
}
else {
$registry->loadINI( $table->params);
}
$fromMediaParams['params'] = $registry->toArray();

if ( empty( $fromMediaParams['params']['file_path'])) {

JError::raiseWarning( 500, JText::_( 'SITE_DEPLOY_FROM_MEDIA_ERR'));
return false;
}

if ( version_compare( JVERSION, '1.6') >= 0) {
$table =& JTable::getInstance('extension', 'JTable', array( 'dbo' => $toDB));
$table->load( array( 'type' => 'component', 'element' => 'com_media') );
} else {
$table =& JTable::getInstance('component', 'JTable', array( 'dbo' => $toDB));
$table->loadByOption( 'com_media' );
}
$toMediaParams = array();
$registry = new JRegistry();
if ( version_compare( JVERSION, '3.0') >= 0) {
$registry->loadString( $table->params);
}
else if ( version_compare( JVERSION, '1.6') >= 0) {
$registry->loadJSON( $table->params);
}
else {
$registry->loadINI( $table->params);
}
$toMediaParams['params'] = $registry->toArray();


if ( !empty( $enteredvalues['media_dir'])) {
$file_path = JPath::clean( MultisitesDatabase::evalStr( $enteredvalues['media_dir'], $site_id, $site_dir, $deploy_dir, $dbInfo));
}
else if ( !empty( $template) && !empty( $template->media_dir)) {
$file_path = JPath::clean( MultisitesDatabase::evalStr( $template->media_dir, $site_id, $site_dir, $deploy_dir, $dbInfo));
}

if ( !empty( $file_path)) {

if(strpos($file_path, '/') === 0 || strpos($file_path, '\\') === 0) {

$file_path = 'images';
}
if(strpos($file_path, '..') !== false) {

$file_path = 'images';
}

if ( empty( $fromSiteID) || $fromSiteID==':master_db:') {
$from_dir = JPATH_ROOT .DS. $fromMediaParams['params']['file_path'];
}

else {
$from_dir = Jms2WinFactory::getSlaveRootPath( $fromSiteID) .DS. $fromMediaParams['params']['file_path'];

if ( !JFolder::exists( $from_dir)) {

$from_dir = JPATH_ROOT .DS. $fromMediaParams['params']['file_path'];
}
}

$toMediaParams['params']['file_path'] = str_replace( '\\', '/', $file_path);

if ( empty( $deploy_dir)) {
$to_dir = JPATH_ROOT . '/' . $toMediaParams['params']['file_path'];
}
else {
$to_dir = $deploy_dir . '/' . $toMediaParams['params']['file_path'];
}


if ( $from_dir != $to_dir && !JFolder::exists( $to_dir)) {

if ( !JFolder::exists( $from_dir)) {
JError::raiseWarning( 500, "Unable to replicate the media folder due to missing source [$from_dir]" );
return false;
}

else if ( !JFolder::copy( $from_dir, $to_dir)) {
JError::raiseWarning( 500, $table->getError() );
return false;
}
}
$doUpdate = true;
}


if ( !empty( $enteredvalues['images_dir'])) {
$image_path = JPath::clean( MultisitesDatabase::evalStr( $enteredvalues['images_dir'], $site_id, $site_dir, $deploy_dir, $dbInfo));
}
else if ( !empty( $template) && !empty( $template->images_dir)) {
$image_path = JPath::clean( MultisitesDatabase::evalStr( $template->images_dir, $site_id, $site_dir, $deploy_dir, $dbInfo));
}
if ( isset( $image_path)) {
if ( version_compare( JVERSION, '1.6') >= 0) {
$image_path_constant = 'images';
}
else {
$image_path_constant = 'images/stories';
}

if(strpos($image_path, '/') === 0 || strpos($image_path, '\\') === 0) {

$image_path = $image_path_constant;
}
if(strpos($image_path, '..') !== false) {

$image_path = $image_path_constant;
}

if ( empty( $fromSiteID) || $fromSiteID==':master_db:') {
$from_dir = JPATH_ROOT .DS. $fromMediaParams['params']['image_path'];
}

else {
$from_dir = Jms2WinFactory::getSlaveRootPath( $fromSiteID) .DS. $fromMediaParams['params']['image_path'];

if ( !JFolder::exists( $from_dir)) {

$from_dir = JPATH_ROOT .DS. $fromMediaParams['params']['image_path'];
}
}
$toMediaParams['params']['image_path'] = str_replace( '\\', '/', $image_path);

if ( empty( $deploy_dir)) {
$to_dir = JPATH_ROOT . '/' . $toMediaParams['params']['image_path'];
}
else {
$to_dir = $deploy_dir . '/' . $toMediaParams['params']['image_path'];
}


if ( $from_dir != $to_dir && !JFolder::exists( $to_dir)) {

if ( !JFolder::exists( $from_dir)) {
JError::raiseWarning( 500, "Unable to replicate the image folder due to missing source [$from_dir]" );
return false;
}

else if ( !JFolder::copy( $from_dir, $to_dir)) {
JError::raiseWarning( 500, $table->getError() );
return false;
}
}
$doUpdate = true;
}

if ( $doUpdate) {

$table->bind( $toMediaParams );

if (!$table->check()) {
JError::raiseWarning( 500, $table->getError() );
return false;
}

if (!$table->store()) {
JError::raiseWarning( 500, $table->getError() );
return false;
}
}

}


static function configureDB( $fromSiteID, $fromDB, $toDB, $enteredvalues, $site_id, $site_dir, $deploy_dir, $dbInfo, $template)
{
$errors = array();

MultisitesDatabase::_updateAdminUser( $toDB, $enteredvalues, $template);

MultisitesDatabase::_config_MediaSettings( $fromSiteID, $fromDB, $toDB, $enteredvalues, $site_id, $site_dir, $deploy_dir, $dbInfo, $template);
return $errors;
}




static function & getDBO($driver, $host, $user, $password, $database, $prefix, $select = true, $forceRetry=false)
{
static $db;
if ( ! $db || $forceRetry)
{
jimport('joomla.database.database');
$options = array ( 'driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix, 'select' => $select );
$db = & J2WinDatabase::getInstance( $options );

}
return $db;
}


static function createDatabase(& $db, $DBname, $DButfSupport=true)
{
$errors = array();
if ($DButfSupport) {
$sql = "CREATE DATABASE `$DBname` CHARACTER SET `utf8`";
}
else {
$sql = "CREATE DATABASE `$DBname`";
}
$db->setQuery($sql);
$db->query();
$result = $db->getErrorNum();
if ($result != 0)
{
$msg = JText::sprintf('SITE_DEPLOY_CREATEDB_ERR', $result, $sql, $db->getErrorMsg());
$errors[] = $msg;
}

$db->setQuery('COMMIT');
$db->query();
return $errors;
}


static function setDBCharset(& $db, $DBname)
{
if ($db->hasUTF())
{
$sql = "ALTER DATABASE `$DBname` CHARACTER SET `utf8`";
$db->setQuery($sql);
$db->query();
$result = $db->getErrorNum();
if ($result != 0) {
return false;
}
}
return true;
}


static function _getServerIP( $toConfig)
{
$ip = null;

if ( defined( 'MULTISITES_DB_GRANT_HOST')) {
$ip = MULTISITES_DB_GRANT_HOST;
if ( !empty( $ip)) {
return $ip;
}
}


if ( !empty( $_SERVER['LOCAL_ADDR'])) {
$ip = $_SERVER['LOCAL_ADDR'];
}

else if ( !empty( $_SERVER['SERVER_ADDR'])) {
$ip = $_SERVER['SERVER_ADDR'];
}

if ( $ip == '127.0.0.1') {

$toHost = $toConfig->getValue( 'config.host');
if ( $toHost == '127.0.0.1' || $toHost == 'localhost') {
return $ip;
}

else {


return null;
}
}
return $ip;
}


static function _getDBUserName( $toConfig)
{
$ip = MultisitesDatabase::_getServerIP( $toConfig);
if ( empty( $ip)) {
$user_name = MultisitesDatabase::backquote( $toConfig->getValue( 'config.user'))
. '@'
. MultisitesDatabase::backquote( '%')
;
}
else {
$user_name = MultisitesDatabase::backquote( $toConfig->getValue( 'config.user'))
. '@'
. MultisitesDatabase::backquote( $ip)
;
}
return $user_name;
}


static function _createUser( & $db, $toConfig, $table_name = null)
{
$errors = array();
if ( empty( $table_name)) {
$table_name = MultisitesDatabase::backquote( $toConfig->getValue( 'config.db'))
. '.*';
}
$user_name = MultisitesDatabase::_getDBUserName( $toConfig);
$toDBPsw = $toConfig->getValue( 'config.password');
$query = "GRANT ALL PRIVILEGES ON $table_name"
. " TO $user_name"
. (empty($toDBPsw) ? '' : " IDENTIFIED BY '$toDBPsw'")
. " WITH GRANT OPTION;"
;
$db->setQuery( $query);
$db->query();
$result = $db->getErrorNum();
if ($result != 0) {
$msg = JText::sprintf('SITE_DEPLOY_CREATEUSER_ERR', $result, $query, $db->getErrorMsg());
$errors[] = $msg;
if ( defined( 'MULTISITESDB_DEBUG')) {
ob_start();
debug_print_backtrace();
$stack = ob_get_contents();
ob_end_clean();
Debug2Win::debug( "_createUser ERROR query=[$query] db: " . var_export( $db, true) . $stack);
}
return $errors;
}
$db->setQuery( "FLUSH PRIVILEGES;");
$db->query();
return $errors;
}

static function makeDB($fromConfig, $toConfig)
{
if ( defined( 'MULTISITESDB_DEBUG')) { Debug2Win::debug_start( '>> makeDB() - START'); }
$errors = array();
$DBtype = $fromConfig->getValue( 'config.dbtype');
$DBhostname = $toConfig->getValue( 'config.host');
$DBname = $toConfig->getValue( 'config.db');
$DBuserName = $fromConfig->getValue( 'config.user');
$DBpassword = $fromConfig->getValue( 'config.password');
if ( defined( 'MULTISITES_DB_ROOT_USER')) {
$DBuserName = MULTISITES_DB_ROOT_USER;
if ( defined( 'MULTISITES_DB_ROOT_PSW')) {
$DBpassword = MULTISITES_DB_ROOT_PSW;
}
}
$DBPrefix = $toConfig->getValue( 'config.dbprefix');
$toDBUser = $toConfig->getValue( 'config.user');
$toDBPsw = $toConfig->getValue( 'config.password');
$toOptions = array( 'driver' => $DBtype,
'host' => $DBhostname,
'user' => $toDBUser,
'password' => $toDBPsw,
'database' => $DBname,
'prefix' => $DBPrefix
);
$rootOptions = $toOptions;
$rootOptions['user'] = $DBuserName;
$rootOptions['password'] = $DBpassword;

$dbTo = & MultisitesDatabase::getInstance( $toOptions, 'none');
if ( $dbTo->isConnected()) {

}

else {

$dbRoot = & MultisitesDatabase::getInstance( $rootOptions, 'user');
if ( $dbRoot->isConnected()) {

if ( $dbRoot->createUser( $toConfig)) {

$dbTo = & MultisitesDatabase::getInstance( $toOptions, 'none');
if ( $dbTo->isConnected()) {

}
else {
$msg = "MakeDB - Unable to login with the new user [$toDBUser].";
$errors[] = $msg;
return $errors;
}
}
else {

$msg = "MakeDB - Unable to create the user [$toDBUser] dynamically.";
$errors[] = $msg;
return $errors;
}
}
else {


$msg = 'MakeDB - Unable to login as Root to create a DB user dynamically.';
$errors[] = $msg;
return $errors;
}
}

if ( $dbTo->select_db()) {

}

else {


$dbRoot = & MultisitesDatabase::getInstance( $rootOptions, 'user');
if ( $dbRoot->isConnected()) {

if ( $dbRoot->select_db( $toOptions['database'])) {


if ( $dbRoot->createUser( $toConfig)) {

$dbTo = & MultisitesDatabase::getInstance( $toOptions, 'none');
if ( $dbTo->isConnected()) {

}
else {
$msg = "MakeDB - Unable creating the new user [$toDBUser] for the existing toDB.";
$errors[] = $msg;
return $errors;
}
}
else {

$msg = "MakeDB - Unable to create the user [$toDBUser] for the existing toDB.";
$errors[] = $msg;
return $errors;
}
}

else {

$errors = MultisitesDatabase::createDatabase( $dbRoot, $DBname);
if ( !empty( $errors)) {
return $errors;
}

$dbTo = & MultisitesDatabase::getInstance( $toOptions, 'none');
if ( $dbTo->isConnected()) {

}
else {
$msg = "MakeDB - Unable to reconnection with the 'to user' [$toDBUser].";
$errors[] = $msg;
return $errors;
}

if ( $dbTo->select_db()) {

}

else {

if ( $dbRoot->createUser( $toConfig)) {

$dbTo = & MultisitesDatabase::getInstance( $toOptions, 'none');
if ( $dbTo->isConnected()) {

}
else {
$msg = "MakeDB - Unable connect the new user [$toDBUser].";
$errors[] = $msg;
return $errors;
}
}
else {

$msg = "MakeDB - Unable to grant the user [$toDBUser] for the existing toDB.";
$errors[] = $msg;
return $errors;
}
}
}
}
else {


$msg = 'MakeDB - Unable to login as Root to create the DB dynamically.';
$errors[] = $msg;
return $errors;
}
}





$db = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $toDBUser, $toDBPsw, $DBname, $DBPrefix);

if ( Jms2WinError::isError($db) ) {



}
else if ($err = $db->getErrorNum()) {



}
else if ( $db->connected()) {


return $errors;
}


$DBselect = false;
$db = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $DBuserName, $DBpassword, null, $DBPrefix, $DBselect, true);
if ( Jms2WinError::isError($db) ) {

$msg = 'MakeDB - DB Connection fail.' . JText::sprintf('WARNNOTCONNECTDB', $db->toString());
$errors[] = $msg;
return $errors;
}
if ($err = $db->getErrorNum()) {

$msg = "MakeDB - Login MySQL error with user [$DBuserName]. " . JText::_('WARNNOTCONNECTDB') . ' Error number = '.$db->getErrorNum() . ' Error message'. $db->getErrorMsg() ;
$errors[] = $msg;

$errors[] = $msg;
return $errors;
}

$DButfSupport = $db->hasUTF();

if ( ! $db->select($DBname) )
{


$todb = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $toDBUser, $toDBPsw, null, $DBPrefix, $DBselect);
if ( Jms2WinError::isError( $todb)
|| $todb->getErrorNum()
|| ! $todb->select($DBname)
)
{


$errors = MultisitesDatabase::createDatabase($db, $DBname, $DButfSupport);
if ( !empty( $errors)) {
return $errors;
}


$todb = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $toDBUser, $toDBPsw, $DBname, $DBPrefix, true, true);
if ( defined( 'MULTISITESDB_DEBUG')) { Debug2Win::debug( "makeDB retry connect DBname=[$DBname] with user=[$toDBUser] / psw=[$toDBPsw] : " . var_export( $todb, true)); }
if ( Jms2WinError::isError( $todb)
|| $todb->getErrorNum()
|| ! $todb->select($DBname)
)
{

$db = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $DBuserName, $DBpassword, null, $DBPrefix, false, true);


$errors = MultisitesDatabase::_createUser( $db, $toConfig);
if ( defined( 'MULTISITESDB_DEBUG')) { Debug2Win::debug( "makeDB create toDB User / Psw: " . var_export( $toConfig, true) . var_export( $db, true)); }
if ( !empty( $errors)) {
return $errors;
}
}



$db = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $toDBUser, $toDBPsw, $DBname, $DBPrefix, true, true);
if ( Jms2WinError::isError( $db)
|| $db->getErrorNum()
|| ! $db->select($DBname)
)
{
$msg = "MakeDB - unable to connect on created DB " . ' Error number = '.$db->getErrorNum() . ' Error message'. $db->getErrorMsg() ;
$errors[] = $msg;
if ( defined( 'MULTISITESDB_DEBUG')) { Debug2Win::debug( $msg); }
return $errors;
}
}

} else {


MultisitesDatabase::setDBCharset($db, $DBname);

$todb = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $toDBUser, $toDBPsw, null, $DBPrefix, $DBselect);
if ( Jms2WinError::isError( $todb) || $todb->getErrorNum()
|| ! $todb->select($DBname)) {

$errors = MultisitesDatabase::_createUser( $db, $toConfig);
if ( !empty( $errors)) {
return $errors;
}
}
}
if ( defined( 'MULTISITESDB_DEBUG')) { Debug2Win::debug_stop( '<< makeDB() - STOP - SUCCESS');}

return $errors;
}
} 
?>
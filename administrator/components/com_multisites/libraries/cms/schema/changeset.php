<?php
// file: changeset.php.
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


defined('_JEXEC') or die;
if ( !defined('JPATH_PLATFORM')) { define('JPATH_PLATFORM', true); }
require_once( JPATH_LIBRARIES . '/cms/schema/changeset.php');
JLoader::register('JSchemaChangeset', JPATH_LIBRARIES . '/cms/schema/changeset.php');
require_once( dirname( __FILE__) . '/changeitem.php');



class MultisitesSchemaChangeset extends JSchemaChangeset
{

public function __construct( $db, $dmrow, $enteredvalues, $withChangeItems=true, $sites=array())
{
$this->db = $db;
$this->dmrow = $dmrow;
$this->enteredvalues = $enteredvalues;
$this->folder = null;
$this->selectedFolder = '';
if ( $withChangeItems) {
$this->chItem = MultisitesSchemaChangeitem::getInstance($this->db, 'fake', '-- fake statement');
$this->getChangeItems( $sites);
}
}

public static function &getInstance( $db, $dmrow, $enteredvalues)
{
static $instance;
if (!is_object($instance))
{
$instance = new MultisitesSchemaChangeset( $db, $dmrow, $enteredvalues);
}
return $instance;
}


function getFoldersCfg( $sites=array())
{
static $instances = array();
$signature = md5(serialize( $this->dmrow));
if ( isset( $instances[$signature])) {
return $instances[$signature];
}


$foldersCfg = array( 'file|joomla||1.5|mysql' => array( '2.5.6 legacy 1.5' => array(dirname(__FILE__).'/sql/updates/mysql/*.sql | ignore_drop_column',


)
),
'file|joomla||1.6|mysql' => array( '2.5.6' => array('{master}/installation/sql/mysql/joomla_update_16beta2.sql',
'{master}/installation/sql/mysql/joomla_update_16beta113.sql',
'{master}/installation/sql/mysql/joomla_update_16beta114.sql',
'{master}/installation/sql/mysql/joomla_update_16beta115.sql',
'{master}/installation/sql/mysql/joomla_update_16ga.sql',
'{master}/installation/sql/mysql/joomla_update_160to161.sql',
'{master}/administrator/components/com_admin/sql/updates/mysql/*.sql'
)
),
'file|joomla||1.6|sqlazure' => array( '2.5.6' => array( '{master}/administrator/components/com_admin/sql/updates/sqlazure/*.sql')),
'file|joomla||*|mysql' => array( '2.5.6' => array( '{master}/administrator/components/com_admin/sql/updates/mysql/*.sql |2.5.0-2011-12-06.sql fix_insert|2.5.0-2011-12-21-1.sql fix_insert|2.5.0-2011-12-22.sql fix_replace',

)
),
'file|joomla||*|sqlazure' => array( '2.5.6' => array( '{master}/administrator/components/com_admin/sql/updates/sqlazure/*.sql')),
'file|joomla||*|sqlsrv' => array( '2.5.6' => array( '{master}/administrator/components/com_admin/sql/updates/sqlsrv/*.sql')),


);
$foldersCfg = array();

if ( !empty( $sites)) {
foreach( $sites as $site) {
if ( !empty( $site->preprocess_datamodel)) { $site_datamodel = $site->preprocess_datamodel; }
else { $site_datamodel = array();
if ( !empty( $this->dmrow)) { $site_datamodel[] = $this->dmrow; }
}
if ( !empty( $site_datamodel)) {
foreach( $site_datamodel as $datamodel) {
$dm_folder = !empty( $datamodel->folder) ? $datamodel->folder : '';
$keyHdr="$datamodel->type|$datamodel->element|$dm_folder|$datamodel->code_version";

if ( !empty( $datamodel->master_structures)) {
$key=$keyHdr."|$site->dbtype";
$label = "master:$datamodel->name";
if ( empty( $foldersCfg[$key])) {
$foldersCfg[$key] = array( $label => $datamodel->master_structures);
}
else {
$foldersCfg[$key] = array_merge( $foldersCfg[$key],
array( $label => $datamodel->master_structures));
}
}

if ( !empty( $datamodel->template_structures)) {
$key=$keyHdr."|$site->dbtype";
$label = "template:$datamodel->name";
if ( empty( $foldersCfg[$key])) {
$foldersCfg[$key] = array( $label => $datamodel->template_structures);
}
else {
$foldersCfg[$key] = array_merge( $foldersCfg[$key],
array( $label => $datamodel->template_structures));
}
}

if ( !empty( $datamodel->install_queries)) {
$key=$keyHdr."|$site->dbtype";
$label = "xml:$datamodel->name";
if ( empty( $foldersCfg[$key])) {
$foldersCfg[$key] = array( $label => $datamodel->install_queries);
}
else {
$foldersCfg[$key] = array_merge( $foldersCfg[$key],
array( $label => $datamodel->install_queries));
}
}

if ( !empty( $datamodel->install_sqlfiles)) {
foreach( $datamodel->install_sqlfiles as $sqlfile) {
$key=$keyHdr."|$sqlfile->driver";
$sql_filename = basename( $sqlfile->filename);
$charset = !empty( $sqlfile->charset) ? $sqlfile->charset.':' : '';
$label = "sqlfile:$charset$sql_filename";
$foldersCfg[$key][$label][] = JPath::clean( $datamodel->jpath_extension.'/'.$sqlfile->filename);
}
}

if ( !empty( $datamodel->discover_sqlfiles)) {
foreach( $datamodel->discover_sqlfiles as $file) {
$key=$keyHdr."|$site->dbtype";
$sql_filename = basename( $file);
$label = "discover:$sql_filename";
$foldersCfg[$key][$label][] = $file;
}
}
}
}
}
}


$updateFiles = JFolder::files( dirname( __FILE__).'/sql/updates', 'updates\.php$', true, true);
if ( !empty( $updateFiles)) {
foreach( $updateFiles as $updateFile) {
$extensionCfg = array();
include( $updateFile);

if ( !empty( $extensionCfg)) {
foreach( $extensionCfg as $key => $variants) {
if ( empty( $foldersCfg[$key])) {
$foldersCfg[$key] = $variants;
}
else {


$foldersCfg[$key] = array_merge( $foldersCfg[$key], $variants);
}
}
}
}
}


JPluginHelper::importPlugin('multisitesmigration');
$results = JFactory::getApplication()->triggerEvent('getUpdateDescriptions', array ());
if ( !empty( $results)) {
foreach( $results as $result) {
if ( is_array( $result)) {
$foldersCfg = array_merge( $foldersCfg, $result);
}
}
}
$instances[$signature] = $foldersCfg;
return $instances[$signature];
}


function getFilteredFolders( $filters, $sites=array())
{
static $instances = array();
$signature = md5(serialize( $filters));
if ( isset( $instances[$signature])) {
return $instances[$signature];
}
$results = array();
if ( !empty( $filters)) {

if ( !empty( $filters['type'])) { $type = $filters['type']; }

if ( !empty( $filters['element'])) { $element = $filters['element']; }

if ( !empty( $filters['folder'])) { $folder = $filters['folder']; }

if ( !empty( $filters['version'])) {
$version = $filters['version'];
$vers = explode( '.', $version);
$vers_any = '*';
if ( !empty( $vers[0])) { $vers_0 = $vers[0]; }
else { $vers_0 = '*'; }
if ( !empty( $vers[1])) { $vers_1 = $vers_0.'.'.$vers[1]; }
else { $vers_1 = $vers_0.'.'.'*'; }
if ( !empty( $vers[2])) { $vers_2 = $vers_1.'.'.$vers[2]; }
else { $vers_2 = $vers_1.'.'.'*'; }
}

if ( !empty( $filters['dbtype'])) { $sqlFolder = $dbtype = $filters['dbtype'];
if (substr( $sqlFolder, 0, 5) == 'mysql') { $sqlFolder = 'mysql'; }
}
}

$foldersCfg = $this->getFoldersCfg( $sites);
if ( !empty( $foldersCfg)) {
foreach( $foldersCfg as $key => $value) {
list( $k_type, $k_element, $k_folder, $k_version, $k_sqlFolder) = explode( '|', $key);

$k_vers = explode( '.', $k_version);
for ($i=count( $k_vers); $i<3; $i++) { $k_vers[$i] = '*'; }
$k_version = implode( '.', $k_vers);


$found = true;
if ( empty( $type) || $type == $k_type || $type == '*' || $type == '[unselected]') {}
else { $found = false; }
if ( empty( $element) || $element == $k_element || $element == '*' || $element == '[unselected]') {}
else { $found = false; }
if ( empty( $folder) || $folder == $k_folder || $folder == '*' || $folder == '[unselected]') {}
else { $found = false; }
if ( empty( $version) || $version == '*' || $version == '[unselected]') {}
else if ( $k_version == $vers_2 || $k_version == $vers_1 || $k_version == $vers_0 || $k_version == $vers_any) {}
else { $found = false; }
if ( empty( $sqlFolder) || $sqlFolder == $k_sqlFolder || $dbtype == $k_sqlFolder || $sqlFolder == '*' || $sqlFolder == '[unselected]') {}
else { $found = false; }
if ( $found) {
foreach( $value as $label => $row) {
$results["$key|$label"] = $row;
}
}
}
}
$instances[$signature] = $results;
return $instances[$signature];
}


function getFolders( $sites=array())
{
$foldersCfg = $this->getFoldersCfg( $sites);
$dmrow = $this->dmrow;

if ( !empty( $this->enteredvalues) && !empty( $this->enteredvalues['schema'])) {
list( $type, $element, $folder, $version_id, $sqlFolder, $label) = explode( '|', $this->enteredvalues['schema']);
}
else {

$db = $this->db;
$type = !empty( $dmrow->type) ? $dmrow->type : '';
$element = !empty( $dmrow->element) ? $dmrow->element : '';
$folder = !empty( $dmrow->folder) ? $dmrow->folder : '';
$sqlFolder = $db->name;
$version_id = !empty( $dmrow->version_id) ? $dmrow->version_id : '';
}
$dbtype = $sqlFolder;
if (substr($sqlFolder, 0, 5) == 'mysql') { $sqlFolder = 'mysql'; } 
if ( empty( $version_id)) {
if ( !empty( $this->dmrow->version_id)) { $version_id = $this->dmrow->version_id; }
else { $version_id = $this->dmrow->code_version; }
}
$vers = explode( '.', $version_id);
$vers_any = '*';
if ( isset( $vers[0])) { $vers_0 = $vers[0]; }
else { $vers_0 = '*'; }
if ( isset( $vers[1])) { $vers_1 = $vers_0.'.'.$vers[1]; }
else { $vers_1 = $vers_0.'.'.'*'; }
if ( isset( $vers[2])) { $vers_2 = $vers_1.'.'.$vers[2]; }
else { $vers_2 = $vers_1.'.'.'*'; }



if ( !empty( $foldersCfg["$type|$element|$folder|$vers_2|$dbtype"])) {
$this->selectedFolder = "$type|$element|$folder|$vers_2|$dbtype";
}


else if ( !empty( $foldersCfg["$type|$element|$folder|$vers_2|$sqlFolder"])) {
$this->selectedFolder = "$type|$element|$folder|$vers_2|$sqlFolder";
}

else if ( !empty( $foldersCfg["$type|$element|$folder|$vers_1|$dbtype"])) {
$this->selectedFolder = "$type|$element|$folder|$vers_1|$dbtype";
}

else if ( !empty( $foldersCfg["$type|$element|$folder|$vers_1|$sqlFolder"])) {
$this->selectedFolder = "$type|$element|$folder|$vers_1|$sqlFolder";
}

else if ( !empty( $foldersCfg["$type|$element|$folder|$vers_0|$dbtype"])) {
$this->selectedFolder = "$type|$element|$folder|$vers_0|$dbtype";
}

else if ( !empty( $foldersCfg["$type|$element|$folder|$vers_0|$sqlFolder"])) {
$this->selectedFolder = "$type|$element|$folder|$vers_0|$sqlFolder";
}

else if ( !empty( $foldersCfg["$type|$element|$folder|$vers_any|$dbtype"])) {
$this->selectedFolder = "$type|$element|$folder|$vers_any|$sqlFolder";
}

else if ( !empty( $foldersCfg["$type|$element|$folder|$vers_any|$sqlFolder"])) {
$this->selectedFolder = "$type|$element|$folder|$vers_any|$sqlFolder";
}
if ( !empty( $this->selectedFolder)) {
$variants = $foldersCfg[$this->selectedFolder];
if ( !empty( $variants)) {
$short_version_id = $dmrow->code_version;
if ( !empty( $dmrow->version_id)) {

if ( preg_match('#([a-z0-9\.]+)#', $dmrow->version_id, $match)) {
$short_version_id = $match[1];
}
else {
$short_version_id = $dmrow->version_id;
}
}
foreach( $variants as $variant_label => $selectedFolder) {
if ( count( $variants) == 1) {
$this->selectedFolder_label = $variant_label;
return array( 'key' => $this->selectedFolder,
'label' => $variant_label,
'values' => $selectedFolder);
}

if ( empty( $label)) {
if ( preg_match('#^xml\:#', $variant_label)
|| preg_match('#^sqlfile\:#', $variant_label)
|| preg_match('#^discover\:#', $variant_label)
|| preg_match('#^master\:#', $variant_label)
|| preg_match('#^template\:#', $variant_label)
)
{
$this->selectedFolder_label = $variant_label;
return array( 'key' => $this->selectedFolder,
'label' => $variant_label,
'values' => $selectedFolder);
}

if ( preg_match('#([a-z0-9\.]+)#', $variant_label, $match)) {
$variant_short_label = $match[1];
}
else {
$variant_short_label = $variant_label;
}
if ($variant_label == $dmrow->version_id) { $selected_version_id_label = $variant_label;
$selected_version_id = $selectedFolder;
}
if ( $variant_label == $short_version_id
|| $variant_short_label == $short_version_id
) { $selected_short_version_id_label = $variant_label;
$selected_short_version_id = $selectedFolder;
}
if ($variant_label == $dmrow->code_version) { $selected_code_version_label = $variant_label;
$selected_code_version = $selectedFolder;
}
$selected_latest_label = $variant_label;
$selected_latest = $selectedFolder;
}

else if ( $variant_label == $label) {
$this->selectedFolder_label = $variant_label;
return array( 'key' => $this->selectedFolder,
'label' => $variant_label,
'values' => $selectedFolder);
}
}

if ( !empty( $selected_version_id)) { $this->selectedFolder_label = $selected_version_id_label;
return array( 'key' => $this->selectedFolder,
'label' => $selected_version_id_label,
'values' => $selected_version_id);
}
if ( !empty( $selected_short_version_id)) { $this->selectedFolder_label = $selected_short_version_id_label;
return array( 'key' => $this->selectedFolder,
'label' => $selected_short_version_id_label,
'values' => $selected_short_version_id);
}
if ( !empty( $selected_code_version)) { $this->selectedFolder_label = $selected_code_version_label;
return array( 'key' => $this->selectedFolder,
'label' => $selected_code_version_label,
'values' => $selected_code_version);
}
if ( !empty( $selected_latest)) { $this->selectedFolder_label = $selected_latest_label;
return array( 'key' => $this->selectedFolder,
'label' => $selected_latest_label,
'values' => $selected_latest);
}
}
}
$this->selectedFolder = '';
$this->selectedFolder_label = '';
return array();
}


function getSelectedFolder()
{
return $this->selectedFolder;
}


function getSelectedFolder_Label()
{
if ( !empty( $this->selectedFolder_label)) {
return $this->selectedFolder_label;
}
return '';
}


function getFilesInFolder( $folderPattern)
{

$folderPattern = str_replace( array( '{master}', '{root}'),
array( JPATH_ROOT, JPATH_ROOT ),
$folderPattern);

$fixActions = explode( '|', $folderPattern);
$fixFiles = array();
if ( count( $fixActions) > 1) {
$folderPattern = trim( array_shift( $fixActions));
foreach( $fixActions as $actionStr) {
if ( !empty( $actionStr)) {
$actions = explode( ' ', trim( $actionStr));
$nAct = count( $actions);
if ( $nAct == 1) {
$path = dirname( $folderPattern);
$pattern = basename( $folderPattern);

$pattern = str_replace( array( '.', '-', '_', '*'),
array( '\.', '\-', '\_', '(.*)'),
$pattern );
$files = JFolder::files( $path, $pattern.'$');
foreach( $files as $file) {
$fixFiles[ $file] = $actions;
}
}
else if ( $nAct >= 2) {
$fileName = array_shift( $actions);
$fixFiles[ $fileName] = $actions;
}
}
}
}
$path = dirname( $folderPattern);
$bnpattern = basename( $folderPattern);

$pattern = str_replace( array( '.', '-', '_', '*'),
array( '\.', '\-', '\_', '(.*)'),
$bnpattern );

if ( strpos( $bnpattern, '*') === false) { $exact = '^'; }
else { $exact = ''; }
$results = array( 'files' => JFolder::files( $path, $exact.$pattern.'$', 1, true),
'fixFiles' => $fixFiles);
return $results;
}


function parseSQLBuffer( &$result, &$buffer, $file, $fixActions, $legacyMode = array( 'ignore_drop_column'))
{

$query = array();
$lines = explode( "\n", $buffer);
foreach( $lines as $oneline) {
$line = trim ( $oneline);

if ( empty( $line)) {}

else if ( substr( $line, 0, 1) == '#') {}

else if ( substr( $line, 0, 2) == '--') {}

else { $query[] = preg_replace( '/(\s)+#\s(.*)$/', '', $oneline); }
}

if ( !empty($query))
{

$queries = $this->db->splitSql( implode( "\n", $query));
if ( !empty( $queries)) {
foreach ($queries as $statement)
{

$statement = trim( $statement);
if ( !empty( $statement))
{
$ignore_drop_column = in_array( 'ignore_drop_column', $legacyMode);
if ( $fixActions === false) {}
else {
if ( !empty( $fixActions)) {
foreach( $fixActions as $action) {
if ( $action == 'default'
|| in_array( 'legacy', $legacyMode)
)
{
$statement = str_replace( array( 'CREATE TABLE', 'INSERT INTO', 'REPLACE INTO'),
array( 'LEGACY TABLE', 'FIX_INSERT INTO', 'FIX_INSERT INTO'),
$statement
);
$ignore_drop_column = true;
}
if ( $action == 'legacy_table' || $action == 'ignore_drop_column'
|| in_array( 'legacy_table', $legacyMode) || in_array( 'ignore_drop_column', $legacyMode)
)
{
$statement = str_replace( 'CREATE TABLE', 'LEGACY TABLE', $statement);
}
if ( $action == 'fix_table' || in_array( 'fix_table', $legacyMode)) {
$statement = str_replace( 'CREATE TABLE', 'FIX TABLE', $statement);
}
if ( $action == 'fix_insert' || in_array( 'fix_insert', $legacyMode)) {
$statement = str_replace( 'INSERT INTO', 'FIX_INSERT INTO', $statement);
}
if ( $action == 'fix_replace' || in_array( 'fix_replace', $legacyMode)) {
$statement = str_replace( 'REPLACE INTO', 'FIX_INSERT INTO', $statement);
}
if ( $action == 'ignore_drop_column' || in_array( 'ignore_drop_column', $legacyMode)) {
$ignore_drop_column = true;
}
}
}
}

if ( in_array( 'legacy', $legacyMode)) {
$statement = str_replace( array( 'CREATE TABLE', 'INSERT INTO', 'REPLACE INTO'),
array( 'LEGACY TABLE', 'FIX_INSERT INTO', 'FIX_INSERT INTO'),
$statement
);
$ignore_drop_column = true;
}
if ( in_array( 'legacy_table', $legacyMode) || in_array( 'ignore_drop_column', $legacyMode)) {
$statement = str_replace( 'CREATE TABLE', 'LEGACY TABLE', $statement);
}
if ( in_array( 'fix_table', $legacyMode)) {
$statement = str_replace( 'CREATE TABLE', 'FIX TABLE', $statement);
}
if ( in_array( 'fix_insert', $legacyMode)) {
$statement = str_replace( 'INSERT INTO', 'FIX_INSERT INTO', $statement);
}
if ( in_array( 'fix_replace', $legacyMode)) {
$statement = str_replace( 'REPLACE INTO', 'FIX_INSERT INTO', $statement);
}
if ( in_array( 'ignore_drop_column', $legacyMode)) {
$ignore_drop_column = true;
}

if ( $ignore_drop_column) {
if (preg_match( '#^ALTER\sTABLE(.*)DROP#si', $statement)) {
continue;
}
}
$fileQueries = new stdClass;
$fileQueries->file = $file;
$fileQueries->updateQuery = $statement;

if ( !empty( $fixActions)) { $fileQueries->fixActions = $fixActions; }
$result[] = $fileQueries;
}
}
}
}
}


function getSQLQueries( array $sqlfiles, array $fixFiles)
{

$result = array();
if ( !empty( $this->enteredvalues['legacymode'])) { $legacyMode = $this->enteredvalues['legacymode']; }
else { $legacyMode = array(); }
foreach ($sqlfiles as $file)
{
$fixActions = false;
if ( isset( $fixFiles[ basename( $file)])) {
$fixActions = $fixFiles[ basename( $file)];
if ( empty( $fixActions)) {
$fixActions = array( 'default');
}
if ( in_array( 'skip_file', $fixActions)) {
continue;
}
}
$buffer = file_get_contents($file);
$this->parseSQLBuffer( $result, $buffer, $file, $fixActions, $legacyMode);
}

if ( !empty( $this->enteredvalues) && !empty( $this->enteredvalues['usersql'])) {
$this->parseSQLBuffer( $result, $this->enteredvalues['usersql'], '** FREE SQL **', array(), $legacyMode);
}
return $result;
}

function fixQuote( $string) { return $this->chItem->fixQuote( $string); }
function nameQuote( $string) { return $this->chItem->nameQuote( $string); }
function normalizeType( $string) { return $this->chItem->normalizeType( $string); }


function isFieldExists( $table, $field, $typeQueryStr='')
{
$query = 'SHOW COLUMNS IN ' . $table . ' WHERE Field = ' . $this->fixQuote( $field);
$ignoreFieldType = true;
$isFieldNull = true;
$isAutoIncrement = false;
if ( !empty( $typeQueryStr)) {
$ignoreFieldType = false;
$typeQuery = $typeQueryStr;
if ( preg_match( '#AUTO_INCREMENT#i', $typeQuery)) { $isAutoIncrement = true; }
else { $isAutoIncrement = false; }

if ( preg_match( '#(.*)(\sNOT|\sNULL)*\sDEFAULT#i', $typeQuery, $matchType)) {
$typeQuery=$matchType[1];
if ( preg_match( '#NOT\sNULL#i', trim( $matchType[2]))) { $isFieldNull = false; }
else { $isFieldNull = true; }
$defaultStr = substr( $typeQuery, strlen( $matchType[0]));
}
else if ( preg_match( '#^(INT(.*)\sNOT\sNULL\sAUTO_INCREMENT)#i', $typeQuery, $matchType)) { $typeQuery=$matchType[1]; $isFieldNull = false; }
else if ( preg_match( '#(.*)\sNOT\sNULL#i', $typeQuery, $matchType)) { $typeQuery=$matchType[1]; $isFieldNull = false; }
else if ( preg_match( '#(.*)\s(NULL|DEFAULT)#i', $typeQuery, $matchType)) { $typeQuery=$matchType[1]; $isFieldNull = true; }
$type = $this->fixQuote($this->normalizeType( $typeQuery));
if ( strpos( $type, '%') !== false) { $oper = 'LIKE '; }
else { $oper = '= '; }
$query .= ' AND type ' . $oper.$type;
}
$this->db->setQuery( $query);
$row = $this->db->loadObject();
if ( !empty( $row)) {
if ( $ignoreFieldType) { return true; }


if ( preg_match( '#([0-9]+)#i', $row->Type, $matchRowType)
&& preg_match( '#([0-9]+)#i', $typeQuery, $matchTypeQuery))
{
if ( $matchRowType[1] >= $matchTypeQuery[1]) {} 
else { return JText::_( 'Fix Invalid field type length'); }
}


if ( strtoupper( $row->Null) == 'YES') {

if ( $isFieldNull) {} 
else { return JText::_( 'Fix field requires NOT NULL'); }
}

else if ( !$isFieldNull) {} 
else { return JText::_( 'Fix field requires NULL'); }

if ( $isAutoIncrement) {
if ( strtolower( $row->Extra) == 'auto_increment') { } 
else { return JText::_( 'Fix field requires AUTO INCREMENT'); }
}
return true;
}

$error = 'not found';
return JText::_( 'Fix field not found');
}

function isTableExists( $table)
{
$query = 'SHOW TABLES LIKE ' . $this->fixQuote( $table);
$this->db->setQuery( $query);
$row = $this->db->loadObject();
if ( !empty( $row)) { return true; }
return false;
}
function getIndexFields( $columnQuery)
{
$str = trim( $columnQuery);
$str = trim( $str, ',');
$str = trim( $str);
if ( substr( $str, 0, 1) == '(' && substr( $str, -1) == ')') {
$str = substr( $str, 1, strlen( $str)-2);
}
$idx_fields = explode( ',', $str);
$idx_len = array();
for( $i=0; $i<count( $idx_fields); $i++) {
$idx_fields[$i] = trim( $idx_fields[$i]);
if ( preg_match( '#(\([0-9\s]+\))#', $idx_fields[$i], $match)) {
$idx_fields[$i] = trim( str_replace( $match[1], '', $idx_fields[$i]));
$idx_len[$i] = trim( $match[1], '()');
}
}
$results = array( 'idx_fields' => $idx_fields,
'idx_len' => $idx_len);
return $results;
}

function isIndexExists( $table, $indexQuery, $checkModified=false)
{
if ( preg_match( '#^PRIMARY\sKEY(.*)#i', $indexQuery, $match)) {
$Key_name = 'PRIMARY';
if ( preg_match( '#\(([^\(\)]+)\)#', $match[1], $fieldMatch)) {
$idx_fields = explode( ',', trim( $fieldMatch[1], ' (),'));
}
else {
$idx_fields = explode( ',', trim( $match[1], ' (),'));
}
}
else if ( preg_match( '#(\s|PRIMARY|UNIQUE|FOREIGN|KEY|INDEX)+(.*)#i', $indexQuery, $match)) {
$keyDescr = trim( $match[2]);
if ( preg_match( '#^([^\(]+)\(#', $keyDescr, $keyMatch)) {
$words = explode( ' ', trim( $keyMatch[1], ' ('));
$Key_name = array_shift( $words);

$r = $this->getIndexFields( substr( $keyDescr, strlen( $keyMatch[1])));
$idx_fields = $r['idx_fields'];
}
else {
$words = explode( ' ', $keyDescr);
$Key_name = array_shift( $words);
$idx_fields = explode( ',', trim( implode( ' ', $words), ' (),'));
}
}
if ( !empty( $Key_name)) {
$query = 'SHOW INDEXES IN ' . $table . ' WHERE Key_name = ' . $this->fixQuote( $Key_name);
$this->db->setQuery( $query);
$rows = $this->db->loadObjectList();
if ( !empty( $rows)) {

if ( !$checkModified) {

return true;
}

for ($i=0; $i<count( $idx_fields); $i++) {
$idx_fields[$i] = trim( $this->fixQuote( $idx_fields[$i]), " '");
}
$found_idx_fields = array();
foreach( $rows as $row) {
$found_idx_fields[] = $row->Column_name;
}

$missing_index = array_diff( $idx_fields, $found_idx_fields);

$extra_index = array_diff( $found_idx_fields, $idx_fields);

if ( empty( $missing_index) && empty( $extra_index)) {

return true;
}
$error = 'field index modified';
return false;
}
}
$error = 'field index missing';
return false;
}


function getChangeItems_FixTable( $obj, $legacy=false)
{
if ( strpos( $obj->updateQuery, 'LEGACY TABLE') !== false) {
$legacy = true;
}
$create_table = str_replace( array( 'LEGACY TABLE', 'FIX TABLE'), 'CREATE TABLE', $obj->updateQuery);

$lines = explode( "\n", $create_table);
if ( !empty( $lines)) {
$first_line = array_shift( $lines); 
$parts = explode( ' ', trim( $first_line));
array_pop( $parts); 
$table = array_pop( $parts); 
}
if ( !empty( $lines)) { array_pop( $lines); } 

if ( !$this->isTableExists( $table)) {

$this->changeItems[] = MultisitesSchemaChangeitem::getInstance($this->db, $obj->file, $create_table);
}

else {


$fields = array();
$indexes = array();
if ( !empty( $lines)) {

foreach( $lines as $line) {
$line = trim( $line);

if ( empty( $line)) {}

else if ( preg_match( '#^PRIMARY\sKEY#i', $line)) { $indexes[] = $line; }
else if ( preg_match( '#^UNIQUE\sKEY#i', $line)) { $indexes[] = $line; }
else if ( preg_match( '#^UNIQUE#i', $line)) { $indexes[] = $line; }
else if ( preg_match( '#^FOREIGN\sKEY#i', $line)) { $indexes[] = $line; }
else if ( preg_match( '#^KEY#i', $line)) { $indexes[] = $line; }
else if ( preg_match( '#^INDEX#i', $line)) { $indexes[] = $line; }

else { $fields[] = $line; }
}
}

if ( !empty( $fields)) {

foreach( $fields as $field) {
$words = explode( ' ', $field);
$fieldName = array_shift( $words);
if ( ($reason=$this->isFieldExists( $table, $fieldName)) !== true ) {
$add_column = 'ALTER TABLE '.$table.' ADD COLUMN ' .rtrim( $field, ',').';';
$this->changeItems[] = MultisitesSchemaChangeitem::getInstance($this->db, $obj->file, $add_column, $reason);
}
}

foreach( $fields as $field) {
$words = explode( ' ', $field);
$fieldName = array_shift( $words);
$typeQuery = implode( ' ', $words);
if ( ($reason=$this->isFieldExists( $table, $fieldName, $typeQuery)) !== true) {
$modify_column = 'ALTER TABLE '.$table.' MODIFY COLUMN ' .rtrim( $field, ',').';';
$this->changeItems[] = MultisitesSchemaChangeitem::getInstance($this->db, $obj->file, $modify_column, $reason);
}
}


if ( $legacy) {} 

else {

$fieldnames = array();
foreach( $fields as $field) {
$parts = explode( ' ', trim( $field));
$fieldnames[] = trim( array_shift( $parts), '`'); 
}
if ( !empty( $fieldnames)) {

$this->db->setQuery( 'SHOW COLUMNS IN ' . $table);
$rows = $this->db->loadObjectList();
if ( !empty( $rows)) {
foreach( $rows as $row) {

if ( in_array( $row->Field, $fieldnames) || in_array( $row->field, $fieldnames)) {}

else {
$fieldname = !empty( $row->Field) ? $row->Field : '';
$fieldname .= !empty( $row->field) ? $row->field : '';
$drop_column = 'ALTER TABLE '.$table.' DROP COLUMN ' . $this->nameQuote( $fieldname). ';';
$this->changeItems[] = MultisitesSchemaChangeitem::getInstance($this->db, $obj->file, $drop_column, JText::_( 'Fix column missing'));
}
}
}
}
}
}
if ( !empty( $indexes)) {

foreach( $indexes as $index) {
if ( !$this->isIndexExists( $table, $index)) {
$add_index = 'ALTER TABLE '.$table.' ADD ' .rtrim( $index, ',').';';
$this->changeItems[] = MultisitesSchemaChangeitem::getInstance($this->db, $obj->file, $add_index, JText::_( 'Fix index missing'));
}
}

foreach( $indexes as $index) {
if ( !$this->isIndexExists( $table, $index, true)) {
$modify_index = 'ALTER TABLE '.$table.' MODIFY ' .rtrim( $index, ',').';';
$this->changeItems[] = MultisitesSchemaChangeitem::getInstance($this->db, $obj->file, $modify_index, JText::_( 'Fix modify index'));
}
}
}
}
}


function getChangeItems_FixInsert( $obj)
{
$replace_into = trim( str_replace( 'FIX_INSERT INTO', 'REPLACE INTO', $obj->updateQuery));
$chItem = $this->chItem;
$processed = false;

if ( preg_match( '#^(INSERT\sINTO|REPLACE\sINTO)(.*)#i', $replace_into, $matchAction)) {
$action = $matchAction[1];
$str = substr( $replace_into, strlen( $action));
$posValues = strpos( strtoupper( $str), 'VALUES');
if ( $posValues !== false) {
$fieldStr = trim( substr( $str, 0, $posValues));
$fields = $chItem->getFields( $fieldStr);

$valuesStr = trim( substr( $str, $posValues+6));

$values = explode( "\n", $valuesStr);
$parsedValues = array();
if ( !empty( $values)) {
$nFields = count($fields);
$processed = true;

for ( $v=0; $v<count( $values); $v++) {
$valueLine = trim( $values[$v], '(),;');
$fldValues = $chItem->getListOfValues( $valueLine);


if ( count($fldValues) != $nFields) {

$fldValues = explode( ',', trim( $values[$v], " \t\r\n\()"));

if ( count($fldValues) != $nFields) {

$parsedValues[] = $valueLine;


break;
}
}
$parsedValues[] = $fldValues;
}
}
}
}

if ( $processed && !empty( $parsedValues) && count( $parsedValues) > 1) {
foreach( $parsedValues as $values) {
if ( is_array( $values)) { $values = implode( ', ', $values); }

$replace_one = $action
. ' ' . $fieldStr
. ' VALUES '
. ' (' . $values . ');'
;
$this->changeItems[] = MultisitesSchemaChangeitem::getInstance($this->db, $obj->file, $replace_one);
}
}

else {
$this->changeItems[] = MultisitesSchemaChangeitem::getInstance($this->db, $obj->file, $replace_into);
}
}


function processQueries( $sqlQueries)
{
if ( empty( $sqlQueries)) {
return;
}
foreach( $sqlQueries as $obj) {
if ( preg_match( '#FIX\sTABLE\s#i', $obj->updateQuery)) { $this->getChangeItems_FixTable( $obj); }
else if ( preg_match( '#LEGACY\sTABLE\s#i', $obj->updateQuery)) { $this->getChangeItems_FixTable( $obj, true); }
else if ( preg_match( '#FIX_INSERT\sINTO\s#i', $obj->updateQuery)) { $this->getChangeItems_FixInsert( $obj); }
else if ( preg_match( '#CREATE\sTABLE\s#i', $obj->updateQuery)
&& ( !empty( $this->enteredvalues['legacysql'])
|| (!empty( $this->enteredvalues['legacymode']) && in_array( 'legacy', $this->enteredvalues['legacymode']))
)
) { $this->getChangeItems_FixTable( $obj, true); }

else if ( preg_match( '#DROP\sTABLE\s#i', $obj->updateQuery)
&& !empty( $this->enteredvalues['legacymode']) && in_array( 'legacy', $this->enteredvalues['legacymode'])) {} 
else {
$this->changeItems[] = MultisitesSchemaChangeitem::getInstance($this->db, $obj->file, $obj->updateQuery);
}
}
}


function getChangeItems( $sites=array())
{
$changeItems = array();
if ( !empty( $this->enteredvalues['legacymode'])) { $legacyMode = $this->enteredvalues['legacymode']; }
else { $legacyMode = array(); }
$foldersVariant = $this->getFolders( $sites);
if ( !empty( $foldersVariant)) {

if ( !empty( $foldersVariant['label'])
&& ( substr( $foldersVariant['label'], 0, 4) == 'xml:'
|| substr( $foldersVariant['label'], 0, 7) == 'master:'
|| substr( $foldersVariant['label'], 0, 9) == 'template:'
)
)
{
$sqlQueries = array();

foreach( $foldersVariant['values'] as $query) { $this->parseSQLBuffer( $sqlQueries, $query, $foldersVariant['label'], array(), $legacyMode); }

if ( !empty( $this->enteredvalues) && !empty( $this->enteredvalues['usersql'])) {
$this->parseSQLBuffer( $sqlQueries, $this->enteredvalues['usersql'], '** FREE SQL **', array(), $legacyMode);
}
$this->processQueries( $sqlQueries);
}

else {
foreach( $foldersVariant['values'] as $folder) {
$filesInFolders = $this->getFilesInFolder( $folder);
$sqlFiles = $filesInFolders['files'];
if ( !empty( $sqlFiles)) {
$sqlQueries = $this->getSQLQueries( $sqlFiles, $filesInFolders['fixFiles']);
$this->processQueries( $sqlQueries);
}
}
}
}

else if ( !empty( $this->enteredvalues) && !empty( $this->enteredvalues['usersql'])) {
$sqlQueries = array();
$this->parseSQLBuffer( $sqlQueries, $this->enteredvalues['usersql'], '** FREE SQL **', array(), $legacyMode);
$this->processQueries( $sqlQueries);
}


if ( !empty( $sqlFiles)) {
$fileinfo = new SplFileInfo( array_pop( $sqlFiles));
$this->fileSchemaVersion = $fileinfo->getBasename('.sql');
}
return $this->changeItems;
}


function getFileSchemaVersion()
{
if ( !empty( $this->fileSchemaVersion)) { return $this->fileSchemaVersion; }
return '';
}

public function getDBSchemaVersion() {
$db = $this->db;
$extension_id = !empty( $this->dmrow->extension_id) ? $this->dmrow->extension_id : 700; 
$query = $db->getQuery(true);
$query->select('version_id')->from($db->qn('#__schemas'))
->where('extension_id = '.$extension_id);
$db->setQuery($query);
$result = $db->loadResult();
return $result;
}

public function fixDBSchemaVersion()
{

$fileSchemaVersion = $this->getFileSchemaVersion();
$db = $this->db;
$result = false;

$dbSchemaVersion = $this->getDBSchemaVersion();
if ( empty( $dbSchemaVersion)) {} 
else if ($dbSchemaVersion == $fileSchemaVersion)
{
$result = $fileSchemaVersion;
}
else
{
$extension_id = !empty( $this->dmrow->extension_id) ? $this->dmrow->extension_id : 700; 

$query = $db->getQuery(true);
$query->delete($db->qn('#__schemas'));
$query->where($db->qn('extension_id') . ' = '.$extension_id);
$db->setQuery($query);
$db->query();

$query = $db->getQuery(true);
$query->insert($db->qn('#__schemas'));
$query->set($db->qn('extension_id') . '= '.$extension_id);
$query->set($db->qn('version_id') . '= ' . $db->q($fileSchemaVersion));
$db->setQuery($query);
if ($db->query()) {
$result = $fileSchemaVersion;
}
}
return $result;
}


public function fix()
{
foreach ($this->changeItems as $item)
{
$item->fix();
}
$this->fixDBSchemaVersion();
}


public function fixUnchecked()
{
$this->check();
foreach ($this->changeItems as $item)
{
$item->fixUnchecked();
}
$this->fixDBSchemaVersion();
}
} 
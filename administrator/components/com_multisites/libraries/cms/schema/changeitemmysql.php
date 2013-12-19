<?php
// file: changeitemmysql.php.
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
if ( file_exists( JPATH_LIBRARIES . '/cms/schema/changeitemmysql.php')) {
require_once( JPATH_LIBRARIES . '/cms/schema/changeitemmysql.php');
JLoader::register('JSchemaChangeitem', JPATH_LIBRARIES . '/cms/schema/changeitemmysql.php');
}

else if ( file_exists( JPATH_LIBRARIES . '/cms/schema/changeitem/mysql.php')) {
require_once( JPATH_LIBRARIES . '/cms/schema/changeitem/mysql.php');
JLoader::register('JSchemaChangeitem', JPATH_LIBRARIES . '/cms/schema/changeitem/mysql.php');
}



class MultisitesSchemaChangeitemMySQL extends JSchemaChangeitemmysql
{
protected function buildCheckQuery()
{
$this->updateQuery = trim( $this->updateQuery);

$this->updateQuery = str_replace( array( "\r\n", "\n", "\r"), ' ', $this->updateQuery);

if ( preg_match( '#(ALTER TABLE(.*))(CHANGE\sCOLUMN|CHANGE|MODIFY\sCOLUMN|MODIFY)(.*)#i', $this->updateQuery)) {}
else {
parent::buildCheckQuery();

if ( $this->checkStatus == -1) {}
else {
return;
}
}


$result = null;

$find = array('#((\s*)\(\s*([^)\s]+)\s*)(\))#', '#(\s)(\s*)#');
$replace = array('($3)', '$1');
$updateQuery = preg_replace($find, $replace, $this->updateQuery);
$wordArray = explode(' ', $updateQuery);


if (count($wordArray) < 5) {
return; 
}

$command = strtoupper($wordArray[0] . ' ' . $wordArray[1]);

if ($command === 'ALTER TABLE') {
$alterCommand = strtoupper($wordArray[3] . ' ' . $wordArray[4]);
if ($alterCommand == 'RENAME TO') {
$table = $wordArray[5];
$result = 'SHOW TABLES LIKE ' . $this->fixQuote($table);

$this->queryType = 'RENAME_TABLE';
$this->msgElements = array($this->fixQuote($table));
}
else if ($alterCommand == 'DROP COLUMN') {
$result = 'SHOW COLUMNS IN ' . $wordArray[2] .
' WHERE field = ' . $this->fixQuote($wordArray[5]);
$this->checkQueryExpected = 0;
$this->queryType = 'DROP_COLUMN';
$this->msgElements = array($this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[5]));
}

elseif ($alterCommand == 'ADD KEY') {
if ($pos = strpos($wordArray[5], '(')) {
$index = $this->fixQuote(substr($wordArray[5], 0, $pos));
} else {
$index = $this->fixQuote($wordArray[5]);
}
$result = 'SHOW INDEXES IN ' . $wordArray[2] . ' WHERE Key_name = ' . $index;
$this->queryType = 'ADD_INDEX';
$this->msgElements = array($this->fixQuote($wordArray[2]), $index);
}

elseif ($alterCommand == 'CHANGE COLUMN' || $wordArray[3]=='CHANGE' || $alterCommand == 'MODIFY COLUMN' || $wordArray[3]=='MODIFY') {
$table = $wordArray[2];
$subQuery=implode( ' ', array_slice( $wordArray, 3));
if ( preg_match( '#(CHANGE\sCOLUMN|CHANGE|MODIFY\sCOLUMN|MODIFY)(\s(.*))#i', $subQuery, $match)) {
$fieldDescr = trim( $match[2]);
if ( preg_match( '#(`[a-z0-9\_]+`\s(`[a-z0-9\_]+`)*)(.*)#i', $fieldDescr, $matchField)) {
$subWordField = explode(' ', trim( $matchField[1]));
if ( count( $subWordField) >= 2) {
$field = $subWordField[1];
}
else {
$field = $subWordField[0];
}
$typeQuery = trim( $matchField[3]);
}
else {
$subWordArray = explode(' ', trim( $match[2]));
if ( preg_match( '#^(VARCHAR|INTEGER|UNSIGNED|DATETIME|TINYINT|TEXT|MEDIUMTEXT|INT)#i', $subWordArray[1])) {
$field = array_shift( $subWordArray);
$typeQuery = trim( implode( ' ', $subWordArray));
}
else {
$field = $subWordArray[1];
$typeQuery = trim( implode( ' ', array_slice( $subWordArray, 2)));
}
}
if ( preg_match( '#(.*)\sNOT\sNULL#i', $typeQuery, $matchType)) { $typeQuery=$matchType[1]; }
else if ( preg_match( '#(.*)\s(NULL|DEFAULT)#i', $typeQuery, $matchType)) { $typeQuery=$matchType[1]; }
$type = $this->fixQuote($this->normalizeType( $typeQuery));
if ( strpos( $type, '%') !== false) { $oper = 'LIKE '; }
else { $oper = '= '; }
$result = 'SHOW COLUMNS IN ' . $table . ' WHERE field = '
. $this->fixQuote( $field) . ' AND type ' . $oper.$type;
$this->queryType = 'CHANGE_COLUMN_TYPE';
$this->msgElements = array($this->fixQuote($wordArray[2]), $this->fixQuote($wordArray[5]), $type);
}
}
$alterCommand = strtoupper($wordArray[3] . ' ' . $wordArray[4]. (!empty( $wordArray[5]) ? ' '.$wordArray[5] : ''));
if ($alterCommand == 'ADD PRIMARY KEY') {
$result = 'SHOW INDEXES IN ' . $wordArray[2] . ' WHERE Key_name = "PRIMARY"';
$this->queryType = 'PRIMARY_KEY';
$this->msgElements = array($this->fixQuote($wordArray[2]));
}
}

else if ( $command === 'INSERT INTO' || $command === 'REPLACE INTO') {

$reg_exp = '/^(INSERT INTO|REPLACE INTO)\s(.*)VALUES(.*);$/i';
if ( preg_match( $reg_exp, $this->updateQuery, $match)) {
if ( count( $match) == 4) {
$fields = $this->getFields( $match[2]);

$valuesStr = $match[3];
$values = preg_split('#\)\s*,\s*\(#', $valuesStr);
$parsedValues = array();
if ( !empty( $values)) {
$where = '';
$or = '';

$nFields = count($fields);
$processed = true;
for ( $v=0; $v<count( $values); $v++) {
$fldValues = $this->getListOfValues( $values[$v]);


if ( count($fldValues) != $nFields) {

$fldValues = explode( ',', trim( $values[$v], " \t\r\n\()"));

if ( count($fldValues) != $nFields) {

$processed = false;
break;
}
}
$parsedValues[] = $fldValues;
$where .= $or . '(';
$or = ' OR ';
$and = '';
for ( $i=0; $i<$nFields; $i++) {
$where .= $and . $fields[$i] . ' = ' .$fldValues[$i];
$and = ' AND ';
}
$where .= ')';
}
if ( $processed) {
$result = 'SELECT count(*) FROM ' . $wordArray[2] . ' WHERE '.$where;
$this->checkQueryExpected = count( $values); 
$this->queryType = 'INSERT_INTO';
$this->msgElements = array($this->fixQuote( $wordArray[2]), $fields, $parsedValues);

if ( count( $values) > 1) {
$this->queryType = 'REPLACE_INTO';
$this->updateQuery = preg_replace( '/^INSERT INTO/i', 'REPLACE INTO', $this->updateQuery);
}
}
}
}
}

else if ( preg_match( '/^(INSERT INTO|REPLACE INTO)\s(.*)SET\s(.*);$/i', $this->updateQuery, $match)) {
if ( count( $match) == 4) {
$valuesStr = $match[3];
$values = $this->parseSETValues( $valuesStr);
if ( !empty( $values)) {
$where = '';
$and = '';
foreach( $values as $value) {
$where .= $and . $value[0] . ' = ' .$value[1];
$and = ' AND ';
}
$result = 'SELECT count(*) FROM ' . $wordArray[2] . ' WHERE '.$where;
$this->checkQueryExpected = count( $values); 
$this->queryType = 'INSERT_INTO_SET';
$this->msgElements = array($this->fixQuote( $wordArray[2]), $values);
}
}
}

else if ( preg_match( '/^(INSERT INTO|REPLACE INTO)\s(.*)SELECT(.*);$/i', $this->updateQuery, $match)) {
$fields = $this->getFields( $match[2]);

$query = 'SELECT'.$match[3];
$this->db->setQuery( $query);
$rows = $this->db->loadRowList();
if ( !empty( $rows)) {
$where = '';
$or = '';

$nFields = count($fields);
$processed = true;
foreach ( $rows as $row) {
$fldValues = $row;


if ( count($fldValues) != $nFields) {

$processed = false;
break;
}
$where .= $or . '(';
$or = "\n OR ";
$and = '';
for ( $i=0; $i<$nFields; $i++) {
$where .= $and . $fields[$i] . ' = ';
if ( is_numeric( $fldValues[$i])) { $where .= $fldValues[$i]; }
else { $where .= $this->db->quote( $fldValues[$i]); }
$and = ' AND ';
}
$where .= ')';
}
if ( $processed) {
$result = 'SELECT count(*) FROM ' . $wordArray[2] . ' WHERE '.$where;
$this->checkQueryExpected = count( $rows); 
$this->queryType = 'INSERT_INTO';
$this->msgElements = array($this->fixQuote( $wordArray[2]), $fields, $rows);

if ( count( $rows) > 1) {
$this->queryType = 'REPLACE_INTO';
$this->updateQuery = preg_replace( '/^INSERT INTO/i', 'REPLACE INTO', $this->updateQuery);
}
}
}
}
}

else if ($command === 'DELETE FROM') {
$table = $wordArray[2];
array_shift( $wordArray); 
$result = 'SELECT * ' . implode( ' ', $wordArray);
$this->checkQueryExpected = 0;
$this->queryType = 'DELETE_FROM';
$this->msgElements = array($this->fixQuote($table));
}

else if ( preg_match( '/^UPDATE(.*)/i', $this->updateQuery, $match)) {
$subWords = explode( ' ', trim( $match[1]));
$table = array_shift( $subWords);
$action = array_shift( $subWords);
while ( empty( $action)) {
$action = array_shift( $subWords);
}
if ( strtoupper( $action) == 'SET') {
$subStr = trim( implode( ' ', $subWords));
$posWhere = strpos( $subStr, ' WHERE ');
if ( $posWhere === false) {}
else {
$setValue = trim( substr( $subStr, 0, $posWhere));
$where = trim( substr( $subStr, $posWhere), ';');
$result = 'SELECT * FROM ' . $this->fixQuoteName($table)
. ' ' . $where
. ' AND (' .$setValue.')'
. ';'
;
$this->checkQueryExpected = 1;
$this->queryType = 'UPDATE_SET';
$this->msgElements = array($this->fixQuote($table));
}
}
}

else if ($command === 'CREATE VIEW') {
$table = $wordArray[2];
$result = 'SHOW CREATE VIEW ' . $this->fixQuoteName($table);
$this->queryType = 'CREATE_VIEW';
$this->msgElements = array($this->fixQuote($table));
}

if ($this->checkQuery = $result) {
$this->checkStatus = 0; 
}
else {
$this->checkStatus = -1; 
}
}


function normalizeType($type)
{
$type = trim( preg_replace( '#NOT\sNULL$#i', '', $type), ' ,');
$ucType = strtoupper( $type);
if ( $ucType == 'INTEGER') { return 'int(%)'; }
if ( preg_match( '#^INT(.*)AUTO_INCREMENT$#i', $type, $match)) { return 'int(%) unsigned'; }
if ( preg_match( '#^INTEGER\s+UNSIGNED#i', $type, $match)) { return 'int(%) unsigned'; }
if ( preg_match( '#^INTEGER\s?\(([0-9\s]+)\)\sUNSIGNED#i', $type, $match)) { return 'int('.intval(trim($match[1])).') unsigned'; }
if ( preg_match( '#^INT\s?\(([0-9\s]+)\)\sUNSIGNED#i', $type, $match)) { return 'int('.intval(trim($match[1])).') unsigned'; }
if ( preg_match( '#^INT\s?\(([0-9\s]+)\)#i', $type, $match)) { return 'int('.intval(trim($match[1])).')'; }
if ( preg_match( '#^VARCHAR\s?\(([0-9\s]+)\)#i', $type, $match)) { return 'varchar('.intval(trim($match[1])).')'; }
if ( preg_match( '#^TINYINT\s+UNSIGNED#i', $type, $match)) { return 'tinyint(%) unsigned'; }
if ( $ucType == 'TINYINT') { return 'tinyint(%)'; }
return strtolower( $type);
}


function fixQuote($string)
{
$string = str_replace('`', '', $string);
$string = str_replace(';', '', $string);
$string = str_replace('#__', $this->db->getPrefix(), $string);
return $this->db->quote($string);
}

function nameQuote( $table)
{

if ( method_exists( $this->db, 'nameQuote')) { return $this->db->nameQuote( $table); }

return $this->db->quoteName( $table);
}


function fixQuoteName($string)
{
$string = str_replace('`', '', $string);
$string = str_replace(';', '', $string);
$string = str_replace('#__', $this->db->getPrefix(), $string);
return $this->nameQuote($string);
}


function getFields( $fieldString)
{
$fields = array();
$posFields = strpos( $fieldString, '(');
if ( $posFields !== false) {
$fieldsStr = trim( substr( $fieldString, $posFields), "() \t\r\n");
$fields = explode( ',', $fieldsStr);
}

else {
$query = 'SHOW COLUMNS IN ' . $fieldString;
$this->db->setQuery( $query);
$rows = $this->db->loadObjectList();
if ( !empty( $rows) && is_array( $rows)) {
foreach ( $rows as $row) { $fields[] = $this->nameQuote( $row->Field); }
}
}
return $fields;
}


function parseSETValues( $valuesStr)
{
$values = array();
$tokenizeSQL = $this->tokenizeSQL( trim( $valuesStr, " \t\r\n\()"));
if ( !empty( $tokenizeSQL) && is_array( $tokenizeSQL)) {
$state = 'name';
$name = '';
foreach ( $tokenizeSQL as $indice => $token) {
if ( $state == 'name') {
if ( trim( $token) == '=') {}
else {
$name = $token;
$state = 'eq';
}
}
else if ( $state == 'eq') {
if ( trim( $token) == '=') {
$state = 'val';
}
}
else if ( $state == 'val') {
$value = $token;
$state = 'commas';
}
else if ( $state == 'commas') {
if ( trim( $token) == ',') {
$values[] = array( $name, $value);
$state = 'name';
$name = '';
$value = '';
}
else {
$value .= $token;
}
}
}
}

if ( !empty( $name)) {
if ( !isset( $value)) {
$test = 'fake';
}
$values[] = array( $name, $value);
}
return $values;
}

function getListOfValues( $values)
{
$results = $this->tokenizeSQL( trim( $values, " \t\r\n\()"));
if ( !empty( $results)) {

for ( $i=count($results)-1; $i>=0; $i--) {
if ( $results[$i] == ',') {
unset( $results[$i]);
}
}
}

$results = array_values( $results);
return $results;
}


function tokenizeSQL( $SQL )
{
$functions = array ( 'concat', 'if' );
$token = '\\(|\\)|[\']|"|\140|[*]|,|<|>|<>|=|[+]';
$terminal = $token.'|;| |\\n';
$result = array();
$string = $SQL;
$string = ltrim($string);
$string = rtrim($string,';').';'; 
$string = preg_replace( "/[\n\r]/s", ' ', $string );
while(
preg_match( "/^($token)($terminal)/s", $string, $matches ) ||
preg_match( "/^({$token})./s", $string, $matches ) ||
preg_match( "/^([a-zA-Z0-9_.]+?)($terminal)/s", $string, $matches)
)
{
$t = $matches[1];
if ($t=='\'')
{

$t = $this->tokSingleQuoteString( $string );
array_push($result, $t);
}
else if ($t=="\140")
{

$t = $this->tokBackQuoteString( $string );
array_push($result, $t);
}
else if ($t=='"')
{

$t = $this->tokDoubleQuoteString( $string );
array_push($result, $t);
}
else
{
array_push($result, $t);
}
$string = substr( $string, strlen($t) );
$string = ltrim($string);
}
return $result;
}
function tokSingleQuoteString( $string )
{


preg_match('/^(\'.*?\').*$/s', $string, $matches );
return $matches[1];
}
function tokBackQuoteString( $string )
{


preg_match('/^([\140].*?[\140]).*$/s', $string, $matches );
if ( !empty( $matches[1])) {
return $matches[1];
}
return "\140";
}
function tokDoubleQuoteString( $string )
{


preg_match('/^(".*?").*$/s', $string, $matches );
if ( !empty( $matches[1])) {
return $matches[1];
}
return '"';
}


public function fix()
{

$this->check();
if ($this->checkStatus === -2) {

$this->db->setQuery($this->updateQuery);
if ($this->db->execute()) {
$this->sqlErrorMsg = $this->db->getErrorMsg();
if ($this->check()) {
$this->checkStatus = 1;
$this->rerunStatus = 1;
$this->sqlErrorMsg = '';
} else {
$this->rerunStatus = -2;
}
} else {
$this->sqlErrorMsg = $this->db->getErrorMsg();
$this->rerunStatus = -2;
}
}
}


public function fixUnchecked()
{
if ( $this->checkStatus === -2 
|| $this->checkStatus === -1) 
{

$this->db->setQuery($this->updateQuery);
if ($this->db->execute()) {
$this->sqlErrorMsg = $this->db->getErrorMsg();
if ($this->check()) {
$this->checkStatus = 1;
$this->rerunStatus = 1;
$this->sqlErrorMsg = '';
} else {
$this->rerunStatus = -2;
}
} else {
$this->sqlErrorMsg = $this->db->getErrorMsg();
$this->rerunStatus = -2;
}
}
}
} 
<?php
// file: j2windb.php.
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






if ( !class_exists( 'J2WinDatabase')) {
class J2WinDatabase{

public function __construct( &$db)
{
$this->db = $db;
}

public function addQuoted($quoted) { if ( method_exists( $this->db, 'addQuoted')) { $this->db->addQuoted($quoted); } }

public function connect() { if ( method_exists( $this->db, 'connect')) { $this->db->connect(); } }

public function connected() { return $this->db->connected(); }

public function debug($level) { return $this->db->debug($level); }

public function dropTable($table, $ifExists = true) { return $this->db->dropTable($table, $ifExists); }

public function escape($text, $extra = false) { return $this->db->escape( $text, $extra); }

public function execute() { try { return $this->db->execute(); }
catch (RuntimeException $e) { return false; }
}

public function explain() { return $this->db->explain(); }

static function getAdapter( $adapter, $option) { jimport('joomla.database.database');
if ( method_exists( 'JDatabase', 'getAdapter')) {
return JDatabase::getAdapter( $adapter, $option);
}
else {
return null;
}
}

public function getAffectedRows() { return $this->db->getAffectedRows(); }

public function getCollation() { return $this->db->getCollation(); }

public function getConnection() { return $this->db->getConnection(); }

static function getConnectors() { jimport('joomla.database.database');
if ( method_exists( 'JDatabase', 'getConnectors')) {
return JDatabase::getConnectors();
}
else {
return null;
}
}

public function getCount() { return $this->db->getCount(); }

public function getDateFormat() { return $this->db->getDateFormat(); }

public static function getInstance($options = array()) { try { $instance = JDatabase::getInstance($options );
$db = new J2WinDatabase( $instance);
return $db;
}
catch (RuntimeException $e) { return null; }
}

public function getErrorMsg($escaped = false) { return $this->db->getErrorMsg( $escaped); }

public function getErrorNum() { return $this->db->getErrorNum(); }

public function getEscaped($text, $extra = false) { return $this->db->getEscaped($text, $extra); }

public function getLog() { return $this->db->getLog(); }

public function getMinimum() { return $this->db->getMinimum(); }

public function getNullDate() { return $this->db->getNullDate(); }

public function getNumRows($cursor = null) { return $this->db->getNumRows($cursor); }

public function getPrefix() { return $this->db->getPrefix(); }

public function getQuery($new = false) { return $this->db->getQuery( $new); }

public function getTableColumns($table, $typeOnly = true) { return $this->db->getTableColumns($table, $typeOnly); }

public function getTableFields($tables, $typeOnly = true) { return $this->db->getTableFields($tables, $typeOnly); }

public function getTableKeys($tables) { return $this->db->getTableKeys($tables); }

public function getTableList() { return $this->db->getTableList(); }

public function getTicker() { return $this->db->getTicker(); }

public function getUTFSupport() { return $this->db->getUTFSupport(); }

public function getVersion() { return $this->db->getVersion(); }

public function hasUTF() { return $this->db->hasUTF(); }

public function insertid() { return $this->db->insertid(); }

public function insertObject($table, &$object, $key = null) { try { return $this->db->insertObject( $table, $object, $key ); }
catch (RuntimeException $e) { return false; }
}

public function isMinimumVersion() { return $this->db->insertid(); }

public function isQuoted($field) { try {
if ( method_exists( $this->db, 'isQuoted')) {
return $this->db->isQuoted( $field);
}

return true;
} catch (RuntimeException $e) { return true; }
}

public function loadAssoc() { try { return $this->db->loadAssoc(); }
catch (RuntimeException $e) { return false; }
}

public function loadAssocList($key = null, $column = null) { try { return $this->db->loadAssocList($key, $column); }
catch (RuntimeException $e) { return false; }
}

public function loadColumn( $offset = 0) { return $this->db->loadColumn( $offset); }

public function loadNextObject($class = 'stdClass') { return $this->db->loadNextObject($class); }

public function loadNextRow() { return $this->db->loadNextRow(); }

public function loadObject( $class = 'stdClass') { try { return $this->db->loadObject( $class); }
catch (RuntimeException $e) { return false; }
}

public function loadObjectList($key = '', $class = 'stdClass') { try { return $this->db->loadObjectList($key, $class); }
catch (RuntimeException $e) { return array(); }
}

public function loadResult() { try {
if ( method_exists( $this->db, 'loadResult')) { return $this->db->loadResult(); }
return $this->db->loadResult(); }
catch (RuntimeException $e) { return false; }
}

public function loadResultArray($offset = 0) { try {
if ( method_exists( $this->db, 'loadResultArray')) { return $this->db->loadResultArray( $offset); }
if ( method_exists( $this->db, 'loadColumn')) { return $this->db->loadColumn( $offset); }
return array();
}
catch (RuntimeException $e) { return array(); }
}

public function loadRow() { return $this->db->loadRow(); }

public function loadRowList($key = null) { return $this->db->loadRowList($key); }

public function lockTable($tableName) { return $this->db->lockTable($tableName); }

public function nameQuote( $name) { if ( method_exists( $this->db, 'nameQuote')) { return $this->db->nameQuote( $name); }
return $this->db->quoteName( $name);
}

public function query() { try { return $this->db->query(); }
catch (RuntimeException $e) { return false; }
}

public function queryBatch($abortOnError = true, $transactionSafe = false) { try { return $this->db->queryBatch($abortOnError, $transactionSafe); }
catch (RuntimeException $e) { return false; }
}

public function quote($text, $escape = true) { return $this->db->quote($text, $escape); }
public function q($text, $escape = true) { return $this->db->quote($text, $escape); }

public function quoteName( $name, $as = null) { return $this->db->quoteName( $name, $as); }
public function qn( $name, $as = null) { return $this->db->quoteName( $name, $as); }
public function nq( $name, $as = null) { return $this->db->quoteName( $name, $as); }

public function replacePrefix($sql, $prefix = '#__') { return $this->db->replacePrefix($sql, $prefix); }

public function renameTable($oldTable, $newTable, $backup = null, $prefix = null) { return $this->db->renameTable($oldTable, $newTable, $backup, $prefix ); }

public function select($database) { try { return $this->db->select($database); }
catch (RuntimeException $e) { return false; }
}

public function setDebug($level) { return $this->db->setDebug($level); }

public function setQuery( $query, $offset = 0, $limit = 0) { return $this->db->setQuery( $query, $offset = 0, $limit); }

public function setUTF() { return $this->db->setUTF(); }

public static function splitSql( $sql) { jimport('joomla.database.database');
if ( method_exists( 'JDatabase', 'splitSql')) {
return JDatabase::splitSql( $sql);
}
else {
return null;
}
}

public function stderr($showSQL = false) { return $this->db->stderr($showSQL); }

public function toString() { if ( method_exists( $this->db, 'toString')) { return $this->db->toString(); }
if ( method_exists( $this->db, '__toString')){ return $this->db->__toString(); }
return get_class($this->db);
}

public function transactionCommit() { return $this->db->transactionCommit(); }

public function transactionRollback() { return $this->db->transactionRollback(); }

public function transactionStart() { return $this->db->transactionStart(); }

public function truncateTable($table) { return $this->db->truncateTable($table); }

public function updateObject($table, &$object, $key, $nulls = false) { return $this->db->updateObject($table, $object, $key, $nulls); }

public function unlockTables() { return $this->db->unlockTables(); }
} 
} 
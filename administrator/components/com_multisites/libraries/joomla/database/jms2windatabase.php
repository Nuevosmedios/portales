<?php
// file: jms2windatabase.php.
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


defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access or JMS2Win patches are not installed');
jimport('joomla.database.database');



class Jms2WinDatabase extends JDatabase
{


function & getInstanceRef($options = array())
{

if ( version_compare( JVERSION, '1.6') >= 0) {

$options['driver'] = (isset($options['driver'])) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $options['driver']) : 'mysql';
$options['database'] = (isset($options['database'])) ? $options['database'] : null;
$options['select'] = (isset($options['select'])) ? $options['select'] : true;

$signature = md5(serialize($options));

if ( !empty( $options['refresh'])) {

if ( !empty(self::$instances[$signature])) {

self::$instances[$signature] = null;
}
}

$db = Jms2WinDatabase::getInstance( $options);

return self::$instances[$signature];
}

return Jms2WinDatabase::getInstance( $options);
}
function writeTrace()
{
require_once( JPATH_MUTLISITES_COMPONENT .DS. 'classes' .DS. 'debug.php');
$prevStandalone = Debug2Win::isStandalone();
$prevFilename = Debug2Win::getFileName();;
$prevDebug = Debug2Win::isDebug();
Debug2Win::enableStandalone(); 
Debug2Win::setFileName( 'database.log.php');
Debug2Win::enableDebug(); 

$arr = debug_backtrace();
Debug2Win::debug( var_export( $arr, true));
Debug2Win::setFileName( $prevFilename);
if ( !$prevDebug) {
Debug2Win::disableDebug( $prevDebug); 
}
if ( !$prevStandalone) {
Debug2Win::disableStandalone();
}
}
public function connected() {}
public function dropTable($table, $ifExists = true) {}
public function escape($text, $extra = false) {}
protected function fetchArray($cursor = null) {}
protected function fetchAssoc($cursor = null) {}
protected function fetchObject($cursor = null, $class = 'stdClass') {}
protected function freeResult($cursor = null) {}
public function getAffectedRows() {}
public function getCollation() {}
public function getNumRows($cursor = null) {}
public function getQuery($new = false) {}
public function getTableColumns($table, $typeOnly = true) {}
public function getTableCreate($tables) {}
public function getTableKeys($tables) {}
public function getTableList() {}
public function getVersion() {}
public function hasUTF() {}
public function insertid() {}
public function lockTable($tableName) {}
public function query() {}
public function renameTable($oldTable, $newTable, $backup = null, $prefix = null) {}
public function select($database) {}
public function setUTF() {}
public function transactionCommit() {}
public function transactionRollback() {}
public function transactionStart() {}
public function unlockTables() {}
public function explain() {}
public function queryBatch($abortOnError = true, $transactionSafe = false) {}
public static function test() {}
} 
?>
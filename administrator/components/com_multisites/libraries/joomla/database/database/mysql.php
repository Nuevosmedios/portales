<?php
// file: mysql.php.
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
if ( JFile::exists( JPATH_LIBRARIES .DS. 'joomla' .DS. 'database' .DS. 'database' .DS. 'mysql.php')) {
require_once( JPATH_LIBRARIES .DS. 'joomla' .DS. 'database' .DS. 'database' .DS. 'mysql.php');
eval( 'class JDBMySQL extends JDatabaseMySQL {}');
}
else if ( JFile::exists( JPATH_LIBRARIES .DS. 'joomla' .DS. 'database' .DS. 'driver' .DS. 'mysql.php')) {
require_once( JPATH_LIBRARIES .DS. 'joomla' .DS. 'database' .DS. 'driver' .DS. 'mysql.php');
eval( 'class JDBMySQL extends JDatabaseDriverMysql {}');
}



class MultisitesDatabaseMySQL extends JDBMySQL
{


public function execute()
{
try {
return parent::execute();
}
catch( RuntimeException $e) {
$this->errorNum = (int) mysqli_errno($this->connection);
$this->errorMsg = $e->getMessage();
return false;
}
}

public function loadResultArray($offset = 0) {
try {
if ( method_exists( 'JDBMySQL', 'loadResultArray')) { return parent::loadResultArray( $offset); }
if ( method_exists( 'JDBMySQL', 'loadColumn')) { return parent::loadColumn( $offset); }
return array();
}
catch (RuntimeException $e) { return array(); }
}

public function nameQuote( $name) {
if ( method_exists( 'JDBMySQL', 'nameQuote')) { return parent::nameQuote( $name); }

return parent::quoteName( $name);
}


function setPrefix( $table_prefix)
{

if ( isset( $this->connection)) {
$result = !empty( $this->tablePrefix) ? $this->tablePrefix : null;
$this->tablePrefix = $table_prefix;
}

else {
$result = $this->_table_prefix;
$this->_table_prefix = $table_prefix;
}
return $result;
}


function setErrorInfo( $errorNum, $errorMsg)
{

if ( isset( $this->errorNum)) {
$this->errorNum = $errorNum;
$this->errorMsg = $errorMsg;
}
else {
$this->_errorNum = $errorNum;
$this->_errorMsg = $errorMsg;
}
}


function setNewConnection( $host, $user, $password, $select, $database)
{

if ( isset( $this->connection)) { $con = 'connection'; }

else if ( isset( $this->_connection)) { $con = '_connection'; }

else { $con = '_resource'; }
if (!($new_con = @mysql_connect( $host, $user, $password, true ))) {
$this->setErrorInfo( 2, "Could not connect to MySQL host=[$host] with user=[$user]");
return false;
}

if ( $this->connected()) {

$this->__destruct();
}
$this->$con = $new_con;

if ( $select ) {
$this->setErrorInfo( 0, '');
try { return $this->select($database); }
catch (Exception $e) {
$this->setErrorInfo( 4, 'Unable to select the DB [$database].');
return false;
}
}
return true;
}
}

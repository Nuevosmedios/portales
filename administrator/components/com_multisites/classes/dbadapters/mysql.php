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



class MultisitesDatabaseLightMySQL extends MultisitesDatabase
{


function __construct( $options)
{
$this->errorNum = 0;
$this->errorMsg = '';
$this->normalizeOptions( $options);
$this->options = $options;

if (!function_exists( 'mysql_connect')) {
$this->errorNum = 1;
$this->errorMsg = 'MySQL is not installed';
return;
}
$this->connection = @mysql_connect( $options['host'], $options['user'], $options['password'], true);
if (!$this->connection) {
$this->errorNum = 2;
$this->errorMsg = 'Unable to connect on MySQL';
return;
}

parent::__construct($options);

mysql_query("SET @@SESSION.sql_mode = '';", $this->connection);
}

function __destruct()
{
if (is_resource($this->connection))
{
mysql_close($this->connection);
}
}


function isConnected()
{
if (is_resource($this->connection))
{
return mysql_ping($this->connection);
}
return false;
}

function select_db( $database=null)
{

if ( empty( $database)) {
$database = $this->options['database'];
}
if ( empty( $database)) {
return false;
}
if (!mysql_select_db( $database, $this->connection))
{
$this->errorNum = 3;
$this->errorMsg = JText::_('JLIB_DATABASE_ERROR_DATABASE_CONNECT');
return false;
}
return true;
}


function execQuery( $query)
{

$this->errorNum = 0;
$this->errorMsg = '';
if ( !is_resource($this->connection)) {
$this->errorNum = -1;
$this->errorMsg = 'DB is not connected';
return false;
}

$sql = $this->replacePrefix( $query);

$this->cursor = mysql_query( $sql, $this->connection);

if ( !$this->cursor)
{
$this->errorNum = mysql_errno($this->connection);
$this->errorMsg = mysql_error($this->connection) . ' SQL=' . $sql;;
return false;
}
return $this->cursor;
}
}

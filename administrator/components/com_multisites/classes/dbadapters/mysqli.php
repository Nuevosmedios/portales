<?php
// file: mysqli.php.
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



class MultisitesDatabaseLightMySQLi extends MultisitesDatabaseLight
{


function __construct( $options)
{
$this->errorNum = 0;
$this->errorMsg = '';
$this->normalizeOptions( $options);
$options['port'] = null;
$options['socket'] = null;

$tmp = substr(strstr($options['host'], ':'), 1);
if (!empty($tmp))
{

if (is_numeric($tmp))
{
$options['port'] = $tmp;
}
else
{
$options['socket'] = $tmp;
}

$options['host'] = substr($options['host'], 0, strlen($options['host']) - (strlen($tmp) + 1));

if ($options['host'] == '')
{
$options['host'] = 'localhost';
}
}
$this->options = $options;

if (!function_exists( 'mysqli_connect')) {
$this->errorNum = 1;
$this->errorMsg = 'MySQLi is not installed';
return;
}
$this->connection = @mysqli_connect( $options['host'],
$options['user'],
$options['password'],
null,
$options['port'],
$options['socket']
);
if (!$this->connection) {
$this->errorNum = 2;
$this->errorMsg = 'Unable to connect on MySQLi';
return;
}

mysqli_query( $this->connection, "SET @@SESSION.sql_mode = '';");
}

function __destruct()
{
if (is_callable($this->connection, 'close')) {
mysqli_close($this->connection);
}
}


function isConnected()
{
if (is_object($this->connection)) {
return mysqli_ping($this->connection);
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
if (!mysqli_select_db( $this->connection, $database))
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
if ( !is_object( $this->connection)) {
$this->errorNum = -1;
$this->errorMsg = 'DB is not connected';
return false;
}

$sql = $this->replacePrefix( $query);

$this->cursor = mysqli_query( $this->connection, $sql);

if ( !$this->cursor)
{
$this->errorNum = (int) mysqli_errno($this->connection);
$this->errorMsg = (string) mysqli_error($this->connection) . ' SQL=' . $sql;
return false;
}
return $this->cursor;
}
}

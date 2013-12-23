<?php
/**
 * @file       mysqli.php
 * @brief      MySQLi driver that allow replace the "protected" table prefix
 *
 * @version    1.2.85
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  JMS Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2010-2012 Edwin2Win sprlu - all right reserved.
 * @license    This program is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License
 *             as published by the Free Software Foundation; either version 2
 *             of the License, or (at your option) any later version.
 *             This program is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with this program; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *             A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
 * @par History:
 * - V1.2.34 17-JUL-2010: Initial version
 * - V1.2.35 27-JUL-2010: Set the "protected" error information with new values.
 * - V1.2.48 19-FEB-2011: Set a New DB Connection to access the "protected" _connection (j16) or _resource(15) field.
 * - V1.2.60 30-JUL-2011: Set a New DB Connection to access the "protected" connection (j17).
 *                        Also update the fieldname table_prefix, errorNum and errorMsg
 * - V1.2.64 07-SEP-2011: Fix Joomla 1.7 setPrefix() to ensure that the table prefix property exists.
 * - V1.2.67 15-OCT-2011: Add Joomla 1.7 compatibility to use tablePrefix (j17) instead of table_prefix(j15, j16)
 * - V1.2.85 07-APR-2012: Reset the error number in setNewConnection to avoid using the error of a previous connection.
 */
 
// No direct access
defined('_JEXEC') or die( 'Restricted access' );


require_once( JPATH_LIBRARIES .DS. 'joomla' .DS. 'database' .DS. 'database' .DS. 'mysqli.php');

// ===========================================================
//             MultisitesDatabaseMySQLi class
// ===========================================================
class MultisitesDatabaseMySQLi extends JDatabaseMySQLi
{

   // ------------- setPrefix ----------------
	/**
	 * @brief   Replace the current table prefix and return the previous value
	 *
	 * @param	$table_prefix	The new table prefix
	 * @return	Return the previous table prefix value.
	 */
	function setPrefix( $table_prefix)
	{
	   // If Joomla 1.7
	   if ( isset( $this->connection)) {
   	   $result = !empty( $this->tablePrefix) ? $this->tablePrefix : null;
   	   $this->tablePrefix = $table_prefix;
	   }
	   // Else Joomla 1.5 or 1.6
	   else {
   	   $result = !empty( $this->_table_prefix) ? $this->_table_prefix : null;
   	   $this->_table_prefix = $table_prefix;
	   }

		return $result;
	}

   // ------------- setErrorInfo ----------------
   /**
    * @brief set the MySQL error information
    */
	function setErrorInfo( $errorNum, $errorMsg)
	{
	   // If Joomla 1.7
	   if ( isset( $this->errorNum)) {
   		$this->errorNum = $errorNum;
   		$this->errorMsg = $errorMsg;
	   }
	   else {
   		$this->_errorNum = $errorNum;
   		$this->_errorMsg = $errorMsg;
	   }
	}


   // ------------- setNewConnection ----------------
   /**
    * @brief set a new DB Connect
	 * @return	Return the previous DB Connection value.
    */
	function setNewConnection( $host, $user, $password, $select, $database)
	{
	   // If Joomla 1.7 then use the '_connection' field 
	   if ( isset( $this->connection))        { $con = 'connection'; }
	   // If Joomla 1.6 then use the '_connection' field 
	   else if ( isset( $this->_connection))  { $con = '_connection'; }
   	// If Joomla 1.5
   	else                                   { $con = '_resource'; }


		// Unlike mysql_connect(), mysqli_connect() takes the port and socket
		// as separate arguments. Therefore, we have to extract them from the
		// host string.
		$port	= NULL;
		$socket	= NULL;
		$targetSlot = substr( strstr( $host, ":" ), 1 );
		if (!empty( $targetSlot )) {
			// Get the port number or socket name
			if (is_numeric( $targetSlot ))
				$port	= $targetSlot;
			else
				$socket	= $targetSlot;

			// Extract the host name only
			$host = substr( $host, 0, strlen( $host ) - (strlen( $targetSlot ) + 1) );
			// This will take care of the following notation: ":3306"
			if($host == '')
				$host = 'localhost';
		}

		// perform a number of fatality checks, then return gracefully
		if (!function_exists( 'mysqli_connect' )) {
			$this->setErrorInfo( 1, 'The MySQL adapter "mysqli" is not available.');
			return false;
		}


		if (!($new_con = @mysqli_connect($host, $user, $password, NULL, $port, $socket))) {
			$this->setErrorInfo( 2, "Could not connect to MySQL host=[$host] with user=[$user]");
			return false;
		}
   	
      // If the current connection is open
      if ( $this->connected()) {
		    // Close previous connection
		   $this->__destruct();
      }
   	$this->$con  = $new_con;

		// Set sqli_mode to non_strict mode
		mysqli_query( $this->$con, "SET @@SESSION.sql_mode = '';");

		// select the database
		if ( $select ) {
		   $this->setErrorInfo( 0, '');
			return $this->select($database);
		}
   	
   	return true;
	}


}


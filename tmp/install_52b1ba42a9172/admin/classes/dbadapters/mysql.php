<?php
/**
 * @file       mysqli.php
 * @brief      Light MySQLi implementation
 * @version    1.2.85
 * @author     Edwin CHERONT     (info@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2012 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.85 07-APR-2012: Initial version
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// ===========================================================
//             MultisitesDatabaseMySQL class
// ===========================================================
class MultisitesDatabaseLightMySQL extends MultisitesDatabase
{
   //------------ __construct ---------------
   /**
    * @brief Login on MySQL using the User/Password
    */
	function __construct( $options)
	{
		$this->errorNum = 0;
		$this->errorMsg = '';

		$this->normalizeOptions( $options);
		$this->options = $options;


		// Make sure the Apache Module is installed and enabled.
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

		// Finalize initialisation
		parent::__construct($options);
		
		// Set sql_mode to non_strict mode
		mysql_query("SET @@SESSION.sql_mode = '';", $this->connection);
	}

   //------------ __destruct ---------------
	function __destruct()
	{
		if (is_resource($this->connection))
		{
			mysql_close($this->connection);
		}
	}



   //------------ isConnected ---------------
   /**
    * @brief Check if the DB conne
    */
    
	function isConnected()
	{
		if (is_resource($this->connection))
		{
			return mysql_ping($this->connection);
		}

		return false;
	}


   //------------ select_db ---------------
	function select_db( $database=null)
	{
	   // In case where there is no DB name specified, use the one provided when creating the object
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



   //------------ execQuery ---------------
	/**
	 * @brief Execute the SQL statement.
	 */
	function execQuery( $query)
	{
		// Reset the error values.
		$this->errorNum = 0;
		$this->errorMsg = '';
		
		if ( !is_resource($this->connection)) {
   		$this->errorNum = -1;
   		$this->errorMsg = 'DB is not connected';
		   return false;
		}

		// replace the #__ by the table prefix
		$sql = $this->replacePrefix( $query);
		
		// Execute the query.
		$this->cursor = mysql_query( $sql, $this->connection);
		// If an error occurred handle it.
		if ( !$this->cursor)
		{
			$this->errorNum = mysql_errno($this->connection);
			$this->errorMsg = mysql_error($this->connection) . ' SQL=' . $sql;;
			return false;
		}

		return $this->cursor;
	}

}

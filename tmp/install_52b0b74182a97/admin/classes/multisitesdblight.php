<?php
/**
 * @file       multisitesdblight.php
 * @brief      Light DB implementation
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
//             MultisitesDatabaseLight class
// ===========================================================
/**
 * @brief Light DB implementation
 */
class MultisitesDatabaseLight
{
   var $errorNum     = 0;
   var $errorMsg     = '';
   
   //------------ normalizeOptions ---------------
	function normalizeOptions( &$options)
	{
		$options['driver']   = (isset($options['driver'])) 	? preg_replace('/[^A-Z0-9_\.-]/i', '', $options['driver']) : 'mysql';
		$options['host']     = (isset($options['host']))      ? $options['host']            : 'localhost';
		$options['user']     = (isset($options['user']))      ? $options['user']            : 'root';
		$options['password'] = (isset($options['password']))  ? $options['password']        : '';
		$options['database'] = (isset($options['database']))  ? $options['database']        : '';
		$options['prefix']   = (isset($options['prefix']))    ? $options['prefix']          : 'jos_';
		$options['select']   = (isset($options['select']))    ? (bool) $options['select']   : true;
	}
	
   //------------ __construct ---------------
	function __construct( $options)
	{
		// Get some basic values from the options.
		MultisitesDatabase::normalizeOptions( $options);
	}
   
   //------------ getInstance ---------------
   function &getInstance( $options = array(), $cache_level = 'prefix')
   {
	   // Just keep a single instance to avoid having to many connection.
		static $instances;
	   
		if (!isset( $instances )) {
			$instances = array();
		}

		// Get some basic values from the options.
		MultisitesDatabase::normalizeOptions( $options);
		
		// If we don't want to use the cache
		if ( $cache_level == 'none') {}
		// If the cache is required
		else {
		   // Compute the signature depending on the cache level
   		$cache_types = array( 'user'     => array( 'driver', 'host', 'user'),
   		                      'database' => array( 'driver', 'host', 'user', 'database'),
   		                      'prefix'   => array( 'driver', 'host', 'user', 'database', 'prefix')
   		                     );
   		
   		$sigOptions = $options;
   		foreach( $sigOptions as $key => $value) {
   		   // If the option key is part of the signature
   		   if ( in_array( $key, $cache_types[ $cache_level])) {
   		      // Keep it
   		   }
   		   // Otherwise
   		   else {
   		      // remove the element from the signature
   		      unset( $sigOptions[$key]);
   		   }
   		}
   
   		// Get the options signature for the database connector.
   		$signature = md5(serialize( $sigOptions));
   
   		// If we already have a database connector instance for these options then just use that.
   		if ( !empty( $instances[$signature])) {
   		   return $instances[$signature];
   		}
   	}
		
		// Otherwise, create a new connection

		// Derive the class name from the driver.
		$class = 'MultisitesDatabaseLight' . ucfirst($options['driver']);
		if ( !class_exists($class)) {
		   // Load the DB adapter
		   $filename = dirname( __FILE__) .DIRECTORY_SEPARATOR. 'dbadapters' .DIRECTORY_SEPARATOR. strtolower( $options['driver']).'.php';
		   if ( !file_exists( $filename)) {
   		   return -1;
		   }
		   include_once( $filename);
		}
		if ( !class_exists($class)) {
   		   return -2;
		}

		// If the cache is required, save the result in the cache
		if ( !empty( $signature)) {
   		$instances[$signature] = new $class( $options);
   	   return $instances[$signature];
   	}
   	
		// When there is no cache, just return the object
		$db = new $class( $options);
	   return $db;
   }
   
   //------------ getErrorMsg ---------------
	function getErrorMsg( $escaped = false)
	{
		if ($escaped) {
			return addslashes($this->errorMsg);
		}
		else {
			return $this->errorMsg;
		}
	}

   //------------ getErrorNum ---------------
	function getErrorNum()
	{
		return $this->errorNum;
	}
   
   //------------ createUser ---------------
	/**
	 * @brief Create the new user and password corresponding to the new DB
	 */
	function createUser( $toConfig, $table_name = null)
	{
      $errors = array();
      
	   if ( empty( $table_name)) {
	      $table_name = MultisitesDatabase::backquote( $toConfig->getValue( 'config.db'))
	                  . '.*';
	   }
	   
      $user_name  = MultisitesDatabase::_getDBUserName( $toConfig);
	   $toDBPsw    = $toConfig->getValue( 'config.password');
	   
		$query = "GRANT ALL PRIVILEGES ON $table_name"
		       . " TO $user_name"
		       . (empty($toDBPsw) ? '' : " IDENTIFIED BY '$toDBPsw'")
		       . " WITH GRANT OPTION;"
		       ;
		if ( !$this->execQuery( $query)) {
         $this->errorNum = -4;
		   $this->errorMsg = JText::sprintf('SITE_DEPLOY_CREATEUSER_ERR', $result, $query, $this->getErrorMsg());
ob_start();
debug_print_backtrace();
$stack = ob_get_contents();
ob_end_clean();
Debug2Win::debug( "_createUser ERROR query=[$query] db: " . var_export( $this, true) . $stack);
			return false;
		}
		
		$this->execQuery( "FLUSH PRIVILEGES;");

		// Commit the DB creation to ensure that all the DB connection will be informed of the DB
		$this->execQuery('COMMIT');

		return true;
	}
   
   //------------ setQuery ---------------
 	function setQuery($query, $offset = 0, $limit = 0)
	{
		$this->sql     = $query;
		$this->limit   = (int) $limit;
		$this->offset  = (int) $offset;

		return $this;
	}
	
   //------------ setQuery ---------------
	function query()
	{
	   return $this->execQuery( $this->sql);
	}
  
   //------------ hasUTF ---------------
	function hasUTF()
	{
		return true;
	}

   //------------ replacePrefix ---------------
	/**
	 * This function replaces a string identifier <var>$prefix</var> with the string held is the
	 * <var>tablePrefix</var> class variable.
	 *
	 * @param   string  $sql     The SQL statement to prepare.
	 * @param   string  $prefix  The common table prefix.
	 *
	 * @return  string  The processed SQL statement.
	 *
	 * @since   11.1
	 */
	public function replacePrefix($sql, $prefix = '#__')
	{
		// Initialize variables.
		$escaped = false;
		$startPos = 0;
		$quoteChar = '';
		$literal = '';

		$sql = trim($sql);
		$n = strlen($sql);

		while ($startPos < $n)
		{
			$ip = strpos($sql, $prefix, $startPos);
			if ($ip === false)
			{
				break;
			}

			$j = strpos($sql, "'", $startPos);
			$k = strpos($sql, '"', $startPos);
			if (($k !== false) && (($k < $j) || ($j === false)))
			{
				$quoteChar = '"';
				$j = $k;
			}
			else
			{
				$quoteChar = "'";
			}

			if ($j === false)
			{
				$j = $n;
			}

			$literal .= str_replace($prefix, $this->options['prefix'], substr($sql, $startPos, $j - $startPos));
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n)
			{
				break;
			}

			// quote comes first, find end of quote
			while (true)
			{
				$k = strpos($sql, $quoteChar, $j);
				$escaped = false;
				if ($k === false)
				{
					break;
				}
				$l = $k - 1;
				while ($l >= 0 && $sql{$l} == '\\')
				{
					$l--;
					$escaped = !$escaped;
				}
				if ($escaped)
				{
					$j = $k + 1;
					continue;
				}
				break;
			}
			if ($k === false)
			{
				// error in the query - no end quote; ignore it
				break;
			}
			$literal .= substr($sql, $startPos, $k - $startPos + 1);
			$startPos = $k + 1;
		}
		if ($startPos < $n)
		{
			$literal .= substr($sql, $startPos, $n - $startPos);
		}

		return $literal;
	}
} // End class

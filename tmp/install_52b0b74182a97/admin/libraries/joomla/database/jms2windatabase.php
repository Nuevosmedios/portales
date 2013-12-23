<?php
/**
 * @file       jms2windatabase.php
 * @brief      Wrapper to JDatabase to allow switch the reall database connection depending on
 *             on the context.
 *
 * @version    1.2.85
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.47 02-FEB-2011: Fix PHP Syntax error
 * - V1.2.85 07-APR-2012: Add a get instance reference
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access or JMS2Win patches are not installed');

jimport('joomla.database.database');




// ===========================================================
//             Jms2WinDatabase class
// ===========================================================
class Jms2WinDatabase extends JDatabase
{
   // ------------- getInstanceRef ----------------
   /**
    * @brief Return a unique reference to a Database object
    */
	function & getInstanceRef($options = array())
	{
	   // As of joomla 1.6, the getInstance return a copy of the instance.
      if ( version_compare( JVERSION, '1.6') >= 0) {
   		// Sanitize the database connector options.
   		$options['driver'] = (isset($options['driver'])) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $options['driver']) : 'mysql';
   		$options['database'] = (isset($options['database'])) ? $options['database'] : null;
   		$options['select'] = (isset($options['select'])) ? $options['select'] : true;
   
   		// Get the options signature for the database connector.
   		$signature = md5(serialize($options));


         // If force to refresh the get instance
         if ( !empty( $options['refresh'])) {
            // And that an instance already exists
      		if ( !empty(self::$instances[$signature])) {
      		   // Then reset it
      		   self::$instances[$signature] = null;
      		}
         }
         
         // Get a new copy of the instance
         $db = Jms2WinDatabase::getInstance( $options);
      	
      	// Finally return the reference of the instance instead of the $db copy.
      	return self::$instances[$signature];
      }
      
      // If Joomla 1.5, this already return a reference.
      return Jms2WinDatabase::getInstance( $options);
	}
	   
   function writeTrace()
   {
      require_once( JPATH_MUTLISITES_COMPONENT .DS. 'classes' .DS. 'debug.php');
      $prevStandalone   = Debug2Win::isStandalone();
      $prevFilename     = Debug2Win::getFileName();;
      $prevDebug        = Debug2Win::isDebug();
      Debug2Win::enableStandalone();   // Write the log in administrator/components/com_multisites/classes/logs
      Debug2Win::setFileName( 'database.log.php');
      Debug2Win::enableDebug();        // Remove the comment to enable the debugging

      // Function available from PHP 4.3.0
      $arr = debug_backtrace();
      Debug2Win::debug( var_export( $arr, true));

      Debug2Win::setFileName( $prevFilename);
      if ( !$prevDebug) {
         Debug2Win::disableDebug( $prevDebug);        // Remove the comment to enable the debugging
      }
      if ( !$prevStandalone) {
         Debug2Win::disableStandalone();
      }
   }
   
   
   public function connected()                     {}
	public function dropTable($table, $ifExists = true)          {}
	public function escape($text, $extra = false)   {}
	protected function fetchArray($cursor = null)   {}
	protected function fetchAssoc($cursor = null)   {}
	protected function fetchObject($cursor = null, $class = 'stdClass')  {}
	protected function freeResult($cursor = null)   {}
	public function getAffectedRows()               {}
	public function getCollation()                  {}
	public function getNumRows($cursor = null)      {}
	public function getQuery($new = false)          {}
	public function getTableColumns($table, $typeOnly = true)   {}
	public function getTableCreate($tables)                     {}
	public function getTableKeys($tables)                       {}
	public function getTableList()                              {}
	public function getVersion()                                {}
	public function hasUTF()                                    {}
	public function insertid()                                  {}
	public function lockTable($tableName)                       {}
	public function query()                                     {}
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)   {}
	public function select($database)                           {}
	public function setUTF()                                    {}
	public function transactionCommit()                         {}
	public function transactionRollback()                       {}
	public function transactionStart()                          {}
	public function unlockTables()                              {}
	public function explain()                                   {}
	public function queryBatch($abortOnError = true, $transactionSafe = false) {}

	public static function test()                               {}

} // End Class
?>
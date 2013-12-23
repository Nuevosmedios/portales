<?php
/**
 * @file       check_jdbmysql.php
 * @brief      Ensure that the session is written before closing the DB.
 * @version    1.2.69
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
 * - V1.2.69 27-SEP-2011: Ensure that the session is written before closing the DB
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkJDatabaseMySQL ---------------
/**
 * check the patch is present
 */
function jms2win_checkJDatabaseMySQL( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[NOK]|File Not Found';
	}
   $str = file_get_contents( $filename);
   
   // If the patch is not present
   $pos = strpos( $str, '//_jms2win');
   if ($pos === false)  { $wrapperIsPresent = false; }
   else                 { $wrapperIsPresent = true; }

   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'Fix Joomla 1.6, 1.7 but to ensure that the session are written before closing the DB.');
      $result .= '|[ACTION]';
      $result .= '|Add 1 line to write the session before closing the DB.';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionJDatabaseMySQL ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionJDatabaseMySQL( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( 'patch_jdbmysql.php');
   if ( $patchStr === false) {
      return false;
   }

//	$filename = JPATH_ROOT.DS.$file;
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
   $content = file_get_contents( $filename);
   if ( $content === false) {
      return false;
   }
   
   // Remove potential exising patches
   // $content = jms2win_removePatch( $content);
   
   // Search/Replace the statement
   /*
      ===========
      Search for:
      ===========
   	public function __destruct()
   	{
   		if (is_resource($this->connection)) {
   			mysql_close($this->connection);
   		}
   	}
      
      ===========
      and Replace by:
      ===========

   	public function __destruct()
   	{
   		if (is_resource($this->connection)) {
//_jms2win_begin v1.2.69
   		   session_write_close();
//_jms2win_end
   			mysql_close($this->connection);
   		}
   	}

   */
   
   // ------------- Patch definition ----------------
   /* ....\n
      \n   ... mysql_close($this->connection);          \n
      p0       p1                                        p2
      
      Produce
      begin -> p0 + INSERT PATCH + p0 -> end
      
    */
    
   // P1: Search begin statement: "mysql_close"
   $p1 = strpos( $content, 'mysql_close');
   if ( $p1 === false) {
      return false;
   }

   // P0: Go to Begin of line
   for ( $p0 = $p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

   // p2: Search for end of line
   for ( $p2=$p1; $content[$p2] != "\n"; $p2++);
   
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p0)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}

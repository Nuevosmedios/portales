<?php
/**
 * @file       check_jdatabase.php
 * @brief      Check if the table_prefix is protected or public.
 * @version    1.2.65
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
 * - V1.0.07 23-AUG-2009: File creation
 * - V1.2.55 20-JUN-2011: Totally discard the old code that were removed in JMS 1.2.40 (Joomla 1.6)
 *                        and re-implement a new patch to be compatible with Joomla 1.7
 * - V1.2.65 07-AUG-2011: Make the "protected function replacePrefix()" as a "public function replacePrefix()"
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkJDatabase ---------------
/**
 * check if 'public _table_prefix' is present
 */
function jms2win_checkJDatabase( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[NOK]|File Not Found';
	}
   $str = file_get_contents( $filename);
   
   // If the patch is not present
   $pos = strpos( $str, '//_jms2win');
   if ($pos === false) $wrapperIsPresent = false;
   else {
      $pos = strpos( $str, 'protected function replacePrefix');
      if ($pos === false)  { $wrapperIsPresent = true; }
      else                 { $wrapperIsPresent = false; }
   }

   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'Add a get instance on foreign adapters after that joomla 1.7 protected the access to the constructor of the adapters.');
      $result .= JText::_( 'Make piblic the protected function replacePrefix.');
      $result .= '|[ACTION]';
      $result .= '|Add 1 line to get new adapter instance and update 1 line to make the protected replacePrefix as public.';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionJDatabase ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionJDatabase( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( 'patch_jdatabase.php');
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
   $content = jms2win_removePatch( $content);
   
   // Search/Replace the statement
   /*
      ===========
      Search for:
      ===========
      abstract class JDatabase
      {
         . . . 
         . . . 
      	protected function replacePrefix($sql, $prefix='#__')
      }
      
      ===========
      and Replace by:
      ===========

      abstract class JDatabase
      {
         function &getAdapter( $adapter, $option) { return new $adapter( $option); }
         . . . 
         . . . 
      	public function replacePrefix($sql, $prefix='#__')
      }

   */
   
   // ------------- Patch definition ----------------
   /* ....\n
      \n   ... class JDatabase          \n
               p0                       p1
      \n   {   \n
           p2  p3
      
      Produce
      begin -> p3 + INSERT PATCH + p3 -> end
      
    */
    
    
   $content = str_replace( 'protected function replacePrefix', 'public function replacePrefix', $content);
   
   // P1: Search begin statement: "class JDatabase"
   $p0 = strpos( $content, 'class JDatabase');
   if ( $p0 === false) {
      return false;
   }

   // P2: Go to '{'
   for ( $p2=$p0; $content[$p2] != "{"; $p2++);

   // p3: Search for end of line
   for ( $p3=$p2; $content[$p3] != "\n"; $p3++);
   $p3++;
   
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p3)
           . $patchStr
           . substr( $content, $p3);

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}

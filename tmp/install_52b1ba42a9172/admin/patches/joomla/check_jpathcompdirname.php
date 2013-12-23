<?php
/**
 * @file       check_jpathcompdirname.php
 * @brief      Check if the JPATH_COMPONENT is computed based on JPATH_BASE.
 * @version    1.2.43
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
 * - V1.2.86 15-JUN-2012: Initial version
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkJPathCompDirname ---------------
/**
 * check if following lines are present:
 * - MULTISITES_ID is not present and dirname( __FILE__) is present
 */
function jms2win_checkJPathCompDirname( $model, $file)
{
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
	if ( !file_exists( $filename)) {
	   return '[IGNORE]|File Not Found';
	}
	
   $str = file_get_contents( $filename);
   
   // if 'MULTISITES_ID' is present
   $pos = strpos( $str, 'MULTISITES_ID');
   if ($pos === false) {
      // If dirname is present
      if ( strpos( $str, 'dirname') !== false) {
         $wrapperIsPresent = false;
      }
      else {
   	   return '[IGNORE]|dirname pattern is not present';
      }
   }
   else {
      $wrapperIsPresent = true;
   }
   
   $result = "";
   $rc = '[OK]';
   if ( !$wrapperIsPresent) {
	   $rc = '[NOK]';
      $result .= JText::_( 'Fix the JPATH_COMPONENT computation used by ajax call. Replace the dirname by JPATH_BASE./components/XXXXX path.');
      $result .= '|[ACTION]';
      $result .= '|Replace 1 line containing the new define value.';
   }
   
   return $rc .'|'. $result;
}



//------------ jms2win_actionJPathCompDirname ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionJPathCompDirname( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( 'patch_jpathcompdirname.php');
   if ( $patchStr === false) {
      return false;
   }

   // Compute the '{option}' value based on the "file" path.
   $path = $file;
   while( !empty( $path) && basename( dirname( dirname( $path))) != 'components') {
      $path = dirname( $path);
   }
   if ( basename( dirname( dirname( $path))) == 'components') {
      $option = basename( dirname( $path));
   }
   
   if ( empty( $option)) {
      $option = basename( dirname( $file));
   }
   
   $patchStr = str_replace( '{option}', $option, $patchStr);
   
   

//	$filename = JPATH_ROOT.DS.$file;
	$filename = JPath::clean( JPATH_ROOT.DS.$file);
   $content = file_get_contents( $filename);
   if ( $content === false) {
      return false;
   }
   
   // Search/Replace the statement
   /*
      ===========
      Search for:
      ===========
		........
		........
      define( 'JPATH_COMPONENT' , dirname( __FILE__ ) );	
		........
		........
		........
      
      ===========
      and Replace by:
      ===========
		........
		........
//_jms2win_begin v1.2.86  MULTISITES_ID
	define('JPATH_COMPONENT', JPATH_BASE . '/components/{option}');   // where {option} is the extension option value
//_jms2win_end
		........
		........
		........

   */
   
   // ------------- Patch definition ----------------
   /* ....\n
      \n .... define( 'JPATH_COMPONENT' , dirname( __FILE__ ) );	... \n
      p0                                  p1                          p2
      
      Produce
      begin -> p0 + INSERT PATCH + p2 -> end
      
    */
   
   // p1: Search for "dirname"
   $p1 = strpos( $content, 'dirname');
   if ( $p1 === false) {
      return false;
   }
   
   // P0: Go to Begin of line
   for ( $p0 = $p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;

   // p2: Search for end of line
   for ( $p2=$p1; $content[$p2] != "\n"; $p2++);
   $p2++;

 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p2)
           ;

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}

   return true;
}


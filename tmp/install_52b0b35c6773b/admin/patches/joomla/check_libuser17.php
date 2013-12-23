<?php
/**
 * @file       check_libuser17.php
 * @brief      Add security check in the getParams
 * @version    1.1.8
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
 */

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

//------------ jms2win_checkLibUser17 ---------------
/**
 * check if the "$this->_params" is present to return a value.
 * Otherwise, return the default value.
 */
function jms2win_checkLibUser17( $model, $file)
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
      $result .= JText::_( 'Fix a bug present in joomla 1.6 and 1.7 that seems have declined to fix.<br/>'
                         . 'See Joomla bug tracker where they decline to fix it as they are not able to reproduce it.<br/>'
                         . 'http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_id=8103&tracker_item_id=24879');
      $result .= '|[ACTION]';
      $result .= '|Add 3 lines to return the default value when joomla has not correctly initialized the users parameters.';
   }
   
   return $rc .'|'. $result;
}

//------------ jms2win_actionJConfig ---------------
/**
 * @brief Install the patch
 */
function jms2win_actionLibUser17( $model, $file)
{
   include_once( dirname(__FILE__) .DS. 'patchloader.php');
   $patchStr = jms2win_loadPatch( 'patch_libuser17.php');
   if ( $patchStr === false) {
      return false;
   }

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

   	public function getParam($key, $default = null)
   	{
   		return $this->_params->get($key, $default);
   	}

      
      ===========
      and Replace by:
      ===========

   	public function getParam($key, $default = null)
   	{
   //_jms2win_begin v1.2.69
   		if ( empty( $this->_params)) {
   		   return $default;
   		}
   //_jms2win_end
   		return $this->_params->get($key, $default);
   	}

   */
   
   // ------------- Patch deinition ----------------
   /* ....\n
      \n return $this->_params->get($key, $default);
      p0        p1
      
      Produce
      begin -> p0 + INSERT PATCH + p0 -> end
      
    */
   // P1: Search begin statement: "$this->_params->get"
   $p1 = strpos( strtolower( $content), '$this->_params->get');
   if ( $p1 === false) {
      return false;
   }
   // P0: Go to Begin of line
   for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
   $p0++;
   
 
   // ------------- Compute the results ----------------
   // Here, we have found the statement to patch
   $result = substr( $content, 0, $p0)
           . $patchStr
           . substr( $content, $p0);

   // ------------- Write the PATCH results ----------------
	jimport('joomla.filesystem.file');
	if ( !JFile::write( $filename, $result)) {
      return false;
	}
   
   return true;
}

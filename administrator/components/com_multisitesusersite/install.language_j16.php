<?php
/**
 * @file       install.language_j16.php
 * @version    1.1.0
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms  Multi Sites
 *             Single Joomla! 1.5.x AND 1.6.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2011 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.0 25-MAY-2011: Initial version.
 */


// Dont allow direct linking
defined( '_JEXEC' ) or die();

jimport('joomla.filesystem.folder');


// ===========================================================
//             MultisitesConvertLanguage class
// ===========================================================
class MultisitesConvertLanguage {

   //------------ content ---------------
   function content( $filename) {
      $lines = array();
      $search  = array( '(', 
                        ')',
                        '{',
                        '}',
                        '[',
                        ']',
                        '"'
                        );
      $replace = array( '&#40;',
                        '&#41;',
                        '&#123;',
                        '&#125;',
                        '&#91;',
                        '&#93;',
                        '"_QQ_"'
                        );
      $skip_keys = array( 'NO',
                          'YES',
                          'SAVE & NEW',
                          'BLACK LIST (DEFAULT)',
                          'ITEM(S) SENT TO THE TRASH',
                          'ITEM(S) SUCCESSFULLY ARCHIVED',
                          'ITEM(S) SUCCESSFULLY COPIED TO SECTION',
                          'ITEM(S) SUCCESSFULLY MOVED TO SECTION',
                          'ITEM(S) SUCCESSFULLY MOVED TO UNCATEGORIZED',
                          'ITEM(S) SUCCESSFULLY PUBLISHED',
                          'ITEM(S) SUCCESSFULLY UNPUBLISHED',
                          'ITEM(S) SUCCESSFULLY UNARCHIVED'
                        );
      
      $fd = @fopen( $filename, "r");
      if ( !$fd) {
         return;
      }
      
      while( !feof( $fd)) {
         $line = fgets( $fd);
         if ( !empty( $line)) {
            $line = trim( $line);

            // If comment, skip the processing
            if ( substr( $line, 0, 1) == ';') {}
            // If a old comment
            else if ( substr( $line, 0, 1) == '#') {
               // replace by ';'
               $line = ';' . substr( $line, 1);
            }
            else {
               // Extract the value to quote it and replace some special characters
               $pos = strpos( $line, '=');
               if ( $pos === false) {}
               else {
                  $key = trim( substr( $line, 0, $pos));
                  if ( in_array( $key, $skip_keys)) {
                     continue;
                  }
                  // position just after the "="
                  $pos++;
                  $value = trim( substr( $line, $pos));
                  if ( !empty( $value)) {
                     // If quote is already present
                     if ( substr( $value, 0, 1) == '"' && substr( $value, -1) == '"') {
                        // remove them to avoid convert them
                        $value = rtrim( $value, '"');
                        $value = ltrim( $value, '"');
                        $addquote = '"';
                     }
                     else {
                        $addquote = '"';
                     }
                     $str = str_replace( $search, $replace, $value);
                     
                     $line = substr( $line, 0, $pos)
                           . $addquote
                           . $str
                           . $addquote
                           ;
                  }
               }
            }
         }
         $lines[] = $line;
      }
      fclose( $fd);
      
      // Convert all lines into a string
      $result = implode( "\n", $lines);
      
      // Write the new language file
   	jimport('joomla.filesystem.file');
   	JFile::write( $filename, $result);
   }
   
   //------------ files ---------------
   function files() {
      // Search in the manifest for all the languages files
      if ( empty( $GLOBALS['installManifest'])) {
         return;
      }
      
      $manifest = $GLOBALS['installManifest'];
      if ( !empty( $manifest->languages) && !empty( $manifest->languages->language)) {
         foreach( $manifest->languages->language as $language_file) {
            $filename = JPath::clean( JPATH_ROOT.DS.'language'.DS.$language_file);
            MultisitesConvertLanguage::content( $filename);
         }
      }

      if ( !empty( $manifest->administration) && !empty( $manifest->administration->languages)) {
         foreach( $manifest->administration->languages->language as $language_file) {
            $filename = JPath::clean( JPATH_ADMINISTRATOR.DS.'language'.DS.$language_file);
            MultisitesConvertLanguage::content( $filename);
         }
      }
   }

} // End class
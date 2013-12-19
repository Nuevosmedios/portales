<?php
/**
 * @file       patches_usersharing.php
 * @brief      Multisites Patches defintions for a partial sharing of the users.
 * @version    1.2.00
 * @author     Edwin CHERONT     (info@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
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
 * - V1.0.0    16-AUG-2011: Initial version
 * - V1.1.2    02-JUN-2012: Add specific dbsharing_25.xml file
 * - V1.2.0    14-MAR-2013: Add Joomla 3.0 compatibility and no more compatible with JMS 1.2.x
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.plugin.plugin' );
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');


// Check that the "Multisites" components is installed.
// Otherwise disable code of this plugin
if ( !defined( 'DS'))   { define('DS', DIRECTORY_SEPARATOR); }
if ( JFolder::exists( JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_multisites'))
{
   require_once( JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');

   // ===========================================================
   //             plgMultisitespatches_usersharing class
   // ===========================================================
   class plgMultisitespatches_usersharing extends JPlugin
   {
      //------------ Constructor ---------------
   	/**
   	 * Constructor
   	 *
   	 * For php4 compatability we must not use the __constructor as a constructor for plugins
   	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
   	 * This causes problems with cross-referencing necessary for the observer design pattern.
   	 *
   	 * @access	protected
   	 * @param	object	$subject The object to observe
   	 * @param 	array   $config  An array that holds the plugin configuration
   	 * @since	1.0
   	 */
   	function plgMultisitespatches_usersharing(& $subject, $config)
   	{
   		parent :: __construct($subject, $config);
 		}
   	
      //------------ onDBTableLoaded ---------------
      /**
       * @brief Allow to append additional Table definition to the JMS Tools
       */
   	function onDBTableLoaded( &$fullxml)
   	{
   	   $xmlpath = dirname( __FILE__) .DS. 'patches_usersharing' .DS. 'dbtables.xml';
   	   return $this->_appendXML( $fullxml, $xmlpath);
   	}

      //------------ onDBSharingLoaded ---------------
      /**
       * @brief Allow to append additional Sharing definition to the JMS Tool and JMS Template menus
       */
   	function onDBSharingLoaded( &$fullxml)
   	{
         if ( version_compare( JVERSION, '2.5') >= 0) {
      	   $xmlpath = dirname( __FILE__) .DS. 'patches_usersharing' .DS. 'dbsharing_25.xml';
      	}
         else if ( version_compare( JVERSION, '1.7') >= 0) {
      	   $xmlpath = dirname( __FILE__) .DS. 'patches_usersharing' .DS. 'dbsharing_17.xml';
      	}
         else if ( version_compare( JVERSION, '1.6') >= 0) {
      	   $xmlpath = dirname( __FILE__) .DS. 'patches_usersharing' .DS. 'dbsharing_16.xml';
      	}
      	else {
      	   $xmlpath = dirname( __FILE__) .DS. 'patches_usersharing' .DS. 'dbsharing.xml';
      	}
   	   return $this->_appendSharingXML( $fullxml, $xmlpath);
   	}

      //------------ _appendXML ---------------
      /**
       * @brief append an XML file to an existing XML
       * Append the content of the "filename" into the "fullxml" document.
       * @param $fullxml   A reference to the XML variable that contain an existing XML content
       * @param $filename  The file name where are store the XML that must be appended to "fullxml"
       */
   	function _appendXML( &$fullxml, $filename)
   	{
   		// load the configuration
   		if ( file_exists( $filename))
   		{
   			$xml =& Jms2WinFactory::getXMLParser('Simple');
   			if ($xml->loadFile( $filename)) {
   			   // Append the current XML to the full one.
   			   for ( $i=0; $i<count( $xml->document->_children); $i++) {
   			      // Append the child to the the full XML
            		$name = $xml->document->_children[$i]->name();
            		
            		//Add the reference of it to the end of an array member named for the elements name
            		$fullxml->document->{$name}[]  =& $xml->document->_children[$i];
            		
            		//Add the reference to the children array member
            		$fullxml->document->_children[] =& $xml->document->_children[$i];
   			   }
   			}
   		}
   		return true;
   	}

      //------------ _appendXML ---------------
      /**
       * @brief append an XML file to an existing XML
       * Append the content of the "filename" into the "fullxml" document.
       * @param $fullxml   A reference to the XML variable that contain an existing XML content
       * @param $filename  The file name where are store the XML that must be appended to "fullxml"
       */
   	function _appendSharingXML( &$fullxml, $filename)
   	{
   		// load the configuration
   		if ( file_exists( $filename))
   		{
   			$xml =& Jms2WinFactory::getXMLParser('Simple');
   			if ($xml->loadFile( $filename)) {
   			   // Append the current XML to the full one.
   			   for ( $i=0; $i<count( $xml->document->_children[0]->_children); $i++) {
   			      // Append the child to the the full XML
            		$name = $xml->document->_children[0]->_children[$i]->name();
            		
            		// Check if a same "entry already exists".
            		$appendEntry = true;
            		$attr_name = $xml->document->_children[0]->_children[$i]->_attributes['name'];
            		for ($j=0; $j<count($fullxml->document->_children[0]->_children); $j++) {
            		   if ( $fullxml->document->_children[0]->_children[$j]->_attributes['name'] == $attr_name) {
            		      if ( $fullxml->document->_children[0]->_children[$j]->_attributes['type'] == 'radio') {
            		         // Append the radio options
            		         for ( $k=0; $k<count($xml->document->_children[0]->_children[$j]->option); $k++) {
            		            $fullxml->document->_children[0]->_children[$j]->_children[] =& $xml->document->_children[0]->_children[$j]->option[$k];
            		            $fullxml->document->_children[0]->_children[$j]->option[] =& $xml->document->_children[0]->_children[$j]->option[$k];
            		         }
               		      $appendEntry = false;
            		      }
            		      else {
            		         // Remove the existing child to let add a new one that will replace the existing entry
                     		$fullxml->document->_children[0]->removeChild( $fullxml->document->_children[0]->_children[$j]);
            		      }
            		      break;
            		   }
            		}
            		if ( $appendEntry) {
               		//Add the reference of it to the end of an array member named for the elements name
               		$fullxml->document->_children[0]->{$name}[]  =& $xml->document->_children[0]->_children[$i];
               		
               		//Add the reference to the children array member
               		$fullxml->document->_children[0]->_children[] =& $xml->document->_children[0]->_children[$i];
            		}
   			   }
   			}
   		}
   		return true;
   	}
   } // End class
} // End check that Multisites component are installed.


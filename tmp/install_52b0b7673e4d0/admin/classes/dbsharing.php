<?php
/**
 * @file       dbsharing.php
 * @version    1.2.71
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2011 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.0 28-APR-2009: Initial version
 * - V1.2.0 RC3 05-JUL-2009: Fix warning concerning deprecated syntax in PHP 5.x
 * - V1.2.0 RC4 09-JUL-2009: Add check that dbsharing.xml contain effectivelly <tables> tags to avoid
 *                           searching children when the tag is not present
 * - V1.2.4 25-AUG-2009: Add Joomla 1.6 compatibility
 * - V1.2.30 01-JUN-2010: Add the possibility to include contributors XML DB Sharing description
 *                        into the loaded XML. Call the multisites plugin onDBSharingLoaded() function
 * - V1.2.33 08-JUL-2010: Add the possibility to exclude tables. Now read the tag <tableexcluded ...>
 * - V1.2.55 21-JUN-2011: Add Joomla 1.7 compatibility
 * - V1.2.65 17-SEP-2011: Read the "tablewhere" clause where could be written additional statement info when create a view.
 *                        Add the possibility to execute addional queries attached on a table.
 * - V1.2.71 06-JAN-2012: Add Joomla 2.5 compatibility
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.filesystem.path');

// ===========================================================
//            Jms2WinTemplate class
// ===========================================================
/**
 * @brief This is a Template record.
 *
 * Generally used in collection, this class contain all the information of a Template.\n
 * A site is defined by:
 */
class Jms2WinDBSharing
{
   var $success         = false;    /**< Flag that is set to true when some information is loaded */
   var $_xml            = null;

   //------------------- getInstance ---------------
   function &getInstance()
   {
		static $instance;

		if (!is_object($instance))
		{
		   $instance = new Jms2WinDBSharing();
		}
		
		return $instance;
   }

   //------------------- Constructor ---------------
   function Jms2WinDBSharing()
   {
      $this->success  = false;
   }


   //------------ getConfigFilename ---------------
	function getConfigFilename()
	{
      if ( version_compare( JVERSION, '2.5') >= 0) { 
   	   $filename = dirname( dirname( __FILE__))
   	             .DS. 'patches'
   	             .DS. 'sharing'
   	             .DS. 'dbsharing_25.xml'
   	             ;
      }
      else if ( version_compare( JVERSION, '1.7') >= 0) { 
   	   $filename = dirname( dirname( __FILE__))
   	             .DS. 'patches'
   	             .DS. 'sharing'
   	             .DS. 'dbsharing_17.xml'
   	             ;
      }
      else if ( version_compare( JVERSION, '1.6') >= 0) { 
   	   $filename = dirname( dirname( __FILE__))
   	             .DS. 'patches'
   	             .DS. 'sharing'
   	             .DS. 'dbsharing_16.xml'
   	             ;
      }
      else {
   	   $filename = dirname( dirname( __FILE__))
   	             .DS. 'patches'
   	             .DS. 'sharing'
   	             .DS. 'dbsharing.xml'
   	             ;
      }
	   return $filename;
	}

   //------------------- Constructor ---------------
   function getXML()
   {
      return $this->_xml;
   }
   //------------------- getSharedTables ---------------
   /**
    * @brief Load the DBSharing configuration.
    * This parse the XML document to extract all the <table> records that match the dbsharing values.
    * This return Table "like" conditions that match to the selected DBSharing entries (conditions).
    */
   function getSharedTables( $dbsharing)
   {
      $results = array();
      $results['table'] = array();
      $results['tableexcluded'] = array();
      
      $xml = & $this->_xml;
		$params =& $xml->getElementByPath('params');
		
		foreach( $dbsharing as $key => $value) {
		   // Search for the parameters with the name = '$key'
		   foreach( $params->children() as $param) {
		      // If the parameter is found
		      if ( $param->attributes( 'name') == $key) {
		         $type = $param->attributes( 'type');
   		      if ( $type == 'checkbox') {
   		         $tables = $param->getElementByPath('tables');
   		         if ( !empty( $tables)) {
      		         foreach( $tables->children() as $table) {
      		            $name = $table->attributes( 'name');
      		            if ( $table->name() == 'tableexcluded')   { $results['tableexcluded'][$name] = $name; }
      		            else if ( $table->name() == 'tablewhere') { $results['table'][$name]      = $name; 
      		                                                        $results['tablewhere'][$name] = $table->attributes( 'where');
      		                                                      }
      		            else                                      { $results['table'][$name] = $name; }
      		            
   		               // If there are queries attached to the table,
      		            foreach( $table->children() as $xmlqueries) {
         		            if ( $xmlqueries->name() == 'queries') {
         		               // Then store the queries collection
         		               $results['tablequeries'][$name][] = $xmlqueries;
         		            }
      		            }
      		         }
      		      }
   		      }
   		      // Radio, list, ... (every type with an option as children).
		         else {
   		         // Search for the option that correspond to the value
         		   foreach( $param->children() as $option) {
         		      if ( $option->attributes( 'value') == $value) {
         		         $tables = $option->getElementByPath('tables');
         		         if ( !empty( $tables)) {
            		         foreach( $tables->children() as $table) {
            		            $name = $table->attributes( 'name');
            		            if ( $table->name() == 'tableexcluded')   { $results['tableexcluded'][$name] = $name; }
            		            else if ( $table->name() == 'tablewhere') { $results['table'][$name]      = $name; 
            		                                                        $results['tablewhere'][$name] = $table->attributes( 'where');
            		                                                      }
            		            else                                      { $results['table'][$name] = $name; }
            		            
         		               // If there are queries attached to the table,
            		            foreach( $table->children() as $xmlqueries) {
               		            if ( $xmlqueries->name() == 'queries') {
               		               // Then store the queries collection
               		               $results['tablequeries'][$name][] = $xmlqueries;
               		            }
            		            }
            		         }
            		      }
         		         break;
         		      }
         		   } // Next <option value='...'
   		      }
		         break;
		      }
		   } // Next <param name='...' 
		}
      return $results;
   }


   //------------------- _getTables ---------------
   /**
    * @brief return an array of <table> node
    */
   function _getTables( &$children, &$tables)
   {
      if ( empty( $children)) {
         return;
      }
      foreach( $children as $child) {
         if ( $child->name() == 'table') {
            $tables[] = $child;
         }
         else if ( $child->name() == 'tableexcluded') {
            $child->excluded = true;
            $tables[] = $child;
         }
         else if ( $child->name() == 'tablewhere') {
            $tables[] = $child;
         }
         $smallChildren = $child->children();
         Jms2WinDBSharing::_getTables( $smallChildren, $tables);
      }
   }


   //------------------- getTables ---------------
   /**
    * @brief return an array of <table> node
    */
   function &getTables( $xmlnode)
   {
      $tables = array();
      Jms2WinDBSharing::_getTables( $xmlnode, $tables);
      return $tables;
   }

   //------------------- cleanupOnCondition ---------------
   /**
    * @brief Remove all nodes having a condition that where the file or folder does not exists
    */
   function cleanupOnCondition( &$node)
   {
		for ($i=count($node->_children)-1;$i>=0;$i--) {
		   $child = & $node->_children[$i];
         $condition = $child->attributes( 'condition');
         if ( !empty($condition)) {
            $path = str_replace( '{root}', JPATH_ROOT, $condition);
            if ( !file_exists( JPath::clean( $path))) {
               $node->removeChild( $child);
            }
         }
         else {
            $node->_children[$i] = $this->cleanupOnCondition( $child);
         }
	   }
	   return( $node);
   }


   //------------------- _indexExtensions ---------------
   /**
    * @brief Extract the extension name from the condition and create an index based on the extension name
    * to speed up retreiving the <tables> defintiion associated to the extension.
    */
   function _indexExtensions( &$node)
   {
		for ($i=count($node->_children)-1;$i>=0;$i--) {
		   $child = & $node->_children[$i];
         $condition = $child->attributes( 'condition');
         if ( !empty($condition)) {
            $parts = explode( '/', $condition);
            $n = count( $parts);
            if ( $n > 2) {
               $dir      = $parts[ $n - 2];
               if ( $dir == 'components'
                 || $dir == 'modules'
                  ) {
                  $ext_name = $parts[ $n - 1];
               }
               else if ( $parts[ $n - 3] == 'plugins') {
                  $ext_name = $dir .DS. $parts[ $n - 1];
               }
            }
            
            if ( !isset( $this->_extensions[$ext_name])) {
               $this->_extensions[$ext_name] = array();
            }
            $this->_extensions[$ext_name][] = & $child;
         }
         else {
            $this->_indexExtensions( $child);
         }
	   }
   }

   //------------------- indexExtensions ---------------
   /**
    * @brief Create an index on all the extension name having a condition that describe the sharing rules.
    */
   function indexExtensions( &$node)
   {
      $this->_extensions = array();
		$this->_indexExtensions( $this->_xml);
   }
   
   
   //------------------- getShareInfos ---------------
   /**
    * @brief Return an array with the list of sharing information available for the extension name
    */
   function &getShareInfos( $ext_name)
   {
      if ( !$this->isLoaded()) {
         $this->load();
      }
      if ( isset( $this->_extensions[ $ext_name])) {
         return $this->_extensions[ $ext_name];
      }
      $none = array();
      return $none;
   }
   
   //------------------- isLoaded ---------------
   function isLoaded()
   {
      if ( isset( $this->_xml)) {
         return true;
      }
      return false;
   }

   //------------------- load ---------------
   /**
    * @brief Load the DBSharing configuration.
    */
   function load()
   {
      // If there is already an XML file loaded
      if ( isset( $this->_xml)) {
         $this->success  = true;
   		return $this->success;
      }
      
      $this->success = false;
		$this->_xml    = null;
	   $xmlpath = $this->getConfigFilename();

		// load the configuration
		if ( file_exists($xmlpath))
		{
			$xml =& JFactory::getXMLParser('Simple');
			if ($xml->loadFile($xmlpath)) {
			   // When the XML file is loaded, give the opportunity to a plugin add description inside.
            JPluginHelper::importPlugin('multisites');
      		$mainframe	= &JFactory::getApplication();
            $results = $mainframe->triggerEvent('onDBSharingLoaded', array ( & $xml));
            
				$this->_xml =& $xml->document;

				$this->cleanupOnCondition( $this->_xml);
				$this->indexExtensions( $this->_xml);
				
            $this->success  = true;
			}
		}
		return $this->success;
	}
}


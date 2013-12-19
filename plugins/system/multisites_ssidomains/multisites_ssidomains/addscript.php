<?php
/**
 * @file       addscript.php
 * @brief      Add a javascript to the page.
 * @version    1.0.2
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
 * - V1.0.1    29-OCT-2011: Add possibility to use additional domains defined in the plugins
 *                          Also make the plugin runing independently (without Jms Multi Sites)
 * - V1.0.2    06-NOV-2011: Fix compatibility with Joomla 1.5
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// report replace the field by a warning message
// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) { 
   class JFormFieldAddScript extends JFormField
   {
   	protected $type = 'AddScript';

      //------------ fetchElement ---------------
   	protected function getInput()
   	{
   	   
   	   if ( !empty( $this->element['script'])) {
   	      $script = (string)$this->element['script'];
   	      if ( substr($script, 0, 1) == '/') {
   	         $script = JURI::root(true).$script;
   	      }
      		JFactory::getDocument()->addScript( $script);
      	}
   
   		return '';
   	}
   }
}
// Else: Default Joomla 1.5
else {
   class JElementAddScript extends JElement
   {
   	var	$_name = 'AddScript';
   	
      //------------ fetchTooltip ---------------
   	function fetchTooltip($label, $description, &$node, $control_name, $name) {
   		return '';
   	}
      //------------ fetchElement ---------------
   	function fetchElement($name, $value, &$node, $control_name)
   	{
   		$script = ($node->attributes('script') ? $node->attributes('script') : null);
   	   if ( !empty( $script)) {
   	      if ( substr($script, 0, 1) == '/') {
   	         $script = JURI::root(true).$script;
   	      }
      		JFactory::getDocument()->addScript( $script);
      	}
   
   		return '';
   	}
   }
}

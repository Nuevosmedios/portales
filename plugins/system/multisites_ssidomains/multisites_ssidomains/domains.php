<?php
/**
 * @file       domains.php
 * @brief      Display all the list of domains present in all the slave sites created in JMS.
 * @version    1.0.7
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
 * - V1.0.1    29-OCT-2011: Initial version
 * - V1.0.7    16-JUN-2012: Remove a notice message
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// When JMS is present
if ( file_exists( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'elements'.DS.basename( __FILE__))) {
   require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'elements'.DS.basename( __FILE__));
}
// When JMS is NOT present
else {
   // report replace the field by a warning message
   // If Joomla 1.6
   if ( version_compare( JVERSION, '1.6') >= 0) { 
      class JFormFieldDomains extends JFormField
      {
      	protected $type = 'Domains';

         //------------ fetchElement ---------------
      	protected function getInput()
      	{
      	   // Get inspired from the /libraries/joomla/form/fields/text.php

      		// Initialize some field attributes.
      		$class		= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
      		$readonly	= ' readonly="readonly"';
      		$disabled	= ' disabled="disabled"';
      
      		// Initialize JavaScript field attributes.
      		$onchange	= (!empty( $this->element) && !empty( $this->element['onchange'])) ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

      		$value      = 'Jms Multi Sites version 1.2.69 or higher is not present';
      		$len        = strlen($value)+7;
      		$size		   = ' size="'.(int)$len.'"';
      		$maxLength  = ' maxlength="'.(int)$len.'"';
      		
      		// rename the field params in "readonly" to avoid it has value in place of the original one
      		$name = str_replace( 'jform[params]', 'jform[params_ro]', $this->name);
      
      		return '<input type="text" name="'.$name.'" id="'.$this->id.'"' .
      				' value="'.$value.'"' .
      				$class.$size.$disabled.$readonly.$onchange.$maxLength.'/>';
      	}
      }
   }
   // Else: Default Joomla 1.5
   else {
      class JElementDomains extends JElement
      {
      	var	$_name = 'Domains';
      	
         //------------ fetchElement ---------------
      	function fetchElement($name, $value, &$node, $control_name)
      	{
      	   // Get inspired from the /libraries/joomla/html/parameter/text.php

      		// Initialize some field attributes.
      		$class = ($node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"');
      		$readonly	= ' readonly="readonly"';
      		$disabled	= ' disabled="disabled"';
      
      		// Initialize JavaScript field attributes.
      		$onchange	= (!empty( $this->element) && !empty( $this->element['onchange'])) ? ' onchange="'.(string) $this->element['onchange'].'"' : '';
      		
      		$value      = 'Jms Multi Sites version 1.2.69 or higher is not present';
            $len        = strlen($value)+7;
      		$size		   = ' size="'.(int)$len.'"';
      		$maxLength  = ' maxlength="'.(int)$len.'"';

      		// Set field READONLY
      		return '<input type="text" readonly="true" name="'.$control_name.'['.$name.'-ro]" id="'.$control_name.$name.'-ro"'
      		      .' value="'.$value.'" '
      		      .$class.$size.$disabled.$readonly.$onchange.$maxLength.' />';
      	}
      }
   }
}
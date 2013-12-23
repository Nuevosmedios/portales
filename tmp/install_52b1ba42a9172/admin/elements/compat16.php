<?php
/**
 * @file       compat16.php
 * @brief      Interface to provide a Joomla 1.5 and 1.6 compatibility.
 *
 * @version    1.2.88
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  JMS Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2012 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.5  20-DEC-2008: Save the current value of the site id to allow customized the article links, ...
 * - V1.2.34 17-JUL-2010: Add multisites selection
 * - V1.2.70 04-DEC-2011: Load the parent class in case where the element is directly called from an Ajax API
 * - V1.2.88 29-MAY-2012: Add Joomla 2.5 compatibility
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();


// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) { 
   jimport( 'joomla.form.formfield' );
   class MultisitesElement extends JFormField
   {
   	function getAttribute( $element, $name)
   	{
   		if ( method_exists( $element, 'getAttribute')) {
   			return $element->getAttribute( $name);
   		}
   		else if ( is_a( $element, 'JSimpleXML') && !empty( $element->document)) {
   			return $this->getAttribute( $element->document, $name);
   		}
   		return $element->attributes( $name);
//   	   return $element[$name] ? (string) $element[$name] : '';
   	}

   	protected function getInput()
   	{
   		$control_name   = str_replace( '['.$this->fieldname.']', '', $this->name);
   		$name           = $this->fieldname;
   		$value          = &$this->value;
   		$node           = &$this->element;

/*
   		$control_name   = &$this->name;
   		$name           = ''; //&$this->fieldname;
   		$value          = &$this->value;
   		$node           = &$this->element;
*/
   		return $this->fetchElement( $name, $value, $node, $control_name);
   	}
   	
   	

   	/**
   	 * Method to get a tool tip from an XML element
   	 *
   	 * @param   string       $label         Label attribute for the element
   	 * @param   string       $description   Description attribute for the element
   	 * @param   JXMLElement  &$xmlElement   The element object
   	 * @param   string       $control_name  Control name
   	 * @param   string       $name          Name attribut
   	 *
   	 * @return  string
   	 *
   	 * @deprecated  12.1
   	 * @since   11.1
   	 * @note    copy from joomla 2.5.4
   	 */
   	public function fetchTooltip($label, $description, &$xmlElement, $control_name = '', $name = '')
   	{
   		$output = '<label id="' . $control_name . $name . '-lbl" for="' . $control_name . $name . '"';
   		if ($description)
   		{
   			$output .= ' class="hasTip" title="' . JText::_($label) . '::' . JText::_($description) . '">';
   		}
   		else
   		{
   			$output .= '>';
   		}
   		$output .= JText::_($label) . '</label>';
   
   		return $output;
   	}
   
   	
   	/**
   	 * Method to render an xml element
   	 *
   	 * @param   string  &$xmlElement   Name of the element
   	 * @param   string  $value         Value of the element
   	 * @param   string  $control_name  Name of the control
   	 *
   	 * @return  array  Attributes of an element
   	 *
   	 * @deprecated    12.1
   	 * @since   11.1
   	 * @note    copy from joomla 2.5.4
   	 */
   	public function render(&$xmlElement, $value, $control_name = 'params')
   	{
   		$name = $xmlElement->attributes('name');
   		$label = $xmlElement->attributes('label');
   		$descr = $xmlElement->attributes('description');
   
   		//make sure we have a valid label
   		$label = $label ? $label : $name;

   		$type = $xmlElement->attributes('type');
   		$class = ' class="' .$type. '"';
   		
   		$result   = array();
   		$result[0] = $this->fetchTooltip($label, $descr, $xmlElement, $control_name, $name);
   		$result[1] = $this->fetchElement($name, $value, $xmlElement, $control_name);
   		$result[2] = $descr;
   		$result[3] = $label;
   		$result[4] = $value;
   		$result[5] = $name;

   		return $result;
   	}

   }
}
// Else: Default Joomla 1.5
else {
   jimport( 'joomla.html.parameter.element' );
   class MultisitesElement extends JElement
   {
   	function getAttribute( $node, $name)
   	{
   	   $value = $node->attributes( $name);
   	   if ( !empty( $value)) {
   	      return $value;
   	   }
   	   return '';
   	}
   }
}
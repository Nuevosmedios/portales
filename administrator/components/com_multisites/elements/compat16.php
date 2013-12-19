<?php
// file: compat16.php.
// copyright : (C) 2008-2012 Edwin2Win sprlu - all right reserved.
// author: www.jms2win.com - info@jms2win.com
/* license: 
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
*/
?><?php


defined('JPATH_BASE') or die();

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

}
protected function getInput()
{
$control_name = str_replace( '['.$this->fieldname.']', '', $this->name);
$name = $this->fieldname;
$value = &$this->value;
$node = &$this->element;

return $this->fetchElement( $name, $value, $node, $control_name);
}

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

public function render(&$xmlElement, $value, $control_name = 'params')
{
$name = $xmlElement->attributes('name');
$label = $xmlElement->attributes('label');
$descr = $xmlElement->attributes('description');

$label = $label ? $label : $name;
$type = $xmlElement->attributes('type');
$class = ' class="' .$type. '"';
$result = array();
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
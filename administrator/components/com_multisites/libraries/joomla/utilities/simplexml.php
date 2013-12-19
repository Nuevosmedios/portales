<?php
// file: simplexml.php.
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

defined('JPATH_PLATFORM') or die;

class JSimpleXML extends JObject
{

private $_parser = null;

public $document = null;

private $_stack = array();

public function __construct($options = null)
{


if (! function_exists('xml_parser_create'))
{

return false;
}

$this->_parser = xml_parser_create('');

xml_set_object($this->_parser, $this);
xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, 0);
if (is_array($options))
{
foreach ($options as $option => $value)
{
xml_parser_set_option($this->_parser, $option, $value);
}
}

xml_set_element_handler($this->_parser, '_startElement', '_endElement');
xml_set_character_data_handler($this->_parser, '_characterData');
}

public function loadString($string, $classname = null)
{


$this->_parse($string);
return true;
}

public function loadFile($path, $classname = null)
{



if (!file_exists($path))
{
return false;
}

$xml = trim(file_get_contents($path));
if ($xml == '')
{
return false;
}
else
{
$this->_parse($xml);
return true;
}
}

public function importDOM($node, $classname = null)
{


return false;
}

public function getParser()
{


return $this->_parser;
}

public function setParser($parser)
{


$this->_parser = $parser;
}

protected function _parse($data = '')
{



if (!xml_parse($this->_parser, $data))
{
$this->_handleError(
xml_get_error_code($this->_parser), xml_get_current_line_number($this->_parser),
xml_get_current_column_number($this->_parser)
);
}

xml_parser_free($this->_parser);
}

protected function _handleError($code, $line, $col)
{


JError::raiseWarning('SOME_ERROR_CODE', 'XML Parsing Error at ' . $line . ':' . $col . '. Error ' . $code . ': ' . xml_error_string($code));
}

protected function _getStackLocation()
{


$return = '';
foreach ($this->_stack as $stack)
{
$return .= $stack . '->';
}
return rtrim($return, '->');
}

protected function _startElement($parser, $name, $attrs = array())
{



$count = count($this->_stack);
if ($count == 0)
{

$classname = get_class($this) . 'Element';
$this->document = new $classname($name, $attrs);

$this->_stack = array('document');
}

else
{

$parent = $this->_getStackLocation();

eval('$this->' . $parent . '->addChild($name, $attrs, ' . $count . ');');

eval('$this->_stack[] = $name.\'[\'.(count($this->' . $parent . '->' . $name . ') - 1).\']\';');
}
}

protected function _endElement($parser, $name)
{



array_pop($this->_stack);
}

protected function _characterData($parser, $data)
{



$tag = $this->_getStackLocation();

eval('$this->' . $tag . '->_data .= $data;');
}
}

class JSimpleXMLElement extends JObject
{

public $_attributes = array();

public $_name = '';

public $_data = '';

public $_children = array();

public $_level = 0;

public function __construct($name, $attrs = array(), $level = 0)
{



$this->_attributes = array_change_key_case($attrs, CASE_LOWER);

$this->_name = strtolower($name);

$this->_level = $level;
}

public function name()
{


return $this->_name;
}

public function attributes($attribute = null)
{


if (!isset($attribute))
{
return $this->_attributes;
}
return isset($this->_attributes[$attribute]) ? $this->_attributes[$attribute] : null;
}

public function data()
{


return $this->_data;
}

public function setData($data)
{


$this->_data = $data;
}

public function children()
{


return $this->_children;
}

public function level()
{


return $this->_level;
}

public function addAttribute($name, $value)
{



$this->_attributes[$name] = $value;
}

public function removeAttribute($name)
{


unset($this->_attributes[$name]);
}

public function addChild($name, $attrs = array(), $level = null)
{




if (!isset($this->$name))
{
$this->$name = array();
}

if ($level == null)
{
$level = ($this->_level + 1);
}

$classname = get_class($this);
$child = new $classname($name, $attrs, $level);

$this->{$name}[] = &$child;

$this->_children[] = &$child;

return $child;
}

public function removeChild(&$child)
{


$name = $child->name();
for ($i = 0, $n = count($this->_children); $i < $n; $i++)
{
if ($this->_children[$i] == $child)
{
unset($this->_children[$i]);
}
}
for ($i = 0, $n = count($this->{$name}); $i < $n; $i++)
{
if ($this->{$name}[$i] == $child)
{
unset($this->{$name}[$i]);
}
}
$this->_children = array_values($this->_children);
$this->{$name} = array_values($this->{$name});
unset($child);
}

public function getElementByPath($path)
{


$tmp = &$this;
$parts = explode('/', trim($path, '/'));
foreach ($parts as $node)
{
$found = false;
foreach ($tmp->_children as $child)
{
if (strtoupper($child->_name) == strtoupper($node))
{
$tmp = &$child;
$found = true;
break;
}
}
if (!$found)
{
break;
}
}
if ($found)
{
return $tmp;
}
return false;
}

public function map($callback, $args = array())
{


$callback($this, $args);

if ($n = count($this->_children))
{
for ($i = 0; $i < $n; $i++)
{
$this->_children[$i]->map($callback, $args);
}
}
}

public function toString($whitespace = true)
{



if ($whitespace)
{
$out = "\n" . str_repeat("\t", $this->_level) . '<' . $this->_name;
}
else
{
$out = '<' . $this->_name;
}

foreach ($this->_attributes as $attr => $value)
{
$out .= ' ' . $attr . '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"';
}
 if (empty($this->_children) && empty($this->_data))
{
$out .= " />";
}

else
{

if (!empty($this->_children))
{

$out .= '>';

foreach ($this->_children as $child)
{
$out .= $child->toString($whitespace);
}

if ($whitespace)
{
$out .= "\n" . str_repeat("\t", $this->_level);
}
}

elseif (!empty($this->_data))
$out .= '>' . htmlspecialchars($this->_data, ENT_COMPAT, 'UTF-8');

$out .= '</' . $this->_name . '>';
}

return $out;
}
}

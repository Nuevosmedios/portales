<?php
// file: treesearch.php.
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


defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.filesystem.path');




class Jms2WinTreeSearch
{

var $_c = null; 




function &getInstance()
{
static $instance;
if (!is_object($instance))
{
$instance = new Jms2WinTreeSearch();
}
return $instance;
}

function Jms2WinTreeSearch( $c = null, $_parent = null)
{
$this->_c = $c;
$this->_parent = $_parent;
}


function add( $key, &$value)
{
$c = substr( $key, 0, 1);

if ( $c == '%') {
if ( empty( $this->_c )) {
return false; 
}
if ( !isset( $this->_valuesType)) {
$this->_valuesType = array();
}
if ( !isset( $this->_valuesType[1])) {
$this->_valuesType[1] = array();
}
$this->_valuesType[1][] = $value; 
return true;
}

if ( !isset( $this->_children)) {
$this->_children = array();
}
if ( !isset( $this->_children[$c])) {
$this->_children[$c] = new Jms2WinTreeSearch( $c, $this);
}
$remaining = substr( $key, 1);
if ( !empty( $remaining)) {
return $this->_children[$c]->add( $remaining, $value);
}
else {
if ( !isset( $this->_children[$c]->valuesType)) {
$this->_children[$c]->_valuesType = array();
}
if ( !isset( $this->_children[$c]->_valuesType[0])) {
$this->_children[$c]->_valuesType[0] = array();
}
$this->_children[$c]->_valuesType[0][] = $value; 
return true;
}
return false;
}


function & _getKey( $key, & $lastValue)
{

if ( !empty( $this->_valuesType[1])) {
$lastValue = &$this;
}
$c = substr( $key, 0, 1);
if ( isset( $this->_children[$c])) {
$remaining = substr( $key, 1);
if ( !empty( $remaining)) {
return $this->_children[$c]->_getKey( $remaining, $lastValue);
}
else {

if ( !empty( $this->_children[$c]->_valuesType[0])) {
$lastValue = $this->_children[$c];
}
}
}
return $lastValue;
}


function & getKey( $key)
{
$null = null;
return $this->_getKey( $key, $null);
}


function & getKeyString( $aSolution)
{
$results = array();
$key = '';
$node = $aSolution;
do {
$key = $node->_c . $key;
$node = $node->_parent;
} while ( !empty( $node));

if ( !empty( $aSolution->_valuesType[1])) {
$results[] = $key . '%';
}

if ( !empty( $aSolution->_valuesType[0])) {
$results[] = $key;
}
return $results;
}
} 

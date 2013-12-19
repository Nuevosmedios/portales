<?php
// file: lettertree.php.
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


if( !defined( '_JEXEC') && !defined( '_EDWIN2WIN_') ) {
die( 'Restricted access' );
}




if ( !class_exists( 'MultisitesLetterTree')) {
class MultisitesLetterTree
{


static function getLetterTreeDir( $id)
{
static $instances;
if (!isset( $instances )) {
$instances = array();
}
if ( empty( $instances[$id]))
{
$str = $id;
$concate_dot = false;
$letter_tree = array();
while( strlen( $str)> 0) {

$c = substr( $str, 0, 1);
if ( $c == '.') {

if ( empty( $letter_tree)) {

$letter_tree[] = $c;
}

else {
$letter_tree[count( $letter_tree)-1] .= $c;
}
$concate_dot = true;
}
else {
if ( $concate_dot) {
$letter_tree[count( $letter_tree)-1] .= $c;
}
else {
$letter_tree[] = $c;
}
$concate_dot = false;
}

$str = substr( $str, 1);
}
$instances[$id] = implode( DIRECTORY_SEPARATOR, $letter_tree);
}
return $instances[$id];
}
} 
}

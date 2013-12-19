<?php
// file: patchloader.php.
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

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );


function jms2win_loadPatch( $fname, $dir=null)
{
if ( empty( $dir)) {
$filename = dirname( __FILE__) .DS. $fname;
}
else {
$filename = $dir .DS. $fname;
}
$content = file_get_contents( $filename);
return $content;
}


function jms2win_removePatch( $content, $replaces = null)
{
$result = '';
$prev_pos = 0;
$i = 0;
while( true) {

$p1 = strpos( $content, '//_jms2win_begin', $prev_pos);
if ( $p1 === false) {
$result .= substr( $content, $prev_pos);
break;
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);

$p2 = strpos( $content, '//_jms2win_end', $p1);
if ( $p2 === false) {
$result .= substr( $content, $prev_pos);
break;
}

for ( $p3=$p2; $content[$p3] != "\n"; $p3++);
$result .= substr( $content, $prev_pos, $p0-$prev_pos+1);

$p4 = strpos( $content, '/*_jms2win_undo', $p3);
if ( $p4 === false) {
$undoPresent = false;

if ( !empty( $replaces) && !empty( $replaces[$i])) {
$result .= $replaces[$i];
}
$prev_pos = $p3+1;
}
else {

$nextBegin = strpos( $content, '//_jms2win_begin', $p3);
$undoPresent = true;
if ( $nextBegin === false) {}
else if ( $nextBegin<$p4) {
$undoPresent = false;
}
}

if ( $undoPresent == false) {

if ( !empty( $replaces) && !empty( $replaces[$i])) {
$result .= $replaces[$i];
}
$prev_pos = $p3+1;
}
else {
$p7 = strpos( $content, '_jms2win_undo */', $p4);
if ( $p7 === false) {

if ( !empty( $replaces) && !empty( $replaces[$i])) {
$result .= $replaces[$i];
}
$prev_pos = $p3+1;
}
else {

for ( $p5=$p4; $content[$p5] != "\n"; $p5++);

for ( $p6=$p7; $p6 > 0 && $content[$p6] != "\n"; $p6--);
$result .= substr( $content, $p5, $p6-$p5+1);

for ( $p8=$p7; $content[$p8] != "\n"; $p8++);
$prev_pos = $p8+1;
}
}
}
return $result;
}


function jms2win_getPatchVersion( $content, $occurence = null)
{
$result = '';
$prev_pos = 0;
$i = 0;
while( true) {
$p1 = strpos( $content, '//_jms2win_begin', $prev_pos);
if ( $p1 === false) {
break;
}
if ( is_null( $occurence) || $i==$occurence) {

for ( $p2=$p1+16; $content[$p2] != " " && $content[$p2] != "\t"; $p2++);

for ( $p3=$p2; $content[$p3] != "\n"; $p3++);

$result = trim( substr( $content, $p2, $p3-$p2));
return $result;
}
$prev_pos = $p1+16;
}
return '';
}
?>
<?php
// file: install.language_j16.php.
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


defined( '_JEXEC' ) or die();
jimport('joomla.filesystem.folder');



class MultisitesConvertLanguage {

function content( $filename) {
$lines = array();
$search = array( '(',
')',
'{',
'}',
'[',
']',
'"'
);
$replace = array( '&#40;',
'&#41;',
'&#123;',
'&#125;',
'&#91;',
'&#93;',
'"_QQ_"'
);
$skip_keys = array( 'NO',
'YES',
'SAVE & NEW',
'BLACK LIST (DEFAULT)',
'ITEM(S) SENT TO THE TRASH',
'ITEM(S) SUCCESSFULLY ARCHIVED',
'ITEM(S) SUCCESSFULLY COPIED TO SECTION',
'ITEM(S) SUCCESSFULLY MOVED TO SECTION',
'ITEM(S) SUCCESSFULLY MOVED TO UNCATEGORIZED',
'ITEM(S) SUCCESSFULLY PUBLISHED',
'ITEM(S) SUCCESSFULLY UNPUBLISHED',
'ITEM(S) SUCCESSFULLY UNARCHIVED',
'THANKS FOR RATING!',
'YOU ALREADY RATED THIS ARTICLE TODAY!'
);
$fd = @fopen( $filename, "r");
if ( !$fd) {
return;
}
while( !feof( $fd)) {
$line = fgets( $fd);
if ( !empty( $line)) {
$line = trim( $line);

if ( substr( $line, 0, 1) == ';') {}

else if ( substr( $line, 0, 1) == '#') {

$line = ';' . substr( $line, 1);
}
else {

$pos = strpos( $line, '=');
if ( $pos === false) {}
else {
$key = trim( substr( $line, 0, $pos));
if ( in_array( $key, $skip_keys)) {
continue;
}

$pos++;
$value = trim( substr( $line, $pos));
if ( !empty( $value)) {

if ( substr( $value, 0, 1) == '"' && substr( $value, -1) == '"') {

$value = rtrim( $value, '"');
$value = ltrim( $value, '"');
$addquote = '"';
}
else {
$addquote = '"';
}
$str = str_replace( $search, $replace, $value);
$line = substr( $line, 0, $pos)
. $addquote
. $str
. $addquote
;
}
}
}
}
$lines[] = $line;
}
fclose( $fd);

$result = implode( "\n", $lines);

jimport('joomla.filesystem.file');
JFile::write( $filename, $result);
}

function files() {

if ( empty( $GLOBALS['installManifest'])) {
return;
}
$manifest = $GLOBALS['installManifest'];
if ( !empty( $manifest->languages) && !empty( $manifest->languages->language)) {
foreach( $manifest->languages->language as $language_file) {
$filename = JPath::clean( JPATH_ROOT.DS.'language'.DS.$language_file);
MultisitesConvertLanguage::content( $filename);
}
}
if ( !empty( $manifest->administration) && !empty( $manifest->administration->languages)) {
foreach( $manifest->administration->languages->language as $language_file) {
$filename = JPath::clean( JPATH_ADMINISTRATOR.DS.'language'.DS.$language_file);
MultisitesConvertLanguage::content( $filename);
}
}
}
} 
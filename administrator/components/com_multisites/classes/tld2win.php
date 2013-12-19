<?php
// file: tld2win.php.
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

require_once( dirname( __FILE__) .DS. 'utils.php');




class TLD2Win
{
var $_tld_rows = null;

function &getInstance()
{
static $instance;
if (!is_object($instance))
{
$instance = new TLD2Win();
}
return $instance;
}


function _processRow( $row)
{
$result = array();
$ncol = count( $row);
if ( $ncol > 0) {
$tld1 = strtolower( $row[0]);
if ( $ncol > 1) {
if ( !empty( $row[1])) {
$type = strtoupper( substr( $row[1], 0, 1));
}
if ( !empty( $type)) {

if ( $type == 'A') {
if ( $ncol > 2) {

$dl = '# ' . $row[2];
}
else {
$dl = '#';
}
}

else if ( $type == 'B') {
if ( $ncol > 2) {
$dl = $row[2];
}
else {
$dl = null; 
}
}

else if ( $type == 'C') {
if ( $ncol > 2) {
$dl = '# ' . $row[2];
}
else {
$dl = '#';
}
}

else if ( $type == 'D') {

if ( $ncol > 2) {
$dl = $tld1 . ' ' . $row[2];
}
else {
$dl = $tld1;
}
}

else {

if ( $ncol > 2) {
$dl = $tld1 . ' ' . $row[2];
}
else {
$dl = $tld1;
}
}
}
}
}
if ( !empty( $dl)) {
$tldsuffix = array();
$domlist = explode( ' ', $dl);
foreach( $domlist as $dom) {
if ( !empty( $dom )) {
$d = ltrim( $dom, '.');
$tld3rd = explode( '.', $d);
$n = count( $tld3rd);
if ( $n >= 2) {
if ( strtolower( $tld3rd[$n-1] == $tld1)) {
array_pop( $tld3rd);
}
else {
echo "Error on " . var_export( $row, true) . '<br />';
echo "- TDL1=[$tld1] TLD3 = ". var_export( $tld3rd, true) . '<br />';
}
}
else if ( $n == 1) {
if ( strtolower( $tld3rd[$n-1]) == $tld1) {
array_pop( $tld3rd);
}

else if ( $tld3rd[$n-1]=='#') {}
else {
echo "Error 2 on " . var_export( $row, true) . '<br />';
echo "-no2 TDL1=[$tld1] TLD3 = ". var_export( $tld3rd, true) . '<br />';
}
}
else {
echo "Error no3 - Unexpected empty tld3rd on " . var_export( $row, true) . '<br />';
}
if ( count( $tld3rd) == 1) {
$str = $tld3rd[0];
$tldsuffix[$str] = '#';
}
else if ( count( $tld3rd) > 1) {
if ( $type == 'D') {
if ( count( $tld3rd) == 2) {
if ( !empty( $tldsuffix[$tld3rd[1]])) {
if ( is_array( $tldsuffix[$tld3rd[1]])) {
$tldsuffix[$tld3rd[1]][$tld3rd[0]] = '#';
}
else {
$tldsuffix[$tld3rd[1]] = array( $tldsuffix[$tld3rd[1]]=>$tldsuffix[$tld3rd[1]], $tld3rd[0] => '#');
}
}
else {
$tldsuffix[$tld3rd[1]] = array($tld3rd[0]=>'#');
}
}
else {
echo "Error no5 - Unexpected tld3rd size on " . var_export( $row, true) . '<br />';
echo var_export( $tld3rd, true) . '<br />';
}
}
else {
echo "Error no4 - Unexpected tld3rd size on " . var_export( $row, true) . '<br />';
echo var_export( $tld3rd, true) . '<br />';
}
}
}
}
}
if ( !empty( $tld1)) {
if ( !empty( $tldsuffix)) {
$result[$tld1] = $tldsuffix;
}
else {
$result[$tld1] = '#';
}
}
return $result;
}


function loadtab( $filename)
{
$handle = fopen( $filename, "r");
if ($handle ) {
$this->_tld_rows = array();
for ( $i=0; !feof($handle); $i++) {
$str = fgets($handle, 4096);
$line = trim( $str);

if ( empty( $line) || substr( $line, 0, 1) == ';' ) {

continue;
}

$row = array();
$fields = explode( "\t", $line);
$j = 0;
foreach( $fields as $field) {
$row[$j] = trim( str_replace( '"', '', $field));
$j++;
}
$tld_rec = $this->_processRow( $row);
$row['_tlds'] = $tld_rec;
$this->_tld_rows[strtolower($row[0])] = $row;
}
fclose($handle);
}
}


function exportTLDs( $filename)
{
$content = "<?php\n"
. '$tlds = array( ' . "\n"
. MultisitesUtils::CnvArray2Str( '', $this->_tld_rows) . "\n"
. ");\n"
. "?>"
;
$fp = fopen( $filename, "w");
fputs( $fp, $content);
fclose( $fp);
}


function importTLDs( $filename=null, $forceReload=false)
{

if ( !empty( $this->_tld_rows) && !$forceReload) {
return;
}

if ( empty( $filename)) {
$filename = dirname( __FILE__) .DIRECTORY_SEPARATOR. 'tld2win.data.php';
}

@include $filename;
if ( isset( $tlds)) {
$this->_tld_rows = &$tlds;
}
}


function splitHost( $host)
{

$parts = explode( '.', $host);
if ( empty( $parts)) {
return $parts;
}

if ( defined( 'MULTISITES_TLD_PARSING') && MULTISITES_TLD_PARSING == false) {
return $parts;
}
if ( empty( $this->_tld_rows)) {

$this->importTLDs();

if ( empty( $this->_tld_rows)) {
return $parts;
}
}
$tlds = &$this->_tld_rows;
$result = -1; 
$n = count( $parts);
for ( $i=$n-1; $i>=0; $i--) {
$name = strtolower( $parts[$i]);

if ( empty( $tlds[ $name])) {

break;
}
else {
if ( is_array( $tlds[ $name])) {
$rec = &$tlds[ $name];

if ( !empty( $rec['_tlds'][$name])) {
$tlds = &$rec['_tlds'][$name];
}
else {
$tlds = &$rec;
}

if ( !empty( $tlds['#'])) {

$result = $i;
}
}

else {

if ( $tlds[ $name] == '#') {
$result = $i;
}
break;
}
}
}

if ( $result < 0) {

return $parts;
}

$suffix = '';

while( count( $parts) > ($result+1)) {
$suffix = '.' . array_pop( $parts) . $suffix;
}

$parts[$result] .= $suffix;
return $parts;
}
} 

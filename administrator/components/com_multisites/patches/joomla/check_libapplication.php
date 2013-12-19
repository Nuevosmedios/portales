<?php
// file: check_libapplication.php.
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


function jms2win_checkLibApplication( $model, $file)
{
$filename = JPath::clean( JPATH_ROOT.DS.$file);
if ( !file_exists( $filename)) {
return '[NOK]|File Not Found';
}
$str = file_get_contents( $filename);
$result = "";
$rc = '[OK]';

$pos = strpos( $str, 'MULTISITES_COOKIE_DOMAINS');
if ($pos === false) {
$rc = '[NOK]';
$result .= JText::_( 'The single sign-in patch for sub-domain is not present');
$result .= '|[ACTION]';
$result .= '|Replace 2 lines by 2x15 lines to accept that sub-domain share the same session information for a single sign-in';
$result .= '|Replace 4 lines by 35 lines to add the single sign-in rescue session restore when the server does not restore the session data on sub-domain.';
}
else {

$pos = strpos( $str, '_jms2win_restore_session_objects_');
if ($pos === false) {
$rc = '[NOK]';
$result .= JText::_( 'The single sign-in patch to restore sub-domain session for some platform is not present');
$result .= '|[ACTION]';
$result .= '|Replace 4 lines by 35 lines to add the single sign-in rescue session restore when the server does not restore the session data on sub-domain.';
}
else {

$sessions_fork = strpos( $str, 'session->fork');
if ( $sessions_fork === false) {}
else {

$posCreateSess = strpos( $str, 'this->_createSession', $sessions_fork);
if ($posCreateSess === false || ($posCreateSess-$sessions_fork) > 100) {

$pos = strpos( $str, '_jms2win_fix_j1_5_16_');
if ($pos === false) {
$rc = '[NOK]';
$result .= JText::_( 'Apply the fix concerning the bug introduced in Joomla 1.5.16 and that is described in http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=20221.');
$result .= '|[ACTION]';
$result .= '|Add 1 line to properly manage the session and allow login into joomla.';
}
}
if (($posCreateSess-$sessions_fork) <= 100) {

$pos = strpos( $str, '_jms2win_fix_j1_5_22_');
if ($pos === false) {
$rc = '[NOK]';
$result .= JText::_( 'Fix a bug introduced in Joomla 1.5.16 and higher that is still not fixed by joomla.');
$result .= '|'.JText::_( 'See bug tracker in http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=20221.');
$result .= '|[ACTION]';
$result .= '|Rolback to Joomla 1.5.15 implementation.';
$result .= '|Deleted the 3 lines that duplicates the session and force the users to relogin in each web site.';
}
}
}
}
}
return $rc .'|'. $result;
}


function jms2win_actionLibApplication( $model, $file)
{
include_once( dirname(__FILE__) .DS. 'patchloader.php');
$patchStr_1 = jms2win_loadPatch( 'patch_libapplication_1.php');
if ( $patchStr_1 === false) {
return false;
}
$patchStr_2 = jms2win_loadPatch( 'patch_libapplication_2.php');
if ( $patchStr_2 === false) {
return false;
}
$patchStr_3 = jms2win_loadPatch( 'patch_libapplication_3.php');
if ( $patchStr_3 === false) {
return false;
}
$patchStr_4 = jms2win_loadPatch( 'patch_libapplication_4.php');
if ( $patchStr_4 === false) {
return false;
}

$filename = JPath::clean( JPATH_ROOT.DS.$file);
$content = file_get_contents( $filename);
if ( $content === false) {
return false;
}

$content = jms2win_removePatch( $content);





$isForkPresent = false;
$plogin = strpos( $content, 'function login');
if ( $plogin === false) {
return false;
}

$isForkPresent = false;
$pb = strpos( $content, 'session->fork', $plogin);
if ( $pb === false) {}
else {
$isForkPresent = true;
$psession = strpos( $content, 'JFactory::getSession', $plogin);
if ( $psession === false) {}
else {
$pb = $psession;
}

for ( $pa=$pb; $pa > 0 && $content[$pa] != "\n"; $pa--);
$pa++;
$state = 0;
for ( $popencomment = $pa-2; $popencomment>0; $popencomment--) {
$c = $content[$popencomment];
if ( $c == "\n" || $c == "\r" || $c == "\t" || $c == " ") {}
else {

if ( $state == 0) {
if ( $c == '*') {
$state = 1;
}
else {

break;
}
}

else if ( $state == 1) {
if ( $c == '/') {



for ( $pa=$popencomment; $pa > 0 && $content[$pa] != "\n"; $pa--);
$pa++;
$state = 9;
break;
}
else {

break;
}
}
else {

break;
}
}
}

for ( $pc=$pb; $content[$pc] != "\n"; $pc++);
$pc++;
$pe = strpos( $content, 'this->_createSession', $pc);
if ( $pe === false) {}

else if ( ($pe-$pc) < 100) {

for ( $pf=$pe; $content[$pf] != "\n"; $pf++);
$pf++;

$p1522 = strpos( $content, '_jms2win_fix_j1_5_22_', $plogin);
if ( $p1522 === false) {

$pclosecomment = strpos( $content, '*/', $pf);
if ( $pclosecomment === false) {}
else {

$strCom = trim( substr( $content, $pf, $pclosecomment-$pf));

if ( empty( $strCom)) {


for ( $pf=$pclosecomment+2; $content[$pf] != "\n"; $pf++);
$pf++;
}
}
}
else {

$isForkPresent = false;
}
}
if ( empty( $pf)) { $pf = $pc; }
}

$p1 = strpos( $content, 'setcookie');
if ( $p1 === false) {
return false;
}

for ( $p0=$p1; $p0 > 0 && $content[$p0] != "\n"; $p0--);
$p0++;

for ( $p2=$p1; $content[$p2] != "\n"; $p2++);
$p2++;

$p4 = strpos( $content, 'setcookie', $p2);
if ( $p4 === false) {
return false;
}

for ( $p3=$p4; $p3 > 0 && $content[$p3] != "\n"; $p3--);
$p3++;

for ( $p5=$p4; $content[$p5] != "\n"; $p5++);
$p5++;

$p6 = strpos( $content, '&_createSession', $p5);
if ( $p6 === false) {
return false;
}

$p8 = strpos( $content, '$storage->load', $p6);
if ( $p8 === false) {
return false;
}

for ( $p7=$p8; $p7 > 0 && $content[$p7] != "\n"; $p7--);
$p7++;

$p9 = strpos( $content, '}', $p8);
if ( $p8 === false) {
return false;
}

for ( $p10=$p9; $content[$p10] != "\n"; $p10++);
$p10++;


if ( $isForkPresent) {
$result = substr( $content, 0, $pa)
. $patchStr_4
. substr( $content, $pf, $p0-$pf)
. $patchStr_1
. substr( $content, $p2, $p3-$p2)
. $patchStr_2
. substr( $content, $p5, $p7-$p5)
. $patchStr_3
. substr( $content, $p10)
;
}
else {
$result = substr( $content, 0, $p0)
. $patchStr_1
. substr( $content, $p2, $p3-$p2)
. $patchStr_2
. substr( $content, $p5, $p7-$p5)
. $patchStr_3
. substr( $content, $p10)
;
}

jimport('joomla.filesystem.file');
if ( !JFile::write( $filename, $result)) {
return false;
}
return true;
}

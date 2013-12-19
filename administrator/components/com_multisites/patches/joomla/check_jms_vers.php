<?php
// file: check_jms_vers.php.
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


function jms2win_checkJMSVers( $model, $file)
{
jimport('joomla.filesystem.path');
$result = "";
$rc = '[OK]';
$jms_vers = MultisitesController::_getVersion();
$patchesVersion = $model->getPatchesVersion();


if ( preg_match('#([0-9]+.[0-9]*.[0-9]+).*#i',$patchesVersion,$matches)) {
$patchesVersion = $matches[1];
}

if ( (version_compare( $patchesVersion, '1.2.3') > 0)
&& (version_compare( $jms_vers, '1.2.0') < 0))
{

$rc = '[NOK]';
$result .= JText::_( 'The patch definition over 1.2.3 also require to update the JMS kernel to a version 1.2.0 or higher');
$result .= '|[ACTION]';
$result .= '|Go to JMS website to <a href="http://www.jms2win.com/get-latest-version">Get latest version</a>';
$result .= '|In case of problem, also have a look in the <a href="http://www.jms2win.com/faq#sec-122">FAQ procedure</a> to get the latest version.';
$result .= '|If you do not update the JMS kernel, you may have icons that will not be correctly displayed in JMS tool and JMS template sharing.';
$result .= '|We recommand to also update the JMS kernel to also benefit of some fixes.';
}

else if ( (version_compare( $patchesVersion, '1.2.10') >= 0)
&& (version_compare( $jms_vers, '1.2.6') < 0))
{

$rc = '[NOK]';
$result .= JText::_( 'The patch definition 1.2.10 (or higher) also require to update the JMS kernel to a version 1.2.6 or higher');
$result .= '|[ACTION]';
$result .= '|Go to JMS website to <a href="http://www.jms2win.com/get-latest-version">Get latest version</a>';
$result .= '|In case of problem, also have a look in the <a href="http://www.jms2win.com/faq#sec-122">FAQ procedure</a> to get the latest version.';
$result .= '|If you do not update the JMS kernel, you will not be able to install some patches relative to Single Sign-In for sub-domains.';
$result .= '|We also recommand to update the JMS kernel to benefit of other fixes and enhancement. See FAQ change history for more details.';
}

else if ( (version_compare( $patchesVersion, '1.2.35') >= 0)
&& (version_compare( $jms_vers, '1.2.30') < 0))
{

$rc = '[NOK]';
$result .= JText::_( 'The patch definition 1.2.35 (or higher) also require to update the JMS kernel to a version 1.2.30 or higher');
$result .= '|[ACTION]';
$result .= '|Go to JMS website to <a href="http://www.jms2win.com/get-latest-version">Get latest version</a>';
$result .= '|In case of problem, also have a look in the <a href="http://www.jms2win.com/faq#sec-122">FAQ procedure</a> to get the latest version.';
$result .= '|If you do not update the JMS kernel, you will not benefit of the new JMS internal structure to allow creating several thousand (and perhaps one million or more) of slave site from the front-end.';
$result .= '|We also recommand to update the JMS kernel to benefit of other fixes and enhancement. See FAQ change history for more details.';
}

else if ( (version_compare( $patchesVersion, '1.2.69') >= 0)
&& (version_compare( $jms_vers, '1.2.65') < 0))
{

$rc = '[NOK]';
$result .= JText::_( 'The patch definition 1.2.69 (or higher) also require to <span style="color:red; font-size: 14px; font-weight: bold;">update the JMS kernel with the version 1.2.65 or higher</span>');
$result .= '|[ACTION]';
$result .= '|Go to JMS website to <a href="http://www.jms2win.com/get-latest-version" target="_blank">Get latest version</a>';
$result .= '|In case of problem, also have a look in the <a href="http://www.jms2win.com/faq#sec-122" target="_blank">FAQ procedure</a> to get the latest version.';
$result .= '|If you do not update the JMS kernel, you will not be able to apply the paches.';
$result .= '|<span style="color:red;">Do not try installing the patches</span>, the result will be to uninstall all the patches and you will not be able to re-install the patches or come back in the current situation.';
$result .= '|We also recommand to update the JMS kernel to benefit of other fixes and enhancement. <a href="http://www.jms2win.com/en/faq/change-history-v12x" target="_blank">See FAQ change history for more details.</a>';
}

$manifest = 'administrator/components/com_multisites/install.xml';
$filename = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'backup' .DS. $manifest);
if ( JFile::exists( $filename)) {
JFile::delete( $filename);
}
$filename = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'backup_on_install' .DS. $manifest);
if ( JFile::exists( $filename)) {
JFile::delete( $filename);
}

$manifest = 'administrator/components/com_multisites/extension.xml';
$filename = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'backup' .DS. $manifest);
if ( JFile::exists( $filename)) {
JFile::delete( $filename);
}
$filename = JPath::clean( JPATH_MUTLISITES_COMPONENT .DS. 'backup_on_install' .DS. $manifest);
if ( JFile::exists( $filename)) {
JFile::delete( $filename);
}
return $rc .'|'. $result;
}

function jms2win_actionJMSVers( $model, $file)
{
return true;
}

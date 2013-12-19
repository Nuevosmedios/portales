<?php
// file: wordpress.php.
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


defined( '_JEXEC' ) or die( 'Restricted access' );

if ( file_exists( JPATH_ADMINISTRATOR.'/components/com_wordpress')) {
jimport('joomla.event.dispatcher');
jimport('joomla.plugin.plugin');



if ( !class_exists( 'plgMultisitesWordpress')) {
class plgMultisitesWordpress extends JPlugin
{

public function __construct(&$subject, $config = array())
{
parent::__construct($subject, $config);
}

protected function _updateWP_Options( $sourcedb, $targetdb, $toConfig)
{

$like = str_replace('_' , '\_', $targetdb->replacePrefix( '#__wp_options'));
$query = "SHOW TABLE STATUS LIKE '$like'";
$targetdb->setQuery( $query );
$obj = $targetdb->loadObject();
if ( empty( $obj)) {
return;
}

$fromPrefix = $sourcedb->getPrefix();
$toPrefix = $targetdb->getPrefix();
$fromPrefixLen = strlen( $fromPrefix)+1;

if ( !empty( $obj->Comment) && strtoupper( substr($obj->Comment, 0, 4)) == 'VIEW') {

$query = str_replace( array( '{fromPrefix}', '{fromPrefixLen}'),
array( $fromPrefix, $fromPrefixLen),
"UPDATE #__wp_options SET option_name = CONCAT( '#__', SUBSTR( option_name, {fromPrefixLen})) WHERE option_name LIKE '{fromPrefix}%'"
);
}

else {

$query = str_replace( array( '{fromPrefix}', '{fromPrefixLen}', '{toPrefix}'),
array( $fromPrefix, $fromPrefixLen, $toPrefix, ),
"UPDATE #__wp_options SET option_name = CONCAT( '{toPrefix}', SUBSTR( option_name, {fromPrefixLen})) WHERE option_name LIKE '{fromPrefix}%'"
);
}

$targetdb->setQuery( $query );
$targetdb->query();
}

protected function _updateWP_usermeta( $sourcedb, $targetdb, $toConfig)
{

$like = str_replace('_' , '\_', $targetdb->replacePrefix( '#__wp_usermeta'));
$query = "SHOW TABLE STATUS LIKE '$like'";
$targetdb->setQuery( $query );
$obj = $targetdb->loadObject();
if ( empty( $obj)) {
return;
}

$fromPrefix = $sourcedb->getPrefix();
$toPrefix = $targetdb->getPrefix();
$fromPrefixLen = strlen( $fromPrefix)+1;

if ( !empty( $obj->Comment) && strtoupper( substr($obj->Comment, 0, 4)) == 'VIEW') {

$query = str_replace( array( '{fromPrefix}', '{fromPrefixLen}'),
array( $fromPrefix, $fromPrefixLen),
"UPDATE #__wp_usermeta SET meta_key = CONCAT( '#__', SUBSTR( meta_key, {fromPrefixLen})) WHERE meta_key LIKE '{fromPrefix}%'"
);
}

else {

$query = str_replace( array( '{fromPrefix}', '{fromPrefixLen}', '{toPrefix}'),
array( $fromPrefix, $fromPrefixLen, $toPrefix, ),
"UPDATE #__wp_usermeta SET meta_key = CONCAT( '{toPrefix}', SUBSTR( meta_key, {fromPrefixLen})) WHERE meta_key LIKE '{fromPrefix}%'"
);
}

$targetdb->setQuery( $query );
$targetdb->query();
}

public function onAfterCopyDB( $sourcedb, $targetdb, $toConfig)
{
$this->_updateWP_Options( $sourcedb, $targetdb, $toConfig);
$this->_updateWP_usermeta( $sourcedb, $targetdb, $toConfig);
return true;
}
} 

if ( class_exists( 'jEventDispatcher')) {
JEventDispatcher::getInstance()->register( 'onAfterCopyDB', 'plgMultisitesWordpress');
}
else {
JDispatcher::getInstance()->register( 'onAfterCopyDB', 'plgMultisitesWordpress');
}
} 
} 

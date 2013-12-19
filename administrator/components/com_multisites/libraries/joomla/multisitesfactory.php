<?php
// file: multisitesfactory.php.
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



class MultisitesFactory extends JFactory
{


static function &setDBO( $new_db)
{
if ( version_compare( JVERSION, '1.6') >= 0) {

$sav_db = JFactory::$database;
JFactory::$database = $new_db;
}
else {

$jdb =& JFactory::getDBO();
$sav_db = $jdb;

$jdb = $new_db;
}
return $sav_db;
}


static function &setConfig( $new_config)
{
if ( version_compare( JVERSION, '1.6') >= 0) {

$sav_config = JFactory::$config;
JFactory::$config = $new_config;
}
else {

$config =& JFactory::getConfig();
$sav_config = $config;

$config = $new_config;
}
return $sav_config;
}
} 

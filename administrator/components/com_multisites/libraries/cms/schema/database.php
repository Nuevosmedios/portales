<?php
// file: database.php.
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


defined('_JEXEC') or die;
require_once( dirname( __FILE__).DS.'changeset.php');



class MultisitesSchemaDatabase
{


static function getSchemas( $filters = null, $sites = array())
{
$changeSet = new MultisitesSchemaChangeset( null, null, array(), false);
$results = $changeSet->getFilteredFolders( $filters, $sites);
return $results;
}


static function checkSchema( $site, $db, $dmrow, $enteredvalues)
{
$results = array();
$changeSet = new MultisitesSchemaChangeset( $db, $dmrow, $enteredvalues, true, array( $site));
$results['errors'] = $changeSet->check();
$results['status'] = $changeSet->getStatus();
$results['selectedFolder'] = $changeSet->getSelectedFolder();
$results['selectedFolder_Label'] = $changeSet->getSelectedFolder_Label();
return $results;
}


static function fixSchema( $site, $db, $dmrow, $enteredvalues)
{
$results = array();
$changeSet = new MultisitesSchemaChangeset( $db, $dmrow, $enteredvalues);
$changeSet->fix();
$results['status'] = $changeSet->getStatus();
$results['selectedFolder'] = $changeSet->getSelectedFolder();
$results['selectedFolder_Label'] = $changeSet->getSelectedFolder_Label();
return $results;
}


static function fixUncheckedSchema( $site, $db, $dmrow, $enteredvalues)
{
$results = array();
$changeSet = new MultisitesSchemaChangeset( $db, $dmrow, $enteredvalues);
$changeSet->fixUnchecked();
$results['status'] = $changeSet->getStatus();
$results['selectedFolder'] = $changeSet->getSelectedFolder();
$results['selectedFolder_Label'] = $changeSet->getSelectedFolder_Label();
return $results;
}
} 

<?php
// file: default_datamodel.php.
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
?><?php defined('_JEXEC') or die('Restricted access'); ?>
<?php $preprocess_datamodel = $site->preprocess_datamodel;
?>
<table class="datamodel">
<?php include( dirname(__FILE__).'/ajaxcheckdb_datamodel_thead.php'); ?>
<tbody>
<?php foreach( $preprocess_datamodel as $datamodel) {

if ( !empty( $downloadedpackagesvalues)
&& !empty( $downloadedpackagesvalues[$this->site->id][$datamodel->extension_id])
&& $downloadedpackagesvalues[$this->site->id][$datamodel->extension_id] != '[unselected]'
)
{
$downloadpackage =
$datamodel->downloadedpackages = $downloadedpackagesvalues[$this->site->id][$datamodel->extension_id];
$downloadpackage = $datamodel->downloadedpackages;
}
else {
$datamodel->downloadedpackages = '';
$downloadpackage = $default_downloadpackage;
}

if ( !empty( $schemavalues)
&& !empty( $schemavalues[$this->site->id][$datamodel->extension_id])
&& $schemavalues[$this->site->id][$datamodel->extension_id] != '[unselected]'
)
{
$schema =
$datamodel->schema = $schemavalues[$this->site->id][$datamodel->extension_id];
}
else {
$datamodel->schema = '';
$schema = $default_schema;
}

$legacySQL_action = '';
if ( !empty( $legacySQLvalues)
&& !empty( $legacySQLvalues[$this->site->id][$datamodel->extension_id])
)
{
$legacySQL = trim( $legacySQLvalues[$this->site->id][$datamodel->extension_id]);
if ( !empty( $legacySQL)) {
$legacySQL_action = ' legacysql=1;';
}
}

$legacyMode_action = '';
if ( !empty( $legacyModevalues)
&& !empty( $legacyModevalues[$this->site->id][$datamodel->extension_id])
)
{
$str = str_replace( array( '[unselected]|', '[unselected]'), '', implode( '|', $legacyModevalues[$this->site->id][$datamodel->extension_id]));
if ( !empty( $str)) {
$legacyMode_action = ' legacymode='.$str.';';
}
}

$userSQL_action = '';
if ( !empty( $userSQLvalues)
&& !empty( $userSQLvalues[$this->site->id][$datamodel->extension_id])
)
{
$userSQL = trim( $userSQLvalues[$this->site->id][$datamodel->extension_id]);
if ( !empty( $userSQL)) {
$userSQL_action = ' usersql='.base64_encode( $userSQL).';';
}
}

if ( in_array( $action, array( 'getdbinfo', 'fixDB', 'fixUncheckedDB')) && $schema == '[ignore]') {}
else if ( in_array( $action, array( 'checkDownloadJoomla', 'extractJoomla')) && $downloadpackage == '[ignore]') {}
else {

if ( empty( $this->cid) || in_array( $site->id, $this->cid)) {
$subresult_id = ' subresult_id=summary'.$this->site->id.$datamodel->extension_id.';';
$datamodel->ajaxAction = "<!-- action=$action; site_id=$site->id; tr_id=$tr_id; extension_id=$datamodel->extension_id; downloadpackage=$downloadpackage; schema=$schema;$subresult_id$legacySQL_action$legacyMode_action$userSQL_action -->";
}
}
include( dirname(__FILE__).'/ajaxcheckdb_datamodel.php');
} ?>
</tbody>
</table>

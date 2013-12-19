<?php
// file: edit_sharing.php.
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
<?php
if ( $this->ignoreUL) {}
else {
?>
<ul <?php echo $this->tree_id;?>>
<?php 
}
$dbsharing = $this->row->dbsharing;
$treeparams = $this->treeparams;
if ( !empty( $treeparams)) {
foreach( $treeparams->children() as $param) {
if ( $param->name() == 'params') {
$label = $param->attributes( 'label');
?><li id="tree_<?php echo $this->node_id++; ?>"><a href="#"><?php echo JText::_( $label); ?></a><?php echo $this->getDBSharingLevel($param); ?></li>
<?php
 }
else if ( $param->name() == 'param') {
$label = $param->attributes( 'label');
$icon = $param->attributes( 'icon');
$openicon = $param->attributes( 'openicon');
$comment = '';
if ( !empty( $icon)) {
$comment = 'icon:' . $icon;
}
if ( !empty( $openicon)) {
if ( !empty($comment)) {
$comment .= '; ';
}
$comment .= 'openicon:' . $openicon;
}
$type = $param->attributes( 'type');
if ( $type == 'checkbox') {
$name = $param->attributes( 'name');
if ( !empty($comment)) {
$comment .= '; ';
}
$checked = '';
if ( !empty( $dbsharing[$name])) {
$checked = 'checked="checked"';
}
$comment .= "beforeText:<input type=\"$type\" name=\"params[$name]\" id=\"params$name\" $checked />";
}
$description = $param->attributes( 'description');
$toolTips = '';
if ( !empty( $description)) {
if ( !empty($comment)) {
$comment .= '; ';
}
$comment .= 'toolTips:' . JText::_($description) . ';';
}
if ( !empty( $comment)) {
$comment = '<!-- ' . $comment . ' -->';
}
?>
<li id="tree_<?php echo $this->node_id++; ?>"><a href="#"><?php echo $comment . JText::_( $label); ?></a><?php echo $this->getDBSharingLevel($param); ?></li>
<?php
 }
else if ( $param->name() == 'option') {
$name = $treeparams->attributes( 'name');
$type = $treeparams->attributes( 'type');
if ( $type == 'list') {
$type = 'radio';
}
$value = $param->attributes( 'value');
$label = $param->data();
$description = $param->attributes( 'description');
$toolTips = '';
if ( !empty( $description)) {
$toolTips = 'toolTips:' . JText::_($description) . ';';
}
$checked = '';
if ( !empty( $dbsharing[$name]) && $value == $dbsharing[$name]) {
$checked = 'checked="checked"';
}
?>
<li id="tree_<?php echo $this->node_id++; ?>">
	   <a href="#"><!-- beforeText:<input type="<?php echo $type; ?>" name="params[<?php echo $name; ?>]" id="params<?php echo $name.$value; ?>" value="<?php echo $value; ?>" <?php echo $checked; ?>/>; icon:dbsharing.gif#7; textClass:mooTree_text2win; <?php echo $toolTips; ?>--><?php echo JText::_($label); ?></a>
<?php echo $this->getDBSharingLevel($param); ?>
</li>
<?php
 }
else if ( $param->name() == 'tables') {
echo $this->getDBSharingLevel($param, true);
}
else if ( $param->name() == 'table' || $param->name() == 'tableexcluded') {
$iconNbr = '5';
if ( $param->name() == 'tableexcluded') { $iconNbr = '57'; }
$label = $param->attributes( 'name');
$description = $param->attributes( 'description');
$toolTips = '';
if ( !empty( $description)) {
$toolTips = 'toolTips:' . JText::_($description) . ';';
}
?>
<li id="tree_<?php echo $this->node_id++; ?>">
	   <a href="#"><!-- icon:dbsharing.gif#<?php echo $iconNbr; ?>; <?php echo $toolTips; ?>textClass:mooTree_text2win --><?php echo $label; ?></a>
<?php echo $this->getDBSharingLevel($param); ?>
</li>
<?php
 }
}
}
if ( $this->ignoreUL) {}
else {
?></ul>
<?php } ?>
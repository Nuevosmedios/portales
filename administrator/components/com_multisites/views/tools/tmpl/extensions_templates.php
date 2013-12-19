<?php
// file: extensions_templates.php.
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
<?php $isTemplate = $this->_isExtensionSite( 1);
$isSite = $this->_isExtensionSite( 2);
$hasChildren = $this->_hasChildren();
$yes = '<img src="components/com_multisites/images/yes.png" title="Present" />'
?>
<table class="adminlist" cellspacing="1">
      <thead>
   		<tr>
   			<th><?php echo JText::_( 'Templates'); ?></th>
   			<th><?php echo JText::_( 'Master'); ?></th>
<?php if ($isTemplate) { ?>
<th><?php echo JText::_( 'Template'); ?></th>
<?php }
if ($isSite) {
?>
<th><?php echo JText::_( 'Site'); ?></th>
   			<th><?php echo JText::_( 'Action'); ?></th>
<?php }
if ( $hasChildren) {
?>
<th><?php echo JText::_( 'Propagate to children'); ?><br/>
   			   <input type="checkbox" name="toggleTemplates" value="" onclick="checkAllComponents(this.checked, 'tmpl');" />
            </th>
   			<th><?php echo JText::_( 'Overwrite'); ?><br/>
   			   <input type="checkbox" name="overwriteTemplates" value="" onclick="checkAllComponents(this.checked, 'tow');" />
            </th>
<?php } ?>
</tr>
      </thead>
      <tbody>
<?php if ( !empty( $this->extensions['Templates'])) {
$i = 0; $k = 0;
foreach( $this->extensions['Templates'] as $name => $columns) {
if ( !empty( $columns[0])) { $template = & $columns[0]; }
else if ( !empty( $columns[1])) { $template = & $columns[1]; }
else if ( !empty( $columns[2])) { $template = & $columns[2]; }
$option = $template->element;
?><tr class="<?php echo "row". $k; ?>">
            <td>
      			<span class="editlinktip hasDynTip" title="<?php echo $this->_getToolTips( $columns, $i); ?>">
      				<?php echo $name ?>
</span>
      		</td>
            <td align="center"><?php echo ( !empty( $columns[0]) ? $yes : '-') ;?></td>
<?php if ($isTemplate) { ?>
<td align="center"><?php echo ( !empty( $columns[1]) ? $yes : '-') ;?></td>
<?php }
if ($isSite) {
?>
<td align="center"><?php echo $this->_getTableType( $columns, $i, 'template'); ?></td>
   			<td><?php echo $this->_getComponentAction( $isTemplate, $option, $columns, 'atmpl', $i, true); ?></td>
<?php } ?>
<?php if ( $hasChildren) { ?>
<td align="center"><input type="checkbox" id="tmpl<?php echo $i; ?>" name="ctmpl[]" value="<?php echo $option; ?>" onclick="synchOverwrite(this.checked, 'tow<?php echo $i; ?>');" /></td>
         	<td align="center"><input type="checkbox" id="tow<?php echo $i; ?>" name="ctow[]" value="<?php echo $option; ?>" disabled="disabled" /></td>
<?php } ?>
</tr>
<?php
 $i++;
$k = 1 - $k;
} 
} 
?></tbody>
   </table>

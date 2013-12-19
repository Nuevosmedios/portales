<?php
// file: edit_unix.php.
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
<div class="edit_unix">
<table border="0">
	<tr>
		<td width="100%" />
		<td nowrap="nowrap">
         <?php echo MultisitesHelper::getFilterActionsCombo( count( $this->symbolicLinks)); ?>
</td>
	</tr>
</table>
<table class="adminlist" cellspacing="1">
<thead>
	<tr>
		<th width="1%">
			<?php echo JText::_( 'Num' ); ?>
</th>
		<th width="5%">
			<div class="title">
   			<?php echo JHTML::_('grid.sort', JText::_( 'Action'), 'action', @$lists['order_Dir'], @$lists['order'] ); ?>
</div>
			<div class="actionAll">
		      <?php echo str_replace( 'onchange="enableSource',
'onfake="doNothing',
MultisitesHelper::getActionsList( "SL_actionsAll", ':limited:', array(), "fake")
);
?>
<a style="float:left;" href="#" onclick="return updateSLActions( <?php echo count($this->symbolicLinks); ?>);" title="Apply on all actions"><img style="width: 16px;height: 16px;margin-left: 3px;cursor: pointer;vertical-align: middle;" src="components/com_multisites/images/icon-16-apply.png" alt="Apply on all actions" /></a>
			</div>
		</th>
		<th width="15%">
			<?php echo JHTML::_('grid.sort', JText::_( 'Folder or File'), 'name', @$lists['order_Dir'], @$lists['order'] ); ?>
</th>
		<th width="55%">
			<div class="title">
   			<?php echo JHTML::_('grid.sort', JText::_( 'TEMPLATE_VIEW_EDT_UNIX_FROM_FILE_OR_FOLDER'), 'file', @$lists['order_Dir'], @$lists['order'] ); ?>
<?php echo JHTML::_('tooltip', JText::_( 'When empty for a copy, this is the master website that is used to replicate the file or the folder' )); ?>
<?php echo MultisitesHelper::tooltipsKeywords(); ?>
</div>
			<div class="inputAll">
         	<input style="float:left;" type="text" name="SL_files_All; ?>]" id="SL_files_All" value="" size="120" maxlength="255"/>
         	<a style="float:left;" href="#" onclick="return updateSLFiles( <?php echo count($this->symbolicLinks); ?>);" title="Apply on all empty sources"><img style="width: 16px;height: 16px;margin-left: 3px;cursor: pointer;vertical-align: middle;" src="components/com_multisites/images/icon-16-apply.png" alt="Update all Sources" /></a>
         	<a style="float:left;" href="#" onclick="return clearSLFiles( <?php echo count($this->symbolicLinks); ?>);" title="Clear all sources"><img style="width: 16px;height: 16px;margin-left: 3px;cursor: pointer;vertical-align: middle;" src="components/com_multisites/images/cancel.png" alt="Clear all sources" /></a>
			</div>
		</th>
	</tr>
</thead>
<tbody>
<?php $i = 0; $k = 0; ?>
<?php foreach ($this->symbolicLinks as $key => $symbolicLink) { ?>
<tr class="<?php echo "row". $k; ?>" id="row_<?php echo $i; ?>">
		<td align="center">
			<?php echo $i+1; ?>
</td>
		<td nowrap="nowrap">
<?php
 if ( isset( $symbolicLink['readOnly']) && $symbolicLink['readOnly']) {
?>
<input type="hidden" name="SL_actions[<?php echo $i; ?>]" value="<?php echo $symbolicLink['action']; ?>" />
      	<input type="hidden" name="SL_readOnly[<?php echo $i; ?>]" value="true" />
<?php
 echo JText::_( "TEMPLATE_ACTION_". $symbolicLink['action']);
}
else {
echo MultisitesHelper::getActionsList( "SL_actions[$i]", $key, $symbolicLink, "SL_files[$i]");
}
?>
</td>
		<td nowrap="nowrap">
      	<input type="hidden" name="SL_names[<?php echo $i; ?>]" value="<?php echo $key; ?>" />
			<?php echo $key; ?>
</td>
		<td nowrap="nowrap">
<?php  $sl_file = (isset( $symbolicLink['file'])) ? $symbolicLink['file'] : '';
if ( $this->isActionEditable( $symbolicLink['action'])) {
?>
<input type="text" name="SL_files[<?php echo $i; ?>]" id="SL_files[<?php echo $i; ?>]" value="<?php echo $sl_file; ?>" size="120" maxlength="255"/>
<?php
 } else {
?>
<input type="hidden" readonly="1" name="SL_files[<?php echo $i; ?>]" id="SL_files[<?php echo $i; ?>]" value="<?php echo $sl_file; ?>" size="120" maxlength="255"/>
<?php  echo $sl_file;
}
?>
</td>
	</tr>
	<?php $i++; $k = 1 - $k; ?>
<?php } ?>
</tbody>
</table>
</div>
<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
   require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'classes'.DS.'utils.php');
   $lists = $this->lists;

	JFactory::getDocument()->addStyleSheet( JURI::base() .'components/com_multisitesusersite/views/usersite/tmpl/list.css');
	
	JPluginHelper::importPlugin('system');
	$dispatcher = &JDispatcher::getInstance();
?>
<form action="index.php?option=com_multisitesusersite" method="post" name="adminForm" id="adminForm">
	<table>
		<tr>
   		<td align="left" width="100%">
   			<?php echo JText::_( 'Filter' ); ?>:
   			<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
   			<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
   			<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
   		</td>
			<td nowrap="nowrap">
				<?php
				echo $lists['user_ids'];
				echo $lists['site_ids'];
				?>
			</td>
		</tr>
	</table>

	<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="4%">
				<?php echo JText::_( 'Num' ); ?>
			</th>
			<th width="4%">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->rows ); ?>);" />
			</th>
			
			<th width="22%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   'MULTISITESUSERSITE_LIST_USER_USERNAME', 'username', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="22%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort',   'MULTISITESUSERSITE_LIST_USER_NAME', 'name', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="22%">
				<?php echo JHTML::_('grid.sort',   'MULTISITESUSERSITE_LIST_SITE_ID', 'site_id', @$lists['order_Dir'], @$lists['order'] ); ?>
			</th>
			<th width="22%">
				<?php echo JText::_('MULTISITESUSERSITE_LIST_SITE_NAME'); ?>
			</th>
			<th width="4%">
				<?php echo JText::_('default'); ?>
			</th>
<?php $dispatcher->trigger('onViewUserSite_TmplList_Header', array( &$this)); ?>
			<th width="4%">
				<?php echo JText::_( 'id' ); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
	   <tr>
		   <td colspan="8">
			   <?php echo $this->pagination->getListFooter(); ?>
		   </td>
	   </tr>
	</tfoot>
	<tbody>
	<?php $i = 0; $k = 0; ?>
	<?php foreach ($this->rows as $row) : ?>
		<?php
		   $id = $row->id;
			// Get the current iteration and set a few values
			$link 	= 'index.php?option=com_multisitesusersite&task=editUserSite&id='. $id;
		?>
		<tr class="<?php echo "row". $k; ?>">
			<td align="center">
				<?php echo $this->pagination->limitstart + 1 + $i; ?>
			</td>
			<td width="30" align="center">
				<input type="checkbox" onclick="isChecked(this.checked);" value="<?php echo $row->id; ?>" name="cid[]" id="cb<?php echo $i; ?>">
			</td>
			<td>
				<a href="<?php echo $link; ?>"><?php echo !empty( $row->username) ? $row->username : '?'; ?></a>
			</td>
			<td>
			   <?php echo !empty( $row->username) ? $row->name : '&nbsp;'; ?>
			</td>
			<td nowrap="nowrap">
				<?php echo !empty($row->site_id) ? $row->site_id : '&nbsp;'; ?>
			</td>
			<td nowrap="nowrap">
<?php
            $site =& MultisitesUtils::getSiteInfo( $row->site_id);
				echo !empty($site->sitename) ? $site->sitename : '&nbsp;';
?>
			</td>
			<td>
<?php if ( !empty($row->home)) { ?>
				<span title="<?php echo JText::_('Default'); ?>" class="jgrid"><span class="state default"><span class="text">Default</span></span></span>
<?php } else { ?>
				&nbsp;
<?php } ?>
			</td>
<?php $dispatcher->trigger('onViewUserSite_TmplList_Body', array( &$this, &$row, &$site)); ?>
			<td>
				<?php echo $id; ?></a>
			</td>
		</tr>
		<?php $i++; $k = 1 - $k; ?>
	<?php endforeach; ?>
	</tbody>
	</table>

	<input type="hidden" name="option"           value="com_multisitesusersite" />
	<input type="hidden" name="task"             value="listusersite" />
	<input type="hidden" name="boxchecked"       value="0" />
	<input type="hidden" name="filter_order"     value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

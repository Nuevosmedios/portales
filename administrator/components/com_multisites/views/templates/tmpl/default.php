<?php
// file: default.php.
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
 $document = & JFactory::getDocument();
$document->addStylesheet( str_replace( '/index.php', '', JURI::base( true)).'/components/com_multisites/css/form.css');
$lists = $this->lists;
if ( JFile::exists( dirname(__FILE__).'/default_geolocalisation.php')) { include_once( dirname(__FILE__).'/default_geolocalisation.php'); }
if ( JFile::exists( dirname(__FILE__).'/default_browser.php')) { include_once( dirname(__FILE__).'/default_browser.php'); }
?>
<div class="formList">
<form action="index.php?option=com_multisites" method="post" name="adminForm" id="adminForm">
	<table>
<?php if ( !empty($this->ads)) { ?>
<tr><td colspan="2"><?php echo $this->ads; ?></td></tr>
<?php } ?>
<tr>
			<td width="100%">
			</td>
			<td nowrap="nowrap">
				<?php
 echo $lists['dbserver'];
echo $lists['dbname'];
?>
</td>
		</tr>
	</table>

	<table class="adminlist" cellspacing="1">
	<thead>
		<tr>
			<th width="5%">
				<?php echo JText::_( 'Num' ); ?>
</th>
			<th width="5%">
				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->templates ); ?>);" />
			</th>
			<th width="5%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort', JText::_( 'TEMPLATE_LIST_ID'), 'id', @$lists['order_Dir'], @$lists['order'] ); ?>
</th>
			<th width="5%">
				<?php echo JHTML::_('grid.sort', JText::_( 'TEMPLATE_LIST_GROUP'), 'groupName', @$lists['order_Dir'], @$lists['order'] ); ?>
</th>
			<th width="10%">
				<?php echo JHTML::_('grid.sort', JText::_( 'TEMPLATE_LIST_FROM_SITE_ID'), 'fromSiteID', @$lists['order_Dir'], @$lists['order'] ); ?>
</th>
			<th width="10%">
				<?php echo JHTML::_('grid.sort', JText::_( 'TEMPLATE_LIST_TO_SITE_ID'), 'toSiteID', @$lists['order_Dir'], @$lists['order'] ); ?>
</th>
			<th width="10%">
				<?php echo JHTML::_('grid.sort', JText::_( 'TEMPLATE_LIST_TO DOMAINS'), 'toDomains', @$lists['order_Dir'], @$lists['order'] ); ?>
</th>
			<th width="10%">
				<?php echo JHTML::_('grid.sort', JText::_( 'TEMPLATE_LIST_FROM_DB'), 'fromDB', @$lists['order_Dir'], @$lists['order'] ); ?>
</th>
			<th width="10%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort', JText::_( 'TEMPLATE_LIST_FROM_DB_PREFIX'), 'fromPrefix', @$lists['order_Dir'], @$lists['order'] ); ?>
</th>
			<th width="20%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort', JText::_( 'TEMPLATE_LIST_TO_DB_NAME'), 'toDBName', @$lists['order_Dir'], @$lists['order'] ); ?>
</th>
			<th width="20%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort', JText::_( 'TEMPLATE_LIST_TO_DB_PREFIX'), 'toPrefix', @$lists['order_Dir'], @$lists['order'] ); ?>
</th>
<?php if ( class_exists( 'TmplDefaultGeolocalisation')) { TmplDefaultGeolocalisation::thead( $lists); } ?>
<?php if ( class_exists( 'TmplDefaultBrowser')) { TmplDefaultBrowser::thead( $lists); } ?>
<th width="20%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort', JText::_( 'TEMPLATE_LIST_THEMES'), 'templates_dir', @$lists['order_Dir'], @$lists['order'] ); ?>
</th>
<?php if ( $this->canShowDeployDir()) { ?>
<th width="10%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort', JText::_( 'TEMPLATE_LIST_DEPLOY_DIRECTORY'), 'deploy_dir', @$lists['order_Dir'], @$lists['order'] ); ?>
</th>
<?php } ?>
</tr>
	</thead>
	<tfoot>
	   <tr>
<?php $colspan = 12;
if ( class_exists( 'TmplDefaultGeolocalisation')) { $colspan += TmplDefaultGeolocalisation::tfoot_colspan(); }
if ( class_exists( 'TmplDefaultBrowser')) { $colspan += TmplDefaultBrowser::tfoot_colspan(); }
if ( $this->canShowDeployDir()) { $colspan++; }
?>
<td colspan="<?php echo $colspan; ?>">
			   <?php echo $this->pagination->getListFooter(); ?>
</td>
	   </tr>
	</tfoot>
	<tbody>
	<?php $i = 0; $k = 0; ?>
<?php foreach ($this->templates as $template) : ?>
<?php
 $id = $template['id'];

$link = 'index.php?option=com_multisites&task=editTemplate&id='. $id;
?>
<tr class="<?php echo "row". $k; ?>">
			<td align="center">
				<?php echo $this->pagination->limitstart + 1 + $i; ?>
</td>
			<td width="30" align="center">
				<input type="checkbox" onclick="isChecked(this.checked);" value="<?php echo $id; ?>" name="cid[]" id="cb<?php echo $i; ?>">
			</td>
			<td>
			<span class="editlinktip hasTip" title="<?php echo $this->getTemplateToolTips( $id, $template);?>">
				<a href="<?php echo $link; ?>">
					<?php echo $id; ?></a></span>
			</td>
			<td nowrap="nowrap">
				<?php echo isset($template['groupName']) ? $template['groupName'] : ''; ?>
</td>
			<td nowrap="nowrap">
				<?php echo $template['fromSiteID']; ?>
</td>
			<td nowrap="nowrap">
				<?php echo isset($template['toSiteID']) ? $template['toSiteID'] : ''; ?>
</td>
			<td nowrap="nowrap">
				<?php echo !empty( $template['toDomains'])
? implode( ",<br/>", $template['toDomains'])
: '&nbsp;'; ?>
</td>
			<td nowrap="nowrap">
				<?php echo $template['fromDB']; ?>
</td>
			<td nowrap="nowrap">
				<?php echo $template['fromPrefix']; ?>
</td>
			<td nowrap="nowrap">
				<?php echo (isset( $template['toDBName'])) ? $template['toDBName'] : ''; ?>
</td>
			<td nowrap="nowrap">
				<?php echo $template['toPrefix']; ?>
</td>
<?php if ( class_exists( 'TmplDefaultGeolocalisation')) { TmplDefaultGeolocalisation::tbody( $template); } ?>
<?php if ( class_exists( 'TmplDefaultBrowser')) { TmplDefaultBrowser::tbody( $template); } ?>
<td>
				<?php echo (isset( $template['templates_dir'])) ? $template['templates_dir'] : ''; ?>
</td>
<?php if ( $this->canShowDeployDir()) { ?>
<td>
				<?php echo (isset( $template['deploy_dir'])) ? $template['deploy_dir'] : ''; ?>
</td>
<?php } ?>
</tr>
		<?php $i++; $k = 1 - $k; ?>
<?php endforeach; ?>
</tbody>
	</table>

	<input type="hidden" name="option"           value="com_multisites" />
	<input type="hidden" name="task"             value="templates" />
	<input type="hidden" name="boxchecked"       value="0" />
	<input type="hidden" name="filter_order"     value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
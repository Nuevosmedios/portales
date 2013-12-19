<?php
// file: extensions_detail.php.
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
<table class="adminform">
	<tr>
		<td class="helpMenu">
			<label for="id">
				<strong><?php echo JText::_( 'SITE_EDIT_SITE_ID' ); ?>:</strong>
			</label>
		</td>
		<td width="75%">
         <?php echo $this->site_info->id;?>
<input type="hidden" name="site_id" value="<?php echo $this->site_info->id; ?>" />
		</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="status">
				<strong><?php echo JText::_( 'SITE_EDIT_STATUS' ); ?>:</strong>
			</label>
		</td>
		<td>
			<?php $this->site_info->status; ?>
</td>
	</tr>
	<tr valign="top">
		<td class="helpMenu">
			<label for="domains">
				<strong><?php echo JText::_( 'SITE_EDIT_DOMAINS' ); ?>:</strong>
			</label>
		</td>
		<td>
		   <?php echo implode( "<br/>", $this->site_info->domains); ?>
</td>
	</tr>
<?php if ( !empty( $this->site_info->sitename)) { ?><tr >
		<td class="helpMenu">
			<label for="toSiteName">
				<strong><?php echo JText::_( 'SITE_LIST_SITENAME' ); ?>:</strong>
			</label>
		</td>
		<td><?php echo $this->site_info->sitename; ?></td>
	</tr>
<?php } ?><?php if ( !empty( $this->site_info->fromTemplateID)) { ?><tr valign="top">
		<td class="helpMenu">
			<label for="fromTemplateID">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMPLATES' ); ?>:</strong>
			</label>
		</td>
		<td><?php 
echo $this->site_info->fromTemplateID;
$fromSiteID = $this->site_info->getFromSiteID();
if ( !empty( $fromSiteID)) {
$fromSite = Site::getInstance( $fromSiteID);
echo ' ( <b>Site ID:</b><font color="green"> ' . $fromSiteID . '</font> <b>DB:</b><font color="green"> ' . $fromSite->db . '</font> <b>Prefix:</b><font color="green"> ' .$fromSite->dbprefix .'</font> )';
}
?></td>
	</tr>
<?php } ?><tr>
      <td class="helpMenu"><label for="host"><strong><?php echo JText::_( 'SITE_EDIT_DB_HOST_NAME' ); ?>:</strong></label></td>
      <td><?php echo $this->site_info->host; ?></td>
   </tr>
   <tr>
      <td class="helpMenu"><label for="db"><strong><?php echo JText::_( 'SITE_EDIT_DB' ); ?>:</strong></label></td>
      <td><?php echo $this->site_info->db; ?></td>
   </tr>
   <tr>
      <td class="helpMenu"><label for="dbprefix"><strong><?php echo JText::_( 'SITE_EDIT_DB_PREFIX' ); ?>:</strong></label></td>
      <td><?php echo $this->site_info->dbprefix; ?></td>
   </tr>
   <tr>
      <td class="helpMenu"><label for="user"><strong><?php echo JText::_( 'SITE_EDIT_DB_USER' ); ?>:</strong></label></td>
      <td><?php echo $this->site_info->user; ?></td>
   </tr>
   <tr>
      <td class="helpMenu"><label for="password"><strong><?php echo JText::_( 'SITE_EDIT_DB_PASSWORD' ); ?>:</strong></label></td>
      <td><?php echo $this->site_info->password; ?></td>
   </tr>
   <tr>
      <td class="helpMenu"><label for="mysql_version"><strong><?php echo JText::_( 'SITE_EDIT_DB_VERSION' ); ?>:</strong></label></td>
      <td><?php 
echo $this->site_info->mysql_version;
if ( $this->site_info->mysql_sharing) {
echo ' (' . JText::_( 'SITE_EDIT_DB_SHARING') . ')';
}
?></td>
   </tr>
</table>

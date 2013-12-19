<?php
// file: delete.php.
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
?>
<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		if (pressbutton == 'doDeleteSite') {

			submitform( pressbutton );
		} else {
			submitform( pressbutton );
		}
	}
//-->
</script>
<?php if ( !empty($this->ads)) { ?>
<table border="0">
   <tr><td><?php echo $this->ads; ?></td></tr>
</table>
<?php } ?>
<div class="formDelete">
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div>
		<span class="note">
			<strong><?php 
$str = JText::sprintf( 'SITE_DELETE', $this->site->sitename, $this->site_dir);
echo $str;
?></strong>
		</span>
<?php if ( !empty( $this->site->delete_dir)) { ?>
<span class="note">
			<strong><?php 
$str = JText::sprintf( 'SITE_DELETE_DIR', $this->site->delete_dir);
echo $str;
?></strong>
		</span>
<?php } ?><div class="clr"></div>
	</div>
	<table><tr><td>
	<table class="adminform" border="1">
	   <caption><?php echo JText::_( 'SITE_DELETE_SITE_INFORMATION' ); ?></caption>
	   <tbody>
	      <tr>
	         <td class="helpMenu"><label for="id"><strong><?php echo JText::_( 'SITE_DELETE_SITE_ID' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->id; ?></td>
	      </tr>
	      <tr valign="top">
	         <td align="right"><label for="domains"><strong><?php echo JText::_( 'SITE_DELETE_DOMAINS' ); ?>:</strong></label></td>
	         <td><?php echo implode( "<br/>", $this->site->domains); ?></td>
	      </tr>
<?php  if ( !empty( $this->site->host) && !empty( $this->site->db) && !empty( $this->site->dbprefix)) {
$canDeleteDB = true;

if ( !empty( $this->shareDB)) { $canDeleteDB = false; }





else if ( !empty( $this->template)) {
if ( !empty( $this->template->shareDB)) { $canDeleteDB = false; }

else if ( empty( $this->template->toDBHost) && empty( $this->template->toDBName) && empty( $this->template->toPrefix)) { $canDeleteDB = true; }
else {

if ( (empty( $this->template->toDBHost) || $this->site->host == $this->template->toDBHost)
&& (empty( $this->template->toDBName) || $this->site->db == $this->template->toDBName)
&& (empty( $this->template->toPrefix) || $this->site->dbprefix == $this->template->toPrefix)
) { $canDeleteDB = false; }
}
}
if ( $canDeleteDB) {
?>
<tr>
	         <td class="helpMenu"><label for="host"><strong><?php echo JText::_( 'SITE_DELETE_DB_CONTENT' ); ?>:</strong></label></td>
	         <td><?php echo JHTML::_('select.booleanlist', 'deleteDB', '', 'no'); ?>&nbsp;&nbsp;&nbsp;&nbsp;<font color="gray"><?php echo JText::sprintf( 'SITE_DELETE_DB_CONTENT_TTIPS', $this->site->dbprefix); ?></td>
	      </tr>
<?php  }
}
?>
<tr>
	         <td class="helpMenu"><label for="host"><strong><?php echo JText::_( 'SITE_DELETE_DB_HOST_NAME' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->host; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="db"><strong><?php echo JText::_( 'SITE_DELETE_DB' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->db; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="dbprefix"><strong><?php echo JText::_( 'SITE_DELETE_DB_PREFIX' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->dbprefix; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="user"><strong><?php echo JText::_( 'SITE_DELETE_DB_USER' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->user; ?></td>
	      </tr>
	      <tr>
	         <td class="helpMenu"><label for="password"><strong><?php echo JText::_( 'SITE_DELETE_DB_PASSWORD' ); ?>:</strong></label></td>
	         <td><?php echo $this->site->password; ?></td>
	      </tr>
	   </tbody>
	</table>
	</td></tr/></table>

	<input type="hidden" name="id" value="<?php echo $this->site->id; ?>" />
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
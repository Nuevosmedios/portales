<?php
// file: edit_dbinfo.php.
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
?><?php defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.utilities.utility.php');
echo $this->paneCmn->startPanel( '<span class="nbr">'.$this->paneNbr++.'</span>'. JText::_( 'SITE_EDIT_FROM_CONFIG' ), "common-dbinfo" );
?>
<div class="formBlock">
      <div style="clear: both;border: 1px solid #ccc;background: #f0f0f0;color: #0B55C4;font-weight: bold;text-align: center;"><?php echo JText::_( 'SITE_EDIT_FROM_CONFIG' ); ?></div>
   </div>
   <div class="formBlock">
      <div class="formLabel"><label for="host"><strong><?php echo JText::_( 'SITE_EDIT_DB_HOST_NAME' ); ?>:</strong></label></div>
      <div class="formField"><?php echo $this->row->host; ?></div>
   </div>
   <div class="formBlock">
      <div class="formLabel"><label for="db"><strong><?php echo JText::_( 'SITE_EDIT_DB' ); ?>:</strong></label></div>
      <div class="formField"><?php echo $this->row->db; ?></div>
   </div>
   <div class="formBlock">
      <div class="formLabel"><label for="dbprefix"><strong><?php echo JText::_( 'SITE_EDIT_DB_PREFIX' ); ?>:</strong></label></div>
      <div class="formField" id="dbprefix"><?php echo $this->row->dbprefix; ?></div>
   </div>
   <div class="formBlock">
      <div class="formLabel"><label for="user"><strong><?php echo JText::_( 'SITE_EDIT_DB_USER' ); ?>:</strong></label></div>
      <div class="formField"><?php echo $this->row->user; ?></div>
   </div>
   <div class="formBlock">
      <div class="formLabel"><label for="password"><strong><?php echo JText::_( 'SITE_EDIT_DB_PASSWORD' ); ?>:</strong></label></div>
      <div class="formField"><?php echo $this->row->password; ?></div>
   </div>
	<?php if ($this->isnew) : ?>
<div class="formBlock">
		   <span class="note">
				<center><strong><?php echo JText::_( 'SITE_EDIT_REMARK' ); ?></strong></center>
		   </span>
	</div>
   <?php endif; ?>
<?php
 echo $this->paneCmn->endPanel();

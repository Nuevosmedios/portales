<?php
// file: donate.php.
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
<form action="<?php echo $this->action; ?>" method="post" target="_blank">
<?php if (!empty($this->message)) echo '<p>' . $this->message . '</p>'; ?>
<input type="hidden" name="option"        value="com_pay2win" />
<input type="hidden" name="task"          value="donations" />
<input type="hidden" name="item_code"     value="<?php echo $this->option; ?>" />
<input type="hidden" name="client_info"   value="<?php echo $this->clientInfo; ?>" />
<span class="editlinktip"><label id="donation-lbl" for="donation" class="hasTip" title="<?php echo $this->btnToolTipMsg; ?>">
<input type="image" src="<?php echo JURI::base() . 'components/' . $this->option; ?>/images/btn_donate.gif" 
       border="0" name="submit" alt="<?php echo $this->btnAltMsg; ?>" />
</label></span>
</form>


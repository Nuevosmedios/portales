<?php
// file: show_quota.php.
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
<div id="quota_frame" class="formBlock">
<?php
 $this->displayFieldForm( $this->row, $this->lists,
array( 'separation' => array( 'type' => 'label', 'label' => '')
)
);
?>
<div class="padding">
   <fieldset class="quota">
   <legend><?php echo JText::_( 'Billable websites'); ?></legend>
   <div class="left">
<?php
 $this->displayFieldForm( $this->row, $this->lists,
array( 'website_count' => array( 'type' => 'label', 'label' => 'Website count'),
'website_quota' => array( 'type' => 'label', 'label' => 'Website quota')
)
);
?>
</div>
   <div class="right">
		<form action="<?php echo $this->row->quota_url; ?>" method="post" name="buyquota" id="form-buyquota" style="clear: both;">
      	<div class="button_holder">
      	<div class="button1">
      		<div class="next">
      			<a onclick="buyquota.submit();">
      				<?php echo JText::_( 'Buy quota'); ?></a>
      		</div>
      	</div>
      	</div>
      	<input type="hidden" name="option"     value="com_pay2win" />
      	<input type="hidden" name="task"       value="jms.buyQuota" />
      	<input type="hidden" name="product_id" value="<?php echo $this->row->product_id; ?>" />
     </form>
   </div>
   </fieldset>
   </div>
   </div>
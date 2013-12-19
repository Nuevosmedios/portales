<?php
// file: check.php.
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
<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		if (pressbutton == 'doInstallPatches') {
			submitform( pressbutton );
		} else if (pressbutton == 'doUninstallPatches') {
			if (confirm ("<?php echo JText::_( 'Are you sure?' ); ?>")) {
				submitform( pressbutton );
			}
		} else {
			submitform( pressbutton );
		}
	}
//-->
</script>
<style type="text/css">
 <!--
table.checkpatches      { background-color:#F9F9F9; border:1px solid #D5D5D5; border-collapse:collapse; margin:8px 0 15px; width:100%; }
table.checkpatches th   { background-repeat:repeat; color:#000000; font-size:11px; height:25px; padding:6px 2px 4px 4px; text-align:left; }
table.checkpatches td   { padding:2px 3px; text-align:left; font-size:11px; }
 -->
</style><form action="index.php" method="post" name="adminForm" id="adminForm">
	<div>
	   <table border="0">
	   <tr><td>
<?php 

$jmsname = JText::_( 'Jms Multi Sites');
if ( !empty( $this->latestVersion['version'])) {
$getLatestURL = '<a href="http://www.jms2win.com/get-latest-version" target="_blank">Get Latest Version !</a>';
if ( version_compare( $this->jmsVersionNbr, $this->latestVersion['versionNbr']) < 0) {
include( dirname( __FILE__).'/check_getlatest.php');
}
else {
include( dirname( __FILE__).'/check_version_ok.php');
}
}
else {
include( dirname( __FILE__).'/check_version_ok.php');
}

include( dirname(__FILE__).'/check_ext_version.php');
?></td></tr>
<?php if ( !empty($this->ads)) { ?>
<tr><td><?php echo $this->ads; ?></td></tr>
<?php } ?>
<tr><td>
	   <table class="checkpatches" border="1">
	      <thead>
	         <tr>
	            <th><?php echo JText::_( 'Files'); ?></th>
	            <th><?php echo JText::_( 'Status'); ?></th>
	         </tr>
	      </thead>
	      <tbody>
<?php
 foreach( $this->patches_status as $filename => $status) {
$msgs = preg_split( '#[|]#', $status);
?>
<tr valign="top">
	            <td><?php echo $filename; ?></td>
	            <td>
<?php  if ( $msgs[0] == '[OK]') { ?>
<center><font color="green">OK</font></center> 
<?php  } else { ?><center><font color="red">Not OK</font></center> 
	               <ul><?php foreach( $msgs as $msg) {
if ( $msg == '[NOK]') {}
else if ( $msg == '[ACTION]') {
break;
}
else { ?>
<li><?php echo $msg; ?></li><?php } } ?>
</ul>
<?php  if ( $msg == '[ACTION]') { ?>
<?php echo JText::_( 'Actions' ); ?>:
                  <ul type="circle"><?php
 $state = 0;
foreach( $msgs as $msg) {
if ( $state == 0) {
if ( $msg == '[ACTION]') {
$state = 1;
}
}
else if ( $state == 1) { ?>
<li><?php echo $msg; ?></li>                        
<?php
 } 
} 
?>
</ul>                     
<?php  } 
?>
<?php  } ?>
<?php  if ( $filename == 'installation' && $msgs[0] == '[NOK]') { ?>
<label for="id">
         				<strong><?php echo JText::_( 'PATCHES_VIEW_RENINSTDIR_LBL' ); ?>:</strong>
         			</label>
         			<input class="inputbox" type="text" name="ren_inst_dir" id="ren_inst_dir" size="30" maxlength="25" value="" />
         			<?php echo JHTML::_('tooltip', JText::_( 'PATCHES_VIEW_RENINSTDIR_TT' )); ?>
<?php } ?></td>
	         </tr>
<?php } ?>
</tbody>
	   </table>
	   </td></tr></table>
<?php if ( $this->can_install) { ?>
<span class="note"><strong><?php echo JText::_('PATCHES_NOTES'); ?>:</strong><br/><?php echo JText::_('PATCHES_CAN_INSTALL'); ?></span>
<?php } else { ?>
<span class="note"><strong><?php echo JText::_('PATCHES_INSTALL_OK'); ?></strong><br/></span>
<?php } ?>
<div class="clr"></div>
	</div>

	<input type="hidden" name="id" value="<?php echo $this->id; ?>" />
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
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
<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		var form = document.adminForm;

		if (pressbutton == 'applyTools') {
			try {
   			if ( form.site_id.value == '' ) {
   				alert( '<?php echo JText::_( 'Please select a site', true ); ?>' );
   				return;
   			}
			}
			catch( e) {
				alert( '<?php echo JText::_( 'Please select a site', true ); ?>' );
				return;
			}
			submitform( 'applyTools' );
		} else {
			submitform( pressbutton );
		}
	}
	
	var g_curtoken = '<?php echo J2WinUtility::getToken2Win() . '=1'; ?>';

   function checkAllComponents( checked, name)
   {
      var i;
      var cbx;
      for ( i=0; ; i++) {
         try {
            cbx = $( name + i);
            if ( cbx.disabled) {
               cbx.checked = false;
            }
            else {
               cbx.checked = checked;
            } 
            
            if ( name == 'com') {
               synchOverwrite( checked, 'ow'+i);
            }
            else if ( name == 'mod') {
               synchOverwrite( checked, 'mow'+i);
            }
            else if ( name == 'plg') {
               synchOverwrite( checked, 'pow'+i);
            }
         }
         catch( e) {
            break;
         }
      }
   }
   
   function updateCB( select, cbName)
   {
      try {
   cbx = $( cbName);
         if ( select.value == '[unselected]') {
            cbx.checked  = false;
            cbx.disabled = true;
         }
         else {
            cbx.disabled = false;
         }
      }
      catch( e) {}
   }

   function synchOverwrite( checked, cbName)
   {
      try {
         cbx = $( cbName);
         if ( checked) {
            cbx.disabled = false;
         }
         else {
            cbx.checked  = false;
            cbx.disabled = true;
         }
      }
      catch( e) {}
   }

//-->
</script>
<?php if ( !empty($this->ads)) { ?>
<table border="0">
   <tr><td><?php echo $this->ads; ?></td></tr>
</table>
<?php } ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
   <table border="0" cellpadding="0" cellspacing="0">
      <tr>
         <td width="20%" valign="top">
            <fieldset id="treeview">
                <div id="treesites_tree"></div>
<?php echo $this->getChildrenTree( $this->treeSites, ' id="treesites"'); ?>
</fieldset>
         </td>
         <td class="treesite_form">
            <div id="treesite_message"><?php echo JText::_( 'Select a site'); ?></div>
            <div id="treesite_detail">&nbsp;</div>
         </td>
      </tr>
   </table>
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task" value="tools" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
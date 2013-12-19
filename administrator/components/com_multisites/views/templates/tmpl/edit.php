<?php
// file: edit.php.
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
<style type="text/css">
 <!--
.tool-tip, .tip { max-width: 600px; }
 -->
</style>
<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		var form = document.adminForm;

		if (pressbutton == 'savetemplate') {
			if ( form.id.value == '' ) {
				alert( '<?php echo JText::_( 'Please enter a template identifier', true ); ?>' );
				form.menutype.focus();
				return;
			}
			var r = new RegExp("[\']", "i");
			if ( r.exec(form.id.value) ) {
				alert( '<?php echo JText::_( 'The template identifier cannot contain a \'', true ); ?>' );
				form.menutype.focus();
				return;
			}
			submitform( 'savetemplate' );
		} else {
			submitform( pressbutton );
		}
	}

   function getUserList( site_id)
   {
      var ajax;
      document.getElementById("divMessage").innerHTML = 'Refreshing ...';
      try {  ajax = new ActiveXObject('Msxml2.XMLHTTP');   }
      catch (e)
      {
        try {   ajax = new ActiveXObject('Microsoft.XMLHTTP');    }
        catch (e2)
        {
          try {  ajax = new XMLHttpRequest();     }
          catch (e3) {  ajax = false;   }
        }
      }

      ajax.onreadystatechange  = function()
      {
         if(ajax.readyState  == 4)
         {
            var showFolders = true;
            if(ajax.status  == 200) {
               if ( ajax.responseText.length <= 0) {
                  showFolders = false;
               }
               else {
                  // JSON decode
                  var json = eval("(" + ajax.responseText + ")");
                  
                  document.getElementById("divAdminUser").innerHTML = json.userslist;
                  if ( json.userslist.length <= 0) {
                     showFolders = false;
    }
                  document.getElementById("setDefaultJLang_input").innerHTML = json.setDefaultJLang;
                  document.getElementById("setDefaultTemplate_input").innerHTML = json.setDefaultTemplate;
                  document.getElementById("setDefaultMenu_input").innerHTML = json.setDefaultMenu;
               }
               document.getElementById("divMessage").innerHTML = '';
            }
            else {
               document.getElementById("divMessage").innerHTML = "Error code " + ajax.status;
               showFolders = false;
            }
            var shareDB = document.getElementById("tr_shareDB");
            var style_display = '';
            var db_display = '';
            if ( !showFolders) {
               style_display   ='none';
               db_display      = 'none';
            }
            else {
               if ( shareDB.checked) {
                  db_display      = 'none';
               }
            }
            

<?php if ( $this->isCreateView) { ?>
try {
               document.getElementById("panelsharing").style.display = style_display;
            }
            catch( ee) {}
<?php } ?>
document.getElementById("show_sku").style.display     = style_display;
            // document.getElementById("title").style.display        = style_display;
            document.getElementById("tr_toSiteID").style.display  = style_display;
            document.getElementById("admin_user").style.display   = style_display;

            document.getElementById("admin_username").style.display  = db_display;
            document.getElementById("admin_login").style.display     = db_display;
            document.getElementById("admin_email").style.display     = db_display;
            document.getElementById("admin_userpsw").style.display   = db_display;
            
            document.getElementById("tr_shareDB").style.display   = style_display;


            document.getElementById("db_host").style.display      = db_display;
            document.getElementById("db_name").style.display      = db_display;
            document.getElementById("db_user").style.display      = db_display;
            document.getElementById("db_psw").style.display       = db_display;
            document.getElementById("table_prefix").style.display = db_display;
            document.getElementById("site_name").style.display    = db_display;
<?php if ( MultisitesHelper::isSymbolicLinks()) { ?>
document.getElementById("alias_folder").style.display = style_display;
<?php } ?>
document.getElementById("media_folder").style.display = style_display;
            document.getElementById("image_folder").style.display = style_display;

            document.getElementById("tr_setDefaultJLang").style.display      = db_display;
            document.getElementById("tr_setDefaultTemplate").style.display      = db_display;
            document.getElementById("tr_setDefaultMenu").style.display      = db_display;

         	var frame_id = "site_ids_frame";
         	var frame = document.getElementById(frame_id);
      		if ( frame) {
      		   var parentFrame = frame.getParent();
      		   parentFrame.setStyle('height', 'auto');
      		}

         }
      };

      ajax.open( "GET", "index.php?option=com_multisites&task=ajaxGetUsersList&<?php echo J2WinUtility::getToken2Win() . '=1'; ?>&site_id="+site_id,  true);
      ajax.send(null);
   }
   
   function enableSource( action, field_id)
   {
      var elt = document.getElementById( field_id);
      if ( action == 'copy' || action == 'unzip' || action == 'SL' || action == 'dirlinks') {
         elt.type       = 'text';
         elt.readOnly   = false;
      }
      else {
         elt.type       = 'hidden';
        elt.readOnly   = true;
      }
   }

   function filterActions( action, nbrows)
   {
      var i = 0;
      for ( i=0; i<nbrows; i++) {
         var field_id = 'SL_actions' + i;
         var elt = document.getElementById( field_id);
         if ( elt != null) {
            var show = true;
            if ( action == 'hide') {
               if ( elt.value == 'ignore') {
             var show = false;
               }
            }
            var eltRow = document.getElementById( 'row_'+i);
            if ( show) {
               eltRow.style.display="";
            }
            else {
               eltRow.style.display="none";
            }
         }
      }
   }
   
   function onSharedDB( checked)
   {
      var db_display = '';
      if ( checked){
         db_display = 'none';
      }

      // document.getElementById("show_sku").style.display     = db_display;

      document.getElementById("admin_user").style.display      = db_display;
      
      document.getElementById("admin_username").style.display  = db_display;
      document.getElementById("admin_login").style.display     = db_display;
      document.getElementById("admin_email").style.display     = db_display;
      document.getElementById("admin_userpsw").style.display   = db_display;

      document.getElementById("db_host").style.display      = db_display;
      document.getElementById("db_name").style.display      = db_display;
      document.getElementById("db_user").style.display      = db_display;
      document.getElementById("db_psw").style.display       = db_display;
      document.getElementById("table_prefix").style.display = db_display;

      document.getElementById("media_folder").style.display = db_display;
      document.getElementById("image_folder").style.display = db_display;


   	var frame_id = 'site_ids_frame';
   	var frame = document.getElementById(frame_id);
		if ( frame) {
		   frame.getParent().setStyle('height', 'auto');
		}
   }

   function onShowFTPField( radiovalue)
   {
      var ftp_display = 'none';
      if ( radiovalue=='0' || radiovalue=='1') {
         ftp_display = '';
      }

      document.getElementById("tr_toFTP_host").style.display      = ftp_display;
      document.getElementById("tr_toFTP_port").style.display      = ftp_display;
      document.getElementById("tr_toFTP_user").style.display      = ftp_display;
      document.getElementById("tr_toFTP_psw").style.display       = ftp_display;
      document.getElementById("tr_toFTP_rootpath").style.display  = ftp_display;
   }

   function toggleSelectListSize(id, maxSize, frame_id)
   {
   	var link = document.getElementById(id+'_toggle');
   	var el = document.getElementById(id);
   	if (link && el) {
if (!el.getAttribute('rel')) {
   			el.setAttribute('rel', el.getAttribute('size'));
   		}
   		if (el.getAttribute('size') == el.getAttribute('rel')) {
   			el.setAttribute('size', ( el.length > maxSize ) ? maxSize : el.length);
   			link.getElement('span.show').setStyle('display', 'none');
   			link.getElement('span.hide').setStyle('display', 'inline');
   		} else {
   			el.setAttribute('size', el.getAttribute('rel'));
   			link.getElement('span.hide').setStyle('display', 'none');
   			link.getElement('span.show').setStyle('display', 'inline');
   		}
   	}
   	var frame = document.getElementById(frame_id);
		if ( frame) {
		   frame.getParent().setStyle('height', 'auto');
		}
   }
   
   function updateSLActions( nbFiles)
   {
      var id = '';
     var elt = null;
      var i = 0;
      var j = 0;
   	var eltAll = document.getElementById( 'SL_actionsAll');
      for( i=0; i<nbFiles; i++) {
         id = 'SL_actions'+i;
      	var elt = document.getElementById(id);
      	if ( elt && elt.type == eltAll.type) {
      	   for ( j=0; j<elt.options.length; j++) {
      	      if ( elt.options[j].value == eltAll.value) {
            	   elt.value = eltAll.value;
            	   enableSource( elt.value, 'SL_files['+i+']');
            	   break;
      	      }
      	   }
      	}
      }
      return true;
   }

   function updateSLFiles( nbFiles)
   {
      var id = '';
      var elt = null;
      var i = 0;
   	var eltAll = document.getElementById( 'SL_files_All');
      for( i=0; i<nbFiles; i++) {
         id = 'SL_files['+i+']';
      	var elt = document.getElementById(id);
      	if ( elt && elt.value.length<=0 && elt.type=='text' && elt.style.display=='') {
      	   elt.value = eltAll.value;
      	}
      }
      return true;
   }
   function clearSLFiles( nbFiles)
   {
      var id = '';
      var elt = null;
      var i = 0;
      for( i=0; i<nbFiles; i++) {
         id = 'SL_files['+i+']';
      	var elt = document.getElementById(id);
      	if ( elt && elt.value.length>0) {
      	   elt.value = '';
      	}
      }
      return true;
   }
//-->
</script>
<div id="multisite-template">
<?php if ( !empty($this->ads)) { ?>
<table border="0">
   <tr><td><?php echo $this->ads; ?></td></tr>
</table>
<?php } ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<?php
if ( file_exists( JPATH_LIBRARIES.'/joomla/html/pane.php')) { jimport('joomla.html.pane'); }
else { require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR.'/libraries/joomla/html/pane.php'); }
$pane =& JPane::getInstance();
echo $pane->startPane( 'pane' );
echo $pane->startPanel( JText::_( 'Common'), 'panelcmn' );
echo $this->loadTemplate('common');
echo $pane->endPanel();
if ( true || $this->canShowDeployDir()) {
echo $pane->startPanel( JText::_( 'Folders and files'), 'panelunix' );
echo $this->loadTemplate('unix');
echo $pane->endPanel();
}
if ( !$this->isCreateView) {
echo $pane->startPanel( JText::_( 'Sharing'), 'panelsharing" style="display:none;');
}
else {
echo $pane->startPanel( JText::_( 'Sharing'), 'panelsharing');
}
?><fieldset id="treeview">
       <div id="dbsharing-tree_tree"></div>
       <?php echo $this->loadTemplate('sharing'); ?>
</fieldset>
   <span class="note">
		<strong><?php echo JText::_( 'TEMPLATE_VIEW_EDT_SHR_NOTES' ); ?></strong>
   </span>
<?php
echo $pane->endPanel();
echo $pane->endPane();
?>
<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task" value="saveTemplate" />
	<input type="hidden" name="isnew" value="<?php if ($this->isnew) { echo '1';} else { echo'0';} ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
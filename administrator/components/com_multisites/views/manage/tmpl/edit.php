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
?><?php defined('_JEXEC') or die('Restricted access');
jimport( 'joomla.utilities.utility.php');
$document = & JFactory::getDocument();
$document->addStylesheet( rtrim( JURI::base( true), '/index.php').'/components/com_multisites/css/form.css');
?>
<style type="text/css">
 <!--
.tool-tip, .tip { max-width: 600px; }
 -->
</style>
<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
		var form = document.adminForm;

		if (pressbutton == 'savesite') {
			if ( form.id.value == '' ) {
				alert( '<?php echo JText::_( 'Please enter a site id', true ); ?>' );
				form.menutype.focus();
				return;
			}
			var r = new RegExp("[\']", "i");
			if ( r.exec(form.id.value) ) {
				alert( '<?php echo JText::_( 'The site id cannot contain a \'', true ); ?>' );
				form.menutype.focus();
				return;
			}
			submitform( 'savesite' );
		} else {
			submitform( pressbutton );
		}
	}


   function refreshShareDBStyle( style_display)
   {
      document.getElementById("tr_toDBHost").style.display  = style_display;
      document.getElementById("tr_toDBName").style.display  = style_display;
      document.getElementById("tr_toDBUser").style.display  = style_display;
      document.getElementById("tr_toDBPsw").style.display   = style_display;
      document.getElementById("tr_toPrefix").style.display  = style_display;

      document.getElementById("tr_newAdminEmail").style.display   = style_display;
      document.getElementById("tr_newAdminPsw").style.display     = style_display;

   	document.getElementById("tr_toDBHost").getParent().setStyle('height', 'auto');
   }

   function refreshShowConfigStyle( style_display)
   {
      var elt = document.getElementById("dbprefix");
      try {
         if ( elt.innerHTML.length > 0) {
            style_display = '';
         }
      } catch( e1) {}
            
      document.getElementById("tr_toSiteName").style.display      = style_display;
      document.getElementById("tr_toMetaDesc").style.display      = style_display;
      document.getElementById("tr_toMetaKeys").style.display      = style_display;
   }

   function refreshShowFoldersStyle( style_display)
   {
//      document.getElementById("tr_toSiteName").style.display      = style_display;
      document.getElementById("tr_newAdminEmail").style.display   = style_display;
      document.getElementById("tr_newAdminPsw").style.display     = style_display;
      document.getElementById("tr_media_dir").style.display       = style_display;
      document.getElementById("tr_images_dir").style.display      = style_display;

//      document.getElementById("tr_gray_message").style.display    = style_display;

   	document.getElementById("tr_toDBHost").getParent().setStyle('height', 'auto');
   }

   function refreshShowFolders()
   {
      var showFolders   = false;
      var tpl_toPrefix  = document.getElementById("toPrefix_defaulthidden").value;
      var toPrefix      = document.getElementById("toPrefix").value;
      if ( tpl_toPrefix.length>0 || toPrefix.length>0) {
         showFolders = true;
      }
      var style_display = '';
      if ( !showFolders) {
         style_display = 'none';
      }
      refreshShowFoldersStyle( style_display);
      refreshShowConfigStyle( style_display);
/*
      if ( style_display.length<=0) {
         var elt =document.getElementById("toSiteName");
         elt.select();
         elt.focus();
      }
*/
   }

   function refreshShowFTPStyle( style_display)
   {
      document.getElementById("tr_toFTP_host").style.display         = style_display;
      document.getElementById("tr_toFTP_port").style.display         = style_display;
      document.getElementById("tr_toFTP_user").style.display         = style_display;
      document.getElementById("tr_toFTP_psw").style.display          = style_display;
      document.getElementById("tr_toFTP_rootpath").style.display     = style_display;document.getElementById("tr_toFTP_host").getParent().setStyle('height', 'auto');
   }

   function refreshShowFTPFields()
   {
      var showFields   = false;
      var tpl_toFTP_enable  = document.getElementById("toFTP_enable_defaulthidden").value;
      var toFTP_enable0      = document.getElementById("toFTP_enable0");
      var toFTP_enable1      = document.getElementById("toFTP_enable1");
      if ( toFTP_enable0.checked || toFTP_enable1.checked) {
         showFields = true;
      }
      else if ( tpl_toFTP_enable=='0' || tpl_toFTP_enable=='1') {
         showFields = true;
      }
      var style_display = '';
      if ( !showFields) {
         style_display = 'none';
      }
      refreshShowFTPStyle( style_display);
   }
   
   function refreshTemplateDir( template_id)
   {
      if( template_id == '[unselected]') {
         document.getElementById("tr_shareDB").style.display = 'none';
         refreshShowConfigStyle( 'none');
         refreshShareDBStyle( 'none');
         refreshShowFoldersStyle( 'none');
         return;
      }
      document.getElementById("tr_shareDB").style.display = '';
      refreshShareDBStyle( '');

<?php @include( dirname(__FILE__).'/edit.js.php'); ?>
}

   function clearExpiration()
   {
      document.getElementById("expiration").value = "";
   }
 
   function onSharedDB( checked)
   {
      var db_display = '';
      if ( checked) {
         db_display = 'none';
      }
      document.getElementById("tr_toDBHost").style.display  = db_display;
      document.getElementById("tr_toDBName").style.display  = db_display;
      document.getElementById("tr_toDBUser").style.display  = db_display;
      document.getElementById("tr_toDBPsw").style.display   = db_display;
      document.getElementById("tr_toPrefix").style.display  = db_display;
      
      document.getElementById("tr_newAdminEmail").style.display   = db_display;
      document.getElementById("tr_newAdminPsw").style.display     = db_display;

      document.getElementById("tr_media_dir").style.display       = db_display;
      document.getElementById("tr_images_dir").style.display      = db_display;
      
   	document.getElementById("tr_toDBHost").getParent().setStyle('height', 'auto');
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

   	document.getElementById("tr_toFTP_host").getParent().setStyle('height', 'auto');
   }

   function toggleSelectListSize(id, maxSize, frame_id)
   {
   	var frame = document.getElementById(frame_id);
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
		if ( frame) {
		   frame.getParent().setStyle('height', 'auto');
		}
   }
//-->
</script>
<div id="multisite-manage">
<?php if ( !empty($this->ads)) { ?>
<table border="0">
   <tr><td><?php echo $this->ads; ?></td></tr>
</table>
<?php } ?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<?php 
?>
<div class="multisites-form">
<div id="edit-common">
<div class="col-left-fr">
<div class="col-left-pad">
<?php
 echo $this->loadTemplate('general');
?>
</div>
</div>
</div>
</div>
<?php 
?>
<div class="col-right-fr">
<div class="body">
<?php
 if ( file_exists( JPATH_LIBRARIES.'/joomla/html/pane.php')) { jimport('joomla.html.pane'); }
else { require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR.'/libraries/joomla/html/pane.php'); }
$paneCmn = &JPane::getInstance('sliders', array('allowAllClose' => true));
echo $paneCmn->startPane("common-pane");
$paneNbr = 1;
$this->assignRef('paneCmn', $paneCmn);
$this->assignRef('paneNbr', $paneNbr);
if ( !empty( $this->lists['templates'])) {
echo $this->loadTemplate('templaterule');
}
echo $this->loadTemplate('geolocalisation');
echo $this->loadTemplate('browser');
echo $this->loadTemplate('newdb');
echo $this->loadTemplate('deploydir');
echo $this->loadTemplate('ftp');
echo $this->loadTemplate('dbinfo');
echo $paneCmn->endPane();
?>
</div>
</div>

	<input type="hidden" name="option"        value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task"          value="saveSite" />
	<input type="hidden" name="site_prefix"   value="<?php echo !empty( $this->row->site_prefix) ? $this->row->site_prefix : ''; ?>" />
	<input type="hidden" name="site_alias"    value="<?php echo !empty( $this->row->site_alias) ? $this->row->site_alias : ''; ?>" />
	<input type="hidden" name="siteComment"   value="<?php echo !empty( $this->row->siteComment) ? $this->row->siteComment : ''; ?>" />
	<input type="hidden" name="isnew"         value="<?php if ($this->isnew) { echo '1';} else { echo'0';} ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
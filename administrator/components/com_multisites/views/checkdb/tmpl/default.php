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
 $sites = $this->sites;
$lists = $this->lists;
?>
<script language="javascript" type="text/javascript">
<!--
	var g_curtoken = '<?php echo J2WinUtility::getToken2Win() . '=1'; ?>';
	
   function toggleChangeItem( elt)
   {
      if ( elt.className == 'close') {
         elt.addClass('open').removeClass('close');
      }
      else {
         elt.addClass('close').removeClass('open');
      }
   }
   
   function updateBoxChecked()
   {
      var cbElt;
      var c=0;
		for (var i = 0; (cbElt=document.getElementById( 'cb'+i)) != null; i++) {
			c += (cbElt.checked == true ? 1 : 0);
		   
		}
		checkbox = document.getElementById( 'adminForm');
		if ( checkbox != null && checkbox.boxchecked) {
			checkbox.boxchecked.value = c;
		}
   }
   
   function selectedSchema( elt, rowNbr)
   {
      if ( elt.value == "" || elt.value == "[unselected]") {}
      else {
         var eltTR = document.getElementById( 'siteid_'+rowNbr);
         var eltCB = document.getElementById( 'cb'+rowNbr);
         
         if ( eltTR != null) { eltTR.addClass('rowselected'); }
         if ( eltCB != null) { eltCB.checked = true; }
         updateBoxChecked();
      }
   }
   function selectedPackage( elt, rowNbr)
   {
      selectedSchema( elt, rowNbr);
   }
   function selectedLegacyMode( elt, rowNbr)
   {
      selectedSchema( elt, rowNbr);
   }
   
   function selectRow( checked, tr_id)
   {
      var eltTR = document.getElementById( tr_id);
      isChecked( checked);
      if ( checked) {
         if ( eltTR != null) { eltTR.addClass('rowselected'); }
      }
      else {
         if ( eltTR != null) { eltTR.removeClass('rowselected'); }
      }
   }
	
   function DownloadPackage( available_id, downloaded_id)
  {
      var ajax;
      var elt = document.getElementById( available_id);
      var url = elt.value;
      var eltDownload = document.getElementById( downloaded_id);
      var selected_value = eltDownload.value
      var eltParent      = eltDownload.getParent();
      eltParent.addClass('downloading');
      try {  ajax = new ActiveXObject('Msxml2.XMLHTTP');   }
      catch (e)
      {try {   ajax = new ActiveXObject('Microsoft.XMLHTTP');    }
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
   // Verify that we receive a JSON 'downloadpackage' type.
               if ( ajax.responseText.indexOf( '"type":"downloadpackage"') > 0) {
                  // JSON decode
                  var json = eval("(" + ajax.responseText + ")");
                  
                  msg = null;
                  if ( json.errors == null) {}
                  else {
                     if ( json.errors.length <= 1) {
                        msg = json.errors[0];
                     }
                     else {
                        msg = '<ul>'+"\n";
                        for (var i=0; i<json.errors.length; i++) {
                           msg += '<li>' + json.errors[i] + '</li>' +"\n";
                        }
                        msg += '</ul>'+"\n";
            }
                  }

                  if ( json.downloadedpackages != null) {
                     eltParent.innerHTML = json.downloadedpackages;
                  }
                  if ( msg == null || msg.length<=0)  {}
                  else {
                     eltParent.innerHTML = eltParent.innerHTML + msg;
                  }
               }
               else {
eltParent.innerHTML = eltParent.innerHTML + "<div>Unexpected answer</div>";
               }
            }
            else {
               eltParent.innerHTML = eltParent.innerHTML + "<div>Error code " + ajax.status + "</div>";
            }
            
            eltParent.addClass('downloaded').removeClass('downloading');
         }
      };

      ajax.open( "GET", "index.php?option=<?php echo $this->option; ?>&task=ajaxDownloadPackage&<?php echo J2WinUtility::getToken2Win() . '=1'; ?>&url="+encodeURIComponent(url)+"&selected_value="+encodeURIComponent(selected_value),  true);
      ajax.send(null);
   }
   
   function setColSizeAuto( elt)
   {
      var restoreElt = elt.getParent();
      var thElt      = restoreElt.getParent();
		var tableElt   = thElt.getParent().getParent().getParent();
		if (!thElt.getAttribute('colClassname')) {
			thElt.setAttribute('colClassname', thElt.className);
		}
		var colClassName = thElt.getAttribute('colClassname');

		tableElt.getElements( 'tbody tr td.'+colClassName).removeClass( 'col_minimize');
		
		restoreElt.setStyle('display', 'none');
		var aElt = thElt.getElement('a.coltitle');
		if ( aElt != null) {
		   aElt.setStyle('display', 'inline');
		}
		var spanElt = thElt.getElement('span.coltitle');
		if ( spanElt != null) {
		   spanElt.setStyle('display', 'inline');
		}
		thElt.removeClass( 'col_minimize');
		thElt.getElement('div.colsize_minimize').setStyle('display', 'inline');

   }

   function setColSizeMinimize( elt)
   {
      var minElt   = elt.getParent();
      var thElt    = minElt.getParent();
		var tableElt = thElt.getParent().getParent().getParent();
		if (!thElt.getAttribute('colClassname')) {
			thElt.setAttribute('colClassname', thElt.className);
		}
		var colClassName = thElt.getAttribute('colClassname');

		
		minElt.setStyle('display', 'none');
		var aElt = thElt.getElement('a.coltitle');
		if ( aElt != null) {
		   aElt.setStyle('display', 'none');
		}
		var spanElt = thElt.getElement('span.coltitle');
		if ( spanElt != null) {
		   spanElt.setStyle('display', 'none');
		}
		thElt.getElement('div.colsize_auto').setStyle('display', 'inline');
		thElt.addClass( 'col_minimize');
		
		tableElt.getElements( 'tbody tr td.'+colClassName).addClass( 'col_minimize');
   }
//-->
</script>
<?php include_once( dirname(__FILE__).'/ajaxcheckdb_datamodel_script.php'); ?>
<div class="checkdb">
<div class="warning"><?php echo JText::_('Before any action, we recommend that you perform a backup of your environment'); ?></div>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="filteringbar">
<?php if ( !empty($this->ads)) { ?>
<tr><td colspan="2"><?php echo $this->ads; ?></td></tr>
<?php } ?>
<tr>
   		<td align="left" width="100%">
   			<?php echo JText::_( 'Filter' ); ?>:
   			<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
   			<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
   			<button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
   		</td>
			<td class="selections">
			   <table border="0">
			      <tr>
<?php  ?><td><?php echo ''.$lists['availablepackages']; ?></td>
      				<td>
                  	<div class="download_button">
                  	<div class="button2-left">
                  		<div class="blank">
                  			<a onclick="DownloadPackage( 'filter_availablepackages', 'filter_downloadedpackages');" title="Download the selected available package and update the downloaded package list">
                  				<?php echo JText::_( 'Download package'); ?></a>
                  		</div>
                  	</div>
                  	</div>
      				</td>
			         <td><?php echo $lists['downloadedpackages']; ?></td>
<?php  ?><td><?php echo JText::_('Default schema action').' :'.$lists['schema']; ?></td>
				   </tr>
				</table>
			</td>
		</tr>
	</table>
   <table class="adminlist">
      <thead>
         <tr>
   			<th class="num">
   				<?php echo JText::_( 'Num' ); ?>
</th>
   			<th class="toggle">
   				<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $this->sites ); ?>);" />
   			</th>
            <th class="id">
   				<?php $coltitle = JHTML::_('grid.sort', JText::_( 'CHECKDB_LIST_ID'), 'id', @$lists['order_Dir'], @$lists['order'] );
$coltitle = '<span class="coltitle">'.JText::_( 'CHECKDB_LIST_ID').'</span>';
echo $coltitle; ?>
</th>
   			<th class="sitename">
   			   <div class="colsize_auto" style="display: none;"><a onclick="setColSizeAuto( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_right.png" alt="Restore column size" title="Restore column size" /></div>
   			   <div class="colsize_minimize"><a onclick="setColSizeMinimize( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_left.png" alt="Minimize column" title="Minimize column" /></div>
   				<?php $coltitle = JHTML::_('grid.sort', JText::_( 'CHECKDB_LIST_SITENAME'), 'sitename', @$lists['order_Dir'], @$lists['order'] );
$coltitle = str_replace( '<a ', '<a class="coltitle" ', $coltitle);
$coltitle = '<span class="coltitle">'.JText::_( 'CHECKDB_LIST_SITENAME').'</span>';
echo $coltitle; ?>
</th>
<?php if ( $this->option == 'com_multisites') { ?>
<th class="domains">
   			   <div class="colsize_auto" style="display: none;"><a onclick="setColSizeAuto( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_right.png" alt="Restore column size" title="Restore column size" /></div>
   			   <div class="colsize_minimize"><a onclick="setColSizeMinimize( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_left.png" alt="Minimize column" title="Minimize column" /></div>
   				<?php $coltitle = JHTML::_('grid.sort', JText::_( 'CHECKDB_LIST_DOMAINS'), 'domains', @$lists['order_Dir'], @$lists['order'] );
$coltitle = str_replace( '<a ', '<a class="coltitle" ', $coltitle);
$coltitle = '<span class="coltitle">'.JText::_( 'CHECKDB_LIST_DOMAINS').'</span>';
echo $coltitle; ?>
</th>
<?php } ?>
<th class="dbtype">
   			   <div class="colsize_auto" style="display: none;"><a onclick="setColSizeAuto( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_right.png" alt="Restore column size" title="Restore column size" /></div>
   			   <div class="colsize_minimize"><a onclick="setColSizeMinimize( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_left.png" alt="Minimize column" title="Minimize column" /></div>
   				<?php $coltitle = JHTML::_('grid.sort', JText::_( 'CHECKDB_LIST_DBTYPE'), 'dbtype', @$lists['order_Dir'], @$lists['order'] );
$coltitle = str_replace( '<a ', '<a class="coltitle" ', $coltitle);
$coltitle = '<span class="coltitle">'.JText::_( 'CHECKDB_LIST_DBTYPE').'</span>';
echo $coltitle; ?>
</th>
   			<th class="db">
   			   <div class="colsize_auto" style="display: none;"><a onclick="setColSizeAuto( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_right.png" alt="Restore column size" title="Restore column size" /></div>
   			   <div class="colsize_minimize"><a onclick="setColSizeMinimize( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_left.png" alt="Minimize column" title="Minimize column" /></div>
   				<?php $coltitle = JHTML::_('grid.sort', JText::_( 'CHECKDB_LIST_DB'), 'db', @$lists['order_Dir'], @$lists['order'] );
$coltitle = str_replace( '<a ', '<a class="coltitle" ', $coltitle);
$coltitle = '<span class="coltitle">'.JText::_( 'CHECKDB_LIST_DB').'</span>';
echo $coltitle; ?>
</th>
   			<th class="dbprefix">
   			   <div class="colsize_auto" style="display: none;"><a onclick="setColSizeAuto( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_right.png" alt="Restore column size" title="Restore column size" /></div>
   			   <div class="colsize_minimize"><a onclick="setColSizeMinimize( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_left.png" alt="Minimize column" title="Minimize column" /></div>
   				<?php $coltitle = JHTML::_('grid.sort', JText::_( 'CHECKDB_LIST_PREFIX'), 'prefix', @$lists['order_Dir'], @$lists['order'] );
$coltitle = str_replace( '<a ', '<a class="coltitle" ', $coltitle);
$coltitle = '<span class="coltitle">'.JText::_( 'CHECKDB_LIST_PREFIX').'</span>';
echo $coltitle; ?>
</th>
   			<th class="dmresult">
   				<?php $coltitle = JHTML::_('grid.sort', JText::_( 'CHECKDB_LIST_DMRESULT'), 'dmresult', @$lists['order_Dir'], @$lists['order'] );
$coltitle = '<span class="coltitle">'.JText::_( 'CHECKDB_LIST_DMRESULT').'</span>';
echo $coltitle; ?>
</th>
         </tr>
      </thead>
      <tbody>
<?php 
$ignore = empty( $this->cid) ? '' : '[ignore]';
$downloadedpackagesvalues = JRequest::getVar('downloadedpackages', array(), '', 'array');
$default_downloadpackage = JRequest::getString('filter_downloadedpackages', $ignore);
if ( !empty( $default_downloadpackage) && $default_downloadpackage == '[unselected]') { $default_downloadpackage = ''; }
$schemavalues = JRequest::getVar('schema', array(), '', 'array');
$default_schema = JRequest::getString('filter_schema', $ignore);
if ( !empty( $default_schema) && $default_schema == '[unselected]') { $default_schema = ''; }
$legacySQLvalues = JRequest::getVar('legacysql', array(), '', 'array');
$legacyModevalues = JRequest::getVar('legacymode', array(), '', 'array');
$userSQLvalues = JRequest::getVar('usersql', array(), '', 'array');
$i = 0; $k = 0;
$boxchecked = 0;
foreach( $sites as $site_id => $site) {
$this->tr_id = $tr_id = 'siteid_'.$i;
$checked = '';
$rowSelectedClass = '';

if ( empty( $this->cid)) { $action = 'getdbinfo';
}

else if ( in_array( $site->id, $this->cid)) {
if ( !empty( $this->action)) { $action = $this->action; }
else { $action = 'getdbinfo'; }
$checked = 'checked="checked"';
$boxchecked++;
$rowSelectedClass=' rowselected';
}

else { $action = ''; }
?>
<tr id="<?php echo $tr_id; ?>" class="<?php echo "row". $k.$rowSelectedClass; ?>">
   			<td class="num"><?php echo $this->pagination->limitstart + 1 + $i; ?></td>
   			<td class="toggle"><div class="loading"></div><input type="checkbox" onclick="selectRow(this.checked, '<?php echo $tr_id; ?>');" value="<?php echo $site->id; ?>" name="cid[]" id="cb<?php echo $i; ?>" <?php echo $checked; ?>></td>
            <td class="id"><?php echo $site->id; ?></td>
            <td class="sitename"><div><?php echo $site->sitename; ?></div></td>
<?php if ( $this->option == 'com_multisites') { ?>
<td class="domains">
            <div>
<?php
 if ( empty( $site->domains)) {
echo '&nbsp;';
}
else {
echo '<ul>';
foreach( $site->domains as $idom => $domain) {
$urldomain = $domain;
if ( !empty( $site->indexDomains) && !empty( $site->indexDomains[$idom])) {
$urldomain = $site->indexDomains[$idom];
}
echo '<li>'.$urldomain. '</li>';
}
echo '</ul>';
}
?>
</div>
            </td>
<?php } ?>
<td class="dbtype"><div><?php echo !empty( $site->dbtype) ? $site->dbtype : '&nbsp;'; ?></div></td>
   			<td class="db"><div><?php echo !empty( $site->db) ? $site->db : '&nbsp;'; ?></div></td>
   			<td class="dbprefix"><div><?php echo !empty( $site->dbprefix) ? $site->dbprefix : '&nbsp;'; ?></div></td>
            <td id="dmresult_<?php echo $i; ?>" class="dmresult">&nbsp;
               <div class="err" id="err_<?php echo $i; ?>"></div>
               <div class="errmsg" id="errmsg_<?php echo $i; ?>"></div>

<?php  if ( empty( $this->action) && !empty( $site->preprocess_datamodel)) {
$this->site = $site;
include( dirname( __FILE__).'/default_datamodel.php');
}
else if ( is_array( $action)) { ?>
<ul class="actions">
<?php  $j= 0;
foreach( $action as $act => $msg) {
$subresult_id = 'subresult_'.$i.'_'.$j;
?>
<li><!-- action=<?php echo $act; ?>; site_id=<?php echo $site->id; ?>; subresult_id=<?php echo $subresult_id; ?>; tr_id=<?php echo $tr_id; ?>; -->
                         <?php echo $msg; ?><span id="<?php echo $subresult_id; ?>"></span>
                     </li>
<?php  $j++;
} ?>
</ul>
<?php  } else if ( !empty( $action)) { ?>
<!-- action=<?php echo $action; ?>; site_id=<?php echo $site->id; ?>; tr_id=<?php echo $tr_id; ?>; -->
<?php  } ?>
</td>
         </tr>
<?php  $k = 1 - $k;
$i++;
} 
?>
</tbody>
   </table>
	<input type="hidden" name="option"           value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task"             value="checkdb" />
	<input type="hidden" name="boxchecked"       value="<?php echo $boxchecked; ?>" />
	<input type="hidden" name="filter_order"     value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
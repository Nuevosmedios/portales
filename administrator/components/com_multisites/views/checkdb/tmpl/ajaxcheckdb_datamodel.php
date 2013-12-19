<?php
// file: ajaxcheckdb_datamodel.php.
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
<?php if ( empty( $datamodel) && !empty( $this->datamodel)) { $datamodel = $this->datamodel; }
if ( !empty( $datamodel)) {
?>
<tr>
   <td class="extension_id"><div><?php echo $datamodel->extension_id; ?></div></td>
   <td class="name"><div><?php echo $datamodel->name; ?></div></td>
   <td class="code_version"><div>
      <div><?php echo !empty( $datamodel->code_version) ? $datamodel->code_version : '&nbsp;'; ?></div>
      <div class="downloadedpackages"><?php echo $this->getDownloadedPackages( $datamodel); ?></div>
   </div></td>
   <td class="type"><div><?php echo $datamodel->type; ?></div></td>
   <td class="element"><div><?php echo $datamodel->element; ?></div></td>
   <td class="folder"><div><?php echo !empty( $datamodel->folder) ? $datamodel->folder: '&nbsp;'; ?></div></td>
   <td class="version_id"><div>
      <div><?php echo !empty( $datamodel->version_id)? $datamodel->version_id: '?'; ?></div>
      <div class="schema"><?php $schemaList = $this->getSchemaList( $datamodel); echo $schemaList; ?></div>
<?php if ( !empty( $schemaList)) { ?>
<div><?php echo $this->getLegacyModeList( $datamodel);?></div>
      <div class="usersql">
<?php  ?><div><label><?php echo JText::_( 'Free SQL') .' :'; ?></label></div>
         <textarea id="usersql<?php echo $this->site->id.$datamodel->extension_id; ?>"
                   rows="5" cols="10" 
                   name="usersql[<?php echo $this->site->id; ?>][<?php echo $datamodel->extension_id; ?>]"
                   onfocus="this.setAttribute('rows', 50); this.setAttribute('cols', 100);"
                   onblur="this.setAttribute('rows', 5); this.setAttribute('cols', 10);"
                   ><?php echo !empty( $datamodel->usersql) ? $datamodel->usersql: ''; ?></textarea>
      </div>
<?php } ?>
</div></td>
   <td class="schemainfo">
      <div class="jpath_root">
         <label><?php echo JText::_( 'Extension path').' :'; ?></label>
         <span class="jpath_extension"><?php if ( !empty( $datamodel->jpath_extension)) { echo $datamodel->jpath_extension; }
else if ( !empty( $this->site->jpath_root)) { echo $this->site->jpath_root; }
else { echo JText::_( 'Unknown root path'); }
?></span>
      </div>
      <div class="summary" id="summary<?php echo $this->site->id.$datamodel->extension_id; ?>">
<?php  if ( !empty( $datamodel->ajaxAction)) {
echo $datamodel->ajaxAction;
}
include( dirname(__FILE__).'/ajaxcheckdb_datamodel_subresult.php');
?>
</div>
   </td>
</tr>
<?php } ?>

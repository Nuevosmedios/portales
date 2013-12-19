<?php
// file: ajaxcheckdb_datamodel_thead.php.
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
 if ( !isset( $action)) { $action = $this->action; }
?>
<thead>
<tr>
<th class="extension_id">
   <div class="colsize_auto" style="display: none;"><a onclick="setColSizeAuto( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_right.png" alt="Restore column size" title="Restore column size" /></div>
   <div class="colsize_minimize"><a onclick="setColSizeMinimize( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_left.png" alt="Minimize column" title="Minimize column" /></div>
   <span class="coltitle" title="<?php echo JText::_( 'AJAXCHECKDB_EXTENSION_ID_TITLE'); ?>"><?php echo JText::_( 'AJAXCHECKDB_EXTENSION_ID'); ?></span>
</th>
<th class="name">
   <div class="colsize_auto" style="display: none;"><a onclick="setColSizeAuto( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_right.png" alt="Restore column size" title="Restore column size" /></div>
   <div class="colsize_minimize"><a onclick="setColSizeMinimize( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_left.png" alt="Minimize column" title="Minimize column" /></div>
   <span class="coltitle" title="<?php echo JText::_( 'AJAXCHECKDB_NAME_TITLE'); ?>"><?php echo JText::_( 'AJAXCHECKDB_NAME'); ?></span>
</th>
<th class="code_version">
   <div class="colsize_auto" style="display: none;"><a onclick="setColSizeAuto( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_right.png" alt="Restore column size" title="Restore column size" /></div>
   <div class="colsize_minimize"><a onclick="setColSizeMinimize( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_left.png" alt="Minimize column" title="Minimize column" /></div>
   <span class="coltitle" title="<?php echo JText::_( 'AJAXCHECKDB_CODE_VERSION_TITLE'); ?>"><?php echo JText::_( 'AJAXCHECKDB_CODE_VERSION'); ?></span>
</th>
<th class="type">
   <div class="colsize_auto" style="display: none;"><a onclick="setColSizeAuto( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_right.png" alt="Restore column size" title="Restore column size" /></div>
   <div class="colsize_minimize"><a onclick="setColSizeMinimize( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_left.png" alt="Minimize column" title="Minimize column" /></div>
   <span class="coltitle" title="<?php echo JText::_( 'AJAXCHECKDB_TYPE_TITLE'); ?>"><?php echo JText::_( 'AJAXCHECKDB_TYPE'); ?></span>
</th>
<th class="element">
   <div class="colsize_auto" style="display: none;"><a onclick="setColSizeAuto( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_right.png" alt="Restore column size" title="Restore column size" /></div>
   <div class="colsize_minimize"><a onclick="setColSizeMinimize( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_left.png" alt="Minimize column" title="Minimize column" /></div>
   <span class="coltitle" title="<?php echo JText::_( 'AJAXCHECKDB_ELEMENT_TITLE'); ?>"><?php echo JText::_( 'AJAXCHECKDB_ELEMENT'); ?></span></th>
<th class="folder">
   <div class="colsize_auto" style="display: none;"><a onclick="setColSizeAuto( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_right.png" alt="Restore column size" title="Restore column size" /></div>
   <div class="colsize_minimize"><a onclick="setColSizeMinimize( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_left.png" alt="Minimize column" title="Minimize column" /></div>
   <span class="coltitle" title="<?php echo JText::_( 'AJAXCHECKDB_FOLDER_TITLE'); ?>"><?php echo JText::_( 'AJAXCHECKDB_FOLDER'); ?></span>
</th>
<th class="version_id">
   <div class="colsize_auto" style="display: none;"><a onclick="setColSizeAuto( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_right.png" alt="Restore column size" title="Restore column size" /></div>
   <div class="colsize_minimize"><a onclick="setColSizeMinimize( this);"><img src="components/<?php echo $this->option; ?>/css/images/j_arrow_left.png" alt="Minimize column" title="Minimize column" /></div>
   <span class="coltitle" title="<?php echo JText::_( 'AJAXCHECKDB_VERSION_ID_TITLE'); ?>"><?php echo JText::_( 'AJAXCHECKDB_VERSION_ID'); ?></span>
</th>
<th class="status"><?php  echo JText::_( 'AJAXCHECKDB_STATUS_'.strtoupper( $action)); ?></th>
</tr>
</thead>

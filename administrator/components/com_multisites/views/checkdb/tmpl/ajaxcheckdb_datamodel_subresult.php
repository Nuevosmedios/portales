<?php
// file: ajaxcheckdb_datamodel_subresult.php.
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
if ( !empty( $datamodel->subActionResult)) {
?>
<div class="selectedFolder">
            <label><?php echo JText::_( 'Selected schema').' :'; ?></label>
            <span class="value"><?php echo $datamodel->subActionResult['selectedFolder']
. '|'
. $datamodel->subActionResult['selectedFolder_Label']
; ?></span>
         </div>
<?php if ( !empty( $this->enteredvalues['legacymode'])) { ?>
<div class="legacyMode">
            <label><?php echo JText::_( 'Legacy mode').' :'; ?></label>
            <span class="value"><?php echo implode( '|', $this->enteredvalues['legacymode']); ?></span>
         </div>
<?php } ?>
<div class="detail">
            <ul class="summary">
<?php
 foreach( array( 'unchecked', 'ok', 'error', 'skipped') as $key) {
$this->schemainfo( $key);
}
?></ul>
         </div>
<?php  } ?>
<?php } ?>

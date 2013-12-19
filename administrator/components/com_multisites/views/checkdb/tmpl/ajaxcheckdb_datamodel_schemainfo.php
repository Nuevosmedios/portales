<?php
// file: ajaxcheckdb_datamodel_schemainfo.php.
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
<li class="close" title="Click on the title to open/close the line"><span title="Open/Close the line" onclick="toggleChangeItem(this.getParent());"><?php echo JText::sprintf( $this->schema_label, count( $this->schema_result)); ?></span>
<?php  if ( in_array( $this->schema_key, array( 'error', 'skipped'))) {
$queries = '';
foreach( $this->schema_result as $changeItem) {
$queries .= $changeItem->updateQuery."\n";
}
?>
<div>
            	<div class="download_button">
            	<div class="button2-left">
            		<div class="blank">
            			<a  title="Copy all the SQL statment into the free SQL field"
            			    onclick="copyToUSerSQL( 'usersql<?php echo $this->site->id.$this->datamodel->extension_id; ?>', '<?php 
echo str_replace( array( "'", '"', "\n"),
array( '[:apos:]', '[:quote:]', '[:nl:]'),
$queries); ?>');"
            			><?php echo JText::_( 'Copy in free SQL'); ?></a>
            		</div>
            	</div>
            	</div>
            	<div style="clear:both;"></div>
            	</div>
<?php  } ?>
<div>
            <ul class="changeitem">
<?php  foreach( $this->schema_result as $changeItem) { ?>
<li><?php echo $changeItem->updateQuery;
if ( !empty( $changeItem->sqlErrorMsg)) {
?>
<ul class="sqlerrormsg">
                     <li><?php echo $changeItem->sqlErrorMsg;
echo '<br/><span class="file">'.$changeItem->file.'</span>';
if ( $this->schema_key == 'error') {
}
?></li>
                  </ul>
<?php  } else if ( in_array( $this->schema_key, array( 'error', 'skipped'))) {
echo '<br/><span class="file">'.$changeItem->file.'</span>';
if ( !empty( $changeItem->reason)) {
echo '<br/><span class="reason">'.$changeItem->reason.'</span>';
}
} ?></li>
<?php  } ?>
</ul>
            </div>
         </li>

<?php
// file: extensions_tables.php.
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
<?php $isTemplate = $this->_isExtensionSite( 1);
$isSite = $this->_isExtensionSite( 2);
$viewImg = '<img src="components/com_multisites/images/view.png" alt="Shared table" title="Shared table{_viewFrom}" />';
$tableImg = '<img src="components/com_multisites/images/table.png" alt="Table" title="Table" />';
?>
<table class="adminlist" cellspacing="1">
      <thead>
   		<tr>
   			<th>Tables</th>
   			<th>Master</th>
<?php if ($isTemplate) { ?>
<th>Template</th>
<?php }
if ($isSite) { ?>
<th>Site</th>
<?php } ?>
</tr>
      </thead>
      <tbody>
<?php 
$i = 0; $k = 0;
foreach( $this->tablesInfo as $name => $columns) {
?><tr class="<?php echo "row". $k; ?>">
            <td><?php echo $name ?></td>
            <td align="center"><?php echo ( !empty( $columns[0]) ? ( $columns[0]->_isView ? str_replace( '{_viewFrom}', $columns[0]->_viewFrom, $viewImg) : $tableImg) : '-') ;?></td>
<?php if ($isTemplate) { ?>
<td align="center"><?php echo ( !empty( $columns[1]) ? ( $columns[1]->_isView ? str_replace( '{_viewFrom}', $columns[1]->_viewFrom, $viewImg) : $tableImg) : '-') ;?></td>
<?php }
if ($isSite) { ?>
<td align="center"><?php echo ( !empty( $columns[2]) ? ( $columns[2]->_isView ? str_replace( '{_viewFrom}', $columns[2]->_viewFrom, $viewImg) : $tableImg) : '-') ;?></td>
<?php } ?>
</tr>
<?php
 $i++;
$k = 1 - $k;
} 
?></tbody>
   </table>

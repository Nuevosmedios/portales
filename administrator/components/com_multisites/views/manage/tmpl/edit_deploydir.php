<?php
// file: edit_deploydir.php.
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
echo $this->paneCmn->startPanel( '<span class="nbr">'.$this->paneNbr++.'</span>'. JText::_( 'SITE_EDIT_DEPLOYDIR_TITLE' ), "common-deploydir" );
$this->displayFieldForm( $this->row, $this->lists,
array( 'master_dir' => array( 'label' => 'SITE_EDIT_MASTER_SITE_DIRECTORY', 'valign' => 'top',
'inputhtml' => '<i>'. JPATH_ROOT.'</i>')
)
);


if ( MultisitesHelper::isSymbolicLinks()) {
$this->displayFieldForm( $this->row, $this->lists,
array( 'deploy_dir' => array( 'size' => 75, 'maxlength' => 255, 'label' => 'SITE_EDIT_DEPLOY_DIR', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true),
'deploy_create' => array( 'type' => 'checkbox', 'label' => 'SITE_EDIT_DEPLOY_CREATE', 'tooltip' => true, 'displayDefault' => true),
'alias_link' => array( 'size' => 75, 'maxlength' => 255, 'label' => 'SITE_EDIT_ALIAS_LINK', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true,
'tr_id' => 'alias_folder', 'tr_attr' => $this->style_showDBFields),
'delete_dir' => array( 'size' => 75, 'maxlength' => 255, 'label' => 'SITE_EDIT_DELETE_DIR', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true)
)
);
}
else {
?><div style="display:none;">
         <input type="hidden" name="deploy_dir"      value="" />
         <input type="hidden" name="deploy_create"   value="" />
         <input type="hidden" name="alias_link"      value="" />
   </div>
<?php
 }
$this->displayFieldForm( $this->row, $this->lists,
array( 'media_dir' => array( 'size' => 75, 'maxlength' => 255, 'label' => 'SITE_EDIT_MEDIA_DIR', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true,
'tr_id' => 'tr_media_dir', 'tr_attr' => $this->style_showDBFields),
'images_dir' => array( 'size' => 75, 'maxlength' => 255, 'label' => 'SITE_EDIT_IMAGES_DIR', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true,
'tr_id' => 'tr_images_dir', 'tr_attr' => $this->style_showDBFields),
'templates_dir' => array( 'size' => 75, 'maxlength' => 255, 'label' => 'SITE_EDIT_THEMES_DIR', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true)
)
);
if ( false) {
?><tr valign="top">
		<td class="helpMenu">
			<label for="tmp_dir">
				<strong><?php echo JText::_( 'SITE_EDIT_TEMP_DIR' ); ?>:</strong>
			</label>
		</td>
		<td>
			<input class="inputbox" type="text" name="tmp_dir" id="tmp_dir" size="90" maxlength="255" value="<?php echo $this->row->tmp_dir; ?>" />
			<?php echo JHTML::_('tooltip', JText::_( 'SITE_EDIT_TEMP_DIR_TTIPS')); ?>
<?php echo MultisitesHelper::tooltipsKeywords(); ?>
</td>
	</tr>
	<tr id="tr_gray_message" <?php echo $this->style_showDBFields; ?>>
		<td valign="top" colspan="2">
		   <span class="note">
				<center><strong><?php echo JText::_( 'SITE_EDIT_GRAY_FIELDS' ); ?></strong></center>
		   </span>
		</td>
	</tr>
<?php }
echo '<div style="clear:both;"></div>';
echo $this->paneCmn->endPanel();

<?php
// file: show_multisites.php.
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
$dir_rights_html = <<<EOT
         <div class="formField" id="dir_rights_input">
      		<div class="fieldInput">
      			<input type="text" value="{owner}" maxlength="1" size="1" id="dir_rights_owner" name="dir_rights_owner" class="inputbox"/>
      			<input type="text" value="{group}" maxlength="1" size="1" id="dir_rights_group" name="dir_rights_group" class="inputbox"/>
      			<input type="text" value="{other}" maxlength="1" size="1" id="dir_rights_other" name="dir_rights_other" class="inputbox"/>
            </div>
      		<div class="fieldTips">
      		</div>
   		</div>   
EOT;
$dir_rights_html = str_replace( array( '{owner}', '{group}', '{other}'),
array( ($this->row->dir_rights >> 6) & 7,
($this->row->dir_rights >> 3) & 7,
$this->row->dir_rights & 7),
$dir_rights_html
);
echo $this->paneCmn->startPanel( '<span class="nbr">'.$this->paneNbr++.'</span>'. JText::_( 'SETTINGS_MULTISITES_TITLE' ), "settings-multisites" );
$this->displayFieldForm( $this->row, $this->lists,
array( 'jpath_multisites' => array( 'size' => 80, 'maxlength' => 255, 'label' => 'SETTINGS_MULTISITES_JPATH_MULTISITES', 'tooltip' => true,),

'dir_rights' => array( 'label' => 'SETTINGS_MULTISITES_DIR_RIGHTS', 'tooltip' => true,
'inputhtml' => $dir_rights_html),
'tld_parsing' => array( 'label' => 'SETTINGS_MULTISITES_TLD_PARSING', 'tooltip' => true,
'inputhtmlClass' => 'fieldRadio',
'inputhtml' => MultisitesHelper::getRadioYesNoDefault( 'tld_parsing', $this->row->tld_parsing, '', false)),
'letter_tree' => array( 'label' => 'SETTINGS_MULTISITES_LETTER_TREE', 'tooltip' => true,
'inputhtmlClass' => 'fieldRadio',
'inputhtml' => MultisitesHelper::getRadioYesNoDefault( 'letter_tree', $this->row->letter_tree, '', false)),
'refresh_disabled' => array( 'label' => 'SETTINGS_MULTISITES_REFRESH_DISABLED', 'tooltip' => true,
'inputhtmlClass' => 'fieldRadio',
'inputhtml' => MultisitesHelper::getRadioYesNoDefault( 'refresh_disabled', $this->row->refresh_disabled, '', false)),
'cookie_domain' => array( 'label' => 'SETTINGS_MULTISITES_COOKIE_DOMAIN', 'tooltip' => true,
'inputhtmlClass' => 'fieldRadio',
'inputhtml' => MultisitesHelper::getRadioYesNoDefault( 'cookie_domain', $this->row->cookie_domain, '', false)),
'ignore_ext_version'=> array( 'label' => 'SETTINGS_MULTISITES_IGNORE_EXT_VERSION', 'tooltip' => true,
'inputhtmlClass' => 'fieldRadio',
'inputhtml' => MultisitesHelper::getRadioYesNoDefault( 'ignore_ext_version', $this->row->ignore_ext_version, '', false)),
'db_grant_host' => array( 'size' => 20, 'maxlength' => 255, 'label' => 'SETTINGS_MULTISITES_DB_GRANT_HOST', 'tooltip' => true),
'db_root_user' => array( 'size' => 20, 'maxlength' => 255, 'label' => 'SETTINGS_MULTISITES_DB_ROOT_USER', 'tooltip' => true),
'db_root_psw' => array( 'size' => 20, 'maxlength' => 255, 'label' => 'SETTINGS_MULTISITES_DB_ROOT_PSW', 'tooltip' => true),
'joomla_download_url'=> array( 'size' => 80, 'maxlength' => 255, 'label' => 'SETTINGS_MULTISITES_JOOMLA_DOWNLOAD_URL', 'tooltip' => true),
'home_dir' => array( 'size' => 50, 'maxlength' => 255, 'label' => 'SETTINGS_MULTISITES_HOME_DIR', 'tooltip' => true),
'public_dir' => array( 'size' => 20, 'maxlength' => 255, 'label' => 'SETTINGS_MULTISITES_PUBLIC_DIR', 'tooltip' => true),
'config_prefix_dir'=> array( 'size' => 20, 'maxlength' => 255, 'label' => 'SETTINGS_MULTISITES_CONFIG_PREFIX_DIR', 'tooltip' => true),
'autoinc_dir' => array( 'size' => 80, 'maxlength' => 255, 'label' => 'SETTINGS_MULTISITES_AUTOINC_DIR', 'tooltip' => true),
'elt_site_text' => array( 'type' => 'textarea', 'rows' => 5, 'cols' => 45, 'valign' => "top",
'label' => 'SETTINGS_MULTISITES_ELT_SITE_TEXT', 'tooltip' => true),
'elt_site_hidden' => array( 'type' => 'textarea', 'rows' => 5, 'cols' => 45, 'valign' => "top",
'label' => 'SETTINGS_MULTISITES_ELT_SITE_HIDDEN', 'tooltip' => true)
)
);
echo $this->paneCmn->endPanel();

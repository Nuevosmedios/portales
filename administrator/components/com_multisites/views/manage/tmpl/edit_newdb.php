<?php
// file: edit_newdb.php.
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
if ( empty( $this->lists['templates'])) {

if ( !empty( $this->row->db)) {
echo $this->paneCmn->startPanel( '<span class="nbr">'.$this->paneNbr++.'</span>'. JText::_( 'SITE_EDIT_SETDEFAULT_TITLE' ), "common-newdb" );
$this->displayFieldForm( $this->row, $this->lists,
array( 'setDefaultJLang' => array( 'uselist' => true, 'valign' => "top", 'label_for' => 'setDefaultJLang',
'label' => 'SITE_EDIT_SETDEFAULT_JLANG', 'tooltip' => true, 'displayDefault' => true,
'tr_id' => 'tr_setDefaultJLang', 'x-tr_attr' => $this->style_showDBFields),
'setDefaultTemplate' => array( 'uselist' => true, 'valign' => "top", 'label_for' => 'setDefaultTemplate',
'label' => 'SITE_EDIT_SETDEFAULT_TEMPLATE', 'tooltip' => true, 'displayDefault' => true,
'tr_id' => 'tr_setDefaultTemplate', 'x-tr_attr' => $this->style_showDBFields),
'setDefaultMenu' => array( 'uselist' => true, 'valign' => "top", 'label_for' => 'setDefaultMenu',
'label' => 'SITE_EDIT_SETDEFAULT_MENU', 'tooltip' => true, 'displayDefault' => true,
'tr_id' => 'tr_setDefaultMenu', 'x-tr_attr' => $this->style_showDBFields)
)
);
echo '<div style="clear:both;"></div>';
echo $this->paneCmn->endPanel();
}
}
else {
echo $this->paneCmn->startPanel( '<span class="nbr">'.$this->paneNbr++.'</span>'. JText::_( 'SITE_EDIT_REPLICATE_TITLE' ), "common-newdb" );
$this->displayFieldForm( $this->row, $this->lists,
array( 'shareDB' => array( 'type' => 'checkbox', 'label' => 'SITE_EDIT_SHAREDB', 'tooltip' => true, 'displayDefault' => true,
'tr_id' => 'tr_shareDB', 'tr_attr' => $this->style_shareDB, 'onclick' => 'onSharedDB(this.checked);'),
'toDBHost' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'SITE_EDIT_TO_DBHOST', 'tooltip' => true, 'displayDefault' => true,
'tr_id' => 'tr_toDBHost', 'tr_attr' => $this->style_shareDB),
'toDBName' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'SITE_EDIT_TO_DBNAME', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true,
'tr_id' => 'tr_toDBName', 'tr_attr' => $this->style_shareDB),
'toDBUser' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'SITE_EDIT_TO_DBUSER', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true,
'tr_id' => 'tr_toDBUser', 'tr_attr' => $this->style_shareDB),
'toDBPsw' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'SITE_EDIT_TO_DBPSW', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true,
'tr_id' => 'tr_toDBPsw', 'tr_attr' => $this->style_shareDB),
'toPrefix' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'SITE_EDIT_NEW_DB_PREFIX', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true, 'addHiddenDefault' => true,
'tr_id' => 'tr_toPrefix', 'tr_attr' => $this->style_shareDB),

'newAdminEmail' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'SITE_EDIT_NEW_ADMIN_EMAIL', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true,
'tr_id' => 'tr_newAdminEmail', 'tr_attr' => $this->style_showDBFields),
'newAdminPsw' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'SITE_EDIT_NEW_ADMIN_PASSWORD', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true,
'tr_id' => 'tr_newAdminPsw', 'tr_attr' => $this->style_showDBFields),
'setDefaultJLang' => array( 'uselist' => true, 'valign' => "top", 'label_for' => 'setDefaultJLang',
'label' => 'SITE_EDIT_SETDEFAULT_JLANG', 'tooltip' => true, 'displayDefault' => true,
'tr_id' => 'tr_setDefaultJLang'),
'setDefaultTemplate' => array( 'uselist' => true, 'valign' => "top", 'label_for' => 'setDefaultTemplate',
'label' => 'SITE_EDIT_SETDEFAULT_TEMPLATE', 'tooltip' => true, 'displayDefault' => true,
'tr_id' => 'tr_setDefaultTemplate'),
'setDefaultMenu' => array( 'uselist' => true, 'valign' => "top", 'label_for' => 'setDefaultMenu',
'label' => 'SITE_EDIT_SETDEFAULT_MENU', 'tooltip' => true, 'displayDefault' => true,
'tr_id' => 'tr_setDefaultMenu')
)
);
echo '<div style="clear:both;"></div>';
echo $this->paneCmn->endPanel();
}

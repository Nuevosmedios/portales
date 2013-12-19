<?php
// file: edit_common.php.
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
 $document = & JFactory::getDocument();
$document->addStylesheet( str_replace( '/index.php', '', JURI::base( true)).'/components/com_multisites/css/form.css');
?>
<div class="multisites-form">
<div id="edit-common">
<div class="col-left-fr">
<div class="col-left-pad">
<div class="pane-sliders">
<div class="panel">
<h3 id="common-general" class="title"><span><?php echo JText::_( 'General'); ?></span></h3>
<div class="body">
<?php
 if ($this->isnew) {
$id_html = '<input class="inputbox" type="text" name="id" id="id" size="30" maxlength="100" value="'. $this->row->id. '" />'
. JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_ID_TTIPS' ))
;
} else {
$id_html = '<input type="hidden" name="id" value="' . $this->row->id .'" />'
. $this->row->id
;
}
$this->displayFieldForm( $this->row, $this->lists,
array( 'id' => array( 'label' => 'TEMPLATE_VIEW_EDT_CMN_ID',
'inputhtml' => $id_html),
'groupName' => array( 'size' => 30, 'maxlength' => 100, 'label' => 'TEMPLATE_VIEW_EDT_CMN_GROUP', 'tooltip' => 'TEMPLATE_VIEW_EDT_CMN_GROUP_TTIPS'),
'validity' => array( 'size' => 7, 'maxlength' => 5, 'valign' => "top",
'label' => 'TEMPLATE_VIEW_EDT_CMN_VALIDITY',
'appendhtml' => MultisitesHelper::getValidityUnits( 'validity_unit', $this->row->validity_unit)
. JHTML::_('tooltip', JText::_( 'TEMPLATE_VIEW_EDT_CMN_VALIDITY_TTIPS'))),
'maxsite' => array( 'size' => 7, 'maxlength' => 5, 'label' => 'TEMPLATE_VIEW_EDT_CMN_TO_MAXSITE', 'tooltip' => true),
'expireurl' => array( 'size' => 80, 'maxlength' => 255, 'label' => 'TEMPLATE_VIEW_EDT_CMN_EXPIRE_URL', 'tooltip' => true, 'tooltipsKeywords' => true),
'sku' => array( 'size' => 30, 'maxlength' => 25, 'label' => 'TEMPLATE_VIEW_EDT_CMN_SKU', 'tooltip' => true,
'tr_id' => 'show_sku', 'tr_attr' => $this->style_showDBFields),
'title' => array( 'size' => 80, 'maxlength' => 90, 'label' => 'TEMPLATE_VIEW_EDT_CMN_TITLE', 'tooltip' => true,
'tr_id' => 'tr_title'),
'description'=> array( 'type' => 'textarea', 'rows' => 3, 'cols' => 45, 'valign' => "top",
'label' => 'TEMPLATE_VIEW_EDT_CMN_DESCRIPTION', 'tooltip' => true),
'toDomains' => array( 'type' => 'textarea', 'rows' => 5, 'cols' => 45, 'valign' => "top",
'label' => 'TEMPLATE_VIEW_EDT_CMN_DOMAIN', 'tooltip' => true, 'tooltipsKeywords' => true),
'redirect1st'=> array( 'label' => 'TEMPLATE_VIEW_EDT_CMN_REDIRECT_ON_1ST_DOMAIN', 'tooltip' => true,
'inputhtmlClass' => 'fieldRadio',
'inputhtml' => MultisitesHelper::getRadioYesNoDefault( 'redirect1st', (int)$this->row->redirect1st, '', false))
),
'div'
);
if ( JFile::exists( JPATH_ROOT.DS.'includes'.DS.'multisites_userexit.php')) {
$this->displayFieldForm( $this->row, $this->lists,
array( 'ignoreMasterIndex'=> array( 'label' => 'TEMPLATE_VIEW_EDT_CMN_IGNORE_MASTER_INDEX', 'tooltip' => true,
'inputhtmlClass' => 'fieldRadio',
'inputhtml' => MultisitesHelper::getRadioYesNoDefault( 'ignoreMasterIndex', (int)$this->row->ignoreMasterIndex, '0', false))
),
'div'
);
}
?><div style="clear:both;"></div>
</div>
<div class="foot">
   <center><font color="red">(*)</font> <?php echo JText::_('TEMPLATE_VIEW_EDT_CMN_FIELD_REQUIRED'); ?></center>
</div>
</div>
</div>
</div>
</div>

<div class="col-right-fr">
<div class="body">
<?php
$paneCmn = &JPane::getInstance('sliders', array('allowAllClose' => true));
echo $paneCmn->startPane("common-pane");
$paneNbr = 1;
$this->assignRef('paneCmn', $paneCmn);
$this->assignRef('paneNbr', $paneNbr);
echo $this->loadTemplate('common_geolocalisation');
echo $this->loadTemplate('common_browser');

if ( !empty( $this->lists['site_ids'])) {
echo $paneCmn->startPanel( '<span class="nbr">'.$paneNbr++.'</span>'. JText::_( 'TEMPLATE_VIEW_EDT_CMN_REPLICATE_TITLE' ), "common-replicate" );
$this->displayFieldForm( $this->row, $this->lists,
array( 'site_ids' => array( 'uselist' => true, 'valign' => "top", 'label_for' => 'fromSiteID',
'label' => 'TEMPLATE_VIEW_EDT_CMN_FROM_SITE', 'tooltip' => true,
'required' => true, 'appendhtml' => '<span id="divMessage"></span>'),
'toSiteID' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'TEMPLATE_VIEW_EDT_CMN_TO_SITE', 'tooltip' => true,
'required' => true, 'tr_id' => 'tr_toSiteID', 'tr_attr' => $this->style_showDBFields),
'shareDB' => array( 'type' => 'checkbox', 'label' => 'TEMPLATE_VIEW_EDT_CMN_SHAREDB', 'tooltip' => true,
'tr_id' => 'tr_shareDB', 'tr_attr' => $this->style_showDBFields, 'onclick' => 'onSharedDB(this.checked);'),
'adminUser' => array( 'label' => 'TEMPLATE_VIEW_EDT_CMN_ADMIN_USER', 'valign' => 'top',
'required' => true, 'tr_id' => 'admin_user', 'tr_attr' => $this->style_showDBFields,
'inputhtml' => '<div id="divAdminUser">'. MultisitesHelper::getUsersList( $this->row->fromSiteID, $this->row->adminUserID).'</div>'),
'adminUserName' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'TEMPLATE_VIEW_EDT_CMN_ADMIN_USERNAME', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'admin_username', 'tr_attr' => $this->style_showDBFields),
'adminUserLogin'=> array( 'size' => 50, 'maxlength' => 50, 'label' => 'TEMPLATE_VIEW_EDT_CMN_ADMIN_LOGIN', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'admin_login', 'tr_attr' => $this->style_showDBFields),
'adminUserEmail'=> array( 'size' => 50, 'maxlength' => 50, 'label' => 'TEMPLATE_VIEW_EDT_CMN_ADMIN_EMAIL', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'admin_email', 'tr_attr' => $this->style_showDBFields),
'adminUserPsw' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'TEMPLATE_VIEW_EDT_CMN_ADMIN_USERPSW', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'admin_userpsw', 'tr_attr' => $this->style_showDBFields),
'toDBHost' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'TEMPLATE_VIEW_EDT_CMN_TO_DBHOST', 'tooltip' => true,
'tr_id' => 'db_host', 'tr_attr' => $this->style_showDBFields),
'toDBName' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'TEMPLATE_VIEW_EDT_CMN_TO_DBNAME', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'db_name', 'tr_attr' => $this->style_showDBFields),
'toDBUser' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'TEMPLATE_VIEW_EDT_CMN_TO_DBUSER', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'db_user', 'tr_attr' => $this->style_showDBFields),
'toDBPsw' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'TEMPLATE_VIEW_EDT_CMN_TO_DBPSW', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'db_psw', 'tr_attr' => $this->style_showDBFields),
'toPrefix' => array( 'size' => 50, 'maxlength' => 50, 'label' => 'TEMPLATE_VIEW_EDT_CMN_TO_PREFIX', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'table_prefix', 'tr_attr' => $this->style_showDBFields),
'toSiteName' => array( 'size' => 70, 'maxlength' => 250, 'label' => 'TEMPLATE_VIEW_EDT_CMN_TO_SITENAME', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'site_name', 'tr_attr' => $this->style_showDBFields),
'setDefaultJLang' => array( 'uselist' => true, 'valign' => "top", 'label_for' => 'setDefaultJLang',
'label' => 'TEMPLATE_VIEW_EDT_CMN_SETDEFAULT_JLANG', 'tooltip' => true,
'tr_id' => 'tr_setDefaultJLang', 'tr_attr' => $this->style_showDBFields),
'setDefaultTemplate' => array( 'uselist' => true, 'valign' => "top", 'label_for' => 'setDefaultTemplate',
'label' => 'TEMPLATE_VIEW_EDT_CMN_SETDEFAULT_TEMPLATE', 'tooltip' => true,
'tr_id' => 'tr_setDefaultTemplate', 'tr_attr' => $this->style_showDBFields),
'setDefaultMenu' => array( 'uselist' => true, 'valign' => "top", 'label_for' => 'setDefaultMenu',
'label' => 'TEMPLATE_VIEW_EDT_CMN_SETDEFAULT_MENU', 'tooltip' => true,
'tr_id' => 'tr_setDefaultMenu', 'tr_attr' => $this->style_showDBFields)
)
);
echo '<div style="clear:both;"></div>';
echo $paneCmn->endPanel();
}

echo $paneCmn->startPanel( '<span class="nbr">'.$paneNbr++.'</span>'. JText::_( 'TEMPLATE_VIEW_EDT_CMN_FOLDERS' ), "common-folders" );
$this->displayFieldForm( $this->row, $this->lists,
array( 'master_dir' => array( 'label' => 'TEMPLATE_VIEW_EDT_CMN_MASTER_DIR', 'valign' => 'top',
'inputhtml' => '<i>'. JPATH_ROOT.'</i>')
)
);
if ( $this->canShowDeployDir()) {
$this->displayFieldForm( $this->row, $this->lists,
array( 'deploy_dir' => array( 'size' => 75, 'maxlength' => 255, 'label' => 'TEMPLATE_VIEW_EDT_CMN_DEPLOY_DIR', 'tooltip' => true, 'tooltipsKeywords' => true),
'deploy_create' => array( 'type' => 'checkbox', 'label' => 'TEMPLATE_VIEW_EDT_CMN_DEPLOY_CREATE', 'tooltip' => true),
'alias_link' => array( 'size' => 75, 'maxlength' => 255, 'label' => 'TEMPLATE_VIEW_EDT_CMN_ALIAS_LINK', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'alias_folder', 'tr_attr' => $this->style_showDBFields),
'delete_dir' => array( 'size' => 75, 'maxlength' => 255, 'label' => 'TEMPLATE_VIEW_EDT_CMN_DELETE_DIR', 'tooltip' => true, 'tooltipsKeywords' => true)
)
);
}
$this->displayFieldForm( $this->row, $this->lists,
array( 'media_dir' => array( 'size' => 75, 'maxlength' => 255, 'label' => 'TEMPLATE_VIEW_EDT_CMN_MEDIA_FOLDER', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'media_folder', 'tr_attr' => $this->style_showDBFields),
'images_dir' => array( 'size' => 75, 'maxlength' => 255, 'label' => 'TEMPLATE_VIEW_EDT_CMN_IMAGE_FOLDER', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'image_folder', 'tr_attr' => $this->style_showDBFields),
'templates_dir' => array( 'size' => 75, 'maxlength' => 255, 'label' => 'TEMPLATE_VIEW_EDT_CMN_TEMPLATES_DIR', 'tooltip' => true, 'tooltipsKeywords' => true)
)
);
echo $paneCmn->endPanel();

echo $paneCmn->startPanel( '<span class="nbr">'.$paneNbr++.'</span>'. JText::_( 'TEMPLATE_VIEW_EDT_CMN_FTP' ), "common-ftp" );
$this->displayFieldForm( $this->row, $this->lists,
array( 'toFTP_enable' => array( 'label' => 'TEMPLATE_VIEW_EDT_CMN_FTP_ENABLE', 'tooltip' => true, 'valign' => 'top',
'inputhtmlClass' => 'fieldRadio',
'inputhtml' => MultisitesHelper::getRadioYesNoDefault( 'toFTP_enable', $this->row->toFTP_enable, 'onShowFTPField(this.value);')),
'toFTP_host' => array( 'size' => 80, 'maxlength' => 255, 'label' => 'TEMPLATE_VIEW_EDT_CMN_FTP_HOST', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'tr_toFTP_host', 'tr_attr' => $this->style_showFTPFields),
'toFTP_port' => array( 'size' => 10, 'maxlength' => 15, 'label' => 'TEMPLATE_VIEW_EDT_CMN_FTP_PORT', 'tooltip' => true,
'tr_id' => 'tr_toFTP_port', 'tr_attr' => $this->style_showFTPFields),
'toFTP_user' => array( 'size' => 80, 'maxlength' => 255, 'label' => 'TEMPLATE_VIEW_EDT_CMN_FTP_USER', 'tooltip' => true,
'tr_id' => 'tr_toFTP_user', 'tr_attr' => $this->style_showFTPFields),
'toFTP_psw' => array( 'size' => 80, 'maxlength' => 255, 'label' => 'TEMPLATE_VIEW_EDT_CMN_FTP_PSW', 'tooltip' => true,
'tr_id' => 'tr_toFTP_psw', 'tr_attr' => $this->style_showFTPFields),
'toFTP_rootpath' => array( 'size' => 80, 'maxlength' => 255, 'label' => 'TEMPLATE_VIEW_EDT_CMN_FTP_ROOTPATH', 'tooltip' => true, 'tooltipsKeywords' => true,
'tr_id' => 'tr_toFTP_rootpath', 'tr_attr' => $this->style_showFTPFields)
)
);
echo $paneCmn->endPanel();
echo $paneCmn->endPane();
?>
</div>
</div>
</div>
<div style="clear:both;"></div>
</div>
<?php
// file: edit_ftp.php.
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
echo $this->paneCmn->startPanel( '<span class="nbr">'.$this->paneNbr++.'</span>'. JText::_( 'SITE_EDIT_FTP' ), "common-ftp" );
$this->displayFieldForm( $this->row, $this->lists,
array( 'toFTP_enable' => array( 'label' => 'SITE_EDIT_FTP_ENABLE', 'tooltip' => true, 'valign' => 'top','displayDefault' => true, 'addHiddenDefault' => true,
'inputhtmlClass' => 'fieldRadio', 'default' => true,
'inputhtml' => MultisitesHelper::getRadioYesNoDefault( 'toFTP_enable', $this->row->toFTP_enable, 'onShowFTPField(this.value);')),
'toFTP_host' => array( 'size' => 80, 'maxlength' => 255, 'label' => 'SITE_EDIT_FTP_HOST', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true,
'tr_id' => 'tr_toFTP_host', 'tr_attr' => $this->style_showFTPFields),
'toFTP_port' => array( 'size' => 10, 'maxlength' => 15, 'label' => 'SITE_EDIT_FTP_PORT', 'tooltip' => true, 'displayDefault' => true,
'tr_id' => 'tr_toFTP_port', 'tr_attr' => $this->style_showFTPFields),
'toFTP_user' => array( 'size' => 80, 'maxlength' => 255, 'label' => 'SITE_EDIT_FTP_USER', 'tooltip' => true, 'displayDefault' => true,
'tr_id' => 'tr_toFTP_user', 'tr_attr' => $this->style_showFTPFields),
'toFTP_psw' => array( 'size' => 80, 'maxlength' => 255, 'label' => 'SITE_EDIT_FTP_PSW', 'tooltip' => true, 'displayDefault' => true,
'tr_id' => 'tr_toFTP_psw', 'tr_attr' => $this->style_showFTPFields),
'toFTP_rootpath' => array( 'size' => 80, 'maxlength' => 255, 'label' => 'SITE_EDIT_FTP_ROOTPATH', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true,
'tr_id' => 'tr_toFTP_rootpath', 'tr_attr' => $this->style_showFTPFields)
)
);

echo $this->paneCmn->endPanel();

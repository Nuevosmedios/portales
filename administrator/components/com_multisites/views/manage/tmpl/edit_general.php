<?php
// file: edit_general.php.
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
?>
<div class="pane-sliders">
<div class="panel">
<h3 id="common-general" class="title"><span><?php echo JText::_( 'General'); ?></span></h3>
<div class="body">
<?php
 
if ( version_compare( JVERSION, '1.6') >= 0) {
$mainframe = &JFactory::getApplication();
$locale = $mainframe->getCfg( 'language' );
$lang = JLanguage::getInstance( $locale );
$mylang = JURI::root(true).'/administrator/components/com_multisites/libraries/js/calendar/lang/calendar-en-GB.js';
if(JFile::exists(JPATH_SITE . '/administrator/components/com_multisites/libraries/js/calendar/lang/calendar-'.$lang->getTag().'.js')){
$mylang = JURI::root(true).'/administrator/components/com_multisites/libraries/js/calendar/lang/calendar-'.$lang->getTag().'.js';
}
$expiration_html = '<script language="JavaScript" src="'.JURI::root(true).'/administrator/components/com_multisites/libraries/js/joomla.javascript.js" type="text/javascript"></script>'
. '<script type="text/javascript" src="'.JURI::root(true).'/administrator/components/com_multisites/libraries/js/calendar/calendar.js"></script>'
. '<script type="text/javascript" src="'.$mylang.'"></script>'
;
JFactory::getDocument()->addStyleSheet( JURI::root(true) . '/administrator/components/com_multisites/libraries/js/calendar/calendar-mos.css' );
if ( !empty( $this->row->expiration)) {
$expiration = strtotime( $this->row->expiration);
$expiration_str = strftime( '%Y-%m-%d', $expiration);
}
else {
$expiration_str = '';
}
$expiration_html .= '<input class="inputbox" type="text" name="expiration" id="expiration" size="15" maxlength="12" readonly="true" value="'.$expiration_str.'" />'
. '<a href="#" onclick="return showCalendar( \'expiration\', \'y-m-d\');"><img class="calendar" src="components/com_multisites/images/blank.png" alt="calendar" /></a>'
. '<a href="#" onclick="return clearExpiration();"><img style="width: 16px;height: 16px;margin-left: 3px;background: url(components/com_multisites/images/cancel.png) no-repeat;cursor: pointer;vertical-align: middle;" src="components/com_multisites/images/blank.png" alt="Clear date" /></a>'
. JHTML::_('tooltip', JText::_( 'SITE_EDIT_EXPIRATION_DATE_TTIPS'))
;
if ( $this->row->isExpired()) { $expiration_html .= '&nbsp;&nbsp;&nbsp;&nbsp;<font color="red"><b>' . JText::_( 'SITE_EDIT_EXPIRED') . '</b></font>'; }
}

else {
$expiration_html = JHTML::_( 'behavior.calendar' )
. '<input class="inputbox" type="text" name="expiration" id="expiration" size="15" maxlength="12" readonly="true" value="'. (!empty( $this->row->expiration) ? JHTML::_('date', $this->row->expiration, '%Y-%m-%d') : '') . '" />'
. '<a href="#" onclick="return showCalendar( \'expiration\', \'%Y-%m-%d\');"><img class="calendar" src="images/blank.png" alt="calendar" /></a>'
. '<a href="#" onclick="return clearExpiration();"><img style="width: 16px;height: 16px;margin-left: 3px;background: url(components/com_multisites/images/cancel.png) no-repeat;cursor: pointer;vertical-align: middle;" src="images/blank.png" alt="Clear date" /></a>'
. JHTML::_('tooltip', JText::_( 'SITE_EDIT_EXPIRATION_DATE_TTIPS'))
;
if ( $this->row->isExpired()) { $expiration_html .= '&nbsp;&nbsp;&nbsp;&nbsp;<font color="red"><b>' . JText::_( 'SITE_EDIT_EXPIRED') . '</b></font>'; }
}
$this->displayFieldForm( $this->row, $this->lists,
array( 'id' => array( 'size' => 50, 'maxlength' => 100, 'label' => 'SITE_EDIT_SITE_ID', 'tooltip' => true, 'required' => true),
'status' => array( 'uselist' => true, 'valign' => "top", 'label_for' => 'status',
'label' => 'SITE_EDIT_STATUS', 'tooltip' => true),
'owner_id' => array( 'uselist' => true, 'valign' => "top", 'label_for' => 'owner_id',
'label' => 'SITE_EDIT_OWNER', 'tooltip' => true),
'domains' => array( 'type' => 'textarea', 'rows' => 5, 'cols' => 45, 'valign' => "top",
'label' => 'SITE_EDIT_DOMAINS', 'tooltip' => true, 'tooltipsKeywords' => true),
'toSiteName' => array( 'size' => 70, 'maxlength' => 250, 'label' => 'SITE_EDIT_NEW_SITE_TITLE', 'tooltip' => true, 'tooltipsKeywords' => true, 'displayDefault' => true,
'tr_id' => 'tr_toSiteName', 'tr_attr' => $this->style_showConfigFields),
'toMetaDesc' => array( 'size' => 70, 'maxlength' => 250, 'label' => 'SITE_EDIT_NEW_META_DESCR', 'tooltip' => true,
'tr_id' => 'tr_toMetaDesc', 'tr_attr' => $this->style_showConfigFields),
'toMetaKeys' => array( 'size' => 70, 'maxlength' => 250, 'label' => 'SITE_EDIT_NEW_META_KEYWORDS', 'tooltip' => true,
'tr_id' => 'tr_toMetaKeys', 'tr_attr' => $this->style_showConfigFields),
'expiration' => array( 'label' => 'SITE_EDIT_EXPIRATION_DATE',
'inputhtml' => $expiration_html),
'redirect1st'=> array( 'label' => 'SITE_EDIT_REDIRECT_ON_1ST_DOMAIN', 'tooltip' => true, 'displayDefault' => true,
'inputhtmlClass' => 'fieldRadio',
'inputhtml' => MultisitesHelper::getRadioYesNoDefault( 'redirect1st', (int)$this->row->redirect1st, '', true))
),
'div'
);
if ( JFile::exists( JPATH_ROOT.DS.'includes'.DS.'multisites_userexit.php')) {
$this->displayFieldForm( $this->row, $this->lists,
array( 'ignoreMasterIndex'=> array( 'label' => 'SITE_EDIT_IGNORE_MASTER_INDEX', 'tooltip' => true, 'displayDefault' => true,
'inputhtmlClass' => 'fieldRadio',
'inputhtml' => MultisitesHelper::getRadioYesNoDefault( 'ignoreMasterIndex', (int)$this->row->ignoreMasterIndex, '', true))
),
'div'
);
}
?>
<div style="clear:both;"></div>
</div>

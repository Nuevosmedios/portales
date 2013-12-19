<?php
// file: view2win.php.
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
?><?php


defined('JPATH_BASE') or die();




if ( !class_exists( 'JView2Win')) {
class JView2Win extends J2WinView
{


function renderToolBar()
{
$option = JRequest::getCmd('option');
$mainframe = &JFactory::getApplication();

jimport('joomla.html.toolbar');
$bar =& JToolBar::getInstance('toolbar');
$toolbarContent = $bar->render('toolbar');
$this->assignRef('toolbarContent', $toolbarContent);
if ( version_compare( JVERSION, '1.6') >= 0) { $toolbarTitle = $mainframe->JComponentTitle; }
else { $toolbarTitle = $mainframe->get('JComponentTitle'); }
$this->assignRef('toolbarTitle', $toolbarTitle);
$document = & JFactory::getDocument();
$document->addStyleSheet( JURI::base() . "administrator/components/$option/css/toolbar.css");
if ( version_compare( JVERSION, '1.6') >= 0) {
$document->addStyleSheet( JURI::base() . "administrator/components/$option/css/toolbar16.css");
}
else {
$document->addStyleSheet( JURI::base() . 'administrator/templates/khepri/css/icon.css');
}
}
function getTemplateToolbar()
{
}


function assignAds()
{
jimport('joomla.filesystem.file');
$ads = '';
if ( !defined('_EDWIN2WIN_')) { define('_EDWIN2WIN_', true); }
if ( JFile::exists( JPATH_COMPONENT.DS.'classes'.DS.'http.php')) {
require_once( JPATH_COMPONENT.DS.'classes'.DS.'http.php' );
}
else if ( JFile::exists( JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'http.php')) {
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'http.php' );
}
if ( JFile::exists( JPATH_COMPONENT.DS.'models'.DS.'registration.php')) {
require_once( JPATH_COMPONENT.DS.'models'.DS.'registration.php' );
}
else if ( JFile::exists( JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'registration.php')) {
require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'registration.php' );
}

if ( class_exists( 'Edwin2WinModelRegistration')) {
$isRegistered =& Edwin2WinModelRegistration::isRegistered();
if ( !$isRegistered) { $ads =& Edwin2WinModelRegistration::getAds(); }
else { $ads = ''; }
}
$this->assignRef('ads', $ads);
}


function &_getPagination( &$filters, $total=0)
{
jimport('joomla.html.pagination');
$pagination = new JPagination( $total, $filters['limitstart'], $filters['limit'] );
return $pagination;
}


function isSuperAdmin()
{
$user = JFactory::getUser();
$isSuperAdmin = false;
if ($user->gid == 25) {
$isSuperAdmin = true;
}
$this->assign('isSuperAdmin', $isSuperAdmin);
return $isSuperAdmin;
}

function enableSortedHeader()
{
$this->_isSortedHeader = true;
}

function displayListHeader( &$rows, $fields = array(), $leadingspaces='')
{
if ( empty( $fields)) {
return;
}
$isSortedHeader = false;
if ( !empty( $this->_isSortedHeader)) {
$isSortedHeader = $this->_isSortedHeader;
}
$isSuperAdmin = $this->isSuperAdmin();
$this->_fieldcount = 0;
foreach( $fields as $fieldname => $properties) {
$show = true;
if ( !empty( $properties)) {

if ( !empty( $properties['isSuperAdmin'])) {

if ( !$isSuperAdmin) {
$show = false;
}
}
}
if ( $show) {
echo $leadingspaces . '<th class="' .$fieldname. '">';
if ( !empty( $properties['input'])) {
$input = $properties['input'];
if ( !empty( $input['type']) && $input['type'] == 'grid.id') {
$nbrec = empty( $rows) ? 0 : count( $rows);
echo '<input type="checkbox" name="toggle" value="" onclick="checkAll(' .$nbrec. ');" />';
}
else {
if ( $isSortedHeader) {
echo JHTML::_('grid.sort', $fieldname, $fieldname, @$this->lists['order_Dir'], @$this->lists['order'] );
}
else {
echo JText::_( $fieldname);
}
}
}
else {
if ( $isSortedHeader) {
echo JHTML::_('grid.sort', $fieldname, $fieldname, @$this->lists['order_Dir'], @$this->lists['order'] );
}
else {
echo JText::_( $fieldname);
}
}
echo '</th>' . "\n";
$this->_fieldcount++;
}
}
}


function getFieldCount()
{
return $this->_fieldcount;
}

function displayListRows( $rows, $fields = array(), $leadingspaces='')
{
if ( empty( $fields)) {
return;
}
$isSuperAdmin = $this->isSuperAdmin();
if ( !empty( $this->pagination) && !empty( $this->pagination->limitstart)) {
$limitstart = $this->pagination->limitstart;
}
else {
$limitstart = 0;
}
$i = 0; $k = 0;
$hiddenField = '';
foreach( $rows as $row) {

echo $leadingspaces . '<tr class="row' .$k. '">'."\n";

foreach( $fields as $fieldname => $properties) {
$show = true;
if ( !empty( $properties)) {

if ( !empty( $properties['isSuperAdmin'])) {

if ( !$isSuperAdmin) {
$show = false;
}
}
}
if ( $show) {
echo $leadingspaces .'   <td class="' .$fieldname. '">';
echo $hiddenField; $hiddenField='';
if ( !empty( $properties['output'])) {
if ( $properties['output'] == 'recno') {
echo $limitstart + 1 + $i;
}
}
else if ( !empty( $properties['input'])) {
$input = $properties['input'];
if ( !empty( $input['type']) && $input['type'] == 'grid.id') {
if ( $input['fieldname']) {
$fieldname = $input['fieldname'];
}
echo JHTML::_('grid.id', $i, $row->$fieldname );
}
else {
$sizeStr = '';
if ( !empty( $input['size'])) {
$sizeStr = ' size="' .$input['size']. '"';
}
$maxlengthStr = '';
if ( !empty( $input['maxlength'])) {
$maxlengthStr = ' maxlength="' .$input['maxlength']. '"';
}
$valueStr = '';
if ( !empty( $input['maxlength'])) {
$valueStr = ' value="' .$input['value']. '"';
}
echo '<input class="inputbox" type="text" name="' .$fieldname. '[]" id="' .$fieldname.$i. '"' .$sizeStr.$maxlengthStr.$valueStr. '" />';
}
}

else {
$fieldvalue = '';
if ( !empty( $row->$fieldname)) {
$fieldvalue = $row->$fieldname;
}
if ( !empty( $properties['url'])) {
$url = str_replace( '[_FIELDVALUE_]', $fieldvalue, $properties['url']);
echo '<a href="' .$url. '">' .$fieldvalue. '</a>';
}
else {
echo $fieldvalue;
}
if ( !empty( $properties['addHidden']) && $properties['addHidden']) {
echo '<input class="inputbox" type="hidden" name="' .$fieldname. '[]" id="' .$fieldname.$i. '" value="' .$fieldvalue. '" />';
}
}
echo '</td>'."\n";
}

else {
$fieldvalue = '';
if ( !empty( $row->$fieldname)) {
$fieldvalue = $row->$fieldname;
}
$hiddenField = '<input class="inputbox" type="hidden" name="' .$fieldname. '[]" id="' .$fieldname.$i. '" value="' .$fieldvalue. '" />';
}
}

if ( !empty( $hiddenField)) {
echo '<td style="display:none;">' . $hiddenField . '</td>';
}

echo $leadingspaces . '</tr>';
$i++;
$k = 1 - $k;
}
}


function displayFieldForm_table( &$row, &$lists, $fieldname, $fieldattributes, $template=null)
{
if ( empty( $fieldattributes) || !is_array( $fieldattributes)) {
return;
}
if ( !empty( $fieldattributes['valign'])) { $valign = ' valign="' .$fieldattributes['valign']. '"'; }
else { $valign = ''; }
if ( !empty( $fieldattributes['label'])) { $label = $fieldattributes['label']; }
else { $label = ''; }
if ( !empty( $fieldattributes['label_for'])) { $label_for = $fieldattributes['label_for']; }
else { $label_for = $fieldname; }
if ( !empty( $fieldattributes['uselist'])) { $uselist = $fieldattributes['uselist']; }
else { $uselist = false; }
if ( !empty( $fieldattributes['tooltip'])) {
if ( $fieldattributes['tooltip'] === true) {
if ( !empty( $label)) { $tooltip = $label . '_TTIPS'; }
else { $tooltip = ''; }
}
else { $tooltip = $fieldattributes['tooltip']; }
}
else { $tooltip = ''; }
if ( !empty( $fieldattributes['tooltipsKeywords'])) { $tooltipsKeywords = $fieldattributes['tooltipsKeywords']; }
else { $tooltipsKeywords = false; }
if ( !empty( $fieldattributes['size'])) { $size = ' size="' .$fieldattributes['size']. '"'; }
else { $size = ''; }
if ( !empty( $fieldattributes['maxlength'])) { $maxlength = ' maxlength="' .$fieldattributes['maxlength']. '"'; }
else { $maxlength = ''; }
if ( !empty( $fieldattributes['rows'])) { $rows = $fieldattributes['rows']; }
else { $rows = '3'; }
if ( !empty( $fieldattributes['cols'])) { $cols = $fieldattributes['cols']; }
else { $cols = '50'; }
if ( !empty( $fieldattributes['required'])) { $required = ' <font color="red">(*)</font>'; }
else { $required = ''; }
if ( !empty( $fieldattributes['tr_id'])) { $tr_id = ' id="' .$fieldattributes['tr_id']. '"'; }
else { $tr_id = ''; }
if ( !empty( $fieldattributes['tr_attr'])) { $tr_attr = $fieldattributes['tr_attr']; }
else { $tr_attr = ''; }
if ( !empty( $fieldattributes['type'])) { $type = $fieldattributes['type']; }
else { $type = ''; }
if ( !empty( $fieldattributes['inputhtml'])) { $inputhtml = $fieldattributes['inputhtml']; }
else { $inputhtml = ''; }
if ( !empty( $fieldattributes['appendhtml'])) { $appendhtml = $fieldattributes['appendhtml']; }
else { $appendhtml = ''; }
if ( !empty( $fieldattributes['onclick'])) { $onclick = ' onclick="' .$fieldattributes['onclick']. '"'; }
else { $onclick = ''; }
?><tr<?php echo $valign .$tr_id.$tr_attr; ?>>
   		<td class="helpMenu">
   			<label for="<?php echo $label_for; ?>">
   				<strong><?php echo JText::_( $label) . $required; ?>:</strong>
   			</label>
   		</td>
   		<td>
<?php if ( $uselist) {
echo $lists[ $fieldname];
}
else if ( !empty( $inputhtml)) {
echo $inputhtml;
if ( !empty( $tooltipsKeywords)) { echo MultisitesHelper::tooltipsKeywords(); }
}
else if ( $type == 'checkbox') {
?>
<input class="inputbox" type="checkbox" name="<?php echo $fieldname; ?>" id="<?php echo $fieldname; ?>" <?php if ($row->$fieldname) { echo 'checked="checked"'; } ?><?php echo $onclick; ?>/>
<?php } else if ( $type == 'textarea') {
?>
<table border="0">
      		  <tr valign="top">
         		  <td>
                     <textarea rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>" name="<?php echo implode( "\n", $row->$fieldname); ?>"><?php echo $row->$fieldname; ?></textarea>
                 </td>
                 <td>
<?php 
if ( !empty( $tooltip)) { echo JHTML::_('tooltip', JText::_( $tooltip)); }
if ( !empty( $tooltipsKeywords)) { echo MultisitesHelper::tooltipsKeywords(); }
if ( !empty( $appendhtml)) { echo $appendhtml; }
?>
</td>
              </tr>
           </table>
<?php } else { ?>
<input class="inputbox" type="text" name="<?php echo $fieldname; ?>" id="<?php echo $fieldname; ?>"<?php echo $size; ?><?php echo $maxlength; ?>value="<?php echo $row->$fieldname; ?>" />
<?php } ?>
<?php 
if ( $type == 'textarea') {}
else {
if ( !empty( $tooltip)) { echo JHTML::_('tooltip', JText::_( $tooltip)); }
if ( !empty( $tooltipsKeywords)) { echo MultisitesHelper::tooltipsKeywords(); }
if ( !empty( $appendhtml)) { echo $appendhtml; }
}
?>
</td>
   	</tr>
<?php
 }


function displayFieldForm_div( &$row, &$lists, $fieldname, $fieldattributes, $template=null)
{
if ( empty( $fieldattributes) || !is_array( $fieldattributes)) {
return;
}
if ( !empty( $fieldattributes['valign'])) { $valign = ' valign="' .$fieldattributes['valign']. '"'; }
else { $valign = ''; }
if ( !empty( $fieldattributes['label'])) { $label = $fieldattributes['label']; }
else { $label = ''; }
if ( !empty( $fieldattributes['label_for'])) { $label_for = $fieldattributes['label_for']; }
else { $label_for = $fieldname; }
if ( !empty( $fieldattributes['displayDefault'])) { $displayDefault = $fieldattributes['displayDefault']; }
else { $displayDefault = false; }
if ( !empty( $fieldattributes['addHiddenDefault'])) { $addHiddenDefault = $fieldattributes['addHiddenDefault']; }
else { $addHiddenDefault = false; }
if ( !empty( $fieldattributes['uselist'])) { $uselist = $fieldattributes['uselist']; }
else { $uselist = false; }
if ( !empty( $fieldattributes['tooltip'])) {
if ( $fieldattributes['tooltip'] === true) {
if ( !empty( $label)) { $tooltip = $label . '_TTIPS'; }
else { $tooltip = ''; }
}
else { $tooltip = $fieldattributes['tooltip']; }
}
else { $tooltip = ''; }
if ( !empty( $fieldattributes['tooltipsKeywords'])) { $tooltipsKeywords = $fieldattributes['tooltipsKeywords']; }
else { $tooltipsKeywords = false; }
if ( !empty( $fieldattributes['size'])) { $size = ' size="' .$fieldattributes['size']. '"'; }
else { $size = ''; }
if ( !empty( $fieldattributes['maxlength'])) { $maxlength = ' maxlength="' .$fieldattributes['maxlength']. '"'; }
else { $maxlength = ''; }
if ( !empty( $fieldattributes['rows'])) { $rows = $fieldattributes['rows']; }
else { $rows = '3'; }
if ( !empty( $fieldattributes['cols'])) { $cols = $fieldattributes['cols']; }
else { $cols = '50'; }
if ( !empty( $fieldattributes['required'])) { $required = ' <font color="red">(*)</font>';
$class_required = ' class="formBlock-required"'; }
else { $required = ''; $class_required = ' class="formBlock"'; }
if ( !empty( $fieldattributes['tr_id'])) { $tr_id = $fieldattributes['tr_id']; }
else { $tr_id = $fieldname .'_frame'; }
if ( !empty( $fieldattributes['tr_attr'])) { $tr_attr = $fieldattributes['tr_attr']; }
else { $tr_attr = ''; }
if ( !empty( $fieldattributes['type'])) { $type = $fieldattributes['type']; }
else { $type = ''; }
if ( !empty( $fieldattributes['inputhtmlClass'])) { $inputhtmlClass = $fieldattributes['inputhtmlClass']; }
else { $inputhtmlClass = 'fieldOther'; }
if ( !empty( $fieldattributes['inputhtml'])) { $inputhtml = $fieldattributes['inputhtml']; }
else { $inputhtml = ''; }
if ( !empty( $fieldattributes['appendhtml'])) { $appendhtml = $fieldattributes['appendhtml']; }
else { $appendhtml = ''; }
if ( !empty( $fieldattributes['onclick'])) { $onclick = ' onclick="' .$fieldattributes['onclick']. '"'; }
else { $onclick = ''; }
if ( !empty( $tooltip)) { $label_tips = ' class="hasTip" title="'.htmlentities( JText::_( $tooltip)).'"'; }
else { $label_tips = ''; }
?><div id="<?php echo $tr_id; ?>" <?php echo $class_required.$tr_attr; ?>>
<?php if ( !empty( $label)) { ?>
<div id="<?php echo $fieldname; ?>_label" class="formLabel">
   			<label for="<?php echo $label_for; ?>"<?php echo $label_tips; ?>>
   				<strong><?php echo JText::_( $label) . $required; ?>:</strong>
   			</label>
   		</div>
<?php } ?>
<?php if ( $displayDefault) { ?>
<div id="<?php echo $fieldname; ?>_withdefault" class="formFieldWithDefault">
<?php
 if ( $addHiddenDefault) {
$defaultValue = (!empty( $template) && !empty( $template->$fieldname))?$template->$fieldname:'';
echo '<input type="hidden" name="'.$fieldname.'_defaulthidden" id="'.$fieldname.'_defaulthidden" value="'.$defaultValue.'" />';
}
?>
<div id="<?php echo $fieldname; ?>_default" class="formFieldDefault">
<?php
 if ( empty( $template) || empty( $template->$fieldname)) {}
else {
if ( is_array( $template->$fieldname) || $uselist) {
echo '<ul class="hasTip" title="'.JText::_('Default value').'">';
$selectStatement = $lists[ $fieldname];
$defaultValues = (array)$template->$fieldname;
foreach( $defaultValues as $key) {
$search = '<option value="'.$key.'"';
$pos = strpos( $selectStatement, $search);
if ( $pos === false) {
echo "<li>$key</li>";
}
else {
$pos += strlen( $search);
for ( ; $selectStatement[$pos] != '>'; $pos++);
$pos++;
$posEnd = strpos( $selectStatement, '</option>', $pos);
echo '<li>'
. substr( $selectStatement, $pos, $posEnd-$pos)
. '</li>'
;
}
}
echo '</ul>';
}
else if ( !empty( $inputhtml) && strpos( $inputhtml, 'type="radio"') !== false) {
if ( in_array( $template->$fieldname, array( 1, true, 'true', 'on'))) {
$defaultValue = JText::_( 'Yes');
}
else if ( in_array( $template->$fieldname, array( 0, false, 'false', 'off'))) {
$defaultValue = JText::_( 'No');
}
else {
$defaultValue = '';
}
echo '<span class="hasTip" title="'.JText::_('Default value').'">'.$defaultValue.'</span>';
}
else {
echo '<span class="hasTip" title="'.JText::_('Default value').'">'.$template->$fieldname.'</span>';
}
}
?>
</div>
<?php } ?>
<div id="<?php echo $fieldname; ?>_input" class="formField">
<?php if ( $uselist) {
if ( !empty( $lists[ $fieldname])) {
echo $lists[ $fieldname];
}
}
else if ( !empty( $inputhtml)) {
?>
<div class="<?php echo $inputhtmlClass; ?>">
<?php  echo $inputhtml;
?>
</div>
<?php  if ( !empty( $tooltipsKeywords)) { ?>
<div class="fieldTips">
<?php  echo MultisitesHelper::tooltipsKeywords(); ?>
</div>
<?php  }
}
else if ( $type == 'checkbox') {
?>
<div class="fieldCheckbox">
      			<input class="inputbox" type="checkbox" name="<?php echo $fieldname; ?>" id="<?php echo $fieldname; ?>" <?php if ($row->$fieldname) { echo 'checked="checked"'; } ?><?php echo $onclick; ?>/>
            </div>
<?php } else if ( $type == 'textarea') {
?>
<div class="fieldTextarea">
               <textarea rows="<?php echo $rows; ?>" cols="<?php echo $cols; ?>" name="<?php echo $fieldname; ?>"><?php echo is_array( $row->$fieldname) ? implode( "\n", $row->$fieldname) : $row->$fieldname; ?></textarea>
            </div>
      		<div class="fieldTips">
<?php 

if ( !empty( $tooltipsKeywords)) { echo MultisitesHelper::tooltipsKeywords(); }
if ( !empty( $appendhtml)) { echo $appendhtml; }
?>
</div>
<?php } else if ( $type == 'label') { ?>
<div class="fieldInput">
      			<?php echo $row->$fieldname; ?>
</div>
<?php } else { ?>
<div class="fieldInput">
      			<input class="inputbox" type="text" name="<?php echo $fieldname; ?>" id="<?php echo $fieldname; ?>"<?php echo $size; ?><?php echo $maxlength; ?>value="<?php echo isset( $row->$fieldname) ? $row->$fieldname : ''; ?>" />
            </div>
<?php } ?>
<?php 
if ( $type == 'textarea') {}
else {
?>
<div class="fieldTips">
<?php 

if ( !empty( $tooltipsKeywords)) { echo MultisitesHelper::tooltipsKeywords(); }
if ( !empty( $appendhtml)) { echo $appendhtml; }
?>
</div>
<?php }
?>
</div>
<?php if ( $displayDefault) { ?>
</div>
<?php } ?>
</div>
<?php
 }


function displayFieldForm( &$row, &$lists, $fields = array(), $layout='div', $template=null)
{
if ( empty( $fields)) {
return;
}

if ( empty( $template) && !empty( $this->template)) { $template = $this->template; }
$format = 'displayFieldForm_' . $layout;
foreach ( $fields as $fieldname => $fieldattributes) {
$this->$format( $row, $lists, $fieldname, $fieldattributes, $template);
}
}


function loadTemplate( $tpl = null)
{

$file = isset($tpl) ? $this->getLayout().'_'.$tpl : $this->getLayout();

$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);

jimport('joomla.filesystem.path');
$filetofind = $this->_createFileName('template', array('name' => $file));
$template = JPath::find($this->_path['template'], $filetofind);

if ($template != false) {
return parent::loadTemplate( $tpl);
}
return '';
}
} 
} 

<?php
// file: show.php.
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
$document = & JFactory::getDocument();
$document->addStylesheet( rtrim( JURI::base( true), '/index.php').'/components/com_multisites/css/form.css');

?>
<script language="javascript" type="text/javascript">
<!--
	function submitbutton(pressbutton) {
			submitform( pressbutton );
	}
//-->
</script>
<?php if ( !empty($this->ads)) { ?>
<table border="0">
   <tr><td><?php echo $this->ads; ?></td></tr>
</table>
<?php } ?>
<?php 
?>
<div class="multisites-form">
<div id="settings-values">
<div class="col-left-fr">
<div class="col-left-pad">
<?php
 echo $this->loadTemplate('left');
?>
</div>
</div>
<?php 
?>
<div class="col-right-fr">
<div class="body">
<form action="index.php" method="post" name="adminForm" id="adminForm">
<?php
 if ( file_exists( JPATH_LIBRARIES.'/joomla/html/pane.php')) { jimport('joomla.html.pane'); }
else { require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR.'/libraries/joomla/html/pane.php'); }
$paneCmn = &JPane::getInstance('sliders', array('allowAllClose' => true));
echo $paneCmn->startPane("settings-pane");
$paneNbr = 1;
$this->assignRef('paneCmn', $paneCmn);
$this->assignRef('paneNbr', $paneNbr);
echo $this->loadTemplate('multisites');
echo $this->loadTemplate('geolocalisation');
echo $this->loadTemplate('browser');
echo $paneCmn->endPane();
?>
<input type="hidden" name="option"        value="<?php echo $this->option; ?>" />
	<input type="hidden" name="task"          value="manage" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>
</div>

</div>
</div>


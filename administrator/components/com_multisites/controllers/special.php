<?php
// file: special.php.
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


defined('_JEXEC') or die( 'Restricted access' );




class MultisitesControllerSpecial extends J2WinController
{




function getConfigFileName()
{
$config_file = dirname(__FILE__) . DS . 'multisites.cfg.php';
return $config_file;
}

function editconfig() {
$option = JRequest::getCmd('option');
$myFilename = $this->getConfigFileName();
$content = file_get_contents($myFilename);
?>
<form action="index.php?option=<?php echo $option; ?>&task=saveconfig" method="POST">
      <table class="adminform">
       <tr class="row0">
         <td class="labelcell"><strong>Configuration file:</strong><?php echo $myFilename; ?></td>
       </tr>
       <tr class="row1">
         <td>
            <textarea class="inputbox" name="content" cols="120" rows="20"><?php echo htmlspecialchars( $content ); ?></textarea>
         </td>
       </tr>
       <tr>
         <td><input type="submit" value="Save"/></td>
       </tr>
     </table>
     </form>
   <?php
 }

function unhtmlspecialchars($string) {
$trans_tbl =get_html_translation_table (HTML_SPECIALCHARS );
$trans_tbl =array_flip ($trans_tbl );
return strtr ($string ,$trans_tbl );
}

function saveconfig() {
jimport('joomla.filesystem.file');
$myFilename = $this->getConfigFileName();
$content = stripslashes( $this->unhtmlspecialchars( $_POST['content']));
JFile::write( $myFilename, $content);
echo "File saved";
}




function getMasterIndexFileName()
{
if ( defined( 'JPATH_MULTISITES')) {
$config_file = JPATH_MULTISITES .DS. 'config_multisites.php';
}
else {

@include_once( dirname( __FILE__) .DS. 'multisites_path.cfg.php');
if ( defined( 'JPATH_MULTISITES')) {
$config_file = JPATH_MULTISITES .DS. 'config_multisites.php';
}

else {
$config_file = JPATH_ROOT.DS.'multisites' .DS. 'config_multisites.php';
}
}
return $config_file;
}

function editindex() {
$option = JRequest::getCmd('option');
$myFilename = $this->getMasterIndexFileName();
$content = file_get_contents($myFilename);
?>
<form action="index.php?option=<?php echo $option; ?>&task=saveindex" method="POST">
      <table class="adminform">
       <tr class="row0">
         <td class="labelcell"><strong>Master index file:</strong><?php echo $myFilename; ?></td>
       </tr>
       <tr class="row1">
         <td>
            <textarea class="inputbox" name="content" cols="120" rows="20"><?php echo htmlspecialchars( $content ); ?></textarea>
         </td>
       </tr>
       <tr>
         <td><input type="submit" value="Save"/></td>
       </tr>
     </table>
     </form>
   <?php
 }

function saveindex() {
jimport('joomla.filesystem.file');
$myFilename = $this->getMasterIndexFileName();
$content = $this->unhtmlspecialchars( $_POST['content']);
JFile::write( $myFilename, $content);
echo "File saved";
}
} 

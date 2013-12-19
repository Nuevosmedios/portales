<?php
// file: check_ext_version.php.
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
 $parsed_patchesVersion = $this->allPatchesVersion[0];
if ( preg_match('#([0-9]+.[0-9]*.[0-9]+).*#i',$this->allPatchesVersion[0],$matches)) {
$parsed_patchesVersion = $matches[1];
}
if ( !empty( $this->latestVersion['patch_version']) && version_compare( $parsed_patchesVersion, $this->latestVersion['patch_version']) < 0) {
$classOK = 'error';
if ( JFile::exists( JPATH_COMPONENT.DS.'controllers'.DS.'updates.php')) {
$checkForUpdateURL = 'index.php?option=com_multisites&task=checkupdates';
}
}
else {
$checkForUpdateURL = '';
$classOK = 'ok';
}
?>
<div class="ext_version">
<div class="bg">
<div class="<?php echo $classOK; ?>">
   <ul>
      <li class="patchesversion">
         <div>
            <span class="label"><?php echo JText::_( 'PATCHES_VIEW_PATCHES_DEF_VERS'); ?></span>
            <span class="version"><?php echo $this->allPatchesVersion[0]; ?></span>
<?php if ( !empty( $this->latestVersion['patch_version'])) { ?>
<em>(<?php echo JText::_( 'Latest available') . ': ' . $this->latestVersion['patch_version']; ?>)</em>
<?php } ?>
</div>
<?php if ( !empty( $checkForUpdateURL)) { ?>
<div class="checkforupdate"><a href="<?php echo $checkForUpdateURL; ?>"><?php echo JText::_( 'Check for Update'); ?></a></div>
<?php } ?></li>
<?php
 if ( !empty( $this->allPatchesVersion)) {
array_shift( $this->allPatchesVersion);
if ( !empty( $this->allPatchesVersion)) {
echo '<li>'.implode( "</li>\n<li>", $this->allPatchesVersion). '</li>';
}
}
?>
</ul>
</div>
</div>
</div>

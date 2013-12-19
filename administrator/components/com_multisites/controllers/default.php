<?php
// file: default.php.
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
require_once( dirname( dirname( __FILE__)).DS.'controller.php');




class MultisitesControllerDefault extends MultisitesController
{




function about()
{
$modelPatches =& $this->getModel( 'patches' );
$allPatchesVersion = $modelPatches->getAllPatchesVersion();
$model =& $this->getModel( 'registration', 'Edwin2WinModel' );
$latestVersion = $model->getLatestVersion();
J2WinToolBarHelper::title( JText::_( 'About us'), 'config.png' );
$yyyy = date( 'Y');
?>
<h3>Multisites for Joomla! 1.5.x, 2.5.x, 3.0.x</h3>
<p>Version <?php
$getLatestURL = '';
$version = $this->_getVersion();
$jmsVersionNbr = $this->_getVersion_NumberOnly( $jmsVersion);
if ( !empty( $latestVersion['version'])) {
$latestVersion['versionNbr'] = $this->_getVersion_NumberOnly( $latestVersion['version']);
if ( version_compare( $jmsVersionNbr, $latestVersion['versionNbr']) < 0) {
echo '<font color="red">' . $version .'</font>';
$getLatestURL = ' <a href="http://www.jms2win.com/get-latest-version">Get Latest Version</a>';
}
else {
echo '<font color="green">' . $version .'</font>';
}
echo ' <em>(' . JText::_( 'Latest available') . ': ' . $latestVersion['version'] . ')</em>';
}
else {
echo $version;
}
?><br/>
Patches definition Version <?php
if ( !empty( $latestVersion['patch_version'])) {
$parsed_patchesVersion = '';
if ( !empty( $allPatchesVersion)) {
$parsed_patchesVersion = $allPatchesVersion[0];
if ( preg_match('#([0-9]+.[0-9]*.[0-9]+).*#i', $allPatchesVersion[0], $matches)) {
$parsed_patchesVersion = $matches[1];
}
}
if ( version_compare( $parsed_patchesVersion, $latestVersion['patch_version']) < 0) {
echo '<font color="red">' . $allPatchesVersion[0] .'</font>';

if ( empty( $getLatestURL)) {
$getLatestURL = ' <a href="index.php?option=com_multisites&task=checkpatches">Check for update</a>';
}
}
else {
echo '<font color="green">' . $allPatchesVersion[0] .'</font>';
}
echo ' <em>(' . JText::_( 'Latest available') . ': ' . $latestVersion['patch_version'] . ')</em>';
array_shift( $allPatchesVersion);
if ( !empty( $allPatchesVersion)) { echo '<br/>';
echo implode( '<br/>', $allPatchesVersion);
}
}
else {
echo implode( '<br/>', $allPatchesVersion);
}

if ( $model->isRegistered() && !empty( $getLatestURL)) {
echo '<br/>' . $getLatestURL;
}
?></p>
<img src="components/com_multisites/images/multisites_logo.jpg" alt="Joomla Multi Sites" />
<h3>Copyright</h3>
<p>Copyright 2008-<?php echo $yyyy; ?>&nbsp;Edwin2Win sprlu<br/>
Rue des robiniers, 107<br/>
B-7024 Ciply<br/>
Belgium
</p>
<p>All rights reserved.</p>
<a href="http://www.jms2win.com" target="_blank">www.jms2win.com</a>
<?php


$regInfo = $model->getRegistrationInfo();
if ( empty( $regInfo) || empty( $regInfo['product_id'])) {}
else {
echo '<br/>' . JText::_( 'Product ID') . ' :' . $regInfo['product_id'];;
}

if ( !$model->isRegistered()) {
$view =& $this->getView( 'registration', '', 'Edwin2WinView');
$view->setModel( $model, true );
$view->registrationButton();
}
MultisitesHelper::addSubmenu(JRequest::getWord('task', 'manage'));
} 


function registered()
{
$option = JRequest::getCmd('option');
$model =& $this->getModel( 'registration', 'Edwin2WinModel' );
$view =& $this->getView( 'registration', '', 'Edwin2WinView');
$view->setModel( $model, true );
$msg = $view->registered( false);
$this->setRedirect( 'index.php?option=' . $option . '&task=manage', $msg);
}
} 

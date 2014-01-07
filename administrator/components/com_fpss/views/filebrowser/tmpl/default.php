<?php
/**
 * @version		$Id: default.php 3411 2013-07-23 15:05:35Z joomlaworks $
 * @package		Frontpage Slideshow
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<div id="filebrowserContainer">
<div class="addressBar">
<img alt="<?php echo JText::_('FPSS_UP'); ?>" src="components/com_fpss/images/upButton.gif" id="folderUpButton"/> <input id="addressPath" type="text" disabled="disabled" name="path" value=""/>
</div>
<iframe name="imageframe" id="filebrowser" width="<?php echo version_compare(JVERSION, '3.0', 'ge') ? '780':'550';?>" height="400" src="index.php?option=com_media&amp;view=imagesList&amp;tmpl=component"></iframe>
</div>
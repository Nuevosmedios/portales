<?php
/**
 * @version		$Id: view.html.php 3411 2013-07-23 15:05:35Z joomlaworks $
 * @package		Frontpage Slideshow
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class FPSSViewFileBrowser extends FPSSView
{

	function display($tpl = null)
	{
		$params = JComponentHelper::getParams('com_media');
		$path = $params->get('image_path', 'media');

		$document = JFactory::getDocument();
		$document->addScriptDeclaration("
			var elementID = '".JRequest::getCmd('elementID')."';
			var imagePath = '".$path."/';
		");
		$document->addScript(JURI::base(true).'/components/com_fpss/js/filebrowser.js');

		parent::display($tpl);
	}

}

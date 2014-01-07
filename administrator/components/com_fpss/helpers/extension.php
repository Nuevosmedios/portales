<?php
/**
 * @version		$Id: extension.php 3411 2013-07-23 15:05:35Z joomlaworks $
 * @package		Frontpage Slideshow
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class FPSSHelperExtension
{

	public static function isInstalled($extension = NULL)
	{
		if (is_null($extension))
		{
			return false;
		}
		$extension = JString::strtolower($extension);
		if (JFile::exists(JPATH_SITE.DS.'components'.DS.'com_'.$extension.DS.$extension.'.php'))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

}

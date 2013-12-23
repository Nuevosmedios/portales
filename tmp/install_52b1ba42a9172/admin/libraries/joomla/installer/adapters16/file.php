<?php
/**
 * @version		$Id:file.php 6961 2010-03-15 16:06:53Z infograf768 $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.installer.filemanifest');
jimport('joomla.base.adapterinstance');

/**
 * File installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.6
 */
class JInstallerFile extends JAdapterInstance
{
	private $route = 'install';

	public function setParent( &$parent)   { $this->parent =& $parent; }
	
	/**
	 * Custom uninstall method
	 *
	 * @access	public
	 * @param	string	$id	The id of the file to uninstall
	 * @param	int		$clientId	The id of the client (unused; files are global)
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function uninstall($id)
	{
		$retval = true;

		return $retval;
	}
}

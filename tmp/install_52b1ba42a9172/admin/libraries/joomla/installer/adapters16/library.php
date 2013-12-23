<?php
/**
 * @file       library.php
 * @version    1.2.47
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2011 Edwin2Win sprlu - all right reserved.
 * @license    This program is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License
 *             as published by the Free Software Foundation; either version 2
 *             of the License, or (at your option) any later version.
 *             This program is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with this program; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *             A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
 * @par History:
 * - V1.2.47 01-FEB-2011: File created based on Joomla 1.6.0 file.
 *
 * ================== Joomla original source ================
 * @version		$Id: library.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.installer.librarymanifest');
jimport('joomla.base.adapterinstance');

/**
 * Library installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.6
 */
class JInstallerLibraryMultisites extends JAdapterInstance
{
	
	public function setParent( &$parent)   { $this->parent =& $parent; }

	/**
	 * Custom uninstall method
	 *
	 * @access	public
	 * @param	string	$id	The id of the library to uninstall
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function uninstall($id)
	{
		// Initialise variables.
		$retval = true;

		// First order of business will be to load the module object table from the database.
		// This should give us the necessary information to proceed.
		$row = JTable::getInstance('extension');
		if (!$row->load((int) $id) || !strlen($row->element))
		{
			JError::raiseWarning(100, JText::_('ERRORUNKOWNEXTENSION'));
			return false;
		}

		// Is the library we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected)
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_LIB_UNINSTALL_WARNCORELIBRARY'));
			return false;
		}

		$row->delete($row->extension_id);
		unset($row);

		return $retval;
	}

	
}

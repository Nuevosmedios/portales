<?php
/**
 * @file       package.php
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
 * @version		$Id: package.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.base.adapterinstance');
jimport('joomla.database.query');
jimport('joomla.installer.packagemanifest');


/**
 * Package installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.6
 */
class JInstallerPackageMultisites extends JAdapterInstance
{
	/** @var string method of system */
	protected $route = 'install';

	public function setParent( &$parent)   { $this->parent =& $parent; }
	
	/**
	 * Custom uninstall method
	 *
	 * @access	public
	 * @param	int		$id	The id of the package to uninstall
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function uninstall($id)
	{
		// Initialise variables.
		$row	= null;
		$retval = true;

		$row = JTable::getInstance('extension');
		$row->load($id);


		$manifestFile = JPATH_MANIFESTS.DS.'packages' . DS . $row->get('element') .'.xml';
		$manifest = new JPackageManifest($manifestFile);

		// Set the package root path
		$this->parent->setPath('extension_root', JPATH_MANIFESTS.DS.'packages'.DS.$manifest->packagename);

		// Because packages may not have their own folders we cannot use the standard method of finding an installation manifest
		if (!file_exists($manifestFile))
		{
			// TODO: Fail?
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MISSINGMANIFEST'));
			return false;

		}

		$xml =JFactory::getXML($manifestFile);

		// If we cannot load the xml file return false
		if (!$xml)
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_LOAD_MANIFEST'));
			return false;
		}

		/*
		 * Check for a valid XML root tag.
		 * @todo: Remove backwards compatability in a future version
		 * Should be 'extension', but for backward compatability we will accept 'install'.
		 */
		if ($xml->getName() != 'install' && $xml->getName() != 'extension')
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_INVALID_MANIFEST'));
			return false;
		}

		$error = false;
		foreach ($manifest->filelist as $extension)
		{
			$tmpInstaller = new JInstaller();
			$id = $this->_getExtensionID($extension->type, $extension->id, $extension->client, $extension->group);
			$client = JApplicationHelper::getClientInfo($extension->client,true);
			if ($id)
			{
				if(!$tmpInstaller->uninstall($extension->type, $id, $client->id)) {
					$error = true;
					JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_NOT_PROPER', basename($extension->filename)));
				}
			} else {
				JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_UNKNOWN_EXTENSION'));
			}
		}


		// clean up manifest file after we're done if there were no errors
		if (!$error) {
			$row->delete();
		}
		else {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PACK_UNINSTALL_MANIFEST_NOT_REMOVED'));
		}

		// return the result up the line
		return $retval;
	}

	private function _getExtensionID($type, $id, $client, $group)
	{
		$db		= $this->parent->getDbo();
		$result = $id;

		$query = new JDatabaseQuery();
		$query->select('extension_id');
		$query->from('#__extensions');
		$query->where('type = '. $db->Quote($type));
		$query->where('element = '. $db->Quote($id));

		switch($type)
		{
			case 'plugin':
				// plugins have a folder but not a client
				$query->where('folder = '. $db->Quote($group));
				break;

			case 'library':
			case 'package':
			case 'component':
				// components, packages and libraries don't have a folder or client
				// included for completeness
				break;

			case 'language':
			case 'module':
			case 'template':
				// languages, modules and templates have a client but not a folder
				$client = JApplicationHelper::getClientInfo($client, true);
				$query->where('client_id = '. (int)$client->id);
				break;
		}

		$db->setQuery($query);
		$result = $db->loadResult();

		// note: for templates, libraries and packages their unique name is their key
		// this means they come out the same way they came in
		return $result;
	}
	
}

<?php
/**
 * @file       module.php
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
 * @version		$Id: module.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.base.adapterinstance');

/**
 * Module installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerModuleMultisites extends JAdapterInstance
{
	/**
	 * @var string install function routing
	 */
	protected $route = 'Install';
	protected $manifest = null;
	protected $manifest_script = null;
	protected $name = null;
	protected $element = null;
	protected $scriptElement = null;

	public function setParent( &$parent)   { $this->parent =& $parent; }

	/**
	 * Custom loadLanguage method
	 *
	 * @param	string	$path the path where to find language files
	 *
	 * @since	1.6
	 */
	public function loadLanguage($path=null)
	{
		$source = $this->parent->getPath('source');

		if (!$source) {
			$this->parent->setPath('source', ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/'.$this->parent->extension->element);
		}

		$this->manifest = $this->parent->getManifest();

		if ($this->manifest->files) {
			$element = $this->manifest->files;
			$extension = '';

			if (count($element->children())) {
				foreach ($element->children() as $file)
				{
					if ((string)$file->attributes()->module) {
						$extension = strtolower((string)$file->attributes()->module);
						break;
					}
				}
			}

			if ($extension) {
				$lang = JFactory::getLanguage();
				$source = $path ? $path : ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/'.$extension ;
				$folder = (string)$element->attributes()->folder;

				if ($folder && file_exists("$path/$folder")) {
					$source = "$path/$folder";
				}

				$client = (string)$this->manifest->attributes()->client;
				$lang->load($extension . '.sys', $source, null, false, false)
				||	$lang->load($extension . '.sys', constant('JPATH_' . strtoupper($client)), null, false, false)
				||	$lang->load($extension . '.sys', $source, $lang->getDefault(), false, false)
				||	$lang->load($extension . '.sys', constant('JPATH_' . strtoupper($client)), $lang->getDefault(), false, false);
			}
		}
	}

	/**
	 * Custom uninstall method
	 *
	 * @param	int		$id			The id of the module to uninstall
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function uninstall($id)
	{
		// Initialise variables.
		$row	= null;
		$retval = true;
		$db		= $this->parent->getDbo();

		// First order of business will be to load the module object table from the database.
		// This should give us the necessary information to proceed.
		$row = JTable::getInstance('extension');

		if (!$row->load((int) $id) || !strlen($row->element)) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_ERRORUNKOWNEXTENSION'));
			return false;
		}

		// Is the module we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected) {
			JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_WARNCOREMODULE', $row->name));
			return false;
		}

		// Get the extension root path
		jimport('joomla.application.helper');
		$element = $row->element;
		$client = JApplicationHelper::getClientInfo($row->client_id);

		if ($client === false) {
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_UNKNOWN_CLIENT', $row->client_id));
			return false;
		}
		$this->parent->setPath('extension_root', $client->path.DS.'modules'.DS.$element);

		$this->parent->setPath('source', $this->parent->getPath('extension_root'));

		// Get the package manifest objecct
		// We do findManifest to avoid problem when uninstalling a list of extension: getManifest cache its manifest file
		$this->parent->findManifest();
		$this->manifest = $this->parent->getManifest();

		// Attempt to load the language file; might have uninstall strings
		$this->loadLanguage(($row->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/'.$element);

		/*
		 * Let's run the uninstall queries for the component
		 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *	file for utf-8 support or non-utf support
		 */
		// try for Joomla 1.5 type queries
		// second argument is the utf compatible version attribute
		$utfresult = $this->parent->parseSQLFiles($this->manifest->uninstall->sql);

		if ($utfresult === false) {
			// Install failed, rollback changes
			JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_SQL_ERROR', $db->stderr(true)));
			$retval = false;
		}

		// Remove the schema version
		$query = $db->getQuery(true);
		$query->delete()->from('#__schemas')->where('extension_id = '. $row->extension_id);
		$db->setQuery($query);
		$db->Query();

		// Lets delete all the module copies for the type we are uninstalling
		$query = 'SELECT `id`' .
				' FROM `#__modules`' .
				' WHERE module = '.$db->Quote($row->element) .
				' AND client_id = '.(int)$row->client_id;
		$db->setQuery($query);

		try
		{
			$modules = $db->loadResultArray();
		}
		catch(JException $e)
		{
			$modules = array();
		}

		// Do we have any module copies?
		if (count($modules))
		{
			// Ensure the list is sane
			JArrayHelper::toInteger($modules);
			$modID = implode(',', $modules);

			// Wipe out any items assigned to menus
			$query = 'DELETE' .
					' FROM #__modules_menu' .
					' WHERE moduleid IN ('.$modID.')';
			$db->setQuery($query);
			try
			{
				$db->query();
			}
			catch(JException $e)
			{
				JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_EXCEPTION', $db->stderr(true)));
				$retval = false;
			}

			// Wipe out any instances in the modules table
			$query = 'DELETE' .
					' FROM #__modules' .
					' WHERE id IN ('.$modID.')';
			$db->setQuery($query);

			try
			{
				$db->query();
			}
			catch (JException $e)
			{
				JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_MOD_UNINSTALL_EXCEPTION', $db->stderr(true)));
				$retval = false;
			}
		}

		// Now we will no longer need the module object, so lets delete it and free up memory
		$row->delete($row->extension_id);
		$query = 'DELETE FROM `#__modules` WHERE module = '.$db->Quote($row->element) . ' AND client_id = ' . $row->client_id;
		$db->setQuery($query);

		try
		{
			$db->Query(); // clean up any other ones that might exist as well
		}
		catch(JException $e)
		{
			//Ignore the error...
		}

		unset ($row);

		return $retval;
	}

	
}

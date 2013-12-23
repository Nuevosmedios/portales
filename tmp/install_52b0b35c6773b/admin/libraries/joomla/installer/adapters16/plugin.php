<?php
/**
 * @file       plugin.php
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
 * @version		$Id: plugin.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.base.adapterinstance');

/**
 * Plugin installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerPluginMultisites extends JAdapterInstance
{
	/** @var string install function routing */
	var $route = 'install';

	protected $manifest = null;
	protected $manifest_script = null;
	protected $name = null;
	protected $scriptElement = null;
	protected $oldFiles = null;	

	public function setParent( &$parent)   { $this->parent =& $parent; }

	/**
	 * Custom loadLanguage method
	 *
	 * @access	public
	 * @param	string	$path the path where to find language files
	 * @since	1.6
	 */
	public function loadLanguage($path=null)
	{
		$source = $this->parent->getPath('source');
		if (!$source) {
			$this->parent->setPath('source', JPATH_PLUGINS . '/'.$this->parent->extension->folder.'/'.$this->parent->extension->element);
		}
		$this->manifest = $this->parent->getManifest();
		$element = $this->manifest->files;
		if ($element)
		{
			$group = strtolower((string)$this->manifest->attributes()->group);
			$name = '';
			if (count($element->children()))
			{
				foreach ($element->children() as $file)
				{
					if ((string)$file->attributes()->plugin)
					{
						$name = strtolower((string)$file->attributes()->plugin);
						break;
					}
				}
			}
			if ($name)
			{
				$extension = "plg_${group}_${name}";
				$lang = JFactory::getLanguage();
				$source = $path ? $path : JPATH_PLUGINS . "/$group/$name";
				$folder = (string)$element->attributes()->folder;
				if ($folder && file_exists("$path/$folder"))
				{
					$source = "$path/$folder";
				}
				$lang->load($extension . '.sys', $source, null, false, false)
				||	$lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, false)
				||	$lang->load($extension . '.sys', $source, $lang->getDefault(), false, false)
				||	$lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false);
			}
		}
	}
	
	
	/**
	 * Custom uninstall method
	 *
	 * @access	public
	 * @param	int		$cid	The id of the plugin to uninstall
	 * @param	int		$clientId	The id of the client (unused)
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
		if (!$row->load((int) $id))
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_ERRORUNKOWNEXTENSION'));
			return false;
		}

		// Is the plugin we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected)
		{
			JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_WARNCOREPLUGIN', $row->name));
			return false;
		}

		// Get the plugin folder so we can properly build the plugin path
		if (trim($row->folder) == '')
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_FOLDER_FIELD_EMPTY'));
			return false;
		}

		// Set the plugin root path
		if (is_dir(JPATH_PLUGINS.DS.$row->folder.DS.$row->element)) {
			// Use 1.6 plugins
			$this->parent->setPath('extension_root', JPATH_PLUGINS.DS.$row->folder.DS.$row->element);
		}
		else {
			// Use Legacy 1.5 plugins
			$this->parent->setPath('extension_root', JPATH_PLUGINS.DS.$row->folder);
		}

		// Because plugins don't have their own folders we cannot use the standard method of finding an installation manifest
		// Since 1.6 they do, however until we move to 1.7 and remove 1.6 legacy we still need to use this method
		// when we get there it'll be something like "$this->parent->findManifest();$manifest = $this->parent->getManifest();"
		$manifestFile = $this->parent->getPath('extension_root').DS.$row->element.'.xml';

		if ( ! file_exists($manifestFile))
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_INVALID_NOTFOUND_MANIFEST'));
			return false;
		}

		$xml = JFactory::getXML($manifestFile);

		$this->manifest = $xml;

		// If we cannot load the xml file return null
		if (!$xml)
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_LOAD_MANIFEST'));
			return false;
		}

		/*
		 * Check for a valid XML root tag.
		 * @todo: Remove backwards compatability in a future version
		 * Should be 'extension', but for backward compatability we will accept 'install'.
		 */
		if ($xml->getName() != 'install' && $xml->getName() != 'extension')
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_PLG_UNINSTALL_INVALID_MANIFEST'));
			return false;
		}

		// Attempt to load the language file; might have uninstall strings
		$this->parent->setPath('source', JPATH_PLUGINS .'/'.$row->folder.'/'.$row->element);
		$this->loadLanguage(JPATH_PLUGINS .'/'.$row->folder.'/'.$row->element);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Installer Trigger Loading
		 * ---------------------------------------------------------------------------------------------
		 */
		/*
		 * Let's run the queries for the module
		 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *	file for utf-8 support or non-utf-8 support
		 */
		// try for Joomla 1.5 type queries
		// second argument is the utf compatible version attribute
		$utfresult = $this->parent->parseSQLFiles($xml->{strtolower($this->route)}->sql);
		if ($utfresult === false)
		{
			// Install failed, rollback changes
			$this->parent->abort(JText::sprintf('JLIB_INSTALLER_ABORT_PLG_UNINSTALL_SQL_ERROR', $db->stderr(true)));
			return false;
		}


		// Remove the schema version
		$query = $db->getQuery(true);
		$query->delete()->from('#__schemas')->where('extension_id = '. $row->extension_id);
		$db->setQuery($query);
		$db->Query();

		// Now we will no longer need the plugin object, so lets delete it
		$row->delete($row->extension_id);
		unset ($row);

		if ($msg) {
			$this->parent->set('extension_message',$msg);
		}

		return $retval;
	}
	
}

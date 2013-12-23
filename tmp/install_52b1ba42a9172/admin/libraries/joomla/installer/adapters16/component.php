<?php
/**
 * @file       component.php
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
 * @version		$Id: component.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.base.adapterinstance');

/**
 * Component installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerComponentMultisites extends JAdapterInstance
{
	protected $manifest = null;
	protected $name = null;
	protected $element = null;
	protected $oldAdminFiles = null;
	protected $oldFiles = null;
	protected $manifest_script = null;
	protected $install_script = null;

	public function setParent( &$parent)   { $this->parent =& $parent; }
	
	/**
	 * Custom loadLanguage method
	 *
	 * @param	string	$path the path where to find language files
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function loadLanguage($path=null)
	{
		$source = $this->parent->getPath('source');

		if (!$source) {
			$this->parent->setPath('source', ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE).'/components/'.$this->parent->extension->element);
		}

		$this->manifest = $this->parent->getManifest();
		$name = strtolower(JFilterInput::getInstance()->clean((string)$this->manifest->name, 'cmd'));

		if (substr($name, 0, 4)=="com_") {
			$extension = $name;
		}
		else {
			$extension = "com_$name";
		}

		$lang	= JFactory::getLanguage();
		$source = $path ? $path : ($this->parent->extension->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE).'/components/'.$extension;

		if ($this->manifest->administration->files) {
			$element = $this->manifest->administration->files;
		}
		else if ($this->manifest->files) {
			$element = $this->manifest->files;
		}
		else {
			$element = null;
		}

		if ($element) {
			$folder = (string)$element->attributes()->folder;

			if ($folder && file_exists("$path/$folder")) {
				$source = "$path/$folder";
			}
		}
			$lang->load($extension.'.sys', $source, null, false, false)
		||	$lang->load($extension.'.sys', JPATH_ADMINISTRATOR, null, false, false)
		||	$lang->load($extension.'.sys', $source, $lang->getDefault(), false, false)
		||	$lang->load($extension.'.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false);
	}
	
	

	/**
	 * Custom uninstall method for components
	 *
	 * @param	int		$id	The unique extension id of the component to uninstall
	 *
	 * @return	mixed	Return value for uninstall method in component uninstall file
	 * @since	1.0
	 */
	public function uninstall($id)
	{
		// Initialise variables.
		$db		= $this->parent->getDbo();
		$row	= null;
		$retval	= true;

		// First order of business will be to load the component object table from the database.
		// This should give us the necessary information to proceed.
		$row = JTable::getInstance('extension');
		if (!$row->load((int) $id)) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_ERRORUNKOWNEXTENSION'));
			return false;
		}

		// Is the component we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_WARNCORECOMPONENT'));
			return false;
		}

		// Get the admin and site paths for the component
		$this->parent->setPath('extension_administrator', JPath::clean(JPATH_ADMINISTRATOR.DS.'components'.DS.$row->element));
		$this->parent->setPath('extension_site', JPath::clean(JPATH_SITE.DS.'components'.DS.$row->element));
		$this->parent->setPath('extension_root', $this->parent->getPath('extension_administrator')); // copy this as its used as a common base

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Manifest Document Setup Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Find and load the XML install file for the component
		$this->parent->setPath('source', $this->parent->getPath('extension_administrator'));

		// Get the package manifest object
		// We do findManifest to avoid problem when uninstalling a list of extension: getManifest cache its manifest file
		$this->parent->findManifest();
		$this->manifest = $this->parent->getManifest();

		if (!$this->manifest) {
			// Remove the menu
			$this->_removeAdminMenus($row);

			// Raise a warning
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_ERRORREMOVEMANUALLY'));

			// Return
			return false;
		}

		// Set the extensions name
		$name = strtolower(JFilterInput::getInstance()->clean((string)$this->manifest->name, 'cmd'));
		if (substr($name, 0, 4)=="com_") {
			$element = $name;
		}
		else {
			$element = "com_$name";
		}

		$this->set('name', $name);
		$this->set('element', $element);

		// Attempt to load the admin language file; might have uninstall strings
		$this->loadLanguage(JPATH_ADMINISTRATOR.'/components/'.$element);



		if ($msg != '') {
			$this->parent->set('extension_message', $msg);
		}

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Database Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		/*
		 * Let's run the uninstall queries for the component
		 *	If Joomla 1.5 compatible, with discreet sql files - execute appropriate
		 *	file for utf-8 support or non-utf support
		 */
		// try for Joomla 1.5 type queries
		// second argument is the utf compatible version attribute
		if (isset($this->manifest->uninstall->sql)) {
			$utfresult = $this->parent->parseSQLFiles($this->manifest->uninstall->sql);

			if ($utfresult === false) {
				// Install failed, rollback changes
				JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_COMP_UNINSTALL_SQL_ERROR', $db->stderr(true)));
				$retval = false;
			}
		}

		$this->_removeAdminMenus($row);

		/**
		 * ---------------------------------------------------------------------------------------------
		 * Filesystem Processing Section
		 * ---------------------------------------------------------------------------------------------
		 */

		// Remove the schema version
		$query = $db->getQuery(true);
		$query->delete()->from('#__schemas')->where('extension_id = '. $id);
		$db->setQuery($query);
		$db->query();


		// Remove the component container in the assets table.
		$asset	= JTable::getInstance('Asset');
		if ($asset->loadByName($element)) {
			$asset->delete();
		}

		// Clobber any possible pending updates
		$update	= JTable::getInstance('update');
		$uid	= $update->find(
			array(
				'element'	=> $row->element,
				'type'		=> 'component',
				'client_id'	=> '',
				'folder'	=> ''
			)
		);

		if ($uid) {
			$update->delete($uid);
		}

		// Now we need to delete the installation directories.  This is the final step in uninstalling the component.
		if (trim($row->element)) {
			// Now we will no longer need the extension object, so lets delete it and free up memory
			$row->delete($row->extension_id);
			unset ($row);

			return $retval;
		}
		else {
			// No component option defined... cannot delete what we don't know about
			JError::raiseWarning(100, 'JLIB_INSTALLER_ERROR_COMP_UNINSTALL_NO_OPTION');
			return false;
		}
	}

	

	/**
	 * Method to remove admin menu references to a component
	 *
	 * @param	object	$component	Component table object
	 *
	 * @return	boolean	True if successful
	 * @since	1.5
	 */
	protected function _removeAdminMenus(&$row)
	{
		// Initialise Variables
		$db		= $this->parent->getDbo();
		$table	= JTable::getInstance('menu');
		$id		= $row->extension_id;

		// Get the ids of the menu items
		$query	= $db->getQuery(true);
		$query->select('id');
		$query->from('#__menu');
		$query->where('`client_id` = 1');
		$query->where('`component_id` = '.(int) $id);

		$db->setQuery($query);

		$ids = $db->loadResultArray();

		// Check for error
		if ($error = $db->getErrorMsg() || empty($ids)){
			JError::raiseWarning('', JText::_('JLIB_INSTALLER_ERROR_COMP_REMOVING_ADMIN_MENUS_FAILED'));

			if ($error && $error != 1) {
				JError::raiseWarning(100, $error);
			}

			return false;
		}
		else {
			// Iterate the items to delete each one.
			foreach($ids as $menuid){
				if (!$table->delete((int) $menuid)) {
					$this->setError($table->getError());
					return false;
				}
			}
			// Rebuild the whole tree
			$table->rebuild();

		}
		return true;
	}

	/**
	 * Custom rollback method
	 * - Roll back the component menu item
	 *
	 * @param	array	$arg	Installation step to rollback
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	protected function _rollback_menu()
	{
		return true;
	}
}

<?php
/**
 * @file       plugin.php
 * @version    1.2.65
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
 * - V1.2.65 13-SEP-2011: Fix the constructor on the Joomla 1.6 and 1.7 that require 2 parameters.
 *                        Also inherit of the joomla "template" installer.
 *
 * ================== Joomla original source ================
 * @version		$Id: template.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

require_once(JPATH_LIBRARIES
             .DS.'joomla'
             .DS.'installer'
             .DS.'adapters'
             .DS.'template.php');

/**
 * Template installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerTemplateMultisites extends JInstallerTemplate
{
	protected $name = null;
	protected $element = null;
	protected $route = 'install';


	function __construct(&$parent)
	{
	   $db = JFactory::getDBO();
		parent::__construct( $parent, $db);
	}

	public function setParent( &$parent)   { $this->parent =& $parent; }

	/**
	 * Custom uninstall method
	 *
	 * @param	int		$id		The extension ID
	 *
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function uninstall($id)
	{
		// Initialise variables.
		$retval	= true;

		// First order of business will be to load the module object table from the database.
		// This should give us the necessary information to proceed.
		$row = JTable::getInstance('extension');

		if (!$row->load((int) $id) || !strlen($row->element)) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_ERRORUNKOWNEXTENSION'));
			return false;
		}

		// Is the template we are trying to uninstall a core one?
		// Because that is not a good idea...
		if ($row->protected) {
			JError::raiseWarning(100, JText::sprintf('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_WARNCORETEMPLATE', $row->name));
			return false;
		}

		$name = $row->element;
		$clientId = $row->client_id;

		// For a template the id will be the template name which represents the subfolder of the templates folder that the template resides in.
		if (!$name) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_ID_EMPTY'));

			return false;
		}

		// Deny remove default template
		$db = $this->parent->getDbo();
		$query = 'SELECT COUNT(*) FROM #__template_styles'.
				' WHERE home = 1 AND template = '.$db->Quote($name);
		$db->setQuery($query);

		if ($db->loadResult() != 0) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_TEMPLATE_DEFAULT'));

			return false;
		}

		// Get the template root path
		$client = JApplicationHelper::getClientInfo($clientId);

		if (!$client) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_INVALID_CLIENT'));
			return false;
		}

		$this->parent->setPath('extension_root', $client->path.DS.'templates'.DS.strtolower($name));
		$this->parent->setPath('source', $this->parent->getPath('extension_root'));

		// We do findManifest to avoid problem when uninstalling a list of extension: getManifest cache its manifest file
		$this->parent->findManifest();
		$manifest = $this->parent->getManifest();
		if (!($manifest instanceof JXMLElement)) {
			// kill the extension entry
			$row->delete($row->extension_id);
			unset($row);
			JError::raiseWarning(100, JTEXT::_('JLIB_INSTALLER_ERROR_TPL_UNINSTALL_INVALID_NOTFOUND_MANIFEST'));

			return false;
		}

		//Set menu that assigned to the template back to default template
		$query = 'UPDATE #__menu INNER JOIN #__template_styles'.
				' ON #__template_styles.id = #__menu.template_style_id'.
				' SET #__menu.template_style_id = 0'.
				' WHERE #__template_styles.template = '.$db->Quote(strtolower($name)).
				' AND #__template_styles.client_id = '.$db->Quote($clientId);
		$db->setQuery($query);
		$db->Query();

		$query = 'DELETE FROM #__template_styles'.
				' WHERE template = '.$db->Quote($name).
				' AND client_id = '.$db->Quote($clientId);
		$db->setQuery($query);
		$db->Query();

		$row->delete($row->extension_id);
		unset($row);

		return $retval;
	}

		
}

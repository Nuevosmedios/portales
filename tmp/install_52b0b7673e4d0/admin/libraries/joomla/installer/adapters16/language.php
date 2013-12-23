<?php
/**
 * @file       language.php
 * @version    1.2.47
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2011 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.47 01-FEB-2011: Created for Joomla 1.6.0 stable compatibility
 *
 * ================== Joomla original source ================
 * @version		$Id: language.php 20196 2011-01-09 02:40:25Z ian $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.base.adapterinstance');

/**
 * Language installer
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerLanguageMultisites extends JAdapterInstance
{
	/**
	 * Core language pack flag
	 * @access	private
	 * @var		boolean
	 */
	protected $_core = false;

	public function setParent( &$parent)   { $this->parent =& $parent; }
	

	/**
	 * Custom uninstall method
	 *
	 * @param	string	$tag		The tag of the language to uninstall
	 * @param	int		$clientId	The id of the client (unused)
	 * @return	mixed	Return value for uninstall method in component uninstall file
	 * @since	1.5
	 */
	public function uninstall($eid)
	{
		// load up the extension details
		$extension = JTable::getInstance('extension');
		$extension->load($eid);
		// grab a copy of the client details
		$client = JApplicationHelper::getClientInfo($extension->get('client_id'));

		// check the element isn't blank to prevent nuking the languages directory...just in case
		$element = $extension->get('element');
		if (empty($element))
		{
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_ELEMENT_EMPTY'));
			return false;
		}

		// verify that it's not the default language for that client
		$params = JComponentHelper::getParams('com_languages');
		if ($params->get($client->name)==$element) {
			JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_LANG_UNINSTALL_DEFAULT'));
			return false;
		}

		// construct the path from the client, the language and the extension element name
		$path = $client->path.DS.'language'.DS.$element;

		// Get the package manifest object and remove media
		$this->parent->setPath('source', $path);

		// Remove the extension table entry
		$extension->delete();

		// Setting the language of users which have this language as the default language
		$db = JFactory::getDbo();
		$query=$db->getQuery(true);
		$query->from('#__users');
		$query->select('*');
		$db->setQuery($query);
		$users = $db->loadObjectList();
		if($client->name == 'administrator') {
			$param_name = 'admin_language';
		} else {
			$param_name = 'language';
		}

		$count = 0;
		foreach ($users as $user) {
			$registry = new JRegistry;
			$registry->loadJSON($user->params);
			if ($registry->get($param_name)==$element) {
				$registry->set($param_name,'');
				$query=$db->getQuery(true);
				$query->update('#__users');
				$query->set('params='.$db->quote($registry));
				$query->where('id='.(int)$user->id);
				$db->setQuery($query);
				$db->query();
				$count = $count + 1;
			}
		}
		if (!empty($count)) {
			JError::raiseNotice(500, JText::plural('JLIB_INSTALLER_NOTICE_LANG_RESET_USERS', $count));
		}

		// All done!
		return true;
	}
	
}

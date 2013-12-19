<?php
/**
 * @file       deletesiteid.php
 * @brief      Delete the Site ID entries in the "#__multisites_users" table when a site is deleted.
 * @version    1.2.00
 * @author     Edwin CHERONT     (info@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2011-2013 Edwin2Win sprlu - all right reserved.
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
 * - V1.0.0    20-SEP-2011: Initial version
 * - V1.2.0 14-MAR-2013: Add Joomla 3.0 compatibility
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.folder');

// Check that the "Multisites" components is installed.
// Otherwise disable code of this plugin
if ( !defined( 'DS'))   { define('DS', DIRECTORY_SEPARATOR); }
if ( JFolder::exists( JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_multisites'))
require_once( JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
{
   // ===========================================================
   //             plgMultisitesDeleteSite class
   // ===========================================================
   class plgMultisitesDeleteSiteID extends JPlugin {
   
      //------------ Constructor ---------------
   	function plgMultisitesDeleteSiteID(& $subject, $config)
   	{
   		parent::__construct($subject, $config);
   	}
   	

      //------------ onBeforeDeleteSiteID ---------------
      /**
       * @brief Remove the site ID into the #__multisites_users in case where this table is shared.
       */
   	function onBeforeDeleteSiteID($site_id)
   	{
   		$db =& Jms2WinFactory::getMultiSitesDBO( $site_id, true, false);
   		// If there is no DB connected
   		if ( empty($db) || !is_object( $db)) {
   		   return true;
   		}
   		$query = 'DELETE FROM #__multisites_users WHERE site_id='.$db->Quote( $site_id);

   		// Execute the delete for this site ID
   		$db->setQuery( $query );
   		$db->query();
   		return true;
   	}
   } // End class
} // End check that Multisites component are installed.

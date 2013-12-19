<?php // no direct access
defined('_JEXEC') or die('Restricted access');
/**
 * @file       router.php
 * @brief      Wrapper to Joomla content router and also duplicate the code for the MultisiteContent.
 * @version    1.1.8
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  JMS Multi Sites for joomla
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2012 Edwin2Win sprlu - all right reserved.
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
 * - V1.0.0 21-DEC-2008: File creation
 * - V1.0.2 12-JAN-2009: Fix syntax error when SEF is enabled and also duplicate the code for MultisiteContent.
 * - V1.0.9 01-JAN-2010: Compute the JPATH_COMPONENT path when it is not defined.
 * - V1.0.11 24-JUL-2010: Fix target directory to always compute the one in the "com_multisitescontent" directory
 *                        and no more use the current component when SEF is enable.
 *           01-SEP-2010: Remove usage of JPATH_COMPONENT_ORIGINAL that might have different value depending
 *                        if other "multisites" extension is present.
 *                        Prefered to dynamically compute the path.
 * - V1.0.12 27-SEP-2010: Hide the Site_ID present in the URL when SEF is enabled
 * - V1.1.8  05-DEC-2012: Add possibility to retreive the Site ID from the menu params.
 */


// Duplicate with MultisitesContent name for the SEF
if ( !defined( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR')) {
   define( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR',
            JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'multisitesfactory.php');

$jms2win_jpath_component            = dirname( __FILE__);
$jms2win_jpath_component_original   = dirname( dirname( __FILE__)) .DS. 'com_content';

Jms2WinFactory::import( $jms2win_jpath_component,
                        $jms2win_jpath_component_original,
                        basename( __FILE__),
                        array( 'function ContentBuildRoute('   => 'function MultisitesContentBuildRoute_parent( $site_id, ',
                               'function ContentParseRoute('   => 'function MultisitesContentParseRoute_parent( $site_id, ',
                               "JCategories::getInstance('Content')" => "JCategories::getInstance('Content', array( 'site_id' => \$site_id))",
                               'JFactory::getDBO()'   => 'Jms2WinFactory::getMultiSitesDBO()'
                             )
                      );

//------------ MultisitesContentBuildRoute ---------------
/**
 * @brief Cleanup the site_id when already present in the menu item
 */
function MultisitesContentBuildRoute(&$query)
{
	// If the site_id is present in the query and is identical to the one present in the menu,
	// then cleanup the site_id present in the query

	// Retreive the menu information
	$app	= JFactory::getApplication();
	$menu	= $app->getMenu();

	if (empty($query['Itemid'])) {
		$item = $menu->getActive();
	} else {
		$item = $menu->getItem($query['Itemid']);
	}

	// Check if we have a valid menu item.
	$menu_site_id = '';
	if (is_object($item))
	{
	   // read the site_id when present in the menu item (URL)
		if ( !empty($item->query['site_id'])) {
			if ( is_array( $item->query['site_id'])) {
			   $menu_site_id = $item->query['site_id'][0];
			}
			else {
			   $menu_site_id = $item->query['site_id'];
			}
		}
		// When not present in the URL, check if there is a site_id present in the menu params
		else if ( !empty( $item->params)) {
		   $param_site_id = $item->params->get( 'site_id');
		   if ( !empty( $param_site_id)) {
		      $menu_site_id = $param_site_id;
		   }
		}
	}

   // Connect on the Site ID Database
	$db =& Jms2WinFactory::getMultiSitesDBO( $menu_site_id, true);
	$sav_db =& MultisitesFactory::setDBO( $db);
   // Query the parent
   $segments = MultisitesContentBuildRoute_parent( $menu_site_id, $query);
	// restore the original DB connection
	MultisitesFactory::setDBO( $sav_db);
   
	if ( !empty( $query['site_id']) && $query['site_id'] == $menu_site_id) {
		unset($query['site_id']);
	}
	
	return $segments;
}


//------------ MultisitesContentParseRoute ---------------
/**
 * @brief When SEF, restore the site_id value from the menu item definition
 */
function MultisitesContentParseRoute($segments)
{
	// Get the active menu item.
	$menu	= &JSite::getMenu();
	$item	= &$menu->getActive();
	
	// Check if we have a valid menu item.
	$menu_site_id = '';
	if (is_object($item))
	{
	   // read the site_id when present in the menu
		if ( !empty($item->query['site_id'])) {
			if ( is_array( $item->query['site_id'])) {
			   $menu_site_id = $item->query['site_id'][0];
			}
			else {
			   $menu_site_id = $item->query['site_id'];
			}
		}
	}

   // Connect on the Site ID Database
	$db =& Jms2WinFactory::getMultiSitesDBO( $menu_site_id, true);
	$sav_db =& MultisitesFactory::setDBO( $db);
   // Query the parent
	$vars	= MultisitesContentParseRoute_parent( $menu_site_id, $segments);
	// restore the original DB connection
	MultisitesFactory::setDBO( $sav_db);
	

	if ( empty($vars['site_id']) && !empty( $menu_site_id)) {
		$vars['site_id'] = $menu_site_id;
	}
	
	return $vars;
}

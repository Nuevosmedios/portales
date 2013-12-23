<?php // no direct access
defined('_JEXEC') or die('Restricted access');
/**
 * @file       router.php
 * @brief      Cleanup the URL when it is present in an article and processed by the plugin SEF.
 * @version    1.2.67
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  JMS Multi Sites for joomla
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2010 Edwin2Win sprlu - all right reserved.
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
 * - V1.2.58 3-JUL-2011: File creation
 * - V1.2.67 05-OCT-2011: remove a warning that may appear in line if ( $item->query[$key] == $value)
 *                        So verify that $item->query[$key] exists before testing its value.
 */


//------------ MultisitesBuildRoute ---------------
/**
 * @brief Cleanup the URL when all the parameters are already present in the menu (ItemID) definition
 */
function MultisitesBuildRoute(&$query)
{
	$segments = array();
	
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
	
	// If there are parameters in the menu item
	if (is_object($item) && is_array( $item->query)) {
   	// For each query items present in the URL
   	foreach ( $query as $key => $value) {
   	   // If the parameter exists in the menu item with same value, clear it
   	   if ( !empty( $item->query[$key]) && ($item->query[$key] == $value)) {
   	      if ( in_array( $key, array( 'Itemid', 'option'))) {}
   	      else {
         		unset($query[$key]); // Remove it from the URL parameters
         	}
   	   }
   	}
   }

	return $segments;
}

//------------ MultisitesParseRoute ---------------
/**
 * @brief Do nothing as everything is present in the menu item
 */
function MultisitesParseRoute($segments)
{
	$vars = array();

	return $vars;
}

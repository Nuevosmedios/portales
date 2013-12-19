<?php
/**
 * @file       category.php
 * @brief      Wrapper for Multisites functionality (sharing content).
 * @version    1.1.8
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008 Edwin2Win sprlu - all right reserved.
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
 * - V1.0.0 20-DEC-2008: File creation
 * - V1.0.8 04-JUL-2008: Fix warning relative to deprecated syntax in PHP 5 concerning a call by reference (&)
 * - V1.1.7 23-NOV-2012: Fix the way to retreive the Site ID value.
 *                       Replace JElementSite by MultisitesElementSite
 * - V1.1.8 05-DEC-2012: Add Joomla 1.5 compatibility
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'multisitesfactory.php');

Jms2WinFactory::import( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisitescontent',
                        JPATH_LIBRARIES.DS.'joomla'.DS.'html'.DS.'parameter',
                        'element'.DS.basename( __FILE__),
                        array( 'class JElementCategory'  => 'class JElementCategoryOrig'
                             )
                      );


class JElementCategory extends JElementCategoryOrig
{
	function fetchElement($name, $value, &$node, $control_name)
	{
		$site_id = MultisitesElementSite::getLastSiteValue();
		$dbSite  =& Jms2WinFactory::getMultisitesDBO( $site_id);
		if ( !empty( $dbSite)) {
   		$sav_db =& MultisitesFactory::setDBO( $dbSite);

   	   $result = parent::fetchElement($name, $value, $node, $control_name);
   
   	   // Restore the current DB
   		MultisitesFactory::setDBO( $sav_db);
		}

	   return $result;
	}
}

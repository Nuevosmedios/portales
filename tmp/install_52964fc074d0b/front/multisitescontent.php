<?php
/**
 * @file       multisitescontent.php
 * @brief      Wrapper to Joomla content component to extend it with Multisites functionality (sharing content).
 * @version    1.1.8
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms Multi Sites
 *             Single Joomla! 1.5.x & 1.6.x installation using multiple configuration (One for each 'slave' sites).
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
 * - V1.0.0 11-OCT-2008: File creation
 * - V1.1.0 11-MAR-2011: File creation for Joomla 1.6
 * - V1.1.8 05-MAR-2012: Ensure that the Site ID is correctly set in the URL in case where SEF (featured) does not call the router
 *                       because there is no additional parameters.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


if ( !defined( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR')) {
   define( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR',
            JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');

// Compute the original path component
if ( !defined( 'JPATH_COMPONENT_ORIGINAL')) {
   define( 'JPATH_COMPONENT_ORIGINAL', dirname( dirname( __FILE__)) .DS. 'com_content' );
}

$site_id = JRequest::getString( 'site_id');
if ( empty( $site_id)) {
   $site_id = JFactory::getApplication()->getParams()->get( 'site_id');
   if ( !empty( $site_id)) {
      JRequest::setVar( 'site_id', $site_id);
   }
}
// Redirect to the original content
require_once(JPATH_COMPONENT_ORIGINAL.DS.'content.php');

<?php
/**
 * @file       patch_plugins.php
 * @brief      Collection to the specific plugins.
 *
 * @version    1.3.04
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2013 Edwin2Win sprlu - all right reserved.
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
 * - 1.3.02 01-FEB-2013 ECH: Initial version
 * - 1.3.03 23-FEB-2013: Fix the test on "restricted access" in case of the Medium edition
 * - 1.3.04 24-FEB-2013: Add the definition of JPATH_MUTLISITES_COMPONENT in case where it is used in the plugins (ie wordpress).
 */

defined('_JEXEC') or die( 'Restricted access' );

if ( !defined( 'JPATH_MUTLISITES_COMPONENT')) { define( 'JPATH_MUTLISITES_COMPONENT', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'); }

include( dirname(__FILE__).DS.'wordpress/plugins/index.php');

<?php
/**
 * @file       controller.php
 * @brief      Wrapper to Joomla content controller.
 * @version    1.1.0
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
 * - V1.0.0 11-OCT-2008: File creation
 * - V1.1.0 11-MAR-2011: Add Joomla 1.6 compatibility
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$jms2win_jpath_component            = dirname( __FILE__);
$jms2win_jpath_component_original   = dirname( dirname( __FILE__)) .DS. 'com_content';
$option = basename( __FILE__);

Jms2WinFactory::import( $jms2win_jpath_component,
                        $jms2win_jpath_component_original,
                        basename( __FILE__),
                        array( 'JFactory::getDBO()'   => 'Jms2WinFactory::getMultiSitesDBO()',
                               'com_content'          => $option,
                               "authorize('com_multisitescontent'"   => "authorize('com_content'",
                               'com_multisitescontent.edit.article'  => 'com_content.edit.article'
                             )
                      );


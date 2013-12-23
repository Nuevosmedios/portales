<?php
/**
 * @file       patch_exception.php
 * @brief      Define the list of extension name exception
 *             Some extension like JomSocial are using "Community" that match "JomSocial"
 *
 * @version    1.2.63
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
 */

// List of package name and the new name after the install
                            // Package       After install
$extname_exceptions  = array( 'community' => 'JomSocial');

// List of Virtual Directory => physical directory.
$dir_exceptions      = array( 'components/com_jomsocial' => 'components/com_community');

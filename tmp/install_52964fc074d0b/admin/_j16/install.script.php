<?php
/**
 * @file       install.script.php
 * @version    1.3.0
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms  Multi Sites
 *             Single Joomla! 1.5.x AND 1.6.x installation using multiple configuration (One for each 'slave' sites).
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
 * - V1.1.0 23-MAY-2011: Initial version.
 * - V1.3.0 02-AUG-2012: Add Joomla 3.0 compatibility
 */


// Dont allow direct linking
defined( '_JEXEC' ) or die();

require_once( dirname( dirname( __FILE__)).DIRECTORY_SEPARATOR.'legacy.php');
include_once( dirname( dirname( __FILE__)).DS.basename( __FILE__));

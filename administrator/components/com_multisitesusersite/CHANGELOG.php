<?php
/**
 * @file       CHANGELOG.php
 * @brief      This document logs changes with a brief description
 *
 * @version    1.2.00
 * @author     Edwin CHERONT     (info@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
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
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

1. Changelog
------------
This is a non-exhaustive (but still near complete) changelog for Joomla Multi Sites.

-------------------- 1.2.00 Stable Release [13-mar-2013] ---------------------
- Add Joomla 3.0 compatibility
- Require JMS 1.3.07 or higher
- It is no more compatible with JMS 1.2.x
- Allow the access to the option (ACL) in a slave site to allow completly disable the extension when it is present.

-------------------- 1.1.4 Stable Release [31-may-2012] ---------------------
- Add basic ACL for joomla 2.5
- Give the possible to proceed with "bulk" delete (replace the radio button by a check box)
- Add cross-check in the plugin to reduce duplicate entries in the DB each time that a user is created or modified.
- Perform call to plugins to allow customizing the user interface to add "User Defined" fields for other multisites extensions (ie. "Multisites Acymailing").

-------------------- 1.1.3 Stable Release [03-dec-2011] ---------------------
- First packaging for public distribution.
- Add the user manual

-------------------- 1.1.2 Stable Release [26-oct-2011] ---------------------
- Add an automatically installation of all the plugins when installing the component.

-------------------- 1.1.1 Stable Release [20-oct-2011] ---------------------
- Add the possibility to have default site associated to a user.

-------------------- 1.1.0 Stable Release [21-sep-2011] ---------------------
- First private distribution.

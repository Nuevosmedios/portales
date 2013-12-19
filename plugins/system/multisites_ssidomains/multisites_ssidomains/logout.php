<?php
/**
 * @file       logout.php
 * @brief      Single Sign In Domains share the login between different domains.
 *             So that the users can remain logged when they change of domains.
 * @version    1.0.4
 * @author     Edwin CHERONT     (info@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Jms Multi Sites
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
 * @par History:
 * - V1.0.0    24-OCT-2011: Initial version
 * - V1.0.1    30-OCT-2011: Add Joomla 1.5 and 1.7 compatibilty
 * - V1.0.4    09-NOV-2011: Add the processing of the "back-end" logout
 */

header('Content-Type: text/javascript');
 
// --- Joomla initialisation ---
define( '_JEXEC', 1 );
define('DS', DIRECTORY_SEPARATOR);

// Compute the Root directory to be Joomla 1.5 & 1.7 compatible
for ( $jpath_root = dirname( __FILE__); !empty( $jpath_root) && basename( $jpath_root) != 'plugins'; $jpath_root = dirname( $jpath_root));
$jpath_root = dirname( $jpath_root);


// Compute the JPATH_BASE value depending on flag (administrator)
if ( !empty( $_GET['a']) && $_GET['a']==1) { $jpath_base = $jpath_root.DS.'administrator'; }
else                                       { $jpath_base = $jpath_root; }

// If joomla 1.7
if (file_exists( $jpath_base . '/defines.php')) {
	include_once $jpath_base . '/defines.php';
}

if (!defined('_JDEFINES')) {
   // Joomla 1.5 & 1.7
	define('JPATH_BASE', $jpath_base);
	require_once JPATH_BASE.'/includes/defines.php';
}

require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

// Instantiate the back-end (administrator) or front-end (site) depending on the flag
if ( !empty( $_GET['a']) && $_GET['a']==1) { $mainframe =& JFactory::getApplication('administrator'); }
else                                       { $mainframe =& JFactory::getApplication('site'); }

// Initialise the application.
$mainframe->initialise();

// --- Specific code start here ---
// Logout the user
if ( $mainframe->logout()) { $result = 'true'; }
else                       { $result = 'false'; }

echo "ssi_domain_logout = $result;";

?>
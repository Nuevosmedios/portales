<?php
/**
 * @file       article.php
 * @brief      Wrapper for Multisites functionality (sharing content).
 * @version    1.1.0
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2009 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.0 19-MAR-2011: File creation for Joomla 1.6 compatibility
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

if ( !defined( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR')) {
   define( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR',
            JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');

$jms2win_jpath_component            = JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_multisitescontent';
$jms2win_jpath_component_original   = JPATH_ADMINISTRATOR.DS.'components' .DS. 'com_content';
$option = basename( $jms2win_jpath_component);
Jms2WinFactory::import( $jms2win_jpath_component,
                        $jms2win_jpath_component_original,
                        'models'.DS.'fields'.DS.'modal'.DS.basename( __FILE__),
                        array(  'com_content'  => 'com_multisitescontent',
                                'COM_CONTENT'  => 'COM_MULTISITESCONTENT',
                                'JFactory::getDBO()'  => 'Jms2WinFactory::getMultiSitesDBO()',
                                'function=jSelectArticle_\'.$this->id;'   => 'function=jSelectArticle_\'.$this->id.\'&amp;task=display&amp;site_id=\' . urlencode( MultisitesElementSite::getLastSiteValue());',
                                '<a class="modal"'    => '<a class="modal" id="jms2win_url"'
                             )
                      );


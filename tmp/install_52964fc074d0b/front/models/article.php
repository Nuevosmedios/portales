<?php
/**
 * @file       article.php
 * @brief      Wrapper for Multisites functionality (sharing content).
 * @version    1.1.0
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
 * - V1.0.0 12-NOV-2008: File creation
 * - V1.0.6 25-FEB-2008: Use the com_content (Article) global parameters to view an articles instead of
 *                       the "Multi Sites" article global parameters that does not exists to display
 *                       the text intro
 * - V1.1.0 11-MAR-2011: Add Joomla 1.6 compatibility
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$jms2win_jpath_component            = dirname( dirname( __FILE__));
$jms2win_jpath_component_original   = dirname( $jms2win_jpath_component) .DS. 'com_content';
$option = basename( $jms2win_jpath_component);
Jms2WinFactory::import( $jms2win_jpath_component,
                        $jms2win_jpath_component_original,
                        'models'.DS.basename( __FILE__),
                        array( 'class ContentModelArticle'   => 'class ContentModelArticleOrig',
                               'com_content' => 'com_multisitescontent',
                               '$this->getDbo()'   => 'Jms2WinFactory::getMultiSitesDBO()',
                               "mainframe->getParams('com_multisitescontent')" => "mainframe->getParams('com_content')"
                             )
                      );


class ContentModelArticle extends ContentModelArticleOrig
{
	function __construct()
	{
		parent::__construct();
		$this->_db =& Jms2WinFactory::getMultiSitesDBO();
	}
}

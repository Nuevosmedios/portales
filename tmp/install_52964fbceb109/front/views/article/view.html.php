<?php
/**
 * @file       view.html.php
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
 * - V1.0.0 25-NOV-2008: File creation
 * - V1.0.6 25-FEB-2009: Use the com_content (Article) global parameters to view an articles instead of
 *                       the "Multi Sites" article global parameters that does not exists.
 * - V1.0.7 25-JUN-2009: Add the possibility to use the "com_content" rendering present in a specific template.
 *                       This avoid to create a "com_multisitescontent" in the specific template or duplicate
 *                       the "com_content" into "com_multisitescontent".
 * - V1.1.0 11-MAR-2011: Add Joomla 1.6 compatibility
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$jms2win_jpath_component            = JPATH_COMPONENT;
$jms2win_jpath_component_original   = dirname( $jms2win_jpath_component) .DS. 'com_content';
$option = basename( $jms2win_jpath_component);

// Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) {
   Jms2WinFactory::import( $jms2win_jpath_component,
                           $jms2win_jpath_component_original,
                           'views'.DS.'article'.DS.basename( __FILE__),
                           array( 'class ContentViewArticle' => 'class ContentViewArticleOrig',
                                  "'com_content',"  => "'com_multisitescontent',",
                                  "'content',"  => "'multisitescontent',",
                                   'JFactory::getDBO()'  => 'Jms2WinFactory::getMultiSitesDBO()',
                                   'protected $item;'    => 'public $item;',
                                   'protected $params;'  => 'public $params;',
                                   'protected $print;'   => 'public $print;',
                                   'protected $state;'   => 'public $state;',
                                   'protected $user;'    => 'public $user;',
                                   'parent::display($tpl);'         => "\n"
                                                                     . "\$tplContentPath = \$this->_path['template'][0];\n"
                                                               		. "\$this->_path['template'][0] = JPATH_BASE.DS.'templates'.DS.JFactory::getApplication()->getTemplate().DS.'html'.DS.'com_content'.DS.\$this->getName();\n"
                                                               		. "\$this->_addPath('template', \$tplContentPath);\n"
                                                               		. 'parent::display($tpl);'
                                )
                         );
                         
   class ContentViewArticle extends ContentViewArticleOrig
   {
      protected function _prepareDocument()
      {
         // Connect to DB site_id
         require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
         require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'multisitesfactory.php');
   
   		$db      =& Jms2WinFactory::getMultisitesDBO();
   		// Set current site DBO as the default one and save the previous value
   		$sav_db  =& MultisitesFactory::setDBO( $db);
   		
   		parent::_prepareDocument();
   		
   	   // Restore the current DB
   		MultisitesFactory::setDBO( $sav_db);
      }
   } // end class
}
// Joomla 1.5
else {
   Jms2WinFactory::import( $jms2win_jpath_component,
                           $jms2win_jpath_component_original,
                           'views'.DS.'article'.DS.basename( __FILE__),
                           array( "'com_content',"  => "'com_multisitescontent',",
                                  "'content',"  => "'multisitescontent',",
                                   'JFactory::getDBO()'  => 'Jms2WinFactory::getMultiSitesDBO()',
                                   'parent::display($tpl);'         => "\n"
                                                                     . "\$tplContentPath = \$this->_path['template'][0];\n"
                                                               		. "\$this->_path['template'][0] = JPATH_BASE.DS.'templates'.DS.JFactory::getApplication()->getTemplate().DS.'html'.DS.'com_content'.DS.\$this->getName();\n"
                                                               		. "\$this->_addPath('template', \$tplContentPath);\n"
                                                               		. 'parent::display($tpl);'
                                )
                         );
}   

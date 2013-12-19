<?php
/**
 * @file       category.php
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
 * - V1.0.0 11-NOV-2008: File creation
 * - V1.1.0 11-MAR-2011: Add Joomla 1.6 compatibility
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$jms2win_jpath_component            = dirname( dirname( __FILE__));
$jms2win_jpath_component_original   = dirname( $jms2win_jpath_component) .DS. 'com_content';
$option = basename( $jms2win_jpath_component);

// Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) {
   Jms2WinFactory::import( $jms2win_jpath_component,
                           $jms2win_jpath_component_original,
                           'models'.DS.basename( __FILE__),
                           array( 'class ContentModelCategory'   => 'class ContentModelCategoryOrig',
                                  '$this->getDbo()'   => 'Jms2WinFactory::getMultiSitesDBO()',
                                  '$app->getParams()'   => "\$app->getParams('com_content')",
                                  'JFactory::getApplication()->getParams()'   => "JFactory::getApplication()->getParams('com_content')"
                                )
                         );

   class ContentModelCategory extends ContentModelCategoryOrig
   {
      public function getItems($recursive = false)
      {
         // Connect to DB site_id
         require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
         require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'multisitesfactory.php');
   
   		$db      =& Jms2WinFactory::getMultisitesDBO();
   		// Set current site DBO as the default one and save the previous value
   		$sav_db  =& MultisitesFactory::setDBO( $db);
   		
   		$results = parent::getItems( $recursive);
   		
   	   // Restore the current DB
   		MultisitesFactory::setDBO( $sav_db);
   		return $results;
      }
      public function getCategory()
      {
         // Connect to DB site_id
         require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
         require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'libraries' .DS. 'joomla' .DS. 'multisitesfactory.php');
   
   		$db      =& Jms2WinFactory::getMultisitesDBO();
   		// Set current site DBO as the default one and save the previous value
   		$sav_db  =& MultisitesFactory::setDBO( $db);
   		
   		$results = parent::getCategory();
   		
   	   // Restore the current DB
   		MultisitesFactory::setDBO( $sav_db);
   		return $results;
      }
   }
}
// Joomla 1.5
else {
   Jms2WinFactory::import( $jms2win_jpath_component,
                           $jms2win_jpath_component_original,
                           'models'.DS.basename( __FILE__),
                           array( 'class ContentModelCategory'   => 'class ContentModelCategoryOrig',
                                  'com_content' => $option,
   
                                  "'content'"   => "'multisitescontent'"
                                )
                         );
   class ContentModelCategory extends ContentModelCategoryOrig
   {
   	function __construct()
   	{
   		parent::__construct();
   		$this->_db =& Jms2WinFactory::getMultiSitesDBO();
   	}
   }
}


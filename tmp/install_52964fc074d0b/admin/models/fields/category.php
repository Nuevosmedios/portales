<?php
/**
 * @file       category.php
 * @brief      Wrapper for Multisites functionality (sharing content).
 * @version    1.1.7
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2012 Edwin2Win sprlu - all right reserved.
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
 * - V1.1.7 23-NOV-2012: File creation
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

// Include the standard
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'libraries'.DS.'joomla' .DS. 'jms2winfactory.php');
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'libraries'.DS.'joomla' .DS. 'multisitesfactory.php');
require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'elements'.DS.'site.php');


Jms2WinFactory::import( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisitescontent',
                        JPATH_LIBRARIES.DS.'joomla'.DS.'form',
                        'fields'.DS.basename( __FILE__),
                        array( 'class JFormFieldCategory'  => 'class JFormFieldCategoryOrig'
                             )
                      );


if ( !class_exists( 'JFormFieldCategory')) {
   class JFormFieldCategory extends JFormFieldCategoryOrig
   {
   	protected function getOptions()
   	{
   		$site_id = MultisitesElementSite::getLastSiteValue();
   		$dbSite  =& Jms2WinFactory::getMultisitesDBO( $site_id);
   		if ( !empty( $dbSite)) {
	   		$sav_db =& MultisitesFactory::setDBO( $dbSite);
	
      	   $result = parent::getOptions();
	   
	   	   // Restore the current DB
	   		MultisitesFactory::setDBO( $sav_db);
   		}
   	   
   	   return $result;
   	}
   }
} // End class exists
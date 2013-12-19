<?php
/**
 * @file       route.php
 * @brief      Wrapper to Joomla helper route.
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
 * - V1.0.0  10-NOV-2008: File creation
 * - V1.0.13 25-JAN-2011: Fix the "com_content" original path that might be wrong when called form a Multisites Content module
 * - V1.1.00 11-MAR-2011: Add Joomla 1.6 compatibility
 * - V1.1.06 02-JUN-2012: Allow calling this source from JATabs plugin.
 *                        Added a test if the class is already defined to avoid duplicate definition.
 * - V1.1.07 26-NOV-2012: Force declaring a MultisitesContentHelperRoute in addition to the ContentHelperRoute.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

if ( !defined( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR')) {
   define( 'JPATH_MULTISITES_COMPONENT_ADMINISTRATOR',
            JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites');
}
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');
require_once( JPATH_MULTISITES_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'multisitesfactory.php');
$jms2win_jpath_component            = dirname( dirname( __FILE__));
$jms2win_jpath_component_original   = dirname( $jms2win_jpath_component) .DS. 'com_content';
$option = basename( $jms2win_jpath_component);
// Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) {
   Jms2WinFactory::import( $jms2win_jpath_component,
                           $jms2win_jpath_component_original,
                           'helpers'.DS.basename( __FILE__),
                           array( 'class ContentHelperRoute'      => 'class ContentHelperRouteOrig',
                                  "class_exists( 'ContentHelperRoute')"      => "class_exists( 'ContentHelperRouteOrig')",
                                  "'index.php?option=com_content" => "'index.php?option=com_multisitescontent&site_id='.MultisitesContentHelperRoute::getSiteID().'",
                                  "JCategories::getInstance('Content')" => "JCategories::getInstance('Content', array( 'site_id' => MultisitesContentHelperRoute::getSiteID()))",
                                  "'com_content'"  => "'com_multisitescontent'"
                                )
                         );

   if ( !class_exists( 'MultisitesContentHelperRoute')){
      class MultisitesContentHelperRoute extends ContentHelperRouteOrig
      {
         public static function getSiteID()
         {
      		static $instance;
      		
      		if ( isset( $instance)) {
      		   return $instance;
      		}
      		
      		// retreive the site id
      		$site_id = JRequest::getString('site_id', null);
            if ( $site_id=='Array') {
               $arr = JRequest::getVar('site_id', null, 'get', 'array');
               if ( !empty( $arr) && is_array($arr) && count( $arr)>0) {
                  $site_id = $arr[0];
               }
            }
            
            $instance = $site_id;
            return $instance;
         }
      
         //------------ getArticleRoute ---------------
         /**
          * @brief Compute the article route corresponding to the site ID
          */
      	public static function getArticleRoute($id, $catid)
      	{
            $site_id = self::getSiteID();
            // Connect on the Site ID Database
         	$db =& Jms2WinFactory::getMultiSitesDBO( $site_id, true);
      		$sav_db =& MultisitesFactory::setDBO( $db);
      	   // Query the parent
      	   $result = parent::getArticleRoute($id, $catid);
      		// restore the original DB connection
      		MultisitesFactory::setDBO( $sav_db);
      	   return $result;
      	}
      
         //------------ getCategoryRoute ---------------
         /**
          * @brief Compute the category route corresponding to the site ID
          */
      	public static function getCategoryRoute($catid)
      	{
            $site_id = self::getSiteID();
            // Connect on the Site ID Database
         	$db =& Jms2WinFactory::getMultiSitesDBO( $site_id, true);
      		$sav_db =& MultisitesFactory::setDBO( $db);
      	   // Query the parent
      	   $result = parent::getCategoryRoute($catid);
      		// restore the original DB connection
      		MultisitesFactory::setDBO( $sav_db);
      	   return $result;
      	}
      } // End class
   } // End exists
   if ( !class_exists( 'ContentHelperRoute')){
      class ContentHelperRoute extends MultisitesContentHelperRoute
      {
      }
   }
}
// Joomla 1.5
else {
   Jms2WinFactory::import( $jms2win_jpath_component,
                           $jms2win_jpath_component_original,
                           'helpers'.DS.basename( __FILE__),
                           array( 'com_content' => $option,
                                  'return $link;'  => "\$link .= '&site_id=' . urlencode( JRequest::getString('site_id', ''));\n"
                                                    . 'return $link;'
                                 )
                         );
}

<?php
/**
 * @file       CHANGELOG.php
 * @brief      This document logs changes with a brief description
 *
 * @version    1.1.8
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
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

1. Changelog
------------
This is a non-exhaustive (but still near complete) changelog for Joomla Multi Sites.

-------------------- 1.1.8 Stable Release [04-dec-2012] ---------------------
- In Joomla 2.5, rename the route to use "MultisitesContentHelperRoute" instead of "ContentHelperRoute" in case where plugin like "Widgetkit"
  force loading the joomla route instead of the multisite route.
  The impact is that joomla route provide a "local SEF" url instead of a "Multisite SEF" url.
  Also Updated the "blog item" and the "icon" to use the "Multisites Helper Route" 

-------------------- 1.1.7 Stable Release [23-nov-2012] ---------------------
- Fix on joomla 2.5, the display of the category selected when updating a menu item.
  The site id was lost internally.
- Fix the joomla 2.5 featured when several categories are selected.
  The parameters was not correctly saved in the #__menu params field.
  Updated the "default.xml" to set the categories in the "params" section.
  Also merge the "com_content" parameters with the "com_multisitescontent" parameters.
- Remove some PHP Strict error message reported in PHP 5.x
  Therefore, no more compatible with PHP 4.3
- Prepare the installation script to be compatibile with Joomla 3.0

-------------------- 1.1.6 Stable Release [02-jun-2012] ---------------------
- Added basic ACL for joomla 2.5.
- Allow JATabs system plugin calling the multisites article sharing.
  Added a test to check that the 'ContentHelperRoute' is not already defined.

-------------------- 1.1.5 Stable Release [16-may-2012] ---------------------
- Fix joomla 2.5 compatibility concerning the featured and "read more" button.

-------------------- 1.1.4 Stable Release [12-jan-2012] ---------------------
- Add in section layout under joomla 1.5 the possibility to Show/Hide the filter and the "Display Select dropdown".
  
-------------------- 1.1.3 Stable Release [11-jan-2012] ---------------------
- Add in section layout under joomla 1.5 the possibility to decide the number of articles
  to display like in Joomla 1.7.

-------------------- 1.1.2 Stable Release [18-aug-2011] ---------------------
- Fix bug when creating a menu with category blog or list under joomla 1.5.
  In this case, the list of categories were not correctly refreshed under Joomla 1.5

-------------------- 1.1.1 Stable Release [21-may-2011] ---------------------
- When working on Joomla 1.6 with Jms Multi Sites version 1.2.54 or higher,
  fix the Jms Multi Sites version detection to use the new manifest file name convention
  that is applicable with Joomla 1.6 and 1.7.

-------------------- 1.1.0 Stable Release [21-may-2011] ---------------------
- Add new implementations for the Joomla 1.6 compatibility.

-------------------- 1.0.13 Stable Release [25-jan-2011] ---------------------
- Fix the path to access the original "route.php" file when called from a Multisites Content Modules.

-------------------- 1.0.12 Stable Release [27-sep-2010] ---------------------
- Hide the Site_ID parameter in the URL when the SEF is enabled and that the site_id
  is present in the URL associated to the menu item id.
  Require JMS Multisites 1.2.34 or higher.
  
-------------------- 1.0.11 Stable Release [15-sep-2010] ---------------------
- Ensure that MultisiteContent cache routing file is always stored in the multisites content directory
  and not in foreign directory that may call the multisitescontent.
  
-------------------- 1.0.10 Stable Release [05-jan-2010] ---------------------
- Fix a PHP syntax error in the RSS feeds with frontpage.

-------------------- 1.0.9 Stable Release [01-jan-2010] ---------------------
- Fix a bug to display article when Joomla SEF is enabled.
  It may happen that the define JPATH_COMPONENT is not defined when computing the root.
  Now compute also the value when it is not defined.

-------------------- 1.0.8 Stable Release [04-jul-2009] ---------------------
- Remove some warnings that are reported by PHP 5 concerning deprecated syntax when referencing objects.

-------------------- 1.0.7 Stable Release [25-jun-2009] ---------------------
- Add now the possibility to use the "com_content" rendering present in a specific template to avoid
  create a specific "com_multisitescontent" in the specific template.
  The objective is to use the same themes rendering than the "com_content" when it is present.

-------------------- 1.0.6 Stable Release [25-feb-2009] ---------------------
- Fix problem when displaying an articles from "read more" that was not using the "Article" global parameters
  for the rendering.
  Now use the "articles" global parameters instead of the "Multi Sites articles" global parameters that does
  not exists.
  
-------------------- 1.0.5 Stable Release [18-feb-2009] ---------------------
- Fix problem in Front Page Blog that does not display correctly the articles.
  Only the first articles was displayed and the "articles" global parameters was corectly read
  for the rendering of the articles that has also cause problem in category list display.
  
-------------------- 1.0.4 Stable Release [05-feb-2009] ---------------------
- Fix problem in Category / Blog that does not display correctly the articles.
  Only the first articles was displayed and the "articles" global parameters was corectly read
  for the rendering of the articles that has also cause problem in category list display.

-------------------- 1.0.3 Stable Release [02-feb-2009] ---------------------
- Fix problem in Section / Blog that does not display correctly the articles.
  Two problem were identified:
  - Problem to get the default article parameters;
  - Problem with usage of require_once in blog_item that avoid calling processin.
    It has to be replaced by include to execute the item each times and not only the first time.
- Fix problem in the Javascript that is used to refresh the list of categories and list of sections
  when the user select another website.
  Internet explorer was not able to update the combo box and we had to do another implementation
  compatible with Internet Explorer.

-------------------- 1.0.2 Stable Release [12-jan-2009] ---------------------
- Fix problem with AJAX routines that call Category and Section element with first capital letter.
  The name must be in lower case due to unix case sensitivity and because the joomla files are present 
  in lower case.
- Fix problem syntax error when SEF is enabled to resolve the MultiSitesContentBuildRoute() and MultiSitesContentParseRoute().

-------------------- 1.0.1 Stable Release [05-jan-2009] ---------------------
Bug Fix:
Remove a Warning: Call-time pass-by-reference has been deprecated on line 268 and 309


-------------------- 1.0.0 Stable Release [24-dec-2008] ---------------------
First public distribution.

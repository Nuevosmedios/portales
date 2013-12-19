<?php
/**
 * @file       view.html.php
 * @brief      Wrapper for Multisites functionality (sharing content).
 * @version    1.1.0
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2011 Edwin2Win sprlu - all right reserved.
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
 * - V1.0.4 05-FEB-2009: Use the "com_content" global parameters instead of 'com_multisitescontent' 
 *                       as there are no global parameters for multisites.
 *                       This allow to show the title, and all the other parameters using the articles parameters.
 * - V1.0.7 25-JUN-2009: Add the possibility to use the "com_content" rendering present in a specific template.
 *                       This avoid to create a "com_multisitescontent" in the specific template or duplicate
 *                       the "com_content" into "com_multisitescontent".
 * - V1.1.0 19-MAY-2011: Add Joomla 1.6 compatibility.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$jms2win_jpath_component            = JPATH_COMPONENT;
$jms2win_jpath_component_original   = dirname( $jms2win_jpath_component) .DS. 'com_content';
$option = basename( $jms2win_jpath_component);
Jms2WinFactory::import( $jms2win_jpath_component,
                        $jms2win_jpath_component_original,
                        'views'.DS.'category'.DS.basename( __FILE__),
                        array( 'com_content' => $option,
                               "'content',"  => "'multisitescontent',",
                               "mainframe->getParams('$option')" => "mainframe->getParams('com_content')",
                                'protected $state'    => 'public $state',
                                'protected $items'    => 'public $items',
                                'protected $category' => 'public $category',
                                'protected $children' => 'public $children',
                                'protected $pagination'  => 'public $pagination',
                                'protected $lead_items'  => 'public $lead_items',
                                'protected $intro_items' => 'public $intro_items',
                                'protected $link_items'  => 'public $link_items',
                                'protected $columns'     => 'public $columns',
                                'parent::display($tpl);' => "\n"
                                                                  . "\$tplContentPath = \$this->_path['template'][0];\n"
                                                            		. "\$this->_path['template'][0] = JPATH_BASE.DS.'templates'.DS.JFactory::getApplication()->getTemplate().DS.'html'.DS.'com_content'.DS.\$this->getName();\n"
                                                            		. "\$this->_addPath('template', \$tplContentPath);\n"
                                                            		. 'parent::display($tpl);'
                             )
                      );

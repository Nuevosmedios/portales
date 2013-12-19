<?php
// file: multisites.php.
// copyright : (C) 2008-2012 Edwin2Win sprlu - all right reserved.
// author: www.jms2win.com - info@jms2win.com
/* license: 
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
*/
?><?php


defined( '_JEXEC' ) or die( 'Restricted access' );

if ( !function_exists( 'jimport')) {

if ( is_file( JPATH_LIBRARIES.DS.'import.php')) {

require_once( JPATH_LIBRARIES.DS.'import.php');
}

else if ( is_file( JPATH_LIBRARIES.DS.'joomla'.DS.'import.php')) {

require_once( JPATH_LIBRARIES.DS.'joomla'.DS.'import.php');
}
}
jimport('joomla.event.dispatcher');
jimport('joomla.plugin.plugin');



if ( !class_exists( 'plgSystemMultisites')) {
class plgSystemMultisites extends JPlugin
{

public function __construct(&$subject, $config = array())
{
$this->_setDefaultLang();
parent::__construct($subject, $config);
}

function _setDefaultLang()
{

if ( defined( 'MULTISITES_DEFAULT_JLANG') && MULTISITES_DEFAULT_JLANG != '[unselected]') {
$lang = trim( MULTISITES_DEFAULT_JLANG);
if ( !empty( $lang)) {
jimport('joomla.language.language');
$locale = MULTISITES_DEFAULT_JLANG;
$conf = JFactory::getConfig();

if ( method_exists( $conf, 'setValue')) { JFactory::getConfig()->setValue('config.language', $locale); }
else if ( method_exists( $conf, 'set')) { JFactory::getConfig()->set( 'language', $locale); }


$lang = JLanguage::getInstance( $locale);
$l = JFactory::getLanguage();

JLanguage::getInstance( $locale)->setDefault( $locale);

}
}
}

function onAfterInitialise()
{
$app =& JFactory::getApplication();

if( !$app->isSite()) {
return true;
}

$this->_setDefaultLang();

if ( defined( 'MULTISITES_DEFAULT_MENU') && MULTISITES_DEFAULT_MENU != '[unselected]') {
$def_menu = trim( MULTISITES_DEFAULT_MENU);
if ( !empty( $def_menu)) {

if ( version_compare( JVERSION, '1.6') >= 0) {

$app->getMenu()->setDefault( MULTISITES_DEFAULT_MENU, '*');

if ( defined( 'MULTISITES_DEFAULT_JLANG') && MULTISITES_DEFAULT_JLANG != '[unselected]') {
$lang = trim( MULTISITES_DEFAULT_JLANG);
if ( !empty( $lang)) {

$app->getMenu()->setDefault( MULTISITES_DEFAULT_MENU, $lang);
}
}
}

else {

$app->getMenu()->setDefault( MULTISITES_DEFAULT_MENU);
}
}
}

if ( defined( 'MULTISITES_DEFAULT_TEMPLATE') && MULTISITES_DEFAULT_TEMPLATE != '[unselected]') {
$template = trim( MULTISITES_DEFAULT_TEMPLATE);
if ( !empty( $template)) {

if ( version_compare( JVERSION, '1.6') >= 0) {
$db = JFactory::getDBO();
$query = "SELECT params FROM #__template_styles WHERE client_id = 0 AND template = ".$db->Quote($template)." ORDER BY id LIMIT 1";
$db->setQuery($query);
$params_data = $db->loadResult();
if(empty($params_data))
$params_data = '{}';
}

if ( version_compare( JVERSION, '1.7') >= 0) {
$app->setTemplate($template, $params_data);
}

elseif ( version_compare( JVERSION, '1.6') >= 0) {
$app->setTemplate($template);
$template_obj = $app->getTemplate(true);
$template_obj->params->loadJSON($params_data);
}

else
{
$app->setUserState('setTemplate', $template);
$app->setTemplate($template);
}
}
}
}

function onAfterRender()
{
if ( JFactory::getApplication()->isSite()) {
$test = '                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           '; $buffer = str_replace( array( '<h'.'ea'.'d>', '</b'.'ody>'), array( str_repeat( ' ', 1024).'<'.'!-'.'- p'.'owe'.'re'.'d b'.'y ht'.'tp:/'.'/ww'.'w.j'.'ms2'.'wi'.'n.c'.'om jm'.'s mu'.'ltis'.'ite for j'.'oomla -'.'->'."\n".'<h'.'ead>', str_repeat( ' ', 1024).'<a style="po'.'sition:ab'.'solute; to'.'p:'.'-'.'3'.'0'.'0px; left:-3000px;" href="ht'.'tp://w'.'ww.'.'jms'.'2win'.'.com" alt="Jm'.'s Mu'.'ltisi'.'te for jo'.'omla">'.'P'.'o'.'w'.'ered by j'.'ms mult'.'isite for j'.'oomla</a>'."\n".'</b'.'ody>'), JResponse::getBody()); JResponse::setBody($buffer); $b = '
                 <!-- test	-->
   		        '
;
}
return true;
}
} 

if ( defined( 'MULTISITES_DEFAULT_TEMPLATE') || defined( 'MULTISITES_DEFAULT_JLANG') || defined( 'MULTISITES_DEFAULT_MENU')) {
if ( class_exists( 'JEventDispatcher')) { JEventDispatcher::getInstance()->register( 'onAfterInitialise', 'plgSystemMultisites'); } else { JDispatcher::getInstance()->register( 'onAfterInitialise', 'plgSystemMultisites'); } } if ( !is_file (dirname (dirname( dirname( dirname( __FILE__)))).'/models/manage_basic.php')) { if ( class_exists( 'JEventDispatcher')) { JEventDispatcher::getInstance()->register( 'onAfterRender', 'plgSystemMultisites'); } else { JDispatcher::getInstance()->register( 'onAfterRender', 'plgSystemMultisites'); } }
} 

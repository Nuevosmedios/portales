<?php // no direct access
defined('_JEXEC') or die('Restricted access');
// Redirect to the original content

if ( version_compare( JVERSION, '1.6') >= 0) {
   // Load the "com_content" languages files.
   	JFactory::getLanguage()->load('com_content', JPATH_BASE, null, false, false)
   ||	JFactory::getLanguage()->load('com_content', dirname( JPATH_COMPONENT).DS.'com_content', null, false, false)
   ||	JFactory::getLanguage()->load('com_content', JPATH_BASE, JFactory::getLanguage()->getDefault(), false, false)
   ||	JFactory::getLanguage()->load('com_content', dirname( JPATH_COMPONENT).DS.'com_content', JFactory::getLanguage()->getDefault(), false, false);
}
include(dirname( JPATH_COMPONENT).DS.'com_content'.DS.'views'.DS.'category'.DS.'tmpl'.DS.basename( __FILE__));

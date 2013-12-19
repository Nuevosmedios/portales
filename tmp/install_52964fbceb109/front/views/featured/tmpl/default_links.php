<?php // no direct access
defined('_JEXEC') or die('Restricted access');

// Joomla 1.6 only
// Load the "com_content" languages files.
	JFactory::getLanguage()->load('com_content', JPATH_BASE, null, false, false)
||	JFactory::getLanguage()->load('com_content', dirname( JPATH_COMPONENT).DS.'com_content', null, false, false)
||	JFactory::getLanguage()->load('com_content', JPATH_BASE, JFactory::getLanguage()->getDefault(), false, false)
||	JFactory::getLanguage()->load('com_content', dirname( JPATH_COMPONENT).DS.'com_content', JFactory::getLanguage()->getDefault(), false, false);

// Redirect to the original content
include(dirname( JPATH_COMPONENT).DS.'com_content'.DS.'views'.DS.'featured'.DS.'tmpl'.DS.basename( __FILE__));

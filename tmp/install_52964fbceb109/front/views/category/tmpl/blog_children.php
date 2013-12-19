<?php // no direct access
defined('_JEXEC') or die('Restricted access');

// Joomla 1.6 only
// Load the "com_content" languages files.
	JFactory::getLanguage()->load('com_content', JPATH_BASE, null, false, false)
||	JFactory::getLanguage()->load('com_content', dirname( JPATH_COMPONENT).DS.'com_content', null, false, false)
||	JFactory::getLanguage()->load('com_content', JPATH_BASE, JFactory::getLanguage()->getDefault(), false, false)
||	JFactory::getLanguage()->load('com_content', dirname( JPATH_COMPONENT).DS.'com_content', JFactory::getLanguage()->getDefault(), false, false);

$jms2win_jpath_component            = JPATH_COMPONENT;
$jms2win_jpath_component_original   = dirname( $jms2win_jpath_component) .DS. 'com_content';
$option = basename( $jms2win_jpath_component);
Jms2WinFactory::import( $jms2win_jpath_component,
                        $jms2win_jpath_component_original,
                        'views'.DS.'category'.DS.'tmpl'.DS.basename( __FILE__),
                        array( 'com_content' => $option,
                               "'content',"  => "'multisitescontent',"
                             ),
                        false
                      );

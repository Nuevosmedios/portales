<?php // no direct access
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

global $option;
Jms2WinFactory::import( JPATH_COMPONENT,
                        JPATH_COMPONENT_ORIGINAL,
                        'views'.DS.'section'.DS.'tmpl'.DS.basename( __FILE__),
                        array( 'com_content' => $option,
                               "'content',"  => "'multisitescontent',"
                             )
                      );


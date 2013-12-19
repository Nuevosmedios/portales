<?php // no direct access
defined('_JEXEC') or die('Restricted access');
global $option;
Jms2WinFactory::import( JPATH_COMPONENT,
                        JPATH_COMPONENT_ORIGINAL,
                        'views'.DS.'frontpage'.DS.basename( __FILE__),
                        array( 'JFactory::getDBO()'   => 'Jms2WinFactory::getMultiSitesDBO()',
                               'com_content'          => $option
                             )
                      );

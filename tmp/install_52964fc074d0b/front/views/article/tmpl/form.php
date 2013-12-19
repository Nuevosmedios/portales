<?php // no direct access
defined('_JEXEC') or die('Restricted access');
global $option;
Jms2WinFactory::import( JPATH_COMPONENT,
                        JPATH_COMPONENT_ORIGINAL,
                        'views'.DS.'article'.DS.'tmpl'.DS.basename( __FILE__),
                        array( '"com_content"' => '"' . $option . '"'
                             )
                      );

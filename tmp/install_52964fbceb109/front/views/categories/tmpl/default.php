<?php // no direct access
defined('_JEXEC') or die('Restricted access');
$jms2win_jpath_component            = JPATH_COMPONENT;
$jms2win_jpath_component_original   = dirname( $jms2win_jpath_component) .DS. 'com_content';
$option = basename( $jms2win_jpath_component);
Jms2WinFactory::import( $jms2win_jpath_component,
                        $jms2win_jpath_component_original,
                        'views'.DS.'categories'.DS.'tmpl'.DS.basename( __FILE__),
                        array( 'com_content' => $option,
                               "'content',"  => "'multisitescontent',"
                             )
                      );

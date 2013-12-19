<?php // no direct access
defined('_JEXEC') or die('Restricted access');
// Redirect to the Joomla Multi Sites implementation
require_once( dirname( dirname(__FILE__)).'/legacy.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites'.DS.'elements'.DS.basename( __FILE__));

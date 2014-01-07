<?php
/**
 * @version $Id: mod_acctexp.php
 * @package AEC - Account Control Expiration - Subscription component for Joomla! OS CMS
 * @subpackage Module
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

$user = &JFactory::getUser();

if ( $user->id ) {
	require_once( JPATH_SITE . '/components/com_acctexp/acctexp.class.php' );

	$class_sfx				= $params->get( 'moduleclass_sfx', "");
	$pretext 				= $params->get( 'pretext' );
	$posttext 				= $params->get( 'posttext' );
	$showExpiration 		= $params->def( 'show_expiration', 0 );
	$displaypipeline		= $params->get( 'displaypipeline', 0 );

	$lang =& JFactory::getLanguage();

	$lang->load( 'mod_acctexp', JPATH_SITE );

	require ( JModuleHelper::getLayoutPath('mod_acctexp', $params->get('layout', 'default')) );
}



?>

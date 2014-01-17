<?php
/**
 * @version $Id: access_denied/html.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

$tmpl->setTitle( JText::_('NOT_ALLOWED_HEADLINE') );

if ( $user->id ) {
	$registerlink = AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=renewsubscription' );
	$loggedin = 1;
} else {
	$loggedin = 0;
	if ( GeneralInfoRequester::detect_component( 'anyCB' ) ) {
		$registerlink = AECToolbox::deadsureURL( 'index.php?option=com_comprofiler&task=registers' );
	} else {
		$registerlink = AECToolbox::deadsureURL( 'index.php?option=com_user&view=register' );
	}
}

$tmpl->addDefaultCSS();

@include( $tmpl->tmpl( 'access_denied' ) );

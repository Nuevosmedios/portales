<?php
/**
 * @version $Id: expired/html.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

if ( empty( $metaUser->userid ) ) {
	aecRedirect( AECToolbox::deadsureURL( 'index.php' ) );
}

$trial		= false;
$expired	= false;
$invoice	= false;

if ( $metaUser->hasSubscription ) {
	// Make sure this really is expired
	if ( !$metaUser->objSubscription->is_expired() ) {
		return getView( 'access_denied' );
	}

	$expired = strtotime( $metaUser->objSubscription->expiration );

	$trial = ( strcmp($metaUser->objSubscription->status, 'Trial') === 0 );
	if ( !$trial ) {
		$params = $metaUser->objSubscription->params;
		if ( isset( $params['trialflag'])) {
			$trial = 1;
		}
	}
}

$invoices = AECfetchfromDB::InvoiceCountbyUserID( $metaUser->userid );

if ( $invoices ) {
	$invoice = AECfetchfromDB::lastUnclearedInvoiceIDbyUserID( $metaUser->userid );
} else {
	$invoice = null;
}

$expiration	= AECToolbox::formatDate( $expired );

$tmpl->setTitle( JText::_('EXPIRED_TITLE') );

$continue = false;
if ( $tmpl->cfg['continue_button'] && $metaUser->hasSubscription ) {
	$status = SubscriptionPlanHandler::PlanStatus( $metaUser->focusSubscription->plan );
	if ( !empty( $status ) ) {
		$continue = true;
	}
}

$intro = 0;

if ( $metaUser->hasSubscription ) {
	if ( $metaUser->objSubscription->status == "Expired" ) {
		$intro = !$tmpl->cfg['intro_expired'];
	}
}

$tmpl->addDefaultCSS();

@include( $tmpl->tmpl( 'expired' ) );

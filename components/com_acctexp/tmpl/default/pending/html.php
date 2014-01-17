<?php
/**
 * @version $Id: pending/html.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

if ( $userid == 0 ) {
	return aecRedirect( AECToolbox::deadsureURL( 'index.php' ) );
}

if ( $metaUser->hasSubscription ) {
	// Make sure this really is pending
	if ( strcmp($metaUser->objSubscription->status, 'Pending') !== 0 ) {
		return getView( 'access_denied' );
	}
}

$invoices = AECfetchfromDB::InvoiceCountbyUserID( $userid );

$reason = "";
if ( $invoices ) {
	$invoice = AECfetchfromDB::lastUnclearedInvoiceIDbyUserID( $userid );

	$objInvoice = new Invoice();
	$objInvoice->loadInvoiceNumber( $invoice );

	$params = $objInvoice->params;

	if ( isset( $params['pending_reason'] ) ) {
		$lang = JFactory::getLanguage();
		if ( $lang->hasKey( 'PENDING_REASON_' . strtoupper( $params['pending_reason'] ) ) ) {
			$reason = JText::_( 'PENDING_REASON_' . strtoupper( $params['pending_reason'] ) );
		} else {
			$reason = $params['pending_reason'];
		}
	} elseif ( strcmp( $objInvoice->method, 'transfer' ) === 0 ) {
		$reason = 'transfer';
	} else {
		$reason = 0;
	}
} else {
	$invoice = 'none';
}

$namearray = $metaUser->explodeName();

$name = $namearray['first'];

$tmpl->addDefaultCSS();

$tmpl->setTitle( JText::_('PENDING_TITLE') );

@include( $tmpl->tmpl( 'pending' ) );

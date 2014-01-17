<?php
/**
 * @version $Id: checkout/html.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

$makegift = false;

if ( !empty( $tmpl->cfg['checkout_as_gift'] ) ) {
	if ( !empty( $tmpl->cfg['checkout_as_gift_access'] ) ) {
		if ( $InvoiceFactory->metaUser->hasGroup( $tmpl->cfg['checkout_as_gift_access'] ) ) {
			$makegift = true;
		}
	} else {
		$makegift = true;
	}
}

$InvoiceFactory->invoice->deformatInvoiceNumber();

$tmpl->addDefaultCSS();

$tmpl->setTitle( $InvoiceFactory->checkout['checkout_title'] );

if ( $tmpl->cfg['checkoutform_jsvalidation'] ) {
	$tmpl->addScript( JURI::root(true) . '/media/com_acctexp/js/ccvalidate.js' );
}

@include( $tmpl->tmpl( 'checkout' ) );

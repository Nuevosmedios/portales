<?php
/**
 * @version $Id: tool_invoicereactivate.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Toolbox - Invoice Reactivation
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class tool_invoicereactivate
{
	function Info()
	{
		$info = array();
		$info['name'] = "Invoice Reactivation";
		$info['desc'] = "If a user has expired on a recurring billing invoice due to a technical error, you can reinstate the invoice and account to how they were before the expiration. The expiration date will be set in 1 minute ago so that another attempt at charging the account can be made.";

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['invoices']			= array( 'inputD', 'Invoices', 'A list of invoices you want to revert, separated by line breaks.', '' );

		return $settings;
	}

	function Action()
	{
		$return = '';

		if ( !empty( $_POST['invoices'] ) ) {
			$notfound = array();
			$updated = array();

			$db = &JFactory::getDBO();

			$list = explode( "\n", $_POST['invoices'] );
			foreach ( $list as $li ) {
				$invoice = new Invoice();
				$invoice->loadInvoiceNumber( trim( $li ) );

				if ( empty( $invoice->id ) ) {
					$notfound[] = trim( $li );

					continue;
				}

				if ( !is_numeric( $invoice->usage ) ) {
					$notfound[] = trim( $li );

					continue;
				}

				$metaUser = new metaUser( $invoice->userid );

				$cfid = $metaUser->focusSubscription->id;

				if ( $cfid != $invoice->subscr_id ) {
					if ( !$metaUser->moveFocus( $invoice->subscr_id ) ) {
						$notfound[] = trim( $li );

						continue;
					}
				}

				if ( $metaUser->focusSubscription->status == 'Active' ) {
					$metaUser->focusSubscription->expiration = date( 'Y-m-d H:i:s', ( (int) gmdate('U') )-60 );

					$metaUser->focusSubscription->plan = $invoice->usage;
					$metaUser->focusSubscription->type = $invoice->method;
					$metaUser->focusSubscription->recurring = 1;

					$metaUser->focusSubscription->check();
					$metaUser->focusSubscription->store();

					$updated[] = $invoice->invoice_number;
				} else {
					$metaUser->focusSubscription->status = 'Active';
					$metaUser->focusSubscription->expiration = date( 'Y-m-d H:i:s', ( (int) gmdate('U') )-60 );

					$metaUser->focusSubscription->plan = $invoice->usage;
					$metaUser->focusSubscription->type = $invoice->method;
					$metaUser->focusSubscription->recurring = 1;

					$metaUser->focusSubscription->check();
					$metaUser->focusSubscription->store();
					
					$updated[] = $invoice->invoice_number;
				}
			}

			$return = '<p>Provided ' . count( $list ) . ' Invoice Numbers.</p>';
			if ( count( $updated ) ) {
				$return .= '<p>Updated ' . count( $updated ) . ' Invoices:</p>';
				$return .= '<p>' . implode( ', ', $updated ) . '</p>';
			}

			if ( count( $notfound ) ) {
				$return .= '<p>Failed to process the following ' . count( $notfound ) . ' Invoices:</p>';
				$return .= '<p>' . implode( ', ', $notfound ) . '</p>';
			}
		} elseif ( isset( $_POST['invoices'] ) ) {
			$return = '<p>Please enter invoice numbers.</p>';
		}

		return $return;
	}

}
?>

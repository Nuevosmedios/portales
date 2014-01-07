<?php
/**
 * @version $Id: sofort.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Sofort Gateway
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_sofort extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'sofort';
		$info['longname']		= JText::_('CFG_SOFORT_LONGNAME');
		$info['statement']		= JText::_('CFG_SOFORT_STATEMENT');
		$info['description']	= JText::_('CFG_SOFORT_DESCRIPTION');
		$info['currencies']		= AECToolbox::aecCurrencyField( true, true, true, true );
		$info['languages']		= AECToolbox::getISO639_1_codes();
		$info['cc_list']		= "";
		$info['recurring']		= 2;
		$info['actions']		= array( 'cancel' => array( 'confirm' ) );

		return $info;
	}

	function getLogoFilename()
	{
		return 'sofortueberweisung.png';
	}

	function getActions( $invoice, $subscription )
	{
		$actions = parent::getActions( $invoice, $subscription );

		if ( ( $subscription->status == 'Cancelled' ) || ( $invoice->transaction_date == '0000-00-00 00:00:00' ) ) {
			if ( isset( $actions['cancel'] ) ) {
				unset( $actions['cancel'] );
			}
		}

		return $actions;
	}

	function settings()
	{
		$settings = array();
		$settings['customer_id']		= "12345";
		$settings['api_key']			= "123467890";
		$settings['project_id']			= "project_id";

		$settings['currency']			= "EUR";
		$settings['language']			= 'US';

		$settings['item_name']			= "[[invoice_number]]";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['customer_id']		= array( "inputC" );
		$settings['api_key']			= array( "inputC" );
		$settings['project_id'] 		= array( "inputC" );

		$settings['currency']			= array( "list_currency" );
		$settings['language']			= array( 'list_language' );

		$settings['item_name']			= array( "inputE" );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createRequestXML( $request )
	{
		if ( is_array( $request->int_var['amount'] ) ) {
			$amt = $request->int_var['amount']['amount3'];
		} else {
			$amt = $request->int_var['amount'];
		}

		$content =	'<?xml version="1.0" encoding="UTF-8"?>'
					. '<multipay>'
					. '<project_id>' . trim( $this->settings['project_id'] ) . '</project_id>'
					. '<interface_version>' . _AEC_VERSION  . ' rev' . _AEC_REVISION . '</interface_version>'
					. '<language_code>' . strtolower( $this->settings['language'] ) . '</language_code>'
					. '<preselection>' . ( is_array( $request->int_var['amount'] ) ? 'sa' : 'su' ) . '</project_id>'
					. '<amount>' . $amt . '</amount>'
					. '<currency_code>' . ( $this->settings['currency'] ) . '</currency_code>'
					;

		$content .= '<reasons><reason>' . AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request ) . '</reason></reasons>';

		$content .= '<success_url>' . AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=thanks' ) . '</success_url>';
		$content .= '<abort_url>' . AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=cancel' ) . '</abort_url>';
		$content .= '<notification_urls><notification_url>' . AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=sofortnotification' ) . '</notification_url></notification_urls>';
		$content .= '<user_variables><invoice_number>' . $request->invoice->invoice_number . '</invoice_number></user_variables>';

		if ( is_array( $request->int_var['amount'] ) ) {
			$content .= '<sa>';

			$content .= '<payments>0</payments>';
			$content .= '<interval>' . $this->convertPeriodUnit( $request->int_var['amount']['period3'], $request->int_var['amount']['unit3'] ) . '</interval>';

			$content .= '</sa>';
		} else {
			$content .= '<su>';

			$content .= '<reasons><reason>' . AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request ) . '</reason></reasons>';
			$content .= '<amount>' . $request->int_var['amount'] . '</amount>';

			$content .= '</su>';
		}

		$content .= '</multipay>';

		return $content;
	}

	function transmitRequestXML( $xml, $request )
	{
		$response = $this->transmitToSofort( $xml );

		$transaction = $this->XMLsubstring_tag( $response, 'transaction' );

		if ( empty( $transaction ) ) {
			$return['error']	= "Invalid Response by Sofort Gateway";			

			return $return;
		} else {
			$redirect = $this->XMLsubstring_tag( $response, 'payment_url' );

			$request->invoice->secondary_ident = $transaction;
			$request->invoice->storeload();

			return aecRedirect( $redirect );
		}
	}

	function convertPeriodUnit( $period, $unit )
	{
		switch ( $unit ) {
			case 'D':
				if ( $period <= 42 ) {
					return 1;
				} elseif ( $period <= 140 ) {
					return 3;
				} elseif ( ( $period > 140 ) && ( $period <= 196 ) ) {
					return 6;
				} else {
					return 12;
				}
				break;
			case 'W':
				if ( $period <= 5 ) {
					return 1;
				} elseif ( $period <= 20 ) {
					return 3;
				} elseif ( ( $period > 20 ) && ( $period <= 36 ) ) {
					return 6;
				} else {
					return 12;
				}
				break;
			case 'M':
				if ( $period <= 3 ) {
					return 1;
				} elseif ( ( $period > 3 ) && ( $period <= 5 ) ) {
					return 3;
				} elseif ( ( $period > 5 ) && ( $period <= 10 ) ) {
					return 6;
				} else {
					return 12;
				}
				break;
			case 'Y':
				return 12;
				break;
		}
	}

	function customaction_cancel( $request )
	{
		$xml =	'<?xml version="1.0" encoding="UTF-8"?>'
				. '<cancel_sa version="1.0">'
				. '<transaction>' . $request->invoice->secondary_ident . '</transaction>'
				. '</cancel_sa>'
			;

		$response = $this->transmitToSofort( $xml );

		$redirect = $this->XMLsubstring_tag( $response, 'cancel_url' );

		if ( !empty( $redirect ) ) {
			return aecRedirect( $redirect );
		} else {
			getView( 'error', array(	'error' => "An error occured while cancelling your subscription. Please contact the system administrator!",
										'metaUser' => $request->metaUser,
										'invoice' => $request->invoice,
										'suppressactions' => true
									) );
		}
	}

	function parseNotification( $post )
	{
		$transaction = $this->XMLsubstring_tag( $post, 'transaction' );

		if ( empty( $transaction ) ) {
			$response['error']				= true;
			$response['errormsg']			= "Notification by Sofort Gateway lacks transaction number. Possible Fraud attempt.";

			return $response;
		}

		$xml =	'<?xml version="1.0" encoding="UTF-8"?>'
				. '<transaction_request version="1.0">'
				. '<transaction>' . $transaction . '</transaction>'
				. '</transaction_request>'
			;

		$check = $this->transmitToSofort( $xml );

		$response['raw'] = $check;

		$response['invoice'] = $this->XMLsubstring_tag( $check, 'invoice_number' );

		if ( empty( $response['invoice'] ) ) {
			$response['error']				= true;
			$response['errormsg']			= "Could not verify notification with Sofort Gateway. Possible Fraud attempt.";
		} else {
			$response['amount_paid']		= $this->XMLsubstring_tag( $check, 'amount' );
			$response['amount_currency']	= $this->XMLsubstring_tag( $check, 'currency_code' );
		}

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 0;

		if ( empty( $response['error'] ) ) {
			$status = $this->XMLsubstring_tag( $response['raw'], 'status' );
			$status_reason = $this->XMLsubstring_tag( $response['raw'], 'status_reason' );

			switch ( $status ) {
				default:
				case 'pending':
					$response['pending']		= 1;
					$response['pending_reason']	= 'signup';
					break;
				case 'refunded':
				case 'loss':
					if ( $status_reason = 'canceled' ) {
						$response['cancel']		= 1;
					} else {
						$response['delete']		= 1;
					}
					break;
				case 'received':
					$response['valid'] = 1;
					break;

			}
		}

		return $response;
	}

	function transmitToSofort( $xml )
	{
		$path = "/api/xml";
		$url = "https://api.sofort.com" . $path;

		$header = array();
		$header["Authorization"]	= "Basic " . base64_encode( trim( $this->settings['customer_id'] ) .':'. trim( $this->settings['api_key'] ) );
		$header["Content-Type"]		= "application/xml; charset=UTF-8";
		$header["Accept"]			= "application/xml; charset=UTF-8";

		return $this->transmitRequest( $url, $path, $xml, 443 );
	}
}
?>
<?php
/**
 * @version $Id: authorize_arb.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Authorize ARB
 * @copyright 2007-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_authorize_arb extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'authorize_arb';
		$info['longname']		= JText::_('CFG_AUTHORIZE_ARB_LONGNAME');
		$info['statement']		= JText::_('CFG_AUTHORIZE_ARB_STATEMENT');
		$info['description']	= JText::_('CFG_AUTHORIZE_ARB_DESCRIPTION');
		$info['currencies']		= AECToolbox::aecCurrencyField( true, true, true, true );
		$info['cc_list']		= "visa,mastercard,discover,americanexpress,echeck,jcb,dinersclub";
		$info['recurring']		= 1;
		$info['actions']		= array( 'cancel' => array( 'confirm' ) );
		$info['secure']			= 1;

		return $info;
	}

	function getLogoFilename()
	{
		return 'authorize.png';
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
		$settings['login']					= "login";
		$settings['transaction_key']		= "transaction_key";
		$settings['testmode']				= 0;
		$settings['ignore_empty_invoices']	= 0;
		$settings['currency']				= "USD";
		$settings['promptAddress']			= 0;
		$settings['totalOccurrences']		= 12;
		$settings['trialOccurrences']		= 1;
		$settings['useSilentPostResponse']	= 1;
		$settings['item_name']				= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']				= array( "toggle" );
		$settings['ignore_empty_invoices']	= array( "toggle" );
		$settings['login'] 					= array( "inputC" );
		$settings['transaction_key']		= array( "inputC" );
		$settings['currency']				= array( "list_currency" );
		$settings['promptAddress']			= array( "toggle" );
		$settings['totalOccurrences']		= array( "inputA" );
		$settings['trialOccurrences']		= array( "inputA" );
		$settings['useSilentPostResponse']	= array( "toggle" );
		$settings['SilentPost_info']		= array( "fieldset" );
		$settings['item_name']				= array( "inputE" );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function checkoutform( $request )
	{
		$var = $this->getCCform();

		$values = array( 'firstname', 'lastname' );

		if ( !empty( $this->settings['promptAddress'] ) ) {
			$values = array_merge( $values, array( 'address', 'city', 'state_usca', 'zip', 'country_list' ) );
		}

		$var = $this->getUserform( $var, $values, $request->metaUser );

		return $var;
	}

	function createRequestXML( $request )
	{
		// Start xml, add login and transaction key, as well as invoice number
		$content =	'<?xml version="1.0" encoding="utf-8"?>'
					. '<ARBCreateSubscriptionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">'
					. '<merchantAuthentication>'
					. '<name>' . trim( substr( $this->settings['login'], 0, 25 ) ) . '</name>'
					. '<transactionKey>' . trim( substr( $this->settings['transaction_key'], 0, 16 ) ) . '</transactionKey>'
					. '</merchantAuthentication>'
					. '<refId>' . $request->invoice->invoice_number . '</refId>';

		$full = $this->convertPeriodUnit( $request->int_var['amount']['period3'], $request->int_var['amount']['unit3'] );

		$name = AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );

		// Add Payment information
		$content .=	'<subscription>'
					. '<name>' . trim( substr( $name, 0, 20 ) ) . '</name>'
					. '<paymentSchedule>'
					. '<interval>'
					. '<length>' . trim( $full['period'] ) . '</length>'
					. '<unit>' . trim( $full['unit'] ) . '</unit>'
					. '</interval>'
					. '<startDate>' . trim( date( 'Y-m-d' ) ) . '</startDate>'
					. '<totalOccurrences>' . trim( $this->settings['totalOccurrences'] ) . '</totalOccurrences>';

		if ( isset( $request->int_var['amount']['amount1'] ) ) {
			$content .=	'<trialOccurrences>' . trim( $this->settings['trialOccurrences'] ) . '</trialOccurrences>';
		}

		$content .=	 '</paymentSchedule>'
					. '<amount>' . trim( $request->int_var['amount']['amount3'] ) .'</amount>';

		if ( isset( $request->int_var['amount']['amount1'] ) ) {
			$content .=	 '<trialAmount>' . trim( $request->int_var['amount']['amount1'] ) . '</trialAmount>';
		}

		$expirationDate =  $request->int_var['params']['expirationYear'] . '-' . str_pad( $request->int_var['params']['expirationMonth'], 2, '0', STR_PAD_LEFT );

		$content .=	'<payment>'
					. '<creditCard>'
					. '<cardNumber>' . trim( $request->int_var['params']['cardNumber'] ) . '</cardNumber>'
					. '<expirationDate>' . trim( $expirationDate ) . '</expirationDate>'
					. '</creditCard>'
					. '</payment>'
					;

		$content .=	'<order>'
					. '<invoiceNumber>' . $request->invoice->invoice_number . '</invoiceNumber>'
					. '<description>' . AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request ) . '</description>'
					. '</order>'
					;

		$content .=	 '<billTo>'
					. '<firstName>'. trim( $request->int_var['params']['billFirstName'] ) . '</firstName>'
					. '<lastName>' . trim( $request->int_var['params']['billLastName'] ) . '</lastName>'
					;

		if ( isset( $request->int_var['params']['billAddress'] ) ) {
			$content .=	 '<address>'. trim( $request->int_var['params']['billAddress'] ) . '</address>'
						. '<city>' . trim( $request->int_var['params']['billCity'] ) . '</city>'
						. '<state>'. trim( $request->int_var['params']['billState'] ) . '</state>'
						. '<zip>' . trim( $request->int_var['params']['billZip'] ) . '</zip>'
						. '<country>'. trim( $request->int_var['params']['billCountry'] ) . '</country>'
						;
		}

		$content .=	'</billTo>';
		$content .=	'</subscription>';

		// Close Request
		$content .=	'</ARBCreateSubscriptionRequest>';

		return $content;
	}

	function transmitRequestXML( $xml, $request )
	{
		// ARB doesn't validate the transactions until the batch processing occurs.  They only do
		// an initial algorithm check, which the test credit cards pass.  So we have to catch them
		// here until Authorize.net decides it's a good idea too. See this page for the test cards:
		// http://developer.authorize.net/faqs/#7429 [KML]
		$cc_testnumbers = array("370000000000002", "6011000000000012", "5424000000000015", "4007000000027",
								"4012888818888", "3088000000000017", "38000000000006", "4222222222222" );

		if ( !$this->settings['testmode'] && in_array( trim( $request->int_var['params']['cardNumber'] ), $cc_testnumbers)) {
			$response['valid'] = false;
			$response['error'] = "Credit Card Number is not a valid ARB testing CC Number";
			return $response;
		}

		$path = "/xml/v1/request.api";
		if ( $this->settings['testmode'] ) {
			$url = "https://apitest.authorize.net" . $path;
		} else {
			$url = "https://api.authorize.net" . $path;
		}

		$response = $this->transmitRequest( $url, $path, $xml, 443 );

		$return['valid'] = false;
		$return['raw'] = $response;

		if ( $response ) {
			$resultCode = $this->substring_between($response,'<resultCode>','</resultCode>');

			$code = $this->substring_between($response,'<code>','</code>');
			$text = $this->substring_between($response,'<text>','</text>');

			if ( strcmp( $resultCode, 'Ok' ) === 0) {
				$return['valid'] = 1;
			} else {
				$return['error'] = $text;
			}

			if ( ( $this->settings['totalOccurrences'] > 1 ) && !$this->settings['useSilentPostResponse'] ) {
				$return['multiplicator'] = $this->settings['totalOccurrences'];
			}

			$subscriptionId = $this->substring_between($response,'<subscriptionId>','</subscriptionId>');

			$return['invoiceparams'] = array( "subscriptionid" => $subscriptionId );
		}

		return $return;
	}

	function substring_between( $haystack, $start, $end )
	{
		if ( strpos( $haystack, $start ) === false || strpos( $haystack, $end ) === false ) {
			return false;
		 } else {
			$start_position = strpos( $haystack, $start ) + strlen( $start );
			$end_position = strpos( $haystack, $end );
			return substr( $haystack, $start_position, $end_position - $start_position );
		}
	}

	function convertPeriodUnit( $period, $unit )
	{
		$return = array();
		switch ( $unit ) {
			case 'D':
				$return['unit'] = 'days';
				$return['period'] = $period;
				break;
			case 'W':
				if ( $period % 4 == 0 ) {
					$return['unit'] = 'months';
					$return['period'] = $period/4;
				} else {
					$return['unit'] = 'days';
					$return['period'] = $period*7;
				}
				break;
			case 'M':
				$return['unit'] = 'months';
				$return['period'] = $period;
				break;
			case 'Y':
				$return['unit'] = 'months';
				$return['period'] = $period*12;
				break;
		}

		return $return;
	}

	function customaction_cancel( $request )
	{
		$content =	'<?xml version="1.0" encoding="utf-8"?>'
					. '<ARBCancelSubscriptionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">'
					. '<merchantAuthentication>'
					. '<name>' . trim( substr( $this->settings['login'], 0, 25 ) ) . '</name>'
					. '<transactionKey>' . trim( substr( $this->settings['transaction_key'], 0, 16 ) ) . '</transactionKey>'
					. '</merchantAuthentication>'
					. '<refId>' . $request->invoice->invoice_number . '</refId>';

		// Add Payment information
		$content .=	'<subscriptionId>' . $request->invoice->params['subscriptionid'] . '</subscriptionId>';

		// Close Request
		$content .=	'</ARBCancelSubscriptionRequest>';

		$path = "/xml/v1/request.api";
		if ( $this->settings['testmode'] ) {
			$url = "https://apitest.authorize.net" . $path;
		} else {
			$url = "https://api.authorize.net" . $path;
		}

		$response = $this->transmitRequest( $url, $path, $content, 443 );

		if ( !empty( $response ) ) {
			$responsestring = $response;

			$resultCode = $this->substring_between( $response,'<resultCode>','</resultCode>' );

			$code = $this->substring_between( $response,'<code>','</code>' );
			$text = $this->substring_between( $response,'<text>','</text>' );

			$return['invoice'] = $this->substring_between( $response,'<refId>','</refId>' );

			if ( strcmp( $resultCode, 'Ok' ) === 0 ) {
				$return['valid'] = 0;
				$return['cancel'] = true;
			} else {
				switch ( $code ) {
					case 'XYZ':
						$text = "Custom Error";
						break;
				}

				$return['valid'] = 0;
				$return['error'] = $text . "(code: $code)";
			}

			return $return;
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
		$x_description			= $post['x_description'];

		$x_amount				= $post['x_amount'];
		$userid					= $post['x_cust_id'];

		$response = array();
		$response['invoice']	= $post['x_invoice_num'];

		if ( empty( $response['invoice'] ) && $this->settings['ignore_empty_invoices'] ) {
			unset( $response['invoice'] );

			$response['null']			= 1;
			$response['explanation']	= 'Empty Invoice Number - ignored.';
		}

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		if ( $post['x_subscription_paynum'] > 1 ) {
			$x_response_code		= $post['x_response_code'];
			$x_response_reason_text	= $post['x_response_reason_text'];

			$response['valid'] = ( $x_response_code == '1' );
		} else {
			$response['valid'] = 0;
			$response['duplicate'] = true;
		}

		return $response;
	}

}
?>
<?php
/**
 * @version $Id: paypal.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - PayPal Buy Now
 * @copyright 2007-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_paypal extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'paypal';
		$info['longname']		= JText::_('AEC_PROC_INFO_PP_LNAME');
		$info['statement']		= JText::_('AEC_PROC_INFO_PP_STMNT');
		$info['description']	= JText::_('DESCRIPTION_PAYPAL');
		$info['currencies']		= 'EUR,USD,GBP,AUD,CAD,JPY,NZD,CHF,HKD,SGD,SEK,DKK,PLN,NOK,HUF,CZK,MXN,ILS,BRL,MYR,PHP,TWD,THB,ZAR';
		$info['languages']		= AECToolbox::getISO639_1_codes();
		$info['cc_list']		= 'visa,mastercard,discover,americanexpress,echeck,giropay';
		$info['recurring']		= 0;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['business']		= 'your@paypal@account.com';
		$settings['testmode']		= 0;
		$settings['brokenipnmode']	= 0;
		$settings['invoice_tax']	= 0;
		$settings['tax']			= '';
		$settings['currency']		= 'USD';
		$settings['checkbusiness']	= 0;
		$settings['acceptpendingecheck'] = 0;
		$settings['lc']				= 'US';
		$settings['no_shipping']	= 1;
		$settings['altipnurl']		= '';
		$settings['item_name']		= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['item_number']	= '[[user_id]]';
		$settings['customparams']	= "";

		// Customization Options
		$settings['cbt']					= '';
		$settings['cn']						= '';
		$settings['cpp_header_image']		= '';
		$settings['cpp_headerback_color']	= '';
		$settings['cpp_headerborder_color']	= '';
		$settings['cpp_payflow_color']		= '';
		$settings['cs']						= 0;
		$settings['image_url']				= '';
		$settings['page_style']				= '';

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();

		$settings['business']				= array( 'inputC' );
		$settings['testmode']				= array( 'toggle' );
		$settings['brokenipnmode']			= array( 'toggle' );
		$settings['invoice_tax']			= array( 'toggle' );
		$settings['tax']					= array( 'inputA' );
		$settings['currency']				= array( 'list_currency' );
		$settings['checkbusiness']			= array( 'toggle' );
		$settings['acceptpendingecheck']	= array( 'toggle' );
		$settings['lc']						= array( 'list_language' );
		$settings['no_shipping']			= array( 'toggle' );
		$settings['altipnurl']				= array( 'inputC' );
		$settings['item_name']				= array( 'inputE' );
		$settings['item_number']			= array( 'inputE' );
		$settings['customparams']			= array( 'inputD' );

		// Customization Options
		$settings['cbt']					= array( 'inputE' );
		$settings['cn']						= array( 'inputE' );
		$settings['cpp_header_image']		= array( 'inputE' );
		$settings['cpp_headerback_color']	= array( 'inputC' );
		$settings['cpp_headerborder_color']	= array( 'inputC' );
		$settings['cpp_payflow_color']		= array( 'inputC' );
		$settings['cs']						= array( 'toggle' );
		$settings['image_url']				= array( 'inputE' );
		$settings['page_style']				= array( 'inputE' );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		if ( $this->settings['testmode'] ) {
			$var['post_url']	= 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		} else {
			$var['post_url']	= 'https://www.paypal.com/cgi-bin/webscr';
		}

		$var['cmd']				= '_xclick';

		if ( !empty( $this->settings['invoice_tax'] ) && isset( $request->items->tax ) ) {
			$tax = 0;

			foreach ( $request->items->tax as $itax ) {
				$tax += $itax['cost'];
			}

			$var['tax']			= AECToolbox::correctAmount( $tax );

			$var['amount']		= $request->items->total->cost['amount'];
		} elseif ( !empty( $this->settings['tax'] ) && $this->settings['tax'] > 0 ) {
			$amount				= $request->int_var['amount'] / ( 100 + $this->settings['tax'] ) * 100;
			$var['tax']			= AECToolbox::correctAmount( ( $request->int_var['amount'] - $amount ), 2 );
			$var['amount']		= AECToolbox::correctAmount( $amount, 2 );
		} else {
			$var['amount']		= $request->int_var['amount'];
		}

		$var['business']		= $this->settings['business'];
		$var['invoice']			= $request->invoice->invoice_number;
		$var['cancel_return']	= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=cancel' );

		if ( strpos( $this->settings['altipnurl'], 'http://' ) === 0 ) {
			$var['notify_url']	= $this->settings['altipnurl'] . 'index.php?option=com_acctexp&amp;task=paypalnotification';
		} else {
			$var['notify_url']	= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=paypalnotification' );
		}

		$var['item_number']		= AECToolbox::rewriteEngineRQ( $this->settings['item_number'], $request );
		$var['item_name']		= AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );

		$var['no_shipping']		= $this->settings['no_shipping'];
		$var['no_note']			= '1';
		$var['rm']				= '2';

		$var['return']			= $request->int_var['return_url'];
		$var['currency_code']	= $this->settings['currency'];
		$var['lc']				= $this->settings['lc'];

		// Customizations
		$customizations = array( 'cbt', 'cn', 'cpp_header_image', 'cpp_headerback_color', 'cpp_headerborder_color', 'cpp_payflow_color', 'image_url', 'page_style' );

		foreach ( $customizations as $cust ) {
			if ( !empty( $this->settings[$cust] ) ) {
					$var[$cust] = $this->settings[$cust];
			}
		}

		if ( isset( $this->settings['cs'] ) ) {
			if ( $this->settings['cs'] != 0 ) {
				$var['cs'] = $this->settings['cs'];
			}
		}

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['invoice'] = $post['invoice'];
		$response['amount_currency'] = $post['mc_currency'];

		if ( !empty( $post['txn_type'] ) ) {
			switch ( $post['txn_type'] ) {
				case "web_accept":
				case "subscr_payment":
					$response['amount_paid'] = $post['mc_gross'];
					break;
				case "subscr_signup":
				case "subscr_cancel":
				case "subscr_modify":
					// Docs suggest mc_amount1 is set with signup, cancel or modify
					// Testing shows otherwise
					$response['amount_paid'] = isset($post['mc_amount1']) ? $post['mc_amount1'] : null;
				break;
				case "subscr_failed":
				case "subscr_eot":
					// May create a problem somewhere donw the line, but NULL
					// is a more representative value
				break;
				default:
				// Either a fraud attempt, or PayPal has changed its API
				$response['amount_paid'] = null;
			}
		}

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$path = '/cgi-bin/webscr';
		if ($this->settings['testmode']) {
			$ppurl = 'https://www.sandbox.paypal.com' . $path;
		} else {
			$ppurl = 'https://www.paypal.com' . $path;
		}

		$req = 'cmd=_notify-validate';

		foreach ( $post as $key => $value ) {
			$value = str_replace('\r\n', "QQLINEBREAKQQ", $value);

			$value = urlencode( stripslashes($value) );

			$value = str_replace( "QQLINEBREAKQQ", "\r\n", $value ); // linebreak fix

			$req .= "&$key=".$value;
		}

		$res = $this->transmitRequest( $ppurl, $path, $req );

		$response['fullresponse']['paypal_verification'] = $res;

		$receiver_email	= null;
		$txn_type		= null;
		$payment_type	= null;
		$payment_status	= null;
		$reason_code	= null;
		$pending_reason	= null;

		$getposts = array( 'txn_type', 'receiver_email', 'payment_status', 'payment_type', 'reason_code', 'pending_reason' );

		foreach ( $getposts as $n ) {
			if ( isset( $post[$n] ) ) {
				$$n = $post[$n];
			} else {
				$$n = null;
			}
		}

		$response['valid'] = 0;

		if ( strcmp( $receiver_email, $this->settings['business'] ) != 0 && $this->settings['checkbusiness'] ) {
			$response['pending_reason'] = 'checkbusiness error';
		} elseif ( ( strcmp( $res, 'VERIFIED' ) == 0 ) || ( empty( $res ) && !empty( $this->settings['brokenipnmode'] ) ) ) {
			if ( empty( $res ) && !empty( $this->settings['brokenipnmode'] ) ) {
				$response['fullresponse']['paypal_verification'] = "MANUAL_OVERRIDE";
			}

			$recurring = ( $txn_type == 'subscr_payment' ) || ( $txn_type == 'recurring_payment' );

			// Process payment: Paypal Subscription & Buy Now
			if ( ( $txn_type == 'web_accept' ) || $recurring ) {

				if ( ( strcmp( $payment_type, 'instant' ) == 0 ) && ( strcmp( $payment_status, 'Pending' ) == 0 ) ) {
					$response['pending_reason'] = $post['pending_reason'];
				} elseif ( strcmp( $payment_type, 'instant' ) == 0 && strcmp( $payment_status, 'Completed' ) == 0 ) {
					$response['valid']			= 1;
				} elseif ( strcmp( $payment_type, 'echeck' ) == 0 && strcmp( $payment_status, 'Pending' ) == 0 ) {
					if ( $this->settings['acceptpendingecheck'] ) {
						if ( is_object( $invoice ) ) {
							$invoice->addParams( array( 'acceptedpendingecheck' => 1 ) );
							$invoice->storeload();
						}

						$response['valid']			= 1;
					} else {
						$response['pending']		= 1;
						$response['pending_reason'] = 'echeck';
					}
				} elseif ( strcmp( $payment_type, 'echeck' ) == 0 && strcmp( $payment_status, 'Completed' ) == 0 ) {
					$response['valid']		= 1;

					if ( is_object( $invoice ) ) {
						if ( isset( $invoice->params['acceptedpendingecheck'] ) ) {
							$response['valid']		= 0;
							$response['duplicate']	= 1;
						}
					}
				}
			} elseif ( strcmp( $txn_type, 'subscr_signup' ) == 0 ) {
				$response['pending']			= 1;
				$response['pending_reason']	 = 'signup';
			} elseif ( ( strcmp( $txn_type, 'paymentreview' ) == 0 ) || ( strcmp( $pending_reason, 'paymentreview' ) == 0 ) ) {
				$response['pending']			= 1;
				$response['pending_reason']	 = 'paymentreview';
			} elseif ( strcmp( $pending_reason, 'intl' ) == 0 ) {
				$response['pending']			= 1;
				$response['pending_reason']	 	= 'no auto-accept';
				$response['explanation']		= 'Configure your PayPal Account to automatically accept incoming payments.';
			} elseif ( strcmp( $txn_type, 'subscr_eot' ) == 0 ) {
				$response['eot']				= 1;
			} elseif ( strcmp( $txn_type, 'subscr_failed' ) == 0 ) {
				$response['null']				= 1;
				$response['explanation']		= 'Subscription Payment Failed';
			} elseif ( strcmp( $txn_type, 'subscr_cancel' ) == 0 ) {
				$response['cancel']				= 1;
			} elseif ( strcmp( $reason_code, 'refund' ) == 0 ) {
				$response['delete']				= 1;
			} elseif ( strcmp( $payment_status, 'Reversed' ) == 0 ) {
				$response['chargeback']			= 1;
			} elseif ( strcmp( $payment_status, 'Canceled_Reversal' ) == 0 ) {
				$response['chargeback_settle']	= 1;
			}
		} else {
			$response['pending_reason']			= 'error: ' . $res;
		}

		return $response;
	}

}
?>

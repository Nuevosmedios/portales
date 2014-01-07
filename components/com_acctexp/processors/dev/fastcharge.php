<?php
/**
 * @version $Id: fastcharge.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Fastcharge
 * @copyright 2007-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_fastcharge extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']				= 'fastcharge';
		$info['longname']			= _CFG_FASTCHARGE_LONGNAME;
		$info['statement']			= _CFG_FASTCHARGE_STATEMENT;
		$info['description']		= _CFG_FASTCHARGE_DESCRIPTION;
		$info['currencies']			= 'EUR,USD,GBP,AUD,CAD,JPY,NZD,CHF,HKD,SGD,SEK,DKK,PLN,NOK,HUF,CZK,MXN,ILS,BRL,MYR,PHP,TWD,THB,ZAR';
		$info['languages']			= AECToolbox::getISO639_1_codes();
		$info['cc_list']			= 'visa,mastercard,discover,americanexpress,echeck,giropay';
		$info['recurring']			= 2;
		$info['actions']			= array( 'cancel' => array( 'confirm' ) );
		$info['secure']				= 1;
		$info['recurring_buttons']	= 2;

		return $info;
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
		$settings['testmode']			= 0;
		$settings['currency']			= 'USD';

		$settings['acctid']				= '';
		$settings['subid']				= '';
		$settings['merchantpin']		= '';
		$settings['country']			= 'US';

		$settings['allow_echecks']		= 0;

		$settings['item_name']			= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']				= array( 'toggle' );
		$settings['currency']				= array( 'list_currency' );

		$settings['acctid']					= array( 'inputC' );
		$settings['subid']					= array( 'inputC' );
		$settings['merchantpin']			= array( 'inputC' );
		$settings['country'] 				= array( 'list' );

		$settings['allow_echecks']			= array( 'toggle' );

		$settings['cancel_note']			= array( 'inputE' );
		$settings['item_name']				= array( 'inputE' );

		$country_sel = array();
		$country_sel[] = JHTML::_('select.option', 'US', 'US' );

		$settings['lists']['country'] = JHTML::_( 'select.genericlist', $country_sel, 'fastcharge_country', 'size="2"', 'value', 'text', $this->settings['country'] );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function registerProfileTabs()
	{
		$tab			= array();
		$tab['details']	= JText::_('AEC_USERFORM_BILLING_DETAILS_NAME');

		return $tab;
	}

	function checkoutform( $request, $vcontent=null, $updated=null )
	{
		$var = array();

		$values = array( 'firstname', 'lastname', 'address', 'address2', 'city', 'state_usca', 'zip', 'country_list' );
		$var = $this->getUserform( $var, $values, $request->metaUser, $vcontent );

		if ( $this->settings['allow_echecks'] ) {
			$var['params'][] = array( 'tabberstart', '', '', '' );
			$var['params'][] = array( 'tabregisterstart', '', '', '' );
			$var['params'][] = array( 'tabregister', 'ccdetails', 'Credit Card', true );
			$var['params'][] = array( 'tabregister', 'echeckdetails', 'eCheck', false );
			$var['params'][] = array( 'tabregisterend', '', '', '' );

			$var['params'][] = array( 'tabstart', 'ccdetails', true, '' );
			$var = $this->getCCform( $var, array( 'card_number', 'card_exp_month', 'card_exp_year', 'card_cvv2' ), $vcontent );
			$var['params'][] = array( 'tabend', '', '', '' );

			$var['params'][] = array( 'tabstart', 'echeckdetails', true, '' );
			$var = $this->getECHECKform( $var );
			$var['params'][] = array( 'tabend', '', '', '' );

			$var['params'][] = array( 'tabberend', '', '', '' );
		} else {
			$var = $this->getCCform( $var, array( 'card_number', 'card_exp_month', 'card_exp_year', 'card_cvv2' ), $vcontent );
		}

		return $var;
	}

	function createRequestXML( $request )
	{
		$app = JFactory::getApplication();

		$var = $this->getFCVars( $request );

		return $this->arrayToNVP( $var );
	}

	function getFCVars( $request )
	{
		$app = JFactory::getApplication();

		if( !empty( $request->int_var['params']['account_no'] ) ) {
			$var['action']		= 'ns_quicksale_check';
		} else {
			$var['action']		= 'ns_quicksale_cc';
		}

		$var['acctid']			= $this->settings['acctid'];

		$var['amount']			= $this->settings['acctid'];

		if( !empty( $request->int_var['params']['account_no'] ) ) {
			$var['ckaba']		= $request->int_var['params']['routing_no'];
			$var['ckacct']		= $request->int_var['params']['account_no'];

			$var['achtransactiontype']		= "WEB";
		} else {
			$var['ccname']		= trim( $request->int_var['params']['billFirstName'] ) . " " . trim( $request->int_var['params']['billLastName'] );
			$var['ccnum']		= $request->int_var['params']['cardNumber'];
			$var['expmon']		= str_pad( $request->int_var['params']['expirationMonth'], 2, '0', STR_PAD_LEFT );
			$var['expyear']		= $request->int_var['params']['expirationYear'];
			$var['cvv2']		= $request->int_var['params']['cardVV2'];
		}

		$var['subid']			= $this->settings['subid'];
		$var['pwd']				= $this->settings['api_password'];
		$var['signature']		= $this->settings['signature'];

		$var['accepturl']		= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=thanks', false, true );
		$var['declineurl']		= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=cancel', false, true );

		$var['ci_billaddr1']		= $request->int_var['params']['billAddress'];

		if ( !empty( $request->int_var['params']['billAddress2'] ) ) {
			$var['ci_billaddr2']	= $request->int_var['params']['billAddress2'];
		}

		$var['ci_billcity']			= $request->int_var['params']['billCity'];
		$var['ci_billstate']		= $request->int_var['params']['billState'];
		$var['ci_billzip']			= $request->int_var['params']['billZip'];
		$var['ci_billcountry']		= $request->int_var['params']['billCountry'];

		$var['ci_email']			= trim( $request->metaUser->cmsUser->email );

		$var = $this->getPaymentVars( $var, $request );

		$var['NotifyUrl']			= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=fastchargenotification', false, true );
		$var['merchantordernumber']	= $request->invoice->invoice_number;

		return $var;
	}

	function getPaymentVars( $var, $request )
	{
		$app = JFactory::getApplication();

		if ( is_array( $request->int_var['amount'] ) ) {
			// $var['InitAmt'] = 'Initial Amount'; // Not Supported Yet
			// $var['FailedInitAmtAction'] = 'ContinueOnFailure'; // Not Supported Yet (optional)

			if ( isset( $request->int_var['amount']['amount1'] ) ) {
				/* For now, this is not working, we have to wait until PayPal fixes this
				$trial = $this->convertPeriodUnit( $request->int_var['amount']['period1'], $request->int_var['amount']['unit1'] );

				$var['TrialBillingPeriod']		= $trial['unit'];
				$var['TrialBillingFrequency']	= $trial['period'];
				$var['TrialAmt']				= $request->int_var['amount']['amount1'];
				$var['TrialTotalBillingCycles'] = 1; // Not Fully Supported Yet
				*/

				switch ( $request->int_var['amount']['unit1'] ) {
					case 'D': $offset = $request->int_var['amount']['period1'] * 3600 * 24; break;
					case 'W': $offset = $request->int_var['amount']['period1'] * 3600 * 24 * 7; break;
					case 'M': $offset = $request->int_var['amount']['period1'] * 3600 * 24 * 31; break;
					case 'Y': $offset = $request->int_var['amount']['period1'] * 3600 * 24 * 356; break;
				}

				$timestamp = ( (int) gmdate('U') ) + $offset;
			} else {
				$timestamp = (int) gmdate('U');
			}

			$var['ProfileStartDate']    = date( 'Y-m-d', $timestamp ) . 'T' . date( 'H:i:s', $timestamp ) . 'Z';

			$full = $this->convertPeriodUnit( $request->int_var['amount']['period3'], $request->int_var['amount']['unit3'] );

			$var['BillingPeriod']		= $full['unit'];
			$var['BillingFrequency']	= $full['period'];
			$var['amt']					= $request->int_var['amount']['amount3'];
			$var['ProfileReference']	= $request->invoice->invoice_number;
		} else {
			$var['amt']					= $request->int_var['amount'];
		}

		$var['currencyCode']			= $this->settings['currency'];

		return $var;
	}

	function transmitToPayPal( $xml, $request )
	{
		$path = "/nvp";

		$url = $this->getPayPalURL( $path );

		$curlextra = array();
		$curlextra[CURLOPT_VERBOSE] = 1;
		$curlextra[CURLOPT_HEADER]	= true;

		return $this->transmitRequest( $url, $path, $xml, 443, $curlextra );
	}

	function getPayPalURL( $path )
	{
		$url = "https://api" . ( $this->settings['use_certificate'] ? "" : "-3t" );

		$url .= ( $this->settings['testmode'] ? ".sandbox" : "" );

		$url .= ".paypal.com" . $path;

		return $url;
	}

	function transmitRequestXML( $xml, $request )
	{
		$response = trim( $this->transmitToPayPal( $xml, $request ) );

		$return = array();
		$return['valid'] = false;
		$return['raw'] = $response;

		// converting NVPResponse to an Associative Array
		$nvpResArray = $this->NVPtoArray( $response );

		if ( !empty( $response ) ) {
			if ( isset( $nvpResArray['PROFILEID'] ) ) {
				$return['invoiceparams'] = array( "fastcharge_customerProfileId" => $nvpResArray['PROFILEID'] );
			}

			if ( strcmp( strtoupper( $nvpResArray['ACK'] ), 'SUCCESS' ) === 0 ) {
				if ( is_array( $request->int_var['amount'] ) ) {
					if ( !isset( $nvpResArray['STATUS'] ) ) {
						$return['valid'] = 1;
					} elseif ( strtoupper( $response['STATUS'] ) == 'ACTIVEPROFILE' ) {
						$return['valid'] = 1;
					} else {
						$response['pending_reason'] = 'pending';
					}
				} else {
					$return['valid'] = 1;
				}

				if ( isset( $nvpResArray['CORRELATIONID'] ) ) {
					$return['correlationid'] = $nvpResArray['CORRELATIONID'];
				}

				if ( isset( $nvpResArray['TOKEN'] ) ) {
					$return['token'] = $nvpResArray['TOKEN'];
				}
			} else {
				$return['error'] = '';

				$count = 0;
				while ( isset( $nvpResArray["L_SHORTMESSAGE".$count] ) ) {
						$return['error'] .= 'Error ' . $nvpResArray["LJText::_('ERRORCODE')".$count] . ' = ' . $nvpResArray["L_SHORTMESSAGE".$count] . ' (' . $nvpResArray["L_LONGMESSAGE".$count] . ')' . "\n";
						$count++;
				}
			}
		} else {
			$return['error'] = 'No Response from the PayPal Server';
		}

		return $return;
	}

	function convertPeriodUnit( $period, $unit )
	{
		$return = array();
		switch ( $unit ) {
			case 'D':
				$return['unit'] = 'Day';
				$return['period'] = $period;
				break;
			case 'W':
				$return['unit'] = 'Week';
				$return['period'] = $period;
				break;
			case 'M':
				$return['unit'] = 'Month';
				$return['period'] = $period;
				break;
			case 'Y':
				$return['unit'] = 'Year';
				$return['period'] = $period;
				break;
		}

		return $return;
	}

	function customaction_cancel( $request )
	{
		$var['Method']				= 'ManageRecurringPaymentsProfileStatus';
		$var['action']				= 'Cancel';
		$var['note']				= $this->settings['cancel_note'];

		$profileid = $request->invoice->params['fastcharge_customerProfileId'];

		$response = $this->ProfileRequest( $request, $profileid, $var );

		if ( !empty( $response ) ) {
			$return['invoice'] = $request->invoice->invoice_number;

			if ( isset( $response['PROFILEID'] ) ) {
				if ( $response['PROFILEID'] == $profileid ) {
					$return['valid'] = 0;
					$return['cancel'] = true;
				} else {
					$return['valid'] = 0;
					$return['error'] = 'Could not transmit Cancel Message - Wrong Profile ID returned';
				}
			} else {
				$return['valid'] = 0;
				$return['error'] = 'Could not transmit Cancel Message - General Failure';
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

	function ProfileRequest( $request, $profileid, $var )
	{
		$var['Version']				= '50.0';
		$var['user']				= $this->settings['api_user'];
		$var['pwd']					= $this->settings['api_password'];
		$var['signature']			= $this->settings['signature'];

		$var['profileid']			= $profileid;

		$content = array();
		foreach ( $var as $name => $value ) {
			$content[] .= strtoupper( $name ) . '=' . urlencode( $value );
		}

		$xml = implode( '&', $content );

		$response = $this->transmitToPayPal( $xml, $request );

		return $this->deformatNVP( $response );
	}

	function parseNotification( $post )
	{
		$db = &JFactory::getDBO();

		$mc_gross			= $post['mc_gross'];
		if ( $mc_gross == '' ) {
			$mc_gross 		= $post['mc_amount1'];
		}
		$mc_currency		= $post['mc_currency'];

		$response = array();

		if ( !empty( $post['invoice'] ) ) {
			$response['invoice'] = $post['invoice'];
		} elseif ( !empty( $post['rp_invoice_id'] ) ) {
			$response['invoice'] = $post['rp_invoice_id'];
		}

		$response['amount_paid'] = $mc_gross;
		$response['amount_currency'] = $mc_currency;

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$path = '/cgi-bin/webscr';
		if ($this->settings['testmode']) {
			$ppurl = 'www.sandbox.paypal.com' . $path;
		} else {
			$ppurl = 'www.paypal.com' . $path;
		}

		$req = 'cmd=_notify-validate';

		foreach ( $post as $key => $value ) {
			$value = urlencode( stripslashes( $value ) );
			$req .= "&$key=$value";
		}

		$res = $this->transmitRequest( $ppurl, $path, $req, $port=443, $curlextra=null );

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
			} elseif ( strcmp( $txn_type, 'subscr_eot' ) == 0 ) {
				$response['eot']				= 1;
			} elseif ( strcmp( $txn_type, 'subscr_failed' ) == 0 ) {
				$response['null']				= 1;
				$response['explanation']		= 'Subscription Payment Failed';
			} elseif ( strcmp( $txn_type, 'subscr_cancel' ) == 0 ) {
				$response['cancel']				= 1;
			} elseif ( strcmp( $reason_code, 'refund' ) == 0 ) {
				$response['delete']				= 1;
			}
		} else {
			$response['pending_reason']			= 'error: ' . $res;
		}

		return $response;
	}

}

?>
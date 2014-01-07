<?php
/**
 * @version $Id: paypal_wpp.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - PayPal Website Payments Pro
 * @copyright 2007-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_paypal_wpp extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']				= 'paypal_wpp';
		$info['longname']			= JText::_('CFG_PAYPAL_WPP_LONGNAME');
		$info['statement']			= JText::_('CFG_PAYPAL_WPP_STATEMENT');
		$info['description']		= JText::_('CFG_PAYPAL_WPP_DESCRIPTION');
		$info['currencies']			= 'EUR,USD,GBP,AUD,CAD,JPY,NZD,CHF,HKD,SGD,SEK,DKK,PLN,NOK,HUF,CZK,MXN,ILS,BRL,MYR,PHP,TWD,THB,ZAR';
		$info['languages']			= AECToolbox::getISO639_1_codes();
		$info['cc_list']			= 'visa,mastercard,discover,americanexpress,echeck,giropay';
		$info['recurring']			= 2;
		$info['actions']			= array( 'cancel' => array( 'confirm' ) );
		$info['secure']				= 1;
		$info['recurring_buttons']	= 2;

		return $info;
	}

	function getLogoFilename()
	{
		return 'paypal.png';
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
		$settings['allow_express_checkout'] = 1;
		$settings['brokenipnmode']		= 0;
		$settings['currency']			= 'USD';
		$settings['totalOccurrences']	= 0;

		$settings['api_user']			= '';
		$settings['api_password']		= '';
		$settings['use_certificate']	= '';
		$settings['certificate_path']	= '';
		$settings['signature']			= '';
		$settings['country']			= 'US';

		$settings['item_name']			= '[[invoice_amount]] - ' . sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']				= array( 'toggle' );
		$settings['brokenipnmode']			= array( 'toggle' );
		$settings['allow_express_checkout']	= array( 'toggle' );
		$settings['currency']				= array( 'list_currency' );
		$settings['totalOccurrences']		= array( 'inputA' );

		$settings['api_user']				= array( 'inputC' );
		$settings['api_password']			= array( 'inputC' );
		$settings['use_certificate']		= array( 'toggle' );
		$settings['certificate_path']		= array( 'inputC' );
		$settings['signature'] 				= array( 'inputC' );
		$settings['country'] 				= array( 'list' );

		$settings['cancel_note']			= array( 'inputE' );
		$settings['item_name']				= array( 'inputE' );

		$country_sel = array();
		$country_sel[] = JHTML::_('select.option', 'US', 'US' );
		//$country_sel[] = JHTML::_('select.option', 'UK', 'UK' );

		$settings['lists']['country'] = JHTML::_( 'select.genericlist', $country_sel, 'paypal_wpp_country', 'size="2"', 'value', 'text', $this->settings['country'] );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function registerProfileTabs()
	{
		$tab			= array();
		$tab['details']	= JText::_('AEC_USERFORM_BILLING_DETAILS_NAME');

		return $tab;
	}

	function customtab_details( $request )
	{
		$profileid = $request->invoice->params['paypal_wpp_customerProfileId'];

		$billfirstname	= aecGetParam( 'billFirstName', null );
		$billcardnumber	= aecGetParam( 'cardNumber', null );

		$updated = null;

		if ( !empty( $billfirstname ) && !empty( $billcardnumber ) && ( strpos( $billcardnumber, 'X' ) === false ) ) {
			$var['Method']				= 'GetRecurringPaymentsProfileDetails';
			$var['Profileid']			= $profileid;

			$vars = $this->ProfileRequest( $request, $profileid, $var );

			$var['Method']					= 'UpdateRecurringPaymentsProfile';
			$var['Profileid']				= $profileid;

			$var['CreditCardType']			= $this->getCCType( aecGetParam( 'cardType' ) );
			$var['Acct']					= aecGetParam( 'cardNumber' );
			$var['expDate']					= str_pad( aecGetParam( 'expirationMonth' ), 2, '0', STR_PAD_LEFT ) . aecGetParam( 'expirationYear' );
			$var['cvv2']					= aecGetParam( 'cardVV2' );

			$var['amt']						= $vars['AMT'];
			$var['currencycode']			= $vars['CURRENCYCODE'];

			$udata = array( 'firstname' => 'billFirstName', 'lastname' => 'billLastName', 'street' => 'billAddress', 'street2' => 'billAddress2',
							'city' => 'billCity', 'state' => 'billState', 'zip' => 'billZip', 'country' => 'billCountry'
							);

			foreach ( $udata as $authvar => $aecvar ) {
				$value = trim( aecGetParam( $aecvar ) );

				if ( !empty( $value ) ) {
					$var[$authvar] = $value;
				}
			}

			$result = $this->ProfileRequest( $request, $profileid, $var );

			$updated = true;
		}

		if ( $profileid ) {
			$var['Method']				= 'GetRecurringPaymentsProfileDetails';
			$var['Profileid']			= $profileid;

			$vars = $this->ProfileRequest( $request, $profileid, $var );

			$vcontent = array();
			$vcontent['card_type']		= strtolower( $vars['CREDITCARDTYPE'] );
			$vcontent['card_number']	= 'XXXX' . $vars['ACCT'];
			$vcontent['firstname']		= $vars['FIRSTNAME'];
			$vcontent['lastname']		= $vars['LASTNAME'];

			if ( isset( $vars['STREET1'] ) ) {
				$vcontent['address']		= $vars['STREET1'];
				$vcontent['address2']		= $vars['STREET2'];
			} else {
				$vcontent['address']		= $vars['STREET'];
			}

			$vcontent['city']			= $vars['CITY'];
			$vcontent['state_usca']		= $vars['STATE'];
			$vcontent['zip']			= $vars['ZIP'];
			$vcontent['country_list']	= $vars['COUNTRY'];
		} else {
			$vcontent = array();
		}

		$var = $this->checkoutform( $request, $vcontent, $updated );

		$return = '<form action="' . AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=paypal_wpp_details', true ) . '" method="post">' . "\n";
		$return .= $this->getParamsHTML( $var ) . '<br /><br />';
		$return .= '<input type="hidden" name="userid" value="' . $request->metaUser->userid . '" />' . "\n";
		$return .= '<input type="hidden" name="task" value="subscriptiondetails" />' . "\n";
		$return .= '<input type="hidden" name="sub" value="paypal_wpp_details" />' . "\n";
		$return .= '<input type="submit" class="button aec-btn btn btn-primary" value="' . JText::_('BUTTON_APPLY') . '" /><br /><br />' . "\n";
		$return .= '</form>' . "\n";

		return $return;
	}

	function checkoutAction( $request, $InvoiceFactory=null )
	{
		$return = "";

		if ( !empty( $_REQUEST['PayerID'] ) && !empty( $_REQUEST['token'] ) && $this->settings['allow_express_checkout'] ) {
			$return .= '<div class="aec-checkout-params">';
			$return .= '<p style="float:left;text-align:left;"><strong>' . JText::_('CFG_PAYPAL_WPP_CHECKOUT_NOTE_RETURN') . '</strong></p>';
			$return .= '<form action="' . AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=checkout', $this->info['secure'] ) . '" method="post">' . "\n";
			$return .= $this->getStdFormVars( $request );
			$return .= '<input type="hidden" name="express" value="1" />' . "\n";
			$return .= '<input type="hidden" name="token" value="' . $_REQUEST['token'] . '" />' . "\n";
			$return .= '<input type="hidden" name="PayerID" value="' . $_REQUEST['PayerID'] . '" />' . "\n";
			$return .= '<input type="submit" class="button aec-btn btn btn-primary" id="aec-checkout-btn" value="' . JText::_('BUTTON_CHECKOUT') . '" /><br /><br />' . "\n";
			$return .= '</form>' . "\n";
			$return .= '</div>';
		} else {
			if ( $this->settings['allow_express_checkout'] ) {
				$return .= '<div class="aec-checkout-params">';
				$return .= '<p style="float:left;text-align:left;"><strong>' . JText::_('CFG_PAYPAL_WPP_CHECKOUT_NOTE_HEADLINE') . '</strong></p><p style="float:left;text-align:left;">' . JText::_('CFG_PAYPAL_WPP_CHECKOUT_NOTE_NOTE') . '</p>';
				$return .= '<form action="' . AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=checkout', $this->info['secure'] ) . '" method="post">' . "\n";
				$return .= $this->getStdFormVars( $request );
				$return .= '<input type="hidden" name="express" value="1" />' . "\n";
				$return .= '<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_xpressCheckout.gif" class="button" id="aec-checkout-btn" value="' . JText::_('BUTTON_CHECKOUT') . '" /><br /><br />' . "\n";
				$return .= '</form>' . "\n";
				$return .= '</div>';
			}

			$return .= parent::checkoutAction( $request, $InvoiceFactory );
		}

		return $return;
	}

	function checkoutform( $request, $vcontent=null, $updated=null )
	{
		$var = array();

		if ( !empty( $vcontent ) ) {
			if ( !empty( $updated ) ) {
				$msg = JText::_('AEC_CCFORM_UPDATE2_DESC');
			} else {
				$msg = JText::_('AEC_CCFORM_UPDATE_DESC');
			}

			$var['params']['billUpdateInfo'] = array( 'p', JText::_('AEC_CCFORM_UPDATE_NAME'), $msg, '' );
		}

		$values = array( 'card_type', 'card_number', 'card_exp_month', 'card_exp_year', 'card_cvv2' );
		$var = $this->getCCform( $var, $values, $vcontent );

		$values = array( 'firstname', 'lastname', 'address', 'address2', 'city', 'state_usca', 'zip', 'country_list' );
		$var = $this->getUserform( $var, $values, $request->metaUser, $vcontent );

		return $var;
	}

	function checkoutProcess( $request, $InvoiceFactory )
	{
		$this->sanitizeRequest( $request );

		if ( !empty( $request->int_var['params']['express'] ) && $this->settings['allow_express_checkout'] ) {
			if ( empty( $request->int_var['params']['token'] ) ) {
				$var = $this->getPayPalVars( $request, false );

				$var['Method']			= 'SetExpressCheckout';
				$var['ReturnUrl']		= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=repeatPayment&invoice='.$request->invoice->invoice_number, $this->info['secure'], true );
				$var['CancelUrl']		= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=cancel', $this->info['secure'], true );

				if ( is_array( $request->int_var['amount'] ) ) {
					unset( $var['desc'] );

					$var['L_BillingType0']		= 'RecurringPayments';

					$var['L_BillingAgreementDescription0']		= $this->getPlaintextDetails( $request );
				}

				if ( isset( $var['amt'] ) ) {
					$var['paymentrequest_0_paymentaction']	= $var['paymentAction'];unset( $var['paymentAction'] );
					$var['paymentrequest_0_amt']			= $var['amt'];unset( $var['amt'] );
					$var['paymentrequest_0_desc']			= $var['desc'];unset( $var['desc'] );
					$var['paymentrequest_0_currencycode']	= $var['currencyCode'];unset( $var['currencyCode'] );
				}

				$xml = $this->arrayToNVP( $var, true );

				$response = $this->transmitRequestXML( $xml, $request );

				if ( isset( $response['correlationid'] ) && isset( $response['token'] ) ) {
					$var = array();
					$var['cmd']			= '_express-checkout';
					$var['token']		= $response['token'];

					$get = $this->arrayToNVP( $var );

					if ( $this->settings['testmode'] ) {
						return aecRedirect( 'https://www.sandbox.paypal.com/webscr?' . $get );
					} else {
						return aecRedirect( 'https://www.paypal.com/webscr?' . $get );
					}
				} elseif ( !empty( $response['error'] ) ) {
					$response['error'] .= " - Could not retrieve token";
				} else {
					$response['error'] = "Could not retrieve token";
				}
			} else {
				// The user has already returned from Paypal - finish the deal
				$var = $this->getPayPalVars( $request, false, false );

				$var['token']			= $request->int_var['params']['token'];
				$var['PayerID']			= $request->int_var['params']['PayerID'];

				$var['Method']			= 'GetExpressCheckoutDetails';

				$xml = $this->arrayToNVP( $var, true );

				$response = $this->transmitRequestXML( $xml, $request );

				$var = $this->getPayPalVars( $request, false, true, true );

				if ( isset( $var['amt'] ) ) {
					$var['paymentrequest_0_paymentaction']	= $var['paymentAction'];unset( $var['paymentAction'] );
					$var['paymentrequest_0_amt']			= $var['amt'];
					$var['paymentrequest_0_itemamt']		= $var['amt'];unset( $var['amt'] );
					$var['paymentrequest_0_desc']			= $var['desc'];unset( $var['desc'] );
					$var['paymentrequest_0_currencycode']	= $var['currencyCode'];unset( $var['currencyCode'] );
					$var['paymentrequest_0_notifyurl']	= $var['NotifyUrl'];unset( $var['NotifyUrl'] );
				}

				$var['token']			= $request->int_var['params']['token'];
				$var['PayerID']			= $request->int_var['params']['PayerID'];

				$var['paymentrequest_0_InvNum']			= $request->invoice->invoice_number;

				$var['Method']			= 'DoExpressCheckoutPayment';

				$xml = $this->arrayToNVP( $var, true );

				$response = $this->transmitRequestXML( $xml, $request );

				if ( is_array( $request->int_var['amount'] ) ) {
					$var = $this->getPayPalVars( $request, false, true, true );

					$var['token']			= $request->int_var['params']['token'];
					$var['PayerID']			= $request->int_var['params']['PayerID'];

					unset( $var['paymentAction'] );
					unset( $var['IPaddress'] );

					$var['desc']		= $this->getPlaintextDetails( $request );

					$xml = $this->arrayToNVP( $var, true );

					$response = $this->transmitRequestXML( $xml, $request );
				}
			}
		} else {
			$db = &JFactory::getDBO();

			// Create the xml string
			$xml = $this->createRequestXML( $request );

			// Transmit xml to server
			$response = $this->transmitRequestXML( $xml, $request );

			if ( empty( $response['invoice'] ) ) {
				$response['invoice'] = $request->invoice->invoice_number;
			}

			if ( $request->invoice->invoice_number != $response['invoice'] ) {
				$request->invoice = new Invoice();
				$request->invoice->loadInvoiceNumber( $response['invoice'] );
			}

			if ( isset( $response['correlationid'] ) ) {
				unset( $response['correlationid'] );
			}
		}

		return $this->checkoutResponse( $request, $response, $InvoiceFactory );
	}

	function createRequestXML( $request )
	{
		$var = $this->getPayPalVars( $request );

		return $this->arrayToNVP( $var, true );
	}

	function getCCType( $type )
	{
		switch ( strtolower( $type ) ) {
			default: case 'mastercard': return 'MasterCard';
			case 'visa': return 'Visa';
			case 'discover': return 'Discover';
			case 'amex': return 'Amex';
			case 'maestro': return 'Maestro';
		}
	}

	function getPayPalVars( $request, $regular=true, $payment=true, $express=false )
	{
		if ( is_array( $request->int_var['amount'] ) ) {
			$var['Method']			= 'CreateRecurringPaymentsProfile';
		} else {
			$var['Method']			= 'DoDirectPayment';
		}

		$var['Version']			= '72.0';

		$var['user']				= $this->settings['api_user'];
		$var['pwd']					= $this->settings['api_password'];
		$var['signature']			= $this->settings['signature'];

		$var['paymentAction']		= 'Sale';
		$var['IPaddress']			= $_SERVER['REMOTE_ADDR'];

		if ( $regular ) {
			$var['firstName']			= trim( $request->int_var['params']['billFirstName'] );
			$var['lastName']			= trim( $request->int_var['params']['billLastName'] );
			$var['creditCardType']		= $this->getCCType( $request->int_var['params']['cardType'] );
			$var['acct']				= $request->int_var['params']['cardNumber'];
			$var['expDate']				= str_pad( $request->int_var['params']['expirationMonth'], 2, '0', STR_PAD_LEFT ).$request->int_var['params']['expirationYear'];

			$var['CardVerificationValue'] = $request->int_var['params']['cardVV2'];
			$var['cvv2']				= $request->int_var['params']['cardVV2'];

			$var['street']				= $request->int_var['params']['billAddress'];

			if ( !empty( $request->int_var['params']['billAddress2'] ) ) {
				$var['street2']			= $request->int_var['params']['billAddress2'];
			}

			$var['city']				= $request->int_var['params']['billCity'];
			$var['state']				= $request->int_var['params']['billState'];
			$var['zip']					= $request->int_var['params']['billZip'];
			$var['countrycode']			= $request->int_var['params']['billCountry'];
		}

		if ( $payment ) {
			$var = $this->getPaymentVars( $var, $request, $express );

			$var['NotifyUrl']			= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=paypal_wppnotification', $this->info['secure'], true );
			$var['desc']				= AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );

			if ( is_array( $request->int_var['amount'] ) ) {
				$var['ProfileReference']	= $request->invoice->invoice_number;
			} else {
				$var['InvNum']				= $request->invoice->invoice_number;
			}
		}

		return $var;
	}

	function getPaymentVars( $var, $request, $express )
	{
		if ( is_array( $request->int_var['amount'] ) ) {
			if ( isset( $request->int_var['amount']['amount1'] ) ) {
				$trial = $this->convertPeriodUnit( $request->int_var['amount']['period1'], $request->int_var['amount']['unit1'] );

				$var['TrialBillingPeriod']		= $trial['unit'];
				$var['TrialBillingFrequency']	= $trial['period'];
				$var['TrialAmt']				= $request->int_var['amount']['amount1'];
				$var['TrialTotalBillingCycles'] = 1;

				$offset = AECToolbox::offsetTime( $request->int_var['amount']['period1'], $request->int_var['amount']['unit1'], gmdate('U') );
			} else {
				$offset = AECToolbox::offsetTime( $request->int_var['amount']['period3'], $request->int_var['amount']['unit3'], gmdate('U') );
			}

			if ( $express ) {
				$timestamp = $offset;
			} else {
				$timestamp = (int) gmdate('U');
			}

			$var['ProfileStartDate']    = date( 'Y-m-d', $timestamp ) . 'T' . date( 'H:i:s', $timestamp ) . 'Z';

			$full = $this->convertPeriodUnit( $request->int_var['amount']['period3'], $request->int_var['amount']['unit3'] );

			$var['BillingPeriod']		= $full['unit'];
			$var['BillingFrequency']	= $full['period'];
			$var['amt']					= $request->int_var['amount']['amount3'];

			if ( !empty( $this->settings['totalOccurrences'] ) ) {
				$var['TotalBillingCycles'] = $this->settings['totalOccurrences'];
			}
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
				$return['invoiceparams'] = array( "paypal_wpp_customerProfileId" => $nvpResArray['PROFILEID'] );
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
						$return['error'] .= 'Error ' . $nvpResArray["L_ERRORCODE".$count] . ' = ' . $nvpResArray["L_SHORTMESSAGE".$count] . ' (' . $nvpResArray["L_LONGMESSAGE".$count] . ')' . "\n";
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

	function getPlaintextDetails( $request )
	{
		$return = "";

		if ( !empty( $request->int_var['amount']['amount1'] ) ) {
			$trial = $this->convertPeriodUnit( $request->int_var['amount']['period1'], $request->int_var['amount']['unit1'] );

			$var['TrialBillingPeriod']		= $trial['unit'];
			$var['TrialBillingFrequency']	= $trial['period'];

			$return .= str_replace( "&nbsp;", '', AECToolbox::formatAmount( $request->int_var['amount']['amount1'], $this->settings['currency'] ) ) . ' for ' . $trial['period'] . ' ' . $trial['unit'] . ( ( $trial['period'] > 1 ) ? 's' : '' ) . ', then ';
		}

		$full = $this->convertPeriodUnit( $request->int_var['amount']['period3'], $request->int_var['amount']['unit3'] );

		$return .= str_replace( "&nbsp;", '', AECToolbox::formatAmount( $request->int_var['amount']['amount3'], $this->settings['currency'] ) ) . ( ( $full['period'] > 1 ) ? ' every ' . $full['period'] : ' per ' ) . $full['unit'] . ( ( $full['period'] > 1 ) ? 's' : '' );

		return $return;
	}

	function customaction_cancel( $request )
	{
		$var['Method']				= 'ManageRecurringPaymentsProfileStatus';
		$var['action']				= 'Cancel';
		$var['note']				= $this->settings['cancel_note'];

		$profileid = $request->invoice->params['paypal_wpp_customerProfileId'];

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

		return $this->NVPtoArray( $response );
	}

	function parseNotification( $post )
	{
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
		} elseif ( !empty( $post['invnum'] ) ) {
			$response['invoice'] = $post['invnum'];
		} elseif ( !empty( $post['paymentrequest_0_invnum'] ) ) {
			$response['invoice'] = $post['paymentrequest_0_invnum'];
		} elseif ( !empty( $post['profilereference'] ) ) {
			$response['invoice'] = $post['profilereference'];
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
			$value = str_replace('\r\n', "QQLINEBREAKQQ", $value);

			$value = urlencode( stripslashes($value) );

			$value = str_replace( "QQLINEBREAKQQ", "\r\n", $value ); // linebreak fix

			$req .= "&$key=".$value;
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

		if ( ( strtotime( $invoice->transaction_date ) + ( 60*60*24 ) ) > ( (int) gmdate('U') ) ) {
			// Double call -> duplicate
			$response['duplicate'] = true;

			return $response;
		}

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
			} elseif ( strcmp( $txn_type, 'recurring_payment_profile_created' ) == 0 ) {
				$response['valid']		= 0;
				$response['duplicate']	= 1;
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
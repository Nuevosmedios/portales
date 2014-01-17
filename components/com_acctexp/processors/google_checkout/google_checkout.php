<?php
/**
 * @version $Id: google_checkout.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Google Checkout
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_google_checkout extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'google_checkout';
		$info['longname']				= JText::_('CFG_GOOGLE_CHECKOUT_LONGNAME');
		$info['statement']				= JText::_('CFG_GOOGLE_CHECKOUT_STATEMENT');
		$info['description']			= JText::_('CFG_GOOGLE_CHECKOUT_DESCRIPTION');
		$info['currencies']				= "USD,GBP";
		$info['cc_list']				= "visa,mastercard,discover,americanexpress,echeck,jcb,dinersclub";
		$info['notify_trail_thanks']	= true;
		$info['recurring']				= 2;
		$info['recurring_buttons']		= 2;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']			= true;
		$settings['merchant_id']		= '--';
		$settings['merchant_key']		= '--';
		$settings['maximum_recur']		= '12';
		$settings['currency']			= 'USD';
		$settings['item_name']			= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['customparams']		= '';

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']			= array( 'toggle' );
		$settings['merchant_id'] 		= array( 'inputC' );
		$settings['merchant_key']		= array( 'inputC' );
		$settings['maximum_recur']		= array( 'inputB' );
		$settings['currency']			= array( 'list_currency' );
		$settings['item_name']			= array( 'inputE' );
		$settings['customparams']		= array( 'inputD' );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}
	
	function checkoutform( $request )
	{
		$var = array();
		return $var;
	}
	
	function checkoutAction( $request, $InvoiceFactory=null )
	{
		require_once( dirname(__FILE__) . '/lib/googlecart.php' );
		require_once( dirname(__FILE__) . '/lib/googleitem.php' );

		if ( $this->settings['testmode'] ) {
			$server_type = "sandbox";
		} else {
			$server_type = "Production";
		}

		$item_name			= $request->plan->name;
		$item_description 	= AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );
		$currency			= $this->settings['currency'];

		$cart	= new GoogleCart( $this->settings['merchant_id'], $this->settings['merchant_key'], $server_type, $currency );

		if ( is_array( $request->int_var['amount'] ) ) {
			require_once( dirname(__FILE__) . '/lib/liary/googlesubscription.php' );

			$item_1 = new GoogleItem( $item_name, $item_description, 1, $request->int_var['amount']['amount3'] );

			$item_s = new GoogleItem( $item_name, $item_description, 1, $request->int_var['amount']['amount3'] );

			if ( empty( $this->settings['maximum_recur'] ) ) {
				$maximum = 12;
			} else {
				$maximum = $this->settings['maximum_recur'];
			}

			$period = $this->convertPeriodUnit( $request->int_var['amount']['period3'], $request->int_var['amount']['unit3'] );

			$subscription = new GoogleSubscription( "google", $period, $request->int_var['amount']['amount3'], $maximum, $item_s );
			
			$item_1->SetSubscription( $subscription );
		} else {	
			$item_1 = new GoogleItem( $item_name, $item_description, 1, $request->int_var['amount'] );
		}

		$cart->AddItem( $item_1 );

		$cart->SetContinueShoppingUrl( $request->int_var['return_url'] );

	    $cart->SetMerchantPrivateData( new MerchantPrivateData( array("invoice" => $request->invoice->invoice_number) ) );

		// Display the Google Checkout button instead of the normal checkout button.
		$return = '<p style="float:right;text-align:right;">' . $cart->CheckoutButtonCode("SMALL") . '</p>';

		return $return;
	}

	function convertPeriodUnit( $period, $unit )
	{
		switch ( $unit ) {
			case 'D':
				if ( $period <= 4 ) {
					return 'DAILY';
				} elseif ( ( $period > 4 ) && ( $period <= 11 ) ) {
					return 'WEEKLY';
				} elseif ( ( $period > 11 ) && ( $period <= 24 ) ) {
					return 'SEMI_MONTHLY';
				} elseif ( ( $period > 24 ) && ( $period <= 42 ) ) {
					return 'MONTHLY';
				} elseif ( ( $period > 42 ) && ( $period <= 66 ) ) {
					return 'EVERY_TWO_MONTHS';
				} elseif ( ( $period > 66 ) && ( $period <= 140 ) ) {
					return 'QUARTERLY';
				} else {
					return 'YEARLY';
				}
				break;
			case 'W':
				if ( $period == 1 ) {
					return 'WEEKLY';
				} elseif ( ( $period > 1 ) && ( $period <= 2 ) ) {
					return 'SEMI_MONTHLY';
				} elseif ( ( $period > 2 ) && ( $period <= 5 ) ) {
					return 'MONTHLY';
				} elseif ( ( $period > 5 ) && ( $period <= 9 ) ) {
					return 'EVERY_TWO_MONTHS';
				} elseif ( ( $period > 9 ) && ( $period <= 20 ) ) {
					return 'QUARTERLY';
				} else {
					return 'YEARLY';
				}
				break;
			case 'M':
				if ( $period == 1 ) {
					return 'MONTHLY';
				} elseif ( ( $period > 1 ) && ( $period <= 3 ) ) {
					return 'EVERY_TWO_MONTHS';
				} elseif ( ( $period > 3 ) && ( $period <= 5 ) ) {
					return 'QUARTERLY';
				} else {
					return 'YEARLY';
				}
				break;
			case 'Y':
				return 'YEARLY';
				break;
		}
	}

	function createRequestXML( $request )
	{
		return "";
	}
	
	function transmitRequestXML( $xml, $request )
	{
		$response 				= array();
		$response['valid'] 		= true;

		return $response;	
	}

	function parseNotification( $post )
	{
		require_once( dirname(__FILE__) . '/lib/googlerequest.php' );

		$response			= array();
		$response['valid'] = false;

		$merchant_id		= $this->settings['merchant_id'];
		$merchant_key		= $this->settings['merchant_key'];
		$currency			= $this->settings['currency'];

		$serial_number		= $_POST["serial-number"];

		if ( $this->settings['testmode'] ) {
			$server_type = "sandbox";
		} else {
			$server_type = "Production";
		}

		$googleRequest = new GoogleRequest( $merchant_id, $merchant_key, $server_type, $currency, $serial_number );
		
		$googleRequest->SendAcknowledgementRequest();

		list( $res, $xml_response ) = $googleRequest->SendHistoryRequest();

		if ( $res != 200 ) {
			return $response;
		}

		// Quick way of filtering out notifications
		$filter = array( 'order-state-change-notification', 'charge-amount-notification', 'risk-information-notification', 'cancelled-subscription-notification' );

		foreach ( $filter as $f ) {
			if ( strpos( $xml_response, $f ) !== false ) {
				$response['null']			= 1;
				$response['explanation']	= 'An additional notification for order ' . $serial_number . ' has arrived.';

				switch ( $f ) {
					case 'order-state-change-notification':
						$prev_fullf = $this->XMLsubstring_tag( $xml_response, 'previous-fulfillment-order-state' );
						$curr_fullf = $this->XMLsubstring_tag( $xml_response, 'new-fulfillment-order-state' );

						$prev_finan = $this->XMLsubstring_tag( $xml_response, 'previous-financial-order-state' );
						$curr_finan = $this->XMLsubstring_tag( $xml_response, 'new-financial-order-state' );

						if ( $prev_fullf != $curr_fullf ) {
							$response['explanation'] .= ' Fullfillment State changed from ' . $prev_fullf . ' to ' . $curr_fullf . '.';
						}

						if ( $prev_finan != $curr_finan ) {
							$response['explanation'] .= ' Financial State changed from ' . $prev_finan . ' to ' . $curr_finan . '.';
						}

						break;
					case 'charge-amount-notification':
						$response['explanation'] .= ' The amount has been charged.';

						break;
					case 'cancelled-subscription-notification':
						$response['null']	= false;
						$response['cancel']	= 1;

						break;
					default:
						$response['explanation'] .= $f;

						break;
				}

				return $response;
			}
		}

		$response['valid']					= true;
		$response['invoice']				= $this->XMLsubstring_tag( $xml_response, 'invoice' );
		$response['google-order-number']	= $this->XMLsubstring_tag( $xml_response, 'google-order-number' );
		$response['serial_number']			= $serial_number;
		$response['server_type']			= $server_type;

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		if ( $response['valid'] ) {
			$googleRequest = new GoogleRequest(	$this->settings['merchant_id'],
											$this->settings['merchant_key'],
											$response['server_type'],
											$this->settings['currency'],
											$response['serial_number']
											);

			$order_number = $response['google-order-number'];

			unset( $response['google-order-number'] );
			unset( $response['server_type'] );
			unset( $response['serial_number'] );

			list( $res, $xml_response ) = $googleRequest->SendDeliverOrder( $order_number );

			if ( $res != 200 ) {
				$response['valid']			= false;
				$response['pending_reason']	= 'Sending Deliver Order failed';

				return $response;
			}

			list( $res, $xml_response ) = $googleRequest->SendChargeOrder( $order_number );

			if ( $res != 200 ) {
				$response['valid']			= false;
				$response['pending_reason']	= 'Sending Charge Order failed';

				return $response;
			}
		} else {
			unset( $response['google-order-number'] );
			unset( $response['server_type'] );
			unset( $response['serial_number'] );
		}

		return $response;
	}

}
?>

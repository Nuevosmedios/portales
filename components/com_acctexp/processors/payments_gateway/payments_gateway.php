<?php
/**
 * @version $Id: payments_gateway.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Payments Gateway
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_payments_gateway extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'payments_gateway';
		$info['longname']		= JText::_('CFG_PAYMENTS_GATEWAY_LONGNAME');
		$info['statement']		= JText::_('CFG_PAYMENTS_GATEWAY_STATEMENT');
		$info['description']	= JText::_('CFG_PAYMENTS_GATEWAY_DESCRIPTION');
		$info['currencies']		= 'USD';
		$info['languages']		= AECToolbox::getISO639_1_codes();
		$info['cc_list']		= 'visa,mastercard,discover,americanexpress,echeck';
		$info['recurring']		= 2;
		$info['notify_trail_thanks']	= 1;
		$info['recurring_buttons']		= 2;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['api_login_id']	= 'a1b2c3d4e5f6';
		$settings['api_key']		= 'a1b2c3d4e5f6';
		$settings['testmode']		= 0;
		$settings['invoice_tax']	= 0;
		$settings['tax']			= '';
		$settings['currency']		= 'USD';
		$settings['customparams']	= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();

		$settings['api_login_id']			= array( 'inputC' );
		$settings['api_key']				= array( 'inputC' );
		$settings['testmode']				= array( 'toggle' );
		$settings['invoice_tax']			= array( 'toggle' );
		$settings['tax']					= array( 'inputA' );
		$settings['currency']				= array( 'list_currency' );
		$settings['customparams']			= array( 'inputD' );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function checkoutAction( $request, $InvoiceFactory=null, $xvar=null )
	{
		$xvar = $this->createGatewayLink( $request, true );

		$form = parent::checkoutAction( $request, $InvoiceFactory, $xvar, JText::_('Pay via Credit Card') );

		$xvar = $this->createGatewayLink( $request, false );

		$form .= parent::checkoutAction( $request, $InvoiceFactory, $xvar, JText::_('Pay via eCheck') );

		return $form;
	}

	function createGatewayLink( $request, $type )
	{
		if ( $this->settings['testmode'] ) {
			$var['post_url']	= 'https://sandbox.paymentsgateway.net/swp/co/default.aspx';
		} else {
			$var['post_url']	= 'https://swp.paymentsgateway.net/co/default.aspx';
		}

		$namearray		= $request->metaUser->explodeName();

		$var['pg_billto_postal_name_first']	= $namearray['first'];
		$var['pg_billto_postal_name_last']	= $namearray['last'];

		$var['pg_api_login_id']					= $this->settings['api_login_id'];
		if ( $type ) {
			$var['pg_transaction_type']				= "10";
		} else {
			$var['pg_transaction_type']				= "20";
		}

		$var['pg_consumerorderid']				= $request->invoice->invoice_number;

		$var['pg_return_url']					= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=payments_gatewaynotification' );
		$var['pg_return_method']				= 'Post';

		$var['pg_version_number']				= '1.0';

		if ( is_array( $request->int_var['amount'] ) ) {
			$var['pg_scheduled_transaction']	= 1;

			$var['pg_total_amount'] 	= $request->int_var['amount']['amount3'];

			$pu = $this->convertPeriodUnit( $request->int_var['amount']['unit3'], $request->int_var['amount']['period3'] );

			$var['pg_schedule_frequency'] 	= $pu[0];
			$var['pg_schedule_start_date'] 	= date( "m/d/Y", $pu[1] );
		} else {
			$var['pg_total_amount'] 	= $request->int_var['amount'];
		}

		if ( !empty( $this->settings['invoice_tax'] ) && isset( $request->items->tax ) ) {
			$tax = 0;

			foreach ( $request->items->tax as $itax ) {
				$tax += $itax['cost'];
			}

			$var['pg_sales_tax_amount']			= AECToolbox::correctAmount( $tax );

			$var['pg_total_amount']		= $request->items->total->cost['amount'];
		} elseif ( !empty( $this->settings['tax'] ) && $this->settings['tax'] > 0 ) {
			$amount				= $var['pg_total_amount'] / ( 100 + $this->settings['tax'] ) * 100;
			$var['pg_sales_tax_amount']			= AECToolbox::correctAmount( ( $var['pg_total_amount'] - $amount ), 2 );
			$var['pg_total_amount']		= AECToolbox::correctAmount( $amount, 2 );
		}

		$var['pg_utc_time']						= number_format((gmdate('U')*10000000 + 621355968000000000), 0, '', '');
		$var['pg_transaction_order_number']		= $request->invoice->id;

		$var['pg_ts_hash']	= $this->hmac(	$this->settings['api_key'],
											implode("|", array(		$var['pg_api_login_id'],
																	$var['pg_transaction_type'],
																	$var['pg_version_number'],
																	$var['pg_total_amount'],
																	$var['pg_utc_time'],
																	$var['pg_transaction_order_number']
																)
											)
										); 

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['amount']		= $post['pg_total_amount'];
		$response['invoice']	= AECfetchfromDB::InvoiceNumberfromId( $post['pg_transaction_order_number'] );

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 0;

		$hash = $this->hmac(	$this->settings['api_key'],
								implode("|", array(	$this->settings['api_login_id'],
													$post['pg_trace_number'],
													$post['pg_total_amount'],
													$post['pg_utc_time']
												)
								)
							); 

		if ( $post['pg_ts_hash_response'] != $hash ) {
			$response['error'] = 'hash mismatch';
		} elseif ( $post['pg_response_type'] == 'A' ) {
			$response['valid'] = 1;
		} else {
			$response['error'] = $post['pg_response_description'];
		}

		return $response;
	}

	function convertPeriodUnit( $period, $unit )
	{
		switch ( $unit ) {
			case 'D':
				if ( $period <= 11 ) {
					return array( 10, strtotime("+1 week", gmdate('U')) );
				} elseif ( ( $period > 11 ) && ( $period <= 24 ) ) {
					return array( 15, strtotime("+2 weeks", gmdate('U') ) );
				} elseif ( ( $period > 24 ) && ( $period <= 42 ) ) {
					return array( 20, strtotime("+1 month", gmdate('U') ) );
				} elseif ( ( $period > 42 ) && ( $period <= 66 ) ) {
					return array( 25, strtotime("+2 months", gmdate('U') ) );
				} elseif ( ( $period > 66 ) && ( $period <= 140 ) ) {
					return array( 30, strtotime("+3 months", gmdate('U') ) );
				} elseif ( ( $period > 140 ) && ( $period <= 196 ) ) {
					return array( 35, strtotime("+6 months", gmdate('U') ) );
				} else {
					return array( 40, strtotime("+1 year", gmdate('U') ) );
				}
				break;
			case 'W':
				if ( $period == 1 ) {
					return array( 10, strtotime("+1 week", gmdate('U') ) );
				} elseif ( ( $period > 1 ) && ( $period <= 2 ) ) {
					return array( 15, strtotime("+2 weeks", gmdate('U') ) );
				} elseif ( ( $period > 2 ) && ( $period <= 5 ) ) {
					return array( 20, strtotime("+1 month", gmdate('U') ) );
				} elseif ( ( $period > 5 ) && ( $period <= 9 ) ) {
					return array( 25, strtotime("+2 months", gmdate('U') ) );
				} elseif ( ( $period > 9 ) && ( $period <= 20 ) ) {
					return array( 30, strtotime("+3 months", gmdate('U') ) );
				} elseif ( ( $period > 20 ) && ( $period <= 36 ) ) {
					return array( 35, strtotime("+6 months", gmdate('U') ) );
				} else {
					return array( 40, strtotime("+1 year", gmdate('U') ) );
				}
				break;
			case 'M':
				if ( $period == 1 ) {
					return array( 20, strtotime("+1 month", gmdate('U') ) );
				} elseif ( ( $period > 1 ) && ( $period <= 3 ) ) {
					return array( 25, strtotime("+2 months", gmdate('U') ) );
				} elseif ( ( $period > 3 ) && ( $period <= 5 ) ) {
					return array( 30, strtotime("+3 months", gmdate('U') ) );
				} elseif ( ( $period > 5 ) && ( $period <= 10 ) ) {
					return array( 35, strtotime("+6 months", gmdate('U') ) );
				} else {
					return array( 40, strtotime("+1 year", gmdate('U') ) );
				}
				break;
			case 'Y':
				return array( 40, strtotime("+1 year", gmdate('U') ) );
				break;
		}
	}

	function hmac( $key, $data )
	{
	   // RFC 2104 HMAC implementation for php.
	   // Creates an md5 HMAC.
	   // Eliminates the need to install mhash to compute a HMAC
	   // Hacked by Lance Rushing

	   $b = 64; // byte length for md5

	   if (strlen($key) > $b) {
	       $key = pack("H*",md5($key));
	   }
	   $key  = str_pad($key, $b, chr(0x00));
	   $ipad = str_pad('', $b, chr(0x36));
	   $opad = str_pad('', $b, chr(0x5c));
	   $k_ipad = $key ^ $ipad ;
	   $k_opad = $key ^ $opad;

	   return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
	}

}
?>

<?php
/**
 * @version $Id: usaepay.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - USA ePay
 * @copyright 2007-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_usaepay extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= "usaepay";
		$info['longname']				= JText::_('CFG_USAEPAY_LONGNAME');
		$info['statement']				= JText::_('CFG_USAEPAY_STATEMENT');
		$info['description']			= JText::_('CFG_USAEPAY_DESCRIPTION');
		$info['currencies']				= AECToolbox::aecCurrencyField( true, true, true, true );
		$info['cc_list']				= "visa,mastercard,discover,jcb";
		$info['recurring']				= 2;

		return $info;
	}

	function settings()
	{
		$settings = array();

		$settings['aec_experimental'] = true;

		$settings['testmode']		= 0;
		$settings['currency']		= 'USD';
		$settings['StoreKey']		= "StoreKey";
		$settings['StorePin']		= "Pin";
		$settings['item_name']		= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['customparams']	= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();

		$settings['testmode']		= array( "toggle" );
		$settings['currency']		= array( 'list_currency' );
		$settings['StoreKey']		= array( "inputC" );
		$settings['StorePin']		= array( "inputC" );
		$settings['item_name']		= array( 'inputE' );
		$settings['customparams']	= array( 'inputD' );
		

		return $settings;
	}

	function checkoutform( $request )
	{
		$var = $this->getCCform();

		$values = array( 'firstname', 'lastname', 'address', 'city', 'state_usca', 'zip', 'country_list' );

		$var = $this->getUserform( $var, $values, $request->metaUser );

		return $var;
	}

	function createRequestXML( $request )
	{
		$var = array(
					"UMkey" => $this->settings['StoreKey'], 
					"UMcommand" => 'sale',
					"UMcard" => trim( $request->int_var['params']['cardNumber'] ),
					"UMexpir" => $request->int_var['params']['expirationMonth'] . $request->int_var['params']['expirationYear'],
					"UMinvoice" => $request->invoice->invoice_number, 
					"UMorderid" => $request->invoice->id,
					"UMtax" => '',
					"UMcurrency" => $request->invoice->currency,
					"UMname" => $request->int_var['params']['billFirstName'] . ' ' . $request->int_var['params']['billLastName'],
					"UMstreet" => $request->int_var['params']['billAddress'],
					"UMzip" => $request->int_var['params']['billZip'],
					"UMdescription" => AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request ),
					"UMcvv2" => trim( $request->int_var['params']['cardVV2'] ),
					"UMtestmode" => $this->settings['testmode']
					);

		if ( is_array( $request->int_var['amount'] ) ) {
			// Seems to only support one cycle type
			$var = array_merge( $var, array(
											"UMaddcustomer" => 1,
											"UMschedule" => $this->convertPeriodUnit( $request->int_var['amount']['period3'], $request->int_var['amount']['unit3'] ),
											"UMstart" => 'next'
											)
								);

			$amount = $request->int_var['amount']['amount3'];
		} else {
			$amount = $request->int_var['amount'];
		}

		$var["UMamount"] = $amount;

		$var = $this->customParams( $this->settings['customparams'], $var, $request );

		return $this->arrayToNVP( $var );
	}

	function transmitRequestXML( $xml, $request )
	{
		$return['valid'] = false;

		$path = '/interface/epayform/' . $this->settings['StoreKey'] . '/';

		if ( $this->settings['testmode'] ) {
			$url	= "https://sandbox.usaepay.com" . $path;
			                        
		} else {
			$url	= "https://secure.usaepay.com" . $path;
		}

		$response = $this->transmitRequest( $url, $path, $xml );

		if ( !empty( $response ) ) {
			$result = parse_str( $response );
aecDebug($response);aecDebug($result);exit;
			if ( $result['result'] == 'Approved' ) {
				$return['valid'] = true;
			} else {
				$return['error'] = $result['error'];
			}
		} else {
			$return['error'] = 'Could not connect to USAepay';
		}

		return $return;
	}

	function convertPeriodUnit( $period, $unit )
	{
		switch ( $unit ) {
			case 'D':
				if ( $period <= 4 ) {
					return 'daily';
				} elseif ( ( $period > 4 ) && ( $period <= 11 ) ) {
					return 'weekly';
				} elseif ( ( $period > 11 ) && ( $period <= 24 ) ) {
					return 'biweekly';
				} elseif ( ( $period > 24 ) && ( $period <= 42 ) ) {
					return 'monthly';
				} elseif ( ( $period > 42 ) && ( $period <= 66 ) ) {
					return 'bimonthly';
				} elseif ( ( $period > 66 ) && ( $period <= 140 ) ) {
					return 'quarterly';
				} elseif ( ( $period > 140 ) && ( $period <= 196 ) ) {
					return 'biannually';
				} else {
					return 'annually';
				}
				break;
			case 'W':
				if ( $period == 1 ) {
					return 'weekly';
				} elseif ( ( $period > 1 ) && ( $period <= 2 ) ) {
					return 'biweekly';
				} elseif ( ( $period > 2 ) && ( $period <= 5 ) ) {
					return 'monthly';
				} elseif ( ( $period > 5 ) && ( $period <= 9 ) ) {
					return 'bimonthly';
				} elseif ( ( $period > 9 ) && ( $period <= 20 ) ) {
					return 'quarterly';
				} elseif ( ( $period > 20 ) && ( $period <= 36 ) ) {
					return 'biannually';
				} else {
					return 'annually';
				}
				break;
			case 'M':
				if ( $period == 1 ) {
					return 'monthly';
				} elseif ( ( $period > 1 ) && ( $period <= 3 ) ) {
					return 'bimonthly';
				} elseif ( ( $period > 3 ) && ( $period <= 5 ) ) {
					return 'quarterly';
				} elseif ( ( $period > 5 ) && ( $period <= 10 ) ) {
					return 'biannually';
				} else {
					return 'annually';
				}
				break;
			case 'Y':
				return 'annually';
				break;
		}
	}

}
?>

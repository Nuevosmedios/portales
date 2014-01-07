<?php
/**
 * @version $Id: paystation.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Paystation
 * @copyright 2007-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_paystation extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'paystation';
		$info['longname']				= JText::_('CFG_PAYSTATION_LONGNAME');
		$info['statement']				= JText::_('CFG_PAYSTATION_STATEMENT');
		$info['description']			= JText::_('CFG_PAYSTATION_DESCRIPTION');
		$info['currencies']				= AECToolbox::aecCurrencyField( true, true, true, true );
		$info['languages']				= AECToolbox::getISO639_1_codes();
		$info['cc_list']				= "visa,mastercard,discover,americanexpress,echeck,jcb,dinersclub";
		$info['secure']					= 1;
		$info['notify_trail_thanks']	= 1;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']		= 0;
		$settings['3party']			= 1;
		$settings['paystation_id'] 	= 'paystationID';
		$settings['gateway_id']		= 'gatewayID';
		$settings['currency']		= 'NZD';

		return $settings;

	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']		= array( 'toggle' );
		$settings['3party']			= array( 'toggle' );
		$settings['paystation_id']	= array( 'inputC' );
		$settings['gateway_id']		= array( 'inputC' );
		$settings['currency']		= array( 'list_currency' );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function requireSSLcheckout()
	{
		if ( empty( $this->settings['3party'] ) ) {
			return $this->info['secure'];
		} else {
			return false;
		}
	}

	function checkoutform( $request )
	{
		if ( empty( $this->settings['3party'] ) ) {
			$var = $this->getCCform( array(), array( 'card_number', 'card_exp_month', 'card_exp_year', 'card_cvv2' ) );
		} else {
			$var = array();
		}

		return $var;
	}

	function createRequestXML( $request )
	{
		$var['paystation']			= '_empty';
		$var['pstn_pi']				= $this->settings['paystation_id'];
		$var['pstn_gi']				= $this->settings['gateway_id'];
		$var['pstn_ms']				= $request->invoice->invoice_number.time();
		$var['pstn_am']				= (int) ( $request->int_var['amount']*100 );
		$var['pstn_cu']				= $this->settings['currency'];

		if ( $this->settings['testmode'] ) {
			$var['pstn_tm'] = 't';
		}

		if ( empty( $this->settings['3party'] ) ) {
			$var['pstn_2p']				= 't';
			$var['pstn_nr']				= 't';
		}

		$var['pstn_mr']				= $request->invoice->invoice_number;

		if ( empty( $this->settings['3party'] ) ) {
			$var['pstn_cn']				= trim( $request->int_var['params']['cardNumber'] );
			$var['pstn_ex']				= str_pad( $request->int_var['params']['expirationMonth'], 2, '0', STR_PAD_LEFT ) . substr($request->int_var['params']['expirationYear'],2);
			$var['pstn_cc']				= trim( $request->int_var['params']['cardVV2'] );
		}

		return $this->arrayToNVP( $var, true );
	}

	function transmitRequestXML( $xml, $request )
	{
		$path = '/direct/paystation.dll';
		$url = 'https://www.paystation.co.nz' . $path;
		$response = $this->transmitRequest( $url, $path, $xml );

		if ( !empty( $this->settings['3party'] ) ) {
			$redirect = $this->XMLsubstring_tag( $response, 'DigitalOrder' );

			if ( !empty( $redirect ) ) {
				aecRedirect( $redirect );exit;
			}
		}

		$return = array();
		$return['valid']	= 0;

		if ( $this->XMLsubstring_tag( $response, 'ec' ) === "0" ) {
			$return['valid']		= 1;
		} else {
			$return['error'] 	= $this->XMLsubstring_tag( $response, 'em' );
		}

		return $return;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['invoice'] = substr( aecGetParam( 'ms', '', true, array( 'word' ) ), 0, 17 );

		$amount = aecGetParam( 'am', '', true, array( 'word' ) );

		if ( !empty( $amount ) ) {
			$response['amount_paid']	= $amount / 100;
		}

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 1;

		return $response;
	}

}

?>

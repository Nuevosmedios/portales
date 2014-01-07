<?php
/**
 * @version $Id: billsafe.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - BillSAFE
 * @copyright 2007-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_billsafe extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'billsafe';
		$info['longname']				= JText::_('CFG_BILLSAFE_LONGNAME');
		$info['statement']				= JText::_('CFG_BILLSAFE_STATEMENT');
		$info['description']			= JText::_('CFG_BILLSAFE_DESCRIPTION');
		$info['currencies']				= 'EUR';
		$info['languages']				= AECToolbox::getISO639_1_codes();
		$info['cc_list']				= '';
		$info['notify_trail_thanks']	= 1;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']				= 0;
		$settings['merchant_id'] 			= '1234567890';
		$settings['merchant_license']		= '2d948c06c1a3154b139818fb9f65527c';
		$settings['signature']				= '';
		$settings['currency']				= 'EUR';

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']					= array( 'toggle' );
		$settings['merchant_id']				= array( 'inputC' );
		$settings['merchant_license']			= array( 'inputC' );
		$settings['signature']					= array( 'inputC' );
		$settings['item_name']					= array( 'inputE' );
		$settings['currency']					= array( 'list_currency' );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createRequestXML( $request )
	{
		$var['url_return']			= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=billsafenotification', $this->info['secure'], true );
		$var['url_cancel']			= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=cancel' );
		$var['desc']				= AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );

		$var['order_number']		= $request->invoice->invoice_number;
		
		if ( isset( $request->items->tax ) ) {
			$tax = 0;

			foreach ( $request->items->tax as $itax ) {
				$tax += $itax['cost'];
			}

			$var['order_taxAmount']		= AECToolbox::correctAmount( $tax );

			$var['order_amount']		= $request->items->total->cost['amount'];
		} else {
			$var['order_amount']		= $request->int_var['amount'];
		}

		$var['order_currencyCode']	= $this->settings['currency'];

		return $this->billsafeRequestXML( 'prepare_Order', $var );
	}

	function billsafeRequestXML( $method, $extra )
	{
		$var['Method']						= $method;

		$var['merchant_id']					= $this->settings['merchant_id'];
		$var['merchant_license']			= $this->settings['merchant_license'];

		$var['application_signature']		= $this->settings['signature'];
		$var['application_version']			= _AEC_VERSION  . ' rev' . _AEC_REVISION;

		$var['format']		= 'JSON';

		$var = array_merge( $var, $extra );

		return $this->arrayToNVP( $var, true );
	}

	function transmitToBillsafe( $xml )
	{
		$path = "/V207";
		
		if ( $this->settings['testmode'] ) {
			$url	= 'https://sandbox-nvp.billsafe.de'. $path;
		} else {
			$url	= 'https://nvp.billsafe.de'. $path;
		}
		
		return $this->transmitRequest( $url, $path, $xml );
	}

	function transmitRequestXML( $xml, $request )
	{
		$response = trim( $this->transmitToBillsafe( $xml ) );

		// converting NVPResponse to an Associative Array
		$response = json_decode( $response );

		if ( !empty( $response['token'] ) ) {
			if ( $this->settings['testmode'] ) {
				return aecRedirect('https://sandbox-nvp.billsafe.de/V207?token='.$response['token']);
			} else {
				return aecRedirect('https://nvp.billsafe.de/V207?token='.$response['token']);
			}
		} else {
			$return = array();
			$return['valid'] = false;
			$return['raw'] = $response;

			$return['error'] = 'No Response from the BillSAFE Server';
		}

		return $return;
	}

	function parseNotification( $post )
	{
		$xml = $this->billsafeRequestXML( 'getTransactionResult', array( 'token' => $_GET['token'] ) );

		$response = trim( $this->transmitToBillsafe( $xml ) );

		$response = json_decode( $response );

		$return = array();
		$return['raw'] = $response;

		if ( !empty( $response['transactionId'] ) ) {
			$return['invoice'] = $response['transactionId'];
		} else {
			$return['error'] = 'Missing transactionId';
		}

		return $return;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid']	= 0;

		if ( $response['raw']['status'] == 'ACCEPTED' ) {
			$response['valid']		= 1;
		} else {
			$response['error'] 	= 1;
			$response['errormsg'] 	= $response['raw']['declineReason_message'];
		}

		return $response;
	}

}

?>

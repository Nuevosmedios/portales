<?php
/**
 * @version $Id: generic_vpc.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Generic VPC
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_generic_vpc extends URLprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= "generic_vpc";
		$info['longname']				= JText::_('CFG_GENERIC_VPC_LONGNAME');
		$info['statement']				= JText::_('CFG_GENERIC_VPC_STATEMENT');
		$info['description']			= JText::_('CFG_GENERIC_VPC_DESCRIPTION');
		$info['currencies']				= 'EUR,USD,GBP,AUD,CAD,JPY,NZD,CHF,HKD,SGD,SEK,DKK,PLN,NOK,HUF,CZK,MXN,ILS';
		$info['languages']				= AECToolbox::getISO639_1_codes();
		$info['cc_list']				= "visa,mastercard";
		$info['recurring']				= 0;
		$info['notify_trail_thanks']	= 1;

		return $info;
	}

	function getLogoFilename()
	{
		return '';
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']			= 0;
		$settings['currency']			= "USD";
		$settings['vpc_Version']		= "1";
		$settings['vpc_URL']			= "https://vendor.mastercard.com.au/vpcpay?";
		$settings['vpc_Command']		= "pay";
		$settings['vpc_AccessCode']		= "ACCESSCODE";
		$settings['vpc_Merchant']		= "MERCHANTCODE";
		$settings['vpc_Locale']			= "en";
		$settings['vpc_SecureSecret']	= "SECRET CODE";
		$settings['vpc_OrderInfo']		= "VPC test";
		$settings['vpc_TicketNo']		= "xxx";
		$settings['customparams']		= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']			= array( 'toggle' );
		$settings['currency']			= array( 'list_currency' );
		$settings['vpc_URL']			= array( 'inputD' );
		$settings['vpc_Version']		= array( 'inputC' );
		$settings['vpc_Command']		= array( 'inputC' );
		$settings['vpc_AccessCode']		= array( 'inputC' );
		$settings['vpc_Merchant']		= array( 'inputC' );
		$settings['vpc_Locale']			= array( 'list_language' );
		$settings['vpc_TicketNo']		= array( 'inputC' );
		$settings['customparams']		= array( 'inputD' );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		if ( !empty( $this->settings['vpc_Version'] ) ) {
			$var['vpc_Version']		= $this->settings['vpc_Version'];
		}

		if ( !empty( $this->settings['vpc_Command'] ) ) {
			$var['vpc_Command']		= $this->settings['vpc_Command'];
		}

		$var['vpc_AccessCode']	= $this->settings['vpc_AccessCode'];
		$var['vpc_Merchant']	= $this->settings['vpc_Merchant'];
		$var['vpc_Amount']		= $request->int_var['amount'] * 100;
		$var['vpc_MerchTxnRef']	= $request->invoice->invoice_number;
		$var['vpc_OrderInfo']	= $this->settings['vpc_OrderInfo'];

		$var['vpc_ReturnURL']	= AECToolbox::deadsureURL("index.php?option=com_acctexp&amp;task=generic_vpcnotification");

		if ( !empty( $this->settings['vpc_TicketNo'] ) ) {
			$var['vpc_Locale']		= $this->settings['vpc_Locale'];
		}

		if ( !empty( $this->settings['vpc_TicketNo'] ) ) {
			$var['vpc_TicketNo']	= $this->settings['vpc_TicketNo'];
		}

		if ( !empty( $this->settings['customparams'] ) ) {
			$var = $this->customParams( $this->settings['customparams'], $var, $request );
		}

		ksort( $var );

		if ( !empty( $this->settings['vpc_SecureSecret'] ) ) {
			$hash = $this->settings['vpc_SecureSecret'];
			foreach ( $var as $k => $v ) {
				$hash .= $v;
			}

			$var['vpc_SecureHash'] = strtoupper( md5( $hash ) );
		}

		$var['post_url']	= $this->settings['vpc_URL'];

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();

		$response['invoice']		= $_GET['vpc_MerchTxnRef'];
		$response['amount_paid']	= $_GET['vpc_Amount'] / 100;

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = false;

		if ( !empty( $this->settings['vpc_SecureSecret'] ) ) {
			$hash = $this->settings['vpc_SecureSecret'];

			foreach( $_GET as $key => $value ) {
				if ( $key != "vpc_Secure_Hash" ) {
					$hash .= $value;
				}
			}

			if ( strtoupper( $_GET['vpc_Secure_Hash']) == strtoupper( md5( $hash ) ) ) {
				if ( $post['vpc_TxnResponseCode'] == 0 ) {
					$response['valid'] = true;
				} else {
					$response['error'] = $this->getResponseDescription( $post['vpc_TxnResponseCode'] );
				}
			} else {
				$response['error'] = 'Security Code Mismatch: ' . $post['vpc_Secure_Hash'];
			}
		} else {
			if ( $post['vpc_TxnResponseCode'] == 0 ) {
				$response['valid'] = true;
			} else {
				$response['error'] = $this->getResponseDescription( $post['vpc_TxnResponseCode'] );
			}
		}

		return $response;
	}

	function getResponseDescription( $responseCode )
	{
		$codes = array(
				"0" => "Transaction Successful",
				"?" => "Transaction status is unknown",
				"1" => "Unknown Error",
				"2" => "Bank Declined Transaction",
				"3" => "No Reply from Bank",
				"4" => "Expired Card",
				"5" => "Insufficient funds",
				"6" => "Error Communicating with Bank",
				"7" => "Payment Server System Error",
				"8" => "Transaction Type Not Supported",
				"9" => "Bank declined transaction (Do not contact Bank)",
				"A" => "Transaction Aborted",
				"C" => "Transaction Cancelled",
				"D" => "Deferred transaction has been received and is awaiting processing",
				"F" => "3D Secure Authentication failed",
				"I" => "Card Security Code verification failed",
				"L" => "Shopping Transaction Locked (Please try the transaction again later)",
				"N" => "Cardholder is not enrolled in Authentication scheme",
				"P" => "Transaction has been received by the Payment Adaptor and is being processed",
				"R" => "Transaction was not processed - Reached limit of retry attempts allowed",
				"S" => "Duplicate SessionID (OrderInfo)",
				"T" => "Address Verification Failed",
				"U" => "Card Security Code Failed",
				"V" => "Address Verification and Card Security Code Failed"
				);

		if ( array_key_exists( $responseCode, $codes ) ) {
			return $codes[$responseCode];
		} else {
			return "Unable to be determined";
		}
	}
}
?>
<?php
/**
 * @version $Id: sagepay.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Sagepay
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_sagepay extends XMLprocessor
	{
		function info()
		{	
			$info = array();
			$info['name']			= 'sagepay';
			$info['longname']		= JText::_('CFG_SAGEPAY_LONGNAME');
			$info['statement']		= JText::_('CFG_SAGEPAY_STATEMENT');
			$info['description']	= JText::_('CFG_SAGEPAY_DESCRIPTION');
			$info['currencies']		= 'EUR,USD,GBP,AUD,CAD,JPY,NZD,CHF,HKD,SGD,SEK,DKK,PLN,NOK,HUF,CZK,MXN,ILS,BRL,MYR,PHP,TWD,THB,ZAR';
			$info['languages']		= AECToolbox::getISO639_1_codes();
			$info['cc_list']		= 'visa,mastercard';
			$info['recurring']		= 0;
			$info['secure']			= 1;

			return $info;
		}

		function settings()
		{
			$settings = array();
			$settings['testmode']	= 0;

			$settings['vid']		= '';
			$settings['secret']		= '';
			$settings['3dsecure']	= 0;

			$settings['item_name']	= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
			$settings['currency']	= 'EUR';
			$settings['country']	= 'GB';

			return $settings;
		}

		function backend_settings()
		{
			$settings = array();
			$settings['testmode']	= array( 'toggle' );

			$settings['vid']		= array( 'inputC' );
			$settings['secret']		= array( 'inputC' );
			$settings['3dsecure'] 	= array( 'toggle' );

			$settings['item_name']	= array( 'inputE' );
			$settings['currency']	= array( 'list_currency' );
			$settings['country'] 	= array( 'list_country' );

			$country_sel = array();
			$country_sel[] = JHTML::_('select.option', 'GB','GB' );

			$settings['lists']['country'] = JHTML::_( 'select.genericlist', $country_sel, 'sagepay_country', 'size="2"', 'value', 'text', $this->settings['country'] );

			$settings = AECToolbox::rewriteEngineInfo( null, $settings );

			return $settings;
		}

		function checkoutform()
		{
			$var = array();

			$values = array( 'firstname', 'lastname', 'address', 'city', 'zip', 'country_list' );

			$var = $this->getUserform( $var, $values, $request->metaUser );

			$values = array( 'card_type','card_number', 'card_exp_month', 'card_exp_year', 'card_cvv2' );

			$var = $this->getCCform( $var, $values, null );

			return $var;
		}

		function createRequestXML( $request )	
		{
			$vars['VPSProtocol']			= "2.23";
			$vars['TxType']					= "PAYMENT"; // AUTHORISE
			$vars['Vendor']					= $this->settings['vid'];
			$vars['VendorTxCode']			= $request->invoice->invoice_number;
			$vars['Amount']					= number_format( $request->items->total->cost['amount'], 2 );
			$vars['Description']			= AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );
			$vars['RelatedSecurityKey']		= $this->settings['secret'];
			$vars['Currency']				= $this->settings['currency'];
			$vars['CardHolder']				= $request->int_var['params']['billFirstName'] . ' ' . $request->int_var['params']['billLastName'];
			$vars['CardNumber']				= $request->int_var['params']['cardNumber'];
			$vars['CardType']				= $this->translateCardType( $request->int_var['params']['cardType'] );
			$vars['ExpiryDate']				= $request->int_var['params']['expirationMonth'] . substr( $request->int_var['params']['expirationYear'], -2 );
			$vars['CV2']					= $request->int_var['params']['cardVV2'];
			$vars['ClientIPAddress']		= $_SERVER['REMOTE_ADDR'];
			$vars['Apply3DSecure']			= $this->settings['3dsecure'];

			$vars['BillingSurname']			= $request->int_var['params']['billLastName'];
			$vars['BillingFirstnames']		= $request->int_var['params']['billFirstName'];
			$vars['BillingAddress1']		= $request->int_var['params']['billAddress'];
			$vars['BillingCity']			= $request->int_var['params']['billCity'];
			$vars['BillingPostCode']		= $request->int_var['params']['billZip'];
			$vars['BillingState']			= $request->int_var['params']['billState'];
			$vars['BillingCountry']			= $request->int_var['params']['billCountry'];

			$vars['DeliverySurname']		= $request->int_var['params']['billLastName'];
			$vars['DeliveryFirstnames']		= $request->int_var['params']['billFirstName'];
			$vars['DeliveryAddress1']		= $request->int_var['params']['billAddress'];
			$vars['DeliveryCity']			= $request->int_var['params']['billCity'];
			$vars['DeliveryPostCode']		= $request->int_var['params']['billZip'];
			$vars['DeliveryState']			= $request->int_var['params']['billState'];
			$vars['DeliveryCountry']		= $request->int_var['params']['billCountry'];			

			return $this->arrayToNVP( $vars );
		}

		function translateCardType( $ct )
		{
			$cctlist = array(	'visa' => 'VISA',
								'mastercard' => 'MC',
								'discover' => 'DC',
								'amex' => 'AMEX'
								);

			if ( isset( $cctlist[$ct] ) ) {
				return ( $cctlist[$ct] );
			} else {
				return 'MC';
			}
		}

		function transmitRequestXML( $xml, $request )
		{
			if ( $this->settings['testmode'] ) {
				$path	= '/Simulator/VSPDirectGateway.asp';
				$url	= 'https://test.sagepay.com' . $path;
			} else {
				$path	= '/gateway/service/vspdirect-register.vsp';
				$url	= 'https://live.sagepay.com' . $path;
			}

			$curlextra = array();
			$curlextra[CURLOPT_HTTPHEADER] = 'application/x-www-form-urlencoded';

			$response = $this->decodeResponse( $this->transmitRequest( $url, $path, $xml, 443, $curlextra ) );

			$return['valid']	= 0;
			$return['raw']		= $response;

			if ( trim($response["Status"]) == "OK" ) {
				$return['valid']	= 1;
			} else {
				$return['error']	= $response['StatusDetail'];
			}

			return $return;
		}

		function decodeResponse( $string )
		{
			// Split by newlines
			$response = split( chr(10), $string );

			$array = array();
			foreach ( $response as $r ) {
				$kv = explode( '=', $r, 2 );

				$array[$kv[0]] = $kv[1];
			}

			return $array;
		}
}
?>

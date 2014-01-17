<?php
/**
 * @version $Id: multisafepay.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Multisafepay
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author Thailo van Ree, David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_multisafepay extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']				= 'multisafepay';
		$info['longname']			= JText::_('CFG_MULTISAFEPAY_LONGNAME');
		$info['statement']			= JText::_('CFG_MULTISAFEPAY_STATEMENT');
		$info['description']		= JText::_('CFG_MULTISAFEPAY_DESCRIPTION');
		$info['currencies']			= 'EUR';
		$info['languages']			= 'DE,EN,NL,ES,FR';
		$info['cc_list']			= 'visa,maestro,mastercard';//'DIRDEB,VISA,WALLET,IDEAL,BANKTRANS,MAESTRO,MASTERCARD';
		$info['recurring']			= 0;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['account']			= "";
		$settings['site_id']			= "";
		$settings['site_secure_code']	= "";
		$settings['testmode']			= "0";
		$settings['currency']			= "EUR";
		$settings['language']			= "NL";
		$settings['customparams']        = "";

		$settings['gateway']			= array( 'IDEAL,DIRDEB,VISA,WALLET,IDEAL,BANKTRANS,MAESTRO,MASTERCARD' );
		$settings['item_name']			= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['account']			= array( 'inputC' );
		$settings['site_id']			= array( 'inputC' );
		$settings['site_secure_code']	= array( 'inputC' );
		
		$settings['testmode']			= array( 'toggle' );
		$settings['currency']			= array( 'list_currency' );
		$settings['language']			= array( 'list_language' );
		$settings['customparams']		= array( 'inputD' );
		$settings['item_name']			= array( 'inputE' );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		$methods = array(	'Visa Credit Cards' => 'VISA',
							'MasterCard' => 'MASTERCARD',
							'Maestro (UK, Spain & Austria)' => 'MAESTRO',
							'MultiSafepay' => 'WALLET',
							'Bank Transfer' => 'BANKTRANS',
							'Direct Debit (Germany)' => 'DIRDEB',
							'iDEAL (Netherlands)' => 'IDEAL'							
							);
		
		$pmethods = array();
		$pmethodssel = array();
		foreach ( $methods as $name => $key ) {
			$pmethods[] = JHTML::_('select.option', $key, $name );

			if ( !empty( $this->settings['gateway'] )) {
				if ( in_array( $key, $this->settings['gateway'] ) ) {
					$pmethodssel[] = JHTML::_('select.option', $key, $name );
				}
			}
		}

		$settings['lists']['gateway'] = JHTML::_( 'select.genericlist', $pmethods, 'gateway[]', 'size="8" multiple="multiple"', 'value', 'text', $pmethodssel );
		
		return $settings;
	}

	function checkoutform( $request )
	{
		$var = array();

		$var['params']['gateway'] = array( 'list', JText::_('CFG_MULTISAFEPAY_SELECT_GATEWAY'), null );
		$var['params']['country'] = array( 'list', JText::_('CFG_MULTISAFEPAY_SELECT_COUNTRY'), null );

		$gateways = $this->getGateways( $request );
		
		foreach ( $gateways as $id => $description ) {
			$options[]	= JHTML::_('select.option', htmlspecialchars($id), htmlspecialchars($description) );
		}		

		$var['params']['lists']['gateway'] = JHTML::_( 'select.genericlist', $options, 'gateway', 'size="1"', 'value', 'text', 'IDEAL' );

		$country_code_list = AECToolbox::getISO3166_1a2_codes();

		$code_list = array();
		foreach ( $country_code_list as $country ) {
			$code_list[] = JHTML::_('select.option', $country, $country . " - " . JText::_( 'COUNTRYCODE_' . $country ) );
		}

		$var['params']['lists']['country'] = JHTML::_( 'select.genericlist', $code_list, 'country', 'size="1"', 'value', 'text', 'NL' );

		return $var;
	}

	function createRequestXML( $request )
	{
		$signature = md5(($request->int_var['amount']*100).$this->settings['currency'].$this->settings['account'].$this->settings['site_id'].$request->invoice->id);

		$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
				. '<redirecttransaction ua="aec-processor-1.0">' . "\n"
				. $this->xml_array_to_xml($this->get_merchant_info(), 'merchant')
				. $this->xml_array_to_xml($this->get_customer_info($request), 'customer')
				. $this->xml_array_to_xml($this->get_transaction_info($request), 'transaction')
				. '<signature>' . $signature . '</signature>' . "\n"
				. '</redirecttransaction>' . "\n"
				;

		return $xml;
	}

	function transmitRequestXML( $xml, $request )
	{
		$response = array();
		$response['valid'] = 0;

		$reply = $this->transmitToMultiSafepay( $xml );

		if ( !empty( $reply ) ) {
			$matches = array();

			// check transaction status
			preg_match( '/\<redirecttransaction result="(.*)"\>/U', $reply, $matches );

			if ( count($matches) > 0 && $matches[1] == 'ok' ) {
				$matches = array();

				// get redirect URL
				preg_match( '/\<payment_url\>(.*)\<\/payment_url\>/U', $reply, $matches );

				if ( count($matches) > 0 ) {
					aecRedirect( $this->xml_unescape( $matches[1] ) );
				} else {
					$response['error'] = 'Unable to redirect user';
				}
			}

			$matches = array();
			preg_match( '/\<error\>.*\<description\>(.*)\<\/description\>.*\<\/error\>/U', $reply, $matches );

			if ( $matches > 0 ) {
				$response['error'] = $this->xml_unescape( $matches[1] );
			} else {
				$response['valid'] = 1;
			}
		} else {
			$response['error'] = "Cannot connect to MultiSafepay";
		}

		return $response;
	}

	function transmitToMultiSafepay( $xml )
	{
		$path = "/ewx/"; // don't remove the trailing slash otherwise the MultiSafepay API will return an error!

		if ( $this->settings['testmode'] ) {
			$url = 'https://testapi.multisafepay.com' . $path;
		} else {
			$url = 'https://api.multisafepay.com' . $path;
		}

		$header = array( 'Content-Type' => 'text/xml' );

		$curlextra = array(	CURLOPT_TIMEOUT => 30 );

		return $this->transmitRequest( $url, $path, $xml, 443, $curlextra, $header );
	}

	function parseNotification ( $post )
	{
		$response = array();
		$response['invoice'] = AECfetchfromDB::InvoiceNumberfromId( aecGetParam( 'transactionid', 0, true, array( 'word', 'int' ) ) );

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 0;

		$signature = md5(($invoice->amount*100).$this->settings['currency'].$this->settings['account'].$this->settings['site_id'].$invoice->id);

		$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
				. '<status ua="aec-processor-1.0">' . "\n"
				. $this->xml_array_to_xml($this->get_merchant_info(), 'merchant')
				. $this->xml_array_to_xml(array ('id' => $invoice->id), 'transaction')
				. '<signature>' . $signature . '</signature>' . "\n"
				. '</status>' . "\n"
				;

		$reply = $this->transmitToMultiSafepay( $xml );

		if ( !empty( $reply ) ) {
			preg_match('/\<status result="(.*)"\>/U', $reply, $matches);

			if ( count($matches) == 0 || $matches[1] != "ok" ) {
				$matches = array();

				preg_match('/\<error\>.*\<description\>(.*)\<\/description\>.*\<\/error\>/U', $reply, $matches);

				if ( $matches > 0 ) {
					$response['error'] = $this->xml_unescape($matches[1]);
				}
			} else {
				$response['valid'] = 1;

				// A link for getting back to the AEC Thanks page
				echo '<a href="' . $request->int_var['return_url'] . '">' . JText::_('CFG_MULTISAFEPAY_RETURN_TO_SHOP') . '</a>';
			}
		} else {
			$response['error'] = "Cannot connect to MultiSafepay";
		}

		return $response;
	}

	function getGateways( $request )
	{
		$xml  =	'<?xml version="1.0" encoding="UTF-8"?>' . "\n"
				.'<gateways ua="aec-processor-1.0">' . "\n"
				. $this->xml_array_to_xml($this->get_merchant_info(), 'merchant')
				. $this->xml_array_to_xml($this->get_customer_info($request), 'customer')
				. '</gateways>' . "\n";

		$gateways = array();

		$reply = $this->transmitToMultiSafepay( $xml );

		if ( !empty( $reply ) ) {
			$matches = array();

			// check transaction status
			preg_match('/\<gateways result="(.*)"\>/U', $reply, $matches);

			if ( count( $matches ) > 0 && $matches[1] == 'ok' ) {
				$matches = array();

				// get redirect URL
				$gatewayCount = preg_match_all( '/\<gateway\>\s*\<id\>(.*)\<\/id\>\s*\<description\>(.*)\<\/description\>\s*\<\/gateway\>/U', $reply, $matches );

				for ( $i = 0; $i < $gatewayCount; $i++ ) {
					$gateways[$matches[1][$i]] = $matches[2][$i];
				}
			} else {
				// get error message
				$matches = array();
				preg_match( '/\<error\>.*\<description\>(.*)\<\/description\>.*\<\/error\>/U', $reply, $matches );

				if ( $matches > 0 ) {
					$error = $this->xml_unescape( $matches[1] );
				}
			}
		}

		return $gateways;
	}

	function get_merchant_info()
	{
		return array(	'account'			=> $this->settings['account'],
						'site_id'			=> $this->settings['site_id'],
						'site_secure_code'	=> $this->settings['site_secure_code'],
						'notification_url'	=> AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=multisafepaynotification' )
		);
	}

	function get_customer_info( $request )
	{
		if ( !empty( $request->int_var['params']['country'] ) ) {
			$country = $request->int_var['params']['country'];
		} else {
			$country = 'NL';
		}
		
		$user = array(	'firstname'	=> $request->metaUser->cmsUser->username,
						'lastname'	=> $request->metaUser->cmsUser->name,
						'email'		=> $request->metaUser->cmsUser->email,
						'country'	=> $country,
						'locale'	=> $this->settings['language']
		);

		if ( !empty( $this->settings['customparams'] ) ) {
			$params = explode( "\n", AECToolbox::rewriteEngineRQ( $this->settings['customparams'], $request ) );

			foreach ( $params as $custom ) {
				$paramsarray = explode( '=', $custom, 2 );

				if ( !empty( $paramsarray[0] ) && isset( $paramsarray[1] ) ) {
					$user[$paramsarray[0]] = $paramsarray[1];
				}
			}
		}

		return $user;
	}

	function get_transaction_info( $request )
	{
		return array(	'id'			=> $request->invoice->id,
						'currency'		=> $this->settings['currency'],
						'amount'		=> $request->int_var['amount'] * 100,
						'description'	=> AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request ),
						'items'			=> $request->plan->name,
						'manual'		=> 'true',
						'gateway'		=> $request->int_var['params']['gateway']
		);
	}

	function xml_escape($str)
	{
		return htmlspecialchars($str, ENT_COMPAT, 'UTF-8');
	}

	function xml_unescape( $str )
	{
		return strtr( $str, array_flip( get_html_translation_table( HTML_SPECIALCHARS, ENT_COMPAT ) ) );
	}

	function xml_array_to_xml( $arr, $name )
	{
		$data = '<' . $name . '>' . "\n";

		foreach ( $arr as $key => $value ) {
			$data .= '<' . $key . '>' . htmlspecialchars( $value, ENT_COMPAT, 'UTF-8' ) . '</' . $key . '>' . "\n";
		}

		$data .= '</' . $name . '>' . "\n";

		return $data;
	}

}
?>

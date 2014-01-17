<?php
/**
 * @version $Id: paycific.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Paycific Subscription
 * @copyright 2012 Copyright (C) Nguyen Chi Trung
 * @author Nguyen Chi Trung <dnctrung@live.com> & Team SYB - http://www.sybt.tk
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_paycific extends POSTprocessor
{
	function info()
	{
  		$info = array();
		$info['name']					= 'paycific';
		$info['longname']				= JText::_('CFG_PAYCIFIC_LONGNAME');
		$info['statement']				= JText::_('CFG_PAYCIFIC_STATEMENT');
		$info['description']			= JText::_('CFG_PAYCIFIC_DESCRIPTION');
		$info['currencies']				= 'USD,EUR,GBP,CHF';
		$info['cc_list']				= 'visa,mastercard,discover,americanexpress,echeck';
		$info['notify_trail_thanks']	= 1;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']				= 0;
		$settings['merchantsecret']			= 'xxxxxxxxxxxx';
		$settings['websiteid']				= '';
		$settings['currency']				= 'EUR';
		$settings['item_name']				= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']				= array( 'toggle' );
		$settings['merchantsecret']			= array( 'inputC' );
		$settings['websiteid']				= array( 'inputC' );
		$settings['currency']				= array( 'list_currency' );
		$settings['item_name']				= array( 'inputE' );

		$settings							= AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		if ( empty( $request->invoice->secondary_ident ) ) {
			$paycific_path = '/en/shops/'.$this->settings['websiteid'].'/create_otp_code';

			$paycific_url = 'https://www.paycific.com' . $paycific_path;

			// Seems like these have to be sorted alphabetically
			$p = array(
				"amount"			=> $request->int_var['amount']*100,
				"currency_code"		=> $this->settings['currency'],
				"description"		=> AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request ),
				"order_number"		=> $request->invoice->invoice_number,
				"userfield_1"		=> $request->metaUser->userid,
				"userfield_2"		=> $request->invoice->invoice_number
			);

			$p["hash"] = md5( implode("", $p) . trim($this->settings['merchantsecret']) );

			$header = array();
			$header["User-Agent"] = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
			$header["Content-Type"] = "multipart/form-data";

			$var['post_url'] = $this->transmitRequest( $paycific_url, $paycific_path, XMLprocessor::arrayToNVP($p), 443, null, $header );

			$ex = explode( "?", $var['post_url'] );

			$pparams = XMLprocessor::NVPtoArray( $ex[1] );

			$request->invoice->secondary_ident = $pparams['otp_code'];
			$request->invoice->addParams( array( 'paycific_url' => $var['post_url'], 'hash' => $pparams['hash'] ) );

			$request->invoice->storeload();
		} else {
			$var['post_url'] = $request->invoice->params['paycific_url'];
		}

		return $var;
	}

	function parseNotification( $post )
	{
		if ( empty( $post ) ) {
			$post = aecPostParamClear( $_GET );

			$response['raw'] = $post;
		}

		$response = array();
		$response['invoice'] = $post['userfield_2'];

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid']	= 0;
		
		if ( $this->isValidMd5($post['hash']) ) {
			$response['error']		= true;
			$response['errormsg']	= "Hash Invalid";
		} elseif ( $_GET['hash'] == $request->invoice->params['hash'] ) {
			$response['valid']			= 1;
		} else {
			$response['error']		= true;
			$response['errormsg']	= "Hash Mismatch";
		}

		return $response;
	}

	function notificationError( $response, $error )
	{
		echo 'OK=0 ERROR: ' . $error;
	}

	function isValidMd5( $hash )
	{
		return !empty($hash) && preg_match('/^[a-f0-9]{32}$/', $hash);
	}
}
?>

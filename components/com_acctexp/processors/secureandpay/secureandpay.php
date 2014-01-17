<?php
/**
 * @version $Id: secureandpay.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Secureandpay.com
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_secureandpay extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'secureandpay';
		$info['longname']		= JText::_('CFG_SECUREANDPAY_LONGNAME');
		$info['statement']		= JText::_('CFG_SECUREANDPAY_STATEMENT');
		$info['description']	= JText::_('CFG_SECUREANDPAY_DESCRIPTION');
		$info['currencies']		= 'EUR,USD,GBP';
		$info['languages']		= array( 'en', 'fr' );
		$info['cc_list']		= 'visa,mastercard,discover,americanexpress';
		$info['recurring']		= 0;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['aec_insecure']		= 1;
		$settings['aec_experimental']	= array( 'p' );
		$settings['numsite']			= 'your NumSite';
		$settings['password']			= 'your***password';
		$settings['testmode']			= 0;
		$settings['invoice_tax']		= 0;
		$settings['currency']			= 'USD';
		$settings['language']			= 'en';
		$settings['item_name']			= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();

		$settings['numsite']		= array( 'inputC' );
		$settings['password']		= array( 'inputC' );
		$settings['testmode']		= array( 'toggle' );
		$settings['invoice_tax']	= array( 'toggle' );
		$settings['currency']		= array( 'list_currency' );
		$settings['language']		= array( 'list_language' );
		$settings['item_name']		= array( 'inputE' );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		if ( $this->settings['testmode'] ) {
			$var['post_url']	= 'https://www.secureandpay.com/test/demande_paiement.php';
		} else {
			$var['post_url']	= 'https://www.secureandpay.com/demande_paiement.php';
		}

		$var['NumSite']			= $this->settings['numsite'];
		$var['Password']		= md5( $this->settings['password'] );
		$var['orderID']			= $request->invoice->invoice_number;

		$tax = 0;
		if ( isset( $request->items->tax ) ) {
			foreach ( $request->items->tax as $itax ) {
				$tax += $itax['cost'];
			}

			$var['Amount']		= (int) ( $request->items->total->cost['amount']*100 );
		} else {
			$var['Amount']		= (int) ( $request->int_var['amount']*100 );
		}

		$var['Currency']		= $this->settings['currency'];

		$var['Language']		= $this->settings['language'];
/*
		if ( !empty( $this->settings['user_email'] ) ) {
			$var['EMAIL']		= AECToolbox::rewriteEngineRQ( $this->settings['user_email'], $request );
		} else {
			$var['EMAIL']		= $request->metaUser->cmsUser->email;
		}

		$name = $request->metaUser->explodeName();

		if ( !empty( $this->settings['user_lastname'] ) ) {
			$var['CustLastName']		= AECToolbox::rewriteEngineRQ( $this->settings['user_lastname'], $request );
		} else {
			$var['CustLastName']		= $name['last'];
		}

		if ( !empty( $this->settings['user_firstname'] ) ) {
			$var['CustFirstName']		= AECToolbox::rewriteEngineRQ( $this->settings['user_firstname'], $request );
		} else {
			$var['CustFirstName']		= $name['first'];
		}

		if ( !empty( $this->settings['user_address'] ) ) {
			$var['CustAddress1']		= AECToolbox::rewriteEngineRQ( $this->settings['user_address'], $request );
		}

		if ( !empty( $this->settings['user_zip'] ) ) {
			$var['CustZIP']		= AECToolbox::rewriteEngineRQ( $this->settings['user_zip'], $request );
		}

		if ( !empty( $this->settings['user_city'] ) ) {
			$var['CustCity']		= AECToolbox::rewriteEngineRQ( $this->settings['user_city'], $request );
		}

		if ( !empty( $this->settings['user_country'] ) ) {
			$var['CustCountry']		= AECToolbox::rewriteEngineRQ( $this->settings['user_country'], $request );
		}

		if ( !empty( $this->settings['user_tel'] ) ) {
			$var['CustTel']		= AECToolbox::rewriteEngineRQ( $this->settings['user_tel'], $request );
		}
*/
		$var['orderProducts']		= AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );
		//$var['MerchantSession']		= ???;
		$var['PaymentType']		= 'Direct';
		//$var['Reccu_Num']		= ???;

		$var['Tax']				= (int) AECToolbox::correctAmount( $tax )*100;

		$var['Signature']		= sha1( $var['NumSite'] . $var['Password'] . $var['orderID'] . $var['Amount'] . $var['Currency'] );

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['invoice'] = $post['PAYID'];

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 0;

		if ( $post['PAYID'] == 'WT' ) {
			$response['pending']		= 1;
			$response['pending_reason']	 = 'waiting';
		} elseif ( $post['PAYID'] == '00' ) {
			$response['valid'] 			= 1;
		} elseif ( $post['PAYID'] == '01' ) {
			$response['duplicate']		= 1;
		} elseif ( $post['PAYID'] == '05' ) {
			$response['error']			= 1;
			$response['errormsg']		= 'Refused';
		}

		return $response;
	}

}
?>

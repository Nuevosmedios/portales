<?php
/**
 * @version $Id: ogone.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Ogone
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org with help from
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_ogone extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'ogone';
		$info['longname']				= JText::_('CFG_OGONE_LONGNAME');
		$info['statement']				= JText::_('CFG_OGONE_STATEMENT');
		$info['description'] 			= JText::_('CFG_OGONE_DESCRIPTION');
		$info['currencies']				= AECToolbox::aecCurrencyField( true, true, true, true );
		$info['cc_list']				= 'americanexpress';
		//$info['languages']			= AECToolbox::getISO639_1_codes();
		$info['recurring']				= 0;
		$info['notify_trail_thanks']	= 1;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']		= '0';
		$settings['psid']			= 'PS ID';
		$settings['secret']			= 'PS Secret';
		$settings['currency']		= 'EUR';
		$settings['language']		= 'en_US';
		$settings['customparams']	= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']		= array( 'toggle' );
		$settings['psid']			= array( 'inputC' );
		$settings['secret']			= array( 'inputC' );
		$settings['currency']		= array( 'list_currency' );
		$settings['language']		= array( 'inputC' );
		$settings['customparams']	= array( 'inputD' );
		
		return $settings;
	}

	function createGatewayLink( $request )
	{
		$var = array();
		$var['PSPID']		= $this->settings['psid'];
		$var['ORDERID']		= $request->invoice->id;
		$var['AMOUNT']		= (int) ( $request->int_var['amount']*100 );
		$var['CURRENCY']	= $this->settings['currency'];

		$var['SHASign']		= $this->getHash($var);

		$var['language']	= $this->settings['language'];

		if ( $this->settings['testmode'] ) {
			$var['post_url']	= 'https://secure.ogone.com/ncol/test/orderstandard.asp';
		} else {
			$var['post_url']	= 'https://secure.ogone.com/ncol/prod/orderstandard.asp';
		}

		return $var;
	}

	function parseNotification( $post )	
	{	
		$response = array();

		$response['raw'] = $_GET;

		$response['invoice']			= $_GET['orderID'];
		$response['amount_paid']		= (float) $_GET['amount']/100;
		$response['amount_currency']	= $_GET['currency'];

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 0;

		$vars = array( 'ORDERID', 'CURRENCY', 'AMOUNT', 'PM', 'ACCEPTANCE', 'STATUS', 'CARDNO', 'ALIAS', 'PAYID', 'NCERROR', 'BRAND' );

		$source = array();
		foreach ( $vars as $v ) {
			$source[$v] = $_GET[$v];
		}

		if ( $this->getHash($source) != $_GET['SHASIGN'] ) {
			$response['error'] = "Hash Mismatch";
		} else {
			if ( $_GET['STATUS'] == 9 ) {
				$response['valid'] = 1;
			} else {
				$response['duplicate'] = 1;
			}
		}

		return $response;
	}

	function getHash( $source )
	{
		$vars = array( 'AMOUNT', 'CURRENCY', 'LANGUAGE', 'ORDERID', 'PSPID' );

		$string = '';
		foreach ( $vars as $var ) {
			$string .= $var.'='.$source[$var] . $this->settings['secret'];
		}

		return sha1( $string );
	}
}


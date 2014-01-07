<?php
/**
 * @version $Id: realex_redirect.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Realex - Redirect Mode
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_realex_redirect extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'realex_redirect';
		$info['longname']		= JText::_('CFG_REALEX_REDIRECT_LONGNAME');
		$info['statement']		= JText::_('CFG_REALEX_REDIRECT_STATEMENT');
		$info['description']	= JText::_('CFG_REALEX_REDIRECT_DESCRIPTION');
		$info['currencies']		= 'EUR,USD,GBP,AUD,CAD,JPY,NZD,CHF,HKD,SGD,SEK,DKK,PLN,NOK,HUF,CZK,MXN,ILS,BRL,MYR,PHP,TWD,THB,ZAR';
		$info['languages']		= AECToolbox::getISO639_1_codes();
		$info['cc_list']		= 'visa,mastercard,laser';
		$info['recurring']		= 0;

		return $info;
	}

	function getLogoFilename()
	{
		return 'realex.png';
	}

	function settings()
	{
		$settings = array();
		$settings['merchantid']	= 'yourmerchantid';
		$settings['account']	= 'youraccount';
		$settings['secret']		= 'yoursecret';
		$settings['testmode']	= 1;
		$settings['currency']	= 'EUR';
		
		return $settings;
	}

	function backend_settings()
	{
		$settings = array();

		$settings['merchantid']	= array( 'inputC' );
		$settings['account']	= array( 'inputC' );
		$settings['secret']		= array( 'inputC' );
		$settings['testmode']	= array( 'toggle' );
		$settings['currency']	= array( 'list_currency' );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		if ( $this->settings['testmode'] ) {
			$var['post_url']	= 'https://epage.payandshop.com/epage.cgi';
		} else {
			$var['post_url']	= 'https://epage.payandshop.com/epage.cgi';
		}

		$timestamp = strftime("%Y%m%d%H%M%S");

		$amount = (int) round( 100*$request->items->total->cost['amount'] );

		$md5hash = md5(
						md5(	$timestamp
								.$this->settings['merchantid']
								.$request->invoice->id
								.$amount
								.$this->settings['currency']
							)
						.$this->settings['secret']
					);	
		
		$var['MERCHANT_ID']			= $this->settings['merchantid'];
		$var['ORDER_ID']			= $request->invoice->id;
		$var['ACCOUNT']				= $this->settings['account'];
		$var['CURRENCY']			= $this->settings['currency'];
		$var['AMOUNT']				= $amount;
		$var['TIMESTAMP']			= $timestamp;
		$var['MD5HASH']				= $md5hash;
		$var['AUTO_SETTLE_FLAG']	= 1;

		return $var;
	}

	function parseNotification( $post )
	{
		aecDebug($post);aecDebug($_GET);

		return $response;
	}

}
?>

<?php
/**
 * @version $Id: paypal_payflow_link.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - PayPal Subscription
 * @copyright 2007-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_paypal_payflow_link extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'paypal_payflow_link';
		$info['longname'] 		= JText::_('CFG_PAYPAL_PAYFLOW_LINK_LONGNAME');
		$info['statement'] 		= JText::_('CFG_PAYPAL_PAYFLOW_LINK_STATEMENT');
		$info['description'] 	= JText::_('CFG_PAYPAL_PAYFLOW_LINK_DESCRIPTION');
		$info['currencies'] 	= 'EUR,USD,AUD,CAD,GBP,JPY,NZD,CHF,HKD,SGD,SEK,DKK,PLN,NOK,HUF,CZK,MXN,ILS,BRL,MYR,PHP,TWD,THB,ZAR';
		$info['languages'] 		= AECToolbox::getISO639_1_codes();
		$info['cc_list']		= 'visa,mastercard,discover,americanexpress,echeck,giropay';
		$info['recurring']		= 0;
		$info['cancel_info']	= JText::_('PAYPAL_SUBSCRIPTION_CANCEL_INFO');

		return $info;
	}

	function getLogoFilename()
	{
		return 'paypal.png';
	}

	function settings()
	{
		$settings = array();
		$settings['currency']		= "USD";
		$settings['login']			= 'your@login';
		$settings['partner']		= 'partner';
		$settings['item_name']		= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['customparams']	= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['aec_experimental']	= array( 'p' );
		$settings['currency']			= array( 'list_currency' );
		$settings['login']				= array( 'inputC' );
		$settings['partner']			= array( 'inputC' );
		$settings['item_name']			= array( 'inputE' );
		$settings['customparams']		= array( 'inputD' );

        $settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		$var['post_url']		= 'https://payflowlink.paypal.com';

		$var['LOGIN']			= $this->settings['login'];
		$var['PARTNER']			= $this->settings['partner'];

		$var['AMOUNT']			= $request->int_var['amount'];
		$var['TYPE']			= "S";

		$var['INVOICE']			= $request->invoice->invoice_number;
		$var['DESCRIPTION']		= AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['invoice']		= $post['INVOICE'];
		$response['amount_paid']	= $post['AMOUNT'];

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$path = '/cgi-bin/webscr';
		$ppurl = 'https://www.paypal.com' . $path;

		$req = 'cmd=_notify-validate';

		foreach ( $post as $key => $value ) {
			$value = str_replace('\r\n', "QQLINEBREAKQQ", $value);

			$value = urlencode( stripslashes($value) );

			$value = str_replace( "QQLINEBREAKQQ", "\r\n", $value ); // linebreak fix

			$req .= "&$key=".$value;
		}

		$res = $this->transmitRequest( $ppurl, $path, $req );

		$response['fullresponse']['paypal_verification'] = $res;

		$response['valid'] = 0;

		if ( ( $post['RESULT'] == 0 ) ) {
			$response['valid'] = 1;
		} else {
			$response['pending_reason'] = 'error: ' . $post['RESPMSG'];
		}

		return $response;
	}

}
?>
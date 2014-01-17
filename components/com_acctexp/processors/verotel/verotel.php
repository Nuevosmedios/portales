<?php
/**
 * @version $Id: verotel.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Verotel
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_verotel extends URLprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'verotel';
		$info['longname']				= JText::_('CFG_VEROTEL_LONGNAME');
		$info['statement']				= JText::_('CFG_VEROTEL_STATEMENT');
		$info['description']			= JText::_('CFG_VEROTEL_DESCRIPTION');
		$info['currencies']				= 'USD,EUR,GBP,NOK,SEK,DKK,CAD,CHF';
		$info['languages']				= 'AU,DE,FR,IT,GB,ES,US';
		$info['cc_list']				= 'visa,mastercard,discover,americanexpress,echeck';
		$info['notify_trail_thanks']	= 1;
		$info['recurring']				= 0;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['currency']			= "USD";
		$settings['merchantid']			= "merchantid";
		$settings['resellerid']			= "resellerid";
		$settings['siteid']				= "siteid";
		$settings['secretcode']			= "secretcode";
		$settings['use_ticketsclub']	= 1;
		$settings['customparams']		= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['currency']			= array( 'list_currency' );
		$settings['merchantid']			= array( 'inputC' );
		$settings['resellerid']			= array( 'inputC' );
		$settings['siteid']				= array( 'inputC' );
		$settings['secretcode']			= array( 'inputC' );
		$settings['use_ticketsclub']	= array( 'toggle' );
		$settings['info']				= array( 'fieldset' );
		$settings['customparams']		= array( 'inputD' );

		return $settings;
	}

	function CustomPlanParams()
	{
		$p = array();

		$p['verotel_product']	= array( 'inputC' );

		return $p;
	}

	function createGatewayLink( $request )
	{
		// Payment Plans are required to have a productid assigned
		if ( empty( $request->int_var['planparams']['verotel_product'] ) ) {
			$product = $this->settings['siteid'];
		} else {
			$product = $request->int_var['planparams']['verotel_product'];
		}

		if ( $this->settings['use_ticketsclub'] ) {
			$var['post_url']			= "https://secure.ticketsclub.com/cgi-bin/boxoffice-one.tc?";
			$var['fldcustomerid']		= $this->settings['merchantid'];
			$var['fldwebsitenr']		= $this->settings['siteid'];
			$var['tc_usercode']			= $request->metaUser->cmsUser->username;
			$var['tc_passcode']			= "xxxxxxxx";
			$var['tc_custom1']			= $request->invoice->invoice_number;
			$var['tc_custom2']			= $request->metaUser->cmsUser->username;
		} else {
			$var['post_url']			= "https://secure.verotel.com/cgi-bin/vtjp.pl?";
			$var['verotel_id']			= $this->settings['merchantid'];
			$var['verotel_product']		= $product;
			$var['verotel_website']		= $this->settings['siteid'];
			$var['verotel_usercode']	= $request->metaUser->cmsUser->username;
			$var['verotel_passcode']	= "xxxxxxxx";
			$var['verotel_custom1']		= $request->invoice->invoice_number;
		}

		return $var;
	}

	function parseNotification( $post )
	{
		$res = explode(":", aecGetParam('vercode'));

		$username	= $res[0];
		$secret		= $res[2];
		$action     = $res[3];
		$amount     = $res[4];
		$payment_id = $res[5];

		$response = array();
		$response['invoice'] = null;

		if ( $res[3] == 'add' ) {
			$response['invoice'] = $payment_id;
		} else {
			$db = &JFactory::getDBO();

			$query = 'SELECT `id` FROM #__users WHERE `username` = \'' . $username . '\'';
			$db->setQuery( $query );

			$userid = $db->loadResult();

			if ( $userid ) {
				$id = AECfetchfromDB::lastClearedInvoiceIDbyUserID( $userid );

				$query = 'SELECT `invoice_number` FROM #__acctexp_invoices WHERE `id` = \'' . $id . '\'';
				$db->setQuery( $query );

				$invoice_number = $db->loadResult();

				if ( !empty( $invoice_number ) ) {
					$response['invoice'] = $invoice_number;
				}
			}
		}

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		//if ( !AECToolbox::in_ip_range( '195.20.32.0', '﻿195.20.32.256' ) ) {
			//$response['error'] = 1;
			//$response['errormsg'] = "Wrong IP tried to send notification: " . $_SERVER["REMOTE_ADDR"];
			//return $response;
		//}

		$res = explode(":", aecGetParam('vercode'));

		if( $this->settings['secretcode'] == $res[2] ) {
			$response['valid'] = 1;
		} else {
			$response['valid'] = 0;
			$response['pending_reason'] = 'INVALID SECRET WORD, provided: ' . $res[2];
		}

		switch ( $res[3] ) {
			case 'add':
				$response['valid'] = 1;
				break;
			case 'cancel':
				$response['cancel'] = 1;
				$response['valid'] = 0;
				break;
			case 'delete':
				$response['delete'] = 1;
				$response['valid'] = 0;
				break;
			case 'rebill':
				$response['amount_paid'] = $res[4];
				break;
		}

		return $response;
	}

}

?>

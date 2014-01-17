<?php
/**
 * @version $Id: dwolla_redirect.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Dwolla Redirect
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_dwolla_redirect extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'dwolla_redirect';
		$info['longname']				= JText::_('CFG_DWOLLA_REDIRECT_LONGNAME');
		$info['statement']				= JText::_('CFG_DWOLLA_REDIRECT_STATEMENT');
		$info['description']			= JText::_('CFG_DWOLLA_REDIRECT_DESCRIPTION');
		$info['currencies']				= 'USD';
		$info['cc_list']				= '';
		$info['recurring']				= 0;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']			= 0;
		$settings['application_key']	= 'key';
		$settings['application_secret']	= 'secret code';
		$settings['destination_id']		= 'dwolla id';
		$settings['currency']			= 'USD';
		$settings['item_name']			= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['item_desc']			= sprintf( JText::_('CFG_PROCESSOR_ITEM_DESC_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['customparams']		= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']			= array( 'toggle' );
		$settings['application_key']	= array( 'inputC' );
		$settings['application_secret']	= array( 'inputC' );
		$settings['destination_id']		= array( 'inputC' );
		$settings['currency']			= array( 'list_currency' );
		$settings['item_name']			= array( 'inputE' );
		$settings['item_desc']			= array( 'inputE' );
		$settings['customparams']		= array( 'inputD' );

		$settings						= AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		$var['post_url']	= 'https://www.dwolla.com/payment/pay';

		$var['key']			= $this->settings['application_key'];

		$timestamp = time();
		$orderid = $request->invoice->id;

		$var['signature']	= hash_hmac('sha1', "{".$var['key']."}&{".$timestamp."}&{".$orderid."}", $this->settings['application_secret']);
		$var['timestamp']	= $timestamp;

		$var['callback']	= AECToolbox::deadsureURL( "index.php?option=com_acctexp&amp;task=dwolla_redirectnotification" );
		$var['redirect']	= AECToolbox::deadsureURL( "index.php?option=com_acctexp&amp;task=thanks" );

		$var['orderId']		= $orderid;

		if ( $this->settings['testmode'] ) {
			$var['test']	= '1';
		}
		
		$var['destinationId']	= $this->settings['destination_id'];

		$var['amount'] 		= $request->int_var['amount'];
		$var['shipping']	= '0.00';

		if ( isset( $request->items->tax ) ) {
			$tax = 0;

			foreach ( $request->items->tax as $itax ) {
				$tax += $itax['cost'];
			}

			$var['tax']			= AECToolbox::correctAmount( $tax );
		} else {
			$var['tax']			= '0.00';
		}

		$var['notes']		= $request->invoice->invoice_number;

		$var['name']		= AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );
		$var['description']	= AECToolbox::rewriteEngineRQ( $this->settings['item_desc'], $request );

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['invoice']		= AECfetchfromDB::InvoiceNumberfromId( $post['OrderId'] );
		$response['amount_paid']	= $post['Amount'];

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = false;

		if ( $post['Status'] == "Completed" )  {
			$signature = hash_hmac("sha1", "{" . $post['CheckoutId'] . "}&{" . $post['Amount'] . "}", $this->settings['application_secret']);

			if ( $post['Signature'] != $signature ) {
				$response['error'] = 'Security Code Mismatch: ' . $post['Signature'];
			} else {
				$response['valid'] = true;
			}
		} else {
			$response['error'] = 'dwolla status: ' . $post['Status'];
		}

		return $response;
	}

}
?>
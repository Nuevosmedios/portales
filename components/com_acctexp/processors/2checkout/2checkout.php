<?php
/**
 * @version $Id: 2checkout.php,v 1.0 2007/06/21 09:22:22 mic Exp $ $Revision: 1.0 $
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - 2CheckOut
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_2checkout extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= '2checkout';
		$info['longname'] 				= JText::_('AEC_PROC_INFO_2CO_LNAME');
		$info['statement'] 				= JText::_('AEC_PROC_INFO_2CO_STMNT');
		$info['description'] 			= JText::_('DESCRIPTION_2CHECKOUT');
		$info['currencies']				= "USD,ARS,AUD,BRL,CAD,DKR,EUR,GBP,HKD,INR,JPY,MXN,NZD,NOK,ZAR,SEK,CHF";
		$info['cc_list'] 				= "visa,mastercard,discover,americanexpress,echeck,jcb,dinersclub";
		$info['recurring'] 				= 2;
		$info['actions']				= array( 'cancel' => array( 'confirm' ) );
		$info['notify_trail_thanks']	= 1;
		$info['recurring_buttons']		= 2;

		return $info;
	}

	function getActions( $invoice, $subscription )
	{
		$actions = parent::getActions( $invoice, $subscription );

		if ( ( $subscription->status == 'Cancelled' ) || ( $invoice->transaction_date == '0000-00-00 00:00:00' ) ) {
			if ( isset( $actions['cancel'] ) ) {
				unset( $actions['cancel'] );
			}
		}

		return $actions;
	}

	function settings()
	{
		$settings = array();
		$settings['currency']		= "USD";
		$settings['sid']			= '2checkout sid';
		$settings['secret_word']	= 'secret_word';
		$settings['testmode']		= 0;
		$settings['alt2courl']		= '';
		$settings['info']			= '';
		$settings['item_name']		= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['customparams']	= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']		= array( 'toggle' );
		$settings['currency']		= array( 'list_currency' );
		$settings['sid']			= array( 'inputC' );
		$settings['secret_word']	= array( 'inputC' );
		$settings['info']			= array( 'fieldset' );
		$settings['alt2courl']		= array( 'toggle' );
		$settings['item_name']		= array( 'inputE' );
		$settings['customparams']	= array( 'inputD' );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function CustomPlanParams()
	{
		$p = array();
		$p['productid']				= array( 'inputC' );

		return $p;
	}

	function createGatewayLink( $request )
	{
		if ( $this->settings['alt2courl'] ) {
			$var['post_url']		= 'https://www2.2checkout.com/2co/buyer/purchase';
		} else {
			$var['post_url']		= 'https://www.2checkout.com/2co/buyer/purchase';
		}

		if ( $this->settings['testmode'] ) {
			$var['testmode']		= 1;
			$var['demo']			= 'Y';
		}

		$var['sid']					= $this->settings['sid'];
		$var['invoice_number']		= $request->invoice->invoice_number;
		$var['merchant_order_id']	= $request->invoice->invoice_number;
		$var['x_invoice_num']		= $request->invoice->invoice_number;
		$var['fixed']				= 'Y';
		$var['total']				= $request->int_var['amount'];

		$var['cust_id']				= $request->metaUser->cmsUser->id;

		if ( empty( $request->int_var['planparams']['productid'] ) ) {
			$var['cart_order_id']		= AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );
		}

		$var['username']			= $request->metaUser->cmsUser->username;
		$var['name']				= $request->metaUser->cmsUser->name;

		if ( !empty( $request->int_var['planparams']['productid'] ) ) {
			$var['product_id'] = $request->int_var['planparams']['productid'];
			$var['quantity'] = 1;
		}

		$var['cart_brand_name'] 	= 'AEC';
		$var['cart_version_name'] 	= _AEC_VERSION . ' Revision ' . _AEC_REVISION;

		return $var;
	}

	function parseNotification( $post )
	{
		$description	= $post['cart_order_id'];
		$key			= $post['key'];
		$total			= $post['total'];
		$userid			= $post['cust_id'];
	    $invoice_number	= $post['invoice_number'];
	    $order_number	= $post['order_number'];
		$username		= $post['username'];
		$name			= $post['name'];
		$planid			= $post['planid'];

		$response = array();
		if ( !empty( $post['invoice_number'] ) ) {
			$response['invoice'] = $post['invoice_number'];
		} elseif ( !empty( $post['merchant_order_id'] ) ) {
			$response['invoice'] = $post['merchant_order_id'];
		} elseif ( !empty( $post['vendor_order_id'] ) ) {
			$response['invoice'] = $post['vendor_order_id'];
		} else {
			$response['invoice'] = $post['x_invoice_num'];
		}

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$hash = "";
		if ( !empty( $post['key'] ) ) {
			$hash = $post['key'];
		} elseif ( !empty( $post['md5_hash'] ) ) {
			$hash = $post['md5_hash'];
		} elseif ( !empty( $post['md5hash'] ) ) {
			$hash = $post['md5hash'];
		}

		$hash = strtoupper( $hash );

		if ( $this->settings['testmode'] ) {
			$string_to_hash	= $this->settings['secret_word'].$this->settings['sid']."1".$post['total'];
		} else {
			$string_to_hash	= $this->settings['secret_word'].$this->settings['sid'].$post['order_number'].$post['total'];
		}

		$check_key = strtoupper(md5($string_to_hash));

		if ( $check_key == $hash ) {
			$response['valid'] = 1;
		} else {
			// Might still be a rebill, where the hash is completely different
			if ( !empty( $post['sale_id'] ) && !empty( $post['invoice_id'] ) ) {
				$string_to_hash	= $post['sale_id'].$this->settings['sid'].$post['invoice_id'].$this->settings['secret_word'];
				$check_key = strtoupper(md5($string_to_hash));

				if ( $check_key == $hash ) {
					$response['valid'] = 1;
				}
			}

			if ( !$response['valid'] ) {
				$response['error'] = true;
				$response['errormsg'] = 'hash mismatch';
			}
		}

		return $response;
	}

	function customaction_cancel( $request )
	{
		$path	= '/api/sales/stop_lineitem_recurring';
		$url	= 'https://www.2checkout.com' . $path;

		$content	= array( 'line_item' => "" );

		$header	= array( "Accept" => "application/json" );

		$curlextra[URLOPT_HTTPHEADER] = array ( "Accept: application/json" );

		$return = json_decode( $this->transmitRequest( $url, $path, $content, 443, $curlextra, $header ) );

		if ( !empty( $response ) ) {
			return $return;
		} else {
			getView( 'error', array(	'error' => "An error occured while cancelling your subscription. Please contact the system administrator!",
										'metaUser' => $request->metaUser,
										'invoice' => $request->invoice,
										'suppressactions' => true
									) );
		}
	}

}
?>

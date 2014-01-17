<?php
/**
 * @version $Id: clickbank.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Clickbank
 * @copyright 2007-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org, initial help by Pasapum Naonan
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_clickbank extends URLprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'clickbank';
		$info['longname'] 				= JText::_('CFG_CLICKBANK_LONGNAME');
		$info['statement'] 				= JText::_('CFG_CLICKBANK_STATEMENT');
		$info['description'] 			= JText::_('CFG_CLICKBANK_DESCRIPTION');
		$info['cc_list'] 				= "visa,mastercard,americanexpress,discover,dinersclub,jcb,paypal";
		$info['currencies']				= "USD";
		$info['recurring'] 				= 2;
		$info['notify_trail_thanks']	= 1;
		$info['recurring_buttons']		= 2;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']		= 0;
		$settings['currency']		= "USD";
		$settings['publisher']		= 'clickbank';
		$settings['secret_key']		= 'secret_key';
		$settings['info']			= "";
		$settings['customparams']	= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['currency']		= array( 'list_currency' );
		$settings['testmode']		= array( 'toggle' );
		$settings['publisher']		= array( 'inputC' );
		$settings['secret_key']		= array( 'inputC' );
		$settings['info']			= array( 'fieldset' );
		$settings['customparams']	= array( 'inputD' );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function CustomPlanParams()
	{
		$p = array();
		$p['item_number']	= array( 'inputC' );

		return $p;
	}

	function createGatewayLink( $request )
	{
		$item_number			= $request->int_var['planparams']['item_number'];

		$var['post_url']		= 'http://'.$item_number.'.'.$this->settings['publisher'].'.pay.clickbank.net?';

		// pass internal invoice to clickbank, so it will pass back to us for internal checking
		$var['invoice']			= $request->invoice->invoice_number;

		$var['cart_order_id']	= AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['invoice']			= aecGetParam( 'invoice', '', true, array( 'word' ) );

		if ( empty( $response['invoice'] ) ) {
			$cvendthru = aecGetParam( 'cvendthru', '', true, array( 'word' ) );

			$carray = explode( "&", $cvendthru );

			foreach ( $carray as $n ) {
				$data = explode( "=", $n );

				if ( $data[0] == 'invoice' ) {
					$response['invoice'] = $data[1];

					break;
				}
			}
		}

		$amount = aecGetParam( 'ctransamount', '', true, array( 'word' ) );

		if ( !empty( $amount ) ) {
			$response['amount_paid']	= $amount / 100;
		}

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 0;

		$cverify = aecGetParam( 'cverify', '', true, array( 'word' ) );

		$post = $_POST;

		if ( empty( $cverify ) ) {
			$postback = array( 'item', 'cbreceipt', 'time', 'cbpop', 'cbaffi', 'cname', 'czip', 'ccountry', 'allowedTypes', 'invoice' );

			foreach ( $postback as $pb ) {
				if ( $pb == 'cname' ) {
					$post[$pb] = aecGetParam( $pb, '', true, array( 'string' ) );
				} else {
					$post[$pb] = aecGetParam( $pb, '', true, array( 'word', 'string' ) );
				}
			}

			// It seems this is the crude postback. Trying to decypher.

			$check = array();
			$check[] = $this->settings['secret_key'];
			$check[] = $post['cbreceipt'];
			$check[] = $post['time'];
			$check[] = $post['item'];

			$xxpop = strtoupper( substr( sha1( mb_convert_encoding(implode( '|', $check ), "UTF-8") ), 0 ,8 ) );

			if ( $post['cbpop'] == $xxpop ) {
				$response['valid']	= 1;
			} else {
				if ( $post['cbpop'] == $this->postToKey( $post ) ) {
					$response['valid']	= 1;
				} else {
					$response['pending_reason'] = 'verification error';
				}
			}
		} else {
			if ( $cverify == $this->postToKey( $post ) ) {
				switch ( $post['ctransaction'] ) {
					// The purchase of a standard product or the initial purchase of recurring billing product.
					case 'SALE':
						$response['valid']	= 1;
						break;
					// The purchase of a standard product or the initial purchase of recurring billing product.
					case 'BILL':
						$response['valid']	= 1;
						break;
					// The refunding of a standard or recurring billing product. Recurring billing products that are refunded also result in a "CANCEL-REBILL" action.
					case 'RFND':
						$response['delete']	= 1;
						break;
					// A chargeback for a standard or recurring product.
					case 'CGBK':
						$response['chargeback']	= 1;
						break;
					// A chargeback for a standard or recurring product.
					case 'INSF':
						$response['chargeback']	= 1;
						break;
					// The cancellation of a recurring billing product. Recurring billing products that are canceled do not result in any other action.
					case 'CANCEL-REBILL':
						$response['cancel']	= 1;
						break;
					// Reversing the cancellation of a recurring billing product.
					case 'UNCANCEL-REBILL':
						$response['cancel']	= 1;
						break;
					// Triggered by using the test link on the site page.
					case 'TEST':
						if ( $this->settings['secret_key'] ) {
							$response['valid']	= 1;
						} else {
							$response['pending_reason'] = 'testmode claimed when not activated';
						}
						break;
				}
			} else {
				$response['pending_reason'] = 'verification error';
			}

		}

		$response['fullresponse'] = $post;

		return $response;
	}

	function postToKey( $post )
	{
		$fields = array();
		foreach ( $post as $key => $value ) {
			if ( $key != "cverify" ) {
				$fields[] = $key;
			}
		}

		sort( $fields );

		$params = array();
		foreach ( $fields as $field ) {
			if ( get_magic_quotes_gpc() ) {
				$params[] = stripslashes( $post[$field] );
			} else {
				$params[] = $post[$field];
			}
		}

		$params[] = $this->settings['secret_key'];

		return strtoupper( substr( sha1( mb_convert_encoding(implode( '|', $params ), "UTF-8") ), 0, 8 ) );
	}
}
?>
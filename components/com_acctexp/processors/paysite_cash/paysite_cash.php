<?php
/**
 * @version $Id: paysite_cash.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Paysite Cash
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_paysite_cash extends URLprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'paysite_cash';
		$info['longname']				= JText::_('CFG_PAYSITE_CASH_LONGNAME');
		$info['statement']				= JText::_('CFG_PAYSITE_CASH_STATEMENT');
		$info['description']			= JText::_('CFG_PAYSITE_CASH_DESCRIPTION');
		$info['currencies']				= 'EUR,USD,CAD,GBP,CHF';
		$info['languages']				= 'FR,US';
		$info['cc_list']				= 'visa,mastercard,discover,americanexpress,echeck';
		$info['notify_trail_thanks']	= 0;
		$info['recurring']				= 2;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']		= 0;
		$settings['siteid']			= "siteid";
		$settings['secret']			= "secret";
		$settings['currency']		= "EUR";
		$settings['customparams']	= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']		= array( 'toggle' );
		$settings['siteid']			= array( 'inputC' );
		$settings['secret']			= array( 'inputC' );
		$settings['currency']		= array( 'list_currency' );
		$settings['customparams']	= array( 'inputD' );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		if ( $this->settings['testmode'] ) {
			$var['test'] = 1;
		}

		$var['post_url'] = "https://billing.paysite-cash.biz/?";
		$var['site'] = $this->settings['siteid'];
		$var['devise'] = $this->settings['currency'];

		if ( is_array( $request->int_var['amount'] ) ) {
			$suffix = '';
			if ( isset( $request->int_var['amount']['amount1'] ) ) {
				$var['periode'] = $request->int_var['amount']['period1'] . strtolower( $request->int_var['amount']['unit1'] );
				$var['montant'] = $request->int_var['amount']['amount1'];
				$suffix = '2';
			}

			$var['periode'.$suffix] = $request->int_var['amount']['period3'] . strtolower( $request->int_var['amount']['unit3'] );
			$var['montant'.$suffix] = $request->int_var['amount']['amount3'];

			$var['nb_redebit'] = 'x';
			$var['subscription'] = 1;
		} else {
			$var['montant'] = $request->int_var['amount'];
		}

		$var['divers'] = base64_encode( md5( $this->settings['secret'] . $request->invoice->invoice_number ) );

		$var['ref'] = $request->invoice->invoice_number;

		$var['email'] = $request->metaUser->cmsUser->email;
		$var['user'] = $request->metaUser->cmsUser->username;
		$var['pass'] = 'xxxx';

		foreach ( $var as $key => $value ) {
			if ( $key != 'post_url' ) {

			}
		}

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['invoice'] = $post['ref'];

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = false;

		switch ( $post['etat'] ) {
			case 'ok':
				$misc = base64_encode( md5( $this->settings['secret'] . $post['ref'] ) );

				if ( $misc == $post['divers'] ) {
					$response['valid'] = true;
				} else {
					$response['valid'] = false;
				}

				/*$response['amount_paid']		= $post['montant_sent'];
				$response['amount_currency']	= $post['devise_sent'];*/
				break;
			case 'ko':
				$response['valid'] = false;
				break;
			case 'end':
				$response['eot'] = true;
				break;
			case 'refund':
				$response['delete'] = true;
				break;
			case 'chargeback':
				$response['chargeback'] = true;
				break;
		}

		echo("confirmation ok");

		return $response;
	}

}

?>

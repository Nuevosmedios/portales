<?php
/**
 * @version $Id: zipzap.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - ZipZap.co.nz
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_zipzap extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'zipzap';
		$info['longname']		= JText::_('CFG_ZIPZAP_LONGNAME');
		$info['statement']		= JText::_('CFG_ZIPZAP_STATEMENT');
		$info['description']	= JText::_('CFG_ZIPZAP_DESCRIPTION');
		$info['currencies']		= 'AUD,CAD,CHF,DEM,FRF,GBP,HKD,JPY,NZD,SGD,USD,EUR,ZAR';
		$info['cc_list']		= "visa,mastercard,discover,americanexpress,echeck,jcb,dinersclub";
		$info['recurring']		= 0;
		$info['secure']			= 1;

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
		$settings['testmode']			= 0;
		$settings['merchantid']			= "your_merchant_id";
		$settings['currency']			= "NZD";
		$settings['item_name']			= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['customparams']		= '';

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']			= array( "toggle" );
		$settings['merchantid']			= array( "inputC" );
		$settings['currency']			= array( "list_currency" );
		$settings['item_name']			= array( "inputE" );
		$settings['customparams']		= array( 'inputD' );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function checkoutform( $request )
	{
		$var = $this->getCCform( array(), array( 'card_number', 'card_exp_month', 'card_exp_year' ) );

		$var['params']['username'] = array( 'hidden', '', '', $this->settings['merchantid'] );
		$var['params']['TYPE'] = array( 'hidden', '', '', 'P' );

		$rename = array(	'card_number' => 'CARDNUM',
							'cart_exp_month' => 'cc_exp_month',
							'cart_exp_year' => 'cc_exp_year',
							);

		foreach ( $var as $k => $v ) {
			if ( isset( $var['params'][$k] ) ) {
				$var['params'][$v] = $var['params'][$k];

				unset( $var['params'][$k] );
			}
		}

		$var['params']['TYPE'] = array( 'hidden', '', '', $this->settings['currency'] );
		$var['params']['AMOUNT'] = array( 'hidden', '', '', $request->int_var['amount'] );
		$var['params']['EMAIL'] = array( 'hidden', '', '', $request->metaUser->cmsUser->email );
		$var['params']['reference1'] = array( 'hidden', '', '', substr( $request->int_var['invoice'], 1 ) );

		$var['params']['success_url'] = array( 'hidden', '', '', AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=thanks' ) );
		$var['params']['error_url'] = array( 'hidden', '', '', AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=cancel' ) );
		$var['params']['fail_url'] = array( 'hidden', '', '', AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=cancel' ) );
		$var['params']['user_url'] = array( 'hidden', '', '', AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=zipzapnotification' ) );

		$var['aec_alternate_checkout'] = 'https://secure.zipzap.biz/zipzap.php';

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();

		aecDebug($post);
		
		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		
	}
}
?>
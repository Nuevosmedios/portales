<?php
/**
 * @version $Id: smscoin.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - smscoin
 * @copyright 2007-2012 Copyright (C) smscoin.com
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_smscoin extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= "smscoin";
		$info['longname']		= JText::_('CFG_SMSCOIN_LONGNAME');
		$info['statement']		= JText::_('CFG_SMSCOIN_STATEMENT');
		$info['description']	= JText::_('CFG_SMSCOIN_DESCRIPTION');
		$info['currencies']		= "y.e.";
		$info['recurring']		= 0;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['s_purse']		= '1234';
		$settings['password']		= '1234';
		$settings['s_clear_amount']	= '0';
		$settings['s_description']	= 'Payout';
	
		return $settings;
	}

	function backend_settings()
	{
		$settings = array();

		$settings['s_purse']		= array('inputE');
		$settings['password']		= array('inputE');
		$settings['s_clear_amount']	= array('toggle');
		$settings['s_description']	= array('inputE');

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		$var = array();
		$var['post_url']		= 'http://service.smscoin.com/bank/';
		$var['s_purse']			= $this->settings['s_purse'];
		$var['s_order_id']		= ( (int) gmdate('U') );
		$var['invoice']			= $request->invoice->invoice_number;

		if ( is_array( $request->int_var['amount'] ) ) {
			$var['s_amount']	= $request->int_var['amount']['amount'];
		} else {
			$var['s_amount']	= $request->int_var['amount'];
		}

		$var['s_clear_amount']	= $this->settings['s_clear_amount'];
		$var['s_description']	= AECToolbox::rewriteEngineRQ( $this->settings['s_description'], $request );

		$var['s_sign']			= $this->ref_sign(	$var['s_purse'], 
													$var['s_order_id'], 
													$var['s_amount'], 
													$var['s_clear_amount'],
													$var['s_description'], 
													$this->settings['password']
												);
		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['invoice']		= $post['invoice'];
		$response['amount_paid']	= $post['s_amount'];

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 0;

		$hash = $this->ref_sign(	$this->settings['password'],
									$post['s_purse'],
									$post['s_order_id'],
									$post['s_amount'],
									$post['s_clear_amount'],
									$post['s_inv'],
									$post['s_phone']
								);

		if ( $post['s_sign_v2'] == $hash ) {
			$response['valid'] = 1;    
		}

		return $response;
	}

	function ref_sign()
	{
		$params = func_get_args();

		return md5( implode( "::", $params ) );
	}

}

?>

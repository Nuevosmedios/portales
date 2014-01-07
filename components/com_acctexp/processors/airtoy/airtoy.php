<?php
/**
 * @version $Id: airtoy.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Airtoy
 * @copyright 2004-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_airtoy extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'airtoy';
		$info['longname']		= JText::_('CFG_AIRTOY_LONGNAME');
		$info['statement']		= JText::_('CFG_AIRTOY_STATEMENT');
		$info['description']	= JText::_('CFG_AIRTOY_DESCRIPTION');
		$info['currencies']		= 'EUR';
		$info['cc_list']		= "";
		$info['recurring']		= 0;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']		= 0;
		$settings['currency']		= "EUR";
		$settings['phone_number']	= "222222";
		$settings['response']		= "";
		$settings['secret']			= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']		= array( "toggle" );
		$settings['currency']		= array( 'list_currency' );
		$settings['phone_number']	= array( "inputC" );
		$settings['response']		= array( "inputE" );
		$settings['secret']			= array( "inputC" );

		return $settings;
	}

	function CustomPlanParams()
	{
		$p = array();
		$p['smscode_prefix']	= array( 'inputC' );

		return $p;
	}

	function checkoutform( $request )
	{
		$db = &JFactory::getDBO();

		$code = $request->int_var['planparams']['smscode_prefix'] . ' ' . $request->invoice->id;

		$var['params']['explain'] = array( 'p', JText::_('AEC_AIRTOY_PARAMS_EXPLAIN_NAME'), sprintf( JText::_('AEC_AIRTOY_PARAMS_EXPLAIN_DESC'), $code, $this->settings['phone_number'] ) );
		$var['params']['smscode'] = array( 'inputC', JText::_('AEC_AIRTOY_PARAMS_SMSCODE_NAME'), JText::_('AEC_AIRTOY_PARAMS_SMSCODE_DESC'));

		return $var;
	}

	function createRequestXML( $request )
	{
		return true;
	}

	function transmitRequestXML( $xml, $request )
	{
		$return['valid'] = false;

		if ( empty( $request->int_var['params']['smscode'] ) ) {
			$return['error'] = JText::_('AEC_AIRTOY_ERROR_NOCODE');
			return $return;
		}

		$compare = ( strcmp( $request->int_var['params']['smscode'], $request->invoice->params['airtoy_smscode'] ) === 0 );

		if ( $compare ) {
			$return['valid'] = true;
			$return['invoice'] = $request->invoice->invoice_number;
		} else {
			$return['error'] = JText::_('AEC_AIRTOY_CODE_WRONG');
		}

/*
		if ( $settings['testmode'] ) {
			$url = "http://82.113.44.50/";
		} else {
			$url = "http://195.47.87.164/";
		}

		if ( $return['valid'] ) {
			$resp = "OK;" . AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request ) . ";1;;";
			$response = $this->transmitRequest( $url, '', $resp, 443 );
		}
*/
		return $return;
	}


	function parseNotification( $post )
	{
		$db = &JFactory::getDBO();

		$smscode	= aecGetParam('smscode');
		$secret		= aecGetParam('secret');

		$sms = explode( ' ', $smscode );

		if ( !isset( $sms[1] ) ) {
			$sms = explode( '+', $smscode );
		}

		$invoice = new Invoice();
		$invoice->load( $sms[1] );

		if ( $invoice->id ) {
			$returncode = rand( 111111, 999999 );

			$invoice->addParams( array( 'airtoy_smscode' => $returncode ) );
			$invoice->check();
			$invoice->store();

			if ( !empty( $this->settings['secret'] ) && !empty( $secret ) ) {
				if ( $this->settings['secret'] != $secret ) {
					exit;
				}

			}
/*
			if ( $this->settings['testmode'] ) {
				$url = "http://82.113.44.50/";
			} else {
				$url = "http://195.47.87.164/";
			}

			$response = $this->transmitRequest( $url, '', $resp, 443 );
*/

			$resp = "OK;" . $returncode . ";1";

			echo $resp;
		}
		exit;
	}

}
?>
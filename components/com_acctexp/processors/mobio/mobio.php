<?php
/**
 * @version $Id: mobio.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Mobio.bg
 * @copyright 2004-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@skore.de> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.2 http://www.gnu.org/licenses/old-licenses/gpl-2.0.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_mobio extends XMLprocessor {

	function info()
	{
		$info = array();
		$info['name']			= 'mobio';
		$info['longname']		= JText::_('CFG_MOBIO_LONGNAME');
		$info['statement']		= JText::_('CFG_MOBIO_STATEMENT');
		$info['description']	= JText::_('CFG_MOBIO_DESCRIPTION');
		$info['currencies']		= 'EUR';
		$info['cc_list']		= "";
		$info['recurring']		= 0;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['servID']				= '00';
		$settings['user_message']		= JText::_('CFG_MOBIO_STDMSG');

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['servID']			= array( 'inputC' );
		$settings['user_message']	= array( 'inputD' );

		return $settings;
	}

	function checkoutAction( $request )
	{
		$return .= '<p>' . $this->settings['user_message'] . '</p>';
		$return .= '<form action="' . AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=checkout', $this->info['secure'] ) . '" method="post">' . "\n";
		$return .= '<input type="hidden" name="invoice" value="' . $request->int_var['invoice'] . '" />' . "\n";
		$return .= '<input type="hidden" name="userid" value="' . $request->metaUser->userid . '" />' . "\n";
		$return .= '<input type="text" name="smscode" value="" />' . "\n";
		$return .= '<button type="submit" class="button aec-btn btn btn-primary" id="aec-checkout-btn">' . aecHTML::Icon( 'shopping-cart' ) . JText::_('BUTTON_CHECKOUT') . '</button>';
		$return .= '</form>';

		return $return;
	}

	function checkoutProcess( $request, $InvoiceFactory )
	{
		$response['valid'] = false;

		if ( $this->mobioCheckcode($request->int_var['params']['smscode']) ) {
			$InvoiceFactory->invoice->addParams( array( 'smscode' => $request->int_var['params']['smscode']) );
			$InvoiceFactory->invoice->storeload();

			$response['valid'] = true;

			$response['invoice'] = $request->invoice->invoice_number;
		} else {
			$response['error'] = JText::_('CFG_MOBIO_INVALID_SMSCODE');
		}

		return $this->checkoutResponse( $request, $response, $InvoiceFactory );
	}

	function mobioCheckcode( $code )
	{
		$res_lines = file( "http://www.mobio.bg/code/checkcode.php?servID=" . $this->settings['servID'] . "&code=" . $code );

		if ( $res_lines ) {
			if ( strpos($res_lines[0], "PAYBG=OK") != false ) {
				return true;
			}
		} else {
			aecDebug( "Unable to connect to mobio.bg server." );
		}

		return false;
	}

}
?>

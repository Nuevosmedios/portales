<?php
/**
 * @version $Id: scb_nsips.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Siam Commercial Bank - NSIPS
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_scb_nsips extends URLprocessor
{
	function info()
	{
		$info = array();
		$info['name']				= 'scb_nsips';
		$info['longname']			= JText::_('CFG_SCB_NSIPS_LONGNAME');
		$info['statement']			= JText::_('CFG_SCB_NSIPS_LONGNAME');
		$info['description'] 		= JText::_('CFG_SCB_NSIPS_DESCRIPTION');
		$info['currencies']			= 'THB,USD,GBP,EUR,JPY,AUD,CAD,DKK,HKD,NZD,SGD,CHF,SEK';
		$info['languages']			= 'GB,DE,FR,IT,ES,US,NL';
		$info['cc_list']			= 'visa,mastercard,eurocard';
		$info['recurring']			= 2;
		$info['recurring_buttons']	= 2;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['merchant_id']	= 'webmaster';
		$settings['terminal_id']	= 'content_id';
		$settings['secret']			= 'secret';
		$settings['type']			= 1;

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['webmaster_id']	= array( 'inputC' );
		$settings['content_id']		= array( 'inputC' );
		$settings['secret']			= array( 'inputC' );

		$settings['customparams']	= array( 'inputD' );

 		$typelist = array();
		$typelist[0] = JHTML::_('select.option', 1, JText::_('CFG_NETDEBIT_TYPE_LISTITEM_ELV') );
		$typelist[1] = JHTML::_('select.option', 2, JText::_('CFG_NETDEBIT_TYPE_LISTITEM_CC') );

		$settings['lists']['type']	= JHTML::_( 'select.genericlist', $typelist, 'scb_nsips_type', 'size="1"', 'value', 'text', $this->settings['type'] );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		$ppParams = $request->metaUser->meta->getProcessorParams( $request->parent->id );

		//$var['item_number']		= $request->metaUser->cmsUser->id;

		if ( !empty( $ppParams->customerid ) ) {
			$cust = $ppParams->customerid;
		} else {
			$cust = '';
		}

		$var['mid']			= $this->settings['merchant_id'];
		$var['terminal']	= $this->settings['terminal_id'];
		$var['version']		= "1.0";
		$var['command']		= "CRAUTH";
		$var['ref_no']		= $this->settings['type']; //1 = Lastschrift, 2 = Kreditkarte

		if ( is_array( $request->int_var['amount'] ) ) {

			$var['AboAmount'] = $request->int_var['amount']['amount3'];

			$period = $request->int_var['amount']['period3'];

			switch ( $request->int_var['amount']['unit3'] ) {
				// Only allows for Months or Years, so we have to go for the smallest larger amount of time
				case 'D': case 'W': $period = 1; // no break; - Leaps over to months to set the unit
				case 'M': $unit = 5; break;
				case 'Y': $unit = 6; break;
				default: $unit = 3; break;
			}

			$var['AboTermType'] = $unit;
			$var['AboTermValue'] = $period;
		} else {
			$var['Amount'] = $request->int_var['amount'];
			$var['TermType'] = 5;
			$var['TermValue'] = 1;
			$var['AboTermType'] = 0;
		}

		$var['post_url']	= "http://www.scb_nsips.de/pay/index.php?";

		return $var;
	}

	function parseNotification( $post )
	{
		$db = &JFactory::getDBO();

		$response = array();
		$response['invoice']			= $post['Ref_no'];
		$response['amount_paid']		= $post['amount'];
		$response['amount_currency']	= $post['Cur_abbr'];

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 0;

		switch ( $post['payment_status'] ) {
			case '002':
				$response['valid'] = 1;
				break;
			case '003':
				$response['error'] = "Host Reject";
				break;
			case '006':
				$response['error'] = "General Error";
				break;
			case '007':
			case '008':
				$response['error'] = "SIPs is down";
				break;
		}

		if ( $post['payment_status'] == "003" ) {
			$response['valid'] = 1;
		}

		return $response;
	}

}
?>

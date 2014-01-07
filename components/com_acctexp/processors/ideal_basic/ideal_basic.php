<?php
/**
 * @version $Id: ideal_basic.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - iDeal Basic
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_ideal_basic extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'ideal_basic';
		$info['longname']				= JText::_('CFG_IDEAL_BASIC_LONGNAME');
		$info['statement']				= JText::_('CFG_IDEAL_BASIC_STATEMENT');
		$info['description']			= JText::_('CFG_IDEAL_BASIC_DESCRIPTION');
		$info['currencies']				= 'EUR';
		$info['languages']				= 'NL';
		$info['cc_list']				= '';
		$info['recurring']				= 0;
		$info['notify_trail_thanks']	= 1;

		return $info;
	}

	function getLogoFilename()
	{
		return 'ideal.png';
	}

	function settings()
	{
		$settings = array();
		$settings['merchantid']		= "123456789";
		$settings['testmode']		= 0;
		$settings['currency']		= 'EUR';
		$settings['testmodestage']	= 1;
		$settings['bank']			= "ing";
		$settings['subid']			= "0";
		$settings['language']		= "NL";
		$settings['key']			= "key";
		$settings['description']	= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['customparams']	= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['merchantid']			= array( 'inputC' );
		$settings['testmode']			= array( 'toggle' );
		$settings['currency']			= array( 'list_currency' );
		$settings['testmodestage']		= array( 'inputC' );
		$settings['bank']				= array( 'list' );
		$settings['subid']				= array( 'inputC' );
		$settings['language']			= array( 'list_language' );
		$settings['key']				= array( 'inputC' );
		$settings['description']		= array( 'inputE' );
		$settings['customparams']		= array( 'inputD' );

 		$banks = array();
		$banks[] = JHTML::_('select.option', "ing", "ING" );
		$banks[] = JHTML::_('select.option', "rabo", "Rabobank" );

		if ( !empty( $this->settings['bank'] ) ) {
			$ba = $this->settings['bank'];
		} else {
			$ba = "ing";
		}

		$settings['lists']['bank']	= JHTML::_( 'select.genericlist', $banks, 'bank', 'size="2"', 'value', 'text', $ba );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		if ( $this->settings['testmode'] ) {
			$sub = 'idealtest';
		} else {
			$sub = 'ideal';
		}

		if ( $this->settings['bank'] == 'ing' ) {
			$var['post_url']		= "https://" . $sub . ".secure-ing.com/ideal/mpiPayInitIng.do";
		} else {
			$var['post_url']		= "https://" . $sub . ".rabobank.nl/ideal/mpiPayInitRabo.do";
		}

		$var['merchantID']			= $this->settings['merchantid'];
		$var['subID']				= $this->settings['subid'];
		$var['purchaseID']			= (int) $request->invoice->id;

		if ( $this->settings['testmode'] ) {
			$var['post_url']		= "https://" . $sub . ".rabobank.nl/ideal/mpiPayInitRabo.do";

			$var['amount']			= max( 1, min( 7, (int) $this->settings['testmodestage'] ) ) . '00';
		} else {
			$var['amount']			= (int) ( $request->int_var['amount'] * 100 );
		}

		$var['currency']			= $this->settings['currency'];
		$var['language']			= strtolower( $this->settings['language'] );
		$var['description']			= substr( $this->settings['description'], 0, 32);
		$var['itemNumber1']			= $request->metaUser->userid;
		$var['itemDescription1']	= substr( $this->settings['description'], 0, 32);
		$var['itemQuantity1']		= 1;
		$var['itemPrice1']			= $var['amount'];
		$var['paymentType']			= 'ideal';
		$var['validUntil']			= date('Y-m-d\TG:i:s\Z', strtotime('+1 hour'));

		$shastring = $this->settings['key']
					.$var['merchantID']
					.$var['subID']
					.$var['amount']
					.$var['purchaseID']
					.$var['paymentType']
					.$var['validUntil']
					.$var['itemNumber1']
					.$var['itemDescription1']
					.$var['itemQuantity1']
					.$var['itemPrice1'];

		$shastring = html_entity_decode( $shastring );

		$shastring = str_replace( array("\t", "\n", "\r", " "), '', $shastring );

		$var['hash']				= sha1( $shastring );
		$var['urlSuccess']			= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=ideal_basicnotification' );
		$var['urlCancel']			= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=cancel' );
		$var['urlError']			= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=cancel' );
		$var['urlService']			= AECToolbox::deadsureURL( 'index.php' );

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['valid'] = 0;

		$response['null'] = true;
		$response['error'] = "Ideal Basic does not support automatic payment notification. Please clear invoice in user profile after checking for new payments.";

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		return $response;
	}

}

?>

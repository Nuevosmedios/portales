<?php
/**
 * @version $Id: sofortueberweisung.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Sofortüberweisung.de
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_sofortueberweisung extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'sofortueberweisung';
		$info['longname']		= JText::_('CFG_SOFORTUEBERWEISUNG_LONGNAME');
		$info['statement']		= JText::_('CFG_SOFORTUEBERWEISUNG_STATEMENT');
		$info['description']	= JText::_('CFG_SOFORTUEBERWEISUNG_DESCRIPTION');
		$info['currencies']		= 'EUR,GBP,CHF';
		$info['languages']		= array( 'DE', 'GB', 'NL', 'FR' );
		$info['cc_list']		= 'visa,mastercard,discover,americanexpress,echeck,giropay';
		$info['recurring']		= 0;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['user_id']				= '123456';
		$settings['project_id']				= '123456';
		$settings['project_password']		= '';
		$settings['hash_encoding']			= 'SHA1';
		$settings['notification_password']	= '';
		$settings['currency']				= 'EUR';
		$settings['language']				= 'DE';
		$settings['item_name_1']			= "";
		$settings['item_name_2']			= "";
		$settings['customparams']			= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();

		$settings['user_id']				= array( 'inputC' );
		$settings['project_id']				= array( 'inputC' );
		$settings['project_password']		= array( 'inputC' );
		$settings['hash_encoding']			= array( 'list' );
		$settings['notification_password']	= array( 'inputC' );
		$settings['currency']				= array( 'list_currency' );
		$settings['language']				= array( 'list_language' );
		$settings['item_name_1']			= array( 'inputE' );
		$settings['item_name_2']			= array( 'inputE' );
		$settings['customparams']			= array( 'inputD' );

 		$encoding = array();
		$encoding[] = JHTML::_('select.option', "MD5", "MD5" );
		$encoding[] = JHTML::_('select.option', "SHA1", "SHA1" );

		if ( !empty( $this->settings['hash_encoding'] ) ) {
			$enc = $this->settings['hash_encoding'];
		} else {
			$enc = "SHA1";
		}

		$settings['lists']['hash_encoding']	= JHTML::_('select.genericlist', $encoding, 'hash_encoding', 'size="2"', 'value', 'text', $enc );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		$var['post_url']		= 'https://www.sofortueberweisung.de/payment/start';

		$var['user_id']			= $this->settings['user_id'];
		$var['project_id']		= $this->settings['project_id'];
		$var['amount']			= $request->int_var['amount'];

		$var['reason_1']		= trim( substr( AECToolbox::rewriteEngineRQ( $this->settings['item_name_1'], $request ), 0, 27 ) );
		$var['reason_2']		= trim( substr( AECToolbox::rewriteEngineRQ( $this->settings['item_name_2'], $request ), 0, 27 ) );

		$var['user_variable_0']	= $request->invoice->invoice_number;

		$var['return']			= $request->int_var['return_url'];
		$var['currency_id']		= $this->settings['currency'];
		$var['language_id']		= $this->settings['language'];

		$prehash =	$var['user_id'] . '|'
					. $var['project_id'] . '|'
					. '||||'
					. $var['amount'] . '|'
					. $var['currency_id'] . '|'
					. $var['reason_1'] . '|'
					. $var['reason_2'] . '|'
					. $var['user_variable_0'] . '|'
					. '|||||'
					. $this->settings['project_password']
					;

		$var['hash'] =	$this->getHash( $prehash );

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		if ( empty( $post['transaction'] ) ) {
			$response['invoice']			= aecGetParam( 'user_variable_0', '', true, array( 'word', 'badchars' ) );
			$response['amount_currency']	= aecGetParam( 'currency_id', '', true, array( 'word', 'badchars' ) );
			$response['amount_paid']		= aecGetParam( 'amount', '', true, array( 'word', 'badchars' ) );
		} else {
			$response['invoice']			= $post['user_variable_0'];
			$response['amount_currency']	= $post['currency_id'];
			$response['amount_paid']		= $post['amount'];
		}

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{

		$values = array(	'transaction','user_id','project_id',
							'sender_holder','sender_account_number','sender_bank_code','sender_bank_name','sender_bank_bic','sender_iban','sender_country_id',
							'recipient_holder','recipient_account_number','recipient_bank_code','recipient_bank_name','recipient_bank_bic',
							'recipient_iban','recipient_country_id',
							'international_transaction','amount','currency_id',
							'reason_1','reason_2','security_criteria',
							'user_variable_0','user_variable_1','user_variable_2','user_variable_3','user_variable_4', 'user_variable_5',
							'created'
						);

		$getmode = empty( $post['transaction'] );

		if ( $getmode ) {
			$status = aecGetParam( 'status' );

			if ( !empty( $status ) ) {
				$values[] = 'status';
				$values[] = 'status_modified';
			}
		} else {
			if ( isset( $post['status'] ) ) {
				$values[] = 'status';
				$values[] = 'status_modified';
			}
		}

		foreach ( $values as $value ) {
			if ( $getmode ) {
				$post[$value] = aecGetParam( $value );
			}

			$array[$value] = $post[$value];
		}

		$array['notification_password'] = $this->settings['notification_password'];

		$hash = $this->getHash( implode( '|', $array ) );

		$response['valid'] = 0;

		if ( $getmode ) {
			$ohash = aecGetParam( 'hash' );
		} else {
			$ohash = $post['hash'];
		}

		if ( empty( $ohash ) || ( $hash != $ohash ) ) {
			$response['pending_reason'] = 'hash value verification failed: ' . $hash . ' != ' . $ohash;
		} else {
			$response['valid'] = 1;
		}

		return $response;
	}

	function getHash( $string )
	{
		if ( $this->settings['hash_encoding'] == 'SHA1' ) {
			return sha1( $string );
		} else {
			return md5( $string );
		}

	}

}
?>

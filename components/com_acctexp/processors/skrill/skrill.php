<?php
/**
 * @version $Id: skrill.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Skrill
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_skrill extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']				= 'skrill';
		$info['longname']			= JText::_('CFG_SKRILL_LONGNAME');
		$info['statement']			= JText::_('CFG_SKRILL_STATEMENT');
		$info['description']		= JText::_('CFG_SKRILL_DESCRIPTION');
		$info['currencies']			= 'EUR,USD,GBP,AUD,CAD,JPY,NZD,CHF,HKD,SGD,SEK,DKK,PLN,NOK,HUF,CZK,ZAR';
		$info['languages']			= 'EN,DE,ES,FR,IT,PL,GR,RO,RU,TR,CN,CZ,NL.';
		$info['cc_list']			= 'visa,mastercard';
		$info['recurring']			= 2;

		return $info;
	}

	function settings()
	{
		$app = JFactory::getApplication();

		$settings = array();

		$settings['pay_to_email']			= '';
		$settings['secret_word']			= '';
		$settings['recipient_description']	= $app->getCfg( 'sitename' );
		$settings['logo_url'] 				= '';
		$settings['language'] 				= 'EN';
		$settings['payment_methods']		= array( 'ACC' );
		$settings['hide_login'] 			= 1;
		$settings['currency'] 				= 'USD';
		$settings['confirmation_note']		= "Thank you for subscribing on " . $app->getCfg( 'sitename' ) . "!";
		$settings['item_name']				= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['customparams']			= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();

		$settings['pay_to_email']			= array( 'inputC');
		$settings['secret_word']			= array( 'inputC');
		$settings['recipient_description']	= array( 'inputE');
		$settings['logo_url']				= array( 'inputE');
		$settings['language'] 				= array( 'list_language' );
		$settings['payment_methods']		= array( 'list');
		$settings['hide_login']				= array( 'toggle');
		$settings['currency'] 				= array( 'list_currency' );
		$settings['confirmation_note']		= array( 'inputE');
		$settings['item_name']				= array( 'inputE');
		$settings['customparams']			= array( 'inputD' );

		$methods = array(	'All Card Types' => 'ACC',
							'Visa' => 'VSA',
							'MasterCard' => 'MSC',
							'Visa Delta/Debit (UK)' => 'VSD',
							'Visa Electron' => 'VSE',
							'Maestro (UK, Spain & Austria)' => 'MAE',
							'Solo (UK)' => 'SLO',
							'American Express' => 'AMX',
							'Diners' => 'DIN',
							'JCB' => 'JCB',
							'Laser (Rep. of Ireland)' => 'LSR',
							'Carte Bleue' => 'GCB',
							'Dankort' => 'DNK',
							'PostePay' => 'PSP',
							'CartaSi' => 'CSI',
							'Giropay (Germany)' => 'GIR',
							'Direct Debit (Germany)' => 'DID',
							'Sofortueberweisung' => 'SFT',
							'eNETS (Singapore)' => 'ENT',
							'Nordea Solo (Sweden)' => 'EBT',
							'Nordea Solo (Finland)' => 'SO2',
							'iDEAL (Netherlands)' => 'IDL',
							'EPS (Netpay) Austria' => 'NPY',
							'POLi (Australia)' => 'PLI',
							'All Polish Banks' => 'PWY',
							'ING Bank Śląski' => 'PWY5',
							'PKO BP (PKO Inteligo)' => 'PWY6',
							'Multibank (Multitransfer)' => 'PWY7',
							'Lukas Bank' => 'PWY14',
							'Bank BPH' => 'PWY15',
							'InvestBank' => 'PWY17',
							'PeKaO S.A.' => 'PWY18',
							'Citibank handlowy' => 'PWY19',
							'Bank Zachodni WBK (Przelew24)' => 'PWY20',
							'BGŻ' => 'PWY21',
							'Millenium' => 'PWY22',
							'mBank (mTransfer)' => 'PWY25',
							'Płacę z Inteligo' => 'PWY26',
							'Bank Ochrony Środowiska' => 'PWY28',
							'Nordea' => 'PWY32',
							'Fortis Bank' => 'PWY33',
							'Deutsche Bank PBC S.A.' => 'PWY36',
							'ePay.bg (Bulgaria)' => 'EPY'
							);

		$pmethods = array();
		$pmethodssel = array();
		foreach ( $methods as $name => $key ) {
			$pmethods[] = JHTML::_('select.option', $key, $name );

			if ( !empty( $this->settings['payment_methods'] )) {
				if ( in_array( $key, $this->settings['payment_methods'] ) ) {
					$pmethodssel[] = JHTML::_('select.option', $key, $name );
				}
			}
		}

		$settings['lists']['payment_methods'] = JHTML::_( 'select.genericlist', $pmethods, 'payment_methods[]', 'size="8" multiple="multiple"', 'value', 'text', $pmethodssel );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		$var['post_url']				= 'https://www.skrill.com/app/payment.pl';
		$var['pay_to_email']			= $this->settings['pay_to_email'];
		$var['recipient_description']	= $this->settings['recipient_description'];
		$var['logo_url']				= $this->settings['logo_url'];
		$var['transaction_id']			= $request->invoice->invoice_number;

		$var['return_url']				= $request->int_var['return_url'];
		$var['cancel_url']				= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=cancel' );
		$var['status_url']				= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=skrillnotification' );

		$var['language']				= $this->settings['language'];

		if ( empty( $this->settings['payment_methods'] ) ) {
			$var['payment_methods']		= "";
		} else {
			$var['payment_methods']		= implode( ',', $this->settings['payment_methods'] );
		}

		$var['hide_login']				= $this->settings['hide_login'];
		$var['pay_from_email']			= $request->metaUser->cmsUser->email;

		if ( is_array( $request->int_var['amount'] ) ) {
			$puf = $this->convertPeriodUnit( $request->int_var['amount']['unit3'], $request->int_var['amount']['period3'] );

			$var['rec_amount']					= $request->int_var['amount'];
			$var['rec_period']					= $puf['period'];
			$var['rec_cycle']					= $puf['unit'];
		} else {
			$var['amount']					= $request->int_var['amount'];
		}

		$var['detail1_description']		= AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );
		$var['detail1_text']			= $request->metaUser->cmsUser->id;
		$var['currency']				= $this->settings['currency'];
		$var['confirmation_note']		= $this->settings['confirmation_note'];

		return $var;
	}

	function convertPeriodUnit( $unit, $period )
	{
		$return = array();
		$return['period'] = $period;
		switch ( $unit ) {
			case 'D':
				$return['unit'] = 'day';
				break;
			case 'W':
				$return['unit'] = 'day';
				$return['period'] = $period*7;
				break;
			case 'M':
				$return['unit'] = 'month';
				break;
			case 'Y':
				$return['unit'] = 'Year';
				break;
		}

		return $return;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['invoice']			= $post['transaction_id'];
		$response['amount_paid']		= $post['mb_amount'];
		$response['amount_currency']	= $post['mb_currency'];

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = false;

		$md5sig = md5( $post['merchant_id'] . $post['transaction_id'] . strtoupper( md5( $this->settings['secret_word'] ) ) . $post['mb_amount'] . $post['mb_currency'] . $post['status'] );

		if ( ( $post['status'] == '2' ) && ( strtoupper( $md5sig ) == $post['md5sig'] ) ) {
			$response['valid'] = true;
		}

		return $response;
	}

}

?>

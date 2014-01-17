<?php
/**
 * @version $Id: moip.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Moip
 * @copyright 2007-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_moip extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'moip';
		$info['longname']				= JText::_('CFG_MOIP_LONGNAME');
		$info['statement']				= JText::_('CFG_MOIP_STATEMENT');
		$info['description']			= JText::_('CFG_MOIP_DESCRIPTION');
		$info['currencies']				= 'EUR,USD,GBP,AUD,CAD,JPY,NZD,CHF,HKD,SGD,SEK,DKK,PLN,NOK,HUF,CZK,MXN,ILS,BRL,MYR,PHP,TWD,THB,ZAR';
		$info['languages']				= AECToolbox::getISO639_1_codes();
		$info['cc_list']				= 'visa,mastercard,discover,americanexpress,echeck,giropay';
		$info['recurring']				= 0;
		$info['notify_trail_thanks']	= 1;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['business']		= 'your@moip@account.com';
		$settings['testmode']		= 0;
		$settings['item_name']		= '[[plan_name]]';
		$settings['item_desc']		= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();

		$settings['business']				= array( 'inputC' );
		$settings['testmode']				= array( 'toggle' );
		$settings['item_name']				= array( 'inputE' );
		$settings['item_desc']				= array( 'inputE' );
		$settings['aec_insecure']			= true;

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		if ( $this->settings['testmode'] ) {
			$var['post_url']	= 'https://desenvolvedor.moip.com.br/sandbox/PagamentoMoIP.do';
		} else {
			$var['post_url']	= 'https://www.moip.com.br/PagamentoMoIP.do';
		}

		$var['valor']			= (int) ( $request->int_var['amount'] * 100 );

		$var['id_carteira']		= $this->settings['business'];
		$var['id_transacao']	= $request->invoice->invoice_number;

		$var['url_retorno']		= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=moipnotification' );

		$var['descricao'] 		= AECToolbox::rewriteEngineRQ( $this->settings['item_desc'], $request );
		$var['nome']			= AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );

		$var['pagador_nome']	= $request->metaUser->cmsUser->name;
		$var['pagador_email']	= $request->metaUser->cmsUser->email;

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['invoice'] = $post['id_transacao'];

		$response['amount_paid'] = $post['valor'] / 100;

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 0;

		// Check the payment_status
		switch( $post['status_pagamento'] ) {
			case 2: // in progress
			case 3: // not yet paid
				$response['pending']		= 1;
				break;
			case 6: // fraud check
				$response['pending']		= 1;
				$response['pending_reason']	= 'fraud check';
				break;

			case 1: // Authorized
			case 4: // Completed
				$response['valid']			= 1;
				break;

			case 5: // Cancelled
				$response['cancel']			= 1;
				break;
			case 7: // Refunded
				$response['delete']			= 1;
				break;
		}

		return $response;
	}

}
?>

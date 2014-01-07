<?php
/**
 * @version $Id: beanstream.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Beanstream
 * @copyright 2007-2008 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_beanstream extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'beanstream';
		$info['longname']		= JText::_('CFG_BEANSTREAM_LONGNAME');
		$info['statement']		= JText::_('CFG_BEANSTREAM_STATEMENT');
		$info['description']	= JText::_('CFG_BEANSTREAM_DESCRIPTION');
		$info['currencies']		= 'USD,EUR,GBP,CAD,AUD,BGN,CZK,DKK,EEK,HKD,HUF,LTL,MYR,NZD,NOK,PLN,ROL,SGD,ZAR,SEK,CHF';
		$info['languages']		= AECToolbox::getISO639_1_codes();
		$info['cc_list']		= 'visa,mastercard,american express,discovery';
		$info['recurring']		= 0;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']		= 0;
        $settings['scEnabled']		= 0;
		$settings['login']			= 'Merchant Login ID';
        $settings['termUrl']		= "";
		$settings['currency']		= 'USD';
		$settings['item_name']		= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['customparams']	= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']		= array( 'toggle' );
        $settings['scEnabled']		= array( 'toggle' );
		$settings['login']			= array( 'inputC' );
        $settings['termUrl']		= array( 'inputC' );
		$settings['currency']		= array( 'list_currency' );
		$settings['item_name']		= array( 'inputE' );
		$settings['customparams']	= array( 'inputD' );

		$settings					= AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

    function checkoutform( $request )
	{
		$var = $this->getCCform( array(), array( 'card_number', 'card_exp_month', 'card_exp_year', 'card_cvv2' ) );

        $this->settings['scEnabled'] ? trim( $request->int_var['params']['cardVV2'] ) : '' ;

		$var['params']['billName']      = array( 'inputC', JText::_('AEC_BEANSTREAM_PARAMS_BILLNAME_NAME'), JText::_('AEC_BEANSTREAM_PARAMS_BILLNAME_NAME'), $request->metaUser->cmsUser->name );
        $var['params']['billAddress']   = array( 'inputC', JText::_('AEC_BEANSTREAM_PARAMS_BILLADDRESS_NAME') );
        $var['params']['billCity']      = array( 'inputC', JText::_('AEC_BEANSTREAM_PARAMS_BILLCITY_NAME') );
        $var['params']['billState']     = array( 'inputC', JText::_('AEC_BEANSTREAM_PARAMS_BILLSTATE_NAME') );
		$var['params']['billZip']       = array( 'inputC', JText::_('AEC_BEANSTREAM_PARAMS_BILLZIP_NAME') );
		$var['params']['billCountry']   = array( 'inputC', JText::_('AEC_BEANSTREAM_PARAMS_BILLCOUNTRY_NAME') );
        $var['params']['billEmail']     = array( 'inputC', JText::_('AEC_BEANSTREAM_PARAMS_BILLEMAIL_NAME'), JText::_('AEC_BEANSTREAM_PARAMS_BILLEMAIL_NAME'), $request->metaUser->cmsUser->name );
        $var['params']['billPhone']     = array( 'inputC', JText::_('AEC_BEANSTREAM_PARAMS_BILLPHONE_NAME') );

		return $var;
	}

	function createGatewayLink( $request )
	{
		$var['post_url']	= "https://www.beanstream.com/scripts/process_transaction.asp";

		$var['merchant_id']		    = trim($this->settings['login']);
		$var['requestType']			= 'backend';
		$var['trnOrderNumber']		= $request->invoice->invoice_number;
		$var['trnAmount']			= $request->int_var['amount'];
        $var['trnCardOwner']        = trim( $request->int_var['params']['billName'] );
        $var['trnCardNumber']       = trim( $request->int_var['params']['cardNumber'] );
        $var['trnExpMonth']         = $request->int_var['params']['expirationMonth'];
        $var['trnExpYear']          = $request->int_var['params']['expirationYear'];
        $var['ordName']             = $request->metaUser->cmsUser->name;
        $var['ordEmailAddress']     = $request->int_var['params']['billAddress'];
        $var['ordPhoneNumber']      = $request->int_var['params']['billZip'];
        $var['ordAddress1']         = $request->int_var['params']['billAddress'];
        $var['ordCity']             = $request->int_var['params']['billCity'];
        $var['ordProvince']         = $request->int_var['params']['billState'];
        $var['ordPostalCode']       = $request->int_var['params']['billZip'];
        $var['ordCountry']          = $request->int_var['params']['billCountry'];
        $var['termURL']             = trim($this->settings['termUrl']);
        $var['scEnabled']           = $this->settings['scEnabled'] ? '' :'';
        $var['errorPage']           ='https//:www.merchantserver.com/auth_script.asp';//place holder example

        if ( $this->settings['scEnabled'] ) {
            $var['trnCardCvd']      = trim( $request->int_var['params']['cardVV2'] );
        }

		return $var;
	}

	function parseNotification( $post )
	{
		$errorMessage	    = $post['errorMessage'];
		$errorFields	    = $post['errorFields'];
        $trnApproved        = $post['trnApproved'];
        $trnId              = $post['trnId'];
        $messageId          = $post['messageId'];
        $messageText        = $post['messageText'];
        $authCode           = $post['authCode'];
        $responseType       = $post['responseType'];
        $trnAmount          = $post['trnDate'];
        $trnDate            = $post['errorFields'];
        $trnOrderNumber     = $post['trnOrderNumber'];
        $trnLanguage        = $post['trnLanguage'];
        $trnCustomerName    = $post['trnCustomerName'];
        $trnEmailAddress    = $post['trnEmailAddress'];
        $trnPhoneNumber     = $post['trnPhoneNumber'];
        $avsProcessed       = $post['avsProcessed'];
        $avsId              = $post['avsId'];
        $avsResult          = $post['avsResult'];
        $avsAddrMatch       = $post['avsAddrMatch'];
        $avsPostalMatch     = $post['avsPostalMatch'];
        $avsMessage         = $post['avsMessage '];
        $cardType           = $post['cardType'];
        $rnType             = $post['rnType'];
        $ref1               = $post['ref1'];
        $ref2               = $post['ref2'];
        $ref3               = $post['ref3'];
        $ref4               = $post['ref4'];
        $ref5               = $post['ref5'];


		$response = array();
		$response['invoice']			= $post['trnOrderNumber'];
		$response['amount_currency']	= $post['mc_currency'];

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = false;
		
		if ( !empty($post['errorMessage']) || !empty($post['errorFields'] ) ) {
			$messages= ($post['errorMessage']) ? html_entity_decode($post['errorMessage']) : '';
            $messages .= ($post['errorFields']) ? html_entity_decode($post['errorFields']) : '';
            $response['error'] = $messages;
        } else {
			if ( $post['trnApproved'] == 1 ) {
                $response['valid'] = true;
			} else {
				$response['error'] = "Unknown Error Occurred!";
			}
		}

		return $response;
	}
}
?>

<?php
/**
 * @version $Id: worldpay.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Worldpay
 * @copyright 2007-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.2 http://www.gnu.org/licenses/old-licenses/gpl-2.0.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_cardsave extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']				= 'cardsave';
		$info['longname']			= JText::_('CFG_CARDSAVE_LONGNAME');
		$info['statement']			= JText::_('CFG_CARDSAVE_STATEMENT');
		$info['description']		= JText::_('CFG_CARDSAVE_DESCRIPTION');
		$info['currencies']			= AECToolbox::aecCurrencyField( true, true, true, true );
		$info['cc_list']			= 'visa,mastercard';
		$info['recurring']			= 0;
		$info['notify_trail_thanks']	= 1;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode'] 			= 0;
		$settings['MerchantID']			= 'MerchantID';
		$settings['Password'] 			= '';
		$settings['PreSharedKey']		= '';
		$settings['currency'] 			= 'GBP';
		$settings['item_name']			= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['InstantCapture']		= 1;
		$settings['CV2Mandatory'] 		= 1;
		$settings['Address1Mandatory']	= '';
		$settings['CityMandatory'] 		= '';
		$settings['PostCodeMandatory']	= '';
		$settings['StateMandatory'] 	= '';
		$settings['CountryMandatory'] 	= '';
		

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']			= array( 'toggle');
		$settings['MerchantID']			= array( 'inputC');
		$settings['Password']			= array( 'inputC');
		$settings['PreSharedKey']		= array( 'inputC' );
		$settings['currency']			= array( 'list_currency');
		$settings['item_name']			= array( 'inputE');
		$settings['InstantCapture']		= array( 'toggle');
		$settings['CV2Mandatory'] 		= array( 'toggle');
		$settings['Address1Mandatory']	= array( 'toggle');
		$settings['CityMandatory'] 		= array( 'toggle');
		$settings['PostCodeMandatory']	= array( 'toggle');
		$settings['StateMandatory'] 	= array( 'toggle');
		$settings['CountryMandatory'] 	= array( 'toggle');
		
 		$rewriteswitches			= array( 'cms', 'user', 'expiration', 'subscription', 'plan');
		$settings = AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		if ( $this->settings['testmode'] ) {
			$url = 'https://test.cardsaveonlinepayments.com/Pages/PublicPages/PaymentForm.aspx';
		} else {
			$url = 'https://mms.cardsaveonlinepayments.com/Pages/PublicPages/PaymentForm.aspx';
		}

		$var = array();
		$var['MerchantID']			= $this->settings['MerchantID'];
		$var['Amount']				= $request->int_var['amount'] * 100;
		$var['CurrencyCode']		= AECToolbox::aecNumCurrency( $this->settings['currency'] );
		$var['OrderID']				= $request->invoice->id;
		$var['TransactionType']		= 'SALE';
		$var['TransactionDateTime']	= date("Y-m-d H:i:s P");		
		$var['CallbackURL']			= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=cardsavenotification', false, true );
		$var['OrderDescription']	= AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );
		$var['CustomerName']		=  '';
  		$var['Address1']			=  '';
  		$var['Address2']			=  '';
  		$var['Address3']			=  '';
  		$var['Address4']			=  '';
  		$var['City']				=  '';
  		$var['State']				=  '';
  		$var['PostCode']			=  '';
  		$var['CountryCode']			= '826';
		$var['CV2Mandatory']		= $this->settings['CV2Mandatory'] ? 'true' : 'false';
		$var['Address1Mandatory']	= $this->settings['Address1Mandatory'] ? 'true' : 'false';
		$var['CityMandatory']		= $this->settings['CityMandatory'] ? 'true' : 'false';
		$var['PostCodeMandatory']	= $this->settings['PostCodeMandatory'] ? 'true' : 'false';
		$var['StateMandatory']		= $this->settings['StateMandatory'] ? 'true' : 'false';
		$var['CountryMandatory']	= $this->settings['CountryMandatory'] ? 'true' : 'false';
		$var['ResultDeliveryMethod']= 'POST';
  		$var['ServerResultURL']		= str_replace( '&amp;', '&', $request->int_var['return_url'] );
  		$var['PaymentFormDisplaysResult']	= 'false';

		return array_merge( array( 'post_url' => $url, 'HashDigest' => $this->createhash( $var ) ), $var );	
	}

	function createhash( $var, $response=false )
	{
		$values = array('PreSharedKey' => $this->settings['PreSharedKey'],
						'MerchantID' => $this->settings['MerchantID'],
						'Password' => $this->settings['Password']
						);

		if ( $response ) {
			$extra = array( 
							'StatusCode', 'Message', 'PreviousStatusCode', 'PreviousMessage',
							'CrossReference', 'Amount', 'CurrencyCode', 'OrderID',
							'TransactionType', 'TransactionDateTime', 'OrderDescription', 'CustomerName',
							'Address1', 'Address2', 'Address3', 'Address4',
							'City' , 'State', 'PostCode', 'CountryCode'
							);
		} else {
			$extra = array( 'Amount', 'CurrencyCode', 'OrderID', 'TransactionType',
							'TransactionDateTime', 'CallbackURL', 'OrderDescription', 'CustomerName',
							'Address1', 'Address2', 'Address3', 'Address4',
							'City', 'State', 'PostCode', 'CountryCode',
							'CV2Mandatory', 'Address1Mandatory', 'CityMandatory', 'PostCodeMandatory',
							'StateMandatory', 'CountryMandatory', 'ResultDeliveryMethod', 'ServerResultURL',
							'PaymentFormDisplaysResult'
	  						);
		}

		foreach ( $extra as $k ) {
			$values[$k] = $var[$k];
		}

		$str = array();
		foreach ( $values as $k => $v ) {
			$str[] = $k."=".$v;
		}

		return sha1( implode( "&", $str ) );

	}

	function parseNotification( $post )
	{
		$response = array();
		$response['invoice']		= AECfetchfromDB::InvoiceNumberfromId( $post['OrderID'] );
		$response['amount_paid']	= $post['Amount']/100;
		
		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 0;

		if ( $this->createhash($_POST, true) === $_POST['HashDigest'] ) {
			
			switch ( $post['StatusCode'] ) {
				case 0:
					$response['valid'] = 1;
					break;
				case 4:
					$response['error']		= true;
					$response['errormsg']	= "Card Referred";
					break;
				case 5:
					$response['error']		= true;
					$response['errormsg']	= "Card Declined";
					break;
				case 20:
					$response['duplicate']	= true;
					break;
				default:
					$response['error']		= true;
					$response['errormsg']	= "Exception";
					break;
			}
			if ( $post['StatusCode'] == 0 ) {
				$response['valid'] = 1;
			} else {
				
			}
		} else {
			$response['error']		= true;
			$response['errormsg']	= "Hash Mismatch";
		}

		return $response;
	}
}
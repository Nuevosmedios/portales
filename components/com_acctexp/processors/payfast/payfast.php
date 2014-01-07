<?php
/**
 * @version $Id: payfast.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - PayFast Buy Now
 * @copyright 2011-2012 Copyright (C) R Botha
 * @author Riekie Botha <riekie@jfundi.com> 
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */
// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_payfast extends POSTprocessor
{	

	function info()
	{
		$info = array();
		$info['name']					= 'payfast';
		$info['longname']				= JText::_('CFG_PAYFAST_LONGNAME');
		$info['statement']				= JText::_('CFG_PAYFAST_STATEMENT');
		$info['description']			= JText::_('CFG_PAYFAST_DESCRIPTION');
		$info['currencies']				= 'ZAR';
		$info['languages']				= AECToolbox::getISO639_1_codes();
		$info['cc_list']				= 'visa,mastercard';
		$info['recurring']				= 0;
		$info['notify_trail_thanks']	= false;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']		= 1;		
		$settings['merchant_id']	= '';		
		$settings['merchant_key']	= '';
		$settings['merchant_email_confirmation'] = 0;
		$settings['merchant_email'] = '';
		$settings['pdt_key']		= '';		
		$settings['currency']		= 'ZAR';
		$settings['item_name']		='[[user_id]]';
		$settings['item_desc']		= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );							

		for ( $i=1; $i<6; $i++ ) {
			$settings['custom_str'.$i]	= "";
			$settings['custom_int'.$i]	= "";
		}
  
		return $settings;
	}

	function backend_settings()
	{
		$settings = array();

		$settings['testmode']						= array( 'toggle' );		
		$settings['merchant_id']					= array( 'inputC' );	
		$settings['merchant_key']					= array( 'inputC' );
		$settings['merchant_email_confirmation']	= array( 'toggle' );
		$settings['merchant_email']					= array( 'inputC' );

		$settings['pdt_key']		= array( 'inputD' );
		$settings['currency']		= array( 'list_currency' );		
		$settings['item_name']		= array( 'inputE' );
		$settings['item_desc']		= array( 'inputE' );

		for ( $i=1; $i<6; $i++ ) {
			$settings['custom_int'.$i]	= array( 'inputC' );
		}

		for ( $i=1; $i<6; $i++ ) {
			$settings['custom_str'.$i]	= array( 'inputC' );
		}
		
		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		// Receiver details
		$var['merchant_id']		= $this->settings['merchant_id'];
		$var['merchant_key']	= $this->settings['merchant_key'];

		$var['return_url']		= str_replace( '&amp;', '&' , $request->int_var['return_url'] );		
		$var['cancel_url']		= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=cancel', false, true );	
		$var['notify_url']		= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=payfastnotification', false, true );

		$name = $request->metaUser->explodeName();

		// Payer details
		$var['name_first']		= $name['first'];
		$var['name_last']		= $name['last'];					
		$var['email_address']	= $request->metaUser->cmsUser->email;

		// Transaction details		
		$var['m_payment_id']	= $request->invoice->invoice_number;
		$var['amount']			= $request->int_var['amount'];
		$var['item_name']		= trim( AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request ) );

		if ( !empty($this->settings['item_desc']) ) {
		    $var['item_description']	= trim( AECToolbox::rewriteEngineRQ( $this->settings['item_desc'], $request ) );
		}

		// Custom variables
		for ( $i=1; $i<6; $i++ ) {
			if ( !empty( $this->settings['custom_str'.$i] ) ) {
				$var['custom_str'.$i] = $this->settings['custom_str'.$i];
			}			
		}

		for ( $i=1; $i<6; $i++ ) { //order is important
		        if ( !empty( $this->settings['custom_int'.$i] ) ) {
				$var['custom_int'.$i] = $this->settings['custom_int'.$i];
			}
		}

		// Transaction options
		if ( $this->settings['merchant_email_confirmation'] ) {
			$var['email_confirmation'] = $this->settings['merchant_email_confirmation'];

			if ( $var['email_confirmation'] ) {
				$var['confirmation_address'] = $this->settings['merchant_email'];
			}
		}		 

		// Security
		$strParam = null;	

		$var['signature'] = $this->getSignature( $var , $strParam);

		//post_url must not be part of the signature calculation
		if ( $this->settings['testmode'] ) {
			$var['post_url']	= 'https://sandbox.payfast.co.za/eng/process';
		} else {
			$var['post_url']	= 'https://www.payfast.co.za/eng/process';
		}

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['invoice'] = $post['m_payment_id'];
		$response['amount_paid'] = $post['amount_gross'];
		
	   return $response;
   }

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 0;

		if ( !$this->pfValidIP( $_SERVER['REMOTE_ADDR'] ) ) {
			$response['pending_reason'] = "Bad Notification Source IP";					

			return $response; 
		}

		$param_string = null;

		$sig = $this->getSignature( $post, $param_string);
         
		if ( $sig != $post['signature'] ) {
			$response['pending_reason'] = "Signature mismatch";					
			return $response; 
		}
		
		if ( !$this->pfValidate( $param_string ) ) {
		  $response['pending_reason'] = "Validation failed";
		  return $response;
		}

		if ( $post['payment_status'] == 'COMPLETE' ) {
			$response['valid'] = 1;
		}

		return $response;
	}

	function getSignature( $data , &$param_string )
	{
		if ( isset( $data['signature'] ) ) {
			unset( $data['signature'] );
		}

		if ( isset( $data['planparams'] ) ) {
			unset( $data['planparams'] );
		}

		$param_string = XMLprocessor::arrayToNVP( $data );

		return md5( $param_string );
	}

        
	function pfValidIP( $sourceIP )
	{
		$validHosts = array( 'www.payfast.co.za', 'sandbox.payfast.co.za', 'w1w.payfast.co.za', 'w2w.payfast.co.za' );

		$validIps = array();
		foreach ( $validHosts as $pfHostname ) {
			$ips = gethostbynamel( $pfHostname );
	
			if ( $ips !== false ) {
				$validIps = array_merge( $validIps, $ips );
			}
		}
	
		$validIps = array_unique( $validIps );
	
		return in_array( $sourceIP, $validIps );
	}

	function pfValidate( $data )
	{
		$path = '/eng/query/validate';
		if ( $this->settings['testmode'] ) {
			$url = 'https://sandbox.payfast.co.za' . $path;			
		} else {
			$url = 'https://www.payfast.co.za' . $path;			
		}

		$agent = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';

		$extra_header = array(	"User-Agent" => $agent,
								CURLOPT_HTTPHEADER => '[[unset]]',
								CURLOPT_USERAGENT => $agent,
								CURLOPT_TIMEOUT => 15
							);
		    
		$res = $this->transmitRequest( $url, $path, $data, 443, $extra_header);

		$lines = explode( "\r\n", $res );

		$verifyResult = trim( $lines[0] );	    

		return ( strcasecmp( $verifyResult, 'VALID' ) == 0 );
	}

}
?>

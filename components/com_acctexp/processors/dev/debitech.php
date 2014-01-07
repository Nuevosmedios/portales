<?php
/**
 * @version $Id: paypal.php 16 2007-06-25 09:04:04Z mic $
 * @package AEC - Account Expiration Control / Subscription management for Joomla
 * @subpackage Payment Processors
 * @author Helder Garcia <helder.garcia@gmail.com>, David Deutsch <skore@skore.de>
 * @copyright 2004-2007 Helder Garcia, David Deutsch
 * @license http://www.gnu.org/copyleft/gpl.html. GNU Public License
 */

// Copyright (C) 2006-2007 David Deutsch
// All rights reserved.
// This source file is part of the Account Expiration Control Component, a  Joomla
// custom Component By Helder Garcia and David Deutsch - http://www.globalnerd.org
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// Please note that the GPL states that any headers in files and
// Copyright notices as well as credits in headers, source files
// and output (screens, prints, etc.) can not be removed.
// You can extend them with your own credits, though...
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// The "GNU General Public License" (GPL) is available at
// http://www.gnu.org/copyleft/gpl.html.

// Dont allow direct linking
defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

class processor_debitech extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'debitech-subscription';
		$info['longname'] 				= _AEC_PROC_INFO_DBTSC_LNAME;
		$info['statement'] 				= _AEC_PROC_INFO_DBTSC_STMNT;
		$info['description'] 			= _DESCRIPTION_DBT_SUBCRIPTION;
		$info['currencies']				= 'SEK,EUR,USD,GBP,AUD,CAD,JPY,NZD,CHF,HKD,SGD,DKK,PLN,NOK,HUF,CZK';
		$info['languages']				= 'SE,GB,DE,FR,IT,ES,US';
		$info['cc_list']				= 'visa,mastercard,discover,americanexpress,echeck,giropay';
		$info['recurring']				= 1;
       	$info['notify_trail_thanks']	= 1; // insert this line

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']		= 0;
		$settings['pageSet']		= 'test';
		$settings['accountName']	= 'accountname';
		$settings['method'] 		= 'method';
		
		$settings['currency']		= 'SEK';
		$settings['lc']				= 'US';
		$settings['uses3dsecure']	= '0';
		$settings['item_name']		= sprintf( _CFG_PROCESSOR_ITEM_NAME_DEFAULT, '[[cms_live_site]]', '[[user_name]]', '[[user_username]]', '[[plan_name]]' );
		$settings['verify_url']		= '';
		$settings['rewriteInfo']	= '';

		return $settings;
	}

	function backend_settings()
	{
		global $mosConfig_live_site;
	
		$settings = array();
		$rewriteswitches			= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );
		
		$settings['testmode']		= array( 'toggle' );
		$settings['pageSet']		= array( 'inputC', 'PageSet *', 'PageSet' );;
		$settings['accountName']	= array( 'inputC', 'Account Name *', 'Account Name' );;
		$settings['uses3dsecure'] 	= array( 'toggle', '3-D Secure', '3-D Secure is MasterCard\'s and VISA\'s way of securing e-commerce credit card payment' );		
		$settings['method']			= array( "inputC", 'Payment method (option)', 'Default cc.nw: American Express via TNS/American Express. Reference information at DebiTech Web Solution Manual - page 8');


		$settings['currency']		= array( 'list_currency' );
		$settings['lc']				= array( 'list_language', 'Language' );		
		$settings['item_name']		= array( 'inputE' );
		$settings['rewriteInfo']	= array( 'fieldset', _AEC_MI_REWRITING_INFO, AECToolbox::rewriteEngineInfo( $rewriteswitches ) );

		return $settings;
	}

	function createGatewayLink( $request )
	{
		global $mosConfig_live_site;

		// system parameters
		$var['post_url']			= 'https://secure.incab.se/verify/bin/'.$this->settings['accountName'].'/index';
		$var['pageSet'] 			= $this->settings['pageSet'] ;		
		$var['test'] 				= $this->settings['testmode'] ;
		$var['method'] 				= $this->settings['method'] ;
		$var['uses3dsecure'] 		= 'true' ;
		
		// order parameters
		$var['currency']			= $this->settings['currency'];	
		$var['separator']			= "|";	
				
		// consumer data
		$var['userid'] 				=	$request->metaUser->cmsUser->id;
		$var['billingFirstName'] 	=	$request->metaUser->cmsUser->username;
		$var['billingLastName'] 	=	$request->metaUser->cmsUser->cb_lastname;
		$var['eMail'] 				=	$request->metaUser->cmsUser->email;
		$var['billingAddress'] 		=	$request->metaUser->cmsUser->cb_address;
		$var['billingZipCode'] 		=	$request->metaUser->cmsUser->cb_zipcode;
		$var['billingCity'] 		=	$request->metaUser->cmsUser->cb_city;
		$var['billingCountry'] 		=	$request->metaUser->cmsUser->cb_country;
		$var['billingAddress'] 		=	$request->metaUser->cmsUser->cb_address;
		
		// invoice information
		$var['invoiceNo']			= $request->int_var['invoice'];
		$var['customerNo']			= $request->metaUser->cmsUser->id;
		
		//payMethodID				
		$var['Amount']				= number_format($request->int_var['amount'], 0);
		$var['OrderId']				= $request->new_subscription->id;
		
		
		// data parameters
		$var['data']	= "1|" 
						. AECToolbox::rewriteEngine( $this->settings['item_name'], $request->metaUser, $request->new_subscription, $request->invoice )
						."|1|" . $request->int_var['amount'] * 100 ."|";		

		// Security key
		$secretKey = "1010101010101010101010101010101010";
		$var['MAC'] = sha1($var['data']."&".$var['currency']."&".$var['invoiceNo']."&".$secretKey."&");

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['valid'] 		= $post['valid'] == "1" ? true : false;
		$response['valid'] 		= true;
		$response['invoice'] 	= $post['invoiceNo'];
		$response['userid'] 	= $post['customerNo'];

		// Secret key
		$secretKey = "1010101010101010101010101010101010";

		// Data needed to generate sha1-key
		$data = array();
		$data['sum'] = $post['sum'];
		$data['currency'] = $post['currency'];
		$data['reply'] = $post['reply'];
		$data['verifyid'] = $post['verifyid'];
		$data['invoiceNo'] = $post['invoiceNo'];
		$data['MAC'] = substr($post['MAC'],strpos($post['MAC'],"=")+1);

		// Generate key to validate
		$key_to_validate = strtoupper(sha1($data['sum']."&".$data['currency']."&".$data['reply']."&".$data['verifyid']."&".$data['invoiceNo']."&".$secretKey."&"));

		// Validate
		if ($key_to_validate != $data['MAC']) {
			// Invalid!
			$response['valid'] = false;
		}

		return $response;
	}
	
	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = $post['valid'] == "1" ? true : false;
		$response['valid'] = true;
		$response['invoice'] = $post['invoiceNo'];
		$response['userid'] = $post['customerNo'];

		// Secret key
		$secretKey = "1010101010101010101010101010101010";

		// Data needed to generate sha1-key
		$data = array();
		$data['sum'] = $post['sum'];
		$data['currency'] = $post['currency'];
		$data['reply'] = $post['reply'];
		$data['verifyid'] = $post['verifyid'];
		$data['invoiceNo'] = $post['invoiceNo'];
		$data['MAC'] = substr($post['MAC'],strpos($post['MAC'],"=")+1);

		// Generate key to validate
		$key_to_validate = strtoupper(sha1($data['sum']."&".$data['currency']."&".$data['reply']."&".$data['verifyid']."&".$data['invoiceNo']."&".$secretKey."&"));

		// Validate
		if ($key_to_validate != $data['MAC']) {
			// Invalid!
			$response['valid'] = false;
		}

		return $response;
	}
}
?>

<?php
/**
 * @version $Id: nelnet.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @based on subpackage Processors - Clickbank
 * @copyright 2007-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @modified by rpalmberg@med.miami.edu
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_nelnet extends URLprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'nelnet';
		$info['longname'] 				= 'Nelnet!';
		$info['statement'] 				= 'Nelnet.';
		$info['description'] 			= 'Nelnet.';
		// NelNet can be configured to take Visa, Mastercard, American Express, Discover, and eChecks. But this depends on the setup that the user has agreed upon with NelNet.
		$info['cc_list'] 				= "visa,mastercard";
		$info['currencies']				= "USD";
		$info['recurring'] 				= 0;
		$info['notify_trail_thanks']	= 1;
		//* As far as I know, NelNet QuickPay does not do recurring transactions, so I tried to remove the recuring option dropdowns.
		//$info['recurring_buttons']		= 2;

		return $info;
	}

	function settings()
	{
		$settings = array();
		//test mode is activated by using a diffrent secret key
		//$settings['testmode']		= 0;
		$settings['currency']		= "USD";
		$settings['publisher']		= 'nelnet';
		$settings['secret_key']		= 'secret_key';
		$settings['info']			= "";
		$settings['customparams']	= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['currency']		= array( 'list_currency' );
		//$settings['testmode']		= array( 'toggle' );
		$settings['publisher']		= array( "inputC","Batch ID Number", "The Batch ID number assigned to you by NelNet QuickPay");
		$settings['secret_key']		= array( "inputC","Secret Key","Used to encrypt and protect transactions" );
		$settings['info']			= array( 'fieldset' );
		$settings['customparams']	= array( 'inputD' );

		
		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function CustomPlanParams()
	{
		$p = array();
		$p['item_number']	= array( 'inputC' );

		return $p;
	}

	function createGatewayLink( $request )
	{

		//Required. Where we are sending our information to NelNet
		//* This should be a frontend user option (to supply the subdirectory name; in this example, 'um')
		$var['post_url']		= 'https://uatquikpayasp.com/um/commerce_manager/payer.do?';
		
//Start creating our querystring to send to NelNet
		//Required. our customer account/ batch ID (assigned by NelNet representative). Max Length: 10
		$var['orderType']		= $this->settings['publisher'];
		//Required. Useless. Supposedly batch ID + our order number. Sending first 9 digits of invoice. Max Length: 9 *Displayed to user by QuickPay*
		$var['orderNumber'] 	= substr($request->invoice->invoice_number,0,9);
		//Required. Cost, in cents of order. Numbers only. Max length: 9
		$var['amount']			= ($request->int_var['amount'])*100;
		// Required. this is the fee that you are charging in addition to the item cost (for shipping & handling or tax). Numbers only. Max length: 9 *Displayed to user by QuickPay*
		//* Does AEC do tax/shipping splits? If so it could be itemized here. *Presume that this is Displayed to user by QuickPay*
		$var['orderFee']		= '0';
		// Required. Useless. Supposedly our unique invoice number. But string can only hold 9 characters. So we can't use it as AEC invoice is longer than that. Sending last 8 digits of invoice. *Displayed to user by QuickPay*
		$var['userChoice1']			= substr($request->invoice->invoice_number,9,8);
		// Required (by AEC.) We use userChoice2 to pass AEC invoice number to Nelnet, so it will pass back to us. Max length: 50
		$var['userChoice2']			= $request->invoice->invoice_number;
		// Optional. You can use userChoice3-10 to pass other information that you want to get back. If you do, include them in the hash. Max length (each): 50
		
		// Required. URL that receives back the response from NelNet. 
		// Don't include the '?option=com_acctexp&task=nelnetnotification', we cannot pass them that
		//* This should be a frontend user option or automatically determined somehow?
		$var['redirectUrl']			= 'http://mysite.com/index.php';

		// Required. Variables that NelNet will send back to us
		$var['redirectUrlParameters']		= 'transactionType,transactionStatus,transactionTotalAmount,userChoice2';	
		// Required. Retries allowed by user to make the payment before failure.
		$var['retriesAllowed'] 		= '3';
		// Required. Unix Epoch time in milliseconds
		$var['timestamp']		= ( (int) gmdate('U') ) * 1000;
		
		// Required. you must make a hash of all variables you are using in correct order + secret key. Do not include any you are not using.
		$var['hash'] = md5($var['orderType'].$var['orderNumber'].$var['amount'].$var['orderFee'].$var['userChoice1'].$var['userChoice2'].$var['redirectUrl'].$var['redirectUrlParameters'].$var['retriesAllowed'].$var['timestamp'].$this->settings['secret_key']);
	
		return $var;
	}

	
		function parseNotification( $post )
	{
		$response = array();

		$response['invoice'] 		= $_GET["userChoice2"];
		$priceincents				= $_GET["transactionTotalAmount"];
		//making the assumption that orderFee above was 0? Might need to change, don't know how AEC does tax/shipping.
		$response['amount_paid']	= $priceincents / 100;

		return $response;
	}
	
	


function validateNotification( $response, $post, $invoice )	{
	$response['valid'] = 0;
	
		//Need to check to see if transaction timestamp is within 5 minutes / 300 seconds as per NelNet documentation
	$posttime = $_GET["timestamp"];
	$nowtime = ( (int) gmdate('U') ) * 1000;
	
		// Notes on transactionType
		// transactionType values:  1 = credit card, 2 = refund, 3 = echeck
		//*My code only works with 1, but for all users would need to process 2 and 3.
		
		// Notes on transactionStatus
		// transactionStatus values: (1-4 are for credit card, 5-7 for echeck)
 		//1 -Accepted credit card payment/refund (successful) 
		//2 -Rejected credit card payment/refund (declined) 
		//3 -Error credit card payment/refund (error) 
		//4 -Unknown credit card payment/refund (unknown)  
		//5 -Accepted eCheck payment (successful) 
		//6 -Posted eCheck payment (successful) 
		//7 -Returned eCheck payment (failed) 
		//*My code only works with 1.
		
		// make our own hash of the values with our copy of the secret key
		$check_hash = md5($_GET["transactionType"].$_GET["transactionStatus"].$_GET["transactionTotalAmount"].$_GET["userChoice2"].$_GET["timestamp"].$this->settings['secret_key']);
	
	// Most common scenario: time, transactionType, transactionStatus and hash are ok	 
	if (( abs($nowtime - $posttime) < 300000) && ($_GET["transactionType"] == 1) && ($_GET["transactionStatus"] == 1) && (strcmp($check_hash, $_GET['hash']) == 0)):

		//since everything is ok, it's valid
		$response['valid'] = 1;
	//Otherwise, figure out what went wrong!
	
	// Error of too much time difference 	
	elseif ( abs($nowtime - $posttime) >= 300000):
		$response['error'] = 1;
		$response['errormsg'] = "Error 1 - Time Difference.";
	
	// Error of wrong transactionType
	elseif ($_GET["transactionType"] != 1):
		$response['error'] = 1;
		$response['errormsg'] = "Error 2 - TransactionType.";
	
	//Error of wrong transactionStatus
	elseif ($_GET["transactionStatus"] != 1):
		$response['error'] = 1;
		$response['errormsg'] = "Error 3 - TransactionStatus.";
	
	//Error of hash not matching
	elseif (strcmp($check_hash, $_GET['hash']) != 0):
		$response['error'] = 1;
		$response['errormsg'] = "Error 4 - Hash does not match.";
	
	//I suppose there are always unknowns in life
	else:
		$response['error'] = 1;
		$response['errormsg'] = "Error 5 - Unknown error.";
	endif;
	
		return $response;

}

}
?>
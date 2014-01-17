 	<?php
/**
 * @version $Id: securepayxml.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - eWay XML
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

/**
* AcctExp Component
* @package AEC - Account Control Expiration - Membership Manager
* @subpackage processor
* @copyright 2006-2012 Copyright (C) David Deutsch
* @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
* @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
**/

class processor_securepay extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'securepayxml';
		$info['longname']		= _CFG_SECUREPAY_LONGNAME;
		$info['statement']		= _CFG_SECUREPAY_STATEMENT;
		$info['description']	= _CFG_SECUREPAY_DESCRIPTION;
		$info['cc_list']		= 'visa,mastercard';
		$info['recurring']		= 0;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']		= "1";
		$settings['merchantid']		= '';
		$settings['password']		= '';
		$settings['tax']			= "10";
		$settings['testAmount']		= "00";
		$settings['item_name']		= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['rewriteInfo']	= '';
		$settings['SiteTitle']		= '';

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']		= array( 'toggle' );
		$settings['merchantid']		= array( 'inputC' );
		$settings['password']		= array( 'inputC' );
		$settings['SiteTitle']		= array( 'inputC' );
		$settings['item_name']		= array( 'inputE' );

 		$rewriteswitches			= array( 'cms', 'user', 'expiration', 'subscription', 'plan');
		$settings = AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function createRequestXML( $request )
	{

		$order_total = $request->int_var['amount'] * 100;

		$xml .= '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<SecurePayMessage>';

		$xml .= '<MessageInfo>';
		$xml .= '<messageID>'.$request->invoice->invoice_number.'</messageID>';
		$xml .= '<messageTimestamp>'.date("YmdGis")."000+1000".'</messageTimestamp>';
		$xml .= '<timeoutValue>60</timeoutValue>';
		$xml .= '<apiVersion>xml-4.2</apiVersion>';
		$xml .= '</MessageInfo>';

		$xml .= '<MerchantInfo>';
		$xml .= '<merchantID>xxxxxxxxxx</merchantID>';
		$xml .= '<password>xxxxxxxxxx</password>';
		$xml .= '</MerchantInfo>';

		$xml .= '<RequestType>Payment</RequestType>';

		$xml .= '<Payment>';
		$xml .= '<TxnList count="1">';
		$xml .= '<Txn ID="1">';
		$xml .= '<txnType>0</txnType>';
		$xml .= '<txnSource>0</txnSource>';
		$xml .= '<amount>'.$order_total.'</amount>';
		$xml .= '<purchaseOrderNo>'.$request->invoice->invoice_number.'</purchaseOrderNo>';

		$xml .= '<CreditCardInfo>';
		$xml .= '<cardNumber>'.$request->int_var['params']['cardNumber'].'</cardNumber>';
		$xml .= '<expiryDate>'.$request->int_var['params']['expirationYear'].'/'.$request->int_var['params']['expirationMonth'].'</expiryDate>';
		$xml .= '</CreditCardInfo>';

		$xml .= '</Txn>';
		$xml .= '</TxnList>';
		$xml .= '</Payment>';

		$xml .= '</SecurePayMessage>';

		return $xml;
	}

	function transmitRequestXML( $xml, $request )
	{
		if ( $this->settings['testmode'] ) {
			$url = 'https://test.securepay.com.au/xmlapi/payment';
		} else {
			$url = 'https://www.securepay.com.au/xmlapi/payment';
		}

		$response = array();

		if ( $objResponse = simplexml_load_string( $this->transmitRequest( $url, '', $xml ) ) ) {
			$response['amount_paid'] = $objResponse->amount / 100;
			$response['invoice'] = $objResponse->purchaseOrderNo;

			 	<?php
/**
 * @version $Id: securepayxml.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - eWay XML
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

/**
* AcctExp Component
* @package AEC - Account Control Expiration - Membership Manager
* @subpackage processor
* @copyright 2006-2012 Copyright (C) David Deutsch
* @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
* @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
**/

class processor_securepay extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'securepayxml';
		$info['longname']		= _CFG_SECUREPAY_LONGNAME;
		$info['statement']		= _CFG_SECUREPAY_STATEMENT;
		$info['description']	= _CFG_SECUREPAY_DESCRIPTION;
		$info['cc_list']		= 'visa,mastercard';
		$info['recurring']		= 0;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']		= "1";
		$settings['merchantid']		= '';
		$settings['password']		= '';
		$settings['tax']			= "10";
		$settings['testAmount']		= "00";
		$settings['item_name']		= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['rewriteInfo']	= '';
		$settings['SiteTitle']		= '';

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']		= array( 'toggle' );
		$settings['merchantid']		= array( 'inputC' );
		$settings['password']		= array( 'inputC' );
		$settings['SiteTitle']		= array( 'inputC' );
		$settings['item_name']		= array( 'inputE' );

 		$rewriteswitches			= array( 'cms', 'user', 'expiration', 'subscription', 'plan');
		$settings = AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function createRequestXML( $request )
	{

		$order_total = $request->int_var['amount'] * 100;

		$xml .= '<?xml version="1.0" encoding="UTF-8"?>';
		$xml .= '<SecurePayMessage>';

		$xml .= '<MessageInfo>';
		$xml .= '<messageID>'.$request->invoice->invoice_number.'</messageID>';
		$xml .= '<messageTimestamp>'.date("YmdGis")."000+1000".'</messageTimestamp>';
		$xml .= '<timeoutValue>60</timeoutValue>';
		$xml .= '<apiVersion>xml-4.2</apiVersion>';
		$xml .= '</MessageInfo>';

		$xml .= '<MerchantInfo>';
		$xml .= '<merchantID>xxxxxxxxxx</merchantID>';
		$xml .= '<password>xxxxxxxxxx</password>';
		$xml .= '</MerchantInfo>';

		$xml .= '<RequestType>Payment</RequestType>';

		$xml .= '<Payment>';
		$xml .= '<TxnList count="1">';
		$xml .= '<Txn ID="1">';
		$xml .= '<txnType>0</txnType>';
		$xml .= '<txnSource>0</txnSource>';
		$xml .= '<amount>'.$order_total.'</amount>';
		$xml .= '<purchaseOrderNo>'.$request->invoice->invoice_number.'</purchaseOrderNo>';

		$xml .= '<CreditCardInfo>';
		$xml .= '<cardNumber>'.$request->int_var['params']['cardNumber'].'</cardNumber>';
		$xml .= '<expiryDate>'.$request->int_var['params']['expirationYear'].'/'.$request->int_var['params']['expirationMonth'].'</expiryDate>';
		$xml .= '</CreditCardInfo>';

		$xml .= '</Txn>';
		$xml .= '</TxnList>';
		$xml .= '</Payment>';

		$xml .= '</SecurePayMessage>';

		return $xml;
	}
/*SimpleXMLElement Object ( [MessageInfo] =>  SimpleXMLElement Object (
[messageID] =>  IMGIzMzBiZTI1NDEx [messageTimestamp] =>
20102907161629663000+600 [apiVersion] =>  xml-4.2 ) [RequestType] =>
20102907161629663000+Payment
[MerchantInfo] =>  SimpleXMLElement Object ( [merchantID] =>  XXXXXXXX
) [Status] =>  SimpleXMLElement Object ( [statusCode] =>  000
[statusDescription] =>  Normal ) [Payment] =>  SimpleXMLElement Object
( [TxnList] =>  SimpleXMLElement Object ( [@attributes] =>  Array (
[count] =>  1
) [Txn] =>  SimpleXMLElement Object ( [@attributes] =>  Array ( [ID]
=>  1 ) [txnType] =>  0 [txnSource] =>  0 [amount] =>  1 [currency] =>
AUD [purchaseOrderNo] =>  IMGIzMzBiZTI1NDEx [approved] =>  No
[responseCode] =>
102 [responseText] =>  Invalid Expiry Date [thinlinkResponseCode] =>
300 [thinlinkResponseText] =>  000 [thinlinkEventStatusCode] =>  981
[thinlinkEventStatusText] =>  Error - Expired Card [settlementDate] =>
SimpleXMLElement Object ( ) [txnID] =>  SimpleXMLElement Object ( )
[CreditCardInfo] =>  SimpleXMLElement Object ( [pan] =>  444433...111
[expiryDate] =>  SimpleXMLElement Object ( ) [cardType] =>  6
[cardDescription] =>  Visa ) ) ) ) )*/
	function transmitRequestXML( $xml, $request )
	{
		if ( $this->settings['testmode'] ) {
			$url = 'https://test.securepay.com.au/xmlapi/payment';
		} else {
			$url = 'https://www.securepay.com.au/xmlapi/payment';
		}

		$response = array();

		if ( $objResponse = simplexml_load_string( $this->transmitRequest( $url, '', $xml ) ) ) {
			$response['amount_paid'] = $objResponse->amount / 100;
			$response['invoice'] = $objResponse->purchaseOrderNo;

			if ( $objResponse->Status->statusCode == '000' ) {
				$response['valid'] = 1;
			} else {
				$response['valid'] = 0;
				$response['error'] = 'Not processed';
			}
		} else {
			$response['valid'] = 0;
			$response['error'] = "Can't connect";
		}

		return $response;
	}

	function checkoutform()
	{
		$var = $this->getUserform();
		$var = $this->getCCform();

		return $var;
	}

}
?>

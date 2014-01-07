<?php
/**
 * @version $Id: desjardins.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - desjardins XML
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_desjardins extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'desjardins';
		$info['longname']				= JText::_('CFG_DESJARDINS_LONGNAME');
		$info['statement']				= JText::_('CFG_DESJARDINS_STATEMENT');
		$info['description']			= JText::_('CFG_DESJARDINS_DESCRIPTION');
		$info['cc_list']				= 'visa,mastercard';
		$info['currencies']				= "CAD";
		$info['recurring']				= 0;
		$info['notify_trail_thanks']	= false;
		$info['custom_notify_trail']	= true;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']		= "0";
		$settings['currency']		= "CAD";
		$settings['custId']			= "";
		$settings['transactionKey']	= "";
		$settings['item_name']		= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['rewriteInfo']	= '';
		$settings['SiteTitle']		= '';

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']		= array( 'toggle' );
		$settings['currency']		= array( 'list_currency' );
		$settings['custId']			= array( 'inputC' );
		$settings['transactionKey']	= array( 'inputC' );
		$settings['SiteTitle']		= array( 'inputC' );
		$settings['item_name']		= array( 'inputE' );

        $settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function checkoutform( $request, $vcontent=null, $updated=null )
	{
		$var = array();

		return $var;
	}

	function createRequestXML( $request )
	{
$xml_request_str = <<<XML
<?xml version="1.0" encoding="ISO-8859-15"?><request></request>
XML;
		$xml_step1_request = new SimpleXMLElement($xml_request_str);

		$merchant = $xml_step1_request->addChild( 'merchant' );
		$merchant->addAttribute( 'key', trim( $this->settings['transactionKey'] ) );

		$login = $merchant->addChild( 'login' );

		$suffix = '';
		if ( isset( $request->invoice->params['desjardin_retries'] ) ) {
			$suffix = $request->invoice->params['desjardin_retries'];
		}

		$trx = $login->addChild( 'trx' );
		$trx->addAttribute( 'id', $request->invoice->invoice_number.$suffix );
		
		return $xml_step1_request->asXML();
	}

	function transmitRequestDesjardin( $url, $path, $xml )
	{
		$header = array();
		$header['MIME-Version']		= "1.0";
		$header['Content-type']		= "text/xml";
		$header['Accept']			= "text/xml";
		$header['Cache-Control']	= "no-cache";

		$curlextra[CURLOPT_RETURNTRANSFER]	= 1;
		$curlextra[CURLOPT_TIMEOUT]			= 25;
		$curlextra[CURLOPT_VERBOSE]			= 0;
		$curlextra[CURLOPT_CUSTOMREQUEST]	= 'POST';
		$curlextra[CURLOPT_SSL_VERIFYPEER]	= false;

		return $this->transmitRequest( $url, $path, $xml, 443, $curlextra, $header );
	}

	function transmitRequestXML( $xml, $request )
	{
		$app = JFactory::getApplication();
		
		$path = '/catch';
		$url = 'https://www.labdevtrx3.com' . $path;

		// Step #1 - Logging in with Desjardins
		$resp = $this->transmitRequestDesjardin( $url, $path, $xml );

		// Step #2 - Receiving the Transaction ID
		$xml = $this->createRequestStep3XML( $resp, $request );

		// Step #3 - Making the Purchase Request
		$resp = $this->transmitRequestDesjardin( $url, $path, $xml );

		// Step #4 - Desjardins validates the information
		$xml_step3_Obj = simplexml_load_string( $resp );

		$redir_url = $xml_step3_Obj->merchant->transactions->transaction->urls->url->path;
		$trx_number = $xml_step3_Obj->xpath('merchant/transactions/transaction/urls/url/parameters/parameter[@name="transaction_id"]');
		$trx_key = $xml_step3_Obj->xpath('merchant/transactions/transaction/urls/url/parameters/parameter[@name="transaction_key"]');
		$trx_merch_id = $xml_step3_Obj->xpath('merchant/transactions/transaction/urls/url/parameters/parameter[@name="merchant_id"]');

		$suffix = '';
		if ( isset( $request->invoice->params['desjardin_attempts'] ) ) {
			$suffix = $request->invoice->params['desjardin_attempts'];
		}

		$url = $redir_url . "?transaction_id=".$trx_number[0].$suffix;
		$url .= "&merchant_id=".$trx_merch_id[0];
		$url .= "&transaction_key=".$trx_key[0];

		if ( isset( $request->invoice->params['desjardin_attempts'] ) ) {
			$attempts = $request->invoice->params['desjardin_attempts'] + 1;
		} else {
			$attempts = 1;
		}

		$request->invoice->addParams( array( 'desjardin_attempts' => $attempts ) );
		$request->invoice->storeload();

		// Step #5 - Redirecting the user to Desjardins
		$app->redirect($url);
			
		return true;
	}

	function createRequestStep3XML( $resp, $request )
	{
		$xml_step1_Obj = simplexml_load_string($resp);
		$amount = $request->int_var['amount'] * 100;

		$suffix = '';
		if ( isset( $request->invoice->params['desjardin_attempts'] ) ) {
			$suffix = $request->invoice->params['desjardin_attempts'];
		}

		$return = JURI::root() . 'components/com_acctexp/processors/notify/notify_redirect.php';

		$xml_step3_request = '<?xml version="1.0" encoding="ISO-8859-15" ?>'."\n";
		$xml_step3_request .= '	<request>'."\n";
		$xml_step3_request .= '	  <merchant id="'.trim($this->settings['custId']).'" key="'.trim($this->settings['transactionKey']).'">'."\n";
		$xml_step3_request .= '		<transactions>'."\n";
		$xml_step3_request .= '		  <transaction id="' . trim($xml_step1_Obj->merchant->login->trx['id']).$suffix . '" key="'.trim($xml_step1_Obj->merchant->login->trx['key']) .'" type="purchase" currency="CAD" currencyText="$CAD">'."\n";
		$xml_step3_request .= '			<amount>'.$amount.'</amount>'."\n";
		$xml_step3_request .= '			<language>fr</language>'."\n";
		$xml_step3_request .= '			<urls>'."\n";
		$xml_step3_request .= '			  <url name="response">'."\n";
		$xml_step3_request .= '			    <path>' . $return . '</path>'."\n";
		$xml_step3_request .= '			    <parameters>'."\n";
		$xml_step3_request .= '			      <parameter name="aec_request">djd_' . $request->invoice->invoice_number . '_response</parameter>'."\n";
		$xml_step3_request .= '			    </parameters>'."\n";
		$xml_step3_request .= '			  </url>'."\n";
		$xml_step3_request .= '			  <url name="success">'."\n";
		$xml_step3_request .= '				<path>' . $return . '</path>'."\n";
		$xml_step3_request .= '			    <parameters>'."\n";
		$xml_step3_request .= '			      <parameter name="aec_request">djd_' . $request->invoice->invoice_number . '_success</parameter>'."\n";
		$xml_step3_request .= '			    </parameters>'."\n";
		$xml_step3_request .= '			  </url>'."\n";
		$xml_step3_request .= '			  <url name="cancel">'."\n";
		$xml_step3_request .= '				<path>' . $return . '</path>'."\n";
		$xml_step3_request .= '			    <parameters>'."\n";
		$xml_step3_request .= '			      <parameter name="aec_request">djd_' . $request->invoice->invoice_number . '_cancel</parameter>'."\n";
		$xml_step3_request .= '			    </parameters>'."\n";
		$xml_step3_request .= '			  </url>'."\n";
		$xml_step3_request .= '			  <url name="error">'."\n";
		$xml_step3_request .= '				<path>' . $return . '</path>'."\n";
		$xml_step3_request .= '			    <parameters>'."\n";
		$xml_step3_request .= '			      <parameter name="aec_request">djd_' . $request->invoice->invoice_number . '_error</parameter>'."\n";
		$xml_step3_request .= '			    </parameters>'."\n";
		$xml_step3_request .= '			  </url>'."\n";
		$xml_step3_request .= '			</urls>'."\n";
		$xml_step3_request .= '			<details>'."\n";
		$xml_step3_request .= '			 <![CDATA['."\n";

		$xml_step3_request .= '			  ]]>'."\n";
		$xml_step3_request .= '			</details>'."\n";
		$xml_step3_request .= '			<details_text>'."\n";
		$xml_step3_request .= '			   <![CDATA['."\n";
		$xml_step3_request .= 'Information de la commande'."\n";
		$xml_step3_request .= "No d'authorisation : ".$request->invoice->invoice_number ."\n";
		$xml_step3_request .= 'Abonnement : '.$request->plan->name ."\n";
		$xml_step3_request .= 'Subscription Cost : ' . $request->items->total->cost['amount'] ."\n";

		foreach ( $request->items->tax as $tax ) {
			$xml_step3_request .= $tax['terms']->terms[0]->cost[0]->cost['details'] . ' : '. AECToolbox::correctAmount( $tax['cost'] ) ."\n";
		}

		$xml_step3_request .= 'Total : '. $request->items->grand_total->cost['amount'] ."\n";
		$xml_step3_request .= '			   ]]>'."\n";
		$xml_step3_request .= '			</details_text>'."\n";
		$xml_step3_request .= '		  </transaction>'."\n";
		$xml_step3_request .= '		</transactions>'."\n";
		$xml_step3_request .= '	  </merchant>'."\n";
		$xml_step3_request .= '	</request>'."\n";

		return $xml_step3_request;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['invoice'] = $post['invoice_number'];

		if ( empty( $response['invoice'] ) ) {
			$response['invoice'] = aecGetParam( 'ResponseFile', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
		}

		if ( empty( $response['invoice'] ) && !empty( $post['original'] ) ) {
			$post['original'] = base64_decode( $post['original'] );

			$response['invoice'] = $this->substring_between( $post['original'], '<transaction id="', '"' );
		}

		// Make sure we don't have a subinvoice number
		$response['invoice'] = substr( $response['invoice'], 0, 17 );

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 0;

		$params = array( 'authorization_no' => '', 'reference_no' => '', 'card_type' => '', 'card_holder' => '', 'transaction_type' => 'Achat', 'transaction_status' => '' );

		foreach ( $params as $k => $v ) {
			if ( isset( $invoice->params[$k] ) ) {
				if ( $invoice->params[$k] == '---' ) {
					$params[$k] = '';
				} else {
					$params[$k] = $invoice->params[$k];
				}
			}
		}

		$td = null;
		if ( !empty( $post['original'] ) ) {
			$xml = base64_decode( $post['original'] );

			$auth = $this->XMLsubstring_tag( $xml, 'authorization_no' );
			$card_holder_name = $this->XMLsubstring_tag( $xml, 'card_holder_name' );

			if ( empty( $params['authorization_no'] ) && !empty( $auth ) ) {
				$stdstatus = $this->XMLsubstring_tag( $xml, 'receipt_text' );

				if ( empty( $stdstatus ) ) {
					switch ( strtolower( $post['status'] ) ) {
						case 'success': $status = 'APPROVED'; break;
						case 'cancel': $status = 'CANCELLED'; break;
						case 'error': $status = 'DECLINED'; break;
						default: $status = 'NOT COMPLETED'; break;
					}
				} else {
					$status = $stdstatus;
				}

				$params['authorization_no']		= $auth;
				$params['reference_no']			= $this->XMLsubstring_tag( $xml, 'sequence_no' ) . ' ' . $this->XMLsubstring_tag( $xml, 'terminal_id' );
				$params['card_type']			= $this->XMLsubstring_tag( $xml, 'card_type' );
				$params['card_holder']			= $this->XMLsubstring_tag( $xml, 'card_holder_name' );
				$params['transaction_type']		= 'Achat';
				$params['transaction_status']	= $status;

				$success = true;
				foreach ( $params as $v ) {
					if ( empty( $v ) ) {
						$success = false;
					}
				}

				if ( $success && empty( $params['transaction_status'] ) ) {
					$params['transaction_status'] = 'APPROVED';
				}
			} elseif ( empty( $params['authorization_no'] ) && !empty( $card_holder_name ) ) {
				$params['card_type']			= $this->XMLsubstring_tag( $xml, 'card_type' );
				$params['card_holder']			= $this->XMLsubstring_tag( $xml, 'card_holder_name' );
				$params['transaction_status']	= $this->XMLsubstring_tag( $xml, 'receipt_text' );
			}
		}

		foreach ( $params as $k => $v ) {
			if ( empty( $v ) ) {
				$params[$k] = '---';
			}
		}

		$invoice->addParams( $params );
		$invoice->storeload();

		if ( $post['status'] == 'error' ) {
			$invoice->transaction_date == '0000-00-00 00:00:00';
			$invoice->storeload();

			$error = "Erreur de procession de vos d&eacute;tails de paiement: Nous ne pouvons effectuer votre transaction par carte de cr&eacute;dit";

			$response['customthanks'] = $this->displayError( $invoice, $error ) . '<br /><br />' . $this->displayInvoice( $invoice );
			$response['break_processing'] = true;

			return $response;
		}

		if ( $post['status'] == 'cancel' ) {
			$invoice->cancel();

			$response['customthanks'] = $this->displayInvoice( $invoice );
			$response['break_processing'] = true;

			return $response;
		}

		if ( $post['status'] == 'success' ) {
			$app = JFactory::getApplication();

			$response['customthanks'] = $this->displayInvoice( $invoice );
			$response['break_processing'] = true;

			return $response;
		}

		if ( !strpos( base64_decode( $post['original'] ), '<confirm>' ) ) {
			$if = new stdClass();
			$if->invoice = $invoice;

			// Step #6 - Validate that we're still talking about the same transaction
			echo $this->notify_trail( $if, $response );

			exit;
		} else {
			$if = new stdClass();
			$if->invoice = $invoice;

			// Step #7 - Desjardins sends a final confirmation
			$response['valid'] = 1;

			$response['customthanks_strict'] = true;
			// Step #8 - We send a final acknowledgement
			$response['customthanks'] = $this->notify_trail( $if, $response );
		}

		return $response;
	}

	function notify_trail( $InvoiceFactory, $response )
	{
		$path = '/catch';
		$url = 'https://www.labdevtrx3.com' . $path;

$xml_request_str = <<<XML
<?xml version="1.0" encoding="ISO-8859-15"?><response></response>
XML;

		$xml_step1_request = new SimpleXMLElement($xml_request_str);

		$merchant = $xml_step1_request->addChild( 'merchant' );
		$merchant->addAttribute( 'id', trim( $this->settings['custId'] ) );

		$trx = $merchant->addChild( 'transaction' );
		$trx->addAttribute( 'id', $InvoiceFactory->invoice->invoice_number );
		$trx->addAttribute( 'accepted', 'yes' );

		$xml = $xml_step1_request->asXML();

		return $xml;
	}

	function displayInvoice( $invoice )
	{
		ob_start();

		$iFactory = new InvoiceFactory( $invoice->userid, null, null, null, null, null, false );
		$iFactory->invoiceprint( 'com_acctexp', $invoice->invoice_number, false );

		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	function displayError( $invoice, $error )
	{
		$db = &JFactory::getDBO();

		$metaUser = new metaUser( $invoice->userid );

		ob_start();

		getView( 'error', array(	'error' => "An error occured while cancelling your subscription. Please contact the system administrator!",
									'metaUser' => $metaUser,
									'invoice' => $invoice,
									'suppressactions' => true
								) );

		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

}
?>

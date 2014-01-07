<?php
/**
* @version $Id: sparkassen_internetkasse.php
* @package AEC - Account Control Expiration - Membership Manager
* @subpackage Processors - Sparkassen Internetkasse Formularservice
* @copyright 2011-2012 Copyright (C) David Deutsch
* @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
* @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
*/

// Dont allow direct linking
( defined('_JEXEC') || defined('_VALID_MOS') ) or die('Direct Access to this location is not allowed.');

class processor_sparkassen_internetkasse extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'sparkassen_internetkasse';
		$info['longname']		= JText::_('CFG_SPARKASSEN_INTERNETKASSE_LONGNAME');
		$info['statement']		= JText::_('CFG_SPARKASSEN_INTERNETKASSE_STATEMENT');
		$info['description']	= JText::_('CFG_SPARKASSEN_INTERNETKASSE_DESCRIPTION');
		$info['currencies']		= 'EUR';
		$info['cc_list']		= 'visa,mastercard,eurocard';
		$info['languages']		= AECToolbox::getISO639_1_codes();
		$info['recurring']		= 2;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']			= 0;
		$settings['pseudocreditcard']	= 0;
		$settings['sslmerchant']		= '';
		$settings['sslmerchantpass']	= '';
		$settings['merchant']			= '';
		$settings['merchantpass']		= '';

		$settings['item_name']			= sprintf(JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]');

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']			= array('toggle');
		$settings['pseudocreditcard']	= array('toggle');
		$settings['sslmerchant']		= array('inputC');
		$settings['sslmerchantpass']	= array('inputC');
		$settings['merchant']			= array('inputC');
		$settings['merchantpass']		= array('inputC');

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function checkoutform( $request )
	{
		return array();
	}

	function checkoutProcess( $request, $InvoiceFactory )
	{
		if ( !empty( $_GET['error'] ) ) {
			$error = $this->getError( $_GET['error'] );

			if ( !empty( $error ) ) {
				return array( 'error' => $error );
			} else {
				return array( 'error' => 'Unknown Error ' . $_GET['error'] );				
			}
		}

		if ( !empty( $this->settings['pseudocreditcard'] ) ) {
			$ppan = $this->getPPAN( $request->metaUser );

			if ( !empty( $ppan ) ) {
				// Make a form to confirm usage of PPAN
				$var = $this->getSIFvars( $request, $ppan );

				$url	= $var['post_url'];
				$path	= $var['post_path'];

				unset( $var['post_url'] );
				unset( $var['post_path'] );

				$curlextra = array( CURLOPT_USERPWD => $this->settings['merchant'] . ':' . $this->settings['merchantpass'] );

				$response = $this->transmitRequest( $url, $path, $this->arrayToNVP($var), 443, $curlextra );

				$result = $this->parseNotification( $response );
				
				$result = $this->validateNotification( $result, $response, $InvoiceFactory->invoice, false );

				return $result;
			}
		}

		$var = $this->getSIFvars( $request );

		$url	= $var['post_url'];

		unset( $var['post_url'] );
		unset( $var['post_path'] );

		aecRedirect( $url.'?'.$this->arrayToNVP($var) );
	}

	function createRequestXML( $request )
	{
		$var = $this->getSIFvars( $request );

		return $this->arrayToNVP( $var );
	}

	function getSIFvars( $request, $ppan )
	{
		$var = array();

		if ( $ppan ) {
			$var['command']			= 'authorization';
			$var['payment_options']	= 'creditcard';
			$var['orderid']			= $request->invoice->id;
			$var['basketnr']		= $request->invoice->invoice_number;
			$var['amount']			= (int) ( $request->int_var['amount'] * 100 );
			$var['currency']		= $this->settings['currency'];
			$var['ppan']			= $ppan;

			$path = '/request/request/prot/Request.po';
		} else {
			$var['amount']			= str_replace( '.', ',', $request->int_var['amount'] );
			$var['basketid']		= $request->invoice->invoice_number;
			$var['command']			= 'sslform';
			$var['currency']		= trim($this->settings['currency']);
			$var['orderid']			= date('YmdHis');

			// Decide whether a PPAN should be issued
			$var['payment_options']	= 'cardholder' . ( !empty( $this->settings['pseudocreditcard'] ) ? '' : ';generate_ppan' );
			$var['paymentmethod']	= 'creditcard';
			$var['sessionid']		= session_id();
			$var['sslmerchant']		= trim($this->settings['sslmerchant']);
			$var['transactiontype']	= 'authorization';
			$var['version']			= '1.5';

			$path = '/vbv/mpi_legacy';
		}

		$astring = array();
		foreach ( $var as $k => $v ) {
			$astring[] = $k.'='.$v;
		}

		$var['mac'] = $this->hmac( $this->settings['sslmerchantpass'], implode( '&amp;', $astring ) );

		if ( $this->settings['testmode'] ) {
			$var['post_path']	= $path;
			$var['post_url']	= 'https://testsystem.sparkassen-internetkasse.de' . $path;
		} else {
			$var['post_path']	= $path;
			$var['post_url']	= 'https://system.sparkassen-internetkasse.de' . $path;
		}

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();
		$response['amount_paid']		= $post['amount'] / 100;
		$response['amount_currency']	= $post['currency'];

		if ( !empty( $response['basketid'] ) ) {
			$response['invoice'] = $response['basketid'];
		} else {
			$response['invoice'] = $response['basketnr'];
		}

		return $response;
	}

	function validateNotification( $response, $post, $invoice, $echo=true )
	{
		$response['valid'] = 0;

		if ( $response['directPosErrorCode'] == '0' ) {
			$response['valid'] = true;

			if ( $this->settings['pseudocreditcard'] && !empty( $post['ppan'] ) ) {
				$this->setPPAN( $request->metaUser, $post['ppan'] );
			}

			if ( $echo ) {
				echo 'redirecturls=' . AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=thanks&usage='.$invoice->usage );

				exit;
			}
		} else {
			$response['error'] = $this->getError( $response['posherr'] );

			if ( empty( $response['error'] ) ) {
				$response['error'] = $post['directPosErrorMessage'];
			}

			if ( $this->settings['pseudocreditcard'] ) {
				// Whatever happened, the PPAN is most likely broken, delete it
				$metaUser = new metaUser( $invoice->userid );

				$this->deletePPAN( $metaUser );
			}

			if ( $echo ) {
				echo 'redirecturlf=' . AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=checkout&invoice='.$invoice->invoice_number.'&processor='.$invoice->method.'&userid='.$invoice->userid.'&error='.$response['posherr'] );

				exit;
			}
		}

		return $response;
	}

	function getError( $errcode )
	{
		$errors = array(	'133' => 'Karte abgelaufen.',
							'344' => 'Karte in Deutschland nicht gültig.',
							'347' => 'Abbruch durch den Benutzer.',
							'349' => 'Karte Transaktionslimit überschritten.',
							'350' => 'Karte gesperrt.'
							);

		if ( isset( $errors[$errcode] ) ) {
			return $errors[$errcode];
		} else {
			return null;
		}
	}

	function setPPAN( $metaUser, $ppan )
	{
		$metaUser->meta->setCustomParams( array( 'ppan' => $ppan ) );
		$metaUser->meta->storeload();

		return true;
	}

	function getPPAN( $metaUser )
	{
		$uparams = $metaUser->meta->getCustomParams();
		
		if ( !empty( $uparams['ppan'] ) ) {
			return $uparams['ppan'];
		}

		return null;
	}

	function deletePPAN( $metaUser )
	{
		$metaUser->meta->setCustomParams( array( 'ppan' => '' ) );
		$metaUser->meta->storeload();

		return true;
	}

	function hmac( $key, $data )
	{
	   // RFC 2104 HMAC implementation for php.
	   // Creates an SHA-1 HMAC.
	   // Eliminates the need to install mhash to compute a HMAC
	   // Hacked by Lance Rushing

	   $b = 64; // byte length for SHA-1

	   $key  = str_pad($key, $b, chr(0x00));
	   $ipad = str_pad('', $b, chr(0x36));
	   $opad = str_pad('', $b, chr(0x5c));
	   $k_ipad = $key ^ $ipad ;
	   $k_opad = $key ^ $opad;

	   return sha1($k_opad  . pack("H*",sha1($k_ipad . $data)));
	}

}

?>
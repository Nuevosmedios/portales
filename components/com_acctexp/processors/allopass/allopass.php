<?php
/**
 * @version $Id: allopass.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Allopass
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// ------------------------------------
// "AlloPass" feature contributed by:
// educ
// Jul 2006
// Thanks!
// ------------------------------------

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_allopass extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= "allopass";
		$info['longname']				= "Allopass";
		$info['statement']				= "Make payments with Allopass!";
		$info['description']			= JText::_('DESCRIPTION_ALLOPASS');
		$info['currencies']				= "EUR";
		$info['cc_list']				= "visa,mastercard";
		$info['recurring']				= 0;
		$info['notify_trail_thanks']	= 1;

		return $info;
	}

	function settings()
	{
		$settings = array();
		$settings['currency']		= "EUR";
		$settings['siteid']			= "siteid";
		$settings['docid']			= "docid";
		$settings['auth']			= "auth";
		$settings['testmode']		= 0;
		$settings['item_name']		= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['customparams']	= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']		= array( "toggle" );
		$settings['currency']		= array( 'list_currency' );
		$settings['siteid']			= array( "inputC" );
		$settings['auth']			= array( "inputC" );
		$settings['item_name']		= array( "inputE" );
		$settings['customparams']	= array( 'inputD' );

		$settings				= AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function CustomPlanParams()
	{
		$p = array();
		$p['docid']	= array( 'inputC' );

		return $p;
	}

	function checkoutform( $request )
	{
		$var = array();
		$var['params']['DESC0'] = array("p", "<img scr=\"http://payment.allopass.com/acte/scripts/popup/access.apu?ids=" . $this->settings['siteid'] . "&idd=" . $this->settings['docid'] . "&lang=fr&country=fr\" />");
		$var['params']['CODE0'] = array("inputC", "Allopass Code", "");

		return $var;
	}

	function parseNotification( $post )
	{

   		$ssl_amount = aecGetParam( 'ssl_amount' ) ;

		$response = array();
		$response['invoice'] = $post['ssl_invoice_number'];

		return $response;
	}

	function createRequestXML( $request )
	{
		$var = array();
		$var['CODE']		= urlencode( $request->int_var['params']['CODE0'] );
		$var['AUTH']		= $this->settings['auth'];
		$var['SITE_ID']		= $this->settings['siteid'];
		$var['DOC_ID']		= $this->settings['docid'];

		$content = array();
		foreach ( $var as $name => $value ) {
			$content[] .= strtoupper( $name ) . '=' . urlencode( stripslashes( $value ) );
		}

		return implode( '&', $content );
	}

	function transmitRequestXML( $xml, $request )
	{
		$path = "/acte/access.apu";
		$url = "http://payment.allopass.com" . $path;

		$fp = $this->transmitRequest( $url, $path, $xml );

		$test_ap = substr( $fp, 0, 2 );

		if ( $test_ap == "OK" ) {
			$response['valid'] = true;
			return;
		} else {
			$response['valid'] = false;

			if ( empty( $request->int_var['params']['CODE0'] ) ) {
				$response['error'] = 'No code entered!';
			} elseif ( empty( $test_ap ) ) {
				$response['error'] = 'Unknown Error - no response from processor';
			} else {
				$response['error'] = $test_ap;
			}
		}

		return $response;
	}
}
?>
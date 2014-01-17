<?php
/**
 * @version $Id: realex_remote.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Realex - Remote Mode
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */
 
// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_realex_remote extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'realex_remote';
		$info['longname']		= JText::_('CFG_REALEX_REMOTE_LONGNAME');
		$info['statement']		= JText::_('CFG_REALEX_REMOTE_STATEMENT');
		$info['description']	= JText::_('CFG_REALEX_REMOTE_DESCRIPTION');
		$info['currencies']		= 'EUR,USD,GBP,AUD,CAD,JPY,NZD,CHF,HKD,SGD,SEK,DKK,PLN,NOK,HUF,CZK,MXN,ILS,BRL,MYR,PHP,TWD,THB,ZAR';
		$info['languages']		= AECToolbox::getISO639_1_codes();
		$info['cc_list']		= 'visa,mastercard,laser';
		$info['recurring']		= 0;
		$info['secure']			= 1;

		return $info;
	}

	function getLogoFilename()
	{
		return 'realex.png';
	}

	function settings()
	{
		$settings = array();
		$settings['merchantid']	= 'yourmerchantid';
		$settings['account']	= 'youraccount';
		$settings['secret']		= 'yoursecret';
		$settings['testmode']	= 1;
		$settings['currency']	= 'EUR';
	
		return $settings;
	}

	function backend_settings()
	{
		$settings = array();

		$settings['merchantid']	= array( 'inputC' );
		$settings['account']	= array( 'inputC' );
		$settings['secret']		= array( 'inputC' );
		$settings['testmode']	= array( 'toggle' );
		$settings['currency']	= array( 'list_currency' );

		return $settings;
	}

	function checkoutform( $request )
	{
		$var = $this->getUserform();

		$var = $this->getCCform( $var, array( 'card_type','card_number', 'card_exp_month', 'card_exp_year', 'card_cvv2' ), null );
		
		return $var;
	}

	function createRequestXML( $request )
	{
		$timestamp = strftime("%Y%m%d%H%M%S");

		$amount = (int) round( 100 * $request->items->total->cost['amount'] );

		$xml = '<request type="auth" timestamp="' . $timestamp . '">
				<merchantid>' . $this->settings['merchantid'] . '</merchantid>
				<account>' . $this->settings['account'] . '</account>
				<orderid>' . $request->invoice->id . '</orderid>
				<amount currency="' . $this->settings['currency'] . '">' . $amount . '</amount>
				<card> 
					<number>' . $request->int_var['params']['cardNumber'] . '</number>
					<expdate>' . $request->int_var['params']['expirationMonth'] . substr( $request->int_var['params']['expirationYear'], -2 ) . '</expdate>
					<type>' . $request->int_var['params']['cardType'] . '</type> 
					<chname>' . $request->int_var['params']['billFirstName'] . ' ' .  $request->int_var['params']['billLastName'] . '</chname>
					<cvn>
						<number>' . $request->int_var['params']['cardVV2'] . '</number>
					</cvn>
				</card> 
				<autosettle flag="1"/>
				<md5hash>' . $this->getHash( $request, $timestamp, $amount ) . '</md5hash>
				<tssinfo>
					<address type=\"billing\">
						<country>ie</country>
					</address>
				</tssinfo>
			</request>';


		return $xml;
	}

	function transmitRequestXML( $xml, $request )
	{
		if ( $this->settings['testmode'] ) {
			$url = 'https://epage.payandshop.com/epage-remote.cgi';
		} else {
			$url = 'https://epage.payandshop.com/epage-remote.cgi';
		}

		$response1 = array();
		$response = $this->transmitRequest( $url, '', $xml );
		
		// Tidy it up
		$response = eregi_replace ( "[[:space:]]+", " ", $response );
		$response = eregi_replace ( "[\n\r]", "", $response );

		$tags = array();

		$parser = xml_parser_create();
		xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE, 1 );
		xml_parse_into_struct( $parser, $response, $tags );
		xml_parser_free( $parser );

		$elements = array();
		$stack = array();

		foreach ( $tags as $tag ) {
			$index = count( $elements );

			if ( $tag['type'] == "complete" || $tag['type'] == "open" ) {
				$elements[$index] = array();
				$elements[$index]['name'] = $tag['tag'];
				$elements[$index]['attributes'] = $tag['attributes'];
				$elements[$index]['content'] = $tag['value'];

				if ( $tag['type'] == "open" ) {    # push
					$elements[$index]['children'] = array();
					$stack[count($stack)] = &$elements;
					$elements = &$elements[$index]['children'];
				}
			}

			if ( $tag['type'] == "close" ) {
				$elements = &$stack[count($stack) - 1];
				unset($stack[count($stack) - 1]);
	        }
    	}

		$i=0;
		while ( $elements[0]['children'][$i]['name'] ) {
			switch ( $elements[0]['children'][$i]['name'] ) {
				case 'result': $result_code = $elements[0]['children'][$i]['content']; break;
				case 'message': $result_msg = $elements[0]['children'][$i]['content']; break;
				case 'orderid': $result_orderid = $elements[0]['children'][$i]['content']; break;
				case 'md5hash': $md5hash1 = $elements[0]['children'][$i]['content']; break;
			}

			$i++;
		}

		if ( $result_code == '00' ) {
			$response1['valid'] = 1;
		} else {
			$response['valid'] = 0;
			$response1['error']  = $result_msg . ' - please check the values';
		}

		return $response1;
	}

	function getHash( $request, $timestamp, $amount )
	{
		return md5(	md5($timestamp
					. $this->settings['merchantid']
					. $request->invoice->id
					. $amount
					. $this->settings['currency']
					. $request->int_var['params']['cardNumber']
					) . $this->settings['secret']
				);
	}
}
?>

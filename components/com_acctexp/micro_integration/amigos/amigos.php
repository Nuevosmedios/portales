<?php
/**
 * @version 0.1
 * @package Amigos
 * @author  Dioscouri Design
 * @link    http://www.dioscouri.com
 * @copyright Copyright (C) 2007 Dioscouri Design. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/** ensure this file is being included by a parent file */
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Restricted Access' );

class mi_amigos
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_AMIGOS');
		$info['desc'] = JText::_('AEC_MI_DESC_AMIGOS');
		$info['type'] = array( 'tracking.affiliate', 'vendor.dioscouri' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['amigos_domain']	= array( 'inputC' );
		$settings['amigos_curl']	= array( 'toggle' );

		return $settings;
	}

	function CommonData()
	{
		return array( 'amigos_domain', 'amigos_curl' );
	}

	function invoice_creation( $request )
	{
		if ( !empty( $_REQUEST['amigosid'] ) ) {
			$request->invoice->params['mi_amigos'] = $_REQUEST['amigosid'];
			$request->invoice->storeload();
		}
	}

	function action( $request )
	{
		if ( !empty( $request->invoice->params['mi_amigos'] ) ) {
			$amigosid = $request->invoice->params['mi_amigos'];
		} else {
			if ( empty( $_REQUEST['amigosid'] ) ) {
				return true;
			} else {
				$amigosid = $_REQUEST['amigosid'];
			}
		}

		$domain = $this->settings['amigos_domain'];

		// if domain was incorrectly entered, add http:// to it
		if ( substr( $this->settings['amigos_domain'], 0, 7 ) != 'http://' ) {
			$domain = "http://".$this->settings['amigos_domain'];
		}

		if ( substr( $domain, -1 ) == '/' ) {
			$domain = substr( $domain, 0, -1 );
		}

		$array = array();
		$array["option"]				= "com_amigos";
		$array["task"]					= "sale";
		$array["amigos_id"]				= $amigosid;
		$array["amigos_ordertype"]		= 'com_acctexp';
		$array["amigos_orderid"]		= $request->invoice->invoice_number;
		$array["amigos_orderamount"]	= $request->invoice->amount;
		$array["amigos_ipaddress"]		= $_SERVER['REMOTE_ADDR'];

		$parts = array();
		foreach ( $array as $k => $v ) {
			$parts[] = $k . "=" . $v;
		}

		$url = JURI::root() . 'index.php?' . implode( '&', $parts );

		if ( !empty( $this->settings['amigos_curl'] ) ) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_exec($ch);
			curl_close($ch);
		} else {
			$text = '<img'
					. ' src="' . $url . '"'
					. ' border="0" width="1" height="1" />';

			$db = &JFactory::getDBO();
			$displaypipeline = new displayPipeline();
			$displaypipeline->create( $request->metaUser->userid, 1, 0, 0, null, 1, $text );
		}

		return true;
	}

}
<?php
/**
 * @version $Id: mi_http_query.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Http Query
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_http_query extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_HTTP_QUERY');
		$info['desc'] = JText::_('AEC_MI_DESC_HTTP_QUERY');
		$info['type'] = array( 'basic.server', 'system', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
        $settings = array();
        $settings['url']			= array( 'inputE' );
        $settings['query']			= array( 'inputD' );

        $settings					= $this->autoduplicatesettings( $settings, array(), true, true );

		return $settings;
	}

	function relayAction( $request )
	{
		if ( !isset( $this->settings['url'.$request->area] ) ) {
			return null;
		}

		$url = AECToolbox::rewriteEngineRQ( $this->settings['url'.$request->area], $request );
		$query = AECToolbox::rewriteEngineRQ( $this->settings['query'.$request->area], $request );

		return $this->fetchURL( $this->createURL( $url, $query ) );
	}

	function createURL( $url, $query ) {
		$urlsplit = explode( '?', $url );

		$p = explode( "\n", $query );

		if ( !empty( $urlsplit[1] ) ) {
			$p2 = explode( '&', $urlsplit[1] );

			if ( !empty( $p2 ) ) {
				$p = array_merge( $p2, $p );
			}
		}

		$fullp = array();
		foreach ( $p as $entry ) {
			$e = explode( '=', $entry );

			if ( !empty( $e[0] ) && !empty( $e[1] ) ) {
				$fullp[] = urlencode( trim($e[0]) ) . '=' . urlencode( trim($e[1]) );
			}
		}

		return $urlsplit[0] . '?' . implode( '&', $fullp );
	}

	function fetchURL( $url )
	{
		global $aecConfig;

		if ( strpos( $url, '://' ) === false ) {
			$purl = 'http://' . $url;
		} else {
			$purl = $url;
		}

		$url_parsed = parse_url( $purl );

		$host = $url_parsed["host"];

		if ( empty( $url_parsed["port"] ) ) {
			$port = 80;
		} else {
			$port = $url_parsed["port"];
		}

		$path = $url_parsed["path"];

		// Prevent 400 Error
		if ( empty( $path ) ) {
			$path = "/";
		}

		if ( $url_parsed["query"] != "" ) {
			$path .= "?".$url_parsed["query"];
		}

		if ( $aecConfig->cfg['curl_default'] ) {
			$response = processor::doTheCurl( $url, '' );
		} else {
			$response = processor::doTheHttp( $url, $path, '', $port );
		}

		return true;
	}
}
?>

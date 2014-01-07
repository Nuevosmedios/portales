<?php
/**
 * @version $Id: mi_yourmembership_com.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - YourMembership.com
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_yourmembership_com extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_YOURMEMBERSHIP_COM_NAME');
		$info['desc'] = JText::_('AEC_MI_YOURMEMBERSHIP_COM_DESC');
		$info['type'] = array( 'services.external' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['apikey']				= array( 'inputC' );
		$settings['passcode']			= array( 'inputC' );

		return $settings;
	}

	function CommonData()
	{
		return array( 'apikey' );
	}

	function action( $request )
	{
		$id = uniqid();

		$params = $request->metaUser->meta->custom_params;

		if ( !empty($params['temp_pw']) ) {
			$request = $this->getCallXML( 'Session.Create', $id );

			$result = $this->apiCall( $request );

			$id = $this->XMLsubstring_tag( $result, 'SessionID' );

			$name = $request->metaUser->explodeName();

			$request = $this->getCallXML( 'Sa.Members.Profile.Create', $id, array(	'FirstName' => $name['first'],
																					'LastName' => $name['last'],
																					'EmailAddr' => $request->metaUser->cmsUser->email,
																					'Password' => $params['temp_pw']
																				)
										);
			$result = $this->apiCall( $request );
		}

		return true;
	}

	function on_userchange_action( $request )
	{
		if ( $request->trace == 'registration' ) {
			$password = $this->getPWrequest( $request );

			$db = &JFactory::getDBO();

			$meta = new metaUserDB();
			$meta->loadUserid( $request->row->id );

			$params = $meta->custom_params;

			if ( empty( $meta->custom_params['is_stored'] ) && empty( $meta->custom_params['temp_pw']) && !empty( $request->row->password ) ) {
				$meta->custom_params['temp_pw'] = $password;
		        $meta->storeload();
    		}
		}
	}

	function getCallXML( $function, $id, $data=array(), $session=null )
	{
		$request = '<?xml version="1.0" encoding="utf-8">';
		$request .= '<YourMembership>';
		$request .= '<Version>1.6</Version>';
		$request .= '<ApiKey>' . $this->settings['apikey'] . '</ApiKey>';
		$request .= '<CallID>' . $id . '</CallID>';

		if ( !empty( $session ) ) {
			$request .= '<SessionID>' . $session . '</SessionID>';
		}

		$request .= '<SaPasscode>' . $this->settings['apikey'] . '</SaPasscode>';

		$request .= '<Call Method="' . $function . '">';

		if ( !empty( $data ) ) {
			foreach ( $data as $k => $v ) {
				$request .= '<'.$k.'>'.$v.'</'.$k.'>';
			}
		}

		$request .= '</Call>';
		$request .= '</YourMembership>';

		return $request;
	}

	function apiCall( $request )
	{
		global $aecConfig;

		$path = '';
		$url = "https://api.yourmembership.com";

		if ( $aecConfig->cfg['curl_default'] ) {
			$response = processor::doTheCurl( $url, $request );
			if ( $response === false ) {
				// If curl doesn't work try using fsockopen
				$response = processor::doTheHttp( $url, $path, $request );
			}
		} else {
			$response = processor::doTheHttp( $url, $path, $request );
			if ( $response === false ) {
				// If fsockopen doesn't work try using curl
				$response = processor::doTheCurl( $url, $request );
			}
		}
aecDebug($response);
		return $response;
	}
}
?>

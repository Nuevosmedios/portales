<?php
/**
 * @version $Id: mi_webex.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Webex
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_webex extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_WEBEX_NAME');
		$info['desc'] = JText::_('AEC_MI_WEBEX_DESC');
		$info['type'] = array( 'services.external' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['hosted_name']		= array( 'inputC' );
		$settings['pid']				= array( 'inputC' );
		$settings['create_user']		= array( 'toggle' );
		$settings['activate_user']		= array( 'toggle' );
		$settings['deactivate_user']	= array( 'toggle' );
		//$settings['customparams']		= array( 'inputD' );

		$rewriteswitches				= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function CommonData()
	{
		return array( 'hosted_name', 'pid' );
	}

	function action( $request )
	{
		if ( $this->settings['create_user'] ) {
			$params = $request->metaUser->meta->custom_params;

			if ( !empty($params['temp_pw']) ) {
				$request->metaUser->meta->custom_params['is_stored'] = true;

				unset( $request->metaUser->meta->custom_params['temp_pw'] );

				$request->metaUser->meta->storeload();

				$request->metaUser->cmsUser->password = $params['temp_pw'];
			}

			$this->apiUserSignup( $request );
		}

		return true;
	}

	function expiration_action( $request )
	{
		if ( $this->settings['deactivate_user'] ) {
			$this->apiDeactivateUser( $request );
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

			if ( empty( $meta->custom_params['is_stored'] ) && empty( $meta->custom_params['temp_pw']) && !empty( $request->row->password ) ) {
				$meta->custom_params['temp_pw'] = $password;
		        $meta->storeload();
    		}
		}
	}

	function apiUserSignup( $request )
	{
		$name = $request->metaUser->explodeName();

		$array = array(	'AT' => 'SU',
						'FN' => $name['first'],
						'LN' => $name['last'],
						'EM' => $request->metaUser->cmsUser->email,
						'PW' => $request->metaUser->cmsUser->password,
						'PID' => $this->settings['pid'],
						'WID' => $request->metaUser->username,
		);

		return $this->apiCall( $array, $request );
	}

	function apiActivateUser( $request )
	{
		$array = array(	'AT' => 'AC',
						'PID' => $this->settings['pid'],
						'WID' => $request->metaUser->username,
		);

		return $this->apiCall( $array, $request );
	}

	function apiDeactivateUser( $request )
	{
		$array = array(	'AT' => 'IN',
						'PID' => $this->settings['pid'],
						'WID' => $request->metaUser->username,
		);

		return $this->apiCall( $array, $request );
	}

	function apiCall( $array, $request )
	{
		global $aecConfig;

		if ( empty( $this->settings['hosted_name'] ) ) {
			return false;
		}

		if ( !empty( $this->settings[$array['AT'].'_customparams'] ) ) {
			$rw_params = AECToolbox::rewriteEngineRQ( $this->settings[$array['AT'].'_customparams'], $request );

			if ( strpos( $rw_params, "\r\n" ) !== false ) {
				$cps = explode( "\r\n", $rw_params );
			} else {
				$cps = explode( "\n", $rw_params );
			}

			foreach ( $cps as $cp ) {
				$array[] = $cp;
			}
		}

		$req = array();
		foreach ( $array as $key => $value ) {
			if ( !empty( $value ) ) {
				$req[] = $key.'='.urlencode( stripslashes( $value ) );
			}
		}

		$path = '/' . $this->settings['hosted_name'] . '/m.php?' . implode( '&', $req );
		$url = 'https://' . $this->settings['hosted_name'] . '.webex.com' . $path;

		if ( $aecConfig->cfg['curl_default'] ) {
			$response = processor::doTheCurl( $url, array() );
			if ( $response === false ) {
				// If curl doesn't work try using fsockopen
				$response = processor::doTheHttp( $url, $path, array() );
			}
		} else {
			$response = processor::doTheHttp( $url, $path, array() );
			if ( $response === false ) {
				// If fsockopen doesn't work try using curl
				$response = processor::doTheCurl( $url, array() );
			}
		}

		return $response;
	}
}
?>

<?php
/**
 * @version $Id: mi_aecusersubscriptiondetails.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - User Subscription Details
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

require_once( JPATH_SITE . '/components/com_acctexp/micro_integration/aecuserdetails/aecuserdetails.php' );

class mi_aecusersubscriptiondetails extends mi_aecuserdetails
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_AECUSERSUBSCRIPTIONDETAILS');
		$info['desc'] = JText::_('AEC_MI_DESC_AECUSERSUBSCRIPTIONDETAILS');
		$info['type'] = array( 'aec.membership', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = parent::Settings();
		
		unset( $settings['emulate_reg'] );
		unset( $settings['display_emul'] );

		return $settings;
	}

	function saveParams( $params )
	{
		foreach ( $params as $n => $v ) {
			if ( !empty( $v ) && ( strpos( $n, '_short' ) ) ) {
				$params[$n] = preg_replace( '/[^a-z0-9._+-]+/i', '', trim( strtolower( $v ) ) );
			}
		}

		return $params;
	}

	function before_invoice_confirm( $request )
	{
		return true;
	}

	function action( $request )
	{
			$request->metaUser->focusSubscription->addCustomParams( $request->params );
			$request->metaUser->focusSubscription->storeload();
	}
}
?>

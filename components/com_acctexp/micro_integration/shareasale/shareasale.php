<?php
/**
 * @version $Id: mi_shareasale.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Share a Sale
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_shareasale
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_SHAREASALE');
		$info['desc'] = JText::_('AEC_MI_DESC_SHAREASALE');
		$info['type'] = array( 'tracking.affiliate', 'vendor.shareasale' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['merchantID']			= array( 'inputC' );
		$settings['onlycustomparams']	= array( 'toggle' );
		$settings['customparams']		= array( 'inputD' );

		$rewriteswitches				= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );
		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function CommonData()
	{
		return array( 'merchantID' );
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		$rooturl = "https://shareasale.com/sale.cfm?";

		$getparams = array();

        $user		= JFactory::getUser($invoice->userid);
        $SSAID		= $user->getParam('SSAID');
        $SSAIDDATA	= $user->getParam('SSAIDDATA');

		$getparams[] = 'amount='		. $request->invoice->amount;
		$getparams[] = 'tracking='		. $request->invoice->invoice_number;
		$getparams[] = 'transtype='		. 'sale';
		$getparams[] = 'merchantID='	. $this->settings['merchantID'];
		
		if ( !empty( $this->settings['onlycustomparams'] ) && !empty( $this->settings['customparams'] ) ) {
			$getparams = array();
		}

		if ( !empty( $this->settings['customparams'] ) ) {
			$rw_params = AECToolbox::rewriteEngineRQ( $this->settings['customparams'], $request );

			if ( strpos( $rw_params, "\r\n" ) !== false ) {
				$cps = explode( "\r\n", $rw_params );
			} else {
				$cps = explode( "\n", $rw_params );
			}

			foreach ( $cps as $cp ) {
				$getparams[] = $cp;
			}
		}

		$newget = array();
		foreach ( $getparams as $v ) {
			$va = explode( '=', $v, 2 );

			$newget[] = urlencode($va[0]) . '=' . urlencode($va[1]);
		}

			$text = '<img border="0" '
					.'src="' . $rooturl . implode( '&amp;', $newget ) . '" '
					.'width="1" height="1" />';

			$displaypipeline = new displayPipeline();
			$displaypipeline->create( $request->metaUser->userid, 1, 0, 0, null, 1, $text );

		return true;
	}

}
?>

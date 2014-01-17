<?php
/**
 * @version $Id: mi_pepperjam.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Pepperjam
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_pepperjam
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_PEPPERJAM');
		$info['desc'] = JText::_('AEC_MI_DESC_PEPPERJAM');
		$info['type'] = array( 'tracking.affiliate', 'vendor.pepperjam' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['pid']				= array( 'inputC' );
		$settings['type']				= array( 'list' );
		$settings['onlycustomparams']	= array( 'toggle' );
		$settings['customparams']		= array( 'inputD' );

		$rewriteswitches				= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		if ( !empty( $this->settings['type'] ) ) {
			$t = $this->settings['type'];
		} else {
			$t = array();
		}

 		$type_opts = array();
		$type_opts[] = JHTML::_('select.option', "1", "Sale (Percentage %)" );
		$type_opts[] = JHTML::_('select.option', "2", "Lead (Flat Rate)" );

		$settings['lists']['type']	= JHTML::_('select.genericlist', $type_opts, 'type', 'size="1"', 'value', 'text', $t );

		$del_opts[] = JHTML::_('select.option', "Set", "Delete group(s) selected above, then apply group(s) below." );

		return $settings;
	}

	function CommonData()
	{
		return array( 'pid', 'type' );
	}

	function Defaults()
	{
        $defaults = array();
        $defaults['type']			= '1';

		return $defaults;
	}

	function afteraction( $request )
	{
		if ( empty( $request->invoice->amount ) ) {
			return null;
		}

		$db = &JFactory::getDBO();

		$getparams = array();
		$getparams[] = 'PID=' . $this->settings['pid'];
		$getparams[] = 'AMOUNT=' . $request->invoice->amount;

		if ( !empty( $this->settings['type'] ) ) {
			$getparams[] = 'TYPE=' . $this->settings['type'];
		}

		$getparams[] = 'OID=' . $request->invoice->invoice_number;

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

		$text = '<iframe'
				.' src="https://t.pepperjamnetwork.com/track?' . implode( '&amp;', $newget ) . '"'
				.' width="1" height="1" frameborder="0"></iframe>';

		$displaypipeline = new displayPipeline();
		$displaypipeline->create( $request->metaUser->userid, 1, 0, 0, null, 1, $text );

		return true;
	}

}
?>

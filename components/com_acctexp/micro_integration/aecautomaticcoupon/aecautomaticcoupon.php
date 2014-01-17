<?php
/**
 * @version $Id: mi_aecautomaticcoupon.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Automatic Discount MI
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this option is not allowed.' );

class mi_aecautomaticcoupon
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_AECAUTOMATICOUPON_NAME');
		$info['desc'] = JText::_('AEC_MI_AECAUTOMATICOUPON_DESC');
		$info['type'] = array( 'aec.checkout', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['coupon']		= array( 'inputC' );

		return $settings;
	}

	function invoice_creation( $request )
	{
		if ( !empty( $this->settings['coupon'] ) ) {
			$request->invoice->addCoupon( $this->settings['coupon'] );
		}
	}

}
?>

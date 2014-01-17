<?php
/**
 * @version $Id: tool_coupons.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Toolbox - Coupons
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class tool_coupons
{
	function Info()
	{
		$info = array();
		$info['name'] = "Coupon Creation";
		$info['desc'] = "Make a lot of coupons.";

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$settings = array();
		$settings['master_coupon']		= array( 'inputC', "Master Coupon", "Coupon Code for the coupon that you want to use as a blueprint copy for the settings of the coupons you want to create." );
		$settings['amount']				= array( 'inputC', "Coupon Amount", "How many coupons do you want to create?" );
		$settings['switch_type']		= array( 'toggle', "Switch Type", "If the Master Coupon is 'popular', make copies not 'popular', or the other way around." );
		$settings['max_reuse']			= array( 'inputC', "Max Reuse", "Set a new number of reuses, or leave it at 1." );

		return $settings;
	}

	function Action()
	{
		$return = "";
		if ( !empty( $_POST['master_coupon'] ) ) {
			$db = &JFactory::getDBO();

			$cph = new CouponHandler();
			$cph->load( $_POST['master_coupon'] );

			if ( is_object( $cph->coupon ) ) {
				for ( $i=0; $i<$_POST['amount']; $i++ ) {
					$newcode = $cph->coupon->generateCouponCode();
					$newcodes[] = $newcode;

					$cph->coupon->id = 0;
					$cph->coupon->coupon_code = $newcode;
					$cph->coupon->active = 1;

					$cph->restrictions['usecount'] = 0;

					if ( !empty( $_POST['create_new_coupons'] ) ) {
						$cph->restrictions['max_reuse'] = $_POST['max_reuse'];
					} else {
						$cph->restrictions['max_reuse'] = 1;
					}

					$cph->coupon->storeload();

					if ( !empty( $_POST['switch_type'] ) ) {
						$cph->switchType();
					}
				}
			}

			$return .= '<table class="adminlist">';

			foreach ( $newcodes as $code ) {
				$return .= '<tr>';
				$return .= '<td>' . $code . '</td>';
				$return .= '</tr>';
			}

			$return .= '</table><br /><br />';
		}

		return $return;
	}

}
?>

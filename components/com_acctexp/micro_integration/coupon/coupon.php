<?php
/**
 * @version $Id: mi_coupon.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Coupon
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_coupon
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_COUPON');
		$info['desc'] = JText::_('AEC_MI_DESC_COUPON');
		$info['type'] = array( 'aec.invoice', 'aec.tools', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['master_coupon']		= array( 'inputC' );
		$settings['switch_type']		= array( 'toggle' );
		$settings['bind_subscription']	= array( 'toggle' );
		$settings['create_new_coupons']	= array( 'inputC' );
		$settings['max_reuse']			= array( 'inputC' );
		$settings['mail_out_coupons']	= array( 'toggle' );
		$settings['always_new_coupons']	= array( 'toggle' );
		$settings['inc_old_coupons']	= array( 'inputC' );

		$settings['sender']				= array( 'inputE' );
		$settings['sender_name']		= array( 'inputE' );

		$settings['recipient']			= array( 'inputE' );

		$settings['subject']			= array( 'inputE' );
		$settings['text_html']			= array( 'toggle' );

		if ( !isset( $this->settings['text_html'] ) ) {
			$this->settings['text_html'] = 0;
		}

		$settings['text']				= array( $this->settings['text_html'] ? 'editor' : 'inputD' );

		$rewriteswitches				= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		$userflags = $request->metaUser->focusSubscription->getMIflags( $request->plan->id, $this->id );

		$total_coupons = array();

		$existing_coupons = array();
		if ( is_array( $userflags ) ) {
			if ( !empty( $userflags['coupons'] ) ) {
				$existing_coupons = explode( ',', $userflags['coupons'] );
				$total_coupons = array_merge( $total_coupons, $existing_coupons );

				if ( !empty( $this->settings['inc_old_coupons'] ) ) {
					foreach ( $existing_coupons as $cid ) {
						$ocph = new couponHandler();
						$ocph->load( $cid );
						$ocph->coupon->active = 1;
						$ocph->restrictions['max_reuse'] += $this->settings['inc_old_coupons'];
						$ocph->coupon->setParams( $ocph->restrictions, 'restrictions' );
						$ocph->coupon->check();
						$ocph->coupon->store();
					}
				}
			}
		}

		$newcodes = array();
		if ( ( !empty( $existing_coupons ) && !empty( $this->settings['always_new_coupons'] ) ) || empty( $existing_coupons ) ) {
			if ( !empty( $this->settings['create_new_coupons'] ) && !empty( $this->settings['master_coupon'] ) ) {

				$cph = new CouponHandler();
				$cph->load( $this->settings['master_coupon'] );

				if ( is_object( $cph->coupon ) ) {
					for ( $i=0; $i<$this->settings['create_new_coupons']; $i++ ) {
						$newcode = $cph->coupon->generateCouponCode();
						$newcodes[] = $newcode;

						$cph->coupon->id = 0;
						$cph->coupon->coupon_code = $newcode;
						$cph->coupon->active = 1;

						$cph->restrictions['usecount'] = 0;

						if ( !empty( $this->settings['max_reuse'] ) ) {
							$cph->restrictions['max_reuse'] = $this->settings['max_reuse'];
						} else {
							$cph->restrictions['max_reuse'] = 1;
						}

						if ( !empty( $this->settings['bind_subscription'] ) ) {
							$cph->restrictions['depend_on_subscr_id'] = 1;
							$cph->restrictions['subscr_id_dependency'] = $request->metaUser->focusSubscription->id;
						}

						$cph->coupon->storeload();

						if ( !empty( $this->settings['switch_type'] ) ) {
							$cph->switchType();
						}
					}

					if ( !empty( $this->settings['mail_out_coupons'] ) ) {
						$this->mailOut( $request, $newcodes );
					}
				}
			}
		}

		$total_coupons = array_merge( $total_coupons, $newcodes );

		$newflags['coupons'] = implode( ',', $total_coupons );

		$request->metaUser->focusSubscription->setMIflags( $plan->id, $this->id, $newflags );

		return true;
	}

	function mailOut( $request, $newcodes )
	{
		$codelist = "";
		if ( $this->settings['text_html'] ) {
			foreach ( $newcodes as $code ) {
				$codelist .= "<p>" . $code . "</p>";
			}
		} else {
			$codelist = implode( "\n", $newcodes );
		}

		$message	= sprintf( $this->settings['text'], $codelist );

		$message	= AECToolbox::rewriteEngineRQ( $message, $request );
		$subject	= AECToolbox::rewriteEngineRQ( $this->settings['subject'], $request );

		if ( empty( $message ) ) {
			return false;
		}

		$recipients = explode( ',', $this->settings['recipient'] );

		foreach ( $recipients as $current => $email ) {
			$recipients[$current] = AECToolbox::rewriteEngineRQ( trim( $email ), $request );
		}

		xJ::sendMail( $this->settings['sender'], $this->settings['sender_name'], $recipients, $subject, $message, $this->settings['text_html'] );

		return true;
	}

}
?>

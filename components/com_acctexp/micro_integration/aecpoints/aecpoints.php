<?php
/**
 * @version $Id: mi_aecpoints.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - AEC Points
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_aecpoints extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_AECPOINTS');
		$info['desc'] = JText::_('AEC_MI_DESC_AECPOINTS');
		$info['type'] = array( 'ecommerce.credits', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();

		$settings['change_points']			= array( 'inputB' );

		$settings = $this->autoduplicatesettings( $settings );

		$xsettings = array();
		$xsettings['checkout_discount']			= array( 'toggle' );
		$xsettings['checkout_conversion']		= array( 'inputB' );

		return array_merge( $xsettings, $settings );
	}

	function getMIform( $request )
	{
		$settings = array();

		$points = $this->getPoints( $request );

		if ( $this->settings['checkout_discount'] && $points ) {
			if ( !empty( $request->invoice->currency ) ) {
				$currency = $request->invoice->currency;
			} else {
				global $aecConfig;

				$currency = $aecConfig->cfg['standard_currency'];
			}
			
			$value = AECToolbox::formatAmount( $this->settings['checkout_conversion'], $currency, false );
			$total = AECToolbox::formatAmount( ( $points * $this->settings['checkout_conversion'] ), $currency );

			$settings['vat_desc'] = array( 'p', "", sprintf( JText::_('MI_MI_AECPOINTS_CONVERSION_INFO'), $points, $value, $total ) );
			$settings['use_points'] = array( 'inputC', JText::_('MI_MI_AECPOINTS_USE_POINTS_NAME'), JText::_('MI_MI_AECPOINTS_USE_POINTS_DESC'), '' );

			$settings['validation']['rules'] = array();
			$settings['validation']['rules']['use_points'] = array( 'number' => true, 'min' => 0, 'max' => $this->getPoints( $request ) );
		}

		return $settings;
	}

	function verifyMIform( $request )
	{
		$return = array();

		if ( !empty( $request->params['use_points'] ) ) {
			$request->params['use_points'] = (int) $request->params['use_points'];

			if ( $request->params['use_points'] > $this->getPoints( $request ) ) {
				$return['error'] = "You don't have that many points";
			}
		}

		return $return;
	}

	function relayAction( $request )
	{
		if ( $request->action == 'action' ) {
			if ( !empty( $this->settings['plan_apply_first'] ) ) {
				if ( empty( $request->metaUser->objSubscription->previous_plan ) ) {
					$request->area = '_first';
				}
			}
		}

		$invoice = "";
		if ( !empty( $request->invoice->invoice_number ) ) {
			$invoice = $request->invoice->invoice_number;
		}

		if ( $request->action == 'action' ) {
			$params = $request->metaUser->meta->getMIParams( $request->parent->id, $request->plan->id );

			if ( $params['use_points'] > 0 ) {
				$points = -$params['use_points'];

				$this->updatePoints( $request, $points, 'mi_action_'.$request->action, $invoice );

				unset( $params['use_points'] );

				$request->metaUser->meta->setMIParams( $request->parent->id, $request->plan->id, $params, true );

				$request->metaUser->meta->storeload();
			}
		}

		if ( empty( $this->settings['change_points'.$request->area] ) ) {
			return null;
		}

		$this->updatePoints( $request, $this->settings['change_points'.$request->area], 'mi_action_'.$request->action, $invoice );

		return true;
	}

	function invoice_item_cost( $request )
	{
		if ( $this->settings['checkout_discount'] && !empty( $this->settings['checkout_conversion'] ) && !empty( $request->params['use_points'] ) ) {
			return $this->modifyPrice( $request );
		} else {
			return null;
		}
	}

	function modifyPrice( $request )
	{
		$discount = AECToolbox::correctAmount( $request->params['use_points'] * $this->settings['checkout_conversion'] );

		$original_price = $request->add['terms']->nextterm->renderTotal();

		if ( $discount > $original_price ) {
			$discount = $original_price;

			$request->params['use_points'] = (int) $discount / $this->settings['checkout_conversion'];

			if ( ( $request->params['use_points'] * $this->settings['checkout_conversion'] ) < $original_price ) {
				$request->params['use_points']++;
			}
		}

		$request->add['terms']->nextterm->discount( $discount, null, array( 'details' => $request->params['use_points'] . " Points" ) );

		return true;
	}

	function getPoints( $request )
	{
		$uparams = $request->metaUser->meta->getCustomParams();

		if ( isset( $uparams['mi_aecpoints']['points'] ) ) {
			return $uparams['mi_aecpoints']['points'];
		}

		return 0;
	}

	function updatePoints( $request, $points, $event="", $invoice="" )
	{
		if ( empty( $points ) ) {
			return;
		}

		$uparams = $request->metaUser->meta->getCustomParams();

		if ( !isset( $uparams['mi_aecpoints']['points'] ) ) {
			$uparams['mi_aecpoints'] = array( 'points' => 0, 'history' => array() );
		}

		$uparams['mi_aecpoints']['points'] = $uparams['mi_aecpoints']['points'] + $points;

		$history = array(	'time' => (int) gmdate('U'),
							'event' => $event,
							'invoice' => $invoice  );

		$uparams['mi_aecpoints']['history'][] = $history;

		$request->metaUser->meta->setCustomParams( $uparams );

		$request->metaUser->meta->storeload();
	}
}
?>

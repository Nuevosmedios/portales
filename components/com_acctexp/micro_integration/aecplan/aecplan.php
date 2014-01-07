<?php
/**
 * @version $Id: mi_aecplan.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - AEC Plan Application
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_aecplan
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_AECPLAN_NAME');
		$info['desc'] = JText::_('AEC_MI_AECPLAN_DESC');
		$info['type'] = array( 'aec.membership', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id` AS value, `name` AS text'
				. ' FROM #__acctexp_plans'
				. ' WHERE `active` = 1'
				;
		$db->setQuery( $query );
		$plans = $db->loadObjectList();

		$payment_plans = array_merge( array( JHTML::_('select.option', '0', "- " . JText::_('PAYPLAN_NOPLAN') . " -" ) ), $plans );

		$total_plans = min( max( (count( $payment_plans ) + 1 ), 4 ), 20 );

		if ( !isset( $this->settings['plan_apply_first'] ) ) {
			$this->settings['plan_apply_first'] = 0;
		}

		if ( !isset( $this->settings['plan_apply'] ) ) {
			$this->settings['plan_apply'] = 0;
		}

		if ( !isset( $this->settings['plan_apply_pre_exp'] ) ) {
			$this->settings['plan_apply_pre_exp'] = 0;
		}

		if ( !isset( $this->settings['plan_apply_exp'] ) ) {
			$this->settings['plan_apply_exp'] = 0;
		}

		$settings = array();

		$settings['first_plan_not_membership']		= array( 'toggle' );

		$settings['plan_apply_first']				= array( 'list' );
		$settings['lists']['plan_apply_first']		= JHTML::_('select.genericlist', $payment_plans, 'plan_apply_first', 'size="' . $total_plans . '"', 'value', 'text', $this->settings['plan_apply_first'] );
		$settings['first_plan_copy_expiration']		= array( 'toggle' );

		$settings['plan_apply']						= array( 'list' );
		$settings['lists']['plan_apply']			= JHTML::_('select.genericlist', $payment_plans, 'plan_apply', 'size="' . $total_plans . '"', 'value', 'text', $this->settings['plan_apply'] );
		$settings['plan_copy_expiration']			= array( 'toggle' );

		$settings['plan_apply_pre_exp']				= array( 'list' );
		$settings['lists']['plan_apply_pre_exp']	= JHTML::_('select.genericlist', $payment_plans, 'plan_apply_pre_exp', 'size="' . $total_plans . '"', 'value', 'text', $this->settings['plan_apply_pre_exp'] );

		$settings['plan_apply_exp']					= array( 'list' );
		$settings['lists']['plan_apply_exp']		= JHTML::_('select.genericlist', $payment_plans, 'plan_apply_exp', 'size="' . $total_plans . '"', 'value', 'text', $this->settings['plan_apply_exp'] );

		return $settings;
	}

	function relayAction( $request )
	{
		if ( $request->action == 'action' ) {
			// Do NOT act on regular action call
			return null;
		}

		if ( $request->area == 'afteraction' ) {
			// But on after action
			$request->area = '';

			// Or maybe this is a first plan?
			if ( !empty( $this->settings['plan_apply_first'] ) ) {
				if ( !empty( $this->settings['first_plan_not_membership'] ) ) {
					$used_plans = $request->metaUser->meta->getUsedPlans();

					if ( empty( $used_plans ) ) {
						$request->area = '_first';
					} else {
						if ( !in_array( $request->plan->id, $used_plans ) ) {
							$request->area = '_first';
						}
					}
				} else {
					if ( empty( $request->metaUser->objSubscription->previous_plan ) ) {
						$request->area = '_first';
					}
				}
			}
		}

		if ( !isset( $this->settings['plan_apply'.$request->area] ) ) {
			return null;
		}

		if ( empty( $this->settings['plan_apply'.$request->area] ) ) {
			return null;
		}

		if ( $request->action == 'action' ) {
			if ( !empty( $this->settings['plan_apply_first'] ) ) {
				if ( empty( $request->metaUser->objSubscription->previous_plan ) ) {
					$request->area = '_first';
				}
			}
		}

		$expiration = $request->metaUser->focusSubscription->expiration;

		$db = &JFactory::getDBO();

		$new_plan = new SubscriptionPlan();
		$new_plan->load( $this->settings['plan_apply'.$request->area] );

		$request->metaUser->establishFocus( $new_plan, 'none', false );

		$new_plan->applyPlan( $request->metaUser );

		if ( ( ( $request->area == '_first' ) && !empty( $this->settings['first_plan_copy_expiration'] ) )
			|| ( empty( $request->area ) && !empty( $this->settings['plan_copy_expiration'] ) ) ) {
			$request->metaUser->focusSubscription->expiration = $expiration;

			$request->metaUser->focusSubscription->storeload();
		}

		return true;
	}
}
?>

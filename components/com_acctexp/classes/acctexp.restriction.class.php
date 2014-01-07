<?php
/**
 * @version $Id: acctexp.restriction.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class aecRestrictionHelper
{
	function checkRestriction( $restrictions, $metaUser )
	{
		if ( count( $restrictions ) ) {
			$status = array();

			if ( isset( $restrictions['custom_restrictions'] ) ) {
				$status = array_merge( $status, $metaUser->CustomRestrictionResponse( $restrictions['custom_restrictions'] ) );
				unset( $restrictions['custom_restrictions'] );
			}

			$status = array_merge( $status, $metaUser->permissionResponse( $restrictions ) );

			if ( count( $status ) ) {
				foreach ( $status as $stname => $ststatus ) {
					if ( !$ststatus ) {
						return false;
					}
				}
			}
		}

		return true;
	}

	function getRestrictionsArray( $restrictions )
	{
		$newrest = array();

		// Check for a fixed GID - this certainly overrides the others
		if ( !empty( $restrictions['fixgid_enabled'] ) ) {
			$newrest['fixgid'] = (int) $restrictions['fixgid'];
		} else {
			// No fixed GID, check for min GID
			if ( !empty( $restrictions['mingid_enabled'] ) ) {
				$newrest['mingid'] = (int) $restrictions['mingid'];
			}
			// Check for max GID
			if ( !empty( $restrictions['maxgid_enabled'] ) ) {
				$newrest['maxgid'] = (int) $restrictions['maxgid'];
			}
		}

		// First we sort out the group restrictions and convert them into plan restrictions

		// Check for a directly previously used group
		if ( !empty( $restrictions['previousgroup_req_enabled'] ) ) {
			if ( !empty( $restrictions['previousgroup_req'] ) ) {
				$restrictions = aecRestrictionHelper::addGroupPlans( $restrictions, 'previousgroup_req', 'previousplan_req' );

				$restrictions['previousplan_req_enabled'] = true;
			}
		}

		// Check for a directly previously used group
		if ( !empty( $restrictions['previousgroup_req_enabled_excluded'] ) ) {
			if ( !empty( $restrictions['previousgroup_req_excluded'] ) ) {
				$restrictions = aecRestrictionHelper::addGroupPlans( $restrictions, 'previousgroup_req_excluded', 'previousplan_req_excluded' );

				$restrictions['previousplan_req_enabled_excluded'] = true;
			}
		}

		// Check for a currently used group
		if ( !empty( $restrictions['currentgroup_req_enabled'] ) ) {
			if ( !empty( $restrictions['currentgroup_req'] ) ) {
				$restrictions = aecRestrictionHelper::addGroupPlans( $restrictions, 'currentgroup_req', 'currentplan_req' );

				$restrictions['currentplan_req_enabled'] = true;
			}
		}

		// Check for a currently used group
		if ( !empty( $restrictions['currentgroup_req_enabled_excluded'] ) ) {
			if ( !empty( $restrictions['currentgroup_req_excluded'] ) ) {
				$restrictions = aecRestrictionHelper::addGroupPlans( $restrictions, 'currentgroup_req_excluded', 'currentplan_req_excluded' );

				$restrictions['currentplan_req_enabled_excluded'] = true;
			}
		}

		// Check for a overall used group
		if ( !empty( $restrictions['overallgroup_req_enabled'] ) ) {
			if ( !empty( $restrictions['overallgroup_req'] ) ) {
				$restrictions = aecRestrictionHelper::addGroupPlans( $restrictions, 'overallgroup_req', 'overallplan_req' );

				$restrictions['overallplan_req_enabled'] = true;
			}
		}

		// Check for a overall used group
		if ( !empty( $restrictions['overallgroup_req_enabled_excluded'] ) ) {
			if ( !empty( $restrictions['overallgroup_req_excluded'] ) ) {
				$restrictions = aecRestrictionHelper::addGroupPlans( $restrictions, 'overallgroup_req_excluded', 'overallplan_req_excluded' );

				$restrictions['overallplan_req_enabled_excluded'] = true;
			}
		}

		// And now we prepare the individual plan restrictions

		// Check for a directly previously used plan
		if ( !empty( $restrictions['previousplan_req_enabled'] ) ) {
			if ( !empty( $restrictions['previousplan_req'] ) ) {
				$newrest['plan_previous'] = $restrictions['previousplan_req'];
			}
		}

		// Check for a directly previously used plan
		if ( !empty( $restrictions['previousplan_req_enabled_excluded'] ) ) {
			if ( !empty( $restrictions['previousplan_req_excluded'] ) ) {
				$newrest['plan_previous_excluded'] = $restrictions['previousplan_req_excluded'];
			}
		}

		// Check for a currently used plan
		if ( !empty( $restrictions['currentplan_req_enabled'] ) ) {
			if ( !empty( $restrictions['currentplan_req'] ) ) {
				$newrest['plan_present'] = $restrictions['currentplan_req'];
			}
		}

		// Check for a currently used plan
		if ( !empty( $restrictions['currentplan_req_enabled_excluded'] ) ) {
			if ( !empty( $restrictions['currentplan_req_excluded'] ) ) {
				$newrest['plan_present_excluded'] = $restrictions['currentplan_req_excluded'];
			}
		}

		// Check for a overall used plan
		if ( !empty( $restrictions['overallplan_req_enabled'] ) ) {
			if ( !empty( $restrictions['overallplan_req'] ) ) {
				$newrest['plan_overall'] = $restrictions['overallplan_req'];
			}
		}

		// Check for a overall used plan
		if ( !empty( $restrictions['overallplan_req_enabled_excluded'] ) ) {
			if ( !empty( $restrictions['overallplan_req_excluded'] ) ) {
				$newrest['plan_overall_excluded'] = $restrictions['overallplan_req_excluded'];
			}
		}

		if ( !empty( $restrictions['used_plan_min_enabled'] ) ) {
			if ( !empty( $restrictions['used_plan_min_amount'] ) && isset( $restrictions['used_plan_min'] ) ) {
				if ( !isset( $newrest['plan_amount_min'] ) ) {
					$newrest['plan_amount_min'] = array();
				}

				if ( is_array( $restrictions['used_plan_min'] ) ) {
					foreach ( $restrictions['used_plan_min'] as $planid ) {
						if ( $planid ) {
							$newrest['plan_amount_min'][] = ( (int) $planid ) . ',' . ( (int) $restrictions['used_plan_min_amount'] );
						}
					}
				} else {
					$newrest['plan_amount_min'][] = array( ( (int) $restrictions['used_plan_min'] ) . ',' . ( (int) $restrictions['used_plan_min_amount'] ) );
				}
			}
		}

		// Check for a overall used group with amount minimum
		if ( !empty( $restrictions['used_group_min_enabled'] ) ) {
			if ( !empty( $restrictions['used_group_min_amount'] ) && isset( $restrictions['used_group_min'] ) ) {
				$temp = aecRestrictionHelper::addGroupPlans( $restrictions, 'used_group_min', 'used_plan_min', array() );

				$ps = array();
				foreach ( $temp['used_plan_min'] as $planid ) {
					if ( $planid ) {
						$newrest['plan_amount_min'][] = ( (int) $planid ) . ',' . ( (int) $restrictions['used_group_min_amount'] );
					}
				}
			}
		}

		// Check for a overall used plan with amount maximum
		if ( !empty( $restrictions['used_plan_max_enabled'] ) ) {
			if ( !empty( $restrictions['used_plan_max_amount'] ) && isset( $restrictions['used_plan_max'] ) ) {
				if ( !isset( $newrest['plan_amount_max'] ) ) {
					$newrest['plan_amount_max'] = array();
				}

				if ( is_array( $restrictions['used_plan_max'] ) ) {
					foreach ( $restrictions['used_plan_max'] as $planid ) {
						if ( $planid ) {
							$newrest['plan_amount_max'][] = ( (int) $planid ) . ',' . ( (int) $restrictions['used_plan_max_amount'] );
						}
					}
				} else {
					$newrest['plan_amount_max'][] = ( (int) $restrictions['used_plan_max'] ) . ',' . ( (int) $restrictions['used_plan_max_amount'] );
				}
			}
		}

		// Check for a overall used group with amount maximum
		if ( !empty( $restrictions['used_group_max_enabled'] ) ) {
			if ( !empty( $restrictions['used_group_max_amount'] ) && isset( $restrictions['used_group_max'] ) ) {
				$temp = aecRestrictionHelper::addGroupPlans( $restrictions, 'used_group_max', 'used_plan_max', array() );

				$ps = array();
				foreach ( $temp['used_plan_max'] as $planid ) {
					if ( $planid ) {
						$newrest['plan_amount_max'][] = ( (int) $planid ) . ',' . ( (int) $restrictions['used_group_max_amount'] );
					}
				}
			}
		}

		// Check for custom restrictions
		if ( !empty( $restrictions['custom_restrictions_enabled'] ) ) {
			if ( !empty( $restrictions['custom_restrictions'] ) ) {
				$newrest['custom_restrictions'] = aecRestrictionHelper::transformCustomRestrictions( $restrictions['custom_restrictions'] );
			}
		}

		return $newrest;
	}

	function addGroupPlans( $source, $gkey, $pkey, $target=null )
	{
		$okey = str_replace( '_req', '_req_enabled', $pkey );

		if ( !is_array( $source[$pkey] ) || empty($source[$okey]) ) {
			$plans = array();
		} else {
			$plans = $source[$pkey];
		}

		$newplans = ItemGroupHandler::getGroupsPlans( $source[$gkey] );

		$plans = array_merge( $plans, $newplans );

		$plans = array_unique( $plans );

		if ( is_null( $target ) ) {
			$restrictions[$pkey] = $plans;

			return $restrictions;
		} else {
			$target[$pkey] = $plans;

			return $target;
		}
	}

	function transformCustomRestrictions( $customrestrictions )
	{
		$cr = explode( "\n", $customrestrictions);

		$custom = array();
		foreach ( $cr as $field ) {
			// WAT?! yes.
			if ( strpos( nl2br( substr( $field, -1, 1 ) ), "<br" ) !== false ) {
				$field = substr( $field, 0, -1 );
			}

			$custom[] = explode( ' ', $field, 3 );
		}

		return $custom;
	}

	function paramList()
	{
		return array( 'mingid_enabled', 'mingid', 'fixgid_enabled', 'fixgid',
						'maxgid_enabled', 'maxgid', 'previousplan_req_enabled', 'previousplan_req',
						'currentplan_req_enabled', 'currentplan_req', 'overallplan_req_enabled', 'overallplan_req',
						'previousplan_req_enabled_excluded', 'previousplan_req_excluded', 'currentplan_req_enabled_excluded', 'currentplan_req_excluded',
						'overallplan_req_enabled_excluded', 'overallplan_req_excluded', 'used_plan_min_enabled', 'used_plan_min_amount',
						'used_plan_min', 'used_plan_max_enabled', 'used_plan_max_amount', 'used_plan_max',
						'custom_restrictions_enabled', 'custom_restrictions', 'previousgroup_req_enabled', 'previousgroup_req',
						'previousgroup_req_enabled_excluded', 'previousgroup_req_excluded', 'currentgroup_req_enabled', 'currentgroup_req',
						'currentgroup_req_enabled_excluded', 'currentgroup_req_excluded', 'overallgroup_req_enabled', 'overallgroup_req',
						'overallgroup_req_enabled_excluded', 'overallgroup_req_excluded', 'used_group_min_enabled', 'used_group_min_amount',
						'used_group_min', 'used_group_max_enabled', 'used_group_max_amount', 'used_group_max' );
	}

	function getParams()
	{
		$params = array();
		$params['mingid_enabled']					= array( 'toggle', 0 );
		$params['mingid']							= array( 'list', 18 );
		$params['fixgid_enabled']					= array( 'toggle', 0 );
		$params['fixgid']							= array( 'list', 19 );
		$params['maxgid_enabled']					= array( 'toggle', 0 );
		$params['maxgid']							= array( 'list', 21 );
		$params['previousplan_req_enabled'] 		= array( 'toggle', 0 );
		$params['previousplan_req']					= array( 'list', 0 );
		$params['previousplan_req_enabled_excluded']	= array( 'toggle', 0 );
		$params['previousplan_req_excluded']			= array( 'list', 0 );
		$params['currentplan_req_enabled']			= array( 'toggle', 0 );
		$params['currentplan_req']					= array( 'list', 0 );
		$params['currentplan_req_enabled_excluded']	= array( 'toggle', 0 );
		$params['currentplan_req_excluded']			= array( 'list', 0 );
		$params['overallplan_req_enabled']			= array( 'toggle', 0 );
		$params['overallplan_req']					= array( 'list', 0 );
		$params['overallplan_req_enabled_excluded']	= array( 'toggle', 0 );
		$params['overallplan_req_excluded']			= array( 'list', 0 );
		$params['used_plan_min_enabled']			= array( 'toggle', 0 );
		$params['used_plan_min_amount']				= array( 'inputB', 0 );
		$params['used_plan_min']					= array( 'list', 0 );
		$params['used_plan_max_enabled']			= array( 'toggle', 0 );
		$params['used_plan_max_amount']				= array( 'inputB', 0 );
		$params['used_plan_max']					= array( 'list', 0 );
		$params['previousgroup_req_enabled'] 		= array( 'toggle', 0 );
		$params['previousgroup_req']				= array( 'list', 0 );
		$params['previousgroup_req_enabled_excluded']	= array( 'toggle', 0 );
		$params['previousgroup_req_excluded']		= array( 'list', 0 );
		$params['currentgroup_req_enabled']			= array( 'toggle', 0 );
		$params['currentgroup_req']					= array( 'list', 0 );
		$params['currentgroup_req_enabled_excluded']	= array( 'toggle', 0 );
		$params['currentgroup_req_excluded']		= array( 'list', 0 );
		$params['overallgroup_req_enabled']			= array( 'toggle', 0 );
		$params['overallgroup_req']					= array( 'list', 0 );
		$params['overallgroup_req_enabled_excluded']	= array( 'toggle', 0 );
		$params['overallgroup_req_excluded']		= array( 'list', 0 );
		$params['used_group_min_enabled']			= array( 'toggle', 0 );
		$params['used_group_min_amount']			= array( 'inputB', 0 );
		$params['used_group_min']					= array( 'list', 0 );
		$params['used_group_max_enabled']			= array( 'toggle', 0 );
		$params['used_group_max_amount']			= array( 'inputB', 0 );
		$params['used_group_max']					= array( 'list', 0 );
		$params['custom_restrictions_enabled']		= array( 'toggle', '' );
		$params['custom_restrictions']				= array( 'inputD', '' );

		return $params;
	}

	function getLists( $params_values, $restrictions_values )
	{
		$db = &JFactory::getDBO();

		$user = &JFactory::getUser();

		$gtree = xJACLhandler::getGroupTree( array( 28, 29, 30 ) );

		// Create GID related Lists
		$lists['gid'] 		= JHTML::_( 'select.genericlist', $gtree, 'gid', 'size="6"', 'value', 'text', arrayValueDefault($params_values, 'gid', ( defined( 'JPATH_MANIFESTS' ) ? 2 : 18 )) );
		$lists['mingid'] 	= JHTML::_( 'select.genericlist', $gtree, 'mingid', 'size="6"', 'value', 'text', arrayValueDefault($restrictions_values, 'mingid', ( defined( 'JPATH_MANIFESTS' ) ? 2 : 18 )) );
		$lists['fixgid'] 	= JHTML::_( 'select.genericlist', $gtree, 'fixgid', 'size="6"', 'value', 'text', arrayValueDefault($restrictions_values, 'fixgid', ( defined( 'JPATH_MANIFESTS' ) ? 3 : 19 )) );
		$lists['maxgid'] 	= JHTML::_( 'select.genericlist', $gtree, 'maxgid', 'size="6"', 'value', 'text', arrayValueDefault($restrictions_values, 'maxgid', ( defined( 'JPATH_MANIFESTS' ) ? 4 : 21 )) );

		$available_plans = array();

		// Fetch Payment Plans
		$query = 'SELECT `id` AS value, `name` AS text'
				. ' FROM #__acctexp_plans'
				;
		$db->setQuery( $query );
		$plans = $db->loadObjectList();

	 	if ( empty( $plans ) ) {
	 		$plans = array();
	 	} else {
	 		$all_plans	= $available_plans;
	 	}

		$planrest = array( 'previousplan_req', 'currentplan_req', 'overallplan_req', 'used_plan_min', 'used_plan_max', 'previousplan_req_excluded', 'currentplan_req_excluded', 'overallplan_req_excluded'  );

		foreach ( $planrest as $name ) {
			$lists[$name] = JHTML::_( 'select.genericlist', $plans, $name.'[]', 'size="1" multiple="multiple"', 'value', 'text', arrayValueDefault($restrictions_values, $name, 0) );
		}

		$available_groups = array();

		// Fetch Item Groups
		$query = 'SELECT `id` AS value, `name` AS text'
				. ' FROM #__acctexp_itemgroups'
				;
		$db->setQuery( $query );
		$groups = $db->loadObjectList();

	 	if ( empty( $groups ) ) {
	 		$groups = array();
	 	}

		$grouprest = array( 'previousgroup_req', 'currentgroup_req', 'overallgroup_req', 'used_group_min', 'used_group_max', 'previousgroup_req_excluded', 'currentgroup_req_excluded', 'overallgroup_req_excluded' );

		foreach ( $grouprest as $name ) {
			$lists[$name] = JHTML::_( 'select.genericlist', $groups, $name.'[]', 'size="1" multiple="multiple"', 'value', 'text', arrayValueDefault($restrictions_values, $name, 0) );
		}

		return $lists;
	}

	function echoSettings( $aecHTML )
	{
		$stdvars =	array(	array(
									array( 'mingid_enabled', 'mingid' ),
									array( 'fixgid_enabled', 'fixgid' ),
									array( 'maxgid_enabled', 'maxgid' )
							),
							array(
									array( 'custom_restrictions_enabled', 'custom_restrictions' )
							),	array(
									array( 'previous*_req_enabled', 'previous*_req' ),
									array( 'previous*_req_enabled_excluded', 'previous*_req_excluded' ),
									array( 'current*_req_enabled', 'current*_req' ),
									array( 'current*_req_enabled_excluded', 'current*_req_excluded' ),
									array( 'overall*_req_enabled', 'overall*_req' ),
									array( 'overall*_req_enabled_excluded', 'overall*_req_excluded' )
							), array(
									array( 'used_*_min_enabled', 'used_*_min_amount', 'used_*_min' ),
									array( 'used_*_max_enabled', 'used_*_max_amount', 'used_*_max' )
							)
					);

		$types = array( 'plan', 'group' );

		foreach ( $types as $type ) {
			foreach ( $stdvars as $block ) {
				// non-* blocks only once
				if ( ( strpos( $block[0][0], '*' ) === false ) && ( $type != 'plan') ) {
					continue;
				}

				echo '<div class="aec_userinfobox_sub">';

				$firstitem = str_replace( '*', $type, $block[0][0] );
				echo '<h4>' . JText::_( strtoupper( 'aec_restrictions_' . substr( $firstitem, 0, strpos( $firstitem, '_', strpos( $firstitem, '_' )+3 ) ) . '_header' ) )  . '</h4>';

				foreach ( $block as $sblock ) {

					if ( count( $block ) < 2 ) {
						echo '<div class="aec_userinfobox_sub_inline">';
					} else {
						echo '<div class="aec_userinfobox_sub_inline form-stacked" style="width:214px;">';
					}

					foreach ( $sblock as $vname ) {
						echo $aecHTML->createSettingsParticle( str_replace( '*', $type, $vname ) );
					}
					echo '</div>';
				}
				echo '</div>';
			}
		}
	}
}

?>

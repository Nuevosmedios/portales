<?php
/**
 * @version $Id: mi_jomsocial.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - JomSocial
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_jomsocial extends MI
{
	function Info ()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_JOMSOCIAL');
		$info['desc'] = JText::_('AEC_MI_DESC_JOMSOCIAL');
		$info['type'] = array( 'community.social', 'vendor.azrul' );

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$psettings['change_points']			= array( 'inputB' );

		$psettings = $this->autoduplicatesettings( $psettings );

		$xsettings = array();
		$xsettings['aectab_discount']			= array( 'tab', 'Userpoints Discount', 'Userpoints Discount' );
		$xsettings['checkout_discount']			= array( 'toggle' );
		$xsettings['checkout_conversion']		= array( 'inputB' );
		$xsettings['aectab_action']				= array( 'tab', 'Userpoints - Action', 'Userpoints - Action' );

		$settings = array();
		$settings['overwrite_existing']	= array( 'toggle' );
		$settings['assign_group']		= array( 'inputC' );
		$settings['remove_group']		= array( 'inputC' );
		$settings['assign_group_exp']	= array( 'inputC' );
		$settings['remove_group_exp']	= array( 'inputC' );
		$settings['set_fields']			= array( 'toggle' );
		$settings['set_fields_exp']		= array( 'toggle' );

		$query = 'SELECT `id`, `name`'
				. ' FROM #__community_fields'
				. ' WHERE `type` != \'group\''
				;
		$db->setQuery( $query );
		$objects = $db->loadObjectList();

		if ( !empty( $objects ) ) {
			foreach ( $objects as $object ) {
				$settings['jsfield_' . $object->id] = array( 'inputE', $object->name, $object->name );
				$expname = $object->name . " " . JText::_('MI_MI_JOMSOCIAL_EXPMARKER');
				$settings['jsfield_' . $object->id . '_exp' ] = array( 'inputE', $expname, $expname );
			}
		}

		$rewriteswitches			= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

		$settings					= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return array_merge( $settings, $xsettings, $psettings );
	}

	function getMIform( $request )
	{
		$settings = array();

		$points = $this->getPoints( $request->metaUser->userid );

		if ( $this->settings['checkout_discount'] && $points ) {
			if ( !empty( $request->invoice->currency ) ) {
				$currency = $request->invoice->currency;
			} else {
				global $aecConfig;

				$currency = $aecConfig->cfg['standard_currency'];
			}
			
			$value = AECToolbox::formatAmount( $this->settings['checkout_conversion'], $currency );
			$total = AECToolbox::formatAmount( ( $points * $this->settings['checkout_conversion'] ), $currency );

			$settings['vat_desc'] = array( 'p', "", sprintf( JText::_('MI_MI_JOMSOCIALUSERPOINTS_CONVERSION_INFO'), $points, $value, $total ) );
			$settings['use_points'] = array( 'inputC', JText::_('MI_MI_JOMSOCIALPOINTS_USE_POINTS_NAME'), JText::_('MI_MI_JOMSOCIALPOINTS_USE_POINTS_DESC'), '' );

			$settings['validation']['rules'] = array();
			$settings['validation']['rules']['use_points'] = array( 'max' => $this->getPoints( $request->metaUser->userid ) );
		}

		return $settings;
	}

	function verifyMIform( $request )
	{
		$return = array();

		if ( !empty( $request->params['use_points'] ) ) {
			$request->params['use_points'] = (int) $request->params['use_points'];

			if ( $request->params['use_points'] > $this->getPoints( $request->metaUser->userid ) ) {
				$return['error'] = "You don't have that many points";
			}
		}

		return $return;
	}

	function relayAction( $request )
	{
		if ( ( $request->action == 'action' ) || ( $request->action == 'expiration_action' ) ) {
			$db = &JFactory::getDBO();

			if ( $this->settings['set_fields'.$request->area] ) {
				$query = 'SELECT `id`, `name`'
						. ' FROM #__community_fields'
						. ' WHERE `type` != \'group\''
						;
				$db->setQuery( $query );
				$objects = $db->loadObjectList();

				foreach ( $objects as $object ) {
					if ( isset( $this->settings['jsfield_' . $object->id.$request->area] ) ) {
						if ( $this->settings['jsfield_' . $object->id.$request->area] !== '' ) {
							$changes[$object->id] = AECToolbox::rewriteEngineRQ( $this->settings['jsfield_' . $object->id.$request->area], $request );
						}
					}
				}

				if ( !empty( $changes ) ) {
					$this->setFields( $changes, $request->metaUser->userid );
				}

				if ( !empty( $this->settings['assign_group'.$request->area] ) ) {
					if ( strpos( ',', $this->settings['assign_group'.$request->area] ) !== false ) {
						$grouplist = explode( ',', $this->settings['assign_group'.$request->area] );
					} else {
						$grouplist = array( $this->settings['assign_group'.$request->area] );
					}

					foreach ( $grouplist as $groupid ) {
						$this->addToGroup( $request->metaUser->userid, $groupid );
					}
				}

				if ( !empty( $this->settings['remove_group'.$request->area] ) ) {
					if ( strpos( ',', $this->settings['remove_group'.$request->area] ) !== false ) {
						$grouplist = explode( ',', $this->settings['remove_group'.$request->area] );
					} else {
						$grouplist = array( $this->settings['remove_group'.$request->area] );
					}

					foreach ( $grouplist as $groupid ) {
						$this->removeFromGroup( $request->metaUser->userid, $groupid );
					}
				}
			}
		}

		if ( $request->action == 'action' ) {
			$params = $request->metaUser->meta->getMIParams( $request->parent->id, $request->plan->id );

			if ( $params['use_points'] > 0 ) {
				$points = -$params['use_points'];

				$this->updatePoints( $request->metaUser->userid, $points, $request->invoice->invoice_number );

				unset( $params['use_points'] );

				$request->metaUser->meta->setMIParams( $request->parent->id, $request->plan->id, $params, true );

				$request->metaUser->meta->storeload();
			}
		}

		if ( empty( $this->settings['change_points'.$request->area] ) ) {
			return null;
		}

		$this->updatePoints( $request->metaUser->userid, $this->settings['change_points'.$request->area], $request->invoice->invoice_number );
	}

	function setFields( $fields, $userid )
	{
		$db = &JFactory::getDBO();

		$ids = array();
		foreach ( $fields as $fi => $ff ) {
			$ids[] = $fi;
		}

		$query = 'SELECT `field_id`, `value`'
				. ' FROM #__community_fields_values'
					. ' WHERE `field_id` IN (' . implode( ',', $ids ) . ')'
					. ' AND `user_id` = \'' . (int) $userid . '\'';
				;
		$db->setQuery( $query );
		$existingfields = $db->loadObjectList();

		foreach( $fields as $id => $value ) {
			$existingfield = $hasvalue = false;
			if ( !empty( $existingfields ) ) {
				foreach ( $existingfields as $ff ) {
					if ( $ff->field_id == $id ) {
						$existingfield = true;

						if ( !empty( $ff->value ) ) {
							$hasvalue = true;
						}

						continue;
					}
				}
			}

			$query = null;
			if ( $existingfield && ( !$hasvalue || $this->settings['overwrite_existing'] ) ) {
				$query	= 'UPDATE #__community_fields_values SET '
						. ' `value` = \'' . xJ::escape( $db, $value ) . '\''
						. ' WHERE `user_id` = \'' . (int) $userid . '\''
						. ' AND `field_id` = \'' . (int) $id . '\''
						;
			} elseif ( !$existingfield ) {
				$query	= 'INSERT INTO #__community_fields_values'
						. ' (`user_id`, `field_id`, `value` )'
						. ' VALUES ( \'' . (int) $userid . '\', \'' . (int) $id . '\', \'' . xJ::escape( $db, $value ) . '\' )'
						;
			}

			if ( !empty( $query ) ) {
				$db->setQuery( $query );
				$db->query();
			}
		}
	}

	function addToGroup( $userid, $groupid )
	{
		$db = &JFactory::getDBO();

		// Check whether group exists
		$query = 'SELECT `id`'
				. ' FROM #__community_groups'
					. ' WHERE `id` = \'' . (int) $groupid . '\''
				;
		$db->setQuery( $query );

		if( $db->loadResult() ) {
			// Check whether user already has the group
			$query = 'SELECT `groupid`'
					. ' FROM #__community_groups_members'
						. ' WHERE `groupid` = \'' . (int) $groupid . '\''
						. ' AND `memberid` = \'' . (int) $userid . '\''
					;
			$db->setQuery( $query );

			if( !$db->loadResult() ) {
				$query	= 'INSERT INTO #__community_groups_members'
						. ' (`groupid`, `memberid`, `approved` )'
						. ' VALUES ( \'' . (int) $groupid . '\', \'' . (int) $userid . '\', \'1\' )'
						;

				$db->setQuery( $query );
				$db->query();
			}
		}
	}

	function removeFromGroup( $userid, $groupid )
	{
		$db = &JFactory::getDBO();

		// Check whether group exists
		$query = 'SELECT `id`'
				. ' FROM #__community_groups'
					. ' WHERE `id` = \'' . (int) $groupid . '\''
				;
		$db->setQuery( $query );

		if( $db->loadResult() ) {
			$query	= 'DELETE'
					. ' FROM #__community_groups_members'
					. ' WHERE `groupid` = \'' . (int) $groupid . '\''
					. ' AND `memberid` = \'' . (int) $userid . '\''
					;

			$db->setQuery( $query );
			$db->query();
		}
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
		$discount = $request->params['use_points'] * $this->settings['checkout_conversion'];

		$original_price = $request->add['terms']->nextterm->renderTotal();

		if ( $discount > $original_price ) {
			$discount = $original_price;

			$request->params['use_points'] = $discount / $this->settings['checkout_conversion'];
		}

		$request->add['terms']->nextterm->discount( $discount, null, $request->params['use_points'] . " Points" );

		return true;
	}

	function getPoints( $userid )
	{
		$db	   =& JFactory::getDBO();

		$query = "SELECT points FROM #__community_users WHERE `userid`='" . $userid . "'";
		$db->setQuery( $query );

		return $db->loadResult();
	}

	function updatePoints( $userid, $points, $comment )
	{
		$db	   =& JFactory::getDBO();

		$query = 'UPDATE #__community_users'
				. ' SET `points` = \'' . ( $this->getPoints( $userid ) + $points ) . '\''
				. ' WHERE `userid` = \'' . $userid . '\''
				;
		$db->setQuery( $query );
		$db->query();
	}
}
?>

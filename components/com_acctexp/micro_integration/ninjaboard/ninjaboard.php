<?php
/**
 * @version $Id: mi_ninjaboard.php 01 2007-08-11 13:29:29Z SBS $
 * @package AEC - Account Control Expiration - Subscription component for Joomla! OS CMS
 * @subpackage Micro Integrations - Ninjaboard
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author Stian Didriksen, David Deutsch & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.2 http://www.gnu.org/copyleft/gpl.html
 */

// Dont allow direct linking
defined('_JEXEC') or die( 'Direct Access to this location is not allowed.' );

class mi_ninjaboard
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_NINJABOARD');
		$info['desc'] = JText::_('AEC_MI_DESC_NINJABOARD');
		$info['type'] = array( 'communication.forum', 'vendor.ninjaforge' );

		return $info;
	}

	function Settings()
	{
		if ( !class_exists( 'KFactory' ) ) {
			return array();
		}

	 	$groups = KFactory::tmp('admin::com.ninjaboard.model.usergroups')->getList();

		$sg		= array();
		$sg2	= array();
		if ( !empty( $groups ) ) {
			foreach ( $groups as $group ) {
				$sg[] = JHTML::_('select.option', $group->id, $group->title );
			}
		}

		// Explode the Groups to Exclude
		if ( !empty($this->settings['groups_exclude'] ) ) {
			$selected_groups_exclude = array();

			foreach ( $this->settings['groups_exclude'] as $group_exclude ) {
				$selected_groups_exclude[]->value = $group_exclude;
			}
		} else {
			$selected_groups_exclude			= '';
		}

		$settings = array();

		$s = array( 'group', 'remove_group', 'group_exp', 'remove_group_exp', 'groups_exclude' );

		foreach ( $s as $si ) {
			$v = null;
			if ( isset( $this->settings[$si] ) ) {
				$v = $this->settings[$si];
			}

			$settings['lists'][$si]	= JHTML::_( 'select.genericlist', $sg, $si.'[]', 'size="10" multiple="true"', 'value', 'text', $v );
		}

		$sub = 0;
		if ( isset( $this->settings['subsc_action'] ) ) {
			$sub = $this->settings['subsc_action'];
		}

 		$subs = array();
		$subs[] = JHTML::_('select.option', 0, "Keep subscriptions" );
		$subs[] = JHTML::_('select.option', 1, "Delete subscriptions" );
		$subs[] = JHTML::_('select.option', 2, "Mute subscriptions" );

		$settings['lists']['subsc_action']	= JHTML::_('select.genericlist', $subs, 'subsc_action', 'size="3"', 'value', 'text', $sub );

		$settings['set_group']				= array( 'toggle' );
		$settings['group']					= array( 'list' );
		$settings['set_remove_group']		= array( 'toggle' );
		$settings['remove_group']			= array( 'list' );
		$settings['set_group_exp']			= array( 'toggle' );
		$settings['group_exp']				= array( 'list' );
		$settings['set_remove_group_exp']	= array( 'toggle' );
		$settings['remove_group_exp']		= array( 'list' );
		$settings['set_groups_exclude']		= array( 'toggle' );
		$settings['groups_exclude']			= array( 'list' );
		$settings['set_clear_groups']		= array( 'toggle' );
		$settings['subsc_action']			= array( 'list' );

		return $settings;
	}

	function action( $request )
	{
		if ( !class_exists( 'KFactory' ) ) {
			return null;
		}

		$id = $request->metaUser->userid;

		$model	= KFactory::tmp('admin::com.ninjaboard.model.usergroupmaps');
		$table  = $model->getTable();
		$groups = $model->id($id)->getGroups();

		if ( $this->settings['set_remove_group'] ) {
			foreach ( $this->settings['remove_group'] as $groupid ) {
				if ( in_array( $groupid, $groups ) ) {
					$query = KFactory::tmp('lib.koowa.database.query');
					$table->select(
						$query->where('joomla_user_id', '=', $id)->where('ninjaboard_user_group_id', '=', $groupid)
					)->delete();
				}
			}
		}

		if ( $this->settings['set_group'] ) {
			foreach ( $this->settings['group'] as $groupid ) {
				if ( !in_array( $groupid, $groups ) ) {
					$row = KFactory::tmp('admin::com.ninjaboard.model.usergroupmaps')->getItem()->setData(array(
						'joomla_user_id' => $id,
						'ninjaboard_user_group_id' => $groupid
					));
					$table->insert($row);
				}
			}
		}

		if ( !empty( $this->settings['subsc_action'] ) ) {
			if ( $this->settings['subsc_action'] === 2 ) {
				KFactory::get('com://site/ninjaboard.controller.person')->id($user->id)->read()->setData(array('notify_enabled' => true))->save();
			}
		}

		return true;
	}

	function expiration_action( $request )
	{
		if ( !class_exists( 'KFactory' ) ) {
			return array();
		}

		$id = $request->metaUser->userid;

		$model	= KFactory::tmp('admin::com.ninjaboard.model.usergroupmaps');
		$table  = $model->getTable();
		$groups = $model->id($id)->getGroups();

		if ( $this->settings['set_clear_groups'] ) {
			if ( $this->settings['set_groups_exclude'] && !empty( $this->settings['groups_exclude'] ) ) {
				$groups = array_diff( $groups, $this->settings['groups_exclude'] );
			}

			$query = KFactory::tmp('lib.koowa.database.query');
			$table->select(
				$query->where('joomla_user_id', '=', $id)->where('ninjaboard_user_group_id', 'IN', $groups)
			)->delete();
		} else {
			if ( $this->settings['set_remove_group_exp'] ) {
				foreach ( $this->settings['remove_group_exp'] as $groupid ) {
					if ( in_array( $groupid, $groups ) ) {
						$query = KFactory::tmp('lib.koowa.database.query');
						$table->select(
							$query->where('joomla_user_id', '=', $id)->where('ninjaboard_user_group_id', '=', $groupid)
						)->delete();
					}
				}
			}

			if ( $this->settings['set_group_exp'] ) {
				foreach ( $this->settings['group_exp'] as $groupid ) {
					if ( !in_array( $groupid, $groups ) ) {
						$row = KFactory::tmp('admin::com.ninjaboard.model.usergroupmaps')->getItem()->setData(array(
							'joomla_user_id' => $id,
							'ninjaboard_user_group_id' => $groupid
						));
						$table->insert($row);
					}
				}
			}
		}

		if ( !empty( $this->settings['subsc_action'] ) ) {
			if ( $this->settings['subsc_action'] === 1 ) {
				$model	= KFactory::tmp('admin::com.ninjaboard.model.subscriptions');
				$table  = $model->getTable();

				$query = KFactory::tmp('lib.koowa.database.query');
				$table->select(
					$query->where('created_by', '=', $id)
				)->delete();
			} else {
				KFactory::get('com://site/ninjaboard.controller.person')->id($user->id)->read()->setData(array('notify_enabled' => false))->save();
			}
		}

		return true;
	}
}
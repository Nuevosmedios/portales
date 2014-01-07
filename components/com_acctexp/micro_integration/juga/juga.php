<?php
/**
 * @version $Id: mi_juga.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - JUGA
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 *
 * based on some of David Deutsch's DocMan MI group handling
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_juga
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_JUGA');
		$info['desc'] = JText::_('AEC_MI_DESC_JUGA');
		$info['type'] = array( 'directory_documentation.directory', 'vendor.dioscouri' );

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`, `title`, `description`'
			 	. ' FROM #__juga_groups'
			 	;
	 	$db->setQuery( $query );
	 	$groups = $db->loadObjectList();

		$sg = array();
		if ( !empty( $groups ) ) {
			foreach ( $groups as $group ) {
				$sg[] = JHTML::_('select.option', $group->id, $group->title . ' - ' . substr( strip_tags( $group->description ), 0, 30 ) );
			}
		}

		$settings = array();

		// Explode the selected groups
		if ( !empty( $this->settings['enroll_group'] ) ) {
			$selected_enroll_gps = array();
			foreach ( $this->settings['enroll_group'] as $enroll_group ) {
				$selected_enroll_gps[]->value = $enroll_group;
			}
		} else {
			$selected_enroll_gps		= '';
		}

		if ( !empty( $this->settings['remove_selected'] ) ) {
			$selected_remove_gps = array();
			foreach ( $this->settings['remove_selected'] as $remove_selected) {
				$selected_remove_gps[]->value = $remove_selected;
			}
		} else {
			$selected_remove_gps		= '';
		}

		if ( !empty( $this->settings['remove_selected_exp'] ) ) {
			$selected_remove_gps_exp = array();
			foreach ( $this->settings['remove_selected_exp'] as $remove_selected_exp) {
				$selected_remove_gps_exp[]->value = $remove_selected_exp;
			}
		} else {
			$selected_remove_gps_exp		= '';
		}

		if ( !empty( $this->settings['enroll_group_exp'] ) ) {
			$selected_enroll_gps_exp = array();
			foreach ( $this->settings['enroll_group_exp'] as $enroll_group_exp) {
				$selected_enroll_gps_exp[]->value = $enroll_group_exp;
			}
		} else {
			$selected_enroll_gps_exp		= '';
		}

		$settings['lists']['enroll_group']		= JHTML::_( 'select.genericlist', $sg, 'enroll_group[]', 'size="4" multiple="true"', 'value', 'text', $selected_enroll_gps );
		$settings['lists']['enroll_group_exp']	= JHTML::_( 'select.genericlist', $sg, 'enroll_group_exp[]', 'size="4" multiple="true"', 'value', 'text', $selected_enroll_gps_exp );
		$settings['lists']['remove_selected']		= JHTML::_( 'select.genericlist', $sg, 'remove_selected[]', 'size="4" multiple="true"', 'value', 'text', $selected_remove_gps ); 
		$settings['lists']['remove_selected_exp']	= JHTML::_( 'select.genericlist', $sg, 'remove_selected_exp[]', 'size="4" multiple="true"', 'value', 'text', $selected_remove_gps_exp );

		$settings['set_remove_group']			= array( 'toggle' );
		$settings['set_remove_selected']		= array( 'toggle'	, 'Remove Selected Groups', 'Set to yes, to delete only selected groups');
		$settings['remove_selected']			= array( 'list'			, 'Remove JUGA Groups', 'List of groups to be removed');
		$settings['set_enroll_group']			= array( 'toggle' );
		$settings['enroll_group']				= array( 'list' );
		$settings['set_remove_group_exp']		= array( 'toggle' );
		$settings['set_remove_selected_exp']	= array( 'list_yesno'	, 'Remove Selected Groups Exp', 'Set to yes, to delete only selected groups on expiration of plan');
		$settings['remove_selected_exp']		= array( 'list'			, 'Remove JUGA Groups Exp', 'List of groups to be removed on expiration of plan');
		$settings['set_enroll_group_exp']		= array( 'toggle' );
		$settings['enroll_group_exp']			= array( 'list' );
		$settings['rebuild']					= array( 'toggle' );
		$settings['remove']						= array( 'toggle' );

		return $settings;
	}

	function detect_application()
	{
		return is_dir( JPATH_SITE . '/components/com_juga' );
	}

	function expiration_action( $request )
	{
		$db = &JFactory::getDBO();

		if ( $this->settings['set_remove_group_exp'] && !empty( $this->settings['enroll_group'] ) ) {
			foreach ( $this->settings['enroll_group'] as $groupid ) {
				$this->DeleteUserFromGroup( $request->metaUser->userid, $groupid );
			}
		}

		if ( !empty( $this->settings['set_remove_selected_exp'] ) && !empty( $this->settings['remove_selected_exp'] ) ) {
			foreach ( $this->settings['remove_selected_exp'] as $remove_group ) {
				$this->DeleteUserFromGroup( $request->metaUser->userid, $remove_group );
			}
		}

		if ( $this->settings['set_enroll_group_exp'] && !empty( $this->settings['enroll_group_exp'] ) ) {
			foreach ( $this->settings['enroll_group_exp'] as $enroll_group_exp) {
				$this->AddUserToGroup( $request->metaUser->userid, $enroll_group_exp );
			}
		}

		return true;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		if ( $this->settings['set_remove_group'] && empty( $this->settings['set_remove_selected'] ) ) {
			$this->DeleteUserFromGroup( $request->metaUser->userid );
		} elseif ( !empty( $this->settings['set_remove_selected'] ) ) {
			foreach ( $this->settings['remove_selected'] as $remove_group ) {
				$this->DeleteUserFromGroup( $request->metaUser->userid, $remove_group );
			}
		}

		if ( $this->settings['set_enroll_group'] && !empty( $this->settings['enroll_group'] ) ) {
			foreach( $this->settings['enroll_group'] as $enroll_group) {
				$this->AddUserToGroup( $request->metaUser->userid, $enroll_group );
			}
		}
	}

	function AddUserToGroup( $userid, $groupid )
	{
		$db = &JFactory::getDBO();

		// Check user is not already a member of the group.
		$query = 'SELECT `user_id`'
				. ' FROM #__juga_u2g'
				. ' WHERE `group_id` = \'' . $groupid . '\''
				. ' AND `user_id` = \''.$userid . '\''
				;
		$db->setQuery( $query );
		$user = $db->loadResult();

		if( $user !== $userid ) {
			// then the user is not already a member of this group and can be set

			$return = new stdClass();
			// load the plugins
			JPluginHelper::importPlugin( 'juga' );

			// fire plugins
			$dispatcher =& JDispatcher::getInstance();
			$before     = $dispatcher->trigger( 'onBeforeAddUserToGroup', array( $userid, $groupid, $return ) );
			if (in_array(false, $before, true)) {
					JError::raiseError(500, $return->errorMsg );
					return false;
			}

			$query = 'INSERT INTO #__juga_u2g'
					. ' SET `group_id` = \'' . $groupid . '\', `user_id` = \''.$userid . '\''
					;
			$db->setQuery( $query );

			if (!$db->query()) {
				$return->error = true;
				$return->errorMsg = $db->getErrorMsg();
				return false;
			}

			// fire plugins
			$dispatcher =& JDispatcher::getInstance();
			$dispatcher->trigger( 'onAfterAddUserToGroup', array( $userid, $groupid ) );

			return true;
		} else {
			return false;
		}
	}

	function DeleteUserFromGroup( $userid, $groupid=null )
	{
		$db = &JFactory::getDBO();

		$return = new stdClass();
		// load the plugins
		JPluginHelper::importPlugin( 'juga' );

		// fire plugins
		$dispatcher =& JDispatcher::getInstance();
		$before     = $dispatcher->trigger( 'onBeforeRemoveUserFromGroup', array( $userid, $groupid, $return ) );
		if (in_array(false, $before, true)) {
				JError::raiseError(500, $return->errorMsg );
				return false;
		}

		$query = 'DELETE FROM #__juga_u2g'
				. ' WHERE `user_id` = \''. $userid . '\''
				;

		if ( !empty( $groupid ) ) {
			$query .= ' AND `group_id` = \''. $groupid . '\'';
		}

		$db->setQuery( $query );

		if (!$db->query()) {
			$return->error = true;
			$return->errorMsg = $db->getErrorMsg();
			return false;
		}

		// fire plugins
		$dispatcher =& JDispatcher::getInstance();
		$dispatcher->trigger( 'onAfterRemoveUserFromGroup', array( $userid, $groupid ) );

		return true;
	}
}
?>

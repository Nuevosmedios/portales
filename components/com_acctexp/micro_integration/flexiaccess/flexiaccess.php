<?php
/**
 * @version $Id: mi_flexiaccess.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - FLEXIaccess
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 *
 * based on David Deutsch's Juga MI group handling
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_flexiaccess
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_FLEXIACCESS');
		$info['desc'] = JText::_('AEC_MI_DESC_FLEXIACCESS');
		$info['type'] = array( 'user.access_restriction', 'vendor.flexicontent' );

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`, `name`, `description`'
			 	. ' FROM #__flexiaccess_groups'
			 	. ' WHERE id > 1'
			 	;
	 	$db->setQuery( $query );
	 	$groups = $db->loadObjectList();

		$sg = array();
		if ( !empty( $groups ) ) {
			foreach ( $groups as $group ) {
				$sg[] = JHTML::_('select.option', $group->id, $group->name . ' - ' . substr( strip_tags( $group->description ), 0, 30 ) );
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

		if ( !empty( $this->settings['enroll_group_exp'] ) ) {
			$selected_enroll_gps_exp = array();
			foreach ( $this->settings['enroll_group_exp'] as $enroll_group_exp) {
				$selected_enroll_gps_exp[]->value = $enroll_group_exp;
			}
		} else {
			$selected_enroll_gps_exp		= '';
		}

		$settings['lists']['enroll_group']		= JHTML::_('select.genericlist', $sg, 'enroll_group[]', 'size="4" multiple="true"', 'value', 'text', $selected_enroll_gps );
		$settings['lists']['enroll_group_exp']	= JHTML::_('select.genericlist', $sg, 'enroll_group_exp[]', 'size="4" multiple="true"', 'value', 'text', $selected_enroll_gps_exp );

		$settings['set_remove_group']			= array( 'toggle' );
		$settings['set_enroll_group']			= array( 'toggle' );
		$settings['enroll_group']				= array( 'list' );
		$settings['set_remove_group_exp']		= array( 'toggle' );
		$settings['set_enroll_group_exp']		= array( 'toggle' );
		$settings['enroll_group_exp']			= array( 'list' );
		$settings['rebuild']					= array( 'toggle' );
		$settings['remove']						= array( 'toggle' );

		return $settings;
	}

	function detect_application()
	{
		return is_dir( JPATH_SITE . '/components/com_flexiaccess' );
	}

	function expiration_action( $request )
	{
		$db = &JFactory::getDBO();

		if ( $this->settings['set_remove_group_exp'] ) {
			foreach ( $this->settings['enroll_group'] as $groupid ) {
				$this->DeleteUserFromGroup( $request->metaUser->userid, $groupid );
			}
		}

		if ( $this->settings['set_enroll_group_exp'] ) {
			if ( !empty( $this->settings['enroll_group_exp'] ) ) {
				foreach ( $this->settings['enroll_group_exp'] as $enroll_group_exp) {
					$this->AddUserToGroup( $request->metaUser->userid, $enroll_group_exp );
				}
			}
		}

		return true;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		if ( $this->settings['set_remove_group'] ) {
			$this->DeleteUserFromGroup( $request->metaUser->userid );
		}

		if ( $this->settings['set_enroll_group'] ) {
			if( !empty( $this->settings['enroll_group'] ) ) {
				foreach( $this->settings['enroll_group'] as $enroll_group) {
					$this->AddUserToGroup( $request->metaUser->userid, $enroll_group );
				}
			}
		}
	}

	function AddUserToGroup( $userid, $groupid )
	{
		$db = &JFactory::getDBO();

		// Check user is not already a member of the group.
		$query = 'SELECT `member_id`'
				. ' FROM #__flexiaccess_members'
				. ' WHERE `group_id` = \'' . $groupid . '\''
				. ' AND `member_id` = \''.$userid . '\''
				;
		$db->setQuery( $query );
		$user = $db->loadResult();

		if( $user !== $userid ) {
			// then the user is not already a member of this group and can be set

			$query = 'INSERT INTO #__flexiaccess_members'
					. ' SET `group_id` = \'' . $groupid . '\', `member_id` = \''.$userid . '\''
					;
			$db->setQuery( $query );
			$db->query();

			return true;
		} else {
			return false;
		}
	}

	function DeleteUserFromGroup( $userid, $groupid=null )
	{
		$db = &JFactory::getDBO();

		$query = 'DELETE FROM #__flexiaccess_members'
				. ' WHERE `member_id` = \''. $userid . '\''
				;

		if ( !empty( $groupid ) ) {
			$query .= ' AND `group_id` = \''. $groupid . '\'';
		}

		$db->setQuery( $query );
		$db->query();

		return true;
	}
}
?>

<?php
/**
 * @version $Id: mi_k2.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - K2
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_k2
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_K2');
		$info['desc'] = JText::_('AEC_MI_DESC_K2');
		$info['type'] = array( 'content', 'vendor.joomlaworks' );

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

        $settings = array();
		$settings['set_group']		= array( 'toggle' );
		$settings['group']			= array( 'list' );
		$settings['set_group_exp']	= array( 'toggle' );
		$settings['group_exp']		= array( 'list' );
		$settings['rebuild']		= array( 'toggle' );
		$settings['remove']			= array( 'toggle' );

		$query = 'SHOW COLUMNS FROM #__k2_user_groups'
				. ' LIKE \'groups_id\''
				;

		$db->setQuery( $query );
		$result = $db->loadResult();

		$query = 'SELECT ' . ( $result ? 'groups_id' : 'id'  ) . ', name'
			 	. ' FROM #__k2_user_groups'
			 	;
	 	$db->setQuery( $query );
	 	$groups = $db->loadObjectList();

		$sg = array();
		$sge = array();

		$gr = array();
		if ( !empty( $groups ) ) {
			foreach( $groups as $group ) {
				if ( isset( $group->id ) ) {
					$gid = $group->id;
				} else {
					$gid = $group->groups_id;
				}

				$gr[] = JHTML::_('select.option', $gid, $group->name );

				if ( !empty( $this->settings['group'] ) ) {
					if ( $gid == $this->settings['group'] ) {
						$sg[] = JHTML::_('select.option', $gid, $group->name );
					}
				}

				if ( !empty( $this->settings['group_exp'] ) ) {
					if ( $gid == $this->settings['group_exp'] ) {
						$sge[] = JHTML::_('select.option', $gid, $group->name );
					}
				}
			}
		}

		$settings['lists']['group']			= JHTML::_( 'select.genericlist', $gr, 'group', 'size="4"', 'value', 'text', $sg );
		$settings['lists']['group_exp'] 	= JHTML::_( 'select.genericlist', $gr, 'group_exp', 'size="4"', 'value', 'text', $sge );

		return $settings;
	}

	function expiration_action( $request )
	{
		$db = &JFactory::getDBO();

		if ( $this->settings['set_group_exp'] && !empty( $this->settings['group_exp'] ) ) {
			$this->AddUserToGroup( $request->metaUser, $this->settings['group_exp'] );
		}

		return true;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		if ( $this->settings['set_group'] && !empty( $this->settings['group'] ) ) {
			$this->AddUserToGroup( $request->metaUser, $this->settings['group'] );
		}

		return true;
	}

	function AddUserToGroup( $metaUser, $groupid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT id FROM #__k2_users'
			. ' WHERE `userID` = \'' . $metaUser->userid . '\''
			;
		$db->setQuery( $query );
		$id = $db->loadResult();

		if ( empty( $id ) ) {
			$query = 'INSERT INTO #__k2_users'
				. ' (`userID`, `userName`, `group` )'
				. ' VALUES ( \'' . $metaUser->userid . '\', \'' . $metaUser->cmsUser->username . '\', \'' . $groupid . '\' )'
				;
			$db->setQuery( $query );
			$db->query();
		} else {
			$query = 'UPDATE #__k2_users'
				. ' SET `group` = \'' . $groupid . '\''
				. ' WHERE `userID` = \'' . $metaUser->userid . '\''
				;
			$db->setQuery( $query );
			$db->query();
		}

		return true;
	}

}

?>

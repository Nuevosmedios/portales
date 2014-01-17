<?php
/**
 * @version $Id: mi_acl.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - ACL
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_acl
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_ACL');
		$info['desc'] = JText::_('AEC_MI_DESC_ACL');
		$info['type'] = array( 'user.access_restriction', 'joomla.acl' );

		return $info;
	}

	function Settings()
	{
		$user = &JFactory::getUser();

		$settings = array();

		if ( defined( 'JPATH_MANIFESTS' ) ) {
			$settings['set_gid']				= array( 'toggle' );
			$settings['gid']					= array( 'list' );
			$settings['set_removegid']			= array( 'toggle' );
			$settings['removegid']				= array( 'list' );

			$settings['aectab_exp']				= array( 'tab', 'Expiration Action', 'Expiration Action' );
			$settings['set_gid_exp']			= array( 'toggle' );
			$settings['gid_exp']				= array( 'list' );
			$settings['set_removegid_exp']		= array( 'toggle' );
			$settings['removegid_exp']			= array( 'list' );

			$settings['aectab_preexp']			= array( 'tab', 'Pre-Expiration Action', 'Pre-Expiration Action' );
			$settings['set_gid_pre_exp']		= array( 'toggle' );
			$settings['gid_pre_exp']			= array( 'list' );
			$settings['set_removegid_pre_exp']	= array( 'toggle' );
			$settings['removegid_pre_exp']		= array( 'list' );
		} else {
			$settings['jaclpluspro']			= array( 'toggle' );
			$settings['delete_subgroups']		= array( 'toggle' );

			$settings['set_gid']				= array( 'toggle' );
			$settings['gid']					= array( 'list' );
			$settings['sub_set_gid']			= array( 'toggle' );
			$settings['sub_gid_del']			= array( 'list' );
			$settings['sub_gid']				= array( 'list' );

			$settings['aectab_exp']				= array( 'tab', 'Expiration Action', 'Expiration Action' );
			$settings['set_gid_exp']			= array( 'toggle' );
			$settings['gid_exp']				= array( 'list' );
			$settings['sub_set_gid_exp']		= array( 'toggle' );
			$settings['sub_gid_exp_del']		= array( 'list' );
			$settings['sub_gid_exp']			= array( 'list' );

			$settings['aectab_preexp']			= array( 'tab', 'Pre-Expiration Action', 'Pre-Expiration Action' );
			$settings['set_gid_pre_exp']		= array( 'toggle' );
			$settings['gid_pre_exp']			= array( 'list' );
			$settings['sub_set_gid_pre_exp']	= array( 'toggle' );
			$settings['sub_gid_pre_exp_del']	= array( 'list' );
			$settings['sub_gid_pre_exp']		= array( 'list' );
		}

		$gtree = xJACLhandler::getGroupTree( array( 28, 29, 30 ) );

		$gidlists = array( 'gid', 'gid_exp', 'gid_pre_exp', 'removegid', 'removegid_exp', 'removegid_pre_exp' );

		foreach ( $gidlists as $name ) {
			if ( defined( 'JPATH_MANIFESTS' ) ) {
				$selected = array();
			} else {
				$selected = 18;
			}

			if ( !empty( $this->settings[$name] ) ) {
				if ( is_array( $this->settings[$name] ) ) {
					foreach ( $this->settings[$name] as $value ) {
						$selected[]->value = $value;
					}
				} else {
					$selected = $this->settings[$name];
				}
			}

			if ( defined( 'JPATH_MANIFESTS' ) ) {
				$settings['lists'][$name] = JHTML::_('select.genericlist', $gtree, $name.'[]', 'size="6" multiple="multiple"', 'value', 'text', $selected );
			} else {
				$settings['lists'][$name] = JHTML::_('select.genericlist', $gtree, $name, 'size="6"', 'value', 'text', $selected );				
			}
		}

		$subgroups = array( 'sub_gid_del', 'sub_gid', 'sub_gid_exp_del', 'sub_gid_exp', 'sub_gid_pre_exp_del', 'sub_gid_pre_exp' );

		foreach ( $subgroups as $groupname ) {
			$selected = array();
			if ( !empty( $this->settings[$groupname] ) ) {
				foreach ( $this->settings[$groupname] as $value ) {
					$selected[]->value = $value;
				}
			}

			$settings['lists'][$groupname] = JHTML::_('select.genericlist', $gtree, $groupname.'[]', 'size="6" multiple="multiple"', 'value', 'text', $selected );
		}

		return $settings;
	}

	function relayAction( $request )
	{
		if ( !empty( $this->settings['jaclpluspro'] ) ) {
			$this->jaclplusGIDchange( $request->metaUser, 'sub_gid' . $request->area );
		}

		if ( !empty( $this->settings['set_gid' . $request->area] ) || !empty( $this->settings['set_removegid' . $request->area] ) ) {
			$add = $remove = array();

			if ( !empty( $this->settings['set_gid' . $request->area] ) && !empty( $this->settings['gid' . $request->area] ) ) {
				$add = $this->settings['gid' . $request->area];
			}

			if ( !empty( $this->settings['set_removegid' . $request->area] ) && !empty( $this->settings['removegid' . $request->area] ) ) {
				$remove = $this->settings['removegid' . $request->area];
			}

			if ( !empty( $add ) || !empty( $remove ) ) {
				$this->instantGIDchange( $request->metaUser, $add, $remove );

				if ( defined( 'JPATH_MANIFESTS' ) ) {
					// The Otter isn't working!
					$this->instantGIDchange( $request->metaUser, $add, $remove );
				}
			}
		} elseif ( !empty( $this->settings['sub_set_gid' . $request->area] ) ) {
			
		}

		return true;
	}

	function instantGIDchange( $metaUser, $add, $remove )
	{
		$sessionextra = array();
		if ( !empty( $this->settings['jaclpluspro'] ) ) {
			if ( is_array( $add ) ) {
				$gid = $add[0];
			} else {
				$gid = $add;
			}
			
			$sessionextra = $this->jaclSessionExtra( $metaUser, $gid );
		}

		$metaUser->instantGIDchange( $add, $remove, $sessionextra );

		return true;
	}

	function jaclplusGIDchange( $metaUser, $section )
	{
		$db = &JFactory::getDBO();

		if ( $this->settings['delete_subgroups'] ) {
			// Delete sub entries
			$query = 'DELETE FROM #__jaclplus_user_group'
					. ' WHERE `id` = \'' . (int) $metaUser->userid . '\''
					. ' AND `group_type` = \'sub\''
					;
			$db->setQuery( $query );
			$db->query();

			$groups = array();
		} else {
			// Check for sub entries
			$query = 'SELECT `group_id`'
					. ' FROM #__jaclplus_user_group'
					. ' WHERE `id` = \'' . (int) $metaUser->userid . '\''
					. ' AND `group_type` = \'sub\''
					;
			$db->setQuery( $query );
			$groups = xJ::getDBArray( $db );
		}

		if ( !empty( $this->settings[$section.'_del'] ) ) {
			foreach ( $this->settings[$section.'_del'] as $gid ) {
				if ( in_array( $gid, $groups ) ) {
					$query = 'DELETE FROM #__jaclplus_user_group'
							. ' WHERE `id` = \'' . (int) $metaUser->userid . '\''
							. ' AND `group_type` = \'sub\''
							. ' AND `group_id` = \'' . (int) $gid . '\''
							;
					$db->setQuery( $query );
					$db->query() or die( $db->stderr() );
				}
			}
		}

		if ( !empty( $this->settings[$section] ) ) {
			foreach ( $this->settings[$section] as $gid ) {
				if ( !in_array( $gid, $groups ) ) {
					$query = 'INSERT INTO #__jaclplus_user_group'
							. ' VALUES( \'' . (int) $metaUser->userid . '\', \'sub\', \'' . $gid . '\', \'\' )'
							;
					$db->setQuery( $query );
					$db->query() or die( $db->stderr() );
				}
			}
		}

		return true;
	}

	function jaclSessionExtra( $metaUser, $gid )
	{
		$sessionextra = array();

		$db = &JFactory::getDBO();

		$acl = &JFactory::getACL();

		$gid_name = $acl->get_group_name( $gid, 'ARO' );

		// Check for main entry
		$query = 'SELECT `group_id`'
				. ' FROM #__jaclplus_user_group'
				. ' WHERE `id` = \'' . (int) $metaUser->userid . '\''
				. ' AND `group_type` = \'main\''
				;
		$db->setQuery( $query );

		if ( $db->loadResult() ) {
			$query = 'UPDATE #__jaclplus_user_group'
					. ' SET `group_id` = \'' . (int) $gid . '\''
					. ' WHERE `id` = \'' . (int) $metaUser->userid . '\''
					. ' AND `group_type` = \'main\''
					;
			$db->setQuery( $query );
			$db->query() or die( $db->stderr() );
		} else {
			$query = 'INSERT INTO #__jaclplus_user_group'
					. ' VALUES( \'' . (int) $metaUser->userid . '\', \'main\', \'' . (int) $gid . '\', \'\' )'
					;
			$db->setQuery( $query );
			$db->query() or die( $db->stderr() );
		}

		// Get Session
		$query = 'SELECT *'
				. ' FROM #__session'
				. ' WHERE `userid` = \'' . (int) $metaUser->userid . '\''
				;
		$db->setQuery( $query );
		$session = $db->loadObject();

		if ( !empty( $session->userid ) ) {
			$query = 'SELECT `group_id`'
					. ' FROM #__jaclplus_user_group'
					. ' WHERE `id` = \'' . (int) $metaUser->userid . '\''
					;
			$db->setQuery( $query );
			$groups = xJ::getDBArray( $db );

			$query = 'SELECT `value`'
					. ' FROM #__core_acl_aro_groups'
					. ' WHERE `id` IN (' . implode( ',', $groups ) . ')'
					;
			$db->setQuery( $query );
			$valuelist = xJ::getDBArray( $db );

			$sessiongroups = array();
			foreach ( $valuelist as $vlist ) {
				$values = explode( ',', $vlist );

				$sessiongroups = array_merge( $sessiongroups, $values );
			}

			$sessiongroups = array_unique( $sessiongroups );

			asort( $sessiongroups );

			$sessionextra['gids']		= $gid;
			$sessionextra['jaclplus']	= implode( ',', $sessiongroups );
		}

		return $sessionextra;
	}
}
?>

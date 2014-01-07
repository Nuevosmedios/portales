<?php
/**
 * @version $Id: mi_g2.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - G2
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_g2 extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_G2');
		$info['desc'] = JText::_('AEC_MI_DESC_G2');

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$settings = array();
		$settings['gallery2path']		= array( 'inputD' );
		$settings['set_groups']			= array( 'toggle' );
		$settings['groups']				= array( 'list' );
		$settings['set_groups_user']	= array( 'toggle' );
		$settings['groups_sel_amt']		= array( 'inputA' );
		$settings['groups_sel_scope']	= array( 'list' );
		$settings['del_groups_exp']		= array( 'toggle' );
		$settings['create_albums']		= array( 'toggle' );
		$settings['albums_name']		= array( 'inputC' );

		$query = 'SELECT `g_id`, `g_groupType`, `g_groupName`'
			 	. ' FROM g2_Group'
			 	;
	 	$db->setQuery( $query );
	 	$groups = $db->loadObjectList();

		$sg = array();
		$sgs = array();

		$gr = array();
		foreach( $groups as $group ) {
			$desc = $group->g_groupName . '' . substr( strip_tags( "" ), 0, 30 );

			$gr[] = JHTML::_('select.option', $group->g_id, $desc );

			if ( !empty( $this->settings['groups'] ) ) {
				if ( in_array( $group->g_id, $this->settings['groups'] ) ) {
					$sg[] = JHTML::_('select.option', $group->g_id, $desc );
				}
			}

			if ( !empty( $this->settings['groups_sel_scope'] ) ) {
				if ( in_array( $group->g_id, $this->settings['groups_sel_scope'] ) ) {
					$sgs[] = JHTML::_('select.option', $group->g_id, $desc );
				}
			}
		}

		$settings['groups']				= array( 'list' );
		$settings['lists']['groups']	= JHTML::_( 'select.genericlist', $gr, 'groups[]', 'size="6" multiple="multiple"', 'value', 'text', $sg );
		$settings['groups_sel_scope']			= array( 'list' );
		$settings['lists']['groups_sel_scope']	= JHTML::_( 'select.genericlist', $gr, 'groups_sel_scope[]', 'size="6" multiple="multiple"', 'value', 'text', $sgs );

		return $settings;
	}

	function getMIform( $request )
	{
		$db = &JFactory::getDBO();

		$settings = array();

		if ( $this->settings['set_groups_user'] ) {
			$query = 'SELECT `g_id`, `g_groupType`, `g_groupName`'
				 	. ' FROM g2_Group'
				 	. ' WHERE `g_id` IN (' . implode( ',', $this->settings['groups_sel_scope'] ) . ')'
				 	;
		 	$db->setQuery( $query );
		 	$groups = $db->loadObjectList();

			$gr = array();
			foreach ( $groups as $group ) {
				$desc = $group->g_groupName . '' . substr( strip_tags( "" ), 0, 30 );

				$gr[] = JHTML::_('select.option', $group->g_id, $desc );
			}

			for ( $i=0; $i<$this->settings['groups_sel_amt']; $i++ ) {
				$settings['g2group_'.$i]			= array( 'list', JText::_('MI_MI_G2_USERSELECT_GROUP_NAME'), JText::_('MI_MI_G2_USERSELECT_GROUP_DESC') );
				$settings['lists']['g2group_'.$i]	= JHTML::_( 'select.genericlist', $gr, 'g2group_'.$i, 'size="6"', 'value', 'text', '' );
			}
		} else {
			return false;
		}

		return $settings;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		$this->loadG2Embed();

		$g2userid = $this->catchG2userid( $request->metaUser );

		$groups = array();

		if ( $this->settings['set_groups'] ) {
			$g = $this->settings['groups'];
			foreach ( $g as $groupid ) {
				$this->mapUserToGroup( $g2userid, $groupid );
				$groups[] = $groupid;
			}
		}

		if ( $this->settings['set_groups_user'] ) {
			for ( $i=0; $i<$this->settings['groups_sel_amt']; $i++ ) {
				if ( isset( $request->params['g2group_'.$i] ) ) {
					$this->mapUserToGroup( $g2userid, $request->params['g2group_'.$i] );
					$groups[] = $request->params['g2group_'.$i];
				}
			}
		}

		if ( !empty( $groups ) && !empty( $this->settings['create_albums'] ) && !empty( $this->settings['albums_name'] ) ) {
			array_unique( $groups );

			foreach ( $groups as $groupid ) {
				$query = 'SELECT `g_groupName`'
					 	. ' FROM g2_Group'
					 	. ' WHERE `g_id` = \'' . $groupid . '\''
					 	;
			 	$db->setQuery( $query );
			 	$groupname = $db->loadResult();

				if ( empty( $groupname ) ) {
					continue;
				}

				$query = 'SELECT `g_id`'
					 	. ' FROM g2_Item'
					 	. ' WHERE `g_title` = \'' . $groupname . '\''
					 	;
			 	$db->setQuery( $query );
			 	$parent = $db->loadResult();

				if ( empty( $parent ) ) {
					continue;
				}

				$this->createAlbumInAlbum( $g2userid, $parent, AECToolbox::rewriteEngineRQ( $this->settings['albums_name'], $request ) );
			}
		}

		return null;
	}

	function loadG2Embed()
	{
		if ( !empty( $this->settings['gallery2path'] ) ) {
			include_once( $this->settings['gallery2path'] . '/embed.php' );
			include_once( $this->settings['gallery2path'] . '/modules/core/classes/GalleryCoreApi.class' );
		}
	}

	function mapUserToGroup( $g2userid, $groupid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT g_userId'
				. ' FROM g2_UserGroupMap'
				. ' WHERE `g_userId` = \'' . $g2userid . '\' AND `g_groupId` = \'' . $groupid . '\''
				;
		$db->setQuery( $query );

		if ( !$db->loadResult() ) {
			list ($ret, $group) = GalleryCoreApi::addUserToGroup( $g2userid, $groupid );
			if ($ret) {
				$this->setError( $ret->_errorMessage );
				return false;
			}
		} else {
			return null;
		}
	}

	function createAlbumInAlbum( $g2userid, $parentid, $albumname )
	{
		$db = &JFactory::getDBO();

		// Check that we don't create a duplicate
		$query = 'SELECT g_id'
				. ' FROM g2_Item'
				. ' WHERE `g_ownerId` = \'' . $g2userid . '\''
				. ' AND `g_title` = \'' . $albumname . '\''
				;
		$db->setQuery( $query );
		$eid = $db->loadResult();

		if ( $eid ) {
			$query = 'SELECT g_parentId'
					. ' FROM g2_ChildEntity'
					. ' WHERE `g_id` = \'' . $eid . '\''
					;
			$db->setQuery( $query );
			$pid = $db->loadResult();

			if ( $pid == $parentid ) {
				return null;
			}
		}

		// Fallback sanity check in case the user has renamed the albums
		$query = 'SELECT count(*)'
				. ' FROM g2_Item'
				. ' WHERE `g_ownerId` = \'' . $g2userid . '\''
				;
		$db->setQuery( $query );
		$entries = $db->loadResult();

		if ( $entries >= $this->settings['groups_sel_amt'] ) {
			return null;
		}

		list ($ret, $group) = GalleryCoreApi::createAlbum( $parentid, $albumname, $albumname, '', '', ''  );
		if ($ret) {
			$this->setError( $ret->_errorMessage );
			return false;
		}

		return true;
	}

	function deleteUserFromGroup( $g2userid, $groupid )
	{
		$db = &JFactory::getDBO();

		$query = 'DELETE FROM g2_UserGroupMap'
				. ' WHERE `g_userId` = \'' . $g2userid . '\' AND `g_groupId` = \'' . $groupid . '\''
				;
		$db->setQuery( $query );

		if ( $db->query() ) {
			return true;
		} else {
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}

	function catchG2userid( $metaUser )
	{
		$g2id = $this->hasG2userid( $metaUser );

		if ( $g2id ) {
			// User found, return id
			return $g2id;
		} else {
			// User not found, create user, then recurse
			return $this->createG2User( $metaUser );
		}
	}

	function hasG2userid( $metaUser )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT g_id'
				. ' FROM g2_User'
				. ' WHERE `g_userName` = \'' . $metaUser->cmsUser->username . '\''
				;
		$db->setQuery( $query );

		return $db->loadResult();
	}

	function createG2User( $metaUser )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT max(g_id)'
				. ' FROM g2_Entity'
				;
		$db->setQuery( $query );

		$userid = $db->loadResult() + 1;

		$args = array();
		$args['username']		= $metaUser->cmsUser->username;
		$args['fullname']		= $metaUser->cmsUser->name;
		$args['hashedpassword']	= $metaUser->cmsUser->password;
		$args['email']			= $metaUser->cmsUser->email;

		list ($ret, $group) = GalleryEmbed::createUser( $metaUser->cmsUser->id, $args );
		if ($ret) {
			$this->setError( $ret->_errorMessage );
			return false;
		}

		// Add to standard groups
		$this->mapUserToGroup( $userid, 2 );
		$this->mapUserToGroup( $userid, 4 );

		if ( $db->query() ) {
			return $userid;
		} else {
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}

}

?>

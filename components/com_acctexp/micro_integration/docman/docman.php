<?php
/**
 * @version $Id: mi_docman.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - DocMan
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_docman
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_DOCMAN');
		$info['desc'] = JText::_('AEC_MI_DESC_DOCMAN');
		$info['type'] = array( 'directory_documentation.downloads', 'vendor.joomlatools' );

		return $info;
	}

	function checkInstallation()
	{
		$db = &JFactory::getDBO();

		$app = JFactory::getApplication();

		$tables	= array();
		$tables	= $db->getTableList();

		return in_array( $app->getCfg( 'dbprefix' ) . 'acctexp_mi_docman', $tables );
	}

	function detect_application()
	{
		return is_dir( JPATH_SITE . '/components/com_docman' );
	}

	function install()
	{
		$db = &JFactory::getDBO();

		$query = 'CREATE TABLE IF NOT EXISTS `#__acctexp_mi_docman` ('
					. '`id` int(11) NOT NULL auto_increment,'
					. '`userid` int(11) NOT NULL,'
					. '`active` int(4) NOT NULL default \'1\','
					. '`granted_downloads` int(11) NULL,'
					. '`unlimited_downloads` int(3) NULL,'
					. '`used_downloads` int(11) NULL,'
					. '`params` text NULL,'
					. ' PRIMARY KEY (`id`)'
					. ')'
					;
		$db->setQuery( $query );
		$db->query();
		return;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$db->setQuery( "SHOW COLUMNS FROM #__docman_groups LIKE 'groups_members'" );
		$result = $db->loadObject();

		if ( (strcmp($result->Field, '`groups_members`') === 0) && (strcmp($result->Type, 'text') === 0) ) {
			// Give extra space for the "too many users to hold all these feels" problem
			$db->setQuery("ALTER TABLE #__docman_groups CHANGE `groups_members` `groups_members` longtext NULL");
			$db->query();
		}

        $settings = array();
		$settings['add_downloads']		= array( 'inputA' );
		$settings['set_downloads']		= array( 'inputA' );
		$settings['set_unlimited']		= array( 'toggle' );

		$settings['delete_on_set']		= array( 'list' );
		$settings['set_group']			= array( 'toggle' );
		$settings['group']				= array( 'list' );
		$settings['delete_on_exp'] 		= array( 'list' );
		$settings['set_group_exp']		= array( 'toggle' );
		$settings['group_exp']			= array( 'list' );
		$settings['unset_unlimited']	= array( 'toggle' );
		$settings['rebuild']			= array( 'toggle' );
		$settings['remove']				= array( 'toggle' );

		$query = 'SELECT groups_id, groups_name, groups_description'
			 	. ' FROM #__docman_groups'
			 	;
	 	$db->setQuery( $query );
	 	$groups = $db->loadObjectList();

		$sg = array();
		$sge = array();

		$gr = array();
		if ( !empty( $groups ) ) {
			foreach( $groups as $group ) {
				$desc = $group->groups_name . ' - ' . substr( strip_tags( $group->groups_description ), 0, 30 );

				$gr[] = JHTML::_('select.option', $group->groups_id, $desc );

				if ( !empty( $this->settings['group'] ) ) {
					if ( in_array( $group->groups_id, $this->settings['group'] ) ) {
						$sg[] = JHTML::_('select.option', $group->groups_id, $desc );
					}
				}

				if ( !empty( $this->settings['group_exp'] ) ) {
					if ( in_array( $group->groups_id, $this->settings['group_exp'] ) ) {
						$sge[] = JHTML::_('select.option', $group->groups_id, $desc );
					}
				}
			}
		}

		$settings['lists']['group']			= JHTML::_('select.genericlist', $gr, 'group[]', 'size="4" multiple="multiple"', 'value', 'text', $sg );
		$settings['lists']['group_exp'] 	= JHTML::_('select.genericlist', $gr, 'group_exp[]', 'size="4" multiple="multiple"', 'value', 'text', $sge );

		if ( !empty( $this->settings['delete_on_set'] ) ) {
			$des = $this->settings['delete_on_set'];
		} else {
			$des = array();
		}

		if ( !empty( $this->settings['delete_on_exp'] ) ) {
			$dee = $this->settings['delete_on_exp'];
		} else {
			$dee = array();
		}

 		$del_opts = array();
		$del_opts[] = JHTML::_('select.option', "No", "Just apply group(s) below." );
		$del_opts[] = JHTML::_('select.option', "All", "Delete ALL, then apply group(s) below." );

		$settings['lists']['delete_on_set']	= JHTML::_('select.genericlist', $del_opts, 'delete_on_set', 'size="3"', 'value', 'text', $des );

		$del_opts[] = JHTML::_('select.option', "Set", "Delete group(s) selected above, then apply group(s) below." );

		$settings['lists']['delete_on_exp']	= JHTML::_('select.genericlist', $del_opts, 'delete_on_exp', 'size="3"', 'value', 'text', $dee );

		return $settings;
	}

	function profile_info( $request )
	{
		$db = &JFactory::getDBO();
		$mi_docmanhandler = new docman_restriction();
		$id = $mi_docmanhandler->getIDbyUserID( $request->metaUser->userid );

		if ( $id ) {
			$mi_docmanhandler->load( $id );
			if ( $mi_docmanhandler->active ) {
				$left = $mi_docmanhandler->getDownloadsLeft();
				if ( !$mi_docmanhandler->used_downloads) {
					$used = 0 ;
				} else {
					$used = $mi_docmanhandler->used_downloads;
				}
				$unlimited = $mi_docmanhandler->unlimited_downloads;
				$message = '<p>'.sprintf(JText::_('AEC_MI_DIV1_DOCMAN_USED'), $used).'</p>';
				if ( $unlimited > 0 ) {
					$message .='<p>' . sprintf( JText::_('AEC_MI_DIV1_DOCMAN_REMAINING'), JText::_('AEC_MI_DIV1_DOCMAN_UNLIMITED') ) . '</p>';
				} else {
					$message .= '<p>' . sprintf( JText::_('AEC_MI_DIV1_DOCMAN_REMAINING'), $left ) . '</p>';
				}
				return $message;
			}
		} else {
			return '';
		}
	}

	function hacks()
	{
		$hacks = array();

		$downloadhack =	'// AEC HACK docmandownloadphp START' . "\n"
		. '$user =& JFactory::getUser();' . "\n"
		. 'include_once( JPATH_SITE . \'/components/com_acctexp/acctexp.class.php\' );' . "\n"
		. 'include_once( JPATH_SITE . \'/components/com_acctexp/micro_integration/docman/docman.php\');' . "\n\n"
		. '$restrictionhandler = new docman_restriction();' . "\n"
		. '$restrict_id = $restrictionhandler->getIDbyUserID( $user->id );' . "\n"
		. '$restrictionhandler->load( $restrict_id );' . "\n\n"
		. 'if (!$restrictionhandler->hasDownloadsLeft()) {' . "\n"
		. "\t" . '$restrictionhandler->noDownloadsLeft();' . "\n"
		. '} else {' . "\n"
		. "\t" . '$restrictionhandler->useDownload();' . "\n"
		. '}' . "\n"
		. '// AEC HACK docmandownloadphp END' . "\n"
		;

		$n = 'docmandownloadphp';
		$hacks[$n]['name']				=	'download.php';
		$hacks[$n]['desc']				=	JText::_('AEC_MI_HACK1_DOCMAN');
		$hacks[$n]['type']				=	'file';

		if ( file_exists( JPATH_SITE . '/components/com_docman/includes_frontend/download.php' ) ) {
			$hacks[$n]['filename']			=	JPATH_SITE . '/components/com_docman/includes_frontend/download.php';
		} else {
			$hacks[$n]['filename']			=	JPATH_SITE . '/components/com_docman/includes/download.php';
		}

		$hacks[$n]['read']				=	'// If the remote host is not allowed';
		$hacks[$n]['insert']			=	$downloadhack . "\n"  . $hacks[$n]['read'];

		return $hacks;
	}

	function expiration_action( $request )
	{
		$db = &JFactory::getDBO();

 		if ( $this->settings['delete_on_exp'] == "Set" ) {
			foreach ( $this->settings['group'] as $tgroup ) {
				$this->DeleteUserFromGroup( $request->metaUser->userid, $tgroup );
			}
		} elseif ( $this->settings['delete_on_exp'] == "All" ) {
			$groups = $this->GetUserGroups( $request->metaUser->userid );

			foreach ( $groups as $group ) {
				$this->DeleteUserFromGroup( $request->metaUser->userid, $group );
			}
		}

		if ( $this->settings['set_group_exp'] && !empty( $this->settings['group_exp'] ) ) {
			foreach ( $this->settings['group_exp'] as $group ) {
				$this->AddUserToGroup( $request->metaUser->userid, $group );
			}
		}

		$mi_docmanhandler = new docman_restriction();
		$id = $mi_docmanhandler->getIDbyUserID( $request->metaUser->userid );
		$mi_id = $id ? $id : 0;
		$mi_docmanhandler->load( $mi_id );


		if ( $mi_id ) {
			if ( $this->settings['unset_unlimited'] ) {
				$mi_docmanhandler->unlimited_downloads = 0 ;
			}
			$mi_docmanhandler->active = 0;
			$mi_docmanhandler->check();
			$mi_docmanhandler->store();
		}

		return true;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

 		if ( $this->settings['delete_on_set'] == "All" ) {
			$groups = $this->GetUserGroups( $request->metaUser->userid );

			foreach ( $groups as $group ) {
				$this->DeleteUserFromGroup( $request->metaUser->userid, $group );
			}
		}

		if ( $this->settings['set_group'] && !empty( $this->settings['group'] ) ) {
			foreach ( $this->settings['group'] as $group ) {
				$this->AddUserToGroup( $request->metaUser->userid, $group );
			}
		}

		$mi_docmanhandler = new docman_restriction();
		$id = $mi_docmanhandler->getIDbyUserID( $request->metaUser->userid );
		$mi_id = $id ? $id : 0;
		$mi_docmanhandler->load( $mi_id );

		if ( !$mi_id ) {
			$mi_docmanhandler->userid = $request->metaUser->userid;
		}

		$mi_docmanhandler->active = 1;

		if ( $this->settings['set_downloads'] ) {
			$mi_docmanhandler->setDownloads( $this->settings['set_downloads'] );
		} elseif ( $this->settings['add_downloads'] ) {
			$mi_docmanhandler->addDownloads( $this->settings['add_downloads'] );
		}
		if ( $this->settings['set_unlimited'] ) {
			$mi_docmanhandler->unlimited_downloads = true ;
		}
		$mi_docmanhandler->check();
		$mi_docmanhandler->store();

		return true;
	}

	function GetUserGroups( $userid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `groups_id`'
				. ' FROM #__docman_groups'
				;
		$db->setQuery( $query );
		$ids = xJ::getDBArray( $db );

		$groups = array();
		foreach ( $ids as $groupid ) {
			$query = 'SELECT `groups_members`'
					. ' FROM #__docman_groups'
					. ' WHERE `groups_id` = \'' . $groupid . '\''
					;
			$db->setQuery( $query );
			$users = explode( ',', $db->loadResult() );

			if ( in_array( $userid, $users ) ) {
				$groups[] = $groupid;
			}
		}

		return $groups;
	}

	function AddUserToGroup( $userid, $groupid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `groups_members`'
			. ' FROM #__docman_groups'
			. ' WHERE `groups_id` = \'' . $groupid . '\''
			;
		$db->setQuery( $query );
		$users = explode( ',', $db->loadResult() );

		if ( in_array( $userid, $users ) ) {
			return null;
		} else {
			// Make sure we have no empty value
			$search = 0;
			while ( $search !== false ) {
				$search = array_search( '', $users );
				if ( $search !== false ) {
					unset( $users[$search] );
				}
			}

			$users[] = $userid;

			$query = 'UPDATE #__docman_groups'
				. ' SET `groups_members` = \'' . implode( ',', $users ) . '\''
				. ' WHERE `groups_id` = \'' . $groupid . '\''
				;
			$db->setQuery( $query );
			$db->query();

			return true;
		}
	}

	function DeleteUserFromGroup( $userid, $groupid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `groups_members`'
			. ' FROM #__docman_groups'
			. ' WHERE `groups_id` = \'' . $groupid . '\''
			;
		$db->setQuery( $query );
		$users = explode( ',', $db->loadResult() );

		if ( in_array( $userid, $users ) ) {
			$key = array_search( $userid, $users );
			unset( $users[$key] );

			// Make sure we have no empty value
			$search = 0;
			while ( $search !== false ) {
				$search = array_search( '', $users );
				if ( $search !== false ) {
					unset( $users[$search] );
				}
			}

			$query = 'UPDATE #__docman_groups'
				. ' SET `groups_members` = \'' . implode( ',', $users ) . '\''
				. ' WHERE `groups_id` = \'' . $groupid . '\''
				;
			$db->setQuery( $query );
			$db->query();

			return true;
		} else {
			return null;
		}
	}
}

class docman_restriction extends serialParamDBTable {
	/** @var int Primary key */
	var $id						= null;
	/** @var int */
	var $userid 				= null;
	/** @var int */
	var $active					= null;
	/** @var int */
	var $granted_downloads		= null;
	/** @var int */
	var $unlimited_downloads	= null;
	/** @var text */
	var $used_downloads			= null;
	/** @var text */
	var $params					= null;

	function getIDbyUserID( $userid ) {
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
			. ' FROM #__acctexp_mi_docman'
			. ' WHERE `userid` = \'' . $userid . '\''
			;
		$db->setQuery( $query );
		return $db->loadResult();
	}

	function docman_restriction() {
		parent::__construct( '#__acctexp_mi_docman', 'id' );
	}

	function is_active()
	{
		if ( $this->active ) {
			return true;
		} else {
			return false;
		}
	}

	function getDownloadsLeft()
	{
		if (  $this->unlimited_downloads > 0 ) {
			return 'unlimited';
		} else {
			$downloads_left = $this->granted_downloads - $this->used_downloads;
			return $downloads_left;
		}
	}

	function hasDownloadsLeft()
	{
                $check = $this->getDownloadsLeft();

                if ( empty ( $check ) ) {
                        return false;
                } elseif  (  is_numeric ($check)  )  {
                        if ( $check > 0 ) {
                                return true;
                        } else {
                                return false;
                        }
                } elseif ( $check == "unlimited" ) {
                        return true;
                }

	}

	function noDownloadsLeft()
	{
		aecRedirect( 'index.php?option=com_docman' , JText::_('AEC_MI_DOCMAN_NOCREDIT') );
	}

	function useDownload()
	{
		if ( $this->hasDownloadsLeft() && $this->is_active() ) {
			$this->used_downloads++;
			$this->check();
			$this->store();
			return true;
		} else {
			return false;
		}
	}

	function setDownloads( $set )
	{
		$this->granted_downloads = $set + $this->used_downloads;
	}

	function addDownloads( $add )
	{
		$this->granted_downloads += $add;
	}
}
?>

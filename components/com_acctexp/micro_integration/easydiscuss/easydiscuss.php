<?php
/**
 * @version $Id: mi_easydiscuss.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - EasyDiscuss
 * @copyright Copyright (C) 2012 David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
defined('_JEXEC') OR defined( '_VALID_MOS' ) OR die( 'Direct Access to this location is not allowed.' );

class mi_easydiscuss extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_EASYDISCUSS');
		$info['desc'] = JText::_('AEC_MI_DESC_EASYDISCUSS');
		$info['type'] = array( 'communication.q_a', 'vendor.stackideas' );

		return $info;
	}

	function Settings()
	{
		$settings = array();

		if ( !$this->isInstalled() ) {
			echo 'This module can not work without the EasyDiscuss Component';
			return $settings;
		}

		$db = &JFactory::getDBO();
		$db->setQuery( 'SELECT * FROM #__discuss_ranks' );

		$ranks = $db->loadObjectList();

		$ranklist = array();

		foreach ( $ranks as $id => $row ) {
			$ranklist[] = JHTML::_('select.option', $row->id, $row->id . ': ' . $row->title );
		}

		$settings['rank']		= array( 'list' );
		$settings['unrank']		= array( 'list' );

		$db->setQuery( 'SELECT * FROM #__discuss_badges' );

		$ranks = $db->loadObjectList();

		$badgelist = array();

		foreach ( $ranks as $id => $row ) {
			$badgelist[] = JHTML::_('select.option', $row->id, $row->id . ': ' . $row->title );
		}

		$settings['badge']		= array( 'list' );
		$settings['unbadge']	= array( 'list' );

		$settings = $this->autoduplicatesettings( $settings );

		foreach ( $settings as $k => $v ) {
			$value = "";
			if ( isset( $this->settings[$k] ) ) {
				$value = $this->settings[$k];
			}
				
			if ( strpos( $k, 'rank' ) !== false ) {
				$settings['lists'][$k]	= JHTML::_( 'select.genericlist', $ranklist, $k."[]", 'size="5" multiple="multiple"', 'value', 'text', $value );
			} elseif ( strpos( $k, 'badge' ) !== false ) {
				$settings['lists'][$k]	= JHTML::_( 'select.genericlist', $badgelist, $k."[]", 'size="5" multiple="multiple"', 'value', 'text', $value );
			}
		}

		$xsettings = array();
		$xsettings['rebuild']	= array( 'toggle' );
		$xsettings['remove']	= array( 'toggle' );

		return array_merge( $xsettings, $settings );
	}

	function isInstalled()
	{
		$app = JFactory::getApplication();

		$db = &JFactory::getDBO();

		$tables = $db->getTableList();
		
		return	in_array( $app->getCfg( 'dbprefix' ) . "discuss_ranks_users", $tables );
	}

	function relayAction( $request )
	{
		if ( !empty( $this->settings['rank' . $request->area] ) || !empty( $this->settings['unrank' . $request->area] ) ) {
			$this->changeRank( $request->metaUser->userid, $this->settings['rank' . $request->area], $this->settings['unrank' . $request->area] );
		}

		if ( !empty( $this->settings['badge' . $request->area] ) || !empty( $this->settings['unbadge' . $request->area] ) ) {
			$this->changeBadge( $request->metaUser->userid, $this->settings['badge' . $request->area], $this->settings['unbadge' . $request->area] );
		}
	}

	function changeRank( $userid, $add, $remove )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `user_id`, `rank_id`'
				. ' FROM #__discuss_ranks_users'
				. ' WHERE `user_id` = \'' . $userid . '\''
				;

		$db->setQuery( $query );

		$ranks = $db->loadObjectList();

		if ( !empty( $remove ) && !empty( $ranks ) ) {
			foreach ( $remove as $rank_id ) {
				foreach ( $ranks as $rk => $rank ) {
					if ( $rank->rank_id == $rank_id ) {
						$query = 'REMOVE FROM #__discuss_ranks_users'
								. ' WHERE `rank_id` = \'' . $rank_id . '\''
								. ' AND `user_id` = \'' . $userid . '\''
								;

						$db->setQuery( $query );
						$db->query();

						unset( $ranks[$rk] );
					}
				}
			}
		}

		if ( !empty( $add ) ) {
			foreach ( $add as $rank_id ) {
				if ( in_array( $rank_id, $ranks ) ) {
					continue;
				}

				$query = 'INSERT INTO #__discuss_ranks_users'
						. ' ( `rank_id` , `user_id`, `created` )'
						. ' VALUES (\'' . $rank_id . '\', \'' . $userid . '\', \'' . date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) ) . '\')'
						;

				$db->setQuery( $query );
				$db->query();
			}
		}
	}

	function changeBadge( $userid, $add, $remove )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `user_id`, `badge_id`'
				. ' FROM #__discuss_badges_users'
				. ' WHERE `user_id` = \'' . $userid . '\''
				;

		$db->setQuery( $query );

		$badges = $db->loadObjectList();

		if ( !empty( $remove ) && !empty( $badges ) ) {
			foreach ( $remove as $badge_id ) {
				foreach ( $badges as $bk => $badge ) {
					if ( $badge->badge_id == $badge_id ) {
						$query = 'REMOVE FROM #__discuss_badges_users'
								. ' WHERE `badge_id` = \'' . $badge_id . '\''
								. ' AND `user_id` = \'' . $userid . '\''
								;

						$db->setQuery( $query );
						$db->query();

						unset( $badges[$bk] );
					}
				}
			}
		}

		if ( !empty( $add ) ) {
			foreach ( $add as $badge_id ) {
				if ( in_array( $badge_id, $badges ) ) {
					continue;
				}

				$query = 'INSERT INTO #__discuss_badges_users'
						. ' ( `badge_id` , `user_id`, `created` )'
						. ' VALUES (\'' . $badge_id . '\', \'' . $userid . '\', \'' . date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) ) . '\')'
						;

				$db->setQuery( $query );
				$db->query();
			}
		}
	}

}

?>
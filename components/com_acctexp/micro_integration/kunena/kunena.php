<?php
/**
 * @version $Id: mi_kunena.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Kunena
 * @copyright Copyright (C) 2011 David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
defined('_JEXEC') OR defined( '_VALID_MOS' ) OR die( 'Direct Access to this location is not allowed.' );

class mi_kunena extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_KUNENA');
		$info['desc'] = JText::_('AEC_MI_DESC_KUNENA');
		$info['type'] = array( 'communication.forum' );

		return $info;
	}

	function Settings()
	{
		$settings = array();

		if ( !$this->isInstalled() ) {
			echo 'This module can not work without the Kunena Forum Component';
			return $settings;
		}

		$db = &JFactory::getDBO();
		$db->setQuery( 'SELECT * FROM #__' . $this->dbTable() . '_ranks' );

		$ranks = $db->loadObjectList();

		$ranklist = array();
		$ranklist[] = JHTML::_('select.option', 0, "--- --- ---" );

		foreach ( $ranks as $id => $row ) {
			$ranklist[] = JHTML::_('select.option', $row->rank_id, $row->rank_id . ': ' . $row->rank_title );
		}

		$settings['rank']	= array( 'list' );
		$settings['unrank']	= array( 'list' );

		$settings = $this->autoduplicatesettings( $settings );

		foreach ( $settings as $k => $v ) {
			if ( isset( $this->settings[$k] ) ) {
				$settings['lists'][$k]	= JHTML::_( 'select.genericlist', $ranklist, $k, 'size="1"', 'value', 'text', $this->settings[$k] );
			} else {
				$settings['lists'][$k]	= JHTML::_( 'select.genericlist', $ranklist, $k, 'size="1"', 'value', 'text', '' );
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
		
		return	in_array( $app->getCfg( 'dbprefix' ) . "kunena_users", $tables ) ||
				in_array( $app->getCfg( 'dbprefix' ) . "fb_users", $tables );
	}

	function is16()
	{
		static $is;

		if ( is_null( $is ) ) {
			$app = JFactory::getApplication();

			$db = &JFactory::getDBO();

			$tables = $db->getTableList();

			$is = in_array( $app->getCfg( 'dbprefix' ) . "kunena_ranks", $tables );

			return $is;
		} else {
			return $is;
		}
	}

	function dbTable()
	{
		if ( $this->is16() ) {
			return 'kunena';
		} else {
			return 'fb';
		}
	}

	function relayAction( $request )
	{
		if ( !empty( $this->settings['rank' . $request->area] ) || !empty( $this->settings['unrank' . $request->area] ) ) {
			$this->changeRank( $request->metaUser->userid, $this->settings['rank' . $request->area], $this->settings['unrank' . $request->area] );
		}
	}

	function changeRank( $userid, $add, $remove )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `userid`, `rank`'
				. ' FROM #__' . $this->dbTable() . '_users'
				. ' WHERE `userid` = \'' . $userid . '\''
				;

		$db->setQuery( $query );

		$kuser = $db->loadObject();

		if ( isset( $kuser->rank ) ) {
			$rank = $kuser->rank;
		} else {
			$rank = 0;
		}

		if ( !empty( $remove ) && $kuser->userid ) {
			if ( $remove == $rank ) {
				$newrank = 0;
			}
		}

		if ( !empty( $add ) && $kuser->userid ) {
			if ( $add == $rank ) {
				// Already in the correct usergroup
				return null;
			} else {
				$newrank = $add;
			}
		}

		if ( is_null($kuser) && !empty( $add ) ) {
			$newrank = $add;
		} 


		if ( $kuser->userid ) {
			$query = 'UPDATE #__' . $this->dbTable() . '_users'
					. ' SET `rank` = \'' . $newrank . '\''
					. ' WHERE `userid` = \'' . $userid . '\''
					;
		} else {
			$query = 'INSERT INTO #__' . $this->dbTable() . '_users'
					. ' ( `rank` , `userid` )'
					. ' VALUES (\'' . $newrank . '\', \'' . $userid . '\')'
					;
		}

		$db->setQuery( $query );
		$db->query();
	}

}

?>
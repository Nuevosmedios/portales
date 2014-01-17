<?php
/**
 * @version $Id: mi_apc.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Advanced Profile Control
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_apc
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_APC');
		$info['desc'] = JText::_('AEC_MI_DESC_APC');
		$info['type'] = array( 'community.social' );

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT groupid, title, description'
	 	. ' FROM #__comprofiler_accesscontrol_groups'
	 	;
	 	$db->setQuery( $query );
	 	$groups = $db->loadObjectList();

		$sg = array();
		if ( !empty( $groups ) ) {
			foreach( $groups as $group ) {
				$sg[] = JHTML::_('select.option', $group->groupid, $group->title . ' - ' . substr( strip_tags( $group->description ), 0, 30 ) );
			}
		}

        $settings = array();
		$settings['set_group']			= array( 'toggle' );
		$settings['set_default']		= array( 'toggle' );
		$settings['group']				= array( 'list' );
		$settings['set_group_exp']		= array( 'toggle' );
		$settings['set_default_exp']	= array( 'toggle' );
		$settings['group_exp']			= array( 'list' );
		$settings['rebuild']			= array( 'toggle' );
		$settings['remove']				= array( 'toggle' );

		if ( !isset( $this->settings['group'] ) ) {
			$this->settings['group'] = 0;
		}

		if ( !isset( $this->settings['group_exp'] ) ) {
			$this->settings['group_exp'] = 0;
		}

		$settings['lists']['group']		= JHTML::_('select.genericlist', $sg, 'group', 'size="4"', 'value', 'text', $this->settings['group'] );
		$settings['lists']['group_exp'] = JHTML::_('select.genericlist', $sg, 'group_exp', 'size="4"', 'value', 'text', $this->settings['group_exp'] );

		return $settings;
	}

	function expiration_action( $request )
	{
		$db = &JFactory::getDBO();

		if ( $this->settings['set_group_exp'] ) {
			return $this->setGroupId( $request->metaUser->userid, $this->settings['group_exp'], $this->settings['set_default_exp'] );
		}
	}

	function action( $request )
	{
		if ( $this->settings['set_group'] ) {
			return $this->setGroupId( $request->metaUser->userid, $this->settings['group'], $this->settings['set_default'] );
		}
	}

	function setGroupId( $userid, $groupid, $default = false )
	{
		$db = &JFactory::getDBO();

		if ( $default ) {
			$query = 'SELECT title'
		 	. ' FROM #__comprofiler_accesscontrol_groups'
		 	. ' WHERE default = \'1\''
		 	;
		 	$db->setQuery( $query );
		 	$group = $db->loadResult();
		} else {
			$query = 'SELECT title'
		 	. ' FROM #__comprofiler_accesscontrol_groups'
		 	. ' WHERE groupid = \'' . $groupid . '\''
		 	;
		 	$db->setQuery( $query );
		 	$group = $db->loadResult();
		}

		if ( !empty( $group ) ) {
			$query = 'UPDATE #__comprofiler'
					. ' SET `apc_type` = \'' . $group . '\''
					. ' WHERE `id` = \'' . (int) $this->userid . '\''
					;
			$db->setQuery( $query );
		} else {
			return false;
		}
	}
}

?>

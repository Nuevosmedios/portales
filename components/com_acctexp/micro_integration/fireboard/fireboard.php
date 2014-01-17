<?php
/**
 * @version $Id: mi_fireboard.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Fireboard
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_fireboard
{

	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_FIREBOARD');
		$info['desc'] = JText::_('AEC_MI_DESC_FIREBOARD');
		$info['type'] = array( 'communication.forum' );

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`, `title`'
			 	. ' FROM #__fb_groups'
			 	;
	 	$db->setQuery( $query );
	 	$groups = $db->loadObjectList();

		$sg = array();
		if ( !empty( $groups ) ) {
			foreach ( $groups as $group ) {
				$sg[] = JHTML::_('select.option', $group->id, $group->title );
			}	
		}

        $settings = array();

		if ( !isset( $this->settings['group'] ) ) {
			$this->settings['group'] = 0;
		}

		if ( !isset( $this->settings['group_exp'] ) ) {
			$this->settings['group_exp'] = 0;
		}

		$settings['lists']['group']		= JHTML::_('select.genericlist', $sg, 'group', 'size="4"', 'value', 'text', $this->settings['group'] );
		$settings['lists']['group_exp'] = JHTML::_('select.genericlist', $sg, 'group_exp', 'size="4"', 'value', 'text', $this->settings['group_exp'] );

		$settings['set_group']			= array( 'toggle' );
		$settings['group']				= array( 'list' );
		$settings['set_group_exp']		= array( 'toggle' );
		$settings['group_exp']			= array( 'list' );
		$settings['rebuild']			= array( 'toggle' );
		$settings['remove']				= array( 'toggle' );

		return $settings;
	}

	function detect_application()
	{
		return is_dir( JPATH_SITE . '/components/com_fireboard' );
	}

	function expiration_action( $request )
	{
		$db = &JFactory::getDBO();

		if ( $this->settings['set_group_exp'] ) {
			$query = 'UPDATE #__fb_users'
				. ' SET `group_id` = \'' . $this->settings['group_exp'] . '\''
				. ' WHERE `userid` = \'' . $request->metaUser->userid . '\''
				;
			$db->setQuery( $query );
			$db->query();
		}

		return true;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		if ( $this->settings['set_group'] ) {
			// Check if exists - users only appear in FB users table normally when they have posted
			$query = 'SELECT `group_id`'
					. ' FROM #__fb_users'
					. ' WHERE `userid` = \'' . $request->metaUser->userid . '\''
					;
			$db->setQuery( $query );

			// If already an entry exists -> update, if not -> create
			if ( $db->loadResult() ) {
				$query = 'UPDATE #__fb_users'
						. ' SET `group_id` = \'' . $this->settings['group'] . '\''
						. ' WHERE `userid` = \'' . $request->metaUser->userid . '\''
						;
			} else {
				$query = 'INSERT INTO #__fb_users'
						. ' ( `group_id` , `userid` )'
						. ' VALUES (\'' . $this->settings['group'] . '\', \'' . $request->metaUser->userid . '\')'
						;
			}

			// Carry out query
			$db->setQuery( $query );
			$db->query();
		}

		return true;
	}
}

?>

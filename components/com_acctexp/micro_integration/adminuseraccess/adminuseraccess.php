<?php
/*
AEC micro-integration plugin
for Frontend-User-Access
connects subscription plans to Frontend-User-Access-usergroups
version 1.0.1
*/


// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_adminuseraccess
{

	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_ADMINUSERACCESS');
		$info['desc'] = JText::_('AEC_MI_DESC_ADMINUSERACCESS');
		$info['type'] = array( 'user.access_restriction', 'vendor.pages-and-items' );

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`, `name`'
				. ' FROM #__pi_aua_usergroups'
				. ' ORDER BY name ASC'
				;
		$db->setQuery( $query );

		$groups = $db->loadObjectList();

		$auagroups = array();
		$auagroups[] = JHTML::_('select.option', 0, 'no group' );

		if ( !empty( $groups ) ) {
			foreach ( $groups as $group ) {
				$auagroups[] = JHTML::_('select.option', $group->id, $group->name );
			}
		}

		if ( !isset( $this->settings['group'] ) ) {
			$this->settings['group'] = 0;
		}

		if ( !isset( $this->settings['group_exp'] ) ) {
			$this->settings['group_exp'] = 0;
		}

        $settings = array();

		$settings['lists']['group']		= JHTML::_('select.genericlist', $auagroups, 'group', 'size="4"', 'value', 'text', $this->settings['group'] );
		$settings['lists']['group_exp'] = JHTML::_('select.genericlist', $auagroups, 'group_exp', 'size="4"', 'value', 'text', $this->settings['group_exp'] );

		$settings['set_group']			= array( 'toggle' );
		$settings['group']				= array( 'list' );
		$settings['set_group_exp']		= array( 'toggle' );
		$settings['group_exp']			= array( 'list' );
		$settings['rebuild']			= array( 'toggle' );
		$settings['remove']				= array( 'toggle' );

		return $settings;
	}

	function action( $request )
	{
		if ( !empty( $this->settings['set_group'] ) && !empty( $this->settings['group'] ) ) {
			$this->update_aua_group( $request->metaUser->userid, $this->settings['group'] );
		}

		return true;
	}

	function expiration_action( $request )
	{
		if ( !empty( $this->settings['set_group_exp'] ) && !empty( $this->settings['group_exp'] ) ) {
			$this->update_aua_group( $request->metaUser->userid, $this->settings['group_exp'] );
		}

		return true;
	}

	function update_aua_group($user_id, $aua_group)
	{
		$db = &JFactory::getDBO();

		$query = 'UPDATE #__pi_aua_userindex'
				. ' SET `group_id` = \'' . $aua_group . '\''
				. ' WHERE `user_id` = \'' . $user_id . '\''
				;
		$db->setQuery( "UPDATE #__pi_aua_userindex SET group_id='$aua_group' WHERE user_id='$user_id'"	);
		$db->query();
	}

}

?>
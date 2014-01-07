<?php
/**
 * @version $Id: mi_redshop.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - redshop
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_redshop
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_REDSHOP');
		$info['desc'] = JText::_('AEC_MI_DESC_REDSHOP');
		$info['type'] = array( 'ecommerce.shopping_cart', 'vendor.redcomponent' );

		return $info;
	}

	function Settings()
	{
		$shopper_groups = $this->getShopperGroups();

		$sg = array();
		if ( !empty( $shopper_groups ) ) {
			foreach ( $shopper_groups as $group ) {
				$sg[] = JHTML::_('select.option', $group->id, $group->title );
			}
		}

		if ( !isset( $this->settings['shopper_group'] ) ) {
			$this->settings['shopper_group'] = 0;
		}

		if ( !isset( $this->settings['shopper_group_exp'] ) ) {
			$this->settings['shopper_group_exp'] = 0;
		}

		$settings = array();
		$settings['lists']['shopper_group']		= JHTML::_( 'select.genericlist', $sg, 'shopper_group', 'size="4"', 'value', 'text', $this->settings['shopper_group'] );
		$settings['lists']['shopper_group_exp'] = JHTML::_( 'select.genericlist', $sg, 'shopper_group_exp', 'size="4"', 'value', 'text', $this->settings['shopper_group_exp'] );

		$settings['set_shopper_group']		= array( 'toggle' );
		$settings['shopper_group']			= array( 'list' );
		$settings['set_shopper_group_exp']	= array( 'toggle' );
		$settings['shopper_group_exp']		= array( 'list' );
		$settings['rebuild']				= array( 'toggle' );
		$settings['remove']					= array( 'toggle' );

		return $settings;
	}

	function expiration_action( $request )
	{
		if ( $this->settings['set_shopper_group_exp'] ) {
			$this->updateGroup( $request->metaUser->userid, $this->settings['shopper_group_exp'] );

			return true;
		} else {
			return null;
		}
	}

	function action( $request )
	{
		if ( $this->settings['set_shopper_group'] ) {
			$this->updateGroup( $request->metaUser->userid, $this->settings['shopper_group'] );

			return true;
		} else {
			return null;
		}
	}

	function getShopperGroups()
	{	
		$db = &JFactory::getDBO();
		$query = 'SELECT `shopper_group_name` AS `title`, `shopper_group_id` AS `id` FROM `#__redshop_shopper_group`';
	
	 	$db->setQuery( $query );
	 	return $db->loadObjectList();
	}

	function updateGroup( $userid, $shoppergroup )
	{
		$db = &JFactory::getDBO();

		$query = 'UPDATE #__redshop_usergroupxref'
				. ' SET `group_id` = \'' . $shoppergroup . '\''
				. ' WHERE `user_id` = \'' . $userid . '\''
				;
		$db->setQuery( $query );
		$db->query();
	}
}
?>

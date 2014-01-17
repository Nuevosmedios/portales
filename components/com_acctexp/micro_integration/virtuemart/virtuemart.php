<?php
/**
 * @version $Id: mi_mysql_query.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - MySQL Query
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_virtuemart
{
	function mi_virtuemart()
	{
		$db = &JFactory::getDBO();
	 	$db->setQuery( 'SHOW TABLES LIKE \'%' . $db->getPrefix() . 'virtuemart_shoppergroups%\'' );

	 	$this->isv2 = $db->loadResult() ? true : false;
	}

	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_VIRTM');
		$info['desc'] = JText::_('AEC_MI_DESC_VIRTM');
		$info['type'] = array( 'ecommerce.shopping_cart', 'vendor.virtuemart' );

		return $info;
	}

	function Settings()
	{
		$shopper_groups = $this->getShopperGroups();

		$sg = array();
		if ( !empty( $shopper_groups ) ) {
			foreach ( $shopper_groups as $group ) {
				$sg[] = JHTML::_('select.option', $group->shopper_group_id, $group->shopper_group_name );
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
		$settings['create_account']			= array( 'toggle' );
		$settings['rebuild']				= array( 'toggle' );
		$settings['remove']					= array( 'toggle' );

		return $settings;
	}

	function expiration_action( $request )
	{
		if ( $this->settings['set_shopper_group_exp'] ) {
			if ( $this->checkVMuserexists( $request->metaUser->userid ) ) {
				$this->updateVMuserSgroup( $request->metaUser->userid, $this->settings['shopper_group_exp'] );
			} elseif ( $this->settings['create_account'] ) {
				$this->createVMuser( $request->metaUser, $this->settings['shopper_group_exp'] );
			}

			return true;
		} else {
			return false;
		}
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		if ( $this->settings['set_shopper_group'] ) {
			if ( $this->checkVMuserexists( $request->metaUser->userid ) ) {
				$this->updateVMuserSgroup( $request->metaUser->userid, $this->settings['shopper_group'] );
			} elseif ( $this->settings['create_account'] ) {
				$this->createVMuser( $request->metaUser, $this->settings['shopper_group'] );
			}

			return true;
		} else {
			return false;
		}
	}

	function getShopperGroups()
	{
		$db = &JFactory::getDBO();

		if ( $this->isv2 ) {
			$query = 'SELECT `virtuemart_shoppergroup_id` AS `shopper_group_id`, `shopper_group_name`'
					. ' FROM #__virtuemart_shoppergroups'
					;
		} else {
			$query = 'SELECT `shopper_group_id`, `shopper_group_name`'
					. ' FROM #__vm_shopper_group'
					;
		}
	 	$db->setQuery( $query );
	 	return $db->loadObjectList();
	}

	function checkVMuserexists( $userid )
	{
		$db = &JFactory::getDBO();


		if ( $this->isv2 ) {
			$query = 'SELECT `virtuemart_user_id`'
					. ' FROM #__virtuemart_userinfos'
					. ' WHERE `virtuemart_user_id` = \'' . $userid . '\''
					;
		} else {
			$query = 'SELECT `user_id`' // Jonathan Appleton changed this from id to user_id - good find indeed!
					. ' FROM #__vm_user_info'
					. ' WHERE `user_id` = \'' . $userid . '\''
					;
		}

		$db->setQuery( $query );
		return $db->loadResult();
	}

	function updateVMuserSgroup( $userid, $shoppergroup )
	{
		$db = &JFactory::getDBO();

		if ( $this->isv2 ) {
			$query = 'UPDATE #__virtuemart_vmuser_shoppergroups'
					. ' SET `virtuemart_shoppergroup_id` = \'' . $shoppergroup . '\''
					. ' WHERE `virtuemart_user_id` = \'' . $userid . '\''
					;
		} else {
			$query = 'UPDATE #__vm_shopper_vendor_xref'
					. ' SET `shopper_group_id` = \'' . $shoppergroup . '\''
					. ' WHERE `user_id` = \'' . $userid . '\''
					;
		}
		$db->setQuery( $query );
		$db->query();
	}

	function createVMuser( $metaUser, $shoppergroup )
	{
		$app = JFactory::getApplication();

		$db = &JFactory::getDBO();

		// TODO: Replace with RWEngine call
		$name = explode( ' ', $metaUser->cmsUser->name );
		$namount = count( $name );
		if ( $namount >= 3 ) {
			$firstname = $name[0];
			$mname = '';
			for( $i=0; $i<$namount; $i++ ) {
				$mname[] = $name[$i];
			}
			$middlename = implode( ' ', $mname );
			$lastname = $name[$namount];
		} elseif ( count( $name ) == 2 ) {
			$firstname = $name[0];
			$middlename = '';
			$lastname = $name[1];
		} else {
			$firstname = $name[0];
			$middlename = '';
			$lastname = '';
		}

		$numberofrows	= 1;
		while ( $numberofrows ) {
			// seed random number generator
			srand( (double) microtime() * 10000 );
			$inum =	strtolower( substr( base64_encode( md5( rand() ) ), 0, 32 ) );
			// Check if already exists

			if ( $this->isv2 ) {
				$query = 'SELECT count(*)'
						. ' FROM #__virtuemart_userinfos'
						. ' WHERE `virtuemart_userinfo_id` = \'' . $inum . '\''
						;
			} else {
				$query = 'SELECT count(*)'
						. ' FROM #__vm_user_info'
						. ' WHERE `user_info_id` = \'' . $inum . '\''
						;
			}

			$db->setQuery( $query );
			$numberofrows = $db->loadResult();
		}

		// Create Useraccount
		if ( $this->isv2 ) {
			$query  = 'INSERT INTO #__virtuemart_vmusers'
					. ' (virtuemart_user_id, virtuemart_vendor_id, user_is_vendor, perms, agreed, created_on, modified_on) '
					. ' VALUES(\'' . $metaUser->userid . '\', \'0\', \'0\', \'shopper\',\'1\',\'' . ( (int) gmdate('U') ) . '\', \'' . ( (int) gmdate('U') ) . '\')'
					;
			$db->setQuery( $query );
			$db->query();

			$query  = 'INSERT INTO #__virtuemart_userinfos'
					. ' (virtuemart_userinfo_id, virtuemart_user_id, address_type, last_name, first_name, middle_name, created_on, modified_on) '
					. ' VALUES(\'' . $inum . '\', \'' . $metaUser->userid . '\', \'BT\', \'' . $lastname . '\', \'' . $firstname . '\', \'' . $middlename . '\', \'' . ( (int) gmdate('U') ) . '\', \'' . ( (int) gmdate('U') ) . '\')'
					;
		} else {
			$query  = 'INSERT INTO #__vm_user_info'
					. ' (user_info_id, user_id, address_type, last_name, first_name, middle_name, user_email, cdate, mdate, perms, bank_account_type)'
					. ' VALUES(\'' . $inum . '\', \'' . $metaUser->userid . '\', \'BT\', \'' . $lastname . '\', \'' . $firstname . '\', \'' . $middlename . '\', \'' . $metaUser->cmsUser->email . '\', \'' . ( (int) gmdate('U') ) . '\', \'' . ( (int) gmdate('U') ) . '\', \'shopper\', \'Checking\')'
					;
		}

		$db->setQuery( $query );
		$db->query();

		// Create Shopper -ShopperGroup - Relationship
		if ( $this->isv2 ) {
			$query  = 'INSERT INTO #__virtuemart_vmuser_shoppergroups'
					. ' (virtuemart_user_id, virtuemart_shoppergroup_id)'
					. ' VALUES(\'' . $metaUser->userid . '\', \'' . $shoppergroup . '\')'
					;
		} else {
			$query  = 'INSERT INTO #__vm_shopper_vendor_xref'
					. ' (user_id, shopper_group_id)'
					. ' VALUES(\'' . $metaUser->userid . '\', \'' . $shoppergroup . '\')'
					;
		}

		$db->setQuery( $query );
		$db->query();
	}
}
?>

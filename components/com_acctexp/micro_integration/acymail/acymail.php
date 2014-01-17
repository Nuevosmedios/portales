<?php
/**
 * @version $Id: mi_acymail.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Kunena
 * @copyright Copyright (C) 2011 David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
defined('_JEXEC') OR defined( '_VALID_MOS' ) OR die( 'Direct Access to this location is not allowed.' );

class mi_acymail extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_ACYMAIL');
		$info['desc'] = JText::_('AEC_MI_DESC_ACYMAIL');
		$info['type'] = array( 'sharing.newsletter', 'vendor.acyba' );

		return $info;
	}

	function Settings()
	{
		$settings = array();

		if ( !$this->loadACY() ) {
			echo 'This module can not work without the ACY Mailing Component';

			return false;
		}

		$db = &JFactory::getDBO();
		$db->setQuery( 'SELECT * FROM #__acymailing_list' );

		$lists = $db->loadObjectList();

		$listslist = array();

		foreach ( $lists as $id => $row ) {
			$listslist[] = JHTML::_('select.option', $row->listid, $row->listid . ': ' . $row->name );
		}

		$settings['addlist']	= array( 'list' );
		$settings['removelist']	= array( 'list' );

		$settings = $this->autoduplicatesettings( $settings );

		foreach ( $settings as $k => $v ) {
			if ( isset( $this->settings[$k] ) ) {
				$settings['lists'][$k]	= JHTML::_( 'select.genericlist', $listslist, $k.'[]', 'size="4" multiple="multiple"', 'value', 'text', $this->settings[$k] );
			} else {
				$settings['lists'][$k]	= JHTML::_( 'select.genericlist', $listslist, $k.'[]', 'size="4" multiple="multiple"', 'value', 'text', '' );
			}
		}

		$xsettings = array();
		$xsettings['user_checkbox']		= array( 'toggle' );
		$xsettings['custominfo']		= array( 'inputD' );

		return array_merge( $xsettings, $settings );
	}

	function getMIform( $request )
	{
		$settings = array();

		if ( empty( $this->settings['user_checkbox'] ) ) {
			return $settings;
		}

		if ( !empty( $this->settings['custominfo'] ) ) {
			$settings['exp'] = array( 'p', "", $this->settings['custominfo'] );
		} else {
			$settings['exp'] = array( 'p', "", JText::_('MI_MI_ACAJOOM_DEFAULT_NOTICE') );
		}

		$settings['get_newsletter'] = array( 'checkbox', JText::_('MI_MI_ACYMAIL_NEWSLETTER_SIGNUP'), 'mi_'.$this->id.'_get_newsletter', 0 );

		return $settings;
	}

	function relayAction( $request )
	{
		if ( !$this->loadACY() ) {
			return null;
		}

		$new_allowed = false;

		if ( empty( $this->settings['user_checkbox'] ) ) {
			$new_allowed = true;
		} elseif ( !empty( $this->settings['user_checkbox'] ) && !empty( $request->params['get_newsletter'] ) ) {
			$new_allowed = true;
		}

		$config = acymailing::config();

		$subscriber = $this->getSubscriber( $config, $request, $new_allowed );

		if ( empty( $subscriber ) ) {
			return null;
		}

		if ( empty( $subscriber->confirmed ) && $config->get( 'require_confirmation',false ) ) {
			$statusAdd = 2;
		} else {
			$statusAdd = 1;
		}

		$lists = array();
		if ( !empty( $this->settings['addlist' . $request->area] ) && $new_allowed ) {
			foreach ( $this->settings['addlist' . $request->area] as $list ) {
				$lists[$list] = array( 'status' => $statusAdd );
			}
		}

		if ( !empty( $this->settings['removelist' . $request->area] ) ) {
			foreach ( $this->settings['removelist' . $request->area] as $list ) {
				$lists[$list] = array( 'status' => 0 );
			}
		}

		if ( !empty( $lists ) ) {
			$sub = acymailing::get('class.subscriber');

			$sub->saveSubscription( $subscriber->subid, $lists );
		}
	}

	function getSubscriber( $config, $request, $new_allowed )
	{
		$userClass = acymailing::get('class.subscriber');

		$subid = $userClass->subid( $request->metaUser->cmsUser->email );

		if ( empty( $subid ) && !$new_allowed ) {
			return false;
		} elseif ( empty( $subid ) ) {
			$joomUser = new stdClass();

			$joomUser->email = $request->metaUser->cmsUser->email;
			$joomUser->name = $request->metaUser->cmsUser->name;

			if ( empty( $request->metaUser->cmsUser->block ) ) {
				$joomUser->confirmed = 1;
			}

			$joomUser->enabled = 1;
			$joomUser->userid = $request->metaUser->userid;

			$userClass = acymailing::get('class.subscriber');

			$userClass->checkVisitor = false;
			$userClass->sendConf = false;

			$subid = $userClass->save( $joomUser );
		}

		$userClass = acymailing::get('class.subscriber');

		return $userClass->get( $subid );
	}

	function loadACY()
	{
		if ( !file_exists( rtrim( JPATH_ROOT, '/' ) . '/administrator/components/com_acymailing/helpers/list.php' ) ) {
			echo 'This module can not work without the ACY Mailing Component';

			return false;
		} else {
			if ( !class_exists( 'acymailing' ) ) {
				@include_once( rtrim( JPATH_ADMINISTRATOR, '/' ).'/components/com_acymailing/helpers/helper.php' );
			}

			return true;
		}

	}
}
?>

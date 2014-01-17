<?php
/**
 * @version $Id: mi_jnews.php 16 2007-07-01 12:07:07Z mic $
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - JNews
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_jnews
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_JNEWS');
		$info['desc'] = JText::_('AEC_MI_DESC_JNEWS');
		$info['type'] = array( 'sharing.newsletter', 'vendor.joobi' );

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`, `list_name`, `list_type`'
				. ' FROM #__jnews_lists'
				;
	 	$db->setQuery( $query );
	 	$lists = $db->loadObjectList();

		$li = array();
		if ( !empty( $lists ) ) {
			foreach( $lists as $list ) {
				$li[] = JHTML::_('select.option', $list->id, $list->list_name );
			}
		}

		if ( !isset( $this->settings['list'] ) ) {
			$this->settings['list'] = 0;
		}

		if ( !isset( $this->settings['list_exp'] ) ) {
			$this->settings['list_exp'] = 0;
		}

		$settings = array();
		$settings['lists']['list']		= JHTML::_('select.genericlist', $li, 'list', 'size="4"', 'value', 'text', $this->settings['list'] );
		$settings['lists']['list_exp']	= JHTML::_('select.genericlist', $li, 'list_exp', 'size="4"', 'value', 'text', $this->settings['list_exp'] );

		$settings['list']			= array( 'list' );
		$settings['list_exp']		= array( 'list' );
		$settings['user_checkbox']	= array( 'toggle' );
		$settings['custominfo']		= array( 'inputD' );

		return $settings;
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
			$settings['exp'] = array( 'p', "", JText::_('MI_MI_JNEWS_DEFAULT_NOTICE') );
		}

		$settings['get_newsletter'] = array( 'checkbox', JText::_('MI_MI_JNEWS_NEWSLETTER_SIGNUP'), 'mi_'.$this->id.'_get_newsletter', 0 );

		return $settings;
	}

	function expiration_action( $request )
	{
		$is_allowed = false;

		if ( empty( $this->settings['user_checkbox'] ) ) {
			$is_allowed = true;
		} elseif ( !empty( $this->settings['user_checkbox'] ) && !empty( $request->params['get_newsletter'] ) ) {
			$is_allowed = true;
		}

		$acauser = $this->getSubscriberID( $request->metaUser->userid );

		if ( !$acauser && $is_allowed ) {
			$this->createSubscriber( $request->metaUser->userid );
			$acauser = $this->getSubscriberID( $request->metaUser->userid );
		}

		if ( empty( $acauser ) ) {
			return null;
		}

		if ( !empty( $this->settings['list'] ) ) {
			$this->deleteFromList( $acauser, $this->settings['list'] );
		}

		if ( !empty( $this->settings['list_exp'] ) && $is_allowed ) {
			$this->addToList( $acauser, $this->settings['list_exp'] );
		}
	}

	function action( $request )
	{
		$is_allowed = false;

		if ( empty( $this->settings['user_checkbox'] ) ) {
			$is_allowed = true;
		} elseif ( !empty( $this->settings['user_checkbox'] ) && !empty( $request->params['get_newsletter'] ) ) {
			$is_allowed = true;
		}

		$acauser = $this->getSubscriberID( $request->metaUser->userid );

		if ( !$acauser ) {
			$this->createSubscriber( $request->metaUser->userid );
			$acauser = $this->getSubscriberID( $request->metaUser->userid );
		}

		if ( empty( $acauser ) ) {
			return null;
		}

		if ( !empty( $this->settings['list_exp'] ) ) {
			$this->deleteFromList( $acauser, $this->settings['list_exp'] );
		}

		if ( !empty( $this->settings['list'] ) && $is_allowed ) {
			$this->addToList( $acauser, $this->settings['list'] );
		}
	}

	function createSubscriber( $userid )
	{
		$db = &JFactory::getDBO();

		$user = new cmsUser();
		$user->load( $userid );

		$query  = 'INSERT INTO #__jnews_subscribers'
				. ' (user_id, name, email, receive_html, confirmed, blacklist, timezone, language_iso, subscribe_date, params)'
				. ' VALUES(\'' . $userid . '\', \'' . $user->name . '\', \'' . $user->email . '\', \'1\', \'1\', \'0\', \'00:00:00\', \'eng\', \'' . ( (int) gmdate('U') ) . '\', \'\' )'
				;
		$db->setQuery( $query );
		$db->query();
	}

	function hasList( $subscriber_id, $listid )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT `list_id`'
				. ' FROM #__jnews_listssubscribers'
				. ' WHERE `subscriber_id` = \'' . $subscriber_id . '\''
				. ' AND `list_id` = \'' . $listid . '\''
				. ' AND `unsubscribe` <> \'1\''
				;
		$db->setQuery( $query );
		if ( $db->loadResult() ) {
			return true;
		} else {
			return false;
		}
	}

	function hasListUnsub( $subscriber_id, $listid )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT `list_id`'
				. ' FROM #__jnews_listssubscribers'
				. ' WHERE `subscriber_id` = \'' . $subscriber_id . '\''
				. ' AND `list_id` = \'' . $listid . '\''
				. ' AND `unsubscribe` = \'1\''
				;
		$db->setQuery( $query );
		if ( $db->loadResult() ) {
			return true;
		} else {
			return false;
		}
	}

	function getSubscriberID( $userid )
	{
		$db = &JFactory::getDBO();
		$query = 'SELECT `id`'
				. ' FROM #__jnews_subscribers'
				. ' WHERE `user_id` = \'' . $userid . '\''
				;
		$db->setQuery( $query );
		return $db->loadResult();
	}

	function addToList( $subscriber_id, $list_id )
	{
		if ( !$this->hasList( $subscriber_id, $list_id ) ) {
			$db = &JFactory::getDBO();

			if ( $this->hasListUnsub( $subscriber_id, $list_id ) ) {
				$query = 'UPDATE #__jnews_listssubscribers'
						. ' SET `unsubscribe` = 0'
						. ' WHERE `subscriber_id` = \'' . $subscriber_id . '\''
						. ' AND `list_id` = \'' . $list_id . '\''
						;
				$db->setQuery( $query );
				$db->query();
			} else {
				$query  = 'INSERT INTO #__jnews_listssubscribers'
						. ' (list_id, subscriber_id, subdate)'
						. ' VALUES(\'' . $list_id . '\', \'' . $subscriber_id . '\', \'' . date( 'Y-m-d H:i:s',  ( (int) gmdate('U') ) ) . '\')'
						;
				$db->setQuery( $query );
				$db->query();
			}

			$query  = 'INSERT INTO #__jnews_queue'
					. ' (type, subscriber_id, list_id, mailing_id, issue_nb, send_date, suspend, delay, acc_level, published, params)'
					. ' VALUES(\'1\', \'' . $subscriber_id . '\', \'' . $list_id . '\', \'0\', \'0\', \'' . date( 'Y-m-d H:i:s',  ( (int) gmdate('U') ) ) . '\', \'0\', \'0\', \'0\', \'0\', \'\' )'
					;
			$db->setQuery( $query );
			$db->query();
		}

		return true;
	}

	function deleteFromList( $subscriber_id, $list_id )
	{	
		if ( $this->hasList( $subscriber_id, $list_id ) ) {
			$db = &JFactory::getDBO();

			$query = 'UPDATE #__jnews_listssubscribers'
					. ' SET `unsubscribe` = 1, `unsubdate` = ' . ( (int) gmdate('U') )
					. ' WHERE `subscriber_id` = \'' . $subscriber_id . '\''
					. ' AND `list_id` = \'' . $list_id . '\''
					;
			$db->setQuery( $query );
			$db->query();

			$query = 'DELETE FROM #__jnews_queue'
					. ' WHERE `subscriber_id` = \'' . $subscriber_id . '\''
					. ' AND `list_id` = \'' . $list_id . '\''
					;
			$db->setQuery( $query );
			$db->query();
		}
	}

}

?>

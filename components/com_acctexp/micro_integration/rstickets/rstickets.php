<?php
/**
 * @version $Id: mi_rstickets.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - RStickets
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_rstickets extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_RSTICKETS');
		$info['desc'] = JText::_('AEC_MI_DESC_RSTICKETS');
		$info['type'] = array( 'service.tickets', 'vendor.rsjoomla' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['userid']				= array( 'inputE' );
		$settings['email']				= array( 'inputE' );
		$settings['department']			= array( 'list' );

		$settings['subject']			= array( 'inputE' );
		$settings['text']				= array( 'inputD' );

		$settings['priority']			= array( 'list' );

		$rewriteswitches				= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		$this->loadRStickets();

		if ( !isset( $this->settings['department'] ) ) {
			$this->settings['department'] = 0;
		}

		if ( !isset( $this->settings['priority'] ) ) {
			$this->settings['priority'] = 0;
		}

		if ( !function_exists( 'rst_get_departments' ) ) {
			$settings['info']				= array( 'p', 'Notice', 'You need to have RStickets installed to use this MI!' );
		} else {
			$departments = rst_get_departments();

			$deps_list = array();
			foreach ( $departments as $dep ) {
				$deps_list[] = JHTML::_('select.option', $dep['DepartmentId'], $dep['DepartmentPrefix'] . ' - ' . $dep['DepartmentName'] );
			}

			$settings['lists']['department'] = JHTML::_( 'select.genericlist', $deps_list, 'department', 'size="1"', 'value', 'text', $this->settings['department'] );
		}

 		$priorities = array();
		$priorities[] = JHTML::_('select.option', "low", "low" );
		$priorities[] = JHTML::_('select.option', "normal", "normal" );
		$priorities[] = JHTML::_('select.option', "high", "high" );

		$settings['lists']['priority'] = JHTML::_( 'select.genericlist', $priorities, 'priority', 'size="1"', 'value', 'text', $this->settings['priority'] );

		return $settings;
	}

	function Defaults()
	{
		$defaults = array();
		$defaults['userid']	= "[[user_id]]";
		$defaults['email']	= "[[user_email]]";

		return $defaults;
	}

	function relayAction( $request )
	{
		if ( $request->action == 'action' ) {
			$this->loadRStickets();

			$text		= AECToolbox::rewriteEngineRQ( $this->settings['text'], $request );
			$subject	= AECToolbox::rewriteEngineRQ( $this->settings['subject'], $request );

			$userid		= AECToolbox::rewriteEngineRQ( $this->settings['userid'], $request );
			$email		= AECToolbox::rewriteEngineRQ( $this->settings['email'], $request );

			$r = rst_add_ticket( $this->settings['department'], $subject, $text, $this->settings['priority'], $userid, $email, 0, array(), true );
		}

		return true;
	}

	function admin_form( $request )
	{
		$db = &JFactory::getDBO();

		$this->loadRStickets();

		$settings = array();

		$query = 'SELECT `id`'
				. ' FROM #__menu'
				. ' WHERE LOWER( `link` ) = \'index.php?option=com_rstickets\''
				. ' AND published = \'1\''
				. ' ORDER BY `ordering` ASC'
				;
		$db->setQuery( $query );
		$mid = $db->loadResult();

		$history_table = '<table style="width:950px;">';
		$history_table .= '<tr><th>Ticket</th><th>Status</th><th>Assigned To</th><th>Replies</th><th>Added</th><th>Used</th><th>Details</th></tr>';

		$tickets = rst_get_tickets(	'', '', '', '', '', '',
									array(), array('open','on-hold','closed'), array('low','normal','high'),
									1, 'TicketTime', 'DESC', '', '', '',
									$request->metaUser->cmsUser->username, '', $request->metaUser->cmsUser->email );

		if ( !empty( $tickets['tickets'] ) ) {
			$i = 0;
			foreach ( $tickets['tickets'] as $id => $entry ) {
				if ( $i > 20 ) {
					continue;
				}

				$history_table .= '<tr>'
									. '<td><a href="' . JRoute::_(JURI::root().'index.php?option=com_rstickets&page=ticket&id=' . $entry['TicketId'] . '&Itemid=' . $mid ) . '">' . $entry['TicketCode'] . '</a> - ' . $entry['TicketSubject'] . '</td>'
									. '<td>' . $entry['TicketStatus'] . '</td>'
									. '<td>' . $entry['StaffFullname'] . '</td>'
									. '<td>' . $entry['TicketReplies'] . '</td>'
									. '</tr>';

				$i++;
			}
		}

		$history_table .= '</table>';

		$settings['history']	= array( 'fieldset', 'Recent Tickets', $history_table );

		return $settings;
	}

	function loadRStickets()
	{
		if ( file_exists( JPATH_SITE . '/components/com_rstickets/config.php' ) ) {
			require_once( JPATH_SITE . '/components/com_rstickets/config.php' );
			require_once( JPATH_SITE . '/components/com_rstickets/functions.php' );
		}
	}
}
?>

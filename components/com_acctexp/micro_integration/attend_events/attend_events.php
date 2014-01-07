<?php
/**
 * @version $Id: mi_attend_events.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Attend Events
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_attend_events
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_ATTEND_EVENTS');
		$info['desc'] = JText::_('AEC_MI_DESC_ATTEND_EVENTS');
		$info['type'] = array( 'calendar_events.events' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		return $settings;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		include_once( JPATH_SITE . '/components/com_attend_events/attend_events.class.php' );

		$db->setQuery("SELECT transaction_id FROM #__events_transactions WHERE ( registration_id = '" . $this->settings['registration_id'] . "' )");
		$transaction_id = $db->loadResult();

		// mark ae invoice as cleared
		$transaction = new comAETransaction();
		$transaction->load( $transaction_id );
		$transaction->bind( $_POST );
		$transaction->gateway = 'Cybermut';
		$transaction->check();
		$transaction->store();

		return true;
	}
}
?>

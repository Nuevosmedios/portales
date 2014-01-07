<?php
/**
 * @version $Id: mi_age_restriction.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Age Restriction
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_age_restriction extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_AGE_RESTRICTION_NAME');
		$info['desc'] = JText::_('AEC_MI_AGE_RESTRICTION_DESC');
		$info['type'] = array( 'aec.checkout', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
        $settings = array();
		$settings['min_age']			= array( 'inputA' );
		$settings['max_age']			= array( 'inputA' );
		$settings['restrict_calendar']	= array( 'toggle' );

		return $settings;
	}

	function getMIform( $request )
	{
		$settings = array();

		$settings['birthday'] = array( 'inputC', JText::_('MI_MI_AGE_RESTRICTION_USERSELECT_BIRTHDAY_NAME'), JText::_('MI_MI_AGE_RESTRICTION_USERSELECT_BIRTHDAY_DESC') );

		return $settings;
	}

	function verifyMIform( $request )
	{
		$return = array();

		if ( empty( $request->params['birthday'] ) ) {
			$return['error'] = "Please fill in your date of birth";
			return $return;
		}

		$age = $this->getAge( $request->params['birthday'] );

		if ( empty( $age ) ) {
			$return['error'] = "Please fill in your date of birth";
			return $return;
		}

		if ( !empty( $this->settings['min_age'] ) ) {
			if ( $age < $this->settings['min_age'] ) {
				$return['error'] = "You must be at least " . $this->settings['min_age'] . " years old!";
				return $return;
			}
		}

		if ( !empty( $this->settings['max_age'] ) ) {
			if ( $age > $this->settings['max_age'] ) {
				$return['error'] = "You cannot purchase this if you are older than " . $this->settings['max_age'] . " years!";
				return $return;
			}
		}

		return $return;
	}

	function relayAction( $request )
	{
		if ( ( $request->action == 'action' ) && !empty( $this->settings['max_age'] ) ) {
			$age = $this->getAge( $request->params['birthday'] );

			$due_date = strtotime( "+" . $this->settings['max_age'] . " years", strtotime( $request->params['birthday'] ) );

			$event_id = $this->issueUniqueEvent( $request, 'BirthdayExpiration', date( 'Y-m-d H:i:s', $due_date ) );
		}

		return true;
	}

	function admin_form( $request )
	{
		return $this->getMIform( $request );
	}

	function admin_form_save( $request )
	{
		if ( !empty( $this->settings['max_age'] ) ) {
			$age = $this->getAge( $request->params['birthday'] );

			$due_date = strtotime( "+" . $this->settings['max_age'] . " years", strtotime( $request->params['birthday'] ) );

			$event_id = $this->redateUniqueEvent( $request, 'BirthdayExpiration', date( 'Y-m-d H:i:s', $due_date ) );
		}

		return true;
	}

	function aecEventHookBirthdayExpiration( $request )
	{
		if ( !empty( $request->metaUser->focusSubscription->id ) ) {
			return $request->metaUser->focusSubscription->expire();
		}

		return null;
	}

	function getAge( $bd )
	{
		$app = JFactory::getApplication();

		return ( ( (int) gmdate('U') ) - strtotime( $bd ) ) / 31536000;
	}
}
?>

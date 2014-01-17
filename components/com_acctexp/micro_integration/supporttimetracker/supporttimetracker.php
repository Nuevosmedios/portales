<?php
/**
 * @version $Id: mi_supporttimetracker.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Support Time tracker
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_supporttimetracker extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = 'Support Time Tracker';
		$info['desc'] = 'Simple time tracker that can be used to keep track of support hours';
		$info['type'] = array( 'aec.membership', 'service.tickets', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['add_minutes']		= array( 'inputC', 'Add Support Minutes', 'Add this amount of minutes to the user account' );

		return $settings;
	}

	function Defaults()
	{
		$defaults = array();
		$defaults['userid']	= "[[user_id]]";
		$defaults['email']	= "[[user_email]]";

		return $defaults;
	}

	function profile_info( $request )
	{
		$minutes = $this->getSupportMinutes( $request->metaUser );

		if ( empty( $minutes ) ) {
			$message = "You don't have any Support Time left for this account";
		} else {
			$hrs = (int) ( $minutes / 60 );
			$min = $minutes % 60;

			$message = "You have <strong>" . $hrs . " hour" . ( ( $hrs == 1 ) ? '' : 's' ) . ' and ' . $min . " minute" . ( ( $min == 1 ) ? '' : 's' ) . '</strong> Support Time left for this account.';
		}

		return $message;
	}

	function relayAction( $request )
	{
		if ( $request->action == 'action' ) {
			if ( !empty( $this->settings['add_minutes'] ) ) {
				$details = "The User has added support time to the account.";

				if ( !empty( $request->plan->id ) ) {
					$details .= " Plan: " . $request->plan->id;
				}

				$this->updateSupportMinutes( $request->metaUser, $this->settings['add_minutes'], 0, $details );
			}
		} elseif ( $request->action == 'refund' ) {
			$this->updateSupportMinutes( $request->metaUser, 0, $this->settings['add_minutes'], 'Refund' );
		}

		return true;
	}

	function admin_form( $request )
	{
		$history = $this->getSupportHistory( $request->metaUser );

		$settings = array();

		$settings['log_minutes']	= array( 'inputC', 'Log Minutes', 'The amount of minutes you want to log for this user account. You can also supply a negative value to correct a mistake' );
		$settings['remove_last']	= array( 'toggle', 'Remove Last', 'If you want to correct your last log, set this option to Yes, provide details below and save.' );
		$settings['details']		= array( 'inputD', 'Details', 'Give details on the update' );

		if ( empty( $history ) ) {
			$settings['history']	= array( 'fieldset', 'History', 'There is no history for this account' );
		} else {
			$app = JFactory::getApplication();

			$history_table = '<table style="width:950px;">';
			$history_table .= '<tr><th>ID</th><th>Date</th><th>Minutes</th><th>Total Minutes Used</th><th>Added</th><th>Used</th><th>Details</th></tr>';

			$history = array_reverse( $history );

			$i = 0;
			foreach ( $history as $id => $entry ) {
				if ( $i > 20 ) {
					continue;
				}

				$history_table .= '<tr>'
									. '<td>' . $id . '</td>'
									. '<td>' . date( 'Y-m-d H:i:s', $entry['tstamp'] ) . '</td>'
									. '<td>' . ( $entry['support_minutes'] ? $entry['support_minutes'] : '0' ). '</td>'
									. '<td>' . ( $entry['support_minutes_used'] ? $entry['support_minutes_used'] : '- - -' ) . '</td>'
									. '<td>' . $entry['minutes_added'] . '</td>'
									. '<td>' . ( $entry['minutes_used'] ? $entry['minutes_used'] : '- - -' ) . '</td>'
									. '<td>' . $entry['details'] . '</td>'
									. '</tr>';

				$i++;
			}

			$history_table .= '</table>';

			$settings['history']	= array( 'fieldset', 'History', $history_table );
		}

		return $settings;
	}

	function admin_form_save( $request )
	{
		if ( !empty( $request->params['remove_last'] ) ) {
			$history = $this->getSupportHistory( $request->metaUser );

			$max = count( $history ) - 1;

			if ( empty( $request->params['details'] ) ) {
				$request->params['details'] = "Removing last entry";
			}

			if ( $history[$max]['minutes_added'] ) {
				$this->updateSupportMinutes( $request->metaUser, 0, $history[$max]['minutes_added'], $request->params['details'] );
			} elseif ( $history[$max]['minutes_used'] ) {
				$this->updateSupportMinutes( $request->metaUser, $history[$max]['minutes_used'], 0, $request->params['details'] );
			}

			$request->params['log_minutes']	= 0;
			$request->params['details']		= '';
		} elseif ( !empty( $request->params['log_minutes'] ) ) {
			$this->updateSupportMinutes( $request->metaUser, 0, $request->params['log_minutes'], $request->params['details'] );

			$request->params['log_minutes']	= 0;
			$request->params['details']		= '';
		}
	}

	function getSupportHistory( $metaUser )
	{
		$uparams = $metaUser->meta->getCustomParams();

		if ( !empty( $uparams['support_minutes_history'] ) ) {
			if ( is_array( $uparams['support_minutes_history'] ) ) {
				return $uparams['support_minutes_history'];
			}
		}

		return array();
	}

	function getSupportMinutes( $metaUser )
	{
		$uparams = $metaUser->meta->getCustomParams();

		if ( isset( $uparams['support_minutes'] ) ) {
			if ( !empty( $uparams['support_minutes_used'] ) ) {
				return $uparams['support_minutes'] - $uparams['support_minutes_used'];
			} else {
				return $uparams['support_minutes'];
			}
		}

		return 0;
	}

	function updateSupportMinutes( $metaUser, $minutes, $use_minutes, $details )
	{
		$user = &JFactory::getUser();

		$uparams = $metaUser->meta->getCustomParams();

		if ( !empty( $uparams['support_minutes_history'] ) ) {
			$history = $uparams['support_minutes_history'];
		} else {
			$history = array();
		}

		if ( !empty( $minutes ) && !empty( $uparams['support_minutes'] ) ) {
			$uparams['support_minutes'] = $uparams['support_minutes'] + $minutes;
		} elseif ( !empty( $minutes ) ) {
			$uparams['support_minutes'] = $minutes;
		}

		if ( !empty( $use_minutes ) && !empty( $uparams['support_minutes_used'] ) ) {
			$uparams['support_minutes_used'] = $uparams['support_minutes_used'] + $use_minutes;
		} elseif ( !empty( $use_minutes ) ) {
			$uparams['support_minutes_used'] = $use_minutes;
		}

		if ( !empty( $user->id ) && ( $user->id != $metaUser->userid ) ) {
			$userid = $user->id;
		} else {
			$userid = $metaUser->id;
		}

		$history = $this->getSupportHistory( $metaUser );

		$history[]	= array(	'tstamp'				=> ( (int) gmdate('U') ),
								'userid'				=> $userid,
								'support_minutes'		=> $uparams['support_minutes'],
								'minutes_added'			=> $minutes,
								'support_minutes_used'	=> $uparams['support_minutes_used'],
								'minutes_used'			=> $use_minutes,
								'details'				=> $details
							);

		$params		= array(	'support_minutes_history'	=> $history,
								'support_minutes_used'		=> $uparams['support_minutes_used'],
								'support_minutes'			=> $uparams['support_minutes']
							);

		$metaUser->meta->setCustomParams( $params );
		$metaUser->meta->storeload();
	}
}
?>

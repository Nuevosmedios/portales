<?php
/**
 * @version $Id: tool_supporthours.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Toolbox - Support Hours
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class tool_supporthours
{
	function Info()
	{
		$info = array();
		$info['name'] = "Support Hours";
		$info['desc'] = "Counts and collates logged support hours (from the Support Time MI) for a given time period.";

		return $info;
	}

	function Settings()
	{
		// Compute sensible timeframe for the default
		$day = date('d');

		if ( ( $day < 7 ) || ( $day > 22 ) ) {
			// Show second week
			if ( $day > 22 ) {
				$start	= strtotime( date( 'Y-m' ) . '-15 00:00:00' );
				$end	= strtotime( date( 'Y-m-t' ) );
			} else {
				// This should probably be replaced by something using straight strtotime
				$year = date( 'Y' );
				
				$prevmonth = date( 'm' ) - 1;

				if ( $prevmonth <= 0 ) {
					$year = $year - 1;

					$prevmonth = 12;
				}

				$lastday = date( 't', strtotime( $year . '-' . $prevmonth . '-15 00:00:00' ) );

				$start	= strtotime( $year . '-' . $prevmonth . '-15 00:00:00' );
				$end	= strtotime( $year . '-' . $prevmonth . '-' . $lastday . ' 23:59:59' );
			}
		} else {
			// Show first week
			$start	= strtotime( date( 'Y-m' ) . '-1 00:00:00' );
			$end	= strtotime( date( 'Y-m' ) . '-14 23:59:59' );
		}

		$settings = array();
		$settings['start_date']	= array( 'list_date', 'Start Date', '', date( 'Y-m-d', $start ) );
		$settings['end_date']	= array( 'list_date', 'End Date', '', date( 'Y-m-d', $end ) );

		return $settings;
	}

	function Action()
	{
		if ( empty( $_POST['start_date'] ) ) {
			return null;
		}

		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_microintegrations'
				. ' WHERE `class_name` LIKE \'%mi_supporttimetracker%\''
				;
		$db->setQuery( $query );
		$mis = xJ::getDBArray( $db );

		$planlist = array();
		foreach ( $mis as $mi ) {
			$plans = MicroIntegrationHandler::getPlansbyMI( $mi );

			$planlist = array_merge( $planlist, $plans );
		}

		$planlist = array_unique( $planlist );

		$userlist = array();
		foreach ( $planlist as $planid ) {
			$users = SubscriptionPlanHandler::getPlanUserlist( $planid );

			$userlist = array_merge( $userlist, $users );
		}

		$userlist = array_unique( $userlist );

		$start_timeframe = strtotime( $_POST['start_date'] . ' 00:00:00' );

		if ( !empty( $_POST['end_date'] ) ) {
			$end_timeframe = strtotime( $_POST['end_date'] . ' 23:59:59' );
		} else {
			$end_timeframe = ( (int) gmdate('U') );
		}

		$unused = $used = 0;

		$minordebt = 0;
		$minordebtthresh = 0;
		$minordebtth = 0;
		$minordebtlist = array();
		$majordebt = 0;
		$majordebtlist = array();

		$historylist = array();
		foreach ( $userlist as $userid ) {
			$db->setQuery(
				'SELECT a.id, a.name, a.username, b.custom_params'
				. ' FROM #__users AS a'
				. ' LEFT JOIN `#__acctexp_metauser` AS b ON a.id = b.userid'
				. ' WHERE a.id = ' . $userid
				. ' AND b.custom_params <> ""'
			);

			$user = $db->loadObject();

			if ( empty( $user->id ) ) {
				continue;
			}

			$uparams = unserialize( base64_decode( $user->custom_params ) );

			if ( !empty( $uparams['support_minutes_history'] ) ) {
				if ( is_array( $uparams['support_minutes_history'] ) ) {
					foreach( $uparams['support_minutes_history'] as $history ) {
						if ( ( $history['tstamp'] > $start_timeframe ) && ( $history['tstamp'] <= $end_timeframe ) ) {
							if ( !empty( $history['userid'] ) && $history['minutes_used'] ) {
								$add = array();
								$add['userid'] = $user->id;
								$add['name'] = $user->name;
								$add['username'] = $user->username;

								$historylist[$history['userid']][] = array_merge( $history, $add );
							}
						}
					}
				}
			}

			if ( !empty( $uparams['support_minutes_used'] ) ) {
				$utotal = $uparams['support_minutes'] - $uparams['support_minutes_used'];
			} else {
				$utotal = $uparams['support_minutes'];
			}

			if ( $utotal < 0 ) {
				if ( $utotal < -30 ) {
					$majordebtlist[] = array( 'userid' => $user->id, 'username' => $user->username, 'debt' => $utotal );
					$majordebt += $utotal;
				} elseif ( $utotal < -14 ) {
					$minordebtlist[] = array( 'userid' => $user->id, 'username' => $user->username, 'debt' => $utotal );
					$minordebt += $utotal;
				} else {
					$minordebtthresh++;

					$minordebtth += $utotal;
					$minordebt += $utotal;
				}
			}

			if ( !empty( $uparams['support_minutes'] ) ) {
				$unused += $uparams['support_minutes'] - $uparams['support_minutes_used'];
			}

			if ( !empty( $uparams['support_minutes_used'] ) ) {
				$used += $uparams['support_minutes_used'];
			}
		}

		$return = "";

		foreach ( $historylist as $userid => $history_list ) {
			if ( empty( $history_list ) ) {
				continue;
			}

			$total_minutes = 0;

			$metaUser = new metaUser( $userid );

			$return .= '<h1>' . $metaUser->cmsUser->name . '</h1>';
			$return .= '<table class="adminlist">';
			$return .= '<tr><th>Date</th><th>Username</th><th>Time Used</th><th>Details</th></tr>';

			$history_list = $this->historySort( $history_list );

			foreach ( $history_list as $history ) {
				$userlink = '<a href="';
				$userlink .= JURI::base() . 'index.php?option=com_acctexp&amp;task=edit&amp;userid=' . $history['userid'];
				$userlink .= '">';
				$userlink .= $history['name'] . ' (' . $history['username'] . ')';
				$userlink .= '</a>';

				$return .= '<tr>';
				$return .= '<td>' . date( 'Y-m-d H:i:s', $history['tstamp'] ) . '</td>';
				$return .= '<td>' . $userlink . '</td>';
				$return .= '<td>' . $history['minutes_used'] . '</td>';
				$return .= '<td>' . $history['details'] . '</td>';
				$return .= '</tr>';

				$total_minutes += $history['minutes_used'];
			}

			$return .= '<tr><td><strong>TOTAL</strong></td><td></td><td><strong>' . $total_minutes . '</strong> (' . round( $total_minutes/60, 2 ) . ' hours)</td><td></td></tr>';

			$return .= '</table><br /><br />';
		}

		$return .= '<p>Total used minutes: <strong>' . $used . '</strong> (' . round( $used/60, 2 ) . ' hours)</p>';
		$return .= '<p>Total unused minutes: ' . $unused . ' (' . round( $unused/60, 2 ) . ' hours)</p>';

		$return .= '<p>Total minor debt: ' . $minordebt . ' (' . round( $minordebt/60, 2 ) . ' hours)</p>';

		$minordebtlist = $this->debtSort( $minordebtlist );

		foreach ( $minordebtlist as  $user ) {
			$return .= '<a href="'. JURI::base() . 'index.php?option=com_acctexp&amp;task=editMembership&amp;userid=' . $user['userid'] . '">' . $user['username'] . ' (' . $user['debt'] . ' minutes)</a> ';
		}

		$return .= '<p>' . $minordebtthresh . ' Users below the -15 minute threshhold, total debt: ' . $minordebtth . ' (' . round( $majordebt/60, 2 ) . ' hours)</p>';

		$return .= '<p>Total major debt: ' . $majordebt . ' (' . round( $majordebt/60, 2 ) . ' hours)</p>';

		$majordebtlist = $this->debtSort( $majordebtlist );

		foreach ( $majordebtlist as  $user ) {
			$return .= '<a href="'. JURI::base() . 'index.php?option=com_acctexp&amp;task=editMembership&amp;userid=' . $user['userid'] . '">' . $user['username'] . ' (' . $user['debt'] . ' minutes)</a> ';
		}

		return $return;
	}

	function historySort( $array )
	{
		// Bastardized Quicksort
		if ( !isset( $array[2] ) ) {
			return $array;
		}

		$piv = $array[0];
		$x = $y = array();
		$len = count( $array );
		$i = 1;

		while ( $i < $len ) {
			if ( $array[$i]['tstamp'] < $piv['tstamp'] ) {
				$x[] = $array[$i];
			} else {
				$y[] = $array[$i];
			}
			++$i;
		}

		return array_merge( tool_supporthours::historySort($x), array($piv), tool_supporthours::historySort($y) );
	}

	function debtSort( $array )
	{
		// Bastardized Quicksort
		if ( !isset( $array[2] ) ) {
			return $array;
		}

		$piv = $array[0];
		$x = $y = array();
		$len = count( $array );
		$i = 1;

		while ( $i < $len ) {
			if ( $array[$i]['debt'] < $piv['debt'] ) {
				$x[] = $array[$i];
			} else {
				$y[] = $array[$i];
			}
			++$i;
		}

		return array_merge( tool_supporthours::debtSort($x), array($piv), tool_supporthours::debtSort($y) );
	}
}
?>

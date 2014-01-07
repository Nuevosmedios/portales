<?php
/**
 * @version $Id: tool_minireport.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Toolbox - Mini Report
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class tool_minireport
{
	function Info()
	{
		$info = array();
		$info['name'] = "Mini Report";
		$info['desc'] = "A quick overview on sales and revenue for a given timeframe.";

		return $info;
	}

	function Settings()
	{
		$monthago = ( (int) gmdate('U') ) - ( 60*60*24 * 31 );

		$settings = array();
		$settings['start_date']	= array( 'list_date', 'Start Date', '', date( 'Y-m-d', $monthago ) );
		$settings['end_date']	= array( 'list_date', 'End Date', '', date( 'Y-m-d' ) );

		return $settings;
	}

	function Action()
	{
		if ( empty( $_POST['start_date'] ) ) {
			return null;
		}

		$db = &JFactory::getDBO();

		$start_timeframe = $_POST['start_date'] . ' 00:00:00';

		if ( empty( $end ) ) {
			$end = date( 'Y-m-d', ( (int) gmdate('U') ) );
		}

		$end_timeframe = $end . ' 23:59:59';

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_log_history'
				. ' WHERE transaction_date >= \'' . $start_timeframe . '\''
				. ' AND transaction_date <= \'' . $end_timeframe . '\''
				. ' ORDER BY transaction_date ASC'
				;
		$db->setQuery( $query );
		$entries = xJ::getDBArray( $db );

		if ( empty( $entries ) ) {
			return "nothing to list";
		}

		$historylist = array();
		$groups = array();
		foreach ( $entries as $id ) {
			$entry = new logHistory();
			$entry->load( $id );

			$refund = false;
			if ( is_array( $entry->response ) ) {
				$filter = array( 'new_case', 'subscr_signup', 'paymentreview', 'subscr_eot', 'subscr_failed', 'subscr_cancel', 'Pending', 'Denied' );

				$refund = false;
				foreach ( $entry->response as $v ) {
					if ( in_array( $v, $filter ) ) {
						continue 2;
					} elseif ( ( $v == 'refund' ) || ( $v == 'Reversed' ) || ( $v == 'Refunded' ) ) {
						$refund = true;
					}
				}
			}

			$date = date( 'Y-m-d', strtotime( $entry->transaction_date ) );

			$pgroups = ItemGroupHandler::parentGroups( $entry->plan_id );

			if ( !in_array( $pgroups[0], $groups ) ) {
				$groups[] = $pgroups[0];
			}

			if ( !isset( $historylist[$date] ) ) {
				$historylist[$date] = array( 'amount' => null, 'groups' => null );
			}

			if ( $refund ) {
				$historylist[$date]['amount'] -= $entry->amount;
				$historylist[$date]['groups'][$pgroups[0]]--;
			} else {
				$historylist[$date]['amount'] += $entry->amount;
				$historylist[$date]['groups'][$pgroups[0]]++;
			}
		}

		foreach ( $historylist as $date => $entry ) {
			ksort( $historylist[$date]['groups'] );
		}

		$return = "";

		$return .= '<table style="background-color: fff; width: 30%; margin: 0 auto; text-align: center !important; font-size: 180%;">';

		$groupnames = array();
		foreach ( $groups as $group ) {
			$groupnames[$group] = ItemGroupHandler::groupName( $group );
		}

		$incomplete = false;
		foreach ( $historylist as $date => $history ) {
			if ( date( 'D', strtotime( $date ) ) == 'Mon' ) {
				$week = array();
			} elseif ( !isset( $week ) ) {
				$week = array();

				$incomplete = true;
			}

			$return .= '<tr style="border-bottom: 2px solid #999 !important; height: 2em;">';

			$return .= '<td title="Date" style="text-align: left !important; color: #aaa;">' . $date . '</td>';
			$return .= '<td style="width: 5em;">&nbsp;</td>';

			foreach ( $groups as $group ) {
				if ( empty( $history['groups'][$group] ) ) {
					$count = 0;
				} else {
					$count = $history['groups'][$group];
				}

				$return .= '<td title="' . $groupnames[$group] . '" style="font-weight: bold; width: 5em;">' . $count . '</td>';

				if ( isset( $week ) ) {
					$week['groups'][$group] += $count;
				}
			}

			if ( isset( $week ) ) {
				$week['amount'] += $history['amount'];
			}

			$return .= '<td style="width: 5em;">&nbsp;</td>';
			$return .= '<td title="Amount" style="text-align: right !important; color: #608919;">' . AECToolbox::correctAmount( $history['amount'] ) . '</td>';
			$return .= '</tr>';

			$return .= '<tr style="height: 1px; background-color: #999;">';
			$return .= '<td colspan="' . ( count($groups) + 4 ) . '"></td>';
			$return .= '</tr>';

			$closer = 0;

			if ( date( 'D', strtotime( $date ) ) == 'Sun' ) {
				$return .= '<tr ' . ( $incomplete ? 'title="Incomplete!"' : '' ) . 'style="border-bottom: 2px solid #999 !important; height: 2em; background-color: #ddd;">';

				$return .= '<td style="text-align: left !important; color: #aaa;">' . ( $incomplete ? '(Week)' : 'Week' ) . '</td>';
				$return .= '<td style="width: 5em;">&nbsp;</td>';

				foreach ( $groups as $group ) {
					if ( empty( $week['groups'][$group] ) ) {
						$count = 0;
					} else {
						$count = $week['groups'][$group];
					}

					if ( $incomplete ) {
						$return .= '<td title="' . $groupnames[$group] . '" style="font-weight: bold; width: 5em;">(' . $count . ')</td>';
					} else {
						$return .= '<td title="' . $groupnames[$group] . '" style="font-weight: bold; width: 5em;">' . $count . '</td>';
					}
				}

				$return .= '<td style="width: 5em;">&nbsp;</td>';

				if ( $incomplete ) {
					$return .= '<td title="Amount" style="text-align: right !important; color: #608919;">(' . AECToolbox::correctAmount( $week['amount'] ) . ')</td>';
				} else {
					$return .= '<td title="Amount" style="text-align: right !important; color: #608919;">' . AECToolbox::correctAmount( $week['amount'] ) . '</td>';
				}

				$return .= '</tr>';

				$return .= '<tr style="height: 1px; background-color: #999;">';
				$return .= '<td colspan="' . ( count($groups) + 4 ) . '"></td>';
				$return .= '</tr>';

				$closer = 1;

				$incomplete = false;
			}

		}

		if ( !$closer ) {
			$return .= '<tr style="border-bottom: 2px solid #999 !important; height: 2em; background-color: #ddd;">';

			$return .= '<td title="Date" style="text-align: left !important; color: #aaa;">(Week)</td>';
			$return .= '<td style="width: 5em;">&nbsp;</td>';

			foreach ( $groups as $group ) {
				if ( empty( $week['groups'][$group] ) ) {
					$count = 0;
				} else {
					$count = $week['groups'][$group];
				}

				$return .= '<td title="' . $groupnames[$group] . '" style="font-weight: bold; width: 5em;">' . $count . '</td>';
			}

			$return .= '<td style="width: 5em;">&nbsp;</td>';
			$return .= '<td title="Amount" style="text-align: right !important; color: #608919;">' . AECToolbox::correctAmount( $week['amount'] ) . '</td>';
			$return .= '</tr>';

			$return .= '<tr style="height: 1px; background-color: #999;">';
			$return .= '<td colspan="' . ( count($groups) + 4 ) . '"></td>';
			$return .= '</tr>';

			$closer = 1;
		}

		$return .= '</table><br /><br />';

		return $return;
	}

}
?>

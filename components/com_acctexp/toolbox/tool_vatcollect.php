<?php
/**
 * @version $Id: tool_vatcollect.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Toolbox - VAT Collect
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class tool_vatcollect
{
	function Info()
	{
		$info = array();
		$info['name'] = "VAT Tax Report";
		$info['desc'] = "If you're using the Tax MI in VAT mode, this tool will collect the logged taxes into a proper monthly report.";

		return $info;
	}

	function Settings()
	{
		// Always show full month
		$start	= strtotime( date( 'Y-m' ) . '-1 00:00:00' );
		$end	= strtotime( date( 'Y-m-t' ) . ' 23:59:59' );

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

		$start_timeframe = $_POST['start_date'] . ' 00:00:00';

		if ( !empty( $_POST['end_date'] ) ) {
			$end_timeframe = $_POST['end_date'] . ' 23:59:59';
		} else {
			$end_timeframe = date( 'Y-m-d', ( (int) gmdate('U') ) );
		}

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
				$filter = array( 'subscr_signup', 'paymentreview', 'subscr_eot', 'subscr_failed', 'subscr_cancel' );

				$refund = false;
				foreach ( $entry->response as $v ) {
					if ( in_array( $v, $filter ) ) {
						continue 2;
					} elseif ( $v == 'refund' ) {
						$refund = true;
					}
				}
			}

			$date = date( 'Y-m-d', strtotime( $entry->transaction_date ) );

			$iFactory = new InvoiceFactory( $entry->user_id, null, null, null, null, null, false, true );

			if ( $iFactory->userid != $entry->user_id ) {
				continue;
			}

			$iFactory->loadMetaUser();
			$iFactory->touchInvoice( 'com_acctexp', $entry->invoice_number, false, true );

			if ( $iFactory->invoice_number != $entry->invoice_number ) {
				continue;
			}

			$iFactory->puffer( 'com_acctexp' );

			$iFactory->loadItems();

			$iFactory->loadItemTotal();

			if ( isset( $iFactory->items->total ) ) {
				$amount = $iFactory->items->total->cost['amount'];
			} else {
				continue;
			}

			$tax = 0;
			foreach ( $iFactory->items->tax as $item ) {
				$tax += $item['cost'];
			}

			if ( $refund ) {
				$historylist[$date]['amount'] -= $amount;

				if ( $tax ) {
					$historylist[$date]['taxed'] -= $amount;
					$historylist[$date]['tax'] -= $tax;
				} else {
					$historylist[$date]['untaxed'] -= $amount;
				}
			} else {
				$historylist[$date]['amount'] += $amount;
				
				if ( $tax ) {
					$historylist[$date]['taxed'] += $amount;
					$historylist[$date]['tax'] += $tax;
				} else {
					$historylist[$date]['untaxed'] += $amount;
				}
			}
		}
		$return = "";

		$return .= '<table style="background-color: fff; width: 30%; margin: 0 auto; text-align: center !important; font-size: 180%;">';

		foreach ( $historylist as $date => $history ) {
			if ( date( 'j', strtotime( $date ) ) == 1 ) {
				$month = array();
			}

			$return .= '<tr style="border-bottom: 2px solid #999 !important; height: 2em;">';

			$return .= '<td title="Date" style="text-align: left !important; color: #aaa;">' . $date . '</td>';
			$return .= '<td style="width: 5em;">&nbsp;</td>';

			$return .= '<td title="Non-Taxed" style="font-weight: bold; width: 5em;">' . AECToolbox::correctAmount( $history['untaxed'] ) . '</td>';

			if ( !empty( $history['taxed'] ) ) {
				$return .= '<td style="width: 5em;">+</td>';
				$return .= '<td title="Taxed including Tax" style="font-weight: bold; width: 5em;">' . AECToolbox::correctAmount( $history['taxed'] + $history['tax'] ) . '</td>';
				$return .= '<td title="Taxed" style="font-weight: bold; width: 5em; color: #aaa;">(' . AECToolbox::correctAmount( $history['taxed'] ) . '</td>';
				$return .= '<td style="width: 5em; color: #aaa;">+</td>';
				$return .= '<td title="Tax" style="font-weight: bold; width: 5em; color: #aaa;">' . AECToolbox::correctAmount( $history['tax'] ) . ')</td>';
			} else {
				$return .= '<td colspan="5"></td>';
			}

			$return .= '<td style="width: 5em;">=</td>';

			$return .= '<td style="width: 5em;">&nbsp;</td>';
			$return .= '<td title="Grand Total" style="text-align: right !important; color: #608919;">' . AECToolbox::correctAmount( $history['amount'] + $history['tax'] ) . '</td>';
			$return .= '</tr>';

			$return .= '<tr style="height: 1px; background-color: #999;">';
			$return .= '<td colspan="11"></td>';
			$return .= '</tr>';

			if ( isset( $month ) ) {
				$month['amount'] += $history['amount'];
				$month['tax'] += $history['tax'];

				$month['taxed'] += $history['taxed'];
				$month['untaxed'] += $history['untaxed'];
			}

			if ( isset( $month ) && ( date( 'j', strtotime( $date ) ) == date( 't', strtotime( $date ) ) ) ) {
				$return .= '<tr style="border-bottom: 2px solid #999 !important; height: 2em; background-color: #ddd;">';

				$return .= '<td title="Date" style="text-align: left !important; color: #aaa;">Month</td>';
				$return .= '<td style="width: 5em;">&nbsp;</td>';

				$return .= '<td title="Non-Taxed" style="font-weight: bold; width: 5em;">' . AECToolbox::correctAmount( $month['untaxed'] ) . '</td>';

				if ( !empty( $month['taxed'] ) ) {
					$return .= '<td style="width: 5em;">+</td>';
					$return .= '<td title="Taxed including Tax" style="font-weight: bold; width: 5em;">' . AECToolbox::correctAmount( $month['taxed'] + $month['tax'] ) . '</td>';
					$return .= '<td title="Taxed" style="font-weight: bold; width: 5em; color: #aaa;">(' . AECToolbox::correctAmount( $month['taxed'] ) . '</td>';
					$return .= '<td style="width: 5em; color: #aaa;">+</td>';
					$return .= '<td title="Tax" style="font-weight: bold; width: 5em; color: #aaa;">' . AECToolbox::correctAmount( $month['tax'] ) . ')</td>';
				} else {
					$return .= '<td colspan="5"></td>';
				}

				$return .= '<td style="width: 5em;">=</td>';

				$return .= '<td style="width: 5em;">&nbsp;</td>';
				$return .= '<td title="Grand Total" style="text-align: right !important; color: #608919;">' . AECToolbox::correctAmount( $month['amount'] + $month['tax'] ) . '</td>';
				$return .= '</tr>';

				$return .= '<tr style="height: 1px; background-color: #999;">';
				$return .= '<td colspan="11"></td>';
				$return .= '</tr>';
			}

		}

		$return .= '</table><br /><br />';

		return $return;
	}

}
?>

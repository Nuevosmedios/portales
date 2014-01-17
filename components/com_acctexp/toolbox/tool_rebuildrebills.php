<?php
/**
 * @version $Id: tool_rebuildrebills.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Toolbox - Rebuild Rebills
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class tool_rebuildrebills
{
	function Info()
	{
		$info = array();
		$info['name'] = "Rebuild Rebills";
		$info['desc'] = "Before AEC 1.0, recurring payments weren't logged in the history, so they don't show up in statistics. With this tool, you can create these missing entries automatically.";

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['safe']		= array( 'toggle', 'Safe Mode', 'Double check that an entry does not exist already.', 1 );
		$settings['create']		= array( 'toggle', 'Create History Entries', 'Actually do create the history entries.', 0 );

		return $settings;
	}

	function Action()
	{
		$db = &JFactory::getDBO();

		$entries = 0;

		$planlist = array();
		$pplist = array();

		$processors = array( 'authorize_arb', 'google_checkout', 'hsbc', 'iats', 'paypal_wpp', 'sparkassen_internetkasse', 'usaepay' );

		$query = 'SELECT `id`'
		. ' FROM #__acctexp_invoices'
		. ' WHERE `method` IN (' . implode( ',', $processors ) . ')'
		;
		$db->setQuery( $query );
		$invoices = xJ::getDBArray( $db );

		foreach ( $invoices as $id ) {
			$invoice = new Invoice();
			$invoice->load( $id );

			// Skip non-rebilled
			if ( count( $invoice->transactions ) < 2 ) {
				continue;
			}

			foreach ( $invoice->transactions as $tid => $transaction ) {
				if ( !$tid ) {
					// Skip first entry
					continue;
				}

				if ( !empty( $_POST['safe'] ) ) {
					$query = 'SELECT `id`'
							. ' FROM #__acctexp_log_history'
							. ' WHERE transaction_date = \'' . $transaction->timestamp . '\''
							. ' AND proc_name = \'' . $transaction->processor . '\''
							. ' AND invoice_number = \'' . $invoice->invoice_number . '\''
							;
					$db->setQuery( $query );
					$hasentry = $db->loadResult();

					if ( $hasentry ) {
						continue;
					}
				}

				$entries++;

				if ( !empty( $_POST['create'] ) ) {
					$entry = new logHistory();

					$user = new cmsUser();
					$user->load( $invoice->userid );

					if ( !isset( $planlist[$invoice->usage] ) ) {
						$plan = new SubscriptionPlan();
						$plan->load( $invoice->usage );

						$planlist[$invoice->usage] = $plan;
					}

					if ( !isset( $pplist[$invoice->method] ) ) {
						$pp = new SubscriptionPlan();
						$pp->load( $invoice->method );

						$pplist[$invoice->method] = $pp;
					}

					if ( $pplist[$invoice->method]->id ) {
						$entry->proc_id			= $pplist[$invoice->method]->id;
						$entry->proc_name		= $pplist[$invoice->method]->processor_name;
					}

					$entry->user_id			= $user->id;
					$entry->user_name		= $user->username;

					if ( $planlist[$invoice->usage]->id ) {
						$entry->plan_id			= $planlist[$invoice->usage]->id;
						$entry->plan_name		= $planlist[$invoice->usage]->name;
					}

					$entry->transaction_date	= $transaction->timestamp;
					$entry->amount				= $transaction->amount;
					$entry->invoice_number		= $invoice->invoice_number;
					$entry->response			= 'Created by the Rebuild Rebills Tool';

					$entry->cleanup();

					$entry->check();
					$entry->store();
				}
			}
		}

		if ( empty( $entries ) ) {
			if ( $_POST['create'] ) {
				return "No Invoices found to create History Entries from.";
			} else {
				return "No Invoices with data found.";
			}
		} else {
			if ( $_POST['create'] ) {
				return $entries . " History Entries created.";
			} else {
				return "No History Entries created, found " . $entries . " that can be converted (select 'Create' from the settings above and carry out the query again)";
			}
		}
	}

}
?>

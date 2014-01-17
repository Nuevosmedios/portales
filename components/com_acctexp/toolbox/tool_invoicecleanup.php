<?php
/**
 * @version $Id: tool_invoicecleanup.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Toolbox - Invoice Cleanup
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class tool_invoicecleanup
{
	function Info()
	{
		$info = array();
		$info['name'] = "Invoice Cleanup";
		$info['desc'] = "Cleans out old and outdated invoices that are still unpaid.";

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['cutoff']				= array( 'inputB', 'Cutoff Age (Months)', 'Delete any unpaid invoices older than this many months', 3 );
		$settings['delete_unpub']		= array( 'toggle', 'Delete Unpublished', 'Remove all invoices with payment plans that are unpublished (regardless of invoice age)' );
		$settings['delete_invis']		= array( 'toggle', 'Delete Invisible', 'Remove all invoices with payment plans that are invisible (regardless of invoice age)' );
		$settings['delete']				= array( 'toggle', 'Delete', 'Do the cleanup (if you click submit without having this checked, AEC will only test how many records it would delete)' );

		return $settings;
	}

	function Action()
	{
		$db = &JFactory::getDBO();

		$found = array();

		$query = 'SELECT count(*)'
				. ' FROM #__acctexp_invoices'
				. ' WHERE `created_date` < \'' . date( 'Y-m-d H:i:s', strtotime( "-" . $_POST['cutoff'] . " months", ((int) gmdate('U')) ) ) . '\''
				. ' AND `transaction_date` = \'0000-00-00 00:00:00\''
				;
		$db->setQuery( $query );
		$found['total_old'] = $db->loadResult();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_plans'
				. ' WHERE `active` = \'0\''
				;
		$db->setQuery( $query );
		$dbaplans = xJ::getDBArray( $db );

		$query = 'SELECT count(*)'
				. ' FROM #__acctexp_invoices'
				. ' WHERE `transaction_date` = \'0000-00-00 00:00:00\''
				. ' AND `usage` IN (' . implode( ',', $dbaplans ) . ')'				
				;
		$db->setQuery( $query );
		$found['total_unpub'] = $db->loadResult();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_plans'
				. ' WHERE `visible` = \'0\''
				;
		$db->setQuery( $query );
		$dbxplans = xJ::getDBArray( $db );

		$query = 'SELECT count(*)'
				. ' FROM #__acctexp_invoices'
				. ' WHERE `transaction_date` = \'0000-00-00 00:00:00\''
				. ' AND `usage` IN (' . implode( ',', $dbxplans ) . ')'				
				;
		$db->setQuery( $query );
		$found['total_invis'] = $db->loadResult();

		if ( !empty( $_POST['delete'] ) ) {
			$return = '<p>Deleted a total of ' . $found['total_old'] . ' invoices older than ' . $_POST['cutoff'] . ' months.<p>';

			$query = 'DELETE'
					. ' FROM #__acctexp_invoices'
					. ' WHERE `created_date` < \'' . date( 'Y-m-d H:i:s', strtotime( "-" . $_POST['cutoff'] . " months", ((int) gmdate('U')) ) ) . '\''
					. ' AND `transaction_date` = \'0000-00-00 00:00:00\''
					;
			$db->setQuery( $query );
			$db->query();

			if ( !empty( $_POST['delete_unpub'] ) ) {
				$query = 'SELECT count(*)'
						. ' FROM #__acctexp_invoices'
						. ' WHERE `transaction_date` = \'0000-00-00 00:00:00\''
						. ' AND `usage` IN (' . implode( ',', $dbaplans ) . ')'				
						;
				$db->setQuery( $query );
				$found['total_unpub'] = $db->loadResult();

				if ( !empty( $found['total_unpub'] ) ) {
					$query = 'DELETE'
							. ' FROM #__acctexp_invoices'
							. ' WHERE `transaction_date` = \'0000-00-00 00:00:00\''
							. ' AND `usage` IN (' . implode( ',', $dbaplans ) . ')'				
							;
					$db->setQuery( $query );
					$db->query();

					$return .= '<p>Deleted ' . $found['total_unpub'] . ' unpaid invoices that reference payment plans that are unpublished.<p>';
				} else {
					$return .= '<p>Found no further unpaid invoices that reference payment plans that are unpublished.<p>';
				}
			}

			if ( !empty( $_POST['delete_invis'] ) ) {
				$query = 'SELECT count(*)'
						. ' FROM #__acctexp_invoices'
						. ' WHERE `transaction_date` = \'0000-00-00 00:00:00\''
						. ' AND `usage` IN (' . implode( ',', $dbxplans ) . ')'				
						;
				$db->setQuery( $query );
				$found['total_invis'] = $db->loadResult();

				if ( !empty( $found['total_invis'] ) ) {
					$query = 'DELETE'
							. ' FROM #__acctexp_invoices'
							. ' WHERE `transaction_date` = \'0000-00-00 00:00:00\''
							. ' AND `usage` IN (' . implode( ',', $dbxplans ) . ')'				
							;
					$db->setQuery( $query );
					$db->query();

					$return .= '<p>Deleted ' . $found['total_invis'] . ' unpaid invoices that reference payment plans that are invisible.<p>';
				} else {
					$return .= '<p>Found no further unpaid invoices that reference payment plans that are invisible.<p>';
				}
			}

			return $return;
		} else {
			$return = "";

			if ( !empty( $found['total_old'] ) ) {
				$return .= '<p>Found ' . $found['total_old'] . ' unpaid invoices older than ' . $_POST['cutoff'] . ' months.<p>';
			} else {
				$return .= '<p>Found no unpaid invoices older than ' . $_POST['cutoff'] . ' months.<p>';
			}

			if ( !empty( $found['total_unpub'] ) ) {
				$return .= '<p>Found ' . $found['total_unpub'] . ' unpaid invoices that reference payment plans that are unpublished.<p>';
			} else {
				$return .= '<p>Found no unpaid invoices that reference payment plans that are unpublished.<p>';
			}

			if ( !empty( $found['total_invis'] ) ) {
				$return .= '<p>Found ' . $found['total_invis'] . ' unpaid invoices that reference payment plans that are invisible.<p>';
			} else {
				$return .= '<p>Found no unpaid invoices that reference payment plans that are invisible.<p>';
			}

			$return .= '<p>Note: Individual counts may overlap as outdated invoices are often both referencing unpublished and invisible plans.<p>';

			return $return;
		}
	}

}
?>

<?php
/**
 * @version $Id: upgrade_0_12_6_RC2n.inc.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Install Includes
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

// Making up for old thoughts
$db->setQuery("UPDATE #__acctexp_itemxgroup SET group_id='1' WHERE group_id='0'");
$db->query();

// Fixing secondary invoice numbers for CCBill
$query = 'SELECT id FROM #__acctexp_config_processors WHERE name = \'ccbill\'';
$db->setQuery( $query );

$ccid = $db->loadResult();

// Checking whether CCBill is installed at all
if ( $ccid ) {
	// Get all history entries for CCBill
	$query = 'SELECT id FROM #__acctexp_log_history WHERE proc_id = \'' . $ccid . '\'';
	$db->setQuery( $query );

	$list = xJ::getDBArray( $db );

	if ( !empty( $list ) ) {
		foreach ( $list as $hid ) {
			$history = new logHistory();
			$history->load( $hid );

			$params = parameterHandler::decode( stripslashes( $history->response ) );

			// Check for the parameters we need
			if ( isset( $params['subscription_id'] ) && isset( $params['invoice'] ) ) {
				$query = 'UPDATE #__acctexp_invoices SET `secondary_ident` = \'' . $params['subscription_id'] . '\' WHERE invoice_number = \'' . $params['invoice'] . '\'';
				$db->setQuery( $query );
				$db->query();
			}
		}
	}
}

// Haunted by ghosts of xmas past
$query = 'SELECT `id` FROM #__acctexp_subscr WHERE `params` LIKE \'%_jsoon%\'';
$db->setQuery( $query );

$list = xJ::getDBArray( $db );

if ( !empty( $list ) ) {
	foreach ( $list as $sid ) {
		$query = 'SELECT `params` FROM #__acctexp_subscr WHERE `id` = \'' . $sid . '\'';
		$db->setQuery( $query );

		$params = $db->loadResult();
		$decode = stripslashes( str_replace( array( '\n', '\t', '\r' ), array( "\n", "\t", "\r" ), trim($params) ) );
		$temp = jsoonHandler::decode( $decode );

		$query = 'UPDATE #__acctexp_subscr SET `params` = \'' . base64_encode( serialize( $temp ) ) . '\' WHERE `id` = \'' . $sid . '\'';
		$db->setQuery( $query );
		$db->query();
	}
}

if ( in_array( $app->getCfg( 'dbprefix' ) . "acctexp_mi_hotproperty", $tables ) ) {
	$filename = JPATH_SITE . '/components/com_acctexp/micro_integration/mi_hotproperty.php';

	if ( file_exists( $filename ) ) {
		include_once $filename;

		$fielddeclare = array( 'params' );

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_mi_hotproperty'
				;
		$db->setQuery( $query );
		$entries = xJ::getDBArray( $db );

		if ( !empty( $entries ) ) {
			foreach ( $entries as $id ) {
				$query = 'SELECT `params` FROM #__acctexp_mi_hotproperty'
				. ' WHERE `id` = \'' . $id . '\''
				;
				$db->setQuery( $query );
				$object = $db->loadObject();

				if ( empty( $object->params ) ) {
					continue;
				}

				// Decode from jsonized fields
				if ( strpos( $object->params, "{" ) === 0 ) {
					$decode = stripslashes( str_replace( array( '\n', '\t', '\r' ), array( "\n", "\t", "\r" ), trim($object->params) ) );
					$temp = jsoonHandler::decode( $decode );
				} else {
					continue;
				}

				// ... to serialized
				$query = 'UPDATE #__acctexp_' . $dbtable
				. ' SET `params` = \'' . base64_encode( serialize( $temp ) ) . '\''
				. ' WHERE `id` = \'' . $id . '\''
				;
				$db->setQuery( $query );
				if ( !$db->query() ) {
			    	$errors[] = array( $db->getErrorMsg(), $query );
				}
			}
		}
	}
}

?>
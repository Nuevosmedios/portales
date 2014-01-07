<?php
/**
 * @version $Id: upgrade_0_12_6_RC2q.inc.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Install Includes
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

// There was a glitch with the RC2p update, making sure that this gets applied here
$db->setQuery("ALTER TABLE #__acctexp_invoices CHANGE `coupons` `coupons` text NULL");
if ( !$db->query() ) {
	$errors[] = array( $db->getErrorMsg(), $query );
}

$query = 'SELECT `id`'
		. ' FROM #__acctexp_metauser'
		;
$db->setQuery( $query );
$entries = xJ::getDBArray( $db );

/*
 * Again using the same method from RC2m to fix the processor params fields here:
 * This may seem odd, but due to unforseen consequences, json encoding and decoding
 * actually fixes some numeric properties so that we can switch them over to arrays,
 * which is done with get_object_vars as its the quickest AND, uhm, dirtiest method.
 * without the encoding and decoding, get_object_vars just purrs out an empty array.
 */

foreach ( $entries as $eid ) {
	$meta = new metaUserDB();
	$meta->load( $eid );

	if ( !empty( $meta->processor_params ) ) {
		if ( is_object( $meta->processor_params ) ) {
			$temp = get_object_vars( json_decode( json_encode( $meta->processor_params ) ) );

			$new = array();
			foreach( $temp as $pid => $param ) {
				$new[$pid] = $param;

				if ( isset( $new[$pid]->paymentProfiles ) ) {
					$new[$pid]->paymentProfiles = get_object_vars( json_decode( json_encode( $new[$pid]->paymentProfiles ) ) );
				}

				if ( isset( $new[$pid]->shippingProfiles ) ) {
					$new[$pid]->shippingProfiles = get_object_vars( json_decode( json_encode( $new[$pid]->shippingProfiles ) ) );
				}
			}

			$meta->processor_params = $new;
		}
	}

	$meta->check();
	$meta->store();
}

$eucaInstalldb->dropColifExists( 'processors', 'plans' );

// Fixing stupid mistake - creating all processors at once for no good reason
$db->setQuery("SELECT count(*) FROM  #__acctexp_config_processors");

$procnum = $db->loadResult();

$db->setQuery("SELECT count(*) FROM  #__acctexp_plans");

$plannum = $db->loadResult();

if ( ( $procnum > 20 ) && ( $plannum > 0 ) ) {
	$allprocs = array();

	$query = 'SELECT id FROM #__acctexp_plans';
	$db->setQuery( $query );

	$plans = xJ::getDBArray( $db );

	foreach ( $plans as $planid ) {
		$plan = new SubscriptionPlan();
		$plan->load( $planid );

		if ( !empty( $plan->params['processors'] ) ) {
			foreach ( $plan->params['processors'] as $pi ) {
				if ( !in_array( $pi, $allprocs ) ) {
					$allprocs[] = $pi;
				}
			}
		}
	}

	$query = 'SELECT id FROM #__acctexp_config_processors';
	$db->setQuery( $query );

	$procs = xJ::getDBArray( $db );

	foreach ( $procs as $procid ) {
		// Check whether the processor has a plan it is applied to
		if ( !in_array( $procid, $allprocs ) ) {
			// Double check whether we have a history entry
			$query = 'SELECT id FROM #__acctexp_log_history WHERE `proc_id` = \'' . $procid . '\'';
			$db->setQuery( $query );

			if ( !$db->loadResult() ) {
				$query = 'DELETE FROM #__acctexp_config_processors WHERE `id` = \'' . $procid . '\'';
				$db->setQuery( $query );
				$db->query();
			}
		}
	}
}

$eucaInstalldb->addColifNotExists( 'hidden', "int(4) NOT NULL default '0'", 'microintegrations' );

?>
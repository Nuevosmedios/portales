<?php
/**
 * @version $Id: upgrade_0_10_0.inc.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Install Includes
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

// Update routine 0.8.0x -> 0.10.0

if ( $eucaInstalldb->columnintable( 'entry', 'plans' ) ) {
	$db->setQuery("ALTER TABLE #__acctexp_plans DROP `entry`");
	if ( !$db->query() ) {
    	$errors[] = array( $db->getErrorMsg(), $query );
	}
} else {
	$db->setQuery("ALTER TABLE #__acctexp_invoices CHANGE `invoice_number` `invoice_number` varchar(64) NULL");
	if ( !$db->query() ) {
    	$errors[] = array( $db->getErrorMsg(), $query );
	}
}

$db->setQuery("SHOW COLUMNS FROM #__acctexp_plans LIKE 'desc'");
$result = $db->loadObject();

if ( (strcmp($result->Field, 'desc') === 0) && (strcmp($result->Type, 'varchar(255)') === 0) ) {
	// Give extra space for plan description
	$db->setQuery("ALTER TABLE #__acctexp_plans CHANGE `desc` `desc` text NULL");
	if ( !$db->query() ) {
    	$errors[] = array( $db->getErrorMsg(), $query );
	}
}

$eucaInstalldb->addColifNotExists( 'lifetime', "int(4) default '0'",  'plans' );

if ( !$eucaInstalldb->columnintable( 'lifetime', 'subscr' ) ) {
	$db->setQuery("ALTER TABLE #__acctexp_subscr CHANGE `extra05` `lifetime` int(1) default '0'");
	if ( !$db->query() ) {
    	$errors[] = array( $db->getErrorMsg(), $query );
	}
}

$eucaInstalldb->addColifNotExists( 'previous_plan', "int(11) NULL",  'subscr' );
$eucaInstalldb->addColifNotExists( 'used_plans', "varchar(255) NULL",  'subscr' );
$eucaInstalldb->dropColifExists( 'email_sent', 'subscr' );
$eucaInstalldb->addColifNotExists( 'coupons', "varchar(255) NULL",  'invoices' );
$eucaInstalldb->addColifNotExists( 'micro_integrations', "text NULL",  'plans' );
$eucaInstalldb->addColifNotExists( 'params', "varchar(255) NULL",  'invoices' );
$eucaInstalldb->addColifNotExists( 'email_desc', "text NULL", 'plans' );

$db->setQuery("SHOW COLUMNS FROM #__acctexp_invoices LIKE 'fixed'");
$result = $db->loadObject();

if (!(strcmp($result->Field, 'fixed') === 0)) {
	$query = "ALTER TABLE #__acctexp_invoices ADD `fixed` int(4) default '0'";
} elseif ( (strcmp($result->Field, 'fixed') === 0) && (strcmp($result->Type, 'int(4)') === 0) && $result->Null ) {
	$query = "ALTER TABLE #__acctexp_invoices CHANGE `fixed` `fixed` int(4) default '0'";
} elseif ( (strcmp($result->Field, 'fixed') === 0) && (strcmp($result->Type, "int(4)") === 0) && $result->Default ) {
	$query = "ALTER TABLE #__acctexp_invoices CHANGE `fixed` `fixed` int(4) default '0'";
}

$db->setQuery( $query );
if ( !$db->query() ) {
	$errors[] = array( $db->getErrorMsg(), $query );
}

$eucaInstalldb->addColifNotExists( 'visible', "int(4) default '1'",  'plans' );
$eucaInstalldb->dropColifExists( 'recurring', 'plans' );
$eucaInstalldb->addColifNotExists( 'on_userchange', "int(4) default '0'",  'microintegrations' );
$eucaInstalldb->addColifNotExists( 'created_date', "datetime NULL default '0000-00-00 00:00:00'",  'invoices' );

if ( $eucaInstalldb->columnintable( 'planid', 'invoices' ) ) {
	$eucaInstalldb->dropColifExists( 'usage', 'invoices' );

	$db->setQuery("ALTER TABLE #__acctexp_invoices CHANGE `planid` `usage` varchar(255) NULL");
	if ( !$db->query() ) {
    	$errors[] = array( $db->getErrorMsg(), $query );
	}
} else {
	$db->setQuery("SHOW COLUMNS FROM #__acctexp_invoices LIKE 'usage'");
	$result = $db->loadObject();

	if ( !$eucaInstalldb->columnintable( 'usage', 'invoices' ) ) {
		$db->setQuery("ALTER TABLE #__acctexp_invoices ADD `usage` varchar(255) NULL");
		if ( !$db->query() ) {
	    	$errors[] = array( $db->getErrorMsg(), $query );
		}
	}
}

$eucaInstalldb->dropColifExists( 'reuse', 'plans' );
$eucaInstalldb->addColifNotExists( 'processors', "varchar(255) NULL",  'plans' );
$eucaInstalldb->addColifNotExists( 'active', "int(4) default '1'",  'invoices' );

$db->setQuery("SHOW COLUMNS FROM #__acctexp_subscr LIKE 'extra01'");
$result = $db->loadObject();

if ( is_object( $result ) ) {
	if (strcmp($result->Field, 'extra01') === 0) {

		$queri = array();
		$queri[] = "ALTER TABLE #__acctexp_subscr CHANGE `extra01` `recurring` int(1) NOT NULL default '0'";
		$queri[] = "ALTER TABLE #__acctexp_subscr DROP `extra02`";
		$queri[] = "ALTER TABLE #__acctexp_subscr DROP `extra03`";
		$queri[] = "ALTER TABLE #__acctexp_subscr DROP `extra04`";

		$eucaInstalldb->multiQueryExec( $queri );
	}
}

$db->setQuery("SELECT count(*) FROM  #__acctexp_config_processors");
$oldplans = ( $db->loadResult() == 0 && in_array( $app->getCfg( 'dbprefix' ) . 'acctexp_processors_plans', $tables ) );

if ( $oldplans || in_array( $app->getCfg( 'dbprefix' ) . 'acctexp_config_paypal', $tables ) ) {
	$db->setQuery( "SELECT proc_id FROM #__acctexp_processors_plans" );
	$db_processors = xJ::getDBArray( $db );

	if ( is_array( $db_processors ) ) {
		$used_processors = array_unique($db_processors);

		$legacy_processors_db = array("", "paypal", "vklix", "authorize", "allopass", "2checkout", "epsnetpay", "paysignet", "worldpay", "alertpay");
		$legacy_processors_name = array("", "paypal", "viaklix", "authorize", "allopass", "2checkout", "epsnetpay", "paysignet", "worldpay", "alertpay");

		foreach ( $used_processors AS $i => $n ) {

			$db->setQuery( "SELECT * FROM #__acctexp_config_" . $legacy_processors_db[$n] );
			$old_cfg = $db->loadObject();

			$pp = new PaymentProcessor();
			$pp->loadName($legacy_processors_name[$n]);
			$pp->init();

			switch ($legacy_processors_name[$n]) {
				case 'paypal':
					$pp->settings['business']		= $old_cfg->business;
					$pp->settings['testmode']		= $old_cfg->testmode;
					$pp->settings['tax']			= $old_cfg->tax;
					$pp->settings['currency']		= $old_cfg->currency_code;
					$pp->settings['checkbusiness']	= $old_cfg->checkbusiness;
					$pp->settings['lc']				= $old_cfg->lc;
					$pp->settings['altipnurl']		= $old_cfg->altipnurl;
					$pp->setSettings();

					$pp = new PaymentProcessor();
					$pp->loadName("paypal_subscription");
					$pp->init();
					$pp->settings['business']		= $old_cfg->business;
					$pp->settings['testmode']		= $old_cfg->testmode;
					$pp->settings['tax']			= $old_cfg->tax;
					$pp->settings['currency']		= $old_cfg->currency_code;
					$pp->settings['checkbusiness']	= $old_cfg->checkbusiness;
					$pp->settings['lc']				= $old_cfg->lc;
					$pp->settings['altipnurl']		= $old_cfg->altipnurl;

					break;
				case 'viaklix':
					$pp->settings['accountid']		= $old_cfg->accountid;
					$pp->settings['userid']			= $old_cfg->userid;
					$pp->settings['pin']			= $old_cfg->pin;
					$pp->settings['testmode']		= $old_cfg->testmode;
					break;
				case 'authorize':
					$pp->settings['login']			= $old_cfg->login;
					$pp->settings['transaction_key'] = $old_cfg->transaction_key;
					$pp->settings['testmode']		= $old_cfg->testmode;
					$pp->settings['currency']		= $old_cfg->currency_code;
					break;
				case 'allopass':
					$pp->settings['siteid']			= $old_cfg->siteid;
					$pp->settings['docid']			= $old_cfg->docid;
					$pp->settings['auth']			= $old_cfg->auth;
					$pp->settings['testmode']		= $old_cfg->testmode;
					break;
				case '2checkout':
					$pp->settings['sid']			= $old_cfg->sid;
					$pp->settings['secret_word']	= $old_cfg->secret_word;
					$pp->settings['alt2courl']		= $old_cfg->alt2courl;
					$pp->settings['testmode']		= $old_cfg->testmode;
					break;
				case 'paysignet':
					$pp->settings['merchant']		= $old_cfg->merchant;
					$pp->settings['testmode']		= $old_cfg->testmode;
					break;
				case 'worldpay':
					$pp->settings['instId']			= $old_cfg->instId;
					$pp->settings['testmode']		= $old_cfg->testmode;
					$pp->settings['currency']		= $old_cfg->currency_code;
					break;
				case 'alertpay':
					$pp->settings['merchant']		= $old_cfg->ap_merchant;
					$pp->settings['securitycode']	= $old_cfg->ap_securitycode;
					$pp->settings['tax']			= $old_cfg->tax;
					$pp->settings['testmode']		= $old_cfg->testmode;
					break;
				default:
					break;
			}

			$pp->setSettings();
		}

		$db->setQuery( "SELECT * FROM #__acctexp_processors_plans" );
		$procplans = $db->loadObjectList();

		foreach ( $procplans as $planentry ) {
			$db->setQuery( "SELECT processors FROM #__acctexp_plans WHERE id='" . $planentry->plan_id . "'" );
			$plan_procs = explode(";", $db->loadResult());

			if (count($plan_procs) && $plan_procs[0]) {
				if (!in_array($planentry->proc_id, $plan_procs)) {
					$db->setQuery( "SELECT id FROM #__acctexp_config_processors WHERE name='" . $legacy_processors_name[$planentry->proc_id] . "'" );
					$proc_realid = $db->loadResult();

					if (($proc_realid > 0) &&!is_null($proc_realid)) {
						$plan_procs[] = $proc_realid;
					}
				}
			} else {
				// Re-init to prevent null entries
				$plan_procs = array();
				$db->setQuery( "SELECT id FROM #__acctexp_config_processors WHERE name='" . $legacy_processors_name[$planentry->proc_id] . "'" );
				$proc_realid = $db->loadResult();

				if (($proc_realid > 0) &&!is_null($proc_realid)) {
					$plan_procs[] = $proc_realid;
				}
			}

			$plan_procs_unique = array_unique( $plan_procs );

			if (count($plan_procs)) {
				$db->setQuery( "UPDATE #__acctexp_plans SET processors='" . implode(";", $plan_procs) . "' WHERE id='" . $planentry->plan_id . "'" );
				if ( !$db->query() ) {
			    	$errors[] = array( $db->getErrorMsg(), $query );
				}
			}
		}
	}

	$queri = null;

	$queri[] = "DROP TABLE IF EXISTS #__acctexp_processors_plans";
	$queri[] = "DROP TABLE IF EXISTS #__acctexp_processors";
	$queri[] = "DROP TABLE IF EXISTS #__acctexp_config_paypal";
	$queri[] = "DROP TABLE IF EXISTS #__acctexp_config_vklix";
	$queri[] = "DROP TABLE IF EXISTS #__acctexp_config_authorize";
	$queri[] = "DROP TABLE IF EXISTS #__acctexp_config_allopass";
	$queri[] = "DROP TABLE IF EXISTS #__acctexp_config_2checkout";
	$queri[] = "DROP TABLE IF EXISTS #__acctexp_config_epsnetpay";
	$queri[] = "DROP TABLE IF EXISTS #__acctexp_config_paysignet";
	$queri[] = "DROP TABLE IF EXISTS #__acctexp_config_worldpay";
	$queri[] = "DROP TABLE IF EXISTS #__acctexp_config_alertpay";


	$eucaInstalldb->multiQueryExec( $queri );
}
?>
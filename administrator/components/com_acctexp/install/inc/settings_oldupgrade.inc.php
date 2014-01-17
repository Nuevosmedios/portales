<?php
/**
 * @version $Id: settings_oldupgrade.inc.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Install Includes
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

// load settings (creates settings parameters that got added in this version)
$db->setQuery( "SHOW COLUMNS FROM #__acctexp_config LIKE 'settings'" );
$result = $db->loadObject();

if ( strcmp( $result->Field, 'settings' ) !== 0 ) {
	$columns = array(	"transferinfo", "initialexp", "alertlevel1", "alertlevel2",
						"alertlevel3", "gwlist", "customintro", "customthanks",
						"customcancel", "bypassintegration", "simpleurls", "expiration_cushion",
						"currency_code", "heartbeat_cycle", "tos", "require_subscription",
						"entry_plan", "plans_first", "transfer", "checkusername", "activate_paid"
					);

	$settings = array();
	foreach ($columns as $column) {
		$db->setQuery("SHOW COLUMNS FROM #__acctexp_config LIKE '" . $column . "'");
		$result = $db->loadObject();

		if (strcmp($result->Field, $column) === 0) {
			$db->setQuery( "SELECT " . $column . " FROM #__acctexp_config WHERE id='1'" );
			$settings[$column] = $db->loadResult();

			$db->setQuery("ALTER TABLE #__acctexp_config DROP COLUMN " . $column);
			if ( !$db->query() ) {
		    	$errors[] = array( $db->getErrorMsg(), $query );
			}
		}
	}

	$db->setQuery("ALTER TABLE #__acctexp_config ADD `settings` text");
	if ( !$db->query() ) {
    	$errors[] = array( $db->getErrorMsg(), $query );
	}

	$db->setQuery("UPDATE #__acctexp_config SET `settings` = '" . parameterHandler::encode( $settings ) . "' WHERE id = '1'");
	if ( !$db->query() ) {
    	$errors[] = array( $db->getErrorMsg(), $query );
	}
}
?>
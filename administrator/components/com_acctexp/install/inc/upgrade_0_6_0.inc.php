<?php
/**
 * @version $Id: upgrade_0_6_0.inc.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Install Includes
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

// Update routine 0.3.0 -> 0.6.0
if ( in_array( $app->getCfg( 'dbprefix' ) . "acctexp_payplans", $tables ) ) {
	// Check for existence of 'gid' column on table #__acctexp_payplans
	// It is existent only from version 0.6.0
	$db->setQuery("SHOW COLUMNS FROM #__acctexp_payplans LIKE 'gid'");
	$result = $db->loadObject();

	if (strcmp($result->Field, 'gid') === 0) {
		// You're already running version 0.6.0 or later. No action required.
	} else {
		// You're not running version 0.6.0 or later. Update required.
		$db->setQuery("ALTER TABLE #__acctexp_payplans ADD `gid` int(3) default NULL");
		if ( !$db->query() ) {
	    	$errors[] = array( $db->getErrorMsg(), $query );
		}
	}
}
?>
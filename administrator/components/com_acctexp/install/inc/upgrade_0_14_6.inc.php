<?php
/**
 * @version $Id: upgrade_0_14_6.inc.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Install Includes
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

$ktables = array('metauser', 'displaypipeline', 'invoices', 'cart', 'event', 'subscr', 'couponsxuser');

foreach ( $ktables as $table ) {
	$haskey = false;

	$db->setQuery("SHOW INDEXES FROM #__acctexp_" . $table . "");
	$kentries = $db->loadObjectList();
	
	foreach ( $kentries as $kentry ) {
		// Whoopsie! Let's get rid of potentially dozens of entries
		if ( strpos( $kentry->Key_name, 'userid' ) !== false ) {
			if ( !$haskey ) {
				$haskey = true;
			} else {
				$db->setQuery("ALTER TABLE #__acctexp_" . $table . " DROP KEY `" . $kentry->Key_name . "`");
				if ( !$db->query() ) {
					$errors[] = array( $db->getErrorMsg(), $query );
				}
			}
		}
	}

	if ( !$haskey ) {
		$db->setQuery("ALTER TABLE #__acctexp_" . $table . " ADD KEY (`userid`)");
		if ( !$db->query() ) {
			$errors[] = array( $db->getErrorMsg(), $query );
		}
	}
}

?>
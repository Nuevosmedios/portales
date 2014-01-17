<?php
/**
 * @version $Id: settings_0_12_6_upgrade.inc.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Install Includes
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

$serialupdate	= false;
$jsonconversion = false;

// Check whether the config is on 0.12.6 status
$query = 'SELECT `settings` FROM #__acctexp_config'
. ' WHERE `id` = \'1\''
;
$db->setQuery( $query );
$res = $db->loadResult();

if ( ( ( strpos( $res, '{' ) === 0 ) || ( strpos( $res, "\n" ) !== false ) ) && !empty( $res ) ) {
	if ( strpos( $res, '{' ) === 0 ) {
		$res = stripslashes( str_replace( array( '\n', '\t', '\r' ), array( "\n", "\t", "\r" ), trim($res) ) );;
		$restings = jsoonHandler::decode( $res );
		$jsonconversion = true;
	} else {
		// Has stripslashes stuff built in
		$restings = parameterHandler::decode( $res );
		$serialupdate = true;

		if ( isset( $restings['milist'] ) ) {
			$temp = explode( ';', $restings['milist'] );
			$restings['milist'] = $temp;
		}

		if ( isset( $restings['gwlist'] ) ) {
			$temp = explode( ';', $restings['gwlist'] );
			$restings['gwlist'] = $temp;
		}
	}

	$entry = base64_encode( serialize( $restings ) );

	$query = 'UPDATE #__acctexp_config'
	. ' SET `settings` = \'' . $entry . '\''
	. ' WHERE `id` = \'1\''
	;
	$db->setQuery( $query );
	$db->query();
}
?>
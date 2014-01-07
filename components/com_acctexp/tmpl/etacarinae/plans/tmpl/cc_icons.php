<?php
/**
 * @version $Id: cc_icons.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<?php
if ( is_array( $cc_list ) ) {
	$cc_array = $cc_list;
} else {
	$cc_array = explode( ',', $cc_list );
}

for ( $i = 0; $i < count( $cc_array ); $i++ ) {
	echo '<img src="' . JURI::root(true) . '/media/' . $option
	. '/images/site/cc_icons/ccicon_' . $cc_array[$i] . '.png"'
	. ' alt="' . $cc_array[$i] . '"'
	. ' title="' . $cc_array[$i] . '"'
	. ' class="cc-icon" />';
}

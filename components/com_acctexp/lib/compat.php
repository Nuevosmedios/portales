<?php

// Make sure we are compatible with php4
if ( version_compare( phpversion(), '5.0' ) < 0 ) {
	include_once( JPATH_SITE . '/components/com_acctexp/lib/php4/php4.php' );
}

// and php5.0<>5.2
if (  ( version_compare( phpversion(), '5.0') >= 0 )  && ( version_compare( phpversion(), '5.2' ) < 0 ) ) {
	include_once( JPATH_SITE . '/components/com_acctexp/lib/php4/phplt5_2.php' );
}

// Cross Joomla Version Compatibility
include_once( JPATH_SITE . '/components/com_acctexp/lib/xj/xj.php' );

?>

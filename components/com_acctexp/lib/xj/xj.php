<?php

include_once( dirname(__FILE__) . '/utility.php' );
include_once( dirname(__FILE__) . '/common.php' );

if ( !defined( 'JPATH_MANIFESTS' ) ) {
	include_once( dirname(__FILE__) . '/ltj30.php' );
	include_once( dirname(__FILE__) . '/j15.php' );
} else {
	include_once( dirname(__FILE__) . '/gtj25.php' );

	$v = new JVersion();

	if ( $v->isCompatible('3.0') ) {
		include_once( dirname(__FILE__) . '/j30.php' );
	} else {
		include_once( dirname(__FILE__) . '/ltj30.php' );
	}
}

?>

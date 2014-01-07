<?php
/**
* Joomla/Mambo Community Builder
* @version $Id: comprofiler.php 1150 2010-07-06 14:44:25Z beat $
* @package Community Builder
* @subpackage comprofiler.php
* @author JoomlaJoe and Beat
* @copyright (C) JoomlaJoe and Beat, www.joomlapolis.com
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// Took out the checks for internal saves

function saveRegistrationNOCHECKSLOL( $option ) {
	global $_CB_framework, $_CB_database, $ueConfig, $_POST, $_PLUGINS;

	// Check rights to access:

	if ( ( ( $_CB_framework->getCfg( 'allowUserRegistration' ) == '0' )
		   && ( ( ! isset($ueConfig['reg_admin_allowcbregistration']) ) || $ueConfig['reg_admin_allowcbregistration'] != '1' ) )
		 || $_CB_framework->myId() ) {
		cbNotAuth();
		return;
	}
	if ( ! isset( $ueConfig['emailpass'] ) ) {
		$ueConfig['emailpass']			=	'0';
	}

	$userComplete						=	new moscomprofilerUser( $_CB_database );

	// Pre-registration trigger:

	$_PLUGINS->loadPluginGroup('user');
	$_PLUGINS->trigger( 'onStartSaveUserRegistration', array() );
	if( $_PLUGINS->is_errors() ) {
		echo "<script type=\"text/javascript\">alert('".addslashes($_PLUGINS->getErrorMSG())."'); </script>\n";
		$oldUserComplete				=	new moscomprofilerUser( $_CB_database );
		$userComplete->bindSafely( $_POST, $_CB_framework->getUi(), 'register', $oldUserComplete );
		HTML_comprofiler::registerForm( $option, $ueConfig['emailpass'], $userComplete, $_POST, $_PLUGINS->getErrorMSG("<br />") );
		return;
	}

	// Check if this user already registered with exactly this username and password:

	$username							=	cbGetParam( $_POST, 'username', '' );
	$usernameExists						=	$userComplete->loadByUsername( $username );
	if ( $usernameExists ) {
		$password						=	cbGetParam( $_POST, 'password', '', _CB_ALLOWRAW );
		if ( $userComplete->verifyPassword( $password ) ) {
			$pwd_md5					=	$userComplete->password;
			$userComplete->password		=	$password;
			$messagesToUser				=	activateUser( $userComplete, 1, 'SameUserRegistrationAgain' );
			$userComplete->password		=	$pwd_md5;
			echo "\n<div>" . implode( "</div>\n<div>", $messagesToUser ) . "</div>\n";
			return;
		} else {
			$msg						=	sprintf( _UE_USERNAME_ALREADY_EXISTS, $username );
			echo "<script type=\"text/javascript\">alert('" . addslashes( $msg ) . "'); </script>\n";
			$oldUserComplete				=	new moscomprofilerUser( $_CB_database );
			$userComplete->bindSafely( $_POST, $_CB_framework->getUi(), 'register', $oldUserComplete );
			HTML_comprofiler::registerForm( $option, $ueConfig['emailpass'], $userComplete, $_POST, htmlspecialchars( $msg ) );
			return;
		}
	}

	// Store and check terms and conditions accepted (not a field yet !!!!):

	if ( isset( $_POST['acceptedterms'] ) ) {
		$userComplete->acceptedterms	=	( (int) cbGetParam( $_POST, 'acceptedterms', 0 ) == 1 ? 1 : 0 );
	} else {
		$userComplete->acceptedterms	=	null;
	}

	if($ueConfig['reg_enable_toc']) {
		if ( $userComplete->acceptedterms != 1 ) {
			echo "<script type=\"text/javascript\">alert('" . addslashes( cbUnHtmlspecialchars( _UE_TOC_REQUIRED ) ) ."'); </script>\n";
			$oldUserComplete				=	new moscomprofilerUser( $_CB_database );
			$userComplete->bindSafely( $_POST, $_CB_framework->getUi(), 'register', $oldUserComplete );
			HTML_comprofiler::registerForm( $option, $ueConfig['emailpass'], $userComplete, $_POST, _UE_TOC_REQUIRED . '<br />' );
			return;
		}
	}

	// Set id to 0 for autoincrement and store IP address used for registration:

	$userComplete->id			 		=	0;
	$userComplete->registeripaddr		=	cbGetIPlist();


	// Store new user state:

	$saveResult					=	$userComplete->saveSafely( $_POST, $_CB_framework->getUi(), 'register' );
	if ( $saveResult === false ) {
		echo "<script type=\"text/javascript\">alert('" . str_replace( '\\\\n', '\\n', addslashes( strip_tags( str_replace( '<br />', '\n', $userComplete->getError() ) ) ) ) ."'); </script>\n";
		HTML_comprofiler::registerForm( $option, $ueConfig['emailpass'], $userComplete, $_POST, $userComplete->getError() );
		return;
	}

	if ( $saveResult['ok'] === true ) {
		$messagesToUser			=	activateUser( $userComplete, 1, "UserRegistration" );
	}
	foreach ( $saveResult['tabs'] as $res ) {
		if ($res) {
			$messagesToUser[] = $res;
		}
	}
	if ( $saveResult['ok'] === false ) {
		echo "<script type=\"text/javascript\">alert('" . str_replace( '\\\\n', '\\n', addslashes( strip_tags( str_replace( '<br />', '\n', $userComplete->getError() ) ) ) ) . "'); </script>\n";
		HTML_comprofiler::registerForm( $option, $ueConfig['emailpass'], $userComplete, $_POST, $userComplete->getError() );
		return;
	}

	$_PLUGINS->trigger( 'onAfterUserRegistrationMailsSent', array( &$userComplete, &$userComplete, &$messagesToUser, $ueConfig['reg_confirmation'], $ueConfig['reg_admin_approval'], true));

	foreach ( $saveResult['after'] as $res ) {
		if ( $res ) {
			echo "\n<div>" . $res . "</div>\n";
		}
	}

	if ( $_PLUGINS->is_errors() ) {
		echo $_PLUGINS->getErrorMSG();
		HTML_comprofiler::registerForm( $option, $ueConfig['emailpass'], $userComplete, $_POST, $_PLUGINS->getErrorMSG() );
		return;
	}

	echo "\n<div>" . implode( "</div>\n<div>", $messagesToUser ) . "</div>\n";
}
?>

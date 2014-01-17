<?php
/**
 * @version $Id: aecErrorHandler.php
 * @package AEC - Account Control Expiration - Joomla 1.5 Plugins
 * @subpackage User
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

// Import library dependencies
jimport('joomla.plugin.plugin');

/**
 * AEC Error Handler
 *
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @package AEC Component
 */
class plgSystemAECerrorhandler extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgSystemAECerrorhandler( &$subject, $config )
	{
		parent::__construct( $subject, $config );
	}

	function onAfterRoute()
	{
		if ( strpos( JPATH_BASE, '/administrator' ) ) {
			// Don't act when on backend
			return true;
		}

		if ( file_exists( JPATH_ROOT."/components/com_acctexp/acctexp.class.php" ) ) {
			// handle login redirect
			$this->handleLoginRedirect();
		}
	}

	/**
	 * check if we are at the login page & there is a return URI set.
	 * if so, check if the return was to com_content (regarless of the view) & redirect to NotAllowed.
	 */
	function handleLoginRedirect()
	{
		$uri	= &JFactory::getURI();

		$task	= $uri->getVar( 'task' );
		$option	= $uri->getVar( 'option' );
		$view	= $uri->getVar( 'view' );
		$return = $uri->getVar( 'return' );

		if ( empty( $task ) ) {
			$task	= JRequest::getVar( 'task', null );
		}

		if ( empty( $option ) ) {
			$option	= JRequest::getVar( 'option', null );
		}

		if ( empty( $view ) ) {
			$view	= JRequest::getVar( 'view', null );
		}

		if ( empty( $return ) ) {
			$return = JRequest::getVar( 'return', '', 'method', 'base64' );

			$return = base64_decode( $return );

			if ( function_exists( 'JURI::isInternal' ) ) {
				if ( !JURI::isInternal( $return ) ) {
					$return = '';
				}
			} else {
				// Copied for pre-1.5.7 compatibility
				$uri =& JURI::getInstance($return);
				$base = $uri->toString(array('scheme', 'host', 'port', 'path'));
				$host = $uri->toString(array('scheme', 'host', 'port'));
				if ( ( strpos( strtolower($base), strtolower(JURI::base()) ) !== 0 ) && !empty($host) ) {
					$return = '';
				}
			}
		} else {
			$return = base64_decode( $return );
		}

		if (
				// If we are in a com_user(s) call
				( ( $option == 'com_user' ) || ( $option == 'com_users' ) )
				// And this is a login
				&& ( ( $view == 'login' ) || ( strpos( $task, 'login' ) != false ) )
				// Not a logout (yeah, really)
				&& ( strpos( $task, 'logout' ) == false )
				// And we have a blank return 
				&& !empty( $return ) && ( $return != 'index.php' )
				// With no username or password
				&& ( empty( $_REQUEST['username'] ) && empty( $_REQUEST['password'] ) )
		) {
			$uri = new JURI( $return );
			$option = $uri->getVar( 'option' );

			$cr = array( 'com_content', 'com_mailto', 'com_newsfeeds', 'com_poll', 'com_weblinks' );

			if ( in_array( $option, $cr ) || empty( $option ) ) {
				$error = new stdClass();
				$error->code = 403;

				$this->redirectNotAllowed( $error );
			}
		}
	}


	function redirectNotAllowed( $error )
	{
		if ( $error->code == 403 ) {
			$app = JFactory::getApplication();

			$app->redirect( JURI::base() . 'index.php?option=com_acctexp&task=NotAllowed' );
		} else {
			JError::customErrorPage( $error );
		}
	}

}

?>

<?php
/**
 * @version $Id: acctexp.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

global $aecConfig;

define( '_AEC_FRONTEND', 1 );

require_once( JPATH_SITE . '/components/com_acctexp/acctexp.class.php' );
require_once( JPATH_SITE . '/components/com_acctexp/acctexp.html.php' );

$user = &JFactory::getUser();

$task = trim( aecGetParam( 'view', '', true, array( 'word', 'string', 'clear_nonalnum' ) ) );

$testtask = trim( aecGetParam( 'task', '', true, array( 'word', 'string', 'clear_nonalnum' ) ) );

if ( !empty( $task ) && !empty( $testtask ) && ( $testtask != $task ) ) {
	$task = $testtask;
} elseif ( empty( $task ) && !empty( $testtask ) ) {
	$task = $testtask;
}

if ( empty( $option ) ) {
	$option = aecGetParam( 'option', '0' );
}

if ( empty( $task ) ) {
	// Regular mode - try to get the task
	$task = trim( aecGetParam( 'task', '', true, array( 'word', 'string', 'clear_nonalnum' ) ) );
} else {
	$params = &JComponentHelper::getParams( 'com_acctexp' );

	$menuitemid = JRequest::getInt( 'Itemid' );
	if ( $menuitemid ) {
		$menu = JSite::getMenu();
		$menuparams = $menu->getParams( $menuitemid );
		$params->merge( $menuparams );
	}

	$translate = array( 'usage', 'group', 'processor', 'intro', 'sub' );

	foreach ( $translate as $k ) {
		// Do not overwrite stuff that our forms supplied
		if ( !isset( $_REQUEST[$k] ) ) {
			$value = $params->get( $k );

			if ( !empty( $value ) ) {
				$_REQUEST[$k]	= $value;
				$_POST[$k]		= $value;
			}
		}
	}

	$layout = trim( aecGetParam( 'layout', '', true, array( 'word', 'string', 'clear_nonalnum' ) ) );

	if ( !empty( $layout ) ) {
		if ( $layout != 'default' ) {
			$task = $layout;
		}
	}
}

if ( !empty( $task ) ) {
	switch ( strtolower( $task ) ) {
		case 'heartbeat':
		case 'beat':
			// Manual Heartbeat
			$hash = aecGetParam( 'hash', 0, true, array( 'word', 'string' ) );

			$db = &JFactory::getDBO();

			$heartbeat = new aecHeartbeat();
			$heartbeat->frontendping( true, $hash );
			break;

		case 'register':
			$intro = aecGetParam( 'intro', 0, true, array( 'word', 'int' ) );
			$usage = aecGetParam( 'usage', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
			$group = aecGetParam( 'group', 0, true, array( 'word', 'int' ) );

			$iFactory = new InvoiceFactory();
			$iFactory->create( $option, $intro, $usage, $group );
			break;

		// Catch hybrid CB registration
		case 'saveregisters':
		// Catch hybrid jUser registration
		case 'saveuserregistration':
		// Catch hybrid CMS registration
		case 'saveregistration':
		case 'subscribe':
		case 'signup':
		case 'login':
			subscribe( $option );
			break;

		case 'usernameexists':
			$username = null;
			foreach ( $_REQUEST as $k => $v ) {
				if ( strpos( $k, '_username' ) ) {
					$username = aecGetParam( $k, 0, true, array( 'string', 'clear_nonalnumwhitespace' ) );
				}
			}

			if ( checkUsernameExists( $username ) ) {
				echo json_encode( JText::_( 'AEC_VALIDATE_USERNAME_EXISTS' ) );exit;
			} else {
				echo json_encode( true );exit;
			}
			break;

		case 'emailexists':
			$email = null;
			foreach ( $_REQUEST as $k => $v ) {
				if ( strpos( $k, '_email' ) ) {
					$email = aecGetParam( $k, 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
				}
			}

			if ( checkEmailExists( $email ) ) {
				echo json_encode( JText::_( 'AEC_VALIDATE_EMAIL_EXISTS' ) );exit;
			} else {
				echo json_encode( true );exit;
			}
			break;

		case 'confirm':
			confirmSubscription($option);
			break;

		case 'addressexception':
			JRequest::checkToken() or die( 'Invalid Token' );

			$invoice	= aecGetParam( 'invoice', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
			$cart		= aecGetParam( 'cart', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
			$userid		= aecGetParam( 'userid', 0 );

			if ( !empty( $user->id ) ) {
				$userid = $user->id;
			}

			repeatInvoice( $option, $invoice, $cart, $userid );
			break;

		case 'savesubscription':
			JRequest::checkToken() or die( 'Invalid Token' );

			$userid		= aecGetParam( 'userid', 0, true, array( 'word', 'int' ) );
			$usage		= aecGetParam( 'usage', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
			$group		= aecGetParam( 'group', 0, true, array( 'word', 'int' ) );
			$processor	= aecGetParam( 'processor', '', true, array( 'word', 'string', 'clear_nonalnum' ) );
			$coupon		= aecGetParam( 'coupon_code', '', true, array( 'word', 'string', 'clear_nonalnum' ) );

			$iFactory = new InvoiceFactory( $userid, $usage, $group, $processor );
			$iFactory->save( $option, $coupon );
			break;

		case 'addtocart':
			$userid			= aecGetParam( 'userid', 0, true, array( 'word', 'int' ) );
			$usage			= aecGetParam( 'usage', '', true, array( 'word', 'string', 'clear_nonalnum' ) );
			$returngroup	= aecGetParam( 'returngroup', '', true, array( 'word', 'int' ) );

			if ( !empty( $user->id ) ) {
				$userid = $user->id;
			}

			if ( !$user->id ) {
				getView( 'access_denied' );
			} else {
				$iFactory = new InvoiceFactory( $userid );
				$iFactory->addtoCart( $option, $usage, $returngroup );
			}
			break;

		case 'cart':
			$user = &JFactory::getUser();

			if ( !$user->id ) {
				getView( 'access_denied' );
			} else {
				$userid		= aecGetParam( 'userid', 0, true, array( 'word', 'int' ) );

				if ( !empty( $user->id ) ) {
					$userid = $user->id;
				}

				$iFactory = new InvoiceFactory( $userid );
				$iFactory->cart( $option );
			}
			break;

		case 'updatecart':
			JRequest::checkToken() or die( 'Invalid Token' );

			$userid		= aecGetParam( 'userid', 0, true, array( 'word', 'int' ) );

			if ( !empty( $user->id ) ) {
				$userid = $user->id;
			}

			if ( !$user->id ) {
				getView( 'access_denied' );
			} else {
				$iFactory = new InvoiceFactory( $userid );
				$iFactory->updateCart( $option, $_POST );
				$iFactory->cart( $option );
			}
			break;

		case 'clearcart':
			JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

			$userid		= aecGetParam( 'userid', 0, true, array( 'word', 'int' ) );

			if ( !empty( $user->id ) ) {
				$userid = $user->id;
			}

			if ( !$user->id ) {
				getView( 'access_denied' );
			} else {
				$iFactory = new InvoiceFactory( $userid );
				$iFactory->clearCart( $option );

				$iFactory = new InvoiceFactory( $userid );
				$iFactory->cart( $option );
			}
			break;

		case 'clearcartitem':
			JRequest::checkToken( 'get' ) or die( 'Invalid Token' );

			$item		= aecGetParam( 'item', 0, true, array( 'word', 'int' ) );

			if ( !empty( $user->id ) ) {
				$userid = $user->id;
			}

			if ( !$user->id ) {
				getView( 'access_denied' );
			} else {
				$iFactory = new InvoiceFactory( $userid );
				$iFactory->clearCartItem( $option, $item );

				$iFactory = new InvoiceFactory( $userid );
				$iFactory->cart( $option );
			}
			break;

		case 'confirmcart':
			JRequest::checkToken() or die( 'Invalid Token' );

			$userid		= aecGetParam( 'userid', 0, true, array( 'word', 'int' ) );
			$coupon		= aecGetParam( 'coupon_code', '', true, array( 'word', 'string', 'clear_nonalnum' ) );

			if ( !empty( $user->id ) ) {
				$userid = $user->id;
			}

			if ( !$user->id ) {
				getView( 'access_denied' );
			} else {
				$iFactory = new InvoiceFactory( $userid );
				$iFactory->confirmcart( $option, $coupon );
			}
			break;

		case 'checkout':
			$invoice	= aecGetParam( 'invoice', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
			$processor	= aecGetParam( 'processor', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
			$userid		= aecGetParam( 'userid', 0, true, array( 'word', 'int' ) );

			internalCheckout( $option, $invoice, $processor, $userid );
			break;

		case 'thanks':
			$renew		= aecGetParam( 'renew', 0, true, array( 'word', 'int' ) );
			$free		= aecGetParam( 'free', 0, true, array( 'word', 'int' ) );

			$usage		= aecGetParam( 'usage', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );

			if ( empty( $usage ) ) {
				$usage = aecGetParam( 'u', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
			}

			$iFactory = new InvoiceFactory();

			if ( !empty( $usage ) ) {
				$db = &JFactory::getDBO();

				$plan = new SubscriptionPlan();
				$plan->load( $usage );
				
				getView( 'thanks', array( 'renew' => $renew, 'free' => $free, 'plan' => $plan ) );
			} else {
				getView( 'thanks', array( 'renew' => $renew, 'free' => $free ) );
			}
			break;

		case 'subscriptiondetails':
			$sub		= aecGetParam( 'sub', 'overview', true, array( 'word', 'string' ) );
			$page		= aecGetParam( 'page', '0', true, array( 'word', 'int' ) );

			getView( 'subscriptiondetails', array( 'sub' => $sub, 'page' => $page ) );
			break;

		case 'renewsubscription':
			JRequest::checkToken() or die( 'Invalid Token' );

			$userid		= aecGetParam( 'userid', 0, true, array( 'word', 'int' ) );
			$intro		= aecGetParam( 'intro', 0, true, array( 'word', 'int' ) );
			$usage		= aecGetParam( 'usage', 0, true, array( 'word', 'int' ) );

			$iFactory = new InvoiceFactory( $userid );
			if ( $iFactory->checkAuth( $option ) ) {
				$iFactory->create( $option, $intro, $usage );
			}
			break;

		case 'repeatpayment':
			$invoice	= aecGetParam( 'invoice', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
			$userid		= aecGetParam( 'userid', 0 );
			$first		= aecGetParam( 'first', 0 );

			repeatInvoice( $option, $invoice, null, $userid, $first );
			break;

		case 'cancelpayment':
			$invoice	= aecGetParam( 'invoice', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
			$pending	= aecGetParam( 'pending', 0 );
			$userid		= aecGetParam( 'userid', 0 );
			$return		= aecGetParam( 'return', 0 );

			cancelInvoice( $option, $invoice, $pending, $userid, $return );
			break;

		case 'planaction':
			$action	= aecGetParam( 'action', 0, true, array( 'word', 'string' ) );
			$subscr	= aecGetParam( 'subscr', '', true, array( 'word', 'int' ) );

			planaction( $option, $action, $subscr );
			break;

		case 'invoiceprint':
			$invoice	= aecGetParam( 'invoice', '', true, array( 'word', 'string', 'clear_nonalnum' ) );

			$iFactory = new InvoiceFactory( $user->id );
			$iFactory->invoiceprint( $option, $invoice );
			break;

		case 'invoicepdf':
			$invoice	= aecGetParam( 'invoice', '', true, array( 'word', 'string', 'clear_nonalnum' ) );

			InvoicePDF( $option, $invoice );
			break;
			
		case 'invoiceaction':
			$action		= aecGetParam( 'action', 0, true, array( 'word', 'string' ) );
			$invoice	= aecGetParam( 'invoice', '', true, array( 'word', 'string', 'clear_nonalnum' ) );

			invoiceAction( $option, $action, $invoice );
			break;

		case 'invoicemakegift':
			JRequest::checkToken() or die( 'Invalid Token' );

			InvoiceMakeGift( $option );
			break;

		case 'invoiceremovegift':
			JRequest::checkToken() or die( 'Invalid Token' );

			InvoiceRemoveGift( $option );
			break;

		case 'invoiceremovegiftcart':
			JRequest::checkToken() or die( 'Invalid Token' );

			InvoiceRemoveGiftCart( $option );
			break;

		case 'invoiceremovegiftconfirm':
			JRequest::checkToken() or die( 'Invalid Token' );

			InvoiceRemoveGiftConfirm( $option );
			break;

		case 'invoiceaddcoupon':
			JRequest::checkToken() or die( 'Invalid Token' );

			InvoiceAddCoupon( $option );
			break;

		case 'invoiceremovecoupon':
			InvoiceRemoveCoupon( $option );
			break;

		case 'invoiceaddparams':
			JRequest::checkToken() or die( 'Invalid Token' );

			InvoiceAddParams( $option );
			break;

		// Legacy - to be deprecated after thorough check
		case 'ipn':
			processNotification( $option, "paypal" );
			break;

		case 'api':
			$app		= aecGetParam( 'app', 0, true, array( 'word', 'string' ) );
			$key		= aecGetParam( 'key', 0, true, array( 'word', 'string' ) );
			$request	= aecGetParam( 'request' );

			apiCall( $app, $key, $request );
			break;

		case 'notallowed':
			$task = 'access_denied';

		default:
			if ( strpos( $task, 'notification' ) > 0 ) {
				$processor = str_replace( 'notification', '', $task );

				processNotification( $option, $processor );
			} else {
				getView( $task );
			}
			break;
	}
}

function subscribe( $option )
{
	global $aecConfig;

	$db = &JFactory::getDBO();

	$user = &JFactory::getUser();

	if ( defined( 'JPATH_MANIFESTS' ) && !empty( $_REQUEST['jform'] ) ) {
		foreach ( $_REQUEST['jform'] as $k => $v ) {
			$map = array( 'password1' => 'password', 'email1' => 'email' );
			
			if ( isset( $map[$k] ) ) {
				$_POST[$map[$k]] = $v;
			} else {
				$_POST[$k] = $v;
			}
		}
	}

	$intro		= aecGetParam( 'intro', 0, true, array( 'word', 'int' ) );
	$usage		= aecGetParam( 'usage', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
	$group		= aecGetParam( 'group', 0, true, array( 'word', 'int' ) );
	$processor	= aecGetParam( 'processor', '', true, array( 'word', 'string', 'clear_nonalnum' ) );
	$userid		= aecGetParam( 'userid', 0, true, array( 'word', 'int' ) );
	$username	= aecGetParam( 'username', '', true, array( 'string', 'clear_nonalnumwhitespace' ) );
	$email		= aecGetParam( 'email', '', true, array( 'string', 'clear_nonemail' ) );

	$token		= aecGetParam( 'aectoken', 0, true, array( 'string' ) );

	$forget		= aecGetParam( 'forget', '', true, array( 'string' ) );

	$k2mode		= false;

	if ( $token ) {
		$temptoken = new aecTempToken();
		$temptoken->getComposite();

		if ( !empty( $temptoken->content['handler'] ) ) {
			if ( $temptoken->content['handler'] == 'k2' ) {
				$k2mode = true;
			}
		}

		if ( !empty( $temptoken->content ) ) {
			$password = null;

			$details = array();

			if ( $forget == 'usage' ) {
				$details[] = 'usage';
				$details[] = 'processor';
				$details[] = 'recurring';
			}

			if ( $forget == 'userdetails' ) {
				$details[] = 'username';
				$details[] = 'email';
				$details[] = 'password';
				$details[] = 'password2';
			}

			foreach ( $temptoken->content as $k => $v ) {
				if ( !in_array( $k, $details ) ) {
					$$k = $v;

					$_POST[$k] = $v;
				}
			}

			if ( !empty( $username ) ) {
				$query = 'SELECT id'
				. ' FROM #__users'
				. ' WHERE username = \'' . $username . '\''
				;
				$db->setQuery( $query );
				$id = $db->loadResult();

				if ( !empty( $id ) ) {
					$userid = $id;

					$metaUser = new metaUser( $id );
					$metaUser->setTempAuth( $password );
				}
			}
		}
	}

	if ( !empty( $username ) && $usage ) {
		$CB = ( GeneralInfoRequester::detect_component( 'anyCB' ) );
		$AL = ( GeneralInfoRequester::detect_component( 'ALPHA' ) );
		$JS = ( GeneralInfoRequester::detect_component( 'JOMSOCIAL' ) );

		if ( !$AL && !$CB && !$JS && !$k2mode ) {
			// Joomla 1.6+ Sanity Check
			if ( isset($_POST['email2']) && isset($_POST['email']) ) {
				if ( $_POST['email2'] !== $_POST['email'] ) {
					aecErrorAlert( JText::_( 'AEC_WARNREG_EMAIL_NOMATCH' ) );
					return JText::_( 'AEC_WARNREG_EMAIL_NOMATCH' );
				}
			}

			if ( isset($_POST['password2']) && isset($_POST['password']) ) {
				if ( $_POST['password2'] !== $_POST['password'] ) {
					aecErrorAlert( JText::_( 'AEC_WARNREG_PASSWORD_NOMATCH' ) );
					return JText::_( 'AEC_WARNREG_PASSWORD_NOMATCH' );
				}
			}

			// Joomla 1.5 Sanity Check

			// Get required system objects
			$user 		= clone(JFactory::getUser());

			$duplicationcheck = checkUsernameEmail( $username, $email );

			// Bind the post array to the user object
			if ( !$user->bind( JRequest::get('post'), 'usertype' ) || ( $duplicationcheck !== true ) ) {
				$binderror = $user->getError();

				if ( !empty( $binderror ) ) {
					JError::raiseError( 500, $user->getError() );
				} else {
					JError::raiseError( 500, $duplicationcheck );
				}

				unset($_POST);
				subscribe();
				return false;
			}

			JRequest::checkToken() or die( 'Invalid Token' );
		} elseif ( empty( $token ) ) {
			if ( isset( $_POST['username'] ) && isset( $_POST['email'] ) ) {
				$check = checkUsernameEmail( $username, $email );
				if ( $check !== true ) {
					return $check;
				}
			}
		}

		$iFactory = new InvoiceFactory( $userid, $usage, $group, $processor );
		$iFactory->confirm( $option );
	} else {
		if ( $user->id ) {
			$userid			= $user->id;
			$passthrough	= array();
		} elseif ( !empty( $userid ) && !isset( $_POST['username'] ) ) {
			$passthrough	= array();
		} elseif ( empty( $userid ) ) {
			if ( !empty( $_POST['username'] ) && !empty( $_POST['email'] ) ) {
				$check = checkUsernameEmail( $username, $email );
				if ( $check !== true ) {
					return $check;
				}
			}

			$nopass = array( 'option', 'task', 'intro', 'usage', 'group', 'processor', 'recurring', 'Itemid', 'submit_x', 'submit_y', 'userid', 'id', 'gid' );

			$passthrough = array();
			foreach ( $_POST as $k => $v ) {
				if ( in_array( $k, $nopass ) ) {
					unset( $_POST[$k] );
				} else {
					$passthrough[$k] = $v;
				}
			}
		}

		if ( !empty( $userid ) ) {
			$passthrough['userid'] = $userid;

			$password = aecGetParam( 'password', '', true, array( 'string' ) );

			if ( !empty( $password ) ) {
				$passthrough['password'] = $password;
			}
		}

		$iFactory = new InvoiceFactory( $userid, $usage, $group, $processor, null, $passthrough, false );

		if ( !$iFactory->authed ) {
			if ( !$iFactory->checkAuth( $option ) ) {
				return;
			}
		}

		if ( !empty( $iFactory->passthrough['invoice'] ) ) {
			repeatInvoice( $option, $iFactory->passthrough['invoice'], null, $userid );
		} else {
			$iFactory->create( $option, $intro, $usage, $group, $processor, 0 );
		}
	}
}

function checkUsernameEmail( $username, $email )
{
	// Implementing the Javascript check in case that is broken on the site
	$regex = preg_match( "#[<>\"'%;()&]#i", $username );

	if ( ( strlen( $username ) < 2 ) || $regex ) {
		aecErrorAlert( JText::_( 'AEC_VALIDATE_ALPHANUMERIC' ) );
		return JText::_( 'AEC_VALIDATE_ALPHANUMERIC' );
	}

	if ( checkUsernameExists( $username ) ) {
		aecErrorAlert( JText::_( 'AEC_VALIDATE_USERNAME_EXISTS' ) );
		return JText::_( 'AEC_VALIDATE_USERNAME_EXISTS' );
	}

	if ( !empty( $email ) ) {
		if ( checkEmailExists( $email ) ) {
			aecErrorAlert( JText::_( 'AEC_VALIDATE_EMAIL_EXISTS' ) );
			return JText::_( 'AEC_VALIDATE_EMAIL_EXISTS' );
		}
	}

	return true;
}

function checkUsernameExists( $username )
{
	$db = &JFactory::getDBO();

	$query = 'SELECT `id`'
			. ' FROM #__users'
			. ' WHERE `username` = \'' . $username . '\''
			;
	$db->setQuery( $query );

	return $db->loadResult() ? true : false;
}

function checkEmailExists( $email )
{
	$db = &JFactory::getDBO();

	$query = 'SELECT `id`'
			. ' FROM #__users'
			. ' WHERE `email` = \'' . $email . '\''
			;
	$db->setQuery( $query );

	return $db->loadResult() ? true : false;
}

function confirmSubscription( $option )
{
	$user = &JFactory::getUser();

	global $aecConfig;

	$app = JFactory::getApplication();

	$userid		= aecGetParam( 'userid', 0, true, array( 'word', 'int' ) );
	$usage		= aecGetParam( 'usage', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
	$group		= aecGetParam( 'group', 0, true, array( 'word', 'int' ) );
	$processor	= aecGetParam( 'processor', '', true, array( 'word', 'string', 'clear_nonalnum' ) );
	$username	= aecGetParam( 'username', '', true, array( 'string', 'clear_nonalnumwhitespace' ) );

	$passthrough = array();
	if ( isset( $_POST['aec_passthrough'] ) ) {
		if ( is_array( $_POST['aec_passthrough'] ) ) {
			$passthrough = $_POST['aec_passthrough'];
		} else {
			$passthrough = unserialize( base64_decode( $_POST['aec_passthrough'] ) );
		}
	}

	if ( $aecConfig->cfg['plans_first'] && !empty( $usage ) && empty( $username ) && empty( $passthrough['username'] ) && !$userid && !$user->id  && empty( $aecConfig->cfg['skip_registration'] ) ) {
		if ( GeneralInfoRequester::detect_component( 'anyCB' ) ) {
			// This is a CB registration, borrowing their code to register the user
			include_once( JPATH_SITE . '/components/com_comprofiler/comprofiler.html.php' );
			include_once( JPATH_SITE . '/components/com_comprofiler/comprofiler.php' );

			registerForm( $option, $app->getCfg( 'emailpass' ), null );
		} else {
			// This is a joomla registration
			joomlaregisterForm( $option, $app->getCfg( 'useractivation' ) );
		}
	} else {
		if ( !empty( $usage ) ) {
			$iFactory = new InvoiceFactory( $userid, $usage, $group, $processor );
			$iFactory->confirm( $option );
		} else {
			subscribe( $option );
		}
	}
}

function internalCheckout( $option, $invoice_number, $processor, $userid )
{
	$db = &JFactory::getDBO();

	$user = &JFactory::getUser();

	// Always rewrite to session userid
	if ( !empty( $user->id ) ) {
		$userid = $user->id;
	}

	$invoiceid = AECfetchfromDB::InvoiceIDfromNumber( $invoice_number, $userid );

	// Only allow a user to access existing and own invoices
	if ( $invoiceid ) {
		$iFactory = new InvoiceFactory( $userid, null, null, $processor );
		$iFactory->touchInvoice( $option, $invoice_number );
		$iFactory->internalcheckout( $option );
	} else {
		getView( 'access_denied' );
		return;
	}
}

function repeatInvoice( $option, $invoice_number, $cart, $userid, $first=0 )
{
	$db = &JFactory::getDBO();

	$user = &JFactory::getUser();

	// Always rewrite to session userid
	if ( !empty( $user->id ) ) {
		$userid = $user->id;
	} elseif ( AECToolbox::quickVerifyUserID( $userid ) === true ) {
			// This user is not expired, so he could log in...
			return getView( 'access_denied' );
	} else {
		$userid = AECfetchfromDB::UserIDfromInvoiceNumber( $invoice_number );
	}

	$invoiceid = null;

	if ( empty( $cart ) ) {
		$invoiceid = AECfetchfromDB::InvoiceIDfromNumber( $invoice_number, $userid );
	}

	// Only allow a user to access existing and own invoices
	if ( $invoiceid ) {
		global $aecConfig;

		if ( !isset( $_POST['invoice'] ) ) {
			$_POST['option']	= $option;
			$_POST['task']		= 'repeatPayment';
			$_POST['invoice']	= $invoice_number;
			$_POST['userid']	= $userid;
		}

		$iFactory = new InvoiceFactory( $userid );
		$iFactory->touchInvoice( $option, $invoice_number );
		$iFactory->loadProcessorObject();

		$status = $iFactory->usageStatus();

		if ( $status || ( !$status && $aecConfig->cfg['allow_invoice_unpublished_item'] ) ) {
			if ( !$iFactory->checkAuth( $option ) ) {
				return getView( 'access_denied' );
			}
		} else {
			return getView( 'access_denied' );
		}

		return $iFactory->save( $option, null );
	} elseif ( $cart ) {
		$iFactory = new InvoiceFactory( $userid );

		$iFactory->usage = 'c.'.$cart;

		if ( !empty( $invoice_number ) ) {
			$iFactory->invoice_number = $invoice_number;
		}

		return $iFactory->confirmcart( $option, null, true );
	} else {
		return getView( 'access_denied' );
	}
}

function cancelInvoice( $option, $invoice_number, $pending=0, $userid, $return=null )
{
	$db = &JFactory::getDBO();

	$user = &JFactory::getUser();

	if ( empty( $user->id ) ) {
		if ( $userid ) {
			if ( AECToolbox::quickVerifyUserID( $userid ) === true ) {
				// This user is not expired, so he could log in...
				return getView( 'access_denied' );
			}
		} else {
			return getView( 'access_denied' );
		}
	} else {
		$userid = $user->id;
	}

	$invoiceid = AECfetchfromDB::InvoiceIDfromNumber( $invoice_number, $userid );

	// Only allow a user to access existing and own invoices
	if ( $invoiceid ) {
		$objInvoice = new Invoice();
		$objInvoice->load( $invoiceid );

		$objInvoice->cancel();
	} else {
		getView( 'access_denied' );
		return;
	}

	if ( $pending ) {
		getView( 'pending' );
	} else {
		if ( !empty( $return ) ) {
			aecRedirect( base64_decode( $return ) );
		} else {
			getView( 'subscriptiondetails', array( 'sub' => 'invoices' ) );
		}
	}

}

function planaction( $option, $action, $subscr )
{
	$db = &JFactory::getDBO();

	$user = &JFactory::getUser();

	if ( !empty( $user->id ) ) {
		$userid = $user->id;

		$iFactory = new InvoiceFactory( $userid );
		$iFactory->planprocessoraction( $action, $subscr );

		getView( 'subscriptiondetails', array( 'sub' => 'invoices' ) );
	} else {
		getView( 'access_denied' );
		return;
	}
}

function invoiceAction( $option, $action, $invoice_number )
{
	$user = &JFactory::getUser();

	if ( empty( $user->id ) ) {
		return getView( 'access_denied' );
	} else {
		$iFactory = new InvoiceFactory( $user->id );
		$iFactory->touchInvoice( $option, $invoice_number );
		$iFactory->invoiceprocessoraction( $option, $action );

		getView( 'subscriptiondetails', array( 'sub' => 'invoices' ) );
	}
}

function InvoicePrintout( $option, $invoice, $standalone=true )
{
	$user = &JFactory::getUser();

	if ( empty( $user->id ) ) {
		return getView( 'access_denied' );
	} else {
		$iFactory = new InvoiceFactory( $user->id );
		$iFactory->invoiceprint( $option, $invoice, $standalone );
	}
}

function InvoicePDF( $option, $invoice )
{
	$user = &JFactory::getUser();

	if ( empty( $user->id ) ) {
		return getView( 'access_denied' );
	} else {
		require_once( JPATH_SITE . '/components/com_acctexp/lib/tcpdf/config/lang/eng.php' );
		require_once( JPATH_SITE . '/components/com_acctexp/lib/tcpdf/tcpdf.php' );

		ob_start();

		InvoicePrintout( $option, $invoice, false );

		$content = ob_get_contents();

		ob_end_clean();

		$document=& JFactory::getDocument();
		$document->_type="html";
		$renderer = $document->loadRenderer("head");

		$content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
					.'<html xmlns="http://www.w3.org/1999/xhtml">'
					.'<head>' . $renderer->render() . '</head><body>'.$content.'</body>'
					.'</html>';

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$pdf->AddPage();
		$pdf->writeHTML($content, true, false, true, false, '');

		$pdf->Output( $invoice.'.pdf', 'I');exit;
	}
}

function InvoiceAddParams( $option )
{
	$db = &JFactory::getDBO();

	$invoice = aecGetParam( 'invoice', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );

	$objinvoice = new Invoice();
	$objinvoice->loadInvoiceNumber( $invoice );
	$objinvoice->savePostParams( $_POST );
	$objinvoice->check();
	$objinvoice->store();

	repeatInvoice( $option, $invoice, null, $objinvoice->userid );
}

function InvoiceMakeGift( $option )
{
	$db = &JFactory::getDBO();

	$invoice	= aecGetParam( 'invoice', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
	$user_ident	= aecGetParam( 'user_ident', 0, true, array( 'string', 'clear_nonemail' ) );

	unset( $_POST['user_ident'] );
	unset( $_REQUEST['user_ident'] );

	$objinvoice = new Invoice();
	$objinvoice->loadInvoiceNumber( $invoice );

	$iFactory = new InvoiceFactory( $objinvoice->userid );
	$iFactory->touchInvoice( $option, $objinvoice->invoice_number );

	if ( $iFactory->invoice->addTargetUser( strtolower( $user_ident ) ) ) {
		$iFactory->invoice->storeload();
	}

	repeatInvoice( $option, $invoice, null, $objinvoice->userid );
}

function InvoiceRemoveGift( $option )
{
	$db = &JFactory::getDBO();

	$invoice	= aecGetParam( 'invoice', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );

	$objinvoice = new Invoice();
	$objinvoice->loadInvoiceNumber( $invoice );

	$iFactory = new InvoiceFactory( $objinvoice->userid );
	$iFactory->touchInvoice( $option, $objinvoice->invoice_number );

	if ( $iFactory->invoice->removeTargetUser() ) {
		$iFactory->invoice->storeload();
	}

	repeatInvoice( $option, $invoice, null, $objinvoice->userid );
}

function InvoiceRemoveGiftConfirm( $option )
{
	$db = &JFactory::getDBO();

	$invoice	= aecGetParam( 'invoice', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
	$userid		= aecGetParam( 'userid', 0, true, array( 'word', 'int' ) );
	$usage		= aecGetParam( 'usage', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
	$group		= aecGetParam( 'group', 0, true, array( 'word', 'int' ) );
	$processor	= aecGetParam( 'processor', '', true, array( 'word', 'string', 'clear_nonalnum' ) );
	$username	= aecGetParam( 'username', 0, true, array( 'string', 'clear_nonalnumwhitespace' ) );

	$objinvoice = new Invoice();
	$objinvoice->loadInvoiceNumber( $invoice );

	if ( $objinvoice->removeTargetUser() ) {
		$objinvoice->storeload();
	}

	$iFactory = new InvoiceFactory( $userid, $usage, $group, $processor, $invoice );
	$iFactory->confirm( $option, $_POST );
}

function InvoiceRemoveGiftCart( $option )
{
	$db = &JFactory::getDBO();

	$invoice	= aecGetParam( 'invoice', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
	$userid		= aecGetParam( 'userid', 0, true, array( 'word', 'int' ) );

	$objinvoice = new Invoice();
	$objinvoice->loadInvoiceNumber( $invoice );

	$iFactory = new InvoiceFactory( $objinvoice->userid );
	$iFactory->touchInvoice( $option, $objinvoice->invoice_number );

	if ( $iFactory->invoice->removeTargetUser() ) {
		$iFactory->invoice->storeload();
	}

	$iFactory = new InvoiceFactory( $userid );
	$iFactory->cart( $option );
}

function InvoiceAddCoupon( $option )
{
	$db = &JFactory::getDBO();

	$invoice		= aecGetParam( 'invoice', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
	$coupon_code	= aecGetParam( 'coupon_code', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );

	$objinvoice = new Invoice();
	$objinvoice->loadInvoiceNumber( $invoice );

	$objinvoice->addCoupon( $coupon_code );

	$objinvoice->storeload();

	repeatInvoice( $option, $invoice, null, $objinvoice->userid );
}

function InvoiceRemoveCoupon( $option )
{
	$db = &JFactory::getDBO();

	$invoice		= aecGetParam( 'invoice', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );
	$coupon_code	= aecGetParam( 'coupon_code', 0, true, array( 'word', 'string', 'clear_nonalnum' ) );

	$objinvoice = new Invoice();
	$objinvoice->loadInvoiceNumber( $invoice );

	$objinvoice->removeCoupon( $coupon_code );

	$objinvoice->computeAmount();

	repeatInvoice( $option, $invoice, null, $objinvoice->userid );
}

function processNotification( $option, $processor )
{
	// Legacy naming support
	switch ( $processor ) {
		case 'vklix':
			$processor = 'viaklix';
			break;
		case 'auth':
			$processor = 'authorize';
			break;
		case '2co':
			$processor = '2checkout';
			break;
		case 'eps':
			$processor = 'epsnetpay';
			break;
	}

	//aecDebug( "ResponseFunction:processNotification" );aecDebug( $_GET );aecDebug( $_POST );
	$response = array();
	$response['fullresponse'] = aecPostParamClear( $_POST );

	// parse processor notification
	$pp = new PaymentProcessor();
	if ( $pp->loadName( $processor ) ) {
		$pp->init();
		$response = array_merge( $response, $pp->parseNotification( $response['fullresponse'] ) );
	} else {
		$eventlog = new eventLog();
		$eventlog->issue(	'processor loading failure',
							'processor,loading,error',
							'When receiving payment notification, tried to load processor: ' . $processor,
							128
						);

		return;
	}

	// Get Invoice record
	if ( !empty( $response['invoice'] ) ) {
		$id = AECfetchfromDB::InvoiceIDfromNumber( $response['invoice'] );
	} else {
		$id = false;

		$response['invoice'] = 'empty';
	}

	if ( !$id ) {
		$short	= JText::_('AEC_MSG_PROC_INVOICE_FAILED_SH');

		if ( isset( $response['null'] ) ) {
			if ( isset( $response['explanation'] ) ) {
				$short	= JText::_('AEC_MSG_PROC_INVOICE_ACTION_SH');

				$event .= $response['explanation'];
			} else {
				$event	.= JText::_('AEC_MSG_PROC_INVOICE_ACTION_EV_NULL');
			}

			$tags	.= 'invoice,processor,payment,null';
		} else {
			$event	= sprintf( JText::_('AEC_MSG_PROC_INVOICE_FAILED_EV'), $processor, $response['invoice'] );
			$tags	= 'invoice,processor,payment,error';
		}

		$params = array();

		$eventlog = new eventLog();

		if ( isset( $response['null'] ) ) {
			if ( isset( $response['error'] ) ) {
				$eventlog->issue( $short, $tags, $response['error'], 128, $params );
			} else {
				$eventlog->issue( $short, $tags, $event, 8, $params );
			}
		} else {
			$eventlog->issue( $short, $tags, $event, 128, $params );

			$error = 'Invoice Number not found. Invoice number provided: "' . $response['invoice'] . '"';

			$pp->notificationError( $response, $error );
		}

		return;
	} else {
		$iFactory = new InvoiceFactory( null, null, null, null, $response['invoice'] );
		$iFactory->processorResponse( $option, $response );
	}
}

function aecErrorAlert( $text, $action='window.history.go(-1);', $mode=1 )
{
	$app = JFactory::getApplication();

	$text = strip_tags( addslashes( nl2br( $text ) ) );

	switch ( $mode ) {
		case 2:
			echo "<script>$action</script> \n";
			break;

		case 1:
		default:
			echo "<script>alert('$text'); $action</script> \n";
			echo '<noscript>';
			echo "$text\n";
			echo '</noscript>';
			break;
	}

	$app->close();
}

function aecNotAuth() { getView( 'access_denied' ); }

?>

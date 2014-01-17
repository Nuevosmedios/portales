<?php
/**
 * @version $Id: acctexp.api.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

function apiCall( $app, $key, $request )
{
	global $aecConfig;

	if ( empty( $aecConfig->cfg['apiapplist'] ) ) {
		header("HTTP/1.0 401 Unauthorized"); die; // die, die
	}

	if ( isset( $aecConfig->cfg['apiapplist'][$app] ) ) {
		if ( trim($key) == trim($aecConfig->cfg['apiapplist'][$app]) ) {
			if ( empty( $request ) ) {
				header( "HTTP/1.0 400 Bad Request" ); die;
			}

			if ( get_magic_quotes_gpc() ) {
				$request = stripslashes( $request );
			}

			$req = json_decode( $request );

			if ( is_null( $request ) ) {
				header( "HTTP/1.0 415 Unsupported Media Type" ); die;
			}

			if ( !is_array($req) ) {
				$req = array( $req );
			}

			header( "HTTP/1.0 200 OK" );

			$api = new aecAPI();

			$return = array();
			foreach ( $req as $r ) {
				$api->load( $r );

				$r = new stdClass();
				$r->response	= new stdClass();
				$r->error		= null;

				if ( empty( $api->error ) ) {
					$api->resolve();

					$r->response	= $api->response;
				} else {
					$r->response->result = false;
				}

				$r->error	= $api->error;

				$return[] = $r;
			}

			if ( count( $return ) == 1 ) {
				$return = $return[0];
			}

			echo json_encode( $return ); die; // regular die
		}
	}

	header("HTTP/1.0 401 Unauthorized"); die; // die, die
}

class aecAPI
{
	var $request	= '';
	var $metaUser	= '';
	var $focus		= '';
	var $error		= '';
	var $response	= '';

	function load( $request )
	{
		$this->request = $request;

		if ( !empty( $this->request->action ) ) {
			$this->action = $this->request->action;
		} else {
			$this->error = 'action missing or empty';
		}

		if ( !empty( $this->request->user ) ) {
			$this->loadUser();
		} else {
			$this->error = 'user missing or empty';
		}

		$this->response = new stdClass();
	}

	function loadUser()
	{
		$users = array();

		if ( is_object( $this->request->user ) ) {
			$db = &JFactory::getDBO();

			if ( isset( $this->request->user->username ) ) {
				$query = 'SELECT `id`'
						. ' FROM #__users'
						. ' WHERE LOWER( `username` ) LIKE \'%' . xJ::escape( $db, strtolower( $this->request->user->username ) ) . '%\''
						;
				$db->setQuery( $query );

				$users = xJ::getDBArray( $db );
			}

			if ( empty( $users ) && isset( $this->request->user->name ) ) {
				$query = 'SELECT `id`'
						. ' FROM #__users'
						. ' WHERE LOWER( `name` ) LIKE \'%' . xJ::escape( $db, strtolower( $this->request->user->name ) ) . '%\''
						;
				$db->setQuery( $query );

				$users = xJ::getDBArray( $db );
			}

			if ( empty( $users ) && isset( $this->request->user->email ) ) {
				$query = 'SELECT `id`'
						. ' FROM #__users'
						. ' WHERE LOWER( `email` ) = \'' . xJ::escape( $db, $this->request->user->email ) . '\''
						;
				$db->setQuery( $query );

				$users = xJ::getDBArray( $db );
			}

			if ( empty( $users ) && isset( $this->request->user->userid ) ) {
				$query = 'SELECT `id`'
						. '  FROM #__users'
						. ' WHERE `id` = \'' . xJ::escape( $db, $this->request->user->userid ) . '\''
						;
				$db->setQuery( $query );

				$users = xJ::getDBArray( $db );
			}

			if ( empty( $users ) && isset( $this->request->user->invoice_number ) ) {
				$query = 'SELECT `userid`'
						. 'FROM #__acctexp_invoices'
						. ' WHERE LOWER( `invoice_number` ) = \'' . xJ::escape( $db, $this->request->user->invoice_number ) . '\''
						. ' OR LOWER( `secondary_ident` ) = \'' . xJ::escape( $db, $this->request->user->invoice_number ) . '\''
						;
				$db->setQuery( $query );

				$users = xJ::getDBArray( $db );
			}
		} else {
			$users = AECToolbox::searchUser( $this->request->user );
		}

		if ( !count( $users ) ) {
			$this->error = 'user not found';
		} elseif ( count( $users ) > 1 ) {
			$this->error = 'multiple users found';
		} else {
			if ( !empty( $this->metaUser->userid ) ) {
				if ( $this->metaUser->userid != $users[0] ) {
					$this->metaUser = new metaUser( $users[0] );
				}
			} else {
				$this->metaUser = new metaUser( $users[0] );
			}
		}
	}

	function resolve()
	{
		$cmd = 'action' . $this->action;

		if ( method_exists( $this, $cmd ) ) {
			$this->$cmd();
		} else {
			$this->error = 'chosen action ' . $cmd . ' does not exist - check spelling (especially upper- and lowercase)';
		}
	}

	function actionUserExists()
	{
		$this->response->result = !empty( $this->metaUser->userid );
	}

	function actionMembershipDetails()
	{
		$this->actionUserExists();

		$this->response->status = AECToolbox::VerifyMetaUser( $this->metaUser );

		if ( $this->response->status === true ) {
			$this->response->status = $this->metaUser->objSubscription->status;
		}

		switch ( strtolower( $this->response->status ) ) {
			case 'active':			$this->response->status_long = 'Account is fine.'; break;
			case 'trial':			$this->response->status_long = 'Account is fine (using a trial right now).'; break;
			case 'expired':			$this->response->status_long = 'Account has expired.'; break;
			case 'pending':			$this->response->status_long = 'Account is pending - awaiting payment for the last invoice to clear.'; break;
			case 'open_invoice':	$this->response->status_long = 'Account is pending - there is an open invoice waiting to be paid.'; break;
			case 'hold':			$this->response->status_long = 'Account is on manual hold.'; break;
			default:				$this->response->status_long = 'No long status explanation for this.'; break;
		}

		if ( !empty( $this->request->details ) ) {
			if ( !is_object( $this->request->details ) ) {
				$this->error = 'details need to be an objects (with "key" and "value" as properties)';
			} else {
				$details = get_object_vars( $this->request->details );

				foreach ( $details as $k => $v ) {
					if ( empty( $k ) || empty( $v ) ) {
						$this->error = 'one or more details empty or malformed';
					} else {
						$this->response->$k = AECToolbox::rewriteEngineRQ( '{aecjson}'.json_encode($v).'{/aecjson}', null, $this->metaUser );
					}
				}
			}
		}
	}

	function actionAuth()
	{
		if ( empty( $this->request->user->username ) || empty( $this->request->user->password ) ) {
			$this->error = 'must provide username and password to authenticate';

			$this->response->result =  false;

			return;
		}

		$credentials = array();
		$credentials['username'] = $this->request->user->username;
		$credentials['password'] = $this->request->user->password;

		// Get the global JAuthentication object.
		jimport('joomla.user.authentication');

		$authenticate = JAuthentication::getInstance();
		$response	= $authenticate->authenticate($credentials, array());

		$this->response->result = ( $response->status === JAUTHENTICATE_STATUS_SUCCESS );
	}

	function actionRestrictionCheck()
	{
		$this->response->result = false;

		if ( !empty( $this->request->details->plan ) ) {
			$plan = new SubscriptionPlan();
			$plan->load( $this->request->details->plan );

			if ( $plan->id != $this->request->details->plan ) {
				$this->error = 'could not find plan to check restrictions for';

				return;
			}

			$restrictions = $plan->getRestrictionsArray();

			if ( aecRestrictionHelper::checkRestriction( $restrictions, $this->metaUser ) !== false ) {
				if ( !ItemGroupHandler::checkParentRestrictions( $plan, 'item', $this->metaUser ) ) {
					$this->error = 'user is denied permission - plans parent group is restricted from this user';
				}
			} else {
				$this->error = 'user is denied permission - plan is restricted from this user';
			}
			
			unset( $this->request->details->plan );
		}

		if ( !empty( $this->request->details->group ) ) {
			$group = new ItemGroup();
			$group->load( $this->request->details->group );

			if ( $group->id != $this->request->details->group ) {
				$this->error = 'could not find group to check restrictions for';

				return;
			}

			$restrictions = $group->getRestrictionsArray();

			if ( aecRestrictionHelper::checkRestriction( $restrictions, $this->metaUser ) !== false ) {
				if ( !ItemGroupHandler::checkParentRestrictions( $group, 'group', $this->metaUser ) ) {
					$this->error = 'user is denied permission - groups parent group is restricted from this user';
				}
			} else {
				$this->error = 'user is denied permission - group is restricted from this user';
			}
			
			unset( $this->request->details->group );
		}


		if ( !empty( $this->request->details ) ) {
			$re = get_object_vars( $this->request->details );

			$restrictions = aecRestrictionHelper::getRestrictionsArray( $re );

			if ( aecRestrictionHelper::checkRestriction( $restrictions, $this->metaUser ) === false ) {
				$this->error = 'user is denied permission - at least one restriction result was negative';
			}
		}

		if ( empty( $this->error ) ) {
			$this->response->result = true;
		}
	}
}

?>

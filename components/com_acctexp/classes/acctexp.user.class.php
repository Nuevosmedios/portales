<?php
/**
 * @version $Id: acctexp.user.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

JLoader::register('JTableUser', JPATH_LIBRARIES.'/joomla/database/table/user.php');

class cmsUser extends JTableUser
{
	function __construct()
	{
		$db = &JFactory::getDBO();

		parent::__construct($db);
	}
}

class metaUser
{
	/** @var int */
	var $userid				= null;
	/** @var object */
	var $cmsUser			= null;
	/** @var object */
	var $objSubscription	= null;
	/** @var int */
	var $hasSubscription	= null;

	function metaUser( $userid, $subscriptionid=null )
	{
		if ( empty( $userid ) && !empty( $subscriptionid ) ) {
			$userid = AECfetchfromDB::UserIDfromSubscriptionID( $subscriptionid );
		}

		$this->meta = new metaUserDB();
		$this->meta->loadUserid( $userid );

		$this->cmsUser = false;
		$this->hasCBprofile = false;
		$this->hasJSprofile = false;
		$this->userid = 0;

		$this->hasSubscription = 0;
		$this->objSubscription = null;
		$this->focusSubscription = null;

		if ( $userid ) {
			$this->cmsUser = new cmsUser();
			$this->cmsUser->load( $userid );

			$this->userid = $userid;

			if ( !empty( $subscriptionid ) ) {
				$aecid = $subscriptionid;
			} else {
				$aecid = AECfetchfromDB::SubscriptionIDfromUserID( $userid );
			}

			if ( $aecid ) {
				$this->objSubscription = new Subscription();
				$this->objSubscription->load( $aecid );
				$this->focusSubscription = new Subscription();
				$this->focusSubscription->load( $aecid );
				$this->hasSubscription = 1;
				$this->temporaryRFIX();
			}
		}
	}

	function dummyUser( $passthrough )
	{
		$this->hasSubscription = false;

		$this->cmsUser = new stdClass();
		$this->cmsUser->gid = 29;

		if ( is_array( $passthrough ) && !empty( $passthrough ) && !empty( $passthrough['username'] ) ) {
			$cpass = $passthrough;
			unset( $cpass['id'] );

			$cmsfields = array( 'name', 'username', 'email', 'password' );

			// Create dummy CMS user
			foreach( $cmsfields as $cmsfield ) {
				foreach ( $cpass as $k => $v ) {
					if ( $k == $cmsfield ) {
						$this->cmsUser->$cmsfield = $v;
						unset( $cpass[$k] );
					}
				}
			}

			if ( empty( $this->cmsUser->name ) && ( !empty( $cpass['firstname'] ) || !empty( $cpass['middlename'] ) || !empty( $cpass['lastname'] ) ) ) {
				$names = array( 'firstname', 'middlename', 'lastname' );

				$namearray = array();
				foreach ( $names as $n ) {
					if ( !empty( $cpass[$n] ) ) {
						$namearray[] = $cpass[$n];
					}
				}

				$this->cmsUser->name = implode( " ", $namearray );
			}

			// Create dummy CB/CBE user
			if ( GeneralInfoRequester::detect_component( 'anyCB' ) ) {
				$this->hasCBprofile = 1;
				$this->cbUser = new stdClass();

				foreach ( $cpass as $cbfield => $cbvalue ) {
					if ( is_array( $cbvalue ) ) {
						$this->cbUser->$cbfield = implode( ';', $cbvalue );
					} else {
						$this->cbUser->$cbfield = $cbvalue;
					}
				}
			}

			if ( isset( $this->_incomplete ) ) {
				unset( $this->_incomplete );
			}
		} else {
			$this->_incomplete = true;

			return true;
		}
	}

	function temporaryRFIX()
	{
		if ( !empty( $this->meta->plan_history->used_plans ) ) {
			$used_plans = $this->meta->plan_history->used_plans;
		} else {
			$used_plans = array();
		}

		$previous_plan = $this->meta->getPreviousPlan();

		$this->focusSubscription->used_plans = $used_plans;
		$this->focusSubscription->previous_plan = $previous_plan;
		$this->objSubscription->used_plans = $used_plans;
		$this->objSubscription->previous_plan = $previous_plan;
	}

	function getCMSparams( $name )
	{
		$userParams = new JParameter( $this->cmsUser->params );

		if ( is_array( $name ) ) {
			$array = array();

			foreach ( $name as $n ) {
				$array[$n] = $userParams->get( $n );
			}

			return $array;
		} else {
			return (int) $userParams->get( $name );
		}
	}

	function setCMSparams( $array )
	{
		$params = explode( "\n", $this->cmsUser->params );

		$oldarray = array();
		foreach ( $params as $chunk ) {
			$k = explode( '=', $chunk, 2 );
			if ( !empty( $k[0] ) ) {
				// Strip slashes, but preserve special characters
				$oldarray[$k[0]] = stripslashes( str_replace( array( '\n', '\t', '\r' ), array( "\n", "\t", "\r" ), $k[1] ) );
			}
			unset( $k );
		}

		foreach ( $array as $n => $v  ) {
			$oldarray[$n] = $v;
		}

		$params = array();
		foreach ( $array as $key => $value ) {
			if ( !is_null( $key ) ) {
				if ( is_array( $value ) ) {
					$temp = implode( ';', $value );
					$value = $temp;
				}

				if ( get_magic_quotes_gpc() ) {
					$value = stripslashes( $value );
				}

				$value = xJ::escape( $db, $value );

				$params[] = $key . '=' . $value;
			}
		}

		$this->cmsUser->params = implode( "\n", $params );

		$this->cmsUser->check();
		return $this->cmsUser->store();
	}

	function getTempAuth()
	{
		$return = false;

		// Only authorize if user IP is matching and the grant is not expired
		if ( isset( $this->meta->custom_params['tempauth_exptime'] ) && isset( $this->meta->custom_params['tempauth_ip'] ) ) {
			if ( ( $this->meta->custom_params['tempauth_ip'] == $_SERVER['REMOTE_ADDR'] ) && ( $this->meta->custom_params['tempauth_exptime'] >= ( (int) gmdate('U') ) ) ) {
				return true;
			}
		}

		return false;
	}

	function setTempAuth( $password=false )
	{
		global $aecConfig;

		if ( !empty( $this->cmsUser->password ) ) {
			// Make sure we catch traditional and new joomla passwords
			if ( ( $password !== false ) ) {
				if ( strpos( $this->cmsUser->password, ':') === false ) {
					if ( $this->cmsUser->password != md5( $password ) ) {
						return false;
					}
				} else {
					list( $hash, $salt ) = explode(':', $this->cmsUser->password);
					$cryptpass = md5( $password . $salt );
					if ( $hash != $cryptpass ) {
						return false;
					}
				}
			}
		}

		// Set params
		$params = array();
		$params['tempauth_ip'] = $_SERVER['REMOTE_ADDR'];
		$params['tempauth_exptime'] = strtotime( '+' . max( 10, $aecConfig->cfg['temp_auth_exp'] ) . ' minutes', ( (int) gmdate('U') ) );

		// Save params either to subscription or to _user entry
		$this->meta->addCustomParams( $params );
		$this->meta->storeload();

		return true;
	}

	function getAllSubscriptions()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_subscr'
				. ' WHERE `userid` = \'' . (int) $this->userid . '\''
				;
		$db->setQuery( $query );
		return xJ::getDBArray( $db );
	}

	function getAllCurrentSubscriptionsInfo()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `a`.`id`, `a`.`plan`, `a`.`expiration`, `a`.`recurring`, `a`.`lifetime`, `b`.`name`'
				. ' FROM #__acctexp_subscr AS a'
				. ' INNER JOIN #__acctexp_plans AS b ON a.plan = b.id'
				. ' WHERE `userid` = \'' . (int) $this->userid . '\''
				. ' AND `status` != \'Expired\''
				. ' AND `status` != \'Closed\''
				. ' AND `status` != \'Hold\''
				. ' ORDER BY `lastpay_date` DESC'
				;
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	function getAllCurrentSubscriptions()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_subscr'
				. ' WHERE `userid` = \'' . (int) $this->userid . '\''
				. ' AND `status` != \'Expired\''
				. ' AND `status` != \'Closed\''
				. ' AND `status` != \'Hold\''
				. ' ORDER BY `lastpay_date` DESC'
				;
		$db->setQuery( $query );
		return xJ::getDBArray( $db );
	}

	function getAllCurrentSubscriptionPlans()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `plan`'
				. ' FROM #__acctexp_subscr'
				. ' WHERE `userid` = \'' . (int) $this->userid . '\''
				. ' AND `status` != \'Expired\''
				. ' AND `status` != \'Closed\''
				. ' AND `status` != \'Hold\''
				. ' ORDER BY `lastpay_date` DESC'
				;
		$db->setQuery( $query );
		return xJ::getDBArray( $db );
	}

	function getSecondarySubscriptions( $simple=false )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`' . ( $simple ? '' : ', `status`, `plan`, `type`, `expiration`, `recurring`, `lifetime`' )
				. ' FROM #__acctexp_subscr'
				. ' WHERE `userid` = \'' . (int) $this->userid . '\''
				. ' AND `primary` = \'0\''
				. ' AND `status` != \'Expired\''
				. ' AND `status` != \'Closed\''
				. ' ORDER BY `lastpay_date` DESC'
				;
		$db->setQuery( $query );
		if ( $simple ) {
			return xJ::getDBArray( $db );
		} else {
			return $db->loadObjectList();
		}
	}

	function getMIlist()
	{
		$plans = $this->getAllCurrentSubscriptionPlans();

		$milist = array();
		foreach ( $plans as $plan_id ) {
			$mis = microIntegrationHandler::getMIsbyPlan( $plan_id );

			if ( !empty( $mis ) ) {
				foreach ( $mis as $mi ) {
					if ( array_key_exists( $mi, $milist ) ) {
						$milist[$mi]++;
					} else {
						$milist[$mi] = 1;
					}
				}
			}
		}

		return $milist;
	}

	function getMIcount( $mi_id )
	{
		$plans = $this->getAllCurrentSubscriptionPlans();

		$count = 0;
		foreach ( $plans as $plan_id ) {
			$mis = microIntegrationHandler::getMIsbyPlan( $plan_id );

			if ( !empty( $mis ) ) {
				foreach ( $mis as $mi ) {
					if ( $mi == $mi_id ) {
						$count++;
					}
				}
			}
		}

		return $count;
	}

	function procTriggerCreate( $user, $payment, $usage )
	{
		$db = &JFactory::getDBO();

		global $aecConfig;

		// Create a new cmsUser from user details - only allowing basic details so far
		// Try different types of usernames to make sure we have a unique one
		$usernames = array( $user['username'],
							$user['username'] . substr( md5( $user['name'] ), 0, 3 ),
							$user['username'] . substr( md5( ( $user['name'] . ( (int) gmdate('U') ) ) ), 0, 3 )
							);

		// Iterate through semi-random and pseudo-random usernames until a non-existing is found
		$id = 1;
		$k = 0;
		while ( $id ) {
			$username = $usernames[min( $k, ( count( $usernames ) - 1 ) )];

			$query = 'SELECT `id`'
					. ' FROM #__users'
					. ' WHERE `username` = \'' . $username . '\''
					;
			$db->setQuery( $query );

			$id = $db->loadResult();
			$k++;
		}

		$var['id'] 			= 0;
		$var['gid'] 		= 0;
		$var['username']	= $username;
		$var['name']		= $user['name'];
		$var['email']		= $user['email'];
		$var['password']	= $user['password'];

		$userid = AECToolbox::saveUserRegistration( 'com_acctexp', $var, true );

		// Create a new invoice with $invoiceid as secondary ident
		$invoice = new Invoice();
		$invoice->create( $userid, $usage, $payment['processor'], $payment['secondary_ident'] );

		// return nothing, the invoice will be handled by the second ident anyways
		return;
	}

	function establishFocus( $payment_plan, $processor='none', $silent=false, $bias=null )
	{
		if ( !is_object( $payment_plan ) ) {
			$planid = $payment_plan;

			$payment_plan = new SubscriptionPlan();
			$payment_plan->load( $planid );

			if ( empty( $payment_plan->id ) ) {
				return false;
			}
		}

		if ( is_object( $this->focusSubscription ) ) {
			if ( $this->focusSubscription->plan == $payment_plan->id ) {
				return 'existing';
			}
		}

		$plan_params = $payment_plan->params;

		if ( !isset( $plan_params['make_primary'] ) ) {
			$plan_params['make_primary'] = 1;
		}

		if ( $plan_params['make_primary'] && $this->hasSubscription ) {
			if ( $this->objSubscription->primary ) {
				$this->focusSubscription = $this->objSubscription;

				return 'existing';
			} else {
				$existing_record = $this->objSubscription->getSubscriptionID( $this->userid );

				if ( $existing_record ) {
					$this->objSubscription = new Subscription();
					$this->objSubscription->load( $existing_record );

					$this->focusSubscription = $this->objSubscription;

					return 'existing';
				}
			}
		}

		// If we are not dealing with a primary (or an otherwise unclear situation),
		// we need to figure out how to prepare the switch

		$existing_record = 0;
		$existing_status = false;

		// Check whether a record exists
		if ( $this->hasSubscription ) {
			$existing_record = $this->focusSubscription->getSubscriptionID( $this->userid, $payment_plan->id, $plan_params['make_primary'], false, $bias );

			if ( !empty( $existing_record ) ) {
				$db = &JFactory::getDBO();

				$query = 'SELECT `status`'
						. ' FROM #__acctexp_subscr'
						. ' WHERE `id` = \'' . (int) $existing_record . '\''
						;
				$db->setQuery( $query );

				$existing_status = $db->loadResult();
			}
		} else {
			$existing_record = 0;
		}

		$return = false;

		// To be failsafe, a new subscription may have to be added in here
		if ( empty( $this->hasSubscription ) || !$plan_params['make_primary'] || $plan_params['update_existing'] ) {
			if ( !empty( $existing_record ) && ( ( $existing_status == 'Trial' ) || ( $existing_status == 'Pending' ) || $plan_params['update_existing'] || $plan_params['make_primary'] ) ) {
				// Update existing non-primary subscription
				if ( $this->focusSubscription->id !== $existing_record ) {
					$this->focusSubscription = new Subscription();
					$this->focusSubscription->load( $existing_record );
				}

				$return = 'existing';
			} else {
				if ( !empty( $this->hasSubscription ) ) {
					$existing_parent = $this->focusSubscription->getSubscriptionID( $this->userid, $plan_params['standard_parent'], null );
				} else {
					$existing_parent = false;
				}

				// Create a root new subscription
				if ( empty( $this->hasSubscription ) && !$plan_params['make_primary'] && !empty( $plan_params['standard_parent'] ) && empty( $existing_parent ) ) {
					$this->objSubscription = new Subscription();
					$this->objSubscription->load( 0 );

					if ( $this->objSubscription->createNew( $this->userid, 'none', 1, 1, $plan_params['standard_parent'] ) ) {
						$this->objSubscription->applyUsage( $plan_params['standard_parent'], 'none', $silent, 0 );
					}
				} elseif ( !$plan_params['make_primary'] && !empty( $plan_params['standard_parent'] ) && $existing_parent ) {
					$this->objSubscription = new Subscription();
					$this->objSubscription->load( $existing_parent );

					if ( $this->objSubscription->is_expired() ) {
						$this->objSubscription->applyUsage( $plan_params['standard_parent'], 'none', $silent, 0 );
					}
				}

				// Create new subscription
				$this->focusSubscription = new Subscription();
				$this->focusSubscription->load( 0 );
				$this->focusSubscription->createNew( $this->userid, $processor, 1, $plan_params['make_primary'], $payment_plan->id );
				$this->hasSubscription = 1;

				if ( $plan_params['make_primary'] ) {
					$this->objSubscription = clone( $this->focusSubscription );
				}

				$return = 'new';
			}

		}

		if ( empty( $this->objSubscription ) && !empty( $this->focusSubscription ) ) {
			$this->objSubscription = clone( $this->focusSubscription );
		}

		$this->temporaryRFIX();

		return $return;
	}

	function moveFocus( $subscrid )
	{
		$subscription = new Subscription();
		$subscription->load( $subscrid );

		// If Subscription exists, move the focus to that one
		if ( $subscription->id ) {
			if ( $subscription->userid == $this->userid ) {
				$this->focusSubscription = $subscription;
				$this->temporaryRFIX();
				return true;
			} else {
				// This subscription does not belong to the user!
				return false;
			}
		} else {
			// This subscription does not exist
			return false;
		}
	}

	function loadSubscriptions()
	{
		$db = &JFactory::getDBO();

		// Get all the users subscriptions
		$query = 'SELECT id'
				. ' FROM #__acctexp_subscr'
				. ' WHERE `userid` = \'' . (int) $this->userid . '\''
				;
		$db->setQuery( $query );
		$subscrids = xJ::getDBArray( $db );

		if ( count( $subscrids ) > 1 ) {
			$this->allSubscriptions = array();

			foreach ( $subscrids as $subscrid ) {
				$subscription = new Subscription();
				$subscription->load( $subscrid );

				$this->allSubscriptions[] = $subscription;
			}

			return true;
		} else {
			// There is only the one that is probably already loaded
			$this->allSubscriptions = false;
			return false;
		}
	}

	function instantGIDchange( $gid, $removegid=array(), $sessionextra=null )
	{
		if ( empty( $this->cmsUser ) ) {
			return null;
		}

		// Always protect last administrator
		if ( $this->isAdmin() ) {
			if ( xJACLhandler::countAdmins() < 2 ) {
				return false;
			}
		}

		$shandler = new xJSessionHandler();

		$shandler->instantGIDchange( $this->userid, $gid, $removegid, $sessionextra );
	}

	function isAdmin()
	{
		if ( defined( 'JPATH_MANIFESTS' ) ) {
			$acl = &JFactory::getACL();

			$allowed_groups = xJACLhandler::getAdminGroups( true );

			$usergroups = $acl->getGroupsByUser( $this->cmsUser->id );

			if ( count( array_intersect( $allowed_groups, $usergroups ) ) ) {
				return true;
			}
		} else {
			if ( ( $this->cmsUser->gid == 24 ) || ( $this->cmsUser->gid == 25 ) ) {
				return true;
			}
		}

		return false;
	}

	function hasGroup( $group )
	{
		if ( is_array( $group ) ) {
			$usergroups = $this->getGroups();

			foreach ( $group as $g ) {
				if ( in_array( $g, $usergroups ) ) {
					return true;
				}
			}

			return false;
		} else {
			return in_array( $group, $this->getGroups() );
		}
		
	}

	function getGroups()
	{
		if ( defined( 'JPATH_MANIFESTS' ) ) {
			$db = &JFactory::getDBO();

			$query = 'SELECT `group_id`'
					. ' FROM #__user_usergroup_map'
					. ' WHERE `user_id`= \'' . (int) $this->userid . '\''
					;
			$db->setQuery( $query );

			$groups = xJ::getDBArray( $db );

			$lower = array();
			foreach ( $groups as $group ) {
				$lower = array_merge( $lower, xJACLhandler::getLowerACLGroups( $group ) );
			}

			$groups = array_merge( $groups, $lower );

			return array_unique( $groups );
		} else {
			return array_merge( xJACLhandler::getLowerACLGroups( $this->cmsUser->gid ), array( $this->cmsUser->gid ) );
		}
	}

	function is_renewing()
	{
		if ( !empty( $this->meta ) ) {
			return ( $this->meta->is_renewing() ? 1 : 0 );
		} else {
			return 0;
		}
	}

	function loadJProfile()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT DISTINCT `profile_key`'
				. ' FROM #__user_profiles';
		$db->setQuery( $query );
		$pkeys = xJ::getDBArray( $db );

		$query = 'SELECT `profile_key`, `profile_value`'
				. ' FROM #__user_profiles'
				. ' WHERE `user_id` = \'' . $this->userid . '\'';
		$db->setQuery( $query );
		$objects = $db->loadObjectList();

		$fields = array();
		foreach ( $pkeys as $k ) {
			if ( !empty( $objects ) ) {
				foreach ( $objects as $oid => $object ) {
					if ( $k == $object->profile_key ) {
						$fields[str_replace( ".", "_", $k )] = $object->profile_value;

						unset( $objects[$oid] );
					}
				}
			} else {
				$fields[str_replace( ".", "_", $k )] = "";
			}
		}

		$this->jProfile = $fields;

		if ( !empty( $this->jProfile ) ) {
			$this->hasJProfile = true;
		}
	}

	function loadCBuser()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT *'
			. ' FROM #__users AS u, #__comprofiler AS ue'
			. ' WHERE `user_id` = \'' . (int) $this->userid . '\' AND u.id = ue.id';
		$db->setQuery( $query );
		$this->cbUser = $db->loadObject();

		if ( is_object( $this->cbUser ) ) {
			$this->hasCBprofile = true;
		}
	}

	function loadJSuser()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__community_fields'
				. ' WHERE `type` != \'group\''
				;
		$db->setQuery( $query );
		$ids = xJ::getDBArray( $db );

		$query = 'SELECT `field_id`, `value`'
				. ' FROM #__community_fields_values'
					. ' WHERE `field_id` IN (' . implode( ',', $ids ) . ')'
					. ' AND `user_id` = \'' . (int) $this->userid . '\'';
				;
		$db->setQuery( $query );
		$fields = $db->loadObjectList();

		$this->jsUser = array();
		foreach ( $ids as $fid ) {
			foreach ( $fields as $field ) {
				if ( $field->field_id == $fid ) {
					$this->jsUser[$fid] = $field->value;
				}
			}

			if ( !isset( $this->jsUser[$fid] ) ) {
				$this->jsUser[$fid] = null;
			}

			$this->rewrite['user_js_' . $fid] = $this->jsUser[$fid];
		}

		if ( !empty( $this->jsUser ) ) {
			$this->hasJSprofile = true;
		}
	}

	function explodeName()
	{
		if ( !empty( $this->cmsUser->name ) ) {
			return $this->_explodeName( $this->cmsUser->name );
		} else {
			return $this->_explodeName( "" );
		}
	}

	function _explodeName( $name )
	{
		$return = array();
		$return['first_first']	= "";
		$return['first']		= "";
		$return['last']			= "";

		// Explode Name
		if ( !empty( $name ) ) {
			if ( is_array( $name ) ) {
				$namearray	= $name;
			} else {
				$namearray	= explode( " ", $name );
			}

			$return['first_first']	= $namearray[0];
			$maxname				= count($namearray) - 1;
			$return['last']			= $namearray[$maxname];

			unset( $namearray[$maxname] );

			$return['first']			= implode( ' ', $namearray );

			if ( empty( $return['first'] ) ) {
				$return['first'] = $return['first_first'];
			}
		}

		return $return;
	}

	function CustomRestrictionResponse( $restrictions )
	{
		$s = array();
		$n = 0;
		if ( is_array( $restrictions ) && !empty( $restrictions ) ) {
			foreach ( $restrictions as $restriction ) {
				$check1 = AECToolbox::rewriteEngine( $restriction[0], $this );
				$check2 = AECToolbox::rewriteEngine( $restriction[2], $this );
				$eval = $restriction[1];

				if ( ( $check1 === $restriction[0] ) && ( reWriteEngine::isRWEstring( $restriction[0] ) ) ) {
					$check1 = null;
				}

				if ( ( $check2 === $restriction[2] ) && ( reWriteEngine::isRWEstring( $restriction[2] ) ) ) {
					$check2 = null;
				}

				$s['customchecker'.$n] = AECToolbox::compare( $eval, $check1, $check2 );
				$n++;
			}
		}

		return $s;
	}

	function permissionResponse( $restrictions )
	{
		if ( is_array( $restrictions ) && !empty( $restrictions ) ) {
			$return = array();
			foreach ( $restrictions as $name => $value ) {
				// Might be zero, so do an expensive check
				if ( !is_null( $value ) && !( $value === "" ) ) {
					// Switch flag for inverted call
					if ( strpos( $name, '_excluded' ) !== false ) {
						$invert = true;
						$name = str_replace( '_excluded', '', $name );
					} else {
						$invert = false;
					}

					// Convert values to array or explode to array if none
					if ( !is_array( $value ) ) {
						if ( strpos( $value, ';' ) !== false ) {
							$check = explode( ';', $value );
						} else {
							$check = array( (int) $value );
						}
					} else {
						$check = $value;
					}

					$status = false;

					switch ( $name ) {
						// Check for set userid
						case 'userid':
							if ( is_object( $this->cmsUser ) ) {
								if ( $this->cmsUser->id === $value ) {
									$status = true;
								}
							}
							break;
						// Check for a certain GID
						case 'fixgid':
							if ( is_object( $this->cmsUser ) ) {
								if ( $this->hasGroup( $value ) ) {
									$status = true;
								}
							}
							break;
						// Check for Minimum GID
						case 'mingid':
							if ( is_object( $this->cmsUser ) ) {
								if ( $this->hasGroup( $value ) ) {
									$status = true;
								}
							}
							break;
						// Check for Maximum GID
						case 'maxgid':
							if ( is_object( $this->cmsUser ) ) {
								$groups = xJACLhandler::getHigherACLGroups( $value );
								if ( !$this->hasGroup( $groups ) ) {
									$status = true;
								}
							} else {
								// New user, so will always pass a max GID test
								$status = true;
							}
							break;
						// Check whether the user is currently in the right plan
						case 'plan_present':
							if ( $this->hasSubscription ) {
								$subs = $this->getAllCurrentSubscriptionPlans();

								foreach ( $subs as $subid ) {
									if ( in_array( (int) $subid, $check ) ) {
										$status = true;
									}
								}
							} else {
								if ( in_array( 0, $check ) ) {
									// "None" chosen, so will always pass if new user
									$status = true;
								}
							}
							break;
						// Check whether the user was in the correct plan before
						case 'plan_previous':
							if ( $this->hasSubscription ) {
								$previous = (int) $this->getPreviousPlan();

								if (
									( in_array( $previous, $check ) )
									|| ( ( in_array( 0, $check ) ) && is_null( $previous ) )
									) {
									$status = true;
								}
							} else {
								if ( in_array( 0, $check ) ) {
									// "None" chosen, so will always pass if new user
									$status = true;
								}
							}
							break;
						// Check whether the user has used the right plan before
						case 'plan_overall':
							if ( $this->hasSubscription ) {
								$subs = $this->getAllCurrentSubscriptionPlans();

								$array = $this->meta->getUsedPlans();
								foreach ( $check as $v ) {
									if ( ( !empty( $array[(int) $v] ) || in_array( $v, $subs ) ) ) {
										$status = true;
									}
								}
							} else {
								if ( in_array( 0, $check ) ) {
									// "None" chosen, so will always pass if new user
									$status = true;
								}
							}
							break;
						// Check whether the user has used the plan at least a certain number of times
						case 'plan_amount_min':
							if ( $this->hasSubscription ) {
								$subs = $this->getAllCurrentSubscriptionPlans();

								$usage = $this->meta->getUsedPlans();

								if ( !is_array( $value ) ) {
									$check = array( $value );
								}

								foreach ( $check as $v ) {
									$c = explode( ',', $v );

									// Make sure we have an entry if the user is currently in this plan
									if ( in_array( $c[0], $subs ) ) {
										if ( !isset( $usage[(int) $c[0]] ) ) {
											$usage[(int) $c[0]] = 1;
										}
									}

									if ( isset( $usage[(int) $c[0]] ) ) {
										if ( $usage[(int) $c[0]] >= (int) $c[1] ) {
											$status = true;
										}
									}
								}
							}
							break;
						// Check whether the user has used the plan at max a certain number of times
						case 'plan_amount_max':
							if ( $this->hasSubscription ) {
								$subs = $this->getAllCurrentSubscriptionPlans();

								$usage = $this->meta->getUsedPlans();

								if ( !is_array( $value ) ) {
									$check = array( $value );
								}

								foreach ( $check as $v ) {
									$c = explode( ',', $v );

									// Make sure we have an entry if the user is currently in this plan
									if ( in_array( $c[0], $subs ) ) {
										if ( !isset( $usage[(int) $c[0]] ) ) {
											$usage[(int) $c[0]] = 1;
										}
									}

									if ( isset( $usage[(int) $c[0]] ) ) {
										if ( $usage[(int) $c[0]] <= (int) $c[1] ) {
											$status = true;
										}
									}
								}
							} else {
								// New user will always pass max plan amount test
								$status = true;
							}
							break;
						default:
							// If it's not there, it's super OK!
							$status = true;
							break;
					}
				}

				// Swap if inverted and reestablish name
				if ( $invert ) {
					$name .= '_excluded';
					$return[$name] = !$status;
				} else {
					$return[$name] = $status;
				}
			}

			return $return;
		} else {
			return array();
		}
	}

	function usedCoupon ( $couponid, $type )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `usecount`'
				. ' FROM #__acctexp_couponsxuser'
				. ' WHERE `userid` = \'' . $this->userid . '\''
				. ' AND `coupon_id` = \'' . $couponid . '\''
				. ' AND `coupon_type` = \'' . $type . '\''
				;
		$db->setQuery( $query );
		$usecount = $db->loadResult();

		if ( $usecount ) {
			return $usecount;
		} else {
			return false;
		}
	}

	function getProperty( $key, $test=false )
	{
		return AECToolbox::getObjectProperty( $this, $key, $test );
	}

	function getPreviousPlan()
	{
		$current = $this->getAllCurrentSubscriptions();

		if ( empty( $current ) ) {
			return null;
		} else {
			return $this->meta->getPreviousPlan();
		}
	}

	function getUserMIs()
	{
		if ( empty( $this->focusSubscription->id ) ) {
			return array();
		}

		$focus = $this->focusSubscription->id;

		$return = array();
		if ( !empty( $this->objSubscription->plan ) ) {
			$selected_plan = new SubscriptionPlan();
			$selected_plan->load( $this->objSubscription->plan );

			$mis = $selected_plan->getMicroIntegrations();

			if ( empty( $mis ) ) {
				$mis = array();
			}

			$sec = $this->getSecondarySubscriptions( true );

			if ( !empty( $sec ) ) {
				foreach ( $sec as $pid ) {
					if ( $this->moveFocus( $pid ) ) {
						$selected_plan = new SubscriptionPlan();
						$selected_plan->load( $this->focusSubscription->plan );

						$miis = $selected_plan->getMicroIntegrations();

						if ( !empty( $miis ) ) {
							$mis = array_merge( $mis, $miis );
						}
					}
				}
			}

			if ( count( $mis ) ) {
				$mis = array_unique( $mis );

				foreach ( $mis as $mi_id ) {
					if ( $mi_id ) {
						$mi = new MicroIntegration();
						$mi->load( $mi_id );

						if ( !$mi->callIntegration() ) {
							continue;
						}

						$return[] = $mi;
					}
				}
			}
		}

		// Go back to initial focus, if it has been changed
		if ( $this->focusSubscription->id != $focus ) {
			$this->moveFocus( $focus );
		}

		return $return;
	}

	function getAlertLevel()
	{
		$alert = array();

		if ( !empty( $this->objSubscription->status ) ) {
			if ( strcmp( $this->objSubscription->status, 'Excluded' ) === 0 ) {
				$alert['level']		= 3;
				$alert['daysleft']	= 'excluded';
			} elseif ( !empty( $this->objSubscription->lifetime ) ) {
				$alert['level']		= 3;
				$alert['daysleft']	= 'infinite';
			} else {
				$alert = $this->objSubscription->GetAlertLevel();
			}
		}

		return $alert;
	}

	function isRecurring()
	{
		if ( !empty( $this->objSubscription->status ) ) {
			if ( strcmp( $this->objSubscription->status, 'Cancelled' ) != 0 ) {
				return $this->objSubscription->recurring;
			}
		}

		return false;
	}

	function delete()
	{
		$subids = $this->getAllSubscriptions();

		foreach ( $subids as $id ) {
			$subscription = new Subscription();
			$subscription->load( $id );

			$subscription->delete();
		}

		$this->meta->delete();
	}
}

class metaUserDB extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $userid				= null;
	/** @var datetime */
	var $created_date		= null;
	/** @var datetime */
	var $modified_date		= null;
	/** @var serialized object */
	var $plan_history		= null;
	/** @var serialized object */
	var $processor_params	= null;
	/** @var serialized object */
	var $plan_params		= null;
	/** @var serialized object */
	var $params 			= null;
	/** @var serialized object */
	var $custom_params		= null;

	function metaUserDB()
	{
		parent::__construct( '#__acctexp_metauser', 'id' );
	}

	function declareParamFields()
	{
		return array( 'plan_history', 'processor_params', 'plan_params', 'params', 'custom_params' );
	}

	/**
	 * loads specified user
	 *
	 * @param int $userid
	 */
	function loadUserid( $userid )
	{
		$id = $this->getIDbyUserid( $userid );

		if ( $id ) {
			$this->load( $id );
		} else {
			$this->createNew( $userid );
		}
	}

	function getIDbyUserid( $userid )
	{
		$query = 'SELECT `id`'
				. ' FROM #__acctexp_metauser'
				. ' WHERE `userid` = \'' . $userid . '\''
				;
		$this->_db->setQuery( $query );

		return $this->_db->loadResult();
	}

	function createNew( $userid )
	{
		$this->userid			= $userid;
		$this->created_date		= date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );

		$this->storeload();
	}

	function storeload()
	{
		$this->modified_date	= date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );

		parent::storeload();
	}

	function getProcessorParams( $processorid )
	{
		if ( isset( $this->processor_params[$processorid] ) ) {
			return $this->processor_params[$processorid];
		} else {
			return false;
		}
	}

	function setProcessorParams( $processorid, $params )
	{
		if ( empty( $this->processor_params ) ) {
			$this->processor_params = array();
		}

		if ( empty( $this->processor_params[$processorid] ) ) {
			$this->processor_params[$processorid] = array();
		}

		$this->processor_params[$processorid] = $params;

		$this->storeload();
	}

	function getMIParams( $miid, $usageid=false, $strict=true )
	{
		if ( $usageid ) {
			if ( is_object( $this->plan_params ) ) {
				$this->plan_params = array();
			}

			if ( isset( $this->plan_params[$usageid] ) ) {
				if ( isset( $this->plan_params[$usageid][$miid] ) ) {
					$return = $this->plan_params[$usageid][$miid];
				}
			} elseif ( !$strict ) {
				$return = $this->getMIParams( $miid );
			}
		} else {
			if ( isset( $this->params->mi[$miid] ) ) {
				$return = $this->params->mi[$miid];
			}
		}

		if ( empty( $return ) ) {
			return array();
		} elseif( is_array( $return ) ) {
			return $return;
		} else {
			return array();
		}
	}

	function setMIParams( $miid, $usageid=false, $params, $replace=false )
	{
		if ( $usageid ) {
			if ( is_object( $this->plan_params ) ) {
				$this->plan_params = array();
			}

			if ( isset( $this->plan_params[$usageid] ) ) {
				if ( isset( $this->plan_params[$usageid][$miid] ) && !$replace ) {
					if ( is_object( $this->plan_params[$usageid][$miid] ) ) {
						$this->plan_params[$usageid][$miid] = get_object_vars( $this->plan_params[$usageid][$miid] );
					}

					$this->plan_params[$usageid][$miid] = $this->mergeParams( $this->plan_params[$usageid][$miid], $params );
				} else {
					$this->plan_params[$usageid][$miid] = $params;
				}
			} else {
				$this->plan_params[$usageid] = array();
				$this->plan_params[$usageid][$miid] = $params;
			}
		}

		if ( isset( $this->params->mi[$miid] ) && !$replace ) {
			$this->params->mi[$miid] = $this->mergeParams( $this->params->mi[$miid], $params );
		} else {
			$this->params->mi[$miid] = $params;
		}

		$this->modified_date	= date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );

		return true;
	}

	function getCustomParams()
	{
		return $this->custom_params;
	}

	function addCustomParams( $params )
	{
		$this->addParams( $params, 'custom_params' );

		$this->modified_date	= date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
	}

	function setCustomParams( $params )
	{
		$this->addParams( $params, 'custom_params', true );

		$this->modified_date	= date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
	}

	function addPreparedMIParams( $plan_mi, $mi=false )
	{
		$this->addParams( $plan_mi, 'plan_params' );

		if ( $mi === false ) {
			// TODO: Write function that recreates pure MI data from plan_mi construct
		}

		if ( !empty( $mi ) ) {
			if ( isset( $this->params->mi ) ) {
				$this->params->mi = $this->mergeParams( $this->params->mi, $mi );
			} else {
				$this->params->mi = $mi;
			}
		}

		return $this->storeload();
	}

	function addPlanID( $id )
	{
		$this->plan_history->plan_history[] = $id;

		if ( isset( $this->plan_history->used_plans[$id] ) ) {
			$this->plan_history->used_plans[$id]++;
		} else {
			$this->plan_history->used_plans[$id] = 1;
		}

		return $this->storeload();
	}

	function is_renewing()
	{
		if ( !empty( $this->plan_history->used_plans ) ) {
			return true;
		} else {
			return false;
		}
	}

	function getUsedPlans()
	{
		if ( !empty( $this->plan_history->used_plans ) ) {
			return $this->plan_history->used_plans;
		} else {
			return array();
		}
	}

	function getPreviousPlan()
	{
		if ( empty( $this->plan_history ) ) {
			return null;
		}

		$last = count( $this->plan_history->plan_history ) - 2;

		if ( $last < 0 ) {
			return null;
		} elseif ( isset( $this->plan_history->plan_history[$last] ) ) {
			return $this->plan_history->plan_history[$last];
		} else {
			return null;
		}
	}
}

?>

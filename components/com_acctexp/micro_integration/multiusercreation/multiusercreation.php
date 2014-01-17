<?php
/**
 * @version $Id: mi_multiusercreation.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Multi User Creation
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_multiusercreation
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_MULTIUSERCREATION');
		$info['desc'] = JText::_('AEC_MI_DESC_MULTIUSERCREATION');
		$info['type'] = array( 'aec.membership', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();

		$settings['expire_child_subscr']	= array( 'toggle' );
		$settings['renew_child_subscr']		= array( 'toggle' );
		$settings['renew_add_child_subscr']	= array( 'toggle' );

		$settings['sender']					= array( 'inputE' );
		$settings['sender_name']			= array( 'inputE' );

		$settings['recipient']				= array( 'inputE' );

		$settings['subject']				= array( 'inputE' );
		$settings['text_html']				= array( 'toggle' );
		$settings['text']					= array( $this->settings['text_html'] ? 'editor' : 'inputD' );
		$settings['text_userlistitem']		= array( $this->settings['text_html'] ? 'editor' : 'inputD' );

		$rewriteswitches					= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );
		$settings['rewriteInfo']			= array( 'fieldset', JText::_( 'AEC_MI_SET11_EMAIL'), AECToolbox::rewriteEngineInfo( $rewriteswitches ) );

		$settings['users_amount']			= array( 'inputC' );

		$plans	= SubscriptionPlanHandler::getActivePlanList();

		$copfrom = array();
		$copfrom[] = JHTML::_('select.option', "NONE", "Do not copy" );
		if ( !empty( $this->settings['users_amount'] ) ) {
			for ( $i=0; $i<$this->settings['users_amount']; $i++ ) {
				$copfrom[] = JHTML::_('select.option', $i, "Copy from #" . $i );
			}
		}

		if ( !empty( $this->settings['users_amount'] ) ) {
			for ( $i=0; $i<$this->settings['users_amount']; $i++ ) {
				$userfields = array( 'copyfrom', 'username', 'name', 'email', 'password_length', 'plan' );

				foreach ( $userfields as $key ) {
					$ndesc = '#' . $i . ': ' . JText::_( 'MI_MI_MULTIUSERCREATION_DETAIL' ) . ": " . $key;

					switch ( $key ) {
						case 'copyfrom':
							if ( !isset( $this->settings['create_user_'.$i.'_'.$key] ) ) {
								$this->settings['create_user_'.$i.'_'.$key] = "NONE";
							}

							$settings['lists']['create_user_'.$i.'_'.$key] = JHTML::_( 'select.genericlist', $copfrom, 'create_user_'.$i.'_'.$key, 'size="' . min( 10, count( $plans ) + 2 ) . '"', 'value', 'text', $this->settings['create_user_'.$i.'_'.$key] );

							$settings['create_user_'.$i.'_'.$key]	= array( 'list', $ndesc, $ndesc );
							break;
						case 'password_length':
							$settings['create_user_'.$i.'_'.$key]	= array( 'inputB', $ndesc, $ndesc );
							break;
						case 'plan':
							if ( !isset( $this->settings['create_user_'.$i.'_'.$key] ) ) {
								$this->settings['create_user_'.$i.'_'.$key] = 0;
							}

							$settings['lists']['create_user_'.$i.'_'.$key] = JHTML::_( 'select.genericlist', $plans, 'create_user_'.$i.'_'.$key, 'size="' . min( 10, count( $plans ) + 2 ) . '"', 'value', 'text', $this->settings['create_user_'.$i.'_'.$key] );

							$settings['create_user_'.$i.'_'.$key]	= array( 'list', $ndesc, $ndesc );
							break;
						default:
							$settings['create_user_'.$i.'_'.$key]	= array( 'inputD', $ndesc, $ndesc );
							break;
					}
				}
			}
		}

		return $settings;
	}

	function Defaults()
	{
		$settings = array();

		return $settings;
	}

	function action( $request )
	{
		$database = &JFactory::getDBO();

		$flags = $request->metaUser->focusSubscription->getMIflags( $request->plan->id, $this->id );

		if ( !empty( $flags['child_list'] ) ) {
			$child_list = $flags['child_list'];
		} else {
			$child_list = array();
		}

		if ( !empty( $child_list ) && !empty( $this->settings['renew_child_subscr'] ) ) {
			foreach ( $child_list as $subscr_id ) {
				$userid = AECfetchfromDB::UserIDfromSubscriptionID( $subscr_id );

				$metaUser = new metaUser( $userid );

				$metaUser->moveFocus( $subscr_id );

				$plan = new SubscriptionPlan( $database );
				$plan->load( $metaUser->focusSubscription->plan );

				$plan->applyPlan( $metaUser->focusSubscription, 'none', 1 );
			}
		}

		$userlist = array();

		if ( empty( $child_list ) || $this->settings['renew_add_child_subscr'] ) {
			for ( $i=0; $i<$this->settings['users_amount']; $i++ ) {
				$fields = array( 'username', 'name', 'email', 'password_length' );

				$x = $i;

				if ( isset( $this->settings['create_user_'.$i.'_copyfrom'] ) ) {
					if ( $this->settings['create_user_'.$i.'_copyfrom'] !== "NONE" ) {
						$x = $this->settings['create_user_'.$i.'_copyfrom'];
					}
				}

				$userfields = array();
				foreach ( $fields as $field ) {
					// Do not create half-empty users
					if ( empty( $this->settings['create_user_'.$x.'_'.$field] ) ) {
						continue 2;
					}

					if ( $field == 'password_length' ) {
						$userfields['password'] = trim( AECToolbox::randomstring( $this->settings['create_user_'.$x.'_'.$field], true ) );
					} elseif ( $field == 'username' ) {
						// Make sure that we create a unique username, but no more often than 10 times
						$unique = false;
						$j = 0;
						while ( !$unique && ( $j < 10 ) ) {
							$userfields[$field] = trim( AECToolbox::rewriteEngineRQ( $this->settings['create_user_'.$x.'_'.$field], $request ) );

							$query = 'SELECT `id`'
									. ' FROM #__users'
									. ' WHERE `username` = \'' . $userfields[$field] . '\''
									;
							$database->setQuery( $query );

							$unique = $database->loadResult() ? false : true;

							$j++;
						}

						// If we tried more than 10 times, it didn't work - the chosen username generator does not produce unique strings
						if ( $j >= 10 ) {
							continue 2;
						}
					} else {
						$userfields[$field] = trim( AECToolbox::rewriteEngineRQ( $this->settings['create_user_'.$x.'_'.$field], $request ) );
					}
				}

				$userfields['password2'] = $userfields['password'];

				if ( $this->settings['create_user_'.$x.'_username'] == $this->settings['create_user_'.$x.'_name'] ) {
					$userfields['username'] = $userfields['name'];
				}

				$userid = $this->createUser( $userfields );

				if ( !empty( $this->settings['create_user_'.$x.'_plan'] ) ) {
					$metaUser = new metaUser( $userid );

					$plan = new SubscriptionPlan( $database );
					$plan->load( $this->settings['create_user_'.$x.'_plan'] );

					$metaUser->establishFocus( $plan );

					$plan->applyPlan( $metaUser->focusSubscription, 'none', 1 );

					$child_list[] = $metaUser->focusSubscription->id;
				}

				$userlist[] = $userfields;
			}
		}

		if ( !empty( $child_list ) ) {
			$request->metaUser->focusSubscription->setMIflags( $request->plan->id, $this->id, array( 'child_list' => $child_list ) );
		}

		if ( !empty( $userlist ) ) {
			$this->mailOut( $request, $userlist );
		}

		return true;
	}

	function expiration_action( $request )
	{
		if ( $this->settings['expire_child_subscr'] ) {
			$database = &JFactory::getDBO();

			$userflags = $request->metaUser->focusSubscription->getMIflags( $request->plan->id, $this->id );

			if ( isset( $userflags['child_list'] ) ) {
				if ( !empty( $userflags['child_list'] ) ) {
					foreach ( $userflags['child_list'] as $subscr_id ) {
						$subscription = new Subscription( $database );
						$subscription->load( $subscr_id );

						if ( $subscription->id ) {
							$subscription->expire( true );
						}
					}
				}
			}
		}

		return true;
	}

	function admin_info( $request )
	{
		$userflags = $request->metaUser->focusSubscription->getMIflags( $request->plan->id, $this->id );
		
		$list = "<ul>";
		if ( isset( $userflags['child_list'] ) ) {
			if ( !empty( $userflags['child_list'] ) ) {
				foreach ( $userflags['child_list'] as $subscr_id ) {
					$metaUser = new metaUser( null, $subscr_id );

					$list .= '<li><a href="index.php?option=com_acctexp&amp;task=editMembership&subscriptionid=' . $subscr_id . '">#' . $subscr_id . ' - ' . $metaUser->cmsUser->name . ' (' . $metaUser->cmsUser->username . ')</a></li>';
				}
			} else {
				$list .= "<li>No Child Subscriptions</li>";
			}
		} else {
			$list .= "<li>No Child Subscriptions</li>";
		}
		$list .= "</ul>";

		$settings['history']	= array( 'fieldset', 'Child Subscriptions', $list );
	}

	function profile_info( $request )
	{
		$userflags = $request->metaUser->focusSubscription->getMIflags( $request->plan->id, $this->id );
		
		$list = "<ul>";
		if ( isset( $userflags['child_list'] ) ) {
			if ( !empty( $userflags['child_list'] ) ) {
				foreach ( $userflags['child_list'] as $subscr_id ) {
					$metaUser = new metaUser( null, $subscr_id );

					$list .= '<li>#' . $subscr_id . ' - ' . $metaUser->cmsUser->name . ' (' . $metaUser->cmsUser->username . ')</li>';
				}
			} else {
				$list .= "<li>No Child Subscriptions</li>";
			}
		} else {
			$list .= "<li>No Child Subscriptions</li>";
		}
		$list .= "</ul>";

		$settings['history']	= array( 'fieldset', 'Child Subscriptions', $list );
	}

	function createUser( $fields )
	{
		return AECToolbox::saveUserRegistration( 'com_acctexp', $fields, true, true, true, true );
	}

	function mailOut( $request, $userlist )
	{
		$rwEngine = new reWriteEngine();
		$rwEngine->resolveRequest( $request );

		$userlist_done = array();
		foreach ( $userlist as $ulist ) {
			$userfields = array( "username", "name", "email", "password" );

			foreach ( $userfields as $key ) {
				$rwEngine->rewrite['userlist_' . $key] = $ulist[$key];
			}

			$userlist_done[] = $rwEngine->resolve( $this->settings['text_userlistitem'] );
		}

		$message	= sprintf( $this->settings['text'], implode( "\n", $userlist_done ) );

		$message	= AECToolbox::rewriteEngineRQ( $message, $request );
		$subject	= AECToolbox::rewriteEngineRQ( $this->settings['subject'], $request );

		if ( empty( $message ) ) {
			return false;
		}

		$recipients = explode( ',', $this->settings['recipient'] );

		foreach ( $recipients as $current => $email ) {
			$recipients[$current] = AECToolbox::rewriteEngineRQ( trim( $email ), $request );
		}

		xJ::sendMail( $this->settings['sender'], $this->settings['sender_name'], $recipients, $subject, $message, $this->settings['text_html'] );

		return true;
	}

}

?>
<?php
/**
 * @version $Id: acctexp.subscription.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class Subscription extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $userid				= null;
	/** @var int */
	var $primary			= null;
	/** @var string */
	var $type				= null;
	/** @var string */
	var $status				= null;
	/** @var datetime */
	var $signup_date		= null;
	/** @var datetime */
	var $lastpay_date		= null;
	/** @var datetime */
	var $cancel_date		= null;
	/** @var datetime */
	var $eot_date			= null;
	/** @var string */
	var $eot_cause			= null;
	/** @var int */
	var $plan				= null;
	/** @var string */
	var $recurring			= null;
	/** @var int */
	var $lifetime			= null;
	/** @var datetime */
	var $expiration			= null;
	/** @var text */
	var $params 			= null;
	/** @var text */
	var $customparams		= null;

	function Subscription()
	{
		parent::__construct( '#__acctexp_subscr', 'id' );
	}

	function declareParamFields()
	{
		return array( 'params', 'customparams' );
	}

	function check()
	{
		$vars = get_class_vars( 'Subscription' );
		$props = get_object_vars( $this );

		foreach ( $props as $n => $prop ) {
			if ( !array_key_exists( $n, $vars  ) ) {
				unset( $this->$n );
			}
		}

		return parent::check();
	}

	function loadUserid( $userid )
	{
		$this->load( $this->getSubscriptionID( $userid ) );
	}

	function getSubscriptionID( $userid, $usage=null, $primary=1, $similar=false, $bias=null )
	{
		$query = 'SELECT `id`'
				. ' FROM #__acctexp_subscr'
				. ' WHERE `userid` = \'' . $userid . '\''
				;

		if ( !empty( $usage ) ) {
			$plan = new SubscriptionPlan();
			$plan->load( $usage );

			if ( ( !empty( $plan->params['similarplans'] ) && $similar ) || !empty( $plan->params['equalplans'] ) ) {
				$allplans = array( $usage );

				if ( !empty( $plan->params['similarplans'] ) || !empty( $plan->params['equalplans'] ) ) {
					if ( empty( $plan->params['similarplans'] ) ) {
						$plan->params['similarplans'] = array();
					}

					if ( empty( $plan->params['equalplans'] ) ) {
						$plan->params['equalplans'] = array();
					}

					if ( $similar ) {
						$allplans = array_merge( $plan->params['similarplans'], $plan->params['equalplans'], $allplans );
					} else {
						$allplans = array_merge( $plan->params['equalplans'], $allplans );
					}
				}

				foreach ( $allplans as $apid => $pid ) {
					$allplans[$apid] = '`plan` = \'' . $pid . '\'';
				}

				$query .= ' AND (' . implode( ' OR ', $allplans ) . ')';
			} else {
				$query .= ' AND ' . '`plan` = \'' . $usage . '\'';
			}
		}

		if ( !empty( $primary ) ) {
			$query .= ' AND `primary` = \'1\'';
		} elseif ( !is_null( $primary ) ) {
			$query .= ' AND `primary` = \'0\'';
		}

		$this->_db->setQuery( $query );

		if ( !empty( $bias ) ) {
			$subscriptionids = xJ::getDBArray( $this->_db );

			if ( in_array( $bias, $subscriptionids ) ) {
				$subscriptionid = $bias;
			}
		} else {
			$subscriptionid = $this->_db->loadResult();
		}

		if ( !isset( $subscriptionid ) ) {
			$subscriptionid = null;
		}

		if ( empty( $subscriptionid ) && !$similar ) {
			return $this->getSubscriptionID( $userid, $usage, false, true, $bias );
		}

		return $subscriptionid;
	}

	function makePrimary()
	{
		$query = 'UPDATE #__acctexp_subscr'
				. ' SET `primary` = \'0\''
				. ' WHERE `userid` = \'' . $this->userid . '\''
				;
		$this->_db->setQuery( $query );
		$this->_db->query();

		$this->primary = 1;
		$this->storeload();
	}

	function manualVerify()
	{
		if ( $this->is_expired() ) {
			aecRedirect( 'index.php?option=com_acctexp&task=expired&userid=' . ((int) $this->userid) );
			return false;
		} else {
			return true;
		}
	}

	function createNew( $userid, $processor, $pending, $primary=1, $plan=null )
	{
		if ( !$userid ) {
			return false;
		}

		$this->userid		= $userid;
		$this->primary		= $primary;
		$this->signup_date	= date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
		$this->expiration	= date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
		$this->status		= $pending ? 'Pending' : 'Active';
		$this->type			= $processor;

		if ( !empty( $plan ) ) {
			$this->plan		= $plan;
		}

		return $this->storeload();
	}

	function is_expired( $offset=false )
	{
		global $aecConfig;

		if ( $this->status == 'Expired' ) {
			return true;
		} elseif ( !$this->is_lifetime() ) {
			if ( $offset ) {
				$expstamp = strtotime( ( '-' . $offset . ' days' ), strtotime( $this->expiration ) );
			} else {
				$expstamp = strtotime( ( '+' . $aecConfig->cfg['expiration_cushion'] . ' hours' ), strtotime( $this->expiration ) );
			}

			$localtime = (int) gmdate('U');

			$is_past = ( $expstamp - $localtime ) < 0;

			if ( ( $expstamp > 0 ) && $is_past ) {
				return true;
			} elseif ( ( $expstamp <= 0 ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function is_lifetime()
	{
		if ( ( $this->expiration === '9999-12-31 00:00:00' ) || ( $this->expiration === '0000-00-00 00:00:00' ) ) {
			return true;
		} else {
			return false;
		}
	}

	function setExpiration( $unit, $value, $extend )
	{
		$now = (int) gmdate('U');

		if ( $extend ) {
			$current = strtotime( $this->expiration );

			if ( $current < $now ) {
				$current = $now;
			}
		} else {
			$current = $now;
		}

		$this->expiration = AECToolbox::computeExpiration( $value, $unit, $current );
	}


	/**
	* Get alert level for a subscription
	* @param int user id
	* @return Object alert
	* alert['level'] = -1 means no threshold has been reached
	* alert['level'] =  0 means subscription expired
	* alert['level'] =  1 means most critical threshold has been reached (default: 7 days or less to expire)
	* alert['level'] =  2 means second level threshold has been reached (default: 14 days or less to expire)
	* alert['daysleft'] = number of days left to expire
	*/
	function GetAlertLevel()
	{
		global $aecConfig;

		if ( $this->expiration ) {
			$alert['level']		= -1;
			$alert['daysleft']	= 0;

			$expstamp = strtotime( $this->expiration );

			// Get how many days left to expire (86400 sec = 1 day)
			$alert['daysleft']	= round( ( $expstamp - ( (int) gmdate('U') ) ) / 86400 );

			if ( $alert['daysleft'] < 0 ) {
				// Subscription already expired. Alert Level 0!
				$alert['level']	= 1;
			} else {
				// Get alert levels
				if ( $alert['daysleft'] <= $aecConfig->cfg['alertlevel1'] ) {
					// Less than $numberofdays to expire! This is a level 1
					$alert['level']		= 1;
				} elseif ( ( $alert['daysleft'] > $aecConfig->cfg['alertlevel1'] ) && ( $alert['daysleft'] <= $aecConfig->cfg['alertlevel2'] ) ) {
					$alert['level']		= 2;
				} elseif ( $alert['daysleft'] > $aecConfig->cfg['alertlevel2'] ) {
					// Everything is ok. Level 3 means no threshold was reached
					$alert['level']		= 3;
				}
			}
		}

		return $alert;
	}

	function verifylogin( $block, $metaUser=false )
	{
		global $aecConfig;

		if ( strcmp( $this->status, 'Excluded' ) === 0 ) {
			$expired = false;
		} elseif ( strcmp( $this->status, 'Expired' ) === 0 ) {
			$expired = true;
		} else {
			$expired = $this->is_expired();
		}

		if ( $expired ) {
			$pp = new PaymentProcessor();

			if ( $pp->loadName( $subscription->type ) ) {
				$validation = $pp->validateSubscription();
			} else {
				$validation = false;
			}
		}

		if ( ( $expired || ( strcmp( $this->status, 'Closed' ) === 0 ) ) && $aecConfig->cfg['require_subscription'] ) {
			if ( $metaUser !== false ) {
				$metaUser->setTempAuth();
			}

			if ( strcmp( $this->status, 'Expired' ) === 0 ) {
				aecRedirect( AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=expired&userid=' . $this->userid ), false, true );
			} else {
				if ( $this->expire() ) {
					aecRedirect( AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=expired&userid=' . $this->userid ), false, true );
				}
			}
		} elseif ( ( strcmp( $this->status, 'Pending' ) === 0 ) || $block ) {
			if ( $metaUser !== false ) {
				$metaUser->setTempAuth();
			}
			aecRedirect( AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=pending&userid=' . $this->userid ), false, true );
		} elseif ( ( strcmp( $this->status, 'Hold' ) === 0 ) || $block ) {
			aecRedirect( AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=hold&userid=' . $this->userid ), false, true );
		}
	}

	function verify( $block, $metaUser=false )
	{
		global $aecConfig;

		if ( strcmp( $this->status, 'Excluded' ) === 0 ) {
			$expired = false;
		} elseif ( strcmp( $this->status, 'Expired' ) === 0 ) {
			$expired = true;
		} else {
			$expired = $this->is_expired();
		}

		if ( $expired ) {
			$pp = new PaymentProcessor();

			if ( $pp->loadName( $this->type ) ) {
				$expired = !$pp->validateSubscription( $this );
			}
		}

		if ( ( $expired || ( strcmp( $this->status, 'Closed' ) === 0 ) ) && $aecConfig->cfg['require_subscription'] ) {
			if ( $metaUser !== false ) {
				$metaUser->setTempAuth();
			}

			if ( strcmp( $this->status, 'Expired' ) === 0 ) {
				return 'expired';
			} else {
				if ( $this->expire() ) {
					return 'expired';
				}
			}
		} elseif ( ( strcmp( $this->status, 'Pending' ) === 0 ) || $block ) {
			return 'pending';
		} elseif ( ( strcmp( $this->status, 'Hold' ) === 0 ) || $block ) {
			return 'hold';
		}

		return true;
	}

	function expire( $overridefallback=false, $special=null )
	{
		// Users who are excluded cannot expire
		if ( strcmp( $this->status, 'Excluded' ) === 0 ) {
			return false;
		}

		// Load plan variables, otherwise load dummies
		if ( $this->plan ) {
			$subscription_plan = new SubscriptionPlan();
			$subscription_plan->load( $this->plan );
		} else {
			$subscription_plan = false;
		}

		$metaUser = new metaUser( $this->userid );

		// Move the focus Subscription		
		if ( !$metaUser->moveFocus( $this->id ) ) {
			return null;
		}

		// Recognize the fallback plan, if not overridden
		if ( !empty( $subscription_plan->params['fallback'] ) && !$overridefallback ) {
			if ( !$subscription_plan->params['make_primary'] && !empty( $subscription_plan->params['fallback_req_parent'] ) ) {
				if ( $metaUser->focusSubscription->id != $metaUser->objSubscription->id ) {
					if ( $metaUser->objSubscription->is_expired() ) {
						$overridefallback = true;
					}
				}
			}

			if ( !$overridefallback ) {
				$metaUser->focusSubscription->applyUsage( $subscription_plan->params['fallback'], 'none', 1 );
				$this->reload();
				return false;
			}
		} else {
			// Set a Trial flag if this is an expired Trial for further reference
			if ( strcmp( $this->status, 'Trial' ) === 0 ) {
				$metaUser->focusSubscription->addParams( array( 'trialflag' => 1 ) );
			} elseif ( is_array( $this->params ) ) {
				if ( in_array( 'trialflag', $this->params ) ) {
					$metaUser->focusSubscription->delParams( array( 'trialflag' ) );
				}
			}

			if ( !( strcmp( $metaUser->focusSubscription->status, 'Expired' ) === 0 ) && !( strcmp( $metaUser->focusSubscription->status, 'Closed' ) === 0 ) ) {
				$metaUser->focusSubscription->setStatus( 'Expired' );
			}

			// Call Expiration MIs
			if ( $subscription_plan !== false ) {
				$mih = new microIntegrationHandler();
				$mih->userPlanExpireActions( $metaUser, $subscription_plan, $special );
			}
		}

		$this->reload();

		return true;
	}

	function cancel( $invoice=null )
	{
		// Since some processors do not notify each period, we need to check whether the expiration
		// lies too far in the future and cut it down to the end of the period the user has paid

		if ( $this->plan ) {
			$app = JFactory::getApplication();

			$subscription_plan = new SubscriptionPlan();
			$subscription_plan->load( $this->plan );

			// Resolve blocks that we are going to substract from the set expiration date
			$unit = 60*60*24;
			switch ( $subscription_plan->params['full_periodunit'] ) {
				case 'W':
					$unit *= 7;
					break;
				case 'M':
					$unit *= 31;
					break;
				case 'Y':
					$unit *= 365;
					break;
			}

			$periodlength = $subscription_plan->params['full_period'] * $unit;

			$newexpiration = strtotime( $this->expiration );
			$now = (int) gmdate('U');

			// ...cut away blocks until we are in the past
			while ( $newexpiration > $now ) {
				$newexpiration -= $periodlength;
			}

			// Be extra sure that we did not overachieve
			if ( $newexpiration < $now ) {
				$newexpiration += $periodlength;
			}

			// And we get the bare expiration date
			$this->expiration = date( 'Y-m-d H:i:s', $newexpiration );
		}

		$this->setStatus( 'Cancelled' );

		return true;
	}

	function hold( $invoice=null )
	{
		$this->setStatus( 'Hold' );

		return true;
	}

	function hold_settle( $invoice=null )
	{
		$this->setStatus( 'Active' );

		return true;
	}

	function setStatus( $status )
	{
		$this->status = $status;

		$this->storeload();
	}

	function applyUsage( $usage = 0, $processor = 'none', $silent = 0, $multiplicator = 1, $invoice=null )
	{
		if ( !$usage ) {
			$usage = $this->plan;
		}

		$new_plan = new SubscriptionPlan();
		$new_plan->load( $usage );

		if ( $new_plan->id ) {
			return $new_plan->applyPlan( $this, $processor, $silent, $multiplicator, $invoice );
		} else {
			return false;
		}
	}

	function triggerPreExpiration( $metaUser, $mi_pexp )
	{
		$actions = 0;

		// No actions on expired, trial or recurring
		if ( ( strcmp( $this->status, 'Expired' ) === 0 ) || ( $this->status == 'Trial' ) || $this->recurring ) {
			return $actions;
		}

		$subscription_plan = new SubscriptionPlan();
		$subscription_plan->load( $this->plan );

		$micro_integrations = $subscription_plan->getMicroIntegrations();

		if ( empty( $micro_integrations ) ) {
			return $actions;
		}

		foreach ( $micro_integrations as $mi_id ) {
			if ( !in_array( $mi_id, $mi_pexp ) ) {
				continue;
			}

			$mi = new microIntegration();

			if ( !$mi->mi_exists( $mi_id ) ) {
				continue;
			}

			$mi->load( $mi_id );

			if ( !$mi->callIntegration() ) {
				continue;
			}

			// Do the actual pre expiration check on this MI
			if ( $this->is_expired( $mi->pre_exp_check ) ) {
				$result = $mi->pre_expiration_action( $metaUser, $subscription_plan );
				if ( $result ) {
					$actions++;
				}
			}


			unset( $mi );
		}

		return $actions;
	}

	function sendEmailRegistered( $renew, $adminonly=false, $invoice=null )
	{
		$app = JFactory::getApplication();

		$lang =& JFactory::getLanguage();

		global $aecConfig;

		$free = ( strcmp( strtolower( $this->type ), 'none' ) == 0 || strcmp( strtolower( $this->type ), 'free' ) == 0 );

		$urow = new cmsUser();
		$urow->load( $this->userid );

		$plan = new SubscriptionPlan();
		$plan->load( $this->plan );

		$name			= $urow->name;
		$email			= $urow->email;
		$username		= $urow->username;
		$pwd			= $urow->password;
		$activationcode	= $urow->activation;

		$message = sprintf( JText::_('ACCTEXP_MAILPARTICLE_GREETING'), $name );

		// Assemble E-Mail Subject & Message
		if ( $renew ) {
			$subject = sprintf( JText::_('ACCTEXP_SEND_MSG_RENEW'), $name, $app->getCfg( 'sitename' ) );

			$message .= sprintf( JText::_('ACCTEXP_MAILPARTICLE_THANKSREN'), $app->getCfg( 'sitename' ) );

			if ( $plan->email_desc ) {
				$message .= "\n\n" . $plan->email_desc . "\n\n";
			} else {
				$message .= " ";
			}

			if ( $free ) {
				$message .= sprintf( JText::_('ACCTEXP_MAILPARTICLE_LOGIN'), JURI::root() );
			} else {
				$message .= JText::_('ACCTEXP_MAILPARTICLE_PAYREC') . " "
				. sprintf( JText::_('ACCTEXP_MAILPARTICLE_LOGIN'), JURI::root() );
			}
		} else {
			$subject = sprintf( JText::_('ACCTEXP_SEND_MSG'), $name, $app->getCfg( 'sitename' ) );

			$message .= sprintf(JText::_('ACCTEXP_MAILPARTICLE_THANKSREG'), $app->getCfg( 'sitename' ) );

			if ( $plan->email_desc ) {
				$message .= "\n\n" . $plan->email_desc . "\n\n";
			} else {
				$message .= " ";
			}

			if ( $free ) {
				$message .= sprintf( JText::_('ACCTEXP_MAILPARTICLE_LOGIN'), JURI::root() );
			} else {
				$message .= JText::_('ACCTEXP_MAILPARTICLE_PAYREC') . " "
				. sprintf( JText::_('ACCTEXP_MAILPARTICLE_LOGIN'), JURI::root() );
			}
		}

		$message .= JText::_('ACCTEXP_MAILPARTICLE_FOOTER');

		$subject = html_entity_decode( $subject, ENT_QUOTES, 'UTF-8' );
		$message = html_entity_decode( $message, ENT_QUOTES, 'UTF-8' );

		// Send email to user
		if ( $app->getCfg( 'mailfrom' ) != '' && $app->getCfg( 'fromname' ) != '' ) {
			$adminName2		= $app->getCfg( 'fromname' );
			$adminEmail2	= $app->getCfg( 'mailfrom' );
		} else {
			$rows = xJACLhandler::getSuperAdmins();
			$row2 			= $rows[0];

			$adminName2		= $row2->name;
			$adminEmail2	= $row2->email;
		}

		if ( !$adminonly ) {
			xJ::sendMail( $adminEmail2, $adminEmail2, $email, $subject, $message );
		}

		$aecUser = array();
		if ( is_object( $invoice ) ) {
			if ( !empty( $invoice->params['creator_ip'] ) ) {
				$aecUser['ip'] 	= $invoice->params['creator_ip'];

				// user Hostname (if not deactivated)
				if ( $aecConfig->cfg['gethostbyaddr'] ) {
					$aecUser['isp'] = gethostbyaddr( $invoice->params['creator_ip'] );
				} else {
					$aecUser['isp'] = 'deactivated';
				}
			}
		}

		if ( empty( $aecUser ) ) {
			$aecUser = AECToolbox::aecIP();
		}

		// Send notification to all administrators

		if ( $renew ) {
			$subject2 = sprintf( JText::_('ACCTEXP_SEND_MSG_RENEW'), $name, $app->getCfg( 'sitename' ) );
			$message2 = sprintf( JText::_('ACCTEXP_ASEND_MSG_RENEW'), $adminName2, $app->getCfg( 'sitename' ), $name, $email, $username, $plan->id, $plan->name, $aecUser['ip'], $aecUser['isp'] );
		} else {
			$subject2 = sprintf( JText::_('ACCTEXP_SEND_MSG'), $name, $app->getCfg( 'sitename' ) );
			$message2 = sprintf( JText::_('ACCTEXP_ASEND_MSG'), $adminName2, $app->getCfg( 'sitename' ), $name, $email, $username, $plan->id, $plan->name, $aecUser['ip'], $aecUser['isp'] );
		}

		$subject2 = html_entity_decode( $subject2, ENT_QUOTES, 'UTF-8' );
		$message2 = html_entity_decode( $message2, ENT_QUOTES, 'UTF-8' );

		$admins = AECToolbox::getAdminEmailList();

		foreach ( $admins as $adminemail ) {
			if ( !empty( $adminemail ) ) {
				xJ::sendMail( $adminEmail2, $adminEmail2, $adminemail, $subject2, $message2 );
			}
		}
	}

	function addCustomParams( $params )
	{
		$this->addParams( $params, 'customparams' );
	}

	function getMIflags( $usage, $mi )
	{
		// Create the Params Prefix
		$flag_name = 'MI_FLAG_USAGE_' . strtoupper( $usage ) . '_MI_' . strtoupper( $mi );

		// Filter out the params for this usage and MI
		$mi_params = array();
		if ( $this->params ) {
			foreach ( $this->params as $name => $value ) {
				if ( strpos( $name, $flag_name ) == 0 ) {
					$paramname = strtolower( substr( strtoupper( $name ), strlen( $flag_name ) + 1 ) );
					$mi_params[$paramname] = $value;
				}
			}
		}

		// Only return params if they exist
		if ( count( $mi_params ) ) {
			return $mi_params;
		} else {
			return false;
		}
	}

	function setMIflags( $usage, $mi, $flags )
	{
		// Create the Params Prefix
		$flag_name = 'MI_FLAG_USAGE_' . strtoupper( $usage ) . '_MI_' . $mi;

		// Write to $params array
		foreach ( $flags as $name => $value ) {
			$param_name = $flag_name . '_' . strtoupper( $name );
			$this->params[$param_name] = $value;
		}

		$this->storeload();
	}
}

?>

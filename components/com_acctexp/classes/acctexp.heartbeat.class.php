<?php
/**
 * @version $Id: acctexp.heartbeat.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class aecHeartbeat extends serialParamDBTable
{
 	/** @var int Primary key */
	var $id				= null;
 	/** @var datetime */
	var $last_beat 		= null;

	/**
	 * @param database A database connector object
	 */
	function aecHeartbeat()
	{
	 	parent::__construct( '#__acctexp_heartbeat', 'id' );

	 	$this->load(1);

		if ( empty( $this->last_beat ) ) {
			global $aecConfig;

			$query = 'INSERT INTO #__acctexp_heartbeat'
			. ' VALUES( \'1\', \'' . date( 'Y-m-d H:i:s', ( ( (int) gmdate('U') ) - $aecConfig->cfg['heartbeat_cycle'] * 3600 ) ) . '\' )'
			;
			$this->_db->setQuery( $query );
			$this->_db->query() or die( $this->_db->stderr() );

			$this->load(1);
		}
	}

	function frontendping( $custom=false, $hash=null )
	{
		global $aecConfig;

		if ( !empty( $aecConfig->cfg['disable_regular_heartbeat'] ) && empty( $custom ) ) {
			return;
		}

		if ( empty( $aecConfig->cfg['allow_frontend_heartbeat'] ) && !empty( $custom ) ) {
			return;
		}

		if ( !empty( $custom ) && !empty( $aecConfig->cfg['custom_heartbeat_securehash'] ) ) {
			if ( empty( $hash ) ) {
				return;
			} elseif( $hash != $aecConfig->cfg['custom_heartbeat_securehash'] ) {
				
				$short	= 'custom heartbeat failure';
				$event	= 'Custom Frontend Heartbeat attempted, but faile due to false hashcode: "' . $hash . '"';
				$tags	= 'heartbeat, failure';
				$params = array();

				$eventlog = new eventLog();
				$eventlog->issue( $short, $tags, $event, 128, $params );

				return;
			}
		}

		if ( !empty( $aecConfig->cfg['allow_frontend_heartbeat'] ) && !empty( $custom ) ) {
			aecHeartbeat::ping( 0 );
		} elseif ( !empty( $aecConfig->cfg['heartbeat_cycle'] ) ) {
			aecHeartbeat::ping( $aecConfig->cfg['heartbeat_cycle'] );
		}
	}

	function backendping()
	{
		global $aecConfig;

		if ( !empty( $aecConfig->cfg['heartbeat_cycle_backend'] ) ) {
			$this->ping( $aecConfig->cfg['heartbeat_cycle_backend'] );
		}
	}

	function ping( $configCycle )
	{
		if ( empty( $this->last_beat ) ) {
			$this->load(1);
		}

		if ( empty( $configCycle ) ) {
			$ping = 0;
		} elseif ( $this->last_beat ) {
			$ping	= strtotime( $this->last_beat ) + $configCycle*3600;
		} else {
			$ping = 0;
		}

		if ( ( $ping - ( (int) gmdate('U') ) ) <= 0 ) {
			$this->last_beat = date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
			$this->check();
			$this->store();
			$this->load(1);

			$this->beat();
		} else {
			// sleep, mechanical Hound, but do not sleep
			// kept awake with
			// wolves teeth
		}
	}

	function beat()
	{
		$this->processors = array();
		$this->proc_prepare = array();

		$this->result = array(	'fail_expired' => 0,
								'expired' => 0,
								'pre_expired' => 0,
								'pre_exp_actions' => 0
								);

		// Some cleanup
		$this->deleteTempTokens();

		// Receive maximum pre expiration time
		$pre_expiration = microIntegrationHandler::getMaxPreExpirationTime();

		$subscription_list = $this->getSubscribers( $pre_expiration );

		// Efficient way to check for expired users without checking on each one
		if ( !empty( $subscription_list ) ) {
			foreach ( $subscription_list as $sid => $sub_id ) {
				$subscription = new Subscription();
				$subscription->load( $sub_id );

				if ( !AECfetchfromDB::UserExists( $subscription->userid ) ) {
					unset( $subscription_list[$sid] );

					continue;
				}

				// Check whether this user really is expired
				// If this check fails, the following subscriptions might still be pre-expiration events
				if ( $subscription->is_expired() ) {
					// If we don't have any validation response, expire
					$validate = $this->processorValidation( $subscription, $subscription_list );

					if ( $validate === false ) {
						// There was some fatal error, return.

						return;
					} elseif ( $validate !== true ) {
						if ( $subscription->expire() ) {
							$this->result['expired']++;
						} else {
							$this->result['fail_expired']++;
						}
					}
					
					unset( $subscription_list[$sid] );
				} elseif ( !$subscription->recurring ) {
					break;
				}
			}

			// Only go for pre expiration action if we have at least one user for it
			if ( $pre_expiration && !empty( $subscription_list ) ) {
				// Get all the MIs which have a pre expiration check
				$mi_pexp = microIntegrationHandler::getPreExpIntegrations();

				// Find plans which have the MIs assigned
				$expmi_plans = microIntegrationHandler::getPlansbyMI( $mi_pexp );

				// Filter out the users which dont have the correct plan
				$query = 'SELECT `id`, `userid`'
						. ' FROM #__acctexp_subscr'
						. ' WHERE `id` IN (' . implode( ',', $subscription_list ) . ')'
						. ' AND `plan` IN (' . implode( ',', $expmi_plans ) . ')'
						;
				$this->_db->setQuery( $query );
				$sub_list = $this->_db->loadObjectList();

				if ( !empty( $sub_list ) ) {
					foreach ( $sub_list as $sl ) {
						$metaUser = new metaUser( $sl->userid );
						$metaUser->moveFocus( $sl->id );

						$res = $metaUser->focusSubscription->triggerPreExpiration( $metaUser, $mi_pexp );

						if ( $res ) {
							$this->result['pre_exp_actions'] += $res;
							$this->result['pre_expired']++;
						}
					}
				}
			}
		}

		aecEventHandler::pingEvents();

		// And we're done.
		$this->fileEventlog();
	}

	function getProcessor( $name )
	{
		if ( !isset( $this->processors[$name] ) ) {
			$processor = new PaymentProcessor();
			if ( $processor->loadName( $name ) ) {
				$processor->init();

				$this->processors[$name] = $processor;
			} else {
				// Processor does not exist
				$this->processors[$name] = false;
			}
		}

		return $this->processors[$name];
	}

	function getSubscribers( $pre_expiration )
	{
		$expiration_limit = $this->getExpirationLimit( $pre_expiration );

		// Select all the users that are Active and have an expiration date
		$query = 'SELECT `id`'
				. ' FROM #__acctexp_subscr'
				. ' WHERE `expiration` <= \'' . $expiration_limit . '\''
				. ' AND `expiration` != \'0000-00-00 00:00:00\''
				. ' AND `status` != \'Expired\''
				. ' AND `status` != \'Closed\''
				. ' AND `status` != \'Excluded\''
				. ' AND `status` != \'Pending\''
				. ' ORDER BY `expiration` ASC'
				;
		$this->_db->setQuery( $query );
		return xJ::getDBArray( $this->_db );
	}

	function getExpirationLimit( $pre_expiration )
	{
		if ( $pre_expiration ) {
			// pre-expiration found, search limit set to the maximum pre-expiration time
			return AECToolbox::computeExpiration( ( $pre_expiration + 1 ), 'D', ( (int) gmdate('U') ) );
		} else {
			// No pre-expiration actions found, limiting search to all users who expire until tomorrow (just to be safe)
			return AECToolbox::computeExpiration( 1, 'D', ( (int) gmdate('U') ) );
		}
	}

	function processorValidation( $subscription, $subscription_list )
	{
		$pp = $this->getProcessor( $subscription->type );

		if ( $pp != false ) {
			if ( !isset( $this->proc_prepare[$subscription->type] ) ) {
				// Prepare validation function
				$pp->prepareValidation( $subscription_list );
			
				// But only once
				$this->proc_prepare[$subscription->type] = true;
			}

			$validation = null;

			// Carry out validation if possible
			if ( !empty( $pp ) ) {
				if ( $subscription->recurring ) {
					$validation = $pp->validateSubscription( $subscription->id, $subscription_list );
				}
			}

			return $validation;
		} else {
			return null;
		}
	}

	function fileEventlog()
	{
		// Make sure we have all the language stuff loaded
		$langlist = array( 'com_acctexp.admin' => JPATH_ADMINISTRATOR );

		xJLanguageHandler::loadList( $langlist );

		$short	= JText::_('AEC_LOG_SH_HEARTBEAT');
		$event	= JText::_('AEC_LOG_LO_HEARTBEAT') . ' ';
		$tags	= array( 'heartbeat' );
		$level	= 2;

		if ( $this->result['expired'] ) {
			if ( $this->result['expired'] > 1 ) {
				$event .= 'Expires ' . $this->result['expired'] . ' subscriptions';
			} else {
				$event .= 'Expires 1 subscription';
			}

			if ( $this->result['pre_exp_actions'] || $this->result['fail_expired'] ) {
				$event .= ', ';
			}

			$tags[] = 'expiration';
		}

		if ( $this->result['fail_expired'] ) {
			if ( $this->result['fail_expired'] > 1 ) {
				$event .= 'Failed to expire ' . $this->result['fail_expired'] . ' subscriptions';
			} else {
				$event .= 'Failed to expire 1 subscription';
			}

			$event .= ', please check your subscriptions for problems';

			if ( $this->result['pre_exp_actions'] ) {
				$event .= ', ';
			}

			$tags[] = 'error';
			$level	= 128;
		}

		if ( $this->result['pre_exp_actions'] ) {
			$event .= $this->result['pre_exp_actions'] . ' Pre-expiration action';
			$event .= ( $this->result['pre_exp_actions'] > 1 ) ? 's' : '';
			$event .= ' for ' . $this->result['pre_expired'] . ' subscription';
			$event .= ( $this->result['pre_expired'] > 1 ) ? 's' : '';

			$tags[] = 'pre-expiration';
		}

		if ( strcmp( JText::_('AEC_LOG_LO_HEARTBEAT') . ' ', $event ) === 0 ) {
			$event .= JText::_('AEC_LOG_AD_HEARTBEAT_DO_NOTHING');
		}

		$eventlog = new eventLog();
		$eventlog->issue( $short, implode( ',', $tags ), $event, $level );
	}

	function deleteTempTokens()
	{
		// Delete old token entries
		$query = 'DELETE'
				. ' FROM #__acctexp_temptoken'
				. ' WHERE `created_date` <= \'' . AECToolbox::computeExpiration( "-3", 'H', ( (int) gmdate('U') ) ) . '\''
				;
		$this->_db->setQuery( $query );
		$this->_db->query();
	}

}

?>

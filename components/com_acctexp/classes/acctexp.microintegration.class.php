<?php
/**
 * @version $Id: acctexp.microintegration.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class microIntegrationHandler
{
	function microIntegrationHandler()
	{
		$this->mi_dir = JPATH_SITE . '/components/com_acctexp/micro_integration';
	}

	function getMIList( $limitstart=false, $limit=false, $use_order=false, $name=false, $classname=false )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT id, class_name' . ( $name ? ', name' : '' )
			 	. ' FROM #__acctexp_microintegrations'
		 		. ' WHERE `hidden` = \'0\''
		 		. ( !empty( $classname ) ? ' AND `class_name` = \'' . $classname . '\'' : '' )
			 	. ' GROUP BY ' . ( $use_order ? '`ordering`' : '`id`' )
			 	. ' ORDER BY `class_name`'
			 	;

		if ( !empty( $limitstart ) && !empty( $limit ) ) {
			$query .= 'LIMIT ' . $limitstart . ',' . $limit;
		}

		$db->setQuery( $query );

		$rows = $db->loadObjectList();
		if ( $db->getErrorNum() ) {
			echo $db->stderr();
			return false;
		} else {
			return $rows;
		}
	}

	function compareMIs( $mi, $cmi_id )
	{
		$db = &JFactory::getDBO();

		$excluded_props = array( 'id' );

		$cmi = new microIntegration();
		$cmi->load( $cmi_id );

		if ( !$cmi->callIntegration( true ) ) {
			return false;
		}

		$props = get_object_vars( $mi );

		$similar = true;
		foreach ( $props as $prop => $value ) {
			if ( ( strpos( $prop, '_' ) === 0 ) || in_array( $prop, $excluded_props ) ) {
				// This is an internal or excluded variable
				continue;
			}

			if ( $cmi->$prop != $mi->$prop ) {
				// Nope, this one is different
				$similar = false;
			}
		}

		return $similar;
	}

	function getIntegrationList()
	{
		$list = xJUtility::getFileArray( $this->mi_dir, '', true, true );

		$integration_list = array();
		foreach ( $list as $name ) {
			if ( is_dir( $this->mi_dir . '/' . $name ) ) {
				// Only add directories with the proper structure
				if ( file_exists( $this->mi_dir . '/' . $name . '/' . $name . '.php' ) ) {
					$integration_list[] = $name;
				}
			}
		}

		return $integration_list;
	}

	function getMIsbyPlan( $plan_id )
	{
		$plan = new SubscriptionPlan();
		$plan->load( $plan_id );

		return $plan->getMicroIntegrations();
	}

	function getPlansbyMI( $mi_id, $inherited=true, $extended=false )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_plans'
				;
		$db->setQuery( $query );
		$plans = xJ::getDBArray( $db );

		$plan_list = array();
		foreach ( $plans as $planid ) {
			$plan = new SubscriptionPlan();
			$plan->load( $planid );

			if ( $inherited ) {
				$mis = $plan->getMicroIntegrations();
			} else {
				$misx = $plan->getMicroIntegrationsSeparate();

				$mis = $misx['plan'];
			}

			if ( !empty( $mis ) ) {
				if ( is_array( $mi_id ) ) {
					if ( array_intersect( $mi_id, $mis ) ) {
						if ( $extended ) {
							$plan_list[] = $plan;
						} else {
							$plan_list[] = $planid;
						}
					}
				} else {
					if ( in_array( $mi_id, $mis ) ) {
						if ( $extended ) {
							$plan_list[] = $plan;
						} else {
							$plan_list[] = $planid;
						}
					}
				}
			}
		}

		return $plan_list;
	}

	function getGroupsbyMI( $mi_id, $inherited=true, $extended=false )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_itemgroups'
				;
		$db->setQuery( $query );
		$groups = xJ::getDBArray( $db );

		$group_list = array();
		foreach ( $groups as $groupid ) {
			$group = new ItemGroup();
			$group->load( $groupid );

			if ( $inherited ) {
				$mis = $group->getMicroIntegrations();
			} else {
				$misx = $group->getMicroIntegrationsSeparate();

				$mis = $misx['group'];
			}

			if ( !empty( $mis ) ) {
				if ( is_array( $mi_id ) ) {
					if ( array_intersect( $mi_id, $mis ) ) {
						if ( $extended ) {
							$group_list[] = $group;
						} else {
							$group_list[] = $groupid;
						}
					}
				} else {
					if ( in_array( $mi_id, $mis ) ) {
						if ( $extended ) {
							$group_list[] = $group;
						} else {
							$group_list[] = $groupid;
						}
					}
				}
			}
		}

		return $group_list;
	}

	function userPlanExpireActions( $metaUser, $subscription_plan, $special=null )
	{
		$mi_autointegrations = $this->getAutoIntegrations();

		if ( is_array( $mi_autointegrations ) || ( $subscription_plan !== false ) ) {
			$mis = $subscription_plan->getMicroIntegrations();

			if ( is_array( $mis ) ) {
				$user_auto_integrations = array_intersect( $mis, $mi_autointegrations );
			} else {
				return null;
			}

			if ( count( $user_auto_integrations ) ) {
				foreach ( $user_auto_integrations as $mi_id ) {
					$mi = new microIntegration();
					$mi->load( $mi_id );
					if ( $mi->callIntegration() ) {
						$invoice = null;
						if ( !empty( $metaUser->focusSubscription->id ) ) {
							$invoice = new Invoice();
							$invoice->loadbySubscriptionId( $metaUser->focusSubscription->id );
							
							if ( empty( $invoice->id ) ) {
								$invoice = null;
							}
						}

						$mi->expiration_action( $metaUser, $subscription_plan, $invoice );
						
						if ( !empty( $special ) ) {
							$mi->relayAction( $metaUser, null, $invoice, $subscription_plan, $special );
						}
					}
				}
			}
		}
	}

	function getHacks()
	{
		$integrations = $this->getMIList();

		$hacks = array();
		foreach ( $integrations as $n => $mix ) {
			$mi = new microIntegration();
			$mi->load( $mix->id );
			$mi->callIntegration();

			if ( method_exists( $mi->mi_class, 'hacks' ) ) {
				if ( method_exists( $mi->mi_class, 'detect_application' ) ) {
					if ( $mi->mi_class->detect_application() ) {
						$mihacks = $mi->mi_class->hacks();
						if ( is_array( $mihacks ) ) {
							$hacks = array_merge( $hacks, $mihacks );
						}
					}
				}
			}
		}

		return $hacks;
	}

	function getPreExpIntegrations()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_microintegrations'
				. ' WHERE `active` = \'1\''
				. ' AND `pre_exp_check` > 0'
				;
		$db->setQuery( $query );
		return xJ::getDBArray( $db );
	}

	function getAutoIntegrations()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_microintegrations'
				. ' WHERE `active` = \'1\''
				. ' AND `auto_check` = \'1\''
				;
		$db->setQuery( $query );
		return xJ::getDBArray( $db );
	}

	function getUserChangeIntegrations()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT id'
				. ' FROM #__acctexp_microintegrations'
				. ' WHERE `active` = \'1\''
				. ' AND `on_userchange` = \'1\''
				;
		$db->setQuery( $query );
		return xJ::getDBArray( $db );
	}

	function userchange( $row, $post, $trace = '' )
	{
		$db = &JFactory::getDBO();

		$mi_list = $this->getUserChangeIntegrations();

		if ( is_int( $row ) ) {
			$userid = $row;
		} elseif ( is_string( $row ) ){
			$query = 'SELECT id'
			. ' FROM #__users'
			. ' WHERE username = \'' . $row . '\''
			;
			$db->setQuery( $query );
			$userid = $db->loadResult();
		} elseif ( is_array( $row ) ) {
			$userid = $row['id'];
		} elseif ( !is_object( $row ) ) {
			$userid = $row;
		}

		if ( !is_object( $row ) ) {
			$row = new cmsUser();
			$row->load( $userid );
		}

		if ( !empty( $mi_list ) ) {
			foreach ( $mi_list as $mi_id ) {;
				if ( !is_null( $mi_id ) && ( $mi_id != '' ) && $mi_id ) {
					$mi = new microIntegration();
					$mi->load( $mi_id );
					if ( $mi->callIntegration() ) {
						$mi->on_userchange_action( $row, $post, $trace );
					}
				}
			}
		}
	}

	function getActiveListbyList( $milist )
	{
		if ( empty( $milist ) ) {
			return array();
		}
		
		$db = &JFactory::getDBO();

		$milist = array_unique( $milist );

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_microintegrations'
				. ' WHERE `id` IN (' . xJ::escape( $db, implode( ',', $milist ) ) . ')'
	 			. ' AND `active` = \'1\''
				. ' ORDER BY `ordering` ASC'
				;
		$db->setQuery( $query );
		return xJ::getDBArray( $db );
	}

	function getMaxPreExpirationTime()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT MAX(pre_exp_check)'
				. ' FROM #__acctexp_microintegrations'
				. ' WHERE `active` = \'1\''
				;
		$db->setQuery( $query );
		return $db->loadResult();
	}

	function getDetailedList()
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`, `name`, `desc`, `class_name`'
				. ' FROM #__acctexp_microintegrations'
				. ' WHERE `active` = 1'
			 	. ' AND `hidden` = \'0\''
				. ' ORDER BY ordering'
				;
		$db->setQuery( $query );
		return $db->loadObjectList();
	}
}

class MI
{
	function autoduplicatesettings( $settings, $ommit=array(), $collate=true, $rwEngine=false )
	{
		if ( isset( $settings['lists'] ) ) {
			$lists = $settings['lists'];
			unset( $settings['lists'] );
		} else {
			$lists = array();
		}

		$new_settings = array();
		$new_lists = array();
		foreach ( $settings as $name => $content ) {
			if ( in_array( $name, $ommit ) ) {
				continue;
			}

			if ( $collate ) {
				$new_settings[$name]				= $content;
				$new_settings_exp[$name.'_exp']		= $content;
				$new_settings_pxp[$name.'_pre_exp']	= $content;
			} else {
				$new_settings[$name]			= $content;
				$new_settings[$name.'_exp']		= $content;
				$new_settings[$name.'_pre_exp']	= $content;
			}
		}

		if ( $collate ) {
			$rewriteswitches			= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

			$new_settings				= AECToolbox::rewriteEngineInfo( $rewriteswitches, $new_settings );
			$new_settings_exp			= AECToolbox::rewriteEngineInfo( $rewriteswitches, $new_settings_exp );
			$new_settings_pxp			= AECToolbox::rewriteEngineInfo( $rewriteswitches, $new_settings_pxp );

			$new_settings = array_merge(	$new_settings,
											array( 'aectab_exp_'.$name => array( 'tab', JText::_('MI_E_AUTO_CHECK_NAME'), JText::_('MI_E_AUTO_CHECK_NAME') ) ),
											$new_settings_exp,
											array( 'aectab_pxp_'.$name => array( 'tab', JText::_('MI_E_PRE_EXP_CHECK_NAME'), JText::_('MI_E_PRE_EXP_CHECK_NAME') ) ),
											$new_settings_pxp
										);
		}

		if ( !empty( $new_lists ) ) {
			$new_settings['lists'] = $lists;
		}

		return $new_settings;
	}

	function setError( $error )
	{
		if ( !isset( $this->error ) ) {
			$this->error = array();
		}

		$this->error[] = $error;
	}

	function setWarning( $warning )
	{
		if ( !isset( $this->warning ) ) {
			$this->warning = array();
		}

		$this->warning[] = $warning;
	}

	function issueUniqueEvent( $request, $event, $due_date, $context=array(), $params=array(), $customparams=array() )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_event'
				. ' WHERE `userid` = \'' . $request->metaUser->userid . '\''
				. ' AND `appid` = \'' . $this->id . '\''
				. ' AND `event` = \'' . $event . '\''
				. ' AND `type` = \'mi\''
	 			. ' AND `status` = \'waiting\''
				;
		$db->setQuery( $query );
		$id = $db->loadResult();

		if ( $id ) {
			return null;
		} else {
			return $this->issueEvent( $request, $event, $due_date, $context, $params, $customparams );
		}
	}

	function redateUniqueEvent( $request, $event, $due_date, $context=array(), $params=array(), $customparams=array() )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_event'
				. ' WHERE `userid` = \'' . $request->metaUser->userid . '\''
				. ' AND `appid` = \'' . $this->id . '\''
				. ' AND `event` = \'' . $event . '\''
				. ' AND `type` = \'mi\''
	 			. ' AND `status` = \'waiting\''
				;
		$db->setQuery( $query );
		$id = $db->loadResult();

		if ( $id ) {
			$aecEvent = new aecEvent();
			$aecEvent->load( $id );

			if ( $aecEvent->due_date != $due_date ) {
				$aecEvent->due_date = $due_date;
				$aecEvent->storeload();
			}
		} else {
			return $this->issueEvent( $request, $event, $due_date, $context, $params, $customparams );
		}
	}

	function removeEvents( $request, $event )
	{
		$db = &JFactory::getDBO();

		$query = 'DELETE'
				. ' FROM #__acctexp_event'
				. ' WHERE `userid` = \'' . $request->metaUser->userid . '\''
				. ' AND `appid` = \'' . $this->id . '\''
				. ' AND `event` = \'' . $event . '\''
				. ' AND `type` = \'mi\''
	 			. ' AND `status` = \'waiting\''
				;
		$db->setQuery( $query );
		$db->query();
	}

	function issueEvent( $request, $event, $due_date, $context=array(), $params=array(), $customparams=array() )
	{
		if ( !empty( $request->metaUser ) ) {
			$context['user_id']	= $request->metaUser->userid;
			$userid				= $request->metaUser->userid;
		} else {
			$context['user_id']	= 0;
			$userid				= 0;
		}

		if ( !empty( $request->metaUser->focusSubscription->id ) ) {
			$context['subscription_id'] = $request->metaUser->focusSubscription->id;
		}

		if ( !empty( $request->invoice->id ) ) {
			$context['invoice_id'] = $request->invoice->id;
		}

		if ( !empty( $request->invoice->invoice_number ) ) {
			$context['invoice_number'] = $request->invoice->invoice_number;
		}

		$aecEvent = new aecEvent();

		return $aecEvent->issue( 'mi', $this->info['name'], $this->id, $event, $userid, $due_date, $context, $params, $customparams );
	}

	function aecEventHook( $event )
	{
		$method = 'aecEventHook' . $event->event;

		if ( !method_exists( $this, $method ) ) {
			return null;
		}

		$request = new stdClass();

		$request->parent	=& $this;
		$request->event		=& $event;

		// Establish metaUser object
		if ( !empty( $event->userid ) ) {
			$request->metaUser = new metaUser( $event->userid );
		} else {
			$request->metaUser = false;
		}

		// Select correct subscription
		if ( !empty( $event->context['subscription_id'] ) && !empty( $request->metaUser ) ) {
			$request->metaUser->moveFocus( $event->context['subscription_id'] );
		}

		// Select correct invoice
		if ( !empty( $event->context['invoice_id'] ) ) {
			$request->invoice = new Invoice();
			$request->invoice->load( $event->context['invoice_id'] );
		}

		return $this->$method( $request );
	}

	function getPWrequest( $request )
	{
		if ( isset( $request->post['password_clear'] ) ) {
			return $request->post['password_clear'];
		} elseif ( !empty( $request->post['password'] ) ) {
			return $request->post['password'];
		} elseif ( !empty( $request->post['password2'] ) ) {
			return $request->post['password2'];
		} elseif ( !empty( $request->post['jform']['password'] ) ) {
			return $request->post['jform']['password'];
		} elseif ( !empty( $request->post['jform']['password2'] ) ) {
			return $request->post['jform']['password2'];
		} else {
			return "";
		}
	}
}

class microIntegration extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $active 			= null;
	/** @var int */
	var $system 			= null;
	/** @var int */
	var $hidden 			= null;
	/** @var int */
	var $ordering			= null;
	/** @var string */
	var $name				= null;
	/** @var text */
	var $desc				= null;
	/** @var string */
	var $class_name			= null;
	/** @var text */
	var $params				= null;
	/** @var text */
	var $restrictions		= null;
	/** @var int */
	var $auto_check			= null;
	/** @var int */
	var $pre_exp_check		= null;
	/** @var int */
	var $on_userchange		= null;

	function microIntegration()
	{
		parent::__construct( '#__acctexp_microintegrations', 'id' );
	}

	function declareParamFields()
	{
		return array( 'params', 'restrictions' );
	}

	function declareMultiLangFields()
	{
		return $this->functionProxy( 'declareMultiLangFields', null, array() );
	}

	function functionProxy( $function, $data=null, $default=null )
	{
		if ( !isset( $this->mi_class ) ) {
			return $default;
		}

		if ( method_exists( $this->mi_class, $function ) ) {
			if ( !is_null( $data ) ) {
				return $this->mi_class->$function( $data );
			} else {
				return $this->mi_class->$function();
			}
		} else {
			return $default;
		}
	}

	function check()
	{
		if ( isset( $this->settings ) ) {
			unset( $this->settings );
		}

		if ( isset( $this->mi_class ) ) {
			unset( $this->mi_class );
		}

		if ( isset( $this->info ) ) {
			unset( $this->info );
		}

		return parent::check();
	}

	function mi_exists( $mi_id )
	{
		$query = 'SELECT count(*)'
				. ' FROM #__acctexp_microintegrations'
				. ' WHERE `id` = \'' . $mi_id . '\''
				;
		$this->_db->setQuery( $query );
		return $this->_db->loadResult();
	}

	function callDry( $mi_name )
	{
		$this->class_name = 'mi_' . $mi_name;

		return $this->callIntegration( true );
	}

	function callIntegration( $override=false )
	{
		$handle = str_replace( 'mi_', '', $this->class_name );

		$basepath = JPATH_SITE . '/components/com_acctexp/micro_integration/' . $handle;

		$filename = $basepath . '/' . $handle . '.php';

		$file_exists = file_exists( $filename );

		if ( ( ( !$this->active && !empty( $this->id ) ) || !$file_exists ) && !$override ) {
			// MI does not exist or is deactivated
			return false;
		} elseif ( $file_exists ) {
			include_once $filename;

			if ( empty( $this->id ) && !$override ) {
				$this->copyAssets();
			} elseif ( $override ) {
				xJLanguageHandler::loadList( array( 'com_acctexp.mi.' . $handle => $basepath ) );
			}

			if ( !$override ) {
				xJLanguageHandler::loadList( array(	'com_acctexp.mi.' . $handle => JPATH_SITE ) );
			}

			$class = $this->class_name;

			$this->mi_class = new $class();
			$this->mi_class->id = $this->id;

			$this->getInfo();

			if ( is_null( $this->name ) || ( $this->name == '' ) ) {
				$this->name = $this->info['name'];
			}

			if ( is_null( $this->desc ) || ( $this->desc == '' ) ) {
				$this->desc = $this->info['desc'];
			}

			$this->settings				=& $this->params;
			$this->mi_class->info		=& $this->info;
			$this->mi_class->settings	=& $this->settings;

			return true;
		} else {
			return false;
		}
	}

	function copyAssets()
	{
		$handle = str_replace( 'mi_', '', $this->class_name );

		$syslangpath = JPATH_SITE . '/language';

		$languages = xJLanguageHandler::getSystemLanguages();

		$langpath = JPATH_SITE . '/components/com_acctexp/micro_integration/' . $handle . '/language';

		foreach ( $languages as $l ) {
			$lpath = $langpath . '/' . $l;

			if ( is_dir( $lpath ) && is_dir( $syslangpath . '/' . $l ) ) {
				$filename = $l . '.com_acctexp.mi.' . $handle . '.ini';

				$source = $lpath . '/' . $filename;
				
				if ( !file_exists( $source ) ) {
					continue;
				}

				$dest = $syslangpath . '/' . $l . '/' . $filename;

				copy( $source, $dest );
			}
		}
	}

	function checkPermission( $metaUser, $invoice )
	{
		$permission = true;

		if ( !empty( $this->restrictions['has_restrictions'] ) ) {
			if ( is_object( $invoice ) ) {
				if ( !empty( $invoice->params['stickyMIpermissions'][$this->id] ) ) {
					return true;
				}
			}

			$restrictions = $this->getRestrictionsArray();

			$permission = aecRestrictionHelper::checkRestriction( $restrictions, $metaUser );

			if ( !empty( $this->restrictions['sticky_permissions'] ) && is_object( $invoice ) && $permission ) {
				if ( is_a( $invoice, 'Invoice' ) ) {
					if ( empty( $invoice->params['stickyMIpermissions'] ) ) {
						$invoice->params['stickyMIpermissions'] = array();
					}

					$invoice->params['stickyMIpermissions'][$this->id] = $permission;
					if ( $invoice->id ) {
						$invoice->storeload();
					}
				}
			}

			return $permission;
		} else {
			return true;
		}
	}

	function getRestrictionsArray()
	{
		return aecRestrictionHelper::getRestrictionsArray( $this->restrictions );
	}

	function action( &$metaUser, $exchange=null, $invoice=null, $objplan=null )
	{
		$add = $params = false;

		return $this->relayAction( $metaUser, $exchange, $invoice, $objplan, 'action', $add, $params );
	}

	function pre_expiration_action( &$metaUser, $objplan=null )
	{
		if ( method_exists( $this->mi_class, 'pre_expiration_action' ) || method_exists( $this->mi_class, 'relayAction' ) ) {
			$userflags = $metaUser->meta->getMIParams( $this->id, $objplan->id );

			// We need the standard variables and their uppercase pendants
			// System MI vars have to be stored and will automatically converted to uppercase
			$spc	= strtoupper( 'system_preexp_call' );
			$spca	= strtoupper( 'system_preexp_call_abandoncheck' );

			$current_expiration = strtotime( $metaUser->focusSubscription->expiration );

			// Check whether we have userflags to work with
			if ( is_array( $userflags ) && !empty( $userflags ) ) {
				// Check whether flags exist
				if ( isset( $userflags[$spc] ) ) {
					if ( $current_expiration == $userflags[$spc] ) {
						// This is a retrigger as expiration dates are equal => break
						return false;
					} else {
						if ( ( (int) gmdate('U') ) > $current_expiration ) {
							// This trigger comes too late as the expiration already happened => break
							return false;
						}
					}
				}

				if ( isset( $userflags[$spca] ) ) {
					if ( ( ( (int) gmdate('U') ) ) < ( $userflags[$spca] + 300 ) ) {
						// There already was a trigger in the last 5 minutes
						return false;
					}
				}
			}

			$userflags[$spc]	= $current_expiration;
			$userflags[$spca]	= (int) gmdate('U');

			// Create the new flags
			$metaUser->meta->setMIParams( $this->id, $objplan->id, $userflags );

			$metaUser->meta->storeload();

			$add = $params = false;

			return $this->relayAction( $metaUser, null, null, $objplan, 'pre_expiration_action', $add, $params );
		} else {
			return null;
		}
	}

	function expiration_action( &$metaUser, $objplan=null, $invoice=null )
	{
		// IF ExpireAllInstances=0 AND hasMoreThanOneInstance -> return null
		if ( empty( $this->settings['_aec_global_exp_all'] ) ) {
			if ( $metaUser->getMIcount( $this->id ) > 1 ) {
				// We have more instances than this one attached to the user, pass on.
				return null;
			}
		}

		$add = $params = false;

		return $this->relayAction( $metaUser, null, $invoice, $objplan, 'expiration_action', $add, $params );
	}

	function relayAction( &$metaUser, $exchange=null, $invoice=null, $objplan=null, $stage='action', &$add, &$params )
	{
		if ( $stage == 'action' ) {
			if ( isset( $this->settings['_aec_action'] ) ) {
				if ( !$this->settings['_aec_action'] ) {
					return null;
				}
			}

			if ( isset( $this->settings['_aec_only_first_bill'] ) && !empty( $invoice ) ) {
				if ( $this->settings['_aec_only_first_bill'] && ( $invoice->counter > 1 ) ) {
					return null;
				}
			}
		}

		if ( !$this->checkPermission( $metaUser, $invoice ) ) {
			return null;
		}

		// Exchange Settings
		if ( is_array( $exchange ) && !empty( $exchange ) ) {
			$this->exchangeSettings( $exchange );
		}

		$request = new stdClass();
		$request->action	=	$stage;
		$request->parent	=&	$this;
		$request->metaUser	=&	$metaUser;
		$request->invoice	=&	$invoice;
		$request->plan		=&	$objplan;

		if ( empty( $params ) ) {
			$params	=&	$metaUser->meta->getMIParams( $this->id, $objplan->id );
		}

		$request->params	=&	$params;

		if ( $add !== false ) {
			$request->add	=& $add;
		} else {
			$request->add	= null;
		}

		// Call Action
		if ( method_exists( $this->mi_class, $stage ) ) {
			$return = $this->mi_class->$stage( $request );
		} elseif ( method_exists( $this->mi_class, 'relayAction' ) ) {
			switch ( $stage ) {
				case 'action':
					$request->area = '';
					break;
				case 'pre_expiration_action':
					$request->area = '_pre_exp';
					break;
				case 'expiration_action':
					$request->area = '_exp';
					break;
				default:
					$request->area = $stage;
					break;
			}

			$return = $this->mi_class->relayAction( $request );
		} else {
			return null;
		}

		// Gather Errors and Warnings
		$errors = $this->getErrors();
		$warnings = $this->getWarnings();

		if ( ( $errors !== false ) || ( $warnings !== false )  ) {
			$level = 2;
			$error = 'The MI "' . $this->name . '" ('.$this->class_name.') encountered problems.';

			if ( $warnings !== false ) {
				$error .= ' ' . $warnings;
				$level = 32;
			}

			if ( $errors !== false ) {
				$error .= ' ' . $errors;
				$level = 128;
			}

			if ( !empty( $request->invoice->invoice_number ) ) {
				$params = array( 'invoice_number' => $request->invoice->invoice_number );
			} else {
				$params = array();
			}

			$eventlog = new eventLog();
			$eventlog->issue( 'MI application problems', 'mi, problems, '.$this->class_name, $error, $level, $params );
		}

		// If returning fatal error, issue additional entry
		if ( $return === false ) {
			

			$error = 'The MI "' . $this->name . '" ('.$this->class_name.') could not be carried out due to errors, plan application was halted';

			$err = $this->_db->getErrorMsg();
			if ( !empty( $err ) ) {
				$error .= ' Last Database Error: ' . $err;
			}

			if ( !empty( $request->invoice->invoice_number ) ) {
				$params = array( 'invoice_number' => $request->invoice->invoice_number );
			} else {
				$params = array();
			}

			$eventlog = new eventLog();
			$eventlog->issue( 'MI application failed', 'mi, failure, '.$this->class_name, $error, 128, $params );
		}

		return $return;
	}

	function getMIform( $plan, $metaUser )
	{
		if ( !$this->checkPermission( $metaUser, null ) ) {
			return null;
		}

		$params	= $metaUser->meta->getMIParams( $this->id, $plan->id, false );

		$request = new stdClass();
		$request->action	=	'getMIform';
		$request->parent	=&	$this;
		$request->metaUser	=&	$metaUser;
		$request->plan		=&	$plan;
		$request->params	=&	$params;

		return $this->functionProxy( 'getMIform', $request );
	}

	function verifyMIform( $plan, $metaUser, $params=null )
	{
		if ( !$this->checkPermission( $metaUser, null ) ) {
			return null;
		}

		if ( is_null( $params ) ) {
			$params	= $metaUser->meta->getMIParams( $this->id, $plan->id, false );
		}

		$request = new stdClass();
		$request->action	=	'verifyMIform';
		$request->parent	=&	$this;
		$request->metaUser	=&	$metaUser;
		$request->plan		=&	$plan;
		$request->params	=&	$params;

		return $this->functionProxy( 'verifyMIform', $request );
	}

	function getMIformParams( $plan, $metaUser, $errors )
	{
		$mi_form = $this->getMIform( $plan, $metaUser );

		$params		= array();
		$lists		= array();
		$validation	= array();
		if ( !empty( $mi_form ) ) {
			$pref = 'mi_'.$this->id.'_';

			if ( !empty( $mi_form['lists'] ) ) {
				foreach ( $mi_form['lists'] as $lname => $lcontent ) {
					$tempname = $pref.$lname;
					$lists[$tempname] = str_replace( '"'.$lname.'"', '"'.$tempname.'"', $lcontent );
				}

				unset( $mi_form['lists'] );
			}

			if ( !empty( $mi_form['validation'] ) ) {
				foreach ( $mi_form['validation'] as $k => $v ) {

					foreach ( $v as $lname => $lcontent ) {
						$tempname = $pref.$lname;
						$validation[$k][$tempname] = str_replace( '"'.$lname.'"', '"'.$tempname.'"', $lcontent );
					}
				}

				unset( $mi_form['validation'] );
			}

			$params[$pref.'remap_area'] = array( 'subarea_change', $this->class_name );

			if ( array_key_exists( $this->id, $errors ) ) {
				$params[] = array( 'divstart', null, null, 'confirmation_error_bg' );
				//$params[] = array( 'h2', $errors[$mi->id] );
			}

			foreach ( $mi_form as $fname => $fcontent ) {
				$params[$pref.$fname] = $fcontent;
			}

			if ( array_key_exists( $this->id, $errors ) ) {
				$params[] = array( 'divend' );
			}
		}

		$params['lists'] = $lists;
		$params['validation'] = $validation;

		return $params;
	}

	function getErrors()
	{
		if ( !empty( $this->mi_class->error ) && is_array( $this->mi_class->error ) ) {
			if ( count( $this->mi_class->error ) > 1 ) {
				$return = 'Error:';
			} else {
				$return = 'Errors:';
			}

			foreach ( $this->mi_class->error as $error ) {
				$return .= ' ' . $error;
			}
		} else {
			return false;
		}

		return $return;
	}

	function getWarnings()
	{
		if ( !empty( $this->mi_class->warning ) ) {
			if ( count( $this->mi_class->warning ) > 1 ) {
				$return = 'Warning:';
			} else {
				$return = 'Warnings:';
			}

			foreach ( $this->mi_class->warning as $warning ) {
				$return .= ' ' . $warning;
			}
		} else {
			return false;
		}

		return $return;
	}

	function aecEventHook( $event )
	{
		if ( empty( $this->mi_class ) ) {
			$this->callIntegration();
		}

		return $this->functionProxy( 'aecEventHook', $event );
	}

	function on_userchange_action( $row, $post, $trace )
	{
		$request = new stdClass();
		$request->parent			=& $this;
		$request->row				=& $row;
		$request->post				=& $post;
		$request->trace				=& $trace;
		$request->metaUser			= null;

		if ( !empty( $row->id ) ) {
			$metaUser = new metaUser( $row->id );

			$request->metaUser		=& $metaUser;
		}

		return $this->functionProxy( 'on_userchange_action', $request );
	}

	function profile_info( $metaUser )
	{
		$request = new stdClass();
		$request->parent	=&	$this;
		$request->metaUser	=&	$metaUser;

		return $this->functionProxy( 'profile_info', $request );
	}

	function admin_info( $metaUser )
	{
		$request = new stdClass();
		$request->parent	=&	$this;
		$request->metaUser	=&	$metaUser;

		return $this->functionProxy( 'admin_info', $request );
	}

	function profile_form( $metaUser, $backend=false )
	{
		$request = new stdClass();
		$request->parent	=&	$this;
		$request->metaUser	=&	$metaUser;
		$request->params	=&	$metaUser->meta->getMIParams( $this->id );
		$request->backend	=	$backend;

		$settings = $this->functionProxy( 'profile_form', $request, array() );

		if ( !empty( $settings ) ) {
			foreach ( $settings as $k => $v ) {
				if ( isset( $request->params[$k] ) && !isset( $v[3] ) ) {
					$settings[$v][3] = $request->params[$k];
				}
			}
		}

		return $settings;
	}

	function profile_form_save( $metaUser, $params )
	{
		$request = new stdClass();
		$request->parent		=&	$this;
		$request->metaUser		=&	$metaUser;
		$request->old_params	=	$metaUser->meta->getMIParams( $this->id );
		$request->params		=	$params;

		return $this->functionProxy( 'profile_form_save', $request );
	}

	function admin_form( $metaUser )
	{
		$request = new stdClass();
		$request->parent	=&	$this;
		$request->metaUser	=&	$metaUser;
		$request->params	=&	$metaUser->meta->getMIParams( $this->id );

		$settings = $this->functionProxy( 'admin_form', $request, array() );

		if ( !empty( $settings ) ) {
			foreach ( $settings as $k => $v ) {
				if ( isset( $request->params[$k] ) && !isset( $v[3] ) ) {
					$settings[$k][3] = $request->params[$k];
				}
			}
		}

		return $settings;
	}

	function admin_form_save( $metaUser, $params )
	{
		$request = new stdClass();
		$request->parent		=&	$this;
		$request->metaUser		=&	$metaUser;
		$request->old_params	=	$metaUser->meta->getMIParams( $this->id );
		$request->params		=	$params;

		return $this->functionProxy( 'admin_form_save', $request );
	}

	function getInfo()
	{
		$lang = JFactory::getLanguage();

		if ( method_exists( $this->mi_class, 'Info' ) ) {
			$this->info = $this->mi_class->Info();
		} else {
			$nname = strtoupper( 'aec_' . $this->class_name . '_name' );
			$ndesc = strtoupper( 'aec_' . $this->class_name . '_desc' );

			$this->info = array();
			if ( $lang->hasKey( $nname ) && $lang->hasKey( $ndesc ) ) {
				$this->info['name'] = JText::_( $nname );
				$this->info['desc'] = JText::_( $ndesc );
			} else {
				$this->info['name'] = 'NONAME';
				$this->info['desc'] = 'NODESC';
			}
		}
	}

	function getGeneralSettings()
	{
		$settings['name']					= array( 'inputC', '' );
		$settings['desc']					= array( 'inputD', '' );
		$settings['active']					= array( 'toggle', 1 );
		$settings['_aec_action']			= array( 'toggle', 1 );
		$settings['_aec_only_first_bill']	= array( 'toggle', 0 );
		$settings['auto_check']				= array( 'toggle', 1 );
		$settings['_aec_global_exp_all']	= array( 'toggle', 0 );
		$settings['on_userchange']			= array( 'toggle', 1 );
		$settings['pre_exp_check']			= array( 'inputB', '' );
		$settings['has_restrictions']		= array( 'toggle', 0 );
		$settings['sticky_permissions']		= array( 'toggle', 1 );

		return $settings;
	}

	function getCommonData()
	{
		$common = array();
		if ( method_exists( $this->mi_class, 'CommonData' ) && empty( $this->settings ) ) {
			$common_data = $this->mi_class->CommonData();

			if ( !empty( $common_data ) ) {
					

					$query = 'SELECT id'
						 	. ' FROM #__acctexp_microintegrations'
						 	. ' WHERE `class_name` = \'' . $this->class_name . '\''
						 	. ' ORDER BY `id` DESC'
						 	;
					$this->_db->setQuery( $query );
					$last_id = $this->_db->loadResult();

					if ( $last_id ) {
						$last_mi = new microIntegration();
						$last_mi->load( $last_id );

						foreach ( $common_data as $key ) {
							// Give the defaults a chance if this instance has empty fields
							if ( !empty( $last_mi->settings[$key] ) ) {
								$common[$key] = $last_mi->settings[$key];
							}
						}
					}
			}
		}

		return $common;
	}

	function getSettings()
	{
		// See whether an install is neccessary (and possible)
		if ( method_exists( $this->mi_class, 'checkInstallation' ) && method_exists( $this->mi_class, 'install' ) ) {
			if ( !$this->mi_class->checkInstallation() ) {
				$this->mi_class->install();
			}
		}

		if ( method_exists( $this->mi_class, 'Settings' ) ) {
			if ( empty( $this->settings ) ) {
				$common = $this->getCommonData();
			} else {
				$common = array();
			}

			if ( method_exists( $this->mi_class, 'Defaults' ) && ( count( $this->settings ) < 4 ) ) {
				$defaults = $this->mi_class->Defaults();
			} else {
				$defaults = array();
			}

			$this->mi_class->_parent =& $this;

			$settings = $this->mi_class->Settings();

			// Autoload Params if they have not been called in by the MI
			foreach ( $settings as $name => $setting ) {
				// Do we have a parameter at first position?
				if ( isset( $setting[1] ) && !isset( $setting[3] ) ) {
					if ( isset( $this->settings[$name] ) ) {
						$settings[$name][3] = $this->settings[$name];
					} elseif( isset( $common[$name] ) ) {
						$settings[$name][3] = $common[$name];
					} elseif( isset( $defaults[$name] ) ) {
						$settings[$name][3] = $defaults[$name];
					}
				} else {
					if ( isset( $this->settings[$name] ) ) {
						$settings[$name][1] = $this->settings[$name];
					} elseif( isset( $common[$name] ) ) {
						$settings[$name][1] = $common[$name];
					} elseif( isset( $defaults[$name] ) ) {
						$settings[$name][1] = $defaults[$name];
					}
				}
			}

			return $settings;
		} else {
			return false;
		}
	}

	function exchangeSettings( $exchange )
	{
		 if ( !empty( $exchange ) ) {
			 foreach ( $exchange as $key => $value ) {
				if( is_string( $value ) ) {
					if ( strcmp( $value, '[[SET_TO_NULL]]' ) === 0 ) {
						// Exception for NULL case
						$this->settings[$key] = null;
					} else {
						$this->settings[$key] = $value;
					}
				} else {
					$this->settings[$key] = $value;
				}
			 }
		 }
	}

	function savePostParams( $array )
	{
		// Strip out params that we don't need
		$params = $this->stripNonParams( $array );

		// Filter out restrictions
		$fixed = aecRestrictionHelper::paramList();

		$fixed[] = 'has_restrictions';
		$fixed[] = 'sticky_permissions';

		$restrictions = array();
		foreach ( $fixed as $varname ) {
			if ( !isset( $array[$varname] ) ) {
				continue;
			}

			$restrictions[$varname] = $array[$varname];

			unset( $array[$varname] );
		}

		$this->restrictions = $restrictions;

		// Check whether there is a custom function for saving params
		$new_params = $this->functionProxy( 'saveparams', $params, $params );

		$this->name				= $array['name'];
		$this->desc				= $array['desc'];
		$this->active			= $array['active'];
		$this->auto_check		= $array['auto_check'];
		$this->on_userchange	= $array['on_userchange'];
		$this->pre_exp_check	= $array['pre_exp_check'];

		if ( !empty( $new_params['rebuild'] ) ) {
			

			$planlist = MicroIntegrationHandler::getPlansbyMI( $this->id );

			foreach ( $planlist as $planid ) {
				$plan = new SubscriptionPlan();
				$plan->load( $planid );

				$userlist = SubscriptionPlanHandler::getPlanUserlist( $planid );
				foreach ( $userlist as $userid ) {
					$metaUser = new metaUser( $userid );

					if ( $metaUser->cmsUser->id ) {
						$this->action( $metaUser, $params, null, $plan );
					}
				}
			}

			$newparams['rebuild'] = 0;
		}

		if ( !empty( $new_params['remove'] ) ) {
			

			$planlist = MicroIntegrationHandler::getPlansbyMI( $this->id );

			foreach ( $planlist as $planid ) {
				$plan = new SubscriptionPlan();
				$plan->load( $planid );

				$userlist = SubscriptionPlanHandler::getPlanUserlist( $planid );
				foreach ( $userlist as $userid ) {
					$metaUser = new metaUser( $userid );

					$this->expiration_action( $metaUser, $plan );
				}
			}

			$newparams['remove'] = 0;
		}

		$this->params = $new_params;

		return true;
	}

	function stripNonParams( $array )
	{
		// All variables of the class have to be stripped out
		$vars = get_class_vars( 'microIntegration' );

		foreach ( $vars as $name => $blind ) {
			if ( isset( $array[$name] ) ) {
				unset( $array[$name] );
			}
		}

		return $array;
	}

	function registerProfileTabs()
	{
		if ( method_exists( $this->mi_class, 'registerProfileTabs' ) ) {
			$response = $this->mi_class->registerProfileTabs();
		} else {
			$response = null;
		}

		return $response;
	}

	function customProfileTab( $action, $metaUser )
	{
		if ( empty( $this->settings ) ) {
			$this->getSettings();
		}

		$method = 'customtab_' . $action;

		if ( method_exists( $this->mi_class, $method ) ) {
			

			$request = new stdClass();
			$request->parent			=& $this;
			$request->metaUser			=& $metaUser;

			$invoice = new Invoice();
			$invoice->loadbySubscriptionId( $metaUser->objSubscription->id );

			$request->invoice			=& $invoice;


			return $this->mi_class->$method( $request );
		} else {
			return false;
		}
	}

	function delete ()
	{
		// Maybe this function needs special actions on delete?
		// TODO: There should be a way to manage complete deletion of use of an MI type
		if ( method_exists( $this->mi_class, 'delete' ) ){
			$this->mi_class->delete();
		}
	}
}

?>

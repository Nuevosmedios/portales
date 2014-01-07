<?php
/**
 * @version $Id: acctexp.paymentprocessor.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class PaymentProcessorHandler
{

	function PaymentProcessorHandler()
	{
		$this->pp_dir = JPATH_SITE . '/components/com_acctexp/processors';
	}

	function getProcessorList()
	{
		$list = xJUtility::getFileArray( $this->pp_dir, null, true, true );

		$pp_list = array();
		foreach ( $list as $name ) {
			if ( is_dir( $this->pp_dir . '/' . $name ) ) {
				// Only add directories with the proper structure
				if ( file_exists( $this->pp_dir . '/' . $name . '/' . $name . '.php' ) ) {
					$pp_list[] = $name;
				}
			}
		}

		return $pp_list;
	}

	function getProcessorSelectList( $multiple=true, $selected=array() )
	{
		$gwlist					= $this->getProcessorList();

		$gw_list_enabled		= array();
		$gw_list_enabled_html	= array();

		$gwlist_selected = array();

		asort($gwlist);

		$ppsettings = array();

		foreach ( $gwlist as $gwname ) {
			$pp = new PaymentProcessor();
			if ( $pp->loadName( $gwname ) ) {
				$pp->getInfo();

				if ( $pp->processor->active ) {
					// Add to Active List
					$gw_list_enabled[]->value = $gwname;

					// Add to selected Description List if existing in db entry
					if ( !empty( $selected ) ) {
						if ( $multiple || is_array( $selected ) ) {
							if ( in_array( $gwname, $selected ) ) {
								$gwlist_selected[]->value = $gwname;
							}
						} else {
							if ( $gwname == $selected ) {
								$gwlist_selected = new stdClass();
								$gwlist_selected->value = $gwname;
							}
						}
						
					}

					// Add to Description List
					$gw_list_enabled_html[] = JHTML::_('select.option', $gwname, $pp->info['longname'] );

				}
			}
		}

		if ( !$multiple && is_array( $gwlist_selected ) ) {
			$gwlist_selected = $gwlist_selected[0];
		} elseif ( $multiple && !is_array( $gwlist_selected ) ) {
			$gwlist_selected = array( $gwlist_selected );
		}

		return JHTML::_('select.genericlist', $gw_list_enabled_html, 'gwlist'.($multiple ? '[]' : ''), 'size="' . max(min(count($gw_list_enabled), 12), 3) . '"'.($multiple ? ' multiple="multiple"' : ''), 'value', 'text', $gwlist_selected);
	}

	function getProcessorIdfromName( $name )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_config_processors'
				. ' WHERE `name` = \'' . xJ::escape( $db, $name ) . '\'';
		$db->setQuery( $query );

		return $db->loadResult();
	}

	function getProcessorNamefromId( $id )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `name`'
				. ' FROM #__acctexp_config_processors'
				. ' WHERE `id` = \'' . xJ::escape( $db, $id ) . '\'';
		$db->setQuery( $query );

		return $db->loadResult();
	}

	/**
	 * gets installed and active processors
	 *
	 * @param bool	$active		get only active objects
	 * @return array of (active) payment processors
	 */
	function getInstalledObjectList( $active = false, $simple = false )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `name`' . ( $simple ? '' : ', `active`, `id`' )
				. ' FROM #__acctexp_config_processors'
				;
		if ( $active ) {
			$query .= ' WHERE `active` = \'1\'';
		}
		$db->setQuery( $query );

		if ( $simple ) {
			return xJ::getDBArray( $db );
		} else {
			return $db->loadObjectList();
		}
	}

	function getInstalledNameList($active=false)
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `name`'
				. ' FROM #__acctexp_config_processors'
				;
		if ( $active !== false ) {
			$query .= ' WHERE `active` = \'' . $active . '\'';
		}
		$db->setQuery( $query );

		return xJ::getDBArray( $db );
	}

	function getObjectList( $array, $getinfo=false, $getsettings=false )
	{
		$excluded = array( 'free', 'none', 'transfer' );

		$list = array();
		foreach ( $array as $ppname ) {
			if ( empty( $ppname ) || in_array( $ppname, $excluded ) ) {
				continue;
			}

			$pp = new PaymentProcessor();

			if ( $pp->loadName( $ppname ) ) {
				$pp->init();

				if ( $getinfo ) {
					$pp->getInfo();
				}

				if ( $getsettings ) {
					$pp->getSettings();
				}
			}

			$list[$ppname] = $pp;
		}

		return $list;
	}

	function getSelectList( $selection="", $installed=false )
	{
		$pplist					= $this->getProcessorList();
		$pp_installed_list		= $this->getInstalledObjectList( false, true );

		asort($pplist);

		$pp_list_html			= array();
		foreach ( $pplist as $ppname ) {
			if ( in_array( $ppname, $pp_installed_list ) && !$installed ) {
				continue;
			} elseif ( !in_array( $ppname, $pp_installed_list ) && $installed ) {
				continue;
			}

			// Load Payment Processor
			$pp = new PaymentProcessor();
			if ( $pp->loadName( $ppname ) ) {
				xJLanguageHandler::loadList( array(	'com_acctexp.processors.' . $ppname => JPATH_SITE . '/components/com_acctexp/processors/' . $ppname ) );

				$pp->getInfo();

				if ( !empty( $pp->info['longname'] ) ) {
					$name = $pp->info['longname'];
				} else {
					$name = ucwords( str_replace( '_', ' ', strtolower( $ppname ) ) );
				}

				// Add to general PP List
				$pp_list_html[] = JHTML::_('select.option', $ppname, $name );
			}
		}

		$size = $installed ? 1 : max(min(count($pplist), 24), 2);

		return JHTML::_('select.genericlist', $pp_list_html, 'processor', 'size="' . $size . '"', 'value', 'text', $selection );
	}
}

class PaymentProcessor
{
	/** var object **/
	var $pph = null;
	/** var int **/
	var $id = null;
	/** var string **/
	var $processor_name = null;
	/** var string **/
	var $path = null;
	/** var object **/
	var $processor = null;
	/** var array **/
	var $settings = null;
	/** var array **/
	var $info = null;

	function PaymentProcessor()
	{
		// Init Payment Processor Handler
		$this->pph = new PaymentProcessorHandler();
	}

	function loadName( $name )
	{
		if ( (strtolower( $name ) == 'free') || (strtolower( $name ) == 'none') ) {
			return null;
		}

		$db = &JFactory::getDBO();

		// Set Name
		$this->processor_name = strtolower( $name );

		// See if the processor is installed & set id
		$query = 'SELECT id, active'
				. ' FROM #__acctexp_config_processors'
				. ' WHERE `name` = \'' . $this->processor_name . '\''
				;
		$db->setQuery( $query );
		$res = $db->loadObject();

		if ( !empty( $res ) && is_object( $res ) ) {
			$this->id = $res->id ? $res->id : 0;
		}

		$this->path = $this->pph->pp_dir . '/' . $this->processor_name;

		$file = $this->path . '/' . $this->processor_name . '.php';

		// Check whether processor exists
		if ( file_exists( $file ) ) {
			// Call Integration file
			include_once $this->path . '/' . $this->processor_name . '.php';

			// Initiate Payment Processor Class
			$class_name = 'processor_' . $this->processor_name;

			$this->processor = new $class_name();
			$this->processor->load( $this->id );
			$this->processor->name = $this->processor_name;

			if ( is_object( $res ) ) {
				$this->processor->active = $res->active;
			} else {
				$this->processor->active = 0;
			}

			return true;
		} else {
			$short	= 'processor loading failure';
			$event	= 'When composing processor info list, tried to load processor: ' . $name;
			$tags	= 'processor,loading,error';
			$params = array();

			$eventlog = new eventLog();
			$eventlog->issue( $short, $tags, $event, 128, $params );

			return false;
		}
	}

	function getNameById( $ppid )
	{
		$db = &JFactory::getDBO();

		// Fetch name from db and load processor
		$query = 'SELECT `name`'
				. ' FROM #__acctexp_config_processors'
				. ' WHERE `id` = \'' . $ppid . '\''
				;
		$db->setQuery( $query );
		$name = $db->loadResult();

		if ( $name ) {
			return $name;
		} else {
			return false;
		}
	}

	function loadId( $ppid )
	{
		$name = $this->getNameById( $ppid );

		if ( $name ) {
			return $this->loadName( $name );
		} else {
			return false;
		}
	}

	function fullInit()
	{
		if ( $this->init() ) {
			$this->getInfo();
			$this->getSettings();

			return true;
		} else {
			return false;
		}
	}

	function init()
	{
		if ( !$this->id ) {
			// Install and recurse
			$this->install();
			$this->init();
		} else {
			xJLanguageHandler::loadList( array(	'com_acctexp.processors.' . $this->processor_name => JPATH_SITE ) );

			// Initiate processor from db
			if ( is_object( $this->processor ) && empty( $this->processor->id ) ) {
				return $this->processor->load( $this->id );
			} else {
				return true;
			}
		}
	}

	function install()
	{
		// Create new db entry
		$this->processor->load( 0 );

		$this->copyAssets();

		xJLanguageHandler::loadList( array(	'com_acctexp.processors.' . $this->processor_name => JPATH_SITE ) );

		// Call default values for Info and Settings
		$this->getInfo();
		$this->getSettings();

		// Set name and activate
		$this->processor->name		= $this->processor_name;
		$this->processor->active	= 1;

		// Set values from defaults and store
		$this->processor->info = $this->info;
		$this->processor->settings = $this->settings;
		$this->processor->storeload();

		$this->id = $this->processor->id;
	}

	function copyAssets()
	{
		$png = $this->processor_name . '.png';

		$source = $this->path . '/media/images/' . $png;

		if ( file_exists( $source ) ) {
			$dest = JPATH_SITE . '/media/com_acctexp/images/site/' . $png;
			if ( !file_exists( $dest ) && file_exists( $source ) ) {
				copy( $source, $dest );
			}
		}

		$syslangpath = JPATH_SITE . '/language';

		$languages = xJLanguageHandler::getSystemLanguages();

		$langpath = $this->path . '/language';

		foreach ( $languages as $l ) {
			$lpath = $langpath . '/' . $l;

			if ( is_dir( $lpath ) && is_dir( $syslangpath . '/' . $l ) ) {
				$filename = $l . '.com_acctexp.processors.' . $this->processor_name . '.ini';

				$source = $lpath . '/' . $filename;

				if ( !file_exists( $source ) ) {
					continue;
				}

				$dest = $syslangpath . '/' . $l . '/' . $filename;

				copy( $source, $dest );
			}
		}
	}

	function getInfo()
	{
		if ( !is_object( $this->processor ) ) {
			return false;
		}

		$this->info	=& $this->processor->info;
		$original	= $this->processor->info();

		foreach ( $original as $name => $var ) {
			if ( !isset( $this->info[$name] ) ) {
				$this->info[$name] = $var;
			}
		}
	}

	function getParamLang( $name )
	{
		$lang = JFactory::getLanguage();

		$nname = 'CFG_' . strtoupper( $this->processor_name ) . '_' . strtoupper($name);
		$gname = 'CFG_PROCESSOR_' . strtoupper($name);

		if ( $lang->hasKey( $nname ) ) {
			return JText::_( $nname );
		} elseif ( $lang->hasKey( $gname ) ) {
			return JText::_( $gname );
		} else {
			return JText::_( $nname );
		}
	}

	function getSettings()
	{
		if ( !is_object( $this->processor ) ) {
			return false;
		}

		$this->settings	=& $this->processor->settings;
		$original		= $this->processor->settings();

		if ( !is_array( $this->settings ) ) {
			$this->settings = $original;
		}

		if ( !isset( $this->settings['recurring'] ) && is_int( $this->is_recurring() ) ) {
			$original['recurring'] = 1;
		}

		foreach ( $original as $name => $var ) {
			if ( !isset( $this->settings[$name] ) ) {
				$this->settings[$name] = $var;
			}
		}
	}

	function exchangeSettings( $exchange )
	{
		 if ( !empty( $exchange ) ) {
			 foreach ( $exchange as $key => $value ) {
				if ( is_string( $value ) ) {
					if ( strcmp( $value, '[[SET_TO_NULL]]' ) === 0 ) {
						// Exception for NULL case
						$this->settings[$key] = null;
					} else {
						if ( !is_null( $value ) || ( $value === "" ) ) {
							$this->settings[$key] = $value;
						}
					}
				} else {
					if ( !empty( $value ) ) {
						$this->settings[$key] = $value;
					}
				}
			 }
		 }
	}

	function setSettings()
	{
		$this->processor->storeload();
	}

	function exchangeSettingsByPlan( $plan, $plan_params=null )
	{
		if ( empty( $this->settings ) ) {
			$this->getSettings();
		}

		if ( empty( $plan_params ) ) {
			$plan_params = $plan->getProcessorParameters( $this );
		}

		if ( isset( $plan_params['aec_overwrite_settings'] ) ) {
			unset( $plan_params['aec_overwrite_settings'] );
		}

		$this->exchangeSettings( $plan_params );
	}

	function getLogoImg()
	{
		$fname = $this->processor->getLogoFilename();

		if ( !empty( $fname ) ) {
			return '<img src="' . $this->getLogoPath() . '" alt="' . $this->processor_name . '" title="' . $this->processor_name .'" class="plogo" />';
		} else {
			return $this->info['longname'];
		}
	}

	function getLogoPath()
	{
		return JURI::root(true) . '/media/com_acctexp/images/site/' . $this->processor->getLogoFilename();
	}

	function is_recurring( $choice=null, $test=false )
	{
		// Warning: Here be Voodoo

		if ( isset( $this->is_recurring ) && !$test ) {
			return $this->is_recurring;
		}

		// Check for bogus choice
		if ( empty( $choice ) && ( $choice !== 0 ) && ( $choice !== '0' ) ) {
			$choice = null;
		}

		$return = false;

		// Load Info if not loaded yet
		if ( !isset( $this->info ) ) {
			$this->getInfo();
		}

		if ( isset( $this->settings['recurring'] ) ) {
			$rec_set = $this->info['recurring'];
		} else {
			$rec_set = null;
		}


		if ( !isset( $this->info['recurring'] ) ) {
			// Keep false
		} elseif ( ( $this->info['recurring'] > 1 ) && ( $rec_set !== 1 ) ) {
			if ( empty( $this->settings ) ) {
				$this->getSettings();
			}

			// If recurring = 2, the processor can
			// set this property on a per-plan basis
			if ( isset( $this->settings['recurring'] ) ) {
				$return = (int) $this->settings['recurring'];
			} else {
				$return = (int) $this->info['recurring'];
			}

			if ( ( !is_null( $choice ) ) && ( $return > 1 ) ) {
				$return = (int) $choice;
			}
		} elseif ( !empty( $this->info['recurring'] ) ) {
			$return = true;
		}

		$this->is_recurring = $return;

		return $return;
	}

	function requireSSLcheckout()
	{
		if ( method_exists( $this->processor, 'requireSSLcheckout' ) ) {
			return $this->processor->requireSSLcheckout();
		} else {
			if ( isset( $this->info['secure'] ) ) {
				return $this->info['secure'];
			} else {
				return false;
			}
		}
	}

	function storeload()
	{
		if ( empty( $this->id ) ) {
			$this->install();
		} else {
			$this->processor->storeload();
		}
	}

	function getBackendSettings()
	{
		if ( empty( $this->settings ) ) {
			$this->getSettings();
		}

		if ( $this->info['recurring'] == 2 ) {
			$settings = array_merge( array( 'recurring' => array( 'list_recurring' ) ), $this->processor->backend_settings() );
		} else {
			$settings = $this->processor->backend_settings();
		}

		$settings['generic_buttons']	= array( 'toggle' );

		if ( isset( $settings['aec_experimental'] ) ) {
			$settings['aec_experimental'] = "p";
			$this->settings['aec_experimental'] = '<div class="aec_processor_experimentalnote"><h1>' . JText::_('PP_GENERAL_PLEASE_NOTE') . '</h1><p>' . JText::_('PP_GENERAL_EXPERIMENTAL') . '</p></div>';
		}

		if ( isset( $settings['aec_insecure'] ) ) {
			$settings['aec_experimental'] = "p";
			$this->settings['aec_insecure'] = '<div class="aec_processor_experimentalnote"><h1>' . JText::_('PP_GENERAL_PLEASE_NOTE') . '</h1><p>' . JText::_('PP_GENERAL_INSECURE') . '</p></div>';
		}

		if ( !isset( $this->info ) ) {
			$this->getInfo();
		}

		if ( !empty( $this->info['cc_list'] ) ) {
			$settings['cc_icons']			= array( 'list' );

			$cc_array = explode( ',', $this->info['cc_list'] );

			if ( isset( $this->settings['cc_icons'] ) ) {
				$set = $this->settings['cc_icons'];
			} else {
				$set = $cc_array;
			}

			$cc = array();
			$ccs = array();
			foreach ( $cc_array as $ccname ) {
				$cc[] = JHTML::_('select.option', $ccname, $ccname );

				if ( in_array( $ccname, $set ) ) {
					$ccs[] = JHTML::_('select.option', $ccname, $ccname );
				}
			}

			$settings['lists']['cc_icons'] = JHTML::_( 'select.genericlist', $cc, $this->processor_name.'_cc_icons[]', 'size="4" multiple="multiple"', 'value', 'text', $ccs );
		}

		return $settings;
	}

	function checkoutAction( $int_var=null, $metaUser=null, $plan=null, $InvoiceFactory=null, $cart=null )
	{
		if ( empty( $this->settings ) ) {
			$this->getSettings();
		}

		if ( isset( $int_var['planparams']['aec_overwrite_settings'] ) ) {
			if ( !empty( $int_var['planparams']['aec_overwrite_settings'] ) ) {
				$this->exchangeSettingsByPlan( null, $int_var['planparams'] );
			}
		}

		if ( empty( $plan ) && !empty( $cart ) ) {
			$plan = aecCartHelper::getFirstCartItemObject( $cart );
		}

		$request = new stdClass();
		$request->parent			=& $this;
		$request->int_var			=& $int_var;
		$request->metaUser			=& $metaUser;
		$request->plan				=& $plan;
		$request->invoice			=& $InvoiceFactory->invoice;
		$request->items				=& $InvoiceFactory->items;
		$request->cart				=& $cart;

		return $this->processor->checkoutAction( $request, $InvoiceFactory );
	}

	function checkoutProcess( $int_var=null, $metaUser=null, $plan=null, $InvoiceFactory=null, $cart=null )
	{
		if ( empty( $this->settings ) ) {
			$this->getSettings();
		}

		if ( isset( $int_var['planparams']['aec_overwrite_settings'] ) ) {
			if ( !empty( $int_var['planparams']['aec_overwrite_settings'] ) ) {
				$this->exchangeSettingsByPlan( null, $int_var['planparams'] );
			}
		}

		if ( empty( $plan ) && !empty( $cart ) ) {
			$plan = aecCartHelper::getFirstCartItemObject( $cart );
		}

		$request = new stdClass();
		$request->parent			=& $this;
		$request->int_var			=& $int_var;
		$request->metaUser			=& $metaUser;
		$request->plan				=& $plan;
		$request->invoice			=& $InvoiceFactory->invoice;
		$request->items				=& $InvoiceFactory->items;
		$request->cart				=& $cart;

		return $this->processor->checkoutProcess( $request, $InvoiceFactory );
	}

	function customAction( $action, $invoice, $metaUser, $int_var=null )
	{
		if ( empty( $this->settings ) ) {
			$this->getSettings();
		}

		if ( ( $action == 'cancel' ) && !empty( $this->settings['minOccurrences'] ) ) {
			if ( ( $this->settings['minOccurrences'] > 1 ) && ( $invoice->counter < $this->settings['minOccurrences'] ) ) {
				$return['valid'] = 0;
				$return['error'] = 'Could not cancel your membership - a minimum of ' . $this->settings['minOccurrences'] . ' periods have to be paid.'
									. ' You have now paid for ' . $invoice->counter . ' cycles.';

				return $return;
			}
		}

		$method = 'customaction_' . $action;

		if ( method_exists( $this->processor, $method ) ) {
			$request = new stdClass();
			$request->parent			=& $this;
			$request->metaUser			=& $metaUser;
			$request->invoice			=& $invoice;
			$request->plan				=& $invoice->getObjUsage();
			$request->int_var			=& $int_var;

			return $this->processor->$method( $request );
		} else {
			return false;
		}
	}

	function customProfileTab( $action, $metaUser )
	{
		$s = $this->processor_name . '_';
		if ( strpos( $action, $s ) !== false ) {
			$action = str_replace( $s, '', $action );
		}

		if ( empty( $this->settings ) ) {
			$this->getSettings();
		}

		$method = 'customtab_' . $action;

		if ( method_exists( $this->processor, $method ) ) {
			$request = new stdClass();
			$request->parent			=& $this;
			$request->metaUser			=& $metaUser;

			$invoice = new Invoice();
			$invoice->loadbySubscriptionId( $metaUser->objSubscription->id );

			$request->invoice			=& $invoice;


			return $this->processor->$method( $request );
		} else {
			return false;
		}
	}

	function getParamsHTML( $params, $values )
	{
		$return = null;
		if ( !empty( $values['params'] ) ) {
			if ( is_array( $values['params'] ) ) {
				if ( isset( $values['params']['lists'] ) ) {
					$lists = $values['params']['lists'];
					unset( $values['params']['lists'] );
				} else {
					$lists = null;
				}

				$settings = new aecSettings ( 'aec', 'ccform' );
				$settings->fullSettingsArray( $params['params'], array(), $lists, array(), false ) ;

				$aecHTML = new aecHTML( $settings->settings, $settings->lists );

				$return .= $aecHTML->returnFull( false, true );

				$return .= '</div>';
			}
		}

		return $return;
	}

	function getParams( $params )
	{
		if ( empty( $this->settings ) ) {
			$this->getSettings();
		}

		if ( method_exists( $this->processor, 'Params' ) ) {
			return $this->processor->Params( $params );
		} else {
			return false;
		}
	}

	function getCustomPlanParams()
	{
		if ( empty( $this->settings ) ) {
			$this->getSettings();
		}

		if ( !isset( $this->info['recurring'] ) ) {
			$this->info['recurring'] = 0;
		}

		if ( $this->info['recurring'] == 2 ) {
			$settings = array_merge( array( 'recurring' => array( 'list_recurring' ) ), $this->processor->backend_settings() );
		} else {
			$settings = $this->processor->backend_settings();
		}

		$params = array();

		if ( $this->info['recurring'] == 2 ) {
			$params = array_merge( array( 'recurring' => array( 'list_recurring' ) ), $params );
		}

		if ( method_exists( $this->processor, 'CustomPlanParams' ) ) {
			$params = array_merge( $params, $this->processor->CustomPlanParams() );
		}

		if ( !empty( $params ) ) {
			return $params;
		} else {
			return false;
		}
	}

	function invoiceCreationAction( $objinvoice )
	{
		if ( empty( $this->settings ) ) {
			$this->getSettings();
		}

		if ( method_exists( $this->processor, 'invoiceCreationAction' ) ) {
			$this->processor->invoiceCreationAction( $objinvoice );
		} else {
			return false;
		}
	}

	function parseNotification( $post )
	{
		if ( empty( $this->settings ) ) {
			$this->getSettings();
		}

		$return = $this->processor->parseNotification( $post );

		// Check whether this is an ad-hoc notification
		if ( !empty( $return['_aec_createuser'] ) && empty( $return['invoice'] ) ) {
			// Identify usage
			$usage = 1;

			// Create new user account and fetch id
			$userid = AECToolbox::saveUserRegistration( 'com_acctexp', $return['_aec_createuser'], true, true, false );

			// Create Invoice
			$invoice = new Invoice();
			$invoice->create( $userid, $usage, $this->processor_name );
			$invoice->computeAmount();

			// Set new return variable - we now know what invoice this is
			$return['invoice'] = $invoice->invoice_number;
		}

		// Always amend secondary ident codes
		if ( !empty( $return['secondary_ident'] )&& !empty( $return['invoice'] ) ) {
			$invoice = new Invoice();
			$invoice->loadInvoiceNumber( $return['invoice'] );
			$invoice->secondary_ident = $return['secondary_ident'];
			$invoice->storeload();
		}

		if ( !empty( $return['_aec_createuser'] ) ) {
			unset( $return['_aec_createuser'] );
		}

		return $return;
	}

	function notificationError( $response, $error )
	{
		if ( method_exists( $this->processor, 'notificationError' ) ) {
			$this->processor->notificationError( $response, $error );
		}
	}

	function notificationSuccess( $response )
	{
		if ( method_exists( $this->processor, 'notificationSuccess' ) ) {
			$this->processor->notificationSuccess( $response );
		}
	}

	function validateNotification( $response, $post, $invoice )
	{
		if ( method_exists( $this->processor, 'validateNotification' ) ) {
			$response = $this->processor->validateNotification( $response, $post, $invoice );
		}

		return $response;
	}

	function instantvalidateNotification( $response, $post, $invoice )
	{
		if ( method_exists( $this->processor, 'instantvalidateNotification' ) ) {
			$response = $this->processor->instantvalidateNotification( $response, $post, $invoice );
		}

		return $response;
	}

	function prepareValidation( $subscription_list )
	{
		if ( empty( $this->settings ) ) {
			$this->getSettings();
		}

		if ( method_exists( $this->processor, 'prepareValidation' ) ) {
			$response = $this->processor->prepareValidation( $subscription_list );
		} else {
			$response = null;
		}

		return $response;
	}

	function validateSubscription( $subscription_id )
	{
		if ( empty( $this->settings ) ) {
			$this->getSettings();
		}

		$response = false;
		if ( method_exists( $this->processor, 'validateSubscription' ) ) {
			$subscription = new Subscription();
			$subscription->load( $subscription_id );

			$allowed = array( "Trial", "Active" );

			if ( !in_array( $subscription->status, $allowed ) ) {
				return null;
			}

			$invoice = new Invoice();
			$invoice->loadbySubscriptionId( $subscription_id );

			if ( empty( $invoice->id ) ) {
				return null;
			}

			$option = 'com_acctexp';

			$iFactory = new InvoiceFactory( null, null, null, $this->processor_name );

			$iFactory->userid = $subscription->userid;
			$iFactory->usage = $invoice->usage;
			$iFactory->processor = $this->processor_name;

			$iFactory->loadMetaUser();

			$iFactory->touchInvoice( $option, $invoice->invoice_number );

			$iFactory->puffer( $option );

			$iFactory->loadItems();

			$iFactory->loadItemTotal();

			$result = $this->processor->validateSubscription( $iFactory, $subscription );

			$resp = array();
			if ( !empty( $result['raw'] ) ) {
				if ( is_array( $result['raw'] ) ) {
					$resp = $result['raw'];
				} else {
					$resp['response'] = $result['raw'];
				}
			}

			$iFactory->invoice->processorResponse( $iFactory, $result, $resp, true );

			if ( !empty( $result['valid'] ) ) {
				$response = true;
			} elseif ( empty( $result['error'] ) ) {
				$response = null;
			}
		} else {
			$response = null;
		}

		return $response;
	}

	function registerProfileTabs()
	{
		if ( method_exists( $this->processor, 'registerProfileTabs' ) ) {
			$response = $this->processor->registerProfileTabs();
		} else {
			$response = null;
		}

		return $response;
	}

	function modifyCheckout( &$int_var, &$InvoiceFactory )
	{
		if ( method_exists( $this->processor, 'modifyCheckout' ) ) {
			$this->processor->modifyCheckout( $int_var, $InvoiceFactory );
		}
	}

	function notify_trail( $InvoiceFactory, $response )
	{
		if ( method_exists( $this->processor, 'notify_trail' ) ) {
			return $this->processor->notify_trail( $InvoiceFactory, $response );
		} else {
			return array();
		}
	}

	function getProfileTabs()
	{
		$addtabs = $this->registerProfileTabs();

		if ( empty( $addtabs ) ) {
			return array();
		}

		foreach ( $addtabs as $atk => $atv ) {
			$action = $this->processor_name . '_' . $atk;
			if ( isset( $tabs[$action] ) ) {
				continue;
			}

			$tabs[$action] = $atv;
		}

		return $tabs;
	}

	function getActions( $invoice, $subscription )
	{
		$actions = array();

		$actionarray = $this->processor->getActions( $invoice, $subscription );

		if ( !empty( $actionarray ) ) {
			foreach ( $actionarray as $action => $aoptions ) {
				$action = array( 'action' => $action, 'insert' => '' );

				if ( !empty( $aoptions ) ) {
					foreach ( $aoptions as $opt ) {
						switch ( $opt ) {
							case 'confirm':
								$action['insert'] .= ' onclick="return show_confirm(\'' . JText::_('AEC_YOUSURE') . '\')" ';
								break;
							default:
								break;
						}
					}
				}
			}

			$actions[] = $action;
		}

		return $actions;
	}
}

class processor extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $name				= null;
	/** @var int */
	var $active				= null;
	/** @var text */
	var $info				= null;
	/** @var text */
	var $settings			= null;
	/** @var text */
	var $params				= null;

	function processor()
	{
		parent::__construct( '#__acctexp_config_processors', 'id' );
	}

	function declareParamFields()
	{
		return array( 'info', 'settings', 'params' );
	}

	function getLogoFilename()
	{
		return $this->name.'.png';
	}

	function loadName( $name )
	{
		$query = 'SELECT `id`'
				. ' FROM #__acctexp_config_processors'
				. ' WHERE `name` = \'' . xJ::escape( $this->_db, $name ) . '\''
				;
		$this->_db->setQuery( $query );
		
		$id = $this->_db->loadResult();

		if ( $id ) {
			return $this->load( $this->_db->loadResult() );
		} else {
			return false;
		}
	}

	function createNew( $name, $info, $settings )
	{
		$this->id		= 0;
		$this->name		= $name;
		$this->active	= 1;
		$this->info		= $info;
		$this->settings	= $settings;

		$this->storeload();
	}

	function checkoutText()
	{
		return JText::_('CHECKOUT_BTN_INFO');
	}

	function checkoutAction( $request, $InvoiceFactory=null )
	{
		return '<p>' . AECToolbox::rewriteEngineRQ( $this->settings['info'], $request ) . '</p>';
	}

	function requireSSLcheckout()
	{
		if ( isset( $this->info['secure'] ) ) {
			return $this->info['secure'];
		} else {
			return false;
		}
	}

	function fileError( $text, $level=128, $tags="", $params=array() )
	{
		$eventlog = new eventLog();

		$t = array();
		$t[] = 'processor';
		$t[] = $this->name;

		if ( is_array( $tags ) ) {
			$x = $tags;
		} else {
			$x = explode( ',', $tags );
		}

		if ( !is_string( $text ) ) {
			$eventlog->issue( 'processor error', implode( ',', array_merge( $t, $x ) ), json_encode( $text ), $level );
		} else {
			$eventlog->issue( 'processor error', implode( ',', array_merge( $t, $x ) ), $text, $level, $params );
		}
	}

	function exchangeSettings( $settings, $exchange )
	{
		 if ( !empty( $exchange ) ) {
			 foreach ( $exchange as $key => $value ) {
				if ( !is_null( $value ) && ( $value != '' ) ) {
					if( is_string( $value ) ) {
						if ( strcmp( $value, '[[SET_TO_NULL]]' ) === 0 ) {
							// Exception for NULL case
							$settings[$key] = null;
						} else {
							$settings[$key] = $value;
						}
					} else {
						$settings[$key] = $value;
					}
				}
			 }
		 }

		return $settings;
	}

	function getActions( $invoice, $subscription )
	{
		if ( !empty( $this->info['actions'] ) ) {
			return $this->info['actions'];
		} else {
			return array();
		}
	}

	function customParams( $custom, $var, $request )
	{
		if ( !empty( $custom ) ) {
			$rw_params = AECToolbox::rewriteEngineRQ( $custom, $request );

			$params = explode( "\n", $rw_params );

			foreach ( $params as $custom ) {
				$paramsarray = explode( '=', $custom, 2 );

				if ( !empty( $paramsarray[0] ) && isset( $paramsarray[1] ) ) {
					$var[trim($paramsarray[0])] = trim($paramsarray[1]);
				}
			}
		}

		return $var;
	}

	function parseNotification( $post )
	{
		$response = array();

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$response['valid'] = 0;

		return $response;
	}

	function transmitRequest( $url, $path, $content=null, $port=443, $curlextra=null, $header=null )
	{
		global $aecConfig;

		$response = null;

		if ( $aecConfig->cfg['curl_default'] ) {
			$response = $this->doTheCurl( $url, $content, $curlextra, $header );
			if ( $response === false ) {
				// If curl doesn't work try using fsockopen
				$response = $this->doTheHttp( $url, $path, $content, $port, $header, $curlextra );
			}
		} else {
			$response = $this->doTheHttp( $url, $path, $content, $port, $header );
			if ( $response === false ) {
				// If fsockopen doesn't work try using curl
				$response = $this->doTheCurl( $url, $content, $curlextra, $header, $curlextra );
			}
		}

		return $response;
	}

	function doTheHttp( $url, $path, $content, $port=443, $extra_header=null, $curlextra=null )
	{
		global $aecConfig;

		if ( strpos( $url, '://' ) === false ) {
			if ( $port == 443 ) {
				$purl = 'https://' . $url;
			} else {
				$purl = 'http://' . $url;
			}
		} else {
			$purl = $url;
		}

		$url_info = parse_url( $purl );

		if ( empty( $url_info ) ) {
				return false;
		}

		switch ( $url_info['scheme'] ) {
				case 'https':
						$scheme = 'ssl://';
						$port = 443;
						break;
				case 'http':
				default:
						$scheme = '';
						$port = 80;
						break;
		}

		$url = $scheme . $url_info['host'];

		if ( !empty( $aecConfig->cfg['use_proxy'] ) && !empty( $aecConfig->cfg['proxy'] ) ) {
			if ( !empty( $aecConfig->cfg['proxy_port'] ) ) {
				$proxyport = $aecConfig->cfg['proxy_port'];
			} else {
				$proxyport = $port;
			}

			$connection = fsockopen( $aecConfig->cfg['proxy'], $proxyport, $errno, $errstr, 30 );
		} else {
			$connection = fsockopen( $url, $port, $errno, $errstr, 30 );
		}

		// Emulate some cURL functionality
		if ( !empty( $curlextra ) && function_exists( "stream_context_set_params" ) ) {
			if ( isset( $curlextra['verify_peer'] ) && isset( $curlextra['allow_self_signed'] ) ) {
				$set_params = array( 'ssl' => array( 'verify_peer' => $curlextra['verify_peer'],'allow_self_signed' => $curlextra['allow_self_signed'] ) );

				stream_context_set_params( $connection, $set_params );
			}
		}

		if ( $connection === false ) {
			

			if ( $errno == 0 ) {
				$errstr .= " This is usually an SSL error.  Check if your server supports fsocket open via SSL.";
			}

			$short	= 'fsockopen failure';
			$event	= 'Trying to establish connection with ' . $url . ' failed with Error #' . $errno . ' ( "' . $errstr . '" ) - will try cURL instead. If Error persists and cURL works, please permanently switch to using that!';
			$tags	= 'processor,payment,phperror';
			$params = array();

			$eventlog = new eventLog();
			$eventlog->issue( $short, $tags, $event, 128, $params );

			return false;
		} else {
		    if ( !empty( $aecConfig->cfg['use_proxy'] ) && !empty( $aecConfig->cfg['proxy'] ) ) {
				$hosturl = $aecConfig->cfg['proxy'];
		    } else {
		    	$hosturl = $url_info['host'];
		    }

			$header_array["Host"] = $hosturl;

			if ( !empty( $aecConfig->cfg['use_proxy'] ) && !empty( $aecConfig->cfg['proxy'] ) ) {
				if ( !empty( $aecConfig->cfg['proxy_username'] ) && !empty( $aecConfig->cfg['proxy_password'] ) ) {
					$header_array["Proxy-Authorization"] = "Basic ". base64_encode( $aecConfig->cfg['proxy_username'] . ":" . $aecConfig->cfg['proxy_password'] );
				}
			}

			$header_array["User-Agent"] = "PHP Script";
			$header_array["Content-Type"] = "application/x-www-form-urlencoded";

			if ( !empty( $content ) ) {
				$header_array["Content-Length"] = strlen( $content );
			}

			if ( !empty( $extra_header ) ) {
				foreach ( $extra_header as $h => $v ) {
					$header_array[$h] = $v;
				}
			}

			$header_array["Connection"] = "Close";

			if ( !empty( $content ) ) {
				$header = "POST " . $path . " HTTP/1.0\r\n";
			} else {
				$header = "GET " . $path . " HTTP/1.0\r\n";
			}

			foreach ( $header_array as $h => $v ) {
				$header .=	$h . ": " . $v . "\r\n";
			}

			$header .= "\r\n";

			if ( !empty( $content ) ) {
				$header .= $content;
			}

			fwrite( $connection, $header );

			$res = "";
			if ( function_exists( 'stream_set_timeout' ) ) {
				stream_set_timeout( $connection, 300 );

				$info = stream_get_meta_data( $connection );

				while ( !feof( $connection ) && ( !$info["timed_out"] ) ) {
					$res = fgets( $connection, 8192 );
				}

		        if ( $info["timed_out"] ) {
					

					$short	= 'fsockopen failure';
					$event	= 'Trying to establish connection with ' . $url . ' timed out - will try cURL instead. If Error persists and cURL works, please permanently switch to using that!';
					$tags	= 'processor,payment,phperror';
					$params = array();

					$eventlog = new eventLog();
					$eventlog->issue( $short, $tags, $event, 128, $params );
		        }
			} else {
				while ( !feof( $connection ) ) {
					$res = fgets( $connection, 1024 );
				}
			}

			fclose( $connection );

			return $res;
		}
	}

	function doTheCurl( $url, $content, $curlextra=null, $header=null )
	{
		global $aecConfig;

		if ( !function_exists( 'curl_init' ) ) {
			$response = false;

			
			$short	= 'cURL failure';
			$event	= 'Trying to establish connection with ' . $url . ' failed - curl_init is not available - will try fsockopen instead. If Error persists and fsockopen works, please permanently switch to using that!';
			$tags	= 'processor,payment,phperror';
			$params = array();

			$eventlog = new eventLog();
			$eventlog->issue( $short, $tags, $event, 128, $params );
			return false;
		}

		if ( empty( $curlextra ) ) {
			$curlextra = array();
		}

		// Preparing cURL variables as array, to possibly overwrite them with custom settings by the processor
		$curl_calls = array();
		$curl_calls[CURLOPT_URL]			= $url;
		$curl_calls[CURLOPT_RETURNTRANSFER]	= true;
		$curl_calls[CURLOPT_HTTPHEADER]		= array( 'Content-Type: text/xml' );
		$curl_calls[CURLOPT_HEADER]			= false;

		if ( !empty( $content ) ) {
			$curl_calls[CURLOPT_POST]			= true;
			$curl_calls[CURLOPT_POSTFIELDS]		= $content;
		}

		if ( !empty( $aecConfig->cfg['ssl_verifypeer'] ) ) {
			$curl_calls[CURLOPT_SSL_VERIFYPEER]	= $aecConfig->cfg['ssl_verifypeer'];
		} else {
			$curl_calls[CURLOPT_SSL_VERIFYPEER]	= false;
		}

		if ( !empty( $aecConfig->cfg['ssl_verifyhost'] ) ) {
			$curl_calls[CURLOPT_SSL_VERIFYHOST]	= $aecConfig->cfg['ssl_verifyhost'];
		} else {
			$curl_calls[CURLOPT_SSL_VERIFYHOST]	= false;
		}

		if ( !empty( $aecConfig->cfg['use_proxy'] ) && !empty( $aecConfig->cfg['proxy'] ) ) {
			$curl_calls[CURLOPT_HTTPPROXYTUNNEL]	= true;
			$curl_calls[CURLOPT_PROXY]				= $aecConfig->cfg['proxy'];

			if ( !empty( $aecConfig->cfg['proxy_port'] ) ) {
				$curl_calls[CURLOPT_PROXYPORT]		= $aecConfig->cfg['proxy_port'];
			}

			if ( !empty( $aecConfig->cfg['proxy_username'] ) && !empty( $aecConfig->cfg['proxy_password'] ) ) {
				$curl_calls[CURLOPT_PROXYUSERPWD]	= $aecConfig->cfg['proxy_username'].":".$aecConfig->cfg['proxy_password'];
			}
		}

		// Set or replace cURL params
		if ( !empty( $curlextra ) ) {
			foreach( $curlextra as $name => $value ) {
				if ( $value == '[[unset]]' ) {
					if ( isset( $curl_calls[$name] ) ) {
						unset( $curl_calls[$name] );
					}
				} else {
					$curl_calls[$name] = $value;
				}
			}
		}

		// Set cURL params
		$ch = curl_init();
		foreach ( $curl_calls as $name => $value ) {
			curl_setopt( $ch, $name, $value );
		}

		$response = curl_exec( $ch );

		if ( $response === false ) {
			

			$short	= 'cURL failure';
			$event	= 'Trying to establish connection with ' . $url . ' failed with Error #' . curl_errno( $ch ) . ' ( "' . curl_error( $ch ) . '" ) - will try fsockopen instead. If Error persists and fsockopen works, please permanently switch to using that!';
			$tags	= 'processor,payment,phperror';
			$params = array();

			$eventlog = new eventLog();
			$eventlog->issue( $short, $tags, $event, 128, $params );
		}

		curl_close( $ch );

		return $response;
	}

}

class XMLprocessor extends processor
{
	function checkoutAction( $request, $InvoiceFactory=null )
	{
		global $aecConfig;

		if ( method_exists( $this, 'checkoutform' ) ) {
			$var = $this->checkoutform( $request );
		} else {
			$var = array();
		}

		if ( isset( $var['aec_alternate_checkout'] ) ) {
			$url = $var['aec_alternate_checkout'];

			unset( $var['aec_alternate_checkout'] );
		} else {
			$url = AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=checkout', $this->requireSSLcheckout() );
		}

		if ( isset( $var['aec_remove_std_vars'] ) ) {
			$stdvars = false;

			unset( $var['aec_remove_std_vars'] );
		} else {
			$stdvars = true;
		}

		$return = '<form action="' . $url . '" method="post">' . "\n";
		$return .= $this->getParamsHTML( $var ) . '<br /><br />';

		if ( $stdvars ) {
			$return .= $this->getStdFormVars( $request );
		}

		$return .= '<button type="submit" class="button aec-btn btn btn-primary" id="aec-checkout-btn">' . aecHTML::Icon( 'shopping-cart', true ) . JText::_('BUTTON_CHECKOUT') . '</button>' . "\n";
		$return .= '</form>' . "\n";

		return $return;
	}

	function getStdFormVars( $request )
	{
		$return = '<input type="hidden" name="invoice" value="' . $request->int_var['invoice'] . '" />' . "\n";
		$return .= '<input type="hidden" name="processor" value="' . $this->name . '" />' . "\n";
		$return .= '<input type="hidden" name="userid" value="' . $request->metaUser->userid . '" />' . "\n";
		$return .= '<input type="hidden" name="task" value="checkout" />' . "\n";

		return $return;
	}

	function getParamsHTML( $params )
	{
		$return = null;
		if ( !empty( $params['params'] ) ) {
			if ( is_array( $params['params'] ) ) {
				if ( isset( $params['params']['lists'] ) ) {
					$lists = $params['params']['lists'];
					unset( $params['params']['lists'] );
				} else {
					$lists = null;
				}

				$hastabs = false;
				foreach ( $params['params'] as $entry ) {
					if ( $entry[0] == 'tabberstart' ) {
						$hastabs = true;
					}
				}

				if ( !$hastabs ) {
					$return .= '<div class="aec-checkout-params">';
				}

				$settings = new aecSettings ( 'aec', 'ccform' );
				$settings->fullSettingsArray( $params['params'], array(), $lists, array(), false ) ;

				$aecHTML = new aecHTML( $settings->settings, $settings->lists );

				$return .= $aecHTML->returnFull( false, true );

				$return .= '</div>';
			}
		}

		return $return;
	}

	function getMULTIPAYform( $var, $array )
	{
		$nlist	= array();
		$prefix	= array();
		$main	= array();

		// We need to separate two blocks - prefix tabberstart generation and put the content inside
		$prefix[] = array( 'tabberstart', '', '', '' );
		$prefix[] = array( 'tabregisterstart', '', '', '' );

		foreach ( $array as $name => $content ) {
			$nu = strtoupper( $name );

			$fname = 'get'.$nu.'form';

			// Only allow to pass if std function exists
			if ( function_exists( 'XMLprocessor::'.$fname ) ) {
				$nl = strtolower( $name );

				// Register tab in prefix
				$prefix[] = array( 'tabregister', $nl.'details', JText::_( 'AEC_'.$nu.'FORM_TABNAME' ), true );

				// Actual tab code
				$main[] = array( 'tabstart', $nl.'details', true, '' );
				$main = $this->$fname( $main, $content['values'], $content['vcontent'] );
				$main[] = array( 'tabend', '', '', '' );
			}
		}

		$prefix[] = array( 'tabregisterend', '', '', '' );

		$var['params'] = array_merge( $var['params'], $prefix );
		$var['params'] = array_merge( $var['params'], $main );

		$var['params'][] = array( 'tabberend', '', '', '' );

		return $var;
	}

	function getCCform( $var=array(), $values=null, $content=null )
	{
		if ( empty( $values ) ) {
			$values = array( 'card_number', 'card_exp_month', 'card_exp_year' );
		}

		foreach ( $values as $value ) {
			if ( strpos( $value, '*' ) ) {
				$pf = '*';

				$value = substr( $value, 0, -1 );
			} else {
				$pf = '';
			}

			$translatelist = array( 'card_type' => 'cardType',
									'card_number' => 'cardNumber',
									'card_exp_month' => 'expirationMonth',
									'card_exp_year' => 'expirationYear',
									'card_cvv2' => 'cardVV2'
									);

			if ( isset( $content[$value] ) ) {
				$vcontent = $content[$value];
			} elseif ( isset( $content[$translatelist[$value]] ) ) {
				$vcontent = $content[$translatelist[$value]];
			} else {
				$vcontent = '';
			}

			switch ( strtolower( $value ) ) {
				case 'card_type':
					$cctlist = array(	'visa' => 'Visa',
										'mastercard' => 'MasterCard',
										'discover' => 'Discover',
										'amex' => 'American Express'
										);

					$options = array();
					foreach ( $cctlist as $ccname => $cclongname ) {
						$options[] = JHTML::_('select.option', $ccname, $cclongname );
					}

					$var['params']['lists']['cardType'] = JHTML::_( 'select.genericlist', $options, 'cardType', 'size="1" style="width:120px;" class="aec_formfield" title="'.JText::_('AEC_CCFORM_CARDNUMBER_DESC').'" autocomplete="off" ', 'value', 'text', $vcontent );
					$var['params']['cardType'] = array( 'list', JText::_('AEC_CCFORM_CARDTYPE_NAME').$pf );
					break;
				case 'card_number':
					// Request the Card number
					$var['params']['cardNumber'] = array( 'inputC', JText::_('AEC_CCFORM_CARDNUMBER_NAME').$pf, JText::_('AEC_CCFORM_CARDNUMBER_DESC') . '" autocomplete="off', $vcontent );
					break;
				case 'card_exp_month':
					// Create a selection box with 12 months
					$months = array();
					for( $i = 1; $i < 13; $i++ ){
						$month = str_pad( $i, 2, "0", STR_PAD_LEFT );
						$months[] = JHTML::_('select.option', $month, $month );
					}

					$var['params']['lists']['expirationMonth'] = JHTML::_( 'select.genericlist', $months, 'expirationMonth', 'size="1" class="aec_formfield" style="width:50px;" title="'.JText::_('AEC_CCFORM_EXPIRATIONMONTH_DESC').'" autocomplete="off"', 'value', 'text', $vcontent );
					$var['params']['expirationMonth'] = array( 'list', JText::_('AEC_CCFORM_EXPIRATIONMONTH_NAME').$pf, JText::_('AEC_CCFORM_EXPIRATIONMONTH_DESC') );
					break;
				case 'card_exp_year':
					// Create a selection box with the next 10 years
					$year = date('Y');
					$years = array();

					for ( $i = $year; $i < $year + 15; $i++ ) {
						$years[] = JHTML::_('select.option', $i, $i );
					}

					$var['params']['lists']['expirationYear'] = JHTML::_( 'select.genericlist', $years, 'expirationYear', 'size="1" class="aec_formfield" style="width:70px;" title="'.JText::_('AEC_CCFORM_EXPIRATIONYEAR_DESC').'" autocomplete="off"', 'value', 'text', $vcontent );
					$var['params']['expirationYear'] = array( 'list', JText::_('AEC_CCFORM_EXPIRATIONYEAR_NAME').$pf, JText::_('AEC_CCFORM_EXPIRATIONYEAR_DESC') );
					break;
				case 'card_cvv2':
					$var['params']['cardVV2'] = array( 'inputB', JText::_('AEC_CCFORM_CARDVV2_NAME').$pf, JText::_('AEC_CCFORM_CARDVV2_DESC') . '" autocomplete="off', null );
					break;
			}
		}

		return $var;
	}

	function getECHECKform( $var=array(), $values=null, $content=null )
	{
		if ( empty( $values ) ) {
			$values = array( 'routing_no', 'account_no', 'account_name', 'bank_name' );
		}

		foreach ( $values as $value ) {
			if ( strpos( $value, '*' ) ) {
				$pf = '*';

				$value = substr( $value, 0, -1 );
			} else {
				$pf = '';
			}

			if ( isset( $content[$value] ) ) {
				$vcontent = $content[$value];
			} else {
				$vcontent = '';
			}

			switch ( strtolower( $value ) ) {
				case 'routing_no':
					$var['params']['routing_no'] = array( 'inputC', JText::_('AEC_ECHECKFORM_ROUTING_NO_NAME').$pf, JText::_('AEC_ECHECKFORM_ROUTING_NO_DESC') . '" autocomplete="off', $vcontent );
					break;
				case 'account_no':
					$var['params']['account_no'] = array( 'inputC', JText::_('AEC_ECHECKFORM_ACCOUNT_NO_NAME').$pf, JText::_('AEC_ECHECKFORM_ACCOUNT_NO_DESC') . '" autocomplete="off', $vcontent );
					break;
				case 'account_name':
					$var['params']['account_name'] = array( 'inputC', JText::_('AEC_ECHECKFORM_ACCOUNT_NAME_NAME').$pf, JText::_('AEC_ECHECKFORM_ACCOUNT_NAME_DESC') . '" autocomplete="off', $vcontent );
					break;
				case 'bank_name':
					$var['params']['bank_name'] = array( 'inputC', JText::_('AEC_ECHECKFORM_BANK_NAME_NAME').$pf, JText::_('AEC_ECHECKFORM_BANK_NAME_DESC') . '" autocomplete="off', $vcontent );
					break;
			}
		}

		return $var;
	}

	function getUserform( $var=array(), $values=null, $metaUser=null, $content=array() )
	{
		$lang = JFactory::getLanguage();

		global $aecConfig;

		if ( empty( $values ) ) {
			$values = array( 'firstname', 'lastname' );
		}

		$name = array( '', '' );

		if ( is_object( $metaUser ) ) {
			if ( isset( $metaUser->cmsUser->name ) ) {
				$name = explode( ' ', $metaUser->cmsUser->name );

				if ( empty( $content['firstname'] ) ) {
					$content['firstname'] = $name[0];
				}

				if ( empty( $content['lastname'] ) && isset( $name[1] ) ) {
					$content['lastname'] = $name[1];
				} else {
					$content['lastname'] = '';
				}
			}
		}

		$fieldlist = explode( "\n", AECToolbox::rewriteEngine( $aecConfig->cfg['user_checkout_prefill'], $metaUser ) );

		$cfgarray = array();
		foreach ( $fieldlist as $fcontent ) {
			$c = explode( '=', $fcontent, 2 );

			if ( !is_array( $c ) ) {
				continue;
			}

			if ( empty( $c[0] ) ) {
				continue;
			}

			if ( !empty( $c[1] ) ) {
				$cfgarray[$c[0]] = trim( $c[1] );
			} else {
				$cfgarray[$c[0]] = "";
			}
		}

		$translatelist = array( 'firstname' => 'billFirstName',
								'lastname' => 'billLastName',
								'address' => 'billAddress',
								'address2' => 'billAddress2',
								'city' => 'billCity',
								'nonus' => 'billNonUs',
								'state' => 'billState',
								'state_us' => 'billState',
								'state_usca' => 'billState',
								'zip' => 'billZip',
								'country_list' => 'billCountry',
								'country3_list' => 'billCountry',
								'country' => 'billCountry',
								'phone' => 'billPhone',
								'fax' => 'billFax',
								'company' => 'billCompany'
								);

		$cfgtranslatelist = array( 'state_us' => 'state',
								'state_usca' => 'state',
								'country_list' => 'country',
								'country3_list' => 'country'
								);

		foreach ( $values as $value ) {
			if ( strpos( $value, '*' ) ) {
				$pf = '*';

				$value = substr( $value, 0, -1 );
			} else {
				$pf = '';
			}

			$vcontent = '';
			if ( isset( $content[$value] ) ) {
				$vcontent = $content[$value];
			} elseif( isset( $translatelist[$value] ) ) {
				if ( isset( $content[$translatelist[$value]] ) ) {
					$vcontent = $content[$translatelist[$value]];
				}
			}

			if ( empty( $vcontent ) ) {
				if ( isset( $cfgtranslatelist[$value] ) ) {
					$xvalue = $cfgtranslatelist[$value];
				} else {
					$xvalue = $value;
				}

				if ( !empty( $cfgarray[strtolower($xvalue)] ) ) {
					$vcontent = $cfgarray[strtolower($xvalue)];
				}
			}

			switch ( strtolower( $value ) ) {
				case 'firstname':
					$var['params']['billFirstName'] = array( 'inputC', JText::_('AEC_USERFORM_BILLFIRSTNAME_NAME').$pf, JText::_('AEC_USERFORM_BILLFIRSTNAME_DESC'), $vcontent );
					break;
				case 'lastname':
					$var['params']['billLastName'] = array( 'inputC', JText::_('AEC_USERFORM_BILLLASTNAME_NAME').$pf, JText::_('AEC_USERFORM_BILLLASTNAME_DESC'), $vcontent );
					break;
				case 'address':
					$var['params']['billAddress'] = array( 'inputC', JText::_('AEC_USERFORM_BILLADDRESS_NAME').$pf, JText::_('AEC_USERFORM_BILLADDRESS_DESC'), $vcontent );
					break;
				case 'address2':
					$var['params']['billAddress2'] = array( 'inputC', JText::_('AEC_USERFORM_BILLADDRESS2_NAME').$pf, JText::_('AEC_USERFORM_BILLADDRESS2_DESC'), $vcontent );
					break;
				case 'city':
					$var['params']['billCity'] = array( 'inputC', JText::_('AEC_USERFORM_BILLCITY_NAME').$pf, JText::_('AEC_USERFORM_BILLCITY_DESC'), $vcontent );
					break;
				case 'nonus':
					$var['params']['billNonUs'] = array( 'checkbox', JText::_('AEC_USERFORM_BILLNONUS_NAME').$pf, 1, $vcontent, JText::_('AEC_USERFORM_BILLNONUS_DESC') );
					break;
				case 'state':
					$var['params']['billState'] = array( 'inputC', JText::_('AEC_USERFORM_BILLSTATE_NAME').$pf, JText::_('AEC_USERFORM_BILLSTATE_DESC'), $vcontent );
					break;
				case 'state_us':
					$states = array( '', '--- United States ---', 'AK', 'AL', 'AR', 'AZ', 'CA', 'CO', 'CT', 'DC', 'DE', 'FL', 'GA', 'HI',
										'IA', 'ID', 'IL', 'IN', 'KS', 'KY', 'LA', 'MA', 'MD', 'ME',
										'MI', 'MN', 'MO', 'MS', 'MT', 'NC', 'ND', 'NE', 'NH', 'NJ',
										'NM', 'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD',
										'TN', 'TX', 'UT', 'VA', 'VT', 'WA', 'WI', 'WV', 'WY', 'AA',
										'AE', 'AP', 'AS', 'FM', 'GU', 'MH', 'MP', 'PR', 'PW', 'VI'
										);

					$statelist = array();
					foreach ( $states as $state ) {
						if ( strpos( $state, '---' ) !== false ) {
							$statelist[] = JHTML::_('select.option', 'NONSELECT', $state, 'value', 'text', true );
						} else {
							$statelist[] = JHTML::_('select.option', $state, $state );
						}
					}

					$var['params']['lists']['billState'] = JHTML::_( 'select.genericlist', $statelist, 'billState', 'size="1" class="aec_formfield" title="'.JText::_('AEC_USERFORM_BILLSTATE_DESC').'"', 'value', 'text', $vcontent );
					$var['params']['billState'] = array( 'list', JText::_('AEC_USERFORM_BILLSTATE_NAME').$pf, JText::_('AEC_USERFORM_BILLSTATE_DESC') );
					break;
				case 'state_usca':
					$states = array( '', '--- United States ---', 'AK', 'AL', 'AR', 'AZ', 'CA', 'CO', 'CT', 'DC', 'DE', 'FL', 'GA', 'HI',
										'IA', 'ID', 'IL', 'IN', 'KS', 'KY', 'LA', 'MA', 'MD', 'ME',
										'MI', 'MN', 'MO', 'MS', 'MT', 'NC', 'ND', 'NE', 'NH', 'NJ',
										'NM', 'NV', 'NY', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD',
										'TN', 'TX', 'UT', 'VA', 'VT', 'WA', 'WI', 'WV', 'WY', 'AA',
										'AE', 'AP', 'AS', 'FM', 'GU', 'MH', 'MP', 'PR', 'PW', 'VI',
										'--- Canada ---','AB','BC','MB','NB','NL','NT','NS','NU','ON','PE','QC','SK','YT'
										);

					$statelist = array();
					foreach ( $states as $state ) {
						if ( strpos( $state, '---' ) !== false ) {
							$statelist[] = JHTML::_('select.option', 'NONSELECT', $state, 'value', 'text', true );
						} else {
							$statelist[] = JHTML::_('select.option', $state, $state );
						}
					}

					$var['params']['lists']['billState'] = JHTML::_( 'select.genericlist', $statelist, 'billState', 'size="1" class="aec_formfield" title="'.JText::_('AEC_USERFORM_BILLSTATEPROV_DESC').'"', 'value', 'text', $vcontent );
					$var['params']['billState'] = array( 'list', JText::_('AEC_USERFORM_BILLSTATEPROV_NAME').$pf, JText::_('AEC_USERFORM_BILLSTATEPROV_DESC') );
					break;
				case 'zip':
					$var['params']['billZip'] = array( 'inputC', JText::_('AEC_USERFORM_BILLZIP_NAME').$pf, JText::_('AEC_USERFORM_BILLZIP_DESC'), $vcontent );
					break;
				case 'country_list':
					$countries = AECToolbox::getCountryCodeList();

					$countrylist[] = JHTML::_('select.option', '', JText::_('COUNTRYCODE_SELECT'), 'value', 'text', true );

					if ( empty( $vcontent ) ) {
						$vcontent = 'US';
					}

					$countrylist = array();
					foreach ( $countries as $country ) {
						if ( !empty( $country ) ) {
							$cname = JText::_( 'COUNTRYCODE_' . $country );

							if ( $vcontent == $cname ) {
								$vcontent = $country;
							}

							$countrylist[] = JHTML::_('select.option', $country, $cname );
						}
					}

					$var['params']['lists']['billCountry'] = JHTML::_( 'select.genericlist', $countrylist, 'billCountry', 'size="1" class="aec_formfield" title="'.JText::_('AEC_USERFORM_BILLCOUNTRY_DESC').'"', 'value', 'text', $vcontent );
					$var['params']['billCountry'] = array( 'list', JText::_('AEC_USERFORM_BILLCOUNTRY_NAME').$pf, JText::_('AEC_USERFORM_BILLCOUNTRY_DESC') );
					break;
				case 'country3_list':
					$countries = AECToolbox::getCountryCodeList( 'num' );

					if ( empty( $vcontent ) ) {
						$vcontent = 826;
					}

					$conversion = AECToolbox::ISO3166_conversiontable( 'num', 'a2' );

					$countrylist = array();
					$countrylist[] = JHTML::_('select.option', '', JText::_('COUNTRYCODE_SELECT'), 'value', 'text', true );

					foreach ( $countries as $country ) {
						if ( $lang->hasKey( 'COUNTRYCODE_' . $conversion[$country] ) ) {
							$cname = JText::_( 'COUNTRYCODE_' . $conversion[$country] );

							if ( $vcontent == $cname ) {
								$vcontent = $country;
							}

							$countrylist[] = JHTML::_('select.option', $country, $cname );
						} elseif ( is_null( $country ) ) {
							$countrylist[] = JHTML::_('select.option', '', " -- -- -- -- -- -- ", 'value', 'text', true );
						}
					}

					$var['params']['lists']['billCountry'] = JHTML::_( 'select.genericlist', $countrylist, 'billCountry', 'size="1" class="aec_formfield" title="'.JText::_('AEC_USERFORM_BILLCOUNTRY_DESC').'"', 'value', 'text', $vcontent );
					$var['params']['billCountry'] = array( 'list', JText::_('AEC_USERFORM_BILLCOUNTRY_NAME').$pf, JText::_('AEC_USERFORM_BILLCOUNTRY_DESC') );
					break;
				case 'country':
					$var['params']['billCountry'] = array( 'inputC', JText::_('AEC_USERFORM_BILLCOUNTRY_NAME').$pf, JText::_('AEC_USERFORM_BILLCOUNTRY_DESC'), $vcontent );
					break;
				case 'phone':
					$var['params']['billPhone'] = array( 'inputC', JText::_('AEC_USERFORM_BILLPHONE_NAME').$pf, JText::_('AEC_USERFORM_BILLPHONE_DESC'), $vcontent );
					break;
				case 'fax':
					$var['params']['billFax'] = array( 'inputC', JText::_('AEC_USERFORM_BILLFAX_NAME').$pf, JText::_('AEC_USERFORM_BILLFAX_DESC'), $vcontent );
					break;
				case 'company':
					$var['params']['billCompany'] = array( 'inputC', JText::_('AEC_USERFORM_BILLCOMPANY_NAME').$pf, JText::_('AEC_USERFORM_BILLCOMPANY_DESC'), $vcontent );
					break;
			}
		}

		return $var;
	}

	function getFormInfo( $var=array(), $values=null )
	{
		if ( empty( $values ) ) {
			$values = array( 'asterisk' );
		}

		foreach ( $values as $value ) {
			switch ( strtolower( $value ) ) {
				case 'asterisk':
					$var['params']['asteriskInfo'] = array( 'p', 0, JText::_('AEC_FORMINFO_ASTERISK'), null, ' class="asterisk-info"' );
					break;
			}
		}

		return $var;
	}

	function sanitizeRequest( &$request )
	{
		if ( isset( $request->int_var['params']['cardNumber'] ) ) {
			$pfx = "";
			if ( strpos( $request->int_var['params']['cardNumber'], 'XXXX' ) !== false ) {
				$pfx = "XXX";
			}

			$request->int_var['params']['cardNumber'] = $pfx . preg_replace( '/[^0-9]+/i', '', $request->int_var['params']['cardNumber'] );
		}

		return true;
	}

	function checkoutProcess( $request, $InvoiceFactory )
	{
		$this->sanitizeRequest( $request );

		// Create the xml string
		$xml = $this->createRequestXML( $request );

		// Transmit xml to server
		$response = $this->transmitRequestXML( $xml, $request );

		if ( empty( $response['invoice'] ) ) {
			$response['invoice'] = $request->invoice->invoice_number;
		}

		if ( $request->invoice->invoice_number != $response['invoice'] ) {
			

			$request->invoice = new Invoice();
			$request->invoice->loadInvoiceNumber( $response['invoice'] );
		}

		return $this->checkoutResponse( $request, $response, $InvoiceFactory );
	}

	function transmitRequest( $url, $path, $content=null, $port=443, $curlextra=null, $header=null )
	{
		if ( is_array( $header ) ) {
			if ( !isset( $header["Content-Type"] ) ) {
				$header["Content-Type"] = "text/xml";
			}
		} else {
			$header = array( "Content-Type" => "text/xml" );
		}

		return parent::transmitRequest( $url, $path, $content, $port, $curlextra, $header );
	}

	function checkoutResponse( $request, $response, $InvoiceFactory=null )
	{
		if ( !empty( $response['error'] ) ) {
			return $response;
		}

		if ( $response != false ) {
			$resp = array();
			if ( isset( $response['raw'] ) ) {
				if ( is_array( $response['raw'] ) ) {
					$resp = $response['raw'];
				} else {
					$resp['response'] = $response['raw'];
				}
				unset( $response['raw'] );
			}

			return $request->invoice->processorResponse( $InvoiceFactory, $response, $resp, true );
		} else {
			return false;
		}
	}

	function simpleCheckoutMod( $array )
	{
		if ( empty( $this->aec_checkout_mod ) ) {
			$this->aec_checkout_mod = array();
		}

		foreach ( $array as $k => $v ) {
			$this->aec_checkout_mod[$k] = $v;
		}
	}

	function modifyCheckout( &$int_var, &$InvoiceFactory )
	{
		if ( !empty( $this->aec_checkout_mod ) ) {
			foreach ( $this->aec_checkout_mod as $k => $v ) {
				$InvoiceFactory->checkout[$k] = $v;
			}
		}
	}

	function XMLtoArray( $xml )
	{
		if ( !( $xml->children() ) ) {
			return (string) $xml;
		}

		foreach ( $xml->children() as $child ) {
			$name = $child->getName();

			if ( count( $xml->$name ) == 1 ) {
				$element[$name] = $this->XMLtoArray( $child );
			} else {
				$element[][$name] = $this->XMLtoArray( $child );
			}
		}

		return $element;
	}

	function NVPtoArray( $nvpstr )
	{
		$intial = 0;
	 	$nvpArray = array();

		while ( strlen( $nvpstr ) ) {
			// postion of Key
			$keypos = strpos( $nvpstr, '=' );

			// position of value
			$valuepos = strpos( $nvpstr, '&' ) ? strpos( $nvpstr, '&' ) : strlen( $nvpstr );

			// getting the Key and Value values and storing in a Associative Array
			$keyval = substr( $nvpstr, $intial, $keypos );
			$valval = substr( $nvpstr, $keypos+1, $valuepos-$keypos-1 );

			// decoding the respose
			$nvpArray[urldecode( $keyval )] = urldecode( $valval );
			$nvpstr = substr( $nvpstr, $valuepos+1, strlen( $nvpstr ) );
		}

		return $nvpArray;
	}

	function arrayToNVP( $var, $uppercase=false )
	{
		$content = array();
		foreach ( $var as $name => $value ) {
			if ( $uppercase ) {
				$content[] .= strtoupper( $name ) . '=' . urlencode( stripslashes( $value ) );
			} else {
				$content[] .= $name . '=' . urlencode( stripslashes( $value ) );
			}
		}

		return implode( '&', $content );
	}

	function XMLsubstring_tag( $haystack, $tag )
	{
		return XMLprocessor::substring_between( $haystack, '<' . $tag . '>', '</' . $tag . '>' );
	}

	function substring_between( $haystack, $start, $end )
	{
		if ( strpos( $haystack, $start ) === false || strpos( $haystack, $end ) === false ) {
			return false;
		 } else {
			$start_position = strpos( $haystack, $start ) + strlen( $start );
			$end_position = strpos( $haystack, $end );
			return substr( $haystack, $start_position, $end_position - $start_position );
		}
	}

}

class SOAPprocessor extends XMLprocessor
{
	function transmitRequest( $url, $path, $command, $content, $headers=null, $options=null )
	{
		global $aecConfig;

		$this->soapclient = new SoapClient( $url, $options );
		
		if ( method_exist( $this->soapclient, '__soapCall' ) ) {
			$response['raw'] = $this->soapclient->__soapCall( $command, $content );
		} elseif ( method_exist( $this->soapclient, 'soapCall' ) ) {
			$response['raw'] = $this->soapclient->soapCall( $command, $content );
		} else {
			$response['raw'] = $this->soapclient->call( $command, $content );
		}

		if ( $response['raw']->error != 0 ) {
			$response['error'] = "Error calling native SOAP function: " . $response['raw']->error . ": " . $response['raw']->errorDescription;
		}

		return $response;
	}

	function followupRequest( $command, $content )
	{
		if ( empty( $this->soapclient ) ) {
			return null;
		}

		if ( !is_object( $this->soapclient ) ) {
			return null;
		}

		$response = array();

		if ( is_a( $this->soapclient, 'SoapClient' ) ) {
			$response['raw'] = $this->soapclient->__soapCall( $command, $content );

			if ( $return_val->error != 0 ) {
				$response['error'] = "Error calling SOAP function: " . $return_val->error;
			}

			return $response;
		} else {
			$response['raw'] = $this->soapclient->call( $command, $content );

			$err = $this->soapclient->getError();

			if ( $err != false ) {
				$response['error'] = "Error calling SOAP function: " . $err;
			}

			return $response;
		}
	}
}

class PROFILEprocessor extends XMLprocessor
{

	function ProfileAdd( $request, $profileid )
	{
		$ppParams = new stdClass();

		$ppParams->profileid			= $profileid;

		$ppParams->paymentprofileid		= '';
		$ppParams->paymentProfiles		= array();

		$request->metaUser->meta->setProcessorParams( $request->parent->id, $ppParams );

		return $ppParams;
	}

	function payProfileSelect( $var, $ppParams, $select=false, $btn=true )
	{
		$var['params'][] = array( 'p', JText::_('AEC_USERFORM_BILLING_DETAILS_NAME') );

		if ( !empty( $ppParams->paymentProfiles ) ) {
			// Single-Select Payment Option
			foreach ( $ppParams->paymentProfiles as $pid => $pobj ) {
				$info = array();

				$info_array = get_object_vars( $pobj->profilehash );

				foreach ( $info_array as $iak => $iav ) {
					if ( !empty( $iav ) ) {
						$info[] = $iav;
					}
				}

				if ( empty( $ppParams->paymentprofileid ) ) {
					$ppParams->paymentprofileid = $pid;
				}

				if ( $ppParams->paymentprofileid == $pid ) {
					$text = '<strong>' . implode( '<br />', $info ) . '</strong>';
				} else {
					$text = implode( '<br />', $info );
				}

				$var['params']['payprofileselect_'.$pid] = array( 'radio', 'payprofileselect', $pid, $ppParams->paymentprofileid, $text );
			}

			if ( count( $ppParams->paymentProfiles ) < 10 ) {
				$var['params']['payprofileselect_new'] = array( 'radio', 'payprofileselect', "new", "", 'new billing details' );
			}

			if ( $btn ) {
				$var['params']['edit_payprofile'] = array( 'submit', '', '', ( $select ? JText::_('BUTTON_SELECT') : JText::_('BUTTON_EDIT') ) );
			}
		}

		return $var;
	}

	function payProfileAdd( $request, $profileid, $details, $ppParams )
	{
		$pointer = count( $ppParams->paymentProfiles );

		$data = new stdClass();
		$data->profileid	= $profileid;
		$data->profilehash	= $this->payProfileHash( $details );

		$ppParams->paymentProfiles[$pointer] = $data;

		$ppParams->paymentprofileid = $pointer;

		$request->metaUser->meta->setProcessorParams( $request->parent->id, $ppParams );

		return $ppParams;
	}

	function payProfileUpdate( $request, $profileid, $details, $ppParams )
	{
		$ppParams->paymentProfiles[$profileid]->profilehash = $this->payProfileHash( $details );

		$ppParams->paymentprofileid = $profileid;

		$request->metaUser->meta->setProcessorParams( $request->parent->id, $ppParams );

		return $ppParams;
	}

	function payProfileHash( $post )
	{
		$hash = new stdClass();
		$hash->name		= $post['billFirstName'] . ' ' . $post['billLastName'];
		$hash->address	= $post['billAddress'];
		$hash->zipcity	= $post['billZip'] . ' ' . $post['billCity'];

		if ( !empty( $post['account_no'] ) ) {
			$hash->cc		= 'XXXX' . substr( $post['account_no'], -4 );
		} else {
			$hash->cc		= 'XXXX' . substr( $post['cardNumber'], -4 );
		}

		return $hash;
	}

	function shipProfileSelect( $var, $ppParams, $select=false, $btn=true, $new=true )
	{
		$var['params'][] = array( 'p', JText::_('AEC_USERFORM_SHIPPING_DETAILS_NAME') );

		if ( !empty( $ppParams->shippingProfiles ) ) {
			// Single-Select Shipment Data
			foreach ( $ppParams->shippingProfiles as $pid => $pobj ) {
				$info = array();

				$info_array = get_object_vars( $pobj->profilehash );

				foreach ( $info_array as $iak => $iav ) {
					if ( !empty( $iav ) ) {
						$info[] = $iav;
					}
				}

				if ( empty( $ppParams->shippingprofileid ) ) {
					$ppParams->shippingprofileid = $pid;
				}

				if ( $ppParams->shippingprofileid == $pid ) {
					$text = '<strong>' . implode( '<br />', $info ) . '</strong>';
				} else {
					$text = implode( '<br />', $info );
				}

				$var['params']['shipprofileselect_'.$pid] = array( 'radio', 'shipprofileselect', $pid, $ppParams->shippingprofileid, $text );
			}

			if ( ( count( $ppParams->shippingProfiles ) < 10 ) && $new ) {
				$var['params']['shipprofileselect_new'] = array( 'radio', 'shipprofileselect', "new", "", 'new shipping details' );
			}

			if ( $btn ) {
				$var['params']['edit_shipprofile'] = array( 'submit', '', '', ( $select ? JText::_('BUTTON_SELECT') : JText::_('BUTTON_EDIT') ) );
			}
		}

		return $var;
	}

	function shipProfileAdd( $request, $profileid, $post, $ppParams )
	{
		$pointer = count( $ppParams->paymentProfiles );

		$ppParams->shippingProfiles[$pointer] = new stdClass();
		$ppParams->shippingProfiles[$pointer]->profileid = $profileid;

		$ppParams->shippingProfiles[$pointer]->profilehash = $this->shipProfileHash( $post );

		$ppParams->shippingprofileid = $pointer;

		$request->metaUser->meta->setProcessorParams( $request->parent->id, $ppParams );

		return $ppParams;
	}

	function shipProfileUpdate( $request, $profileid, $post, $ppParams )
	{
		$ppParams->shippingProfiles[$profileid]->profilehash = $this->shipProfileHash( $post );

		$ppParams->shippingprofileid = $profileid;

		$request->metaUser->meta->setProcessorParams( $request->parent->id, $ppParams );

		return $ppParams;
	}

	function shipProfileHash( $post )
	{
		$hash = new stdClass();
		$hash->name		= $post['billFirstName'] . ' ' . $post['billLastName'];
		$hash->address	= $post['billAddress'];
		$hash->zipcity	= $post['billZip'] . ' ' . $post['billCity'];

		return $hash;
	}

}

class POSTprocessor extends processor
{
	function checkoutAction( $request, $InvoiceFactory=null, $xvar=null, $text=null )
	{
		if ( empty( $xvar ) ) {
			$var = $this->createGatewayLink( $request );

			if ( !empty( $this->settings['customparams'] ) ) {
				$var = $this->customParams( $this->settings['customparams'], $var, $request );
			}
		} else {
			$var = $xvar;
		}

		$onclick = "";
		if ( isset( $var['_aec_checkout_onclick'] ) ) {
			$onclick = 'onclick="' . $var['_aec_checkout_onclick'] . '"';
			unset( $var['_aec_checkout_onclick'] );
		}

		$return = '<form action="' . $var['post_url'] . '" method="post">' . "\n";
		unset( $var['post_url'] );

		foreach ( $var as $key => $value ) {
			$return .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
		}

		if ( empty( $text ) ) {
			$text = JText::_('BUTTON_CHECKOUT'); 
		}

		$return .= '<button type="submit" class="button aec-btn btn btn-primary" id="aec-checkout-btn" ' . $onclick . '>' . aecHTML::Icon( 'shopping-cart', true ) . $text . '</button>' . "\n";
		$return .= '</form>' . "\n";

		return $return;
	}
}

class GETprocessor extends processor
{
	function checkoutAction( $request, $InvoiceFactory=null )
	{
		$var = $this->createGatewayLink( $request );

		if ( !empty( $this->settings['customparams'] ) ) {
			$var = $this->customParams( $this->settings['customparams'], $var, $request );
		}

		$onclick = "";
		if ( isset( $var['_aec_checkout_onclick'] ) ) {
			$onclick = ' onclick="' . $var['_aec_checkout_onclick'] . '"';
			unset( $var['_aec_checkout_onclick'] );
		}

		$return = '<form action="' . $var['post_url'] . '" method="get">' . "\n";
		unset( $var['post_url'] );

		foreach ( $var as $key => $value ) {
			$return .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
		}

		$return .= '<button type="submit" class="button aec-btn btn btn-primary" id="aec-checkout-btn" ' . $onclick . '>' . aecHTML::Icon( 'shopping-cart', true ) . JText::_('BUTTON_CHECKOUT') . '</button>' . "\n";
		$return .= '</form>' . "\n";

		return $return;
	}
}

class URLprocessor extends processor
{
	function checkoutAction( $request, $InvoiceFactory=null )
	{
		$var = $this->createGatewayLink( $request );

		if ( isset( $var['_aec_html_head'] ) ) {
			$document=& JFactory::getDocument();

			if ( is_array( $var['_aec_html_head'] ) ) {
				foreach ( $var['_aec_html_head'] as $content ) {
					$document->addCustomTag( $content );
				}
			} else {
				$document->addCustomTag( $var['_aec_html_head'] );
			}

			unset( $var['_aec_html_head'] );
		}

		if ( !empty( $this->settings['customparams'] ) ) {
			$var = $this->customParams( $this->settings['customparams'], $var, $request );
		}

		if ( isset( $var['_aec_checkout_onclick'] ) ) {
			$onclick = ' onclick="' . $var['_aec_checkout_onclick'] . '"';
			unset( $var['_aec_checkout_onclick'] );
		} else {
			$onclick = '';
		}

		$return = '<a href="' . $var['post_url'];
		unset( $var['post_url'] );

		if ( substr( $return, -1, 1 ) !== '?' ) {
			$return .= '?';
		}

		$vars = array();
		if ( !empty( $var ) ) {
			foreach ( $var as $key => $value ) {
				$vars[] .= urlencode( $key ) . '=' . urlencode( $value );
			}

			$return .= implode( '&amp;', $vars );
		}

		$return .= '"' . $onclick . ' class="button aec-btn btn btn-primary" >' . aecHTML::Icon( 'shopping-cart', true ) . JText::_('BUTTON_CHECKOUT') . '</a>' . "\n";

		return $return;
	}
}

?>

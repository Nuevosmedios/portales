<?php
/**
 * @version $Id: acctexp.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

global $aecConfig;

define( '_AEC_VERSION', '1.2RC' );
define( '_AEC_REVISION', '5942' );

include_once( JPATH_SITE . '/components/com_acctexp/lib/compat.php' );

$langlist = array(	'com_acctexp' => JPATH_SITE,
					'com_acctexp.iso3166-1a2' => JPATH_SITE,
					'com_acctexp.iso639-1' => JPATH_SITE,
					'com_acctexp.microintegrations' => JPATH_SITE,
					'com_acctexp.processors' => JPATH_SITE
					);

xJLanguageHandler::loadList( $langlist );

if ( !class_exists( 'paramDBTable' ) ) {
	include_once( JPATH_SITE . '/components/com_acctexp/lib/eucalib/eucalib.php' );
}

// Load teh moniez
include_once( JPATH_SITE . '/components/com_acctexp/lib/mammontini/mammontini.php' );

$aecclasses = array(	'api',
						'bucket',
						'cart',
						'config',
						'coupon',
						'displaypipeline',
						'event',
						'eventlog',
						'heartbeat',
						'history',
						'html',
						'invoice',
						'itemgroup',
						'microintegration',
						'paymentprocessor',
						'registration',
						'restriction',
						'rewriteengine',
						'settings',
						'subscription',
						'subscriptionplan',
						'template',
						'temptoken',
						'toolbox',
						'user'
					);

foreach ( $aecclasses as $class ) {
	include_once( dirname(__FILE__) . '/classes/acctexp.' . $class . '.class.php' );
}

function aecDebug( $text, $level = 128 )
{
	aecQuickLog( 'debug', 'debug', $text, $level );
}

function aecQuickLog( $short, $tags, $text, $level = 128 )
{
	$eventlog = new eventLog();
	if ( empty( $text ) ) {
		$eventlog->issue( $short, $tags, "[[EMPTY]]", $level );
	} elseif ( is_array( $text ) || is_object( $text ) ) {
		// Due to some weird error, json_encode sometimes throws a notice - even on a proper array or object
		$eventlog->issue( $short, $tags, @json_encode( $text ), $level );
	} elseif ( is_string( $text ) || is_bool( $text ) || is_float( $text ) ) {
		$eventlog->issue( $short, $tags, $text, $level );
	} elseif ( is_numeric( $text ) ) {
		$eventlog->issue( $short, $tags, $text, $level );
	} else {
		$eventlog->issue( $short, $tags, "[[UNSUPPORTED TYPE]]", $level );
	}
}

function aecGetParam( $name, $default='', $safe=false, $safe_params=array() )
{
	$return = JArrayHelper::getValue( $_REQUEST, $name, $default );

	if ( !isset( $_REQUEST[$name] ) && !isset( $_POST[$name] ) ) {
		return $default;
	}

	if ( !is_array( $return ) ) {
		$return = trim( $return );
	}

	if ( !empty( $_POST[$name] ) ) {
		if ( is_array( $_POST[$name] ) && !is_array( $return ) ) {
			$return = $_POST[$name];
		} elseif ( empty( $return ) ) {
			$return = $_POST[$name];
		}
	}

	if ( empty( $return ) && !empty( $_REQUEST[$name] ) ) {
		$return = $_REQUEST[$name];
	}

	if ( $safe ) {
		if ( is_array( $return ) ) {
			foreach ( $return as $k => $v ) {
				$return[$k] = aecEscape( $v, $safe_params );
			}
		} else {
			$return = aecEscape( $return, $safe_params );
		}

	}

	return $return;
}

function aecEscape( $value, $safe_params )
{
	if ( is_array( $value ) ) {
		$array = array();
		foreach ( $value as $k => $v ) {
			$array[$k] = aecEscape( $v, $safe_params );
		}

		return $array;
	}

	$regex = "#{aecjson}(.*?){/aecjson}#s";

	// find all instances of json code
	$matches = array();
	preg_match_all( $regex, $value, $matches, PREG_SET_ORDER );

	if ( count( $matches ) ) {
		$value = str_replace( $matches, array(''), $value );
	}

	if ( get_magic_quotes_gpc() ) {
		$return = stripslashes( $value );
	} else {
		$return = $value;
	}

	if ( in_array( 'clear_nonemail', $safe_params ) ) {
		if ( strpos( $value, '@' ) === false ) {
			if ( !in_array( 'clear_nonalnum', $safe_params ) ) {
				// This is not a valid email adress to begin with, so strip everything hazardous
				$safe_params[] = 'clear_nonalnum';
			}
		} else {
			$array = explode('@', $return, 2);

			$username = preg_replace( '/[^a-z0-9._+-]+/i', '', $array[0] );
			$domain = preg_replace( '/[^a-z0-9.-]+/i', '', $array[1] );

			$return = $username.'@'.$domain;
		}
	}

	if ( in_array( 'clear_nonalnumwhitespace', $safe_params ) ) {
		$return = preg_replace( "/[^a-z0-9@._+-\s]/i", '', $return );
	}

	if ( in_array( 'clear_nonalnum', $safe_params ) ) {
		$return = preg_replace( "/[^a-z0-9@._+-]/i", '', $return );
	}

	if ( !empty( $safe_params ) ) {
		foreach ( $safe_params as $param ) {
			switch ( $param ) {
				case 'word':
					$e = strpos( $return, ' ' );
					if ( $e !== false ) {
						$r = substr( $return, 0, $e );
					} else {
						$r = $return;
					}
					break;
				case 'badchars':
					$r = preg_replace( "#[<>\"'%;()&]#i", '', $return );
					break;
				case 'int':
					$r = (int) $return;
					break;
				case 'string':
					$r = (string) $return;
					break;
				case 'float':
					$r = (float) $return;
					break;
			}

			$return = $r;
		}

	}

	$db = &JFactory::getDBO();

	return xJ::escape( $db, $return );
}

function aecPostParamClear( $array, $safe=false, $safe_params=array( 'string', 'badchars' ) )
{
	$cleararray = array();
	foreach ( $array as $key => $value ) {
		$cleararray[$key] = aecGetParam( $key, $safe, $safe_params );
	}

	return $cleararray;
}

function aecRedirect( $url, $msg=null, $class=null )
{
	$app = JFactory::getApplication();

	$app->redirect( $url, $msg, $class );
}

class GeneralInfoRequester
{
	/**
	 * Check whether a component is installed
	 * @return Bool
	 */
	function detect_component( $component )
	{
		$db = &JFactory::getDBO();

		global $aecConfig;

		$app = JFactory::getApplication();

		$tables	= array();
		$tables	= $db->getTableList();

		if ( !empty( $aecConfig->cfg['bypassintegration'] ) ) {
			$bypass = str_replace( ',', ' ', $aecConfig->cfg['bypassintegration'] );

			$overrides = explode( ' ', $bypass );

			foreach ( $overrides as $i => $c ) {
				$overrides[$i] = trim($c);
			}

			if ( in_array( 'CB', $overrides ) ) {
				$overrides[] = 'CB1.2';
			}

			if ( in_array( 'CB', $overrides ) || in_array( 'CB1.2', $overrides ) || in_array( 'CBE', $overrides ) ) {
				$overrides[] = 'anyCB';
			}

			if ( in_array( $component, $overrides ) ) {
				return false;
			}
		}

		$pathCB		= JPATH_SITE . '/components/com_comprofiler';

		switch ( $component ) {
			case 'anyCB': // any Community Builder
				return is_dir( $pathCB );
				break;

			case 'CB1.2': // Community Builder 1.2
				$is_cbe	= ( is_dir( $pathCB. '/enhanced' ) || is_dir( $pathCB . '/enhanced_admin' ) );
				$is_cb	= ( is_dir( $pathCB ) && !$is_cbe );

				$is12 = file_exists( $pathCB . '/js/cb12.js' );

				return ( $is_cb && $is12 );
				break;

			case 'CB': // Community Builder
				$is_cbe	= ( is_dir( $pathCB. '/enhanced' ) || is_dir( $pathCB . '/enhanced_admin' ) );
				$is_cb	= ( is_dir( $pathCB ) && !$is_cbe );
				return $is_cb;
				break;

			case 'CBE': // Community Builder Enhanced
				$is_cbe = ( is_dir( $pathCB . '/enhanced' ) || is_dir( $pathCB . '/enhanced_admin' ) );
				return $is_cbe;
				break;

			case 'CBM': // Community Builder Moderator for Workflows
				return file_exists( JPATH_SITE . '/modules/mod_comprofilermoderator.php' );
				break;

			case 'ALPHA': // AlphaRegistration
				return file_exists( JPATH_SITE . '/components/com_alpharegistration/alpharegistration.php' );
				break;

			case 'UE': // User Extended
				return in_array( $app->getCfg( 'dbprefix' ) . 'user_extended', $tables );
				break;

			case 'SMF': // Simple Machines Forum
				$pathSMF	= JPATH_SITE . '/administrator/components/com_smf';

				return file_exists( $pathSMF . '/config.smf.php') || file_exists( $pathSMF . '/smf.php' );
				break;

			case 'VM': // VirtueMart
				return in_array( $app->getCfg( 'dbprefix' ) . 'vm_orders', $tables );
				break;

			case 'JACL': // JACL
				return in_array( $app->getCfg( 'dbprefix' ) . 'jaclplus', $tables );
				break;

			case 'UHP2':
				return file_exists( JPATH_SITE . '/modules/mod_uhp2_manage.php' );
				break;

			case 'JUSER':
				return file_exists( JPATH_SITE . '/components/com_juser/juser.php' );
				break;

			case 'JOMSOCIAL':
				return file_exists( JPATH_SITE . '/components/com_community/community.php' );
				break;
		}
	}
}

class AECfetchfromDB
{
	function UserIDfromInvoiceNumber( $invoice_number )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `userid`'
				. ' FROM #__acctexp_invoices'
				. ' WHERE `invoice_number` = \'' . $invoice_number . '\''
				;
		$db->setQuery( $query );

		return $db->loadResult();
	}

	function InvoiceIDfromNumber( $invoice_number, $userid = 0, $override_active = false )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_invoices'
				;

		if ( $override_active ) {
			$query .= ' WHERE';
		} else {
			$query .= ' WHERE `active` = \'1\' AND';
		}

		$query .= ' ( `invoice_number` LIKE \'' . $invoice_number . '\''
				. ' OR `secondary_ident` LIKE \'' . $invoice_number . '\' )'
				;

		if ( $userid ) {
			$query .= ' AND `userid` = \'' . ( (int) $userid ) . '\'';
		}

		$db->setQuery( $query );

		return $db->loadResult();
	}

	function InvoiceNumberfromId( $id, $override_active = false )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `invoice_number`'
				. ' FROM #__acctexp_invoices'
				;

		if ( $override_active ) {
			$query .= ' WHERE';
		} else {
			$query .= ' WHERE `active` = \'1\' AND';
		}

		$query .= ' `id` = \'' . ( (int) $id ) . '\'';

		$db->setQuery( $query );

		return $db->loadResult();
	}

	function lastUnclearedInvoiceIDbyUserID( $userid, $excludedusage=null )
	{
		global $aecConfig;

		if ( empty( $excludedusage ) ) {
			$excludedusage = array();
		}

		$db = &JFactory::getDBO();

		$query = 'SELECT `id`, `invoice_number`, `usage`'
				. ' FROM #__acctexp_invoices'
				. ' WHERE `userid` = \'' . ( (int) $userid ) . '\''
				. ' AND `transaction_date` = \'0000-00-00 00:00:00\''
				. ' AND `active` = \'1\''
				. ' ORDER BY `id` DESC'
				;
		$db->setQuery( $query );
		$invoice_list = $db->loadObjectList();

		if ( empty( $invoice_list ) ) {
			return false;
		}

		foreach ( $invoice_list as $invoice ) {
			if ( strpos( $invoice->usage, '.' ) ) {
				return $invoice->invoice_number;
			} elseif ( !in_array( $invoice->usage, $excludedusage ) ) {
				$status = SubscriptionPlanHandler::PlanStatus( $invoice->usage );
				if ( $status || ( !$status && $aecConfig->cfg['allow_invoice_unpublished_item'] ) ) {
					return $invoice->invoice_number;
				} else {
					// Plan is not active anymore, try the next invoice.
					$excludedusage[] = $invoice->usage;

					return AECfetchfromDB::lastUnclearedInvoiceIDbyUserID( $userid, $excludedusage );
				}
			}
		}

		return false;
	}

	function lastClearedInvoiceIDbyUserID( $userid, $planid=0 )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT id'
				. ' FROM #__acctexp_invoices'
				. ' WHERE `userid` = \'' . (int) $userid . '\''
				;

		if ( $planid ) {
			$query .= ' AND `usage` = \'' . (int) $planid . '\'';
		}

		$query .= ' ORDER BY `transaction_date` DESC';

		$db->setQuery( $query );

		return $db->loadResult();
	}

	function InvoiceCountbyUserID( $userid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT count(*)'
				. ' FROM #__acctexp_invoices'
				. ' WHERE `userid` = \'' . (int) $userid . '\''
				. ' AND `active` = \'1\''
				;
		$db->setQuery( $query );

		return $db->loadResult();
	}

	function UnpaidInvoiceCountbyUserID( $userid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT count(*)'
				. ' FROM #__acctexp_invoices'
				. ' WHERE `userid` = \'' . (int) $userid . '\''
				. ' AND `active` = \'1\''
				. ' AND `transaction_date` = \'0000-00-00 00:00:00\''
				;
		$db->setQuery( $query );

		return $db->loadResult();
	}

	function PaidInvoiceCountbyUserID( $userid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT count(*)'
				. ' FROM #__acctexp_invoices'
				. ' WHERE `userid` = \'' . (int) $userid . '\''
				. ' AND `active` = \'1\''
				. ' AND `transaction_date` != \'0000-00-00 00:00:00\''
				;
		$db->setQuery( $query );

		return $db->loadResult();
	}

	function InvoiceNumberbyCartId( $userid, $cartid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `invoice_number`'
				. ' FROM #__acctexp_invoices'
				. ' WHERE `userid` = \'' . $userid . '\''
				. ' AND `usage` = \'c.' . $cartid . '\''
				;

		$db->setQuery( $query );

		return $db->loadResult();
	}

	function InvoiceIdList( $userid, $start, $limit, $sort='`transaction_date` DESC' )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_invoices'
				. ' WHERE `userid` = \'' . $userid . '\''
				. ' AND `active` = \'1\''
				. ' ORDER BY ' . $sort . ', `id` DESC'
				. ' LIMIT ' . $start . ',' . $limit
				;
		$db->setQuery( $query );

		return xJ::getDBArray( $db );
	}

	function SubscriptionIDfromUserID( $userid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_subscr'
				. ' WHERE `userid` = \'' . (int) $userid . '\''
				. ' ORDER BY `primary` DESC'
				;
		$db->setQuery( $query );

		return $db->loadResult();
	}

	function RecurringStatusfromSubscriptionID( $subscriptionid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `recurring`'
				. ' FROM #__acctexp_subscr'
				. ' WHERE `id` = \'' . (int) $subscriptionid . '\''
				. ' ORDER BY `primary` DESC'
				;
		$db->setQuery( $query );

		return $db->loadResult();
	}

	function UserIDfromUsername( $username )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT id'
		. ' FROM #__users'
		. ' WHERE username = \'' . aecEscape( $username, array( 'string', 'badchars' ) ) . '\''
		;
		$db->setQuery( $query );

		return $db->loadResult();
	}

	function UserIDfromSubscriptionID( $susbcriptionid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `userid`'
				. ' FROM #__acctexp_subscr'
				. ' WHERE `id` = \'' . (int) $susbcriptionid . '\''
				. ' ORDER BY `primary` DESC'
				;
		$db->setQuery( $query );

		return $db->loadResult();
	}

	function UserExists( $userid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__users'
				. ' WHERE `id` = \'' . $userid . '\''
				;
		$db->setQuery( $query );

		return $db->loadResult();
	}
	
}

?>

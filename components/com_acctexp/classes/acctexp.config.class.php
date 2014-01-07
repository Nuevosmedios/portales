<?php
/**
 * @version $Id: acctexp.config.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class aecConfig extends serialParamDBTable
{
	/** @var int Primary key */
	var $id 				= null;
	/** @var text */
	var $settings 			= null;

	function aecConfig()
	{
		parent::__construct( '#__acctexp_config', 'id' );

		$this->load(1);

		// If we have no settings, init them
		if ( empty( $this->settings ) ) {
			$this->initParams();
		}
	}

	function declareParamFields()
	{
		return array( 'settings' );
	}

	function load( $id )
	{
		parent::load( $id );

		$this->cfg =& $this->settings;
	}

	function check( $fields=array() )
	{
		unset( $this->cfg );

		return parent::check();
	}

	function paramsList()
	{
		$def = array();
		$def['require_subscription']			= 0;
		$def['alertlevel2']						= 7;
		$def['alertlevel1']						= 3;
		$def['expiration_cushion']				= 12;
		$def['heartbeat_cycle']					= 24;
		$def['heartbeat_cycle_backend']			= 1;
		$def['plans_first']						= 0;
		$def['simpleurls']						= 0;
		$def['display_date_backend']			= "";
		$def['skip_confirmation']				= 0;
		// new 0.12.4
		$def['bypassintegration']				= '';
		// new 0.12.4.2
		$def['adminaccess']						= 1;
		$def['noemails']						= 0;
		$def['nojoomlaregemails']				= 0;
		// new 0.12.4.12
		$def['override_reqssl']					= 0;
		// new 0.12.4.16
		$def['invoicenum_doformat']				= 0;
		$def['invoicenum_formatting']			= '{aecjson}{"cmd":"concat","vars":[{"cmd":"date","vars":["Y",{"cmd":"rw_constant",'
													. '"vars":"invoice_created_date"}]},"-",{"cmd":"rw_constant","vars":"invoice_id"}]}'
													.'{/aecjson}';
		$def['use_recaptcha']					= 0;
		$def['ssl_signup']						= 0;
		$def['error_notification_level']		= 32;
		$def['email_notification_level']		= 128;
		$def['temp_auth_exp']					= 15;
		$def['show_fixeddecision']				= 0;
		$def['confirmation_coupons']			= 0;
		$def['breakon_mi_error']				= 0;
		$def['curl_default']					= 1;
		$def['amount_currency_symbol']			= 0;
		$def['amount_currency_symbolfirst']		= 0;
		$def['amount_use_comma']				= 0;
		$def['use_proxy']						= 0;
		$def['proxy']							= '';
		$def['proxy_port']						= '';
		$def['ssl_profile']						= 0;
		$def['overrideJ15']						= 0;
		$def['proxy_username']					= '';
		$def['proxy_password']					= '';
		$def['gethostbyaddr']					= 1;
		$def['root_group']						= 1;
		$def['root_group_rw']					= '';
		$def['integrate_registration']			= 1;
		$def['enable_shoppingcart']				= 0;
		$def['additem_stayonpage']				= '';
		$def['gwlist']							= array();
		$def['altsslurl']						= '';
		$def['checkout_as_gift']				= 0;
		$def['checkout_as_gift_access']			= 23;
		$def['invoice_cushion']					= 10; //Minutes
		$def['allow_frontend_heartbeat']		= 0;
		$def['disable_regular_heartbeat']		= 0;
		$def['custom_heartbeat_securehash']		= "";
		$def['delete_tables']					= "";
		$def['delete_tables_sure']				= "";
		$def['standard_currency']				= "USD";
		$def['manageraccess']					= 0;
		$def['per_plan_mis']					= 0;
		$def['intro_expired']					= 0;
		$def['email_default_admins']			= 1;
		$def['email_extra_admins']				= "";
		$def['countries_available']				= "";
		$def['countries_top']					= "";
		$def['checkoutform_jsvalidation']		= 0;
		$def['allow_invoice_unpublished_item']	= 0;
		$def['itemid_default']					= "";
		$def['itemid_cart']						= "";
		$def['itemid_checkout']					= "";
		$def['itemid_confirmation']				= "";
		$def['itemid_subscribe']				= "";
		$def['itemid_exception']				= "";
		$def['itemid_thanks']					= "";
		$def['itemid_expired']					= "";
		$def['itemid_hold']						= "";
		$def['itemid_notallowed']				= "";
		$def['itemid_pending']					= "";
		$def['itemid_subscriptiondetails']		= "";
		$def['itemid_cb']						= "";
		$def['itemid_joomlauser']				= "";
		$def['checkout_coupons']				= 1;
		$def['customAppAuth']					= "";
		$def['skip_registration']				= 0;
		$def['user_checkout_prefill']			= "firstname=[[user_first_name]]\nlastname=[[user_last_name]]\naddress=\naddress2=\n"
													. "city=\nstate=\nzip=\ncountry=\nphone=\nfax=\ncompany=";
		$def['noemails_adminoverride']			= 1;

		return $def;
	}

	function initParams()
	{
		// Insert a new entry if there is none yet
		if ( empty( $this->settings ) ) {
			$query = 'SELECT * FROM #__acctexp_config'
			. ' WHERE `id` = \'1\''
			;
			$this->_db->setQuery( $query );

			if ( !$this->_db->loadResult() ) {
				$query = 'INSERT INTO #__acctexp_config'
				. ' VALUES( \'1\', \'\' )'
				;
				$this->_db->setQuery( $query );
				$this->_db->query() or die( $this->_db->stderr() );
			}

			$this->id = 1;
			$this->settings = '';
		}

		// Write to Params, do not overwrite existing data
		$this->addParams( $this->paramsList(), 'settings', false );

		$this->storeload();

		return true;
	}

	function saveSettings()
	{
		// Extra check for duplicated rows
		if ( $this->RowDuplicationCheck() ) {
			$this->CleanDuplicatedRows();
			$this->load(1);
		}

		$this->storeload();
	}

	function RowDuplicationCheck()
	{
		$query = 'SELECT count(*)'
				. ' FROM #__acctexp_config'
				;
		$this->_db->setQuery( $query );
		$rows = $this->_db->loadResult();

		if ( $rows > 1 ) {
			return true;
		} else {
			return false;
		}
	}

	function CleanDuplicatedRows()
	{
		$query = 'SELECT max(id)'
				. ' FROM #__acctexp_config'
				;
		$this->_db->setQuery( $query );
		$this->_db->query();
		$max = $this->_db->loadResult();

		$query = 'DELETE'
				. ' FROM #__acctexp_config'
				. ' WHERE `id` != \'' . $max . '\''
				;
		$this->_db->setQuery( $query );
		$this->_db->query();

		if ( !( $max == 1 ) ) {
			$query = 'UPDATE #__acctexp_config'
					. ' SET `id` = \'1\''
					. ' WHERE `id` =\'' . $max . '\''
					;
			$this->_db->setQuery( $query );
			$this->_db->query();
		}
	}
}

if ( !is_object( $aecConfig ) ) {
	global $aecConfig;

	$aecConfig = new aecConfig();
}

?>

<?php
/**
 * @version $Id: acctexp.settings.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class aecSettings
{

	function aecSettings( $area, $subarea='' )
	{
		$this->area				= $area;
		$this->original_subarea	= $subarea;
		$this->subarea			= $subarea;
	}

	function fullSettingsArray( $params, $params_values, $lists = array(), $settings = array(), $showmissing=true ) {
		$this->params			= $params;
		$this->params_values	= $params_values;
		$this->lists			= $lists;
		$this->settings			= $settings;
		$this->prefix			= '';

		$lang = JFactory::getLanguage();

		foreach ( $this->params as $name => $content ) {
			if ( !isset( $content[0] ) ) {
				continue;
			}

			// $content[0] = type
			// $content[1] = value
			// $content[2] = disabled?
			// $content[3] = set name
			// $content[4] = set description

			$cname = $name;

			if ( !empty( $this->prefix ) ) {
				if ( strpos( $name, $this->prefix ) === 0 ) {
					$cname = str_replace( $this->prefix, '', $name );
				}
			}

			$name = $this->prefix . $cname;

			if ( !isset( $this->params_values[$name] ) ) {
				if ( isset( $content[3] ) ) {
					$this->params_values[$name] = $content[3];
				} elseif ( isset( $content[1] ) && !isset( $content[2] ) ) {
					$this->params_values[$name] = $content[1];
				} else {
					$this->params_values[$name] = '';
				}
			}

			if ( isset( $this->params_values[$name] ) ) {
				$value = $this->params_values[$name];
			}

			// Checking for remap functions
			$remap = 'remap_' . $content[0];

			if ( method_exists( $this, $remap ) ) {
				$type = $this->$remap( $name, $value );
			} else {
				$type = $content[0];
			}

			if ( strcmp( $type, 'DEL' ) === 0 ) {
				continue;
			}

			if ( !isset( $content[2] ) ) {
				// Create constant names
				$constant_generic	= strtoupper($this->area)
										. '_' . strtoupper( $this->original_subarea )
										. '_' . strtoupper( $cname );
				$constant			= strtoupper( $this->area )
										. '_' . strtoupper( $this->subarea )
										. '_' . strtoupper( $cname );
				$constantname		= $constant . '_NAME';
				$constantdesc		= $constant . '_DESC';
				
				// If the constantname does not exists, try a generic name
				if ( $lang->hasKey( $constantname ) ) {
					$info_name = JText::_( $constantname );
				} else {
					$info_name = JText::_( $constant_generic . '_NAME' );
				}

				// If the constantdesc does not exists, try a generic desc
				if ( $lang->hasKey( $constantdesc ) ) {
					$info_desc = JText::_( $constantdesc );
				} else {
					$info_desc = JText::_( $constant_generic . '_DESC' );
				}
			} else {
				$info_name = $content[1];
				$info_desc = $content[2];
			}

			if ( isset( $content[4] ) ) {
				$this->settings[$name] = array( $type, $info_name, $info_desc, $value, $content[4] );
			} else {
				$this->settings[$name] = array( $type, $info_name, $info_desc, $value );
			}
		}
	}

	function remap_add_prefix( $name, $value )
	{
		$this->prefix = $value;
		return 'DEL';
	}

	function remap_area_change( $name, $value )
	{
		$this->area = $value;
		$this->prefix = '';
		return 'DEL';
	}

	function remap_subarea_change( $name, $value )
	{
		$this->subarea = $value;
		$this->prefix = '';
		return 'DEL';
	}

	function remap_list_yesno( $name, $value )
	{
		$arr = array(
			JHTML::_('select.option', 0, JText::_( 'no' ) ),
			JHTML::_('select.option', 1, JText::_( 'yes' ) ),
		);

		$this->lists[$name] = JHTML::_('select.genericlist', $arr, $name, '', 'value', 'text', (int) $value );
		return 'list';
	}

	function remap_list_currency( $name, $value )
	{
		$currency_code_list = AECToolbox::aecCurrencyField( true, true, true );

		$this->lists[$name] = JHTML::_( 'select.genericlist', $currency_code_list, $name, 'size="10"', 'value', 'text', $value );

		return 'list';
	}

	function remap_list_country( $name, $value )
	{
		$country_code_list = AECToolbox::getCountryCodeList();

		$code_list = array();
		foreach ( $country_code_list as $country ) {
			$code_list[] = JHTML::_('select.option', $country, JText::_( 'COUNTRYCODE_' . $country ) );
		}

		$this->lists[$name] = JHTML::_( 'select.genericlist', $code_list, $name.'[]', 'size="10" multiple="multiple"', 'value', 'text', $value );

		return 'list';
	}

	function remap_list_country_full( $name, $value )
	{
		$country_code_list = AECToolbox::getISO3166_1a2_codes();

		$code_list = array();
		foreach ( $country_code_list as $country ) {
			$code_list[] = JHTML::_('select.option', $country, $country . " - " . JText::_( 'COUNTRYCODE_' . $country ) );
		}

		$this->lists[$name] = JHTML::_( 'select.genericlist', $code_list, $name.'[]', 'size="10" multiple="multiple"', 'value', 'text', $value );

		return 'list';
	}

	function remap_list_yesnoinherit( $name, $value )
	{
		$arr = array(
			JHTML::_('select.option', '0', JText::_('AEC_CMN_NO') ),
			JHTML::_('select.option', '1', JText::_('AEC_CMN_YES') ),
			JHTML::_('select.option', '1', JText::_('AEC_CMN_INHERIT') ),
		);

		$this->lists[$name] = JHTML::_( 'select.genericlist', $arr, $name, '', 'value', 'text', $value );
		return 'list';
	}

	function remap_list_recurring( $name, $value )
	{
		$recurring[] = JHTML::_('select.option', 0, JText::_('AEC_SELECT_RECURRING_NO') );
		$recurring[] = JHTML::_('select.option', 1, JText::_('AEC_SELECT_RECURRING_YES') );
		$recurring[] = JHTML::_('select.option', 2, JText::_('AEC_SELECT_RECURRING_BOTH') );

		$this->lists[$name] = JHTML::_( 'select.genericlist', $recurring, $name, 'size="3"', 'value', 'text', $value );

		return 'list';
	}

	function remap_list_date( $name, $value )
	{
		$this->lists[$name] = '<input id="datepicker-' . $name . '" name="' . $name . '" class="jqui-datepicker" type="text" value="' . $value . '">';

		return 'list';
	}
}

?>

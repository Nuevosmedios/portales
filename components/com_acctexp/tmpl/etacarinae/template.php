<?php
/**
 * @version $Id: etacarinae/config.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Template Config - Eta Carinae
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class template_etacarinae extends aecTemplate
{
	function info()
	{
		$info = array();
		$info['name']			= 'etacarinae';
		$info['longname']		= "Eta Carinae";
		$info['description']	= "The standard AEC 1.0 template";

		return $info;
	}

	function addDefaultCSS()
	{
		$this->addCSS( JURI::root(true) . '/media/' . $this->option . '/css/template.' . $this->template . '.css' );
	}

	function settings()
	{
		$tab_data = array();

		$params = array();

		$v = new JVersion();

		$params[] = array( 'userinfobox', 49.8 );
		$params = array_merge( $params, $this->stdSettings() );
		$params[] = array( 'userinfobox_sub', JText::_('Javascript Loading') );
		$params['jquery']							= array( 'toggle', !$v->isCompatible('3.0') );
		$params['bootstrap']						= array( 'toggle', !$v->isCompatible('3.0') );
		$params[] = array( 'div_end', 0 );
		$params[] = array( 'userinfobox_sub', JText::_('CFG_GENERAL_SUB_REGFLOW') );
		$params['displayccinfo']					= array( 'toggle', 0 );
		$params[] = array( 'div_end', 0 );
		$params[] = array( 'userinfobox_sub', JText::_('CFG_CUSTOMIZATION_SUB_BUTTONS_SUB') );
		$params['renew_button_never']				= array( 'toggle', '' );
		$params['renew_button_nolifetimerecurring']	= array( 'toggle', '' );
		$params['continue_button']					= array( 'toggle', '' );
		$params[] = array( 'div_end', 0 );
		$params[] = array( 'userinfobox_sub', 'Shopping Cart' );
		$params['customlink_continueshopping']		= array( 'inputC', '' );
		$params[] = array( 'div_end', 0 );
		$params[] = array( 'userinfobox_sub', 'Invoice Printout' );
		$params['invoice_address_allow_edit']		= array( 'toggle', 1 );
		$params[] = array( 'div_end', 0 );
		$params[] = array( '2div_end', 0 );

		$params[] = array( 'userinfobox', 49.8 );
		$params[] = array( 'userinfobox_sub', JText::_('CFG_GENERAL_SUB_CONFIRMATION') );
		$params['confirmation_changeusername']		= array( 'toggle', '' );
		$params['confirmation_changeusage']			= array( 'toggle', '' );
		$params['confirmation_display_descriptions']	= array( 'toggle', '' );
		$params['tos']								= array( 'inputC', '' );
		$params['tos_iframe']						= array( 'toggle', '' );
		$params[] = array( 'div_end', 0 );
		$params[] = array( 'userinfobox_sub', JText::_('CFG_GENERAL_SUB_CHECKOUT') );
		$params['enable_coupons']					= array( 'toggle', 0 );
		$params['checkout_display_descriptions']	= array( 'toggle', '' );
		$params[] = array( 'div_end', 0 );
		$params[] = array( 'userinfobox_sub', JText::_('CFG_GENERAL_SUB_SUBSCRIPTIONDETAILS') );
		$params['subscriptiondetails_menu']			= array( 'toggle', 1 );
		$params[] = array( 'div_end', 0 );
		$params[] = array( '2div_end', 0 );

		$params[] = array( '2div_end', 0 );

		@end( $params );
		$tab_data[] = array( JText::_('CFG_TAB_CUSTOMIZATION_TITLE'), key( $params ), '<h2>' . JText::_('CFG_TAB_CUSTOMIZATION_SUBTITLE') . '</h2>' );

		return array( 'params' => $params, 'tab_data' => $tab_data );
	}

	function beforesave()
	{
		$change = true;

		if ( !empty( $_POST['bootstrap'] ) ) {
			$change = $this->cfg['bootstrap'] != $_POST['bootstrap'];
		}

		if ( $change ) {
			include_once( JPATH_SITE . '/components/com_acctexp/lib/lessphp/lessc.inc.php' );

			$less = new lessc();
			$less->setImportDir( array(JPATH_SITE . '/media/com_acctexp/less/') );
			//$less->setFormatter("compressed");
			$less->setPreserveComments(true);

			if ( !isset( $_POST['bootstrap'] ) ) {
				$v = new JVersion();
			
				$this->cfg['bootstrap'] = !$v->isCompatible('3.0');
			} else {
				$this->cfg['bootstrap'] = $_POST['bootstrap'];
			}
		

			if ( $this->cfg['bootstrap'] ) {
				$less->compileFile( JPATH_SITE . "/media/com_acctexp/less/template.etacarinae.less", JPATH_SITE . '/media/com_acctexp/css/template.etacarinae.css' );
			} else {
				$less->compileFile( JPATH_SITE . "/media/com_acctexp/less/template.etacarinae-j3.less", JPATH_SITE . '/media/com_acctexp/css/template.etacarinae.css');			
			}
		}
	}
	function defaultHeader()
	{
		if ( !empty( $this->validation ) ) {
			$this->addValidation();
		}

		if ( !empty( $this->jQueryCode ) ) {
			$this->loadJS();
		}

		$this->addDefaultCSS();
	}

	function loadJS()
	{
		if ( !isset( $this->cfg['jquery'] ) ) {
			$v = new JVersion();
			
			$this->cfg['jquery'] = !$v->isCompatible('3.0');
		}

		$this->loadJQuery();

		$js = "jQuery(document).ready(function(){\n\n" . implode( "\n", $this->jQueryCode ) . "\n\n});";

		$this->addScriptDeclaration( $js );
	}

	function loadJQuery()
	{
		$v = new JVersion();

		if ( $this->cfg['jquery'] ) {
			$this->addScript( JURI::root(true).'/media/com_acctexp/js/jquery/jquery-1.7.2.min.js' );
		} else {
			$this->addScript( 'jquery.framework' );
		}

		$this->loadJQueryExtensions();

		$this->addScript( JURI::root(true).'/media/com_acctexp/js/jquery/jquerync.js' );
	}

	function loadJQueryExtensions()
	{
		if ( !empty( $this->jqueryExtensions ) ) {
			foreach ( $this->jqueryExtensions as $ext ) {
				$this->addScript( JURI::root(true).'/media/com_acctexp/js/' . $ext . '.js' );
			}
		}
	}
	
	function enqueueJQueryExtension( $name )
	{
		$this->jqueryExtensions[] = $name;
	}

	function enqueueJQueryCode( $js )
	{
		$this->jQueryCode[] = $js;
	}

	function enqueueValidation( $validation )
	{
		if ( !empty( $this->validation ) ) {
			$this->validation = array_merge( $this->validation, $validation );
		} else {
			$this->validation = $validation;
		}
	}

	function addValidation()
	{
		$this->enqueueJQueryExtension( 'jquery/jquery.validate' );
		$this->enqueueJQueryExtension( 'jquery/jquery.validate.additional-methods' );

		$msgs = array(	'required' => 0, 'remote' => 0, 'email' => 0, 'url' => 0, 'date' => 0,
						'dateISO' => 0, 'number' => 0, 'digits' => 0, 'creditcard' => 0, 'equalTo' => 0,
						'maxlength' => 1, 'minlength' => 1, 'rangelength' => 2, 'range' => 2, 'max' => 1,
						'min' => 1, 'maxwords' => 1, 'minwords' => 1, 'rangewords' => 2, 'letterswithbasicpunc' => 0,
						'alphanumeric' => 0, 'alphanumericwithbasicpunc' => 0, 'lettersonly' => 0, 'nowhitespace' => 0, 'ziprange' => 0,
						'zipcodeus' => 0, 'integer' => 0, 'vinus' => 0, 'dateita' => 0, 'datenl' => 0,
						'time' => 0, 'time12h' => 0, 'phoneus' => 0, 'phoneuk' => 0, 'mobileuk' => 0,
						'phonesuk' => 0, 'postcodeuk' => 0, 'strippedminlength' => 1, 'email2' => 0, 'url2' => 0,
						'creditcardtypes' => 0, 'ipv4' => 0, 'ipv6' => 0, 'pattern' => 0, 'require_from_group' => 1,
						'skip_or_fill_minimum' => 1, 'accept' => 0, 'extension' => 0 );

		$messages = array();
		foreach( $msgs as $k => $i ) {
			if ( $i ) {
				$messages[] = $k . ': ' . 'jQuery.validator.format("' . JText::_( strtoupper( 'aec_validate_' . $k ) ) . '")';
			} else {
				$messages[] = $k . ': ' . '"' . JText::_( strtoupper( 'aec_validate_' . $k ) ) . '"';
			}
		}

		$js = "
		jQuery(document).ready(function(){

			jQuery.extend(jQuery.validator.messages, {" . implode( ",\n", $messages ) . "} );

			jQuery('#aec form:last').validate(
			{
			rules: " . json_encode( $this->validation['rules'] ) . ",
			highlight: function(label) {
				jQuery(label).closest('.well').addClass('well-highlight');
				jQuery(label).closest('.control-group').addClass('error');
				jQuery(label).closest('.label-important').prepend('" . addslashes( aecHTML::Icon( 'ban-circle', true ) ) . "');
				jQuery('#aec form button#confirmation').attr('disabled','disabled');
			},
			unhighlight: function(label) {
				jQuery(label).closest('.well').removeClass('well-highlight');
				jQuery(label).closest('.control-group').removeClass('error');
				if ( jQuery(\"#aec form .label-important\").length > 0) {
					jQuery('#aec form button').attr('disabled','disabled');
				} else {
					jQuery('#aec form button').attr(\"disabled\", false);
				}
			},
			success: function(label) {
				label.remove();
		
				jQuery('#aec form button').attr(\"disabled\", false);
			},
			errorClass: 'label label-important',
			submitHandler: function(form) {
				if ( jQuery('#aec form').valid() ) {
					form.submit();
				} else {
					jQuery('#aec form button').attr('disabled','disabled');
				}
			}
			});
		});
		";

		$this->enqueueJQueryCode( $js );
	}
}
?>

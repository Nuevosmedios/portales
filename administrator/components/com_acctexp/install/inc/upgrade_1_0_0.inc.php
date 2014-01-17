<?php
/**
 * @version $Id: upgrade_1_0_0.inc.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Install Includes
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

if ( isset( $aecConfig->cfg['customtext_plans'] ) ) {
	$oldsettings = array(	'customtext_plans', 'custom_confirm_userdetails', 'customtext_confirm_keeporiginal', 'customtext_confirm',
							'customtext_checkout_keeporiginal', 'customtext_checkout', 'customtext_exception_keeporiginal', 'customtext_exception',
							'customtext_notallowed_keeporiginal', 'customtext_notallowed', 'customtext_pending_keeporiginal', 'customtext_pending',
							'customtext_hold_keeporiginal', 'customtext_hold', 'customtext_expired_keeporiginal', 'customtext_expired',
							'customtext_thanks_keeporiginal', 'customtext_thanks', 'customtext_cancel_keeporiginal', 'customtext_cancel',
							'confirmation_changeusername', 'confirmation_changeusage', 'invoice_before_header', 'invoice_page_title',
							'invoice_header', 'invoice_after_header', 'invoice_address_allow_edit', 'invoice_address',
							'invoice_before_content', 'invoice_after_content', 'invoice_before_footer', 'invoice_footer',
							'invoice_after_footer',
							'customthanks', 'customcancel', 'customnotallowed', ' confirmation_display_descriptions',
							'tos', 'tos_iframe', 'customlink_continueshopping', ' renew_button_never',
							'renew_button_nolifetimerecurring', 'continue_button', 'use_recaptcha', 'recaptcha_privatekey',
							'recaptcha_publickey' );

	$copysettings = array();
	foreach ( $oldsettings as $k ) {
		if ( isset( $aecConfig->cfg[$k] ) ) {
			$copysettings[$k] = $aecConfig->cfg[$k];

			unset( $aecConfig->cfg[$k] );
		} else {
			$copysettings[$k] = "";
		}
	}

	$aecConfig->saveSettings();

	$template = new configTemplate();
	$template->name = 'helix';
	$template->default = 1;
	$template->settings = $copysettings;

	$template->storeload();
}

$eucaInstalldb->dropColifExists( 'ordering', 'coupons_static' );
$eucaInstalldb->dropColifExists( 'ordering', 'coupons' );

$eucaInstalldb->addColifNotExists( 'restrictions', "text NULL", 'microintegrations' );

?>

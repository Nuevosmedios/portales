<?php
/**
 * @version $Id: couponinfo.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<?php if ( $InvoiceFactory->coupons['active'] ) {
	if ( !empty( $tmpl->cfg['confirmation_coupons'] ) ) {
		?><p><?php echo JText::_('CONFIRM_COUPON_INFO_BOTH') ?></p><?php
	} else {
		?><p><?php echo JText::_('CONFIRM_COUPON_INFO') ?></p><?php
	}

	if ( !empty( $tmpl->cfg['confirmation_coupons'] ) ) { ?>
		<strong><?php echo JText::_('CHECKOUT_COUPON_CODE') ?></strong>
		<input type="text" size="20" name="coupon_code" class="inputbox" value="" />
	<?php }
} ?>

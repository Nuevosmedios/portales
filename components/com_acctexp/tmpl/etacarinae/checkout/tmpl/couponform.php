<?php
/**
 * @version $Id: couponform.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div id="coupons-box">
	<div class="coupons-info">
		<h5><?php echo JText::_('CHECKOUT_COUPON_CODE') ?></h5>
		<?php if ( !empty( $InvoiceFactory->errors ) ) {
			foreach ( $InvoiceFactory->errors as $err ) { ?>
				<div class="alert alert-error">
					<p><strong><?php echo JText::_('COUPON_ERROR_PRETEXT') ?></strong>&nbsp;<?php echo $err ?></p>
				</div>
			<?php }
		} ?>
	</div>
	<div class="well coupons-info">
		<p><?php echo JText::_('CHECKOUT_COUPON_INFO') ?></p>
		<form id="form-coupons" action="<?php echo $tmpl->url( array( 'task' => 'InvoiceAddCoupon') ) ?>" method="post">
			<input type="text" size="20" name="coupon_code" class="inputbox" value="" />
			<input type="hidden" name="option" value="<?php echo $option ?>" />
			<input type="hidden" name="task" value="InvoiceAddCoupon" />
			<input type="hidden" name="invoice" value="<?php echo $InvoiceFactory->invoice->invoice_number ?>" />
			<input type="submit" class="button btn" value="<?php echo JText::_('BUTTON_APPLY') ?>" />
			<?php echo JHTML::_( 'form.token' ) ?>
		</form>
	</div>
</div>

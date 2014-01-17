<?php
/**
 * @version $Id: checkout.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div id="aec">
	<div id="aec-checkout">
		<div class="componentheading"><?php echo $InvoiceFactory->checkout['checkout_title'] ?></div>
		<?php

		@include( $tmpl->tmpl( 'itemlist' ) );

		if ( !empty( $InvoiceFactory->checkout['enable_coupons'] ) ) {
			@include( $tmpl->tmpl( 'couponform' ) );
		}

		if ( $makegift ) { @include( $tmpl->tmpl( 'giftform' ) ); }
		if ( !empty( $params ) ) { @include( $tmpl->tmpl( 'paramsform' ) ); }

		if ( !empty( $var ) ) { ?>
			<div id="checkout-box">
				<?php if ( ( strpos( $var, '<tr class="aec_formrow">' ) !== false ) || is_string( $InvoiceFactory->display_error ) ) { ?>
					<h4><?php echo $InvoiceFactory->checkout['customtext_checkout_table'] ?></h4>
				<?php } ?>
				<?php if ( is_string( $InvoiceFactory->display_error ) ) { ?>
					<div class="alert alert-error">
						<p><?php echo JText::_('CHECKOUT_ERROR_EXPLANATION') . ":" ?></p>
						<p><strong><?php echo $InvoiceFactory->display_error ?></strong></p>
						<p><?php echo JText::_('CHECKOUT_ERROR_FURTHEREXPLANATION') ?></p>
					</div>
				<?php } ?>
				<?php if ( !empty( $InvoiceFactory->checkout['processor_addin'] ) ) { ?>
					<div class="alert alert-info">
						<?php echo $InvoiceFactory->checkout['processor_addin'] ?>
					</div>
				<?php } ?>
				<?php if ( is_string( $var ) ) { ?>
					<div class="well">
						<div id="checkout-button">
							<p><?php echo $InvoiceFactory->pp->processor->checkoutText(); ?></p>
							<?php print $var ?>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php } ?>
		<?php @include( $tmpl->tmpl( 'confirmation.processorinfo' ) ) ?>
	</div>
</div>

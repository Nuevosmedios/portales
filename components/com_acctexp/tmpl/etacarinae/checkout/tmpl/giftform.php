<?php
/**
 * @version $Id: giftform.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div id="gift-box">
	<h5><?php echo JText::_('CHECKOUT_GIFT_HEAD') ?></h5>
	<div class="well gift-details">
		<?php if ( !empty( $InvoiceFactory->invoice->params['target_username'] ) ) { ?>
			<p>This purchase will be gifted to: <?php echo $InvoiceFactory->invoice->params['target_username'] ?> (<?php echo $tmpl->lnk( array('task' => 'InvoiceRemoveGift','invoice' => $InvoiceFactory->invoice->invoice_number), "undo?" ) ?>)</p>
		<?php } else { ?>
		<p><?php echo JText::_('CHECKOUT_GIFT_INFO') ?></p>
		<form id="form-gift" action="<?php echo $tmpl->url( array( 'task' => 'InvoiceMakeGift') ) ?>" method="post">
			<input type="text" size="20" name="user_ident" class="inputbox" value="" />
			<input type="hidden" name="option" value="<?php echo $option ?>" />
			<input type="hidden" name="task" value="InvoiceMakeGift" />
			<input type="hidden" name="invoice" value="<?php echo $InvoiceFactory->invoice->invoice_number ?>" />
			<input type="submit" class="button btn" value="<?php echo JText::_('BUTTON_APPLY') ?>" />
			<?php echo JHTML::_( 'form.token' ) ?>
		</form>
		<?php } ?>
	</div>
</div>

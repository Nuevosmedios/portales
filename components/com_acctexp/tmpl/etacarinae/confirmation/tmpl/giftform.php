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
		<?php if ( !empty( $InvoiceFactory->invoice->params['target_user'] ) ) { ?>
			<p>This purchase will be gifted to: <?php echo $InvoiceFactory->invoice->params['target_username'] ?> (<?php echo $tmpl->lnk( array('task' => 'InvoiceRemoveGift','invoice' => $InvoiceFactory->invoice->invoice_number), "undo?" ) ?>)</p>
			<input type="hidden" name="user_ident" value="<?php echo $InvoiceFactory->invoice->params['target_username'] ?>" />
		<?php } else { ?>
			<p><?php echo JText::_('CHECKOUT_GIFT_INFO') ?></p>
			<input type="text" size="20" name="user_ident" class="inputbox" value="" />
		<?php } ?>
	</div>
</div>
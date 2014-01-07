<?php
/**
 * @version $Id: form.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div id="cart-info">
	<?php if ( empty( $InvoiceFactory->cart ) ) { ?>
		<p>Your Shopping Cart is empty!</p>
	<?php } else { ?>
		<?php @include( $tmpl->tmpl( 'list' ) ); ?>
	<?php } ?>

	<?php if ( empty( $InvoiceFactory->userid ) ) { ?>
		<p>Save Registration to Continue Shopping:</p>
	<?php } else {
		if ( !empty( $tmpl->cfg['customlink_continueshopping'] ) ) {
			$continueurl = $tmpl->cfg['customlink_continueshopping'];
		} else {
			$continueurl = $tmpl->url( array( 'task' => 'subscribe') );
		}
	?>
	<div id="continue-button">
		<form id="form-continue" action="<?php echo $continueurl ?>" method="post">
			<button type="submit" class="btn"><?php echo aecHTML::Icon( 'arrow-left' ) . JText::_('AEC_BTN_CONTINUE_SHOPPING') ?></button>
		</form>
	</div>
	<?php } ?>
</div>

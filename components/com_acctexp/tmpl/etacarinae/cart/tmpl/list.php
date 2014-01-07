<?php
/**
 * @version $Id: list.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<form id="form-update" action="<?php echo $tmpl->url( array( 'task' => 'updateCart') ) ?>" method="post">
	<table class="cart-list table table-striped">
		<thead>
			<tr>
				<th>Item</th>
				<th width="1%">Cost</th>
				<th width="1%">Amount</th>
				<th width="1%">Total</th>
				<th width="1%">&nbsp;</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $InvoiceFactory->cart as $bid => $bitem ) {
				if ( !empty( $bitem['name'] ) ) {
					?><tr>
						<td><?php echo $bitem['name'] ?></td>
						<td><?php echo $bitem['cost'] ?></td>
						<?php if ( !empty( $bitem['obj']->params['addtocart_max'] ) && ( $bitem['obj']->params['addtocart_max'] < 2 ) ) { ?>
							<td class="center-cell">1</td>
						<?php } else { ?>
							<td><input type="inputbox" type="text" class="span1" name="cartitem_<?php echo $bid ?>" value="<?php echo $bitem['quantity'] ?>" /></td>
						<?php } ?>
						<td><?php echo $bitem['cost_total'] ?></td>
						<td class="center-cell"><?php echo $tmpl->lnk( array('task' => 'clearCartItem','item' => $bid), '&times;', 'btn btn-mini btn-danger' ) ?></td>
					</tr>
				<?php } else { ?>
					<tr>
						<td></td>
						<td></td>
						<td class="center-cell"><button type="submit" class="btn btn-info pull-right"><?php echo aecHTML::Icon( 'refresh', true, ' narrow' ); ?></button></td>
						<td></td>
						<td class="center-cell"><div id="clear-button"><?php echo $tmpl->lnk( array('task' => 'clearCart'), aecHTML::Icon( 'trash', true, ' narrow' ), 'btn btn-danger' ) ?></div></td>
					</tr>
					<tr>
						<td><strong><?php echo JText::_('CART_ROW_TOTAL') ?></strong></td>
						<td></td>
						<td></td>
						<td><strong><?php echo $bitem['cost'] ?></strong></td>
						<td></td>
					</tr>
				<?php }
			} ?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo $option ?>" />
	<input type="hidden" name="userid" value="<?php echo $user->id ? $user->id : 0 ?>" />
	<input type="hidden" name="task" value="updateCart" />
	<?php echo JHTML::_( 'form.token' ) ?>
</form>

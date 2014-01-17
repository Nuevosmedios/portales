<?php
/**
 * @version $Id: itemlist.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div class="checkout-list-item-terms">
	<?php foreach ( $item['terms'] as $term ) { ?>
		<div class="checkout-list-term list-term-<?php echo $term['type'] ?><?php echo $term['current'] ? 'list-term-current':'list-term-other' ?>">
			<?php if ( !empty( $term['title'] ) ) { ?>
				<h4><?php echo $term['title'] ?></h4>
			<?php } ?>
			<?php if ( empty( $item['params']['hide_duration_checkout'] ) ) { ?>
				<?php if ( !empty( $term['duration'] ) ) { ?>
					<p><?php echo JText::_('AEC_CHECKOUT_DURATION') . ': ' . $term['duration'] ?></p>
				<?php } ?>
			<?php } ?>
			<table class="checkout-term-cost table table-striped">
				<tbody>
					<?php foreach ( $term['cost'] as $cost ) { ?>
						<tr class="checkout-cost-<?php echo $cost['type'] ?>">
							<td class="cost-details"><?php echo $cost['details'] ?></td>
							<td class="cost-cost"><?php echo $cost['cost'] ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php } ?>
</div>

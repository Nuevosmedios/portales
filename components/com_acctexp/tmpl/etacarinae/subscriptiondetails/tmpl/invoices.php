<?php
/**
 * @version $Id: invoices.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div id="aec-subscriptiondetails-invoices">
	<table class="table table-striped">
		<thead>
			<tr>
				<th><?php echo JText::_('HISTORY_COL1_TITLE');?></th>
				<th><?php echo JText::_('HISTORY_COL2_TITLE');?></th>
				<th><?php echo JText::_('HISTORY_COL3_TITLE');?></th>
				<th><?php echo JText::_('HISTORY_COL4_TITLE');?></th>
				<th><?php echo JText::_('HISTORY_COL5_TITLE');?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach ( $invoices as $invoice ) { ?>
				<tr class="<?php echo $invoice['rowstyle'] ?>">
					<td><?php echo $invoice['invoice_number']; ?></td>
					<td><?php echo $invoice['amount'] . '&nbsp;' . $invoice['currency_code']; ?></td>
					<td><?php echo $invoice['transactiondate']; ?></td>
					<td><?php echo $invoice['processor']; ?></td>
					<td><?php echo $invoice['actions']; ?></td>
				</tr>
				<?php
			} ?>
		</tbody>
	</table>
	<?php if ( $properties['invoice_pages'] > 1 ) { ?>
		<div class="pagination pagination-centered">
			<ul>
				<?php for ( $i=0; $i<$properties['invoice_pages']; $i++ ) { ?>
					<li<?php echo ($i == $properties['invoice_page']) ? ' class="active"':'' ?>>
						<?php echo $tmpl->lnk( array('task' => 'subscriptiondetails', 'sub' => 'invoices', 'page' => $i), ( $i + 1 ), '', true ) ?>
					</li>
				<?php } ?>
			</ul>
		</div>
	<?php } ?>
</div>

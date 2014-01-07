<?php
/**
 * @version $Id: invoice.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ); ?>

<div id="invoice_wrap">
	<div id="before_invoice_header"><?php echo $data['before_header'] ?></div>
	<div id="invoice_header">
		<?php echo $data['header'] ?>
	</div>
	<div id="after_invoice_header"><?php echo $data['after_header'] ?></div>
	<div id="address"><pre><?php echo $data['address'] ?></pre></div>
	<div id="invoice_details">
		<table id="invoice_details">
			<tr><th><?php echo JText::_('INVOICEPRINT_DATE') ?></th></tr>
			<tr><td><?php echo $data['invoice_date'] ?></td></tr>
			<tr><th><?php echo JText::_('INVOICEPRINT_ID') ?></th></tr>
			<tr><td><?php echo $data['invoice_id'] ?></td></tr>
			<tr><th><?php echo JText::_('INVOICEPRINT_REFERENCE_NUMBER') ?></th></tr>
			<tr><td><?php echo $data['invoice_number'] ?></td></tr>
		</table>
	</div>
	<div id="text_before_content"><?php echo $data['before_content'] ?></div>
	<div id="invoice_content">
		<table id="invoice_content">
			<tr>
				<th><?php echo JText::_('INVOICEPRINT_ITEM_NAME') ?></th>
				<th><?php echo JText::_('INVOICEPRINT_UNIT_PRICE') ?></th>
				<th><?php echo JText::_('INVOICEPRINT_QUANTITY') ?></th>
				<th><?php echo JText::_('INVOICEPRINT_TOTAL') ?></th>
			</tr>
			<?php echo implode( "\r\n", $data['itemlist'] ) ?>
			<?php echo implode( "\r\n", $data['totallist'] ) ?>
		</table>
	</div>
	<div id="text_after_content"><?php echo $data['after_content'] ?></div>
	<?php if ( !empty( $data['recurringstatus'] ) && !empty( $data['invoice_billing_history'] ) ) { ?>
		<div id="invoice_paidstatus">
			<p><?php echo $data['paidstatus'] ?></p>
			<div id="invoice_billing_history">
				<table id="invoice_billing_history">
					<tr>
						<th><?php echo JText::_('HISTORY_COL3_TITLE') ?></th>
						<th><?php echo JText::_('HISTORY_COL2_TITLE') ?></th>
						<th><?php echo JText::_('HISTORY_COL4_TITLE') ?></th>
					</tr>
					<?php echo $data['invoice_billing_history'] ?>
				</table>
			</div>
		</div>
		<div id="invoice_recurringstatus"><?php echo $data['recurringstatus'] ?></div>
	<?php } else { ?>
		<div id="invoice_paidstatus"><p><?php echo $data['paidstatus'] ?></p></div>
	<?php } ?>
	<div id="before_footer"><?php echo $data['before_footer'] ?></div>
	<div id="footer"><?php echo $data['footer'] ?></div>
	<div id="after_footer"><?php echo $data['after_footer'] ?></div>
</div>

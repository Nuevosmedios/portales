<?php
/**
 * @version $Id: info.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div id="confirmation-info">
	<?php if ( !empty( $InvoiceFactory->userdetails ) ) { ?>
		<div class="alert alert-info">
			<div class="confirmation-info-header">
				<h4 class="alert-heading"><?php echo JText::_('CONFIRM_COL1_TITLE') ?></h4>
			</div>
			<div class="confirmation-info-content">
					<?php echo $InvoiceFactory->userdetails ?>
				<?php if ( empty( $userid ) && $tmpl->cfg['confirmation_changeusername'] ) { ?>
					<?php @include( $tmpl->tmpl( 'backdetailsbtn' ) ) ?>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
	<div class="alert alert-info">
		<div class="confirmation-info-header">
			<h4 class="alert-heading"><?php echo JText::_('CONFIRM_YOU_HAVE_SELECTED') ?></h4>
		</div>
		<div class="confirmation-info-content">
			<p><strong><?php echo $InvoiceFactory->plan->name ?></strong></p>
			<?php if ( !empty( $InvoiceFactory->plan->desc ) && $tmpl->cfg['confirmation_display_descriptions'] ) { ?>
				<p><?php echo stripslashes( $InvoiceFactory->plan->desc ) ?></p>
			<?php } ?>
			<?php if ( $tmpl->cfg['confirmation_changeusage'] ) { ?>
				<?php @include( $tmpl->tmpl( 'backusagebtn' ) ) ?>
			<?php } ?>
		</div>
	</div>
	<div class="alert alert-info">
		<div class="confirmation-info-header">
			<h4 class="alert-heading"><?php echo JText::_('CONFIRM_COL3_TITLE') ?></h4>
		</div>
		<div class="confirmation-info-content">
			<div class="confirmation-info-price">
				<p><?php echo $InvoiceFactory->payment->amount_format ?></p>
			</div>
		</div>
	</div>
</div>

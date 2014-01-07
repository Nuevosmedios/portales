<?php
/**
 * @version $Id: pending.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ); ?>
<div id="aec">
	<div id="aec-pending">
		<div class="componentheading"><?php echo JText::_('PENDING_TITLE') ?></div>
		<p class="expired_dear"><?php echo sprintf( JText::_('DEAR'), $name ) . ',' ?></p>
		<p class="expired_date"><?php echo JText::_('WARN_PENDING') ?></p>
		<div id="box-pending">
			<?php if ( strcmp($invoice, "none") === 0 ) { ?>
				<p><?php echo JText::_('PENDING_NOINVOICE') ?></p>
				<?php @include( $tmpl->tmpl( 'upgradebtn' ) ) ?>
			<?php } elseif ( $invoice ) { ?>
				<p><?php echo JText::_('PENDING_OPENINVOICE');
						@include( $tmpl->tmpl( 'invoice_links' ) );
						echo ( !empty($reason) ? ' '.$reason : '' );
				?></p>
			<?php } ?>
		</div>
	</div>
</div>
<?php
/**
 * @version $Id: expired.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div id="aec">
	<div id="aec-expired">
		<div class="componentheading"><?php echo JText::_('EXPIRED_TITLE') ?></div>
		<h4><?php echo sprintf( JText::_('DEAR'), $metaUser->cmsUser->name ) ?></h4>
		<p><?php echo JText::_( $is_trial ? 'EXPIRED_TRIAL' : 'EXPIRED' ) . $expiration ?></p>
		
			<?php if ( $invoice ) { ?>
				<div class="alert alert-danger">
				<p><?php echo JText::_('PENDING_OPENINVOICE') ?></p>
				<?php echo $tmpl->btn( array(	'task' => 'repeatPayment',
												'invoice' => $invoice,
												'userid' => $metaUser->id
												), JText::_('GOTO_CHECKOUT_BTN'),
												'btn btn-danger' ) ?>
				</div>
			<?php } ?>
			<?php if ( $continue ) { ?>
				<div class="alert alert-info">
					<div id="continue-button">
						<p><?php echo JText::_('CONTINUE_BTN_INFO') ?></p>
						<?php echo $tmpl->btn( array(	'task' => 'renewSubscription',
												'userid' => $metaUser->userid,
												'usage' => $metaUser->focusSubscription->plan,
												'intro' => $intro
												), JText::_('RENEW_BUTTON_CONTINUE'),
												'btn btn-info' ) ?>
					</div>
				</div>
			<?php } ?>
			<div class="well">
				<div id="renew-button">
						<p><?php echo JText::_('RENEW_BTN_INFO') ?></p>
						<?php echo $tmpl->btn( array(	'task' => 'renewSubscription',
												'userid' => $metaUser->userid,
												'intro' => $intro
												), JText::_('RENEW_BUTTON'),
												'btn btn-success' ) ?>
				</div>
			</div>
	</div>
</div>

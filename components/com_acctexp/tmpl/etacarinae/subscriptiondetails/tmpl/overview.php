<?php
/**
 * @version $Id: overview.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

if ( !empty( $metaUser->objSubscription->signup_date ) ) {
	echo '<p>' . JText::_('MEMBER_SINCE') . '&nbsp;' . $tmpl->date( $metaUser->objSubscription->signup_date ) .'</p>';
}

if ( !empty( $properties['showcheckout'] ) ) { ?>
	<div class="details-openinvoice">
	<p>
		<?php echo JText::_('PENDING_OPENINVOICE'); ?>&nbsp;
		<a href="<?php echo AECToolbox::deadsureURL( 'index.php?option=' . $option . '&task=repeatPayment&invoice=' . $properties['showcheckout'] . '&userid=' . $metaUser->userid.'&'. xJ::token() .'=1' ); ?>" title="<?php echo JText::_('GOTO_CHECKOUT'); ?>"><?php echo JText::_('GOTO_CHECKOUT'); ?></a>
	</p>
	</div>
<?php }

if ( $metaUser->hasSubscription ) {
	if ( $properties['alert']['level'] > 2 ) {
		$al = ' alert-success';
	} elseif ( $properties['alert']['level'] == 2 ) {
		$al = '';
	} elseif ( $properties['alert']['level'] < 2 ) {
		$al = ' alert-danger';
	}
	if ( !empty( $subscriptions ) ) {
		@include( $tmpl->tmpl( 'subscriptions' ) );
	} ?>
	<div class="alert<?php echo $al; ?>">
		<div id="box-expiration">
			<div id="box-expiration-desc">
				<?php if ( !empty( $metaUser->objSubscription->lifetime ) ) { ?>
					<p><strong><?php echo JText::_('RENEW_LIFETIME'); ?></strong></p>
				<?php } else { ?>
					<p><?php echo $tmpl->date( $metaUser->focusSubscription->expiration, true, true, $trial ); ?></p>
				<?php } ?>
			</div>
			<div id="days-left">
				<p><strong><?php echo $daysleft; ?></strong>&nbsp;&nbsp;<?php echo $daysleft_append; ?></p>
			</div>
		</div>
	</div>
	<?php if ( !empty( $properties['upgrade_button'] ) ) {
		@include( $tmpl->tmpl( 'btn' ) );
	} ?>
<?php
}

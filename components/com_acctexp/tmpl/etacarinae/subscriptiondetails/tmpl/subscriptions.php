<?php
/**
 * @version $Id: subscriptiondetails.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div class="alert alert-info">
	<?php foreach ( $subscriptions as $sid => $subscription ) {
		switch ( $sid ) {
			case 0:
				echo '<h4>' . JText::_('YOUR_SUBSCRIPTION') . '</h4>';
				break;
			case 1:
				echo '<h4>' . JText::_('YOUR_FURTHER_SUBSCRIPTIONS') . '</h4>';
				break;
		} ?>
		<div class="subscription-info">
			<p><strong><?php echo $subscription->objPlan->getProperty( 'name' ) ?></strong></p>
			<p><?php echo $subscription->objPlan->getProperty( 'desc' ) ?></p>
			<?php if ( !empty( $subscription->objPlan->proc_actions ) ) { ?>
				<p><?php echo JText::_('PLAN_PROCESSOR_ACTIONS') . ' ' . implode( " | ", $subscription->objPlan->proc_actions ) ?></p>
			<?php } ?>
			<?php if ( !empty( $subscription->lifetime ) ) { ?>
				<p><?php echo JText::_('AEC_ISLIFETIME') ?></p>
			<?php } else {
				if ( $subscription->recurring && ( in_array( $subscription->status, array( 'Active', 'Trial' ) ) ) ) { ?>
					<p><?php echo JText::_('AEC_WILLRENEW') . ': ' . $tmpl->date( $subscription->expiration ) ?></p>
				<?php } else { ?>
					<p><?php echo JText::_('AEC_WILLEXPIRE') . ': ' . $tmpl->date( $subscription->expiration ) ?></p>
				<?php }
			} ?>
		</div>
	<?php } ?>
</div>

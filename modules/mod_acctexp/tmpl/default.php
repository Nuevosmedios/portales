<?php
/**
 * @version $Id: default.php
 * @package AEC - Account Control Expiration - Subscription component for Joomla! OS CMS
 * @subpackage Module Default Template
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );
?>
<div class="aec_module_inner<?php echo $class_sfx; ?>">
<?php
	echo $pretext;

	$metaUser = new metaUser( $user->id );

	$subscriptions = $metaUser->getAllCurrentSubscriptionsInfo();

	if ( !empty( $subscriptions ) ) {
		foreach ( $subscriptions as $subscription ) { ?>
			<div class="aec_module_subscription aec_module_subscriptionid_<?php echo $subscription->plan; ?>">
				<h4><?php echo $subscription->name; ?></h4>
				<?php if ( $showExpiration ) { ?>
					<p>
						<?php if ( empty( $subscription->expiration ) || $subscription->lifetime ) {
							echo JText::_('ACCOUNT_UNLIMIT');
						} elseif ( $recurring ) {
							echo JText::_('ACCOUNT_RENEWAL') . ": " . AECToolbox::formatDate( ( strtotime( $subscription->expiration ) ) );
						} else {
							echo JText::_('ACCOUNT_EXPIRES') . ": " . AECToolbox::formatDate( ( strtotime( $subscription->expiration ) ) );
						} ?>
					</p>
				<?php } ?>
			</div>
		<?php }
	} ?>

	<?php if ( $displaypipeline ) {
		$dph = new displayPipelineHandler;
		echo $dph->getUserPipelineEvents( $user->id );
	} ?>

	<?php echo $posttext; ?>
</div>

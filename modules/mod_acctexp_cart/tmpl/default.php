<?php
/**
 * @version $Id: default.php
 * @package AEC - Account Control Expiration - Subscription component for Joomla! OS CMS
 * @subpackage Cart Module Default Template
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );
?>
<div class="aec_cart_module_inner<?php echo $class_sfx; ?>">
	<?php echo $pretext; ?>

	<p><?php echo JText::_('AEC_CART_MODULE_ITEMS_IN_CART'); ?>: <?php echo $quantity; ?></p>
	<p><?php echo JText::_('AEC_CART_MODULE_ROW_TOTAL'); ?>: <?php echo $total; ?></p>

	<?php if ( $button ) {
		global $aecConfig; ?>
		<form id="form-backtocart" action="<?php echo AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=cart', $aecConfig->cfg['ssl_signup'] ) ?>" method="post">
			<div class="backtocart-button">
				<button type="submit" class="btn"><?php echo aecHTML::Icon( 'shopping-cart' ) . JText::_('AEC_BTN_YOUR_CART') ?></button>
			</div>
			<?php echo JHTML::_( 'form.token' ) ?>
		</form>
	<?php } ?>

	<?php echo $posttext; ?>
</div>

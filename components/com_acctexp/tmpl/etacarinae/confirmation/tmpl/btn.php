<?php
/**
 * @version $Id: btn.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<?php if ( !empty( $tmpl->cfg['tos_iframe'] ) && !empty( $tmpl->cfg['tos'] ) ) { ?>
	<div class="well" id="confirmation-tos">
		<iframe src="<?php echo $tmpl->cfg['tos'] ?>" width="100%" height="150px"></iframe>
		<p><input name="tos" type="checkbox" id="tos" />&nbsp;&nbsp;<?php echo JText::_('CONFIRM_TOS_IFRAME') ?></p>
	</div>
<?php } elseif ( !empty( $tmpl->cfg['tos'] ) ) { ?>
	<div class="well" id="confirmation-tos">
		<p><input name="tos" type="checkbox" id="tos" />&nbsp;&nbsp;<?php echo JText::sprintf( 'CONFIRM_TOS', $tmpl->cfg['tos'] ) ?></p>
	</div>
<?php } ?>

<div class="well" id="confirmation-button" >
	<p><?php echo JText::_('CONFIRM_INFO') ?></p>
	<input type="hidden" name="option" value="<?php echo $option ?>" />
	<input type="hidden" name="userid" value="<?php echo $userid ? $userid : 0 ?>" />
	<input type="hidden" name="task" value="saveSubscription" />
	<input type="hidden" name="usage" value="<?php echo $InvoiceFactory->usage ?>" />
	<input type="hidden" name="processor" value="<?php echo $InvoiceFactory->processor ?>" />
	<?php if ( isset( $InvoiceFactory->recurring ) ) { ?>
		<input type="hidden" name="recurring" value="<?php echo $InvoiceFactory->recurring ?>" />
	<?php } ?>
	<?php if ( $passthrough != false ) { ?>
		<input type="hidden" name="aec_passthrough" value="<?php echo $passthrough ?>" />
	<?php } ?>
	<button type="submit" class="button btn btn-success" id="confirmation"><?php echo aecHTML::Icon( 'ok', true ); ?><?php echo JText::_('BUTTON_CONFIRM') ?></button>
</div>

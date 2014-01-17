<?php
/**
 * @version $Id: paramsform.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>

<div id="params-box">
	<form id="form-params" action="<?php echo $tmpl->url( array( 'task' => 'InvoiceAddParams') ) ?>" method="post">
		<?php echo $params ?>
		<input type="hidden" name="option" value="<?php echo $option ?>" />
		<input type="hidden" name="task" value="InvoiceAddParams" />
		<input type="hidden" name="invoice" value="<?php echo $InvoiceFactory->invoice->invoice_number ?>" />
		<input type="submit" class="button btn" value="<?php echo JText::_('BUTTON_APPEND') ?>" />
		<?php echo JHTML::_( 'form.token' ) ?>
	</form>
</div>

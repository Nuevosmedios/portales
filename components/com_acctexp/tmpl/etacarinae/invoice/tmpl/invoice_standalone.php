<?php
/**
 * @version $Id: invoice_standalone.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ); ?>
<div id="aec">
<?php if ( !empty( $tmpl->cfg['invoice_address_allow_edit'] ) ) { ?>
	<div id="printbutton">
		<div id="printbutton_inner">
			<textarea align="left" cols="40" rows="5" name="address" /><?php echo $data['address'] ?></textarea>
			<button onClick="window.print()"><?php echo JText::_('INVOICEPRINT_PRINT') ?></button>
		</div>
		<p><?php echo JText::_('INVOICEPRINT_BLOCKNOTICE') ?></p>
	</div>
<?php } else { ?>
	<div id="printbutton">
		<div id="printbutton_inner">
			<textarea align="left" cols="40" rows="5" name="address" disabled="disabled" /><?php echo $data['address'] ?></textarea>
			<button onClick="window.print()" id="printbutton"><?php echo JText::_('INVOICEPRINT_PRINT') ?></button>
		</div>
	</div>
<?php } ?>
<?php @include( $tmpl->tmpl( 'invoice' ) ) ?>
</div>

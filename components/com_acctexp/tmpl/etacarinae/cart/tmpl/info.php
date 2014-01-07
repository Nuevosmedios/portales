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
<form id="form-confirm" action="<?php echo AECToolbox::deadsureURL( 'index.php?option=' . $option . '&task=confirmCart', $tmpl->cfg['ssl_signup'] ) ?>" method="post">
	<div id="confirmation-info">
		<?php
		@include( $tmpl->tmpl( 'confirmation.miform' ) );
		$tmpl->custom( 'customtext_confirm' );
		?>
	<?php if ( $makegift ) {
		@include( $tmpl->tmpl( 'confirmation.giftform' ) );
	} ?>
	</div>
	<?php @include( $tmpl->tmpl( 'btn' ) ) ?>
	<?php echo JHTML::_( 'form.token' ) ?>
</form>

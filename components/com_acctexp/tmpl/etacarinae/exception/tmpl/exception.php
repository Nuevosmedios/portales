<?php
/**
 * @version $Id: exception.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div id="aec">
	<div id="aec-exception">
		<div class="componentheading"><?php echo $hasform ? JText::_('EXCEPTION_TITLE') : JText::_('EXCEPTION_TITLE_NOFORM') ?></div>
		<p><?php echo $hasform ? JText::_('EXCEPTION_INFO') : "" ?></p>
		<form id="form-exception" action="<?php echo $tmpl->url( array( 'task' => 'addressException') ) ?>" method="post">
			<?php @include( $tmpl->tmpl( 'list' ) ) ?>
			<?php @include( $tmpl->tmpl( 'form' ) ) ?>
			<?php echo JHTML::_( 'form.token' ) ?>
		</form>
	</div>
		<?php if ( !empty( $InvoiceFactory->pp ) ) {
			@include( $tmpl->tmpl( 'confirmation.processorinfo' ) );
		} ?>
</div>

<?php
/**
 * @version $Id: plans.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div id="aec">
	<div id="aec-plans">
		<div class="componentheading"><?php echo JText::_('PAYPLANS_HEADER') ?></div>
		<?php if ( !empty( $cart ) ) { @include( $tmpl->tmpl( 'backtocart' ) ); } ?>
		<div class="subscriptions">
			<?php
			$tmpl->custom( 'customtext_plans' );

			if ( isset( $list['group'] ) && $selected ) {
				@include( $tmpl->tmpl( 'groupheader' ) );
				unset( $list['group'] );
			}

			@include( $tmpl->tmpl( 'list' ) );
			?>
		</div>
	</div>
</div>

<?php
/**
 * @version $Id: processor_details.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div class="processor-details">
	<?php echo $processor->getLogoImg(); ?>
	<p><?php if ( isset( $processor->info['description'] ) ) { echo $processor->info['description']; } ?></p>
	<?php if ( $tmpl->cfg['displayccinfo'] && !empty( $processor->info['cc_list'] ) ) { ?>
		<div class="processor-cc-icons">
			<?php
			if ( !empty( $processor->settings['cc_icons'] ) ) {
				$cc_list = $processor->settings['cc_icons'];
			} else {
				$cc_list = $processor->info['cc_list'];
			}

			@include( $tmpl->tmpl( 'plans.cc_icons' ) );
			?>
		</div>
	<?php } ?>
</div>

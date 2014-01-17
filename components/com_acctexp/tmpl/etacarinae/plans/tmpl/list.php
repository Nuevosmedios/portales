<?php
/**
 * @version $Id: list.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div class="aec-planlist">
	<?php foreach ( $list as $litem ) { ?>
		<div class="aec-planlist-<?php echo $litem['type'] ?>" id="aec-<?php echo $litem['type'] . '-' . $litem['id'] ?>">
			<h2><?php echo $litem['name'] ?></h2>
			<p><?php echo $litem['desc'] ?></p>
			<?php if ( $litem['type'] == 'group' ) {
				@include( $tmpl->tmpl( 'groupbtn' ) );
			} else { ?>
				<div class="aec-processor-buttons">
					<?php foreach ( $litem['gw'] as $gwitem ) { ?>
						<?php @include( $tmpl->tmpl( 'planbtn' ) ); ?>
					<?php } ?>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
</div>

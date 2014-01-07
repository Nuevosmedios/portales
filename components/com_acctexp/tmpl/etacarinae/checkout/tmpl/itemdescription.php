<?php
/**
 * @version $Id: itemlist.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div class="alert alert-info checkout-list-item-description">
	<?php if ( !empty( $item['name'] ) ) {
		if ( !empty( $item['quantity'] ) ) { ?>
			<h4><?php echo $item['name'] . ( ( $item['quantity'] > 1 ) ? " (&times;" . $item['quantity'] . ")" : '' ) ?></h4>
		<?php } else { ?>
			<h4><?php echo $item['name'] ?></h4>
		<?php } ?>
	<?php } ?>
	<?php if ( !empty( $item['desc'] ) ) { ?>
		<p><?php echo $item['desc'] ?></p>
	<?php } ?>
</div>

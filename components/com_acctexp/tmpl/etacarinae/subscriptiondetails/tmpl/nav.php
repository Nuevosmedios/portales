<?php
/**
 * @version $Id: nav.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div id="aec-nav-profile">
	<ul id="aec-nav-profile-list" class="nav nav-tabs">
	<?php foreach ( $tabs as $fieldlink => $fieldname ) { ?>
		<li<?php echo ($fieldlink == $sub) ? ' class="active"':'' ?>>
			<?php echo $tmpl->lnk( array('task' => 'subscriptiondetails', 'sub' => $fieldlink), $fieldname, '', true ) ?>
		</li>
	<?php } ?>
	</ul>
</div>

<?php
/**
 * @version $Id: groupheader.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<?php if ( $list['group']['id'] > 1 ) { ?>
	<div class="group-backlink">
		<?php echo $tmpl->btn( array( 'task' => 'subscribe', 'userid' => $userid, 'passthrough' => $passthrough ), JText::_('AEC_PAYM_METHOD_BACK'), 'btn func_button' ) ?>
	</div>
	<h2><?php echo $list['group']['name'] ?></h2>
	<p><?php echo $list['group']['desc'] ?></p>
<?php } ?>

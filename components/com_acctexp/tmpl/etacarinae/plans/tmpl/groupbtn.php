<?php
/**
 * @version $Id: groupbtn.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div class="group-button">
	<?php echo $tmpl->btn( array( 'task' => 'subscribe', 'group' => $litem['id'], 'userid' => $userid, 'passthrough' => $passthrough ), JText::_('Select'), 'btn btn-plangroup' ) ?>
</div>

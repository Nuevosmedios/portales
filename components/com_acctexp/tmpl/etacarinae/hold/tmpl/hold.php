<?php
/**
 * @version $Id: hold.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div id="aec">
	<div id="aec-hold">
		<div class="componentheading"><?php echo JText::_('HOLD_TITLE') ?></div>
		<h4><?php echo sprintf( JText::_('DEAR'), $metaUser->cmsUser->name ) ?></h4>
		<div class="alert">
			<p><?php echo JText::_('HOLD_EXPLANATION') ?></p>
		</div>
</div>

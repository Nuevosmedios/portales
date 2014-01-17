<?php
/**
 * @version $Id: subscriptiondetails.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div class="well">
	<div id="upgrade-button">
		<form action="<?php echo AECToolbox::deadsureURL( 'index.php?option=com_acctexp&task=renewsubscription', !empty( $tmpl->cfg['ssl_signup'] ) ); ?>" method="post">
			<input type="hidden" name="option" value="<?php echo $option; ?>" />
			<input type="hidden" name="task" value="renewsubscription" />
			<input type="hidden" name="userid" value="<?php echo $metaUser->cmsUser->id; ?>" />
			<input type="submit" class="button btn btn-success" value="<?php echo JText::_('RENEW_BUTTON_UPGRADE');?>" />
			<?php echo JHTML::_( 'form.token' ); ?>
		</form>
	</div>
</div>

<?php
/**
 * @version $Id: processor_list.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<?php
$processors = array();
if ( !empty($tmpl->cfg['gwlist']) ) {
	$gwnames = PaymentProcessorHandler::getInstalledNameList( true );

	if ( !empty( $gwnames ) ) {
		foreach ( $gwnames as $procname ) {
			if ( !in_array( $procname, $tmpl->cfg['gwlist'] ) ) {
				continue;
			}

			$processor = trim( $procname );
			$processors[$processor] = new PaymentProcessor();
			if ( $processors[$processor]->loadName( $processor ) ) {
				$processors[$processor]->init();
				$processors[$processor]->getInfo();
				$processors[$processor]->getSettings();
			} else {
				unset( $processors[$processor] );
			}
		}
	}
}

if ( !empty( $processors ) ) { ?>
	<p>&nbsp;</p>
	<p><?php echo JText::_('NOT_ALLOWED_SECONDPAR') ?></p>
	<div class="processor-list">
		<?php foreach ( $processors as $processor ) {
			@include( $tmpl->tmpl( 'plans.processor_details' ) );
		} ?>
	</div>
<?php } ?>

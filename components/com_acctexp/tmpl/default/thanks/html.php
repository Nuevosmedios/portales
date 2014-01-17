<?php
/**
 * @version $Id: thanks/html.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

$tmpl->setTitle( JText::_('THANKYOU_TITLE') );

if ( !empty( $plan ) ) {
	if ( is_object( $plan ) ) {
		if ( !empty( $plan->params['customthanks'] ) ) {
			aecRedirect( $plan->params['customthanks'] );
		}
	}
}

if ( $renew ) {
	$msg = JText::_('SUB_FEPARTICLE_HEAD_RENEW') . '</p><p>' . JText::_('SUB_FEPARTICLE_THANKSRENEW');
	if ( $free ) {
		$msg .= JText::_('SUB_FEPARTICLE_LOGIN');
	} else {
		$msg .= JText::_('SUB_FEPARTICLE_PROCESSPAY') . JText::_('SUB_FEPARTICLE_MAIL');
	}
} else {
	$app = JFactory::getApplication();

	$msg = JText::_('SUB_FEPARTICLE_HEAD') . '</p><p>' . JText::_('SUB_FEPARTICLE_THANKS');

	$msg .=  $free ? JText::_('SUB_FEPARTICLE_PROCESS') : JText::_('SUB_FEPARTICLE_PROCESSPAY');

	$msg .= $app->getCfg( 'useractivation' ) ? JText::_('SUB_FEPARTICLE_ACTMAIL') : JText::_('SUB_FEPARTICLE_MAIL');
}

$up =& $plan->params;

if ( !empty( $up['customtext_thanks'] ) ) {
	if ( isset( $up['customtext_thanks_keeporiginal'] ) ) {
		if ( empty( $up['customtext_thanks_keeporiginal'] ) ) {
			$msg = $up['customtext_thanks'];
		} else {
			$msg .= $up['customtext_thanks'];
		}
	} else {
		$msg = $up['customtext_thanks'];
	}
}

$tmpl->addDefaultCSS();

@include( $tmpl->tmpl( 'thanks' ) );

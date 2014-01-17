<?php
/**
 * @version $Id: mod_acctexp_cart.php
 * @package AEC - Account Control Expiration - Subscription component for Joomla! OS CMS
 * @subpackage Cart Module
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

require_once( JPATH_SITE . '/components/com_acctexp/acctexp.class.php' );

$class_sfx	= $params->get( 'moduleclass_sfx', "");
$pretext 	= $params->get( 'pretext' );
$posttext 	= $params->get( 'posttext' );
$mode		= $params->get( 'mode', 'abridged' );
$button		= $params->get( 'button', 1 );

$user = &JFactory::getUser();

if ( $user->id ) {
	$lang =& JFactory::getLanguage();

	$lang->load( 'mod_acctexp_cart', JPATH_SITE );

	$c = aecCartHelper::getCartbyUserid( $user->id );

	$metaUser = new metaUser( $user->id );

	$cart = $c->getCheckout( $metaUser );

	if ( empty( $c->content ) ) {
		require ( JModuleHelper::getLayoutPath('mod_acctexp_cart', $params->get('layout', 'empty')) );
	} else {
		switch ( $mode ) {
			default:
			case 'abridged':
				$quantity = 0;
				$total = 0;
				foreach ( $cart as $bid => $bitem ) {
					if ( !empty( $bitem['obj'] ) ) {
						$quantity += $bitem['quantity'];
					} else {
						$total = $bitem['cost_total'];
					}
				}

				require ( JModuleHelper::getLayoutPath('mod_acctexp_cart', $params->get('layout', 'default')) );
				break;
			case 'full':
				require ( JModuleHelper::getLayoutPath('mod_acctexp_cart', $params->get('layout', 'full')) );
				break;
		}
	}
}

?>
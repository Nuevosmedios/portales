<?php
/**
 * @version $Id: invoice_links.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<?php echo $tmpl->lnk( array(	'task' => 'repeatPayment',
							'invoice' => $invoice,
							'userid' => $metaUser->userid
							), JText::_('GOTO_CHECKOUT') )
	. ', ' . JText::_('GOTO_CHECKOUT_CANCEL') . ' '
	.  $tmpl->lnk( array(	'task' => 'cancelPayment',
							'invoice' => $invoice,
							'userid' => $metaUser->userid,
							'pending' => 1
							), JText::_('HISTORY_ACTION_CANCEL') ) ?>

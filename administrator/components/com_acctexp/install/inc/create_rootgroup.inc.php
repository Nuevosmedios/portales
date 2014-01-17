<?php
/**
 * @version $Id: create_rootgroup.inc.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Install Includes
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

// Check for a root group
$db->setQuery("SELECT id FROM  #__acctexp_itemgroups WHERE id='1'");

// Create root group completely out of thin air (tadaa!)
if ( $db->loadResult() != 1 ) {
	$rootgroup = new ItemGroup();

	$rootgroup->id = 0;
	$rootgroup->active = 1;
	$rootgroup->visible = 1;
	$rootgroup->name = JText::_('AEC_INST_ROOT_GROUP_NAME');
	$rootgroup->desc = JText::_('AEC_INST_ROOT_GROUP_DESC');
	$rootgroup->params = array( 'color' => 'bbddff', 'icon' => 'flag_blue', 'reveal_child_items' => 1 );

	$rootgroup->storeload();

	if ( $rootgroup->id != 1 ) {
		$db->setQuery("UPDATE #__acctexp_itemgroups SET id='1' WHERE id='" . $rootgroup->id . "'");
		$db->query();
	}

	// Adding in root group relation for all plans
	$planlist = SubscriptionPlanHandler::listPlans();

	$db->setQuery("SELECT count(*) FROM  #__acctexp_itemxgroup");

	if ( count( $planlist ) > $db->loadResult() ) {
		ItemGroupHandler::setChildren( 1, $planlist );
	}
}

?>
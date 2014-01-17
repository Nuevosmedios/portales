<?php
/**
 * @version $Id: mi_mysms.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - MySMS
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_mysms
{

	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_MYSMS');
		$info['desc'] = JText::_('AEC_MI_DESC_MYSMS');
		$info['type'] = array( 'communication.other' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['add_credits']		= array( 'inputA' );
		$settings['disable_exp']		= array( 'toggle' );
		return $settings;
	}

	function expiration_action( $request )
	{
		$db = &JFactory::getDBO();

		if ( !empty( $this->settings['disable_exp'] ) ) {
			// unpublish the user
			$query = 'UPDATE #__mysms_joomlauser' .
					' SET `status` = \'0\'' .
					' WHERE `userid` = \'' . $request->metaUser->userid . '\'' .
					' LIMIT 1';
			$db->setQuery( $query );
			$db->query();
		}

		return true;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		if ( !empty( $this->settings['add_credits'] ) ) {
			$credits = (int) $this->settings['add_credits'];

			//set the user active and the new credits
			$query = 'UPDATE #__mysms_joomlauser' .
					' SET `state` = \'1\',' .
					' `credits` = credits+' . $credits .
					' WHERE `userid` = \'' . $request->metaUser->userid . '\'' .
					' LIMIT 1';
			$db->setQuery( $query );
			$db->query();
		}

		return true;
	}
}
?>

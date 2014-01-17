<?php
/**
 * @version $Id: mi_raffle.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Raffle
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_raffle
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_RAFFLE_NAME');
		$info['desc'] = JText::_('AEC_MI_RAFFLE_DESC');
		$info['type'] = array( 'aec.membership', 'vendor.valanx' );

		return $info;
	}

	function checkInstallation()
	{
		$db = &JFactory::getDBO();

		$app = JFactory::getApplication();

		$tables	= array();
		$tables	= $db->getTableList();

		return in_array( $app->getCfg( 'dbprefix' ) . 'acctexp_mi_rafflelist', $tables );
	}

	function install()
	{
		$db = &JFactory::getDBO();

		$query = 'CREATE TABLE IF NOT EXISTS `#__acctexp_mi_rafflelist` ('
		. '`id` int(11) NOT NULL auto_increment,'
		. '`group` int(11) NULL,'
		. '`params` text NULL,'
		. '`finished` int(11) default \'0\','
		. ' PRIMARY KEY (`id`)'
		. ')'
		;
		$db->setQuery( $query );
		$db->query();

		$query = 'CREATE TABLE IF NOT EXISTS `#__acctexp_mi_raffleuser` ('
		. '`id` int(11) NOT NULL auto_increment,'
		. '`userid` int(11) NOT NULL,'
		. '`wins` int(11) NOT NULL default \'0\','
		. '`runs` int(11) NOT NULL default \'0\','
		. '`params` text NULL,'
		. ' PRIMARY KEY (`id`)'
		. ')'
		;
		$db->setQuery( $query );
		$db->query();

		return true;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

        $settings = array();
		$settings['list_group']			= array( 'inputA' );
		$settings['draw_range']			= array( 'inputA' );
		$settings['max_participations']	= array( 'inputA' );
		$settings['max_wins']			= array( 'inputA' );

		$settings['col_recipient']		= array( 'inputE' );

		return $settings;
	}

	function saveparams( $params )
	{
		$db = &JFactory::getDBO();

		$app = JFactory::getApplication();

		$tables	= array();
		$tables	= $db->getTableList();

		if ( in_array( $app->getCfg( 'dbprefix' ) . 'acctexp_mi_rafflelist', $tables ) ) {
			$db->setQuery( "SHOW COLUMNS FROM #__acctexp_mi_rafflelist LIKE 'finished'" );
			$result = $db->loadObject();

			if ( empty( $result->Field ) ) {
				$db->setQuery( "ALTER TABLE #__acctexp_mi_rafflelist ADD `finished` int(11) default '0'" );
				$db->query();
			}
		}

		return $params;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		$raffleuser = new AECMI_raffleuser();
		$raffleuser->loadUserid( $request->metaUser->userid );

		if ( empty( $raffleuser->id ) ) {
			$raffleuser->userid = $request->metaUser->userid;
			$raffleuser->storeload();
		}

		if ( $raffleuser->wins >= $this->settings['max_wins'] ) {
			return null;
		}

		if ( $raffleuser->runs >= $this->settings['max_participations'] ) {
			return null;
		}

		$list_group = empty( $this->settings['list_group'] ) ? 0 : $this->settings['list_group'];

		$rafflelist = new AECMI_rafflelist();

		if ( $rafflelist->loadMax( $list_group ) === false ) {
			$rafflelist->id = 0;
			$rafflelist->group = $list_group;

			$rafflelist->params = new stdClass();

			$rafflelist->params->participants = array();
			$rafflelist->params->settings = array();
			$rafflelist->params->settings['draw_range'] = $this->settings['draw_range'];
		}

		if ( in_array( $raffleuser->id, $rafflelist->params->participants ) ) {
			return null;
		}

		$rafflelist->params->participants[] = $raffleuser->id;

		$miinfo = array();
		$miinfo['listid']		= $rafflelist->id;
		$miinfo['sequenceid']	= count( $rafflelist->params->participants );

		$request->metaUser->meta->setMIParams( $request->parent->id, $request->plan->id, $miinfo );
		$request->metaUser->meta->storeload();

		if ( count( $rafflelist->params->participants ) >= $rafflelist->params->settings['draw_range'] ) {
			$app = JFactory::getApplication();

			$range = (int) $rafflelist->params->settings['draw_range'];

			$winner = rand( 1, $range );

			$rafflelist->params->winid = $rafflelist->params->participants[($winner-1)];

			$result = $rafflelist->closeRun( $rafflelist->params->winid );

			// TODO: Multiple winners
			$winnerMeta = new metaUser( $result['winners'][0] );

			$colET = 'The current draw results are in:' . "\n" . "\n";
			$colET .= 'List ID: ' . $rafflelist->id . "\n" . "\n";
			$colET .= 'Winner:' . "\n";
			$colET .= 'Sequence ID:' . count( $rafflelist->params->participants ) . "\n";
			$colET .= 'Userid: ' . $winnerMeta->userid . '; Username: ' . $winnerMeta->cmsUser->username . '; Email: ' . $winnerMeta->cmsUser->email . "\n" . "\n";
			$colET .= 'Further Participants:' . "\n" . "\n";

			foreach ( $result['participants'] as $userid ) {
				$query = 'SELECT `username`, `email`'
					. ' FROM #__users'
					. ' WHERE `id` = \'' . $userid . '\'';
					;
				$db->setQuery( $query );
				$u = $db->loadObject();

				$colET .= $userid . ';' . $u->username . ';' . $u->email . "\n";
			}

			// check if Global Config `mailfrom` and `fromname` values exist
			if ( $app->getCfg( 'mailfrom' ) != '' && $app->getCfg( 'fromname' ) != '' ) {
				$adminName2 	= $app->getCfg( 'fromname' );
				$adminEmail2 	= $app->getCfg( 'mailfrom' );
			} else {
				// use email address and name of first superadmin for use in email sent to user
				$rows = xJACLhandler::getSuperAdmins();

				$adminName2 	= $rows[0]->name;
				$adminEmail2 	= $rows[0]->email;
			}

			$recipients = explode( ',', $this->settings['col_recipient'] );

			foreach ( $recipients as $current => $email ) {
				$recipients[$current] = AECToolbox::rewriteEngineRQ( trim( $email ), $request );
			}

			$recipients[] = $admin->email;

			$subject = 'Raffle Drawing Results for ' . $app->getCfg( 'sitename' );

			xJ::sendMail( $adminEmail2, $adminEmail2, $recipients, $subject, $colET );
		}

		$rafflelist->check();
		$rafflelist->store();

		return true;
	}

}

class AECMI_rafflelist extends serialParamDBTable {
	/** @var int Primary key */
	var $id						= null;
	/** @var int */
	var $group					= null;
	/** @var text */
	var $params					= null;
	/** @var int */
	var $finished				= null;

	/**
	* @param database A database connector object
	*/
	function AECMI_rafflelist()
	{
		parent::__construct( '#__acctexp_mi_rafflelist', 'id' );
	}

	function declareParamFields()
	{
		return array( 'params' );
	}

	function loadMax( $group=null ) {
		$db = &JFactory::getDBO();

		$query = 'SELECT max(`id`)'
			. ' FROM #__acctexp_mi_rafflelist'
			. ' WHERE `finished` = 0'
			;

		if ( !empty( $group ) ) {
			$query .= ' AND `group` = \'' . $group . '\'';
		}

		$db->setQuery( $query );
		$id = $db->loadResult();
		if ( empty( $id ) ) {
			return false;
		} else {
			return $this->load( $id );
		}
	}

	function closeRun( $winid )
	{
		$db = &JFactory::getDBO();

		$participants = array();
		$winners = array();
		foreach ( $this->params->participants as $rid ) {
			$raffleuser = new AECMI_raffleuser();
			$raffleuser->load( $rid );

			$raffleuser->runs += 1;

			if ( $rid == $winid ) {
				$raffleuser->wins += 1;

				$winners[] = $raffleuser->userid;
			} else {
				$participants[] = $raffleuser->userid;
			}

			$raffleuser->storeload();
		}

		$this->finished = 1;

		return array( 'participants' => $participants, 'winners' => $winners );
	}
}

class AECMI_raffleuser extends serialParamDBTable {
	/** @var int Primary key */
	var $id						= null;
	/** @var int */
	var $userid					= null;
	/** @var int */
	var $wins					= null;
	/** @var int */
	var $runs					= null;
	/** @var text */
	var $params					= null;

	/**
	* @param database A database connector object
	*/
	function AECMI_raffleuser()
	{
		parent::__construct( '#__acctexp_mi_raffleuser', 'id' );
	}

	function declareParamFields()
	{
		return array( 'params' );
	}

	function loadUserid( $userid) {
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
			. ' FROM #__acctexp_mi_raffleuser'
			. ' WHERE `userid` = \'' . $userid . '\''
			;
		$db->setQuery( $query );
		return $this->load( $db->loadResult() );
	}
}

?>

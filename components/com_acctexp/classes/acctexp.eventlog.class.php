<?php
/**
 * @version $Id: acctexp.eventlog.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class eventLog extends serialParamDBTable
{
	/** @var int Primary key */
	var $id			= null;
	/** @var datetime */
	var $datetime	= null;
	/** @var string */
	var $short 		= null;
	/** @var text */
	var $tags 		= null;
	/** @var text */
	var $event 		= null;
	/** @var int */
	var $level		= null;
	/** @var int */
	var $notify		= null;
	/** @var text */
	var $params		= null;

	/**
	 * @param database A database connector object
	 */
	function eventLog()
	{
	 	parent::__construct( '#__acctexp_eventlog', 'id' );
	}

	function declareParamFields()
	{
		return array( 'params' );
	}

	function issue( $short, $tags, $text, $level = 2, $params = null, $force_notify = 0, $force_email = 0 )
	{
		global $aecConfig;

		$app = JFactory::getApplication();

		$lang = JFactory::getLanguage();

		// Event, Notice, Warning, Error
		$legal_levels = array( 2, 8, 32, 128 );

		if ( !in_array( (int) $level, $legal_levels ) ) {
			$level = $legal_levels[0];
		}

		$this->datetime	= date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
		$this->short	= $short;
		$this->tags		= $tags;
		$this->event	= $text;
		$this->level	= (int) $level;

		// Create a notification link if this matches the desired level
		if ( $this->level >= $aecConfig->cfg['error_notification_level'] ) {
			$this->notify	= 1;
		} else {
			$this->notify	= $force_notify ? 1 : 0;
		}

		// Mail out notification to all admins if this matches the desired level
		if ( ( $this->level >= $aecConfig->cfg['email_notification_level'] ) || $force_email ) {
			// check if Global Config `mailfrom` and `fromname` values exist
			if ( $app->getCfg( 'mailfrom' ) != '' && $app->getCfg( 'fromname' ) != '' ) {
				$adminName2 	= $app->getCfg( 'fromname' );
				$adminEmail2 	= $app->getCfg( 'mailfrom' );
			} else {
				$rows = xJACLhandler::getSuperAdmins();

				$adminName2 	= $rows[0]->name;
				$adminEmail2 	= $rows[0]->email;
			}

			if ( !$lang->hasKey( "AEC_NOTICE_NUMBER_" . $this->level ) ) {
				$lang =& JFactory::getLanguage();
				
				$lang->load( 'com_acctexp.admin', JPATH_ADMINISTRATOR );
			}

			// Send notification to all administrators
			$subject2	= sprintf( JText::_('AEC_ASEND_NOTICE'), JText::_( "AEC_NOTICE_NUMBER_" . $this->level ), $this->short, $app->getCfg( 'sitename' ) );
			$message2	= sprintf( JText::_('AEC_ASEND_NOTICE_MSG'), $this->event  );

			$subject2	= html_entity_decode( $subject2, ENT_QUOTES, 'UTF-8' );
			$message2	= html_entity_decode( $message2, ENT_QUOTES, 'UTF-8' );

			// get email addresses of all admins and superadmins set to recieve system emails
			$admins = AECToolbox::getAdminEmailList();

			foreach ( $admins as $adminemail ) {
				if ( !empty( $adminemail ) ) {
					xJ::sendMail( $adminEmail2, $adminEmail2, $adminemail, $subject2, $message2 );
				}
			}
		}

		if ( !empty( $params ) && is_array( $params ) ) {
			$this->params = $params;
		}

		$this->check();
		$this->store();
	}

}

?>

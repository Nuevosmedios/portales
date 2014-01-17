<?php
/**
 * @version $Id: acctexp.displaypipeline.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class displayPipelineHandler
{
	function getUserPipelineEvents( $userid )
	{
		$db = &JFactory::getDBO();

		// Entries for this user only
		$query = 'SELECT `id`'
				. ' FROM #__acctexp_displaypipeline'
				. ' WHERE `userid` = \'' . $userid . '\' AND `only_user` = \'1\''
				;
		$db->setQuery( $query );
		$events = xJ::getDBArray( $db );

		// Entries for all users
		$query = 'SELECT `id`'
				. ' FROM #__acctexp_displaypipeline'
				. ' WHERE `only_user` = \'0\''
				;
		$db->setQuery( $query );
		$events = array_merge( $events, $db->loadResultArray() );

		$return = '';
		if ( empty( $events ) ) {
			return $return;
		}

		foreach ( $events as $eventid ) {
			$displayPipeline = new displayPipeline();
			$displayPipeline->load( $eventid );
			if ( $displayPipeline->id ) {

				// If expire & expired -> delete
				if ( $displayPipeline->expire ) {
					$expstamp = strtotime( $displayPipeline->expstamp );
					if ( ( $expstamp - ( (int) gmdate('U') ) ) < 0 ) {
						$displayPipeline->delete();
						continue;
					}
				}

				// If displaymax exceeded -> delete
				$displayremain = $displayPipeline->displaymax - $displayPipeline->displaycount;
				if ( $displayremain <= 0 ) {
					$displayPipeline->delete();
					continue;
				}

				// If this can only be displayed once per user, prevent it from being displayed again
				if ( $displayPipeline->once_per_user ) {
					$params = $displayPipeline->params;

					if ( isset( $displayPipeline->params['displayedto'] ) ) {
						$users = $displayPipeline->params['displayedto'];
						if ( in_array( $userid, $users ) ) {
							continue;
						} else {
							$users[] = $userid;
							$displayPipeline->params['displayedto'] = $users;
						}
					}
				}

				// Ok, now append text
				$return .= stripslashes( $displayPipeline->displaytext );

				// Update display if at least one display would remain
				if ( $displayremain > 1 ) {
					$displayPipeline->displaycount = $displayPipeline->displaycount + 1;
					$displayPipeline->check();
					$displayPipeline->store();
				} else {
					$displayPipeline->delete();
				}
			}
		}

		return $return;
	}
}

class displayPipeline extends serialParamDBTable
{
	/** @var int Primary key */
	var $id				= null;
	/** @var int */
	var $userid			= null;
	/** @var int */
	var $only_user		= null;
	/** @var int */
	var $once_per_user	= null;
	/** @var datetime */
	var $timestamp		= null;
	/** @var int */
	var $expire			= null;
	/** @var datetime */
	var $expstamp 		= null;
	/** @var int */
	var $displaycount	= null;
	/** @var int */
	var $displaymax		= null;
	/** @var text */
	var $displaytext	= null;
	/** @var text */
	var $params			= null;

	/**
	 * @param database A database connector object
	 */
	function displayPipeline()
	{
	 	parent::__construct( '#__acctexp_displaypipeline', 'id' );
	}

	function declareParamFields()
	{
		return array( 'params', 'displaytext' );
	}

	function create( $userid, $only_user, $once_per_user, $expire, $expiration, $displaymax, $displaytext, $params=null )
	{
		$this->id				= 0;
		$this->userid			= $userid;
		$this->only_user		= $only_user;
		$this->once_per_user	= $once_per_user;
		$this->timestamp		= date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
		$this->expire			= $expire ? 1 : 0;
		if ( $expire ) {
			$this->expstamp		= date( 'Y-m-d H:i:s', strtotime( $expiration ) );
		}
		$this->displaycount		= 0;
		$this->displaymax		= $displaymax;

		$this->displaytext		= $displaytext;

		if ( is_array( $params ) ) {
			$this->params = $params;
		}

		$this->check();

		if ( $this->store() ) {
			return true;
		} else {
			return false;
		}
	}
}

?>

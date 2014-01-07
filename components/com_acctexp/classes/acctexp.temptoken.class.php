<?php
/**
 * @version $Id: acctexp.temptoken.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class TempTokenHandler
{
	function TempTokenFromPlan( $plan )
	{
		$temptoken = new aecTempToken();
		$temptoken->getComposite();

		if ( empty( $temptoken->content ) ) {
			$content = array();
			$content['usage']		= $plan['id'];
			$content['processor']	= $plan['gw'][0]->processor_name;
			if ( isset( $plan['gw'][0]->recurring ) ) {
				$content['recurring']	= $plan['gw'][0]->recurring;
			}

			$temptoken->create( $content );
		} elseif ( empty( $temptoken->content['usage'] ) || ( $temptoken->content['usage'] !== $plan['id'] ) ) {
			$temptoken->content['usage']		= $plan['id'];
			$temptoken->content['processor']	= $plan['gw'][0]->processor_name;
			if ( isset( $plan['gw'][0]->recurring ) ) {
				$temptoken->content['recurring']	= $plan['gw'][0]->recurring;
			}

			$temptoken->storeload();
		}
	}
}

class aecTempToken extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $token 				= null;
	/** @var text */
	var $content 			= null;
	/** @var datetime */
	var $created_date	 	= null;
	/** @var string */
	var $ip		 			= null;

	function aecTempToken()
	{
		parent::__construct( '#__acctexp_temptoken', 'id' );
	}

	function declareParamFields()
	{
		return array( 'content' );
	}

	function getToken()
	{
		$session =& JFactory::getSession();
		return $session->getToken();
	}

	function getComposite()
	{
		$token = $this->getToken();

		$this->getByToken( $token );

		if ( empty( $this->content ) && !empty( $_COOKIE['aec_token'] ) ) {
			$token = $_COOKIE['aec_token'];

			$this->getByToken( $token );
		}

		if ( empty( $this->token ) ) {
			$this->token = $token;
		}

		if ( empty( $this->ip ) ) {
			$this->created_date	= date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
			$this->ip			= $_SERVER['REMOTE_ADDR'];
		}
	}

	function getByToken( $token )
	{
		$query = 'SELECT `id`'
		. ' FROM #__acctexp_temptoken'
		. ' WHERE `token` = \'' . $token . '\''
		;
		$this->_db->setQuery( $query );
		$id = $this->_db->loadResult();

		if ( empty( $id ) ) {
			$query = 'SELECT `id`'
			. ' FROM #__acctexp_temptoken'
			. ' WHERE `ip` = \'' . $_SERVER['REMOTE_ADDR'] . '\''
			;
			$this->_db->setQuery( $query );
			$id = $this->_db->loadResult();
		}

		if ( empty( $id ) ) {
			$this->load(0);
		} else {
			$this->load( $id );

			if ( ( $this->ip != $_SERVER['REMOTE_ADDR'] ) ) {
				$this->delete();

				$this->load(0);
			}
		}
	}

	function create( $content, $token=null )
	{
		if ( empty( $token ) ) {
			$session =& JFactory::getSession();
			$token = $session->getToken();
		}

		$query = 'SELECT `id`'
		. ' FROM #__acctexp_temptoken'
		. ' WHERE `token` = \'' . $token . '\''
		. ' OR `ip` = \'' . $_SERVER['REMOTE_ADDR'] . '\''
		;
		$this->_db->setQuery( $query );
		$id = $this->_db->loadResult();

		if ( $id ) {
			$this->id		= $id;
		}

		if ( empty( $token ) ) {
			$token = date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
		}

		$this->token		= $token;
		$this->content		= $content;
		$this->created_date	= date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
		$this->ip			= $_SERVER['REMOTE_ADDR'];

		setcookie( 'aec_token', $token, ( (int) gmdate('U') )+ 600 );

		return $this->storeload();
	}
	
	function delete()
	{
		setcookie( 'aec_token', "", ( (int) gmdate('U') ) - 3600);

		return parent::delete();
	}
}

?>

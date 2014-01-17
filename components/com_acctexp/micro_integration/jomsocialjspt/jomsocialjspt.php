<?php
/**
* @Copyright Ready Bytes Software Labs Pvt. Ltd. (C) 2010- author-Team Joomlaxi
* @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
**/


// Dont allow direct linking
defined('_JEXEC') or die( 'Direct Access to this location is not allowed.' );

class mi_jomsocialjspt
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_("AEC_MI_NAME_JOMSOCIALJSPT");
		$info['desc'] = JText::_("AEC_MI_DESC_JOMSOCIALJSPT");
		$info['type'] = array( 'community.social', 'vendor.joomlaxi' );

		return $info;
	}

	function detect_application()
	{
		if ( !is_dir( JPATH_ROOT. DS . 'components'. DS .'com_community' ) || !is_dir( JPATH_ROOT. DS . 'components'. DS .'com_xipt' ) ) {
			return false;
		}

		return true;
	}

	function Settings()
	{
		require_once ( JPATH_ROOT.'/components/com_xipt/api.xipt.php');
		
		$database	=& JFactory::getDBO();
        $settings = array();
		$settings['profiletype']				= array( 'list' );
		$settings['profiletype_after_exp'] 		= array( 'list' );

		$filter = array ('published'=>1);
		
		$profiletypes = XiptAPI::getProfiletypeInfo(0, $filter);
	 	
		$spt = array();
		$spte = array();

		$ptype = array();
		foreach ( $profiletypes as $profiletype ) {
			$ptype[] = JHTML::_('select.option', $profiletype->id, $profiletype->name );
			if ( !empty( $this->settings['profiletype'] ) ){
				if ( $profiletype->id == $this->settings['profiletype'] ) {
					$spt[] = JHTML::_('select.option', $profiletype->id, $profiletype->name );
				}
			}

			if ( !empty( $this->settings['profiletype_after_exp'] ) ) {
				if ( $profiletype->id == $this->settings['profiletype_after_exp'] ) {
					$spte[] = JHTML::_('select.option', $profiletype->id, $profiletype->name );
				}
			}
		}

		$settings['lists']['profiletype']			= JHTML::_('select.genericlist', $ptype, 'profiletype', 'size="1"' , 'value', 'text', $spt );
		$settings['lists']['profiletype_after_exp'] = JHTML::_('select.genericlist', $ptype, 'profiletype_after_exp', 'size="1"', 'value', 'text', $spte );

		return $settings;
	}

	function action( $request )
	{
		if ( is_array( $this->settings['profiletype'] ) ) {
			$this->settings['profiletype'] = $this->settings['profiletype'][0];
		}

		if ( !empty( $this->settings['profiletype'] ) ) {
				$this->setUserProfiletype( $request->metaUser->userid, $this->settings['profiletype'] );
		}

		return true;
	}

	function expiration_action( $request )
	{
		if ( is_array( $this->settings['profiletype_after_exp'] ) ) {
			$this->settings['profiletype_after_exp'] = $this->settings['profiletype_after_exp'][0];
		}

		if ( !empty( $this->settings['profiletype_after_exp'] ) ) {
				$this->setUserProfiletype( $request->metaUser->userid, $this->settings['profiletype_after_exp'] );
		}

		return true;
	}


	function setUserProfiletype( $userId, $pId )
	{
		if ( !$this->detect_application() ) {
			return null;
		}

		require_once ( JPATH_ROOT.'/components/com_xipt/api.xipt.php');

		$subscription_message = XiptAPI::getGlobalConfig('subscription_message');	

		if ( empty( $subscription_message ) ) {
			return;
		}
			
		XiptAPI::setUserProfiletype( $userId, $pId, 'ALL' );
	}

	function saveparams( $request )
	{
		//save all data in xipt_aec table
		$db =& JFactory::getDBO();

		$planid = $this->id;
		$mi_jspthandler = new jomsocialjspt_restriction();

		$id = $mi_jspthandler->getIDbyPlanId( $planid );

		$mi_id = $id ? $id : 0;
		$mi_jspthandler->load( $mi_id );

		$mi_jspthandler->planid = $planid;
		$mi_jspthandler->profiletype = $request['profiletype'][0];

		$mi_jspthandler->check();
		$mi_jspthandler->store();

		return $request;
	}

}


class jomsocialjspt_restriction extends serialParamDBTable {
	/** @var int Primary key */
	var $id						= null;
	/** @var int */
	var $planid		 			= null;
	/** @var int contain micro-integration id  */
	var $profiletype 			= null;
	/** @var int */

	function jomsocialjspt_restriction() {
		parent::__construct( '#__xipt_aec', 'id' );
	}

	function getIDbyPlanId( $planid ) {
		$db = &JFactory::getDBO();

		$query = 'SELECT '.$db->nameQuote('id')
			. ' FROM '.$db->nameQuote('#__xipt_aec')
			. ' WHERE '.$db->nameQuote('planid').'=' .$db->Quote($planid);

		$db->setQuery( $query );
		return $db->loadResult();
	}
}
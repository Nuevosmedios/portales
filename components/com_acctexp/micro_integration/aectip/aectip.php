<?php
/**
 * @version $Id: mi_aectip.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - AEC Tip
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_aectip
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_AECTIP_NAME');
		$info['desc'] = JText::_('AEC_MI_AECTIP_DESC');
		$info['type'] = array( 'aec.checkout', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();

		$settings['max']			= array( 'inputB' );
		$settings['confirm_name']	= array( 'inputC' );
		$settings['confirm_desc']	= array( 'inputD' );

		return $settings;
	}

	function Defaults()
	{
		$settings = array();

		$settings['confirm_name']	= array( JText::_('MI_MI_AECTIP_USERSELECT_DEFAULT_NAME') );
		$settings['confirm_desc']	= array( JText::_('MI_MI_AECTIP_USERSELECT_DEFAULT_DESC') );

		return $settings;
	}

	function saveParams( $params )
	{
		$params['max'] = AECToolbox::correctAmount( $params['max'] );

		return $params;
	}

	function getMIform( $request )
	{
		$settings = array();

		if ( !empty( $this->settings['confirm_desc'] ) ) {
			$settings['confirm_desc'] = array( 'p', "", $this->settings['confirm_desc'] );
		}

		$settings['amt'] = array( 'inputC', $this->settings['confirm_name'], '', '' );

		if ( !empty( $this->settings['max'] ) ) {
			$settings['validation']['rules'] = array();
			$settings['validation']['rules']['amt'] = array( 'max' => $this->settings['max'] );
		}

		return $settings;
	}

	function verifyMIform( $request )
	{
		$return = array();

		if ( !empty( $request->params['amt'] ) ) {
			if ( $request->params['amt'] > $this->settings['max'] ) {
				$return['error'] = JText::_('MI_MI_AECTIP_USERSELECT_TOOMUCH') . ' ' . $this->settings['max'];
			}
		}

		return $return;
	}

	function invoice_item_cost( $request )
	{
		$this->modifyPrice( $request );

		return true;
	}

	function modifyPrice( $request )
	{
		if ( !isset( $request->params['amt'] ) ) {
			return null;
		}

		$price = AECToolbox::correctAmount( $request->params['amt'] );

		$request->add['terms']->nextterm->addCost( $price, array( 'details' => $this->settings['confirm_name'], 'no-discount' => true ) );

		return null;
	}

}
?>

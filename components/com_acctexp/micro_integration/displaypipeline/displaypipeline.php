<?php
/**
 * @version $Id: mi_displaypipeline.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - DisplayPipeline
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_displaypipeline
{

	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_DISPLAYPIPELINE');
		$info['desc'] = JText::_('AEC_MI_DESC_DISPLAYPIPELINE');
		$info['type'] = array( 'tracking.affiliate', 'system', 'aec.tools', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['only_user']			= array( 'toggle' );
		$settings['once_per_user']		= array( 'toggle' );

		$settings['expire']				= array( 'toggle' );
		$settings['expiration']			= array( 'inputE' );

		$settings['displaymax']			= array( 'inputB' );
		$settings['text']				= array( 'inputE' );

		$rewriteswitches				= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		$text = AECToolbox::rewriteEngineRQ( $this->settings['text'], $request );

		$displaypipeline = new displayPipeline();
		$displaypipeline->create( $metaUser->userid, $this->settings['only_user'], $this->settings['once_per_user'], $this->settings['expire'], $this->settings['expiration'], $this->settings['displaymax'], $text );
		return true;
	}

}

?>

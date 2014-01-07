<?php
/**
 * @version $Id: mi_sobipro.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Sigsiu Online Business Index
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@skore.de> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_sobipro extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_SOBIPRO_NAME');
		$info['desc'] = JText::_('AEC_MI_SOBIPRO_DESC');
		$info['type'] = array( 'directory_documentation.directory', 'vendor.sigsiu' );

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

        $settings = array();
		$settings['publish_all']		= array( 'toggle' );
		$settings['unpublish_all']		= array( 'toggle' );

		$settings = $this->autoduplicatesettings( $settings );

		$xsettings = array();
		$xsettings['rebuild']			= array( 'toggle' );
		$xsettings['remove']			= array( 'toggle' );

		return array_merge( $xsettings, $settings );
	}

	function relayAction( $request )
	{
		if ( isset( $this->settings['unpublish_all'.$request->area] ) ) {
			if ( $this->settings['unpublish_all'.$request->area] ) {
				$this->unpublishItems( $request->metaUser );
			}
		}

		if ( isset( $this->settings['publish_all'.$request->area] ) ) {
			if ( $this->settings['publish_all'.$request->area] ) {
				$this->publishItems( $request->metaUser );
			}
		}

		return true;
	}

	function publishItems( $metaUser )
	{
		$db = &JFactory::getDBO();

		$query = 'UPDATE #__sobipro_object'
				. ' SET `state` = \'1\''
				. ' WHERE `owner` = \'' . $metaUser->userid . '\''
				. ' AND `oType`=\'entry\''
				;
		$db->setQuery( $query );

		if ( $db->query() ) {
			return true;
		} else {
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}

	function unpublishItems( $metaUser )
	{
		$db = &JFactory::getDBO();

		$query = 'UPDATE #__sobipro_object'
				. ' SET `state` = \'0\''
				. ' WHERE `owner` = \'' . $metaUser->userid . '\''
				. ' AND `oType`=\'entry\''
				;
		$db->setQuery( $query );

		if ( $db->query() ) {
			return true;
		} else {
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}
}

?>

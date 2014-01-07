<?php
/**
 * @version $Id: mi_eventlog.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Eventlog
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_eventlog extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_EVENTLOG_NAME');
		$info['desc'] = JText::_('AEC_MI_EVENTLOG_DESC');
		$info['type'] = array( 'system', 'aec.tools', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['short']			= array( 'inputE' );
		$settings['tags']			= array( 'inputE' );
		$settings['text']			= array( 'inputD' );
		$settings['level']			= array( 'list' );
		$settings['force_notify']	= array( 'toggle' );
		$settings['force_email']	= array( 'toggle' );
		$settings['params']			= array( 'inputD' );

		$settings = $this->autoduplicatesettings( $settings, array(), true, true );

		$levels[] = JHTML::_('select.option', 2, JText::_('AEC_NOTICE_NUMBER_2') );
		$levels[] = JHTML::_('select.option', 8, JText::_('AEC_NOTICE_NUMBER_8') );
		$levels[] = JHTML::_('select.option', 32, JText::_('AEC_NOTICE_NUMBER_32') );
		$levels[] = JHTML::_('select.option', 128, JText::_('AEC_NOTICE_NUMBER_128') );

		if ( !isset( $this->settings['level'] ) ) {
			$this->settings['level'] = 0;
		}

		if ( !isset( $this->settings['level_exp'] ) ) {
			$this->settings['level_exp'] = 0;
		}

		if ( !isset( $this->settings['level_pre_exp'] ) ) {
			$this->settings['level_pre_exp'] = 0;
		}

		$settings['lists']['level'] = JHTML::_('select.genericlist', $levels, 'level', 'size="5"', 'value', 'text', $this->settings['level'] );
		$settings['lists']['level_exp'] = JHTML::_('select.genericlist', $levels, 'level_exp', 'size="5"', 'value', 'text', $this->settings['level_exp'] );
		$settings['lists']['level_pre_exp'] = JHTML::_('select.genericlist', $levels, 'level_pre_exp', 'size="5"', 'value', 'text', $this->settings['level_pre_exp'] );

		return $settings;
	}


	function relayAction( $request )
	{
		if ( !isset( $this->settings['short'.$request->area] ) ) {
			return null;
		}

		$db = &JFactory::getDBO();

		$rewriting = array( 'short', 'tags', 'text', 'params' );

		foreach ( $rewriting as $rw_name ) {
			$this->settings[$rw_name.$request->area] = AECToolbox::rewriteEngineRQ( $this->settings[$rw_name.$request->area], $request );
		}

		$log_entry = new EventLog();
		$log_entry->issue( $this->settings['short'.$request->area], $this->settings['tags'.$request->area], $this->settings['text'.$request->area], $this->settings['level'.$request->area], $this->settings['params'.$request->area], $this->settings['force_notify'.$request->area], $this->settings['force_email'.$request->area] );
	}
}
?>

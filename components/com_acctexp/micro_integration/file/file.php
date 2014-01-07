<?php
/**
 * @version $Id: mi_file.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - File Manipulation
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_file extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_FILE_NAME');
		$info['desc'] = JText::_('AEC_MI_FILE_DESC');
		$info['type'] = array( 'basic.filesystem', 'system', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['path']			= array( 'inputE' );
		$settings['append']			= array( 'toggle' );
		$settings['content']		= array( 'inputD' );

		$settings = $this->autoduplicatesettings( $settings, array(), true, true );

		return $settings;
	}


	function relayAction( $request )
	{
		if ( !isset( $this->settings['path'.$request->area] ) ) {
			return null;
		}

		$db = &JFactory::getDBO();

		$rewriting = array( 'path', 'append', 'content' );

		foreach ( $rewriting as $rw ) {
			$this->settings[$rw.$request->area] = AECToolbox::rewriteEngineRQ( $this->settings[$rw.$request->area], $request );
		}

		if ( $this->settings['append'.$request->area] ) {
			$file = fopen( $this->settings['path'.$request->area], "a" );
		} else {
			$file = fopen( $this->settings['path'.$request->area], "w" );
		}

		fwrite( $file, $this->settings['content'.$request->area] );

		return fclose( $file );
	}
}
?>

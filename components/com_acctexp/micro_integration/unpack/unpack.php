<?php
/**
 * @version $Id: mi_unpack.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - File Unpacking
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_unpack extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_UNPACK_NAME');
		$info['desc'] = JText::_('AEC_MI_UNPACK_DESC');
		$info['type'] = array( 'basic.filesystem', 'system', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['source']			= array( 'inputE' );
		$settings['target']			= array( 'inputE' );

		$settings = $this->autoduplicatesettings( $settings, array(), true, true );

		return $settings;
	}


	function relayAction( $request )
	{
		if ( !isset( $this->settings['path'.$request->area] ) ) {
			return null;
		}

		$rewriting = array( 'source', 'target' );

		foreach ( $rewriting as $rw ) {
			$this->settings[$rw.$request->area] = AECToolbox::rewriteEngineRQ( $this->settings[$rw.$request->area], $request );
		}

		$this->unpackFile( $this->settings['source'.$request->area], $this->settings['target'.$request->area] );

		return true;
	}

	function unpackFile( $source, $target )
	{
		if ( !function_exists( 'PclTarExtract' ) ) {
			require_once( JPATH_SITE . '/administrator/includes/pcl/pcltar.lib.php' );
		}

		if ( !@is_dir( $target ) ) {
			// Borrowed from php.net page on mkdir. Created by V-Tec (vojtech.vitek at seznam dot cz)
			$folder_path = array( strstr( $target, '.' ) ? dirname( $target ) : $target );

			while ( !@is_dir( dirname( end( $folder_path ) ) )
					&& dirname(end($folder_path)) != '/'
					&& dirname(end($folder_path)) != '.'
					&& dirname(end($folder_path)) != '' ) {
				array_push( $folder_path, dirname( end( $folder_path ) ) );
			}

			while ( $parent_folder_path = array_pop( $folder_path ) ) {
				@mkdir( $parent_folder_path, 0644 );
			}
		}

		return PclTarExtract( $source, $target );
	}

}
?>

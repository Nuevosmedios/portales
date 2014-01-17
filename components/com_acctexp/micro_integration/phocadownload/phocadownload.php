<?php
/**
 * @version $Id: mi_phocadownload.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Phoca Download
 * @copyright Copyright (C) 2011 David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
defined('_JEXEC') OR defined( '_VALID_MOS' ) OR die( 'Direct Access to this location is not allowed.' );

class mi_phocadownload extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_PHOCADOWNLOAD');
		$info['desc'] = JText::_('AEC_MI_DESC_PHOCADOWNLOAD');
		$info['type'] = array( 'directory_documentation.downloads', 'vendor.phoca' );

		return $info;
	}

	function Settings()
	{
		$settings = array();

		$path = rtrim( JPATH_ROOT, DS ) . DS . 'administrator' . DS . 'components' . DS . 'com_phocadownload' . DS . 'tables' . DS . 'phocadownloadcat.php';

		if ( !file_exists( $path ) ) {
			echo 'This module can not work without the Phoca Download Component';

			return $settings;
		} else {
			@include_once( $path );
		}

		$db = &JFactory::getDBO();
		$db->setQuery( 'SELECT * FROM #__phocadownload_categories' );

		$cats = $db->loadObjectList();

		$catslist = array();
		$catslist[] = JHTML::_('select.option', 0, "--- --- ---" );

		foreach ( $cats as $id => $row ) {
			$catslist[] = JHTML::_('select.option', $row->id, $row->id . ': ' . $row->title );
		}

		$settings['addaccess']			= array( 'list' );
		$settings['unaccess']			= array( 'list' );

		$settings['upload_addaccess']	= array( 'list' );
		$settings['upload_unaccess']	= array( 'list' );

		$settings = $this->autoduplicatesettings( $settings );

		foreach ( $settings as $k => $v ) {
			if ( isset( $this->settings[$k] ) ) {
				$settings['lists'][$k]	= JHTML::_( 'select.genericlist', $catslist, $k, 'size="1"', 'value', 'text', $this->settings[$k] );
			} else {
				$settings['lists'][$k]	= JHTML::_( 'select.genericlist', $catslist, $k, 'size="1"', 'value', 'text', '' );
			}
		}

		$xsettings = array();

		return array_merge( $xsettings, $settings );
	}

	function relayAction( $request )
	{
		if ( !empty( $this->settings['addaccess' . $request->area] ) || !empty( $this->settings['unaccess' . $request->area] ) ) {
			$this->changeAccess( $request->metaUser->userid, $this->settings['addaccess' . $request->area], $this->settings['unaccess' . $request->area], $request->action );
		}

		if ( !empty( $this->settings['upload_addaccess' . $request->area] ) || !empty( $this->settings['upload_unaccess' . $request->area] ) ) {
			$this->changeUploadAccess( $request->metaUser->userid, $this->settings['upload_addaccess' . $request->area], $this->settings['upload_unaccess' . $request->area], $request->action );
		}
	}

	function changeAccess( $userid, $add, $remove, $act )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `accessuserid`'
				 . ' FROM #__phocadownload_categories'
				 . ' WHERE `id` = \'' . $add . '\'';
		$db->setQuery( $query );

		$paccess = $db->loadResult();

		$pos = strpos( $paccess, $userid );

		if ( $add !== '0' && $pos === false ) {
			$query = 'UPDATE #__phocadownload_categories'
			. ' SET `accessuserid` = CONCAT(`accessuserid`,",", \''. $userid . '\')'
			. ' WHERE `id` = \'' . $add . '\''
			;

			$db->setQuery( $query );
			$db->query();
		}

		$query = 'SELECT `accessuserid`'
				. ' FROM #__phocadownload_categories'
				. ' WHERE `id` = \'' . $remove . '\'';
		$db->setQuery( $query );

		$praccess = $db->loadResult();

		$rpos = strpos( $praccess, $userid );
                 
		if ( $remove !== '0' && $rpos !== false ) {
			$query = 'UPDATE #__phocadownload_categories'
			. ' SET `accessuserid` = TRIM(BOTH \',\' FROM REPLACE(CONCAT(",",`accessuserid`,",") , CONCAT(",",\''. $userid . '\',",") , \',\'))'
			. ' WHERE `id` = \'' . $remove . '\''
			;

			$db->setQuery( $query );
			$db->query();
		} 

	}

	function changeUploadAccess( $userid, $add, $remove, $act )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `uploaduserid`'
				 . ' FROM #__phocadownload_categories'
				 . ' WHERE `id` = \'' . $add . '\'';
		$db->setQuery( $query );

		$paccess = $db->loadResult();

		$pos = strpos( $paccess, $userid );

		if ( $add !== '0' && $pos === false ) {
			$query = 'UPDATE #__phocadownload_categories'
			. ' SET `uploaduserid` = CONCAT(`uploaduserid`,",", \''. $userid . '\')'
			. ' WHERE `id` = \'' . $add . '\''
			;

			$db->setQuery( $query );
			$db->query();
		}

		$query = 'SELECT `uploaduserid`'
				. ' FROM #__phocadownload_categories'
				. ' WHERE `id` = \'' . $remove . '\'';
		$db->setQuery( $query );

		$praccess = $db->loadResult();

		$rpos = strpos( $praccess, $userid );
                 
		if ( $remove !== '0' && $rpos !== false ) {
			$query = 'UPDATE #__phocadownload_categories'
			. ' SET `uploaduserid` = TRIM(BOTH \',\' FROM REPLACE(CONCAT(",",`uploaduserid`,",") , CONCAT(",",\''. $userid . '\',",") , \',\'))'
			. ' WHERE `id` = \'' . $remove . '\''
			;

			$db->setQuery( $query );
			$db->query();
		} 

	}

}
?>
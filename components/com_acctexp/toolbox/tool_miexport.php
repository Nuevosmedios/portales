<?php
/**
 * @version $Id: tool_miexport.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Toolbox - MI Export
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class tool_miexport
{
	function Info()
	{
		$info = array();
		$info['name'] = "Micro Integration Export";
		$info['desc'] = "Export Micro Integration Settings into a format that you can easily import on another server.";

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$settings = array();
		$settings['micro_integrations']				= array( 'list', 'Micro Integration', 'Select one or more MIs to export' );

		// get available micro integrations
		$query = 'SELECT `id` AS value, CONCAT(`name`, " - ", `desc`) AS text'
				. ' FROM #__acctexp_microintegrations'
				. ' WHERE `active` = 1'
			 	. ' AND `hidden` = \'0\''
				. ' ORDER BY ordering'
				;
		$db->setQuery( $query );
		$mi_list = $db->loadObjectList();

		$settings['lists']['micro_integrations'] = JHTML::_('select.genericlist', $mi_list, 'micro_integrations[]', 'size="' . min((count( $mi_list ) + 1), 25) . '" multiple="multiple" style="width:760px;"', 'value', 'text', array());

		return $settings;
	}

	function Action()
	{
		$db = &JFactory::getDBO();

		$app = JFactory::getApplication();

		$list = array();
		if ( empty( $_POST['micro_integrations'] ) ) {
			return null;
		}

		foreach ( $_POST['micro_integrations'] as $mi_id ) {
			$mi = new microIntegration();
			$mi->load( $mi_id );

			$mi->id = 0;

			$mi->clear();

			$list[] = clone( $mi );
		}

		// Generate somewhat unique filename
		$fname = 'aec_mi_export_' . date( 'Y_m_d', ( (int) gmdate('U') ) ) . '_' . ( ( (int) gmdate('U') ) - strtotime( date( 'Y_m_d' ) ) );

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");

		header("Content-Type: application/download");
		header('Content-Disposition: inline; filename="' . $fname . '.mi"');

		echo base64_encode( serialize( $list ) );

		exit;
	}

}
?>

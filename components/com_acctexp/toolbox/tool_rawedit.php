<?php
/**
 * @version $Id: tool_rawedit.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Toolbox - Raw Data Edit
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class tool_rawedit
{
	function Info()
	{
		$info = array();
		$info['name'] = "Raw Data Edit";
		$info['desc'] = "Some Item types (Metauser information, Processors and Invoices) lack a full edit screen. With this, you can at least edit their raw data.";

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$settings = array();

		if ( !empty( $_POST['type'] ) && !empty( $_POST['id'] ) && empty( $_POST['edit'] ) ) {
			$db = &JFactory::getDBO();

			$settings['edit']	= array( 'hidden', 1 );
			$settings['type']	= array( 'hidden', $_POST['type'] );

			$fixed = array();

			switch ( $_POST['type'] ) {
				case 'metauser':
					$fixed = array( 'userid' );

					$object = new metaUserDB();

					$_POST['id'] = $object->getIDbyUserid( $_POST['id'] );
					break;
				case 'processor':
					if ( !is_numeric( $_POST['id'] ) )  {
						$query = 'SELECT `id`'
								. ' FROM #__acctexp_config_processors'
								. ' WHERE `name` = \'' . ( (int) $_POST['id'] ) . '\''
								;
						$db->setQuery( $query );

						$_POST['id'] = $db->loadResult();
					}

					$object = new processor();
					break;
				case 'invoice':
					if ( !is_numeric( $_POST['id'] ) )  {
						$_POST['id'] = AECfetchfromDB::InvoiceIDfromNumber( $_POST['id'] );
					}

					$object = new Invoice();
					break;
			}

			$object->load( $_POST['id'] );

			$vars = get_object_vars( $object );

			$encoded = $object->declareParamFields();

			foreach ( $vars as $k => $v ) {
				if ( is_null( $k ) ) {
					$k = "";
				}

				if ( $k == 'id' ) {
					$settings['id']	= array( 'hidden', $v );
				} elseif ( in_array( $k, $fixed ) ) {
					$settings[$k]	= array( 'p', $k, $k, $v );
				} elseif ( in_array( $k, $encoded ) ) {
					$v = jsoonHandler::encode( $v );

					if ( $v === "null" ) {
						$v = "";
					}

					$settings[$k]	= array( 'inputD', $k, $k, $v );
				} elseif ( strpos( $k, '_' ) !== 0 ) {
					$settings[$k]	= array( 'inputD', $k, $k, $v );
				}
			}
		} else {
			$settings['type']	= array( 'list', 'Item Type', 'The type of Item you want to edit' );
			$settings['id']		= array( 'inputC', 'Item ID', 'Identification for your Item' );

			$types = array( 'metauser' => 'MetaUser Information', 'processor' => 'Payment Processor', 'invoice' => 'Invoice' );

			$typelist = array();
			foreach ( $types as $type => $typename ) {
				$typelist[] = JHTML::_('select.option', $type, $typename );
			}

			$settings['lists']['type'] = JHTML::_('select.genericlist', $typelist, 'type', 'size="3"', 'value', 'text', array());
		}

		return $settings;
	}

	function Action()
	{
		if ( empty( $_POST['edit'] ) ) {
			return null;
		}

		$db = &JFactory::getDBO();

		switch ( $_POST['type'] ) {
			case 'metauser':
				$object = new metaUserDB();
				break;
			case 'processor':
				$object = new processor();
				break;
			case 'invoice':
				$object = new Invoice();
				break;
		}

		$object->load( $_POST['id'] );

		if ( $object->id != $_POST['id'] ) {
			return "<h3>Error - could not find item.</h3>";
		}

		$vars = get_object_vars( $object );

		$encoded = $object->declareParamFields();

		foreach ( $vars as $k => $v ) {
			if ( in_array( $k, $encoded ) ) {
				if ( get_magic_quotes_gpc() ) {
					$object->$k	= jsoonHandler::decode( stripslashes( $_POST[$k] ) );
				} else {
					$object->$k	= jsoonHandler::decode( $_POST[$k] );
				}
			} elseif ( strpos( $k, '_' ) !== 0 ) {
				$object->$k	= $_POST[$k];
			}
		}

		$object->check();

		if ( $object->store() ) {
			return "<h3>Success! Item updated.</h3>";
		} else {
			return "<h3>Error - could not store item.</h3>";
		}
	}

}
?>

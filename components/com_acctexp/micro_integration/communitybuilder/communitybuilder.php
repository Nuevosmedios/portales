<?php
/**
 * @version $Id: mi_communitybuilder.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - CommunityBuilder (CB)
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_communitybuilder
{
	function Info ()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_COMMUNITYBUILDER');
		$info['desc'] = JText::_('AEC_MI_DESC_COMMUNITYBUILDER');
		$info['type'] = array( 'community.social', 'vendor.joomlapolis' );

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$settings = array();
		$settings['approve']		= array( 'toggle' );
		$settings['unapprove_exp']	= array( 'toggle' );
		$settings['set_fields']		= array( 'toggle' );
		$settings['set_fields_exp']	= array( 'toggle' );

		$query = 'SELECT `name`, `title`'
				. ' FROM #__comprofiler_fields'
				. ' WHERE `table` != \'#__users\''
				. ' AND `name` != \'NA\''
				;
		$db->setQuery( $query );
		$objects = $db->loadObjectList();

		if ( !empty( $objects ) ) {
			foreach ( $objects as $object ) {
				if ( strpos( $object->title, '_' ) === 0 ) {
					$title = $object->name;
				} else {
					$title = $object->title;
				}

				$settings['cbfield_' . $object->name] = array( 'inputE', $title, $title );
				$expname = $title . " "  . JText::_('MI_MI_COMMUNITYBUILDER_EXPMARKER');
				$settings['cbfield_' . $object->name . '_exp' ] = array( 'inputE', $expname, $expname );
			}
		}

		$rewriteswitches	= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

		$settings			= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		if( $this->settings['approve'] ) {
			$query = 'UPDATE #__comprofiler'
					.' SET `approved` = \'1\''
					.' WHERE `user_id` = \'' . (int) $request->metaUser->userid . '\''
					;
			$db->setQuery( $query );
			$db->query() or die( $db->stderr() );
		}

		if ( $this->settings['set_fields'] ) {
			$this->setFields( $request );
		}
	}

	function expiration_action( $request )
	{
		$db = &JFactory::getDBO();

		if( $this->settings['unapprove_exp'] ) {
			$query = 'UPDATE #__comprofiler'
					.' SET `approved` = \'0\''
					.' WHERE `user_id` = \'' . (int) $request->metaUser->userid . '\''
					;
			$db->setQuery( $query );
			$db->query() or die( $db->stderr() );
		}

		if ( $this->settings['set_fields_exp'] ) {
			$this->setFields( $request, '_exp' );
		}
	}

	function setFields( $request, $stage="" )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `name`, `title`'
				. ' FROM #__comprofiler_fields'
				. ' WHERE `table` != \'#__users\''
				. ' AND `name` != \'NA\''
				;
		$db->setQuery( $query );
		$objects = $db->loadObjectList();

		$changes = array();
		foreach ( $objects as $object ) {
			$key = 'cbfield_' . $object->name . $stage;

			if ( !empty( $this->settings[$key] ) ) {
				$changes[$object->name] = $this->settings[$key];
			}
		}

		if ( !empty( $changes ) ) {
			$alterstring = array();
			foreach ( $changes as $name => $value ) {
				if ( ( $value === 0 ) || ( $value === "0" ) ) {
					$alterstring[] = "`" . $name . "`" . ' = \'0\'';
				} elseif ( ( $value === 1 ) || ( $value === "1" ) ) {
					$alterstring[] = "`" . $name . "`" . ' = \'1\'';
				} elseif ( strcmp( $value, 'NULL' ) === 0 ) {
					$alterstring[] = "`" . $name . "`" . ' = NULL';
				} else {
					$alterstring[] = "`" . $name . "`" . ' = \'' . AECToolbox::rewriteEngineRQ( $value, $request ) . '\'';
				}
			}

			$query = 'UPDATE #__comprofiler'
					. ' SET ' . implode( ', ', $alterstring )
					. ' WHERE `user_id` = \'' . (int) $request->metaUser->userid . '\''
					;
			$db->setQuery( $query );
			$db->query() or die( $db->stderr() );
		}
	}

}
?>

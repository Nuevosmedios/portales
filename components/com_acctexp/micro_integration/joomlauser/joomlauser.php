<?php
/**
 * @version $Id: mi_joomlauser.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Joomla User
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_joomlauser
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_JOOMLAUSER');
		$info['desc'] = JText::_('AEC_MI_DESC_JOOMLAUSER');
		$info['type'] = array( 'joomla.user', 'community.profile', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['activate']		= array( 'toggle' );
		$settings['block']			= array( 'toggle' );
		$settings['username']		= array( 'inputD' );
		$settings['username_rand']	= array( 'inputC' );
		$settings['password']		= array( 'inputD' );

		$xsettings = array();
		if ( defined( 'JPATH_MANIFESTS' ) ) {
			$settings['set_fields']		= array( 'toggle' );

			$db = &JFactory::getDBO();

			$query = 'SELECT DISTINCT `profile_key`'
					. ' FROM #__user_profiles';
			$db->setQuery( $query );
			$pkeys = xJ::getDBArray( $db );

			if ( !empty( $pkeys ) ) {
				foreach ( $pkeys as $k ) {
					$title = ucfirst( str_replace( 'profile.', '', $k ) );

					$settings['jprofile_' . str_replace( ".", "_", $k )] = array( 'inputE', $title, $title );
					$expname = $title . " "  . JText::_('MI_MI_JOOMLAUSER_EXPMARKER');
					$xsettings['jprofile_' . str_replace( ".", "_", $k ) . '_exp' ] = array( 'inputE', $expname, $expname );
				}
			}
		}

		$rewriteswitches				= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		$settings['aectab_reg']			= array( 'tab', 'Expiration', 'Expiration' );

		$settings['set_fields_exp']	= array( 'toggle' );

		$settings = array_merge( $settings, $xsettings );

		$settings					= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		$set = array();

		if ( $this->settings['activate'] ) {
			$set[] = '`block` = \'0\'';
			$set[] = '`activation` = \'\'';
		}

		$username = $this->getUsername( $request );

		if ( !empty( $username ) ) {
			$set[] = '`username` = \'' . $username . '\'';
		}

		if ( !empty( $this->settings['password'] ) ) {
			$pw = AECToolbox::rewriteEngineRQ( $this->settings['password'], $request );

			jimport('joomla.user.helper');

			$salt  = JUserHelper::genRandomPassword( 32 );
			$crypt = JUserHelper::getCryptedPassword( $pw, $salt );
			$password = $crypt.':'.$salt;

			$set[] = '`password` = \'' . $password . '\'';
		}

		if ( !empty( $set ) ) {
			$query = 'UPDATE #__users';
			$query .= ' SET ' . implode( ', ', $set );
			$query .= ' WHERE `id` = \'' . (int) $request->metaUser->userid . '\'';

			$db->setQuery( $query );
			$db->query() or die( $db->stderr() );

			$userid = $request->metaUser->userid;

			// Reloading metaUser object for other MIs
			$request->metaUser = new metaUser( $userid );
		}

		if ( !empty( $this->settings['set_fields'] ) ) {
			$this->setFields( $request );
		}
	}

	function getUsername( $request )
	{
		if ( !empty( $this->settings['username_rand'] ) ) {
			$db = &JFactory::getDBO();

			$numberofrows	= 1;
			while ( $numberofrows ) {
				$uname =	strtolower( substr( base64_encode( md5( rand() ) ), 0, $this->settings['username_rand'] ) );
				// Check if already exists
				$query = 'SELECT count(*)'
						. ' FROM #__users'
						. ' WHERE `username` = \'' . $uname . '\''
						;
				$db->setQuery( $query );
				$numberofrows = $db->loadResult();
			}

			return $uname;
		} elseif ( !empty( $this->settings['username'] ) ) {
			return AECToolbox::rewriteEngineRQ( $this->settings['username'], $request );
		}
	}

	function expiration_action( $request )
	{
		if ( $this->settings['block'] ) {
			$db = &JFactory::getDBO();

			$query = 'UPDATE #__users'
				. ' SET `block` = \'1\''
				. ' WHERE `id` = \'' . (int) $request->metaUser->userid . '\''
				;

			$db->setQuery( $query );
			$db->query() or die( $db->stderr() );
		}

		if ( !empty( $this->settings['set_fields_exp'] ) ) {
			$this->setFields( $request, '_exp' );
		}
	}

	function setFields( $request, $stage="" )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `profile_key`, `profile_value`'
				. ' FROM #__user_profiles'
				. ' WHERE `user_id` = \'' . $request->metaUser->userid . '\'';
		$db->setQuery( $query );
		$objects = $db->loadObjectList();

		$changes = $additions = array();

		foreach ( $this->settings as $k => $v ) {
			if ( strpos($k, 'jprofile_') !== false ) {
				if ( $stage == '_exp' ) {
					if ( strpos($k, '_exp') === false ) {
						continue;
					}
				} else {
					if ( strpos($k, '_exp') !== false ) {
						continue;
					}
				}

				if ( empty( $v ) ) {
					continue;
				}

				if ( ( $v === 0 ) || ( $v === "0" ) ) {
					$v = '\'0\'';
				} elseif ( ( $v === 1 ) || ( $v === "1" ) ) {
					$v = '\'1\'';
				} elseif ( strcmp( $v, 'NULL' ) === 0 ) {
					$v = 'NULL';
				} else {
					$v = '\'' . AECToolbox::rewriteEngineRQ( $v, $request ) . '\'';
				}

				$f = false;
				foreach ( $objects as $object ) {
					if ( $k == 'jprofile_' . str_replace( ".", "_", $object->profile_key ) . $stage ) {
						$changes[$object->profile_key] = $v;
						$f = true;
					}
				}

				if ( !$f ) {
					$key = str_replace( array( "jprofile_", '_exp', "_" ), array( "", "", "." ), $k );
					$additions[$key] = $v;
				}
			}
		}

		if ( !empty( $changes ) ) {
			foreach ( $changes as $name => $value ) {
				$query = 'UPDATE #__user_profiles'
						. ' SET `profile_value` = ' . $value
						. ' WHERE `user_id` = \'' . (int) $request->metaUser->userid . '\''
						. ' AND `profile_key` = \'' . $name . '\''
						;
				$db->setQuery( $query );
				$db->query() or die( $db->stderr() );
			}
		}

		if ( !empty( $additions ) ) {
			$query = 'SELECT MAX(ordering)'
					. ' FROM #__user_profiles'
					. ' WHERE `user_id` = \'' . $request->metaUser->userid . '\'';
			$db->setQuery( $query );
			$order = $db->loadResult();

			if ( empty( $order ) ) {
				$order = 0;
			}

			$values = array();
			foreach ( $additions as $name => $value ) {
				$values[] = '('.((int) $request->metaUser->userid).', '.$db->quote($name).', '.$value.', '.$order++.')';
			}

			$query = 'INSERT INTO #__user_profiles VALUES '.implode(', ', $values);

			$db->setQuery($query);
			$db->query() or die( $db->stderr() );
		}
	}

}

?>
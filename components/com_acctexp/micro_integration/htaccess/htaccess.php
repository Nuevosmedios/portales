<?php
/**
 * @version $Id: mi_htaccess.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - .htaccess
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_htaccess extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_HTACCESS');
		$info['desc'] = JText::_('AEC_MI_DESC_HTACCESS');
		$info['type'] = array( 'basic.server', 'system', 'vendor.valanx' );

		return $info;
	}

	function mi_htaccess()
	{
		include_once( JPATH_SITE . '/components/com_acctexp/micro_integration/htaccess/lib/htaccess.class.php' );
	}

	function checkInstallation()
	{
		$db = &JFactory::getDBO();

		$app = JFactory::getApplication();

		$tables	= array();
		$tables	= $db->getTableList();

		return in_array( $app->getCfg( 'dbprefix' ) .'acctexp_mi_htaccess_apachepw', $tables );
	}

	function install()
	{
		$db = &JFactory::getDBO();

		$query = 'CREATE TABLE IF NOT EXISTS `#__acctexp_mi_htaccess_apachepw`'
		. ' (`id` int(11) NOT NULL auto_increment,'
		. '`userid` int(11) NOT NULL,'
		. '`apachepw` varchar(255) NOT NULL default \'1\','
		. ' PRIMARY KEY (`id`)'
		. ')'
		;
		$db->setQuery( $query );
		$db->query();
		return;
	}

	function Settings()
	{
		$settings = array();
		// field type; name; variable value, description, extra (variable name)
		$settings['mi_folder']			= array( 'inputD' );
		$settings['mi_passwordfolder']	= array( 'inputD' );
		$settings['mi_name']			= array( 'inputC' );
		$settings['use_md5']			= array( 'toggle' );
		$settings['use_apachemd5']		= array( 'toggle' );
		$settings['rebuild']			= array( 'toggle' );
		$settings['remove']				= array( 'toggle' );

		return $settings;
	}

	function saveparams( $params )
	{
		$db = &JFactory::getDBO();

		$newparams = $params;

		// Rewrite foldername to include cmsroot directory
		if ( strpos( $params['mi_folder'], "[cmsroot]" ) !== false ) {
			$newparams['mi_folder'] = str_replace("[cmsroot]", JPATH_SITE, $params['mi_folder']);
		}

		if ( strpos( $params['mi_folder'], "[abovecmsroot]" ) !== false ) {
			$newparams['mi_folder'] = str_replace("[abovecmsroot]", JPATH_SITE . "/..", $params['mi_folder']);
		}

		if ( strpos( $params['mi_passwordfolder'], "[abovecmsroot]" ) !== false ) {
			$newparams['mi_passwordfolder'] = str_replace("[abovecmsroot]", JPATH_SITE . "/..", $params['mi_passwordfolder']);
		}

		$newparams['mi_folder_fullpath']		= $newparams['mi_folder'] . "/.htaccess";
		$newparams['mi_folder_user_fullpath']	= $newparams['mi_passwordfolder'] . "/.htuser" . str_replace( "/", "_", str_replace( ".", "/", $newparams['mi_folder'] ) );

		if ( ( !file_exists( $newparams['mi_folder_fullpath'] ) || !file_exists( $newparams['mi_folder_user_fullpath'] ) ) || $params['rebuild'] ) {
			$ht = $this->getHTAccess( $newparams );

			$ht->addLogin();

			$newparams['rebuild'] = 0;
		}

		return $newparams;
	}

	function expiration_action( $request )
	{
		$db = &JFactory::getDBO();

		$ht = $this->getHTAccess( $this->settings );
		$ht->delUser( $request->metaUser->cmsUser->username );
	}

	function action( $request )
	{
		$ht = $this->getHTAccess( $this->settings );

		if ( $this->settings['use_md5'] ) {
			$ht->addUser( $request->metaUser->cmsUser->username, $request->metaUser->cmsUser->password );
		} else {
			$apachepw = $this->getApachePW( $request->metaUser->userid );

			if ( !empty( $apachepw->id ) ) {
				$ht->addUser( $request->metaUser->cmsUser->username, $apachepw->apachepw );
			}
		}

		$ht->addLogin();

		return true;
	}

	function on_userchange_action( $request )
	{
		$password = $this->getPWrequest( $request );

		if ( empty( $password ) ) {
			return null;
		}

		$apachepw = $this->getApachePW( $request->row->id );

		$apachepw->apachepw = $this->makePassword( $password );

		$apachepw->check();
		$apachepw->store();

		if ( !( strcmp( $request->trace, 'registration' ) === 0 ) ) {
			$ht = $this->getHTAccess( $this->settings );

			$userlist = $ht->getUsers();

			if ( in_array( $request->row->username, $userlist ) ) {
				$ht->delUser( $request->row->username );

				if ( $this->settings['use_md5'] ) {
					$ht->addUser( $request->row->username, $request->row->password );
				} else {
					$ht->addUser( $request->row->username, $apachepw->apachepw );
				}

				$ht->addLogin();
			}
		}

		return true;
	}

	function getHTAccess( $settings )
	{
		$htaccess = new htaccess();
		$htaccess->setFPasswd( $settings['mi_folder_user_fullpath'] );
		$htaccess->setFHtaccess( $settings['mi_folder_fullpath'] );

		if ( !empty( $settings['mi_name'] ) ) {
			$htaccess->setAuthName( $settings['mi_name'] );
		}

		return $htaccess;
	}

	function getApachePW( $userid )
	{
		$db = &JFactory::getDBO();

		$apachepw = new apachepw();
		$apwid = $apachepw->getIDbyUserID( $userid );

		if ( $apwid ) {
			$apachepw->load( $apwid );
		} else {
			$apachepw->load(0);
			$apachepw->userid = $userid;
		}

		return $apachepw;
	}

	function makePassword( $cleartext )
	{
		if ( !empty( $this->settings['use_apachemd5'] ) ) {
			return $this->crypt_apr1_md5( $cleartext );
		} elseif( $this->settings['use_md5'] ) {
			return md5( $cleartext );
		} else {
			return crypt( $cleartext );
		}
	}

	function crypt_apr1_md5( $plainpasswd )
	{
		$salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);

		$len = strlen($plainpasswd);

		$text = $plainpasswd.'$apr1$'.$salt;

		$bin = pack("H32", md5($plainpasswd.$salt.$plainpasswd));

		for ( $i=$len; $i>0; $i-=16 ) {
			$text .= substr($bin, 0, min(16, $i));
		}

		for ( $i=$len; $i>0; $i>>=1 ) {
			$text .= ($i & 1) ? chr(0) : $plainpasswd{0};
		}

		$bin = pack("H32", md5($text));

		for ( $i = 0; $i < 1000; $i++ ) {
			$new = ($i & 1) ? $plainpasswd : $bin;
			if ($i % 3) $new .= $salt;
			if ($i % 7) $new .= $plainpasswd;
			$new .= ($i & 1) ? $bin : $plainpasswd;
			$bin = pack("H32", md5($new));
		}

		$tmp = "";
		for ( $i = 0; $i < 5; $i++ ) {
			$k = $i + 6;
			$j = $i + 12;
			if ($j == 16) $j = 5;
			$tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
		}

		$tmp = chr(0).chr(0).$bin[11].$tmp;

		$tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
		"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
		"./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");

		return "$"."apr1"."$".$salt."$".$tmp;
	}

}

class apachepw extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $userid 			= null;
	/** @var string */
	var $apachepw			= null;

	function apachepw()
	{
		parent::__construct( '#__acctexp_mi_htaccess_apachepw', 'id' );
	}

	function getIDbyUserID( $userid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_mi_htaccess_apachepw'
				. ' WHERE `userid` = \'' . $userid . '\''
				;
		$db->setQuery( $query );
		return $db->loadResult();
	}
}
?>

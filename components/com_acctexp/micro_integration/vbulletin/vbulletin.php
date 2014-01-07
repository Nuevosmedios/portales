<?php
/**
 * @version $Id: mi_vbulletin.php
 * @package AEC - Account Control Expiration - Subscription component for Joomla! OS CMS
 * @subpackage Micro Integrations - vBulletin
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_vbulletin
{

	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_VBULLETIN');
		$info['desc'] = JText::_('AEC_MI_DESC_VBULLETIN');
		$info['type'] = array( 'communication.forum', 'vendor.vbulletin' );

		return $info;
	}

	function checkInstallation()
	{
		$db = &JFactory::getDBO();

		$conf =& JFactory::getConfig();

		$tables	= array();
		$tables	= $db->getTableList();

		return in_array( $conf->getValue('config.dbprefix') .'_acctexp_mi_vbulletinpw', $tables );
	}

	function install()
	{
		$db = &JFactory::getDBO();

		$query = 'CREATE TABLE IF NOT EXISTS `#__acctexp_mi_vbulletinpw`'
		. ' (`id` int(11) NOT NULL auto_increment,'
		. '`userid` int(11) NOT NULL,'
		. '`vbulletinpw` varchar(255) NOT NULL default \'1\','
		. '`vbulletinsalt` varchar(255) NOT NULL default \'1\','
		. ' PRIMARY KEY (`id`)'
		. ')'
		;
		$db->setQuery( $query );
		$db->query();
		return;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$vbdb = $this->getDB();

		if ( !empty( $this->settings['table_prefix'] ) ) {
			$prefix = $this->settings['table_prefix'];
		} else {
			$prefix = 'vb_';
		}

		$query = 'SELECT `usergroupid`, `title`'
			 	. ' FROM ' . $prefix . 'usergroup'
			 	;
	 	$vbdb->setQuery( $query );
	 	$groups = $vbdb->loadObjectList();

		$sg		= array();
		if ( !empty( $groups ) ) {
			foreach ( $groups as $group ) {
				$sg[] = JHTML::_('select.option', $group->usergroupid, $group->title );
			}
		}

		$settings = array();

		$settings['rebuild']		= array( 'toggle' );
		$settings['remove']			= array( 'toggle' );

		$settings['aectab_dat']		= array( 'tab', 'Database', 'Database' );
		$settings['use_altdb']		= array( 'toggle' );
		$settings['dbms']			= array( 'inputC' );
		$settings['dbhost']			= array( 'inputC' );
		$settings['dbuser']			= array( 'inputC' );
		$settings['dbpasswd']		= array( 'inputC' );
		$settings['dbname']			= array( 'inputC' );
		$settings['table_prefix']	= array( 'inputC' );

		$s = array( 'group', 'displaygroup', 'add_secondarygroups', 'remove_secondarygroups', 'group_exp', 'displaygroup_exp', 'add_secondarygroups_exp', 'remove_secondarygroups_exp' );

		$settings['aectab_act']		= array( 'tab', 'Groups', 'Groups' );

		$exp = false;
		foreach ( $s as $si ) {
			if ( strpos( $si, '_exp' ) && !$exp ) {
				$exp = true;

				$settings['aectab_exp']		= array( 'tab', 'Groups (Expiration)', 'Groups (Expiration)' );
			}

			$settings['set_'.$si]	= array( 'toggle' );
			$settings[$si]			= array( 'list' );

			$v = null;
			if ( isset( $this->settings[$si] ) ) {
				$v = $this->settings[$si];
			}

			if ( strpos( $si, 'secondarygroups' ) ) {
				$settings['lists'][$si]	= JHTML::_( 'select.genericlist', $sg, $si.'[]', 'size="10" multiple="true"', 'value', 'text', $v );
			} else {
				if ( is_array( $v ) ) {
					$v = $v[0];
				}

				$settings['lists'][$si]	= JHTML::_( 'select.genericlist', $sg, $si, 'size="10"', 'value', 'text', $v );
			}
		}

		$userfields = $this->getUserFields( $vbdb );

		if ( !empty( $userfields ) ) {
			$settings['aectab_cuu']				= array( 'tab', 'Create User', 'Create User' );
			$settings['create_user']			= array( 'toggle' );

			foreach ( $userfields as $key ) {
				$ndesc = JText::_('MI_MI_VBULLETIN_CREATE_FIELD') . ": " . $key;

				$settings['create_user_'.$key]	= array( 'inputC', $ndesc, $ndesc );
			}

			$settings['aectab_uuu']				= array( 'tab', 'Update User', 'Update User' );
			$settings['update_user']			= array( 'toggle' );

			foreach ( $userfields as $key ) {
				$ndesc = JText::_('MI_MI_VBULLETIN_UPDATE_FIELD') . ": " . $key;

				$settings['update_user_'.$key]	= array( 'inputC', $ndesc, $ndesc );
			}

			$settings['update_user_exp']		= array( 'toggle' );

			foreach ( $userfields as $key ) {
				$ndesc = JText::_('MI_MI_VBULLETIN_UPDATE_FIELD_EXP') . ": " . $key;

				$settings['update_user_exp_'.$key]	= array( 'inputC', $ndesc, $ndesc );
			}
		}

		return $settings;
	}

	function Defaults()
	{
		$settings = array();

		$settings['use_altdb']		= 0;
		$settings['dbms']			= 'mysqli';
		$settings['dbhost']			= 'localhost';
		$settings['dbuser']			= '';
		$settings['dbpasswd']		= '';
		$settings['dbname']			= '';
		$settings['table_prefix']	= 'vb_';

		return $settings;
	}

	function action( $request )
	{
		$app = JFactory::getApplication();

		$db = &JFactory::getDBO();

		$vbdb = $this->getDB();

		$vbUserId = $this->vbUserid( $vbdb, $request->metaUser->cmsUser->email );

		if ( empty( $vbUserId ) && empty( $this->settings['create_user'] ) ) {
			return null;
		} elseif ( empty( $vbUserId ) ) {
			$vbulletinpw = new vbulletinpw();
			$vbulletinpw->loadUserID( $request->metaUser->userid );

			$password = $vbulletinpw->vbulletinpw;

			$fields = $this->getUserFields( $vbdb );

			$content = array();
			if ( !empty( $fields ) ) {
				foreach ( $fields as $key ) {
					if ( !empty( $this->settings['create_user_'.$key] ) ) {
						$content[$key] = $this->settings['create_user_'.$key];
					}
				}
			}

			$content['joindate']		= (int) gmdate('U');
			$content['passworddate']	= date( 'Y-m-d', ( (int) gmdate('U') ) );
			$content['usertitle']		= 'Junior Member';

			if ( empty( $content['username'] ) ) {
				$content['username']	= $request->metaUser->cmsUser->username;
			}

			$content['password']		= $vbulletinpw->vbulletinpw;
			$content['salt']			= $vbulletinpw->vbulletinsalt;
			$content['email']			= $request->metaUser->cmsUser->email;

			$this->createUser( $vbdb, $content );

			$vbUserId = $this->vbUserid( $vbdb, $request->metaUser->cmsUser->email );
		} elseif ( $this->settings['update_user'] ) {
			$fields = $this->getUserFields( $vbdb );

			$content = array();
			foreach ( $fields as $key ) {
				if ( !empty( $this->settings['update_user_'.$key] ) ) {
					$content[$key] = $this->settings['update_user_'.$key];
				}
			}

			$this->updateUser( $vbdb, $content );
		}

		if ( $vbUserId ) {
			$this->updateGroups( $vbdb, $vbUserId );
		}

		return true;
	}

	function expiration_action( $request )
	{
		$vbdb = $this->getDB();

		$vbUserId = $this->vbUserid( $vbdb, $request->metaUser->cmsUser->email );

		if ( empty( $vbUserId ) && empty( $this->settings['create_user'] ) ) {
			return null;
		}

		if ( $vbUserId ) {
			$this->updateGroups( $vbdb, $vbUserId, '_exp' );
		}

		return true;
	}

	function userGroups( $db, $userid )
	{
		$query = 'SELECT `userid`, `usergroupid`, `membergroupids`, `displaygroupid`'
				. ' FROM ' . $this->settings['table_prefix'] . 'user'
				. ' WHERE `userid` = \'' . $userid . '\''
				;
		$db->setQuery( $query );

		return $db->loadObject();
	}

	function updateGroups( $db, $userid, $suffix="" )
	{
		$user = $this->userGroups( $db, $userid );

		if ( !empty( $user->membergroupids ) ) {
			$user->membergroupids = explode( ',', $user->membergroupids );
		} else {
			$user->membergroupids = array();
		}

		$change = false;

		$s = array( 'group', 'displaygroup', 'add_secondarygroups', 'remove_secondarygroups' );

		foreach ( $s as $setting ) {
			$set = $setting.$suffix;

			if ( !empty( $this->settings[$set] ) && !empty( $this->settings['set_'.$set] ) ) {
				switch ( $setting ) {
					case 'group':
						if ( is_array( $this->settings[$set] ) ) {
							if ( !is_array( $this->settings['add_secondarygroups'.$suffix] ) ) {
								$this->settings['add_secondarygroups'.$suffix] = array();
							} 

							$this->settings['set_add_secondarygroups'.$suffix] = true;

							$this->settings['add_secondarygroups'.$suffix] = array_merge( $this->settings['add_secondarygroups'.$suffix], $this->settings[$set] );

							$this->settings['add_secondarygroups'.$suffix] = array_unique( $this->settings['add_secondarygroups'.$suffix] );

							$this->settings[$set] = $this->settings[$set][0];
						}

						if ( $user->usergroupid !== $this->settings[$set] ) {
							$change = true;

							$user->usergroupid = $this->settings[$set];
						}

						if ( !in_array( $this->settings[$set], $user->membergroupids ) ) {
							$change = true;

							$user->membergroupids[] = $this->settings[$set];
						}
						break;
					case 'displaygroup':
						if ( is_array( $this->settings[$set] ) ) {
							$this->settings[$set] = $this->settings[$set][0];
						}

						if ( $user->displaygrouppid !== $this->settings[$set] ) {
							$change = true;

							$user->displaygroupid = $this->settings[$set];
						}

						if ( !in_array( $this->settings[$set], $user->membergroupids ) ) {
							$change = true;

							$user->membergroupids[] = $this->settings[$set];
						}
						break;
					default:
						$groups = array();

						if ( strpos( $set, 'add_' ) !== false ) {
							foreach ( $this->settings[$set] as $group ) {
								if ( !in_array( $group, $user->membergroupids ) ) {
									$groups[] = $group;
								}
							}

							if ( !empty( $groups ) ) {
								$change = true;

								$user->membergroupids = array_merge( $user->membergroupids, $groups );

								asort( $user->membergroupids );
							}
						} else {
							foreach ( $this->settings[$set] as $group ) {
								if ( in_array( $group, $user->membergroupids ) ) {
									$change = true;

									unset( $user->membergroupids[array_search( $group, $user->membergroupids )] );
								}
							}
						}
						break;
				}
			}
		}

		if ( $change ) {
			if ( is_array( $user->usergroupid ) ) {
				$user->usergroupid = $user->usergroupid[0];
			}

			if ( is_array( $user->displaygroupid ) ) {
				$user->displaygroupid = $user->displaygroupid[0];
			}

			$query = 'UPDATE ' . $this->settings['table_prefix'] . 'user'
					. ' SET ' . '`usergroupid` = \'' . $user->usergroupid . '\','
					. ' `membergroupids` = \'' . implode( ',', $user->membergroupids ) . '\','
					. ' `displaygroupid` = \'' . $user->displaygroupid . '\''
					. ' WHERE `userid` = \'' . $user->userid . '\''
					;
			$db->setQuery( $query );

			$db->query();
		}

		return true;
	}

	function vbUserid( $db, $email )
	{
		$query = 'SELECT `userid`'
				. ' FROM ' . $this->settings['table_prefix'] . 'user'
				. ' WHERE LOWER( `email` ) = \'' . $email . '\''
				;
		$db->setQuery( $query );

		return $db->loadResult();
	}

	function createUser( $db, $fields )
	{
		$query = 'INSERT INTO ' . $this->settings['table_prefix'] . 'user'
				. ' (`' . implode( '`, `', array_keys( $fields ) ) . '`)'
				. ' VALUES ( \'' . implode( '\', \'', array_values( $fields ) ) . '\' )'
				;
		$db->setQuery( $query );

		$db->query();
	}

	function updateUser( $db, $userid, $fields )
	{
		$set = array();
		foreach ( $fields as $key => $value ) {
			if ( !empty( $value ) ) {
				$set[] = '`' . $key . '` = \'' . $value . '\'';
			}
		}

		$query = 'UPDATE ' . $this->settings['table_prefix'] . 'user'
				. ' SET ' . implode( ', ', $set )
				. ' WHERE `userid` = \'' . $userid . '\''
				;
		$db->setQuery( $query );

		return $db->query();
	}

	function getUserFields( $db )
	{
		// Exclude all standard fields so that we can write possible custom fields
		$excluded = array(	"userid", "usergroupid", "membergroupids", "displaygroupid", "username", "password", "passworddate", "email",
							"styleid", "parentemail", "icq", "aim", "yahoo", "msn", "skype", "showvbcode",
							"showbirthday", "customtitle", "joindate", "daysprune", "lastvisit", "lastactivity", "lastpost",
							"lastpostid", "posts", "reputation", "reputationlevelid", "timezoneoffset", "pmpopup", "avatarid", "avatarrevision",
							"profilepicrevision", "sigpicrevision", "options", "birthday_search", "maxposts", "startofweek", "ipaddress", "referrerid",
							"languageid", "emailstamp", "threadedmode", "autosubscribe", "pmtotal", "pmunread", "salt", "ipoints",
							"infractions", "warnings", "infractiongroupids", "infractiongroupid", "adminoptions", "profilevisits", "friendcount", "friendreqcount",
							"vmunreadcount", "vmmoderatedcount", "socgroupinvitecount", "socgroupreqcount", "pcunreadcount", "pcmoderatedcount", "gmmoderatedcount", "importuserid"
							);

		$query = 'SHOW COLUMNS FROM #__user';
		$db->setQuery( $query );

		$fields = xJ::getDBArray( $db );

		$return = array();
		if ( !empty( $fields ) ) {
			foreach ( $fields as $key ) {
				if ( !in_array( $key, $excluded ) ) {
					$return[] = $key;
				}
			}
		}

		return $return;
	}

	function getDB()
	{
        if ( !empty( $this->settings['use_altdb'] ) ) {
	        $options = array(	'driver'	=> $this->settings['dbms'],
								'host'		=> $this->settings['dbhost'],
								'user'		=> $this->settings['dbuser'],
								'password'	=> $this->settings['dbpasswd'],
								'database'	=> $this->settings['dbname'],
								'prefix'	=> $this->settings['table_prefix']
								);

	        $db = &JDatabase::getInstance($options);
        } else {
        	$db = &JFactory::getDBO();
        }

		return $db;
	}

	function on_userchange_action( $request )
	{
		$db = &JFactory::getDBO();

		$vbulletinpw = new vbulletinpw();
		$apwid = $vbulletinpw->getIDbyUserID( $request->row->id );

		if ( $apwid ) {
			$vbulletinpw->load( $apwid );
		} else {
			$vbulletinpw->load(0);
			$vbulletinpw->userid = $request->row->id;
		}

		if ( isset( $request->post['password_clear'] ) ) {
			$password = crypt( $request->post['password_clear'] );

		} elseif ( !empty( $request->post['password'] ) ) {
			$password = $request->post['password'];
		} elseif ( !empty( $request->post['password2'] ) ) {
			$password = $request->post['password2'];
		} elseif ( !$apwid ) {
			// No new password and no existing password - nothing to be done here
			return;
		}

		if ( !empty( $password ) ) {
			$vbulletinpw->vbulletinsalt	= xJ::escape( $db, $vbulletinpw->saltgen() );
			$vbulletinpw->vbulletinpw	= $vbulletinpw->hash( $password, $vbulletinpw->vbulletinsalt );

			$vbulletinpw->check();
			$vbulletinpw->store();
		}

		return true;
	}

}

class vbulletinpw extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $userid 			= null;
	/** @var string */
	var $vbulletinpw		= null;
	/** @var string */
	var $vbulletinsalt		= null;

	function vbulletinpw()
	{
		parent::__construct( '#__acctexp_mi_vbulletinpw', 'id' );
	}

	function loadUserID( $userid )
	{
		$uid = $this->getIDbyUserID( $userid );

		return $this->load( $uid );
	}

	function getIDbyUserID( $userid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_mi_vbulletinpw'
				. ' WHERE `userid` = \'' . $userid . '\''
				;
		$db->setQuery( $query );
		return $db->loadResult();
	}

	function hash( $password, $salt )
	{
		return md5( md5( $password ) . $salt );
	}

	function saltgen( $length=16 )
	{
		return AECToolbox::randomstring( $length, true );
	}
}

?>
<?php
/**
 * @version $Id: acctexp.registration.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class aecRegistration
{
	function registerRedirect( $intro, $plan )
	{
		$_POST['intro'] = $intro;

		// The plans are supposed to be first, so the details form should hold the values
		if ( !empty( $plan['id'] ) ) {
			$_POST['usage']		= $plan['id'];
			$_POST['processor']	= $plan['gw'][0]->processor_name;

			if ( isset( $plan['gw'][0]->recurring ) ) {
				$_POST['recurring']	= $plan['gw'][0]->recurring;
			}
		}

		// Send to registration handler
		if ( GeneralInfoRequester::detect_component( 'anyCB' ) ) {
			aecRegistration::registerRedirectCB( $plan );
		} elseif ( GeneralInfoRequester::detect_component( 'JUSER' ) ) {
			aecRegistration::registerRedirectJUser();
		} elseif ( GeneralInfoRequester::detect_component( 'JOMSOCIAL' ) ) {
			aecRegistration::registerRedirectJomSocial( $plan );
		} else {
			aecRegistration::registerRedirectJoomla( $plan );
		}
	}

	function registerRedirectJoomla( $plan )
	{
		$app = JFactory::getApplication();

		if ( isset( $plan['gw'][0]->recurring ) ) {
			$recurring = $plan['gw'][0]->recurring;
		} else {
			$recurring = 0;
		}

		if ( defined( 'JPATH_MANIFESTS' ) ) {
			$app->redirect( 'index.php?option=com_users&view=registration&usage=' . $plan['id'] . '&processor=' . $plan['gw'][0]->processor_name . '&recurring=' . $recurring );
		} else {
			$app->redirect( 'index.php?option=com_user&view=register&usage=' . $plan['id'] . '&processor=' . $plan['gw'][0]->processor_name . '&recurring=' . $recurring );
		}
	}

	function registerRedirectCB( $plan )
	{
		if ( GeneralInfoRequester::detect_component( 'CB1.2' ) ) {
			TempTokenHandler::TempTokenFromPlan( $plan );

			if ( !empty( $_GET['fname'] ) ) {
				setcookie( "fname", $_GET['fname'], ( (int) gmdate('U') )+60*10 );
			}

			if ( !empty( $_GET['femail'] ) ) {
				setcookie( "femail", $_GET['femail'], ( (int) gmdate('U') )+60*10 );
			}

			aecRedirect( 'index.php?option=com_comprofiler&task=registers' );
		} else {
			global $task, $_PLUGINS, $ueConfig, $_CB_database;;

			$app = JFactory::getApplication();

			$savetask	= $task;
			$_REQUEST['task'] = 'done';

			include_once( JPATH_SITE . '/components/com_comprofiler/comprofiler.php' );
			include_once( JPATH_SITE . '/components/com_comprofiler/comprofiler.html.php' );

			$task = $savetask;

			registerForm( 'com_acctexp', $app->getCfg( 'emailpass' ), null );
		}
	}

	function registerRedirectJUser()
	{
		global $task;

		$savetask	= $task;
		$task		= 'blind';

		include_once( JPATH_SITE . '/components/com_juser/juser.html.php' );
		include_once( JPATH_SITE . '/components/com_juser/juser.php' );

		$task = $savetask;

		userRegistration( 'com_acctexp', null );
	}

	function registerRedirectJomSocial( $plan )
	{
		TempTokenHandler::TempTokenFromPlan( $plan );

		aecRedirect( 'index.php?option=com_community&view=register' );
	}

	function saveUserRegistration( $var, $internal=false, $overrideActivation=false, $overrideEmails=false, $overrideJS=false )
	{
		$db = &JFactory::getDBO();

		global $task, $aecConfig;

		$app = JFactory::getApplication();

		ob_start();

		// Let CB/JUSER think that everything is going fine
		if ( GeneralInfoRequester::detect_component( 'anyCB' ) ) {
			if ( GeneralInfoRequester::detect_component( 'CBE' ) || $overrideActivation ) {
				global $ueConfig;
			}

			$savetask	= $task;
			$_REQUEST['task']	= 'done';
			include_once ( JPATH_SITE . '/components/com_comprofiler/comprofiler.php' );
			$task		= $savetask;

			if ( $overrideActivation ) {
				$ueConfig['reg_confirmation'] = 0;
			}

			if ( $overrideEmails ) {
				$ueConfig['reg_welcome_sub'] = '';

				// Only disable "Pending Approval / Confirmation" emails if it makes sense
				if ( !$ueConfig['reg_confirmation'] || !$ueConfig['reg_admin_approval'] ) {
					$ueConfig['reg_pend_appr_sub'] = '';
				}
			}
		} elseif ( GeneralInfoRequester::detect_component( 'JUSER' ) ) {
			$savetask	= $task;
			$task		= 'blind';
			include_once( JPATH_SITE . '/components/com_juser/juser.php' );
			include_once( JPATH_SITE .'/administrator/components/com_juser/juser.class.php' );
			$task		= $savetask;
		} elseif ( GeneralInfoRequester::detect_component( 'JOMSOCIAL' ) ) {

		}

		// For joomla and CB, we must filter out some internal variables before handing over the POST data
		$badbadvars = array( 'userid', 'method_name', 'usage', 'processor', 'recurring', 'currency', 'amount', 'invoice', 'id', 'gid' );
		foreach ( $badbadvars as $badvar ) {
			if ( isset( $var[$badvar] ) ) {
				unset( $var[$badvar] );
			}
		}

		if ( empty( $var['name'] ) ) {
			// Must be K2
			$var['name'] = aecEscape( $var['jform']['name'], array( 'string', 'clear_nonalnum' ) );

			unset($var['jform']);
		}

		$_POST = $var;

		$var['username'] = aecEscape( $var['username'], array( 'string', 'badchars' ) );

		$savepwd = aecEscape( $var['password'], array( 'string', 'badchars' ) );

		if ( GeneralInfoRequester::detect_component( 'anyCB' ) ) {
			// This is a CB registration, borrowing their code to save the user
			if ( $internal && !GeneralInfoRequester::detect_component( 'CBE' ) ) {
				include_once( JPATH_SITE . '/components/com_acctexp/lib/codeofshame/cbregister.php' );

				if ( empty( $_POST['firstname'] ) && !empty( $_POST['name'] ) ) {
					$name = metaUser::_explodeName( $_POST['name'] );

					$_POST['firstname'] = $name['first'];

					if ( empty( $name['last'] ) ) {
						$_POST['lastname'] = $name['first'];
					} else {
						$_POST['lastname'] = $name['last'];
					}
				}

				$_POST['password__verify'] = $_POST['password2'];

				unset( $_POST['password2'] );

				@saveRegistrationNOCHECKSLOL( 'com_acctexp' );
			} else {
				@saveRegistration( 'com_acctexp' );

				$cbreply = ob_get_contents();

				$indicator = '<script type="text/javascript">alert(\'';

				$alertstart = strpos( $cbreply, $indicator );

				// Emergency fallback
				if ( $alertstart !== false ) {
					ob_clean();

					$alertend = strpos( $cbreply, '\'); </script>', $alertstart );

					$alert = substr( $cbreply, $alertstart+strlen($indicator), $alertend-$alertstart-strlen($indicator) );

					if ( $aecConfig->cfg['plans_first'] ) {
						return aecErrorAlert( $alert, $action='window.history.go(-2);' );
					} else {
						return aecErrorAlert( $alert, $action='window.history.go(-3);' );
					}
				}
			}
		} elseif ( GeneralInfoRequester::detect_component( 'JUSER' ) ) {
			// This is a JUSER registration, borrowing their code to save the user
			saveRegistration( 'com_acctexp' );

			$query = 'SELECT `id`'
					. ' FROM #__users'
					. ' WHERE `username` = \'' . $var['username'] . '\''
					;
			$db->setQuery( $query );
			$uid = $db->loadResult();
			JUser::saveUser_ext( $uid );
			//synchronize dublicate user data
			$query = 'SELECT `id`' .
					' FROM #__juser_integration' .
					' WHERE `published` = \'1\'' .
					' AND `export_status` = \'1\'';
			$db->setQuery( $query );
			$components = $db->loadObjectList();
			if ( !empty( $components ) ) {
				foreach ( $components as $component ) {
					$synchronize = require_integration( $component->id );
					$synchronize->synchronizeFrom( $uid );
				}
			}
		} elseif ( GeneralInfoRequester::detect_component( 'JOMSOCIAL' ) && !$overrideJS ) {

		} else {
			$data = array(	'username' => $var['username'],
							'password' => $var['password'],
							'password2' => $var['password2'],
							'email' => $var['email'],
							'name' => $var['name'],
							);

			if ( isset( $var['jform']['profile'] ) ) {
				$data['profile'] = $var['jform']['profile'];
			}

			if ( defined( 'JPATH_MANIFESTS' ) ) {
				$params = JComponentHelper::getParams('com_users');

				// Initialise the table with JUser.
				JUser::getTable('User', 'JTable');
				$user = new JUser();

				// Prepare the data for the user object.
				$useractivation = $params->get('useractivation');

				// Check if the user needs to activate their account.
				if ( (($useractivation == 1) || ($useractivation == 2)) && !$overrideActivation ) {
					jimport('joomla.user.helper');
					$data['activation'] = xJ::getHash();
					$data['block'] = 1;
				}

				$usersConfig = &JComponentHelper::getParams( 'com_users' );

				$system	= $usersConfig->get('new_usertype', 2);

				$data['groups'][] = $system;

				// Bind the data.
				if (!$user->bind($data)) {
					JError::raiseWarning( 500, JText::sprintf('COM_USERS_REGISTRATION_BIND_FAILED', $user->getError()));
					return false;
				}

				// Load the users plugin group.
				JPluginHelper::importPlugin('users');

				// Store the data.
				if (!$user->save()) {
					JError::raiseWarning( 500, JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError()));
					return false;
				}
			} else {
				// This is a joomla registration, borrowing their code to save the user

				// Check for request forgeries
				if ( !$internal ) {
					JRequest::checkToken() or die( 'Invalid Token' );
				}

				// Get required system objects
				$user 		= clone(JFactory::getUser());
				//$pathway 	=& $app->getPathway();
				$config		=& JFactory::getConfig();
				$authorize	=& JFactory::getACL();
				$document   =& JFactory::getDocument();

				// If user registration is not allowed, show 403 not authorized.
				$usersConfig = &JComponentHelper::getParams( 'com_users' );
				if ($usersConfig->get('allowUserRegistration') == '0') {
					JError::raiseError( 403, JText::_( 'Access Forbidden' ));
					return;
				}

				// Initialize new usertype setting
				$newUsertype = $usersConfig->get( 'new_usertype' );
				if (!$newUsertype) {
					$newUsertype = 'Registered';
				}

				// Bind the post array to the user object
				if (!$user->bind( $data )) {
					JError::raiseError( 500, $user->getError());

					unset($_POST);
					subscribe();
					return false;
				}

				// Set some initial user values
				$user->set('id', 0);
				$user->set('usertype', '');
				$user->set('gid', $authorize->get_group_id( '', $newUsertype, 'ARO' ));
				$user->set('sendEmail', 0);

				$user->set('registerDate', date('Y-m-d H:i:s'));

				// If user activation is turned on, we need to set the activation information
				$useractivation = $usersConfig->get( 'useractivation' );
				if ( ($useractivation == '1') &&  !$overrideActivation )
				{
					jimport('joomla.user.helper');
					$user->set('activation', md5( JUserHelper::genRandomPassword()) );
					$user->set('block', '1');
				}

				// If there was an error with registration, set the message and display form
				if ( !$user->save() )
				{
					JError::raiseWarning('', JText::_( $user->getError()));
					echo JText::_( $user->getError());
					return false;
				}
			}

			$row = $user;

			$name 		= $row->name;
			$email 		= $row->email;
			$username 	= $row->username;

			$subject 	= sprintf ( JText::_('AEC_SEND_SUB'), $name, $app->getCfg( 'sitename' ) );
			$subject 	= html_entity_decode( $subject, ENT_QUOTES, 'UTF-8' );

			$usersConfig = &JComponentHelper::getParams( 'com_users' );
			$activation = $usersConfig->get('useractivation');

			if ( ( $activation > 0 ) && !$overrideActivation ) {
				$atext = JText::_('AEC_USEND_MSG_ACTIVATE');

				if ( defined( 'JPATH_MANIFESTS' ) ) {
					$activation_link	= JURI::root() . 'index.php?option=com_users&amp;task=registration.activate&amp;token=' . $row->activation;

					if ( $activation == 2 ) {
						$atext = JText::_('COM_USERS_MSG_ADMIN_ACTIVATE');
					}
				} else {
					$activation_link	= JURI::root() . 'index.php?option=com_user&amp;task=activate&amp;activation=' . $row->activation;
				}

				$message = sprintf( $atext, $name, $app->getCfg( 'sitename' ), $activation_link, JURI::root(), $username, $savepwd );
			} else {
				$message = sprintf( JText::_('AEC_USEND_MSG'), $name, $app->getCfg( 'sitename' ), JURI::root() );
			}

			$message = html_entity_decode( $message, ENT_QUOTES, 'UTF-8' );

			// check if Global Config `mailfrom` and `fromname` values exist
			if ( $app->getCfg( 'mailfrom' ) != '' && $app->getCfg( 'fromname' ) != '' ) {
				$adminName2 	= $app->getCfg( 'fromname' );
				$adminEmail2 	= $app->getCfg( 'mailfrom' );
			} else {
				// use email address and name of first superadmin for use in email sent to user
				$rows = xJACLhandler::getSuperAdmins();
				$row2 			= $rows[0];

				$adminName2 	= $row2->name;
				$adminEmail2 	= $row2->email;
			}

			// Send email to user
			if ( !( $aecConfig->cfg['nojoomlaregemails'] || $overrideEmails ) ) {
				xJ::sendMail( $adminEmail2, $adminEmail2, $email, $subject, $message );
			}

			// Send notification to all administrators
			$aecUser	= AECToolbox::aecIP();

			$subject2	= sprintf( JText::_('AEC_SEND_SUB'), $name, $app->getCfg( 'sitename' ) );
			$message2	= sprintf( JText::_('AEC_ASEND_MSG_NEW_REG'), $adminName2, $app->getCfg( 'sitename' ), $row->name, $email, $username, $aecUser['ip'], $aecUser['isp'] );

			$subject2	= html_entity_decode( $subject2, ENT_QUOTES, 'UTF-8' );
			$message2	= html_entity_decode( $message2, ENT_QUOTES, 'UTF-8' );

			// get email addresses of all admins and superadmins set to recieve system emails
			$admins = AECToolbox::getAdminEmailList();

			foreach ( $admins as $adminemail ) {
				if ( !empty( $adminemail ) ) {
					xJ::sendMail( $adminEmail2, $adminEmail2, $adminemail, $subject2, $message2 );
				}
			}
		}

		ob_clean();

		// We need the new userid, so we're fetching it from the newly created entry here
		$query = 'SELECT `id`'
				. ' FROM #__users'
				. ' WHERE `username` = \'' . $var['username'] . '\''
				;
		$db->setQuery( $query );

		return $db->loadResult();
	}

}

?>

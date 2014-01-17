<?php
/**
 * @version $Id: acctexp.rewriteengine.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class reWriteEngine
{
	function isRWEstring( $string )
	{
		if ( ( strpos( $string, '[[' ) !== false ) && ( strpos( $string, ']]' ) !== false ) ) {
			return true;
		}

		if ( ( strpos( $string, '{aecjson}' ) !== false ) && ( strpos( $string, '{/aecjson}' ) !== false ) ) {
			return true;
		}

		return false;
	}

	function info( $switches=array(), $params=null )
	{
		$lang = JFactory::getLanguage();

		if ( is_array( $switches ) ) {
			if ( !count( $switches ) ) {
				$switches = array( 'cms', 'user', 'subscription', 'invoice', 'plan', 'system' );
			}
		} else {
			if ( empty( $switches ) ) {
				$switches = array( 'cms', 'user', 'subscription', 'invoice', 'plan', 'system' );
			} else {
				$temp = $switches;
				$switches = array( $temp );
			}
		}

		$rewrite = array();

		if ( in_array( 'system', $switches ) ) {
			$rewrite['system'][] = 'timestamp';
			$rewrite['system'][] = 'timestamp_backend';
			$rewrite['system'][] = 'server_timestamp';
			$rewrite['system'][] = 'server_timestamp_backend';
		}

		if ( in_array( 'cms', $switches ) ) {
			$rewrite['cms'][] = 'absolute_path';
			$rewrite['cms'][] = 'live_site';
		}

		$newlang = array();

		if ( in_array( 'user', $switches ) ) {
			$rewrite['user'][] = 'id';
			$rewrite['user'][] = 'username';
			$rewrite['user'][] = 'name';
			$rewrite['user'][] = 'first_name';
			$rewrite['user'][] = 'first_first_name';
			$rewrite['user'][] = 'last_name';
			$rewrite['user'][] = 'email';
			$rewrite['user'][] = 'activationcode';
			$rewrite['user'][] = 'activationlink';

			if ( defined( 'JPATH_MANIFESTS' ) ) {
				$db = &JFactory::getDBO();

				$query = 'SELECT DISTINCT `profile_key`'
						. ' FROM #__user_profiles';
				$db->setQuery( $query );
				$pkeys = xJ::getDBArray( $db );

				if ( is_array( $pkeys ) ) {
					foreach ( $pkeys as $pkey ) {
						$content = str_replace( ".", "_", $pkey );

						$rewrite['user'][] = $content;

						$name = 'REWRITE_KEY_USER_' . strtoupper( $content );
						if ( !$lang->hasKey( $name ) ) {
							$newlang[$name] = $content;
						}
					}
				}
			}

			if ( GeneralInfoRequester::detect_component( 'anyCB' ) ) {
				$db = &JFactory::getDBO();

				$query = 'SELECT name, title'
						. ' FROM #__comprofiler_fields'
						. ' WHERE `table` != \'#__users\''
						. ' AND name != \'NA\'';
				$db->setQuery( $query );
				$objects = $db->loadObjectList();

				if ( is_array( $objects ) ) {
					foreach ( $objects as $object ) {
						$rewrite['user'][] = $object->name;

						if ( strpos( $object->title, '_' ) === 0 ) {
							$content = $object->name;
						} else {
							$content = $object->title;
						}

						$name = 'REWRITE_KEY_USER_' . strtoupper( $object->name );
						if ( !$lang->hasKey( $name ) ) {
							$newlang[$name] = $content;
						}
					}
				}
			}

			if ( GeneralInfoRequester::detect_component( 'JOMSOCIAL' ) ) {
				$db = &JFactory::getDBO();

				$query = 'SELECT `id`, `name`'
						. ' FROM #__community_fields'
						. ' WHERE `type` != \'group\''
						;
				$db->setQuery( $query );
				$fields = $db->loadObjectList();

				if ( is_array( $fields ) ) {
					foreach ( $fields as $field ) {
						$rewrite['user'][] = 'js_' . $field->id;

						$content = $field->name;

						$name = 'REWRITE_KEY_USER_JS_' . $field->id;
						if ( !$lang->hasKey( $name ) ) {
							$newlang[$name] = $content;
						}
					}
				}
			}
		}

		if ( !empty( $newlang ) ) {
			if ( !isset( $lang->_strings ) ) {
				$lang->_strings = $newlang;
			} else {
				$lang->_strings = array_merge( $newlang, $lang->_strings );
			}
		}

		if ( in_array( 'subscription', $switches ) ) {
			$rewrite['subscription'][] = 'id';
			$rewrite['subscription'][] = 'type';
			$rewrite['subscription'][] = 'status';
			$rewrite['subscription'][] = 'signup_date';
			$rewrite['subscription'][] = 'signup_date_backend';
			$rewrite['subscription'][] = 'lastpay_date';
			$rewrite['subscription'][] = 'lastpay_date_backend';
			$rewrite['subscription'][] = 'plan';
			$rewrite['subscription'][] = 'previous_plan';
			$rewrite['subscription'][] = 'recurring';
			$rewrite['subscription'][] = 'lifetime';
			$rewrite['subscription'][] = 'expiration_date';
			$rewrite['subscription'][] = 'expiration_date_backend';
			$rewrite['subscription'][] = 'expiration_daysleft';
			$rewrite['subscription'][] = 'notes';
		}

		if ( in_array( 'invoice', $switches ) ) {
			$rewrite['invoice'][] = 'id';
			$rewrite['invoice'][] = 'number';
			$rewrite['invoice'][] = 'number_format';
			$rewrite['invoice'][] = 'created_date';
			$rewrite['invoice'][] = 'transaction_date';
			$rewrite['invoice'][] = 'method';
			$rewrite['invoice'][] = 'amount';
			$rewrite['invoice'][] = 'currency';
			$rewrite['invoice'][] = 'coupons';
		}

		if ( in_array( 'plan', $switches ) ) {
			$rewrite['plan'][] = 'name';
			$rewrite['plan'][] = 'desc';
			$rewrite['plan'][] = 'notes';
		}

		if ( !empty( $params ) ) {
			$params[] = array( 'accordion_start', 'accordion-small' );

			$params[] = array( 'accordion_itemstart', JText::_('REWRITE_ENGINE_TITLE') );
			$list = '<div class="rewriteinfoblock">' . "\n"
			. '<p>' . JText::_('REWRITE_ENGINE_DESC') . '</p>' . "\n"
			. '</div>' . "\n";
			$params[] = array( 'literal', $list );
			$params[] = array( 'accordion_itemend', '' );

			foreach ( $rewrite as $area => $keys ) {
				$params[] = array( 'accordion_itemstart', JText::_( 'REWRITE_AREA_' . strtoupper( $area ) ) );

				$list = '<div class="rewriteinfoblock">' . "\n"
				. '<ul>' . "\n";

				foreach ( $keys as $key ) {
					if ( $lang->hasKey( 'REWRITE_KEY_' . strtoupper( $area . "_" . $key ) ) ) {
						$list .= '<li>[[' . $area . "_" . $key . ']] =&gt; ' . JText::_( 'REWRITE_KEY_' . strtoupper( $area . "_" . $key ) ) . '</li>' . "\n";
					} else {
						$list .= '<li>[[' . $area . "_" . $key . ']] =&gt; ' . ucfirst( str_replace( '_', ' ', $key ) ) . '</li>' . "\n";
					}
				}
				$list .= '</ul>' . "\n"
				. '</div>' . "\n";

				$params[] = array( 'literal', $list );
				$params[] = array( 'accordion_itemend', '' );
			}

			$params[] = array( 'accordion_itemstart', JText::_('REWRITE_ENGINE_AECJSON_TITLE' ) );
			$list = '<div class="rewriteinfoblock">' . "\n"
			. '<p>' . JText::_('REWRITE_ENGINE_AECJSON_DESC') . '</p>' . "\n"
			. '</div>' . "\n";
			$params[] = array( 'literal', $list );
			$params[] = array( 'accordion_itemend', '' );

			$params[] = array( 'div_end', '' );

			return $params;
		} else {
			$return = '';
			foreach ( $rewrite as $area => $keys ) {
				$return .= '<div class="rewriteinfoblock">' . "\n"
				. '<p><strong>' . JText::_( 'REWRITE_AREA_' . strtoupper( $area ) ) . '</strong></p>' . "\n"
				. '<ul>' . "\n";

				foreach ( $keys as $key ) {
					if ( $lang->hasKey( 'REWRITE_KEY_' . strtoupper( $area . "_" . $key ) ) ) {
						$return .= '<li>[[' . $area . "_" . $key . ']] =&gt; ' . JText::_( 'REWRITE_KEY_' . strtoupper( $area . "_" . $key ) ) . '</li>' . "\n";
					} else {
						$return .= '<li>[[' . $area . "_" . $key . ']] =&gt; ' . ucfirst( str_replace( '_', ' ', $key ) ) . '</li>' . "\n";
					}
				}
				$return .= '</ul>' . "\n"
				. '</div>' . "\n";
			}

			$return .= '<div class="rewriteinfoblock">' . "\n"
			. '<p><strong>' . JText::_('REWRITE_ENGINE_AECJSON_TITLE') . '</strong></p>' . "\n"
			. '<p>' . JText::_('REWRITE_ENGINE_AECJSON_DESC') . '</p>' . "\n"
			. '</div>' . "\n";

			return $return;
		}
	}

	function resolveRequest( $request )
	{
		$rqitems = get_object_vars( $request );

		$data = array();

		foreach ( $rqitems as $rqitem => $content ) {
			if ( is_object( $content ) || is_array( $content ) ) {
				$data[$rqitem] = $content;
			}
		}

		$this->feedData( $data );

		return true;
	}

	function feedData( $data )
	{
		if ( !isset( $this->data ) ) {
			$this->data = $data;

			return true;
		}

		foreach ( $data as $name => $content ) {
			$this->data[$name] = $content;
		}

		return true;
	}

	function armRewrite()
	{
		global $aecConfig;

		$this->rewrite = array();

		$this->rewrite['system_timestamp']					= AECToolbox::formatDate( ( (int) gmdate('U') ), false, false );
		$this->rewrite['system_timestamp_backend']			= AECToolbox::formatDate( (int) gmdate('U'), true, false );
		$this->rewrite['system_serverstamp_time']			= AECToolbox::formatDate( ( (int) gmdate('U') ) );
		$this->rewrite['system_server_timestamp_backend']	= AECToolbox::formatDate( ( (int) gmdate('U') ), true );

		$this->rewrite['cms_absolute_path']	= JPATH_SITE;
		$this->rewrite['cms_live_site']		= JURI::root();

		if ( empty( $this->data['invoice'] ) ) {
			$this->data['invoice'] = null;
		}

		if ( !empty( $this->data['metaUser'] ) ) {
			if ( is_object( $this->data['metaUser'] ) ) {
				if ( isset( $this->data['metaUser']->cmsUser->id ) ) {
					$this->rewrite['user_id']				= $this->data['metaUser']->cmsUser->id;
				} else {
					$this->rewrite['user_id']				= 0;
				}

				if ( !empty( $this->data['metaUser']->cmsUser->username ) ) {
					$this->rewrite['user_username']			= $this->data['metaUser']->cmsUser->username;
				} else {
					$this->rewrite['user_username']				= "";
				}

				if ( !empty( $this->data['metaUser']->cmsUser->name ) ) {
					$this->rewrite['user_name']				= $this->data['metaUser']->cmsUser->name;
				} else {
					$this->rewrite['user_name']				= "";
				}

				$name = $this->data['metaUser']->explodeName();

				$this->rewrite['user_first_name']		= $name['first'];
				$this->rewrite['user_first_first_name']	= $name['first_first'];
				$this->rewrite['user_last_name']		= $name['last'];

				if ( !empty( $this->data['metaUser']->cmsUser->email ) ) {
					$this->rewrite['user_email']			= $this->data['metaUser']->cmsUser->email;
				} else {
					$this->rewrite['user_name']				= "";
				}

				if ( defined( 'JPATH_MANIFESTS' ) ) {
					if ( empty( $this->data['metaUser']->hasJProfile ) ) {
						$this->data['metaUser']->loadJProfile();
					}

					if ( !empty( $this->data['metaUser']->hasJProfile ) ) {
						foreach ( $this->data['metaUser']->jProfile as $field => $value ) {
							$this->rewrite['user_'.$field] = $value;
						}
					}
				}

				if ( GeneralInfoRequester::detect_component( 'JOMSOCIAL' ) ) {
					if ( !$this->data['metaUser']->hasJSprofile ) {
						$this->data['metaUser']->loadJSuser();
					}

					if ( !empty( $this->data['metaUser']->hasJSprofile ) ) {
						foreach ( $this->data['metaUser']->jsUser as $k => $v ) {
							$this->rewrite['user_js_' . $k] = $v;
						}
					}
				}

				if ( GeneralInfoRequester::detect_component( 'anyCB' ) ) {
					if ( !$this->data['metaUser']->hasCBprofile ) {
						$this->data['metaUser']->loadCBuser();
					}

					if ( !empty( $this->data['metaUser']->hasCBprofile ) ) {
						$fields = get_object_vars( $this->data['metaUser']->cbUser );

						if ( !empty( $fields ) ) {
							foreach ( $fields as $fieldname => $fieldcontents ) {
								$this->rewrite['user_' . $fieldname] = $fieldcontents;
							}
						}

						if ( isset( $this->data['metaUser']->cbUser->cbactivation ) ) {
							$this->rewrite['user_activationcode']		= $this->data['metaUser']->cbUser->cbactivation;
							$this->rewrite['user_activationlink']		= JURI::root()."index.php?option=com_comprofiler&task=confirm&confirmcode=" . $this->data['metaUser']->cbUser->cbactivation;
						} else {
							$this->rewrite['user_activationcode']		= "";
							$this->rewrite['user_activationlink']		= "";
						}
					} else {
						if ( isset( $this->data['metaUser']->cmsUser->activation ) ) {
							$this->rewrite['user_activationcode']		= $this->data['metaUser']->cmsUser->activation;

							$v = new JVersion();

							if ( $v->isCompatible('1.6') ) {
								$this->rewrite['user_activationlink']	= JURI::root().'index.php?option=com_users&amp;task=registration.activate&amp;token=' . $this->data['metaUser']->cmsUser->activation;
							} else {
								$this->rewrite['user_activationlink']	= JURI::root().'index.php?option=com_user&amp;task=activate&amp;activation=' . $this->data['metaUser']->cmsUser->activation;
							}
						} else {
							$this->rewrite['user_activationcode']		= "";
							$this->rewrite['user_activationlink']		= "";
						}
					}
				} else {
					if ( isset( $this->data['metaUser']->cmsUser->activation ) ) {
						$this->rewrite['user_activationcode']		= $this->data['metaUser']->cmsUser->activation;

						$v = new JVersion();

						if ( $v->isCompatible('1.6') ) {
							$this->rewrite['user_activationlink']	= JURI::root().'index.php?option=com_users&amp;task=registration.activate&amp;token=' . $this->data['metaUser']->cmsUser->activation;
						} else {
							$this->rewrite['user_activationlink']	= JURI::root().'index.php?option=com_user&amp;task=activate&amp;activation=' . $this->data['metaUser']->cmsUser->activation;
						}
					}
				}

				if ( !empty( $this->data['metaUser']->meta->custom_params ) ) {
					foreach ( $this->data['metaUser']->meta->custom_params as $k => $v ) {
						if ( is_array( $v ) ) {
							foreach ( $v as $xk => $xv ) {
								if ( is_array( $xv ) ) {
									foreach ( $xv as $xyk => $xyv ) {
										$this->rewrite['user_'.$k.'_'.$xk.'_'.$xyk] = $xyv;
									}
								} else {
									$this->rewrite['user_'.$k.'_'.$xk] = $xv;
								}
							}
						} else {
							$this->rewrite['user_' . $k] = $v;
						}
					}
				}

				if ( $this->data['metaUser']->hasSubscription ) {
					$this->rewrite['subscription_id']				= $this->data['metaUser']->focusSubscription->id;
					$this->rewrite['subscription_type']				= $this->data['metaUser']->focusSubscription->type;
					$this->rewrite['subscription_status']			= $this->data['metaUser']->focusSubscription->status;

					$this->rewrite['subscription_signup_date']			= AECToolbox::formatDate( $this->data['metaUser']->focusSubscription->signup_date );
					$this->rewrite['subscription_signup_date_backend']	= AECToolbox::formatDate( $this->data['metaUser']->focusSubscription->signup_date, true );

					$this->rewrite['subscription_lastpay_date']			= AECToolbox::formatDate( $this->data['metaUser']->focusSubscription->lastpay_date );
					$this->rewrite['subscription_lastpay_date_backend']	= AECToolbox::formatDate( $this->data['metaUser']->focusSubscription->lastpay_date, true );

					$this->rewrite['subscription_plan']				= $this->data['metaUser']->focusSubscription->plan;

					if ( !empty( $this->data['metaUser']->focusSubscription->previous_plan ) ) {
						$this->rewrite['subscription_previous_plan']	= $this->data['metaUser']->focusSubscription->previous_plan;
					} else {
						$this->rewrite['subscription_previous_plan']	= "";
					}

					$this->rewrite['subscription_recurring']		= $this->data['metaUser']->focusSubscription->recurring;
					$this->rewrite['subscription_lifetime']			= $this->data['metaUser']->focusSubscription->lifetime;
					$this->rewrite['subscription_expiration_date']	= AECToolbox::formatDate( $this->data['metaUser']->focusSubscription->expiration );
					$this->rewrite['subscription_expiration_date_backend']	= AECToolbox::formatDate( $this->data['metaUser']->focusSubscription->expiration, true );

					$this->rewrite['subscription_expiration_daysleft']	= round( ( strtotime( $this->data['metaUser']->focusSubscription->expiration ) - ( (int) gmdate('U') ) ) / 86400 );

					if ( !empty( $this->data['metaUser']->focusSubscription->customparams['notes'] ) ) {
						$this->rewrite['subscription_notes']		=  $this->data['metaUser']->focusSubscription->customparams['notes'];
					} else {
						$this->rewrite['subscription_notes']		=  '';
					}
				}

				if ( empty( $this->data['invoice'] ) && !empty( $this->data['metaUser']->cmsUser->id ) ) {
					$lastinvoice = AECfetchfromDB::lastClearedInvoiceIDbyUserID( $this->data['metaUser']->cmsUser->id );

					$this->data['invoice'] = new Invoice();
					$this->data['invoice']->load( $lastinvoice );
				}
			}
		}

		if ( is_object( $this->data['invoice'] ) ) {
			if ( !empty( $this->data['invoice']->id ) ) {
				$this->rewrite['invoice_id']				= $this->data['invoice']->id;
				$this->rewrite['invoice_number']			= $this->data['invoice']->invoice_number;
				$this->rewrite['invoice_created_date']		= $this->data['invoice']->created_date;
				$this->rewrite['invoice_transaction_date']	= $this->data['invoice']->transaction_date;
				$this->rewrite['invoice_method']			= $this->data['invoice']->method;
				$this->rewrite['invoice_amount']			= $this->data['invoice']->amount;
				$this->rewrite['invoice_currency']			= $this->data['invoice']->currency;

				if ( !empty( $this->data['invoice']->coupons ) && is_array( $this->data['invoice']->coupons ) ) {
					$this->rewrite['invoice_coupons']		=  implode( ';', $this->data['invoice']->coupons );
				} else {
					$this->rewrite['invoice_coupons']		=  '';
				}

				if ( !empty( $this->data['metaUser'] ) && !empty( $this->data['invoice'] ) ) {
					if ( !empty( $this->data['invoice']->id ) ) {
						$this->data['invoice']->formatInvoiceNumber();
						$this->rewrite['invoice_number_format']	= $this->data['invoice']->invoice_number;
						$this->data['invoice']->deformatInvoiceNumber();
					}
				}
			}
		}

		if ( !empty( $this->data['plan'] ) ) {
			if ( is_object( $this->data['plan'] ) ) {
				$this->rewrite['plan_name'] = $this->data['plan']->getProperty( 'name' );
				$this->rewrite['plan_desc'] = $this->data['plan']->getProperty( 'desc' );

				if ( !empty( $this->data['plan']->params['notes'] ) ) {
					$this->rewrite['plan_notes'] = $this->data['plan']->params['notes'];
				} else {
					$this->rewrite['plan_notes'] = '';
				}
			}
		}
	}

	function resolve( $subject )
	{
		// Check whether a replacement exists at all
		if ( ( strpos( $subject, '[[' ) === false ) && ( strpos( $subject, '{aecjson}' ) === false ) ) {
			return $subject;
		}

		if ( empty( $this->rewrite ) ) {
			$this->armRewrite();
		}

		if ( strpos( $subject, '{aecjson}' ) !== false ) {
			if ( ( strpos( $subject, '[[' ) !== false ) && ( strpos( $subject, ']]' ) !== false ) ) {
				// also found classic tags, doing that rewrite first
				$subject = $this->classicRewrite( $subject );
			}

			// We have at least one JSON object, switching to JSON mode
			return $this->decodeTags( $subject );
		} else {
			// No JSON found, do traditional parsing
			return $this->classicRewrite( $subject );
		}
	}

	function classicRewrite( $subject )
	{
		$search = array();
		$replace = array();
		foreach ( $this->rewrite as $name => $replacement ) {
			if ( is_array( $replacement ) ) {
				$replacement = implode( $replacement );
			}

			$search[]	= '[[' . $name . ']]';
			$replace[]	= $replacement;
		}

		return str_replace( $search, $replace, $subject );
	}

	function decodeTags( $subject )
	{
		// Example:
		// {aecjson} {"cmd":"concat","vars":["These ",{"cmd":"condition","vars":{"cmd":"compare","vars":["apples","=","oranges"]},"appl","orang"},"es"} {/aecjson}
		// ...would return either "These apples" or "These oranges", depending on whether the compare function thinks that they are the same

		$regex = "#{aecjson}(.*?){/aecjson}#s";

		// find all instances of json code
		$matches = array();
		preg_match_all( $regex, $subject, $matches, PREG_SET_ORDER );

		if ( count( $matches ) < 1 ) {
			return $subject;
		}

		foreach ( $matches as $match ) {
			$json = jsoonHandler::decode( $match[1] );

			$result = $this->resolveJSONitem( $json );

			$subject = str_replace( $match, $result, $subject );
		}

		return $subject;
	}

	function resolveJSONitem( $current, $safe=false )
	{
		if ( is_object( $current ) ) {
			if ( !isset( $current->cmd ) || !isset( $current->vars ) ) {
				// Malformed String
				return "JSON PARSE ERROR - Malformed String!";
			}

			$variables = $this->resolveJSONitem( $current->vars, $safe );

			$current = $this->executeCommand( $current->cmd, $variables );
		} elseif ( is_array( $current ) ) {
			foreach( $current as $id => $item ) {
				$current[$id] = $this->resolveJSONitem( $item, $safe );
			}
		}

		return $current;
	}

	function executeCommand( $command, $vars, $safe=false )
	{
		$result = '';
		switch( $command ) {
			case 'rw_constant':
				if ( isset( $this->rewrite[$vars] ) ) {
					$result = $this->rewrite[$vars];
				}
				break;
			case 'data':
				if ( empty( $this->data ) ) {
					return false;
				}

				$result = AECToolbox::getObjectProperty( $this->data, $vars );
				break;
			case 'safedata':
				if ( empty( $this->data ) ) {
					return false;
				}

				if ( AECToolbox::getObjectProperty( $this->data, $vars, true ) ) {
					$result = AECToolbox::getObjectProperty( $this->data, $vars );
				}
				break;
			case 'checkdata':
				if ( empty( $this->data ) ) {
					return false;
				}

				$result = AECToolbox::getObjectProperty( $this->data, $vars, true );
				break;
			case 'checkdata_notempty':
				if ( empty( $this->data ) ) {
					return false;
				}

				$check = AECToolbox::getObjectProperty( $this->data, $vars, true );

				if ( AECToolbox::getObjectProperty( $this->data, $vars, true ) ) {
					$check = AECToolbox::getObjectProperty( $this->data, $vars );

					$result = !empty( $check );
				}
				break;
			case 'metaUser':
				if ( !is_object( $this->data['metaUser'] ) ) {
					return false;
				}

				// We also support dot notation for the vars,
				// so explode if that is what the admin wants here
				if ( !is_array( $vars ) && ( strpos( $vars, '.' ) !== false ) ) {
					$temp = explode( '.', $vars );
					$vars = $temp;
				} elseif ( !is_array( $vars ) ) {
					return false;
				}

				$result = $this->data['metaUser']->getProperty( $vars );
				break;
			case 'invoice_count':
				if ( !is_object( $this->data['metaUser'] ) ) {
					return false;
				}

				return AECfetchfromDB::InvoiceCountbyUserID( $this->data['metaUser']->userid );

				break;
			case 'invoice_count_paid':
				if ( !is_object( $this->data['metaUser'] ) ) {
					return false;
				}

				return AECfetchfromDB::PaidInvoiceCountbyUserID( $this->data['metaUser']->userid );

				break;
			case 'invoice_count_unpaid':
				if ( !is_object( $this->data['metaUser'] ) ) {
					return false;
				}

				return AECfetchfromDB::UnpaidInvoiceCountbyUserID( $this->data['metaUser']->userid );

				break;
			case 'jtext':
				$result = JText::_( $vars );
				break;
			case 'constant':
				if ( defined( $vars ) ) {
					$result = constant( $vars );
				} else {
					$result = JText::_( $vars );
				}
				break;
			case 'global':
				if ( is_array( $vars ) ) {
					if ( isset( $vars[0] ) && isset( $vars[1] ) ) {
						$call = strtoupper( $vars[0] );

						$v = $vars[1];

						$allowed = array( 'SERVER', 'GET', 'POST', 'FILES', 'COOKIE', 'SESSION', 'REQUEST', 'ENV' );

						if ( in_array( $call, $allowed ) ) {
							switch ( $call ) {
								case 'SERVER':
									if ( isset( $_SERVER[$v] ) && !$safe ) {
										$result = $_SERVER[$v];
									}
									break;
								case 'GET':
									if ( isset( $_GET[$v] ) ) {
										$result = $_GET[$v];
									}
									break;
								case 'POST':
									if ( isset( $_POST[$v] ) ) {
										$result = $_POST[$v];
									}
									break;
								case 'FILES':
									if ( isset( $_FILES[$v] ) && !$safe ) {
										$result = $_FILES[$v];
									}
									break;
								case 'COOKIE':
									if ( isset( $_COOKIE[$v] ) ) {
										$result = $_COOKIE[$v];
									}
									break;
								case 'SESSION':
									if ( isset( $_SESSION[$v] ) ) {
										$result = $_SESSION[$v];
									}
									break;
								case 'REQUEST':
									if ( isset( $_REQUEST[$v] ) ) {
										$result = $_REQUEST[$v];
									}
									break;
								case 'ENV':
									if ( isset( $_ENV[$v] ) && !$safe ) {
										$result = $_ENV[$v];
									}
									break;
							}
						}
					}
				} else {
					if ( isset( $GLOBALS[$vars] ) ) {
						$result = $GLOBALS[$vars];
					}
				}
				break;
			case 'condition':
				if ( empty( $vars[0] ) || !isset( $vars[1] ) ) {
					if ( isset( $vars[2] ) ) {
						$result = $vars[2];
					} else {
						$result = '';
					}
				} elseif ( isset( $vars[1] ) ) {
					$result = $vars[1];
				} else {
					$result = '';
				}
				break;
			case 'hastext':
				$result = ( strpos( $vars[0], $vars[1] ) !== false ) ? 1 : 0;
				break;
			case 'uppercase':
				$result = strtoupper( $vars );
				break;
			case 'lowercase':
				$result = strtoupper( $vars );
				break;
			case 'concat':
				$result = implode( $vars );
				break;
			case 'date':
				$result = date( $vars[0], strtotime( $vars[1] ) );
				break;
			case 'date_distance':
				$result = round( $vars - ( (int) gmdate('U') ) );
				break;
			case 'date_distance_days':
				$result = round( ( $vars - ( (int) gmdate('U') ) ) / 86400 );
				break;
			case 'crop':
				if ( isset( $vars[2] ) ) {
					$result = substr( $vars[0], (int) $vars[1], (int) $vars[2] );
				} else {
					$result = substr( $vars[0], (int) $vars[1] );
				}
				break;
			case 'pad':
				if ( isset( $vars[3] ) ) {
					$result = str_pad( $vars[0], (int) $vars[1], $vars[2], JText::_( "STR_PAD_" . strtoupper( $vars[3] ) ) );
				} elseif ( isset( $vars[2] ) ) {
					$result = str_pad( $vars[0], (int) $vars[1], $vars[2] );
				} else {
					$result = str_pad( $vars[0], (int) $vars[1] );
				}
				break;
			case 'chunk':
				$chunks = str_split( $vars[0], (int) $vars[1] );

				if ( isset( $vars[2] ) ) {
					$result = implode( $vars[2], $chunks );
				} else {
					$result = implode( ' ', $chunks );
				}
				break;
			case 'compare':
				if ( isset( $vars[2] ) ) {
					$result = AECToolbox::compare( $vars[1], $vars[0], $vars[2] );
				} else {
					$result = 0;
				}
				break;
			case 'math':
				if ( isset( $vars[2] ) ) {
					$result = AECToolbox::math( $vars[1], (float) $vars[0], (float) $vars[2] );
				} else {
					$result = 0;
				}
				break;
			case 'randomstring':
				$result = AECToolbox::randomstring( (int) $vars );
				break;
			case 'randomstring_alphanum':
				$result = AECToolbox::randomstring( (int) $vars, true );
				break;
			case 'randomstring_alphanum_large':
				$result = AECToolbox::randomstring( (int) $vars, true, true );
				break;
			case 'php_function':
				if ( !$safe ) {
					if ( isset( $vars[1] ) ) {
						$result = call_user_func_array( $vars[0], $vars[1] );
					} else {
						$result = call_user_func_array( $vars[0] );
					}
				}
				break;
			case 'php_method':
				if ( !$safe ) {
					if ( function_exists( 'call_user_method_array' ) ) {
						if ( isset( $vars[2] ) ) {
							$result = call_user_method_array( $vars[0], $vars[1], $vars[2] );
						} else {
							$result = call_user_method_array( $vars[0], $vars[1] );
						}
					} else {
						$callback = array( $vars[0], $vars[1] );
	
						if ( isset( $vars[2] ) ) {
							$result = call_user_func_array( $callback, $vars[2] );
						} else {
							$result = call_user_func_array( $callback );
						}
					}
				}
				break;
			default:
				$result = $command . ' is no command';
				break;
		}

		return $result;
	}

	function explain( $subject )
	{
		// Check whether a replacement exists at all
		if ( ( strpos( $subject, '[[' ) === false ) && ( strpos( $subject, '{aecjson}' ) === false ) ) {
			return $subject;
		}

		if ( empty( $this->rewrite ) ) {
			$this->armRewrite();
		}

		if ( strpos( $subject, '{aecjson}' ) !== false ) {
			if ( ( strpos( $subject, '[[' ) !== false ) && ( strpos( $subject, ']]' ) !== false ) ) {
				// also found classic tags, doing that rewrite first
				$subject = $this->classicExplain( $subject );
			}

			// We have at least one JSON object, switching to JSON mode
			return $this->explainTags( $subject );
		} else {
			// No JSON found, do traditional parsing
			return $this->classicExplain( $subject );
		}
	}

	function classicExplain( $subject )
	{
		$regex = "#\[\[(.*?)\]\]#s";

		// find all instances of json code
		$matches = array();
		preg_match_all( $regex, $subject, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {
			$subject = str_replace( $match[0], $match[1], $subject );
		}

		return $subject;
	}

	function explainTags( $subject )
	{
		$regex = "#{aecjson}(.*?){/aecjson}#s";

		// find all instances of json code
		$matches = array();
		preg_match_all( $regex, $subject, $matches, PREG_SET_ORDER );

		if ( count( $matches ) < 1 ) {
			return $subject;
		}

		foreach ( $matches as $match ) {
			$json = jsoonHandler::decode( $match[1] );

			$result = $this->explainJSONitem( $json );

			$subject = str_replace( $match, $result, $subject );
		}

		return $subject;
	}

	function explainJSONitem( $current )
	{
		if ( is_object( $current ) ) {
			if ( !isset( $current->cmd ) || !isset( $current->vars ) ) {
				// Malformed String
				return "JSON PARSE ERROR - Malformed String!";
			}

			$variables = $this->explainJSONitem( $current->vars );

			$current = $this->explainCommand( $current->cmd, $variables );
		} elseif ( is_array( $current ) ) {
			foreach( $current as $id => $item ) {
				$current[$id] = $this->explainJSONitem( $item );
			}
		}

		return $current;
	}

	function explainCommand( $command, $vars )
	{
		switch( $command ) {
			case 'rw_constant': return $vars; break;
			case 'checkdata_notempty':
			case 'checkdata':
			case 'safedata':
			case 'data':
				if ( empty( $this->data ) ) {
					return false;
				} elseif ( is_array( $vars ) ) {
					$vars = implode( '.', $vars );
				}

				return $vars;
				break;
			case 'metaUser':
				if ( !is_object( $this->data['metaUser'] ) ) {
					return false;
				} elseif ( is_array( $vars ) ) {
					$vars = implode( '.', $vars );
				}

				return 'metaUser.'.$vars;
				break;
			case 'jtext': return JText::_( $vars ); break;
			case 'constant': return $vars; break;
			case 'global':
				if ( is_array( $vars ) ) {
					if ( isset( $vars[0] ) && isset( $vars[1] ) ) {
						return $vars[0].'.'.$vars[1];
					}
				} else {
					return $vars;
				}
				break;
			case 'condition':
				if ( isset( $vars[2] ) ) {
					$result = $vars[2];
					return $command.':'.$vars[1].'||'.$vars[2].'?'; break;
				} else {
					return $command.':'.$vars[1].'?'; break;
				}
				break;
			case 'hastext': return $command.'-'.$vars[1]; break;
			case 'lowercase':
			case 'uppercase': return $command.'-'.$vars; break;
			case 'concat': return $command.'-'.implode( '|', $vars ); break;
			case 'date': return $command.'-'.$vars[0]; break;
			case 'date_distance':
			case 'date_distance_days': return $command.'-'.$vars; break;
			case 'crop':
				if ( isset( $vars[2] ) ) {
					return $command.'-'.$vars[0].'['.((int) $vars[1]).'-'.((int) $vars[2]).']'; break;
				} else {
					return $command.'-'.$vars[0].'['.((int) $vars[1]).']'; break;
				}
				break;
			case 'pad':
				if ( isset( $vars[3] ) ) {
					return $command.'-'.$vars[0].'['.((int) $vars[1]).'-'.((int) $vars[2]).'-'.((int) $vars[3]).']'; break;
				} elseif ( isset( $vars[2] ) ) {
					return $command.'-'.$vars[0].'['.((int) $vars[1]).'-'.((int) $vars[2]).']'; break;
				} else {
					return $command.'-'.$vars[0].'['.((int) $vars[1]).']'; break;
				}
				break;
			case 'chunk':
				if ( isset( $vars[2] ) ) {
					return $command.'-'.$vars[0].'['.((int) $vars[1]).'-'.((int) $vars[2]).']'; break;
				} else {
					return $command.'-'.$vars[0].'['.((int) $vars[1]).']'; break;
				}
				break;
			case 'compare':
				if ( isset( $vars[2] ) ) {
					return $vars[0].$vars[1].$vars[2];
				} else {
					return $command;
				}
				break;
			case 'math':
				if ( isset( $vars[2] ) ) {
					return $vars[0].$vars[1].$vars[2];
				} else {
					return $command;
				}
				break;
			case 'randomstring':
			case 'randomstring_alphanum':
			case 'randomstring_alphanum_large':
				return $command;
				break;
			case 'php_function':
				return $command.'-'.$vars[0];
			case 'php_method':
				if ( isset( $vars[2] ) ) {
					return $command.'-'.get_class($vars[0]).'::'.$vars[1].'[' . implode(',',$vars[2]) . ']';
				} else {
					return $command.'-'.get_class($vars[0]).'::'.$vars[1];
				}
				break;
			default: return $command . ' is no command'; break;
		}
	}

}

?>

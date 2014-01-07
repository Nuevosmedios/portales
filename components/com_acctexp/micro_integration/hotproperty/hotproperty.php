<?php
/**
 * @version $Id: mi_hotproperty.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Mosets Hot Property
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_hotproperty extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_HOTPROPERTY_NAME');
		$info['desc'] = JText::_('AEC_MI_HOTPROPERTY_DESC');
		$info['type'] = array( 'vertical_markets.real_estate', 'vendor.mosets' );

		return $info;
	}

	function checkInstallation()
	{
		$db = &JFactory::getDBO();

		$app = JFactory::getApplication();

		$tables	= array();
		$tables	= $db->getTableList();

		return in_array( $app->getCfg( 'dbprefix' ) . 'acctexp_mi_hotproperty', $tables );
	}

	function install()
	{
		$db = &JFactory::getDBO();

		$query = 'CREATE TABLE IF NOT EXISTS `#__acctexp_mi_hotproperty` ('
		. '`id` int(11) NOT NULL auto_increment,'
		. '`userid` int(11) NOT NULL,'
		. '`active` int(4) NOT NULL default \'1\','
		. '`granted_listings` int(11) NULL,'
		. '`used_listings` int(11) NULL,'
		. '`params` text NULL,'
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

        $settings = array();
		$settings['create_agent']	= array( 'toggle' );
		$settings['agent_fields']	= array( 'inputD' );
		$settings['update_agent']	= array( 'toggle' );
		$settings['update_afields']	= array( 'inputD' );
		$settings['create_company']	= array( 'toggle' );
		$settings['company_fields']	= array( 'inputD' );
		$settings['update_company']	= array( 'toggle' );
		$settings['update_cfields']	= array( 'inputD' );
		$settings['add_listings']	= array( 'inputA' );
		$settings['set_listings']	= array( 'inputA' );
		$settings['set_unlimited']	= array( 'toggle' );
		$settings['publish_all']	= array( 'toggle' );
		$settings['unpublish_all']	= array( 'toggle' );

		$settings = $this->autoduplicatesettings( $settings, array(), true, true );

		$xsettings = array();
		$xsettings['add_list_userchoice']		= array( 'toggle' );
		$xsettings['add_list_userchoice_amt']	= array( 'inputD' );
		$xsettings['add_list_customprice']		= array( 'inputD' );

		$xsettings['easy_list_userchoice']		= array( 'toggle' );
		$xsettings['easy_list_userchoice_n']		= array( 'inputA' );

		if ( !empty( $this->settings['easy_list_userchoice_n'] ) ) {
	 		$opts = array();
			$opts[0] = JHTML::_('select.option', "EQ", "Equal to" ); // Should probably be language file defined?
			$opts[1] = JHTML::_('select.option', "LT", "Lesser than" );
			$opts[2] = JHTML::_('select.option', "GT", "Greater than" );

			for( $i=0; $i<$this->settings['easy_list_userchoice_n']; $i++ ) {
				$xsettings['lists']['elu_'.$i.'_op']	= JHTML::_('select.genericlist', $opts, 'elu_'.$i.'_op', 'size="1"', 'value', 'text', $this->settings['elu_'.$i.'_op'] );

				$xsettings[] = array( '', 'hr', '' );
				$xsettings['elu_'.$i.'_op'] = array( 'list', JText::_('AEC_MI_HOTPROPERTY_EASYLIST_OP_NAME'), JText::_('AEC_MI_HOTPROPERTY_EASYLIST_OP_DESC') );
				$xsettings['elu_'.$i.'_no'] = array( 'inputA', JText::_('AEC_MI_HOTPROPERTY_EASYLIST_NO_NAME'), JText::_('AEC_MI_HOTPROPERTY_EASYLIST_NO_DESC') );
				$xsettings['elu_'.$i.'_ch'] = array( 'inputA', JText::_('AEC_MI_HOTPROPERTY_EASYLIST_CH_NAME'), JText::_('AEC_MI_HOTPROPERTY_EASYLIST_CH_DESC') );
			}

			$xsettings[] = array( '', 'hr', '' );
		}

		$xsettings['assoc_company']	= array( 'toggle' );
		$xsettings['rebuild']		= array( 'toggle' );
		$xsettings['remove']			= array( 'toggle' );

		$rewriteswitches			= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

		$xsettings					= AECToolbox::rewriteEngineInfo( $rewriteswitches, $xsettings );

		return array_merge( $xsettings, $settings );
	}

	function getMIform( $request )
	{
		$db = &JFactory::getDBO();

		$settings = array();

		if ( ( !empty( $this->settings['add_list_userchoice'] ) && !empty( $this->settings['add_list_userchoice_amt'] ) ) || !empty( $this->settings['easy_list_userchoice'] ) ) {
			$groups = explode( ';', $this->settings['add_list_userchoice_amt'] );
			$gr = array();
			foreach ( $groups as $group ) {
				if ( strpos( $group, ',' ) ) {
					$gg = explode( ',', $group );
					$gr[] = JHTML::_('select.option', $gg[0], $gg[1] );
				} else {
					$gr[] = JHTML::_('select.option', $group, $group.' Listings' );
				}
			}

			$settings['hpamt']			= array( 'list', JText::_('MI_MI_HOTPROPERTY_USERSELECT_ADDAMOUNT_NAME'), JText::_('MI_MI_HOTPROPERTY_USERSELECT_ADDAMOUNT_DESC') );
			$settings['lists']['hpamt']	= JHTML::_('select.genericlist', $gr, 'hpamt', 'size="6"', 'value', 'text', '' );
		} else {
			return false;
		}

		return $settings;
	}

	function invoice_item_cost( $request )
	{
		$this->modifyPrice( $request );

		return true;
	}

	function modifyPrice( $request )
	{
		if ( !empty( $request->params['hpamt'] ) ) {
			if ( !empty( $this->settings['easy_list_userchoice'] ) && !empty( $this->settings['easy_list_userchoice_n'] ) ) {
				for( $i=0; $i<$this->settings['easy_list_userchoice_n']; $i++ ) {
					switch ( $this->settings['elu_'.$i.'_op'] ) {
						case 'EQ':
							if ( $request->params['hpamt'] == $this->settings['elu_'.$i.'_no'] ) {
								$request->add['terms']->price = $this->parseEasyPrice( $request->add['terms']->price, $request->params['hpamt'], $this->settings['elu_'.$i.'_ch'] );
							}
							break;
						case 'GT':
							if ( $request->params['hpamt'] > $this->settings['elu_'.$i.'_no'] ) {
								$request->add['terms']->price = $this->parseEasyPrice( $request->add['terms']->price, $request->params['hpamt'], $this->settings['elu_'.$i.'_ch'] );
							}
							break;
						case 'LT':
							if ( $request->params['hpamt'] < $this->settings['elu_'.$i.'_no'] ) {
								$request->add['terms']->price = $this->parseEasyPrice( $request->add['terms']->price, $request->params['hpamt'], $this->settings['elu_'.$i.'_ch'] );
							}
							break;
					}
				}

				return true;
			} else {
				$groups = explode( ';', $this->settings['add_list_userchoice_amt'] );

				$discount = 0;
				foreach ( $groups as $group ) {
					if ( strpos( $group, ',' ) ) {
						$gg = explode( ',', $group );

						if ( strpos( '<', $gg[0] ) !== false ) {
							$s = str_replace( '<', '', $gg[0] );
							if ( $request->params['hpamt'] < $s ) {
								$discount = $gg[1];
								continue;
							}
						} elseif ( strpos( '>', $group ) !== false ) {
							$s = str_replace( '>', '', $gg[0] );
							if ( $request->params['hpamt'] > $s ) {
								$discount = $gg[1];
								continue;
							}
						} else {
							if ( $request->params['hpamt'] == $gg[0] ) {
								$discount = $gg[1];
								continue;
							}
						}
					} else {
						return null;
					}
				}

				$cph = new couponHandler();
				if ( $cph->forceload( $discount ) ) {
					$cph->applyCoupon( $request->add['terms']->price );
				}

				return true;
			}
		}

		return null;
	}

	function parseEasyPrice( $p, $a, $parse )
	{
		if ( strpos( $parse, 'p' ) !== false ) {
			$parse = str_replace( 'p', $p, $parse );
		}

		if ( strpos( $parse, 'a' ) !== false ) {
			$parse = str_replace( 'a', $a, $parse );
		}

		if ( strpos( $parse, '*' ) !== false ) {
			$pp = explode( '*', $parse );

			return $pp[0] * $pp[1];
		} elseif ( strpos( $parse, '+' )  !== false) {
			$pp = explode( '+', $parse );

			return $pp[0] + $pp[1];
		} elseif ( strpos( $parse, '-' ) !== false ) {
			$pp = explode( '-', $parse );

			return $pp[0] - $pp[1];
		} elseif ( strpos( $parse, '/' ) !== false ) {
			$pp = explode( '/', $parse );

			return $pp[0] / $pp[1];
		} else {
			return $parse;
		}
	}

	function Defaults()
	{
		$defaults = array();
		$defaults['agent_fields']	= "user=[[user_id]]\nname=[[user_name]]\nemail=[[user_email]]\nneed_approval=0";
		$defaults['company_fields']	= "name=[[user_name]]\naddress=\nsuburb=\ncountry=\nstate=\npostcode=\ntelephone=\nfax=\nwebsite=\nemail=[[user_email]]";

		return $defaults;
	}

	function detect_application()
	{
		return is_dir( JPATH_SITE . '/components/com_hotproperty' );
	}

	function hacks()
	{
		$v10x = false;

		if ( file_exists( JPATH_SITE . '/components/com_hotproperty/router.php' ) ) {
			$v10x = true;
			$v10 = false;
		} else {
			$v10 = is_dir( JPATH_SITE . '/components/com_hotproperty/helpers' );
		}

		$hacks = array();

		if ( $v10x ) {
			$edithack = '// AEC HACK hotproperty1 START' . "\n"
			. '$task = JRequest::getCmd(\'task\');' . "\n"
			. '$view = JRequest::getCmd(\'view\');' . "\n"
			. '$lid = JRequest::getCmd(\'id\');' . "\n"
			. 'if ( ( $view == "properties" ) && ( ( $task == "add" ) || ( ( $task == "save" ) && empty( $lid ) ) ) ) {' . "\n"
			. '$user = &JFactory::getUser();' . "\n"
			. 'if ( !empty( $user->id ) ) {' . "\n"
			. 'include_once( JPATH_SITE . \'/components/com_acctexp/acctexp.class.php\' );' . "\n"
			. 'include_once( JPATH_SITE . \'/components/com_acctexp/micro_integration/hotproperty/hotproperty.php\' );' . "\n"
			. '$mi_hphandler = new aec_hotproperty();' . "\n"
			. '$mi_hphandler->loadUserID( $user->id );' . "\n"
			. 'if( $mi_hphandler->id ) {' . "\n"
			. 'if( !$mi_hphandler->hasListingsLeft() ) {' . "\n"
			. 'echo "' . JText::_('AEC_MI_HACK1_HOTPROPERTY') . '";' . "\n"
			. 'return;' . "\n"
			. '} elseif ( $task == "save" ) {' . "\n"
			. '$mi_hphandler->useListing();' . "\n"
			. '}' . "\n"
			. '} else {' . "\n"
			. 'echo "' . JText::_('AEC_MI_HACK2_HOTPROPERTY') . '";' . "\n"
			. 'return;' . "\n"
			. '}' . "\n"
			. '}' . "\n"
			. '}' . "\n"
			. '// AEC HACK hotproperty1 END' . "\n"
			;

			$n = 'hotproperty1';
			$hacks[$n]['name']				=	'hotproperty.php #1';
			$hacks[$n]['desc']				=	JText::_('AEC_MI_HACK3_HOTPROPERTY');
			$hacks[$n]['type']				=	'file';
			$hacks[$n]['filename']			=	JPATH_SITE . '/components/com_hotproperty/hotproperty.php';
			$hacks[$n]['read']				=	'$hotproperty =& MosetsFactory::getApplication(\'hotproperty\');';
			$hacks[$n]['insert']			=	$edithack . "\n"  . $hacks[$n]['read'];

			$edithack2 = '// AEC HACK adminhotproperty1 START' . "\n"
			. '$task = JRequest::getCmd(\'task\');' . "\n"
			. '$controller = JRequest::getCmd(\'controller\');' . "\n"
			. 'if ( ( $controller == "properties" ) && ( $task == "remove" ) ) {' . "\n"
			. 'if ( !empty( $_REQUEST[\'id\'][0] ) ) {' . "\n"
			. 'include_once( JPATH_SITE . \'/components/com_acctexp/acctexp.class.php\' );' . "\n"
			. 'include_once( JPATH_SITE . \'/components/com_acctexp/micro_integration/hotproperty/hotproperty.php\' );' . "\n"
			. '$mi_hphandler = new aec_hotproperty();' . "\n"
			. '$mi_hphandler->loadLinkID( $_REQUEST[\'id\'][0] );' . "\n"
			. 'if( $mi_hphandler->id ) {' . "\n"
			. '$mi_hphandler->removeListing();' . "\n"
			. '}' . "\n"
			. '}' . "\n"
			. '}' . "\n"
			. '// AEC HACK adminhotproperty1 END' . "\n"
			;

			$n = 'adminhotproperty1';
			$hacks[$n]['name']				=	'admin.hotproperty.php #1';
			$hacks[$n]['desc']				=	JText::_('AEC_MI_HACK5_HOTPROPERTY');
			$hacks[$n]['type']				=	'file';
			$hacks[$n]['filename']			=	JPATH_SITE . '/administrator/components/com_hotproperty/admin.hotproperty.php';
			$hacks[$n]['read']				=	'$hotproperty =& MosetsFactory::getApplication(\'hotproperty\');';
			$hacks[$n]['insert']			=	$edithack2 . "\n"  . $hacks[$n]['read'];
		} else {
			$edithack = '// AEC HACK hotproperty1 START' . "\n"
			. ( defined( '_JEXEC' ) ? '$user = &JFactory::getUser();' : 'global $mosConfig_absolute_path;' ) . "\n"
			. 'include_once( JPATH_SITE . \'/components/com_acctexp/acctexp.class.php\' );' . "\n"
			. 'include_once( JPATH_SITE . \'/components/com_acctexp/micro_integration/hotproperty/hotproperty.php\' );' . "\n"
			. '$mi_hphandler = new aec_hotproperty();' . "\n"
			. '$mi_hphandler->loadUserID( $user->id );' . "\n"
			. 'if( $mi_hphandler->id ) {' . "\n"
			. 'if( !$mi_hphandler->hasListingsLeft() ) {' . "\n"
			. 'echo "' . JText::_('AEC_MI_HACK1_HOTPROPERTY') . '";' . "\n"
			. 'return;' . "\n"
			. '}' . "\n"
			. '} else {' . "\n"
			. 'echo "' . JText::_('AEC_MI_HACK2_HOTPROPERTY') . '";' . "\n"
			. 'return;' . "\n"
			. '}' . "\n"
			. '// AEC HACK hotproperty1 END' . "\n"
			;

			$edithack2 = '// AEC HACK hotproperty2 START' . "\n"
			. ( defined( '_JEXEC' ) ? '$user = &JFactory::getUser();' : 'global $mosConfig_absolute_path;' ) . "\n"
			. 'include_once( JPATH_SITE . \'/components/com_acctexp/acctexp.class.php\' );' . "\n"
			. 'include_once( JPATH_SITE . \'/components/com_acctexp/micro_integration/hotproperty/hotproperty.php\' );' . "\n"
			. '$mi_hphandler = new aec_hotproperty();' . "\n"
			. '$mi_hphandler->loadUserID( $user->id );' . "\n"
			. 'if( $mi_hphandler->id ) {' . "\n"
			. 'if( $mi_hphandler->hasListingsLeft() ) {' . "\n"
			. '$mi_hphandler->useListing();' . "\n"
			. '} else {' . "\n"
			. 'echo "' . JText::_('AEC_MI_HACK1_HOTPROPERTY') . '";' . "\n"
			. 'return;' . "\n"
			. '}' . "\n"
			. '} else {' . "\n"
			. 'echo "' . JText::_('AEC_MI_HACK2_HOTPROPERTY') . '";' . "\n"
			. 'return;' . "\n"
			. '}' . "\n"
			. '// AEC HACK hotproperty2 END' . "\n"
			;

			/*$edithack3 = '// AEC HACK adminhotproperty3 START' . "\n"
			. 'global JPATH_SITE;' . "\n"
			. 'include_once( JPATH_SITE . \'/components/com_acctexp/acctexp.class.php\' );' . "\n"
			. 'include_once( JPATH_SITE . \'/components/com_acctexp/micro_integration/hotproperty/hotproperty.php\' );' . "\n"
			. '$mi_hphandler = new aec_hotproperty();' . "\n"
			. '$mi_hphandler->loadUserID( $mtLinks->user_id );' . "\n"
			. 'if( $mi_hphandler->id ) {' . "\n"
			. 'if( $mi_hphandler->hasListingsLeft() ) {' . "\n"
			. '$mi_hphandler->useListing();' . "\n"
			. '} else {' . "\n"
			. 'continue;' . "\n"
			. '}' . "\n"
			. '} else {' . "\n"
			. 'continue;' . "\n"
			. '}' . "\n"
			. '// AEC HACK adminhotproperty3 END' . "\n"
			;*/

			$edithack4 = '// AEC HACK adminhotproperty4 START' . "\n"
			. ( defined( '_JEXEC' ) ? '' : 'global $mosConfig_absolute_path;' ) . "\n"
			. 'include_once( JPATH_SITE . \'/components/com_acctexp/acctexp.class.php\' );' . "\n"
			. 'include_once( JPATH_SITE . \'/components/com_acctexp/micro_integration/hotproperty/hotproperty.php\' );' . "\n"
			. '$cid = array_keys( $datas[$this->getName()] );' . "\n"
			. '$mi_hphandler = new aec_hotproperty();' . "\n"
			. '$mi_hphandler->loadLinkID( $cid[0] );' . "\n"
			. 'if( $mi_hphandler->id ) {' . "\n"
			. '$mi_hphandler->removeListing();' . "\n"
			. '}' . "\n"
			. '// AEC HACK adminhotproperty4 END' . "\n"
			;

			$n = 'hotproperty1';
			$hacks[$n]['name']				=	'hotproperty.php #1';
			$hacks[$n]['desc']				=	JText::_('AEC_MI_HACK3_HOTPROPERTY');
			$hacks[$n]['type']				=	'file';
			if ( $v10 ) {
				$hacks[$n]['filename']			=	JPATH_SITE . '/components/com_hotproperty/controller.php';
				$hacks[$n]['read']				=	'$function_name = \'edit\'';
			} else {
				$hacks[$n]['filename']			=	JPATH_SITE . '/components/com_hotproperty/property.php';
				$hacks[$n]['read']				=	'# Assign default value for new data';
			}
			$hacks[$n]['insert']			=	$edithack . "\n"  . $hacks[$n]['read'];

			$n = 'hotproperty2';
			$hacks[$n]['name']				=	'hotproperty.php #2';
			$hacks[$n]['desc']				=	JText::_('AEC_MI_HACK4_HOTPROPERTY');
			$hacks[$n]['type']				=	'file';
			if ( $v10 ) {
				$hacks[$n]['filename']			=	JPATH_SITE . '/components/com_hotproperty/controller.php';
				$hacks[$n]['read']				=	'$function_name = \'save\'';
				$hacks[$n]['insert']			=	$edithack2 . "\n"  . $hacks[$n]['read'];
			} else {
				$hacks[$n]['filename']			=	JPATH_SITE . '/components/com_hotproperty/property.php';
				$hacks[$n]['oldread']			=	'# Assign current logon user to Agent field';
				$hacks[$n]['oldinsert']			=	$hacks[$n]['oldread'] . "\n" . $edithack2;
				$hacks[$n]['read']				=	'if ($row->id < 1) {';
				$hacks[$n]['insert']			=	$hacks[$n]['read'] . "\n" . $edithack2;
			}

			/*
			$n = 'adminhotproperty3';
			$hacks[$n]['name']				=	'admin.hotproperty.php #3';
			$hacks[$n]['desc']				=	JText::_('AEC_MI_HACK5_HOTPROPERTY');
			$hacks[$n]['type']				=	'file';
			$hacks[$n]['filename']			=	JPATH_SITE . '/administrator/components/com_hotproperty/admin.mtree.php';
			$hacks[$n]['read']				=	'if ( $mtLinks->link_approved == 0 ) {';
			$hacks[$n]['insert']			=	$hacks[$n]['read'] . "\n" . $edithack3;
			*/

			$n = 'adminhotproperty4';
			$hacks[$n]['name']				=	'admin.hotproperty.php #4';
			$hacks[$n]['desc']				=	JText::_('AEC_MI_HACK5_HOTPROPERTY');
			$hacks[$n]['type']				=	'file';
			if ( $v10 ) {
				$hacks[$n]['filename']			=	JPATH_SITE . '/administrator/components/com_hotproperty/controller.php';
				$hacks[$n]['read']				=	'$_files = JRequest';
				$hacks[$n]['insert']			=	$edithack4 . "\n" . $hacks[$n]['read'];
			} else {
				$hacks[$n]['filename']			=	JPATH_SITE . '/administrator/components/com_hotproperty/admin.hotproperty.php';
				$hacks[$n]['read']				=	'# Remove property from database';
				$hacks[$n]['insert']			=	$hacks[$n]['read'] . "\n" . $edithack4;
			}
		}


		return $hacks;
	}

	function profile_info( $request )
	{
		$db = &JFactory::getDBO();

		$mi_hphandler = new aec_hotproperty();
		$id = $mi_hphandler->getIDbyUserID( $request->metaUser->userid );

		if ( $id ) {
			$mi_hphandler->load( $id );
			return '<p>' . sprintf( JText::_('AEC_MI_DIV1_HOTPROPERTY'), $mi_hphandler->getListingsLeft() ) . '</p>';
		} else {
			return '';
		}
	}

	function relayAction( $request )
	{
		$agent = null;
		$company = null;

		if ( $request->area == 'modifyPrice' ) {
			return $this->modifyPrice( $request );
		}

		if ( !isset( $this->settings['create_agent'.$request->area] ) ) {
			return null;
		}

		if ( $this->settings['create_agent'.$request->area] ) {
			if ( !empty( $this->settings['agent_fields'.$request->area] ) ) {
				$agent = $this->createAgent( $this->settings['agent_fields'.$request->area], $request );
			}
		}

		if ( $agent === false ) {
			$this->setError( 'Agent was not found and could not be created' );
			return false;
		}

		if ( $this->settings['update_agent'.$request->area] ) {
			if ( !empty( $this->settings['update_afields'.$request->area] ) ) {
				if ( !empty( $agent ) ) {
					$agent = $this->update( 'agents', 'user', $this->settings['update_afields'.$request->area], $request );
				}
			}
		}

		if ( $agent === false ) {
			$this->setError( 'Agent was not found or could not be updated' );
			return false;
		}

		if ( $this->settings['create_company'.$request->area] ) {
			if ( !empty( $this->settings['company_fields'.$request->area] ) ) {
				$company = $this->createCompany( $this->settings['company_fields'.$request->area], $this->settings['assoc_company'], $request );
			}

			if ( $company === false ) {
				$this->setError( 'Company was not found and could not be created' );
				return false;
			}
		}

		if ( $this->settings['update_company'.$request->area] ) {
			if ( !empty( $this->settings['update_cfields'.$request->area] ) ) {
				if ( empty( $company ) ) {
					$company = $this->companyExists( $request->metaUser->userid );
				}

				if ( !empty( $company ) ) {
					$company = $this->update( 'companies', 'id', $this->settings['update_cfields'.$request->area], $request, $company );
				}

				if ( $company === false ) {
					$this->setError( 'Company was not found and could not be updated' );
					return false;
				}
			}
		}

		if ( $this->settings['unpublish_all'.$request->area] ) {
			$this->unpublishProperties( $agent );
		}

		if ( $this->settings['publish_all'.$request->area] ) {
			$this->publishProperties( $agent );
		}

		if ( !empty( $this->settings['set_listings'.$request->area] ) || !empty( $this->settings['add_listings'.$request->area] ) || ( !empty( $this->settings['add_list_userchoice'] ) && !empty( $request->params['hpamt']  ) )  ) {
			$db = &JFactory::getDBO();

			$mi_hphandler = new aec_hotproperty();
			$id = $mi_hphandler->getIDbyUserID( $request->metaUser->userid );
			$mi_id = $id ? $id : 0;
			$mi_hphandler->load( $mi_id );

			if ( !$mi_id ){
				$mi_hphandler->userid = $request->metaUser->userid;
				$mi_hphandler->active = 1;
			}

			if ( $this->settings['set_listings'] ) {
				$mi_hphandler->setListings( $this->settings['set_listings'] );
			}

			if ( $this->settings['add_listings'] ) {
				$mi_hphandler->addListings( $this->settings['add_listings'] );
			}

			if ( $this->settings['add_list_userchoice'] ) {
				if ( strpos( $request->params['hpamt'], '>' ) ) {
					$mi_hphandler->unlimitedListings();
				} else {
					$mi_hphandler->addListings( $request->params['hpamt'] );
				}
			}

			$mi_hphandler->storeload();
		}

		return true;
	}

	function agentExists( $userid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT id FROM #__hp_agents'
				. ' WHERE user = \'' . $userid . '\''
				;
		$db->setQuery( $query );
		$id = $db->loadResult();

		if ( $id ) {
			return $id;
		} else {
			return false;
		}
	}

	function createAgent( $fields, $request )
	{
		$db = &JFactory::getDBO();

		$check = $this->agentExists( $request->metaUser->userid );

		if ( !empty( $check ) ) {
			return $check;
		}

		$fields = AECToolbox::rewriteEngineRQ( $fields, $request );

		$fieldlist = explode( "\n", $fields );

		$keys = array();
		$values = array();
		foreach ( $fieldlist as $content ) {
			$c = explode( '=', $content, 2 );

			if ( !empty( $c[0] ) ) {
				$keys[] = trim( $c[0] );
				$values[] = trim( $c[1] );
			}
		}

		$query = 'INSERT INTO #__hp_agents'
				. ' (' . implode( ',', $keys ) . ')'
				. ' VALUES (\'' . implode( '\',\'', $values ) . '\')'
				;
		$db->setQuery( $query );
		$result = $db->query();

		if ( $result ) {
			$query = 'SELECT max(id)'
					. ' FROM #__hp_agents'
					;
			$db->setQuery( $query );
			return $db->loadResult();
		} else {
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}

	function companyExists( $userid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT company FROM #__hp_agents'
				. ' WHERE user = \'' . $userid . '\''
				;
		$db->setQuery( $query );
		$id = $db->loadResult();

		if ( $id ) {
			return $id;
		} else {
			return false;
		}
	}

	function createCompany( $fields, $assoc, $request )
	{
		$db = &JFactory::getDBO();

		$check = $this->companyExists( $request->metaUser->userid );
		if ( !empty( $check ) ) {
			return $check;
		}

		$fields = AECToolbox::rewriteEngineRQ( $fields, $request );

		$fieldlist = explode( "\n", $fields );

		$keys = array();
		$values = array();
		foreach ( $fieldlist as $content ) {
			$c = explode( '=', $content, 2 );

			if ( !empty( $c[0] ) ) {
				$keys[] = trim( $c[0] );
				$values[] = trim( $c[1] );
			}
		}

		$query = 'INSERT INTO #__hp_companies'
				. ' (' . implode( ',', $keys ) . ')'
				. ' VALUES (\'' . implode( '\',\'', $values ) . '\')'
				;
		$db->setQuery( $query );
		$result = $db->query();

		$query = 'SELECT max(id)'
				. ' FROM #__hp_companies'
				;
		$db->setQuery( $query );
		$result = $db->loadResult();

		if ( $result ) {
			if ( $assoc ) {
				if ( $result ) {
					$query = 'UPDATE #__hp_agents'
							. ' SET company = \'' . $result . '\''
							. ' WHERE user = \'' . $request->metaUser->userid . '\''
							;

					$db->setQuery( $query );
					if ( $db->query() ) {
						return $result;
					}
				}
			} else {
				return $result;
			}
		}

		$this->setError( $db->getErrorMsg() );
		return false;
	}

	function update( $table, $id, $fields, $request, $sid=false )
	{
		$db = &JFactory::getDBO();

		$fields = AECToolbox::rewriteEngineRQ( $fields, $request );

		$fieldlist = explode( "\n", $fields, 2 );

		$set = array();
		foreach ( $fieldlist as $content ) {
			$c = explode( '=', $content, 2 );

			if ( !empty( $c[0] ) ) {
				$set[] = '`' . trim( $c[0] ) . '` = \'' . trim( $c[1] ) . '\'';
			}
		}

		$query = 'UPDATE #__hp_' . $table
				. ' SET ' . implode( ', ', $set )
				. ' WHERE ' . $id . ' = \'' . ( $sid ? $sid : $request->metaUser->userid ) . '\''
				;

		$db->setQuery( $query );
		if ( $db->query() ) {
			return true;
		} else {
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}

	function publishProperties( $agentid )
	{
		$db = &JFactory::getDBO();

		$query = 'UPDATE #__hp_properties'
				. ' SET `published` = \'1\''
				. ' WHERE `agent` = \'' . $agentid . '\''
				;
		$db->setQuery( $query );
		if ( $db->query() ) {
			return true;
		} else {
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}

	function unpublishProperties( $agentid )
	{
		$db = &JFactory::getDBO();

		$query = 'UPDATE #__hp_properties'
				. ' SET `published` = \'0\''
				. ' WHERE `agent` = \'' . $agentid . '\''
				;
		$db->setQuery( $query );
		if ( $db->query() ) {
			return true;
		} else {
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}

}

class aec_hotproperty extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $userid 			= null;
	/** @var int */
	var $active				= null;
	/** @var int */
	var $granted_listings	= null;
	/** @var text */
	var $used_listings		= null;
	/** @var text */
	var $params				= null;

	function declareParamFields(){ return array( 'params' ); }

	function aec_hotproperty()
	{
		$lang =& JFactory::getLanguage();

		$lang->load( 'com_acctexp.microintegrations', JPATH_SITE );

		parent::__construct( '#__acctexp_mi_hotproperty', 'id' );
	}

	function getIDbyUserID( $userid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_mi_hotproperty'
				. ' WHERE `userid` = \'' . $userid . '\''
				;
		$db->setQuery( $query );
		return $db->loadResult();
	}

	function loadUserID( $userid )
	{
		$id = $this->getIDbyUserID( $userid );
		$this->load( $id );
	}

	function loadAgentID( $agent )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `user`'
				. ' FROM #__hp_agents'
				. ' WHERE `id` = \'' . $agent . '\''
				;
		$db->setQuery( $query );
		$userid = $db->loadResult();

		$this->loadUserID( $userid );
	}

	function getIDbyLinkID( $linkid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `agent`'
				. ' FROM #__hp_properties'
				. ' WHERE `id` = \'' . $linkid . '\''
				;
		$db->setQuery( $query );
		$agent = $db->loadResult();

		if ( empty( $agent ) ) {
			return $agent;
		}

		$query = 'SELECT `user`'
				. ' FROM #__hp_agents'
				. ' WHERE `id` = \'' . $agent . '\''
				;
		$db->setQuery( $query );
		$userid = $db->loadResult();

		if ( empty( $userid ) ) {
			return $userid;
		}

		return $this->getIDbyUserID( $userid );
	}

	function loadLinkID( $linkid )
	{
		$id = $this->getIDbyLinkID( $linkid );
		$this->load( $id );
	}

	function is_active()
	{
		return $this->active ? true : false;
	}

	function getListingsLeft()
	{
		if ( !empty( $this->params['unlimited'] ) ) {
			return 'unlimited';
		} else {
			return $this->granted_listings - $this->used_listings;
		}
	}

	function hasListingsLeft()
	{
		$listings = $this->getListingsLeft();
		if ( $listings === 'unlimited' ) {
			return true;
		} elseif ( $listings > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	function useListing()
	{
		if( $this->hasListingsLeft() && $this->is_active() ) {
			$this->used_listings++;
			$this->check();
			$this->store();
			return true;
		}else{
			return false;
		}
	}

	function removeListing()
	{
		if ( $this->is_active() ) {
			$this->used_listings--;
			$this->storeload();
			return true;
		} else {
			return false;
		}
	}

	function setListings( $set )
	{
		$this->granted_listings = $set;
	}

	function unlimitedListings()
	{
		$this->addParams( array( 'unlimited' => true ) );
	}

	function addListings( $add )
	{
		$this->granted_listings += $add;
	}
}
?>

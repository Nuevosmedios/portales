<?php
/**
 * @version $Id: tool_readout.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Toolbox - Readout
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class tool_readout
{
	function Info()
	{
		$info = array();
		$info['name'] = "Readout";
		$info['desc'] = "Gives you an overview of all the settings in the system.";

		return $info;
	}

	function options()
	{
		return array(		'show_settings' => 0,
							'show_extsettings' => 0,
							'show_processors' => 0,
							'show_plans' => 1,
							'show_mi_relations' => 1,
							'show_mis' => 1,
							'truncation_length' => 42,
							'noformat_newlines' => 0,
							'use_ordering' => 0,
							'column_headers' => 20,
							'export_csv' => 0,
							'store_settings' => 1
						);
	}

	function Settings()
	{
		$optionlist = $this->options();

		$user = &JFactory::getUser();

		$metaUser = new metaUser( $user->id );
		if ( isset( $metaUser->meta->custom_params['aecadmin_readout'] ) ) {
			$prefs = $metaUser->meta->custom_params['aecadmin_readout'];
		} else {
			$prefs = array();
		}

		$settings = array();
		foreach ( $optionlist as $opt => $optdefault ) {
			if ( isset( $prefs[$opt] ) ) {
				$optval = $prefs[$opt];
			} else {
				$optval = $optdefault;
			}

			if ( ( $optdefault == 1 ) || ( $optdefault == 0 ) ) {
				$settings[$opt] = array( 'toggle', $optval );
			} else {
				$settings[$opt] = array( 'inputB', $optval );
			}
		}

		return $settings;
	}

	function Action()
	{
		$optionlist = $this->options();

		if ( !empty( $_POST['export_csv'] ) ) {
			$method = "csv";
		} else {
			$method = "html";
		}

		$r = array();
		$readout = new aecReadout( $optionlist, $method );

		foreach ( $optionlist as $opt => $odefault ) {
			if ( !isset( $_POST[$opt] ) ) {
				continue;
			}

			switch ( $opt ) {
				case 'show_settings':
					$s = $readout->readSettings();
					break;
				case 'show_processors':
					$s = $readout->readProcessors();
					break;
				case 'show_plans':
					$s = $readout->readPlans();
					break;
				case 'show_mi_relations':
					$s = $readout->readPlanMIrel();
					break;
				case 'show_mis':
					$s = $readout->readMIs();
					break;
				case 'store_settings':
					$user = &JFactory::getUser();

					$settings = array();
					foreach ( $optionlist as $opt => $optdefault ) {
						if ( !empty( $_POST[$opt] ) ) {
							$settings[$opt] = $_POST[$opt];
						} else {
							$settings[$opt] = 0;
						}
					}

					$metaUser = new metaUser( $user->id );
					$metaUser->meta->addCustomParams( array( 'aecadmin_readout' => $settings ) );
					$metaUser->meta->storeload();
					continue 2;
					break;
				default:
					continue 2;
					break;
			}

			if ( isset( $s['def'] ) ) {
				$r[] = $s;
			} elseif ( is_array( $s ) ) {
				foreach ( $s as $i => $x ) {
					$r[] = $x;
				}
			}
		}

		if ( !empty( $_POST['export_csv'] ) ) {
			return $this->readoutCSV( $r );
		} else {
			return $this->readout( $r );
		}
	}

	function readout( $readout )
	{
		if ( isset( $_POST['column_headers'] ) ) {
			$ch = $_POST['column_headers'];
		} else {
			$ch = 20;
		}

		$return = "";
		foreach ( $readout as $part ) {
			if ( !empty( $part['head'] ) ) {
				if ( !empty( $part['sub'] ) ) {
					$return .= "<h6>" . $part['head'] . "</h6>";
				} else {
					$return .= "<h5>" . $part['head'] . "</h5>";
				}
			}

			if ( empty( $part['type'] ) ) {
				continue;
			}

			if ( $part['type'] != 'table' ) {
				continue;
			}

			$return .= "<table class=\"aec_readout_bit\">";

			$i = 0; $j = 0;
			foreach ( $part['set'] as $entry ) {
				if ( $j%$ch == 0 ) {
					$return .= "<tr>";
					$k = 0;
					foreach ( $part['def'] as $def => $dc ) {
						if ( is_array( $dc[0] ) ) {
							$dn = $dc[0][0].'_'.$dc[0][1];
						} else {
							$dn = $dc[0];
						}

						$return .= "<th class=\"col".$k." ".$dn."\">" . $def . "</th>";
						$k = $k ? 0 : 1;
					}
					$return .= "</tr>";
				}

				$return .= "<tr class=\"row".$i."\">";

				foreach ( $part['def'] as $def => $dc ) {
					if ( is_array( $dc[0] ) ) {
						$dn = $dc[0][0].'_'.$dc[0][1];
					} else {
						$dn = $dc[0];
					}

					$tdclass = $dn;

					if ( isset( $entry[$dn] ) ) {
						$dcc = $entry[$dn];
					} else {
						$dcc = "";
					}

					if ( isset( $dc[1] ) ) {
						$types = explode( ' ', $dc[1] );

						foreach ( $types as $tt ) {
							switch ( $tt ) {
								case 'bool';
									$dcc = $dcc ? 'Yes' : 'No';
									$tdclass .= " bool".$dcc;
									break;
							}
						}
					} else {
						if ( is_array( $dcc ) ) {
							$dcc = implode( ', ', $dcc );
						}
					}

					$return .= "<td class=\"".$tdclass."\">" . $dcc . "</td>";
				}

				$return .= "</tr>";

				$i = $i ? 0 : 1;
				$j++;
			}

			$return .= "</table>";
		 }

 		return $return;
	}

	function readoutCSV( $readout )
	{
		// Send download header
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");

		header("Content-Type: application/download");
		header('Content-Disposition: inline; filename="aec_readout.csv"');

		// Load Exporting Class
		$filename = JPATH_SITE . '/components/com_acctexp/lib/export/csv.php';
		$classname = 'AECexport_csv';

		include_once( $filename );

		$exphandler = new $classname();


		if ( isset( $_POST['column_headers'] ) ) {
			$ch = $_POST['column_headers'];
		} else {
			$ch = 20;
		}

		foreach ( $readout as $part ) {
			if ( !empty( $part['head'] ) ) {
				$exphandler->putln( array( $part['head'] ) );
			}

			switch ( $part['type'] ) {
				case 'table':

					$i = 0; $j = 0;
					foreach ( $part['set'] as $entry ) {
						if ( $j%$ch == 0 ) {
							$array = array();
							foreach ( $part['def'] as $k => $v ) {
								$array[] = $k;
							}
							$exphandler->putln( $array );
						}

						$array = array();
						foreach ( $part['def'] as $def => $dc ) {
							if ( is_array( $dc[0] ) ) {
								$dn = $dc[0][0].'_'.$dc[0][1];
							} else {
								$dn = $dc[0];
							}

							$dcc = $entry[$dn];

							if ( isset( $dc[1] ) ) {
								$types = explode( ' ', $dc[1] );

								foreach ( $types as $tt ) {
									switch ( $tt ) {
										case 'bool';
											$dcc = $dcc ? 'Yes' : 'No';
											break;
									}
								}
							}

							if ( is_array( $dcc ) ) {
								$dcc = implode( ', ', $dcc );
							}

							$array[] = $dcc;
						}

						$exphandler->putln( $array );

						$j++;
					}

					echo "\n\n";
					break;
			}
		}
 		exit;
	}

}

class aecReadout
{

	function aecReadout( $optionlist, $method )
	{
		$this->optionlist = $optionlist;
		$this->method = "conversionHelper" . strtoupper( $method );

		$this->lists = array();

		$this->acllist = xJACLhandler::aclList();

		foreach ( $this->acllist as $aclitem ) {
			$this->lists['gid'][$aclitem->group_id] = $aclitem->name;
		}

		$this->planlist = SubscriptionPlanHandler::getFullPlanList();

		foreach ( $this->planlist as $planitem ) {
			$this->lists['plan'][$planitem->id] = $planitem->name;
		}

		$this->milist = microIntegrationHandler::getMIList( null, null, isset( $_POST['use_ordering'] ), true );

		foreach ( $this->milist as $miitem ) {
			$this->lists['mi'][$miitem->id] = $miitem->name;
		}
	}

	function conversionHelper( $content, $obj )
	{
		return $this->{$this->method}( $content, $obj );
	}

	function readSettings()
	{
		global $aecConfig;

		$r = array();
		$r['head'] = "Settings";
		$r['type'] = "table";

		$setdef = aecConfig::paramsList();

		$r['def'] = array();
		foreach ( $setdef as $sd => $sdd ) {
			if ( ( $sdd === 0 ) || ( $sdd === 1 ) ) {
				$tname = str_replace( ':', '', JText::_( 'CFG_GENERAL_' . strtoupper( $sd ) . '_NAME' ) );

				$r['def'][$tname] = array( $sd, 'bool' );
			}
		}

		$r['set'][] = $aecConfig->cfg;

		if ( !empty( $_POST['show_extsettings'] ) ) {
			$readout[] = $r;

			unset($r);

			$r['head'] = "";
			$r['type'] = "table";

			$setdef = aecConfig::paramsList();

			$r['def'] = array();
			foreach ( $setdef as $sd => $sdd ) {
				if ( ( $sdd !== 0 ) && ( $sdd !== 1 ) ) {
					$reg = array( 'GENERAL', 'MI' );

					foreach ( $reg as $regg ) {
						$cname = 'CFG_' . $regg . '_' . strtoupper( $sd ) . '_NAME';

						if ( defined( $cname ) )  {
							$tname = str_replace( ':', '', JText::_( $cname ) );
						}
					}

					$r['def'][$tname] = array( $sd );
				}
			}

			$r['set'][] = $aecConfig->cfg;
		}

		$readout[] = $r;

		return $readout;
	}

	function readProcessors()
	{
		$db = &JFactory::getDBO();

		$lang = JFactory::getLanguage();

		$readout = array();

		$r = array();
		$r['head'] = "Processors";

		$processors = PaymentProcessorHandler::getInstalledNameList();

		foreach ( $processors as $procname ) {
			$pp = null;
			$pp = new PaymentProcessor();

			if ( !$pp->loadName( $procname ) ) {
				continue;
			}

			$pp->fullInit();

			$readout[] = $r;

			$r = array();

			$r['head'] = $pp->info['longname'];
			$r['type'] = "table";
			$r['sub'] = true;

			$r['def'] = array (
				"ID" => array( 'id' ),
				"Published" => array( 'active', 'bool' )
			);

			foreach ( $pp->info as $iname => $ic ) {
				if ( empty( $iname ) ) {
					continue;
				}

				$cname = 'CFG_' . strtoupper( $procname ) . '_' . strtoupper($iname) . '_NAME';
				$gname = 'CFG_PROCESSOR_' . strtoupper($iname) . '_NAME';

				if ( $lang->hasKey( $cname ) ) {
					$tname = JText::_( $cname );
				} elseif ( $lang->hasKey( $gname ) )  {
					$tname = JText::_( $gname );
				} else {
					$tname = $iname;
				}

				$r['def'][$tname] = array( array( 'info', $iname ), 'smartlimit' );
			}

			$bsettings = $pp->getBackendSettings();

			foreach ( $bsettings as $psname => $sc ) {
				if ( empty( $psname ) || is_numeric( $psname ) || ( $psname == 'lists') ) {
					continue;
				}

				$cname = 'CFG_' . strtoupper( $procname ) . '_' . strtoupper($psname) . '_NAME';
				$gname = 'CFG_PROCESSOR_' . strtoupper($psname) . '_NAME';

				if ( $lang->hasKey( $cname ) )  {
					$tname = JText::_( $cname );
				} elseif ( $lang->hasKey( $gname ) )  {
					$tname = JText::_( $gname );
				} else {
					$tname = $psname;
				}

				if ( $sc[0] == 'list_yesno' ) {
					$stype = 'bool';
				} else {
					$stype = 'smartlimit';
				}

				$r['def'][$tname] = array( array( 'settings', $psname ), $stype );
			}

			$ps = array();
			foreach ( $r['def'] as $nn => $def ) {
				$ps = array_merge( $ps, $this->conversionHelper( $def, $pp ) );
			}

			$r['set'] = array( 0 => $ps );
		}

		$readout[] = $r;

		return $readout;
	}

	function readPlans()
	{
		$db = &JFactory::getDBO();

		$r = array();
		$r['head'] = "Payment Plans";
		$r['type'] = "table";

		$r['def'] = array (
			"ID" => array( 'id' ),
			"Published" => array( 'active', 'bool' ),
			"Visible" => array( 'visible', 'bool' ),
			"Name" => array( 'name', 'smartlimit haslink', 'editSubscriptionPlan', 'id' ),
			"Desc" => array( 'desc', 'notags smartlimit' ),
			"Primary" => array( array( 'params', 'make_primary' ), 'bool' ),
			"Activate" => array( array( 'params', 'make_active' ), 'bool' ),
			"Update Exist." => array( array( 'params', 'update_existing' ), 'bool' ),
			"Override Activat." => array( array( 'params', 'override_activation' ), 'bool' ),
			"Override Reg. Email" => array( array( 'params', 'override_regmail' ), 'bool' ),
			"Set GID" => array( array( 'params', 'gid_enabled' ), 'bool' ),
			"GID" => array( array( 'params', 'gid' ), 'list', 'gid' ),

			"Standard Parent Plan" => array( array( 'params', 'standard_parent' ), 'list', 'plan' ),
			"Fallback Plan" => array( array( 'params', 'fallback' ), 'list', 'plan' ),

			"Free" => array( array( 'params', 'full_free' ), 'bool' ),
			"Cost" => array( array( 'params', 'full_amount' ) ),
			"Lifetime" => array( array( 'params', 'lifetime' ), 'bool' ),
			"Period" => array( array( 'params', 'full_period' ) ),
			"Unit" => array( array( 'params', 'full_periodunit' ) ),

			"Free Trial" => array( array( 'params', 'trial_free' ), 'bool' ),
			"Trial Cost" => array( array( 'params', 'trial_amount' ) ),
			"Trial Period" => array( array( 'params', 'trial_period' ) ),
			"Trial Unit" => array( array( 'params', 'trial_periodunit' ) ),

			"Has MinGID" => array( array( 'restrictions', 'mingid_enabled' ), 'bool' ),
			"MinGID" => array( array( 'restrictions', 'mingid' ), 'list', 'gid' ),
			"Has FixGID" => array( array( 'restrictions', 'fixgid_enabled' ), 'bool' ),
			"FixGID" => array( array( 'restrictions', 'fixgid' ), 'list', 'gid' ),
			"Has MaxGID" => array( array( 'restrictions', 'fixgid_enabled' ), 'bool' ),
			"MaxGID" => array( array( 'restrictions', 'fixgid' ), 'list', 'gid' ),

			"Requires Prev. Plan" => array( array( 'restrictions', 'previousplan_req_enabled' ), 'bool' ),
			"Prev. Plan" => array( array( 'restrictions', 'previousplan_req' ), 'list', 'plan' ),
			"Excluding Prev. Plan" => array( array( 'restrictions', 'previousplan_req_enabled_excluded' ), 'bool' ),
			"Excl. Prev. Plan" => array( array( 'restrictions', 'previousplan_req_excluded' ), 'list', 'plan' ),
			"Requires Curr. Plan" => array( array( 'restrictions', 'currentplan_req_enabled' ), 'bool' ),
			"Curr. Plan" => array( array( 'restrictions', 'currentplan_req' ), 'list', 'plan' ),
			"Excluding Curr. Plan" => array( array( 'restrictions', 'currentplan_req_enabled_excluded' ), 'bool' ),
			"Excl. Curr. Plan" => array( array( 'restrictions', 'currentplan_req_excluded' ), 'list', 'plan' ),
			"Requires Overall Plan" => array( array( 'restrictions', 'overallplan_req_enabled' ), 'bool' ),
			"Overall Plan" => array( array( 'restrictions', 'overallplan_req' ), 'list', 'plan' ),
			"Excluding Overall. Plan" => array( array( 'restrictions', 'overallplan_req_enabled_excluded' ), 'bool' ),
			"Excl. Overall. Plan" => array( array( 'restrictions', 'overallplan_req_excluded' ), 'list', 'plan' ),

			"Min Used Plan" => array( array( 'restrictions', 'used_plan_min_enabled' ), 'bool' ),
			"Min Used Plan Amount" => array( array( 'restrictions', 'used_plan_min_amount' ) ),
			"Min Used Plans" => array( array( 'restrictions', 'used_plan_min' ), 'list', 'plan' ),
			"Max Used Plan" => array( array( 'restrictions', 'used_plan_max_enabled' ), 'bool' ),
			"Max Used Plan Amount" => array( array( 'restrictions', 'used_plan_max_amount' ) ),
			"Max Used Plans" => array( array( 'restrictions', 'used_plan_max' ), 'list', 'plan' ),

			"Custom Restrictions" => array( array( 'restrictions', 'custom_restrictions_enabled' ), 'bool' ),
			"Restrictions" => array( array( 'restrictions', 'custom_restrictions' ) )
		);

		$plans = SubscriptionPlanHandler::getPlanList( null, null, isset( $_POST['use_ordering'] ) );

		$r['set'] = array();
		foreach ( $plans as $planid ) {
			$plan = new SubscriptionPlan();
			$plan->load( $planid );

			$ps = array();
			foreach ( $r['def'] as $nn => $def ) {
				$ps = array_merge( $ps, $this->conversionHelper( $def, $plan ) );
			}

			$r['set'][] = $ps;
		}

		return $r;
	}

	function readPlanMIrel()
	{
		$db = &JFactory::getDBO();

		$r = array();
		$r['head'] = "Payment Plan - MicroIntegration relationships";
		$r['type'] = "table";

		$r['def'] = array (
			"ID" => array( 'id' ),
			"Published" => array( 'active', 'bool' ),
			"Visible" => array( 'visible', 'bool' ),
			"Name" => array( 'name', 'smartlimit' )
		);

		$micursor = '';
		$mis = array();
		foreach ( $this->milist as $miobj ) {
			$mi = new microIntegration();
			$mi->load( $miobj->id );
			if ( !$mi->callIntegration() ) {
				continue;
			}

			if ( $miobj->class_name != $micursor ) {
				if ( !empty( $mi->info ) ) {
					$miname = $mi->info['name'];
				} else {
					$miname = $miobj->class_name;
				}
				$r['def'][$miname] = array( $miobj->class_name, 'list', 'mi' );

				$micursor = $miobj->class_name;
			}

			$mis[$mi->id] = array( $miobj->class_name, $mi->name );
		}

		$r['set'] = array();
		foreach ( $this->planlist as $planid => $planobj ) {
			$plan = new SubscriptionPlan();
			$plan->load( $planobj->id );

			if ( !empty( $plan->micro_integrations ) ) {
				foreach ( $plan->micro_integrations as $pmi ) {
					if ( isset( $mis[$pmi] ) ) {
						$plan->{$mis[$pmi][0]}[] = $pmi;
					}
				}
			}

			$ps = array();
			foreach ( $r['def'] as $nn => $def ) {
				$ps = array_merge( $ps, $this->conversionHelper( $def, $plan ) );
			}

			$r['set'][] = $ps;
		}

		return $r;
	}

	function readMIs()
	{
		$db = &JFactory::getDBO();

		$lang = JFactory::getLanguage();

		$r = array();
		$r['head'] = "Micro Integration";
		$r['type'] = "";

		$micursor = '';
		foreach ( $this->milist as $miobj ) {
			$mi = new microIntegration();
			$mi->load( $miobj->id );
			$mi->callIntegration(true);

			if ( $miobj->class_name != $micursor ) {
				$readout[] = $r;
				unset($r);
				$r = array();
				if ( !empty( $mi->info ) ) {
					$r['head'] = $mi->info['name'];
				} else {
					$r['head'] = $miobj->class_name;
				}
				$r['type'] = "table";
				$r['sub'] = true;
				$r['set'] = array();

				$r['def'] = array (
					"ID" => array( 'id' ),
					"Published" => array( 'active', 'bool' ),
					"Visible" => array( 'visible', 'bool' ),
					"Name" => array( 'name', 'smartlimit haslink', 'editMicroIntegration', 'id' ),
					"Desc" => array( 'desc', 'notags smartlimit' ),
					"Exp Action" => array( 'auto_check', 'bool' ),
					"PreExp Action" => array( 'pre_exp_check' ),
					"UserChange Action" => array( 'on_userchange', 'bool' )
					);

				$settings = $mi->getSettings();

				if ( isset( $settings['lists'] ) ) {
					unset( $settings['lists'] );
				}

				if ( !empty( $settings ) ) {
					foreach ( $settings as $sname => $setting ) {
						if ( is_numeric( $sname ) || ( strpos( $sname, 'aectab_' ) !== false ) ) {
							continue;
						}

						$name =  'MI_' . strtoupper( $miobj->class_name ) . '_' . strtoupper( $sname ) .'_NAME';
				
						if ( $lang->hasKey( $name ) ) {
							$r['def'][JText::_($name)] = array( array( 'settings', $sname ), 'notags smartlimit' );
						} else {
							$r['def'][$sname] = array( array( 'settings', $sname ), 'notags smartlimit' );
						}
					}
				}
			}

			$ps = array();
			foreach ( $r['def'] as $nn => $def ) {
				$ps = array_merge( $ps, $this->conversionHelper( $def, $mi ) );
			}

			$r['set'][] = $ps;

			$micursor = $miobj->class_name;
		}

		$readout[] = $r;

		return $readout;
	}

	function conversionHelperHTML( $content, $obj )
	{
		$cc = $content[0];

		if ( is_array( $cc ) ) {
			$dname = $cc[0].'_'.$cc[1];
			if ( !isset( $obj->{$cc[0]}[$cc[1]] ) ) {
				return array( $dname => '' );
			}
			$dvalue = $obj->{$cc[0]}[$cc[1]];
		} else {
			$dname = $cc;
			if ( !isset( $obj->{$cc} ) ) {
				return array( $dname => '' );
			}
			$dvalue = $obj->{$cc};
		}

		if ( isset( $content[1] ) ) {
			$type = $content[1];
		} else {
			$type = null;
		}

		if ( isset( $_POST['noformat_newlines'] ) ) {
			$nnl = ', ';
		} else {
			$nnl = ',<br />';
		}

		if ( !empty( $type ) ) {
			$types = explode( ' ', $type );

			if ( is_array( $dvalue ) ) {
				foreach ( $dvalue as $dv ) {
					if ( is_array( $dv ) ) {
						return array( $dname => '' );
					}
				}

				$dvalue = implode( ',', $dvalue );
			}

			foreach ( $types as $tt ) {
				switch ( $tt ) {
					case 'notags':
						$dvalue = strip_tags( $dvalue );
						break;
					case 'limit32':
						$dvalue = substr( $dvalue, 0, 32 );
						break;
					case 'smartlimit':
						if ( isset( $_POST['truncation_length'] ) ) {
							$truncation = $_POST['truncation_length'];
						} else {
							$truncation = 42;
						}

						if ( $truncation > 12 ) {
							$tls = 12;
						} else {
							$tls = $truncation/2;
						}

						if ( is_array( $dvalue ) ) {
							$vv = array();
							foreach ( $dvalue as $val ) {
								if ( strlen( $val ) > $truncation ) {
									$vv[] = substr( $val, 0, $truncation-$tls ) . '<strong title="' . htmlentities($val) . '">[...]</strong>' . substr( $val, -$tls, $tls );
								} else {
									$vv[] = $val;
								}
							}
							$dvalue = implode( $nnl, $vv );
						} else {
							if ( strlen( $dvalue ) > $truncation ) {
								$dvalue = substr( $dvalue, 0, $truncation-$tls ) . '<strong title="' . htmlentities($dvalue) . '">[...]</strong>' . substr( $dvalue, -$tls, $tls );
							}
						}
						break;
					case 'list':
						if ( is_array( $dvalue ) ) {
							$vv = array();
							foreach ( $dvalue as $val ) {
								if ( $val == 0 ) {
									$vv[] = '--';
								} else {
									$vv[] = "#" . $val . ":&nbsp;<strong>" . $this->lists[$content[2]][$val] . "</strong>";
								}
							}
							$dvalue = implode( $nnl, $vv );
						} else {
							if ( $dvalue == 0 ) {
								$dvalue = '--';
							} else {
								$dvalue = "#" . $dvalue . ":&nbsp;<strong>" . $this->lists[$content[2]][$dvalue] . "</strong>";
							}
						}
						break;
					case 'haslink':
						if ( isset( $content[3] ) ) {
							$tasklink = $content[2] . "&amp;" . $content[3] . "=" . $obj->{$content[3]};
							$dvalue = AECToolbox::backendTaskLink( $tasklink, $dvalue );
						} else {
							$dvalue = AECToolbox::backendTaskLink( $content[2], $dvalue );
						}
						break;
				}
			}
		}

		return array( $dname => $dvalue );
	}

	function conversionHelperCSV( $content, $obj )
	{
		$cc = $content[0];

		if ( is_array( $cc ) ) {
			$dname = $cc[0].'_'.$cc[1];
			if ( !isset( $obj->{$cc[0]}[$cc[1]] ) ) {
				return array( $dname => '' );
			}
			$dvalue = $obj->{$cc[0]}[$cc[1]];
		} else {
			$dname = $cc;
			if ( !isset( $obj->{$cc} ) ) {
				return array( $dname => '' );
			}
			$dvalue = $obj->{$cc};
		}

		if ( isset( $content[1] ) ) {
			$type = $content[1];
		} else {
			$type = null;
		}

		if ( isset( $_POST['noformat_newlines'] ) ) {
			$nnl = ', ';
		} else {
			$nnl = ',' . "\n";
		}

		if ( !empty( $type ) ) {
			$types = explode( ' ', $type );

			foreach ( $types as $tt ) {
				switch ( $tt ) {
					case 'notags':
						$dvalue = strip_tags( $dvalue );
						break;
					case 'limit32':
						$dvalue = substr( $dvalue, 0, 32 );
						break;
					case 'smartlimit':
						if ( isset( $_POST['truncation_length'] ) ) {
							$truncation = $_POST['truncation_length'];
						} else {
							$truncation = 42;
						}

						if ( $truncation > 12 ) {
							$tls = 12;
						} else {
							$tls = $truncation/2;
						}

						if ( is_array( $dvalue ) ) {
							$vv = array();
							foreach ( $dvalue as $val ) {
								if ( strlen( $val ) > $truncation ) {
									$vv[] = substr( $val, 0, $truncation-$tls ) . '[...]' . substr( $val, -$tls, $tls );
								} else {
									$vv[] = $val;
								}
							}
							$dvalue = implode( $nnl, $vv );
						} else {
							if ( strlen( $dvalue ) > $truncation ) {
								$dvalue = substr( $dvalue, 0, $truncation-$tls ) . '[...]' . substr( $dvalue, -$tls, $tls );
							}
						}
						break;
					case 'list':
						if ( is_array( $dvalue ) ) {
							$vv = array();
							foreach ( $dvalue as $val ) {
								if ( $dvalue == 0 ) {
									$vv[] = '--';
								} else {
									$vv[] = "#" . $val . ": " . $this->lists[$content[2]][$val];
								}
							}
							$dvalue = implode( $nnl, $vv );
						} else {
							if ( $dvalue == 0 ) {
								$dvalue = '--';
							} else {
								$dvalue = "#" . $dvalue . ": " . $this->lists[$content[2]][$dvalue];
							}
						}
						break;
					case 'haslink':
						$dvalue = $dvalue;
						break;
				}
			}
		}

		return array( $dname => $dvalue );
	}
}

?>

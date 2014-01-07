<?php
/**
 * @version $Id: mi_pardot_marketing.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Pardot Marketing
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_pardot_marketing extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_PARDOT_MARKETING');
		$info['desc'] = JText::_('AEC_MI_DESC_PARDOT_MARKETING');
		$info['type'] = array( 'services.external' );

		return $info;
	}

	function Settings()
	{
        $settings = array();
        $settings['email']					= array( 'inputC' );
        $settings['password']				= array( 'inputC' );
        $settings['user_key']				= array( 'inputC' );
        $settings['prospect_details']		= array( 'inputD' );
        $settings['pardot_lists']			= array( 'inputD' );
        $settings['pardot_lists_del']		= array( 'inputD' );
        $settings['pardot_lists_exp']		= array( 'inputD' );
        $settings['pardot_lists_exp_del']	= array( 'inputD' );
		$settings['rebuild']				= array( 'toggle' );
		$settings['remove']					= array( 'toggle' );

		if ( !empty( $this->settings['email'] ) && !empty( $this->settings['password'] ) && !empty( $this->settings['user_key'] ) ) {
			$db = &JFactory::getDBO();

			$pc = new PardotConnector();
			$pc->get( $this->settings );

        	$settings['api_key']		= array( 'p', null, null, "API Key currently in use: ".$pc->api_key );
		} else {
			$settings['api_key']		= array( 'p', null, null, "API Key currently in use: Please fill in the above details to request an API key" );
		}

		$rewriteswitches				= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );
		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function checkInstallation()
	{
		$db = &JFactory::getDBO();

		$app = JFactory::getApplication();

		$tables	= array();
		$tables	= $db->getTableList();

		return in_array( $app->getCfg( 'dbprefix' ) . 'acctexp_mi_pardot_marketing', $tables );
	}

	function install()
	{
		$db = &JFactory::getDBO();

		$query = 'CREATE TABLE IF NOT EXISTS `#__acctexp_mi_pardot_marketing` ('
		. '`id` int(11) NOT NULL auto_increment,'
		. '`created_on` datetime NOT NULL default \'0000-00-00 00:00:00\','
		. '`api_key` text NULL,'
		. ' PRIMARY KEY (`id`)'
		. ')'
		;
		$db->setQuery( $query );
		$db->query();

		return;
	}

	function on_userchange_action( $request )
	{
		$db = &JFactory::getDBO();

		$pc = new PardotConnector();
		$pc->get( $this->settings );

		$lists = array( 'add' => array(), 'remove' => array() );

		if ( !empty( $this->settings['pardot_lists'.$request->area] ) ) {
			$li = explode( "\n", $this->settings['pardot_lists'.$request->area] );
			
			foreach ( $li as $k ) {
				$lists['add'][] = $k;
			}
		}

		$uparams = $request->metaUser->meta->getCustomParams();

		$pparams = array();

		if ( !empty( $this->settings['prospect_details'] ) ) {
			$details = explode( "\n", AECToolbox::rewriteEngineRQ( $this->settings['prospect_details'], $request ) );

			foreach ( $details as $chunk ) {
				$k = explode( '=', $chunk, 2 );

				$kk = trim( $k[0] );
				$pparams[$kk] = trim( $k[1] );
			}
		}

		$result = new stdClass();
		if ( empty( $uparams['mi_pardot_marketing_prospect_id'] ) ) {
			$result = $pc->createUser( $this->settings, $request->metaUser->cmsUser->email, $lists, $pparams );

			if ( !empty( $result->prospect->id ) ) {
				// Completely new user, just create
				$request->metaUser->meta->setCustomParams( array( 'mi_pardot_marketing_prospect_id' => ( (int) $result->prospect->id ) ) );

				return true;
			} elseif( !empty( $result->err ) ) {
				// We have an entry, try to find the prospect
				if ( strpos( $result->err, 'email address already exists' ) !== false ) {
					$result = $pc->readProspect( $this->settings, 'email', $request->metaUser->cmsUser->email );

					if ( !empty( $result->prospect->id ) ) {
						// Found! Store it.
						$request->metaUser->meta->setCustomParams( array( 'mi_pardot_marketing_prospect_id' => ( (int) $result->prospect->id ) ) );
						$request->metaUser->meta->storeload();

						$id = ( (int) $result->prospect->id );
					}
				}
			}
		} else {
			$id = $uparams['mi_pardot_marketing_prospect_id'];

			$result = $pc->readProspect( $this->settings, 'id', $uparams['mi_pardot_marketing_prospect_id'] );
		}

		return $pc->updateUser( $this->settings, 'id', $id, $lists, $pparams );
	}

	function relayAction( $request )
	{
		if ( ( $request->action != 'action' ) || ( $request->action != 'expiration_action' ) ) {
			return null;
		}

		$db = &JFactory::getDBO();

		$pc = new PardotConnector();
		$pc->get( $this->settings );

		$lists = array( 'add' => array(), 'remove' => array() );

		if ( !empty( $this->settings['pardot_lists'.$request->area] ) ) {
			$li = explode( "\n", $this->settings['pardot_lists'.$request->area] );
			
			foreach ( $li as $k ) {
				$lists['add'][] = $k;
			}
		}

		$uparams = $request->metaUser->meta->getCustomParams();

		$pparams = array();

		if ( !empty( $this->settings['prospect_details'] ) ) {
			$details = explode( "\n", AECToolbox::rewriteEngineRQ( $this->settings['prospect_details'], $request ) );

			foreach ( $details as $chunk ) {
				$k = explode( '=', $chunk, 2 );

				$kk = trim( $k[0] );
				$pparams[$kk] = trim( $k[1] );
			}
		}

		$result = new stdClass();
		if ( empty( $uparams['mi_pardot_marketing_prospect_id'] ) ) {
			$result = $pc->createUser( $this->settings, $request->metaUser->cmsUser->email, $lists, $pparams );

			if ( !empty( $result->prospect->id ) ) {
				// Completely new user, just create
				$request->metaUser->meta->setCustomParams( array( 'mi_pardot_marketing_prospect_id' => ( (int) $result->prospect->id ) ) );

				return true;
			} elseif( !empty( $result->err ) ) {
				// We have an entry, try to find the prospect
				if ( strpos( $result->err, 'email address already exists' ) !== false ) {
					$result = $pc->readProspect( $this->settings, 'email', $request->metaUser->cmsUser->email );

					if ( !empty( $result->prospect->id ) ) {
						// Found! Store it.
						$request->metaUser->meta->setCustomParams( array( 'mi_pardot_marketing_prospect_id' => ( (int) $result->prospect->id ) ) );
						$request->metaUser->meta->storeload();

						$id = ( (int) $result->prospect->id );
					}
				}
			}
		} else {
			$id = $uparams['mi_pardot_marketing_prospect_id'];

			$result = $pc->readProspect( $this->settings, 'id', $uparams['mi_pardot_marketing_prospect_id'] );
		}

		// Remove lists that we already have assigned from the query
		if ( !empty( $result->lists ) ) {
			foreach ( $result->lists as $list ) {
				foreach ( $lists['add'] as $k => $v ) {
					if ( $list->id == $v ) {
						unset( $lists['add'][$k] );
					}
				}
			}
		}

		if ( !empty( $settings['pardot_lists'.$request->area.'_del'] ) ) {
			$li = explode( "\n", $settings['pardot_lists'.$request->area.'_del'] );

			foreach ( $li as $k ) {
				$lists['remove'][] = $k;
			}
		}

		if ( !empty( $lists['add'] ) || !empty( $lists['remove'] ) ) {
			return $pc->updateUser( $this->settings, 'id', $id, $lists, $pparams );
		}
	}
}

class PardotConnector extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $created_on			= null;
	/** @var int */
	var $api_key			= null;

	function PardotConnector()
	{
		parent::__construct( '#__acctexp_mi_pardot_marketing', 'id' );
	}

	function get( $settings, $force=false )
	{
	 	$this->load(1);

		if ( empty( $this->id ) ) {
			$db = &JFactory::getDBO();

			$query = 'INSERT INTO #__acctexp_mi_pardot_marketing'
			. ' VALUES( \'1\', \'' . gmdate( 'Y-m-d H:i:s' ) . '\', \'\' )'
			;
			$db->setQuery( $query );
			$db->query() or die( $db->stderr() );

			$this->load(1);
		}

		$diff = intval( ( (int) gmdate('U') ) - strtotime( $this->created_on ) );

		// Check whether key is null or old
		if ( ( $diff > 2700 ) || empty( $this->api_key ) || ( strpos( $this->api_key, 'ERROR:' ) !== false ) ) {
			$response = $this->getAPIkey( $settings, $force );

			if ( isset( $response->api_key )  ) {
				$this->api_key = (string) $response->api_key;
			} else {
				$this->api_key = 'ERROR: ' . $response->err;
			}

			$this->created_on = gmdate( 'Y-m-d H:i:s' );

			$this->storeload();
		}
	}

	function getAPIkey( $settings, $forced=false )
	{
		$params = array(	'email' => $settings['email'],
							'password' => $settings['password'],
							'user_key' => $settings['user_key']
						);

		return $this->fetch( $settings, 'login', null, $params, $forced );
	}

	function createUser( $settings, $email, $lists, $p=array() )
	{
		$params = array(	'user_key' => $settings['user_key'],
							'api_key' => $this->api_key
						);

		if ( !empty( $p ) ) {
			foreach ( $p as $k => $v ) {
				$params[$k] = $v;
			}
		}

		if ( !empty( $lists['add'] ) ) {
			foreach ( $lists['add'] as $k ) {
				$params['list_'.$k] = "1";
			}
		}

		if ( !empty( $lists['remove'] ) ) {
			foreach ( $lists['remove'] as $k ) {
				$params['list_'.$k] = "0";
			}
		}

		return $this->fetch( $settings, 'prospect', 'do/create/email/'.$email, $params );
	}

	function updateUser( $settings, $id_type, $id, $lists, $p=array() )
	{
		$params = array(	'user_key' => $settings['user_key'],
							'api_key' => $this->api_key
						);

		if ( !empty( $p ) ) {
			foreach ( $p as $k => $v ) {
				$params[$k] = $v;
			}
		}

		if ( !empty( $lists['add'] ) ) {
			foreach ( $lists['add'] as $k ) {
				$params['list_'.$k] = "1";
			}
		}

		if ( !empty( $lists['remove'] ) ) {
			foreach ( $lists['remove'] as $k ) {
				$params['list_'.$k] = "0";
			}
		}

		return $this->fetch( $settings, 'prospect', 'do/update/'.$id_type.'/'.$id, $params );
	}

	function readProspect( $settings, $id_type, $id )
	{
		$params = array(	'user_key' => $settings['user_key'],
							'api_key' => $this->api_key
						);

		if ( !empty( $lists['add'] ) ) {
			foreach ( $lists['add'] as $k ) {
				$params[$k] = "1";
			}
		}

		if ( !empty( $lists['remove'] ) ) {
			foreach ( $lists['remove'] as $k ) {
				$params[$k] = "0";
			}
		}

		return $this->fetch( $settings, 'prospect', 'do/read/'.$id_type.'/'.$id, $params );
	}

	function fetch( $settings, $area, $cmd, $params, $retry=false )
	{
		global $aecConfig;

		$url = 'https://pi.pardot.com/api/' . $area . '/version/3';

		if ( !empty( $cmd ) ) {
			 $url .= '/'.$cmd;
		}

		if ( !empty( $params ) ) {
			$url .= '?';

			$ps = array();
			foreach ( $params as $k => $v ) {
				$ps[] = urlencode( $k ) . "=" . urlencode( $v );
			}

			$url .= implode( '&', $ps );
		}

		$url_parsed = parse_url( $url );

		$host = $url_parsed["host"];

		if ( empty( $url_parsed["port"] ) ) {
			$port = 80;
		} else {
			$port = $url_parsed["port"];
		}

		$path = $url_parsed["path"];

		// Prevent 400 Error
		if ( empty( $path ) ) {
			$path = "/";
		}

		if ( $url_parsed["query"] != "" ) {
			$path .= "?".$url_parsed["query"];
		}

		if ( $aecConfig->cfg['curl_default'] ) {
			$response = processor::doTheCurl( $url, '' );
		} else {
			$response = processor::doTheHttp( $url, $path, '', $port );
		}

		if ( ( strpos( $response, 'Invalid API key' ) !== false ) && !$retry ) {
			$this->get( $settings, true );

			return $this->fetch( $settings, $area, $cmd, $params, true );
		}

		return simplexml_load_string( $response );
	}
}

?>

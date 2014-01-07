<?php
/**
 * @version $Id: mi_iproperty.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - iProperty
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_iproperty
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_IPROPERTY_NAME');
		$info['desc'] = JText::_('AEC_MI_IPROPERTY_DESC');
		$info['type'] = array( 'vertical_markets.real_estate', 'vendor.the_thinkery' );

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

        $settings = array();

		$settings['create_agent']		= array( 'toggle' );
		$settings['agent_fields']		= array( 'inputD' );

		$settings['set_agentlistings']	= array( 'inputA' );
		$settings['add_agentlistings']	= array( 'inputA' );
		$settings['set_agentflistings']	= array( 'inputA' );
		$settings['add_agentflistings']	= array( 'inputA' );

		$settings['update_agent']		= array( 'toggle' );
		$settings['update_afields']		= array( 'inputD' );

		$settings['publish_all']		= array( 'toggle' );
		$settings['unpublish_all']		= array( 'toggle' );

		$settings['create_company']		= array( 'toggle' );
		$settings['company_fields']		= array( 'inputD' );

		$settings['update_company']		= array( 'toggle' );
		$settings['update_cfields']		= array( 'inputD' );

		$settings['set_listings']		= array( 'inputA' );
		$settings['add_listings']		= array( 'inputA' );
		$settings['set_flistings']		= array( 'inputA' );
		$settings['add_flistings']		= array( 'inputA' );

		$settings['set_agents']			= array( 'inputA' );
		$settings['add_agents']			= array( 'inputA' );
		$settings['set_fagents']		= array( 'inputA' );
		$settings['add_fagents']		= array( 'inputA' );

		$settings['set_images']			= array( 'inputA' );
		$settings['add_images']			= array( 'inputA' );

		$settings['rebuild']			= array( 'toggle' );
		$settings['remove']				= array( 'toggle' );

		$rewriteswitches				= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );
		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function Defaults()
	{
		$defaults = array();
		$defaults['agent_fields']	= "fname=[[user_first_name]]\nlname=[[user_last_name]]\nemail=[[user_email]]";
		$defaults['company_fields']	= "name=[[user_name]]\ndescription=\nemail=[[user_email]]";

		return $defaults;
	}

	function action( $request )
	{
		$agent = $this->getAgent( $request->metaUser->userid );

		if ( empty( $agent->id ) && $this->settings['create_agent'] && !empty( $this->settings['agent_fields'] ) ) {
			$agent = $this->createAgent( $request );
		}

		if ( empty( $agent->id ) ) {
			return null;
		}

		if ( !empty( $this->settings['publish_all'] ) ) {
			$this->publishProperties( $agent->id );
		}

		$agent = $this->updateAgent( $agent, $request );

		if ( !empty( $agent->company ) ) {
			$company = $this->getCompany( $agent->company );
		}

		if ( empty( $company->id ) && $this->settings['create_company'] && !empty( $this->settings['company_fields'] ) ) {
			$company = $this->createCompany( $request, $agent );
		}

		if ( empty( $agent->company ) && !empty( $company->id ) ) {
			$agent->company = $company->id;
		}

		if ( $company->id ) {
			$company = $this->updateCompany( $company, $request );

			$this->storeObject( $company, 'companies' );
		}

		if ( $agent->id ) {
			$this->storeObject( $agent, 'agents' );
		}

		return true;
	}

	function expiration_action( $request )
	{
		if ( !empty( $this->settings['unpublish_all'] ) ) {
			$agent = $this->getAgent( $request->metaUser->userid );

			if ( !empty( $agent->id ) ) {
				return $this->unpublishProperties( $agent->id );
			}
		}

		return null;
	}

	function createAgent( $request )
	{
		$fields = $this->convertSettings( $this->settings['agent_fields'], $request );

		$fields['user_id'] = $request->metaUser->userid;

		if ( empty( $fields['fname'] ) || empty( $fields['lname'] ) ) {
			return false;
		}

		if ( empty( $fields['alias'] ) ) {
			$fields['alias'] = $fields['fname']." ".$fields['lname'];
		}

		$fields['alias'] = JApplication::stringURLSafe( $fields['alias'] );

		$fields['fname'] = htmlspecialchars_decode($fields['fname'], ENT_QUOTES);
        $fields['lname'] = htmlspecialchars_decode($fields['lname'], ENT_QUOTES);

		$this->createQuery( $fields, 'agents' );

		return $this->getAgent( $request->metaUser->userid );
	}

	function updateAgent( $agent, $request )
	{
		if ( !empty( $this->settings['update_agent'] ) && !empty( $this->settings['update_afields'] ) ) {
			$agent = $this->mergeObject( $agent, $this->convertSettings( $this->settings['update_afields'], $request ) );
		}

		if ( !empty( $this->settings['set_agentlistings'] ) ) {
			$agent->params['maxlistings'] = $this->settings['set_agentlistings'];
		} elseif ( !empty( $this->settings['add_agentlistings'] ) ) {
			if ( isset( $agent->params['maxlistings'] ) ) {
				$agent->params['maxlistings'] += $this->settings['add_agentlistings'];
			} else {
				$agent->params['maxlistings'] = $this->settings['add_agentlistings'];
			}
		}

		if ( !empty( $this->settings['set_agentflistings'] ) ) {
			$agent->params['maxflistings'] = $this->settings['set_agentflistings'];
		} elseif ( !empty( $this->settings['add_agentflistings'] ) ) {
			if ( isset( $agent->params['maxlistings'] ) ) {
				$agent->params['maxflistings'] += $this->settings['add_agentflistings'];
			} else {
				$agent->params['maxflistings'] = $this->settings['add_agentflistings'];
			}
		}

		return $agent;
	}

	function createCompany( $request )
	{
		$fields = $this->convertSettings( $this->settings['company_fields'], $request );

		if ( empty( $fields['name'] ) ) {
			return false;
		}

		if ( empty( $fields['alias'] ) ) {
			$fields['alias'] = $fields['name'];
		}

		$fields['alias'] = JApplication::stringURLSafe( $fields['alias'] );

		$fields['name'] = htmlspecialchars_decode($fields['name'], ENT_QUOTES);

		$this->createQuery( $fields, 'companies' );

		return $this->getCompanyByName( $fields['name'] );
	}

	function updateCompany( $company, $request )
	{
		if ( !empty( $this->settings['update_company'] ) && !empty( $this->settings['update_cfields'] ) ) {
			$company = $this->mergeObject( $company, $this->convertSettings( $this->settings['update_cfields'], $request ) );
		}

		if ( !empty( $this->settings['set_listings'] ) ) {
			$company->params['maxlistings'] = $this->settings['set_listings'];
		} elseif ( !empty( $this->settings['add_listings'] ) ) {
			if ( isset( $company->params['maxlistings'] ) ) {
				$company->params['maxlistings'] += $this->settings['add_listings'];
			} else {
				$company->params['maxlistings'] = $this->settings['add_listings'];
			}
		}

		if ( !empty( $this->settings['set_flistings'] ) ) {
			$company->params['maxflistings'] = $this->settings['set_flistings'];
		} elseif ( !empty( $this->settings['add_flistings'] ) ) {
			if ( isset( $company->params['maxlistings'] ) ) {
				$company->params['maxflistings'] += $this->settings['add_flistings'];
			} else {
				$company->params['maxflistings'] = $this->settings['add_flistings'];
			}
		}

		if ( !empty( $this->settings['set_agents'] ) ) {
			$company->params['maxagents'] = $this->settings['set_agents'];
		} elseif ( !empty( $this->settings['add_agents'] ) ) {
			if ( isset( $company->params['maxagents'] ) ) {
				$company->params['maxagents'] += $this->settings['add_agents'];
			} else {
				$company->params['maxagents'] = $this->settings['add_agents'];
			}
		}

		if ( !empty( $this->settings['set_fagents'] ) ) {
			$company->params['maxfagents'] = $this->settings['set_fagents'];
		} elseif ( !empty( $this->settings['add_fagents'] ) ) {
			if ( isset( $company->params['maxfagents'] ) ) {
				$company->params['maxfagents'] += $this->settings['add_fagents'];
			} else {
				$company->params['maxfagents'] = $this->settings['add_fagents'];
			}
		}

		if ( !empty( $this->settings['set_images'] ) ) {
			$company->params['maximgs'] = $this->settings['set_images'];
		} elseif ( !empty( $this->settings['add_images'] ) ) {
			if ( isset( $company->params['maxagents'] ) ) {
				$company->params['maximgs'] += $this->settings['add_images'];
			} else {
				$company->params['maximgs'] = $this->settings['add_images'];
			}
		}

		return $company;
	}

	function convertSettings( $field, $request )
	{
		$fieldlist = explode( "\n", $field );

		$array = array();
		foreach ( $fieldlist as $content ) {
			$c = explode( '=', $content, 2 );

			if ( !empty( $c[0] ) ) {
				if ( !empty( $c[1] ) ) {
					$array[$c[0]] = trim( AECToolbox::rewriteEngineRQ( $c[1], $request ) );
				} else {
					$array[$c[0]] = "";
				}
			}
		}

		return $array;
	}

	function mergeObject( $object, $settings )
	{
		foreach ( $settings as $k => $v ) {
			if ( isset( $object->$k ) ) {
				$object->$k = $v;
			}
		}

		return $object;
	}

	function createQuery( $fields, $table )
	{
		if ( empty( $fields ) ) {
			return false;
		}

		$db = &JFactory::getDBO();

		$query  = 'INSERT INTO #__iproperty_' . $table
				. ' (' . implode(', ', array_keys($fields) ) . ') '
				. ' VALUES(\'' . implode('\', \'', array_values($fields) ) . '\')'
				;
		$db->setQuery( $query );

		return $db->query();
	}

	function getAgent( $id )
	{
		return $this->getObject( 'agents', 'user_id', $id );
	}

	function getCompany( $id )
	{
		return $this->getObject( 'companies', 'id', $id );
	}

	function getCompanyByName( $name )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT id FROM #__iproperty_companies'
		. ' WHERE `name` = \'' . xJ::escape( $db, $name ) . '\''
		;
		$db->setQuery( $query );
		
		return $this->getCompany( $db->loadResult() );
	}

	function getObject( $table, $field, $id )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT * FROM #__iproperty_' . $table
		. ' WHERE `' . $field . '` = \'' . xJ::escape( $db, $id ) . '\''
		;
		$db->setQuery( $query );

		$object = $db->loadObject();

		if ( isset( $object->params ) ) {
			$object->params = json_decode( $object->params );

			if ( !is_array( $object->params ) ) {
				$object->params = array();
			}
		}

		return $object;
	}

	function storeObject( $object, $table )
	{
		$db = &JFactory::getDBO();

		if ( is_array( $object->params ) ) {
			$object->params = json_encode( $object->params );
		}

		$vars = get_object_vars( $object );

		$fields = array();
		$values = array();
		foreach ( $vars as $k => $v ) {
			if ( ( $k != 'id' ) && ( $k != 'ip_source' ) ) {
				$updates[] = '`' . $k . '` = \'' .  xJ::escape( $db, $v ) . '\'';
			}
		}

		$query  = 'UPDATE #__iproperty_' . $table
				. ' SET ' . implode(', ', $updates ) . ' '
				. ' WHERE id = \'' .  xJ::escape( $db, $object->id ) . '\''
				;
		$db->setQuery( $query );
		return $db->query();
	}

	function publishProperties( $agentid )
	{
		$properties = $this->getProperties( $agentid );

		if ( !empty( $properties ) ) {
			$db = &JFactory::getDBO();

			$query = 'UPDATE #__iproperty'
					. ' SET `state` = \'1\''
					. ' WHERE `id` IN (' . implode( ',', $properties ) . ')'
					;
			$db->setQuery( $query );
			$db->query();
		}
	}

	function unpublishProperties( $agentid )
	{
		$properties = $this->getProperties( $agentid );

		if ( !empty( $properties ) ) {
			$db = &JFactory::getDBO();

			$query = 'UPDATE #__iproperty'
					. ' SET `state` = \'0\''
					. ' WHERE `id` IN (' . implode( ',', $properties ) . ')'
					;
			$db->setQuery( $query );
			$db->query();
		}
	}

	function getProperties( $agentid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT prop_id'
				. ' FROM #__iproperty_agentmid'
				. ' WHERE `agent_id` = \'' . (int) $agentid . '\''
				;
		$db->setQuery( $query );
		return xJ::getDBArray( $db );
	}

}

?>

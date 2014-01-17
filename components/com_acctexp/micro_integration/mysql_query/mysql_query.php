<?php
/**
 * @version $Id: mi_mysql_query.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - MySQL Query
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_mysql_query
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_MYSQL');
		$info['desc'] = JText::_('AEC_MI_DESC_MYSQL');
		$info['type'] = array( 'basic.database', 'system', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
        $settings = array();
		$settings['use_altdb']		= array( 'toggle' );
		$settings['dbms']			= array( 'inputC' );
		$settings['dbhost']			= array( 'inputC' );
		$settings['dbuser']			= array( 'inputC' );
		$settings['dbpasswd']		= array( 'inputC' );
		$settings['dbname']			= array( 'inputC' );
		$settings['table_prefix']	= array( 'inputC' );

        $settings['query']			= array( 'inputD' );
        $settings['query_exp']		= array( 'inputD' );
        $settings['query_pre_exp']	= array( 'inputD' );

		$rewriteswitches			= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

		$settings					= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function relayAction( $request )
	{
		if ( isset( $this->settings['query'.$request->area] ) ) {
	        if ( $this->settings['use_altdb'] ) {
		        $options = array(	'driver'	=> $this->settings['dbms'],
									'host'		=> $this->settings['dbhost'],
									'user'		=> $this->settings['dbuser'],
									'password'	=> $this->settings['dbpasswd'],
									'database'	=> $this->settings['dbname'],
									'prefix'	=> $this->settings['table_prefix']
									);

		        $db =& JDatabase::getInstance($options);
	        } else {
	        	$db = &JFactory::getDBO();
	        }

			$query = AECToolbox::rewriteEngineRQ( $this->settings['query'.$request->area], $request );

			$db->setQuery( $query );
			if ( !$db->queryBatch( false ) ) {
				$this->error = "MYSQL ERROR: " . $db->stderr();
			}
		}

		return true;
	}

}
?>

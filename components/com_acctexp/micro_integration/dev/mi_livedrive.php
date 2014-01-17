﻿<?php
// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_livedrive extends MI
{

	function Info()
	{
		$info = array();
		$info['name'] = 'LiveDrive MI';
		$info['desc'] = 'Full Description';

		return $info;
	}

	function checkInstallation()
	{
		$database = &JFactory::getDBO();

		$app = JFactory::getApplication();

		$tables	= array();
		$tables	= $database->getTableList();

		return in_array($app->getCfg( 'dbprefix' )."_acctexp_mi_livedrive", $tables);
	}

	
	function install()
	{
		$database = &JFactory::getDBO();

		$query =	"CREATE TABLE IF NOT EXISTS `#__livedrive_users` (" . "\n" .
					"`id` bigint(20) NOT NULL auto_increment," . "\n" .
					"`userid` bigint(20) NOT NULL," . "\n" .
					"`active` int(4) NOT NULL default '1'," . "\n" .
					"`params` text NULL," . "\n" .
                                        "`user_name` varchar(250) NOT NULL," . "\n" .
		                        "`subscription_date` double NOT NULL," . "\n" .
					"PRIMARY KEY  (`id`)" . "\n" .
					") COLLATE utf8_general_ci;";
		$database->setQuery( $query );
		$database->query();
		return;
	}


	function Settings()
	{
		$settings = array();
		$settings['ld_duration_period']	= array("inputA", "Duration", "description");
		$settings['ld_duration_unit']	= array("fieldset", "Duration Unit", "description3");
		$settings['ld_api_key']			= array("inputE", "Api Key", "description2");
		$settings['user_details']		= array( "inputD", "User Details", "description4" );

		return $settings;
	}

	function action( $request )
	{
		$params = $request->metaUser->meta->custom_params;

		if ( !empty($params['temp_pw']) ) {
			$password = $params['temp_pw'];

			$request->metaUser->meta->custom_params['is_stored'] = true;

			unset( $request->metaUser->meta->custom_params['temp_pw'] );

			$request->metaUser->meta->storeload();
		}
	}

	function on_userchange_action( $request )
	{
		if ( $request->trace == 'registration' ) {
			$password = $this->getPWrequest( $request );

			$db = &JFactory::getDBO();

			$meta = new metaUserDB();
			$meta->loadUserid( $request->row->id );

			$params = $meta->custom_params;

			if ( empty( $meta->custom_params['is_stored'] ) && empty( $meta->custom_params['temp_pw']) && !empty( $request->row->password ) ) {
				$meta->custom_params['temp_pw'] = $password;
		        $meta->storeload();
    		}
		}
	}

}

?>

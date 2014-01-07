<?php

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

define('_MI_MI_COMMUNITYACL_CACL_GROUP_LIST_NAME','Community ACL Groups');
define('_MI_MI_COMMUNITYACL_CACL_ROLE_LIST_NAME','Community ACL Roles');
define('_MI_MI_COMMUNITYACL_CACL_FUNC_LIST_NAME','Community ACL Functions');

class mi_communityacl
{
	function Info()
	{
		$info = array();
		$info['name'] = 'Community ACL';
		$info['desc'] = 'corePHP Community ACL Micro Integration';

		return $info;
	}

	function detect_application()
	{
		return is_dir( JPATH_SITE . '/components/com_community_acl' );
	}

	function Settings()
	{

		$db = &JFactory::getDBO();

        $settings 						= array();
		$lists 							= array();
		$settings['cacl_group_list']	= array( 'list' );
		$settings['cacl_role_list']		= array( 'list' );
		$settings['cacl_func_list']		= array( 'list' );

		// Lets get our groups and prepare some javascript
		$query = 'SELECT id AS value, name AS text'
			. ' FROM `#__community_acl_groups`'
			. ' ORDER BY name'
			;
		$db->setQuery( $query );
		$groups = $db->loadObjectList();

		$javascript = "onchange=\"changeDynaList( 'cacl_role_list', grouproles, document.adminForm.cacl_group_list.options[document.adminForm.cacl_group_list.selectedIndex].value, 0, 0);\"";
		$settings['lists']['cacl_group_list'] = JHTML::_('select.genericlist',   $groups, 'cacl_group_list', ' class="inputbox" size="1" '.$javascript, 'value', 'text', $this->settings['cacl_group_list'] );

		if (count($groups) < 1)
			$settings['lists']['cacl_group_list'] = JText::_( 'There are no groups' );


		// Lets get our roles
		$query = 'SELECT id '
			. ' FROM `#__community_acl_groups`'
			. ' ORDER BY name'
			;
		$db->setQuery( $query );
		$groups = $db->loadObjectList();

		$query = 'SELECT id AS value, name AS text, group_id'
			. ' FROM `#__community_acl_roles`'
			. ' ORDER BY group_id, name'
			;
		$db->setQuery( $query );
		$roles = $db->loadObjectList();

		$tmp_arr = array();
		if (is_array($roles) && count($roles)) {
			$tmp_arr = array();
			foreach($groups as $group) {

				$z = 0;
				foreach($roles as $i=>$role){
					if ($role->group_id != $group->id)
						continue;
					$tmp_arr[] = array('group'=>$role->group_id, 'value'=>$role->value, 'text'=>$role->text);

					$z++;
				}
				if ($z == 0)
					$tmp_arr[] = array('group'=>$group->id, 'value'=>0, 'text'=>JText::_( 'None' ));
			}
		}


		$lists['cacl_rid_arr'] = $tmp_arr;
		$settings['lists']['cacl_role_list'] = JHTML::_('select.genericlist',   $roles, 'cacl_role_list', ' class="inputbox" size="1" ', 'value', 'text', $this->settings['cacl_role_list'] );

		if (count($roles) < 1)
			$settings['lists']['cacl_role_list'] = JText::_( 'There are no roles' );


		$query = 'SELECT id AS value, name AS text'
			. ' FROM `#__community_acl_functions`'
			. ' ORDER BY name'
			;
		$db->setQuery( $query );
		$functions[] = JHTML::_('select.option', '0', JText::_('None'), 'value', 'text');
		$functions = @array_merge($functions, $db->loadObjectList());

		$settings['lists']['cacl_func_list'] = JHTML::_('select.genericlist',   $functions, 'cacl_func_list', ' class="inputbox" size="1" ', 'value', 'text', $this->settings['cacl_func_list'] );
		if (count($functions) < 1)
			$settings['lists']['cacl_func_list'] = JText::_( 'There are no functions' );

		?>

		<script language="javascript" type="text/javascript">
				<!--

				var grouproles = new Array;

				<?php
				$i = 0;
				foreach ($lists['cacl_rid_arr'] as $k=>$v) {
					echo "grouproles[".$k++."] = new Array( '".addslashes( $v['group'] )."','".addslashes( $v['value'] )."','".addslashes( $v['text'] )."' );\n\t\t";
				}
				?>

				//-->
		</script>
		<?php

		return $settings;
	}


	function action( $request )
	{
		if ( is_dir( JPATH_SITE . '/components/com_community_acl' ) ) {
			require_once( JPATH_ADMINISTRATOR . '/components/com_community_acl/community_acl.class.php' );

			$db = &JFactory::getDBO();

			$settings = array();

			if ( !empty( $this->settings['cacl_group_list'] ) && !empty( $this->settings['cacl_role_list'] ) ) {
					$cacl_usr = new CACL_user($db);
					$cacl_usr->user_id       = $request->metaUser->userid;
					$cacl_usr->group_id  	 = (isset($this->settings['cacl_group_list'])? $this->settings['cacl_group_list']: 0);
					$cacl_usr->role_id       = (isset($this->settings['cacl_role_list'])? $this->settings['cacl_role_list']: 0);
					$cacl_usr->function_id   = (isset($this->settings['cacl_func_list'])? $this->settings['cacl_func_list']: 0);
					$cacl_usr->store();
			}
		}

		return true;
	}

	function expiration_action( $request )
	{
		$db = &JFactory::getDBO();

		// Lets delete the user from Community ACL
		$query = "DELETE FROM `#__community_acl_users` WHERE user_id = " . $request->metaUser->userid;
		$db->setQuery( $query );
		$db->query();

		return true;
	}
}
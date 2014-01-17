<?php
/**
 * @version $Id: acctexp.itemgroup.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class ItemGroupHandler
{
	function getGroups( $filter=null, $select=false )
	{
		$db = &JFactory::getDBO();

		if ( $select ) {
			$query = 'SELECT `id` AS value, `name` AS text FROM #__acctexp_itemgroups';
		} else {
			$query = 'SELECT id FROM #__acctexp_itemgroups';
		}

		if ( !empty( $filter ) ) {
			$query .= ' WHERE `id` NOT IN (' . implode( ',', $filter ) . ')';
		}

		$db->setQuery( $query );

		if ( $select ) {
			$rows = $db->loadObjectList();
		} else {
			$rows = xJ::getDBArray( $db );
		}

		return $rows;
	}

	function getTree()
	{
		$db = &JFactory::getDBO();

		// Filter out groups that have no relationship
		$query = 'SELECT id'
				. ' FROM #__acctexp_itemxgroup'
				. ' WHERE `type` = \'group\''
				;
		$db->setQuery( $query );
		$nitems = xJ::getDBArray( $db );

		$query = 'SELECT id'
				. ' FROM #__acctexp_itemgroups'
				. ( !empty( $nitems ) ? ' WHERE `id` NOT IN (' . implode( ',', $nitems ) . ')' : '' )
				;
		$db->setQuery( $query );
		$items = xJ::getDBArray( $db );

		$list = array();
		$tree = ItemGroupHandler::resolveTreeItem( 1 );

		ItemGroupHandler::indentList( $tree, $list );

		return $list;
	}

	function indentList( $tree, &$list, $indent=0 )
	{
		$list[] = array( $tree['id'], str_repeat( '&nbsp;', $indent ) . ( ( $indent > 0 ) ? '-' : '' ) . $tree['name'] . ' (#' . $tree['id'] . ')' );

		if ( isset( $tree['children'] ) ) {
			foreach ( $tree['children'] as $id => $co ) {
				ItemGroupHandler::indentList( $co, $list, $indent+1 );
			}
		}

		return $list;
	}

	function resolveTreeItem( $id )
	{
		$tree = array();
		$tree['id']		= $id;
		$tree['name']	= ItemGroupHandler::groupName( $id );

		$groups = ItemGroupHandler::getChildren( $id, 'group' );

		if ( !empty( $groups ) ) {
			// Has children, append them
			$tree['children'] = array();
			foreach ( $groups as $child_id ) {
				$tree['children'][] = ItemGroupHandler::resolveTreeItem( $child_id );
			}
		}

		return $tree;
	}

	function groupName( $groupid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT name'
				. ' FROM #__acctexp_itemgroups'
				. ' WHERE `id` = \'' . $groupid . '\''
				;
		$db->setQuery( $query );
		return $db->loadResult();
	}

	function groupColor( $groupid )
	{
		$db = &JFactory::getDBO();

		$group = new ItemGroup();
		$group->load( $groupid );

		return $group->params['color'];
	}

	function parentGroups( $item_id, $type='item' )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT group_id'
				. ' FROM #__acctexp_itemxgroup'
				. ' WHERE `type` = \'' . $type . '\''
				. ' AND `item_id` = \'' . $item_id . '\''
				;
		$db->setQuery( $query );
		return xJ::getDBArray( $db );
	}

	function updateChildRelation( $item_id, $groups, $type='item' )
	{
		$currentParents	= ItemGroupHandler::parentGroups( $item_id, $type );

		// Filtering out which groups will stay
		$keepGroups		= array_intersect( $currentParents, $groups );

		// Which will be newly added
		$addGroups		= array_diff( $groups, $keepGroups );
		ItemGroupHandler::setChildren( $item_id, $addGroups, $type );

		// And which removed
		$delGroups		= array_diff( $currentParents, $keepGroups, $addGroups );
		ItemGroupHandler::removeChildren( $item_id, $delGroups, $type );
	}

	function setChild( $child_id, $group_id, $type='item' )
	{
		if ( $type == 'group' ) {
			// Don't let a group be assigned to itself
			if ( ( $group_id == $child_id ) ) {
				continue;
			}

			$children = ItemGroupHandler::getChildren( $child_id, 'group' );

			// Don't allow circular assignment
			if ( in_array( $group_id, $children ) ) {
				continue;
			}
		}

		$ig = new itemXgroup();
		return $ig->createNew( $type, $child_id, $group_id );
	}

	function setChildren( $group_id, $children, $type='item' )
	{
		$success = false;
		foreach ( $children as $child_id ) {
			// Check bogus assignments
			if ( $type == 'group' ) {
				// Don't let a group be assigned to itself
				if ( ( $child_id == $group_id ) ) {
					continue;
				}

				$children = ItemGroupHandler::getChildren( $child_id, 'group' );

				// Don't allow circular assignment
				if ( in_array( $group_id, $children ) ) {
					continue;
				}
			}

			$ig = new itemXgroup();

			if ( !$ig->createNew( $type, $child_id, $group_id ) ) {
				return false;
			} else {
				$success = true;
			}
		}

		return $success;
	}

	function getParents( $item_id, $type='item' )
	{
		if ( ( $item_id == 1 ) && ( $type == 'group' ) ) {
			return array();
		}

		$itemParents = ItemGroupHandler::parentGroups( $item_id, $type );

		$allParents = $itemParents;
		foreach ( $itemParents as $parentid ) {
			$parentParents = ItemGroupHandler::getParents( $parentid, 'group' );

			if ( !empty( $parentParents ) ) {
				$allParents = array_merge( $allParents, $parentParents );
			}
		}

		$allParents = array_unique( $allParents );

		return $allParents;
	}

	function getChildren( $groups, $type )
	{
		$db = &JFactory::getDBO();

		$where = array();

		if ( is_array( $groups ) ) {
			$where[] = '`group_id` IN (' . implode( ',', $groups ) . ')';
		} else {
			$where[] = '`group_id` = ' . $groups . '';
		}

		if ( !empty( $type ) ) {
			$where[] = '`type` = \'' . $type . '\'';
		}

		$query = 'SELECT item_id'
				. ' FROM #__acctexp_itemxgroup'
				;

		if ( !empty( $where ) ) {
			$query .= ' WHERE ( ' . implode( ' AND ', $where ) . ' )';
		}

		$db->setQuery( $query );

		$result = xJ::getDBArray( $db );

		if ( !empty( $result ) ) {
			foreach ( $result as $k => $v ) {
				if ( empty( $v ) ) {
					unset($result[$k]);
				}
			}

			// Order results
			$query = 'SELECT id'
					. ' FROM #__acctexp_' . ( ( $type == 'group' ) ? 'itemgroups' : 'plans' )
					. ' WHERE id IN (' . implode( ',', $result ) . ')'
					. ' ORDER BY `ordering`'
					;
			$db->setQuery( $query );

			return xJ::getDBArray( $db );
		} else {
			return $result;
		}
	}

	function getGroupsPlans( $groups )
	{
		static $groupstore;

		$plans = array();
		foreach ( $groups as $group ) {
			if ( !isset( $groupstore[$group] ) ) {
				$groupstore[$group] = ItemGroupHandler::getTotalChildItems( array( $group ) );

				$groupstore[$group] = array_unique( $groupstore[$group] );
			}

			$plans = array_merge( $plans, $groupstore[$group] );
		}

		if ( !empty( $plans ) ) {
			return $plans;
		} else {
			return array();
		}
	}

	function checkParentRestrictions( $item, $type, $metaUser )
	{
		$parents = ItemGroupHandler::parentGroups( $item->id, $type );

		if ( !empty( $parents ) ) {
			foreach ( $parents as $parent ) {
				$g = new ItemGroup();
				$g->load( $parent );

				// Only check for permission, visibility might be overridden
				if ( !$g->checkPermission( $metaUser ) ) {
					return false;
				}

				if ( !ItemGroupHandler::checkParentRestrictions( $g, 'group', $metaUser ) ) {
					return false;
				}
			}
		}

		return true;
	}

	function hasVisibleChildren( $group, $metaUser )
	{
		$children = ItemGroupHandler::getChildren( $group->id, 'item' );
		if ( !empty( $children ) ) {
			$i = 0;
			foreach( $children as $itemid ) {
				$plan = new SubscriptionPlan();
				$plan->load( $itemid );

				if ( $plan->checkVisibility( $metaUser ) ) {
					return true;
				}
			}
		}

		$groups = ItemGroupHandler::getChildren( $group->id, 'group' );
		if ( !empty( $groups ) ) {
			foreach ( $groups as $groupid ) {
				$g = new ItemGroup();
				$g->load( $groupid );

				if ( !$g->checkVisibility( $metaUser ) ) {
					continue;
				}

				if ( ItemGroupHandler::hasVisibleChildren( $g, $metaUser ) ) {
					return true;
				}
			}
		}

		return false;
	}

	function getTotalChildItems( $gids, $list=array() )
	{
		$groups = ItemGroupHandler::getChildren( $gids, 'group' );

		foreach ( $groups as $groupid ) {
			$list = ItemGroupHandler::getTotalChildItems( $groupid, $list );
		}

		$items = ItemGroupHandler::getChildren( $gids, 'item' );

		return array_merge( $list, $items );
	}

	function getTotalAllowedChildItems( $gids, $metaUser, $list=array() )
	{
		$groups = ItemGroupHandler::getChildren( $gids, 'group' );

		if ( !empty( $groups ) ) {
			foreach ( $groups as $groupid ) {
				$group = new ItemGroup();
				$group->load( $groupid );

				if ( !$group->checkVisibility( $metaUser ) ) {
					continue;
				}

				if ( $group->params['reveal_child_items'] && empty( $group->params['symlink'] ) ) {
					$list = ItemGroupHandler::getTotalAllowedChildItems( $groupid, $metaUser, $list );
				} else {
						if ( ItemGroupHandler::hasVisibleChildren( $group, $metaUser ) ) {
							$list[] = ItemGroupHandler::getGroupListItem( $group );
						}
					}
			}
		}

		$items = ItemGroupHandler::getChildren( $gids, 'item' );

		if ( !empty( $items ) ) {
			foreach( $items as $itemid ) {
				$plan = new SubscriptionPlan();
				$plan->load( $itemid );

				if ( !$plan->checkVisibility( $metaUser ) ) {
					continue;
				}

				$list[] = ItemGroupHandler::getItemListItem( $plan );
			}
		}

		return $list;
	}

	function getGroupListItem( $group )
	{
		$details = array(	'type'		=> 'group',
							'id'		=> $group->id,
							'name'		=> $group->getProperty( 'name' ),
							'desc'		=> $group->getProperty( 'desc' ),
							'meta'	=> array()
							);

		if ( !empty( $group->params['meta'] ) ) {
			$details['meta'] = parameterHandler::decode( $group->params['meta'] );
		}

		return $details;
	}

	function getItemListItem( $plan )
	{
		$details = array(	'type'		=> 'item',
							'id'		=> $plan->id,
							'plan'		=> $plan,
							'name'		=> $plan->getProperty( 'name' ),
							'desc'		=> $plan->getProperty( 'desc' ),
							'ordering'	=> $plan->ordering,
							'lifetime'	=> $plan->params['lifetime'],
							'meta'	=> array()
							);

		if ( !empty( $plan->params['meta'] ) ) {
			$details['meta'] = parameterHandler::decode( $plan->params['meta'] );
		}

		return $details;
	}

	function removeChildren( $items, $groups, $type='item' )
	{
		$db = &JFactory::getDBO();

		$query = 'DELETE'
				. ' FROM #__acctexp_itemxgroup'
				. ' WHERE `type` = \'' . $type . '\''
				;

		if ( is_array( $items ) ) {
			$query .= ' AND `item_id` IN (' . implode( ',', $items ) . ')';
		} else {
			$query .= ' AND `item_id` = \'' . $items . '\'';
		}

		if ( !empty( $groups ) ) {
			$query .= ' AND `group_id` IN (' . implode( ',', $groups ) . ')';
		}

		$db->setQuery( $query );
		return $db->query();
	}

}

class ItemGroup extends serialParamDBTable
{
	/** @var int Primary key */
	var $id 				= null;
	/** @var int */
	var $active				= null;
	/** @var int */
	var $visible			= null;
	/** @var int */
	var $ordering			= null;
	/** @var string */
	var $name				= null;
	/** @var string */
	var $desc				= null;
	/** @var text */
	var $params 			= null;
	/** @var text */
	var $custom_params		= null;
	/** @var text */
	var $restrictions		= null;

	function ItemGroup()
	{
		parent::__construct( '#__acctexp_itemgroups', 'id' );
	}

	function getProperty( $name )
	{
		if ( isset( $this->$name ) ) {
			return stripslashes( $this->$name );
		} else {
			return null;
		}
	}

	function declareParamFields()
	{
		return array( 'params', 'custom_params', 'restrictions' );
	}

	function checkVisibility( $metaUser )
	{
		if ( !$this->visible ) {
			return false;
		} else {
			return $this->checkPermission( $metaUser );
		}
	}

	function checkPermission( $metaUser )
	{
		if ( !$this->active ) {
			return false;
		}

		$restrictions = $this->getRestrictionsArray();

		return aecRestrictionHelper::checkRestriction( $restrictions, $metaUser );
	}

	function getRestrictionsArray()
	{
		return aecRestrictionHelper::getRestrictionsArray( $this->restrictions );
	}

	function getMicroIntegrationsSeparate( $strip_inherited=false )
	{
		if ( empty( $this->params['micro_integrations'] ) ) {
			$milist = array();
		} else {
			$milist = $this->params['micro_integrations'];
		}

		// Find parent ItemGroups to attach their MIs
		$parents = ItemGroupHandler::getParents( $this->id, 'group' );

		$gmilist = array();
		if ( !empty( $parents ) ) {
			foreach ( $parents as $parent ) {
				$g = new ItemGroup();
				$g->load( $parent );

				if ( !empty( $g->params['micro_integrations'] ) ) {
					$gmilist = array_merge( $gmilist, $g->params['micro_integrations'] );
				}
			}
		}

		if ( empty( $milist ) && empty( $gmilist ) ) {
			return array( 'group' => array(), 'inherited' => array() );
		}

		$milist = microIntegrationHandler::getActiveListbyList( $milist );
		$gmilist = microIntegrationHandler::getActiveListbyList( $gmilist );

		if ( empty( $milist ) && empty( $gmilist ) ) {
			return array( 'group' => array(), 'inherited' => array() );
		}

		if ( $this->id > 1 ) {
			// Remove entries from the group MIs that are already inherited
			if ( !empty( $gmilist ) && !empty( $milist ) && $strip_inherited ) {
				$theintersect = array_intersect( $gmilist, $milist );
	
				if ( !empty( $theintersect ) ) {
					foreach ( $theintersect as $value ) {
						// STAY IN THE CAR
						unset( $milist[array_search( $value, $milist )] );
					}
				}
			}
		} else {
			$gmilist = array();
		}

		return array( 'group' => $milist, 'inherited' => $gmilist );
	}

	function savePOSTsettings( $post )
	{
		// Fake knowing the planid if is zero.
		if ( !empty( $post['id'] ) ) {
			$groupid = $post['id'];
		} else {
			$groupid = $this->getMax() + 1;
		}

		if ( isset( $post['id'] ) ) {
			unset( $post['id'] );
		}

		if ( isset( $post['inherited_micro_integrations'] ) ) {
			unset( $post['inherited_micro_integrations'] );
		}

		if ( !empty( $post['add_group'] ) ) {
			ItemGroupHandler::setChildren( $post['add_group'], array( $groupid ), 'group' );
		}

		if ( $this->id == 1 ) {
			$post['active']				= 1;
			$post['visible']			= 1;
			$post['name']				= JText::_('AEC_INST_ROOT_GROUP_NAME');
			$post['desc']				= JText::_('AEC_INST_ROOT_GROUP_DESC');
			$post['reveal_child_items']	= 1;
		}

		// Filter out fixed variables
		$fixed = array( 'active', 'visible', 'name', 'desc' );

		foreach ( $fixed as $varname ) {
			$this->$varname = $post[$varname];
			unset( $post[$varname] );
		}

		foreach ( $post['micro_integrations'] as $k => $v ) {
			if ( $v ) {
				$post['micro_integrations'][$k] = $v;
			} else {
				unset( $post['micro_integrations'][$k] );
			}
		}

		// Filter out params
		$fixed = array(	'color', 'reveal_child_items', 'symlink',
						'symlink_userid', 'notauth_redirect', 'micro_integrations', 'meta' );

		$params = array();
		foreach ( $fixed as $varname ) {
			if ( !isset( $post[$varname] ) ) {
				continue;
			}

			if ( $varname == 'color' ) {
				if ( strpos( $post[$varname], '#' ) !== false ) {
					$post[$varname] = substr( $post[$varname], 1 );
				} 
			}

			$params[$varname] = $post[$varname];

			unset( $post[$varname] );
		}

		$this->saveParams( $params );

		// Filter out restrictions
		$fixed = aecRestrictionHelper::paramList();

		$restrictions = array();
		foreach ( $fixed as $varname ) {
			if ( !isset( $post[$varname] ) ) {
				continue;
			}

			$restrictions[$varname] = $post[$varname];

			unset( $post[$varname] );
		}

		$this->restrictions = $restrictions;

		// There might be deletions set for groups
		foreach ( $post as $varname => $content ) {
			if ( ( strpos( $varname, 'group_delete_' ) !== false ) && $content ) {
				$parentid = (int) str_replace( 'group_delete_', '', $varname );

				ItemGroupHandler::removeChildren( $groupid, array( $parentid ), 'group' );

				unset( $post[$varname] );
			}
		}

		// The rest of the vars are custom params
		$custom_params = array();
		foreach ( $post as $varname => $content ) {
			if ( substr( $varname, 0, 4 ) != 'mce_' ) {
				$custom_params[$varname] = $content;
			}
			unset( $post[$varname] );
		}

		$this->custom_params = $custom_params;
	}

	function saveParams( $params )
	{
		$this->params = $params;
	}

	function delete()
	{
		if ( $this->id == 1 ) {
			return false;
		}

		// Delete possible item connections
		$query = 'DELETE FROM #__acctexp_itemxgroup'
				. ' WHERE `group_id` = \'' . $this->id . '\''
				. ' AND `type` = \'item\''
				;
		$this->_db->setQuery( $query );
		if ( !$this->_db->query() ) {
			echo "<script> alert('".$this->_db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		// Delete possible group connections
		$query = 'DELETE FROM #__acctexp_itemxgroup'
				. ' WHERE `group_id` = \'' . $this->id . '\''
				. ' AND `type` = \'group\''
				;
		$this->_db->setQuery( $query );
		if ( !$this->_db->query() ) {
			echo "<script> alert('".$this->_db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}

		return parent::delete();
	}

	function copy()
	{
		$pid = $this->id;

		$this->id = 0;
		$this->storeload();

		$parents = ItemGroupHandler::parentGroups( $pid, 'group' );

		foreach ( $parents as $parentid ) {
			ItemGroupHandler::setChild( $this->id, $parentid, 'group' );
		}
	}
}

class itemXgroup extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $type				= null;
	/** @var int */
	var $item_id			= null;
	/** @var int */
	var $group_id			= null;

	function itemXgroup()
	{
		parent::__construct( '#__acctexp_itemxgroup', 'id' );
	}

	function createNew( $type, $item_id, $group_id )
	{
		$this->id		= 0;
		$this->type		= $type;
		$this->item_id	= $item_id;
		$this->group_id	= $group_id;

		$this->check();
		$this->store();

		return true;
	}

}

?>

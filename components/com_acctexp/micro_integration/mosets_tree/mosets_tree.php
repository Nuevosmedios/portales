<?php
/**
 * @version $Id: mi_idevaffiliate.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Mosets Tree
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_mosets_tree extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_MOSETS');
		$info['desc'] = JText::_('AEC_MI_DESC_MOSETS');
		$info['type'] = array( 'directory_documentation.directory', 'vendor.mosets' );

		return $info;
	}

	function checkInstallation()
	{
		$db = &JFactory::getDBO();

		$app = JFactory::getApplication();

		$tables	= array();
		$tables	= $db->getTableList();

		return in_array( $app->getCfg( 'dbprefix' ) . 'acctexp_mi_mosetstree', $tables );
	}

	function install()
	{
		$db = &JFactory::getDBO();

		$query = 'CREATE TABLE IF NOT EXISTS `#__acctexp_mi_mosetstree` ('
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
		// field type; name; variable value, description, extra (variable name)

		$settings = array();
		$settings['allow']				= array( 'toggle' );
		$settings['allow_exp']			= array( 'toggle' );
		$settings['unlimited']			= array( 'toggle' );
		$settings['unlimited_exp']		= array( 'toggle' );
		$settings['add_listings']		= array( 'inputA' );
		$settings['set_listings']		= array( 'inputA' );
		$settings['publish_all']		= array( 'toggle' );
		$settings['unpublish_all']		= array( 'toggle' );
		$settings['feature_all']		= array( 'toggle' );
		$settings['unfeature_all']		= array( 'toggle' );

		return $settings;
	}

	function expiration_action( $request )
	{
		$db = &JFactory::getDBO();

		$mi_mosetshandler = new mosetstree();
		$id = $mi_mosetshandler->getIDbyUserID( $request->metaUser->userid );

		$mi_mosetshandler->load( $id );
		$mi_mosetshandler->active = 0;

		if ( !empty( $this->settings['allow_exp'] ) ) {
			$mi_mosetshandler->params['deny'] = false;
		} else {
			$mi_mosetshandler->params['deny'] = true;
		}

		if ( !empty( $this->settings['unlimited_exp'] ) ) {
			$mi_mosetshandler->params['unlimited'] = true;
		} else {
			$mi_mosetshandler->params['unlimited'] = false;
		}

		$mi_mosetshandler->check();
		$mi_mosetshandler->store();

		if ( $this->settings['unpublish_all'] ) {
			$this->unpublishListings( $request->metaUser );
		}

		if ( $this->settings['unfeature_all'] ) {
			$this->unfeatureListings( $request->metaUser );
		}

		return true;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		$mi_mosetshandler = new mosetstree();
		$id = $mi_mosetshandler->getIDbyUserID( $request->metaUser->userid );

		$mi_id = $id ? $id : 0;
		$mi_mosetshandler->load( $mi_id );

		if ( !$mi_id ){
			$mi_mosetshandler->userid = $request->metaUser->userid;
			$mi_mosetshandler->active = 1;
		}

		if ( $this->settings['set_listings'] ) {
			$mi_mosetshandler->setListings( $this->settings['set_listings'] );
		} elseif ( $this->settings['add_listings'] ) {
			$mi_mosetshandler->addListings( $this->settings['add_listings'] );
		}

		if ( !empty( $this->settings['unlimited'] ) ) {
			$mi_mosetshandler->params['unlimited'] = true;
		} else {
			$mi_mosetshandler->params['unlimited'] = false;
		}

		if ( !empty( $this->settings['allow'] ) ) {
			$mi_mosetshandler->params['deny'] = false;
		} else {
			$mi_mosetshandler->params['deny'] = true;
		}

		$mi_mosetshandler->storeload();

		if ( $this->settings['publish_all'] ) {
			$this->publishListings( $request->metaUser );
		}

		if ( $this->settings['feature_all'] ) {
			$this->featureListings( $request->metaUser );
		}

		return true;
	}

	function detect_application()
	{
		return is_dir( JPATH_SITE . '/components/com_mtree' );
	}

	function hacks()
	{
		$hacks = array();

		$edithack = '// AEC HACK mtree1 START' . "\n"
		. 'if (!$link_id) {' . "\n"
		. ( defined( '_JEXEC' ) ? '' : 'global $mosConfig_absolute_path;' ) . "\n"
		. 'include_once( JPATH_SITE . \'/components/com_acctexp/acctexp.class.php\' );' . "\n"
		. 'include_once( JPATH_SITE . \'/components/com_acctexp/micro_integration/mosets_tree/mosets_tree.php\' );' . "\n"
		. '$mi_mosetshandler = new mosetstree();' . "\n"
		. '$mi_mosetshandler->loadUserID( $my->id );' . "\n"
		. 'if( $mi_mosetshandler->id ) {' . "\n"
		. 'if( !$mi_mosetshandler->hasListingsLeft() ) {' . "\n"
		. 'echo "' . JText::_('AEC_MI_HACK1_MOSETS') . '";' . "\n"
		. 'return;' . "\n"
		. '}' . "\n"
		. '} else {' . "\n"
		. 'echo "' . JText::_('AEC_MI_HACK2_MOSETS') . '";' . "\n"
		. 'return;' . "\n"
		. '}' . "\n"
		. '}' . "\n"
		. '// AEC HACK mtree1 END' . "\n"
		;

		$edithack2 = '// AEC HACK mtree2 START' . "\n"
		. 'if ($row->link_approved == 1) {' . "\n"
		. ( defined( '_JEXEC' ) ? '' : 'global $mosConfig_absolute_path;' ) . "\n"
		. 'include_once( JPATH_SITE . \'/components/com_acctexp/acctexp.class.php\' );' . "\n"
		. 'include_once( JPATH_SITE . \'/components/com_acctexp/micro_integration/mosets_tree/mosets_tree.php\' );' . "\n"
		. '$mi_mosetshandler = new mosetstree();' . "\n"
		. '$mi_mosetshandler->loadUserID( $my->id );' . "\n"
		. 'if( $mi_mosetshandler->id ) {' . "\n"
		. 'if( $mi_mosetshandler->hasListingsLeft() ) {' . "\n"
		. '$mi_mosetshandler->useListing();' . "\n"
		. '} else {' . "\n"
		. 'echo "' . JText::_('AEC_MI_HACK1_MOSETS') . '";' . "\n"
		. 'return;' . "\n"
		. '}' . "\n"
		. '} else {' . "\n"
		. 'echo "' . JText::_('AEC_MI_HACK2_MOSETS') . '";' . "\n"
		. 'return;' . "\n"
		. '}' . "\n"
		. '}' . "\n"
		. '// AEC HACK mtree2 END' . "\n"
		;

		$edithack3 = '// AEC HACK adminmtree3 START' . "\n"
		. ( defined( '_JEXEC' ) ? '' : 'global $mosConfig_absolute_path;' ) . "\n"
		. 'include_once( JPATH_SITE . \'/components/com_acctexp/acctexp.class.php\' );' . "\n"
		. 'include_once( JPATH_SITE . \'/components/com_acctexp/micro_integration/mosets_tree/mosets_tree.php\' );' . "\n"
		. '$mi_mosetshandler = new mosetstree();' . "\n"
		. '$mi_mosetshandler->loadUserID( $mtLinks->user_id );' . "\n"
		. 'if( $mi_mosetshandler->id ) {' . "\n"
		. 'if( $mi_mosetshandler->hasListingsLeft() ) {' . "\n"
		. '$mi_mosetshandler->useListing();' . "\n"
		. '} else {' . "\n"
		. 'continue;' . "\n"
		. '}' . "\n"
		. '} else {' . "\n"
		. 'continue;' . "\n"
		. '}' . "\n"
		. '// AEC HACK adminmtree3 END' . "\n"
		;

		$n = 'mtree1';
		$hacks[$n]['name']				=	'mtree.php #1';
		$hacks[$n]['desc']				=	JText::_('AEC_MI_HACK3_MOSETS');
		$hacks[$n]['type']				=	'file';
		$hacks[$n]['filename']			=	JPATH_SITE . '/components/com_mtree/mtree.php';
		$hacks[$n]['read']				=	'// OK, you can edit';
		$hacks[$n]['insert']			=	$edithack . "\n"  . $hacks[$n]['read'];

		$n = 'mtree2';
		$hacks[$n]['name']				=	'mtree.php #2';
		$hacks[$n]['desc']				=	JText::_('AEC_MI_HACK4_MOSETS');
		$hacks[$n]['type']				=	'file';
		$hacks[$n]['filename']			=	JPATH_SITE . '/components/com_mtree/mtree.php';
		$hacks[$n]['read']				=	'$row->updateLinkCount( 1 );';
		$hacks[$n]['insert']			=	$edithack2 . "\n"  . $hacks[$n]['read'];

		$n = 'adminmtree3';
		$hacks[$n]['name']				=	'admin.mtree.php #3';
		$hacks[$n]['desc']				=	JText::_('AEC_MI_HACK5_MOSETS');
		$hacks[$n]['type']				=	'file';
		$hacks[$n]['filename']			=	JPATH_SITE . '/administrator/components/com_mtree/admin.mtree.php';
		$hacks[$n]['read']				=	'if ( $mtLinks->link_approved == 0 ) {';
		$hacks[$n]['insert']			=	$hacks[$n]['read'] . "\n" . $edithack3;

		return $hacks;
	}

	function profile_info( $request )
	{
		$db = &JFactory::getDBO();

		$mi_mosetshandler = new mosetstree();
		$id = $mi_mosetshandler->getIDbyUserID( $request->metaUser->userid );

		if ( $id ) {
			$mi_mosetshandler->load( $id );
			return '<p>' . sprintf( JText::_('AEC_MI_DIV1_MOSETS'), $mi_mosetshandler->getListingsLeft() ) . '</p>';
		} else {
			return '';
		}
	}

	function publishListings( $metaUser )
	{
		$db = &JFactory::getDBO();

		$query = 'UPDATE #__mt_links'
				. ' SET `link_published` = \'1\''
				. ' WHERE `user_id` = \'' . $metaUser->userid . '\''
				;
		$db->setQuery( $query );
		if ( $db->query() ) {
			return true;
		} else {
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}

	function unpublishListings( $metaUser )
	{
		$db = &JFactory::getDBO();

		$query = 'UPDATE #__mt_links'
				. ' SET `link_published` = \'0\''
				. ' WHERE `user_id` = \'' . $metaUser->userid . '\''
				;
		$db->setQuery( $query );
		if ( $db->query() ) {
			return true;
		} else {
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}

	function featureListings( $metaUser )
	{
		$db = &JFactory::getDBO();

		$query = 'UPDATE #__mt_links'
				. ' SET `link_featured` = \'1\''
				. ' WHERE `user_id` = \'' . $metaUser->userid . '\''
				;
		$db->setQuery( $query );
		if ( $db->query() ) {
			return true;
		} else {
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}

	function unfeatureListings( $metaUser )
	{
		$db = &JFactory::getDBO();

		$query = 'UPDATE #__mt_links'
				. ' SET `link_featured` = \'0\''
				. ' WHERE `user_id` = \'' . $metaUser->userid . '\''
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

class mosetstree extends serialParamDBTable
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

	function mosetstree()
	{
		$lang =& JFactory::getLanguage();

		$lang->load( 'com_acctexp.microintegrations', JPATH_SITE );

		parent::__construct( '#__acctexp_mi_mosetstree', 'id' );
	}

	function declareParamFields()
	{
		return array( 'params' );
	}

	function getIDbyUserID( $userid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_mi_mosetstree'
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

	function is_active()
	{
		return $this->active ? true : false;
	}

	function getListingsLeft()
	{
		if ( !empty( $this->params['deny'] ) ) {
			return 0;
		} elseif ( !empty( $this->params['unlimited'] ) ) {
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

	function setListings( $set )
	{
		$this->granted_listings = $set;
	}

	function addListings( $add )
	{
		$this->granted_listings += $add;
	}
}
?>

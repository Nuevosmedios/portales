<?php
/**
 * @version $Id: mi_rsgallery2.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - RSgallery2
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_rsgallery2 extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_RSGALLERY2_NAME');
		$info['desc'] = JText::_('AEC_MI_RSGALLERY2_DESC');
		$info['type'] = array( 'media.gallery', 'vendor.rsgallery' );

		return $info;
	}

	function Settings()
	{
		$db = &JFactory::getDBO();

		$settings = array();
		$settings['create_galleries']		= array( 'toggle' );
		$settings['galleries_name']		= array( 'inputC' );
		$settings['galleries_desc']		= array( 'inputD' );
		$settings['set_galleries']			= array( 'toggle' );
		$settings['galleries']				= array( 'list' );
		$settings['set_galleries_user']	= array( 'toggle' );
		$settings['gallery_sel_amt']		= array( 'inputA' );
		$settings['gallery_sel_scope']	= array( 'list' );
		$settings['publish_all']		= array( 'toggle' );
		$settings['unpublish_all']		= array( 'toggle' );

		$query = 'SELECT `id`, `name`'
			 	. ' FROM #__rsgallery2_galleries'
			 	. ' WHERE parent = \'0\''
			 	;
	 	$db->setQuery( $query );
	 	$galleries = $db->loadObjectList();

		$sg = array();
		$sgs = array();

		$gr = array();
		if ( !empty( $galleries ) ) {
			foreach( $galleries as $gallery ) {
				$gr[] = JHTML::_('select.option', $gallery->id, $gallery->name );

				if ( !empty( $this->settings['galleries'] ) ) {
					if ( in_array( $gallery->id, $this->settings['galleries'] ) ) {
						$sg[] = JHTML::_('select.option', $gallery->id, $gallery->name );
					}
				}

				if ( !empty( $this->settings['gallery_sel_scope'] ) ) {
					if ( in_array( $gallery->id, $this->settings['gallery_sel_scope'] ) ) {
						$sgs[] = JHTML::_('select.option', $gallery->id, $gallery->name );
					}
				}
			}
		}

		$settings['galleries']						= array( 'list' );
		$settings['lists']['galleries']			= JHTML::_( 'select.genericlist', $gr, 'galleries[]', 'size="6" multiple="multiple"', 'value', 'text', $sg );
		$settings['gallery_sel_scope']			= array( 'list' );
		$settings['lists']['gallery_sel_scope']	= JHTML::_( 'select.genericlist', $gr, 'gallery_sel_scope[]', 'size="6" multiple="multiple"', 'value', 'text', $sgs );

		return $settings;
	}

	function getMIform( $request )
	{
		$db = &JFactory::getDBO();

		$settings = array();

		if ( $this->settings['set_galleries_user'] ) {
			$query = 'SELECT `id`, `name`'
				 	. ' FROM #__rsgallery2_galleries'
				 	. ' WHERE `id` IN (' . implode( ',', $this->settings['gallery_sel_scope'] ) . ')'
				 	;
		 	$db->setQuery( $query );
		 	$galleries = $db->loadObjectList();

			$gr = array();
			foreach ( $galleries as $gallery ) {
				$desc = $gallery->name . '' . substr( strip_tags( "" ), 0, 30 );

				$gr[] = JHTML::_('select.option', $gallery->id, $desc );
			}

			for ( $i=0; $i<$this->settings['gallery_sel_amt']; $i++ ) {
				$settings['mi_gallery_'.$i]			= array( 'list', JText::_('MI_MI_RSGALLERY2_GALLERY_USERSELECT_NAME'), JText::_('MI_MI_RSGALLERY2_GALLERY_USERSELECT_DESC') );
				$settings['lists']['mi_gallery_'.$i]	= JHTML::_( 'select.genericlist', $gr, 'mi_gallery_'.$i, 'size="6"', 'value', 'text', '' );
			}
		} else {
			return false;
		}

		return $settings;
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		if ( !empty( $this->settings['publish_all'] ) ) {
			$query = 'SELECT `id`'
				 	. ' FROM #__rsgallery2_galleries'
				 	. ' WHERE `uid` = ' . $request->metaUser->userid . ''
				 	;
		 	$db->setQuery( $query );

			// Make sure we have at least one entry
			if ( $db->loadResult() ) {
				$query = 'UPDATE #__rsgallery2_galleries'
						. ' SET published = \'1\''
						. ' WHERE uid =  \'' . $request->metaUser->userid . '\'';
						;
				$db->setQuery( $query );

				if ( !$db->query() ) {
					$this->setError( $db->getErrorMsg() );
					return false;
				}
			}
		}

		$galleries = array();

		if ( $this->settings['set_galleries'] ) {
			$a = $this->settings['galleries'];
			foreach ( $a as $aid ) {
				$galleries[] = $aid;
			}
		}

		if ( $this->settings['set_galleries_user'] ) {
			for ( $i=0; $i<$this->settings['gallery_sel_amt']; $i++ ) {
				if ( isset( $request->params['mi_gallery_'.$i] ) ) {
					$galleries[] = $request->params['mi_gallery_'.$i];
				}
			}
		}

		if ( !empty( $galleries ) && !empty( $this->settings['create_galleries'] ) && !empty( $this->settings['galleries_name'] ) ) {
			array_unique( $galleries );

			$name = AECToolbox::rewriteEngineRQ( $this->settings['galleries_name'], $request );
			$desc = AECToolbox::rewriteEngineRQ( $this->settings['galleries_desc'], $request );

			foreach ( $galleries as $groupid ) {
				$this->createAlbum( $request->metaUser->userid, $groupid, $name, $desc );
			}
		}

		return null;
	}

	function expiration_action( $request )
	{
		if ( !empty( $this->settings['unpublish_all'] ) ) {
			$query = 'SELECT `id`'
				 	. ' FROM #__rsgallery2_galleries'
				 	. ' WHERE `uid` = ' . $request->metaUser->userid . ''
				 	;
		 	$db->setQuery( $query );

			// Make sure we have at least one entry
			if ( $db->loadResult() ) {
				$query = 'UPDATE #__rsgallery2_galleries'
						. ' SET published = \'0\''
						. ' WHERE uid =  \'' . $request->metaUser->userid . '\'';
						;
				$db->setQuery( $query );

				if ( !$db->query() ) {
					$this->setError( $db->getErrorMsg() );
					return false;
				}
			}
		}

		return true;
	}

	function createAlbum( $userid, $parentid, $name, $desc )
	{
		$app = JFactory::getApplication();

		$db = &JFactory::getDBO();

		// Check that we don't create a duplicate
		$query = 'SELECT id'
				. ' FROM #__rsgallery2_galleries'
				. ' WHERE `uid` = \'' . $userid . '\''
				. ' AND `parent` = \'' . $parentid . '\''
				. ' AND `name` = \'' . xJ::escape( $db, $name ) . '\''
				;
		$db->setQuery( $query );
		$tentries = $db->loadResult();

		if ( $tentries ) {
			return null;
		}

		// Fallback sanity check in case the user has renamed the galleries
		$query = 'SELECT count(*)'
				. ' FROM #__rsgallery2_galleries'
				. ' WHERE `uid` = \'' . $userid . '\''
				;
		$db->setQuery( $query );
		$entries = $db->loadResult();

		if ( $entries >= $this->settings['gallery_sel_amt'] ) {
			return null;
		}

		$query = 'INSERT INTO #__rsgallery2_galleries'
				. ' ( `parent`, `name`, `description`, `published`, `date`, `uid` )'
				. ' VALUES ( \'' . $parentid . '\', \'' . xJ::escape( $db, $name ) . '\', \'' . xJ::escape( $db, $desc ) . '\', \'1\', \'' . date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) ) . '\', \'' . $userid . '\' )'
				;
		$db->setQuery( $query );

		if ( !$db->query() ) {
			$this->setError( $db->getErrorMsg() );
			return false;
		}

		// Check that we don't create a duplicate
		$query = 'SELECT max(id)'
				. ' FROM #__rsgallery2_galleries'
				;
		$db->setQuery( $query );
		$gid = $db->loadResult();

		$query = 'INSERT INTO #__rsgallery2_acl'
				. ' ( `gallery_id`, `parent_id`, `registered_up_mod_img`, `registered_create_mod_gal` )'
				//. ' ( `gallery_id`, `parent_id`, `public_view`, `public_up_mod_img`, `public_del_img`, `public_create_mod_gal`, `public_del_gal`, `public_vote_view`, `public_vote_vote`, `registered_view`, `registered_up_mod_img`, `registered_del_img`, `registered_create_mod_gal`, `registered_del_gal`, `registered_vote_view`, `registered_vote_vote` )'
				. ' VALUES ( \'' . $gid . '\', \'' . $parentid . '\', \'0\', \'0\') '
				;
		$db->setQuery( $query );


		if ( !$db->query() ) {
			$this->setError( $db->getErrorMsg() );
			return false;
		}
		return true;
	}

}

?>

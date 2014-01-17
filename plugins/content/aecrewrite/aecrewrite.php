<?php
/**
 * @version	$Id: aecrewrite.php $
 * @package AEC - Account Control Expiration - Joomla 1.5 Plugins
 * @subpackage Rewrite
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

$app =& JFactory::getApplication();
$app->registerEvent( 'onPrepareContent', 'plgContentAECRewrite' );

class plgContentAECRewrite extends JPlugin
{
	function onPrepareContent( &$article, &$params, $limitstart )
	{
		return $this->onContentPrepare( "", $article, $params, $limitstart );
	}

	/**
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 */
	function onContentPrepare( $context, &$article, &$params, $page=0 )
	{
		// See whether there is anything to replace
		if (JString::strpos($article->text, '{aecrewrite') === false) {
			return true;
		}

		if ( false ) {
			// component check
		}

		$regex = "#{aecrewrite}(.*?){/aecrewrite}#s";

		if ( file_exists( JPATH_ROOT."/components/com_acctexp/acctexp.class.php" ) ) {
			$article->text = preg_replace_callback( $regex, array(&$this, '_replace'), $article->text );
		} else {
			$article->text = preg_replace( $regex, "", $article->text );
		}

		return true;
	}

	/**
	 * Replaces the matched tags.
	 *
	 * @param	array	An array of matches (see preg_match_all)
	 * @return	string
	 */
	protected function _replace( &$matches )
	{
		static $rwEngine;

		include_once( JPATH_ROOT."/components/com_acctexp/acctexp.class.php" );

		if ( empty( $rwEngine->rewrite ) ) {
			$user = &JFactory::getUser();

			$rwEngine = new reWriteEngine();

			$metaUser = new metaUser( $user->id );

			$request = new stdClass();
			$request->metaUser = $metaUser;

			$rwEngine->resolveRequest( $request );
		}

		return $rwEngine->resolve( $matches[1] );
	}
}

<?php
/**
 * @version $Id: acctexp.template.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

function getView( $view, $args=null )
{
	global $aecConfig;

	$db = &JFactory::getDBO();

	$user = &JFactory::getUser();

	$metaUser = null;
	if ( $user->id ) {
		$userid = $user->id;

		$metaUser = new metaUser( $user->id );
	} else {
		$userid		= aecGetParam( 'userid', 0, true, array( 'word', 'int' ) );

		$metaUser = new metaUser( $userid );
	}

	$app = JFactory::getApplication();

	$option = 'com_acctexp';

	$dbtmpl = new configTemplate();
	$dbtmpl->loadDefault();

	$tmpl = $dbtmpl->template;

	if ( !empty( $dbtmpl->settings ) ) {
		$tmpl->cfg = array_merge( $aecConfig->cfg, $dbtmpl->settings );
	} else {
		$tmpl->cfg = $aecConfig->cfg;
	}

	$tmpl->option = 'com_acctexp';
	$tmpl->metaUser = $metaUser;
	
	if ( strpos( JPATH_BASE, '/administrator' ) ) {
		if ( defined( 'JPATH_MANIFESTS' ) ) {
			$query = 'SELECT `template`'
					. ' FROM #__template_styles'
					. ' WHERE `home` = 1 AND `client_id` = 0'
					;
			$db->setQuery( $query );
			$tmpl->system_template = $db->loadResult();
		} else {
			$query = 'SELECT `template`'
					. ' FROM #__templates_menu'
					. ' WHERE `menu_id` = 0 AND `client_id` = 0'
					;
			$db->setQuery( $query );
			$tmpl->system_template = $db->loadResult();
		}
	} else {
		$tmpl->system_template = $app->getTemplate();
	}

	$tmpl->template = $dbtmpl->name;
	$tmpl->view = $view;

	$tmpl->paths['base'] = JPATH_SITE . '/components/com_acctexp/tmpl';
	$tmpl->paths = array(	'default' => $tmpl->paths['base'] . '/default',
							'current' => $tmpl->paths['base'] . '/' . $tmpl->template,
							'site' => JPATH_SITE . '/templates/' . $tmpl->system_template . '/html/com_acctexp'
						);

	$hphp = '/'.$view.'/html.php';
	$tphp = '/'.$view.'/tmpl/'.$view.'.php';

	if ( !empty( $args ) ) {
		foreach ( $args as $n => $v ) {
			$$n = $v;
		}
	}

	if ( file_exists( $tmpl->paths['site'].$hphp ) ) {
		include( $tmpl->paths['site'].$hphp );
	} elseif ( file_exists( $tmpl->paths['current'].$hphp ) ) {
		include( $tmpl->paths['current'].$hphp );
	} elseif ( file_exists( $tmpl->paths['default'].$hphp ) ) {
		include( $tmpl->paths['default'].$hphp );
	} elseif ( file_exists( $tmpl->paths['site'].$tphp ) ) {
		include( $tmpl->paths['site'].$tphp );
	} elseif ( file_exists( $tmpl->paths['current'].$tphp ) ) {
		include( $tmpl->paths['current'].$tphp );
	} elseif ( file_exists( $tmpl->paths['default'].$tphp ) ) {
		include( $tmpl->paths['default'].$tphp );
	}
}

class configTemplate extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $name				= null;
	/** @var int */
	var $default			= null;
	/** @var text */
	var $settings			= null;

	function configTemplate()
	{
		parent::__construct( '#__acctexp_config_templates', 'id' );
	}

	function declareParamFields()
	{
		return array( 'settings' );
	}

	function loadDefault()
	{
		// See if the processor is installed & set id
		$query = 'SELECT name'
				. ' FROM #__acctexp_config_templates'
				. ' WHERE `default` = 1'
				;
		$this->_db->setQuery( $query );
		$name = $this->_db->loadResult();

		if ( !empty( $name ) ) {
			$this->loadName($name);
		} else {
			$this->loadName('etacarinae');
		}
	}

	function loadName( $name )
	{
		$this->name = $name;

		// See if the processor is installed & set id
		$query = 'SELECT id'
				. ' FROM #__acctexp_config_templates'
				. ' WHERE `name` = \'' . $name . '\''
				;
		$this->_db->setQuery( $query );
		$res = $this->_db->loadResult();

		if ( !empty( $res ) ) {
			$this->load($res);
		} else {
			$this->load(0);
		}

		$file = JPATH_SITE . '/components/com_acctexp/tmpl/' . $name . '/template.php';

		if ( file_exists( $file ) ) {
			// Call Integration file
			include_once ( $file );

			// Initiate Payment Processor Class
			$class_name = 'template_' . $name;
			$this->template = new $class_name();
			$this->template->id = $this->id;
			$this->template->default = $this->default;

			$this->info = $this->template->info();
		}
	}

	function storeload()
	{
		if ( method_exists( $this->template, 'beforesave' ) ) {
			$this->template->beforesave();
		}

		parent::storeload();
	}
}

class aecTemplate
{
	function stdSettings()
	{
		$info = $this->info();

		$params = array();
		$params[] = array( 'userinfobox_sub', JText::_('TEMPLATE_TITLE') );
		$params[] = array( 'div', '<div class="alert alert-info">' );
		$params[] = array( 'p', '<p>'.$info['description'].'</p>' );
		$params['default'] = array( ($this->default ? 'toggle_disabled':'toggle'), '' );
		$params[] = array( 'div_end', 0 );
		$params[] = array( 'div_end', 0 );

		return $params;
	}

	function defaultHeader()
	{
		$this->addDefaultCSS();

		if ( !empty( $this->js ) ) {
			$this->loadJS();
		}
	}

	function setTitle( $title )
	{
		$document=& JFactory::getDocument();
		$document->setTitle( html_entity_decode( $title, ENT_COMPAT, 'UTF-8' ) );
	}

	function addDefaultCSS()
	{
		$this->addCSS( JURI::root(true) . '/media/' . $this->option . '/css/template.' . $this->template . '.css' );
	}

	function addCSS( $path )
	{
		$document=& JFactory::getDocument();
		$document->addCustomTag( '<link rel="stylesheet" type="text/css" media="all" href="' . $path . '" />' );
	}

	function addCSSDeclaration( $css )
	{
		$document=& JFactory::getDocument();
		$document->addStyleDeclaration( $css );
	}

	function addScriptDeclaration( $js )
	{
		$document=& JFactory::getDocument();
		$document->addScriptDeclaration( $js );
	}

	function addScript( $js )
	{
		$v = new JVersion();

		if ( $v->isCompatible('3.0') && ( strpos( $js, '/' ) === false ) ) {
			JHtml::_( $js, false );
		} else {
			$document =& JFactory::getDocument();
			$document->addScript( $js );
		}
	}

	function btn( $params, $value, $class='btn' )
	{
		if ( empty( $params['option'] ) ) {
			$params['option'] = 'com_acctexp';
		}

		$xurl = 'index.php?option='.$params['option'];

		if ( !empty( $params['task'] ) ) {
			$xurl .= '&task='.$params['task'];
		}

		if ( !empty( $params['view'] ) ) {
			$xurl .= '&view='.$params['view'];
		}

		if ( $params['option'] == 'com_acctexp' ) {
			$url = AECToolbox::deadsureURL( $xurl, $this->cfg['ssl_signup'] );
		} else {
			if ( !empty( $id ) ) {
				$xurl	.= '&Itemid=' . $id;
			}
			
			$uri    = JURI::getInstance();
			$prefix = $uri->toString( array( 'scheme', 'host', 'port' ) );

			$url = $prefix.JRoute::_( $xurl );

			if ( $params['option'] == 'com_community' ) {
				// Judge me all you want.
				$db = &JFactory::getDBO();

				$query = 'SELECT `alias`'
						. ' FROM #__menu'
						. ' WHERE `link` = \'index.php?option=com_community&view=register\'';
				$db->setQuery( $query );

				$replacement = $db->loadResult();

				if ( empty( $replacement ) ) {
					$query = 'SELECT `alias`'
							. ' FROM #__menu'
							. ' WHERE `link` = \'index.php?option=com_community&view=frontpage\'';
					$db->setQuery( $query );

					$replacement = $db->loadResult();
				}

				if ( empty( $replacement ) ) {
					$replacement = 'jomsocial';
				}

				$url = str_replace( '/component/community/', '/'.$replacement.'/', $url );
			}
		}

		$btn = '<form action="'.$url.'" method="post">';

		if ( isset( $params['class'] ) ) {
			unset( $params['class'] );
		}

		if ( isset( $params['content'] ) ) {
			unset( $params['content'] );
		}

		if ( empty( $params['task'] ) ) {
			unset( $params['task'] );
		}

		foreach ( $params as $k => $v ) {
			$btn .= '<input type="hidden" name="'.$k.'" value="'.$v.'" />';
		}

		$btn .= '<button type="submit" class="'.$class.'">'.$value.'</button>';

		$btn .= JHTML::_( 'form.token' );
		$btn .= '</form>';

		return $btn;
	}

	function lnk( $params, $value, $class="", $profile=false )
	{
		if ( is_array( $params ) ) {
			$url = $this->url( $params, $profile );
		} else {
			$url = $params;
		}

		return '<a href="'.$url.'"'.( !empty($class) ? ' class="'.$class.'"':'').'>'.$value.'</a>';
	}

	function url( $params, $profile=false )
	{
		if ( empty( $params['option'] ) ) {
			$params = array_merge( array( 'option' => 'com_acctexp' ), $params );
		}

		$params[xJ::token()] = '1';

		$p = array();
		foreach ( $params as $k => $v ) {
			$p[] = $k.'='.$v;
		}

		if ( $profile ) {
			$secure = $this->cfg['ssl_profile'];
		} else {
			$secure = $this->cfg['ssl_signup'];
		}

		return AECToolbox::deadsureURL( 'index.php?'.implode("&",$p), $secure );
	}

	function rw( $string )
	{
		return AECToolbox::rewriteEngine( $string, $this->metaUser );
	}

	function rwrq( $string, $request )
	{
		return AECToolbox::rewriteEngineRQ( $string, $request );
	}

	function custom( $setting, $original=null, $obj=null )
	{
		if ( empty( $obj ) ) {
			$obj = $this->cfg;
		}

		if ( !empty( $original ) && isset( $obj[$setting.'_keeporiginal'] ) ) {
			echo '<p>' . $obj[$original] . '</p>';
		}

		if ( !empty( $obj[$setting] ) ) {
			echo '<p>' . $obj[$setting] . '</p>';
		}
	}

	function date( $SQLDate, $check = false, $display = false, $trial = false )
	{
		if ( $SQLDate == '' ) {
			return JText::_('AEC_EXPIRE_NOT_SET');
		} else {
			$retVal = AECToolbox::formatDate( $SQLDate );

			if ( $check ) {
				$timeDif = strtotime( $SQLDate ) - ( (int) gmdate('U') );
				if ( $timeDif < 0 ) {
					$retVal = ( $trial ? JText::_('AEC_EXPIRE_TRIAL_PAST') : JText::_('AEC_EXPIRE_PAST') ) . ':&nbsp;<strong>' . $retVal . '</strong>';
				} elseif ( ( $timeDif >= 0 ) && ( $timeDif < 86400 ) ) {
					$retVal = ( $trial ? JText::_('AEC_EXPIRE_TRIAL_TODAY') : JText::_('AEC_EXPIRE_TODAY') );
				} else {
					$retVal = ( $trial ? JText::_('AEC_EXPIRE_TRIAL_FUTURE') : JText::_('AEC_EXPIRE_FUTURE') ) . ': ' . $retVal;
				}
			}

			return $retVal;
		}
	}

	function tmpl( $name )
	{
		$t = explode( '.', $name );

		if ( count($t) > 2 ) {
			// Load from another template
			return $this->tmplPath( $t[1], $t[0], $t[2] );
		} elseif ( count($t) == 2 ) {
			// Load from another view
			return $this->tmplPath( $t[1], $t[0] );
		} else {
			// Load within view
			return $this->tmplPath( $t[0] );
		}
	}

	function tmplPath( $subview, $view=null, $template=null )
	{
		if ( empty( $view ) ) {
			$view = $this->view;
		}

		if ( empty( $template ) ) {
			$current = $this->paths['current'];
		} else {
			$current = $this->paths['base'].'/'.$this->template;
		}

		$t = '/'.$view.'/tmpl/'.$subview.'.php';

		if ( file_exists( $this->paths['site'].$t ) ) {
			return $this->paths['site'].$t;
		} elseif ( file_exists( $current.$t ) ) {
			return $current.$t;
		} elseif ( file_exists( $this->paths['default'].$t ) ) {
			return $this->paths['default'].$t;
		}
	}
}

?>

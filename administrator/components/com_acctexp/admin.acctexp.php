<?php
/**
 * @version $Id: admin.acctexp.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Backend
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// no direct access
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Restricted access' );

global $aecConfig;

$app = JFactory::getApplication();

require_once( JPATH_SITE . '/components/com_acctexp/acctexp.class.php' );
require_once( JPATH_SITE . '/administrator/components/com_acctexp/admin.acctexp.class.php' );
require_once( JPATH_SITE . '/administrator/components/com_acctexp/admin.acctexp.html.php' );

$langlist = array(	'com_acctexp' => JPATH_ADMINISTRATOR,
					'com_acctexp.iso4217' => JPATH_ADMINISTRATOR );

xJLanguageHandler::loadList( $langlist );

JLoader::register('JPaneTabs',  JPATH_LIBRARIES.'/joomla/html/pane.php');

xJACLhandler::adminBlock( $aecConfig->cfg['adminaccess'], $aecConfig->cfg['manageraccess'] );

$task			= trim( aecGetParam( 'task', null ) );
$returnTask 	= trim( aecGetParam( 'returnTask', null ) );
$userid			= aecGetParam( 'userid', null );
$subscriptionid	= aecGetParam( 'subscriptionid', null );
$id				= aecGetParam( 'id', null );

if ( !is_null( $id ) ) {
	if ( !is_array( $id ) ) {
		$savid = $id;

		$id = array();
		$id[0] = $savid;
	}
}

$db = &JFactory::getDBO();

// Auto Heartbeat renew every one hour to make sure that the admin gets a view as recent as possible
$heartbeat = new aecHeartbeat();
$heartbeat->backendping();

if ( empty( $option ) ) {
	$option = aecGetParam( 'option', '0' );
}

switch( strtolower( $task ) ) {
	case 'heartbeat':
	case 'beat':
		// Manual Heartbeat
		$heartbeat = new aecHeartbeat();
		$heartbeat->beat();
		echo "wolves teeth";
		break;

	case 'editmembership': editUser( $option, $userid, $subscriptionid, $returnTask, aecGetParam('page') ); break;

	case 'quickfire': miQuickfire( $option, $subscriptionid, aecGetParam('mi'), aecGetParam('action') ); break;

	case 'savemembership': saveUser( $option ); break;
	case 'applymembership': saveUser( $option, 1 ); break;
	case 'cancelmembership': cancel( $option ); break;
	case 'showcentral': aecCentral( $option ); break;

	case 'showsubscriptions': listSubscriptions( $option, '', $subscriptionid, $userid, aecGetParam('plan') ); break;

	case 'showallsubscriptions':
		$groups = array( 'active', 'expired', 'pending', 'cancelled', 'hold', 'closed' );

		listSubscriptions( $option, $groups, $subscriptionid, $userid, aecGetParam('plan') );
		break;

	case 'showexcluded': listSubscriptions( $option, 'excluded', $subscriptionid, $userid ); break;
	case 'showactive': listSubscriptions( $option, 'active', $subscriptionid, $userid ); break;
	case 'showexpired': listSubscriptions( $option, 'expired', $subscriptionid, $userid, aecGetParam('plan') ); break;
	case 'showpending': listSubscriptions( $option, 'pending', $subscriptionid, $userid ); break;
	case 'showcancelled': listSubscriptions( $option, 'cancelled', $subscriptionid, $userid ); break;
	case 'showhold': listSubscriptions( $option, 'hold', $subscriptionid, $userid ); break;
	case 'showclosed': listSubscriptions( $option, 'closed', $subscriptionid, $userid ); break;
	case 'showmanual': listSubscriptions( $option, 'notconfig', $subscriptionid, $userid ); break;

	case 'showsettings': editSettings( $option ); break;
	case 'savesettings': saveSettings( $option ); break;
	case 'applysettings': saveSettings( $option, 1 ); break;
	case 'cancelsettings': aecRedirect( 'index.php?option=' . $option . '&task=showCentral', JText::_('AEC_CONFIG_CANCELLED') ); break;

	case 'showtemplates': listTemplates( $option ); break;
	case 'edittemplate': editTemplate( $option, aecGetParam('name') ); break;
	case 'savetemplate': saveTemplate( $option, aecGetParam('name') ); break;
	case 'applytemplate': saveTemplate( $option, aecGetParam('name'), 1 ); break;
	case 'canceltemplate': aecRedirect( 'index.php?option=' . $option . '&task=showTemplates', JText::_('AEC_CONFIG_CANCELLED') ); break;

	case 'showprocessors': listProcessors( $option ); break;
	case 'newprocessor': editProcessor( 0, $option ); break;
	case 'editprocessor': editProcessor( $id[0], $option ); break;
	case 'saveprocessor': saveProcessor( $option ); break;
	case 'applyprocessor': saveProcessor( $option, 1 ); break;
	case 'cancelprocessor': aecRedirect( 'index.php?option=' . $option . '&task=showProcessors', JText::_('AEC_CONFIG_CANCELLED') ); break;
	case 'publishprocessor': changeProcessor( $id, 1, 'active', $option ); break;
	case 'unpublishprocessor': changeProcessor( $id, 0, 'active', $option ); break;

	case 'showsubscriptionplans': listSubscriptionPlans( $option ); break;
	case 'showsubscriptionplans2': listSubscriptionPlans2( $option ); break;
	case 'getsubscriptionplans': getSubscriptionPlans(); break;
	case 'newsubscriptionplan': editSubscriptionPlan( 0, $option ); break;
	case 'editsubscriptionplan': editSubscriptionPlan( $id[0], $option ); break;
	case 'copysubscriptionplan': copyObject( $option, 'SubscriptionPlan', $id ); break;
	case 'savesubscriptionplan': saveSubscriptionPlan( $option ); break;
	case 'applysubscriptionplan': saveSubscriptionPlan( $option, 1 ); break;
	case 'publishsubscriptionplan': changeSubscriptionPlan( $id, 1, 'active', $option ); break;
	case 'unpublishsubscriptionplan': changeSubscriptionPlan( $id, 0, 'active', $option ); break;
	case 'visiblesubscriptionplan': changeSubscriptionPlan( $id, 1, 'visible', $option ); break;
	case 'invisiblesubscriptionplan': changeSubscriptionPlan( $id, 0, 'visible', $option ); break;
	case 'removesubscriptionplan': removeSubscriptionPlan( $id, $option, $returnTask ); break;
	case 'cancelsubscriptionplan': aecRedirect( 'index.php?option=' . $option . '&task=showSubscriptionPlans', JText::_('AEC_CMN_EDIT_CANCELLED') ); break;
	case 'orderplanup': orderObject( $option, 'SubscriptionPlan', $id[0], 1 ); break;
	case 'orderplandown': orderObject( $option, 'SubscriptionPlan', $id[0], 0 ); break;

	case 'showitemgroups': listItemGroups( $option ); break;
	case 'newitemgroup': editItemGroup( 0, $option ); break;
	case 'edititemgroup': editItemGroup( $id[0], $option ); break;
	case 'copyitemgroup': copyObject( $option, 'ItemGroup', $id ); break;
	case 'saveitemgroup': saveItemGroup( $option ); break;
	case 'applyitemgroup': saveItemGroup( $option, 1 ); break;
	case 'publishitemgroup': changeItemGroup( $id, 1, 'active', $option ); break;
	case 'unpublishitemgroup': changeItemGroup( $id, 0, 'active', $option ); break;
	case 'visibleitemgroup': changeItemGroup( $id, 1, 'visible', $option ); break;
	case 'invisibleitemgroup': changeItemGroup( $id, 0, 'visible', $option ); break;
	case 'removeitemgroup': removeItemGroup( $id, $option, $returnTask ); break;
	case 'cancelitemgroup': aecRedirect( 'index.php?option=' . $option . '&task=showItemGroups', JText::_('AEC_CMN_EDIT_CANCELLED') ); break;
	case 'ordergroupup': orderObject( $option, 'ItemGroup', $id[0], 1 ); break;
	case 'ordergroupdown': orderObject( $option, 'ItemGroup', $id[0], 0 ); break;

	case 'showmicrointegrations': listMicroIntegrations( $option ); break;
	case 'newmicrointegration': editMicroIntegration( 0, $option ); break;
	case 'editmicrointegration': editMicroIntegration( $id[0], $option ); break;
	case 'savemicrointegration': saveMicroIntegration( $option ); break;
	case 'applymicrointegration': saveMicroIntegration( $option, 1 ); break;
	case 'copymicrointegration': copyObject( $option, 'microIntegration', $id ); break;
	case 'publishmicrointegration': changeMicroIntegration( $id, 1, $option ); break;
	case 'unpublishmicrointegration': changeMicroIntegration( $id, 0, $option ); break;
	case 'removemicrointegration': removeMicroIntegration( $id, $option, $returnTask ); break;
	case 'cancelmicrointegration': cancelMicroIntegration( $option ); break;
	case 'ordermiup': orderObject( $option, 'microIntegration', $id[0], 1 ); break;
	case 'ordermidown': orderObject( $option, 'microIntegration', $id[0], 0 ); break;

	case 'showcoupons': listCoupons( $option ); break;

	case 'copycoupon':
		$db = &JFactory::getDBO();

		foreach ( $id as $pid ) {
			$c = explode( '.', $pid );

			$row = new Coupon( $c[0] );
			$row->load( $c[1] );
			$row->copy();
		}

		aecRedirect( 'index.php?option='. $option . '&task=showCoupons' );
		break;

	case 'newcoupon': editCoupon( 0, $option, 1 ); break;
	case 'editcoupon': editCoupon( $id[0], $option, 0 ); break;
	case 'savecoupon': saveCoupon( $option, 0 ); break;
	case 'applycoupon': saveCoupon( $option, 1 ); break;
	case 'publishcoupon': changeCoupon( $id, 1, $option ); break;
	case 'unpublishcoupon': changeCoupon( $id, 0, $option ); break;
	case 'removecoupon': removeCoupon( $id, $option, $returnTask ); break;
	case 'cancelcoupon': aecRedirect( 'index.php?option=' . $option . '&task=showCoupons', JText::_('AEC_CMN_EDIT_CANCELLED') ); break;

	case 'editcss': editCSS( $option ); break;
	case 'savecss': saveCSS( $option ); break;
	case 'cancelcss': aecRedirect( 'index.php?option='. $option ); break;

	case 'hacks':
		$undohack	= aecGetParam( 'undohack', 0 );
		$filename	= aecGetParam( 'filename', 0 );
		$check_hack	= $filename ? 0 : 1;

		hackcorefile( $option, $filename, $check_hack, $undohack );

		HTML_AcctExp::hacks( $option, hackcorefile( $option, 0, 1, 0 ) );
		break;

	case 'invoices': invoices( $option ); break;
	case 'newinvoice': editInvoice( 0, $option, $returnTask, $userid ); break;
	case 'editinvoice': editInvoice( $id[0], $option, $returnTask, $userid ); break;
	case 'applyinvoice': saveInvoice( $option, 1 ); break;
	case 'saveinvoice': saveInvoice( $option ); break;

	case 'clearinvoice': clearInvoice( $option, aecGetParam('invoice'), aecGetParam('applyplan'), $returnTask ); break;

	case 'cancelinvoice': cancelInvoice( $option, aecGetParam('invoice'), $returnTask ); break;

	case 'printinvoice': AdminInvoicePrintout( $option, aecGetParam('invoice') ); break;
	case 'pdfinvoice': AdminInvoicePDF( $option, aecGetParam('invoice') ); break;

	case 'history': history( $option ); break;
	case 'eventlog': eventlog( $option ); break;

	case 'stats': aec_stats( $option, aecGetParam('page') ); break;

	case 'statrequest': aec_statrequest( $option, aecGetParam('type'), aecGetParam('start'), aecGetParam('end') ); break;

	case 'testexportmembers': exportData( $option, 'members', 'export' ); break;
	case 'exportmembers': exportData( $option, 'members' ); break;
	case 'loadexportmembers': exportData( $option, 'members', 'load' ); break;
	case 'applyexportmembers': exportData( $option, 'members', 'apply' ); break;
	case 'exportexportmembers': exportData( $option, 'members', 'export' ); break;
	case 'saveexportmembers': exportData( $option, 'members', 'save' ); break;

	case 'testexportsales': exportData( $option, 'sales', 'export' ); break;
	case 'exportsales': exportData( $option, 'sales' ); break;
	case 'loadexportsales': exportData( $option, 'sales', 'load' ); break;
	case 'applyexportsales': exportData( $option, 'sales', 'apply' ); break;
	case 'exportexportsales': exportData( $option, 'sales', 'export' ); break;
	case 'saveexportsales': exportData( $option, 'sales', 'save' ); break;

	case 'import': importData( $option ); break;

	case 'toolbox': toolBoxTool( $option, aecGetParam('cmd') ); break;

	case 'credits': HTML_AcctExp::credits(); break;

	case 'quicklookup':
		$return = quicklookup( $option );

		if ( is_array( $return ) ) {
			aecCentral( $option, $return['return'], $return['search'] );
		} elseif ( strpos( $return, '</a>' ) || strpos( $return, '</div>' ) ) {
			aecCentral( $option, $return );
		} elseif ( !empty( $return ) ) {
			aecRedirect( 'index.php?option=' . $option . '&task=editMembership&userid=' . $return, JText::_('AEC_QUICKSEARCH_THANKS') );
		} else {
			aecRedirect( 'index.php?option=' . $option . '&task=showcentral', JText::_('AEC_QUICKSEARCH_NOTFOUND') );
		}
		break;

	case 'quicksearch':
		$search = quicklookup( $option );

		if ( empty($search) ) {
			echo JText::_('AEC_QUICKSEARCH_NOTFOUND');
		} else {
			echo $search;
		}

		exit;
		break;

	case 'noticesmodal': getNotices();exit; break;

	case 'readnoticeajax': readNotice($id[0]); exit; break;

	case 'readnoticesajax':
		$db = &JFactory::getDBO();

		$query = 'UPDATE #__acctexp_eventlog'
				. ' SET `notify` = \'0\''
				. ' WHERE `notify` = \'1\''
				;
		$db->setQuery( $query	);
		$db->query();
		exit;
		break;

	case 'getnotice': echo getNotice();exit; break;

	case 'readallnotices':
		$db = &JFactory::getDBO();

		$query = 'UPDATE #__acctexp_eventlog'
				. ' SET `notify` = \'0\''
				. ' WHERE `notify` = \'1\''
				;
		$db->setQuery( $query	);
		$db->query();

		aecCentral( $option );
		break;

	case 'toggleajax': toggleProperty( aecGetParam('type'), aecGetParam('id'), aecGetParam('property') ); exit; break;

	case 'addgroupajax': addGroup( aecGetParam('type'), aecGetParam('id'), aecGetParam('group') ); exit; break;

	case 'removegroupajax': removeGroup( aecGetParam('type'), aecGetParam('id'), aecGetParam('group') ); exit; break;

	case 'recallinstall':
		include_once( JPATH_SITE . '/administrator/components/com_acctexp/install.acctexp.php' );
		com_install();
		break;

	case 'initsettings':
		$aecConfig = new aecConfig();
		$aecConfig->initParams();

		echo 'SPLINES RETICULATED.';
		break;

	case 'parsertest':
		$top = new templateOverrideParser();
		break;

	case 'lessen':
		include_once( JPATH_SITE . '/components/com_acctexp/lib/lessphp/lessc.inc.php' );
		$less = new lessc();
		$less->setImportDir( array(JPATH_SITE . '/media/com_acctexp/less/') );
		//$less->setFormatter("compressed");
		$less->setPreserveComments(true);

		$v = new JVersion();

		if ( $v->isCompatible('3.0') ) {
			$less->compileFile( JPATH_SITE . "/media/com_acctexp/less/admin-j3.less", JPATH_SITE . '/media/com_acctexp/css/admin.css' );
		} else {
			$less->compileFile( JPATH_SITE . "/media/com_acctexp/less/admin.less", JPATH_SITE . '/media/com_acctexp/css/admin.css' );
		}

	default: aecCentral( $option ); break;
}

function toggleProperty( $type, $id, $property )
{
	$db = &JFactory::getDBO();

	$query = 'SELECT `'.$property.'` FROM #__acctexp_' . $type
			. ' WHERE `id` = ' . $id
			;
	$db->setQuery( $query );
	$newstate = $db->loadResult() ? 0 : 1;

	if ( $property == 'default' ) {
		if ( !$newstate ) {
			echo !$newstate;

			return;
		}

		// Reset all other items
		$query = 'UPDATE #__acctexp_' . $type
				. ' SET `'.$property.'` = '.($newstate ? 0 : 1)
				. ' WHERE `id` != ' . $id
				;
		$db->setQuery( $query );
		$db->query();
	}

	$query = 'UPDATE #__acctexp_' . $type
			. ' SET `'.$property.'` = '.$newstate
			. ' WHERE `id` = ' . $id
			;
	$db->setQuery( $query );
	$db->query();

	echo $newstate;
}

function addGroup( $type, $id, $groupid )
{
	$db = &JFactory::getDBO();

	if ( ItemGroupHandler::setChildren( $groupid, array( $id ), $type ) ) {
		$group = new ItemGroup();
		$group->load( $groupid );

		$g = array();
		$g['id']	= $group->id;
		$g['name']	= $group->getProperty('name');
		$g['color']	= $group->params['color'];
		$g['icon']	= $group->params['icon'].'.png';

		$g['group']	= '<strong>' . $group->id . '</strong>';

		HTML_AcctExp::groupRow( $type, $g );
	}
}

function removeGroup( $type, $id, $groupid )
{
	ItemGroupHandler::removeChildren( $id, array( $groupid ), $type );

	echo 1;
}

function orderObject( $option, $type, $id, $up, $customreturn=null )
{
	$db = &JFactory::getDBO();

	$row = new $type();
	$row->load( $id );
	$row->move( $up ? -1 : 1 );

	aecRedirect( 'index.php?option='. $option . '&task=' . ( empty( $customreturn ) ? 'show' . $type . 's' : $customreturn ) );
}

function copyObject( $option, $type, $id, $up, $customreturn=null )
{
	$db = &JFactory::getDBO();

	foreach ( $id as $pid ) {
		$row = new $type( $db, 1 );
		$row->load( $pid );
		$row->copy();
	}

	aecRedirect( 'index.php?option='. $option . '&task=' . ( empty( $customreturn ) ? 'show' . $type . 's' : $customreturn ) );
}

function aecCentral( $option, $searchresult=null, $searchcontent=null )
{
	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

	$query = 'SELECT COUNT(*)'
			. ' FROM #__acctexp_eventlog'
			. ' WHERE `notify` = \'1\''
			;
	$db->setQuery( $query );
	$furthernotices = $db->loadResult() - 10;

	$query = 'SELECT *'
			. ' FROM #__acctexp_eventlog'
			. ' WHERE `notify` = \'1\''
			. ' ORDER BY `datetime` DESC'
			. ' LIMIT 0, 10'
			;
	$db->setQuery( $query	);
	$notices = $db->loadObjectList();

 	HTML_AcctExp::central( $searchresult, $notices, $furthernotices, $searchcontent );
}

function getNotices()
{
	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

	$query = 'SELECT COUNT(*)'
			. ' FROM #__acctexp_eventlog'
			. ' WHERE `notify` = \'1\''
			;
	$db->setQuery( $query );
	$furthernotices = $db->loadResult() - 5;

	$query = 'SELECT *'
			. ' FROM #__acctexp_eventlog'
			. ' WHERE `notify` = \'1\''
			. ' ORDER BY `datetime` DESC'
			. ' LIMIT 0, 5'
			;
	$db->setQuery( $query	);
	$notices = $db->loadObjectList();

 	HTML_AcctExp::eventlogModal( $notices, $furthernotices );
}

function readNotice( $id )
{
	$db = &JFactory::getDBO();

	$query = 'UPDATE #__acctexp_eventlog'
			. ' SET `notify` = \'0\''
			. ' WHERE `id` = \'' . $id . '\''
			;
	$db->setQuery( $query	);
	$db->query(); echo 1;exit;
}

function getNotice()
{
	$db = &JFactory::getDBO();

	$query = 'SELECT *'
			. ' FROM #__acctexp_eventlog'
			. ' WHERE `notify` = \'1\''
			. ' ORDER BY `datetime` DESC'
			. ' LIMIT 5, 1'
			;
	$db->setQuery( $query	);
	$notice = $db->loadObject();

	if ( empty( $notice->id ) ) {
		return '';
	}

	$noticex = array( 2 => 'success', 8 => 'info', 32 => 'warning', 128 => 'error' );

	return '<div class="alert alert-' . $noticex[$notice->level] . '" id="alert-' . $notice->id . '">
			<a class="close" href="#' . $notice->id . '" onclick="readNotice(' . $notice->id . ')">&times;</a>
			<h5><strong>' . JText::_( "AEC_NOTICE_NUMBER_" . $notice->level ) . ': ' . $notice->short . '</strong></h5>
			<p>' . substr( htmlentities( stripslashes( $notice->event ) ), 0, 256 ) . '</p>
			<span class="help-block">' . $notice->datetime . '</span>
		</div>';
}

function cancel( $option )
{
	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

 	$limit		= $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg( 'list_limit' ) );
	$limitstart = $app->getUserStateFromRequest( "viewnotconf{$option}limitstart", 'limitstart', 0 );
	$nexttask	= aecGetParam( 'nexttask', 'config' ) ;

	$app->redirect( 'index.php?option=' . $option . '&task=' . $nexttask, JText::_('CANCELED') );
}

function editUser( $option, $userid, $subscriptionid, $task, $page=0 )
{
	if ( !empty( $subscriptionid ) ) {
		$userid = AECfetchfromDB::UserIDfromSubscriptionID( $subscriptionid );
	}

	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

	$lang = JFactory::getLanguage();

	if ( !empty( $subscriptionid ) ) {
		$sid = $subscriptionid;
	} else {
		$sid = 0;
	}

	$lists = array();

	$metaUser = new metaUser( $userid );

	if ( !empty( $sid ) ) {
		$metaUser->moveFocus( $sid );
	} else {
		if ( $metaUser->hasSubscription ) {
			$sid = $metaUser->focusSubscription->id;
		}
	}

	if ( $metaUser->loadSubscriptions() && !empty( $sid ) ) {
		foreach ( $metaUser->allSubscriptions as $s_id => $s_c ) {
			if ( $s_c->id == $sid ) {
				$metaUser->allSubscriptions[$s_id]->current_focus = true;
				continue;
			}
		}
	}

	$invoices_limit = 15;

	$invoice_ids = AECfetchfromDB::InvoiceIdList( $metaUser->userid, $page*$invoices_limit, $invoices_limit );

	$group_selection = array();
	$group_selection[] = JHTML::_('select.option', '',			JText::_('EXPIRE_SET') );
	$group_selection[] = JHTML::_('select.option', 'expired',	JText::_('EXPIRE_NOW') );
	$group_selection[] = JHTML::_('select.option', 'excluded',	JText::_('EXPIRE_EXCLUDE') );
	$group_selection[] = JHTML::_('select.option', 'active',	JText::_('EXPIRE_INCLUDE') );
	$group_selection[] = JHTML::_('select.option', 'closed',	JText::_('EXPIRE_CLOSE') );
	$group_selection[] = JHTML::_('select.option', 'cancelled',	JText::_('EXPIRE_CANCEL') );
	$group_selection[] = JHTML::_('select.option', 'hold',		JText::_('EXPIRE_HOLD') );

	$lists['set_status'] = JHTML::_('select.genericlist', $group_selection, 'set_status', 'class="inputbox" size="1"', 'value', 'text', '' );

	$invoices = array();
	$couponsh = array();
	$invoice_counter = 0;

	foreach ( $invoice_ids as $inv_id ) {
		$invoice = new Invoice();
		$invoice->load ($inv_id );

		if ( !empty( $invoice->coupons ) ) {
			foreach( $invoice->coupons as $coupon_code ) {
				if ( !isset( $couponsh[$coupon_code] ) ) {
					$couponsh[$coupon_code] = couponHandler::idFromCode( $coupon_code );
				}

				$couponsh[$coupon_code]['invoices'][] = $invoice->invoice_number;
			}
		}

		if ( $invoice_counter >= $invoices_limit && ( strcmp( $invoice->transaction_date, '0000-00-00 00:00:00' ) !== 0 ) ) {
			continue;
		} else {
			$invoice_counter++;
		}

		$status = aecHTML::Icon( 'plus' ) . HTML_AcctExp::DisplayDateInLocalTime( $invoice->created_date ) . '<br />';

		$current_status = 'uncleared';

		if ( isset( $invoice->params['deactivated'] ) ) {
			$status .= aecHTML::Icon( 'remove-circle' ) . 'deactivated';
		} elseif ( strcmp( $invoice->transaction_date, '0000-00-00 00:00:00' ) === 0 ) {
			if ( isset( $invoice->params['pending_reason'] ) ) {
				if ( $lang->hasKey( 'PAYMENT_PENDING_REASON_' . strtoupper( $invoice->params['pending_reason'] ) ) ) {
					$status .= aecHTML::Icon( 'warning-sign' ) . JText::_( 'PAYMENT_PENDING_REASON_' . strtoupper($invoice->params['pending_reason'] ) );
				} else {
					$status .= aecHTML::Icon( 'warning-sign' ) . $invoice->params['pending_reason'];
				}
			} else {
				$status .= aecHTML::Icon( 'time' ) . 'uncleared';
			}
		}

		$actions	= array();
		$rowstyle	= '';

		if ( strcmp( $invoice->transaction_date, '0000-00-00 00:00:00' ) === 0 ) {
			$checkoutlink = AECToolbox::deadsureURL( 'index.php?option=' . $option . '&amp;task=repeatPayment&amp;invoice=' . $invoice->invoice_number );

			$actions = array(
								array( 'repeat', 'arrow-right', 'USERINVOICE_ACTION_REPEAT', 'info', '', $checkoutlink ),
								array( 'cancel', 'remove', 'USERINVOICE_ACTION_CANCEL', 'danger' ),
								array( 'clear', 'ok', 'USERINVOICE_ACTION_CLEAR_APPLY', 'success', '&applyplan=1' ),
								array( 'clear', 'check', 'USERINVOICE_ACTION_CLEAR', 'warning' ),
			);

			$rowstyle = ' style="background-color:#fee;"';
		} else {
			$status .= aecHTML::Icon( 'shopping-cart' ) . HTML_AcctExp::DisplayDateInLocalTime( $invoice->transaction_date );
		}

		$actions[] = array( 'print', 'print', 'HISTORY_ACTION_PRINT', '', '&tmpl=component" target="_blank' );
		$actions[] = array( 'pdf', 'file', 'PDF', '', '' );

		$actionlist = '<div class="btn-group">';
		foreach ( $actions as $action ) {
			if ( !empty( $action[5] ) ) {
				$alink = $action[5];
			} else {
				$alink = 'index.php?option=' . $option . '&task='.$action[0].'Invoice&invoice='. $invoice->invoice_number . '&returnTask=editMembership&userid=' . $metaUser->userid;

				if ( !empty( $action[4] ) ) {
					$alink .= $action[4];
				}
			}

			$actionlist .= aecHTML::Button( $action[1], $action[2], $action[3], $alink );
		}
		$actionlist .= '</div>';

		$non_formatted = $invoice->invoice_number;
		$invoice->formatInvoiceNumber();
		$is_formatted = $invoice->invoice_number;

		if ( $non_formatted != $is_formatted ) {
			$is_formatted = $non_formatted . "\n" . '(' . $is_formatted . ')';
		}

		$invoices[$inv_id] = array();
		$invoices[$inv_id]['rowstyle']			= $rowstyle;
		$invoices[$inv_id]['invoice_number']	= $is_formatted;
		$invoices[$inv_id]['amount']			= $invoice->amount . '&nbsp;' . $invoice->currency;
		$invoices[$inv_id]['status']			= $status;
		$invoices[$inv_id]['processor']			= $invoice->method;
		$invoices[$inv_id]['usage']				= $invoice->usage;
		$invoices[$inv_id]['actions']			= $actionlist;
	}

	$coupons = array();

	$coupon_counter = 0;
	foreach ( $couponsh as $coupon_code => $coupon ) {
		if ( $coupon_counter >= 10 ) {
			continue;
		} else {
			$coupon_counter++;
		}

		$cc = array();
		$cc['coupon_code']	= '<a href="index.php?option=com_acctexp&amp;task=editCoupon&id=' . $coupon['type'].'.'.$coupon['id'] . '">' . $coupon_code . '</a>';
		$cc['invoices']		= implode( ", ", $coupon['invoices'] );

		$coupons[] = $cc;
	}

	// get available plans
	$available_plans	= SubscriptionPlanHandler::getActivePlanList();

	$lists['assignto_plan'] = JHTML::_('select.genericlist', $available_plans, 'assignto_plan[]', 'size="5" multiple="multiple"', 'value', 'text', 0 );

	$userMIs = $metaUser->getUserMIs();

	$mi					= array();
	$mi['profile']		= array();
	$mi['admin']		= array();
	$mi['profile_form']	= array();
	$mi['admin_form']	= array();

	foreach ( $userMIs as $m ) {
		$pref = 'mi_'.$m->id.'_';

		$ui = $m->profile_info( $metaUser );
		if ( !empty( $ui ) ) {
			$mi['profile'][] = array( 'name' => $m->info['name'] . ' - ' . $m->name, 'info' => $ui );
		}

		$uf = $m->profile_form( $metaUser, true );
		if ( !empty( $uf ) ) {
			foreach ( $uf as $k => $v ) {
				$mi['profile_form'][] = $pref.$k;
				$params[$pref.$k] = $v;
			}
		}

		$ai = $m->admin_info( $metaUser );
		if ( !empty( $ai ) ) {
			$mi['admin'][] = array( 'name' => $m->info['name'] . ' - ' . $m->name, 'info' => $ai );
		}

		$af = $m->admin_form( $metaUser );
		if ( !empty( $af ) ) {
			foreach ( $af as $k => $v ) {
				$mi['admin_form'][] = $pref.$k;
				$params[$pref.$k] = $v;
			}
		}
	}

	if ( !empty( $params ) ) {
		$settings = new aecSettings ( 'userForm', 'mi' );
		$settings->fullSettingsArray( $params, array(), $lists ) ;

		// Call HTML Class
		$aecHTML = new aecHTML( $settings->settings, $settings->lists );
	} else {
		$aecHTML = new stdClass();
	}

	$aecHTML->invoice_pages	= (int) ( AECfetchfromDB::InvoiceCountbyUserID( $metaUser->userid ) / $invoices_limit );
	$aecHTML->invoice_page	= $page;
	$aecHTML->sid			= $sid;

	HTML_AcctExp::userForm( $option, $metaUser, $invoices, $coupons, $mi, $lists, $task, $aecHTML );
}

function saveUser( $option, $apply=0 )
{
	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

	$post = $_POST;

	if ( $post['assignto_plan'][0] == 0 ) {
		unset( $post['assignto_plan'][0] );
	}

	$metaUser = new metaUser( $post['userid'] );

	if ( $metaUser->hasSubscription && !empty( $post['subscriptionid'] ) ) {
		$metaUser->moveFocus( $post['subscriptionid'] );
	}

	$ck_primary = aecGetParam( 'ck_primary' );

	if ( $ck_primary && !$metaUser->focusSubscription->primary ) {
		$metaUser->focusSubscription->makePrimary();
	}

	if ( !empty( $post['assignto_plan'] ) && is_array( $post['assignto_plan'] ) ) {
		foreach ( $post['assignto_plan'] as $assign_planid ) {
			$plan = new SubscriptionPlan();
			$plan->load( $assign_planid );

			$metaUser->establishFocus( $plan );

			$metaUser->focusSubscription->applyUsage( $assign_planid, 'none', 1 );

			// We have to reload the metaUser object because of the changes
			$metaUser = new metaUser( $post['userid'] );

			$metaUser->hasSubscription = true;
		}
	}

	$ck_lifetime = aecGetParam( 'ck_lifetime' );

	$set_status = trim( aecGetParam( 'set_status', null ) );

	if ( !$metaUser->hasSubscription ) {
		if ( $set_status == 'excluded' ) {
			$metaUser->focusSubscription = new Subscription();
			$metaUser->focusSubscription->createNew( $metaUser->userid, 'none', 0 );

			$metaUser->hasSubscription = true;
		} else {
			echo "<script> alert('".JText::_('AEC_ERR_NO_SUBSCRIPTION')."'); window.history.go(-1); </script>\n";
			exit();
		}
	}

	if ( empty( $post['assignto_plan'] ) ) {
		if ( $ck_lifetime ) {
			$metaUser->focusSubscription->expiration	= '9999-12-31 00:00:00';
			$metaUser->focusSubscription->status		= 'Active';
			$metaUser->focusSubscription->lifetime	= 1;
		} elseif ( !empty( $post['expiration'] ) ) {
			if ( $post['expiration'] != $post['expiration_check'] ) {
				if ( strpos( $post['expiration'], ':' ) === false ) {
					$metaUser->focusSubscription->expiration = $post['expiration'] . ' 00:00:00';
				} else {
					$metaUser->focusSubscription->expiration = $post['expiration'];
				}

				if ( $metaUser->focusSubscription->status == 'Trial' ) {
					$metaUser->focusSubscription->status = 'Trial';
				} else {
					$metaUser->focusSubscription->status = 'Active';
				}

				$metaUser->focusSubscription->lifetime = 0;
			}
		}
	}

	if ( !empty( $set_status ) ) {
		switch ( $set_status ) {
			case 'expired':
				$metaUser->focusSubscription->expire();
				break;
			case 'cancelled':
				$metaUser->focusSubscription->cancel();
				break;
			default:
				$metaUser->focusSubscription->setStatus( ucfirst( $set_status ) );
				break;
		}
	}

	if ( !empty( $post['notes'] ) ) {
		$metaUser->focusSubscription->customparams['notes'] = $post['notes'];

		unset( $post['notes'] );
	}

	if ( $metaUser->hasSubscription ) {
		$metaUser->focusSubscription->storeload();
	}

	$userMIs = $metaUser->getUserMIs();

	if ( !empty( $userMIs ) ) {
		foreach ( $userMIs as $m ) {
			$params = array();

			$pref = 'mi_'.$m->id.'_';

			$uf = $m->profile_form( $metaUser );
			if ( !empty( $uf ) ) {
				foreach ( $uf as $k => $v ) {
					if ( isset( $post[$pref.$k] ) ) {
						$params[$k] = $post[$pref.$k];
					}
				}

				$m->profile_form_save( $metaUser, $params );
			}

			$admin_params = array();

			$af = $m->admin_form( $metaUser );
			if ( !empty( $af ) ) {
				foreach ( $af as $k => $v ) {
					if ( isset( $post[$pref.$k] ) ) {
						$admin_params[$k] = $post[$pref.$k];
					}
				}

				$m->admin_form_save( $metaUser, $admin_params );
			}

			if ( empty( $params ) ) {
				continue;
			}

			$metaUser->meta->setMIParams( $m->id, null, $params, true );
		}

		$metaUser->meta->storeload();
	}

 	$limit		= $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg( 'list_limit' ) );
	$limitstart	= $app->getUserStateFromRequest( "viewnotconf{$option}limitstart", 'limitstart', 0 );

	$nexttask	= aecGetParam( 'nexttask', 'showSubscriptions' ) ;

	if ( empty( $nexttask ) ) {
		$nexttask = 'showSubscriptions';
	}

	if ( $apply ) {
		$subID = !empty($post['subscriptionid']) ? $post['subscriptionid'] : $metaUser->focusSubscription->id;

		if ( empty( $subID ) ) {
			aecRedirect( 'index.php?option=' . $option . '&task=editMembership&userid=' . $metaUser->userid, JText::_('AEC_MSG_SUCESSFULLY_SAVED') );
		} else {
			aecRedirect( 'index.php?option=' . $option . '&task=editMembership&subscriptionid=' . $subID, JText::_('AEC_MSG_SUCESSFULLY_SAVED') );
		}
	} else {
		aecRedirect( 'index.php?option=' . $option . '&task=' . $nexttask, JText::_('SAVED') );
	}
}

function listSubscriptions( $option, $set_group, $subscriptionid, $userid=array(), $planid=null )
{
	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

	$limit			= $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg( 'list_limit' ) );
	$limitstart		= $app->getUserStateFromRequest( "viewconf{$option}limitstart", 'limitstart', 0 );

	$orderby		= $app->getUserStateFromRequest( "orderby_subscr{$option}", 'orderby_subscr', 'name ASC' );
	$groups			= $app->getUserStateFromRequest( "groups{$option}", 'groups', 'active' );
	$search			= $app->getUserStateFromRequest( "search{$option}", 'search', '' );
	$search			= xJ::escape( $db, trim( strtolower( $search ) ) );

	if ( empty( $planid ) ) {
		$filter_plan	= $app->getUserStateFromRequest( "filter_plan{$option}", 'filter_plan', 0 );
	} else {
		$filter_plan	= $planid;
	}

	if ( !is_array( $filter_plan ) ) {
		$filter_plan = array( $filter_plan );
	}

	$filter_group	= $app->getUserStateFromRequest( "filter_group{$option}", 'filter_group', 0 );

	if ( !is_array( $filter_group ) ) {
		$filter_group = array( $filter_group );
	}

	if ( !empty( $set_group ) && empty( $_REQUEST['groups'] ) ) {
		if ( is_array( $set_group ) ) {
			$groups		= $set_group;
		} else {
			$groups		= array();
			$groups[]	= $set_group;
		}
	} else {
		if ( $groups ) {
			if ( is_array($groups ) ) {
				if ( count( $groups ) == 1 ) {
					if ( $groups[0] == 'all' ) {
						$groups = array('active', 'excluded', 'expired', 'pending', 'cancelled', 'hold', 'closed');
					}
				}

				$groups 	= $groups;
				$set_group	= $groups[0];
			} else {
				$groups		= array( $groups );
			}
		}
	}

	if ( array_search( 'notconfig', $groups ) ) {
		$set_group	= 'notconfig';
	} else {
		$set_group	= $groups[0];
	}

	if ( !empty( $orderby ) ) {
		if ( $set_group == "notconfig" ) {
			$forder = array(	'name ASC', 'name DESC', 'lastname ASC', 'lastname DESC', 'username ASC', 'username DESC',
								'signup_date ASC', 'signup_date DESC', 'lastpay_date ASC', 'lastpay_date DESC',
								);
		} else {
			$forder = array(	'expiration ASC', 'expiration DESC', 'lastpay_date ASC', 'lastpay_date DESC',
								'name ASC', 'name DESC', 'lastname ASC', 'lastname DESC', 'username ASC', 'username DESC',
								'signup_date ASC', 'signup_date DESC', 'lastpay_date ASC', 'lastpay_date DESC',
								'plan_name ASC', 'plan_name DESC', 'status ASC', 'status DESC', 'type ASC', 'type DESC'
								);
		}

		if ( !in_array( $orderby, $forder ) ) {
			$orderby = 'name ASC';
		}
	}

	// define displaying at html
	$action = array();
	switch( $set_group ){
		case 'active':
			$action[0]	= 'active';
			$action[1]	= JText::_('AEC_HEAD_ACTIVE_SUBS');
			break;

		case 'excluded':
			$action[0]	= 'excluded';
			$action[1]	= JText::_('AEC_HEAD_EXCLUDED_SUBS');
			break;

		case 'expired':
			$action[0]	= 'expired';
			$action[1]	= JText::_('AEC_HEAD_EXPIRED_SUBS');
			break;

		case 'pending':
			$action[0]	= 'pending';
			$action[1]	= JText::_('AEC_HEAD_PENDING_SUBS');
			break;

		case 'cancelled':
			$action[0]	= 'cancelled';
			$action[1]	= JText::_('AEC_HEAD_CANCELLED_SUBS');
			break;

		case 'hold':
			$action[0]	= 'hold';
			$action[1]	= JText::_('AEC_HEAD_HOLD_SUBS');
			break;

		case 'closed':
			$action[0]	= 'closed';
			$action[1]	= JText::_('AEC_HEAD_CLOSED_SUBS');
		break;

		case 'notconfig':
			$action[0]	= 'manual';
			$action[1]	= JText::_('AEC_HEAD_MANUAL_SUBS');
			break;
	}

	$filter		= '';
	$where		= array();
	$where_or	= array();
	$notconfig	= false;

	$planid = trim( aecGetParam( 'assign_planid', null ) );

	$users_selected = ( ( is_array( $subscriptionid ) && count( $subscriptionid ) ) || ( is_array( $userid ) && count( $userid ) ) );

	if ( !empty( $planid ) && $users_selected ) {
		$plan = new SubscriptionPlan();
		$plan->load( $planid );

		if ( !empty( $subscriptionid ) ) {
			foreach ( $subscriptionid as $sid ) {
				$metaUser = new metaUser( false, $sid );

				$metaUser->establishFocus( $plan );

				$metaUser->focusSubscription->applyUsage( $planid, 'none', 1 );
			}
		}

		if ( !empty( $userid ) ) {
			foreach ( $userid as $uid ) {
				$metaUser = new metaUser( $uid );

				$metaUser->establishFocus( $plan );

				$metaUser->focusSubscription->applyUsage( $planid, 'none', 1 );

				$subscriptionid[] = $metaUser->focusSubscription->id;
			}
		}

		// Also show active users now
		if ( !in_array( 'active', $groups ) ) {
			$groups[] = 'active';
		}
	}

	$expire = trim( aecGetParam( 'set_expiration', null ) );
	if ( !is_null( $expire ) && is_array( $subscriptionid ) && count( $subscriptionid ) > 0 ) {
		foreach ( $subscriptionid as $k ) {
			$subscriptionHandler = new Subscription();

			if ( !empty( $k ) ) {
				$subscriptionHandler->load( $k );
			} else {
				$subscriptionHandler->createNew( $k, '', 1 );
			}

			if ( strcmp( $expire, 'now' ) === 0) {
				$subscriptionHandler->expire();

				if ( !in_array( 'expired', $groups ) ) {
					$groups[] = 'expired';
				}
			} elseif ( strcmp( $expire, 'exclude' ) === 0 ) {
				$subscriptionHandler->setStatus( 'Excluded' );

				if ( !in_array( 'excluded', $groups ) ) {
					$groups[] = 'excluded';
				}
			} elseif ( strcmp( $expire, 'close' ) === 0 ) {
				$subscriptionHandler->setStatus( 'Closed' );

				if ( !in_array( 'closed', $groups ) ) {
					$groups[] = 'closed';
				}
			} elseif ( strcmp( $expire, 'hold' ) === 0 ) {
				$subscriptionHandler->setStatus( 'Hold' );

				if ( !in_array( 'hold', $groups ) ) {
					$groups[] = 'hold';
				}
			} elseif ( strcmp( $expire, 'include' ) === 0 ) {
				$subscriptionHandler->setStatus( 'Active' );

				if ( !in_array( 'active', $groups ) ) {
					$groups[] = 'active';
				}
			} elseif ( strcmp( $expire, 'lifetime' ) === 0 ) {
				if ( !$subscriptionHandler->is_lifetime() ) {
					$subscriptionHandler->expiration = '9999-12-31 00:00:00';
					$subscriptionHandler->lifetime = 1;
				}

				$subscriptionHandler->setStatus( 'Active' );

				if ( !in_array( 'active', $groups ) ) {
					$groups[] = 'active';
				}
			} elseif ( strpos( $expire, 'set' ) === 0 ) {
				$subscriptionHandler->setExpiration( 'M', substr( $expire, 4 ), 0 );

				$subscriptionHandler->lifetime = 0;
				$subscriptionHandler->setStatus( 'Active' );

				if ( !in_array( 'active', $groups ) ) {
					$groups[] = 'active';
				}
			} elseif ( strpos( $expire, 'add' ) === 0 ) {
				if ( $subscriptionHandler->lifetime) {
					$subscriptionHandler->setExpiration( 'M', substr( $expire, 4 ), 0 );
				} else {
					$subscriptionHandler->setExpiration( 'M', substr( $expire, 4 ), 1 );
				}

				$subscriptionHandler->lifetime = 0;
				$subscriptionHandler->setStatus( 'Active' );

				if ( !in_array( 'active', $groups ) ) {
					$groups[] = 'active';
				}
			}
		}
	}

	if ( is_array( $groups ) ) {
		if ( in_array( 'notconfig', $groups ) ) {
 			$notconfig = true;
 			$groups = array( 'notconfig' );
		} else {
			if ( in_array( 'excluded', $groups ) ) {
				$where_or[] = "a.status = 'Excluded'";
			}
			if ( in_array( 'expired', $groups ) ) {
				$where_or[] = "a.status = 'Expired'";
			}
			if ( in_array( 'active', $groups ) ) {
				$where_or[] = "(a.status = 'Active' || a.status = 'Trial')";
			}
			if ( in_array( 'pending', $groups ) ) {
				$where_or[] = "a.status = 'Pending'";
			}
			if ( in_array( 'cancelled', $groups ) ) {
				$where_or[] = "a.status = 'Cancelled'";
			}
			if ( in_array( 'hold', $groups ) ) {
				$where_or[] = "a.status = 'Hold'";
			}
			if ( in_array( 'closed', $groups ) ) {
	 			$where_or[] = "a.status = 'Closed'";
			}
		}
	}

	if ( isset( $search ) && $search!= '' ) {
		if ( $notconfig ) {
			$where[] = "(username LIKE '%$search%' OR name LIKE '%$search%')";
		} else {
			$where[] = "(b.username LIKE '%$search%' OR b.name LIKE '%$search%')";
		}
	}

	$group_plans = ItemGroupHandler::getChildren( $filter_group, 'item' );

	if ( !empty( $filter_plan ) || !empty( $group_plans ) ) {
		$plan_selection = array();

		if ( !empty( $filter_plan ) ) {
			$plan_selection = $filter_plan;
		}

		if ( !empty( $group_plans ) ) {
			$plan_selection = array_merge( $plan_selection, $group_plans );
		}

		if ( empty( $plan_selection[0] ) ) {
			unset( $plan_selection[0] );
		}

		$plan_selection = array_unique( $plan_selection );

		if ( !$notconfig && !empty( $plan_selection ) ) {
			$where[] = "a.plan IN (" . implode( ',', $plan_selection ) . ")";
		}
	}

	// get the total number of records
	if ( $notconfig ) {
		$where[] = 'b.status is null';

		$query = 'SELECT count(*)'
				. ' FROM #__users AS a'
				. ' LEFT JOIN #__acctexp_subscr AS b ON a.id = b.userid'
				. (count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' )
				;
	} else {
		$query = 'SELECT count(*)'
				. ' FROM #__acctexp_subscr AS a'
				. ' INNER JOIN #__users AS b ON a.userid = b.id'
				;

		if ( count( $where_or ) ) {
			$where[] = ( count( $where_or ) ? '(' . implode( ' OR ', $where_or ) . ')' : '' );
		}

		$query .= (count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
	}

	$db->setQuery( $query );
	$total = $db->loadResult();

	$pageNav = new bsPagination( $total, $limitstart, $limit );

	// get the subset (based on limits) of required records
	if ( $notconfig ) {
		$forder = array(	'name ASC', 'name DESC', 'lastname ASC', 'lastname DESC', 'username ASC', 'username DESC',
							'signup_date ASC', 'signup_date DESC' );

		if ( !in_array( $orderby, $forder ) ) {
			$orderby = 'name ASC';
		}

		if ( strpos( $orderby, 'lastname' ) !== false ) {
			$orderby = str_replace( 'lastname', 'SUBSTRING_INDEX(name, \' \', -1)', $orderby );
		}

		$query = 'SELECT a.id, a.name, a.username, a.registerDate as signup_date'
				. ' FROM #__users AS a'
				. ' LEFT JOIN #__acctexp_subscr AS b ON a.id = b.userid'
				. (count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' )
				. ' ORDER BY ' . str_replace( 'signup_date', 'registerDate', $orderby )
				. ' LIMIT ' . $pageNav->limitstart . ',' . $pageNav->limit
				;

		if ( strpos( $orderby, 'SUBSTRING_INDEX' ) !== false ) {
			$orderby = str_replace( 'SUBSTRING_INDEX(name, \' \', -1)', 'lastname', $orderby );
		}
	} else {
		if ( strpos( $orderby, 'lastname' ) !== false ) {
			$orderby = str_replace( 'lastname', 'SUBSTRING_INDEX(b.name, \' \', -1)', $orderby );
		}

		$query = 'SELECT a.*, b.name, b.username, b.email, c.name AS plan_name'
				. ' FROM #__acctexp_subscr AS a'
				. ' INNER JOIN #__users AS b ON a.userid = b.id'
				. ' LEFT JOIN #__acctexp_plans AS c ON a.plan = c.id'
				. ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' )
				. ' ORDER BY ' . $orderby
				. ' LIMIT ' . $pageNav->limitstart . ',' . $pageNav->limit
				;

		if ( strpos( $orderby, 'SUBSTRING_INDEX' ) !== false ) {
			$orderby = str_replace( 'SUBSTRING_INDEX(b.name, \' \', -1)', 'lastname', $orderby );
		}
	}

	$db->setQuery( 'SET SQL_BIG_SELECTS=1');
	$db->query();

	$db->setQuery( $query );
	$rows = $db->loadObjectList();

	if ( $db->getErrorNum() ) {
		echo $db->stderr();
		return false;
	}

	$db->setQuery( 'SET SQL_BIG_SELECTS=0');
	$db->query();

	$sel = array();
	if ( $set_group != "manual" ) {
		$sel[] = JHTML::_('select.option', 'expiration ASC',	JText::_('EXP_ASC') );
		$sel[] = JHTML::_('select.option', 'expiration DESC',	JText::_('EXP_DESC') );
	}

	$sel[] = JHTML::_('select.option', 'name ASC',			JText::_('NAME_ASC') );
	$sel[] = JHTML::_('select.option', 'name DESC',			JText::_('NAME_DESC') );
	$sel[] = JHTML::_('select.option', 'lastname ASC',		JText::_('LASTNAME_ASC') );
	$sel[] = JHTML::_('select.option', 'lastname DESC',		JText::_('LASTNAME_DESC') );
	$sel[] = JHTML::_('select.option', 'username ASC',		JText::_('LOGIN_ASC') );
	$sel[] = JHTML::_('select.option', 'username DESC',		JText::_('LOGIN_DESC') );
	$sel[] = JHTML::_('select.option', 'signup_date ASC',	JText::_('SIGNUP_ASC') );
	$sel[] = JHTML::_('select.option', 'signup_date DESC',	JText::_('SIGNUP_DESC') );

	if ( $set_group != "manual" ) {
		$sel[] = JHTML::_('select.option', 'lastpay_date ASC',	JText::_('LASTPAY_ASC') );
		$sel[] = JHTML::_('select.option', 'lastpay_date DESC',	JText::_('LASTPAY_DESC') );
		$sel[] = JHTML::_('select.option', 'plan_name ASC',		JText::_('PLAN_ASC') );
		$sel[] = JHTML::_('select.option', 'plan_name DESC',	JText::_('PLAN_DESC') );
		$sel[] = JHTML::_('select.option', 'status ASC',		JText::_('STATUS_ASC') );
		$sel[] = JHTML::_('select.option', 'status DESC',		JText::_('STATUS_DESC') );
		$sel[] = JHTML::_('select.option', 'type ASC',			JText::_('TYPE_ASC') );
		$sel[] = JHTML::_('select.option', 'type DESC',			JText::_('TYPE_DESC') );
	}

	$lists['orderNav'] = JHTML::_('select.genericlist', $sel, 'orderby_subscr', 'class="inputbox span2" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $orderby );

	// Get list of plans for filter
	$query = 'SELECT `id`, `name`'
			. ' FROM #__acctexp_plans'
			. ' ORDER BY `ordering`'
			;
	$db->setQuery( $query );
	$db_plans = $db->loadObjectList();

	$plans2[] = JHTML::_('select.option', '0', JText::_('BIND_USER'), 'id', 'name' );
	if ( is_array( $db_plans ) ) {
		$plans2 = array_merge( $plans2, $db_plans );
	}
	$lists['planid']	= JHTML::_('select.genericlist', $plans2, 'assign_planid', 'class="inputbox span2" size="1" onchange="document.adminForm.submit();"', 'id', 'name', 0 );

	$lists['filter_plan'] = '<select id="plan-filter-select" name="filter_plan[]" multiple="multiple" size="5">';
	foreach ( $db_plans as $plan ) {
		$lists['filter_plan'] .= '<option value="' . $plan->id . '"' . ( in_array( $plan->id, $filter_plan ) ? ' selected="selected"' : '' ) . '/>' . $plan->name . '</option>';
	}
	$lists['filter_plan'] .= '</select>';

	$grouplist = ItemGroupHandler::getTree();

	$lists['filter_group'] = '<select id="group-filter-select" name="filter_group[]" multiple="multiple" size="5">';
	foreach ( $grouplist as $glisti ) {
		if ( defined( 'JPATH_MANIFESTS' ) ) {
			$lists['filter_group'] .= '<option value="' . $glisti[0] . '"' . ( in_array( $glisti[0], $filter_group ) ? ' selected="selected"' : '' ) . '/>' . str_replace( '&nbsp;', ' ', $glisti[1] ) . '</option>';
		} else {
			$lists['filter_group'] .= '<option value="' . $glisti[0] . '"' . ( in_array( $glisti[0], $filter_group ) ? ' selected="selected"' : '' ) . '/>' . $glisti[1] . '</option>';
		}
	}
	$lists['filter_group'] .= '</select>';

	$status = array(	'excluded'	=> JText::_('AEC_SEL_EXCLUDED'),
						'pending'	=> JText::_('AEC_SEL_PENDING'),
						'active'	=> JText::_('AEC_SEL_ACTIVE'),
						'expired'	=> JText::_('AEC_SEL_EXPIRED'),
						'closed'	=> JText::_('AEC_SEL_CLOSED'),
						'cancelled'	=> JText::_('AEC_SEL_CANCELLED'),
						'hold'		=> JText::_('AEC_SEL_HOLD'),
						'notconfig'	=> JText::_('AEC_SEL_NOT_CONFIGURED')
						);

	$lists['groups'] = '<select id="status-group-select" name="groups[]" multiple="multiple" size="5">';
	foreach ( $status as $id => $txt ) {
		$lists['groups'] .= '<option value="' . $id . '"' . ( in_array( $id, $groups ) ? ' selected="selected"' : '' ) . '/>' . $txt . '</option>';
	}
	$lists['groups'] .= '</select>';

	$group_selection = array();
	$group_selection[] = JHTML::_('select.option', '',			JText::_('EXPIRE_SET') );
	$group_selection[] = JHTML::_('select.option', 'now',		JText::_('EXPIRE_NOW') );
	$group_selection[] = JHTML::_('select.option', 'exclude',	JText::_('EXPIRE_EXCLUDE') );
	$group_selection[] = JHTML::_('select.option', 'lifetime',	JText::_('AEC_CMN_LIFETIME') );
	$group_selection[] = JHTML::_('select.option', 'include',	JText::_('EXPIRE_INCLUDE') );
	$group_selection[] = JHTML::_('select.option', 'close',		JText::_('EXPIRE_CLOSE') );
	$group_selection[] = JHTML::_('select.option', 'hold',		JText::_('EXPIRE_HOLD') );
	$group_selection[] = JHTML::_('select.option', 'add_1',		JText::_('EXPIRE_ADD01MONTH') );
	$group_selection[] = JHTML::_('select.option', 'add_3',		JText::_('EXPIRE_ADD03MONTH') );
	$group_selection[] = JHTML::_('select.option', 'add_12',	JText::_('EXPIRE_ADD12MONTH') );
	$group_selection[] = JHTML::_('select.option', 'set_1',		JText::_('EXPIRE_01MONTH') );
	$group_selection[] = JHTML::_('select.option', 'set_3',		JText::_('EXPIRE_03MONTH') );
	$group_selection[] = JHTML::_('select.option', 'set_12',	JText::_('EXPIRE_12MONTH') );

	$lists['set_expiration'] = JHTML::_('select.genericlist', $group_selection, 'set_expiration', 'class="inputbox span2" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', "");

	HTML_AcctExp::listSubscriptions( $rows, $pageNav, $search, $option, $lists, $subscriptionid, $action );
}

function editSettings( $option )
{
	$db = &JFactory::getDBO();

	global $aecConfig;

	// See whether we have a duplication
	if ( $aecConfig->RowDuplicationCheck() ) {
		// Clean out duplication and reload settings
		$aecConfig->CleanDuplicatedRows();
		$aecConfig = new aecConfig();
	}

	$lists = array();

	$currency_code_list	= AECToolbox::aecCurrencyField( true, true, true );
	$lists['currency_code_general'] = JHTML::_('select.genericlist', $currency_code_list, ( 'currency_code_general' ), 'size="10"', 'value', 'text', ( !empty( $aecConfig->cfg['currency_code_general'] ) ? $aecConfig->cfg['currency_code_general'] : '' ) );

	$available_plans	= SubscriptionPlanHandler::getActivePlanList();

	if ( !isset( $aecConfig->cfg['entry_plan'] ) ) {
		$aecConfig->cfg['entry_plan'] = 0;
	}

	$lists['entry_plan'] = JHTML::_('select.genericlist', $available_plans, 'entry_plan', 'size="' . min( 10, count( $available_plans ) + 2 ) . '"', 'value', 'text', $aecConfig->cfg['entry_plan'] );

	$gtree = xJACLhandler::getGroupTree( array( 28, 29, 30 ) );

	if ( !isset( $aecConfig->cfg['checkout_as_gift_access'] ) ) {
		$aecConfig->cfg['checkout_as_gift_access'] = 0;
	}
	
	// Create GID related Lists
	$lists['checkout_as_gift_access'] 		= JHTML::_('select.genericlist', $gtree, 'checkout_as_gift_access', 'size="6"', 'value', 'text', $aecConfig->cfg['checkout_as_gift_access'] );

	$tab_data = array();

	$params = array();
	$params[] = array( 'page-head', JText::_('General Configuration') );
	$params[] = array( 'section', 'access' );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_ACCESS') );
	$params['require_subscription']			= array( 'toggle', 0 );
	$params['adminaccess']					= array( 'toggle', 0 );
	$params['manageraccess']				= array( 'toggle', 0 );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_PROCESSORS') );
	$params['gwlist']						= array( 'list', 0 );
	$params['standard_currency']			= array( 'list_currency', 0 );
	$params[] = array( 'section-end' );

	$params[] = array( 'page-head', JText::_('Registration Flow') );
	$params[] = array( 'section', 'plans' );
	$params['plans_first']					= array( 'toggle', 0 );
	$params['integrate_registration']		= array( 'toggle', 0 );
	$params['skip_confirmation']			= array( 'toggle', 0 );
	$params[] = array( 'section-end' );
	$params[] = array( 'section', 'plans' );
	$params[] = array( 'section-head', JText::_('Plan List') );
	$params['root_group']					= array( 'list', 0 );
	$params[] = array( 'section-end' );
	$params[] = array( 'section', 'cart' );
	$params[] = array( 'section-head', 'Shopping Cart' );
	$params['enable_shoppingcart']			= array( 'toggle', '' );
	$params['additem_stayonpage']			= array( 'toggle', '' );
	$params[] = array( 'section-end' );
	$params[] = array( 'section', 'checkout' );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_CHECKOUT') );
	$params['checkout_coupons']				= array( 'toggle', 0 );
	$params['user_checkout_prefill']		= array( 'inputD', 0 );

	$rewriteswitches						= array( 'cms', 'user', 'expiration', 'subscription' );
	$params									= AECToolbox::rewriteEngineInfo( $rewriteswitches, $params );

	$params[] = array( 'section-end' );

	$params[] = array( 'page-head', JText::_('Inner workings') );
	$params[] = array( 'section', 'heartbeat' );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_SYSTEM') );
	$params['heartbeat_cycle']				= array( 'inputA', 0 );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_EMAIL') );
	$params['noemails']						= array( 'toggle', 0 );
	$params['noemails_adminoverride']		= array( 'toggle', 0 );
	$params['nojoomlaregemails']			= array( 'toggle', 0 );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_DEBUG') );
	$params['curl_default']					= array( 'toggle', 0 );
	$params['simpleurls']					= array( 'toggle', 0 );
	$params['error_notification_level']		= array( 'list', 0 );
	$params['email_notification_level']		= array( 'list', 0 );
	$params[] = array( 'section-end' );

	@end( $params );
	$tab_data[] = array( JText::_('CFG_TAB1_TITLE'), key( $params ), '<h2>' . JText::_('CFG_TAB1_SUBTITLE') . '</h2>' );

	$params[] = array( 'page-head', JText::_('CFG_TAB_CUSTOMIZATION_TITLE') );
	$params[] = array( 'section', 'customredirect' );
	$params[] = array( 'section-head', JText::_('CFG_CUSTOMIZATION_SUB_CREDIRECT') );
	$params['customintro']						= array( 'inputC', '' );
	$params['customintro_userid']				= array( 'toggle', '' );
	$params['customintro_always']				= array( 'toggle', '' );
	$params[] = array( 'section-end' );
	$params[] = array( 'section', 'invoice-number' );
	$params[] = array( 'section-head', JText::_('CFG_CUSTOMIZATION_SUB_FORMAT_INUM') );
	$params['invoicenum_doformat']				= array( 'toggle', '' );
	$params['invoicenum_formatting']			= array( 'inputD', '' );

	$rewriteswitches							= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );
	$params										= AECToolbox::rewriteEngineInfo( $rewriteswitches, $params );

	$params[] = array( 'section-end' );
	$params[] = array( 'section', 'captcha' );
	$params[] = array( 'section-head', JText::_('CFG_CUSTOMIZATION_SUB_CAPTCHA') );
	$params['use_recaptcha']					= array( 'toggle', '' );
	$params['recaptcha_privatekey']				= array( 'inputC', '' );
	$params['recaptcha_publickey']				= array( 'inputC', '' );
	$params[] = array( 'section-end' );
	$params[] = array( 'section', 'proxy' );
	$params[] = array( 'section-head', JText::_('CFG_CUSTOMIZATION_SUB_PROXY') );
	$params['use_proxy']						= array( 'toggle', '' );
	$params['proxy']							= array( 'inputC', '' );
	$params['proxy_port']						= array( 'inputC', '' );
	$params['proxy_username']					= array( 'inputC', '' );
	$params['proxy_password']					= array( 'inputC', '' );
	$params['gethostbyaddr']					= array( 'toggle', '' );
	$params[] = array( 'section-end' );

	$itemidlist = array(	'cart' => array( 'view' => 'cart', 'params' => false ),
							'checkout' => array( 'view' => 'checkout', 'params' => false ),
							'confirmation' => array( 'view' => 'confirmation', 'params' => false ),
							'subscribe' => array( 'view' => 'subscribe', 'params' => false ),
							'exception' => array( 'view' => 'exception', 'params' => false ),
							'thanks' => array( 'view' => 'thanks', 'params' => false ),
							'expired' => array( 'view' => 'expired', 'params' => false ),
							'hold' => array( 'view' => 'hold', 'params' => false ),
							'notallowed' => array( 'view' => 'notallowed', 'params' => false ),
							'pending' => array( 'view' => 'pending', 'params' => false ),
							'subscriptiondetails' => array( 'view' => 'subscriptiondetails', 'params' => false ),
							'subscriptiondetails_invoices' => array( 'view' => 'subscriptiondetails', 'params' => 'sub=invoices' ),
							'subscriptiondetails_details' => array( 'view' => 'subscriptiondetails', 'params' => 'sub=details' )
							);


	$params[] = array( 'section', 'date' );
	$params[] = array( 'section-head', JText::_('CFG_CUSTOMIZATION_SUB_FORMAT_DATE') );
	$params['display_date_backend']				= array( 'inputC', '%a, %d %b %Y %T %Z' );
	$params['display_date_frontend']			= array( 'inputC', '%a, %d %b %Y %T %Z' );
	$params[] = array( 'section-head', JText::_('CFG_CUSTOMIZATION_SUB_FORMAT_PRICE') );
	$params['amount_currency_symbol']			= array( 'toggle', 0 );
	$params['amount_currency_symbolfirst']		= array( 'toggle', 0 );
	$params['amount_use_comma']					= array( 'toggle', 0 );
	$params[] = array( 'section-end' );
	$params[] = array( 'section', 'itemid' );
	$params[] = array( 'section-head', JText::_('CFG_CUSTOMIZATION_SUB_ITEMID') );

	foreach ( $itemidlist as $param => $xparams ) {
		$params['itemid_'.$param]				= array( 'inputA', '' );
	}

	$params['itemid_cb']						= array( 'inputA', '' );
	$params['itemid_joomlauser']				= array( 'inputA', '' );

	$params[] = array( 'section-end' );

	@end( $params );
	$tab_data[] = array( JText::_('CFG_TAB_CUSTOMIZATION_TITLE'), key( $params ), '<h2>' . JText::_('CFG_TAB_CUSTOMIZATION_SUBTITLE') . '</h2>' );

	$params[] = array( 'page-head', JText::_('CFG_TAB_EXPERT_SUBTITLE') );
	$params[] = array( 'section', 'system' );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_SYSTEM') );
	$params['alertlevel2']					= array( 'inputA', 0 );
	$params['alertlevel1']					= array( 'inputA', 0 );
	$params['expiration_cushion']			= array( 'inputA', 0 );
	$params['invoice_cushion']				= array( 'inputA', 0 );
	$params['invoice_spawn_new']			= array( 'toggle', 0 );
	$params['heartbeat_cycle_backend']		= array( 'inputA', 0 );
	$params['allow_frontend_heartbeat']		= array( 'toggle', 0 );
	$params['disable_regular_heartbeat']	= array( 'toggle', 0 );
	$params['custom_heartbeat_securehash']	= array( 'inputC', '' );
	$params['countries_available']			= array( 'list_country_full', 0 );
	$params['countries_top']				= array( 'list_country_full', 0 );
	$params[] = array( 'section-end' );
	$params[] = array( 'section', 'api' );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_API') );
	$params['apiapplist']					= array( 'inputD', '' );
	$params[] = array( 'section-end' );

	$params[] = array( 'section', 'registration' );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_REGFLOW') );
	$params['show_fixeddecision']			= array( 'toggle', 0 );
	$params['temp_auth_exp']				= array( 'inputC', '' );
	$params['intro_expired']				= array( 'toggle', 0 );
	$params['skip_registration']			= array( 'toggle', 0 );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_CONFIRMATION') );
	$params['confirmation_coupons']			= array( 'toggle', 0 );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_CHECKOUT') );
	$params['checkoutform_jsvalidation']	= array( 'toggle', '' );
	$params['checkout_coupons']				= array( 'toggle', 1 );
	$params['checkout_as_gift']				= array( 'toggle', '' );
	$params['checkout_as_gift_access']		= array( 'list', ( defined( 'JPATH_MANIFESTS' ) ? 2 : 18 ) );
	$params['confirm_as_gift']				= array( 'toggle', '' );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_PLANS') );
	$params['root_group_rw']				= array( 'inputD', 0 );
	$params['entry_plan']					= array( 'list', 0 );
	$params['per_plan_mis']					= array( 'toggle', 0 );
	$params[] = array( 'section-end' );

	$params[] = array( 'section', 'security' );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_SECURITY') );
	$params['ssl_signup']					= array( 'toggle', 0 );
	$params['ssl_profile']					= array( 'toggle', 0 );
	$params['override_reqssl']				= array( 'toggle', 0 );
	$params['altsslurl']					= array( 'inputC', '' );
	$params['ssl_verifypeer']				= array( 'toggle', 0 );
	$params['ssl_verifyhost']				= array( 'inputC', '' );
	$params['ssl_cainfo']					= array( 'inputC', '' );
	$params['ssl_capath']					= array( 'inputC', '' );
	$params['allow_invoice_unpublished_item']				= array( 'toggle', 0 );
	$params[] = array( 'section-end' );
	$params[] = array( 'section', 'debug' );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_DEBUG') );
	$params['bypassintegration']			= array( 'inputC', '' );
	$params['breakon_mi_error']				= array( 'toggle', 0 );
	$params['email_default_admins']			= array( 'toggle', 1 );
	$params['email_extra_admins']			= array( 'inputD', '' );
	$params[] = array( 'section-end' );
	$params[] = array( 'section', 'uninstall' );
	$params[] = array( 'section-head', JText::_('CFG_GENERAL_SUB_UNINSTALL') );
	$params['delete_tables']				= array( 'toggle', 0 );
	$params['delete_tables_sure']			= array( 'toggle', 0 );
	$params[] = array( 'section-end' );

	@end( $params );
	$tab_data[] = array( JText::_('CFG_TAB_EXPERT_TITLE'), key( $params ), '<h2>' . JText::_('CFG_TAB_EXPERT_SUBTITLE') . '</h2>' );

	$error_reporting_notices[] = JHTML::_('select.option', 512, JText::_('AEC_NOTICE_NUMBER_512') );
	$error_reporting_notices[] = JHTML::_('select.option', 128, JText::_('AEC_NOTICE_NUMBER_128') );
	$error_reporting_notices[] = JHTML::_('select.option', 32, JText::_('AEC_NOTICE_NUMBER_32') );
	$error_reporting_notices[] = JHTML::_('select.option', 8, JText::_('AEC_NOTICE_NUMBER_8') );
	$error_reporting_notices[] = JHTML::_('select.option', 2, JText::_('AEC_NOTICE_NUMBER_2') );
	$lists['error_notification_level']			= JHTML::_('select.genericlist', $error_reporting_notices, 'error_notification_level', 'size="5"', 'value', 'text', $aecConfig->cfg['error_notification_level'] );
	$lists['email_notification_level']			= JHTML::_('select.genericlist', $error_reporting_notices, 'email_notification_level', 'size="5"', 'value', 'text', $aecConfig->cfg['email_notification_level'] );

	// Display Processor descriptions?
	if ( !empty( $aecConfig->cfg['gwlist'] ) ) {
		$desc_list = $aecConfig->cfg['gwlist'];
	} else {
		$desc_list = array();
	}

	$pph = new PaymentProcessorHandler();
	$lists['gwlist'] = $pph->getProcessorSelectList( true, $desc_list );

	$grouplist = ItemGroupHandler::getTree();

	$glist = array();

	foreach ( $grouplist as $id => $glisti ) {
		if ( defined( 'JPATH_MANIFESTS' ) ) {
			$glist[] = JHTML::_('select.option', $glisti[0], str_replace( '&nbsp;', ' ', $glisti[1] ) );
		} else {
			$glist[] = JHTML::_('select.option', $glisti[0], $glisti[1] );
		}
	}

	$lists['root_group'] 		= JHTML::_('select.genericlist', $glist, 'root_group', 'size="' . min(6,count($glist)+1) . '"', 'value', 'text', $aecConfig->cfg['root_group'] );

	foreach ( $itemidlist as $idk => $idkp ) {
		if ( empty( $aecConfig->cfg['itemid_' . $idk] ) ) {
			$query = 'SELECT `id`'
					. ' FROM #__menu'
					. ' WHERE ( LOWER( `link` ) = \'index.php?option=com_acctexp&view=' . $idkp['view'] . '\''
					. ' OR LOWER( `link` ) LIKE \'%' . 'layout='. $idkp['view'] . '%\' )'
					. ' AND published = \'1\''
					;
			$db->setQuery( $query );

			$mid = 0;
			if ( empty( $idkp['params'] ) ) {
				$mid = $db->loadResult();
			} else {
				$mids = xJ::getDBArray( $db );

				if ( !empty( $mids ) ) {
					$query = 'SELECT `id`'
							. ' FROM #__menu'
							. ' WHERE `id` IN (' . implode( ',', $mids ) . ')'
							. ' AND `params` LIKE \'%' . $idkp['params'] . '%\''
							. ' AND published = \'1\''
							;
					$db->setQuery( $query );

					$mid = $db->loadResult();
				}
			}

			if ( $mid ) {
				$aecConfig->cfg['itemid_' . $idk] = $mid;
			}
		}
	}

	if ( !empty( $aecConfig->cfg['apiapplist'] ) ) {
		$string = "";

		foreach ( $aecConfig->cfg['apiapplist'] as $app => $key ) {
			$string .= $app . "=" . $key . "\n";
		}

		$aecConfig->cfg['apiapplist'] = $string;
	} else {
		$aecConfig->cfg['apiapplist'] = "";
	}

	$settings = new aecSettings ( 'cfg', 'general' );
	$settings->fullSettingsArray( $params, $aecConfig->cfg, $lists ) ;

	// Call HTML Class
	$aecHTML = new aecHTML( $settings->settings, $settings->lists );
	if ( !empty( $customparamsarray ) ) {
		$aecHTML->customparams = $customparamsarray;
	}

	HTML_AcctExp::Settings( $option, $aecHTML, $params, $tab_data );
}

function saveSettings( $option, $return=0 )
{
	$db		= &JFactory::getDBO();
	$user	= &JFactory::getUser();

	global $aecConfig;

	$app = JFactory::getApplication();

	unset( $_POST['id'] );
	unset( $_POST['task'] );
	unset( $_POST['option'] );

	$general_settings = $_POST;

	if ( !empty( $general_settings['apiapplist'] ) ) {
		$list = explode( "\n", $general_settings['apiapplist'] );

		$array = array();
		foreach ( $list as $item ) {
			$li = explode( "=", $item, 2 );
			
			$k = $li[0];

			if ( !empty( $k ) ) {
				if ( !empty( $li[1] ) ) {
					$v = $li[1];
				} else {
					$v = AECToolbox::randomstring( 32, true, true );
				}

				$array[$k] = $v;
			}
		}

		$general_settings['apiapplist'] = $array;
	} else {
		$general_settings['apiapplist'] = array();
	}

	$diff = $aecConfig->diffParams( $general_settings, 'settings' );
	$difference = '';

	if ( is_array( $diff ) ) {
		$newdiff = array();
		foreach ( $diff as $value => $change ) {
			$newdiff[] = $value . '(' . implode( ' -> ', $change ) . ')';
		}
		$difference = implode( ',', $newdiff );
	} else {
		$difference = 'none';
	}

	$aecConfig->cfg = $general_settings;
	$aecConfig->saveSettings();

	$ip = AECToolbox::aecIP();

	$short	= JText::_('AEC_LOG_SH_SETT_SAVED');
	$event	= JText::_('AEC_LOG_LO_SETT_SAVED') . ' ' . $difference;
	$tags	= 'settings,system';
	$params = array(	'userid' => $user->id,
						'ip' => $ip['ip'],
						'isp' => $ip['isp'] );

	$eventlog = new eventLog();
	$eventlog->issue( $short, $tags, $event, 2, $params );

	if ( !empty( $aecConfig->cfg['entry_plan'] ) ) {
		$plan = new SubscriptionPlan();
		$plan->load( $aecConfig->cfg['entry_plan'] );

		$terms = $plan->getTerms();

		if ( !$terms->checkFree() ) {
			$short	= "Settings Warning";
			$event	= "You have selected a non-free plan as Entry Plan."
						. " Please keep in mind that this means that users"
						. " will be getting it for free when they log in"
						. " without having any membership";
			$tags	= 'settings,system';
			$params = array(	'userid' => $user->id,
								'ip' => $ip['ip'],
								'isp' => $ip['isp'] );

			$eventlog = new eventLog();
			$eventlog->issue( $short, $tags, $event, 32, $params );
		}
	}

	if ( $return ) {
		aecRedirect( 'index.php?option=' . $option . '&task=showSettings', JText::_('AEC_CONFIG_SAVED') );
	} else {
		aecRedirect( 'index.php?option=' . $option . '&task=showCentral', JText::_('AEC_CONFIG_SAVED') );
	}
}

function listTemplates( $option )
{
	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

 	$limit = $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg( 'list_limit' ) );
	$limitstart = $app->getUserStateFromRequest( "viewconf{$option}limitstart", 'limitstart', 0 );

	$list = xJUtility::getFileArray( JPATH_SITE . '/components/com_acctexp/tmpl', '[*]', true );

	foreach ( $list as $id => $name ) {
		if ( ( $name == 'default' ) || ( $name == 'classic' ) ) {
			unset( $list[$id] );
		}
	}

 	$total = count($list);

 	if ( $limitstart > $total ) {
 		$limitstart = 0;
 	}

	$pageNav = new bsPagination( $total, $limitstart, $limit );

	$names = array_slice( $list, $limitstart, $limit );

	$rows = array();
	foreach ( $names as $name ) {
		$t = new configTemplate();
		$t->loadName( $name );

		if ( !$t->id ){
			$t->default = 0;

			if ( $name == 'helix' ) {
				continue;
			}
		}

		$rows[] = $t;
	}

 	HTML_AcctExp::listTemplates( $rows, $pageNav, $option );
}

function editTemplate( $option, $name )
{
	$db = &JFactory::getDBO();

	$temp = new configTemplate();
	$temp->loadName( $name );

	$tempsettings = $temp->template->settings();
	$temp->settings['default'] = $temp->default;

	$lists = array();

	$settings = new aecSettings ( 'cfg', 'general' );
	$settings->fullSettingsArray( $tempsettings['params'], $temp->settings, $lists ) ;

	// Call HTML Class
	$aecHTML = new aecHTML( $settings->settings, $settings->lists );

	$aecHTML->tempname	= $name;
	$aecHTML->name		= $temp->info['longname'];

	HTML_AcctExp::editTemplate( $option, $aecHTML, $tempsettings['tab_data'] );
}

function saveTemplate( $option, $name, $return=0 )
{
	$db = &JFactory::getDBO();

	$temp = new configTemplate();
	$temp->loadName( $name );

	$app = JFactory::getApplication();

	if ( $_POST['default'] ) {
		if ( $temp->id ) {
			if ( !$temp->default ) {
				// Reset all other items
				$query = 'UPDATE #__acctexp_config_templates'
						. ' SET `default` = 0'
						. ' WHERE `id` > 0'
						;
				$db->setQuery( $query );
				$db->query();
			}
		} else {
			// Reset all other items
			$query = 'UPDATE #__acctexp_config_templates'
					. ' SET `default` = 0'
					. ' WHERE `id` > 0'
					;
			$db->setQuery( $query );
			$db->query();
		}
		
		$temp->default = 1;
	} else {
		$temp->default = 0;
	}

	unset( $_POST['id'] );
	unset( $_POST['task'] );
	unset( $_POST['option'] );
	unset( $_POST['name'] );
	unset( $_POST['default'] );

	$temp->template->cfg = $temp->settings;

	$temp->settings = $_POST;

	$temp->storeload();

	if ( $return ) {
		editTemplate( $option, $name );
	} else {
		aecRedirect( 'index.php?option=' . $option . '&task=showTemplates', JText::_('AEC_CONFIG_SAVED') );
	}
}

function listProcessors( $option )
{
 	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

 	$limit = $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg( 'list_limit' ) );
	$limitstart = $app->getUserStateFromRequest( "viewconf{$option}limitstart", 'limitstart', 0 );

 	// get the total number of records
 	$query = 'SELECT count(*)'
		 	. ' FROM #__acctexp_config_processors'
		 	;
 	$db->setQuery( $query );
 	$total = $db->loadResult();

 	if ( $limitstart > $total ) {
 		$limitstart = 0;
 	}

	$pageNav = new bsPagination( $total, $limitstart, $limit );

 	// get the subset (based on limits) of records
 	$query = 'SELECT name'
		 	. ' FROM #__acctexp_config_processors'
		 	. ' GROUP BY `id`'
		 	//. ' ORDER BY `ordering`'
		 	. ' LIMIT ' . $pageNav->limitstart . ',' . $pageNav->limit
		 	;
	$db->setQuery( $query );
	$names = xJ::getDBArray( $db );

	$rows = array();
	foreach ( $names as $name ) {
		$pp = new PaymentProcessor();
		$pp->loadName( $name );

		if ( $pp->fullInit() ) {
			$rows[] = $pp;
		}
	}

 	HTML_AcctExp::listProcessors( $rows, $pageNav, $option );
}

function editProcessor( $id, $option )
{
	$db = &JFactory::getDBO();

	$lang = JFactory::getLanguage();

	if ( $id ) {
		$pp = new PaymentProcessor();

		if ( !$pp->loadId( $id ) ) {
			return false;
		}

		// Init Info and Settings
		$pp->fullInit();

		// Get Backend Settings
		$settings_array		= $pp->getBackendSettings();
		$original_settings	= $pp->processor->settings();

		if ( isset( $settings_array['lists'] ) ) {
			foreach ( $settings_array['lists'] as $lname => $lvalue ) {
				$list_name = $pp->processor_name . '_' . $lname;

				$lists[$list_name] = str_replace( 'name="' . $lname . '"', 'name="' . $list_name . '"', $lvalue );
			}

			unset( $settings_array['lists'] );
		}

		$available_plans = SubscriptionPlanHandler::getActivePlanList();
		$total_plans = count( $available_plans );

		// Iterate through settings form assigning the db settings
		foreach ( $settings_array as $name => $values ) {
			$setting_name = $pp->processor_name . '_' . $name;

			switch( $settings_array[$name][0] ) {
				case 'list_currency':
					// Get currency list
					if ( is_array( $pp->info['currencies'] ) ) {
						$currency_array	= $pp->info['currencies'];
					} else {
						$currency_array	= explode( ',', $pp->info['currencies'] );
					}

					// Transform currencies into OptionArray
					$currency_code_list = array();
					foreach ( $currency_array as $currency ) {
						if ( $lang->hasKey( 'CURRENCY_' . $currency )) {
							$currency_code_list[] = JHTML::_('select.option', $currency, $currency . ' - ' . JText::_( 'CURRENCY_' . $currency ) );							
						}
					}

					$size = min( count($currency_array), 10 );

					// Create list
					$lists[$setting_name] = JHTML::_('select.genericlist', $currency_code_list, $setting_name, 'size="' . $size . '"', 'value', 'text', $pp->settings[$name] );

					$settings_array[$name][0] = 'list';
					break;
				case 'list_language':
					// Get language list
					if ( is_array( $pp->info['languages'] ) ) {
						$language_array	= $pp->info['languages'];
					} else {
						$language_array	= explode( ',', $pp->info['languages'] );
					}

					// Transform languages into OptionArray
					$language_code_list = array();
					foreach ( $language_array as $language ) {
						$language_code_list[] = JHTML::_('select.option', $language, JText::_( 'LANGUAGECODE_' . strtoupper( $language ) ) );
					}
					// Create list
					$lists[$setting_name] = JHTML::_('select.genericlist', $language_code_list, $setting_name, 'size="10"', 'value', 'text', $pp->settings[$name] );
					$settings_array[$name][0] = 'list';
					break;
				case 'list_plan':
					// Create list
					$lists[$setting_name] = JHTML::_('select.genericlist', $available_plans, $setting_name, 'size="10"', 'value', 'text', $pp->settings[$name] );
					$settings_array[$name][0] = 'list';
					break;
				default:
					break;
			}

			if ( !isset( $settings_array[$name][1] ) ) {
				$settings_array[$name][1] = $pp->getParamLang( $name . '_NAME' );
				$settings_array[$name][2] = $pp->getParamLang( $name . '_DESC' );
			}

			// It might be that the processor has got some new properties, so we need to double check here
			if ( isset( $pp->settings[$name] ) ) {
				$content = $pp->settings[$name];
			} elseif ( isset( $original_settings[$name] ) ) {
				$content = $original_settings[$name];
			} else {
				$content = null;
			}

			// Set the settings value
			$settings_array[$setting_name] = array_merge( (array) $settings_array[$name], array( $content ) );

			// unload the original value
			unset( $settings_array[$name] );
		}

		$longname = $pp->processor_name . '_info_longname';
		$description = $pp->processor_name . '_info_description';

		$settingsparams = $pp->settings;

		$params = array();
		$params[$pp->processor_name.'_active'] = array( 'toggle', JText::_('PP_GENERAL_ACTIVE_NAME'), JText::_('PP_GENERAL_ACTIVE_DESC'), $pp->processor->active);

		if ( is_array( $settings_array ) && !empty( $settings_array ) ) {
			$params = array_merge( $params, $settings_array );
		}

		$params[$longname] = array( 'inputC', JText::_('CFG_PROCESSOR_NAME_NAME'), JText::_('CFG_PROCESSOR_NAME_DESC'), $pp->info['longname'], $longname);
		$params[$description] = array( 'editor', JText::_('CFG_PROCESSOR_DESC_NAME'), JText::_('CFG_PROCESSOR_DESC_DESC'), $pp->info['description'], $description);
	} else {
		$pph					= new PaymentProcessorHandler();
		$lists['processor']		= $pph->getSelectList();

		$params['processor']	= array( 'list' );

		$settingsparams = array();

		$pp = null;
	}

	$settings = new aecSettings ( 'pp', 'general' );
	$settings->fullSettingsArray( $params, $settingsparams, $lists ) ;

	// Call HTML Class
	$aecHTML = new aecHTML( $settings->settings, $settings->lists );
	if ( !empty( $customparamsarray ) ) {
		$aecHTML->customparams = $customparamsarray;
	}

	$aecHTML->pp = $pp;

	HTML_AcctExp::editProcessor( $option, $aecHTML );
}

function changeProcessor( $cid=null, $state=0, $type, $option )
{
	$db = &JFactory::getDBO();

	if ( count( $cid ) < 1 ) {
		echo "<script> alert('" . JText::_('AEC_ALERT_SELECT_FIRST') . "'); window.history.go(-1);</script>\n";
		exit;
	}

	$total	= count( $cid );
	$cids	= implode( ',', $cid );

	$query = 'UPDATE #__acctexp_config_processors'
			. ' SET `' . $type . '` = \'' . $state . '\''
			. ' WHERE `id` IN (' . $cids . ')'
			;
	$db->setQuery( $query );

	if ( !$db->query() ) {
		echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if ( $state == '1' ) {
		$msg = ( ( strcmp( $type, 'active' ) === 0 ) ? JText::_('AEC_CMN_PUBLISHED') : JText::_('AEC_CMN_MADE_VISIBLE') );
	} elseif ( $state == '0' ) {
		$msg = ( ( strcmp( $type, 'active' ) === 0 ) ? JText::_('AEC_CMN_NOT_PUBLISHED') : JText::_('AEC_CMN_MADE_INVISIBLE') );
	}

	$msg = sprintf( JText::_('AEC_MSG_ITEMS_SUCESSFULLY'), $total ) . ' ' . $msg;

	aecRedirect( 'index.php?option=' . $option . '&task=showProcessors', $msg );
}

function saveProcessor( $option, $return=0 )
{
	$db = &JFactory::getDBO();

	$pp = new PaymentProcessor();

	if ( !empty( $_POST['id'] ) ) {
		$pp->loadId( $_POST['id'] );

		if ( empty( $pp->id ) ) {
			cancel();
		}

		$procname = $pp->processor_name;
	} elseif ( isset( $_POST['processor'] ) ) {
		$pp->loadName( $_POST['processor'] );

		$procname = $_POST['processor'];
	}

	$pp->fullInit();

	$active			= $procname . '_active';
	$longname		= $procname . '_info_longname';
	$description	= $procname . '_info_description';

	if ( isset( $_POST[$longname] ) ) {
		$pp->info['longname'] = $_POST[$longname];
		unset( $_POST[$longname] );
	}

	if ( isset( $_POST[$description] ) ) {
		$pp->info['description'] = $_POST[$description];
		unset( $_POST[$description] );
	}

	if ( isset( $_POST[$active] ) ) {
		$pp->processor->active = $_POST[$active];
		unset( $_POST[$active] );
	}

	$settings = $pp->getBackendSettings();

	if ( is_int( $pp->is_recurring() ) ) {
		$settings['recurring'] = 2;
	}

	foreach ( $settings as $name => $value ) {
		if ( $name == 'lists' ) {
			continue;
		}

		$postname = $procname  . '_' . $name;

		if ( isset( $_POST[$postname] ) ) {
			$val = $_POST[$postname];

			if ( empty( $val ) ) {
				switch( $name ) {
					case 'currency':
						$val = 'USD';
						break;
					default:
						break;
				}
			}

			$pp->settings[$name] = $_POST[$postname];
			unset( $_POST[$postname] );
		}
	}

	$pp->storeload();

	if ( $return ) {
		aecRedirect( 'index.php?option=' . $option . '&task=editProcessor&id=' . $pp->processor->id, JText::_('AEC_CONFIG_SAVED') );
	} else {
		aecRedirect( 'index.php?option=' . $option . '&task=showProcessors', JText::_('AEC_CONFIG_SAVED') );
	}
}

function getSubscriptionPlans()
{
	$db = &JFactory::getDBO();

	$rows = SubscriptionPlanHandler::getFullPlanList();

	$totals = array();
	$query = 'SELECT count(*)'
			. ' FROM #__acctexp_subscr'
			. ' WHERE (status = \'Active\' OR status = \'Trial\')'
		 	. ( empty( $subselect ) ? '' : ' AND plan IN (' . implode( ',', $subselect ) . ')' )
			;
	$db->setQuery( $query );

 	$totals['active'] = $db->loadResult();
 	if ( $db->getErrorNum() ) {
 		echo $db->stderr();
 		return false;
 	}

 	$query = 'SELECT count(*)'
			. ' FROM #__acctexp_subscr'
			. ' WHERE (status = \'Expired\')'
			. ( empty( $subselect ) ? '' : ' AND plan IN (' . implode( ',', $subselect ) . ')' )
			;
	$db->setQuery( $query );

 	$totals['expired'] = $db->loadResult();
 	if ( $db->getErrorNum() ) {
 		echo $db->stderr();
 		return false;
 	}

	$gcolors = array();

	foreach ( $rows as $n => $row ) {
		$query = 'SELECT count(*)'
				. ' FROM #__acctexp_subscr'
				. ' WHERE plan = ' . $row->id
				. ' AND (status = \'Active\' OR status = \'Trial\')'
				;
		$db->setQuery( $query );

	 	$rows[$n]->usercount = $db->loadResult();
	 	if ( $db->getErrorNum() ) {
	 		echo $db->stderr();
	 		return false;
	 	}

	 	$query = 'SELECT count(*)'
				. ' FROM #__acctexp_subscr'
				. ' WHERE plan = ' . $row->id
				. ' AND (status = \'Expired\')'
				;
		$db->setQuery( $query );

	 	$rows[$n]->expiredcount = $db->loadResult();
	 	if ( $db->getErrorNum() ) {
	 		echo $db->stderr();
	 		return false;
	 	}

	 	$query = 'SELECT group_id'
				. ' FROM #__acctexp_itemxgroup'
				. ' WHERE type = \'item\''
				. ' AND item_id = \'' . $rows[$n]->id . '\''
				;
		$db->setQuery( $query	);
		$g = (int) $db->loadResult();

		$group = empty( $g ) ? 0 : $g;

		if ( !isset( $gcolors[$group] ) ) {
			$gcolors[$group] = array();
			$gcolors[$group]['color'] = ItemGroupHandler::groupColor( $group );
		}

		$rows[$n]->group = $group;
		$rows[$n]->color = $gcolors[$group]['color'];

		$rows[$n]->link = 'index.php?option=com_acctexp&amp;task=showSubscriptions&amp;plan='.$row->id.'&amp;groups[]=all';
		$rows[$n]->link_active = 'index.php?option=com_acctexp&amp;task=showSubscriptions&amp;plan='.$row->id.'&amp;groups[]=active';
		$rows[$n]->link_expired = 'index.php?option=com_acctexp&amp;task=showSubscriptions&amp;plan='.$row->id.'&amp;groups[]=expired';

		if ( $totals['expired'] ) {
			$rows[$n]->expired_percentage = $row->expiredcount / ( $totals['expired'] / 100 );
		} else {
			$rows[$n]->expired_percentage = 0;
		}

		$rows[$n]->expired_inner = false;
		if ( $rows[$n]->expired_percentage > 45 ) {
			$rows[$n]->expired_inner = true;
		}

		$row->activecount = $row->usercount;

		if ( $totals['active'] ) {
			$rows[$n]->active_percentage = $row->usercount / ( $totals['active'] / 100 );
		} else {
			$rows[$n]->active_percentage = 0;
		}

		$rows[$n]->active_inner = false;
		if ( $rows[$n]->active_percentage > 45 ) {
			$rows[$n]->active_inner = true;
		}

		$row->totalcount = $row->expiredcount+$row->usercount;

		if ( $totals['active']+$totals['expired'] ) {
			$rows[$n]->total_percentage = ($row->expiredcount+$row->usercount) / ( ($totals['active']+$totals['expired']) / 100 );
		} else {
			$rows[$n]->total_percentage = 0;
		}

		$rows[$n]->total_inner = false;
		if ( $rows[$n]->total_percentage > 20 ) {
			$rows[$n]->total_inner = true;
		}

		if ( !empty( $row->desc ) ) {
			$rows[$n]->desc = stripslashes( strip_tags( $row->desc ) );
			if ( strlen( $rows[$n]->desc ) > 50 ) {
				$rows[$n]->desc = substr( $rows[$n]->desc, 0, 50) . ' ...';
			}
		}
	}

	$ret = new stdClass();
	$ret->aaData = $rows;

	echo json_encode( $ret );exit;
}

function listSubscriptionPlans( $option )
{
 	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

 	$limit			= $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg( 'list_limit' ) );
	$limitstart		= $app->getUserStateFromRequest( "viewconf{$option}limitstart", 'limitstart', 0 );
	$filter_group	= $app->getUserStateFromRequest( "filter_group", 'filter_group', array() );

	if ( !empty( $filter_group ) ) {
		$subselect = ItemGroupHandler::getChildren( $filter_group, 'item' );
	} else {
		$subselect = array();
	}

 	// get the total number of records
 	$query = 'SELECT count(*)'
		 	. ' FROM #__acctexp_plans'
		 	. ( empty( $subselect ) ? '' : ' WHERE id IN (' . implode( ',', $subselect ) . ')' )
		 	;
 	$db->setQuery( $query );
 	$total = $db->loadResult();

 	if ( $limitstart > $total ) {
 		$limitstart = 0;
 	}

	$pageNav = new bsPagination( $total, $limitstart, $limit );

 	// get the subset (based on limits) of records
	$rows = SubscriptionPlanHandler::getFullPlanList( $pageNav->limitstart, $pageNav->limit, $subselect );

	$gcolors = array();

	foreach ( $rows as $n => $row ) {
		$query = 'SELECT count(*)'
				. ' FROM #__acctexp_subscr'
				. ' WHERE plan = ' . $row->id
				. ' AND (status = \'Active\' OR status = \'Trial\')'
				;
		$db->setQuery( $query );

	 	$rows[$n]->usercount = $db->loadResult();
	 	if ( $db->getErrorNum() ) {
	 		echo $db->stderr();
	 		return false;
	 	}

	 	$query = 'SELECT count(*)'
				. ' FROM #__acctexp_subscr'
				. ' WHERE plan = ' . $row->id
				. ' AND (status = \'Expired\')'
				;
		$db->setQuery( $query );

	 	$rows[$n]->expiredcount = $db->loadResult();
	 	if ( $db->getErrorNum() ) {
	 		echo $db->stderr();
	 		return false;
	 	}

	 	$query = 'SELECT group_id'
				. ' FROM #__acctexp_itemxgroup'
				. ' WHERE type = \'item\''
				. ' AND item_id = \'' . $rows[$n]->id . '\''
				;
		$db->setQuery( $query	);
		$g = (int) $db->loadResult();

		$group = empty( $g ) ? 0 : $g;

		if ( !isset( $gcolors[$group] ) ) {
			$gcolors[$group] = array();
			$gcolors[$group]['color'] = ItemGroupHandler::groupColor( $group );
		}

		$rows[$n]->group = $group;
		$rows[$n]->color = $gcolors[$group]['color'];
	}

	$grouplist = ItemGroupHandler::getTree();

	$glist		= array();
	$sel_groups	= array();

	$glist[] = JHTML::_('select.option', 0, '- - - - - -' );

	if ( empty( $filter_group ) ) {
		$sel_groups[] = JHTML::_('select.option', 0, '- - - - - -' );
	}

	foreach ( $grouplist as $id => $glisti ) {
		if ( defined( 'JPATH_MANIFESTS' ) ) {
			$glist[] = JHTML::_('select.option', $glisti[0], str_replace( '&nbsp;', ' ', $glisti[1] ) );
		} else {
			$glist[] = JHTML::_('select.option', $glisti[0], $glisti[1] );
		}

		if ( !empty( $filter_group ) ) {
			if ( in_array( $glisti[0], $filter_group ) ) {
				$sel_groups[] = JHTML::_('select.option', $glisti[0], $glisti[1] );
			}
		}
	}

	$lists['filter_group'] = JHTML::_('select.genericlist', $glist, 'filter_group[]', 'size="4" multiple="multiple"', 'value', 'text', $sel_groups );

	$totals = array();
	$query = 'SELECT count(*)'
			. ' FROM #__acctexp_subscr'
			. ' WHERE (status = \'Active\' OR status = \'Trial\')'
		 	. ( empty( $subselect ) ? '' : ' AND plan IN (' . implode( ',', $subselect ) . ')' )
			;
	$db->setQuery( $query );

 	$totals['active'] = $db->loadResult();
 	if ( $db->getErrorNum() ) {
 		echo $db->stderr();
 		return false;
 	}

 	$query = 'SELECT count(*)'
			. ' FROM #__acctexp_subscr'
			. ' WHERE (status = \'Expired\')'
			. ( empty( $subselect ) ? '' : ' AND plan IN (' . implode( ',', $subselect ) . ')' )
			;
	$db->setQuery( $query );

 	$totals['expired'] = $db->loadResult();
 	if ( $db->getErrorNum() ) {
 		echo $db->stderr();
 		return false;
 	}

	foreach ( $rows as $rid => $row ) {
		$rows[$rid]->link = 'index.php?option=com_acctexp&amp;task=showSubscriptions&amp;plan='.$row->id.'&amp;groups[]=all';
		$rows[$rid]->link_active = 'index.php?option=com_acctexp&amp;task=showSubscriptions&amp;plan='.$row->id.'&amp;groups[]=active';
		$rows[$rid]->link_expired = 'index.php?option=com_acctexp&amp;task=showSubscriptions&amp;plan='.$row->id.'&amp;groups[]=expired';

		if ( $totals['expired'] ) {
			$rows[$rid]->expired_percentage = $row->expiredcount / ( $totals['expired'] / 100 );
		} else {
			$rows[$rid]->expired_percentage = 0;
		}
		
		$rows[$rid]->expired_inner = false;
		if ( $rows[$rid]->expired_percentage > 45 ) {
			$rows[$rid]->expired_inner = true;
		}

		if ( $totals['active'] ) {
			$rows[$rid]->active_percentage = $row->usercount / ( $totals['active'] / 100 );
		} else {
			$rows[$rid]->active_percentage = 0;
		}

		$rows[$rid]->active_inner = false;
		if ( $rows[$rid]->active_percentage > 45 ) {
			$rows[$rid]->active_inner = true;
		}

		if ( $totals['active']+$totals['expired'] ) {
			$rows[$rid]->total_percentage = ($row->expiredcount+$row->usercount) / ( ($totals['active']+$totals['expired']) / 100 );
		} else {
			$rows[$rid]->total_percentage = 0;
		}
		
		$rows[$rid]->total_inner = false;
		if ( $rows[$rid]->total_percentage > 20 ) {
			$rows[$rid]->total_inner = true;
		}

		if ( !empty( $row->desc ) ) {
			$rows[$rid]->desc = stripslashes( strip_tags( $row->desc ) );
			if ( strlen( $rows[$rid]->desc ) > 50 ) {
				$rows[$rid]->desc = substr( $rows[$rid]->desc, 0, 50) . ' ...';
			}
		}
	}

 	HTML_AcctExp::listSubscriptionPlans( $rows, $lists, $pageNav, $option );
}

function editSubscriptionPlan( $id, $option )
{
	global $aecConfig;

	$db = &JFactory::getDBO();

	$lang = JFactory::getLanguage();

	$lists = array();
	$params_values = array();
	$restrictions_values = array();
	$customparams_values = array();

	$customparamsarray = new stdClass();

	$row = new SubscriptionPlan();
	$row->load( $id );

	$restrictionHelper = new aecRestrictionHelper();

	if ( !$row->id ) {
		$row->ordering	= 9999;
		$hasrecusers	= false;

		$params_values['active']	= 1;
		$params_values['visible']	= 0;
		$params_values['processors'] = 0;

		$restrictions_values['gid_enabled']	= 1;
		if ( defined( 'JPATH_MANIFESTS' ) ) {
			$restrictions_values['gid']			= 2;
		} else {
			$restrictions_values['gid']			= 18;
		}
	} else {
		$params_values = $row->params;
		$restrictions_values = $row->restrictions;

		if ( empty( $restrictions_values ) ) {
			$restrictions_values = array();
		}

		// Clean up custom params
		if ( !empty( $row->customparams ) ) {
			foreach ( $row->customparams as $n => $v ) {
				if ( isset( $params_values[$n] ) || isset( $restrictions_values[$n] ) ) {
					unset( $row->customparams[$n] );
				}
			}
		}

		$customparams_values = $row->custom_params;

		// We need to convert the values that are set as object properties
		$params_values['active']				= $row->active;
		$params_values['visible']				= $row->visible;
		$params_values['email_desc']			= $row->getProperty( 'email_desc' );
		$params_values['name']					= $row->getProperty( 'name' );
		$params_values['desc']					= $row->getProperty( 'desc' );
		$params_values['micro_integrations']	= $row->micro_integrations;
		$params_values['processors']			= $row->params['processors'];

		// Checking if there is already a user, which disables certain actions
		$query  = 'SELECT count(*)'
				. ' FROM #__users AS a'
				. ' LEFT JOIN #__acctexp_subscr AS b ON a.id = b.userid'
				. ' WHERE b.plan = ' . $row->id
				. ' AND (b.status = \'Active\' OR b.status = \'Trial\')'
				. ' AND b.recurring =\'1\''
				;
		$db->setQuery( $query );
		$hasrecusers = ( $db->loadResult() > 0 ) ? true : false;
	}

	$stdformat = '{aecjson}{"cmd":"condition","vars":[{"cmd":"data","vars":"payment.freetrial"},'
				.'{"cmd":"concat","vars":[{"cmd":"jtext","vars":"CONFIRM_FREETRIAL"},"&nbsp;",{"cmd":"data","vars":"payment.method_name"}]},'
				.'{"cmd":"concat","vars":[{"cmd":"data","vars":"payment.amount"},{"cmd":"data","vars":"payment.currency_symbol"},"&nbsp;",{"cmd":"data","vars":"payment.method_name"}]}'
				.']}{/aecjson}'
				;

	// params and their type values
	$params['active']					= array( 'toggle', 1 );
	$params['visible']					= array( 'toggle', 1 );

	$params['name']						= array( 'inputC', '' );
	$params['desc']						= array( 'editor', '' );
	$params['customamountformat']		= array( 'inputD', $stdformat );
	$params['customthanks']				= array( 'inputC', '' );
	$params['customtext_thanks_keeporiginal']	= array( 'toggle', 1 );
	$params['customtext_thanks']		= array( 'editor', '' );
	$params['email_desc']				= array( 'inputD', '' );
	$params['meta']						= array( 'inputD', '' );
	$params['micro_integrations_inherited']		= array( 'list', '' );
	$params['micro_integrations']		= array( 'list', '' );
	$params['micro_integrations_plan']	= array( 'list', '' );

	$params['params_remap']				= array( 'subarea_change', 'groups' );

	$groups = ItemGroupHandler::parentGroups( $row->id, 'item' );

	if ( !empty( $groups ) ) {
		$gs = array();
		foreach ( $groups as $groupid ) {
			$group = new ItemGroup();
			$group->load( $groupid );

			$g = array();
			$g['id']	= $group->id;
			$g['name']	= $group->getProperty('name');
			$g['color']	= $group->params['color'];

			$g['group']	= '<strong>' . $groupid . '</strong>';

			$gs[$groupid] = $g;
		}


		$customparamsarray->groups = $gs;
	} else {
		$customparamsarray->groups = null;
	}

	$grouplist = ItemGroupHandler::getTree();

	$glist = array();

	$glist[] = JHTML::_('select.option', 0, '- - - - - -' );
	$groupids = array();
	foreach ( $grouplist as $id => $glisti ) {
		if ( defined( 'JPATH_MANIFESTS' ) ) {
			$glist[] = JHTML::_('select.option', $glisti[0], str_replace( '&nbsp;', ' ', $glisti[1] ), 'value', 'text', in_array($glisti[0], $groups) );
		} else {
			$glist[] = JHTML::_('select.option', $glisti[0], $glisti[1], 'value', 'text', in_array($glisti[0], $groups) );
		}

		$groupids[$glisti[0]] = ItemGroupHandler::groupColor( $glisti[0] );
	}

	$lists['add_group'] 			= JHTML::_('select.genericlist', $glist, 'add_group', 'size="1"', 'value', 'text', ( ( $row->id ) ? 0 : 1 ) );

	foreach ( $groupids as $groupid => $groupcolor ) {
		$lists['add_group'] = str_replace( 'value="'.$groupid.'"', 'value="'.$groupid.'" style="background-color: #'.$groupcolor.' !important;"', $lists['add_group'] );
	}

	$params['add_group']			= array( 'list', '', '', ( ( $row->id ) ? 0 : 1 ) );

	$params['params_remap']			= array( 'subarea_change', 'params' );

	$params['override_activation']	= array( 'toggle', 0 );
	$params['override_regmail']		= array( 'toggle', 0 );

	$params['full_free']			= array( 'toggle', '' );
	$params['full_amount']			= array( 'inputA', '' );
	$params['full_period']			= array( 'inputA', '' );
	$params['full_periodunit']		= array( 'list', 'D' );
	$params['trial_free']			= array( 'toggle', '' );
	$params['trial_amount']			= array( 'inputA', '' );
	$params['trial_period']			= array( 'inputA', '' );
	$params['trial_periodunit']		= array( 'list', 'D' );

	$params['gid_enabled']			= array( 'toggle', 1 );
	$params['gid']					= array( 'list', ( defined( 'JPATH_MANIFESTS' ) ? 2 : 18 ) );
	$params['lifetime']				= array( 'toggle', 0 );
	$params['processors']			= array( 'list', '' );
	$params['standard_parent']		= array( 'list', '' );
	$params['fallback']				= array( 'list', '' );
	$params['fallback_req_parent']	= array( 'toggle', 0 );
	$params['make_active']			= array( 'toggle', 1 );
	$params['make_primary']			= array( 'toggle', 1 );
	$params['update_existing']		= array( 'toggle', 1 );

	$params['similarplans']			= array( 'list', '' );
	$params['equalplans']			= array( 'list', '' );

	$params['notauth_redirect']		= array( 'inputC', '' );
	$params['fixed_redirect']		= array( 'inputC', '' );
	$params['hide_duration_checkout']	= array( 'toggle', 0 );
	$params['cart_behavior']		= array( 'list', 0 );
	$params['addtocart_redirect']	= array( 'inputC', '' );
	$params['addtocart_max']		= array( 'inputA', '' );
	$params['notes']				= array( 'textarea', '' );

	$params['restr_remap']			= array( 'subarea_change', 'restrictions' );

	$params['inventory_amount_enabled']	= array( 'toggle', 0 );
	$params['inventory_amount']			= array( 'inputB', 0 );
	$params['inventory_amount_used']	= array( 'inputB', 0 );

	$params = array_merge( $params, $restrictionHelper->getParams() );

	$rewriteswitches				= array( 'cms', 'user' );
	$params['rewriteInfo']			= array( 'fieldset', '', AECToolbox::rewriteEngineInfo( $rewriteswitches ) );

	// make the select list for first trial period units
	$perunit[] = JHTML::_('select.option', 'D', JText::_('PAYPLAN_PERUNIT1') );
	$perunit[] = JHTML::_('select.option', 'W', JText::_('PAYPLAN_PERUNIT2') );
	$perunit[] = JHTML::_('select.option', 'M', JText::_('PAYPLAN_PERUNIT3') );
	$perunit[] = JHTML::_('select.option', 'Y', JText::_('PAYPLAN_PERUNIT4') );

	$lists['trial_periodunit'] = JHTML::_('select.genericlist', $perunit, 'trial_periodunit', 'size="4"', 'value', 'text', arrayValueDefault($params_values, 'trial_periodunit', "D") );
	$lists['full_periodunit'] = JHTML::_('select.genericlist', $perunit, 'full_periodunit', 'size="4"', 'value', 'text', arrayValueDefault($params_values, 'full_periodunit', "D") );

	$params['processors_remap'] = array("subarea_change", "plan_params");

	$pps = PaymentProcessorHandler::getInstalledObjectList( 1 );

	if ( empty( $params_values['processors'] ) ) {
		$plan_procs = array();
	} else {
		$plan_procs = $params_values['processors'];
	}

	$firstarray = array();
	$secndarray = array();
	foreach ( $pps as $ppo ) {
		if ( in_array( $ppo->id, $plan_procs ) && !empty( $customparams_values[$ppo->id . '_aec_overwrite_settings'] ) ) {
			$firstarray[] = $ppo;
		} else {
			$secndarray[] = $ppo;
		}
	}

	$pps = array_merge( $firstarray, $secndarray );

	$selected_gw = array();
	$custompar = array();
	foreach ( $pps as $ppobj ) {
		if ( !$ppobj->active ) {
			continue;
		}

		$pp = null;
		$pp = new PaymentProcessor();

		if ( !$pp->loadName( $ppobj->name ) ) {
			continue;
		}

		$pp->init();
		$pp->getInfo();

		$custompar[$pp->id] = array();
		$custompar[$pp->id]['handle'] = $ppobj->name;
		$custompar[$pp->id]['name'] = $pp->info['longname'];
		$custompar[$pp->id]['params'] = array();

		$params['processor_' . $pp->id] = array( 'toggle', JText::_('PAYPLAN_PROCESSORS_ACTIVATE_NAME'), JText::_('PAYPLAN_PROCESSORS_ACTIVATE_DESC')  );
		$custompar[$pp->id]['params'][] = 'processor_' . $pp->id;

		$params[$pp->id . '_aec_overwrite_settings'] = array( 'toggle', JText::_('PAYPLAN_PROCESSORS_OVERWRITE_SETTINGS_NAME'), JText::_('PAYPLAN_PROCESSORS_OVERWRITE_SETTINGS_DESC') );
		$custompar[$pp->id]['params'][] = $pp->id . '_aec_overwrite_settings';

		$customparams = $pp->getCustomPlanParams();

		if ( is_array( $customparams ) ) {
			foreach ( $customparams as $customparam => $cpcontent ) {
				$naming = array( $pp->getParamLang( $customparam . '_NAME' ), $pp->getParamLang( $customparam . '_DESC' ) );

				$shortname = $pp->id . "_" . $customparam;
				$params[$shortname] = array_merge( $cpcontent, $naming );
				$custompar[$pp->id]['params'][] = $shortname;
			}
		}

		if ( empty( $plan_procs ) ) {
			continue;
		}

		if ( !in_array( $pp->id, $plan_procs ) ) {
			continue;
		}

		$params_values['processor_' . $pp->id] = 1;

		if ( isset( $customparams_values[$pp->id . '_aec_overwrite_settings'] ) ) {
			if ( !$customparams_values[$pp->id . '_aec_overwrite_settings'] ) {
				continue;
			}
		} else {
			continue;
		}

		$settings_array = $pp->getBackendSettings();

		if ( isset( $settings_array['lists'] ) ) {
			foreach ( $settings_array['lists'] as $listname => $listcontent ) {
				$lists[$pp->id . '_' . $listname] = $listcontent;
			}

			unset( $settings_array['lists'] );
		}

		// Iterate through settings form to...
		foreach ( $settings_array as $name => $values ) {
			$setting_name = $pp->id . '_' . $name;

			if ( isset( $params[$setting_name] ) ) {
				continue;
			}

			if ( isset( $customparams_values[$setting_name] ) ) {
				$value = $customparams_values[$setting_name];
			} elseif ( isset( $pp->settings[$name] ) ) {
				$value = $pp->settings[$name];
			} else {
				$value = '';
			}

			// ...assign new list fields
			switch( $settings_array[$name][0] ) {
				case 'list_yesno':
					$arr = array(
						JHTML::_('select.option', 0, JText::_( 'no' ) ),
						JHTML::_('select.option', 1, JText::_( 'yes' ) ),
					);

					$lists[$setting_name] = JHTML::_('select.genericlist', $arr, $setting_name, '', 'value', 'text', (int) $value );

					$settings_array[$name][0] = 'list';
					break;

				case 'list_currency':
					// Get currency list
					$currency_array	= explode( ',', $pp->info['currencies'] );

					// Transform currencies into OptionArray
					$currency_code_list = array();
					foreach ( $currency_array as $currency ) {
						if ( $lang->hasKey( 'CURRENCY_' . $currency )) {
							$currency_code_list[] = JHTML::_('select.option', $currency, JText::_( 'CURRENCY_' . $currency ) );
						}
					}

					// Create list
					$lists[$setting_name] = JHTML::_('select.genericlist', $currency_code_list, $setting_name, 'size="10"', 'value', 'text', $value );
					$settings_array[$name][0] = 'list';
					break;

				case 'list_language':
					// Get language list
					if ( !is_array( $pp->info['languages'] ) ) {
						$language_array	= explode( ',', $pp->info['languages'] );
					} else {
						$language_array	= $pp->info['languages'];
					}

					// Transform languages into OptionArray
					$language_code_list = array();
					foreach ( $language_array as $language ) {
						$language_code_list[] = JHTML::_('select.option', $language, ( $lang->hasKey( 'LANGUAGECODE_' . $language  ) ? JText::_( 'LANGUAGECODE_' . $language ) : $language ) );
					}
					// Create list
					$lists[$setting_name] = JHTML::_('select.genericlist', $language_code_list, $setting_name, 'size="10"', 'value', 'text', $value );
					$settings_array[$name][0] = 'list';
					break;

				case 'list_plan':
					unset( $settings_array[$name] );
					break;

				default:
					break;
			}

			// ...put in missing language fields
			if ( !isset( $settings_array[$name][1] ) ) {
				$settings_array[$name][1] = $pp->getParamLang( $name . '_NAME' );
				$settings_array[$name][2] = $pp->getParamLang( $name . '_DESC' );
			}

			$params[$setting_name] = $settings_array[$name];
			$custompar[$pp->id]['params'][] = $setting_name;
		}
	}

	$customparamsarray->pp = $custompar;

	// get available active plans
	$fallback_plans = array( JHTML::_('select.option', '0', JText::_('PAYPLAN_NOFALLBACKPLAN') ) );
	$parent_plans = array( JHTML::_('select.option', '0', JText::_('PAYPLAN_NOPARENTPLAN') ) );

	$query = 'SELECT `id` AS value, `name` AS text'
			. ' FROM #__acctexp_plans'
			. ' WHERE `active` = 1'
			. ' AND `id` != \'' . $row->id . '\'';
			;
	$db->setQuery( $query );
	$payment_plans = $db->loadObjectList();

 	if ( is_array( $payment_plans ) ) {
 		$fallback_plans	= array_merge( $fallback_plans, $payment_plans );
 		$parent_plans	= array_merge( $parent_plans, $payment_plans );
 	}

	$lists['fallback'] = JHTML::_('select.genericlist', $fallback_plans, 'fallback', 'size="1"', 'value', 'text', arrayValueDefault($params_values, 'fallback', 0));
	$lists['standard_parent'] = JHTML::_('select.genericlist', $parent_plans, 'standard_parent', 'size="1"', 'value', 'text', arrayValueDefault($params_values, 'standard_parent', 0));

	// get similar plans
	if ( !empty( $params_values['similarplans'] ) ) {
		$query = 'SELECT `id` AS value, `name` As text'
				. ' FROM #__acctexp_plans'
				. ' WHERE `id` IN (' . implode( ',', $params_values['similarplans'] ) .')'
				;
		$db->setQuery( $query );

	 	$sel_similar_plans = $db->loadObjectList();
	} else {
		$sel_similar_plans = 0;
	}

	$lists['similarplans'] = JHTML::_('select.genericlist', $payment_plans, 'similarplans[]', 'size="1" multiple="multiple"', 'value', 'text', $sel_similar_plans);

	// get equal plans
	if ( !empty( $params_values['equalplans'] ) ) {
		$query = 'SELECT `id` AS value, `name` AS text'
				. ' FROM #__acctexp_plans'
				. ' WHERE `id` IN (' . implode( ',', $params_values['equalplans'] ) .')'
				;
		$db->setQuery( $query );

	 	$sel_equal_plans = $db->loadObjectList();
	} else {
		$sel_equal_plans = 0;
	}

	$lists['equalplans'] = JHTML::_('select.genericlist', $payment_plans, 'equalplans[]', 'size="1" multiple="multiple"', 'value', 'text', $sel_equal_plans);

	$lists = array_merge( $lists, $restrictionHelper->getLists( $params_values, $restrictions_values ) );

	// make the select list for first trial period units
	$cartmode[] = JHTML::_('select.option', '0', JText::_('PAYPLAN_CARTMODE_INHERIT') );
	$cartmode[] = JHTML::_('select.option', '1', JText::_('PAYPLAN_CARTMODE_FORCE_CART') );
	$cartmode[] = JHTML::_('select.option', '2', JText::_('PAYPLAN_CARTMODE_FORCE_DIRECT') );

	$lists['cart_behavior'] = JHTML::_('select.genericlist', $cartmode, 'cart_behavior', 'size="1"', 'value', 'text', arrayValueDefault($params_values, 'cart_behavior', "0") );

	$mi_list = microIntegrationHandler::getDetailedList();

	$mi_settings = array( 'inherited' => array(), 'attached' => array(), 'custom' => array() );

	$attached_mis = $row->getMicroIntegrationsSeparate( true );

	foreach ( $mi_list as $mi_details ) {
		$mi_details->inherited = false;
		if ( in_array( $mi_details->id, $attached_mis['inherited'] ) ) {
			$mi_details->inherited = true;

			$mi_settings['inherited'][] = $mi_details;
		}

		$mi_details->attached = false;
		if ( in_array( $mi_details->id, $attached_mis['plan'] ) ) {
			$mi_details->attached = true;
		}

		$mi_settings['attached'][] = $mi_details;
	}

	$mi_handler = new microIntegrationHandler();
	$mi_list = $mi_handler->getIntegrationList();

	$mi_htmllist = array();
	$mi_htmllist[]	= JHTML::_('select.option', '', JText::_('AEC_CMN_NONE_SELECTED') );

	foreach ( $mi_list as $name ) {
		$mi = new microIntegration();
		$mi->class_name = 'mi_'.$name;
		if ( $mi->callIntegration() ){
			$len = 30 - AECToolbox::visualstrlen( trim( $mi->name ) );
			$fullname = str_replace( '#', '&nbsp;', str_pad( $mi->name, $len, '#' ) ) . ' - ' . substr($mi->desc, 0, 120);
			$mi_htmllist[] = JHTML::_('select.option', $name, $fullname );
		}
	}

	if ( !empty( $row->micro_integrations ) && is_array( $row->micro_integrations ) ) {
		$query = 'SELECT `id`'
				. ' FROM #__acctexp_microintegrations'
				. ' WHERE `id` IN (' . implode( ',', $row->micro_integrations ) . ')'
		 		. ' AND `hidden` = \'1\''
				;
	 	$db->setQuery( $query );
		$hidden_mi = $db->loadObjectList();
	} else {
		$hidden_mi = array();
	}

	$customparamsarray->hasperplanmi = false;

	if ( !empty( $aecConfig->cfg['per_plan_mis'] ) || !empty( $hidden_mi ) ) {
		$customparamsarray->hasperplanmi = true;

		$lists['micro_integrations_plan'] = JHTML::_('select.genericlist', $mi_htmllist, 'micro_integrations_plan[]', 'size="' . min( ( count( $mi_list ) + 1 ), 25 ) . '" multiple="multiple"', 'value', 'text', array() );

		$custompar = array();

		$hidden_mi_list = array();
		if ( !empty( $hidden_mi ) ) {
			foreach ( $hidden_mi as $miobj ) {
				$hidden_mi_list[] = $miobj->id;
			}
		}

		$params['micro_integrations_hidden']		= array( 'hidden', '' );
		$params_values['micro_integrations_hidden']		= $hidden_mi_list;

		if ( !empty( $hidden_mi ) ) {
			foreach ( $hidden_mi as $miobj ) {
				$mi = new microIntegration();

				if ( !$mi->load( $miobj->id ) ) {
					continue;
				}

				if ( !$mi->callIntegration( 1 ) ) {
					continue;
				}

				$custompar[$mi->id] = array();
				$custompar[$mi->id]['name'] = $mi->name;
				$custompar[$mi->id]['params'] = array();

				$prefix = 'MI_' . $mi->id . '_';

				$params[] = array( 'area_change', 'MI' );
				$params[] = array( 'subarea_change', 'E' );
				$params[] = array( 'add_prefix', $prefix );
				$params[] = array( 'userinfobox_sub', JText::_('MI_E_TITLE') );

				$generalsettings = $mi->getGeneralSettings();

				foreach ( $generalsettings as $name => $value ) {
					$params[$prefix . $name] = $value;
					$custompar[$mi->id]['params'][] = $prefix . $name;

					if ( isset( $mi->$name ) ) {
						$params_values[$prefix.$name] = $mi->$name;
					} else {
						$params_values[$prefix.$name] = '';
					}
				}

				$params[]	= array( 'div_end', 0 );

				$misettings = $mi->getSettings();

				if ( isset( $misettings['lists'] ) ) {
					foreach ( $misettings['lists'] as $listname => $listcontent ) {
						$lists[$prefix . $listname] = str_replace( 'name="', 'name="'.$prefix, $listcontent );
					}

					unset( $misettings['lists'] );
				}

				$params[] = array( 'area_change', 'MI' );
				$params[] = array( 'subarea_change', $mi->class_name );
				$params[] = array( 'add_prefix', $prefix );
				$params[] = array( 'userinfobox_sub', JText::_('MI_E_SETTINGS') );

				foreach ( $misettings as $name => $value ) {
					$params[$prefix . $name] = $value;
					$custompar[$mi->id]['params'][] = $prefix . $name;
				}

				$params[]	= array( 'div_end', 0 );
			}
		}

		if ( !empty( $custompar ) ) {
			$mi_settings['custom'] = $custompar;
		}
	}

	$customparamsarray->mi = $mi_settings;

	$settings = new aecSettings ( 'payplan', 'general' );

	if ( is_array( $customparams_values ) ) {
		$settingsparams = array_merge( $params_values, $customparams_values, $restrictions_values );
	} else {
		$settingsparams = array_merge( $params_values, $restrictions_values );
	}

	$settings->fullSettingsArray( $params, $settingsparams, $lists ) ;

	// Call HTML Class
	$aecHTML = new aecHTML( $settings->settings, $settings->lists );

	if ( !empty( $customparamsarray ) ) {
		$aecHTML->customparams = $customparamsarray;
	}

	HTML_AcctExp::editSubscriptionPlan( $option, $aecHTML, $row, $hasrecusers );
}

function saveSubscriptionPlan( $option, $apply=0 )
{
	$db = &JFactory::getDBO();

	$row = new SubscriptionPlan();
	$row->load( $_POST['id'] );

	$post = AECToolbox::cleanPOST( $_POST, false );

	$row->savePOSTsettings( $post );

	$row->storeload();

	if ( $_POST['id'] ) {
		$id = $_POST['id'];
	} else {
		$id = $row->getMax();
	}

	if ( empty( $_POST['id'] ) ) {
		ItemGroupHandler::setChildren( 1, array( $id ), 'item' );
	}

	if ( !empty( $row->params['lifetime'] ) && !empty( $row->params['full_period'] ) ) {
		$short	= "Plan Warning";
		$event	= "You have selected a regular period for a plan that"
					. " already has the 'lifetime' (i.e. 'non expiring') flag set."
					. " The period you have set will be overridden by"
					. " that setting.";
		$tags	= 'settings,plan';
		$params = array();

		$eventlog = new eventLog();
		$eventlog->issue( $short, $tags, $event, 32, $params );
	}

	$terms = $row->getTerms();

	if ( !$terms->checkFree() && empty( $row->params['processors'] ) ) {
		$short	= "Plan Warning";
		$event	= "You have set a plan to be non-free, yet did not select a payment processor."
					. " Without a processor assigned, the plan will not show up on the frontend.";
		$tags	= 'settings,plan';
		$params = array();

		$eventlog = new eventLog();
		$eventlog->issue( $short, $tags, $event, 32, $params );
	}

	if ( !empty( $row->params['lifetime'] ) && !empty( $row->params['processors'] ) ) {
		$fcount	= 0;
		$found	= 0;

		foreach ( $row->params['processors'] as $procid ) {
			$fcount++;

			if ( isset( $row->custom_params[$procid.'_recurring'] ) ) {
				if ( ( 0 < $row->custom_params[$procid.'_recurring'] ) && ( $row->custom_params[$procid.'_recurring'] < 2 ) ) {
					$found++;
				} elseif ( $row->custom_params[$procid.'_recurring'] == 2 ) {
					$fcount++;
				}
			} else {
				$pp = new PaymentProcessor();
				if ( ( 0 < $pp->is_recurring() ) && ( $pp->is_recurring() < 2 ) ) {
					$found++;
				} elseif ( $pp->is_recurring() == 2 ) {
					$fcount++;
				}
			}
		}

		if ( $found ) {
			if ( ( $found < $fcount ) && ( $fcount > 1 ) ) {
				$event	= "You have selected one or more processors that only support recurring payments"
						. ", yet the plan is set to a lifetime period."
						. " This is not possible and the processors will not be displayed as options.";
			} else {
				$event	= "You have selected a processor that only supports recurring payments"
						. ", yet the plan is set to a lifetime period."
						. " This is not possible and the plan will not be displayed.";
			}

			$short	= "Plan Warning";
			$tags	= 'settings,plan';
			$params = array();

			$eventlog = new eventLog();
			$eventlog->issue( $short, $tags, $event, 32, $params );
		}
	}

	if ( $apply ) {
		aecRedirect( 'index.php?option=' . $option . '&task=editSubscriptionPlan&id=' . $id, JText::_('AEC_MSG_SUCESSFULLY_SAVED') );
	} else {
		aecRedirect( 'index.php?option=' . $option . '&task=showSubscriptionPlans', JText::_('SAVED') );
	}
}

function removeSubscriptionPlan( $id, $option )
{
	$db = &JFactory::getDBO();

	$ids = implode( ',', $id );

	$query = 'SELECT count(*)'
			. ' FROM #__acctexp_plans'
			. ' WHERE `id` IN (' . $ids . ')'
			;
	$db->setQuery( $query );
	$total = $db->loadResult();

	if ( $total == 0 ) {
		echo "<script> alert('" . html_entity_decode( JText::_('AEC_MSG_NO_ITEMS_TO_DELETE') ) . "'); window.history.go(-1);</script>\n";
		exit;
	}

	foreach ( $ids as $planid ) {
		if ( SubscriptionPlanHandler::getPlanUserCount( $planid ) > 0 ) {
			$msg = JText::_('AEC_MSG_NO_DEL_W_ACTIVE_SUBSCRIBER');

			aecRedirect( 'index.php?option=' . $option . '&task=showSubscriptionPlans', $msg );
		} else {
			$plan = new SubscriptionPlan();
			$plan->load( $planid );

			$plan->delete();
		}
	}

	$msg = $total . ' ' . JText::_('AEC_MSG_ITEMS_DELETED');

	aecRedirect( 'index.php?option=' . $option . '&task=showSubscriptionPlans', $msg );
}

function changeSubscriptionPlan( $cid=null, $state=0, $type, $option )
{
	$db = &JFactory::getDBO();

	if ( count( $cid ) < 1 ) {
		echo "<script> alert('" . JText::_('AEC_ALERT_SELECT_FIRST') . "'); window.history.go(-1);</script>\n";
		exit;
	}

	$total	= count( $cid );
	$cids	= implode( ',', $cid );

	$query = 'UPDATE #__acctexp_plans'
			. ' SET `' . $type . '` = \'' . $state . '\''
			. ' WHERE `id` IN (' . $cids . ')'
			;
	$db->setQuery( $query );

	if ( !$db->query() ) {
		echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if ( $state == '1' ) {
		$msg = ( ( strcmp( $type, 'active' ) === 0 ) ? JText::_('AEC_CMN_PUBLISHED') : JText::_('AEC_CMN_MADE_VISIBLE') );
	} elseif ( $state == '0' ) {
		$msg = ( ( strcmp( $type, 'active' ) === 0 ) ? JText::_('AEC_CMN_NOT_PUBLISHED') : JText::_('AEC_CMN_MADE_INVISIBLE') );
	}

	$msg = sprintf( JText::_('AEC_MSG_ITEMS_SUCESSFULLY'), $total ) . ' ' . $msg;

	aecRedirect( 'index.php?option=' . $option . '&task=showSubscriptionPlans', $msg );
}

function listItemGroups( $option )
{
 	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

 	$limit		= $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg( 'list_limit' ) );
	$limitstart = $app->getUserStateFromRequest( "viewconf{$option}limitstart", 'limitstart', 0 );

 	// get the total number of records
 	$query = 'SELECT count(*)'
		 	. ' FROM #__acctexp_itemgroups'
		 	;
 	$db->setQuery( $query );
 	$total = $db->loadResult();
 	echo $db->getErrorMsg();

 	if ( $limitstart > $total ) {
 		$limitstart = 0;
 	}

	$pageNav = new bsPagination( $total, $limitstart, $limit );

 	// get the subset (based on limits) of records
 	$query = 'SELECT *'
		 	. ' FROM #__acctexp_itemgroups'
		 	. ' GROUP BY `id`'
		 	. ' ORDER BY `ordering`'
		 	. ' LIMIT ' . $pageNav->limitstart . ',' . $pageNav->limit
		 	;
	$db->setQuery( $query );

 	$rows = $db->loadObjectList();
 	if ( $db->getErrorNum() ) {
 		echo $db->stderr();
 		return false;
 	}

	$gcolors = array();

	foreach ( $rows as $rid => $row ) {
		$query = 'SELECT count(*)'
				. 'FROM #__users AS a'
				. ' LEFT JOIN #__acctexp_subscr AS b ON a.id = b.userid'
				. ' WHERE b.plan = ' . $row->id
				. ' AND (b.status = \'Active\' OR b.status = \'Trial\')'
				;
		$db->setQuery( $query	);

	 	$rows[$rid]->usercount = $db->loadResult();
	 	if ( $db->getErrorNum() ) {
	 		echo $db->stderr();
	 		return false;
	 	}

	 	$query = 'SELECT count(*)'
				. ' FROM #__users AS a'
				. ' LEFT JOIN #__acctexp_subscr AS b ON a.id = b.userid'
				. ' WHERE b.plan = ' . $row->id
				. ' AND (b.status = \'Expired\')'
				;
		$db->setQuery( $query	);

	 	$rows[$rid]->expiredcount = $db->loadResult();
	 	if ( $db->getErrorNum() ) {
	 		echo $db->stderr();
	 		return false;
	 	}

		$gid = $rows[$rid]->id;

		if ( !isset( $gcolors[$gid] ) ) {
			$gcolors[$gid] = array();
			$gcolors[$gid]['color'] = ItemGroupHandler::groupColor( $gid );
		}

		$rows[$rid]->color = $gcolors[$gid]['color'];

		$parents = ItemGroupHandler::getParents( $gid, 'group' );

		if ( !empty( $parents ) ) {
			$parent_group = $parents[0];

			if ( !isset( $gcolors[$parent_group] ) ) {
				$gcolors[$parent_group] = array();
				$gcolors[$parent_group]['color'] = ItemGroupHandler::groupColor( $parent_group );
			}

			$rows[$rid]->parent_group = $parent_group;
			$rows[$rid]->parent_color = $gcolors[$parent_group]['color'];
		} else {
			$rows[$rid]->parent_group = $gid;
			$rows[$rid]->parent_color = $gcolors[$gid]['color'];
		}

		if ( !empty( $row->desc ) ) {
			$rows[$rid]->desc = stripslashes( strip_tags( $row->desc ) );
			if ( strlen( $rows[$rid]->desc ) > 50 ) {
				$rows[$rid]->desc = substr( $rows[$rid]->desc, 0, 50) . ' ...';
			}
		}
	}

 	HTML_AcctExp::listItemGroups( $rows, $pageNav, $option );
 }

function editItemGroup( $id, $option )
{
	$db = &JFactory::getDBO();

	$lists = array();
	$params_values = array();
	$restrictions_values = array();
	$customparams_values = array();

	$row = new ItemGroup();
	$row->load( $id );

	$restrictionHelper = new aecRestrictionHelper();

	if ( !$row->id ) {
		$row->ordering	= 9999;

		$params_values['active']	= 1;
		$params_values['visible']	= 0;

		$restrictions_values['gid_enabled']	= 1;
		$restrictions_values['gid']			= 18;
	} else {
		$params_values = $row->params;
		$restrictions_values = $row->restrictions;
		$customparams_values = $row->custom_params;

		// We need to convert the values that are set as object properties
		$params_values['active']				= $row->active;
		$params_values['visible']				= $row->visible;
		$params_values['name']					= $row->getProperty( 'name' );
		$params_values['desc']					= $row->getProperty( 'desc' );
	}

	// params and their type values
	$params['active']					= array( 'toggle', 1 );
	$params['visible']					= array( 'toggle', 0 );

	$params['name']						= array( 'inputC', '' );
	$params['desc']						= array( 'editor', '' );

	$params['color']					= array( 'list', '' );

	$params['reveal_child_items']		= array( 'toggle', 0 );
	$params['symlink']					= array( 'inputC', '' );
	$params['symlink_userid']			= array( 'toggle', 0 );

	$params['notauth_redirect']			= array( 'inputD', '' );

	$params['micro_integrations']		= array( 'list', '' );
	$params['meta']						= array( 'inputD', '' );

	$params['params_remap']				= array( 'subarea_change', 'groups' );

	$groups = ItemGroupHandler::parentGroups( $row->id, 'group' );

	if ( !empty( $groups ) ) {
		$gs = array();
		foreach ( $groups as $groupid ) {
			$params['group_delete_'.$groupid] = array( 'checkbox', '', '', '' );

			$group = new ItemGroup();
			$group->load( $groupid );

			$g = array();
			$g['id']	= $group->id;
			$g['name']	= $group->getProperty('name');
			$g['color']	= $group->params['color'];

			$g['group']	= '<strong>' . $groupid . '</strong>';

			$gs[$groupid] = $g;
		}


		$customparamsarray->groups = $gs;
	} else {
		$customparamsarray->groups = null;
	}

	$grouplist = ItemGroupHandler::getTree();

	$glist = array();

	$glist[] = JHTML::_('select.option', 0, '- - - - - -' );
	$groupids = array();
	foreach ( $grouplist as $gid => $glisti ) {
		$children = ItemGroupHandler::getParents( $glisti[0], 'group' );

		$disabled = in_array( $id, $children );

		if ( $id ) {
			$self = ( $glisti[0] == $id );
			$existing = in_array( $glisti[0], $groups );
			
			$disabled = ( $disabled || $self || $existing );
		}

		if ( defined( 'JPATH_MANIFESTS' ) ) {
			$glist[] = JHTML::_('select.option', $glisti[0], str_replace( '&nbsp;', ' ', $glisti[1] ), 'value', 'text', $disabled );
		} else {
			$glist[] = JHTML::_('select.option', $glisti[0], $glisti[1], 'value', 'text', $disabled );
		}

		$groupids[$glisti[0]] = ItemGroupHandler::groupColor( $glisti[0] );
	}

	$lists['add_group'] 			= JHTML::_('select.genericlist', $glist, 'add_group', 'size="1"', 'value', 'text', ( ( $row->id ) ? 0 : 1 ) );

	foreach ( $groupids as $groupid => $groupcolor ) {
		$lists['add_group'] = str_replace( 'value="'.$groupid.'"', 'value="'.$groupid.'" style="background-color: #'.$groupcolor.' !important;"', $lists['add_group'] );
	}

	$params['add_group']	= array( 'list', '', '', ( ( $row->id ) ? 0 : 1 ) );

	$params['restr_remap']	= array( 'subarea_change', 'restrictions' );

	$params = array_merge( $params, $restrictionHelper->getParams() );

	$rewriteswitches		= array( 'cms', 'user' );
	$params['rewriteInfo']	= array( 'fieldset', '', AECToolbox::rewriteEngineInfo( $rewriteswitches ) );

	$colors = array(	'3182bd', '6baed6', '9ecae1', 'c6dbef', 'e6550d', 'fd8d3c', 'fdae6b', 'fdd0a2',
						'31a354', '74c476', 'a1d99b', 'c7e9c0', '756bb1', '9e9ac8', 'bcbddc', 'dadaeb',
						'636363', '969696', 'bdbdbd', 'd9d9d9',
						'1f77b4', 'aec7e8', 'ff7f0e', 'ffbb78', '2ca02c', '98df8a', 'd62728', 'ff9896',
						'9467bd', 'c5b0d5', '8c564b', 'c49c94', 'e377c2', 'f7b6d2', '7f7f7f', 'c7c7c7',
						'bcbd22', 'dbdb8d', '17becf', '9edae5', 'BBDDFF', '5F8BC4', 'A2BE72', 'DDFF99',
						'D07C30', 'C43C42', 'AA89BB', 'B7B7B7' );

	$colorlist = array();
	foreach ( $colors as $color ) {
		$obj = new stdClass;
		$obj->value = '#'.$color;
		$obj->text = $color;

		$colorlist[] = $obj;
	}

	$lists['color'] = JHTML::_('select.genericlist', $colorlist, 'color', 'size="1"', 'value', 'text', '#'.arrayValueDefault($params_values, 'color', 'BBDDFF'));

	$mi_list = microIntegrationHandler::getDetailedList();

	$mi_settings = array( 'inherited' => array(), 'attached' => array(), 'custom' => array() );

	$attached_mis = $row->getMicroIntegrationsSeparate( true );

	foreach ( $mi_list as $mi_details ) {
		$mi_details->inherited = false;
		if ( in_array( $mi_details->id, $attached_mis['inherited'] ) ) {
			$mi_details->inherited = true;

			$mi_settings['inherited'][] = $mi_details;
		}

		$mi_details->attached = false;
		if ( in_array( $mi_details->id, $attached_mis['group'] ) ) {
			$mi_details->attached = true;
		}

		$mi_settings['attached'][] = $mi_details;
	}

	$customparamsarray->mi = $mi_settings;

	$settings = new aecSettings ( 'itemgroup', 'general' );
	if ( is_array( $customparams_values ) ) {
		$settingsparams = array_merge( $params_values, $customparams_values, $restrictions_values );
	} elseif( is_array( $restrictions_values ) ){
		$settingsparams = array_merge( $params_values, $restrictions_values );
	}
	else {
		$settingsparams = $params_values;
	}

	$lists = array_merge( $lists, $restrictionHelper->getLists( $params_values, $restrictions_values ) );

	$settings->fullSettingsArray( $params, $settingsparams, $lists ) ;

	// Call HTML Class
	$aecHTML = new aecHTML( $settings->settings, $settings->lists );
	if ( !empty( $customparamsarray ) ) {
		$aecHTML->customparams = $customparamsarray;
	}

	HTML_AcctExp::editItemGroup( $option, $aecHTML, $row );
}

function saveItemGroup( $option, $apply=0 )
{
	$db = &JFactory::getDBO();

	$row = new ItemGroup();
	$row->load( $_POST['id'] );

	$post = AECToolbox::cleanPOST( $_POST, false );

	$row->savePOSTsettings( $post );

	if ( !$row->check() ) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-2); </script>\n";
		exit();
	}
	if ( !$row->store() ) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-2); </script>\n";
		exit();
	}

	$row->reorder();

	if ( $_POST['id'] ) {
		$id = $_POST['id'];
	} else {
		$id = $row->getMax();
	}

	if ( empty( $_POST['id'] ) ) {
		ItemGroupHandler::setChildren( 1, array( $id ), 'group' );
	}

	if ( $apply ) {
		aecRedirect( 'index.php?option=' . $option . '&task=editItemGroup&id=' . $id, JText::_('AEC_MSG_SUCESSFULLY_SAVED') );
	} else {
		aecRedirect( 'index.php?option=' . $option . '&task=showItemGroups', JText::_('SAVED') );
	}
}

function removeItemGroup( $id, $option )
{
	$db = &JFactory::getDBO();

	$ids = implode( ',', $id );

	$query = 'SELECT count(*)'
			. ' FROM #__acctexp_itemgroups'
			. ' WHERE `id` IN (' . $ids . ')'
			;
	$db->setQuery( $query );
	$total = $db->loadResult();

	if ( $total == 0 ) {
		echo "<script> alert('" . html_entity_decode( JText::_('AEC_MSG_NO_ITEMS_TO_DELETE') ) . "'); window.history.go(-1);</script>\n";
		exit;
	}

	$total = 0;

	foreach ( $id as $i ) {
		$ig = new ItemGroup();
		$ig->load( $i );

		if ( $ig->delete() !== false ) {
			ItemGroupHandler::removeChildren( $i, false, 'group' );

			$total++;
		}
	}

	if ( $total == 0 ) {
		echo "<script> alert('" . html_entity_decode( JText::_('AEC_MSG_NO_ITEMS_TO_DELETE') ) . "'); window.history.go(-1);</script>\n";
		exit;
	} else {
		$msg = $total . ' ' . JText::_('AEC_MSG_ITEMS_DELETED');

		aecRedirect( 'index.php?option=' . $option . '&task=showItemGroups', $msg );
	}
}

function changeItemGroup( $cid=null, $state=0, $type, $option )
{
	$db = &JFactory::getDBO();

	if ( count( $cid ) < 1 ) {
		echo "<script> alert('" . JText::_('AEC_ALERT_SELECT_FIRST') . "'); window.history.go(-1);</script>\n";
		exit;
	}

	$total	= count( $cid );
	$cids	= implode( ',', $cid );

	$query = 'UPDATE #__acctexp_itemgroups'
			. ' SET `' . $type . '` = \'' . $state . '\''
			. ' WHERE `id` IN (' . $cids . ')'
			;
	$db->setQuery( $query );

	if ( !$db->query() ) {
		echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if ( $state == '1' ) {
		$msg = ( ( strcmp( $type, 'active' ) === 0 ) ? JText::_('AEC_CMN_PUBLISHED') : JText::_('AEC_CMN_MADE_VISIBLE') );
	} elseif ( $state == '0' ) {
		$msg = ( ( strcmp( $type, 'active' ) === 0 ) ? JText::_('AEC_CMN_NOT_PUBLISHED') : JText::_('AEC_CMN_MADE_INVISIBLE') );
	}

	$msg = sprintf( JText::_('AEC_MSG_ITEMS_SUCESSFULLY'), $total ) . ' ' . $msg;

	aecRedirect( 'index.php?option=' . $option . '&task=showItemGroups', $msg );
}

function listMicroIntegrations( $option )
{
	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

	$limit		= $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg( 'list_limit' ) );
	$limitstart	= $app->getUserStateFromRequest( "viewconf{$option}limitstart", 'limitstart', 0 );

	$orderby		= $app->getUserStateFromRequest( "orderby_mi{$option}", 'orderby_mi', 'ordering ASC' );
	$search			= $app->getUserStateFromRequest( "search{$option}", 'search', '' );
	$search			= xJ::escape( $db, trim( strtolower( $search ) ) );

	$filter_planid	= intval( $app->getUserStateFromRequest( "filter_planid{$option}", 'filter_planid', 0 ) );

	$ordering = false;

	if ( strpos( $orderby, 'ordering' ) !== false ) {
		$ordering = true;
	}

	// get the total number of records
	$query = 'SELECT count(*)'
		 	. ' FROM #__acctexp_microintegrations'
		 	. ' WHERE `hidden` = \'0\''
		 	;
	$db->setQuery( $query );
	$total = $db->loadResult();
	echo $db->getErrorMsg();

	if ( $limitstart > $total ) {
		$limitstart = 0;
	}

	$pageNav = new bsPagination( $total, $limitstart, $limit );

	$where = array();
	$where[] = '`hidden` = \'0\'';

	if ( isset( $search ) && $search!= '' ) {
		$where[] = "(name LIKE '%$search%' OR class_name LIKE '%$search%')";
	}

	if ( isset( $filter_planid ) && $filter_planid > 0 ) {
		$mis = microIntegrationHandler::getMIsbyPlan( $filter_planid );

		if ( !empty( $mis ) ) {
			$where[] = "(id IN (" . implode( ',', $mis ) . "))";
		} else {
			$filter_planid = "";
		}
	}

	// get the subset (based on limits) of required records
	$query = 'SELECT * FROM #__acctexp_microintegrations';

	$query .= (count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

	$query .= ' ORDER BY ' . $orderby;
	$query .= ' LIMIT ' . $pageNav->limitstart . ',' . $pageNav->limit;

	$db->setQuery( $query );

	$rows = $db->loadObjectList();
	if ( $db->getErrorNum() ) {
		echo $db->stderr();
		return false;
	}

	foreach ( $rows as $rid => $row ) {
		if ( !empty( $row->desc ) ) {
			$rows[$rid]->desc = stripslashes( strip_tags( $row->desc ) );
			if ( strlen( $rows[$rid]->desc ) > 50 ) {
				$rows[$rid]->desc = substr( $rows[$rid]->desc, 0, 50) . ' ...';
			}
		}
	}

	$sel = array();
	$sel[] = JHTML::_('select.option', 'ordering ASC',		JText::_('ORDERING_ASC') );
	$sel[] = JHTML::_('select.option', 'ordering DESC',		JText::_('ORDERING_DESC') );
	$sel[] = JHTML::_('select.option', 'id ASC',			JText::_('ID_ASC') );
	$sel[] = JHTML::_('select.option', 'id DESC',			JText::_('ID_DESC') );
	$sel[] = JHTML::_('select.option', 'name ASC',			JText::_('NAME_ASC') );
	$sel[] = JHTML::_('select.option', 'name DESC',			JText::_('NAME_DESC') );
	$sel[] = JHTML::_('select.option', 'class_name ASC',	JText::_('CLASSNAME_ASC') );
	$sel[] = JHTML::_('select.option', 'class_name DESC',	JText::_('CLASSNAME_DESC') );

	$lists['orderNav'] = JHTML::_('select.genericlist', $sel, 'orderby_mi', 'class="inputbox span2" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $orderby );

	// Get list of plans for filter
	$query = 'SELECT `id`, `name`'
			. ' FROM #__acctexp_plans'
			. ' ORDER BY `ordering`'
			;
	$db->setQuery( $query );
	$db_plans = $db->loadObjectList();

	$plans[] = JHTML::_('select.option', '0', JText::_('FILTER_PLAN'), 'id', 'name' );
	if ( is_array( $db_plans ) ) {
		$plans = array_merge( $plans, $db_plans );
	}
	$lists['filterplanid']	= JHTML::_('select.genericlist', $plans, 'filter_planid', 'class="inputbox span2" size="1" onchange="document.adminForm.submit();"', 'id', 'name', $filter_planid );

	HTML_AcctExp::listMicroIntegrations( $rows, $pageNav, $option, $lists, $search, $ordering );
}

function editMicroIntegration( $id, $option )
{
	$db = &JFactory::getDBO();

	$user = &JFactory::getUser();

	$lists	= array();
	$mi		= new microIntegration();
	$mi->load( $id );

	$aecHTML = null;
	$attached = array();

	$mi_gsettings = $mi->getGeneralSettings();

	if ( !$mi->id ) {
		$lang = JFactory::getLanguage();

		// Create MI Selection List
		$mi_handler = new microIntegrationHandler();
		$mi_list = $mi_handler->getIntegrationList();

		$drilldown = array( 'all' => array() );

		$mi_gsettings['class_name']		= array( 'hidden' );
		$mi_gsettings['class_list']		= array( 'list' );

		if ( count( $mi_list ) > 0 ) {
			foreach ( $mi_list as $name ) {
				$mi_item = new microIntegration();

				if ( $mi_item->callDry( $name ) ) {
					$handle = str_replace( 'mi_', '', $mi_item->class_name );

					if ( isset( $mi_item->info['type'] ) ) {
						foreach ( $mi_item->info['type'] as $type ) {
							$drill = explode( '.', $type );

							$cursor =& $drilldown;

							$mi_item->name = str_replace( array(' AEC ', ' MI '), ' ', $mi_item->name );

							foreach ( $drill as $i => $k ) {
								if ( !isset( $cursor[$k] ) ) {
									$cursor[$k] = array();
								}

								if ( $i == count( $drill )-1 ) {
									$cursor[$k][] = '<a href="#' . $handle . '" class="mi-menu-mi"><span class="mi-menu-mi-name">' . $mi_item->name . '</span><span class="mi-menu-mi-desc">' . $mi_item->desc . '</span></a>';
								} else {
									$cursor =& $cursor[$k]; 
								}
							}
						}
					}

					$drilldown['all'][] = '<a href="#' . $handle . '" class="mi-menu-mi"><span class="mi-menu-mi-name">' . $mi_item->name . '</span><span class="mi-menu-mi-desc">' . $mi_item->desc . '</span></a>';
				}
			}

			deep_ksort( $drilldown );

			$lists['class_list'] = '<a tabindex="0" href="#mi-select-list" class="btn btn-primary" id="drilldown">Select an Integration</a>';

			$lists['class_list'] .= '<div id="mi-select-list" class="hidden"><ul>';
			foreach ( $drilldown as $lin => $li ) {
				if ( $lang->hasKey( 'AEC_MI_LIST_' . strtoupper( $lin ) ) ) {
					$kkey = JText::_('AEC_MI_LIST_' . strtoupper( $lin ) );
				} else {
					$kkey = ucwords( str_replace('_', ' ', $lin) );
				}

				$lists['class_list'] .= '<li><a href="#">' . $kkey . '</a><ul>';

				foreach ( $li as $lixn => $lix ) {
					if ( is_array( $lix ) ) {
						if ( $lang->hasKey( 'AEC_MI_LIST_' . strtoupper( $lixn ) ) ) {
							$xkey = JText::_('AEC_MI_LIST_' . strtoupper( $lixn ) );
						} else {
							$xkey = ucwords( str_replace('_', ' ', $lixn) );
						}
	
						$lists['class_list'] .= '<li><a href="#">' . $xkey . '</a><ul>';

						foreach ( $lix as $mix ) {
							$lists['class_list'] .= '<li>' . $mix . '</li>';
						}

						$lists['class_list'] .= '</ul></li>';
					} else {
						$lists['class_list'] .= '<li>' . $lix . '</li>';
					}
				}

				$lists['class_list'] .= '</ul></li>';
			}
			$lists['class_list'] .= '</ul></div>';

		} else {
			$lists['class_list'] = '';
		}
	}

	if ( $mi->id ) {
		// Call MI (override active check) and Settings
		if ( $mi->callIntegration( true ) ) {
			$attached['plans'] = microIntegrationHandler::getPlansbyMI( $mi->id, false, true );
			$attached['groups'] = microIntegrationHandler::getGroupsbyMI( $mi->id, false, true );

			$set = array();
			foreach ( $mi_gsettings as $n => $v ) {
				if ( !isset( $mi->$n ) ) {
					if (  isset( $mi->settings[$n] ) ) {
						$set[$n] = $mi->settings[$n];
					} else {
						$set[$n] = null;
					}
				} else {
					$set[$n] = $mi->$n;
				}
			}

			$restrictionHelper = new aecRestrictionHelper();

			$mi_gsettings['restr_remaps']	= array( 'subarea_change', 'restrictions' );

			$mi_gsettings = array_merge( $mi_gsettings, $restrictionHelper->getParams() );

			if ( empty( $mi->restrictions ) ) {
				$mi->restrictions = array();
			}

			$lists = array_merge( $lists, $restrictionHelper->getLists( $set, $mi->restrictions ) );

			$mi_gsettings[$mi->id.'remap']	= array( 'area_change', 'MI' );
			$mi_gsettings[$mi->id.'remaps']	= array( 'subarea_change', $mi->class_name );

			$mi_settings = $mi->getSettings();

			// Get lists supplied by the MI
			if ( !empty( $mi_settings['lists'] ) ) {
				$lists = array_merge( $lists, $mi_settings['lists'] );
				unset( $mi_settings['lists'] );
			}

			$available_plans = SubscriptionPlanHandler::getPlanList( false, false, true, null, true );

			$selected_plans = array();
			foreach ( $attached['plans'] as $p ) {
				$selected_plans[] = (object) array( 'value' => $p->id, 'text' => $p->name );
			}

			$lists['attach_to_plans'] = JHTML::_('select.genericlist', $available_plans, 'attach_to_plans[]', 'size="1" multiple="multiple"', 'value', 'text', $selected_plans );

			$available_groups = ItemGroupHandler::getGroups( null, true );

			$selected_groups = array();
			foreach ( $attached['groups'] as $g ) {
				$selected_groups[] = (object) array( 'value' => $g->id, 'text' => $g->name );
			}

			$lists['attach_to_groups'] = JHTML::_('select.genericlist', $available_groups, 'attach_to_groups[]', 'size="1" multiple="multiple"', 'value', 'text', $selected_groups );

			$gsettings = new aecSettings( 'MI', 'E' );
			$gsettings->fullSettingsArray( $mi_gsettings, array_merge( $set, $mi->restrictions ), $lists );

			$settings = new aecSettings( 'MI', $mi->class_name );
			$settings->fullSettingsArray( $mi_settings, $set, $lists );

			// Call HTML Class
			$aecHTML = new aecHTML( array_merge( $gsettings->settings, $settings->settings ), array_merge( $gsettings->lists, $settings->lists ) );

			$aecHTML->hasHacks = method_exists( $mi->mi_class, 'hacks' );

			$aecHTML->customparams = array();
			foreach ( $mi_settings as $n => $v ) {
				$aecHTML->customparams[] = $n;
			}

			$aecHTML->hasSettings = true;

			$aecHTML->hasRestrictions = !empty( $mi->settings['has_restrictions'] );
		} else {
			$short	= 'microIntegration loading failure';
			$event	= 'When trying to load microIntegration: ' . $mi->id . ', callIntegration failed';
			$tags	= 'microintegration,loading,error';
			$params = array();

			$eventlog = new eventLog();
			$eventlog->issue( $short, $tags, $event, 128, $params );
		}
	} else {
		$settings = new aecSettings( 'MI', 'E' );
		$settings->fullSettingsArray( $mi_gsettings, array(), $lists );

		// Call HTML Class
		$aecHTML = new aecHTML( $settings->settings, $settings->lists );

		$aecHTML->hasSettings = false;

		$aecHTML->hasRestrictions = false;

		$available_plans = SubscriptionPlanHandler::getPlanList( false, false, true, null, true );
		$lists['attach_to_plans'] = JHTML::_('select.genericlist', $available_plans, 'attach_to_plans[]', 'size="1" multiple="multiple"', 'value', 'text', null );

		$available_groups = ItemGroupHandler::getGroups( null, true );
		$lists['attach_to_groups'] = JHTML::_('select.genericlist', $available_groups, 'attach_to_groups[]', 'size="1" multiple="multiple"', 'value', 'text', null );
	}

	HTML_AcctExp::editMicroIntegration( $option, $mi, $lists, $aecHTML, $attached );
}

function saveMicroIntegration( $option, $apply=0 )
{
	$db = &JFactory::getDBO();

	unset( $_POST['option'] );
	unset( $_POST['task'] );

	$id = $_POST['id'] ? $_POST['id'] : 0;

	$mi = new microIntegration();
	$mi->load( $id );

	if ( !empty( $_POST['class_name'] ) ) {
		$load = $mi->callDry( $_POST['class_name'] );
	} else {
		$load = $mi->callIntegration( 1 );
	}

	if ( $load ) {
		$save = array( 'attach_to_plans' => array(), 'attached_to_plans' => array(), 'attach_to_groups' => array(), 'attached_to_groups' => array() );
		
		foreach ( $save as $pid => $v ) {
			if ( isset( $_POST[$pid] ) ) {
				$save[$pid] = $_POST[$pid];

				unset( $_POST[$pid] );
			} else {
				$save[$pid] = array();
			}
		}

		$group_attach = array();
		if ( isset( $_POST['attach_to_groups'] ) ) {
			$group_attach = $_POST['attach_to_groups'];

			unset( $_POST['attach_to_groups'] );
		}

		$mi->savePostParams( $_POST );

		$mi->storeload();

		$all_groups = array_unique( array_merge( $save['attach_to_groups'], $save['attached_to_groups'] ) );

		if ( !empty( $all_groups ) ) {
			foreach ( $all_groups as $groupid ) {
				$group = new ItemGroup();
				$group->load( $groupid );

				if ( in_array( $groupid, $save['attach_to_groups'] ) && !in_array( $groupid, $save['attached_to_groups'] ) ) {
					$group->params['micro_integrations'][] = $mi->id;

					$group->storeload();
				} elseif ( !in_array( $groupid, $save['attach_to_groups'] ) && in_array( $groupid, $save['attached_to_groups'] ) ) {
					unset( $group->params['micro_integrations'][array_search( $mi->id, $group->params['micro_integrations'] )] );

					$group->storeload();
				}
			}
		}

		$all_plans = array_unique( array_merge( $save['attach_to_plans'], $save['attached_to_plans'] ) );

		if ( !empty( $all_plans ) ) {
			foreach ( $all_plans as $planid ) {
				$plan = new SubscriptionPlan();
				$plan->load( $planid );

				if ( in_array( $planid, $save['attach_to_plans'] ) && !in_array( $planid, $save['attached_to_plans'] ) ) {
					$plan->micro_integrations[] = $mi->id;

					$plan->storeload();
				} elseif ( !in_array( $planid, $save['attach_to_plans'] ) && in_array( $planid, $save['attached_to_plans'] ) ) {
					unset( $plan->micro_integrations[array_search( $mi->id, $plan->micro_integrations )] );

					$plan->storeload();
				}
			}
		}
	} else {
		$short	= 'microIntegration storing failure';
		if ( !empty( $_POST['class_name'] ) ) {
			$event	= 'When trying to store microIntegration: ' . $_POST['class_name'] . ', callIntegration failed';
		} else {
			$event	= 'When trying to store microIntegration: ' . $mi->id . ', callIntegration failed';
		}
		$tags	= 'microintegration,loading,error';
		$params = array();

		$eventlog = new eventLog();
		$eventlog->issue( $short, $tags, $event, 128, $params );
	}

	$mi->reorder();

	if ( $id ) {
		if ( $apply ) {
			aecRedirect( 'index.php?option=' . $option . '&task=editMicroIntegration&id=' . $id, JText::_('AEC_MSG_SUCESSFULLY_SAVED') );
		} else {
			aecRedirect( 'index.php?option=' . $option . '&task=showMicroIntegrations', JText::_('AEC_MSG_SUCESSFULLY_SAVED') );
		}
	} else {
		aecRedirect( 'index.php?option=' . $option . '&task=editMicroIntegration&id=' . $mi->id , JText::_('AEC_MSG_SUCESSFULLY_SAVED') );
	}

}

function removeMicroIntegration( $id, $option )
{
	$db = &JFactory::getDBO();

	$ids = implode( ',', $id );

	$query = 'SELECT count(*)'
			. ' FROM #__acctexp_microintegrations'
			. ' WHERE `id` IN (' . $ids . ')'
			;
	$db->setQuery( $query );
	$total = $db->loadResult();

	if ( $total==0 ) {
		echo "<script> alert('" . html_entity_decode( JText::_('AEC_MSG_NO_ITEMS_TO_DELETE') ) . "'); window.history.go(-1);</script>\n";
		exit;
	}

	// Call On-Deletion function
	foreach ( $id as $k ) {
		$mi = new microIntegration();
		$mi->load($k);
		if ( $mi->callIntegration() ) {
			$mi->delete();
		}
	}

	// Micro Integrations from table
	$query = 'DELETE FROM #__acctexp_microintegrations'
			. ' WHERE `id` IN (' . $ids . ')'
			;
	$db->setQuery( $query	);

	if ( !$db->query() ) {
		echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	$msg = $total . ' ' . JText::_('AEC_MSG_ITEMS_DELETED');

	aecRedirect( 'index.php?option=' . $option . '&task=showMicroIntegrations', $msg );
}

function cancelMicroIntegration( $option )
{
	aecRedirect( 'index.php?option=' . $option . '&task=showMicroIntegrations', JText::_('AEC_CMN_EDIT_CANCELLED') );
}

function changeMicroIntegration( $cid=null, $state=0, $option )
{
	$db = &JFactory::getDBO();

	if ( count( $cid ) < 1 ) {
		$action = $state == 1 ? JText::_('AEC_CMN_TOPUBLISH'): JText::_('AEC_CMN_TOUNPUBLISH');
		echo "<script> alert('" . sprintf( html_entity_decode( JText::_('AEC_ALERT_SELECT_FIRST_TO') ), $action ) . "'); window.history.go(-1);</script>\n";
		exit;
	}

	$total = count( $cid );
	$cids = implode( ',', $cid );

	$query = 'UPDATE #__acctexp_microintegrations'
			. ' SET `active` = \'' . $state . '\''
			. ' WHERE `id` IN (' . $cids . ')'
			;
	$db->setQuery( $query );
	if ( !$db->query() ) {
		echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if ( $state == '1' ) {
		$msg = $total . ' ' . JText::_('AEC_MSG_ITEMS_SUCC_PUBLISHED');
	} elseif ( $state == '0' ) {
		$msg = $total . ' ' . JText::_('AEC_MSG_ITEMS_SUCC_UNPUBLISHED');
	}

	aecRedirect( 'index.php?option=' . $option . '&task=showMicroIntegrations', $msg );
}

function listCoupons( $option )
{
 	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

 	$limit		= $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg( 'list_limit' ) );
	$limitstart = $app->getUserStateFromRequest( "viewconf{$option}limitstart", 'limitstart', 0 );

	$total = 0;

	$query = 'SELECT count(*)'
			. ' FROM #__acctexp_coupons'
			;
 	$db->setQuery( $query );
 	$total += $db->loadResult();

	$query = 'SELECT count(*)'
			. ' FROM #__acctexp_coupons_static'
			;
 	$db->setQuery( $query );
 	$total += $db->loadResult();

 	if ( $limitstart > $total ) {
 		$limitstart = 0;
 	}

	$pageNav = new bsPagination( $total, $limitstart, $limit );

 	// get the subset (based on limits) of required records
 	$query = '(SELECT *, "0" as `type`'
		 	. ' FROM #__acctexp_coupons)'
		 	. ' UNION '
		 	. '(SELECT *, "1" as `type`'
		 	. ' FROM #__acctexp_coupons_static)'
		 	. ' ORDER BY `id` DESC'
		 	. ' LIMIT ' . $pageNav->limitstart . ',' . $pageNav->limit
		 	;
 	$db->setQuery( $query );

 	$rows = $db->loadObjectList();
 	if ( $db->getErrorNum() ) {
 		echo $db->stderr();
 		return false;
 	}

 	$query = 'SELECT SUM(usecount)'
			. ' FROM #__acctexp_coupons'
			;
	$db->setQuery( $query );

 	$total_usecount = $db->loadResult();
 	if ( $db->getErrorNum() ) {
 		echo $db->stderr();
 		return false;
 	}

 	$query = 'SELECT SUM(usecount)'
			. ' FROM #__acctexp_coupons_static'
			;
	$db->setQuery( $query );

 	$total_usecount += $db->loadResult();
 	if ( $db->getErrorNum() ) {
 		echo $db->stderr();
 		return false;
 	}

	foreach ( $rows as $rid => $row ) {
		if ( $row->usecount ) {
			$rows[$rid]->percentage = $row->usecount / ( $total_usecount / 100 );
		} else {
			$rows[$rid]->percentage = 0;
		}
		
		$rows[$rid]->inner = false;
		if ( $rows[$rid]->percentage > 15 ) {
			$rows[$rid]->inner = true;
		}
	}

	HTML_AcctExp::listCoupons( $rows, $pageNav, $option );
 }

function editCoupon( $id, $option, $new )
{
	$db = &JFactory::getDBO();

	$user = &JFactory::getUser();

	$lists					= array();
	$params_values			= array();
	$restrictions_values	= array();

	$cph = new couponHandler();

	if ( !$new ) {
		$idx = explode( ".", $id );

		$cph->coupon = new Coupon( $idx[0] );
		$cph->coupon->load( $idx[1] );

		$params_values			= $cph->coupon->params;
		$discount_values		= $cph->coupon->discount;
		$restrictions_values	= $cph->coupon->restrictions;
	} else {
		$cph->coupon = new Coupon();
		$cph->coupon->createNew();

		$discount_values		= array();
		$restrictions_values	= array();
	}

	// We need to convert the values that are set as object properties
	$params_values['active']				= $cph->coupon->active;
	$params_values['type']					= $cph->coupon->type;
	$params_values['name']					= $cph->coupon->name;
	$params_values['desc']					= $cph->coupon->desc;
	$params_values['coupon_code']			= $cph->coupon->coupon_code;
	$params_values['usecount']				= $cph->coupon->usecount;
	$params_values['micro_integrations']	= $cph->coupon->micro_integrations;

	// params and their type values
	$params['active']						= array( 'toggle',		1 );
	$params['type']							= array( 'toggle',		1 );
	$params['name']							= array( 'inputC',		'' );
	$params['desc']							= array( 'inputE',		'' );
	$params['coupon_code']					= array( 'inputC',		'' );
	$params['micro_integrations']			= array( 'list',		'' );

	$params['params_remap']					= array( 'subarea_change',	'params' );

	$params['amount_use']					= array( 'toggle',		'' );
	$params['amount']						= array( 'inputB',		'' );
	$params['amount_percent_use']			= array( 'toggle',		'' );
	$params['amount_percent']				= array( 'inputB',		'' );
	$params['percent_first']				= array( 'toggle',		'' );
	$params['useon_trial']					= array( 'toggle',		'' );
	$params['useon_full']					= array( 'toggle',		'1' );
	$params['useon_full_all']				= array( 'toggle',		'1' );

	$params['has_start_date']				= array( 'toggle',		1 );
	$params['start_date']					= array( 'list_date',	date( 'Y-m-d', ( (int) gmdate('U') ) ) );
	$params['has_expiration']				= array( 'toggle',		0);
	$params['expiration']					= array( 'list_date',	date( 'Y-m-d', ( (int) gmdate('U') ) ) );
	$params['has_max_reuse']				= array( 'toggle',		0 );
	$params['max_reuse']					= array( 'inputA',		1 );
	$params['has_max_peruser_reuse']		= array( 'toggle',		1 );
	$params['max_peruser_reuse']			= array( 'inputA',		1 );
	$params['usecount']						= array( 'inputA',		0 );

	$params['usage_plans_enabled']			= array( 'toggle',		0 );
	$params['usage_plans']					= array( 'list',		0 );

	$params['usage_cart_full']				= array( 'toggle',		0 );
	$params['cart_multiple_items']			= array( 'toggle',		0 );
	$params['cart_multiple_items_amount']	= array( 'inputB',		'' );

	$params['restr_remap']					= array( 'subarea_change',	'restrictions' );

	$params['depend_on_subscr_id']			= array( 'toggle',		0 );
	$params['subscr_id_dependency']			= array( 'inputB',		'' );
	$params['allow_trial_depend_subscr']	= array( 'toggle',		0 );

	$params['restrict_combination']			= array( 'toggle',		0 );
	$params['bad_combinations']				= array( 'list',		'' );

	$params['allow_combination']			= array( 'toggle',		0 );
	$params['good_combinations']			= array( 'list',		'' );

	$params['restrict_combination_cart']	= array( 'toggle',		0 );
	$params['bad_combinations_cart']		= array( 'list',		'' );

	$params['allow_combination_cart']		= array( 'toggle',		0 );
	$params['good_combinations_cart']		= array( 'list',		'' );

	$restrictionHelper = new aecRestrictionHelper();
	$params = array_merge( $params, $restrictionHelper->getParams() );

	// get available plans
	$available_plans = array();

	$query = 'SELECT `id` as value, `name` as text'
			. ' FROM #__acctexp_plans'
			;
	$db->setQuery( $query );
	$plans = $db->loadObjectList();

 	if ( is_array( $plans ) ) {
 		$all_plans					= array_merge( $available_plans, $plans );
 	} else {
 		$all_plans					= $available_plans;
 	}
	$total_all_plans			= min( max( ( count( $all_plans ) + 1 ), 4 ), 20 );

	// get usages
	if ( !empty( $restrictions_values['usage_plans'] ) ) {
		$query = 'SELECT `id` AS value, `name` as text'
				. ' FROM #__acctexp_plans'
				. ' WHERE `id` IN (' . implode( ',', $restrictions_values['usage_plans'] ) . ')'
				;
		$db->setQuery( $query );

	 	$sel_usage_plans = $db->loadObjectList();
	} else {
		$sel_usage_plans = 0;
	}

	$lists['usage_plans']		= JHTML::_('select.genericlist', $all_plans, 'usage_plans[]', 'size="' . $total_all_plans . '" multiple="multiple"',
									'value', 'text', $sel_usage_plans);


	// get available micro integrations
	$available_mi = array();

	$query = 'SELECT `id` AS value, CONCAT(`name`, " - ", `desc`) AS text'
			. ' FROM #__acctexp_microintegrations'
			. ' WHERE `active` = 1'
			. ' ORDER BY `ordering`'
			;
	$db->setQuery( $query );
	$mi_list = $db->loadObjectList();

	$mis = array();
	if ( !empty( $mi_list ) && !empty( $params_values['micro_integrations'] ) ) {
		foreach ( $mi_list as $mi_item ) {
			if ( in_array( $mi_item->value, $params_values['micro_integrations'] ) ) {
				$mis[] = $mi_item->value;
			}
		}
	}

 	if ( !empty( $mis ) ) {
	 	$query = 'SELECT `id` AS value, CONCAT(`name`, " - ", `desc`) AS text'
			 	. ' FROM #__acctexp_microintegrations'
			 	. ( !empty( $mis ) ? ' WHERE `id` IN (' . implode( ',', $mis ) . ')' : '' )
			 	;
	 	$db->setQuery( $query );
		$selected_mi = $db->loadObjectList();
 	} else {
 		$selected_mi = array();
 	}

	$lists['micro_integrations'] = JHTML::_('select.genericlist', $mi_list, 'micro_integrations[]', 'size="' . min((count( $mi_list ) + 1), 25) . '" multiple="multiple"', 'value', 'text', $selected_mi );

	$query = 'SELECT `coupon_code` as value, `coupon_code` as text'
			. ' FROM #__acctexp_coupons'
			. ' WHERE `coupon_code` != \'' . $cph->coupon->coupon_code . '\''
			;
	$db->setQuery( $query );
	$coupons = $db->loadObjectList();

	$query = 'SELECT `coupon_code` as value, `coupon_code` as text'
			. ' FROM #__acctexp_coupons_static'
			. ' WHERE `coupon_code` != \'' . $cph->coupon->coupon_code . '\''
			;
	$db->setQuery( $query );
	$coupons = array_merge( $db->loadObjectList(), $coupons );

	$cpl = array( 'bad_combinations', 'good_combinations', 'bad_combinations_cart', 'good_combinations_cart' );

	foreach ( $cpl as $cpn ) {
		$cur = array();

		if ( !empty( $restrictions_values[$cpn] ) ) {
			$query = 'SELECT `coupon_code` as value, `coupon_code` as text'
					. ' FROM #__acctexp_coupons'
					. ' WHERE `coupon_code` IN (\'' . implode( '\',\'', $restrictions_values[$cpn] ) . '\')'
					;
			$db->setQuery( $query );
			$cur = $db->loadObjectList();

			$query = 'SELECT `coupon_code` as value, `coupon_code` as text'
					. ' FROM #__acctexp_coupons_static'
					. ' WHERE `coupon_code` IN (\'' . implode( '\',\'', $restrictions_values[$cpn] ) . '\')'
					;
			$db->setQuery( $query );
			$nc = $db->loadObjectList();

			if ( !empty( $nc ) ) {
				$cur = array_merge( $nc, $cur );
			}
		}

		$lists[$cpn] = JHTML::_('select.genericlist', $coupons, $cpn.'[]', 'size="' . min((count( $coupons ) + 1), 25) . '" multiple="multiple"', 'value', 'text', $cur);
	}

	$lists = array_merge( $lists, $restrictionHelper->getLists( $params_values, $restrictions_values ) );

	$settings = new aecSettings( 'coupon', 'general' );

	if ( is_array( $discount_values ) && is_array( $restrictions_values ) ) {
		$settingsparams = array_merge( $params_values, $discount_values, $restrictions_values );
	} else {
		$settingsparams = $params_values;
	}

	$settings->fullSettingsArray( $params, $settingsparams, $lists );

	// Call HTML Class
	$aecHTML = new aecHTML( $settings->settings, $settings->lists );

	// Lets grab the data and fill it in.
	$query = 'SELECT id'
			. ' FROM #__acctexp_invoices'
			. ' WHERE `coupons` <> \'\''
			. ' ORDER BY `created_date` DESC'
			;
	$db->setQuery( $query );
	$rows = $db->loadObjectList();

	if ( $db->getErrorNum() ) {
		echo $db->stderr();
		return false;
	}

	$aecHTML->invoices = array();
	foreach ( $rows as $row ) {
		$invoice = new Invoice();
		$invoice->load( $row->id );

		if ( !in_array( $cph->coupon->coupon_code, $invoice->coupons ) ) {
			continue;
		}

		$in_formatted = $invoice->formatInvoiceNumber();

		$invoice->invoice_number_formatted = $invoice->invoice_number . ( ($in_formatted != $invoice->invoice_number) ? "\n" . '(' . $in_formatted . ')' : '' );

		$invoice->usage = '<a href="index.php?option=com_acctexp&amp;task=editSubscriptionPlan&amp;id=' . $invoice->usage . '">' . $invoice->usage . '</a>';

		$query = 'SELECT username'
				. ' FROM #__users'
				. ' WHERE `id` = \'' . $invoice->userid . '\''
				;
		$db->setQuery( $query );
		$username = $db->loadResult();

		$invoice->username = '<a href="index.php?option=com_acctexp&amp;task=editMembership&userid=' . $invoice->userid . '">';

		if ( !empty( $username ) ) {
			$invoice->username .= $username . '</a>';
		} else {
			$invoice->username .= $invoice->userid;
		}

		$invoice->username .= '</a>';

		$aecHTML->invoices[] = $invoice;
	}

	HTML_AcctExp::editCoupon( $option, $aecHTML, $cph->coupon );
}

function saveCoupon( $option, $apply=0 )
{
	$db = &JFactory::getDBO();

	$new = 0;
	$type = $_POST['type'];

	if ( $_POST['coupon_code'] != '' ) {

		$cph = new couponHandler();

		if ( !empty( $_POST['id'] ) ) {
			$cph->coupon = new Coupon( $_POST['oldtype'] );
			$cph->coupon->load( $_POST['id'] );

			if ( $cph->coupon->id ) {
				$cph->status = true;
			}
		} else {
			$cph->load( $_POST['coupon_code'] );
		}

		if ( !$cph->status ) {
			$cph->coupon = new Coupon( $type );
			$cph->coupon->createNew( $_POST['coupon_code'] );
			$cph->status = true;
			$new = 1;
		}

		if ( $cph->status ) {
			if ( !$new ) {
				if ( $cph->coupon->type != $_POST['type'] ) {
					$cph->switchType();
				}
			}

			unset( $_POST['type'] );
			unset( $_POST['oldtype'] );
			unset( $_POST['id'] );

			$post = AECToolbox::cleanPOST( $_POST, false );

			$cph->coupon->savePOSTsettings( $post );

			$cph->coupon->storeload();
		} else {
			$short	= 'coupon store failure';
			$event	= 'When trying to store coupon';
			$tags	= 'coupon,loading,error';
			$params = array();

			$eventlog = new eventLog();
			$eventlog->issue( $short, $tags, $event, 128, $params );
		}

		if ( $apply ) {
			aecRedirect( 'index.php?option=' . $option . '&task=editCoupon&id=' . $cph->coupon->type.'.'.$cph->coupon->id, JText::_('AEC_MSG_SUCESSFULLY_SAVED') );
		} else {
			aecRedirect( 'index.php?option=' . $option . '&task=showCoupons', JText::_('AEC_MSG_SUCESSFULLY_SAVED') );
		}
	} else {
		aecRedirect( 'index.php?option=' . $option . '&task=showCoupons', JText::_('AEC_MSG_NO_COUPON_CODE') );
	}

}

function removeCoupon( $id, $option, $returnTask )
{
	$db = &JFactory::getDBO();

	$rids = $sids = array();
	foreach ( $id as $i ) {
		$ex = explode( '.', $i );

		if ( $ex[0] ) {
			$sids[] = $ex[1];
		} else {
			$rids[] = $ex[1];
		}
	}

	if ( !empty( $sids ) ) {
		$query = 'DELETE FROM #__acctexp_coupons_static'
				. ' WHERE `id` IN (' . implode( ',', $sids ) . ')'
				;
		$db->setQuery( $query );

		if ( !$db->query() ) {
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}
	}

	if ( !empty( $rids ) ) {
		$query = 'DELETE FROM #__acctexp_coupons'
				. ' WHERE `id` IN (' . implode( ',', $rids ) . ')'
				;
		$db->setQuery( $query );

		if ( !$db->query() ) {
			echo "<script> alert('".$db->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit();
		}
	}

	$msg = JText::_('AEC_MSG_ITEMS_DELETED');

	aecRedirect( 'index.php?option=' . $option . '&task=showCoupons', $msg );
}

function changeCoupon( $id=null, $state=0, $option )
{
	$db = &JFactory::getDBO();

	if ( count( $id ) < 1 ) {
		$action = $state == 1 ? JText::_('AEC_CMN_TOPUBLISH') : JText::_('AEC_CMN_TOUNPUBLISH');
		echo "<script> alert('" . sprintf( html_entity_decode( JText::_('AEC_ALERT_SELECT_FIRST_TO') ) ), $action . "'); window.history.go(-1);</script>\n";
		exit;
	}

	$idx	= explode($id);
	$total	= count( $id );

	$rids = $sids = array();
	foreach ( $idx as $ctype => $cid ) {
		if ( $ctype ) {
			$sids[] = $cid;
		} else {
			$rids[] = $cid;
		}
	}

	$total	= count( $id );
	$cids	= implode( ',', $cid );

	if ( !empty( $sids ) ) {
		$query = 'UPDATE #__acctexp_coupons_static'
				. ' SET `active` = IF (`active` = 1, 0, 1)'
				. ' WHERE `id` IN (' . implode( ',', $sids ) . ')'
				;
		$db->setQuery( $query );
		$db->query();
	}

	if ( !empty( $rids ) ) {
		$query = 'UPDATE #__acctexp_coupons'
				. ' SET `active` = IF (`active` = 1, 0, 1)'
				. ' WHERE `id` IN (' . implode( ',', $rids ) . ')'
				;
		$db->setQuery( $query );
		$db->query();
	}

	$msg = $total . ' ' . JText::_('AEC_MSG_ITEMS_SUCC_UPDATED');

	aecRedirect( 'index.php?option=' . $option . '&task=showCoupons', $msg );
}

function invoices( $option )
{
	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

	$limit 		= intval( $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg( 'list_limit' ) ) );
	$limitstart = intval( $app->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 ) );
	$search 	= $app->getUserStateFromRequest( "search{$option}_invoices", 'search', '' );

	if ( $search ) {
		$unformatted = xJ::escape( $db, trim( strtolower( $search ) ) );

		$where = 'LOWER(`invoice_number`) LIKE \'%' . $unformatted . '%\''
				. ' OR LOWER(`secondary_ident`) LIKE \'%' . $unformatted . '%\''
				. ' OR `id` LIKE \'%' . $unformatted . '%\''
				. ' OR LOWER(`invoice_number_format`) LIKE \'%' . $unformatted . '%\''
				;
	}

	// get the total number of records
	$query = 'SELECT count(*)'
			. ' FROM #__acctexp_invoices'
			;
	$db->setQuery( $query );
	$total = $db->loadResult();
	echo $db->getErrorMsg();

	$pageNav = new bsPagination( $total, $limitstart, $limit );

	// Lets grab the data and fill it in.
	$query = 'SELECT *'
			. ' FROM #__acctexp_invoices'
			. ( !empty( $where ) ? ( ' WHERE ' . $where . ' ' ) : '' )
			. ' ORDER BY `created_date` DESC'
			. ' LIMIT ' . $pageNav->limitstart . ',' . $pageNav->limit;
			;
	$db->setQuery( $query );
	$rows = $db->loadObjectList();

	if ( $db->getErrorNum() ) {
		echo $db->stderr();
		return false;
	}

	$cclist = array();
	foreach ( $rows as $id => $row ) {
		$in_formatted = Invoice::formatInvoiceNumber( $row );

		$rows[$id]->invoice_number_formatted = $row->invoice_number . ( ($in_formatted != $row->invoice_number) ? "\n" . '(' . $in_formatted . ')' : '' );

		if ( !empty( $row->coupons ) ) {
			$coupons = unserialize( base64_decode( $row->coupons ) );
		} else {
			$coupons = null;
		}

		if ( !empty( $coupons ) ) {
			$rows[$id]->coupons = "";

			$couponslist = array();
			foreach ( $coupons as $coupon_code ) {
				if ( !isset( $cclist[$coupon_code] ) ) {
					$cclist[$coupon_code] = couponHandler::idFromCode( $coupon_code );
				}

				if ( !empty( $cclist[$coupon_code]['id'] ) ) {
					$couponslist[] = '<a href="index.php?option=com_acctexp&amp;task=' . ( $cclist[$coupon_code]['type'] ? 'editcouponstatic' : 'editcoupon' ) . '&amp;id=' . $cclist[$coupon_code]['id'] . '">' . $coupon_code . '</a>';
				}
			}

			$rows[$id]->coupons = implode( ", ", $couponslist );
		} else {
			$rows[$id]->coupons = null;
		}

		$rows[$id]->usage = '<a href="index.php?option=com_acctexp&amp;task=editSubscriptionPlan&amp;id=' . $rows[$id]->usage . '">' . $rows[$id]->usage . '</a>';

		$query = 'SELECT username'
				. ' FROM #__users'
				. ' WHERE `id` = \'' . $row->userid . '\''
				;
		$db->setQuery( $query );
		$username = $db->loadResult();

		$rows[$id]->username = '<a href="index.php?option=com_acctexp&amp;task=editMembership&userid=' . $row->userid . '">';

		if ( !empty( $username ) ) {
			$rows[$id]->username .= $username . '</a>';
		} else {
			$rows[$id]->username .= $row->userid;
		}

		$rows[$id]->username .= '</a>';
	}

	HTML_AcctExp::viewInvoices( $option, $rows, $search, $pageNav );
}

function editInvoice( $id, $option, $returnTask, $userid )
{
	$db = &JFactory::getDBO();

	$row = new Invoice();
	$row->load( $id );

	$params['active']						= array( 'toggle',		1 );
	$params['fixed']						= array( 'toggle',		0 );
	$params['userid']						= array( 'hidden',		$userid );
	$params['returnTask']					= array( 'hidden',		$returnTask );
	$params['created_date']					= array( 'list_date',	date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) ) );
	$params['amount']						= array( 'inputB',		'' );
	$params['usage']						= array( 'list', 		0 );
	$params['method']						= array( 'list', 		'' );

	$available_plans = SubscriptionPlanHandler::getActivePlanList();

	$lists['usage'] = JHTML::_('select.genericlist', $available_plans, 'usage', 'size="1"', 'value', 'text', $row->usage );

	$pph					= new PaymentProcessorHandler();
	$lists['method']		= str_replace( 'processor', 'method', $pph->getSelectList( $row->method, true ) );

	$params_values = array();
	$params_values['active']			= $row->active;
	$params_values['fixed']				= $row->fixed;
	$params_values['userid']			= $row->userid;
	$params_values['created_date']		= $row->created_date;

	$settings = new aecSettings ( 'invoice', 'general' );
	$settings->fullSettingsArray( $params, $params_values, $lists ) ;

	// Call HTML Class
	$aecHTML = new aecHTML( $settings->settings, $settings->lists );
	if ( !empty( $customparamsarray ) ) {
		$aecHTML->customparams = $customparamsarray;
	}

	HTML_AcctExp::editInvoice( $option, $aecHTML, $id );
}

function saveInvoice( $option, $return=0 )
{
	$db = &JFactory::getDBO();

	$user = &JFactory::getUser();

	$row = new Invoice();
	$row->load( $_POST['id'] );

	$returnTask = $_POST['returnTask'];

	unset( $_POST['id'] );
	unset( $_POST['returnTask'] );

	$row->savePOSTsettings( $_POST );

	$row->storeload();

	if ( $return ) {
		aecRedirect( 'index.php?option=' . $option . '&task=editInvoice&id=' . $row->id . '&returnTask=' . $returnTask, JText::_('AEC_CONFIG_SAVED') );
	} else {
		if ( $returnTask ) {
			aecRedirect( 'index.php?option=' . $option . '&task=editMembership&userid='.$_POST['userid'], JText::_('AEC_CONFIG_SAVED') );
		} else {
			aecRedirect( 'index.php?option=' . $option . '&task=invoices', JText::_('AEC_CONFIG_SAVED') );
		}
	}
}

function clearInvoice( $option, $invoice_number, $applyplan, $task )
{
	$db = &JFactory::getDBO();

	$invoiceid = AECfetchfromDB::InvoiceIDfromNumber( $invoice_number, 0, true );

	if ( $invoiceid ) {
		$db = &JFactory::getDBO();

		$objInvoice = new Invoice();
		$objInvoice->load( $invoiceid );

		$pp = new stdClass();
		$pp->id = 0;
		$pp->processor_name = 'none';

		if ( $applyplan ) {
			$objInvoice->pay();
		} else {
			$objInvoice->setTransactionDate();
		}

		$history = new logHistory();
		$history->entryFromInvoice( $objInvoice, null, $pp );

		if ( strcmp( $task, 'editMembership' ) == 0) {
			$userid = '&userid=' . $objInvoice->userid;
		} else {
			$userid = '';
		}
	}

	aecRedirect( 'index.php?option=' . $option . '&task=' . $task . $userid, JText::_('AEC_MSG_INVOICE_CLEARED') );
}

function cancelInvoice( $option, $invoice_number, $task )
{
	$db = &JFactory::getDBO();

	$invoiceid = AECfetchfromDB::InvoiceIDfromNumber( $invoice_number, 0, true );

	if ( $invoiceid ) {
		$objInvoice = new Invoice();
		$objInvoice->load( $invoiceid );

		$objInvoice->delete();

		if ( strcmp( $task, 'editMembership' ) == 0 ) {
			$userid = '&userid=' . $objInvoice->userid;
		} else {
			$userid = '';
		}
	}

	aecRedirect( 'index.php?option=' . $option . '&task=' . $task . $userid, JText::_('REMOVED') );
}

function AdminInvoicePrintout( $option, $invoice_number, $standalone=true )
{
	$invoice = new Invoice();
	$invoice->loadInvoiceNumber( $invoice_number );

	$iFactory = new InvoiceFactory( $invoice->userid, null, null, null, null, null, false, true );
	$iFactory->invoiceprint( 'com_acctexp', $invoice->invoice_number, $standalone );
}

function AdminInvoicePDF( $option, $invoice_number )
{
	require_once( JPATH_SITE . '/components/com_acctexp/lib/tcpdf/config/lang/eng.php' );
	require_once( JPATH_SITE . '/components/com_acctexp/lib/tcpdf/tcpdf.php' );

	ob_start();

	AdminInvoicePrintout( $option, $invoice_number, false );

	$buffer = ob_get_contents();

	ob_end_clean();

	$document=& JFactory::getDocument();
	$document->_type="html";
	$renderer = $document->loadRenderer("head");

	$content = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
				.'<html xmlns="http://www.w3.org/1999/xhtml">'
				.'<head>' . $renderer->render() . '</head><body>'.$buffer.'</body>'
				.'</html>';

	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	$pdf->AddPage();
	$pdf->writeHTML($content, true, false, true, false, '');

	$pdf->Output( $invoice_number.'.pdf', 'I');exit;
}

function history( $option )
{
	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

	$limit 		= intval( $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg( 'list_limit' ) ) );
	$limitstart = intval( $app->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 ) );
	$search 	= $app->getUserStateFromRequest( "search{$option}_log_history", 'search', '' );

	$where = array();
	if ( $search ) {
		$where[] = 'LOWER(`user_name`) LIKE \'%' . xJ::escape( $db, trim( strtolower( $search ) ) ) . '%\'';
		$where[] = 'LOWER(`invoice_number`) LIKE \'%' . xJ::escape( $db, trim( strtolower( $search ) ) ) . '%\'';
		$where[] = 'LOWER(`proc_name`) LIKE \'%' . xJ::escape( $db, trim( strtolower( $search ) ) ) . '%\'';
	}

	// get the total number of records
	$query = 'SELECT count(*)'
			. '  FROM #__acctexp_log_history'
			. ( count( $where ) ? ' WHERE ' . implode( ' OR ', $where ) : '' )
			;
	$db->setQuery( $query );
	$total = $db->loadResult();
	echo $db->getErrorMsg();

	$pageNav = new bsPagination( $total, $limitstart, $limit );

	// Lets grab the data and fill it in.
	$query = 'SELECT id'
			. ' FROM #__acctexp_log_history'
			. ( count( $where ) ? ' WHERE ' . implode( ' OR ', $where ) : '' )
			. ' GROUP BY `transaction_date`'
			. ' ORDER BY `transaction_date` DESC'
			. ' LIMIT ' . $pageNav->limitstart . ',' . $pageNav->limit
			;
	$db->setQuery( $query );
	$rowids = xJ::getDBArray( $db );

	$rows = array();
	foreach ( $rowids as $rid ) {
		$entry = new logHistory();
		$entry->load( $rid );

		$rows[] = $entry;
	}

	if ( $db->getErrorNum() ) {
		echo $db->stderr();
		return false;
	}

	HTML_AcctExp::viewHistory( $option, $rows, $search, $pageNav );
}

function eventlog( $option )
{
	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

	$limit 		= intval( $app->getUserStateFromRequest( "viewlistlimit", 'limit', $app->getCfg( 'list_limit' ) ) );
	$limitstart = intval( $app->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 ) );
	$search 	= $app->getUserStateFromRequest( "search{$option}_invoices", 'search', '' );

	$where = array();
	if ( $search ) {
		$where[] = 'LOWER(`short`) LIKE \'%' . xJ::escape( $db, trim( strtolower( $search ) ) ) . '%\'';
		$where[] = 'LOWER(`event`) LIKE \'%' . xJ::escape( $db, trim( strtolower( $search ) ) ) . '%\'';
		$where[] = 'LOWER(`tags`) LIKE \'%' . xJ::escape( $db, trim( strtolower( $search ) ) ) . '%\'';
	}

	// get the total number of records
	$query = 'SELECT count(*)'
			. ' FROM #__acctexp_eventlog'
			. ( count( $where ) ? ' WHERE ' . implode( ' OR ', $where ) : '' )
			;
	$db->setQuery( $query );
	$total = $db->loadResult();
	echo $db->getErrorMsg();

	$pageNav = new bsPagination( $total, $limitstart, $limit );

	// Lets grab the data and fill it in.
	$query = 'SELECT id'
			. ' FROM #__acctexp_eventlog'
			. ( count( $where ) ? ' WHERE ' . implode( ' OR ', $where ) : '' )
			. ' ORDER BY `id` DESC'
			. ' LIMIT ' . $pageNav->limitstart . ',' . $pageNav->limit
			;
	$db->setQuery( $query );
	$rows = xJ::getDBArray( $db );

	if ( $db->getErrorNum() ) {
		echo $db->stderr();
		return false;
	}

	$events = array();
	foreach ( $rows as $id ) {
		$row = new EventLog();
		$row->load( $id );

		$events[$id]->id		= $row->id;
		$events[$id]->datetime	= $row->datetime;
		$events[$id]->short		= $row->short;
		$events[$id]->tags		= implode( ', ', explode( ',', $row->tags ) );
		$events[$id]->event		= $row->event;
		$events[$id]->level		= $row->level;
		$events[$id]->notify	= $row->notify;

		$params = array();
		if ( !empty( $row->params ) && is_array( $row->params ) ) {
			foreach ( $row->params as $key => $value ) {
				switch ( $key ) {
					case 'userid':
						$content = '<a href="index.php?option=com_acctexp&amp;task=editMembership&userid=' . $value . '">' . $value . '</a>';
						break;
					case 'invoice_number':
						$content = '<a class="quicksearch" href="#">' . $value . '</a>';
						break;
					default:
						$content = $value;
						break;
				}
				$params[] = $key . '(' . $content . ')';
			}
		}
		$events[$id]->params = implode( ', ', $params );

		if ( strpos( $row->event, '<?xml' ) !== false ) {
			$events[$id]->event = '<p><strong>XML cell - decoded as:</strong></p><pre class="prettyprint">'.htmlentities($row->event).'</pre>';
		} else {
			$format = @json_decode( $row->event );

			if ( is_array( $format ) || is_object( $format ) ) {
				$events[$id]->event = '<p><strong>JSON cell - decoded as:</strong></p><pre class="prettyprint">'.print_r($format,true).'</pre>';
			} else {
				$events[$id]->event = htmlentities( stripslashes( $events[$id]->event ) );
			}
		}
	}

	HTML_AcctExp::eventlog( $option, $events, $search, $pageNav );
}

function aec_stats( $option, $page )
{
	if ( empty( $page ) ) {
		$page = 'overview';
	}

	$stats = array();

	$document=& JFactory::getDocument();
	$document->addCustomTag( '<script type="text/javascript" src="' . JURI::root(true) . '/media/' . $option . '/js/d3/d3.min.js"></script>' );
	$document->addCustomTag( '<script type="text/javascript" src="' . JURI::root(true) . '/media/' . $option . '/js/d3/d3.time.min.js"></script>' );
	$document->addCustomTag( '<script type="text/javascript" src="' . JURI::root(true) . '/media/' . $option . '/js/d3/d3.layout.min.js"></script>' );
	$document->addCustomTag( '<script type="text/javascript" src="' . JURI::root(true) . '/media/' . $option . '/js/rickshaw/rickshaw.js"></script>' );
	$document->addCustomTag( '<link type="text/css" href="' . JURI::root(true) . '/media/' . $option . '/js/rickshaw/rickshaw.css" rel="stylesheet" />' );
	$document->addCustomTag( '<link type="text/css" href="' . JURI::root(true) . '/media/' . $option . '/js/colorbrewer/colorbrewer.css" rel="stylesheet" />' );

	$db = &JFactory::getDBO();

	$query = 'SELECT count(*)'
			. ' FROM #__acctexp_log_history'
			;
	$db->setQuery( $query );

	$stats['sale_count'] = $db->loadResult();

	$query = 'SELECT DISTINCT(date(transaction_date)) AS date, count( * ) AS count' .
			' FROM #__acctexp_log_history' .
			' GROUP BY date' .
			' ORDER BY count ASC';
	$db->setQuery( $query );
	$sales_count = $db->loadObjectList();
	$stats['min_sale_count'] = $sales_count[0]->count;
	$stats['max_sale_count'] = $sales_count[count($sales_count)-1]->count;
	$stats['avg_sale_count'] = $sales_count[count($sales_count)/2]->count;

	$query = 'SELECT amount'
			. ' FROM #__acctexp_log_history'
			. ' ORDER BY 0+`amount` DESC'
			;
	$db->setQuery( $query );

	$stats['max_sale_value'] = $db->loadResult();

	$query = 'SELECT MIN(amount)'
			. ' FROM #__acctexp_log_history'
			. ' WHERE amount > 0'
			;
	$db->setQuery( $query );

	$stats['min_sale_value'] = $db->loadResult();

	$query = 'SELECT SUM(amount)'
			. ' FROM #__acctexp_log_history'
			;
	$db->setQuery( $query );

	if ( $stats['sale_count'] ) {
		$stats['avg_sale_value'] = round( $db->loadResult() / $stats['sale_count'], 2 );
	} else {
		$stats['avg_sale_value'] = 0;
	}

	$stats['avg_sale'] = $stats['avg_sale_count']*$stats['avg_sale_value']*1.8;

	$query = 'SELECT MIN(transaction_date)'
			. ' FROM #__acctexp_log_history'
			;
	$db->setQuery( $query );

	$stats['first_sale'] = $db->loadResult();

	$query = 'SELECT id, name'
			. ' FROM #__acctexp_plans'
			. ' ORDER BY `id`'
		 	;

	$db->setQuery( $query );

	$rows = $db->loadObjectList();

	$mrow = count( $rows )-1;

	$i = 0;
	$stats['plan_names'] = array();
	for ( $i=0; $i<=$rows[$mrow]->id; $i++ ) {
		$stats['plan_names'][$i] = "";
		foreach ( $rows as $rid => $row ) {
			if ( $row->id == $i ) {
				$stats['plan_names'][$i] = $row->name;
			}
		}
	}

	$query = 'SELECT id, name'
			. ' FROM #__acctexp_itemgroups'
			. ' ORDER BY `id`'
		 	;

	$db->setQuery( $query );

	$rows = $db->loadObjectList();

	$mrow = count( $rows )-1;

	$i = 0;
	$stats['group_names'] = array();
	for ( $i=0; $i<=$rows[$mrow]->id; $i++ ) {
		$stats['group_names'][$i] = "";
		foreach ( $rows as $rid => $row ) {
			if ( $row->id == $i ) {
				$stats['group_names'][$i] = $row->name;
			}
		}
	}

	HTML_AcctExp::stats( $option, $page, $stats );
}

function aec_statrequest( $option, $type, $start, $end )
{
	$db = &JFactory::getDBO();

	$tree = new stdClass();

	switch ( $type ) {
		case 'sales':
			$tree = array();

			if ( empty( $end ) ) {
				$end = date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
			}

			$query = 'SELECT `id`'
					. ' FROM #__acctexp_log_history'
					. ' WHERE transaction_date >= \'' . $start . '\''
					. ' AND transaction_date <= \'' . $end . '\''
					. ' ORDER BY transaction_date ASC'
					;
			$db->setQuery( $query );
			$entries = xJ::getDBArray( $db );

			if ( empty( $entries ) ) {
				echo json_encode( $tree );exit;
			}

			$historylist = array();
			$groups = array();
			foreach ( $entries as $id ) {
				$entry = new logHistory();
				$entry->load( $id );
				$entry->amount = AECToolbox::correctAmount( $entry->amount );

				$refund = false;

				if ( is_array( $entry->response ) && !empty( $entry->response ) ) {
					$filter = array( 'new_case', 'subscr_signup', 'paymentreview', 'subscr_eot', 'subscr_failed', 'subscr_cancel', 'Pending', 'Denied' );

					$refund = false;
					foreach ( $entry->response as $v ) {
						if ( in_array( $v, $filter ) ) {
							continue 2;
						} elseif ( ( $v == 'refund' ) || ( $v == 'Reversed' ) || ( $v == 'Refunded' ) ) {
							$refund = true;
						}
					}
				} else {
					continue;
				}

				$pgroups = ItemGroupHandler::parentGroups( $entry->plan_id );

				if ( empty( $pgroups[0] ) ) {
					$pgroups[0] = 0;
				}

				if ( !in_array( $pgroups[0], $groups ) ) {
					$groups[] = $pgroups[0];
				}

				$sale			= new stdClass();
				$sale->id		= $id;
				//$sale->invoice	= $entry->invoice_number;
				$sale->date		= $entry->transaction_date;
				//$sale->datejs	= date( 'F d, Y H:i:s', strtotime( $entry->transaction_date ) );
				$sale->plan		= $entry->plan_id;
				$sale->group	= $pgroups[0];
				$sale->amount	= $refund ? (-$entry->amount) : $entry->amount;

				$tree[] = $sale;
			}

			break;
	}

	echo json_encode( $tree );exit;
}

function aec_stats2( $option )
{
	$stats = null;

	HTML_AcctExp::stats2( $option, $stats );
}

function quicklookup( $option )
{
	$db = &JFactory::getDBO();

	$searcc	= trim( aecGetParam( 'search', 0 ) );

	if ( empty( $searcc ) ) {
		return false;
	}

	$search = xJ::escape( $db, strtolower( $searcc ) );

	$s = AECToolbox::searchUser( $search );

	if ( !empty( $s ) ) {
		if ( is_array( $s ) ) {
			$return = array();
			foreach ( $s as $user ) {
				$JTableUser = new cmsUser();
				$JTableUser->load( $user );
				$userlink = '<div class="lookupresult">';
				$userlink .= '<a href="';
				$userlink .= JURI::base() . 'index.php?option=com_acctexp&amp;task=editMembership&amp;userid=' . $JTableUser->id;
				$userlink .= '">';
				$userlink .= str_replace( $search, '<span class="search-match">' . $search . '</span>', $JTableUser->name ) . ' (' . str_replace( $search, '<span class="search-match">' . $search . '</span>', $JTableUser->username ) . ')';
				$userlink .= '</a>';
				$userlink .= '</div>';

				$return[] = $userlink;
			}

			return '<div class="lookupresults">' . implode( $return ) . '</div>';
		} else {
			return $s;
		}
	}

	return false;
}

function hackcorefile( $option, $filename, $check_hack, $undohack, $checkonly=false )
{
	$db = &JFactory::getDBO();

	$app = JFactory::getApplication();

	$aec_hack_start				= "// AEC HACK %s START" . "\n";
	$aec_hack_end				= "// AEC HACK %s END" . "\n";

	$aec_condition_start		= 'if (file_exists( JPATH_ROOT."/components/com_acctexp/acctexp.class.php" )) {' . "\n";

	$aec_condition_end			= '}' . "\n";

	$aec_include_class			= 'include_once(JPATH_SITE . "/components/com_acctexp/acctexp.class.php");' . "\n";

	$aec_verification_check		= "AECToolBox::VerifyUsername( %s );" . "\n";
	$aec_userchange_clause		= '$mih = new microIntegrationHandler();' . "\n" . '$mih->userchange($row, $_POST, \'%s\');' . "\n";
	$aec_userchange_clauseCB12	= '$mih = new microIntegrationHandler();' . "\n" . '$mih->userchange($userComplete, $_POST, \'%s\');' . "\n";
	$aec_userchange_clause15	= '$mih = new microIntegrationHandler();' . "\n" . '$mih->userchange($userid, $post, \'%s\');' . "\n";
	$aec_userregchange_clause15	= '$mih = new microIntegrationHandler();' . "\n" . '$mih->userchange($user, $post, \'%s\');' . "\n";

	$aec_global_call			= "\n";

	$aec_redirect_notallowed	= 'aecRedirect( $mosConfig_live_site . "/index.php?option=com_acctexp&task=NotAllowed" );' . "\n";
	$aec_redirect_notallowed15	= '$app = JFactory::getApplication();' . "\n" . '$app->redirect( "index.php?option=com_acctexp&task=NotAllowed" );' . "\n";

	$aec_redirect_subscribe		= 'aecRedirect( JURI::root() . \'index.php?option=com_acctexp&task=subscribe\' );' . "\n";

	$aec_normal_hack = $aec_hack_start
					. $aec_global_call
					. $aec_condition_start
					. $aec_redirect_notallowed
					. $aec_condition_end
					. $aec_hack_end;

	$aec_jhack1 = $aec_hack_start
					. 'function mosNotAuth($override=false) {' . "\n"
					. $aec_global_call
					. $aec_condition_start
					. 'if (!$override) {' . "\n"
					. $aec_redirect_notallowed
					. $aec_condition_end
					. $aec_condition_end
					. $aec_hack_end;

	$aec_jhack2 = $aec_hack_start
					. $aec_global_call
					. $aec_condition_start
					. $aec_redirect_notallowed
					. $aec_condition_end
					. $aec_hack_end;

	$aec_jhack3 = $aec_hack_start
					. $aec_global_call
					. $aec_condition_start
					. $aec_include_class
					. sprintf( $aec_verification_check, '$credentials[\'username\']' )
					. $aec_condition_end
					. $aec_hack_end;

	$aec_cbmhack =	$aec_hack_start
					. "mosNotAuth(true);" . "\n"
					. $aec_hack_end;

	$aec_uchangehack =	$aec_hack_start
						. $aec_global_call
						. $aec_condition_start
						. $aec_include_class
						. $aec_userchange_clause
						. $aec_condition_end
						. $aec_hack_end;

	$aec_uchangehackCB12 = str_replace( '$row', '$userComplete', $aec_uchangehack );
	$aec_uchangehackCB12x = str_replace( '$row', '$this', $aec_uchangehack );

	$aec_uchangehackCB12 =	$aec_hack_start
						. $aec_global_call
						. $aec_condition_start
						. $aec_include_class
						. $aec_userchange_clauseCB12
						. $aec_condition_end
						. $aec_hack_end;

	$aec_uchangehack15 =	$aec_hack_start
						. $aec_global_call
						. $aec_condition_start
						. $aec_include_class
						. $aec_userregchange_clause15
						. $aec_condition_end
						. $aec_hack_end;

	$aec_uchangereghack15 =	$aec_hack_start
						. $aec_global_call
						. $aec_condition_start
						. $aec_include_class
						. $aec_userchange_clause15
						. $aec_condition_end
						. $aec_hack_end;

	$aec_rhackbefore =	$aec_hack_start
						. $aec_global_call
						. $aec_condition_start
						. 'if (!isset($_POST[\'planid\'])) {' . "\n"
						. $aec_include_class
						. 'aecRedirect(JURI::root() . "index.php?option=com_acctexp&amp;task=subscribe");' . "\n"
						. $aec_condition_end
						. $aec_condition_end
						. $aec_hack_end;

	$aec_rhackbefore_fix = str_replace("planid", "usage", $aec_rhackbefore);

	$aec_rhackbefore2 =	$aec_hack_start
						. $aec_global_call . '$app = JFactory::getApplication();' . "\n"
						. $aec_condition_start
						. 'if (!isset($_POST[\'usage\'])) {' . "\n"
						. $aec_include_class
						. 'aecRedirect(JURI::root() . "index.php?option=com_acctexp&amp;task=subscribe");' . "\n"
						. $aec_condition_end
						. $aec_condition_end
						. $aec_hack_end;

	$aec_optionhack =	$aec_hack_start
						. $aec_global_call
						. $aec_condition_start
						. '$option = "com_acctexp";' . "\n"
						. $aec_condition_end
						. $aec_hack_end;

	$aec_regvarshack =	'<?php' . "\n"
						. $aec_hack_start
						. $aec_global_call
						. $aec_condition_start
						. '?>' . "\n"
						. '<input type="hidden" name="planid" value="<?php echo $_POST[\'planid\'];?>" />' . "\n"
						. '<input type="hidden" name="processor" value="<?php echo $_POST[\'processor\'];?>" />' . "\n"
						. '<?php' . "\n"
						. 'if ( isset( $_POST[\'recurring\'] ) ) {'
						. '?>' . "\n"
						. '<input type="hidden" name="recurring" value="<?php echo $_POST[\'recurring\'];?>" />' . "\n"
						. '<?php' . "\n"
						. '}' . "\n"
						. $aec_condition_end
						. $aec_hack_end
						. '?>' . "\n";

	$aec_regvarshack_fix = str_replace( 'planid', 'usage', $aec_regvarshack);

	$aec_regvarshack_fixcb = $aec_hack_start
						. $aec_global_call
						. $aec_condition_start
						. 'if ( isset( $_POST[\'usage\'] ) ) {' . "\n"
						. '$regFormTag .= \'<input type="hidden" name="usage" value="\' . $_POST[\'usage\'] . \'" />\';' . "\n"
						. '}' . "\n"
						. 'if ( isset( $_POST[\'processor\'] ) ) {' . "\n"
						. '$regFormTag .= \'<input type="hidden" name="processor" value="\' . $_POST[\'processor\'] . \'" />\';' . "\n"
						. '}' . "\n"
						. 'if ( isset( $_POST[\'recurring\'] ) ) {' . "\n"
						. '$regFormTag .= \'<input type="hidden" name="recurring" value="\' . $_POST[\'recurring\'] . \'" />\';' . "\n"
						. '}' . "\n"
						. $aec_condition_end
						. $aec_hack_end
						;

	$aec_regredirect = $aec_hack_start
					. $aec_global_call
					. $aec_condition_start
					. $aec_redirect_subscribe
					. $aec_condition_end
					. $aec_hack_end;

	$juser_blind = $aec_hack_start
					. 'case \'blind\':'. "\n"
					. 'break;'. "\n"
					. $aec_hack_end;

	$aec_j15hack1 =  $aec_hack_start
					. 'if ( $error->message == JText::_("ALERTNOTAUTH") ) {'
					. $aec_condition_start
					. $aec_redirect_notallowed15
					. $aec_condition_end
					. $aec_condition_end
					. $aec_hack_end;

	$n = 'errorphp';
	$hacks[$n]['name']			=	'error.php ' . JText::_('AEC_HACK_HACK') . ' #1';
	$hacks[$n]['desc']			=	JText::_('AEC_HACKS_NOTAUTH');
	$hacks[$n]['type']			=	'file';
	$hacks[$n]['filename']		=	JPATH_SITE . '/libraries/joomla/error/error.php';
	$hacks[$n]['read']			=	'// Initialize variables';
	$hacks[$n]['insert']		=	sprintf( $aec_j15hack1, $n, $n ) . "\n" . $hacks[$n]['read'];
	$hacks[$n]['legacy']		=	1;

	$n = 'joomlaphp4';
	$hacks[$n]['name']			=	'authentication.php';
	$hacks[$n]['desc']			=	JText::_('AEC_HACKS_LEGACY_PLUGIN');
	$hacks[$n]['uncondition']	=	'joomlaphp';
	$hacks[$n]['type']			=	'file';
	$hacks[$n]['filename']		=	JPATH_SITE . '/libraries/joomla/user/authentication.php';
	$hacks[$n]['read'] 			=	'if(empty($response->username)) {';
	$hacks[$n]['insert']		=	sprintf($aec_jhack3, $n, $n) . "\n" . $hacks[$n]['read'];
	$hacks[$n]['legacy']		=	1;

	if ( GeneralInfoRequester::detect_component( 'UHP2' ) ) {
		$n = 'uhp2menuentry';
		$hacks[$n]['name']			=	JText::_('AEC_HACKS_UHP2');
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_UHP2_DESC');
		$hacks[$n]['uncondition']	=	'uhp2managephp';
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['filename']		=	JPATH_SITE . '/modules/mod_uhp2_manage.php';
		$hacks[$n]['read']			=	'<?php echo "$settings"; ?></a>';
		$hacks[$n]['insert']		=	sprintf( $hacks[$n]['read'] . "\n</li>\n<?php " . $aec_hack_start . '?>'
		. '<li class="latest<?php echo $moduleclass_sfx; ?>">'
		. '<a href="index.php?option=com_acctexp&task=subscriptionDetails" class="latest<?php echo $moduleclass_sfx; ?>">'
		. JText::_('AEC_SPEC_MENU_ENTRY') . '</a>'."\n<?php ".$aec_hack_end."?>", $n, $n );
	}

	if ( GeneralInfoRequester::detect_component( 'CB1.2' ) ) {
		$n = 'comprofilerphp2';
		$hacks[$n]['name']			=	'comprofiler.php ' . JText::_('AEC_HACK_HACK') . ' #2';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_CB2');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_comprofiler/comprofiler.php';
		$hacks[$n]['read']			=	'function registerForm( $option, $emailpass, $regErrorMSG = null ) {';
		$hacks[$n]['insert']		=	$hacks[$n]['read'] . "\n" . sprintf($aec_optionhack, $n, $n);
		$hacks[$n]['legacy']		=	1;

		$n = 'comprofilerphp6';
		$hacks[$n]['name']			=	'comprofiler.php ' . JText::_('AEC_HACK_HACK') . ' #6';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_CB6');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_comprofiler/comprofiler.php';
		$hacks[$n]['read']			=	'HTML_comprofiler::registerForm( $option, $emailpass, $userComplete, $regErrorMSG );';
		$hacks[$n]['insert']		=	sprintf($aec_rhackbefore_fix, $n, $n) . "\n" . $hacks[$n]['read'];
		$hacks[$n]['legacy']		=	1;

		$n = 'comprofilerhtml2';
		$hacks[$n]['name']			=	'comprofiler.html.php ' . JText::_('AEC_HACK_HACK') . ' #2';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_CB_HTML2');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_comprofiler/comprofiler.html.php';
		$hacks[$n]['read']			=	'echo HTML_comprofiler::_cbTemplateRender( $user, \'RegisterForm\'';
		$hacks[$n]['insert']		=	sprintf($aec_regvarshack_fixcb, $n, $n) . "\n" . $hacks[$n]['read'];
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_LEGACY');
		$hacks[$n]['legacy']		=	1;

	} elseif ( GeneralInfoRequester::detect_component( 'CB' ) ) {
		$n = 'comprofilerphp2';
		$hacks[$n]['name']			=	'comprofiler.php ' . JText::_('AEC_HACK_HACK') . ' #2';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_CB2');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_comprofiler/comprofiler.php';
		$hacks[$n]['read']			=	'if ($regErrorMSG===null) {';
		$hacks[$n]['insert']		=	sprintf($aec_optionhack, $n, $n) . "\n" . $hacks[$n]['read'];

		$n = 'comprofilerphp6';
		$hacks[$n]['name']			=	'comprofiler.php ' . JText::_('AEC_HACK_HACK') . ' #6';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_CB6');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['condition']		=	'comprofilerphp2';
		$hacks[$n]['uncondition']	=	'comprofilerphp3';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_comprofiler/comprofiler.php';
		$hacks[$n]['read']			=	'HTML_comprofiler::registerForm';
		$hacks[$n]['insert']		=	sprintf($aec_rhackbefore_fix, $n, $n) . "\n" . $hacks[$n]['read'];

		$n = 'comprofilerhtml2';
		$hacks[$n]['name']			=	'comprofiler.html.php ' . JText::_('AEC_HACK_HACK') . ' #2';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_CB_HTML2');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['uncondition']	=	'comprofilerhtml';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_comprofiler/comprofiler.html.php';
		$hacks[$n]['read']			=	'<input type="hidden" name="task" value="saveregisters" />';
		$hacks[$n]['insert']		=	$hacks[$n]['read'] . "\n" . sprintf($aec_regvarshack_fix, $n, $n);

	} elseif ( GeneralInfoRequester::detect_component( 'CBE' ) ) {
		$n = 'comprofilerphp2';
		$hacks[$n]['name']			=	'comprofiler.php ' . JText::_('AEC_HACK_HACK') . ' #2';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_CB2');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_comprofiler/comprofiler.php';
		$hacks[$n]['read']			=	'$rowFieldValues=array();';
		$hacks[$n]['insert']		=	sprintf($aec_optionhack, $n, $n) . "\n" . $hacks[$n]['read'];

		$n = 'comprofilerphp6';
		$hacks[$n]['name']			=	'comprofiler.php ' . JText::_('AEC_HACK_HACK') . ' #6';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_CB6');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['condition']		=	'comprofilerphp2';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_comprofiler/comprofiler.php';
		$hacks[$n]['read']			=	'HTML_comprofiler::registerForm';
		$hacks[$n]['insert']		=	sprintf($aec_rhackbefore2, $n, $n) . "\n" . $hacks[$n]['read'];

		$n = 'comprofilerhtml2';
		$hacks[$n]['name']			=	'comprofiler.html.php ' . JText::_('AEC_HACK_HACK') . ' #2';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_CB_HTML2');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['uncondition']	=	'comprofilerhtml';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_comprofiler/comprofiler.html.php';
		$hacks[$n]['read']			=	'<input type="hidden" name="task" value="saveRegistration" />';
		$hacks[$n]['insert']		=	$hacks[$n]['read'] . "\n" . sprintf($aec_regvarshack_fix, $n, $n);
	} elseif ( GeneralInfoRequester::detect_component( 'JUSER' ) ) {
		$n = 'juserhtml1';
		$hacks[$n]['name']			=	'juser.html.php ' . JText::_('AEC_HACK_HACK') . ' #1';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_JUSER_HTML1');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_juser/juser.html.php';
		$hacks[$n]['read']			=	'<input type="hidden" name="option" value="com_juser" />';
		$hacks[$n]['insert']		=	sprintf($aec_regvarshack_fix, $n, $n) . "\n" . '<input type="hidden" name="option" value="com_acctexp" />';

		$n = 'juserphp1';
		$hacks[$n]['name']			=	'juser.php ' . JText::_('AEC_HACK_HACK') . ' #1';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_JUSER_PHP1');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_juser/juser.php';
		$hacks[$n]['read']			=	'HTML_JUser::userEdit( $row, $option, $params, $ext_row, \'saveUserRegistration\' );';
		$hacks[$n]['insert']		=	sprintf($aec_rhackbefore_fix, $n, $n) . "\n" . $hacks[$n]['read'];

		$n = 'juserphp2';
		$hacks[$n]['name']			=	'juser.php ' . JText::_('AEC_HACK_HACK') . ' #2';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_JUSER_PHP2');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_juser/juser.php';
		$hacks[$n]['read']			=	'default:';
		$hacks[$n]['insert']		=	sprintf($juser_blind, $n, $n) . "\n" . $hacks[$n]['read'];
	} else {

		$n = 'registrationhtml2';
		$hacks[$n]['name']			=	'registration.html.php ' . JText::_('AEC_HACK_HACK') . ' #2';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_LEGACY');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['uncondition']	=	'registrationhtml';
		$hacks[$n]['condition']		=	'registrationphp2';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_user/views/register/tmpl/default.php';
		$hacks[$n]['read']			=	'<input type="hidden" name="task" value="register_save" />';
		$hacks[$n]['insert']		=	$hacks[$n]['read'] . "\n" . sprintf($aec_regvarshack_fix, $n, $n);
		$hacks[$n]['legacy']		=	1;

		$n = 'registrationphp6';
		$hacks[$n]['name']			=	'user.php';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_REG5');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['uncondition']	=	'registrationphp5';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_user/controller.php';
		$hacks[$n]['read']			=	'JRequest::setVar(\'view\', \'register\');';
		$hacks[$n]['insert']		=	$hacks[$n]['read'] . "\n" . sprintf($aec_regredirect, $n, $n);
		$hacks[$n]['legacy']		=	1;
	}

	if ( GeneralInfoRequester::detect_component( 'anyCB' ) ) {
		if ( GeneralInfoRequester::detect_component( 'CB1.2' ) ) {
			$n = 'comprofilerphp7';
			$hacks[$n]['name']			=	'comprofiler.php ' . JText::_('AEC_HACK_HACK') . ' #7';
			$hacks[$n]['desc']			=	JText::_('AEC_HACKS_MI1');
			$hacks[$n]['type']			=	'file';
			$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_comprofiler/comprofiler.php';
			$hacks[$n]['read']			=	'$_PLUGINS->trigger( \'onAfterUserRegistrationMailsSent\',';
			$hacks[$n]['insert']		=	sprintf( $aec_uchangehackCB12, $n, 'registration', $n ) . "\n" . $hacks[$n]['read'];
			$hacks[$n]['legacy']		=	1;

			$n = 'comprofilerphp8';
			$hacks[$n]['name']			=	'comprofiler.php ' . JText::_('AEC_HACK_HACK') . ' #8';
			$hacks[$n]['desc']			=	JText::_('AEC_HACKS_MI1');
			$hacks[$n]['type']			=	'file';
			$hacks[$n]['filename']		=	JPATH_SITE . '/administrator/components/com_comprofiler/library/cb/cb.tables.php';
			$hacks[$n]['read']			=	'$_PLUGINS->trigger( \'onAfterUserUpdate\', array( &$this, &$this, true ) );';
			$hacks[$n]['insert']		=	$hacks[$n]['read'] . "\n" . sprintf( $aec_uchangehackCB12x, $n, 'user', $n );
			$hacks[$n]['legacy']		=	1;
		} else {
			$n = 'comprofilerphp4';
			$hacks[$n]['name']			=	'comprofiler.php ' . JText::_('AEC_HACK_HACK') . ' #4';
			$hacks[$n]['desc']			=	JText::_('AEC_HACKS_MI1');
			$hacks[$n]['type']			=	'file';
			$hacks[$n]['filename']		=	JPATH_SITE . "/components/com_comprofiler/comprofiler.php";
			$hacks[$n]['read']			=	'$_PLUGINS->trigger( \'onAfterUserRegistrationMailsSent\',';
			$hacks[$n]['insert']		=	sprintf($aec_uchangehack, $n, "user", $n) . "\n" . $hacks[$n]['read'];
			$hacks[$n]['legacy']		=	1;

			$n = 'comprofilerphp5';
			$hacks[$n]['name']			=	'comprofiler.php ' . JText::_('AEC_HACK_HACK') . ' #5';
			$hacks[$n]['desc']			=	JText::_('AEC_HACKS_MI2');
			$hacks[$n]['type']			=	'file';
			$hacks[$n]['filename']		=	JPATH_SITE . "/components/com_comprofiler/comprofiler.php";
			$hacks[$n]['read']			=	'$_PLUGINS->trigger( \'onAfterUserUpdate\', array($row, $rowExtras, true));';
			$hacks[$n]['insert']		=	$hacks[$n]['read'] . "\n" . sprintf($aec_uchangehack, $n, "registration",$n);
			$hacks[$n]['legacy']		=	1;

			$n = 'comprofilerphp7';
			$hacks[$n]['name']			=	'comprofiler.php ' . JText::_('AEC_HACK_HACK') . ' #7';
			$hacks[$n]['desc']			=	JText::_('AEC_HACKS_MI1');
			$hacks[$n]['type']			=	'file';
			$hacks[$n]['uncondition']	=	'comprofilerphp4';
			$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_comprofiler/comprofiler.php';
			$hacks[$n]['read']			=	'$_PLUGINS->trigger( \'onAfterUserRegistrationMailsSent\',';
			$hacks[$n]['insert']		=	sprintf( $aec_uchangehack, $n, 'registration', $n ) . "\n" . $hacks[$n]['read'];

			$n = 'comprofilerphp8';
			$hacks[$n]['name']			=	'comprofiler.php ' . JText::_('AEC_HACK_HACK') . ' #8';
			$hacks[$n]['desc']			=	JText::_('AEC_HACKS_MI1');
			$hacks[$n]['type']			=	'file';
			$hacks[$n]['uncondition']	=	'comprofilerphp5';
			$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_comprofiler/comprofiler.php';
			$hacks[$n]['read']			=	'$_PLUGINS->trigger( \'onAfterUserUpdate\', array($row, $rowExtras, true));';
			$hacks[$n]['insert']		=	$hacks[$n]['read'] . "\n" . sprintf( $aec_uchangehack, $n, 'user', $n );
		}
	} else {
		$n = 'userphp';
		$hacks[$n]['name']			=	'user.php';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_LEGACY');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_user/controller.php';
		$hacks[$n]['read']			=	'if ($model->store($post)) {';
		$hacks[$n]['insert']		=	sprintf( $aec_uchangehack15, $n, "user", $n ) . "\n" . $hacks[$n]['read'];
		$hacks[$n]['legacy']		=	1;

		$n = 'registrationphp1';
		$hacks[$n]['name']			=	'registration.php ' . JText::_('AEC_HACK_HACK') . ' #1';
		$hacks[$n]['desc']			=	JText::_('AEC_HACKS_LEGACY');
		$hacks[$n]['type']			=	'file';
		$hacks[$n]['filename']		=	JPATH_SITE . '/components/com_user/controller.php';
		$hacks[$n]['read']			=	'UserController::_sendMail($user, $password);';
		$hacks[$n]['insert']		=	$hacks[$n]['read'] . "\n" . sprintf( $aec_uchangereghack15, $n, "registration", $n );
		$hacks[$n]['legacy']		=	1;
	}

	$n = 'adminuserphp';
	$hacks[$n]['name']			=	'admin.user.php';
	$hacks[$n]['desc']			=	JText::_('AEC_HACKS_LEGACY');
	$hacks[$n]['type']			=	'file';
	$hacks[$n]['filename']		=	JPATH_SITE . '/administrator/components/com_users/controller.php';
	$hacks[$n]['read']			=	'if (!$user->save())';
	$hacks[$n]['insert']		=	sprintf( $aec_uchangehack15, $n, 'adminuser', $n ) . "\n" . $hacks[$n]['read'];
	$hacks[$n]['legacy']	=	1;

	if ( GeneralInfoRequester::detect_component( 'CBM' ) ) {
		if ( !GeneralInfoRequester::detect_component( 'CB1.2' ) ) {
			$n = 'comprofilermoderator';
			$hacks[$n]['name']			=	'comprofilermoderator.php';
			$hacks[$n]['desc']			=	JText::_('AEC_HACKS_CBM');
			$hacks[$n]['type']			=	'file';
			$hacks[$n]['filename']		=	JPATH_SITE . '/modules/mod_comprofilermoderator.php';
			$hacks[$n]['read']			=	'mosNotAuth();';
			$hacks[$n]['insert']		=	sprintf( $aec_cbmhack, $n, $n );
		}
	}

	$mih = new microIntegrationHandler();
	$new_hacks = $mih->getHacks();

	if ( is_array( $new_hacks ) ) {
		$hacks = array_merge( $hacks, $new_hacks );
	}

	// Receive the status for the hacks
	foreach ( $hacks as $name => $hack ) {

		$hacks[$name]['status'] = 0;

		if ( !empty( $hack['filename'] ) ) {
			if ( !file_exists( $hack['filename'] ) ) {
				continue;
			}
		}

		if ( $hack['type'] ) {
			switch( $hack['type'] ) {
				case 'file':
					if ( $hack['filename'] != 'UNKNOWN' ) {
						$originalFileHandle = fopen( $hack['filename'], 'r' );
						$oldData			= fread( $originalFileHandle, filesize($hack['filename'] ) );
						fclose( $originalFileHandle );

						if ( strpos( $oldData, 'AEC HACK START' ) || strpos( $oldData, 'AEC CHANGE START' )) {
							$hacks[$name]['status'] = 'legacy';
						} else {
							if ( ( strpos( $oldData, 'AEC HACK ' . $name . ' START' ) > 0 ) || ( strpos( $oldData, 'AEC CHANGE ' . $name . ' START' ) > 0 )) {
								$hacks[$name]['status'] = 1;
							}
						}

						if ( function_exists( 'posix_getpwuid' ) ) {
							$hacks[$name]['fileinfo'] = posix_getpwuid( fileowner( $hack['filename'] ) );
						}
					}
					break;

				case 'menuentry':
					$count = 0;
					$query = 'SELECT COUNT(*)'
							. ' FROM #__menu'
							. ' WHERE `link` = \'' . JURI::root()  . '/index.php?option=com_acctexp&task=subscriptionDetails\''
							;
					$db->setQuery( $query );
					$count = $db->loadResult();

					if ( $count ) {
						$hacks[$name]['status'] = 1;
					}
					break;
			}
		}
	}

	if ( $checkonly ) {
		return $hacks[$filename]['status'];
	}

	// Commit the hacks
	if ( !$check_hack ) {

		switch( $hacks[$filename]['type'] ) {
			case 'file':
				// mic: fix if CMS is not Joomla or Mambo
				if ( $hack['filename'] != 'UNKNOWN' ) {
					$originalFileHandle = fopen( $hacks[$filename]['filename'], 'r' ) or die ("Cannot open $originalFile<br>");
					// Transfer File into variable $oldData
					$oldData = fread( $originalFileHandle, filesize( $hacks[$filename]['filename'] ) );
					fclose( $originalFileHandle );

					if ( !$undohack ) { // hack
						$newData			= str_replace( $hacks[$filename]['read'], $hacks[$filename]['insert'], $oldData );

							//make a backup
							if ( !backupFile( $hacks[$filename]['filename'], $hacks[$filename]['filename'] . '.aec-backup' ) ) {
							// Echo error message
							}

					} else { // undo hack
						if ( strcmp( $hacks[$filename]['status'], 'legacy' ) === 0 ) {
							$newData = preg_replace( '/\/\/.AEC.(HACK|CHANGE).START\\n.*\/\/.AEC.(HACK|CHANGE).END\\n/s', $hacks[$filename]['read'], $oldData );
						} else {
							if ( strpos( $oldData, $hacks[$filename]['insert'] ) ) {
								if ( isset( $hacks[$filename]['oldread'] ) && isset( $hacks[$filename]['oldinsert'] ) ) {
									$newData = str_replace( $hacks[$filename]['oldinsert'], $hacks[$filename]['oldread'], $oldData );
								}

								$newData = str_replace( $hacks[$filename]['insert'], $hacks[$filename]['read'], $oldData );
							} else {
								$newData = preg_replace( '/\/\/.AEC.(HACK|CHANGE).' . $filename . '.START\\n.*\/\/.AEC.(HACK|CHANGE).' . $filename . '.END\\n/s', $hacks[$filename]['read'], $oldData );
							}
						}
					}

						$oldperms = fileperms( $hacks[$filename]['filename'] );
						chmod( $hacks[$filename]['filename'], $oldperms | 0222 );

						if ( $fp = fopen( $hacks[$filename]['filename'], 'wb' ) ) {
								fwrite( $fp, $newData, strlen( $newData ) );
								fclose( $fp );
								chmod( $hacks[$filename]['filename'], $oldperms );
						}
				}
				break;
		}
	}

	return $hacks;
}

function backupFile( $file, $file_new )
{
		if ( !copy( $file, $file_new ) ) {
				return false;
		}
		return true;
}

function importData( $option )
{
	$show_form = false;
	$done = false;

	$temp_dir = JPATH_SITE . '/tmp';

	$file_list = xJUtility::getFileArray( $temp_dir, 'csv', false, true );

	$params = array();
	$lists = array();

	if ( !empty( $_FILES ) ) {
		if ( strpos( $_FILES['import_file']['name'], '.csv' ) === false ) {
			$len = strlen( $_FILES['import_file']['name'] );

			$last = strrpos( $_FILES['import_file']['name'], '.' );

			$filename = substr( $_FILES['import_file']['name'], 0, $last ) . '.csv';
		} else {
			$filename = $_FILES['import_file']['name'];
		}

		$destination = $temp_dir . '/' . $filename;

		if ( move_uploaded_file( $_FILES['import_file']['tmp_name'], $destination ) ) {
			$file_select = $filename;
		}
	} else {

	}

	if ( empty( $file_select ) ) {
		$file_select = aecGetParam( 'file_select', '' );
	}

	if ( empty( $file_select ) ) {
		$show_form = true;

		$params['file_select']			= array( 'list', '' );
		$params['MAX_FILE_SIZE']		= array( 'hidden', '5120000' );
		$params['import_file']			= array( 'file', 'Upload', 'Upload a file and select it for importing', '' );

		$file_htmllist		= array();
		$file_htmllist[]	= JHTML::_('select.option', '', JText::_('AEC_CMN_NONE_SELECTED') );

		if ( !empty( $file_list ) ) {
			foreach ( $file_list as $name ) {
				$file_htmllist[] = JHTML::_('select.option', $name, $name );
			}
		}

		$lists['file_select'] = JHTML::_('select.genericlist', $file_htmllist, 'file_select', 'size="' . min( ( count( $file_htmllist ) + 1 ), 25 ) . '"', 'value', 'text', 0 );
	} else {
		$options = array();

		if ( !empty( $_POST['assign_plan'] ) ) {
			$options['assign_plan'] = $_POST['assign_plan'];
		}

		$import = new aecImport( $temp_dir . '/' . $file_select, $options );

		if ( !$import->read() ) {
			die( 'could not read file' );
		}

		$import->parse();

		if ( !empty( $import->rows ) ) {
			$params['file_select']		= array( 'hidden', $file_select );

			if ( !isset( $_POST['convert_field_0'] ) ) {
				$fields = array(	"id" => "User ID",
									"name" => "User Full Name",
									"username" => "Username",
									"email" => "User Email",
									"password" => "Password",
									"plan_id" => "Payment Plan ID",
									"invoice_number" => "Invoice Number",
									"expiration" => "Membership Expiration"
								);

				$field_htmllist		= array();
				$field_htmllist[]	= JHTML::_('select.option', 0, 'Ignore' );

				foreach ( $fields as $name => $longname ) {
					$field_htmllist[] = JHTML::_('select.option', $name, $longname );
				}

				$cols = count( $import->rows[0] );

				$columns = array();
				for ( $i=0; $i<$cols; $i++ ) {
					$columns[] = 'convert_field_'.$i;

					$params['convert_field_'.$i] = array( 'list', '', '', '' );

					$lists['convert_field_'.$i] = JHTML::_('select.genericlist', $field_htmllist, 'convert_field_'.$i, 'size="1" class="span2"', 'value', 'text', 0 );
				}

				$rows_count = count( $import->rows );

				$rowcount = min( $rows_count, 5 );

				$rows = array();
				for ( $i=0; $i<$rowcount; $i++ ) {
					$rows[] = $import->rows[$i];
				}

				$params['assign_plan'] = array( 'list', 'Assign Plan', 'Assign users to a specific payment plan. Is overridden if you provide an individual plan ID with the "Payment Plan ID" field assignment.' );

				$available_plans	= SubscriptionPlanHandler::getActivePlanList();

				$lists['assign_plan'] = JHTML::_('select.genericlist', $available_plans, 'assign_plan', 'size="5"', 'value', 'text', 0 );
			} else {
				$import->getConversionList();

				$import->import();

				$done = true;
			}
		} else {
			die( 'could not find any entries in this file' );
		}
	}

	$settingsparams = array();

	$settings = new aecSettings ( 'import', 'general' );
	$settings->fullSettingsArray( $params, $settingsparams, $lists ) ;

	// Call HTML Class
	$aecHTML = new aecHTML( $settings->settings, $settings->lists );

	$aecHTML->form		= $show_form;
	$aecHTML->done		= $done;

	if ( !empty( $import->errors ) ) {
		$aecHTML->errors	= $import->errors;
	}

	if ( !$show_form && !$done ) {
		$aecHTML->user_rows = $rows;
		$aecHTML->user_rows_count = $rows_count;
		$aecHTML->columns = $columns;
	}

	HTML_AcctExp::import( $option, $aecHTML );
}

function exportData( $option, $type, $cmd=null )
{
	$db = &JFactory::getDBO();

	$cmd_save = ( strcmp( 'save', $cmd ) === 0 );
	$cmd_apply = ( strcmp( 'apply', $cmd ) === 0 );
	$cmd_load = ( strcmp( 'load', $cmd ) === 0 );
	$cmd_export = ( strcmp( 'export', $cmd ) === 0 );
	$use_original = 0;

	$system_values = array();
	$filter_values = array();
	$options_values = array();
	$params_values = array();

	if ( $type == 'sales' ) {
		$getpost = array(	'system' => array( 'selected_export', 'delete', 'save', 'save_name' ),
							'filter' => array( 'date_start', 'date_end', 'method', 'planid', 'groupid', 'status', 'orderby' ),
							'options' => array( 'collate', 'breakdown', 'breakdown_custom' ),
							'params' => array( 'export_method' )
						);

		$pf = 8;
	} else {
		$getpost = array(	'system' => array( 'selected_export', 'delete', 'save', 'save_name' ),
							'filter' => array( 'planid', 'groupid', 'status', 'orderby' ),
							'options' => array( 'rewrite_rule' ),
							'params' => array( 'export_method' )
						);

		$pf = 5;
	}

	$postfields = 0;
	foreach( $getpost as $name => $array ) {
		$field = $name . '_values';

		foreach( $array as $vname ) {
			$vvalue = aecGetParam( $vname, '' );
			 if ( !empty( $vvalue ) ) {
				 ${$field}[$vname] = $vvalue;

			 	$postfields++;
			 }
		}
	}

	if ( !empty( $params_values['export_method'] ) ) {
		$is_test = $params_values['export_method'] == 'test';
	} else {
		$is_test = false;
	}

	$lists = array();

	$pname = "";

	if ( !empty( $system_values['selected_export'] ) || $cmd_save || $cmd_apply || $is_test ) {
		$row = new aecExport( ( $type == 'sales' ) );
		if ( isset( $system_values['selected_export'] ) ) {
			$row->load( $system_values['selected_export'] );

			$pname = $row->name;
		} else {
			$row->load(0);
		}

		if ( !empty( $system_values['delete'] ) ) {
			// User wants to delete the entry
			$row->delete();
		} elseif ( ( $cmd_save || $cmd_apply ) && ( !empty( $system_values['selected_export'] ) || !empty( $system_values['save_name'] ) ) ) {
			// User wants to save an entry
			if ( !empty( $system_values['save'] ) ) {
				// But as a copy of another entry
				$row->load( 0 );
			}

			$row->save( $system_values['save_name'], $filter_values, $options_values, $params_values );

			if ( !empty( $system_values['save'] ) ) {
				$system_values['selected_export'] = $row->getMax();
			}
		} elseif ( ( $cmd_save || $cmd_apply ) && ( empty( $system_values['selected_export'] ) && !empty( $system_values['save_name'] ) && $system_values['save'] ) && !$is_test ) {
			// User wants to save a new entry
			$row->save( $system_values['save_name'], $filter_values, $options_values, $params_values );
		}  elseif ( $cmd_load || ( count($postfields) && ( $postfields <= $pf ) && ( $cmd_export || $is_test ) )  ) {
			if ( $row->id ) {
				// User wants to load an entry
				$filter_values = $row->filter;
				$options_values = $row->options;
				$params_values = $row->params;
				$pname = $row->name;

				$use_original = 1;
			}
		}
	}

	// Always store the last ten calls, but only if something is happening
	if ( $cmd_save || $cmd_apply || $cmd_export ) {
		$autorow = new aecExport( ( $type == 'sales' ) );
		$autorow->load(0);
		$autorow->save( 'Autosave', $filter_values, $options_values, $params_values, true );

		if ( isset( $row ) ) {
			if ( ( $autorow->filter == $row->filter ) && ( $autorow->options == $row->options ) && ( $autorow->params == $row->params ) ) {
				$use_original = 1;
			}
		}
	}

	$filters = array( 'planid', 'groupid', 'status' );

	foreach ( $filters as $filter ) {
		if ( !isset( $filter_values[$filter] ) ) {
			$filter_values[$filter] = array();

			continue;
		}

		if ( !is_array( $filter_values[$filter] ) ) {
			if ( !empty( $filter_values[$filter] ) ) {
				$filter_values[$filter] = array( $filter_values[$filter] );
			} else {
				$filter_values[$filter] = array();
			}
		}
	}

	if ( $is_test ) {
		$row->params['export_method'] = 'test';
	}

	// Create Parameters

	$params[] = array( 'userinfobox', 49 );

	if ( $type == 'members' ) {
		$params[] = array( 'userinfobox_sub', 'Compose Export' );
		$params['params_remap']		= array( 'subarea_change', 'params' );
		$params[] = array( 'div', '<div class="alert alert-info">' );
		$params[] = array( 'p', '<p>Take users that fit these criteria:</p>' );
		$params['groupid']			= array( 'list', '' );
		$params['planid']			= array( 'list', '' );
		$params['status']			= array( 'list', '' );
		$params[] = array( 'div_end', '' );
		$params[] = array( 'div', '<div class="alert alert-warning">' );
		$params[] = array( 'p', '<p>Order them like this:</p>' );
		$params['orderby']			= array( 'list', '' );
		$params[] = array( 'div_end', '' );
		$params[] = array( 'div', '<div class="alert alert-success">' );
		$params[] = array( 'p', '<p>And use these details for each line of the export:</p>' );
		$params['rewrite_rule']	= array( 'inputD', '[[user_id]];[[user_username]];[[subscription_expiration_date]]' );
		$params[] = array( '2div_end', '' );
	} else {
		$monthago = ( (int) gmdate('U') ) - ( 60*60*24 * 31 );

		$params[] = array( 'userinfobox_sub', 'Compose Export' );
		$params['params_remap']		= array( 'subarea_change', 'params' );
		$params[] = array( 'div', '<div class="alert alert-info">' );
		$params[] = array( 'p', '<p>Collect Sales Data from this range:</p>' );
		$params['date_start']		= array( 'list_date', date( 'Y-m-d', $monthago ) );
		$params['date_end']			= array( 'list_date', date( 'Y-m-d' ) );
		$params['method']			= array( 'list', '' );
		$params['planid']			= array( 'list', '' );
		$params['groupid']			= array( 'list', '' );
		$params[] = array( 'div_end', '' );
		$params[] = array( 'div', '<div class="alert alert-warning">' );
		$params[] = array( 'p', '<p>Collate it like this:</p>' );
		$params['collate']			= array( 'list', 'day' );
		$params[] = array( 'div_end', '' );
		$params[] = array( 'div', '<div class="alert alert-success">' );
		$params[] = array( 'p', '<p>Break down the data in each line like so:</p>' );
		$params['breakdown']		= array( 'list', 'month' );
		$params['breakdown_custom']	= array( 'inputD', '' );
		$params[] = array( '2div_end', '' );
	}

	if ( $type == 'members' ) {
		$params[] = array( 'userinfobox', 49 );
		$params[] = array( 'userinfobox_sub' );
		$rewriteswitches			= array( 'cms', 'user', 'subscription', 'plan', 'invoice' );
		$params = AECToolbox::rewriteEngineInfo( $rewriteswitches, $params );
		$params[] = array( 'div_end', '' );
		$params[] = array( '2div_end', '' );
	}

	$params[] = array( '2div_end', '' );

	$params[] = array( 'userinfobox', 49 );
	$params[] = array( 'userinfobox_sub', 'Save or Load Export Presets' );
	$params[] = array( 'div', '<div class="form-wide">' );
	$params['selected_export']	= array( 'list', '' );
	$params['delete']			= array( 'checkbox', 0 );
	$params['save']				= array( 'checkbox', 0 );
	$params['save_name']		= array( 'inputC', $pname );
	$params[] = array( 'div_end', '' );
	$params[] = array( 'div', '<div class="right-btns">' );
	$params[] = array( 'p', '<a class="btn btn-primary" onclick="javascript: submitbutton(\'loadExport' . $type . '\')" href="#">' . aecHTML::Icon( 'upload', true ) . '&nbsp;Load Preset</a>' );
	$params[] = array( 'p', '<a class="btn btn-success" onclick="javascript: submitbutton(\'applyExport' . $type . '\')" href="#">' . aecHTML::Icon( 'download', true ) . '&nbsp;Save Preset</a>' );
	$params[] = array( 'p', '<a class="btn danger" onclick="javascript: submitbutton(\'saveExport' . $type . '\')" href="#">' . aecHTML::Icon( 'download-alt' ) . '&nbsp;Save Preset &amp; Exit</a>' );
	$params[] = array( 'div_end', '' );
	$params[] = array( 'div_end', '' );
	$params[] = array( '2div_end', '' );

	$params[] = array( 'userinfobox', 49 );
	$params[] = array( 'userinfobox_sub', 'Export' );
	$params['export_method']	= array( 'list', '' );
	$params[] = array( 'p', '<div class="right-btns"><div class="btn-group">' );
	$params[] = array( 'p', '<a class="btn btn-info" id="testexport" href="#export-result">' . aecHTML::Icon( 'eye-open', true ) . '&nbsp;Test Export</a>' );
	$params[] = array( 'p', '<a class="btn btn-success" onclick="javascript: submitbutton(\'exportExport' . $type . '\')" href="#">' . aecHTML::Icon( 'file', true ) . '&nbsp;Export Now</a>' );
	$params[] = array( '2div_end', '' );
	$params[] = array( 'div_end', '' );
	$params[] = array( '2div_end', '' );

	$params[] = array( 'userinfobox', 49 );
	$params[] = array( 'div', '<div class="aec_userinfobox_sub" id="export-result">' );
	$params[] = array( 'h4', '<h4>Preview</h4>' );
	$params[] = array( '2div_end', '' );

	// Create a list of export options
	// First, only the non-autosaved entries
	$query = 'SELECT `id`, `name`, `created_date`, `lastused_date`'
			. ' FROM #__acctexp_export' . ( ( $type == 'sales' ) ? '_sales' : '' )
			. ' WHERE `system` = \''
			;
	$db->setQuery( $query . '0\'' );
	$user_exports = $db->loadObjectList();

	// Then the autosaved entries
	$db->setQuery( $query . '1\'' );
	$system_exports = $db->loadObjectList();

	$entries = count( $user_exports ) + count( $system_exports );

	$m = 0;
	if ( $entries > 0 ) {
		$listitems = array();
		$listitems[] = JHTML::_('select.option', 0, " --- Your Exports --- " );

		$user = false;
		for ( $i=0; $i < $entries; $i++ ) {
			if ( ( $i >= count( $user_exports ) ) && ( $user === false ) ) {
				$user = $i;

				$listitems[] = JHTML::_('select.option', 0, " --- Autosaves --- " );
			}

			if ( $user === false ) {
				if ( !empty( $user_exports[$i]->name ) ) {
					$used_date = ( $user_exports[$i]->lastused_date == '0000-00-00 00:00:00' ) ? 'never' : $user_exports[$i]->lastused_date;
					$listitems[] = JHTML::_('select.option', $user_exports[$i]->id, substr( $user_exports[$i]->name, 0, 64 ) . ' - ' . 'last used: ' . $used_date . ', created: ' . $user_exports[$i]->created_date );
				} else {
					$m--;
				}
			} else {
				$ix = $i - $user;
				$used_date = ( $system_exports[$ix]->lastused_date == '0000-00-00 00:00:00' ) ? 'never' : $system_exports[$ix]->lastused_date;
				$listitems[] = JHTML::_('select.option', $system_exports[$ix]->id, substr( $system_exports[$ix]->name, 0, 64 ) . ' - ' . 'last used: ' . $used_date . ', created: ' . $system_exports[$ix]->created_date );
			}
		}
	} else {
		$listitems[] = JHTML::_('select.option', 0, " --- No saved Preset available --- " );
		$listitems[] = JHTML::_('select.option', 0, " --- Your Exports --- ", 'value', 'text', true );
		$listitems[] = JHTML::_('select.option', 0, " --- Autosaves --- ", 'value', 'text', true );
	}

	$lists['selected_export'] = JHTML::_('select.genericlist', $listitems, 'selected_export', 'size="' . max( 10, min( 20, $entries+$m+2 ) ) . '" class="span7"', 'value', 'text', arrayValueDefault($system_values, 'selected_export', '') );

	// Get list of plans for filter
	$query = 'SELECT `id`, `name`'
			. ' FROM #__acctexp_plans'
			. ' ORDER BY `ordering`'
			;
	$db->setQuery( $query );
	$db_plans = $db->loadObjectList();

	$lists['planid'] = '<select id="plan-filter-select" class="span3" name="planid[]" multiple="multiple" size="5">';
	foreach ( $db_plans as $plan ) {
		$lists['planid'] .= '<option value="' . $plan->id . '"' . ( in_array( $plan->id, $filter_values['planid'] ) ? ' selected="selected"' : '' ) . '/>' . $plan->name . '</option>';
	}
	$lists['planid'] .= '</select>';

	$grouplist = ItemGroupHandler::getTree();

	$lists['groupid'] = '<select id="group-filter-select" class="span3" name="groupid[]" multiple="multiple" size="5">';
	foreach ( $grouplist as $glisti ) {
		if ( defined( 'JPATH_MANIFESTS' ) ) {
			$lists['groupid'] .= '<option value="' . $glisti[0] . '"' . ( in_array( $glisti[0], $filter_values['groupid'] ) ? ' selected="selected"' : '' ) . '/>' . str_replace( '&nbsp;', ' ', $glisti[1] ) . '</option>';
		} else {
			$lists['groupid'] .= '<option value="' . $glisti[0] . '"' . ( in_array( $glisti[0], $filter_values['groupid'] ) ? ' selected="selected"' : '' ) . '/>' . $glisti[1] . '</option>';
		}
	}
	$lists['groupid'] .= '</select>';

	if ( $type == 'members' ) {
		$status = array(	'excluded'	=> JText::_('AEC_SEL_EXCLUDED'),
							'pending'	=> JText::_('AEC_SEL_PENDING'),
							'active'	=> JText::_('AEC_SEL_ACTIVE'),
							'expired'	=> JText::_('AEC_SEL_EXPIRED'),
							'closed'	=> JText::_('AEC_SEL_CLOSED'),
							'cancelled'	=> JText::_('AEC_SEL_CANCELLED'),
							'hold'		=> JText::_('AEC_SEL_HOLD'),
							'notconfig'	=> JText::_('AEC_SEL_NOT_CONFIGURED')
							);

		$lists['status'] = '<select id="status-group-select" name="status[]" multiple="multiple" size="5">';
		foreach ( $status as $id => $txt ) {
			$lists['status'] .= '<option value="' . $id . '"' . ( in_array( $id, $filter_values['status'] ) ? ' selected="selected"' : '' ) . '/>' . $txt . '</option>';
		}
		$lists['status'] .= '</select>';

		// Ordering
		$sel = array();
		$sel[] = JHTML::_('select.option', 'expiration ASC',	JText::_('EXP_ASC') );
		$sel[] = JHTML::_('select.option', 'expiration DESC',	JText::_('EXP_DESC') );
		$sel[] = JHTML::_('select.option', 'name ASC',			JText::_('NAME_ASC') );
		$sel[] = JHTML::_('select.option', 'name DESC',			JText::_('NAME_DESC') );
		$sel[] = JHTML::_('select.option', 'username ASC',		JText::_('LOGIN_ASC') );
		$sel[] = JHTML::_('select.option', 'username DESC',		JText::_('LOGIN_DESC') );
		$sel[] = JHTML::_('select.option', 'signup_date ASC',	JText::_('SIGNUP_ASC') );
		$sel[] = JHTML::_('select.option', 'signup_date DESC',	JText::_('SIGNUP_DESC') );
		$sel[] = JHTML::_('select.option', 'lastpay_date ASC',	JText::_('LASTPAY_ASC') );
		$sel[] = JHTML::_('select.option', 'lastpay_date DESC',	JText::_('LASTPAY_DESC') );
		$sel[] = JHTML::_('select.option', 'plan_name ASC',		JText::_('PLAN_ASC') );
		$sel[] = JHTML::_('select.option', 'plan_name DESC',	JText::_('PLAN_DESC') );
		$sel[] = JHTML::_('select.option', 'status ASC',		JText::_('STATUS_ASC') );
		$sel[] = JHTML::_('select.option', 'status DESC',		JText::_('STATUS_DESC') );
		$sel[] = JHTML::_('select.option', 'type ASC',			JText::_('TYPE_ASC') );
		$sel[] = JHTML::_('select.option', 'type DESC',			JText::_('TYPE_DESC') );

		$lists['orderby'] = JHTML::_('select.genericlist', $sel, 'orderby', 'class="inputbox" size="1"', 'value', 'text', arrayValueDefault($filter_values, 'orderby', '') );
	} else {
		$collate_selection = array();
		$collate_selection[] = JHTML::_('select.option', 'day',	JText::_('Day') );
		$collate_selection[] = JHTML::_('select.option', 'week',	JText::_('Week') );
		$collate_selection[] = JHTML::_('select.option', 'month',		JText::_('Month') );
		$collate_selection[] = JHTML::_('select.option', 'year',		JText::_('Year') );

		$selected_collate = 0;
		if ( !empty( $options_values['collate'] ) ) {
			$selected_collate = $options_values['collate'];
		} else {
			$selected_collate = 'day';
		}

		$lists['collate'] = JHTML::_('select.genericlist', $collate_selection, 'collate', 'size="1"', 'value', 'text', $selected_collate);

		$breakdown_selection = array();
		$breakdown_selection[] = JHTML::_('select.option', '0',	JText::_('None') );
		$breakdown_selection[] = JHTML::_('select.option', 'plan',	JText::_('Plan') );
		$breakdown_selection[] = JHTML::_('select.option', 'group',	JText::_('Group') );

		$selected_breakdown = 0;
		if ( !empty( $options_values['breakdown'] ) ) {
			$selected_breakdown = $options_values['breakdown'];
		}

		$lists['breakdown'] = JHTML::_('select.genericlist', $breakdown_selection, 'breakdown', 'size="1"', 'value', 'text', $selected_breakdown);

		$processors = PaymentProcessorHandler::getInstalledObjectList();

		$proc_list = array();
		$selected_proc = array();
		foreach ( $processors as $proc ) {
			$pp = new PaymentProcessor();
			$pp->loadName( $proc->name );
			$pp->getInfo();

			$proc_list[] = JHTML::_('select.option', $pp->id, $pp->info['longname'] );

			if ( !empty( $filter_values['method'] ) ) {
				foreach ( $filter_values['method'] as $id ) {
					if ( $id == $pp->id ) {
						$selected_proc[] = JHTML::_('select.option', $id, $pp->info['longname'] );
					}
				}
			}
		}

		$lists['method'] = JHTML::_('select.genericlist', $proc_list, 'method[]', 'size="8" multiple="multiple"', 'value', 'text', $selected_proc);
	}

	// Export Method
	$list = xJUtility::getFileArray( JPATH_SITE . '/components/com_acctexp/lib/export', 'php', false, true );

	$sel = array();
	foreach ( $list as $ltype ) {
		$ltype = str_replace( '.php', '', $ltype );
		if ( $ltype != 'test' ) {
			$sel[] = JHTML::_('select.option', $ltype, $ltype );
		}
	}

	if ( empty( $params_values['export_method'] ) ) {
		$params_values['export_method'] = 'csv';
	}

	$lists['export_method'] = JHTML::_('select.genericlist', $sel, 'export_method', 'class="inputbox" size="1"', 'value', 'text', $params_values['export_method'] );

	$settings = new aecSettings ( 'export', 'general' );

	// Repackage the objects as array
	foreach( $getpost as $name => $array ) {
		$field = $name . '_values';
		foreach( $array as $vname ) {
			if ( !empty( $$field->$name ) ) {
				$settingsparams[$name] = $$field->$name;
			} else {
				$settingsparams[$name] = "";
			}
		}
	}

	if ( empty( $params_values['rewrite_rule'] ) ) {
		//$params_values['rewrite_rule'] = '[[user_id]];[[user_username]];[[subscription_expiration_date]]';
	}

	$settingsparams = array_merge( $filter_values, $options_values, $params_values );

	$settings->fullSettingsArray( $params, $settingsparams, $lists ) ;

	// Call HTML Class
	$aecHTML = new aecHTML( $settings->settings, $settings->lists );

	if ( ( $cmd_export ) && !empty( $params_values['export_method'] ) ) {
		if ( $use_original ) {
			$row->useExport();
		} else {
			$autorow->useExport();
		}
	}

	if ( $cmd_save ) {
		aecRedirect( 'index.php?option=' . $option . '&task=showCentral' );
	} else {
		HTML_AcctExp::export( $option, $type, $aecHTML );
	}
}

function toolBoxTool( $option, $cmd )
{
	$path = JPATH_SITE . '/components/com_acctexp/toolbox';

	if ( empty( $cmd ) ) {
		$list = array();

		$files = xJUtility::getFileArray( $path, 'php', false, true );

		asort( $files );

		foreach ( $files as $n => $name ) {
			$file = $path . '/' . $name;

			include_once $file;

			$class = str_replace( '.php', '', $name );

			$tool = new $class();

			if ( !method_exists( $tool, 'Info' ) ) {
				continue;
			}

			$info = $tool->Info();

			$info['link'] = AECToolbox::deadsureURL( 'administrator/index.php?option=' . $option . '&task=toolbox&cmd=' . $class );

			$list[] = $info;
		}

		HTML_AcctExp::toolBox( $option, '', $list );
	} else {
		$file = $path . '/' . $cmd . '.php';

		include_once $file;

		$tool = new $cmd();

		$info = $tool->Info();

		$return = '';
		if ( !method_exists( $tool, 'Action' ) ) {
			$return .= '<div id="aec-toolbox-result">' . '<p>Tool doesn\'t have an action to carry out!</p>' . '</div>';
		} else {
			if ( method_exists( $tool, 'Settings' ) ) {
				$tb_settings = $tool->Settings();

				if ( !empty( $tb_settings ) ) {

					$lists = array();
					if ( isset( $tb_settings['lists'] ) ) {
						$lists = $tb_settings['lists'];

						unset( $tb_settings['lists'] );
					}

					// Get preset values from POST
					foreach ( $tb_settings as $n => $v ) {
						if ( isset( $_POST[$n] ) ) {
							$tb_settings[$n][3] = $_POST[$n];
						}
					}

					$settings = new aecSettings( 'TOOLBOX', 'E' );
					$settings->fullSettingsArray( $tb_settings, array(), $lists );

					// Call HTML Class
					$aecHTML = new aecHTML( $settings->settings, $settings->lists );

					foreach ( $tb_settings as $n => $v ) {
						$return .= $aecHTML->createSettingsParticle( $n );
					}

					$return .= '<input type="submit" class="btn btn-primary pull-right"/>';
				}
			}

			$return .= '</div><div class="aec_userinfobox_sub"><h4>' . JText::_('Response') . '</h4><div id="aec-toolbox-result">' . $tool->Action() . '</div></div>';
		}

		HTML_AcctExp::toolBox( $option, $cmd, $return, $info['name'] );
	}
}

?>

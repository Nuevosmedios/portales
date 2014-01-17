<?php
/**
 * @version $Id: mi_eventlist.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Event List
 * @copyright Copyright (C) 2011 David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
defined('_JEXEC') OR defined( '_VALID_MOS' ) OR die( 'Direct Access to this location is not allowed.' );

class mi_eventlist extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_EVENTLIST');
		$info['desc'] = JText::_('AEC_MI_DESC_EVENTLIST');
		$info['type'] = array( 'calendar_events.events' );

		return $info;
	}

	function Settings()
	{
		$settings = array();

		$path = rtrim( JPATH_ROOT, DS ) . DS . 'components' . DS . 'com_eventlist' . DS . 'helpers' . DS . 'helper.php';

		if ( !file_exists( $path ) ) {
			echo 'This module can not work without the Event List Component';

			return $settings;
		} else {
			@include_once( $path );
		}

		$db = &JFactory::getDBO();
		$db->setQuery( 'SELECT * FROM #__eventlist_events WHERE `registra` = 1 AND `published` = 1' );

		$events = $db->loadObjectList();

		$eventslist = array();
		$eventslist[] = JHTML::_('select.option', 0, "--- --- ---" );

		foreach ( $events as $id => $row ) {
			$eventslist[] = JHTML::_('select.option', $row->id, $row->id . ': ' . $row->title );
		}

		$settings['event']	= array( 'list' );

		$settings = $this->autoduplicatesettings( $settings );

		foreach ( $settings as $k => $v ) {
			if ( !isset( $this->settings[$k] ) ) {
				$this->settings[$k] = null;
			}

			$settings['lists'][$k]	= JHTML::_( 'select.genericlist', $eventslist, $k, 'size="1"', 'value', 'text', $this->settings[$k] );
		}

		$xsettings = array();

		return array_merge( $xsettings, $settings );
	}

	function detect_application()
	{
		$path = rtrim( JPATH_ROOT, DS ) . DS . 'components' . DS . 'com_eventlist' . DS . 'helpers' . DS . 'helper.php';

		return file_exists( $path );
	}

	function hacks()
	{
		$hacks = array();

		$edithack = '// AEC HACK eventlist1 START' . "\n"
		. 'case 4:' . "\n"
		. '// Tell the user to use the standard register link' . "\n"
		. 'echo "Register using the link above"' . "\n"
		. 'break;' . "\n"
		. '// Dismissing the standard case 4 here' . "\n"
		. 'case 999:' . "\n"
		. '// AEC HACK eventlist1 END' . "\n"
		;

		$n = 'eventlist1';
		$hacks[$n]['name']				=	'default_attendees.php #1';
		$hacks[$n]['desc']				=	"Show a registration notice instead of a registration form, which could result in broken registrations.";
		$hacks[$n]['type']				=	'file';
		$hacks[$n]['filename']			=	JPATH_SITE . '/components/com_eventlist/views/details/tmpl/default_attendees.php';
		$hacks[$n]['read']				=	'case 4:';
		$hacks[$n]['insert']			=	$edithack . "\n";

		return $hacks;
	}

	function relayAction( $request )
	{
		if ( !empty( $this->settings['event' . $request->area] ) ) {
			$this->regEvent( $request->metaUser->userid, $this->settings['event' . $request->area] );
		}
	}

	function regEvent( $userid, $newevent )
	{
		$db = &JFactory::getDBO();

		$query = 'INSERT INTO #__eventlist_register'
				. ' ( `event` , `uid` )'
				. ' VALUES (\'' . $newevent . '\', \'' . $userid . '\')'
				;

		$db->setQuery( $query );
		$db->query();
	}

}
?>
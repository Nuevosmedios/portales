<?php
/**
 * @version $Id: mi_joomdle.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Joomdle
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_joomdle
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_JOOMDLE');
		$info['desc'] = JText::_('AEC_MI_DESC_JOOMDLE');
		$info['type'] = array( 'education.courses', 'vendor.joomdle' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['courses']		  = array( 'list' );

		$this->loadHelper();

		$courses = JoomdleHelperContent::getCourseList(0);

		$options = array();
		foreach ( $courses as $course ) {
			$options[] = JHTML::_('select.option', $course['remoteid'], $course['fullname']);
		}

		$selected_courses = array();
		if ( !empty( $this->settings['courses'] ) ) {
			$selected_courses = $this->settings['courses'];
		}

		$settings['lists']['courses'] =  JHTML::_('select.genericlist', $options, 'courses[]', 'class="inputbox" size="10" multiple="true"', 'value', 'text', $selected_courses );

		return $settings;
	}

	function expiration_action( $request )
	{
		if ( empty( $this->settings['courses'] ) ) {
			return null;
		}

		$c = array();
		foreach ( $this->settings['courses'] as $course_id ) {
			$c[] = array( 'id' => ( (int) $course_id ) );
		}

		$this->loadHelper();

		return JoomdleHelperContent::call_method('multiple_suspend_enrolment', $request->metaUser->cmsUser->username, $c);
	}

	function action( $request )
	{
		if ( empty( $this->settings['courses'] ) ) {
			return null;
		}

		$c = array();
		foreach ( $this->settings['courses'] as $course_id ) {
			$c[] = array( 'id' => ( (int) $course_id ) );
		}

		$this->loadHelper();

		return JoomdleHelperContent::call_method('multiple_enrol', $request->metaUser->cmsUser->username, $c, 5);
	}

	function loadHelper()
	{
		require_once(JPATH_ADMINISTRATOR.'/components/com_joomdle/helpers/content.php');
	}
}

?>

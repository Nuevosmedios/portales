<?php
/**
 * @version $Id: mi_googleadsenseconversion.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Google Adsense Conversion
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_googleadsenseconversion
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_GOOGLEADSENSECONVERSION');
		$info['desc'] = JText::_('AEC_MI_DESC_GOOGLEADSENSECONVERSION');
		$info['type'] = array( 'tracking.analytics', 'vendor.google' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['conversion_id']	= array( 'inputB' );
		$settings['language']		= array( 'inputB' );
		$settings['format']			= array( 'inputB' );
		$settings['color']			= array( 'inputB' );
		$settings['label']			= array( 'inputB' );

		return $settings;
	}

	function Defaults()
	{
		$settings = array();
		$settings['conversion_id']	= "";
		$settings['language']		= "en_US";
		$settings['format']			= "1";
		$settings['color']			= "ffffff";
		$settings['label']			= "Purchase";

		return $settings;
	}

	function CommonData()
	{
		return array( 'conversion_id' );
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		$text = '<script language="JavaScript" type="text/javascript">' . "\n"
				. '<!--' . "\n"
				. 'var google_conversion_id = ' . $this->settings['conversion_id'] . ';' . "\n"
				. 'var google_conversion_language = "' . $this->settings['language'] . '";' . "\n"
				. 'var google_conversion_format = "' . $this->settings['format'] . '";' . "\n"
				. 'var google_conversion_color = "' . $this->settings['format'] . '";' . "\n"
				. 'var google_conversion_label = "' . $this->settings['label'] . '";' . "\n"
				. 'var google_conversion_value = ' . $request->invoice->amount . ';' . "\n"
				. '//-->' . "\n"
				. '</script>'
				. '<script language="JavaScript" src="http://www.googleadservices.com/pagead/conversion.js">'
				. '</script>'
				. '<noscript>'
				. '<img height="1" width="1" border="0" src="http://www.googleadservices.com/pagead/conversion/1055602872/?value='
				. $request->invoice->amount . 'amp;label=' . $this->settings['label'] . '&amp;guid=ON&amp;script=0"/>'
				. '</noscript>';

		$displaypipeline = new displayPipeline();
		$displaypipeline->create( $request->metaUser->userid, 1, 0, 0, null, 1, $text );

		return true;
	}
}
?>

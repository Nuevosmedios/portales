<?php
/**
 * @version $Id: mi_aecinvoiceprintmod.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Invoice Printout Modification
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_aecinvoiceprintmod
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_AECINVOICEPRINTMOD');
		$info['desc'] = JText::_('AEC_MI_DESC_AECINVOICEPRINTMOD');
		$info['type'] = array( 'aec.invoice', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$s = array( "before_header", "header", "after_header", "address",
					"before_content", "after_content",
					"before_footer", "footer", "after_footer",
					);

 		$modelist = array();
		$modelist[] = JHTML::_('select.option', "none", JText::_('AEC_TEXTMODE_NONE') );
		$modelist[] = JHTML::_('select.option', "before", JText::_('AEC_TEXTMODE_BEFORE') );
		$modelist[] = JHTML::_('select.option', "after", JText::_('AEC_TEXTMODE_AFTER') );
		$modelist[] = JHTML::_('select.option', "replace", JText::_('AEC_TEXTMODE_REPLACE') );
		$modelist[] = JHTML::_('select.option', "delete", JText::_('AEC_TEXTMODE_DELETE') );

		$settings = array();
		foreach ( $s as $x ) {
			$y = $x."_mode";

			if ( isset( $this->settings[$y] ) ) {
				$dv = $this->settings[$y];
			} else {
				$dv = null;
			}

			$settings[$y]			= array( "list" );
			$settings['lists'][$y]	= JHTML::_('select.genericlist', $modelist, $y, 'size="1"', 'value', 'text', $dv );

			$settings[$x]			= array( "editor" );
		}

		return $settings;
	}

	function invoice_printout( $request )
	{
		$db = &JFactory::getDBO();

		foreach ( $request->add as $k => $v ) {
			if ( isset( $this->settings[$k] ) ) {
				if ( isset( $this->settings[$k."_mode"] ) ) {
					switch ( $this->settings[$k."_mode"] ) {
						case "none":
							$value = $v;
							break;
						case "before":
							$value = $this->settings[$k] . $v;
							break;
						case "after":
							$value = $v . $this->settings[$k];
							break;
						case "replace":
							$value = $this->settings[$k];
							break;
						case "delete":
							$value = "";
							break;
					}
				} else {
					$value = $this->settings[$k];
				}

				$request->add[$k] = $value;
			}
		}

		return true;
	}
}
?>

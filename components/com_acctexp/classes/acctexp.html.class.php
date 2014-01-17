<?php
/**
 * @version $Id: acctexp.html.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class aecHTML
{
	function aecHTML( $rows, $lists=null, $js=array() )
	{
		$this->rows		= $rows;
		$this->lists	= $lists;
		$this->js		= $js;
	}

	function createSettingsParticle( $name, $notooltip=false, $insertlabel=null, $insertctrl=null )
	{
		if ( !isset( $this->rows[$name] ) ) {
			return;
		}

		$row	= $this->rows[$name];
		$type	= $row[0];

		$return = '';

		if ( isset( $row[2] ) ) {
			if ( isset( $row[3] ) ) {
				$value = $row[3];
			} else {
				$value = '';
			}

			if ( !empty( $row[1] ) && !empty( $row[2] ) && !$notooltip ) {
				$return = '<div class="control-group">';

				if ( strnatcmp( phpversion(),'5.2.3' ) >= 0 ) {
					$xtitle = htmlentities( $row[2], ENT_QUOTES, "UTF-8", false );
					$xlabel = htmlentities( $row[1], ENT_QUOTES, "UTF-8", false );
				} else {
					$xtitle = htmlentities( $row[2], ENT_QUOTES, "UTF-8" );
					$xlabel = htmlentities( $row[1], ENT_QUOTES, "UTF-8" );
				}

				$return .= '<label class="control-label bstooltip" for="' . $name . '" rel="tooltip" class="bstooltip" data-original-title="' . $xtitle . '">';

				$return .= $xlabel;

				$return .= $insertlabel;

				$return .= '</label>';
			} else {
				$return = '<div class="control-group">';
				$return .= '<label class="control-label" for="' . $name . '"><div class="controls"></div></label>';
			}
		} else {
			if ( isset( $row[1] ) ) {
				$value = $row[1];
			} else {
				$value = '';
			}
		}

		switch ( $type ) {
			case 'inputA':
				$return .= '<div class="controls">';
				$return .= '<input id="' . $name . '" class="span1" name="' . $name . '" type="text" value="' . $value . '" />';
				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			case 'inputB':
				$return .= '<div class="controls">';
				$return .= '<input id="' . $name . '" class="span2" type="text" name="' . $name . '" value="' . $value . '" />';
				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			case 'inputC':
				$return .= '<div class="controls">';
				$return .= '<input id="' . $name . '" class="span3" type="text" name="' . $name . '" value="' . $value . '" />';
				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			case 'inputD':
				$return .= '<div class="controls">';
				$return .= '<textarea id="' . $name . '" class="span4" rows="5" name="' . $name . '" >' . $value . '</textarea>';
				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			case 'inputE':
				$return .= '<div class="controls">';
				$return .= '<textarea id="' . $name . '" class="span4" cols="450" rows="1" name="' . $name . '" >' . $value . '</textarea>';
				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			case 'password':
				$return .= '<div class="controls">';
				$return .= '<input id="' . $name . '" class="span3" type="password" name="' . $name . '" value="' . $value . '" />';
				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			case 'p':
				$return = ( !empty( $value ) ? '<p>' . $value . '</p>' : '' );
				break;
			case 'hr':
				$return = '<hr />';
				break;
			case 'checkbox':
				$return = '<div class="control-group">';
				$return .= '<label class="control-label" for="' . $name . '"></label>';
				$return .= '<div class="controls">';
				$return .= '<input type="hidden" name="' . $name . '" value="0"/>';
				$return .= '<input id="' . $name . '" type="checkbox" name="' . $name . '" ' . ( $value ? 'checked="checked" ' : '' ) . ' value="1"/>';

				$return .= $xlabel;

				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			case 'toggle':
				$return .= '<input type="hidden" name="' . $name . '" value="0"/>';
				$return .= '<div class="controls">';
				$return .= '<div class="toggleswitch">';
				$return .= '<label class="toggleswitch" onclick="">';
				$return .= '<input id="' . $name . '" type="checkbox" name="' . $name . '"' . ( $value ? ' checked="checked" ' : '' ) . ' value="1"/>';
				$return .= '<span class="toggleswitch-inner">';
				$return .= '<span class="toggleswitch-on">' . JText::_( 'yes' ) . '</span>';
				$return .= '<span class="toggleswitch-off">' . JText::_( 'no' ) . '</span>';
				$return .= '<span class="toggleswitch-handle"></span>';
				$return .= '</span>';
				$return .= '</label>';
				$return .= '</div>';
				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			case 'toggle_disabled':
				$return .= '<input type="hidden" name="' . $name . '" value="' . $value . '"/>';
				$return .= '<div class="controls">';
				$return .= '<div class="toggleswitch">';
				$return .= '<label class="toggleswitch" onclick="">';
				$return .= '<input id="' . $name . '" type="checkbox" name="' . $name . '"' . ( $value ? ' checked="checked" ' : '' ) . ' disabled="disabled" value="1"/>';
				$return .= '<span class="toggleswitch-inner">';
				$return .= '<span class="toggleswitch-on">' . JText::_( 'yes' ) . '</span>';
				$return .= '<span class="toggleswitch-off">' . JText::_( 'no' ) . '</span>';
				$return .= '<span class="toggleswitch-handle"></span>';
				$return .= '</span>';
				$return .= '</label>';
				$return .= '</div>';
				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			case 'editor':
				$return .= '<div class="controls">';

				$editor = &JFactory::getEditor();

				$return .= '<div>' . $editor->display( $name,  $value , '', '250', '50', '20' ) . '</div>';
				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			case 'textarea':
				$return .= '<textarea style="width:90%" cols="450" rows="10" name="' . $name . '" id="' . $name . '" >' . $value . '</textarea></div>';
				break;
			case 'list':
				$return .= '<div class="controls">';

				if ( strpos( $this->lists[$name], '[]"' ) ) {
					$return .= '<input type="hidden" name="' . $name . '" value="0" />';
					$return .= str_replace( '<select', '<select class="jqui-multiselect"', $this->lists[$name] );
				} else {
					$return .= str_replace( '<select', '<select class="span3"', $this->lists[$name] );
				}
				
				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			case 'radio':
				$return = '<div class="control-group">';
				$return .= '<label class="control-label" for="' . $name . '">';
				$return .= '<input type="radio" id="' . $name . '" name="' . $row[1] . '"' . ( ( $row[3] == $row[2] ) ? ' checked="checked"' : '' ) . ' value="' . $row[2] . '"/>';
				$return .= '</label>';
				$return .= '<div class="controls">';
				$return .= $row[4];
				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			case 'file':
				$return .= '<div class="controls">';
				$return .= '<input id="' . $name . '" name="' . $name . '" type="file" />';
				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			case 'accordion_start':
				if ( !isset( $this->accordions ) ) {
					$this->accordionitems = 1;
					$this->accordions = 1;
				} else {
					$this->accordions++;
				}

				$return = '<div id="accordion' . $this->accordions . '" class="accordion' . ( !empty( $value ) ? ' ' . $value : '' ) . '"' . '>';
				break;
			case 'accordion_itemstart':
				$return = '<div class="accordion-group">';
				$return .= '<div class="accordion-heading"><a href="#collapse' . ($this->accordions+$this->accordionitems) . '" data-parent="#accordion' . $this->accordions . '" data-toggle="collapse" class="accordion-toggle">' . $value . '</a></div>';
				$return .= '<div class="accordion-body collapse" id="collapse' . ($this->accordions+$this->accordionitems) . '"><div class="accordion-inner">';
				break;
			case 'accordion_itemend':
				$this->accordionitems++;

				$return = '</div></div></div>';
				break;
			case 'div_end':
				$return = '</div>';
				break;
			case '2div_end':
				$return = '</div></div>';
				break;
			case 'tabberstart':
				$return = '';
				break;
			case 'tabregisterstart':
				$return = '<ul class="nav nav-tabs">';
				break;
			case 'tabregister':
				$return = '<li' . ($row[3] ? ' class="active"': '') . '><a href="#' . $row[1] . '" data-toggle="tab">' . $row[2] . '</a></li> ';
				break;
			case 'tabregisterend':
				$return = '</ul><div class="tab-content">';
				break;
			case 'tabstart':
				$act = false;
				if ( isset( $row[2] ) ) {
					$act = $row[2];
				}
				$return = '<div id="' . $row[1] . '" class="tab-pane' . ($act ? ' active': '') . '">';
				break;
			case 'tabend':
				$return = '</div>';
				break;
			case 'tabberend':
				$return = '</div>';
				break;
			case 'userinfobox':
				$return = '<div style="position:relative;float:left;width:' . $value . '%;"><div class="userinfobox">';
				break;
			case 'userinfobox_sub':
				$return = '<div class="aec_userinfobox_sub">' . ( !empty( $value ) ? '<h4>' . $value . '</h4>' : '' );
				break;
			case 'userinfobox_sub_stacked':
				$return = '<div class="aec_userinfobox_sub form-stacked">' . ( !empty( $value ) ? '<h4>' . $value . '</h4>' : '' );
				break;
			case 'fieldset':
				$return = '<div class="controls">' . "\n"
				. '<fieldset><legend>' . $row[1] . '</legend>' . "\n"
				. $row[2] . "\n"
				. '</fieldset>' . "\n"
				. '</div>'
				;
				break;
			case 'page-head':
				$return = '<div class="page-header" id="' . str_replace(" ", "_", strtolower($value) ) . '"><h1>' . $value . '</h1></div>';
				break;
			case 'section':
				$return = '<section' . ( !empty( $value ) ? ' id="' . $value . '"' : '' ) . '>';
				break;
			case 'section-head':
				$return = '<h2>' . $value . '</h2>';
				break;
			case 'section-end':
				$return = '</section>';
				break;
			case 'hidden':
				$return = '';
				if ( is_array( $value ) ) {
					foreach ( $value as $v ) {
						$return .= '<input id="' . $name . '" type="hidden" name="' . $name . '[]" value="' . $v . '" />';
					}
				} else {
					$return .= '<input id="' . $name . '" type="hidden" name="' . $name . '" value="' . $value . '" />';
				}
				break;
			case 'passthrough':
				$return .= '<div class="controls">';
				$return .= $value;
				$return .= $insertctrl;
				$return .= '</div></div>';
				break;
			default:
				$return = $value;
				break;
		}
		return $return;
	}

	function loadJS( $return=null )
	{
		if ( !empty( $this->js ) || !empty( $return ) ) {
			$js = "\n" . '<script type="text/javascript">';

			if ( !empty( $this->js ) ) {
				foreach ( $this->js as $scriptblock ) {
					$js .= "\n";
					$js .= $scriptblock;
				}
			}

			$js .= $return;
			$js .= "\n" . '</script>';

			$return = $js;
		}

		return $return;
	}

	function returnFull( $notooltip=false, $table=false )
	{
		$return = '';
		foreach ( $this->rows as $rowname => $rowcontent ) {
			$return .= $this->createSettingsParticle( $rowname, $notooltip );
		}

		return $return;
	}

	function printFull( $notooltip=false )
	{
		echo $this->returnFull( $notooltip );
	}

	function Icon( $icon='fire', $white=false, $addin=null )
	{
		return '<i class="bsicon-'. $icon . ( $white ? ' bsicon-white' : '' ) . $addin .'"></i>';
		/* So, yeah, IcoMoon deployment and sticking with Bootstrap conventions
		 * is a huge failure in J3.0

		$v = new JVersion();

		if ( $v->isCompatible('3.0') ) {
			$icon = str_replace( '-sign', '', $icon );

			return '<i class="bsicon-'. $icon . ( $white ? ' bsicon-white' : '' ) . $addin .'"></i>';
		} else {
			return '<i class="bsicon-'. $icon . ( $white ? ' bsicon-white' : '' ) . $addin .'"></i>';
		}*/
	}

	function Button( $icon='fire', $text='', $style='', $link='', $js='' )
	{
		$white = true;

		if ( empty( $style ) ) {
			$white = false;
		} else {
			$style = ' btn-'.$style;
		}

		if ( empty( $link ) ) {
			$link = '#';
		}

		if ( !empty( $js ) ) {
			$js = 'onclick="javascript: submitbutton(\''.$js.'\')"';
		}

		return '<a data-original-title="'.JText::_($text).'" rel="tooltip" href="'.$link.'"'.$js.' class="btn'.$style.'">'.aecHTML::Icon( $icon, $white ).'</a>';
	}

}

?>

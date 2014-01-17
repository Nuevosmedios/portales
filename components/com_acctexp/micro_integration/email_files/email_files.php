<?php
/**
 * @version $Id: mi_email_files.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Email Files
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_email_files
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_EMAIL_FILES');
		$info['desc'] = JText::_('AEC_MI_DESC_EMAIL_FILES');
		$info['type'] = array( 'communication.email', 'basic.email', 'system', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['sender']				= array( 'inputE' );
		$settings['sender_name']		= array( 'inputE' );

		$settings['recipient']			= array( 'inputE' );
		$settings['cc']					= array( 'inputE' );
		$settings['bcc']				= array( 'inputE' );

		$settings['subject']			= array( 'inputE' );
		$settings['text_html']			= array( 'toggle' );
		$settings['text']				= array( !empty( $this->settings['text_html'] ) ? 'editor' : 'inputD' );

		$settings['base_path']			= array( 'inputE' );
		$settings['file_list']			= array( 'inputD' );
		$settings['desc_list']			= array( 'inputD' );
		$settings['max_choices']		= array( 'inputA' );
		$settings['min_choices']		= array( 'inputA' );

		$rewriteswitches				= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function getMIform( $request )
	{
		$settings = array();

		if ( !empty( $this->settings['desc_list'] ) ) {
			$list = explode( "\n", $this->settings['desc_list'] );

			if ( ( $this->settings['min_choices'] == 1 ) && ( count( $list ) == 1 ) ) {
				return $settings;
			}

			$settings['exp'] = array( 'p', JText::_('MI_MI_USER_CHOICE_FILES_NAME'), JText::_('MI_MI_USER_CHOICE_FILES_DESC') );

			$gr = array();
			foreach ( $list as $id => $choice ) {
				$choice = trim( $choice );

				if ( ( $this->settings['max_choices'] > 1 ) && ( count( $list ) > 1 ) ) {
					$settings['ef'.$id] = array( 'checkbox', 'mi_'.$this->id.'_mi_email_files[]', $id, true, $choice );
				} else {
					$settings['ef'.$id] = array( 'radio', 'mi_'.$this->id.'_mi_email_files', $id, true, $choice );
				}
			}
			$settings['mi_email_files'] = array( 'hidden', null, 'mi_'.$this->id.'_mi_email_files[]' );
		} else {
			return false;
		}

		return $settings;
	}

	function verifyMIform( $request )
	{
		$return = array();

		$list = explode( "\n", $this->settings['desc_list'] );

		if ( ( $this->settings['min_choices'] == 1 ) && ( count( $list ) == 1 ) ) {
			return $return;
		}

		if ( !empty( $request->params['mi_email_files'] ) ) {
			foreach ( $request->params['mi_email_files'] as $i => $v ) {
				if ( is_null( $v ) || ( $v == "" ) ) {
					unset( $request->params['mi_email_files'][$i] );
				}
			}
		}

		if ( empty( $request->params['mi_email_files'] ) ) {
			if ( $this->settings['min_choices'] == $this->settings['max_choices'] ) {
				$return['error'] = "Please select " . $this->settings['min_choices'] . " options!";
			} else {
				$return['error'] = "Please select at least " . $this->settings['min_choices'] . " options!";
			}
			return $return;
		}

		$selected = count( $request->params['mi_email_files'] );

		if ( $selected > $this->settings['max_choices'] ) {
			if ( $this->settings['min_choices'] == $this->settings['max_choices'] ) {
				$return['error'] = "Too many options selected - Please select exactly " . $this->settings['max_choices'] . " options!";
			} else {
				$return['error'] = "Too many options selected! Please select no more than " . $this->settings['max_choices'] . " options!";
			}
		}

		if ( $selected < $this->settings['min_choices'] ) {
			if ( $this->settings['min_choices'] == $this->settings['max_choices'] ) {
				$return['error'] = "Not enough options selected - Please select exactly " . $this->settings['min_choices'] . " options!";
			} else {
				$return['error'] = "Please select more than " . $this->settings['min_choices'] . " options!";
			}
		}

		return $return;
	}

	function action( $request )
	{
		$message	= AECToolbox::rewriteEngineRQ( $this->settings['text'], $request );
		$subject	= AECToolbox::rewriteEngineRQ( $this->settings['subject'], $request );

		if ( empty( $message ) ) {
			return null;
		}

		$recipient = $cc = $bcc = array();

		$rec_groups = array( "recipient", "cc", "bcc" );

		foreach ( $rec_groups as $setting ) {
			$list = AECToolbox::rewriteEngineRQ( $this->settings[$setting], $request );

			$recipient_array = explode( ',', $list );

	        if ( !empty( $recipient_array ) ) {
		        $$setting = array();

		        foreach ( $recipient_array as $k => $email ) {
		            if ( !empty( $email ) ) {
		            	${$setting}[] = trim( $email );
		            }
		        }
	        }
		}

		$f = explode( "\n", $this->settings['file_list'] );

		if ( !empty( $this->settings['desc_list'] ) ) {
			$userchoice = $request->params['mi_email_files'];

			if ( !empty( $this->settings['max_choices'] ) ) {
				if ( count( $userchoice ) > $this->settings['max_choices'] ) {
					$userchoice = array_slice( $userchoice, 0, $this->settings['max_choices']);
				}
			}
		} else {
			$userchoice = false;
		}

		if ( !empty( $this->settings['base_path'] ) ) {
			$b = $this->settings['base_path'] . '/';
		} else {
			$b = '';
		}

		$attach = array();
		foreach ( $f as $fid => $fname ) {
			if ( empty( $fname ) ) {
				continue;
			}

			if ( ( count( $f ) > 1 ) && ( $this->settings['min_choices'] == 1 ) ) {

			} elseif ( $userchoice != false ) {
				if ( !in_array( $fid, $userchoice ) ) {
					continue;
				}
			}

			$ff = $b . trim( $fname );

			if ( file_exists( $ff ) ) {
				$attach[] = $ff;
			}
		}

		xJ::sendMail( $this->settings['sender'], $this->settings['sender_name'], $recipient, $subject, $message, $this->settings['text_html'], $cc, $bcc, $attach );

		return true;
	}
}
?>

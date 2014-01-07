<?php
/**
 * @version $Id: mi_email_multi.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Multi Email
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_email_multi extends MI
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_EMAIL_MULTI');
		$info['desc'] = JText::_('AEC_MI_DESC_EMAIL_MULTI');
		$info['type'] = array( 'communication.email', 'basic.email', 'system', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['sender']			= array( 'inputE' );
		$settings['sender_name']	= array( 'inputE' );

		$settings['emails_count']	= array( 'inputC' );

		if ( !empty( $this->settings['emails_count'] ) ) {
			for ( $i=0; $i<$this->settings['emails_count']; $i++ ) {
				$pf = 'email_' . $i . '_';

				$settings['aectab_'.$pf]		= array( 'tab', 'Email '.($i+1), 'Email '.($i+1) );
				$settings[$pf.'timing']			= array( 'inputE', sprintf( JText::_('MI_MI_EMAIL_MULTI_TIMING_NAME'), $i+1 ), JText::_('MI_MI_EMAIL_MULTI_TIMING_DESC') );
				$settings[$pf.'recipient']		= array( 'inputE', sprintf( JText::_('MI_MI_EMAIL_MULTI_RECIPIENT_NAME'), $i+1 ), JText::_('MI_MI_EMAIL_MULTI_RECIPIENT_DESC') );
				$settings[$pf.'cc']				= array( 'inputE', sprintf( JText::_('MI_MI_EMAIL_MULTI_CC_NAME'), $i+1 ), JText::_('MI_MI_EMAIL_MULTI_CC_DESC') );
				$settings[$pf.'bcc']			= array( 'inputE', sprintf( JText::_('MI_MI_EMAIL_MULTI_BCC_NAME'), $i+1 ), JText::_('MI_MI_EMAIL_MULTI_BCC_DESC') );
				$settings[$pf.'subject']		= array( 'inputE', sprintf( JText::_('MI_MI_EMAIL_MULTI_SUBJECT_NAME'), $i+1 ), JText::_('MI_MI_EMAIL_MULTI_SUBJECT_DESC') );
				$settings[$pf.'text_html']		= array( 'toggle', sprintf( JText::_('MI_MI_EMAIL_MULTI_TEXT_HTML_NAME'), $i+1 ), JText::_('MI_MI_EMAIL_MULTI_TEXT_HTML_DESC') );
				$settings[$pf.'text']			= array( ( !empty( $this->settings[$pf.'text_html'] ) ? 'editor' : 'inputD' ), sprintf( JText::_('MI_MI_EMAIL_MULTI_TEXT_NAME'), $i+1 ), JText::_('MI_MI_EMAIL_MULTI_TEXT_DESC') );
			}
		}

		$rewriteswitches			= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

		$settings					= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function relayAction( $request )
	{
		$app = JFactory::getApplication();

		if ( !isset( $this->settings['sender'.$request->area] ) ) {
			return null;
		}

		if ( !empty( $this->settings['emails_count'] ) && !empty( $this->settings['sender'] ) && !empty( $this->settings['sender_name'] ) ) {
			for ( $i=0; $i<$this->settings['emails_count']; $i++ ) {
				$pf = 'email_' . $i . '_';

				if ( !empty( $this->settings[$pf.'recipient'] ) && !empty( $this->settings[$pf.'timing'] ) ) {
					$timing	= AECToolbox::rewriteEngineRQ( $this->settings[$pf.'timing'], $request );

					if ( ( strpos( $timing, '-' ) === 0 ) || ( strpos( $timing, '++' ) === 0 ) ) {
						// Go back from Expiration date
						$tstamp = strtotime( $request->metaUser->focusSubscription->expiration );
					} else {
						// Go from current timestamp
						$tstamp = (int) gmdate('U');
					}

					if ( strpos( $timing, '++' ) === 0 ) {
						$time = str_replace( '++', '+', $this->settings[$pf.'timing'] );
					} else {
						$time = $timing;
					}

					$due_date = strtotime( $time, $tstamp );

					$this->issueEvent( $request, 'email', $due_date, array(), array( 'emailid' => $i ) );
				}
			}
		}

		return true;
	}

	function aecEventHookEmail( $request )
	{
		$pf = 'email_' . $request->event->params['emailid'] . '_';

		$message	= AECToolbox::rewriteEngineRQ( $this->settings[$pf.'text'], $request );
		$subject	= AECToolbox::rewriteEngineRQ( $this->settings[$pf.'subject'], $request );

		if ( empty( $message ) ) {
			return null;
		}

		$recipient = $cc = $bcc = array();

		$rec_groups = array( "recipient", "cc", "bcc" );

		foreach ( $rec_groups as $setting ) {
			$list = AECToolbox::rewriteEngineRQ( $this->settings[$pf.$setting], $request );

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

		xJ::sendMail( $this->settings['sender'], $this->settings['sender_name'], $recipient, $subject, $message, $this->settings[$pf.'text_html'], $cc, $bcc );
	}
}
?>

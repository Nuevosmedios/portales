<?php
/**
 * @version $Id: offline_payment3.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Offline Payment
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_offline_payment3 extends processor
{
	function info()
	{
		$info = array();
		$info['name']			= 'offline_payment3';
		$info['longname']		= JText::_('CFG_OFFLINE_PAYMENT3_LONGNAME');
		$info['statement']		= JText::_('CFG_OFFLINE_PAYMENT3_STATEMENT');
		$info['description']	= JText::_('CFG_OFFLINE_PAYMENT3_DESCRIPTION');
		$info['currencies']		= AECToolbox::aecCurrencyField( true, true, true, true );
		$info['cc_list']		= "";
		$info['recurring']		= 0;
		$info['actions']		= array( 'email' => array() );

		return $info;
	}

	function getLogoFilename()
	{
		return '';
	}

	function checkoutText()
	{
		return "";
	}

	function getActions( $invoice, $subscription )
	{
		$actions = parent::getActions( $invoice, $subscription );

		if ( empty( $this->settings['email_link'] ) ) {
			unset( $actions['email'] );
		}

		return $actions;
	}

	function modifyCheckout( &$int_var, &$InvoiceFactory )
	{
		if ( !empty( $this->settings['paylater'] ) ) {
			$InvoiceFactory->invoice->pay( 1, true );

			$InvoiceFactory->invoice->fixed = true;
			$InvoiceFactory->invoice->storeload();

			$InvoiceFactory->thanks( 'com_acctexp', false, true );exit;
		}
	}

	function settings()
	{
		$settings = array();
		$settings['info']			= '';
		$settings['waitingplan']	= 0;
		$settings['paylater']		= 0;
		$settings['currency']		='';

		$settings['email_info']		= 0;
		$settings['email_link']		= 1;
		$settings['sender']			= "";
		$settings['sender_name']	= "";
		$settings['recipient']		= "[[user_email]]";
		$settings['bcc']			= "";
		$settings['subject']		= "";
		$settings['text_html']		= 0;
		$settings['text']			= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['waitingplan']		= array( 'list_plan' );
		$settings['paylater']			= array( 'toggle' );
		$settings['info']				= array( 'editor' );
		$settings['currency']			= array( 'list_currency' );

		$settings['email_info']			= array( 'toggle' );
		$settings['email_link']			= array( 'toggle' );

		$settings['sender']				= array( 'inputE' );
		$settings['sender_name']		= array( 'inputE' );

		$settings['recipient']			= array( 'inputE' );
		$settings['bcc']				= array( 'inputE' );

		$settings['subject']			= array( 'inputE' );
		$settings['text_html']			= array( 'toggle' );
		$settings['text']				= array( !empty( $this->settings['text_html'] ) ? 'editor' : 'inputD' );

 		$rewriteswitches				= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );
		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function customaction_email( $request )
	{
		$message	= AECToolbox::rewriteEngineRQ( $this->settings['text'], $request );
		$subject	= AECToolbox::rewriteEngineRQ( $this->settings['subject'], $request );

		if ( empty( $message ) ) {
			return null;
		}

		$recipients = AECToolbox::rewriteEngineRQ( $this->settings['recipient'], $request );
		$recips = explode( ',', $recipients );

        $recipients2 = array();
        foreach ( $recips as $k => $email ) {
            $recipients2[$k] = trim( $email );
        }
        $recipients = $recipients2;

		xJ::sendMail( $this->settings['sender'], $this->settings['sender_name'], $recipients, $subject, $message, $this->settings['text_html'] );

		return true;
	}

	function invoiceCreationAction( $objInvoice )
	{
		if ( $this->settings['email_info'] ) {
			$metaUser = new metaUser( $objInvoice->userid );

			$request = new stdClass();
			$request->metaUser	=&	$metaUser;
			$request->invoice	=&	$objInvoice;
			$request->plan		=&	$objInvoice->getObjUsage();

			$message	= AECToolbox::rewriteEngineRQ( $this->settings['text'], $request );
			$subject	= AECToolbox::rewriteEngineRQ( $this->settings['subject'], $request );

			if ( !empty( $message ) ) {
				$recipients = AECToolbox::rewriteEngineRQ( $this->settings['recipient'], $request );
				$recips = explode( ',', $recipients );

		        $recipients2 = array();
		        foreach ( $recips as $k => $email ) {
		            $recipients2[$k] = trim( $email );
		        }
		        $recipients = $recipients2;

				$bccipients = AECToolbox::rewriteEngineRQ( $this->settings['bcc'], $request );
				$bccips = explode( ',', $bccipients );

		        $bccipients2 = array();
		        foreach ( $bccips as $k => $email ) {
		            $bccipients2[$k] = trim( $email );
		        }
		        $bccipients = $bccipients2;

				if ( !empty( $bccipients2 ) ) {
					$bcc = $bccipients;
				} else {
					$bcc = null;
				}

				xJ::sendMail( $this->settings['sender'], $this->settings['sender_name'], $recipients, $subject, $message, $this->settings['text_html'], null, $bcc );
			}

		}

		if ( $this->settings['waitingplan'] ) {
			$db = &JFactory::getDBO();

			$metaUser = new metaUser( $objInvoice->userid );

			if ( !$metaUser->hasSubscription || in_array( $metaUser->objSubscription->status, array( 'Expired', 'Closed' ) ) ) {
				if ( !$metaUser->hasSubscription ) {
					$payment_plan = new SubscriptionPlan();
					$payment_plan->load( $this->settings['waitingplan'] );

					$metaUser->establishFocus( $payment_plan, 'offline_payment3', false );
				}

				$metaUser->objSubscription->applyUsage( $this->settings['waitingplan'], 'none', 0 );

				$short	= 'waiting plan';
				$event	= 'Offline Payment waiting plan assigned for ' . $objInvoice->invoice_number;
				$tags	= 'processor,waitingplan';
				$params = array( 'invoice_number' => $objInvoice->invoice_number );

				$eventlog = new eventLog();
				$eventlog->issue( $short, $tags, $event, 2, $params );
			}
		}
	}

}

?>

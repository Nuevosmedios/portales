<?php
/**
 * @version $Id: mi_aecinvoiceprintemail.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Invoice Printout Mailout
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_aecinvoiceprintemail
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_AECINVOICEPRINTEMAIL');
		$info['desc'] = JText::_('AEC_MI_DESC_AECINVOICEPRINTEMAIL');
		$info['type'] = array( 'aec.invoice', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		$rewriteswitches				= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );

		$settings = array();
		$settings['sender']				= array( 'inputE' );
		$settings['sender_name']		= array( 'inputE' );

		$settings['recipient']			= array( 'inputE' );
		$settings['cc']					= array( 'inputE' );
		$settings['bcc']				= array( 'inputE' );
		$settings['subject']			= array( 'inputE' );
		$settings['customcss']			= array( 'inputD' );
		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		$settings['aectab_pdf']			= array( 'tab', 'PDF Invoice', 'PDF Invoice' );
		$settings['make_pdf']			= array( 'toggle' );
		$settings['text_html']			= array( 'toggle' );
		$settings['text']				= array( !empty( $this->settings['text_html'] ) ? 'editor' : 'inputD' );
		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		$settings['aectab_reg']			= array( 'tab', 'Modify Invoice', 'Modify Invoice' );

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

		$settings						= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function relayAction( $request )
	{
		if ( $request->action != 'action' ) {
			return null;
		}

		if ( empty( $request->invoice->id ) ){
			return null;
		}

		if ( empty( $this->settings['subject'] ) ) {
			return null;
		}

		if ( isset( $request->invoice->params['mi_aecinvoiceprintemail'] ) ) {
			if ( ( ( (int) gmdate('U') ) - $request->invoice->params['mi_aecinvoiceprintemail'] ) < 10 ) {
				// Seems like we have a card processing, skip
				return null;
			}
		}

		$subject	= AECToolbox::rewriteEngineRQ( $this->settings['subject' . $request->area], $request );

		if ( !empty( $this->settings['customcss'] ) ) {
			$css = $this->settings['customcss'];
		} else {
			$cssfile = JPATH_SITE . '/media/com_acctexp/css/invoice.css';

			if ( file_exists( $cssfile ) ) {
				$css = file_get_contents( $cssfile );
			} else {
				$css = "";
			}
		}

		$message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>' . $subject . '</title>
	<meta http-equiv="Content-Type" content="text/html;" />
	<style type="text/css">' . $css . '</style>
</head>
<body style="padding:0;margin:0;background-color:#fff;" >';

		$message .= $this->getInvoice( $request->invoice ) . '</body></html>';

		if ( empty( $message ) ) {
			return null;
		}

		$attachment = null;
		$html_mode = true;
		if ( !empty( $this->settings['make_pdf'] ) ) {
			$app = JFactory::getApplication();

			require_once( JPATH_SITE . '/components/com_acctexp/lib/tcpdf/config/lang/eng.php' );
			require_once( JPATH_SITE . '/components/com_acctexp/lib/tcpdf/tcpdf.php' );

			$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->AddPage();
			$pdf->writeHTML($message, true, false, true, false, '');

			if ( !empty( $request->invoice->invoice_number_format ) ) {
				$name = $request->invoice->invoice_number_format;
			} else {
				$name = $request->invoice->invoice_number;
			}

			$fname = preg_replace("/[^a-z0-9@._+-]/i", '', $name) . '.pdf';

			$content = $pdf->Output( $fname, 'S');

			$attachment = $app->getCfg( 'tmp_path' ) . '/' . $fname;

			$fp = fopen( $attachment, 'w' );
			fwrite( $fp, $content );
			fclose( $fp );

			$message = $this->settings['text'];
			
			$html_mode = $this->settings['text_html'];
		}

		$recipient = $cc = $bcc = null;

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

		xJ::sendMail( $this->settings['sender'], $this->settings['sender_name'], $recipient, $subject, $message, $html_mode, $cc, $bcc, $attachment );

		if ( !empty( $attachment ) ) {
			unlink( $attachment );
		}

		$request->invoice->params['mi_aecinvoiceprintemail'] = (int) gmdate('U');
		$request->invoice->storeload();

		return true;
	}

	function getInvoice( $invoice )
	{
		ob_start();

		$iFactory = new InvoiceFactory( $invoice->userid, null, null, null, null, null, false, true );
		$iFactory->invoiceprint( 'com_acctexp', $invoice->invoice_number, false, array( 'mi_aecinvoiceprintemail' => true ), true );

		$content = AECToolbox::rewriteEngineRQ( ob_get_contents(), $iFactory );

		ob_end_clean();

		return $content;
	}

	function invoice_printout( $request )
	{
		// Only handle self-calls
		if ( !isset( $request->add['mi_aecinvoiceprintemail'] ) ) {
			return true;
		}

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

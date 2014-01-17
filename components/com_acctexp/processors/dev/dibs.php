<?php
/**
 * @version $Id: dibs.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - DIBS
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author Thailo van Ree <info@transwarp.nl> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

define('CFG_DIBS_LONGNAME','DIBS Internet');
define('CFG_DIBS_STATEMENT','The leading payment solution in the Nordic countries.');
define('CFG_DIBS_DESCRIPTION','DIBS Internet is the safe payment solution for e-commerce shops wishing to receive online-payments with payment cards, bank payments or one of the other payment types accessible through DIBS.');

define('CFG_DIBS_MERCHANT_NAME','Merchant ID');
define('CFG_DIBS_MERCHANT_DESC','Your DIBS merchant ID');
define('CFG_DIBS_MD5_KEY1_NAME','MD5 KEY 1');
define('CFG_DIBS_MD5_KEY1_DESC','Your MD5 KEY 1');
define('CFG_DIBS_MD5_KEY2_NAME','MD5 KEY 2');
define('CFG_DIBS_MD5_KEY2_DESC','Your MD5 KEY 2');
define('CFG_DIBS_PAYMENTMETHODS_NAME','Payment Methods');
define('CFG_DIBS_PAYMENTMETHODS_DESC','Select the payment methods you enabled in your DIBS administration panel.');


class processor_dibs extends POSTprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'dibs';
		$info['longname']				= CFG_DIBS_LONGNAME;
		$info['statement']				= CFG_DIBS_STATEMENT;
		$info['description']			= CFG_DIBS_DESCRIPTION;
		$info['currencies']				= 'DKK,EUR,USD,GBP,SEK,AUD,CAD,ISK,JPY,NZD,NOK,CHF,TRY';
		$info['cc_list']				= 'visa,mastercard';
		$info['notify_trail_thanks']	= true;

		return $info;
	}

	function settings()
	{
		$app = JFactory::getApplication();

		$settings = array();

		$settings['testmode']			= true;
		$settings['merchant']			= '--';
		$settings['md5_key1']			= '--';
		$settings['md5_key2']			= '--';
		$settings['payment_methods']	= array( 'ACC' );
		$settings['currency'] 			= 'DKK';
		
		$settings['item_name']			= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['customparams']		= "";

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']			= array( 'toggle' );
		$settings['merchant'] 			= array( 'inputC' );
		$settings['md5_key1'] 			= array( 'inputC' );
		$settings['md5_key2'] 			= array( 'inputC' );
		$settings['payment_methods']	= array( 'list');
		$settings['currency'] 			= array( 'list_currency' );
		$settings['item_name']			= array( 'inputE');
		$settings['customparams']		= array( 'inputD' );

		$methods = $this->getPaymentMethods();

		$pmethods = array();
		$pmethodssel = array();
		foreach ( $methods as $name => $key ) {
			$pmethods[] = JHTML::_('select.option', $key, $name );

			if ( !empty( $this->settings['payment_methods'] )) {
				if ( in_array( $key, $this->settings['payment_methods'] ) ) {
					$pmethodssel[] = JHTML::_('select.option', $key, $name );
				}
			}
		}

		$settings['lists']['payment_methods'] = JHTML::_( 'select.genericlist', $pmethods, 'payment_methods[]', 'size="8" multiple="multiple"', 'value', 'text', $pmethodssel );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function checkoutAction( $request, $InvoiceFactory=null, $xvar=null )
	{
		if ( empty( $xvar ) ) {
			$var = $this->createGatewayLink( $request );

			if ( !empty( $this->settings['customparams'] ) ) {
				$var = $this->customParams( $this->settings['customparams'], $var, $request );
			}
		} else {
			$var = $xvar;
		}

		if ( isset( $var['_aec_checkout_onclick'] ) ) {
			$onclick = 'onclick="' . $var['_aec_checkout_onclick'] . '"';
			unset( $var['_aec_checkout_onclick'] );
		} else {
			$onclick = "";
		}

		$return = '<form action="' . $var['post_url'] . '" method="post">' . "\n";
		unset( $var['post_url'] );

		foreach ( $var as $key => $value ) {
			$return .= '<input type="hidden" name="' . $key . '" value="' . $value . '" />' . "\n";
		}
		
		$methods = $this->getPaymentMethods();
		
		foreach ( $methods as $description => $id) {
			$options[]	= JHTML::_('select.option', htmlspecialchars($id), htmlspecialchars($description) );
		}		

		$return .= JText::_('CFG_MULTISAFEPAY_SELECT_GATEWAY') . "&nbsp;&nbsp;" . JHTML::_( 'select.genericlist', $options, 'payment_method', 'size="1"', 'value', 'text', null );		
		
		$return .= "&nbsp;&nbsp;";
		
		$country_code_list = array ( 'DK', 'GB', 'ES', 'FI', 'FO', 'FR', 'IT', 'NL', 'NO', 'PL', 'SV' );
		$code_list = array();
		foreach ( $country_code_list as $country ) {
			$code_list[] = JHTML::_('select.option', $country, $country . " - " . JText::_( 'COUNTRYCODE_' . $country ) );
		}

		$return .=  JText::_('CFG_MULTISAFEPAY_SELECT_COUNTRY') . "&nbsp;&nbsp;" . JHTML::_( 'select.genericlist', $code_list, 'delivery03.Country', 'size="1"', 'value', 'text', 'NL' );		
		
		$return .= '<input type="submit" class="button" id="aec-checkout-btn" ' . $onclick . ' value="' . JText::_('BUTTON_CHECKOUT') . '" />' . "\n";
		$return .= '</form>' . "\n";
aecdebug("checkoutAction");aecdebug($return);
		return $return;
	}	

	function createGatewayLink( $request )
	{
		require_once('dibs/DIBSFunctions.php');
		
		$var = array();
		
		if ($this->settings['testmode']) {
			$var['test'] = 'yes';
		}

		$orderid	= $request->invoice->invoice_number;
		$amount		= $request->int_var['amount'] * 100;
		$currency	= getCurrency($this->settings['currency']);
		$merchant	= $this->settings['merchant'];
			
		$var['post_url']		= 'https://payment.architrade.com/paymentweb/start.action';		
		$var['orderid']			= $orderid;
		$var['merchant']		= $merchant;
		$var['cancelurl']		= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=cancel' );
		$var['accepturl']		= AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=dibsnotification' );

		$var['delivery01.Firstname']	= trim($request->metaUser->cmsUser->username);
		$var['delivery02.Lastname']		= trim($request->metaUser->cmsUser->name);
		//$var['delivery03.Country'] 		= trim("");
		$var['delivery04.Email']		= trim($request->metaUser->cmsUser->email);	
		
		$var['ordline0-1'] = 'Subscription';
		$var['ordline0-2'] = 'Description';
		$var['ordline0-3'] = 'Amount';
		$var['ordline1-1'] = $request->plan->name;
		$var['ordline1-2'] = AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request );
		$var['ordline1-3'] = $request->int_var['amount'];

		$var['amount']				= $amount;
		$var['currency']			= $currency;
		$var['doNotShowLastPage']	= "true";

		$md5key = md5(
					$this->settings['md5_key2'] . md5(
						$this->settings['md5_key1']
							. 'merchant=' . $merchant
							. '&orderid=' . $orderid
							. '&currency=' . $currency
							. '&amount=' . $amount)
					);
		
		$var["md5key"] = $md5key;		
aecdebug("createGatewayLink");aecdebug($var);
		return $var;
	}

	function parseNotification( $post )
	{aecdebug("parseNotification");aecdebug($post);aecdebug($_REQUEST);
		$response				= array();
		$response['valid']		= true;
		$response['invoice']	= aecGetParam( 'orderid', '', true, array( 'word', 'string', 'clear_nonalnum' ) );

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{aecdebug("validateNotification");aecdebug($response);
		require_once('dibs/DIBSFunctions.php');
		
		$response['valid'] = false;
		
		$transInfo = DIBSTransInfo( $response['orderid'], 
									$this->settings['merchant'], 
									$this->settings['currency'],
									$response['amount'] );

		$response['valid'] = true;

		return $response;
	}
	
	function getPaymentMethods() 
	{
		return array(	'ABN AMRO iDeal Payment' => 'ABN',
						'Accept card' => 'ACCEPT',
						'Aktia Web Payment' => 'AKTIA',
						'Albertslund Centrum Kundekort' => 'ACK',
						'American Express' => 'AMEX',
						'Apollo-/Kuonikonto' => 'AKK',
						'Århus city kort' => 'AAK',
						'Bank Einzug (eOLV)' => 'ELV',
						'BankAxess' => 'BAX',
						'Bauhaus Best card' => 'BHBC',
						'BG Netbetaling' => 'BG',
						'CoinClick' => 'CC',
						'Computer City Customer Card' => 'CCK',
						'Daells Bolighus Kundekort' => 'DAELLS',
						'Dankort' => 'DK',
						'Danske Netbetaling (Danske Bank)' => 'DNB',
						'Diners Club' => 'DIN',
						'eCredit Payment' => 'ECRED',
						'eDankort' => 'EDK',
						'Electronic World Credit Card' => 'EWORLD',
						'Fields Shoppingcard' => 'FISC',
						'Finax (SE)' => 'FINX(SE)',
						'Fisketorvet Shopping Card' => 'FSC',
						'Fleggard kort' => 'FLEGCARD',
						'Forbrugsforeningen Card' => 'FFK',
						'Ford Credit Card' => 'FCC',
						'Frederiksberg Centret Kundekort' => 'FCK',
						'Getitcard' => 'GIT',
						'Glostrup Shopping Card' => 'GSC',
						'Graphium' => 'GRA',
						'Handelsbanken Köpkort' => 'SHB',
						'HansaBank' => 'HNS',
						'Harald Nyborg' => 'HNYBORG',
						'Hemtex clubkort' => 'HEMTX',
						'Hemtex faktura' => 'HEME',
						'Hemtex personalkort' => 'HEMP',
						'Hillerød Shopping Card' => 'HSC',
						'HM Konto (Hennes og Mauritz)' => 'HMK',
						'Hydro Texaco' => 'HTX',
						'iDeal Web Payment (ING Bank)' => 'ING',
						'IKEA kort' => 'IKEA',
						'Inspiration Best Card' => 'IBC',
						'JCB (Japan Credit Bureau)' => 'JCB',
						'Jem&Fix Kundekort' => 'JEM_FIX',
						'Kaupthing Bankkort' => 'KAUPBK',
						'Kreditor' => 'KRE',
						'Lærernes IndkøbsCentral (Denmark)' => 'LIC(DK)',
						'Lærernes IndkøbsCentral (Sweden)' => 'LIC(SE)',
						'Länsförsäkringar Bank Bankkort' => 'LFBBK',
						'LO Plus Guldkort' => 'LOPLUS',
						'Maestro' => 'MTRO',
						'Mastercard' => 'MC',
						'Medmera' => 'MEDM',
						'Merlin Kreditkort' => 'MERLIN',
						'My Holiday Card' => 'MYHC',
						'Nordea Bankkort' => 'NSBK',
						'Nordea Solo-E betaling (Denmark)' => 'SOLO',
						'Nordea Solo-E betaling (Sweden)' => 'NDB',
						'Nordea Solo-E payment (Finland)' => 'SOLOFI',
						'OKO Web Payment' => 'OKO',
						'Östgöta Enskilda Bankkort' => 'OESBK',
						'PagoCard' => 'PAGOC',
						'PayByBill' => 'PBB',
						'Q8 ServiceKort' => 'Q8SK',
						'Q8/LIC' => 'Q8LIC',
						'Rejsekonto' => 'RK',
						'Remember Card' => 'REMCARD',
						'Resurs Bank' => 'REB',
						'Rødovre Centerkort' => 'ROEDCEN',
						'Royal Bank of Scotland iDeal Payment' => 'RBS',
						'Sampo Web Payment' => 'SAMPO',
						'SEB Bankkort' => 'SEBSBK',
						'SEB Direktbetalning' => 'SEB',
						'SEB Köpkort' => 'SEB_KOBK',
						'SHB Direktbetalning' => 'SHB',
						'Silvan Konto Erhverv' => 'SILV_ERHV',
						'Silvan Konto Privat' => 'SILV_PRIV',
						'Skandiabanken Bankkort' => 'SBSBK',
						'Sparbank Vestkort' => 'ISHBY',
						'Spies/Tjæreborg' => 'S/T',
						'Star Tour' => 'STARTOUR',
						'Swedbank Direktbetalning' => 'FSB',
						'Tæppeland' => 'TLK',
						'Toys R Us - BestCard' => 'TUBC',
						'VEKO Finans' => 'VEKO',
						'VISA' => 'VISA'
						);		
	}

}

?>

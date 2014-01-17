<?php
/**
 * @version $Id: ideal_advanced.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - iDeal Advanced
 * @author Thailo van Ree, David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_ideal_advanced extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']					= 'ideal_advanced';
		$info['longname']				= JText::_('CFG_IDEAL_ADVANCED_LONGNAME');
		$info['statement']				= JText::_('CFG_IDEAL_ADVANCED_STATEMENT');
		$info['description']			= JText::_('CFG_IDEAL_ADVANCED_DESCRIPTION');
		$info['currencies']				= 'EUR';
		$info['languages']				= 'NL';
		$info['recurring']	   			= 0;
		$info['notify_trail_thanks']	= true;

		return $info;
	}

	function getLogoFilename()
	{
		return 'ideal.png';
	}

	function settings()
	{
		$settings = array();
		$settings['testmode']		= 0;
		$settings['merchantid']		= "123456789";
		$settings['acquirer']		= "sim";
		$settings['ideal_sub_id']	= 0;

		$settings['private_certificate_file']	= "private.cer";
		$settings['private_key']				= "Password";
		$settings['private_key_file']			= "private.key";
		
		$settings['cache_path']    	= dirname(__FILE__) . '/lib/cache/';
		$settings['ssl_path']    	= dirname(__FILE__) . '/lib/ssl/';
		
		$settings['currency']		= 'EUR';

		$settings['description']	= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );		
		
		$settings['customparams']	= "";
		
		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']		= array( 'toggle' );
		$settings['merchantid']		= array( 'inputC' );
		$settings['acquirer'] 		= array( 'list' );
		$settings['ideal_sub_id']	= array( 'inputC' );

		$settings['private_certificate_file']	= array( 'inputC' );
		$settings['private_key']				= array( 'inputC' );
		$settings['private_key_file']			= array( 'inputC' );
		
		$settings['cache_path']    	= array( 'inputD' );
		$settings['ssl_path']   	= array( 'inputD' );
		$settings['currency']		= array( 'list_currency' );
		$settings['description']	= array( 'inputE' );
		
		$acquirers = array(	'sim' => 'Simulator',
							'rabo' => 'Rabobank',
							'ing' => 'ING Bank',
							'abn' => 'ABN Amro' );	
					
		foreach ( $acquirers as $key => $name ) {
			$options[]	= JHTML::_('select.option', $key, $name );
		}		

		$settings['lists']['acquirer'] = JHTML::_( 'select.genericlist', $options, 'acquirer', 'size="1"', 'value', 'text', $this->settings['acquirer'] );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function checkoutform( $request )
	{
		$idealRequest = new IssuerRequest();

		$idealRequest->initMerchant( $this->settings );

		$issuerList = $idealRequest->doRequest();

		if( $idealRequest->hasErrors() ) {
			$this->fileError( 'Could not retrieve Issuer list. Error(s): ' . implode( ', ', $idealRequest->getErrorsDesc() ) );
		} else {		
			foreach ( $issuerList as $key => $name ) {
				$options[]	= JHTML::_('select.option', $key, $name );
			}
	
			$var['params']['lists']['issuerId'] = JHTML::_( 'select.genericlist', $options, 'issuerId', 'size="1"', 'value', 'text', null );
			$var['params']['issuerId'] = array( 'list', 'Kies uw bank', null );			
		}

		return $var;
	}

	function createRequestXML( $request )
	{
		return "";
	}

	function transmitRequestXML( $xml, $request )
	{
		$response				= array();
		$response['valid']		= false;

		$description 		= substr( AECToolbox::rewriteEngineRQ( $this->settings['description'], $request ), 0, 32 );
		$merchantReturnURL	= AECToolbox::deadsureURL( "index.php?option=com_acctexp&task=ideal_advancednotification" );
		
		$idealRequest = new TransactionRequest();
		$idealRequest->initMerchant( $this->settings );	

		$idealRequest->setOrderId			( $request->invoice->invoice_number );
		$idealRequest->setOrderDescription	( $description );
		$idealRequest->setOrderAmount		( $request->int_var['amount'] );	
		$idealRequest->setIssuerId			( $request->int_var['params']['issuerId'] );
		$idealRequest->setEntranceCode		( $request->invoice->invoice_number );
		$idealRequest->setReturnUrl			( $merchantReturnURL );

		$transactionId = $idealRequest->doRequest();

		if ( $idealRequest->hasErrors() ) {
			$return['error'] = implode( ', ', $idealRequest->getErrorsDesc() );

			return $return;
		} else {
			// Redirect
			return $idealRequest->doTransaction();
		}
	}

	function parseNotification( $post )
	{
		$response				= array();
		$response['valid']		= false;
		$response['invoice']	= aecGetParam( 'ec', '', true, array( 'word', 'string', 'clear_nonalnum' ) );

		return $response;
	}

	function validateNotification( $response, $post, $invoice )
	{
		$idealRequest = new StatusRequest();
		$idealRequest->initMerchant( $this->settings );

		$idealRequest->setTransactionId( trim( aecGetParam( 'trxid', '', true, array( 'word', 'string', 'clear_nonalnum' ) ) ) ); 

		$status = $idealRequest->doRequest();

		if ( $idealRequest->hasErrors() ) {
			$response['error']		= true;
			$response['errormsg']	= implode( ', ', $idealRequest->getErrorsDesc() );
		} elseif ( $status == 'SUCCESS' ) {
			$response['valid'] = true;
		}

		return $response;
	}
}

// =============================================================
// iDEAL API helper classes, courtesy of www.php-solutions.nl,
// integrated into AEC by Thailo van Ree.
// =============================================================

class IdealRequest
{
	protected $aErrors = array();
	
	// Security settings
	protected $sSecurePath;
	protected $sCachePath;
	protected $sPrivateKeyPass;
	protected $sPrivateKeyFile;
	protected $sPrivateCertificateFile;
	protected $sPublicCertificateFile;
	
	// Account settings
	protected $bABNAMRO = false; // ABN has some issues
	protected $sAcquirerName;
	protected $sAcquirerUrl;
	protected $bTestMode = false;
	protected $sMerchantId;
	protected $sSubId;
	
	// Constants
	protected $LF = "\n";
	protected $CRLF = "\r\n";
	
	public function __construct()
	{
	}

	public function initMerchant( $settings )
	{
		$this->setAcquirer		( $settings['acquirer'], $settings['testmode'] );
		$this->setCachePath		( $settings['cache_path'] );
		$this->setMerchant		( $settings['merchantid'],$settings['ideal_sub_id'] );
		$this->setPrivateKey	( $settings['private_key'], $settings['private_key_file'], $settings['private_certificate_file']);
		$this->setSecurePath	( $settings['ssl_path'] );
	}

	// Should point to directory with .cer and .key files
	public function setSecurePath($sPath)
	{
		$this->sSecurePath = $sPath;
	}
	
	// Should point to directory where cache is stored
	public function setCachePath($sPath = false)
	{
		$this->sCachePath = $sPath;
	}
	
	// Set password to generate signatures
	public function setPrivateKey($sPrivateKeyPass, $sPrivateKeyFile = false, $sPrivateCertificateFile = false)
	{
		$this->sPrivateKeyPass = $sPrivateKeyPass;
	
		if($sPrivateKeyFile)
		{
			$this->sPrivateKeyFile = $sPrivateKeyFile;
		}
	
		if($sPrivateCertificateFile)
		{
			$this->sPrivateCertificateFile = $sPrivateCertificateFile;
		}
	}
	
	// Set MerchantID id and SubID
	public function setMerchant($sMerchantId, $sSubId = 0)
	{
		$this->sMerchantId = $sMerchantId;
		$this->sSubId = $sSubId;
	}
	
	// Set Acquirer (Use: Rabobank, ING Bank or ABN Amro)
	public function setAcquirer($sAcquirer, $bTestMode = false)
	{
		$this->sAcquirerName = $sAcquirer;
		$this->bTestMode = $bTestMode;
	
		if(stripos($sAcquirer, 'rabo') !== false) // Rabobank
		{
			$this->sPublicCertificateFile = 'rabobank.cer';
			$this->sAcquirerUrl = 'ssl://ideal' . ($bTestMode ? 'test' : '') . '.rabobank.nl:443/ideal/iDeal';
		}
		elseif(stripos($sAcquirer, 'ing') !== false) // ING Bank
		{
			$this->sPublicCertificateFile = 'ingbank.cer';
			$this->sAcquirerUrl = 'ssl://ideal' . ($bTestMode ? 'test' : '') . '.secure-ing.com:443/ideal/iDeal';
		}
		elseif(stripos($sAcquirer, 'abn') !== false) // ABN AMRO
		{
			$this->bABNAMRO = true;
			$this->sPublicCertificateFile = 'abnamro' . ($bTestMode ? '.test' : '') . '.cer';
			$this->sAcquirerUrl = '';
			
			// With ABN AMRO, the AcquirerUrl depends on the request type
			$sClass = get_class($this);
	
			if(strcasecmp($sClass, 'issuerrequest') === 0)
			{
				if($bTestMode)
				{
					$this->sAcquirerUrl = 'ssl://itt.idealdesk.com:443/ITTEmulatorAcquirer/Directory.aspx';
				}
				else
				{
					$this->sAcquirerUrl = 'ssl://idealm.abnamro.nl:443/nl/issuerInformation/getIssuerInformation.xml';
				}
			}
			elseif(strcasecmp($sClass, 'transactionrequest') === 0)
			{
				if($bTestMode)
				{
					$this->sAcquirerUrl = 'ssl://itt.idealdesk.com:443/ITTEmulatorAcquirer/Transaction.aspx';
				}
				else
				{
					$this->sAcquirerUrl = 'ssl://idealm.abnamro.nl:443/nl/acquirerTrxRegistration/getAcquirerTrxRegistration.xml';
				}
			}
			elseif(strcasecmp($sClass, 'statusrequest') === 0)
			{
				if($bTestMode)
				{
					$this->sAcquirerUrl = 'ssl://itt.idealdesk.com:443/ITTEmulatorAcquirer/Status.aspx';
				}
				else
				{
					$this->sAcquirerUrl = 'ssl://idealm.abnamro.nl:443/nl/acquirerStatusInquiry/getAcquirerStatusInquiry.xml';
				}
			}
		}
		elseif(stripos($sAcquirer, 'sim') !== false) // IDEAL SIMULATOR
		{
			$this->sPublicCertificateFile = 'simulator.cer';
			$this->sAcquirerUrl = 'ssl://www.ideal-simulator.nl:443/professional/';
		}
		else // Unknown issuer
		{
			$this->setError('Unknown Acquirer. Please use "Rabobank", "ING Bank", "ABN Amro" or "Simulator".', false, __FILE__, __LINE__);
			return false;
		}
	}
	
	// Error functions
	protected function setError($sDesc, $sCode = false, $sFile = 0, $sLine = 0)
	{
		$this->aErrors[] = array('desc' => $sDesc, 'code' => $sCode, 'file' => $sFile, 'line' => $sLine);
	}
	
	public function getErrors()
	{
		return $this->aErrors;
	}

	public function getErrorsDesc()
	{
		$errors = $this->getErrors();

		$array = array();
		foreach ( $errors as $error ) {
			$array[] = $error['desc'];
		}

		return $array;
	}

	public function hasErrors()
	{
		return (count($this->aErrors) ? true : false);
	}
	
	// Validate configuration
	protected function checkConfiguration($aSettings = array('sSecurePath', 'sPrivateKeyPass', 'sPrivateKeyFile', 'sPrivateCertificateFile', 'sPublicCertificateFile', 'sAcquirerUrl', 'sMerchantId'))
	{
		$bOk = true;
	
		for($i = 0; $i < count($aSettings); $i++)
		{
			if(empty($this->$aSettings[$i]))
			{
				$bOk = false;
				$this->setError('Setting ' . $aSettings[$i] . ' is not configured.', false, __FILE__, __LINE__);
			}
		}
	
		return $bOk;
	}
	
	// Send GET/POST data through sockets
	protected function postToHost($url, $data, $timeout = 30)
	{
		$__url = $url;
		$idx = strrpos($url, ':');
		$host = substr($url, 0, $idx);
		$url = substr($url, $idx + 1);
		$idx = strpos($url, '/');
		$port = substr($url, 0, $idx);
		$path = substr($url, $idx);
	
		$fsp = fsockopen($host, $port, $errno, $errstr, $timeout);
		$res = '';
		
		if($fsp)
		{
			fputs($fsp, 'POST ' . $path . ' HTTP/1.0' . $this->CRLF);
			fputs($fsp, 'Host: ' . substr($host, 6) . $this->CRLF);
			fputs($fsp, 'Accept: text/html' . $this->CRLF);
			fputs($fsp, 'Accept: charset=ISO-8859-1' . $this->CRLF);
			fputs($fsp, 'Content-Length:' . strlen($data) . $this->CRLF);
			fputs($fsp, 'Content-Type: text/html; charset=ISO-8859-1' . $this->CRLF . $this->CRLF);
			fputs($fsp, $data, strlen($data));
	
			while(!feof($fsp))
			{
				$res .= @fgets($fsp, 128);
			}
	
			fclose($fsp);
		}
		else
		{
			$this->setError('Error while connecting to ' . $__url, false, __FILE__, __LINE__);
		}
	
		return $res;
	}
	
	// Get value within given XML tag
	protected function parseFromXml($key, $xml)
	{
		$begin = 0;
		$end = 0;
		$begin = strpos($xml, '<' . $key . '>');
		
		if($begin === false)
		{
			return false;
		}
	
		$begin += strlen($key) + 2;
		$end = strpos($xml, '</' . $key . '>');
	
		if($end === false)
		{
			return false;
		}
	
		$result = substr($xml, $begin, $end - $begin);
		return $this->unescapeXml($result);
	}
	
	// Remove space characters from string
	protected function removeSpaceCharacters($string)
	{
		if($this->bABNAMRO)
		{
			return preg_replace('/(\f|\n|\r|\t|\v)/', '', $string);
		}
		else
		{
			return preg_replace('/\s/', '', $string);
		}
	}
	
	// Escape (replace/remove) special characters in string
	protected function escapeSpecialChars($string)
	{
		$string = str_replace(array('à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ð', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', '§', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', '€', 'Ð', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', '§', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'Ÿ'), array('a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'ed', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 's', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'EUR', 'ED', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'S', 'U', 'U', 'U', 'U', 'Y', 'Y'), $string);
		$string = preg_replace('/[^a-zA-Z0-9\-\.\,\(\)_]+/', ' ', $string);
		$string = preg_replace('/[\s]+/', ' ', $string);
	
		return $string;
	}
	
	// Escape special XML characters
	protected function escapeXml($string)
	{
		return utf8_encode(str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	
	// Unescape special XML characters
	protected function unescapeXml($string)
	{
		return str_replace(array('&lt;', '&gt;', '&quot;', '&amp;'), array('<', '>', '"', '&'), utf8_decode($string));
	}
	
	// Security functions
	protected function getCertificateFingerprint($bPublicCertificate = false)
	{
		if($fp = fopen($this->sSecurePath . ($bPublicCertificate ? $this->sPublicCertificateFile : $this->sPrivateCertificateFile), 'r'))
		{
			$sRawData = fread($fp, 8192);
			fclose($fp);
	
			$sData = openssl_x509_read($sRawData);
	
			if(!openssl_x509_export($sData, $sData))
			{
				$this->setError('Error in certificate ' . $this->sSecurePath . ($bPublicCertificate ? $this->sPublicCertificateFile : $this->sPrivateCertificateFile), false, __FILE__, __LINE__);
				return false;
			}
		
			$sData = str_replace('-----BEGIN CERTIFICATE-----', '', $sData);
			$sData = str_replace('-----END CERTIFICATE-----', '', $sData);
	
			return strtoupper(sha1(base64_decode($sData)));
		}
		else
		{
			$this->setError('Cannot open certificate file: ' . ($bPublicCertificate ? $this->sPublicCertificateFile : $this->sPrivateCertificateFile), false, __FILE__, __LINE__);
		}
	}
	
	// Calculate signature of the given message
	protected function getSignature($sMessage)
	{
		$sMessage = $this->removeSpaceCharacters($sMessage);
	
		if($fp = fopen($this->sSecurePath . $this->sPrivateKeyFile, 'r'))
		{
			$sRawData = fread($fp, 8192);
			fclose($fp);
	
			$sSignature = '';
	
			if($sPrivateKey = openssl_get_privatekey($sRawData, $this->sPrivateKeyPass))
			{
				if(openssl_sign($sMessage, $sSignature, $sPrivateKey))
				{
					openssl_free_key($sPrivateKey);
					$sSignature = base64_encode($sSignature);
				}
				else
				{
					$this->setError('Error while signing message.', false, __FILE__, __LINE__);
				}
			}
			else
			{
				$this->setError('Invalid password for ' . $this->sPrivateKeyFile . ' file.', false, __FILE__, __LINE__);
			}
	
			return $sSignature;
		}
		else
		{
			$this->setError('Cannot open private key file: ' . $this->sPrivateKeyFile, false, __FILE__, __LINE__);
		}
	}
	
	// Validate signature for the given data
	protected function verifySignature($sData, $sSignature)
	{
		$bOk = false;
	
		if($fp = fopen($this->sSecurePath . $this->sPublicCertificateFile, 'r'))
		{
			$sRawData = fread($fp, 8192);
			fclose($fp);
	
			if($sPublicKey = openssl_get_publickey($sRawData))
			{
				$bOk = (openssl_verify($sData, $sSignature, $sPublicKey) ? true : false);
				openssl_free_key($sPublicKey);
			}
			else
			{
				$this->setError('Cannot retrieve key from public certificate file: ' . $this->sPublicCertificateFile, false, __FILE__, __LINE__);
			}
		}
		else
		{
			$this->setError('Cannot open public certificate file: ' . $this->sPublicCertificateFile, false, __FILE__, __LINE__);
		}
	
		return $bOk;
	}
}


class IssuerRequest extends IdealRequest
{
	public function __construct()
	{
		parent::__construct();
	}

	// Execute request (Lookup issuer list)
	public function doRequest()
	{
		if($this->checkConfiguration())
		{
			$sCacheFile = false;

			if($this->sCachePath)
			{
				$sCacheFile = $this->sCachePath . 'issuerrequest.cache';

				if(file_exists($sCacheFile) == false)
				{
					// Attempt to create cache file
					if(@touch($sCacheFile))
					{
						@chmod($sCacheFile, 0777);
					}
				}

				if(file_exists($sCacheFile) && is_readable($sCacheFile) && is_writable($sCacheFile))
				{
					if(filemtime($sCacheFile) > strtotime('-24 Hours'))
					{
						// Read data from cache file
						if($sData = file_get_contents($sCacheFile))
						{
							return unserialize($sData);
						}
					}
				}
				else
				{
					$sCacheFile = false;
				}
			}

			$sTimestamp = gmdate('Y-m-d') . 'T' . gmdate('H:i:s') . '.000Z';
			$sMessage = $this->removeSpaceCharacters($sTimestamp . $this->sMerchantId . $this->sSubId);

			$sToken = $this->getCertificateFingerprint();
			$sTokenCode = $this->getSignature($sMessage);

			$sXmlMessage = '<?xml version="1.0" encoding="UTF-8" ?>' . $this->LF
			. '<DirectoryReq xmlns="http://www.idealdesk.com/Message" version="1.1.0">' . $this->LF
			. '<createDateTimeStamp>' . $this->escapeXml($sTimestamp) . '</createDateTimeStamp>' . $this->LF
			. '<Merchant>' . $this->LF
			. '<merchantID>' . $this->escapeXml($this->sMerchantId) . '</merchantID>' . $this->LF
			. '<subID>' . $this->escapeXml($this->sSubId) . '</subID>' . $this->LF
			. '<authentication>SHA1_RSA</authentication>' . $this->LF
			. '<token>' . $this->escapeXml($sToken) . '</token>' . $this->LF
			. '<tokenCode>' . $this->escapeXml($sTokenCode) . '</tokenCode>' . $this->LF
			. '</Merchant>' . $this->LF
			. '</DirectoryReq>';

			$sXmlReply = $this->postToHost($this->sAcquirerUrl, $sXmlMessage, 10);

			if($sXmlReply)
			{
				if($this->parseFromXml('errorCode', $sXmlReply)) // Error found
				{
					// Add error to error-list
					$this->setError($this->parseFromXml('errorMessage', $sXmlReply) . ' - ' . $this->parseFromXml('errorDetail', $sXmlReply), $this->parseFromXml('errorCode', $sXmlReply), __FILE__, __LINE__);
				}
				else
				{
					$aIssuerShortList = array();
					$aIssuerLongList = array();

					while(strpos($sXmlReply, '<issuerID>'))
					{
						$sIssuerId = $this->parseFromXml('issuerID', $sXmlReply);
						$sIssuerName = $this->parseFromXml('issuerName', $sXmlReply);
						$sIssuerList = $this->parseFromXml('issuerList', $sXmlReply);

						if(strcmp($sIssuerList, 'Short') === 0) // Short list
						{
							// Only support ABN Amro Bank when in HTTPS mode.
							// if((strcasecmp(substr($_SERVER['SERVER_PROTOCOL'], 0, 5), 'HTTPS') === 0) || (stripos($sIssuerName, 'ABN') === false))
							{
								$aIssuerShortList[$sIssuerId] = $sIssuerName;
							}
						}
						else // Long list
						{
							$aIssuerLongList[$sIssuerId] = $sIssuerName;
						}

						$sXmlReply = substr($sXmlReply, strpos($sXmlReply, '</issuerList>') + 13);
					}

					$aIssuerList = array_merge($aIssuerShortList, $aIssuerLongList);

					// Save data in cache?
					if($sCacheFile)
					{
						if($oHandle = fopen($sCacheFile, 'w'))
						{
							fwrite($oHandle, serialize($aIssuerList));
							fclose($oHandle);
						}
					}

					return $aIssuerList;
				}
			}
		}

		return false;
	}
}

class TransactionRequest extends IdealRequest
{
	protected $sOrderId;
	protected $sOrderDescription;
	protected $iOrderAmount;
	protected $sReturnUrl;
	protected $sIssuerId;
	protected $sEntranceCode;

	// Transaction info
	protected $sTransactionId;
	protected $sTransactionUrl;

	public function __construct()
	{
		parent::__construct();

		// Random EntranceCode
		$this->sEntranceCode = sha1(rand(1000000, 9999999));
	}

	public function setOrderId($sOrderId)
	{
		$this->sOrderId = substr($sOrderId, 0, 16);
	}

	public function setOrderDescription($sOrderDescription)
	{
		$this->sOrderDescription = substr($this->escapeSpecialChars($sOrderDescription), 0, 32);
	}

	public function setOrderAmount($fOrderAmount)
	{
		$this->iOrderAmount = round($fOrderAmount * 100);
	}

	public function setReturnUrl($sReturnUrl)
	{
		// Fix for ING Bank, urlescape [ and ]
		$sReturnUrl = str_replace('[', '%5B', $sReturnUrl);
		$sReturnUrl = str_replace(']', '%5D', $sReturnUrl);

		$this->sReturnUrl = substr($sReturnUrl, 0, 512);
	}

	// ID of the selected bank
	public function setIssuerId($sIssuerId)
	{
		$this->sIssuerId = $sIssuerId;
	}

	// A random generated entrance code
	public function setEntranceCode($sEntranceCode)
	{
		$this->sEntranceCode = substr($sEntranceCode, 0, 40);
	}

	// Retrieve the transaction URL recieved in the XML response of de IDEAL SERVER
	public function getTransactionUrl()
	{
		return $this->sTransactionUrl;
	}

	// Execute request (Setup transaction)
	public function doRequest()
	{
		if($this->checkConfiguration() && $this->checkConfiguration(array('sOrderId', 'sOrderDescription', 'iOrderAmount', 'sReturnUrl', 'sReturnUrl', 'sIssuerId', 'sEntranceCode')))
		{
			$sTimestamp = gmdate('Y-m-d') . 'T' . gmdate('H:i:s') . '.000Z';
			$sMessage = $this->removeSpaceCharacters($sTimestamp . $this->sIssuerId . $this->sMerchantId . $this->sSubId . $this->sReturnUrl . $this->sOrderId . $this->iOrderAmount . 'EUR' . 'nl' . $this->sOrderDescription . $this->sEntranceCode);
			$sToken = $this->getCertificateFingerprint();
			$sTokenCode = $this->getSignature($sMessage);

			$sXmlMessage = '<?xml version="1.0" encoding="UTF-8" ?>' . $this->LF
			. '<AcquirerTrxReq xmlns="http://www.idealdesk.com/Message" version="1.1.0">' . $this->LF
			. '<createDateTimeStamp>' . $this->escapeXml($sTimestamp) .  '</createDateTimeStamp>' . $this->LF
			. '<Issuer>' . $this->LF
			. '<issuerID>' . $this->escapeXml($this->sIssuerId) . '</issuerID>' . $this->LF
			. '</Issuer>' . $this->LF
			. '<Merchant>' . $this->LF 
			. '<merchantID>' . $this->escapeXml($this->sMerchantId) . '</merchantID>' . $this->LF
			. '<subID>' . $this->escapeXml($this->sSubId) . '</subID>' . $this->LF
			. '<authentication>SHA1_RSA</authentication>' . $this->LF
			. '<token>' . $this->escapeXml($sToken) . '</token>' . $this->LF
			. '<tokenCode>' . $this->escapeXml($sTokenCode) . '</tokenCode>' . $this->LF
			. '<merchantReturnURL>' . $this->escapeXml($this->sReturnUrl) . '</merchantReturnURL>' . $this->LF
			. '</Merchant>' . $this->LF
			. '<Transaction>' . $this->LF
			. '<purchaseID>' . $this->escapeXml($this->sOrderId) . '</purchaseID>' . $this->LF
			. '<amount>' . $this->escapeXml($this->iOrderAmount) . '</amount>' . $this->LF
			. '<currency>EUR</currency>' . $this->LF
			. '<expirationPeriod>PT30M</expirationPeriod>' . $this->LF
			. '<language>nl</language>' . $this->LF
			. '<description>' . $this->escapeXml($this->sOrderDescription) . '</description>' . $this->LF
			. '<entranceCode>' . $this->escapeXml($this->sEntranceCode) . '</entranceCode>' . $this->LF
			. '</Transaction>' . $this->LF 
			. '</AcquirerTrxReq>';

			$sXmlReply = $this->postToHost($this->sAcquirerUrl, $sXmlMessage, 10);

			if($sXmlReply)
			{
				if($this->parseFromXml('errorCode', $sXmlReply)) // Error found
				{
					// Add error to error-list
					$this->setError($this->parseFromXml('errorMessage', $sXmlReply) . ' - ' . $this->parseFromXml('errorDetail', $sXmlReply), $this->parseFromXml('errorCode', $sXmlReply), __FILE__, __LINE__);
				}
				else
				{
					$this->sTransactionId = $this->parseFromXml('transactionID', $sXmlReply);
					$this->sTransactionUrl = html_entity_decode($this->parseFromXml('issuerAuthenticationURL', $sXmlReply));

					return $this->sTransactionId;
				}
			}
		}

		return false;
	}

	// Start transaction
	public function doTransaction()
	{
		if((sizeof($this->aErrors) == 0) && $this->sTransactionId && $this->sTransactionUrl)
		{
			header('Location: ' . $this->sTransactionUrl);
			exit;
		}

		$this->setError('Please setup a valid transaction request first.', false, __FILE__, __LINE__);
		return false;
	}
}

class StatusRequest extends IdealRequest
{
	// Account info
	protected $sAccountCity;
	protected $sAccountName;
	protected $sAccountNumber;

	// Transaction info
	protected $sTransactionId;
	protected $sTransactionStatus;

	public function __construct()
	{
		parent::__construct();
	}

	// Set transaction id
	public function setTransactionId($sTransactionId)
	{
		$this->sTransactionId = $sTransactionId;
	}

	// Get account city
	public function getAccountCity()
	{
		if(!empty($this->sAccountCity))
		{
			return $this->sAccountCity;
		}

		return '';
	}

	// Get account name
	public function getAccountName()
	{
		if(!empty($this->sAccountName))
		{
			return $this->sAccountName;
		}

		return '';
	}

	// Get account number
	public function getAccountNumber()
	{
		if(!empty($this->sAccountNumber))
		{
			return $this->sAccountNumber;
		}

		return '';
	}

	// Execute request
	public function doRequest()
	{
		if($this->checkConfiguration() && $this->checkConfiguration(array('sTransactionId')))
		{
			$sTimestamp = gmdate('Y-m-d') . 'T' . gmdate('H:i:s') . '.000Z';
			$sMessage = $this->removeSpaceCharacters($sTimestamp . $this->sMerchantId . $this->sSubId . $this->sTransactionId);
			$sToken = $this->getCertificateFingerprint();
			$sTokenCode = $this->getSignature($sMessage);

			$sXmlMessage = '<?xml version="1.0" encoding="UTF-8" ?>' . $this->LF
			. '<AcquirerStatusReq xmlns="http://www.idealdesk.com/Message" version="1.1.0">' . $this->LF
			. '<createDateTimeStamp>' . $this->escapeXml($sTimestamp) . '</createDateTimeStamp>' . $this->LF
			. '<Merchant>' 
			. '<merchantID>' . $this->escapeXml($this->sMerchantId) . '</merchantID>' . $this->LF
			. '<subID>' . $this->escapeXml($this->sSubId) . '</subID>' . $this->LF
			. '<authentication>SHA1_RSA</authentication>' . $this->LF
			. '<token>' . $this->escapeXml($sToken) . '</token>' . $this->LF
			. '<tokenCode>' . $this->escapeXml($sTokenCode) . '</tokenCode>' . $this->LF
			. '</Merchant>' . $this->LF
			. '<Transaction>' 
			. '<transactionID>' . $this->escapeXml($this->sTransactionId) . '</transactionID>' . $this->LF
			. '</Transaction>' 
			. '</AcquirerStatusReq>';

			$sXmlReply = $this->postToHost($this->sAcquirerUrl, $sXmlMessage, 10);

			if($sXmlReply)
			{
				if($this->parseFromXml('errorCode', $sXmlReply)) // Error found
				{
					// Add error to error-list
					$this->setError($this->parseFromXml('errorMessage', $sXmlReply) . ' - ' . $this->parseFromXml('errorDetail', $sXmlReply), $this->parseFromXml('errorCode', $sXmlReply), __FILE__, __LINE__);
				}
				else
				{
					$sTimestamp = $this->parseFromXml('createDateTimeStamp', $sXmlReply);
					$sTransactionId = $this->parseFromXml('transactionID', $sXmlReply);
					$sTransactionStatus = $this->parseFromXml('status', $sXmlReply);

					$sAccountNumber = $this->parseFromXml('consumerAccountNumber', $sXmlReply);
					$sAccountName = $this->parseFromXml('consumerName', $sXmlReply);
					$sAccountCity = $this->parseFromXml('consumerCity', $sXmlReply);

					$sMessage = $this->removeSpaceCharacters($sTimestamp . $sTransactionId . $sTransactionStatus . $sAccountNumber);

					$sSignature = base64_decode($this->parseFromXml('signatureValue', $sXmlReply));
					$sFingerprint = $this->parseFromXml('fingerprint', $sXmlReply);

					if(strcasecmp($sFingerprint, $this->getCertificateFingerprint(true)) !== 0)
					{
						// Invalid Fingerprint
						$this->setError('Unknown fingerprint.', false, __FILE__, __LINE__);
					}
					elseif($this->verifySignature($sMessage, $sSignature) == false)
					{
						// Invalid Fingerprint
						$this->setError('Bad signature.', false, __FILE__, __LINE__);
					}
					else
					{
						// $this->sTransactionId = $sTransactionId;
						$this->sTransactionStatus = strtoupper($sTransactionStatus);

						$this->sAccountCity = $sAccountCity;
						$this->sAccountName = $sAccountName;
						$this->sAccountNumber = $sAccountNumber;

						return $this->sTransactionStatus;
					}
				}
			}
		}

		return false;
	}
}
?>

<?php
/**
 * Mollie Wallie API
 *
 * For Mollie B.V.
 * More information? Go to www.mollie.nl
 *
 * LICENSE
 *
 * You can use this code freely, if you don't change this comment.
 * Do you make any changes, please provide them back to help us all
 *
 * @category   Mollie
 * @package    Mollie_Wallie
 * @copyright  (c) 2009 Mollie B.V.
 * @author     René Feiner
 * @link       http://www.mollie.nl/
 * @version 1.1
 */

class Mollie_Wallie 
{
	private $partnerId        = 0;
	private $transactionId    = 0;
	private $amount           = 0;
	private $reportUrl        = '';
	private $returnUrl        = '';
	private $language         = 'NL';     // Default: Dutch (NL)
	private $currency         = 'EUR';    // Default: Euro
	private $wallieUrl        = '';
	private $payed            = false;
	private $statusMessage    = '';

	protected $_errorMessage = '';
	protected $_errorCode = 0;

	/**
	* Constructor
	*
	* @param integer $partnerId
	*/
	function __construct($partnerId) 
	{
		$this->setPartnerID($partnerId);
	}

	/**
	* Prepare a Payment
	*
	* @param integer $amount
	* @param string $reportUrl
	* @param string $returnUrl
	* @return boolean True on succes, false otherwise
	*/
	public function createPayment($amount, $reportUrl, $returnUrl) 
	{
    
		if (!$this->setAmount($amount)
			|| !$this->setReportUrl($reportUrl)
			|| !$this->setReturnUrl($returnUrl)) 
		{
			$this->_errorMessage = "The given arguments are incorrect or incomplete.";
			return false;
		}

		$result = $this->sendToHost('www.mollie.nl', '/xml/wallie/',
									'a=fetch' .
									'&partnerid=' . urlencode($this->getPartnerId()) .
									'&amount=' .    urlencode($this->getAmount()) .
									'&reporturl=' . urlencode($this->getReportUrl()) .
									'&returnurl=' . urlencode($this->getReturnUrl()) .
									'&language=' .  urlencode($this->getLanguage())
		);
		
		if (empty($result)) 
		{
			return false;
		}

		list($headers, $xml) = preg_split("/(\r?\n){2}/", $result, 2);

		try {
			$data = $this->_XMLtoObject($xml);

			if ($data == false || $this->_XMLisError($data)) {
				return false;
			}

			$data          = $data->order;
			$transactionId = (int) $data->transaction_id;
			$amount        = (int) $data->amount;
			$currency      = (string) $data->currency;
			$wallieUrl     = html_entity_decode((string) $data->URL);
			$status        = (string) $data->message;

			if (!$this->setTransactionId($transactionId)
					|| !$this->setAmount($amount)
					|| !$this->setCurrency($currency)
					|| !$this->setWallieUrl($wallieUrl)
					|| !$this->setStatus($status)) {
				return false;
			}
		}
		catch (Exception $e)
		{
			return false;
		}

		return true;
	}

	/**
	* Check if the payment was succesfull
	*
	* @param integer $transactionId
	* @param integer $amount
	* @return boolean True on succes, false otherwise
	*/
	public function checkPayment($transactionId, $amount) 
	{
		// set transaction id & amount
		if (!$this->setTransactionId($transactionId)
		    || !$this->setAmount($amount)) 
		{
			$this->_errorMessage = "The given transactionID and/or amount is incorrect.";
			return false;
		}

		// check a payment with mollie
		$result = $this->sendToHost('www.mollie.nl', '/xml/wallie/',
									'a=check' .
									'&partnerid=' .      urlencode($this->getPartnerId()).
									'&transaction_id=' . urlencode($this->getTransactionId()).
									'&amount='.          urlencode($this->getAmount()));
		if (empty($result)) 
		{
			return false;
		}

		list($headers, $xml) = preg_split("/(\r?\n){2}/", $result, 2);

		try 
		{
			$data = $this->_XMLtoObject($xml);

			if ($data == false ) 
			{
				return false;
			}

			$data     = $data->order;
			$payed    = ($data->payed == 'true');
			$amount   = (int) $data->amount;
			$status   = (string) $data->message;

			if (!$this->setPayed($payed)
				|| !$this->setAmount($amount)
				|| !$this->setStatus($status)) 
			{
				return false;
			}
		} 
		catch (Exception $e) 
		{
			return false;
		}
		
		return true;
	}

	protected function _XMLtoObject ($xml) 
	{
		try 
		{
			$xml_object = new SimpleXMLElement($xml);
			if ($xml_object == false) {
				$this->error_message = "Kon XML resultaat niet verwerken";
				return false;
			}
		}
		catch (Exception $e) 
		{
			return false;
		}

		return $xml_object;
	}
	
	protected function _XMLisError($xml)
	{
		if (isset($xml->item))
		{
			$attributes = $xml->item->attributes();
			if ($attributes['type'] == 'error')
			{
				$this->_errorMessage = (string) $xml->item->message;
				$this->_errorCode    = (string) $xml->item->errorcode;
			
				return true;
			}
		}
		
		return false;
	}

	/**
	* Send data to given host
	*
	* @param string $host Full webhost Url (like 'www.mollie.nl')
	* @param string $path Path of script
	* @param string $data Data to send
	* @return string
	*/
	protected function sendToHost($host, $path, $data) 
	{
		// posts data to server
		$fp = @fsockopen($host, 80);
		$buf = '';
		if ($fp) 
		{
			@fputs($fp, "POST $path HTTP/1.0\n");
			@fputs($fp, "Host: $host\n");
			@fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
			@fputs($fp, "Content-length: " . strlen($data) . "\n");
			@fputs($fp, "Connection: close\n\n");
			@fputs($fp, $data);
			
			while (!feof($fp)) 
			{
				$buf .= fgets($fp, 128);
			}
		
			fclose($fp);		
		}
		
		return $buf;
	}

 	/**
	* Set the Partner Id
	*
	* @param integer $partnerId
	* @return boolean
	*/
	protected function setPartnerId($partnerId) 
	{
		if (!is_numeric($partnerId)) 
		{
			return false;
		}
		
		$this->partnerId = $partnerId;
		return true;
	}

	/**
	* Get the Partner Id
	*
	* @return integer
	*/
	
	protected function getPartnerId() 
	{
		return $this->partnerId;
	}

	/**
	 * Set transaction amount (price) in cents
	 *
	 * @param integer $amount Minimum amount is the cost of a transaction!
	 * @return boolean
	 */
	protected function setAmount($amount) 
	{	
		if (!is_numeric($amount)) 
		{
			return false;
		}
		
		$this->amount = $amount;
		return true;
	}

	/**
	* Get the Amount (price) in cents
	*
	* @return integer
	*/
	public function getAmount() 
	{
		return $this->amount;
	}

	/**
	 * Set Currency
	 *
	 * @param string $currency
	 * @return boolean
	 */
	protected function setCurrency($currency) 
	{
		if (empty($currency)) 
		{
			return false;
		}
		
		$this->currency = $currency;
		return true;
	}

	/**
	 * Get the Currency
	 *
	 * @return string
	 */
	protected function getCurrency() 
	{
		return $this->currency;
	}

	/**
     * Set the Language
     *
     * @param integer $language
     * @return boolean
     */
	protected function setLanguage($language) 
	{
		if (empty($language)) 
		{
			return false;
		}
		
		$this->language = $language;
		return true;
	}

	/**
	 * Get the Language
	 *
	 * @return integer
	 */
	protected function getLanguage() 
	{
		return $this->language;
	}

	/**
	 * Set the Url where Mollie reports to if the status of one of our
	 * payments changes
	 *
	 * Mollie adds the 'transaction_id' to this url
	 *
	 * @param string $reportUrl
	 * @return boolean
	 */
	protected function setReportUrl($reportUrl) 
	{
	
		if (!preg_match('|(\w+)://([^/:]+)(:\d+)?/(.*)|', $reportUrl)) 
		{
			return false;
		}

		$this->reportUrl = $reportUrl;
		return true;
	}

	/**
	 * Get the Report Url
	 *
	 * @return string
	 */
	protected function getReportUrl() 
	{
		return $this->reportUrl;
	}

	/**
	 * Set the Url where Mollie returns to when the payment is done
	 *
	 * Mollie add the 'transaction_id' to this url
	 *
	 * @param string $returnUrl
	 * @return boolean
	 */
	protected function setReturnUrl($returnUrl) 
	{
		if (!preg_match('|(\w+)://([^/:]+)(:\d+)?/(.*)|', $returnUrl)) 
		{
			return false;
		}
		
		$this->returnUrl = $returnUrl;
		return true;
	}

	/**
	 * Get the Return Url
	 *
	 * @return string
	 */
	protected function getReturnUrl() 
	{
		return $this->returnUrl;
	}

	/**
	 * Set Payed status
	 *
	 * @param boolean $payed
	 * @return boolean
	 */
	protected function setPayed($payed)
	{
		if ($payed === false) 
		{
			$this->payed = false;
			return false;
		}
		
		$this->payed = true;
		return true;
	}

	/**
     * Get the payed status
     *
     * @return boolean
     */
	protected function getPayed() 
	{
		return $this->payed;
	}

	/**
	 * Set Status message
	 *
	 * @param string $status
	 * @return boolean
	 */
	protected function setStatus($status)
	{
		if (empty($status)) 
		{
			return false;
		}
		
		$this->statusMessage = $status;
		return true;
	}

	/**
	 * Get the Status message
	 *
	 * @return string
	 */
	public function getStatus() 
	{
		return $this->statusMessage;
	}

	/**
	 * Set the Transaction Id
	 *
	 * @param integer $transactionId
	 * @return boolean
	 */
	protected function setTransactionId($transactionId) 
	{
		if (empty($transactionId)) 
		{
			return false;
		}
		
		$this->transactionId = $transactionId;
		return true;
	}

	/**
     * Get the Transaction Id
     *
     * @return integer
     */
	public function getTransactionId() 
	{
		return $this->transactionId;
	}

	/**
	 * Get the URL of the Walliepaymentscreen
	 *
	 * @return null|string Bank URL when exists, else null
	 */
	public function getWallieUrl() 
	{
		if (is_null($this->wallieUrl)) 
		{
			return null;
		}
		
		return $this->wallieUrl;
	}

	/**
	* Set the URL of the Walliepaymentscreen
	*
	* @param string $bankUrl
	* @return boolean
	*/
	protected function setWallieUrl($wallieUrl) 
	{	
		if (!preg_match('|(\w+)://([^/:]+)(:\d+)?/(.*)|', $wallieUrl)) 
		{
			return false;
		}
		
		$this->wallieUrl = $wallieUrl;
		return true;
	}

	public function getErrorMessage()
	{
		return $this->_errorMessage;
	}
	
	public function getErrorCode()
	{
		return $this->_errorCode;
	}  
}


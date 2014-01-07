<?php
/**
 * @version		13.8.225 sender.php
 * @package		J2XML
 * @subpackage	lib_j2xml
 * @since		1.5.3beta3.38
 *
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2010-2012 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access.');

// Includes the required class file for the XML-RPC Client
jimport('eshiol.core.xmlrpc');

jimport('eshiol.j2xml.messages');

class J2XMLSender
{
	private static $codes = array(
		'message',
		'notice',
		'message',
		'notice',
		'message',
		'notice',
		'message',
		'notice',
		'message',
		'notice',
		'message',
		'notice',
		'message',
		'notice',
		'notice',
		'error',
		'error'
	);	
	
	/*
	 * Send data
	 * @param $xml		data
	 * @param $debug
	 * @param $export_gzip
	 * @param $sid		remote server id
	 * @since		1.5.3beta3.38
	 */
	static function send($xml, $debug, $export_gzip, $sid)
	{
		$app = JFactory::getApplication();
/*
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('element'));
		$query->from($db->quoteName('#__extensions'));
		$query->where($db->quoteName('type') . ' = ' . $db->quote('library'));
		$query->where($db->quoteName('element') . ' = ' . $db->quote('xmlrpc'));
		$query->where($db->quoteName('enabled') . ' = 1');
		$db->setQuery($query);
		$xmlrpclib = ($db->loadResult() != null);

		if (!$xmlrpclib)
		{
			// Merge the default translation with the current translation
			$jlang = JFactory::getLanguage();
			// Back-end translation
			$jlang->load('lib_j2xml', JPATH_SITE, 'en-GB', true);
			$jlang->load('lib_j2xml', JPATH_SITE, $jlang->getDefault(), true);
			$jlang->load('lib_j2xml', JPATH_SITE, null, true);
			
			$app->enqueueMessage(JText::_('LIB_J2XML_XMLRPC_ERROR'), 'error');
			return;
		}
*/
/*
		if ($debug > 0)
		{
			$data = ob_get_contents();
			if ($data)
			{	
				$app->enqueueMessage(JText::_('LIB_J2XML_MSG_ERROR_EXPORT'), 'error');
					$app->enqueueMessage($data, 'error');
				return false;
			}
		}		
*/		
		ob_clean();
		$version = explode(".", J2XMLVersion::$DOCVERSION);
		$xmlVersionNumber = $version[0].$version[1].substr('0'.$version[2], strlen($version[2])-1);
		
		$data = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n";
		$data .= J2XMLVersion::$DOCTYPE."\n";
		$data .= "<j2xml version=\"".J2XMLVersion::$DOCVERSION."\">\n";
		$data .= $xml; 
		$data .= "</j2xml>";
		
		// modify the MIME type
		$document = JFactory::getDocument();
		if ($export_gzip)
		{
//			$document->setMimeEncoding('application/gzip-compressed', true);
//			JResponse::setHeader('Content-disposition', 'attachment; filename="j2xml'.$xmlVersionNumber.date('YmdHis').'.gz"', true);
			$data = gzencode($data, 9);
		}
		else 
		{
//			$document->setMimeEncoding('application/xml', true);
//			JResponse::setHeader('Content-disposition', 'attachment; filename="j2xml'.$xmlVersionNumber.date('YmdHis').'.xml"', true);
		}

		$db = JFactory::getDBO();
		$query = 'SELECT `remote_url`, `username`, `password` '
			. 'FROM `#__j2xml_websites` WHERE `id` = '.$sid;
		$db->setQuery($query);
		$server = $db->loadAssoc();		

		$str = $server['remote_url'];

		if (strpos($str, "://") === false)
			$server['remote_url'] = "http://".$server['remote_url'];
				
		if ($str[strlen($str)-1] != '/')
			$server['remote_url'] .= '/';
		$server['remote_url'] .= 'index.php?option=com_j2xml&task=cpanel.import&format=xmlrpc';

		$res = 
			self::_xmlrpc_j2xml_send(
				$server['remote_url'], 
				$data, 
				$server['username'], 
				$server['password'], 
				$debug
			);
//		return $res;
		if ($res->faultcode())
			$app->enqueueMessage(JText::_($res->faultString()), 'error');
		else
		{
			$msgs = $res->value();
			$len=$msgs->arraysize();
			for ($i = 0; $i < $len; $i++)
			{
				$msg=$msgs->arraymem($i);
				$code = $msg->structmem('code')->scalarval();
				$string = $msg->structmem('string')->scalarval();
				$app->enqueueMessage(JText::sprintf(J2XMLMessages::$messages[$code], $string), self::$codes[$code]);
			}
		}
	}

	/**
	* Send xml data to
	* @param string $remote_url
	* @param string $xml
	* @param string $username
	* @param string $password
	* @param int $debug when 1 (or 2) will enable debugging of the underlying xmlrpc call (defaults to 0)
	* @return xmlrpcresp obj instance
	*/
	private static function _xmlrpc_j2xml_send($remote_url, $xml, $username, $password, $debug=0) 
	{
		$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';
		$client = new xmlrpc_client($remote_url);
		$client->return_type = 'xmlrpcvals';
		$client->request_charset_encoding = 'UTF-8';
		$client->setDebug($debug);
		$msg = new xmlrpcmsg('j2xml.import');
		$p1 = new xmlrpcval(base64_encode($xml), 'base64');
		$msg->addparam($p1);
		$p2 = new xmlrpcval($username, 'string');
		$msg->addparam($p2);
		$p3 = new xmlrpcval($password, 'string');
		$msg->addparam($p3);
		return $client->send($msg, 0, '');
	}
}
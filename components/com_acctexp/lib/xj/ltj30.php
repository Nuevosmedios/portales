<?php
class xJ
{
	function getDBArray( $db )
	{
		return $db->loadResultArray();
	}

	function escape( $db, $value )
	{
		return $db->getEscaped( $value );
	}

	function token()
	{
		return JUtility::getToken();
	}

	function getHash()
	{
		return JUtility::getHash(JUserHelper::genRandomPassword());
	}

	function sendMail( $sender, $sender_name, $recipient, $subject, $message, $html=null, $cc=null, $bcc=null, $attach=null )
	{
		JUTility::sendMail( $sender, $sender_name, $recipient, $subject, $message, $html, $cc, $bcc, $attach );
	}
}

?>

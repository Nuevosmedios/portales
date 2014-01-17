<?php 
//use joomla db class
require("../../../../../configuration.php");
$jconfig = new JConfig();

//db establish
$db_error = "I am sorry! We are maintenaning the website, please try again later.";
$db_config = mysql_connect( $jconfig->host, $jconfig->user, $jconfig->password ) or die( $db_error );
mysql_select_db( $jconfig->db, $db_config ) or die( $db_error );
$prefix = $jconfig->dbprefix;

function GetParameters(){
	$parameters = array();
	foreach ($_REQUEST as $key => $value){
		if($key != "action"){
			$parameters[$key]=$value;
		}
	}
	return $parameters;
}
function GetKMEWSDL(){
	global $prefix;
	$wsdl ="";
	$query = "SELECT * FROM ".$prefix."kmecertificates_configuration WHERE nombre='KMEServiceUrl'";
	$queryExecute = mysql_query($query);
		if($res = mysql_fetch_row($queryExecute)) {
			$wsdl = $res[1];	   
		}
	return $wsdl;
}
function CallService($attempts){
	try {
			$wsdl = GetKMEWSDL();
			$client = new soapclient($wsdl);
			$parameters = GetParameters();
			$response = $client->__soapCall($_GET["action"],array($parameters));
			echo json_encode($response);
	} 
	catch (SoapFault $fault) {
	if($attempts<=0){		
		$error = array("faultcode"=>$fault->faultcode, "faultstring"=>$fault->faultstring);
		echo json_encode($error);
		}
		else{
			CallService($attempts-1);
		}
	}
}

CallService(5);
mysql_close($db_config);
		?>
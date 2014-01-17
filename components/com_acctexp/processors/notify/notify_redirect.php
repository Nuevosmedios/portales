<?php

// harr harr harr
$bsLoader = new bootstrapLoader();

$original = file_get_contents("php://input");

if ( empty( $original ) && isset( $GLOBALS['HTTP_RAW_POST_DATA'] ) ) {
	$original = $GLOBALS['HTTP_RAW_POST_DATA'];
}

$post = array();

// See whether we have some sneaky encoded request
if ( !empty( $_GET['aec_request'] ) ) {
	if ( strpos( $_GET['aec_request'], '_' ) !== false ) {
		$data = explode( '_', urlencode( $bsLoader->clearVariable( $_GET['aec_request'] ) ) );

		$pmap = array( 'djd' => 'desjardinsnotification' );

		if ( array_key_exists( $data[0], $pmap ) ) {
			$_GET['task'] = $pmap[$data[0]];
		} else {
			$_GET['task'] = $data[0];
		}

		$post['invoice_number'] = $data[1];
		$post['status'] = $data[2];
	} else {
		$decode = unserialize( base64_decode( $_GET['aec_request'] ) );
	}

	if ( !empty( $decode['get'] ) ) {
		foreach ( $decode['get'] as $k => $v ) {
			$_GET[$k] = $v;
		}
	}

	if ( !empty( $decode['post'] ) ) {
		foreach ( $decode['post'] as $k => $v ) {
			$post[$k] = $v;
		}
	}

	unset( $_GET['aec_request'] );
}

// Force redirection to AEC
$_GET['option'] = 'com_acctexp';

$get = array();

// Block out anything that aims at non-notification actions
if ( isset( $_GET['task'] ) ) {
	if ( strpos( $_GET['task'], 'notification' ) === false ) {
		$path = str_replace( '/components/com_acctexp/processors/notify/notify_redirect.php', '', $_SERVER['PHP_SELF'] ) . '/index.php' . '?' . $bsLoader->makeGet( $_GET );

		$url = 'http://' . $_SERVER['HTTP_HOST'] . $path;

		header( 'Location: ' . $url );
		exit;
	}
} else {
	foreach ( $_GET as $k => $v ) {
		if ( strpos( $v, 'notification' ) !== false ) {
			$get['task'] = $v; 
		}
	}
}

foreach ( $_GET as $k => $v ) {
	if ( strpos( $k, 'Zombaio' ) !== false ) {
		$post = $_GET;

		unset( $_GET );

		$get['option'] = 'com_acctexp';
		$get['task'] = 'zombaionotification';

		break;
	}
}

if ( !empty( $original ) ) {
	$post['original'] = base64_encode( stripslashes( $original ) );
}

// Instead of doing fancy-pants figuring out of the subdirectory, just deduct that from the call
$path = str_replace( '/components/com_acctexp/processors/notify/notify_redirect.php', '', $_SERVER['PHP_SELF'] ) . '/index.php' . '?' . $bsLoader->makeGet( $get );

$url = 'http://' . $_SERVER['HTTP_HOST'] . $path;

$response = $bsLoader->transmitRequest( $url, $path, $post );

if ( $response === false ) {
	//  Last fallback - use a redirect
	header( 'Location: ' . $url );
} else {
	echo $response;
}

exit;

class bootstrapLoader
{
	function clearKey( $key )
	{
		return preg_replace( "/[^a-z \d]/i", "", $key );
	}

	function makeGet( $array )
	{
		$reduced = array();

		foreach ( $array as $k => $v ) {
			// Make extra sure that the variable contains no shenanigans
			$reduced[] = $this->clearKey($k) . '=' . urlencode( $this->clearVariable($v) );
		}

		return implode( '&', $reduced );
	}

	function clearVariable( $var )
	{
		$return = $var;

		for ( $n=0; $n<strlen($var); $n++ ) {
			if ( preg_match( '`[\<|\>|\"|\'|\%|\;|\(|\)]`Di', $return[$n] ) ) {
				 $return = substr( $return, 0, $n );
				 continue;
			}
		}

		return $return;
	}

	function transmitRequest( $url, $path, $content=null, $port=443, $curlextra=null, $header=null )
	{
		$response = $this->doTheCurl( $url, $content, $curlextra, $header );
		if ( $response === false ) {
			// If curl doesn't work try using fsockopen
			$response = $this->doTheHttp( $url, $path, $content, $port, $header );
		}

		return $response;
	}

	function doTheHttp( $url, $path, $content, $port=443, $extra_header=null )
	{
		if ( strpos( $url, '://' ) === false ) {
			if ( $port == 443 ) {
				$purl = 'https://' . $url;
			} else {
				$purl = 'http://' . $url;
			}
		} else {
			$purl = $url;
		}

		$url_info = parse_url( $purl );

		if ( empty( $url_info ) ) {
				return false;
		}

		switch ( $url_info['scheme'] ) {
				case 'https':
						$scheme = 'ssl://';
						$port = 443;
						break;
				case 'http':
				default:
						$scheme = '';
						$port = 80;
						break;
		}

		$url = $scheme . $url_info['host'];

		$connection = fsockopen( $url, $port, $errno, $errstr, 30 );

		if ( $connection === false ) {
			return false;
		} else {
		    $hosturl = $url_info['host'];

			$header_array["Host"] = $hosturl;

			$header_array["User-Agent"] = "PHP Script";
			$header_array["Content-Type"] = "multipart/form-data";

			if ( !empty( $content ) ) {
				$header_array["Content-Length"] = strlen( $content );
			}

			if ( !empty( $extra_header ) ) {
				foreach ( $extra_header as $h => $v ) {
					$header_array[$h] = $v;
				}
			}

			$header_array["Connection"] = "Close";

			if ( !empty( $content ) ) {
				$header = "POST " . $path . " HTTP/1.0\r\n";
			} else {
				$header = "GET " . $path . " HTTP/1.0\r\n";
			}

			foreach ( $header_array as $h => $v ) {
				$header .=	$h . ": " . $v . "\r\n";
			}

			$header .= "\r\n";

			if ( !empty( $content ) ) {
				$header .= $content;
			}

			fwrite( $connection, $header );

			$res = "";
			if ( function_exists( 'stream_set_timeout' ) ) {
				stream_set_timeout( $connection, 300 );

				$info = stream_get_meta_data( $connection );

				while ( !feof( $connection ) && ( !$info["timed_out"] ) ) {
					$res = fgets( $connection, 8192 );
				}

		        if ( $info["timed_out"] ) {
					fclose( $connection );

					return false;
		        }
			} else {
				while ( !feof( $connection ) ) {
					$res = fgets( $connection, 1024 );
				}
			}

			fclose( $connection );

			return $res;
		}
	}

	function doTheCurl( $url, $content, $curlextra=null, $header=null )
	{
		if ( !function_exists( 'curl_init' ) ) {
			return false;
		}

		if ( empty( $curlextra ) ) {
			$curlextra = array();
		}

		// Preparing cURL variables as array, to possibly overwrite them with custom settings by the processor
		$curl_calls = array();
		$curl_calls[CURLOPT_URL]			= $url;
		$curl_calls[CURLOPT_RETURNTRANSFER]	= true;
		$curl_calls[CURLOPT_HTTPHEADER]		= array( 'Content-Type: multipart/form-data' );
		$curl_calls[CURLOPT_HEADER]			= false;

		if ( !empty( $content ) ) {
			$curl_calls[CURLOPT_POST]			= true;
			$curl_calls[CURLOPT_POSTFIELDS]		= $content;
		}

		// Set or replace cURL params
		if ( !empty( $curlextra ) ) {
			foreach( $curlextra as $name => $value ) {
				if ( $value == '[[unset]]' ) {
					if ( isset( $curl_calls[$name] ) ) {
						unset( $curl_calls[$name] );
					}
				} else {
					$curl_calls[$name] = $value;
				}
			}
		}

		// Set cURL params
		$ch = curl_init();
		foreach ( $curl_calls as $name => $value ) {
			curl_setopt( $ch, $name, $value );
		}

		$response = curl_exec( $ch );

		curl_close( $ch );

		return $response;
	}
}

?>

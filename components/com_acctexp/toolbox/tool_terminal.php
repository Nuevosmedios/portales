<?php
/**
 * @version $Id: tool_terminal.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Toolbox - Terminal
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class tool_terminal
{
	function Info()
	{
		$info = array();
		$info['name'] = "Terminal";
		$info['desc'] = "Fire off exotic queries. Consulting the manual is pretty much mandatory. Sorry about that.";

		return $info;
	}

	function Settings()
	{
		$settings = array();

		if ( !isset( $_POST['query'] ) ) {
			$_POST['query'] = "";
		}

		$suggestions = array( 'supercommand: users|everybody|apply:plan:42', '!supercommand: users|has:plan:42|apply:mi:128', 'jsonserialencode JSON', 'serialdecodejson BASE64', 'serialdecode BASE64', 'unserialize PHP-SERIAL', '?', '??', '???', '????', 'what to do', 'need strategy', 'help', 'help me', 'huh?', 'AAAAH!', 'logthis: STUFF' );

		$settings['input']	= array( 'p', '<input type="text" name="query" class="search span8 typeahead" autocomplete="off" data-source="[&quot;' . implode('&quot;,&quot;', $suggestions) . '&quot;]" data-items="4" data-provide="typeahead" placeholder="Type, if you dare!" value="' . $_POST['query'] . '" />' );

		return $settings;
	}

	function Action()
	{
		if ( empty( $_POST['query'] ) ) {
			return null;
		}

		$db = &JFactory::getDBO();

		$query = trim( aecGetParam( 'query', 0 ) );

		if ( strpos( $query, 'supercommand:' ) !== false ) {
			$supercommand = new aecSuperCommand();

			if ( $supercommand->parseString( $query ) ) {
				if ( strpos( $query, '!' ) === 0 ) {
					$armed = true;
				} else {
					$armed = false;
				}

				$return = $supercommand->query( $armed );

				if ( $return > 1 ) {
					$multiple = true;
				} else {
					$multiple = false;
				}

				if ( ( $return != false ) && !$armed ) {
					$r = '<p>This supercommand would affect ' . $return . " user" . ($multiple ? "s":"") . ". Add a ! in front of supercommand to carry out the command.</p>";
				} elseif ( $return != false ) {
					$r = '<p>If you\'re so clever, you tell us what <strong>colour</strong> it should be!? (Everything went fine. Really! It affected ' . $return . " user" . ($multiple ? "s":"") . ")</p>";
				} else {
					$r = '<p>Something went wrong. No users found.</p>';
				}

				return $r;
			}
	
			return "I think you ought to know I'm feeling very depressed. (Something was wrong with your query.)";
		}

		if ( strpos( $query, 'jsonserialencode' ) === 0 ) {
			$s = trim( substr( $query, 16 ) );
			if ( !empty( $s ) ) {
				$return = base64_encode( serialize( jsoonHandler::decode( $s ) ) );
				return '<p>' . $return . '</p>';
			}
		}

		if ( strpos( $query, 'serialdecodejson' ) === 0 ) {
			$s = trim( substr( $query, 16 ) );
			if ( !empty( $s ) ) {
				$return = jsoonHandler::encode( unserialize( base64_decode( $s ) ) );
				return '<p>' . $return . '</p>';
			}
		}

		if ( strpos( $query, 'serialdecode' ) === 0 ) {
			$s = trim( substr( $query, 12 ) );
			if ( !empty( $s ) ) {
				$return = unserialize( base64_decode( $s ) );
				return '<p>' . obsafe_print_r( $return, true, true ) . '</p>';
			}
		}

		if ( strpos( $query, 'unserialize' ) === 0 ) {
			$s = trim( substr( $query, 11 ) );
			if ( !empty( $s ) ) {
				$return = unserialize( $s );
				return '<p>' . obsafe_print_r( $return, true, true ) . '</p>';
			}
		}

		$maybe = array( '?', '??', '???', '????', 'what to do', 'need strategy', 'help', 'help me', 'huh?', 'AAAAH!' );

		if ( in_array( $query, $maybe ) ) {
			include_once( JPATH_SITE . '/components/com_acctexp/lib/eucalib/eucalib.add.php' );

			$ed = ( rand( 1, 4 ) );
			$edf = ${'edition_0' . $ed};
			$maxed = count( ${'edition_0' . $ed} );

			return $edf['quote_' . str_pad( rand( 1, ( $maxed + 1 ) ), 2, '0' )];
		}

		if ( strpos( $query, 'logthis:' ) === 0 ) {
			$eventlog = new eventLog();
			$eventlog->issue( 'debug', 'debug', 'debug entry: '.str_replace( 'logthis:', '', $query ), 128 );

			return 'alright, logged.';
		}

	}

}
?>

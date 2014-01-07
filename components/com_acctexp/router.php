<?php
/**
 * @version $Id: router.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

/**
 * @param	array	A named array
 * @return	array
 */
function AcctexpBuildRoute( &$query )
{
	$segments = array();

	$direct = array( 'task' );
	$ignore = array( 'option', 'itemid' );

	foreach ( $query as $k => $v ) {
		if ( in_array( $k, $direct ) ) {
			$segments[] = $v;

			unset( $query[$k] );
		} else {
			if ( !in_array( strtolower($k), $ignore ) ) {
				$segments[] = $k;
				$segments[] = $v;

				unset( $query[$k] );
			}
		}
	}

	return $segments;
}

/**
 * @param	array	A named array
 * @param	array
 *
 * Format:
 *
 * index.php?/component/task/sub/Itemid
 */
function AcctexpParseRoute( $segments )
{
	$vars = array();

	$bit = 0;
	$kk = null;

	foreach ( $segments as $k => $segment ) {
		if ( $k < 1 ) {
			$vars['task'] = $segment;
		} else {
			if ( !$bit ) {
				$kk = $segment;
			} else {
				$vars[$kk] = $segment;
			}

			$bit = !$bit;

			if ( $segment == 'intro' ) {
				$vars['intro'] = 1;
			}
		}
	}

	return $vars;
}
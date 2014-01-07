<?php
/**
 * @version $Id: eucalib.admin.proxy.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Admin Proxy to relay to subtask files
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 *
 *                         _ _ _
 *                        | (_) |
 *     ___ _   _  ___ __ _| |_| |__
 *    / _ \ | | |/ __/ _` | | | '_ \
 *   |  __/ |_| | (_| (_| | | | |_) |
 *    \___|\__,_|\___\__,_|_|_|_.__/  v1.0
 *
 * The Extremely Useful Component LIBrary will rock your socks. Seriously. Reuse it!
 */

( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Restricted access' );

$task		= trim( aecGetParam( $_REQUEST, 'task', null ) );
$returntask = trim( aecGetParam( $_REQUEST, 'returntask', null ) );

resolveProxy( $task, $returntask, true );

?>

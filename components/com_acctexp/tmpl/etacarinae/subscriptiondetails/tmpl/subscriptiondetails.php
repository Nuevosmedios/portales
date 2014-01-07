<?php
/**
 * @version $Id: subscriptiondetails.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' ) ?>
<div id="aec">
	<div id="aec-subscriptiondetails">
		<div class="componentheading"><?php echo JText::_('MYSUBSCRIPTION_TITLE');?></div>
		<?php
		if ( $properties['hascart'] ) {
			@include( $tmpl->tmpl( 'plans.backtocart' ) );
		}

		if ( $tmpl->cfg['subscriptiondetails_menu'] ) { @include( $tmpl->tmpl( 'nav' ) ); }

		switch ( $sub ) {
			case 'overview':
				@include( $tmpl->tmpl( 'overview' ) );
				break;
			case 'invoices':
				@include( $tmpl->tmpl( 'invoices' ) );
				break;
			case 'details':
				if ( $mi_info ) {
					echo $mi_info;
				} else {
					@include( $tmpl->tmpl( 'overview' ) );
				}
				break;
			default:
				if ( $custom ) {
					echo $custom;
				} else {
					@include( $tmpl->tmpl( 'overview' ) );
				}
				break;
		} ?>
	</div>
</div>

<?php
/**
 * @version $Id: checkout/html.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main Frontend
 * @copyright 2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

$makegift = false;

if ( !empty( $tmpl->cfg['checkout_as_gift'] ) ) {
	if ( !empty( $tmpl->cfg['checkout_as_gift_access'] ) ) {
		if ( $InvoiceFactory->metaUser->hasGroup( $tmpl->cfg['checkout_as_gift_access'] ) ) {
			$makegift = true;
		}
	} else {
		$makegift = true;
	}
}

$InvoiceFactory->invoice->deformatInvoiceNumber();

$itemlist = array();
foreach ( $InvoiceFactory->items->itemlist as $item ) {
	if ( !empty( $item['terms'] ) ) {
		$terms = $item['terms']->getTerms();
	} else {
		continue;
	}

	$i = array();

	if ( isset( $item['quantity'] ) ) {
		$i['quantity'] = $item['quantity'];
	} else {
		$i['quantity'] = 1;
	}

	if ( isset( $item['params'] ) ) {
		$i['params'] = $item['params'];
	} else {
		$i['params'] = array();
	}

	if ( isset( $item['name'] ) ) {
		$i['name'] = $item['name'];
	} else {
		$i['name'] = "";
	}

	$display = true;
	if ( !empty( $InvoiceFactory->checkout['checkout_display_descriptions'] ) ) {
		$display = $InvoiceFactory->checkout['checkout_display_descriptions'];
	}

	if ( isset( $item['desc'] ) && $display ) {
		$i['desc'] = $item['desc'];
	} else {
		$i['desc'] = "";
	}

	$i['terms'] = array();
	foreach ( $terms as $tid => $term ) {
		if ( !is_object( $term ) ) {
			continue;
		}

		$applicable = ( $tid >= $item['terms']->pointer ) ? '' : '&nbsp;('.JText::_('AEC_CHECKOUT_NOTAPPLICABLE').')';

		// Iterate through costs
		$cost = array();
		foreach ( $term->renderCost() as $citem ) {
			$t = JText::_( strtoupper( 'aec_checkout_' . $citem->type ) );

			if ( isset( $item['quantity'] ) ) {
				$amount = AECToolbox::correctAmount( $citem->cost['amount'] * $item['quantity'] );
			} else {
				$amount = AECToolbox::correctAmount( $citem->cost['amount'] );
			}

			$c = AECToolbox::formatAmount( $amount, $InvoiceFactory->payment->currency );

			switch ( $citem->type ) {
				case 'discount':
					if ( !empty( $citem->cost['details'] ) ) {
						$t .= '&nbsp;(' . $citem->cost['details'] . ')';
					}

					if ( !empty( $citem->cost['coupon'] ) ) {
						$t .= '&nbsp;['
								. $tmpl->lnk( array(	'task' => 'InvoiceRemoveCoupon',
														'invoice' => $InvoiceFactory->invoice->invoice_number,
														'coupon_code' => $citem->cost['coupon']
														), JText::_('CHECKOUT_INVOICE_COUPON_REMOVE') )
								. ']';
					}
					break;
				case 'tax':
					if ( !empty( $citem->cost['details'] ) ) {
						$t .= '&nbsp;( ' . $citem->cost['details'] . ' )';
					}
					break;
				case 'cost':
					if ( !empty( $citem->cost['details'] ) ) {
						$t = $citem->cost['details'];
					}
				break;
				case 'total': break;
				default: break;
			}

			$cost[] = array( 'type' => $citem->type, 'details' => $t, 'cost' => $c );
		}

		$i['terms'][] = array(	'title' => JText::_( strtoupper( 'aec_termtype_' . $term->type ) ).$applicable,
								'type' => $term->type,
								'current' => ( $tid == $item['terms']->pointer ),
								'duration' => $term->renderDuration(),
								'cost' => $cost );
	}

	$itemlist[] = $i;
}

if ( count( $InvoiceFactory->items->itemlist ) > 1 ) {
	$i				= array( 'name' => JText::_('CART_ROW_TOTAL'), 'quantity' => 1, 'terms' => array() );
	$c				= AECToolbox::formatAmount( $InvoiceFactory->items->total->renderCost(), $InvoiceFactory->payment->currency );
	$cost			= array( );
	$cost[]			= array( 'type' => 'total', 'details' => JText::_('AEC_CHECKOUT_TOTAL'), 'cost' => $c );

	if ( !empty( $InvoiceFactory->items->discount ) ) {
		// Iterate through full discounts
		foreach ( $InvoiceFactory->items->discount as $citems ) {
			foreach ( $citems as $ccitem ) {
				$citem = $ccitem->renderCost();

				foreach ( $citem as $cost ) {
					if ( $cost->type == 'discount' ) {
						$t = JText::_( strtoupper( 'aec_checkout_' . $cost->type ) );

						$amount = AECToolbox::correctAmount( $cost->cost['amount'] );

						$c = AECToolbox::formatAmount( $amount, $InvoiceFactory->payment->currency );

						if ( !empty( $cost->cost['details'] ) ) {
							$t .= '&nbsp;(' . $cost->cost['details'] . ')';
						}

						if ( !empty( $cost->cost['coupon'] ) ) {
							$t .= '&nbsp;['
									. $tmpl->lnk( array(	'task' => 'InvoiceRemoveCoupon',
														'invoice' => $InvoiceFactory->invoice->invoice_number,
														'coupon_code' => $citem->cost['coupon']
														), JText::_('CHECKOUT_INVOICE_COUPON_REMOVE') )
									. ']';
						}
					}
					
					$cost[] = array( 'type' => $cost->type, 'details' => $t, 'cost' => $c );
				}
			}
		}
	}

	if ( !empty( $InvoiceFactory->items->tax ) ) {
		foreach ( $InvoiceFactory->items->tax as $titems ) {
			foreach ( $titems['terms']->terms as $titem ) {
				$citem = $titem->renderCost();

				foreach ( $citem as $cost ) {
					if ( $cost->type == 'tax' ) {
						$t = JText::_( strtoupper( 'aec_checkout_' . $cost->type ) );

						$amount = AECToolbox::correctAmount( $cost->cost['amount'] );

						$c = AECToolbox::formatAmount( $amount, $InvoiceFactory->payment->currency );

						if ( !empty( $cost->cost['details'] ) ) {
							$t .= '&nbsp;( ' . $cost->cost['details'] . ' )';
						}
					}

					$cost[] = array( 'type' => $cost->type, 'details' => $t, 'cost' => $c );
				}
			}
		}

	}

	$i['terms'][]	= array( 'type' => 'total', 'current' => 1, 'cost' => $cost );

	$itemlist[] = $i;

	if ( !empty( $InvoiceFactory->items->grand_total ) ) {
		$i				= array( 'name' => JText::_('AEC_CHECKOUT_GRAND_TOTAL'), 'quantity' => 1, 'terms' => array() );
		$c = AECToolbox::formatAmount( $InvoiceFactory->items->grand_total->renderCost(), $InvoiceFactory->payment->currency );
		$cost			= array( array( 'type' => 'total', 'details' => JText::_('AEC_CHECKOUT_TOTAL'), 'cost' => $c ) );
		$i['terms'][]	= array( 'type' => 'total', 'current' => 1, 'cost' => $cost );

		$itemlist[] = $i;
	}
}

if ( strpos( $var, 'class="tab-content"' ) ) {
	/*$tmpl->enqueueValidation( array( 'rules' => array(
													'cardNumber' => array( 'creditcard' => true, 'required' => true ),
													'cardVV2' => array( 'required' => true )
												) ) );*/

	$tmpl->enqueueJQueryExtension( 'bootstrap/bootstrap.min' );

	$js = "jQuery('.nav-tabs a:first').tab('show');";

	$tmpl->enqueueJQueryCode( $js );
}

$tmpl->setTitle( $InvoiceFactory->checkout['checkout_title'] );

$tmpl->defaultHeader();

@include( $tmpl->tmpl( 'checkout' ) );

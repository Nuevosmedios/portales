<?php
/**
 * @version $Id: acctexp.coupon.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class couponsHandler extends eucaObject
{
	/** @var bool - Was the cart changed? Needed to signal reload action */
	var $affectedCart		= false;
	/** @var array - Coupons that should not be applied on any later action */
	var $noapplylist		= array();
	/** @var array - List of coupons */
	var $coupons_list		= array();
	/** @var array - Coupons that will be applied to the whole cart */
	var $fullcartlist		= array();
	/** @var array - Global List of coupon mix rules */
	var $mixlist	 		= array();
	/** @var array - Global List of applied coupons */
	var $global_applied 	= array();
	/** @var array - Local List of excluding coupons */
	var $item_applied 		= array();
	/** @var array - Coupons that need to be deleted */
	var $delete_list	 	= array();
	/** @var array - Exceptions that need to be addressed (by the user) */
	var $exceptions			= array();

	function couponsHandler( $metaUser, $InvoiceFactory, $coupons )
	{
		$this->metaUser			=& $metaUser;
		$this->InvoiceFactory	=& $InvoiceFactory;
		$this->coupons			=& $coupons;
	}

	function raiseException( $exception )
	{
		$this->exceptions[] = $exception;
	}

	function getExceptions()
	{
		return $this->exceptions;
	}

	function addCouponToRecord( $itemid, $coupon_code, $ccombo )
	{
		if ( !empty( $ccombo['bad_combinations_cart'] ) ) {
			if ( !empty( $this->mixlist['global']['restrictmix'] ) ) {
				$this->mixlist['global']['restrictmix'] = array_merge( $this->mixlist['global']['restrictmix'], $ccombo['bad_combinations_cart'] );
			} else {
				$this->mixlist['global']['restrictmix'] = $ccombo['bad_combinations_cart'];
			}
		}

		if ( !empty( $ccombo['good_combinations_cart'] ) ) {
			if ( !empty( $this->mixlist['global']['allowmix'] ) ) {
				$this->mixlist['global']['allowmix'] = array_merge( $this->mixlist['global']['allowmix'], $ccombo['good_combinations_cart'] );
			} else {
				$this->mixlist['global']['allowmix'] = $ccombo['good_combinations_cart'];
			}
		}

		$this->global_applied[] = $coupon_code;

		if ( $itemid !== false ) {
			if ( !empty( $ccombo['bad_combinations'] ) ) {
				if ( !empty( $this->mixlist['local'][$itemid]['restrictmix'] ) ) {
					$this->mixlist['local'][$itemid]['restrictmix'] = array_merge( $this->mixlist['local'][$itemid]['restrictmix'], $ccombo['bad_combinations'] );
				} else {
					$this->mixlist['local'][$itemid]['restrictmix'] = $ccombo['bad_combinations'];
				}
			}

			if ( !empty( $ccombo['good_combinations'] ) ) {
				if ( !empty( $this->mixlist['local'][$itemid]['allowmix'] ) ) {
					$this->mixlist['local'][$itemid]['allowmix'] = array_merge( $this->mixlist['local'][$itemid]['allowmix'], $ccombo['good_combinations'] );
				} else {
					$this->mixlist['local'][$itemid]['allowmix'] = $ccombo['good_combinations'];
				}
			}

			$this->item_applied[$itemid][] = $coupon_code;
		}
	}

	function mixCheck( $itemid, $coupon_code, $ccombo )
	{
		// First check whether any other coupon in the cart could block this
		if ( !empty( $this->mixlist['global']['allowmix'] ) ) {
			// Or maybe it just blocks everything?
			if ( !is_array( $this->mixlist['global']['allowmix'] ) ) {
				return false;
			} else {
				// Nope, check which ones it blocks
				if ( !in_array( $coupon_code, $this->mixlist['global']['allowmix'] ) ) {
					return false;
				}
			}
		}

		if ( $itemid !== false ) {
			// Now check whether any other coupon for this item could block this
			if ( !empty( $this->mixlist['local'][$itemid]['allowmix'] ) ) {
				// Or maybe it just blocks everything?
				if ( !is_array( $this->mixlist['local'][$itemid]['allowmix'] ) ) {
					return false;
				} else {
					// Nope, check which ones it blocks
					if ( !in_array( $coupon_code, $this->mixlist['local'][$itemid]['allowmix'] ) ) {
						return false;
					}
				}
			}
		}

		if ( !empty( $this->global_applied ) && !empty( $ccombo['good_combinations_cart'] ) ) {
			// Now check whether any other coupon in the cart could interfere with this ones restrictions
			// Maybe it just blocks everything?
			if ( !is_array( $ccombo['good_combinations_cart'] ) ) {
				return false;
			} else {
				// Nope, check which ones it blocks
				if ( !count( array_intersect( $this->global_applied, $ccombo['good_combinations_cart'] ) ) ) {
					return false;
				}
			}
		}

		if ( $itemid !== false ) {
			if ( !empty( $this->item_applied[$itemid] ) && !empty( $ccombo['good_combinations'] ) ) {
				// Now check whether any other coupon for this item could interfere with this ones restrictions
				// Maybe it just blocks everything?
				if ( !is_array( $ccombo['good_combinations'] ) ) {
					return false;
				} else {
					// Nope, check which ones it blocks
					if ( !count( array_intersect( $this->item_applied[$itemid], $ccombo['good_combinations'] ) ) ) {
						return false;
					}
				}
			}
		}

		// Now check for restrictions the other way around
		if ( !empty( $this->mixlist['global']['restrictmix'] ) && is_array( $this->mixlist['global']['restrictmix'] ) ) {
			if ( in_array( $coupon_code, $this->mixlist['global']['restrictmix'] ) ) {
				return false;
			}
		}

		if ( $itemid !== false ) {
			if ( !empty( $this->mixlist['local'][$itemid]['restrictmix'] ) && is_array( $this->mixlist['local'][$itemid]['restrictmix'] ) ) {
				if ( in_array( $coupon_code, $this->mixlist['local'][$itemid]['restrictmix'] ) ) {
					return false;
				}
			}
		}

		if ( !empty( $this->global_applied ) && !empty( $ccombo['bad_combinations_cart'] ) && is_array( $ccombo['bad_combinations_cart'] ) ) {
			if ( count( array_intersect( $this->global_applied, $ccombo['bad_combinations_cart'] ) ) ) {
				return false;
			}
		}

		if ( $itemid !== false ) {
			if ( !empty( $this->item_applied[$itemid] ) && !empty( $ccombo['bad_combinations'] ) && is_array( $ccombo['bad_combinations'] ) ) {
				if ( count( array_intersect( $this->item_applied[$itemid], $ccombo['bad_combinations'] ) ) ) {
					return false;
				}
			}
		}

		return true;
	}

	function loadCoupon( $coupon_code, $strict=true )
	{
		if ( in_array( $coupon_code, $this->delete_list ) && $strict ) {
			return false;
		}

		$cph = new couponHandler();
		$cph->load( $coupon_code );

		if ( empty( $cph->coupon->id ) ) {
			$this->setError( JText::_( 'COUPON_ERROR_INVALID' ) );

			$this->delete_list[] = $coupon_code;
			return false;
		}

		if ( $cph->coupon->coupon_code !== $coupon_code ) {
			$this->setError( JText::_( 'COUPON_ERROR_INVALID' ) );

			$this->delete_list[] = $coupon_code;
			return false;
		}

		if ( !$cph->status ) {
			$this->setError( $cph->error );

			$this->delete_list[] = $coupon_code;
			return false;
		}

		if ( $cph->coupon->restrictions['cart_multiple_items'] && !empty( $cph->coupon->restrictions['cart_multiple_items_amount'] ) ) {
			if ( !array_key_exists( $coupon_code, $this->max_amount_list ) ) {
				$this->coupons_list[] = array( 'coupon_code' => $coupon_code );

				$this->max_amount_list[$coupon_code] = $this->coupon->restrictions['cart_multiple_items_amount'];
			}

			if ( $this->max_amount_list[$coupon_code] ) {
				$this->max_amount_list[$coupon_code]--;
			} else {
				return false;
			}
		} else {
			$this->coupons_list[] = array( 'coupon_code' => $coupon_code );
		}

		$this->cph = $cph;

		return true;
	}

	function applyToTotal( $items, $cart=false, $fullcart=false )
	{
		$itemcount = 0;
		foreach ( $items->itemlist as $item ) {
			if ( is_object( $item ) ) {
				$itemcount += $item->quantitiy;
			} else {
				$itemcount++;
			}
		}

		if ( empty( $this->fullcartlist ) ) {
			return $items;
		}

		foreach ( $this->fullcartlist as $coupon_code ) {
			if ( !$this->loadCoupon( $coupon_code ) ) {
				continue;
			}

			if ( $this->cph->discount['amount_use'] ) {
				$this->cph->discount['amount'] = $this->cph->discount['amount'] / $itemcount;
			}

			$cost = null;
			$costarray = array();
			foreach ( $items->itemlist as $cid => $citem ) {
				if ( $citem['obj'] == false ) {
					continue;
				}

				$citem['terms']->nextterm->computeTotal();

				if ( empty( $cost ) ) {
					$cost = clone( $citem['terms']->nextterm->getBaseCostObject() );

					$costarray[$cid] = $cost->cost['amount'];
				} else {
					$ccost = $citem['terms']->nextterm->getBaseCostObject();

					$cost->cost['amount'] = $cost->cost['amount'] + ( $ccost->cost['amount'] * $citem['quantity'] );

					$costarray[$cid] = $ccost->cost['amount'];
				}

				$items->itemlist[$cid]['terms'] = $this->cph->applyToTerms( $items->itemlist[$cid]['terms'], true );
			}
		}

		$discounttypes = array();
		$discount_col = array();
		foreach ( $items->itemlist as $item ) {
			foreach ( $item['terms']->nextterm->cost as $cost ) {
				if ( $cost->type != 'discount' ) {
					continue;
				}

				$cc = $cost->cost['coupon'] . ' - ' . $cost->cost['details'];

				if ( in_array( $cc, $discounttypes ) ) {
					$typeid = array_search( $cc, $discounttypes );
				} else {
					$discounttypes[] = $cc;

					$typeid = count( $discounttypes ) - 1;
				}

				if ( !isset( $discount_col[$typeid] ) ) {
					$discount_col[$typeid] = 0;
				}

				$discount_col[$typeid] += $cost->renderCost();
			}
		}

		if ( !empty( $discount_col ) ) {
			// Dummy terms
			$terms = new mammonTerms();
			$term = new mammonTerm();

			foreach ( $discount_col as $cid => $discount ) {
				$cce = explode( ' - ', $discounttypes[$cid], 2 );

				$term->addCost( $discount, array( 'amount' => $discount, 'coupon' => $cce[0], 'details' => $cce[1] ) );
			}

			$terms->addTerm( $term );

			if ( empty( $items->discount ) ) {
				$items->discount = array();
			}

			$items->discount[] = $terms->terms;
		}

		return $items;
	}

	function applyToCart( $items, $cart=false, $fullcart=false )
	{
		$this->prefilter( $items, $cart, $fullcart );

		foreach ( $items as $iid => $item ) {
			$items[$iid] = $this->applyAllToItems( $iid, $item, $cart );
		}

		return $items;
	}

	function prefilter( $items, $cart=false, $fullcart=false )
	{
		foreach ( $this->coupons as $ccid => $coupon_code ) {
			if ( !$this->loadCoupon( $coupon_code ) ) {
				continue;
			}

			if ( $this->cph->coupon->restrictions['usage_cart_full'] ) {
				if ( !in_array( $coupon_code, $this->fullcartlist ) ) {
					$this->fullcartlist[] = $coupon_code;

					if ( !empty( $cart ) ) {
						if ( !$cart->hasCoupon( $coupon_code ) ) {
							$cart->addCoupon( $coupon_code );
							$cart->storeload();

							$this->affectedCart = true;
						}
					}

					continue;
				}
			}

			if ( empty( $cart ) && empty( $fullcart ) ) {
				return;
			}

			$plans = $cart->getItemIdArray();

			if ( $this->cph->coupon->restrictions['usage_plans_enabled'] ) {
				$allowed = array_intersect( $plans, $this->cph->coupon->restrictions['usage_plans'] );

				if ( empty( $allowed ) ) {
					$allowed = false;
				}
			} else {
				$allowed = $plans;
			}

			foreach ( $cart->content as $iid => $c ) {
				if ( $cart->hasCoupon( $coupon_code, $iid ) ) {
					continue 2;
				}
			}

			if ( !is_array( $allowed ) ) {
				continue;
			}

			$fname = 'cartcoupon_'.$ccid.'_item';

			$pgsel = aecGetParam( $fname, null, true, array( 'word', 'int' ) );

			if ( ( count( $allowed ) == 1 ) ) {
				$min = array_shift( array_keys( $allowed ) );

				foreach ( $items as $iid => $item ) {
					if ( $item['obj']->id == $allowed[$min] ) {
						$pgsel = $iid;
					}
				}
			}

			if ( !is_null( $pgsel ) ) {
				$items[$pgsel] == $this->applyToItem( $pgsel, $items[$pgsel], $coupon_code );

				if ( !$cart->hasCoupon( $coupon_code, $pgsel ) ) {
					$cart->addCoupon( $coupon_code, $pgsel );
					$cart->storeload();

					$this->affectedCart = true;
				}
			} else {
				$found = false;
				foreach ( $cart->content as $cid => $content ) {
					if ( $cart->hasCoupon( $coupon_code, $cid ) ) {
						$items[$cid] == $this->applyToItem( $cid, $items[$cid], $coupon_code );
						$found = true;

						$this->noapplylist[] = $coupon_code;
					}
				}

				if ( !$found ) {
					$ex = array();
					$ex['head'] = "Select Item for Coupon \"" . $coupon_code . "\"";
					$ex['desc'] = "The coupon you have entered can be applied to one of the following items:<br />";

					$ex['rows'] = array();

					foreach ( $allowed as $cid => $objid ) {
						if ( empty( $fullcart[$cid]['free'] ) ) {
							$ex['rows'][] = array( 'radio', $fname, $cid, true, $fullcart[$cid]['name'] );
						}
					}

					if ( !empty( $ex['rows'] ) ) {
						$this->raiseException( $ex );
					}
				}
			}
		}
	}

	function applyToItemList( $items )
	{
		foreach ( $items as $iid => $item ) {
			$items[$iid] = $this->applyAllToItems( $iid, $item );
		}

		return $items;
	}

	function applyAllToItems( $id, $item, $cart=false )
	{
		$this->global_applied = array();

		$hasterm = !empty( $item['terms'] );

		if ( $hasterm ) {
			if ( !empty( $item['terms']->terms[0]->type ) ) {
				$termtype = $item['terms']->terms[0]->type;
			} else {
				$termtype = null;
			}
		} else {
			$termtype = null;
		}

		if ( empty( $item['obj'] ) && ( !$hasterm || ( $termtype == "total" ) ) ) {
			// This is the total item - apply total coupons - totally
			foreach ( $this->coupons as $coupon_code ) {
				if ( in_array( $coupon_code, $this->fullcartlist ) ) {
					$item = $this->applyToItem( $id, $item, $coupon_code );
				}
			}
		} else {
			if ( !empty( $this->coupons ) ) {
				foreach ( $this->coupons as $coupon_code ) {
					if ( in_array( $coupon_code, $this->noapplylist ) ) {
						continue;
					}

					if ( $this->loadCoupon( $coupon_code ) ) {
						if ( $cart != false ) {
							if ( $cart->hasCoupon( $coupon_code, $id ) ) {
								$item = $this->applyToItem( $id, $item, $coupon_code );
							}
						} else {
							$item = $this->applyToItem( $id, $item, $coupon_code );
						}
					}
				}
			}
		}

		$item['terms']->checkFree();

		return $item;
	}

	function applyToItem( $id, $item, $coupon_code )
	{
		if ( !$this->loadCoupon( $coupon_code, false ) ) {
			return $item;
		}

		if ( !empty( $this->item_applied[$id] ) ) {
			if ( in_array( $coupon_code, $this->item_applied[$id] ) ) {
				return $item;
			}
		}

		if ( isset( $item['terms'] ) ) {
			$terms = $item['terms'];
		} elseif ( isset( $item['obj'] ) ) {
			if ( !empty( $this->invoice ) ) {
				$terms = $item['obj']->getTerms( false, $this->metaUser->focusSubscription, $this->metaUser, $this->invoice );
			} else {
				$terms = $item['obj']->getTerms( false, $this->metaUser->focusSubscription, $this->metaUser );
			}
		} elseif ( isset( $item['cost'] ) ) {
			$terms = $item['cost'];
		} else {
			return $item;
		}

		$ccombo		= $this->cph->getCombinations();

		if ( !empty( $item['obj']->id ) ) {
			$this->InvoiceFactory->usage = $item['obj']->id;
			
			$usage = $item['obj']->id;
		} elseif ( !empty( $this->InvoiceFactory->usage ) ) {
			$usage = $this->InvoiceFactory->usage;
		} else {
			$usage = 0;
		}

		if ( !$this->mixCheck( $id, $coupon_code, $ccombo ) ) {
			$this->setError( JText::_('COUPON_ERROR_COMBINATION') );
		} else {
			if ( $this->cph->status ) {
				// Coupon approved, checking restrictions
				$r = $this->cph->checkRestrictions( $this->metaUser, $terms, $usage );

				if ( $this->cph->status ) {
					$item['terms'] = $this->cph->applyToTerms( $terms );

					$this->addCouponToRecord( $id, $coupon_code, $ccombo );

					return $item;
				} else {
					$this->setError( $this->cph->error );
				}
			}
		}

		$this->delete_list[] = $coupon_code;

		return $item;
	}

	function applyToAmount( $amount, $original_amount=null )
	{
		if ( empty( $this->coupons ) || !is_array( $this->coupons ) ) {
			return $amount;
		}

		foreach ( $this->coupons as $coupon_code ) {
			if ( !$this->loadCoupon( $coupon_code ) ) {
				continue;
			}

			$ccombo	= $this->cph->getCombinations();

			if ( !$this->mixCheck( false, $coupon_code, $ccombo ) ) {
				$this->setError( JText::_('COUPON_ERROR_COMBINATION') );
			} else {
				if ( $this->cph->status ) {
					// Coupon approved, checking restrictions
					$this->cph->checkRestrictions( $this->metaUser, $amount, $original_amount, $this->InvoiceFactory->usage );

					if ( $this->cph->status ) {
						$amount = $this->cph->applyCoupon( $amount );

						$this->addCouponToRecord( false, $coupon_code, $ccombo );
					}
				}
			}
		}

		$this->setError( $this->cph->error );

		return $amount;
	}

}

class couponHandler
{
	/** @var bool */
	var $status				= null;
	/** @var string */
	var $error				= null;
	/** @var object */
	var $coupon				= null;

	function couponHandler(){}

	function setError( $error )
	{
		$this->status = false;

		$this->error = $error;
	}

	function idFromCode( $coupon_code )
	{
		$db = &JFactory::getDBO();

		$return = array();

		// Get this coupons id from the static table
		$query = 'SELECT `id`'
				. ' FROM #__acctexp_coupons_static'
				. ' WHERE `coupon_code` = \'' . $coupon_code . '\''
				;
		$db->setQuery( $query );
		$couponid = $db->loadResult();

		if ( $couponid ) {
			// Its static, so set type to 1
			$return['type'] = 1;
		} else {
			// Coupon not found, take the regular table
			$query = 'SELECT `id`'
					. ' FROM #__acctexp_coupons'
					. ' WHERE `coupon_code` = \'' . $coupon_code . '\''
					;
			$db->setQuery( $query );
			$couponid = $db->loadResult();

			// Its not static, so set type to 0
			$return['type'] = 0;
		}

		$return['id'] = $couponid;

		return $return;
	}

	function load( $coupon_code )
	{
		$db = &JFactory::getDBO();

		$cc = $this->idFromCode( $coupon_code );

		$this->type = $cc['type'];

		if ( $cc['id'] ) {
			// Status = OK
			$this->status = true;

			// establish coupon object
			$this->coupon = new coupon( $this->type );
			$this->coupon->load( $cc['id'] );

			// Check whether coupon is active
			if ( !$this->coupon->active ) {
				$this->setError( JText::_('COUPON_ERROR_EXPIRED') );
			}

			// load parameters into local array
			$this->discount		= $this->coupon->discount;
			$this->restrictions = $this->coupon->restrictions;

			// Check whether coupon can be used yet
			if ( $this->restrictions['has_start_date'] && !empty( $this->restrictions['start_date'] ) ) {
				$expstamp = strtotime( $this->restrictions['start_date'] );

				// Error: Use of this coupon has not started yet
				if ( ( $expstamp > 0 ) && ( ( $expstamp - ( (int) gmdate('U') ) ) > 0 ) ) {
					$this->setError( JText::_('COUPON_ERROR_NOTSTARTED') );
				}
			}

			// Check whether coupon is expired
			if ( $this->restrictions['has_expiration'] ) {
				$expstamp = strtotime( $this->restrictions['expiration'] );

				// Error: Use of this coupon has expired
				if ( ( $expstamp > 0 ) && ( ( $expstamp - ( (int) gmdate('U') ) ) < 0 ) ) {
					$this->setError( JText::_('COUPON_ERROR_EXPIRED') );
					$this->coupon->deactivate();
				}
			}

			// Check for max reuse
			if ( !empty( $this->restrictions['has_max_reuse'] ) ) {
				if ( !empty( $this->restrictions['max_reuse'] ) ) {
					// Error: Max Reuse of this coupon is exceeded
					if ( (int) $this->coupon->usecount > (int) $this->restrictions['max_reuse'] ) {
						$this->setError( JText::_('COUPON_ERROR_MAX_REUSE') );
						return;
					}
				}
			}

			// Check for dependency on subscription
			if ( !empty( $this->restrictions['depend_on_subscr_id'] ) ) {
				if ( $this->restrictions['subscr_id_dependency'] ) {
					// See whether this subscription is active
					$query = 'SELECT `status`'
							. ' FROM #__acctexp_subscr'
							. ' WHERE `id` = \'' . $this->restrictions['subscr_id_dependency'] . '\''
							;
					$db->setQuery( $query );

					$subscr_status = strtolower( $db->loadResult() );

					// Error: The Subscription this Coupon depends on has run out
					if ( ( strcmp( $subscr_status, 'active' ) === 0 ) || ( ( strcmp( $subscr_status, 'trial' ) === 0 ) && $this->restrictions['allow_trial_depend_subscr'] ) ) {
						$this->setError( JText::_('COUPON_ERROR_SPONSORSHIP_ENDED') );
						return;
					}
				}
			}
		} else {
			// Error: Coupon does not exist
			$this->setError( JText::_('COUPON_ERROR_NOTFOUND') );
		}
	}

	function forceload( $coupon_code )
	{
		$cc = $this->idFromCode( $coupon_code );

		$this->type = $cc['type'];

		if ( $cc['id'] ) {
			// Status = OK
			$this->status = true;

			// establish coupon object
			$this->coupon = new coupon( $this->type );
			$this->coupon->load( $cc['id'] );
			return true;
		} else {
			return false;
		}
	}

	function switchType()
	{
		$db = &JFactory::getDBO();

		$oldtype = $this->coupon->type;

		// Duplicate Coupon at other table
		$coupon = new coupon( !$oldtype );
		$coupon->createNew( $this->coupon->coupon_code, $this->coupon->created_date );

		// Switch id over to new table max
		$oldid = $this->coupon->id;

		$this->coupon->delete();

		$this->coupon = $coupon;

		// Migrate usage entries
		$query = 'UPDATE #__acctexp_couponsxuser'
				. ' SET `coupon_id` = \'' . $this->coupon->id . '\', `coupon_type` = \'' . $this->coupon->type . '\''
				. ' WHERE `coupon_id` = \'' . $oldid . '\' AND `coupon_type` = \'' . $oldtype . '\''
				;

		$db->setQuery( $query );
		$db->query();
	}

	function incrementCount( $invoice )
	{
		$db = &JFactory::getDBO();

		// Get existing coupon relations for this user
		$query = 'SELECT `id`'
				. ' FROM #__acctexp_couponsxuser'
				. ' WHERE `userid` = \'' . $invoice->userid . '\''
				. ' AND `coupon_id` = \'' . $this->coupon->id . '\''
				. ' AND `coupon_type` = \'' . $this->type . '\''
				;

		$db->setQuery( $query );
		$id = $db->loadResult();

		$couponxuser = new couponXuser();

		if ( !empty( $id ) ) {
			// Relation exists, update count
			$couponxuser->load( $id );
			$couponxuser->usecount += 1;
			$couponxuser->addInvoice( $invoice->invoice_number );
			$couponxuser->last_updated = date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
			$couponxuser->storeload();
		} else {
			// Relation does not exist, create one
			$couponxuser->createNew( $invoice->userid, $this->coupon, $this->type );
			$couponxuser->addInvoice( $invoice->invoice_number );
			$couponxuser->storeload();
		}

		$this->coupon->incrementcount();
	}

	function decrementCount( $invoice )
	{
		$db = &JFactory::getDBO();

		// Get existing coupon relations for this user
		$query = 'SELECT `id`'
				. ' FROM #__acctexp_couponsxuser'
				. ' WHERE `userid` = \'' . $invoice->userid . '\''
				. ' AND `coupon_id` = \'' . $this->coupon->id . '\''
				. ' AND `coupon_type` = \'' . $this->type . '\''
				;

		$db->setQuery( $query );
		$id = $db->loadResult();

		$couponxuser = new couponXuser();

		// Only do something if a relation exists
		if ( $id ) {
			// Decrement use count
			$couponxuser->load( $id );
			$couponxuser->usecount -= 1;
			$couponxuser->last_updated = date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );

			if ( $couponxuser->usecount ) {
				// Use count is 1 or above - break invoice relation but leave overall relation intact
				$couponxuser->delInvoice( $invoice->invoice_number );
				$couponxuser->storeload();
			} else {
				// Use count is 0 or below - delete relationship
				$couponxuser->delete();
			}
		}

		$this->coupon->decrementCount();
	}

	function checkRestrictions( $metaUser, $terms=null, $usage=null )
	{
		if ( empty( $metaUser ) ) {
			return false;
		}

		$restrictionHelper = new aecRestrictionHelper();

		// Load Restrictions and resulting Permissions
		$restrictions	= $restrictionHelper->getRestrictionsArray( $this->restrictions );
		$permissions	= $metaUser->permissionResponse( $restrictions );

		// Check for a set usage
		if ( !empty( $this->restrictions['usage_plans_enabled'] ) && !is_null( $usage ) ) {
			if ( !empty( $this->restrictions['usage_plans'] ) ) {
				// Check whether this usage is restricted
				$plans = $this->restrictions['usage_plans'];

				if ( in_array( $usage, $plans ) ) {
					$permissions['usage'] = true;
				} else {
					$permissions['usage'] = false;
				}
			}
		}

		// Check for Trial only
		if ( $this->discount['useon_trial'] && !$this->discount['useon_full'] && is_object( $terms ) ) {
			$permissions['trial_only'] = false;

			if ( $terms->nextterm->type == 'trial' ) {
				$permissions['trial_only'] = true;
			}
		}

		// Check for max reuse per user
		if ( !empty( $this->restrictions['has_max_peruser_reuse'] ) && !empty( $this->restrictions['max_peruser_reuse'] ) ) {
			$used = $metaUser->usedCoupon( $this->coupon->id, $this->type );

			if ( $used == false ) {
				$permissions['max_peruser_reuse'] = true;
			} elseif ( (int) $used  <= (int) $this->restrictions['max_peruser_reuse'] ) {
				// use count was set immediately before, so <= is accurate
				$permissions['max_peruser_reuse'] = true;
			} else {
				$permissions['max_peruser_reuse'] = false;
			}
		}

		// Plot out error messages
		if ( count( $permissions ) ) {
			foreach ( $permissions as $name => $status ) {
				if ( !$status ) {
					$errors = array(	'fixgid'			=> 'permission',
										'mingid'			=> 'permission',
										'maxgid'			=> 'permission',
										'setgid'			=> 'permission',
										'usage'				=> 'wrong_usage',
										'trial_only'		=> 'trial_only',
										'plan_previous'		=> 'wrong_plan_previous',
										'plan_present'		=> 'wrong_plan',
										'plan_overall'		=> 'wrong_plans_overall',
										'plan_amount_min'	=> 'wrong_plan',
										'plan_amount_max'	=> 'wrong_plans_overall',
										'max_reuse'			=> 'max_reuse',
										'max_peruser_reuse'	=> 'max_reuse'
									);

					if ( isset( $errors[$name] ) ) {
						$this->setError( JText::_( strtoupper( 'coupon_error_' . $errors[$name] ) ) );
					} else {
						$this->status = false;
					}

					return false;
				}
			}
		}

		return true;
	}

	function getInfo( $amount )
	{
		$this->code = $this->coupon->coupon_code;
		$this->name = $this->coupon->name;

		if ( is_array( $amount ) ) {
			$newamount = $this->applyCoupon( $amount['amount'] );
		} else {
			$newamount = $this->applyCoupon( $amount );
		}

		// Load amount or convert amount array to current amount
		if ( is_array( $newamount ) ) {
			if ( isset( $newamount['amount1'] ) ) {
				$this->amount = $newamount['amount1'];
			} elseif ( isset( $newamount['amount2'] ) ) {
				$this->amount = $newamount['amount2'];
			} elseif ( isset( $newamount['amount3'] ) ) {
				$this->amount = $newamount['amount3'];
			}
		} else {
			$this->amount = $newamount;
		}

		// Load amount or convert discount amount array to current amount
		if ( is_array( $newamount ) ) {
			if ( isset( $newamount['amount1'] ) ) {
				$this->discount_amount = $amount['amount']['amount1'] - $newamount['amount1'];
			} elseif ( isset( $newamount['amount2'] ) ) {
				$this->discount_amount = $amount['amount']['amount3'] - $newamount['amount2'];
			} elseif ( isset( $newamount['amount3'] ) ) {
				$this->discount_amount = $amount['amount']['amount3'] - $newamount['amount3'];
			}
		} else {
			$this->discount_amount = $amount['amount'] - $newamount;
		}

		$action = '';

		// Convert chosen rules to user information
		if ( $this->discount['percent_first'] ) {
			if ( $this->discount['amount_percent_use'] ) {
				$action .= '-' . $this->discount['amount_percent'] . '%';
			}
			if ( $this->discount['amount_use'] ) {
				if ( !( $action === '' ) ) {
					$action .= ' &amp; ';
				}
				$action .= '-' . $this->discount['amount'];
			}
		} else {
			if ( $this->discount['amount_use']) {
				$action .= '-' . $this->discount['amount'];
			}
			if ($this->discount['amount_percent_use']) {
				if ( !( $action === '' ) ) {
					$action .= ' &amp; ';
				}
				$action .= '-' . $this->discount['amount_percent'] . '%';
			}
		}

		$this->action = $action;
	}

	function getCombinations()
	{
		$combinations = array();

		$cpl = array( 'bad_combinations', 'good_combinations', 'bad_combinations_cart', 'good_combinations_cart' );

		foreach ( $cpl as $cpn ) {
			$cmd = str_replace( "combinations", "combination", $cpn );

			if ( strpos( $cpn, 'bad' ) !== false ) {
				$cmd = str_replace( "bad", "restrict", $cmd );
			} else {
				$cmd = str_replace( "good", "allow", $cmd );
			}

			if ( !empty( $this->restrictions[$cmd] ) && !empty( $this->restrictions[$cpn] ) ) {
				$combinations[$cpn] = $this->restrictions[$cpn];
			} elseif ( !empty( $this->restrictions[$cmd] ) ) {
				$combinations[$cpn] = true;
			} else {
				$combinations[$cpn] = false;
			}
		}

		return $combinations;
	}

	function applyCoupon( $amount )
	{
		// Distinguish between recurring and one-off payments
		if ( is_array( $amount ) ) {
			// Check for Trial Rules
			if ( isset( $amount['amount1'] ) ) {
				if ( $this->discount['useon_trial'] ) {
					if ( $amount['amount1'] > 0 ) {
						$amount['amount1'] = $this->applyDiscount( $amount['amount1'] );
					}
				}
			}

			// Check for Full Rules
			if ( isset( $amount['amount3'] ) ) {
				if ( $this->discount['useon_full'] ) {
					if ( $this->discount['useon_full_all'] ) {
						$amount['amount3']	= $this->applyDiscount( $amount['amount3'] );
					} else {
						// If we have no trial yet, the one-off discount will be one
						if ( empty( $amount['period1'] ) ) {
							$amount['amount1']	= $this->applyDiscount( $amount['amount3'] );
							$amount['period1']	= $amount['period3'];
							$amount['unit1']	= $amount['unit3'];
						} else {
							if ( $amount['amount1'] > 0 ) {
								// If we already have a trial that costs, we can put the discount on that
								$amount['amount1']	= $this->applyDiscount( $amount['amount1'] );
								$amount['period1']	= $amount['period1'];
								$amount['unit1']	= $amount['unit1'];
							} else {
								// Otherwise we need to create a new period
								// Even in case the user cannot get it - then it will just be skipped anyhow
								$amount['amount2']	= $this->applyDiscount( $amount['amount3'] );
								$amount['period2']	= $amount['period3'];
								$amount['unit2']	= $amount['unit3'];
							}
						}
					}
				}
			}
		} else {
			$amount = $this->applyDiscount( $amount );
		}

		return $amount;
	}

	function applyDiscount( $amount )
	{
		// Apply Discount according to rules
		if ( $this->discount['percent_first'] ) {
			if ( $this->discount['amount_percent_use'] ) {
				$amount -= round( ( ( $amount / 100 ) * $this->discount['amount_percent'] ), 2 );
			}
			if ( $this->discount['amount_use'] ) {
				$amount -= $this->discount['amount'];
			}
		} else {
			if ( $this->discount['amount_use'] ) {
				$amount -= $this->discount['amount'];
			}
			if ( $this->discount['amount_percent_use'] ) {
				$amount -= round( ( ( $amount / 100 ) * $this->discount['amount_percent'] ), 2 );
			}
		}

		$amount = round( $amount, 2 );

		if ( $amount <= 0 ) {
			return "0.00";
		} else {
			// Fix Amount if broken and return
			return AECToolbox::correctAmount( $amount );
		}
	}

	function applyToTerms( $terms, $temp_coupon=false )
	{
		$offset = 0;

		// Only allow application on trial when there is one and the pointer is correct
		if ( $this->discount['useon_trial'] && $terms->hasTrial && ( $terms->pointer == 0 ) ) {
			$offset = 0;
		} elseif( $terms->hasTrial ) {
			$offset = 1;
		}

		$info = array();
		$info['coupon'] = $this->coupon->coupon_code;

		if ( $temp_coupon ) {
			$info['temp_coupon'] = true;
		}

		$initcount = count( $terms->terms );

		for ( $i = $offset; $i < $initcount; $i++ ) {
			// Check if this is only applied on Trial
			if ( !$this->discount['useon_full'] && ( $i > 0 ) ) {
				continue;
			}

			// Check whether it's only on ONE full period and whether we already have a nondiscounted copy set up
			if ( !$this->discount['useon_full_all'] && ( $i < $initcount ) && ( count($terms->terms[$i]->cost) < 3 ) ) {
				// Duplicate current term
				$newterm = unserialize( serialize( $terms->terms[$i] ) );

				$terms->addTerm( $newterm );
			}

			if ( $this->discount['percent_first'] ) {
				if ( $this->discount['amount_percent_use'] ) {
					$info['details'] = '-' . $this->discount['amount_percent'] . '%';
					$terms->terms[$i]->discount( null, $this->discount['amount_percent'], $info );
				}
				if ( $this->discount['amount_use'] ) {
					$info['details'] = null;
					$terms->terms[$i]->discount( $this->discount['amount'], null, $info );
				}
			} else {
				if ( $this->discount['amount_use'] ) {
					$info['details'] = null;
					$terms->terms[$i]->discount( $this->discount['amount'], null, $info );
				}
				if ( $this->discount['amount_percent_use'] ) {
					$info['details'] = '-' . $this->discount['amount_percent'] . '%';
					$terms->terms[$i]->discount( null, $this->discount['amount_percent'], $info );
				}
			}

			if ( $this->discount['useon_full'] && !$this->discount['useon_full_all'] ) {
				break;
			}
		}

		$terms->checkFree();

		return $terms;
	}

	function triggerMIs( $metaUser, $invoice, $new_plan )
	{
		global $aecConfig;

		// See whether this coupon has micro integrations
		if ( empty( $this->coupon->micro_integrations ) ) {
			return null;
		}

		foreach ( $this->coupon->micro_integrations as $mi_id ) {
			$mi = new microIntegration();

			// Only call if it exists
			if ( !$mi->mi_exists( $mi_id ) ) {
				continue;
			}

			$mi->load( $mi_id );

			// Check whether we can really call
			if ( !$mi->callIntegration() ) {
				continue;
			}

			if ( is_object( $metaUser ) ) {
				if ( $mi->action( $metaUser, null, $invoice, $new_plan ) === false ) {
					if ( $aecConfig->cfg['breakon_mi_error'] ) {
						return false;
					}
				}
			} else {
				if ( $mi->action( false, null, $invoice, $new_plan ) === false ) {
					if ( $aecConfig->cfg['breakon_mi_error'] ) {
						return false;
					}
				}
			}
		}
	}
}

class Coupon extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $active				= null;
	/** @var string */
	var $coupon_code		= null;
	/** @var datetime */
	var $created_date 		= null;
	/** @var string */
	var $name				= null;
	/** @var string */
	var $desc				= null;
	/** @var text */
	var $discount			= null;
	/** @var text */
	var $restrictions		= null;
	/** @var text */
	var $params				= null;
	/** @var int */
	var $usecount			= null;
	/** @var text */
	var $micro_integrations	= null;

	function Coupon( $type=0 )
	{
		if ( $type ) {
			parent::__construct( '#__acctexp_coupons_static', 'id' );
		} else {
			parent::__construct( '#__acctexp_coupons', 'id' );
		}
	}

	function load( $id )
	{
		parent::load( $id );

		$this->getType();
	}

	function getType()
	{
		$this->type = 0;

		if ( strpos( $this->getTableName(), 'acctexp_coupons_static' ) ) {
			$this->type = 1;
		}

		return $this->type;
	}

	function declareParamFields()
	{
		return array( 'discount', 'restrictions', 'params', 'micro_integrations'  );
	}

	function deactivate()
	{
		$this->active = 0;
		$this->storeload();
	}

	function createNew( $code=null, $created=null )
	{
		$this->id		= 0;
		$this->active	= 1;

		// Override creation of new Coupon Code if one is supplied
		if ( is_null( $code ) ) {
			$this->coupon_code = $this->generateCouponCode();
		} else {
			$this->coupon_code = $code;
		}

		// Set created date if supplied
		if ( is_null( $created ) ) {
			$this->created_date = date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
		} else {
			$this->created_date = $created;
		}

		$this->usecount = 0;

		$this->storeload();

		$this->getType();

		$this->id = $this->getMax();
	}

	function savePOSTsettings( $post )
	{
		if ( !empty( $post['coupon_code'] ) ) {
			$query = 'SELECT `id`'
					. ' FROM #__acctexp_coupons_static'
					. ' WHERE `coupon_code` = \'' . $post['coupon_code'] . '\''
					;
			$this->_db->setQuery( $query );
			$couponid = $this->_db->loadResult();

			if ( empty( $couponid ) ) {
				$query = 'SELECT `id`'
						. ' FROM #__acctexp_coupons'
						. ' WHERE `coupon_code` = \'' . $post['coupon_code'] . '\''
						;
				$this->_db->setQuery( $query );
				$couponid = $this->_db->loadResult();
			}

			if ( !empty( $couponid ) && ( $couponid !== $this->id ) ) {
				$post['coupon_code'] = $this->generateCouponCode();
			}
		}

		// Filter out fixed variables
		$fixed = array( 'active', 'name', 'desc', 'coupon_code', 'usecount', 'micro_integrations' );

		foreach ( $fixed as $varname ) {
			if ( isset( $post[$varname] ) ) {
				$this->$varname = $post[$varname];
				unset( $post[$varname] );
			} else {
				$this->$varname = null;
			}
		}

		// Filter out params
		$fixed = array( 'amount_use', 'amount', 'amount_percent_use', 'amount_percent', 'percent_first', 'useon_trial', 'useon_full', 'useon_full_all' );

		$params = array();
		foreach ( $fixed as $varname ) {
			if ( !isset( $post[$varname] ) ) {
				continue;
			}

			$params[$varname] = $post[$varname];
			unset( $post[$varname] );
		}

		$this->saveDiscount( $params );

		// the rest is restrictions
		$this->saveRestrictions( $post );
	}

	function saveDiscount( $params )
	{
		// Correct a malformed Amount
		if ( !strlen( $params['amount'] ) ) {
			$params['amount_use'] = 0;
		} else {
			$params['amount'] = AECToolbox::correctAmount( $params['amount'] );
		}

		$this->discount = $params;
	}

	function saveRestrictions( $restrictions )
	{
		$this->restrictions = $restrictions;
	}

	function incrementCount()
	{
		$this->usecount += 1;
		$this->storeload();
	}

	function decrementCount()
	{
		if ( $this->usecount ) {
			$this->usecount -= 1;
			$this->storeload();
		}
	}

	function generateCouponCode( $maxlength = 6 )
	{
		$numberofrows = 1;

		while ( $numberofrows ) {
			$inum =	strtoupper( substr( base64_encode( md5( rand() ) ), 0, $maxlength ) );
			// check single coupons
			$query = 'SELECT count(*)'
					. ' FROM #__acctexp_coupons'
					. ' WHERE `coupon_code` = \'' . $inum . '\''
					;
			$this->_db->setQuery( $query );
			$numberofrows_normal = $this->_db->loadResult();

			// check static coupons
			$query = 'SELECT count(*)'
					. ' FROM #__acctexp_coupons_static'
					. ' WHERE `coupon_code` = \'' . $inum . '\''
					;
			$this->_db->setQuery( $query );
			$numberofrows_static = $this->_db->loadResult();

			$numberofrows = $numberofrows_normal + $numberofrows_static;
		}

		return $inum;
	}

	function copy()
	{
		$this->id = 0;
		$this->coupon_code = $this->generateCouponCode();
		$this->usecount = 0;
		$this->check();
		$this->store();
	}

	function check()
	{
		if ( isset( $this->type ) ) {
			unset( $this->type );
		}

		parent::check();
	}
}

class couponXuser extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $coupon_id			= null;
	/** @var int */
	var $coupon_type		= null;
	/** @var string */
	var $coupon_code		= null;
	/** @var int */
	var $userid				= null;
	/** @var datetime */
	var $created_date 		= null;
	/** @var datetime */
	var $last_updated		= null;
	/** @var text */
	var $params				= null;
	/** @var int */
	var $usecount			= null;

	function couponXuser()
	{
		parent::__construct( '#__acctexp_couponsxuser', 'id' );
	}

	function declareParamFields()
	{
		return array( 'params'  );
	}

	function createNew( $userid, $coupon, $type, $params=null )
	{
		$this->id = 0;
		$this->coupon_id = $coupon->id;
		$this->coupon_type = $type;
		$this->coupon_code = $coupon->coupon_code;
		$this->userid = $userid;
		$this->created_date = date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
		$this->last_updated = date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );

		if ( is_array( $params ) ) {
			$this->params = $params;
		}

		$this->usecount = 1;

		$this->storeload();
	}

	function getInvoiceList()
	{
		$invoicelist = array();
		if ( isset( $this->params['invoices'] ) ) {
			$invoices = explode( ';', $this->params['invoices'] );

			foreach ( $invoices as $invoice ) {
				$inv = explode( ',', $invoice );

				if ( isset( $invoice[1] ) ) {
					$invoicelist[$invoice[0]] = $invoice[1];
				} else {
					$invoicelist[$invoice[0]] = 1;
				}
			}
		}

		return $invoicelist;
	}

	function setInvoiceList( $invoicelist )
	{
		$invoices = array();

		foreach ( $invoicelist as $invoicenumber => $counter ) {
			$invoices[] = $invoicenumber . ',' . $counter;
		}

		$params['invoices'] = implode( ';', $invoices );

		$this->addParams( $params );
	}

	function addInvoice( $invoicenumber )
	{
		$invoicelist = $this->getInvoiceList();

		if ( isset( $invoicelist[$invoicenumber] ) ) {
			$invoicelist[$invoicenumber] += 1;
		} else {
			$invoicelist[$invoicenumber] = 1;
		}

		$this->setInvoiceList( $invoicelist );
	}

	function delInvoice( $invoicenumber )
	{
		$invoicelist = $this->getInvoiceList();

		if ( isset( $invoicelist[$invoicenumber] ) ) {
			$invoicelist[$invoicenumber] -= 1;

			if ( $invoicelist[$invoicenumber] === 0 ) {
				unset( $invoicelist[$invoicenumber] );
			}
		}

		$this->setInvoiceList( $invoicelist );
	}
}

?>

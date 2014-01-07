<?php
/**
 * @version $Id: acctexp.cart.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class CartFactory
{
	function CartFactory()
	{
		
	}

	function addItem( $type )
	{
		
	}

	function addCoupon( $coupon, $id )
	{
		
	}

	function removeItem( $id )
	{
		
	}
}

class aecCartHelper
{
	function getCartidbyUserid( $userid )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT `id`'
				. ' FROM #__acctexp_cart'
				. ' WHERE userid = \'' . $userid . '\''
				;

		$db->setQuery( $query );
		return $db->loadResult();

	}

	function getCartbyUserid( $userid )
	{
		$id = aecCartHelper::getCartidbyUserid( $userid );

		$cart = new aecCart();
		$cart->load( $id );

		if ( empty( $id ) ) {
			$cart->userid = $userid;
		}

		return $cart;
	}

	function getCartItemObject( $cart, $id )
	{
		$item = $cart->getItem( $id );
		if ( !empty( $item ) ) {
			$plan = new SubscriptionPlan();
			$plan->load( $item['id'] );

			return $plan;
		}
	}

	function getFirstCartItemObject( $cart )
	{
		if ( !empty( $cart->content ) ) {
			foreach ( $cart->content as $cid => $c ) {
				return aecCartHelper::getCartItemObject( $cart, $cid );
			}
		}

		return null;
	}

	function getFirstSortedCartItemObject( $cart )
	{
		$db = &JFactory::getDBO();

		$highest = 0;
		$cursor = 1000000;

		foreach ( $cart->content as $cid => $c ) {
			$query = 'SELECT ordering'
			. ' FROM #__acctexp_plans'
			. ' WHERE `id` = \'' . $c['id'] . '\''
			;
			$db->setQuery( $query );
			$ordering = $db->loadResult();

			if ( $ordering < $cursor ) {
				$highest = $cid;
				$cursor = $ordering;
			}
		}

		return aecCartHelper::getCartItemObject( $cart, $highest );
	}

	function getCartProcessorList( $cart, $nofree=true )
	{
		$proclist = array();

		if ( empty( $cart->content ) ) {
			return $proclist;
		}

		foreach ( $cart->content as $cid => $c ) {
			$cartitem = aecCartHelper::getCartItemObject( $cart, $cid );

			if ( is_array( $cartitem->params['processors'] ) && !empty( $cartitem->params['processors'] ) ) {
				foreach ( $cartitem->params['processors'] as $pid ) {
					$sid = $pid;

					/*if ( $cartitem->custom_params[$pid . '_aec_overwrite_settings'] ) {
						if ( !empty( $cartitem->custom_params[$pid . '_recurring'] ) ) {
							if ( $cartitem->custom_params[$pid . '_recurring'] == 2 ) {
								if ( array_search( $sid, $proclist ) === false ) {
									$proclist[] = $sid;
								}
							}

							$sid .= '_recurring';
						}
					}*/

					if ( array_search( $sid, $proclist ) === false ) {
						$proclist[] = $sid;
					}
				}
			}
		}

		return $proclist;
	}

	function getCartProcessorGroups( $cart )
	{
		$pgroups	= array();

		foreach ( $cart->content as $cid => $c ) {
			$cartitem = aecCartHelper::getCartItemObject( $cart, $cid );

			$pplist			= array();
			$pplist_names	= array();
			if ( !empty( $cartitem->params['processors'] ) ) {
				foreach ( $cartitem->params['processors'] as $n ) {
					$pp = new PaymentProcessor();

					if ( !$pp->loadId( $n ) ) {
						continue;
					}

					$pp->init();
					$pp->getInfo();
					$pp->exchangeSettingsByPlan( $cartitem );

					if ( isset( $this->recurring ) ) {
						$recurring = $pp->is_recurring( $this->recurring );
					} else {
						$recurring = $pp->is_recurring();
					}

					if ( $recurring > 1 ) {
						$pplist[]		= $pp->id;
						$pplist_names[]	= $pp->info['longname'];

						if ( !$cartitem->params['lifetime'] ) {
							$pplist[]		= $pp->id . '_recurring';
							$pplist_names[]	= $pp->info['longname'];
						}
					} elseif ( !$cartitem->params['lifetime'] && $recurring ) {
						$pplist[]		= $pp->id . '_recurring';
						$pplist_names[]	= $pp->info['longname'];
					} else {
						$pplist[]		= $pp->id;
						$pplist_names[]	= $pp->info['longname'];
					}
				}
			}

			if ( empty( $pplist ) ) {
				continue;
			}

			if ( empty( $pgroups ) ) {
				$pg = array();
				$pg['members']			= array( $cid );
				$pg['processors']		= $pplist;
				$pg['processor_names']	= $pplist_names;

				$pgroups[] = $pg;
			} else {
				$create = true;

				foreach ( $pgroups as $pgid => $pgroup ) {
					$pg = array();

					if ( count( $pplist ) == count( $pgroup['processors'] ) ) {
						$a = true;
						foreach ( $pplist as $k => $v ) {
							if ( $pgroup['processors'][$k] != $v ) {
								$a = false;
							}
						}

						if ( $a ) {
							$pgroups[$pgid]['members'][] = $cid;
							$create = false;
						}
					}
				}

				if ( $create ) {
					$pg['members']			= array( $cid );
					$pg['processors']		= $pplist;
					$pg['processor_names']	= $pplist_names;

					$pgroups[] = $pg;
				}
			}
		}

		return $pgroups;
	}

	function getInvoiceIdByCart( $cart )
	{
		$db = &JFactory::getDBO();

		$query = 'SELECT id'
		. ' FROM #__acctexp_invoices'
		. ' WHERE `usage` = \'c.' . $cart->id . '\''
		. ' AND active = \'1\''
		;
		$db->setQuery( $query );

		return $db->loadResult();
	}
}

class aecCart extends serialParamDBTable
{
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $userid				= null;
	/** @var datetime */
	var $created_date		= null;
	/** @var datetime */
	var $last_updated		= null;
	/** @var text */
	var $content 			= array();
	/** @var text */
	var $history 			= array();
	/** @var text */
	var $params 			= array();
	/** @var text */
	var $customparams		= array();

	function aecCart()
	{
		parent::__construct( '#__acctexp_cart', 'id' );
	}

	function declareParamFields()
	{
		return array( 'content', 'history', 'params', 'customparams' );
	}

	function check()
	{
		$vars = get_class_vars( 'aecCart' );
		$props = get_object_vars( $this );

		foreach ( $props as $n => $prop ) {
			if ( !array_key_exists( $n, $vars  ) ) {
				unset( $this->$n );
			}
		}

		return parent::check();
	}

	function save()
	{
		if ( !$this->id || ( strcmp( $user_subscription->created_date, '0000-00-00 00:00:00' ) !== 0 ) ) {
			$this->created_date = date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );
		}

		$this->last_updated = date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) );

		return parent::save();
	}

	function action( $action, $details=null )
	{
		if ( $action == "clearCart" ) {
			// Delete Invoices referencing this Cart as well
			$query = 'DELETE FROM #__acctexp_invoices WHERE `usage` = \'c.' . $this->id . '\'';
			$this->_db->setQuery( $query );
			$this->_db->query();

			return $this->delete();
		}

		if ( method_exists( $this, $action ) ) {
			$a = array( 'action' => 'action',
						'event' => $action,
						'details' => $details
			);

			$return = $this->$action( $a, $details );

			$this->issueHistoryEvent( $return['action'], $return['event'], $return['details'] );
		} else {
			$this->issueHistoryEvent( 'error', 'action_not_found', array( $action, $details ) );
		}

		$this->storeload();
	}

	function addItem( $return, $item )
	{
		if ( is_object( $item ) ) {
			$id = $item->id;
		} else {
			$id = $item;
		}

		if ( !empty( $id ) ) {
			$element = array();
			$element['type']		= 'plan';
			$element['id']			= $id;
			$element['quantity']	= 1;

			$return['details'] = array( 'type' => 'plan', 'id' => $id );

			$update = false;
			if ( !empty( $this->content ) ) {
				foreach ( $this->content as $iid => $plan ) {
					if ( ( $plan['type'] == $element['type'] ) && ( $plan['id'] == $element['id'] ) ) {
						if ( !empty( $item->settings['addtocart_max'] ) ) {
							if ( $this->content[$iid]['quantity'] < $item->settings['addtocart_max'] ) {
								$return['event'] = 'updateItem';
								$this->content[$iid]['quantity']++;
							} else {
								$return['action']	= 'error';
								$return['event']	= 'item_qty_maxed_out';
							}
						} else {
							$return['event'] = 'updateItem';
							$this->content[$iid]['quantity']++;
						}

						$update = true;
						break;
					}
				}
			}

			if ( !$update ) {
				$this->content[] = $element;
			}
		} else {
			$return['action']	= 'error';
			$return['event']	= 'no_item_provided';
			$return['details']	= array( 'type' => 'plan', 'item' => $item );
		}

		return $return;
	}

	function addCoupon( $coupon_code, $id=null )
	{
		if ( is_null( $id ) ) {
			if ( !isset( $this->params['overall_coupons'] ) ) {
				$this->params['overall_coupons'] = array();
			}

			if ( !in_array( $coupon_code, $this->params['overall_coupons'] ) ) {
				$this->params['overall_coupons'][] = $coupon_code;
			}
		} elseif ( isset( $this->content[$id] ) ) {
			if ( !isset( $this->content[$id]['coupons'] ) ) {
				$this->content[$id]['coupons'] = array();
			}

			if ( !in_array( $coupon_code, $this->content[$id]['coupons'] ) ) {
				$this->content[$id]['coupons'][] = $coupon_code;
			}
		}
	}

	function removeCoupon( $coupon_code, $id=null )
	{
		foreach ( $this->content as $cid => $content ) {
			if ( !is_null( $id ) ) {
				if ( $id !== $cid ) {
					continue;
				}
			}

			if ( !empty( $content['coupons'] ) ) {
				foreach ( $content['coupons'] as $ccid => $code ) {
					if ( $code == $coupon_code ) {
						unset( $this->content[$cid]['coupons'][$ccid] );
					}
				}
			}
		}

		if ( is_null( $id ) ) {
			if ( is_array( $this->params['overall_coupons'] ) && !empty( $this->params['overall_coupons'] ) ) {
				if ( in_array( $coupon_code, $this->params['overall_coupons'] ) ) {
					$oid = array_search( $coupon_code, $this->params['overall_coupons'] );
					unset( $this->params['overall_coupons'][$oid] );
				}
			}
		}
	}

	function hasCoupon( $coupon_code, $id=null )
	{
		if ( is_null( $id ) ) {
			if ( !empty( $this->params['overall_coupons'] ) ) {
				return in_array( $coupon_code, $this->params['overall_coupons'] );
			} else {
				return false;
			}
		} elseif ( isset( $this->content[$id] ) ) {
			if ( !empty( $this->content[$id]['coupons'] ) ) {
				return in_array( $coupon_code, $this->content[$id]['coupons'] );
			} else {
				return false;
			}
		}

		return false;
	}

	function getItemIdArray()
	{
		$array = array();
		foreach ( $this->content as $cid => $content ) {
			$array[$cid] = $content['id'];
		}

		return $array;
	}

	function getItem( $item )
	{
		if ( isset( $this->content[$item] ) ) {
			return $this->content[$item];
		} else {
			return null;
		}
	}

	function removeItem( $return, $itemid )
	{
		if ( isset( $this->content[$itemid] ) ) {
			$return['details'] = array( 'item_id' => $itemid, 'item' => $this->content[$itemid] );
			unset( $this->content[$itemid] );
		} else {
			$return = array(	'action' => 'error',
								'event' => 'item_not_found',
								'details' => array( 'item_id' => $itemid )
								);
		}

		return $return;
	}

	function updateItems( $return, $updates )
	{
		foreach ( $updates as $uid => $count ) {
			if ( isset( $this->content[$uid] ) ) {
				if ( empty( $count ) ) {
					unset( $this->content[$uid] );
				} else {
					$item = aecCartHelper::getCartItemObject( $this, $uid );
					if ( !empty( $item->params['addtocart_max'] ) ) {
						if ( $count <= $item->params['addtocart_max'] ) {
							$this->content[$uid]['quantity'] = $count;
						} else {
							$this->content[$uid]['quantity'] = $item->params['addtocart_max'];
						}
					} else {
						$this->content[$uid]['quantity'] = $count;
					}
				}
			}
		}

		return $return;
	}

	function getCheckout( $metaUser, $counter=0, $InvoiceFactory=null )
	{
		$c = array();

		$totalcost = 0;

		if ( empty( $this->content ) ) {
			return array();
		}

		$return = array();
		foreach ( $this->content as $cid => $content ) {
			// Cache items
			if ( !isset( $c[$content['type']][$content['id']] ) ) {
				switch ( $content['type'] ) {
					case 'plan':
						$obj = new SubscriptionPlan();
						$obj->load( $content['id'] );

						$o = array();
						$o['obj']	= $obj;
						$o['name']	= $obj->getProperty( 'name' );
						$o['desc']	= $obj->getProperty( 'desc' );

						$terms = $obj->getTermsForUser( false, $metaUser );

						if ( $counter ) {
							$terms->incrementPointer( $counter );
						}

						$o['terms']	= $terms;
						$o['cost']	= $terms->nextterm->renderCost();

						$c[$content['type']][$content['id']] = $o;
						break;
				}
			}

			$entry = array();
			$entry['obj']			= $c[$content['type']][$content['id']]['obj'];
			$entry['fullamount']	= $c[$content['type']][$content['id']]['cost'];

			$entry['name']			= $c[$content['type']][$content['id']]['name'];
			$entry['desc']			= $c[$content['type']][$content['id']]['desc'];
			$entry['terms']			= $c[$content['type']][$content['id']]['terms'];

			$item = array( 'item' => array( 'obj' => $entry['obj'] ), 'terms' => $entry['terms'] );

			if ( !empty( $content['coupons'] ) ) {
				$cpsh = new couponsHandler( $metaUser, false, $content['coupons'] );

				$item = $cpsh->applyAllToItems( 0, $item );

				$entry['terms'] = $item['terms'];
			}

			$entry['cost'] = $entry['terms']->nextterm->renderTotal();

			if ( $entry['cost'] > 0 ) {
				$total = $content['quantity'] * $entry['cost'];

				$entry['cost_total']	= AECToolbox::correctAmount( $total );
			} else {
				$entry['cost_total']	= AECToolbox::correctAmount( '0.00' );
			}

			if ( $entry['cost_total'] == '0.00' ) {
				$entry['free'] = true;
			} else {
				$entry['free'] = false;
			}

			$entry['cost']			= AECToolbox::correctAmount( $entry['cost'] );

			$entry['quantity']		= $content['quantity'];

			$totalcost += $entry['cost_total'];

			$return[$cid] = $entry;
		}

		if ( !empty( $this->params['overall_coupons'] ) ) {
			$cpsh = new couponsHandler( $metaUser, $InvoiceFactory, $this->params['overall_coupons'] );

			$totalcost_ncp = $totalcost;
			$totalcost = $cpsh->applyToAmount( $totalcost );
		} else {
			$totalcost_ncp = $totalcost;
		}

		// Append total cost
		$return[] = array( 'name' => '',
							'count' => '',
							'cost' => AECToolbox::correctAmount( $totalcost_ncp ),
							'cost_total' => AECToolbox::correctAmount( $totalcost ),
							'is_total' => true,
							'obj' => false
							);

		return $return;
	}

	function getAmount( $metaUser=null, $counter=0, $InvoiceFactory=null )
	{
		$checkout = $this->getCheckout( $metaUser, $counter, $InvoiceFactory );

		if ( !empty( $checkout ) ) {
			$max = array_pop( array_keys( $checkout ) );

			return $checkout[$max]['cost_total'];
		} else {
			return '0.00';
		}
	}

	function checkAllFree( $metaUser, $counter=0, $InvoiceFactory=null )
	{
		$co = $this->getCheckout( $metaUser, $counter, $InvoiceFactory );

		foreach ( $co as $entry ) {
			if ( is_object( $entry ) ) {
				if ( !$entry['terms']->checkFree() ) {
					return false;
				}
			}
		}

		return true;
	}

	function getTopPlan()
	{
		return aecCartHelper::getFirstSortedCartItemObject( $this );
	}

	function triggerMIs( $action, &$metaUser, &$exchange, &$invoice, &$add, &$silent )
	{
		if ( is_array( $add ) ) {
			if ( !empty( $add['obj'] ) ) {
				$add['obj']->triggerMIs( $action, $metaUser, $exchange, $invoice, $add, $silent );
			}
		} else {
			if ( is_object( $add ) ) {
				foreach ( $add->itemlist as $nadd ) {
					$nadd['obj']->triggerMIs( $action, $metaUser, $exchange, $invoice, $add, $silent );
				}
			}
		}
	}

	function issueHistoryEvent( $class, $event, $details )
	{
		if ( $class == 'error' ) {
			$this->_error = $event;
		}

		if ( !is_array( $this->history ) ) {
			$this->history = array();
		}

		$this->history[] = array(
							'timestamp'	=> date( 'Y-m-d H:i:s', ( (int) gmdate('U') ) ),
							'class'		=> $class,
							'event'		=> $event,
							'details'	=> $details,
							);

		return true;
	}
}

class LineItemList
{
	var $list = array();

	function addItem( $type, $args )
	{
		$itemclass = 'LineItem'.ucfirst($type);

		if ( !class_exists( $itemclass ) ) {
			$classpath = dirname(__FILE__) . '../itemtypes/acctexp.lineitem.' . strtolower($type) . '.php';

			if ( file_exists( $classpath ) ) {
				include( $classpath );
			} else {
				return false;
			}
		}

		$item = new $itemclass( $args );
	}

	function add( $item )
	{
		$this->list[] = $item;
	}

	function remove( $id )
	{
		if ( isset( $this->list[$id] ) ) {
			unset( $this->list[$id] );

			return true;
		}

		return null;
	}

	function checkCoherence()
	{
		// Test whether this cart can be paid in a single invoice or has to be split up
	}
}

class LineItem
{
	var $obj = null;
	var $qty = null;
	var $amt = null;

	function LineItem( $args )
	{
		if ( isset( $args['id'] ) ) {
			$this->loadObject( $args['id'] );
		}

		if ( isset( $args['qty'] ) ) {
			$this->qty = $args['qty'];
		} elseif( empty( $this->qty ) ) {
			$this->qty = 1;
		}

		$this->getAmount();
	}

	function loadObject( $id )
	{
		return null;
	}

	function updateQuantity( $qty )
	{
		$this->qty = $qty;		
	}

	function getAmount()
	{
		$this->amt = 0.00;
	}

	function getItemName()
	{
		return "StdItem";
	}
}

class LineItemCustom extends LineItem
{
	
}

class LineItemSubscriptionPlan extends LineItem
{
	function loadObject( $id )
	{
		$this->obj = new SubscriptionPlan();
		$this->obj->load( $id );
	}

	function getItemName()
	{
		return $this->obj->getProperty( 'name' );
	}

	function getItemTerms()
	{
		
	}
}

?>

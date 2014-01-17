<?php
/**
 * @version $Id: acctexp.itemterms.class.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Core Class
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class itemTerms extends eucaObject
{
	var $hasTrial	= null;
	var $free		= null;
	var $pointer	= 0;
	var $terms		= array();

	function readParams( $params, $allow_trial=true )
	{
		// Old params only had trial and full
		if ( $allow_trial ) {
			$terms	= array( 'trial_', 'full_' );
		} else {
			$terms	= array( 'full_' );
		}

		$return	= false;

		$this->pointer = 0;

		$this->terms = array();

		foreach ( $terms as $t ) {
			// Make sure this period is actually of substance
			if ( !empty( $params[$t.'period'] ) || ( !empty( $params['lifetime'] ) && ( $t == 'full_' ) ) ) {
				$term = new itemTerm();

				// If we have a trial, we need to mark this
				if ( $t == 'trial_' ) {
					$this->set( 'hasTrial', true );
					$term->set( 'type', 'trial' );
				} else {
					$term->set( 'type', 'term' );
				}

				if ( $t != 'trial_' && !empty( $params['lifetime'] ) ) {
					$duration['lifetime']	= true;
				} else {
					$duration['period']		= $params[$t.'period'];
					$duration['unit']		= $params[$t.'periodunit'];
				}

				$term->set( 'duration', $duration );

				if ( $params[$t.'free'] ) {
					$term->addCost( '0.00' );
				} else {
					$term->addCost( $params[$t.'amount'] );
				}

				$this->addTerm( $term );
				$return = true;
			}
		}

		$free = true;
		foreach ( $this->terms as $term ) {
			if ( empty( $term->free ) ) {
				$free = false;
			}
		}

		$this->free = $free;

		$this->nextterm =& $this->terms[$this->pointer];

		return $return;
	}

	function getOldAmount( $recurring=true )
	{
		$amount = array();

		$apointer = 0;
		foreach ( $this->terms as $tid => $term ) {
			$apointer++;
			if ( $tid < $this->pointer ) {
				continue;
			}

			$i = $apointer;

			if ( count( $this->terms ) == 1 ) {
				$i = 3;
			} elseif ( count( $this->terms ) == 2 ) {
				if ( $i == 2 ) {
					$i = 3;
				}
			}

			if ( $recurring ) {
				$amount['amount'.$i]	= $term->renderTotal();
				$amount['period'.$i]	= $term->duration['period'];
				$amount['unit'.$i]		= $term->duration['unit'];
			} else {
				$amount = $term->renderTotal();

				// Only render "next" amount
				return $amount;
			}
		}

		return $amount;
	}

	function incrementPointer( $amount=1 )
	{
		for ( $i=0; $i<$amount; $i++ ) {
			if ( $this->pointer < ( count( $this->terms ) - 1 ) ) {
				$this->pointer++;
			}
		}

		$this->nextterm =& $this->terms[$this->pointer];
	}

	function decrementPointer( $amount=1 )
	{
		for ( $i=0; $i<$amount; $i++ ) {
			if ( $this->pointer > 0 ) {
				$this->pointer--;
			}
		}

		$this->nextterm =& $this->terms[$this->pointer];
	}

	function setPointer( $pointer )
	{
		if ( empty( $pointer ) ) {
			$pointer = 0;
		}

		if ( $pointer < ( count( $this->terms ) ) ) {
			$this->pointer = $pointer;
		} else {
			$this->pointer = count( $this->terms ) - 1;
		}

		$this->nextterm =& $this->terms[$this->pointer];
	}

	function addTerm( $term )
	{
		array_push( $this->terms, $term );
	}

	function getTerms()
	{
		return $this->terms;
	}

	function checkFree()
	{
		$free = true;
		foreach ( $this->terms as $term ) {
			if ( empty( $term->free ) ) {
				$free = false;
			}
		}

		$this->free = $free;

		return $this->free;
	}

	function renderTotal()
	{
		$cost = 0;
		foreach ( $this->terms as $term ) {
			$cost = $cost + $term->renderTotal();
		}

		return $cost;
	}

}

class itemTerm extends eucaObject
{
	var $type			= null;
	var $start			= array();
	var $duration		= array();
	var $cost			= array();
	var $free			= false;

	function itemTerm()
	{
		$this->set( 'duration', array( 'none' => true ) );
	}

	function renderDuration()
	{
		if ( isset( $this->duration['none'] ) ) {
			return '';
		} elseif ( isset( $this->duration['lifetime'] ) ) {
			return JText::_('AEC_CHECKOUT_DUR_LIFETIME');
		} else {
			switch ( $this->duration['unit'] ) {
				case 'D':
					$unit = 'day';
					break;
				case 'W':
					$unit = 'week';
					break;
				case 'M':
					$unit = 'month';
					break;
				case 'Y':
					$unit = 'year';
					break;
			}

			if ( $this->duration['period'] > 1 ) {
				$unit .= 's';
			}

			return $this->duration['period'] . ' ' . JText::_( strtoupper( 'aec_checkout_dur_' . $unit ) );
		}
	}

	function renderCost()
	{
		if ( count( $this->cost ) <= 2 ) {
			return array( $this->cost[0] );
		} else {
			return $this->cost;
		}
	}

	function addCost( $amount, $info=null )
	{
		if ( !empty( $this->cost ) ) {
			// Delete current total, if exists
			foreach( $this->cost as $cid => $cost ) {
				if ( $cost->type == 'total' ) {
					unset( $this->cost[$cid] );
				}
			}
		}

		if ( empty( $info ) ) {
			$info = array();
		}

		// Tax is Tax, whether positive or negative
		if ( !empty( $info['tax'] ) ) {
			$type = 'tax';
		} else {
			// Switch this to discount if its negative
			if ( $amount < 0 ) {
				$type = 'discount';
			} else {
				$type = 'cost';
			}
		}

		if ( empty( $this->type ) ) {
			$this->type = $type;
		}

		$cost = new itemCost();
		$cost->set( 'type', $type );

		if ( !empty( $info ) && is_array( $info ) ) {
			$content = array_merge( array( 'amount' => $amount ), $info );

			$cost->set( 'cost', $content );
		} else {
			$cost->set( 'cost', array( 'amount' => $amount ) );
		}

		$this->cost[] = $cost;

		// Compute value of total cost
		$total = 0;
		$tax = 0;
		foreach ( $this->cost as $citem ) {
			$total += $citem->renderCost();

			if ( $citem->type == 'tax' ) {
				$tax++;
			}
		}

		if ( $tax == count( $this->cost ) ) {
			return;
		}

		// Set total cost object
		$cost = new itemCost();
		$cost->set( 'type', 'total' );
		$cost->set( 'cost', array( 'amount' => $total ) );

		if ( $cost->isFree() ) {
			$this->free = true;
		}

		$this->cost[] = $cost;
	}

	function setCost( $amount, $info=null )
	{
		$this->cost = array();

		$cost = new itemCost();
		$cost->set( 'type', 'cost' );

		if ( !empty( $info ) && is_array( $info ) ) {
			$content = array_merge( array( 'amount' => $amount ), $info );

			$cost->set( 'cost', $content );
		} else {
			$cost->set( 'cost', array( 'amount' => $amount ) );
		}

		$this->cost[] = $cost;

		$this->computeTotal();
	}

	function modifyCost( $id, $amount )
	{
		$this->cost[$id]->cost['amount'] = $amount;

		$this->computeTotal();
	}

	function discount( $amount, $percent=null, $info=null  )
	{
		// Only apply if its not already free
		if ( !$this->free ) {
			// discount amount
			if ( !empty( $amount ) ) {
				$total = $this->renderTotal();

				foreach ( $this->cost as $cost ) {
					if ( !empty( $cost->cost['no-discount'] ) ) {
						$total = $total - $cost->cost['amount'];
					}
				}

				if ( $amount > $this->renderTotal() ) {
					$amount = $total;
				}

				$am = 0 - $amount;
				$this->addCost( $am, $info );
			}

			// discount percentage
			if ( !empty( $percent ) ) {
				$total = $this->renderTotal();

				foreach ( $this->cost as $cost ) {
					if ( !empty( $cost->cost['no-discount'] ) ) {
						$total = $total - $cost->cost['amount'];
					}
				}

				$am = 0 - round( ( ( $total / 100 ) * $percent ), 2 );
				$this->addCost( $am, $info );
			}
		}
	}

	function computeTotal()
	{
		// Unset old total, if present
		$k = array_pop( array_keys( $this->cost ) );

		if ( $this->cost[$k]->type == 'total' ) {
			unset( $this->cost[$k] );
		}

		// Compute value of total cost
		$total = 0;
		foreach ( $this->cost as $citem ) {
			$total += $citem->renderCost();
		}

		// Set total cost object
		$cost = new itemCost();
		$cost->set( 'type', 'total' );
		$cost->set( 'cost', array( 'amount' => $total ) );

		if ( $cost->isFree() ) {
			$this->free = true;
		}

		$this->cost[] = $cost;

		return true;
	}

	function renderTotal()
	{
		$k = array_pop( array_keys( $this->cost ) );

		return $this->cost[$k]->renderCost();
	}

	function getBaseCostObject( $filter=false, $filter_temp_coupons=false )
	{
		if ( $filter === false ) {
			$filter = array( 'tax', 'total' );
		}

		$return = null;
		foreach ( $this->cost as $id => $cost ) {
			if ( in_array( $cost->type, $filter ) || ( isset( $cost->cost['temp_coupon'] ) && $filter_temp_coupons ) ) {
				if ( isset( $return->cost['amount'] ) ) {
					$return->cost['amount'] = $return->cost['amount'];

					return $return;
				} else {
					return $cost;
				}
			}

			if ( empty( $return ) ) {
				$return = clone( $cost );
			} else {
				$return->cost['amount'] = $return->cost['amount'] + $cost->cost['amount'];
			}
		}

		return $return;
	}

}

class itemCost extends eucaObject
{
	var $type			= null;
	var $cost			= array();

	function renderCost()
	{
		return $this->cost['amount'];
	}

	function isFree()
	{
		if ( $this->renderCost() <= 0 ) {
			return true;
		} else {
			return false;
		}
	}
}

?>

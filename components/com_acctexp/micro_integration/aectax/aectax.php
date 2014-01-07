<?php
/**
 * @version $Id: mi_aectax.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Overall Tax Management MI
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_aectax
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_AECTAX');
		$info['desc'] = JText::_('AEC_MI_DESC_AECTAX');
		$info['type'] = array( 'aec.invoice', 'vendor.valanx' );

		return $info;
	}

	function Settings()
	{
		if ( isset( $this->settings['locations'] ) ) {
			$this->upgradeSettings();
		}

		$settings = array();
		$settings['custominfo']			= array( 'inputD' );
		$settings['vat_no_request']		= array( 'toggle' );
		$settings['vat_countrylist']	= array( 'toggle' );
		$settings['vat_merchcountry']	= array( 'list' );
		$settings['vat_localtax']		= array( 'toggle' );
		$settings['vat_removeonvalid']	= array( 'toggle' );
		$settings['vat_percentage']		= array( 'inputC' );
		$settings['vat_mode']			= array( 'list' );
		$settings['vat_validation']		= array( 'list' );
		$settings['locations_amount']	= array( 'inputB' );

		$locations = $this->getLocationList();

		$loc = array();
		if ( empty( $locations ) ) {
			$loc[] = JHTML::_('select.option', 0, "No locations added yet" );
		} else {
			$loc = array();
			$loc[] = JHTML::_('select.option', 0, "- - - - - - - -" );

			$llist = array();
			foreach ( $locations as $id => $choice ) {
				$llist[$choice['text']] = $id;
			}

			asort( $llist );

			foreach ( $llist as $id ) {
				$loc[] = JHTML::_('select.option', $locations[$id]['id'], $locations[$id]['text'] );
			}
		}

		$mc = 0;
		if ( !empty( $this->settings['vat_merchcountry'] ) ) {
			$mc = $this->settings['vat_merchcountry'];
		}

		$settings['lists']['vat_merchcountry']	= JHTML::_('select.genericlist', $loc, 'vat_merchcountry', 'size="1"', 'value', 'text', $mc );

		$vatval = array();
		$vatval[] = JHTML::_('select.option', '0', JText::_('MI_MI_AECTAX_SET_VATVAL_NONE') );
		$vatval[] = JHTML::_('select.option', '1', JText::_('MI_MI_AECTAX_SET_VATVAL_BASIC') );
		$vatval[] = JHTML::_('select.option', '2', JText::_('MI_MI_AECTAX_SET_VATVAL_EXTENDED') );

		if ( isset( $this->settings['vat_validation'] ) ) {
			$vval = $this->settings['vat_validation'];
		} else {
			$vval = '2';
		}

		$settings['lists']['vat_validation'] = JHTML::_('select.genericlist', $vatval, 'vat_validation', 'size="1"', 'value', 'text', $vval );

		$modes = array();
		$modes[] = JHTML::_('select.option', 'pseudo_subtract', JText::_('MI_MI_AECTAX_SET_MODE_PSEUDO_SUBTRACT') );
		$modes[] = JHTML::_('select.option', 'add', JText::_('MI_MI_AECTAX_SET_MODE_ADD') );
		$modes[] = JHTML::_('select.option', 'subtract', JText::_('MI_MI_AECTAX_SET_MODE_SUBTRACT') );

		if ( !empty( $this->settings['locations_amount'] ) ) {
			for ( $i=0; $i<$this->settings['locations_amount']; $i++ ) {
				$p = $i . '_';

				$settings[$p.'id']			= array( 'inputC', sprintf( JText::_('MI_MI_AECTAX_SET_ID_NAME'), $i+1 ), JText::_('MI_MI_AECTAX_SET_ID_DESC') );
				$settings[$p.'text']		= array( 'inputC', sprintf( JText::_('MI_MI_AECTAX_SET_TEXT_NAME'), $i+1 ), JText::_('MI_MI_AECTAX_SET_TEXT_DESC') );
				$settings[$p.'percentage']	= array( 'inputC', sprintf( JText::_('MI_MI_AECTAX_SET_PERCENTAGE_NAME'), $i+1 ), JText::_('MI_MI_AECTAX_SET_PERCENTAGE_DESC') );
				$settings[$p.'mode']		= array( 'list', sprintf( JText::_('MI_MI_AECTAX_SET_MODE_NAME'), $i+1 ), JText::_('MI_MI_AECTAX_SET_MODE_DESC') );
				$settings[$p.'extra']		= array( 'inputC', sprintf( JText::_('MI_MI_AECTAX_SET_EXTRA_NAME'), $i+1 ), JText::_('MI_MI_AECTAX_SET_EXTRA_DESC') );
				$settings[$p.'mi']			= array( 'inputC', sprintf( JText::_('MI_MI_AECTAX_SET_MI_NAME'), $i+1 ), JText::_('MI_MI_AECTAX_SET_MI_DESC') );

				if ( isset( $this->settings[$p.'mode'] ) ) {
					$val = $this->settings[$p.'mode'];
				} else {
					$val = 'pseudo_subtract';
				}

				$settings['lists'][$p.'mode']	= JHTML::_('select.genericlist', $modes, $p.'mode', 'size="1"', 'value', 'text', $val );
			}
		}

		if ( !isset( $this->settings['vat_mode'] ) ) {
			$this->settings['vat_mode'] = 'pseudo_subtract';
		}

		$settings['lists']['vat_mode']			= JHTML::_('select.genericlist', $modes, 'vat_mode', 'size="1"', 'value', 'text', $this->settings['vat_mode'] );

		return $settings;
	}

	function getMIform( $request )
	{
		$settings = array();

		$locations = $this->getLocationList();

		if ( !empty( $locations ) ) {
			if ( ( count( $locations ) > 1 ) || !empty( $this->settings['vat_no_request'] ) ) {
				if ( !empty( $this->settings['custominfo'] ) ) {
					$settings['exp'] = array( 'p', "", $this->settings['custominfo'] );
				} else {
					$settings['exp'] = array( 'p', "", JText::_('MI_MI_AECTAX_DEFAULT_NOTICE') );
				}
			}

			if ( count( $locations ) == 1 ) {
				// Only one location, nothing to do here
			} else {
				if ( count( $locations ) < 5 ) {
					$settings['location'] = array( 'hidden', null, 'mi_'.$this->id.'_location' );

					foreach ( $locations as $id => $choice ) {
						$settings['ef'.$id] = array( 'radio', 'mi_'.$this->id.'_location', $choice['id'], true, $choice['text'] );
					}
				} else {
					$settings['location'] = array( 'list', "", "" );

					$loc = array();
					$loc[] = JHTML::_('select.option', 0, "- - - - - - - -" );

					$llist = array();
					foreach ( $locations as $id => $choice ) {
						$llist[$choice['text']] = $id;
					}

					asort( $llist );

					foreach ( $llist as $id ) {
						$loc[] = JHTML::_('select.option', $locations[$id]['id'], $locations[$id]['text'] );
					}

					$settings['lists']['location']	= JHTML::_('select.genericlist', $loc, 'location', 'size="1"', 'value', 'text', 0 );
				}

				$settings['validation']['rules'] = array();
				$settings['validation']['rules']['location'] = array( 'required' => true );
			}

		} else {
			return false;
		}

		if ( !empty( $this->settings['vat_no_request'] ) ) {
			$vat_no = '';
			if ( $request->metaUser->userid ) {
				$uparams = $request->metaUser->meta->getCustomParams();
				
				if ( isset( $uparams['vat_no'] ) ) {
					$vat_no = $uparams['vat_no'];
				}
			}

			$settings['vat_desc'] = array( 'p', "", JText::_('MI_MI_AECTAX_VAT_DESC_NAME') );
			$settings['vat_number'] = array( 'inputC', JText::_('MI_MI_AECTAX_VAT_NUMBER_NAME'), JText::_('MI_MI_AECTAX_VAT_NUMBER_DESC'), $vat_no );
		}

		return $settings;
	}

	function verifyMIform( $request )
	{
		$return = array();

		$locations = $this->getLocationList();

		if ( count( $locations ) > 1 ) {
			if ( empty( $request->params['location'] ) || ( $request->params['location'] == "" ) ) {
				$return['error'] = "Please make a selection";
				return $return;
			}
		}

		if ( !empty( $this->settings['vat_no_request'] ) ) {
			if ( !empty( $request->params['vat_number'] ) && ( $request->params['vat_number'] !== "" ) ) {
				$vatlist = $this->vatList();

				$vat_number = $this->clearVatNumber( $request->params['vat_number'] );

				$check = $this->checkVatNumber( $vat_number, $request->params['location'], $vatlist );

				if ( !$check ) {
					$return['error'] = "Invalid VAT Number";
					return $return;
				}
			}
		}

		if ( !isset( $return['error'] ) && !empty( $vat_number ) && $request->metaUser->userid ) {
			$request->metaUser->meta->setCustomParams( array( 'vat_no' => $vat_number ) );
			$request->metaUser->meta->storeload();
		}

		return $return;
	}

	function invoice_item_cost( $request )
	{
		$location = $this->getLocation( $request );

		if ( !empty( $location ) ) {
			$request = $this->prepareTax( $request, $request->add, $location );
		}

		return true;
	}

	function invoice_item( $request )
	{
		$location = $this->getLocation( $request );

		if ( !empty( $location ) ) {
			$request = $this->addTax( $request, $request->add, $location );
		}

		return true;
	}

	function invoice_items_total( $request )
	{
		if ( isset( $request->add->tax ) ) {
			return true;
		} else {
			$request->add->tax = array();
		}

		$location = $this->getLocation( $request );

		$taxtypes		= array();
		$taxcollections	= array();

		// Collect all the taxes from the individual item costs
		foreach ( $request->add->itemlist as $item ) {
			foreach ( $item['terms']->nextterm->cost as $cost ) {
				if ( $cost->type == 'tax' ) {
					if ( in_array( $cost->cost['details'], $taxtypes ) ) {
						$typeid = array_search( $cost->cost['details'], $taxtypes );
					} else {
						$taxtypes[] = $cost->cost['details'];

						$typeid = count( $taxtypes ) - 1;
					}

					if ( !isset( $taxcollections[$typeid] ) ) {
						$taxcollections[$typeid] = 0;
					}
					$taxcollections[$typeid] += ( $cost->renderCost() * $item['quantity'] );
				}
			}
		}

		if ( count( $taxcollections ) == 0 ) {
			return null;
		}

		$taxamount = 0;

		// Add tax items to total
		foreach ( $taxcollections as $tid => $amount ) {
			// Create tax
			$term = new mammonTerm();

			if ( !empty( $taxtypes[$tid] ) ) {
				$term->addCost( $amount, array( 'details' => $taxtypes[$tid], 'tax' => true ) );
			} else {
				$term->addCost( $amount, array( 'tax' => true ) );
			}

			$terms = new mammonTerms();
			$terms->addTerm( $term );

			// Add the "Tax" row
			$request->add->tax[] = array( 'cost' => $amount, 'terms' => $terms );

			$taxamount += $amount;
		}

		$grand_total = $request->add->total->cost['amount'];

		if ( !empty( $request->add->discount ) ) {
			foreach ( $request->add->discount as $citems ) {
				foreach ( $citems as $ccitem ) {
					$citem = $ccitem->renderCost();

					foreach ( $citem as $cost ) {
						if ( $cost->type == 'discount' ) {
							$grand_total += $cost->cost['amount'];
						}
					}
				}
			}
		}

		$grand_total = AECToolbox::correctAmount( $grand_total + $taxamount );

		// Modify grand total according to tax
		$request->add->grand_total->set( 'cost', array( 'amount' => $grand_total ) );
		
		// Formatting for total
		$request->add->total->cost['amount'] = AECToolbox::correctAmount( $request->add->total->cost['amount'] );

		return true;
	}

	function action( $request )
	{
		$location = $this->getLocation( $request );

		if ( empty( $location['mi'] ) ) {
			return true;
		}

		$db = &JFactory::getDBO();

		$mi = new microIntegration();

		if ( !$mi->mi_exists( $location['mi'] ) ) {
			return true;
		}

		$mi->load( $location['mi'] );

		if ( !$mi->callIntegration() ) {
			continue;
		}

		$action = 'action';

		$exchange = null;

		if ( $mi->relayAction( $request->metaUser, $exchange, $request->invoice, null, $action, $request->add ) === false ) {
			if ( $aecConfig->cfg['breakon_mi_error'] ) {
				return false;
			}
		}
	}

	function prepareTax( $request, $item, $location )
	{
		foreach ( $item['terms']->terms as $tid => $term ) {
			switch ( $location['mode'] ) {
				case 'reverse_pseudo_subtract':
					// Get root cost without coupons
					$cost = $term->getBaseCostObject( false, true );

					$itemtotal = ( $cost->cost['amount'] / ( 100 + $location['percentage'] ) ) * 100;

					$item['terms']->terms[$tid]->modifyCost( 0, $itemtotal );
					break;
				case 'pseudo_subtract':
					// Get root cost without coupons
					$itemcost = $term->getBaseCostObject( array( 'tax', 'discount', 'total' ), true );

					// Compute cost as it would have been with pure tax subtracted
					$originalcost = ( $itemcost->cost['amount'] / ( 100 + $location['percentage'] ) ) * 100;

					// Set new root cost
					$item['terms']->terms[$tid]->modifyCost( 0, $originalcost );
					break;
			}
		}

		$item['cost'] = $item['terms']->nextterm->renderTotal();

		if ( is_object( $request->add ) ) {
			$request->add->itemlist[] = $item;
		} else {
			$request->add = $item;
		}

		return $request;
	}

	function addTax( $request, $item, $location )
	{
		foreach ( $item['terms']->terms as $tid => $term ) {
			switch ( $location['mode'] ) {
				default:
					$tax = "0.00";
					break;
				case 'xpseudo_subtract':
					// Get root cost without coupons
					$itemcost = $term->getBaseCostObject( array( 'tax', 'discount', 'total' ), true );

					// Compute cost as it would have been with pure tax subtracted
					$originalcost = ( $itemcost->cost['amount'] / ( 100 + $location['percentage'] ) ) * 100;

					// Set new root cost
					$item['terms']->terms[$tid]->modifyCost( 0, $originalcost );

					// Get cost of the (discounted?) item
					$fullcost = $term->getBaseCostObject( array( 'tax', 'total' ), true );

					if ( $fullcost->cost['amount'] == $originalcost ) {
						// No discounts, just make it fit
						$tax = $itemcost->cost['amount'] - $originalcost;
					} else {
						// Discounts, re-compute
						$tax = $fullcost->cost['amount'] * ( $location['percentage'] / 100 );
					}
					break;
				case 'subtract':
					$total = $term->renderTotal();

					$tax = $total * ( $location['percentage'] / 100 );

					$tax = -$tax;
					break;
				case 'pseudo_subtract':
				case 'add':
					$total = $term->renderTotal();

					$tax = $total * ( $location['percentage'] / 100 );
					break;
			}

			$item['terms']->terms[$tid]->addCost( $tax, array( 'details' => $location['extra'], 'tax' => true ) );
		}

		$item['cost'] = $item['terms']->nextterm->renderTotal();

		if ( is_object( $request->add ) ) {
			$request->add->itemlist[] = $item;
		} else {
			$request->add = $item;
		}

		return $request;
	}

	function getLocation( $request )
	{
		$locations = $this->getLocationList();

		$lid = null;

		if ( count( $locations ) == 1 ) {
			$lid = 0;
		} else {
			foreach ( $locations as $lix => $location ) {
				if ( $location['id'] == $request->params['location'] ) {
					$lid = $lix;
				}
			}
		}

		if ( is_null( $lid ) ) {
			return $lid;
		}

		$location = $locations[$lid];

		if ( !empty( $this->settings['vat_no_request'] ) ) {
			if ( !empty( $request->params['vat_number'] ) && ( $request->params['vat_number'] !== "" ) ) {
				$vatlist = $this->vatList();

				$vat_number = $this->clearVatNumber( $request->params['vat_number'] );

				$check = $this->checkVatNumber( $vat_number, $location['id'], $vatlist );

				if ( $check && $this->settings['vat_removeonvalid'] ) {
					$b2b2c = true;
					if ( !empty( $this->settings['vat_merchcountry'] ) ) {
						if ( $this->settings['vat_merchcountry'] == $location['id'] ) {
							$b2b2c = false;
						}
					}

					if ( $b2b2c ) {
						if ( $location['mode'] == 'pseudo_subtract' ) {
							// If this is a b2b transaction, remove VAT altogether
							// But only if it is cross-country, otherwise - add VAT as per usual
							$location['mode'] = 'reverse_pseudo_subtract';
						} elseif ( $location['mode'] == 'add' ) {
							$location['mode'] = '';
						} elseif ( $location['mode'] == 'subtract' ) {
							$location['mode'] = '';
						}
					}
				}
			}
		}

		return $location;
	}

	function getLocationList()
	{
		if ( isset( $this->settings['locations'] ) ) {
			$this->upgradeSettings();
		}

		$locations = array();

		if ( !empty( $this->settings['locations_amount'] ) ) {
			for ( $i=0; $this->settings['locations_amount']>$i; $i++ ) {
				$locations[] = array(	'id'			=> $this->settings[$i.'_id'],
										'text'			=> $this->settings[$i.'_text'],
										'percentage'	=> $this->settings[$i.'_percentage'],
										'mode'			=> $this->settings[$i.'_mode'],
										'extra'			=> $this->settings[$i.'_extra'],
										'mi'			=> $this->settings[$i.'_mi']
									);
			}
		}

		if ( !empty( $this->settings['vat_countrylist'] ) ) {
			$list = $this->vatList();

			$conversion = AECToolbox::ISO3166_conversiontable( 'a3', 'a2' );

			foreach ( $list as $ccode => $litem ) {
				$text = JText::_( 'COUNTRYCODE_' . $conversion[$ccode] );

				if ( $this->settings['vat_localtax'] ) {
					$tax = $litem['tax'];
				} else {
					$tax = $this->settings['vat_percentage'];
				}

				$locations[] = array(	'id'			=> $ccode,
										'text'			=> $text,
										'percentage'	=> $tax,
										'mode'			=> $this->settings['vat_mode'],
										'extra'			=> $tax . '%',
										'mi'			=> null
									);
			}
		}

		return $locations;
	}

	function clearVatNumber( $vat_number )
	{
		// Remove whitespace
		$vat_number = preg_replace('/\s\s+/', '', $vat_number);

		// Only allow alphanumeric characters
		$vat_number = preg_replace( "/[^a-z \d]/i", '', $vat_number );

		return $vat_number;
	}

	function checkVatNumber( $number, $country, $vatlist )
	{
		if ( !$this->settings['vat_validation'] ) {
			return true;
		}

		if ( strlen( $country ) == 2 ) {
			$conversion = AECToolbox::ISO3166_conversiontable( 'a2', 'a3' );

			$country = $conversion[$country];
		}

		$check = false;
		if ( array_key_exists( $country, $vatlist ) ) {
			$check = preg_match( $vatlist[$country]["regex"], $number );

			$countrycode = substr( $vatlist[$country]["regex"], 3, 2 );
		} else {
			$match = false;
			foreach ( $vatlist as $ccode => $cc ) {
				if ( !$match ) {
					$match = preg_match( $cc["regex"], $number );

					if ( $match ) {
						$check = true;

						$countrycode = substr( $cc["regex"], 3, 2 );
					}
				}
			}
		}

		if ( ( $this->settings['vat_validation'] == 2 ) && $check ) {
			return $this->viesValidation( substr( $number, 2 ), $countrycode );
		} else {
			return $check;
		}
	}

	function viesValidation( $number, $country )
	{
		$db = &JFactory::getDBO();

		$get = 'vat=' . $number . '&ms=' . $country . '&iso=' . $country . '&lang=EN';

		$path = '/taxation_customs/vies/viesquer.do?'.$get;

		$url = 'http://ec.europa.eu' . $path;

		$tempprocessor = new processor();

		$result = $tempprocessor->transmitRequest( $url, $path );

		if ( strpos( $result, 'Request time-out' ) != 0 ) {
			return null;
		} elseif ( strpos( $result, 'Yes, valid VAT number' ) != 0 ) {
			return true;
		} else {
			return false;
		}
	}

	function vatList()
	{
		return array(	"AUT" => array( "tax" => "20",		"regex" => '/^(AT){0,1}U[0-9]{8}$/i' ),
						"BEL" => array( "tax" => "21",		"regex" => '/^(BE){0,1}[0]{0,1}[0-9]{9}$/i' ),
						"BGR" => array( "tax" => "20",		"regex" => '/^(BG){0,1}[0-9]{9,10}$/i' ),
						"CYP" => array( "tax" => "15",		"regex" => '/^(CY){0,1}[0-9]{8}[A-Z]$/i' ),
						"CZE" => array( "tax" => "20",		"regex" => '/^(CZ){0,1}[0-9]{8,10}$/i' ),
						"DEU" => array( "tax" => "19",		"regex" => '/^(DE){0,1}[0-9]{9}$/i' ),
						"DNK" => array( "tax" => "25",		"regex" => '/^(DK){0,1}[0-9]{8}$/i' ),
						"EST" => array( "tax" => "20",		"regex" => '/^(EE){0,1}[0-9]{9}$/i' ),
						"ESP" => array( "tax" => "18",		"regex" => '/^(ES){0,1}([0-9A-Z][0-9]{7}[A-Z])|([A-Z][0-9]{7}[0-9A-Z])$/i' ),
						"FIN" => array( "tax" => "22",		"regex" => '/^(FI){0,1}[0-9]{8}$/i' ),
						"FRA" => array( "tax" => "19.6",	"regex" => '/^(FR){0,1}[0-9A-Z]{2}[\ ]{0,1}[0-9]{9}$/i' ),
						"GBR" => array( "tax" => "17.5",	"regex" => '/^(GB|UK){0,1}([1-9][0-9]{2}[\ ]{0,1}[0-9]{4}[\ ]{0,1}[0-9]{2})|([1-9][0-9]{2}[\ ]{0,1}[0-9]{4}[\ ]{0,1}[0-9]{2}[\ ]{0,1}[0-9]{3})|((GD|HA)[0-9]{3})$/i' ),
						"GRC" => array( "tax" => "19",		"regex" => '/^(EL|GR){0,1}[0-9]{9}$/i' ),
						"HUN" => array( "tax" => "25",		"regex" => '/^(HU){0,1}[0-9]{8}$/i' ),
						"IRL" => array( "tax" => "21",		"regex" => '/^(IE){0,1}[0-9][0-9A-Z\+\*][0-9]{5}[A-Z]$/i' ),
						"ITA" => array( "tax" => "20",		"regex" => '/^(IT){0,1}[0-9]{11}$/i' ),
						"LTU" => array( "tax" => "21",		"regex" => '/^(LT){0,1}([0-9]{9}|[0-9]{12})$/i' ),
						"LUX" => array( "tax" => "15",		"regex" => '/^(LU){0,1}[0-9]{8}$/i' ),
						"LVA" => array( "tax" => "21",		"regex" => '/^(LV){0,1}[0-9]{11}$/i' ),
						"MLT" => array( "tax" => "18",		"regex" => '/^(MT){0,1}[0-9]{8}$/i' ),
						"NLD" => array( "tax" => "19",		"regex" => '/^(NL){0,1}[0-9]{9}B[0-9]{2}$/i' ),
						"POL" => array( "tax" => "22",		"regex" => '/^(PL){0,1}[0-9]{10}$/i' ),
						"PRT" => array( "tax" => "20",		"regex" => '/^(PT){0,1}[0-9]{9}$/i' ),
						"ROU" => array( "tax" => "19",		"regex" => '/^(RO){0,1}[0-9]{2,10}$/i' ),
						"SWE" => array( "tax" => "25",		"regex" => '/^(SE){0,1}[0-9]{12}$/i' ),
						"SVN" => array( "tax" => "20",		"regex" => '/^(SI){0,1}[0-9]{8}$/i' ),
						"SVK" => array( "tax" => "19",		"regex" => '/^(SK){0,1}[0-9]{10}$/i' )
					);
	}

	function upgradeSettings()
	{
		$llist = $this->oldLocationList();

		$this->settings['locations_amount'] = count( $llist );

		$i = 0;
		foreach ( $llist as $location ) {
			$p = $i . '_';

			foreach ( $location as $key => $value ) {
				$this->settings[$p.$key] = $value;
			}

			$i++;
		}

		unset( $this->settings['locations'] );

		foreach ( $this->settings as $k => $v ) {
			$this->_parent->params[$k] = $v;
		}

		return $this->_parent->storeload();
	}

	function oldLocationList()
	{
		$locations = array();

		$l = explode( "\n", $this->settings['locations'] );

		if ( !empty( $l ) ) {
			foreach ( $l as $loc ) {
				$location = explode( "|", $loc );

				if ( empty( $location[3] ) ) {
					$location[3] = null;
				}

				if ( empty( $location[4] ) ) {
					$location[4] = null;
				}

				$locations[] = array( 'id' => $location[0], 'text' => $location[1], 'percentage' => $location[2], 'extra' => $location[3], 'mi' => $location[4] );
			}
		}

		return $locations;
	}
}
?>

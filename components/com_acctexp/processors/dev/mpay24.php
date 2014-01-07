<?php
/**
 * @version $Id: mpay24.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Mpay24
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class processor_mpay24 extends XMLprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'mpay24';
		$info['longname']		= JText::_('CFG_MPAY24_LONGNAME');
		$info['statement']		= JText::_('CFG_MPAY24_STATEMENT');
		$info['description']	= JText::_('CFG_MPAY24_DESCRIPTION');
		$info['currencies']		= AECToolbox::aecCurrencyField( true, true, true, true );
		$info['cc_list']		= "visa,mastercard,discover,americanexpress,echeck,jcb,dinersclub";
		$info['recurring']		= 2;
		$info['actions']		= array( 'cancel' => array( 'confirm' ) );
		$info['secure']			= 1;

		return $info;
	}

	function getActions( $invoice, $subscription )
	{
		$actions = parent::getActions( $invoice, $subscription );

		if ( ( $subscription->status == 'Cancelled' ) || ( $invoice->transaction_date == '0000-00-00 00:00:00' ) ) {
			if ( isset( $actions['cancel'] ) ) {
				unset( $actions['cancel'] );
			}
		}

		return $actions;
	}

	function settings()
	{
		$settings = array();
		$settings['login']				= 'login';
		$settings['transaction_key']	= 'transaction_key';
		$settings['testmode']			= 0;
		$settings['currency']			= 'USD';
		$settings['promptAddress']		= 1;
		$settings['minimalAddress']		= 0;
		$settings['extendedAddress']	= 0;
		$settings['dedicatedShipping']	= 0;
		$settings['noechecks']			= 0;
		$settings['totalOccurrences']	= 12;
		$settings['item_name']			= sprintf( JText::_('CFG_PROCESSOR_ITEM_NAME_DEFAULT'), '[[cms_live_site]]', '[[user_name]]', '[[user_username]]' );
		$settings['customparams']		= '';

		return $settings;
	}

	function backend_settings()
	{
		$settings = array();
		$settings['testmode']			= array( 'toggle' );
		$settings['login'] 				= array( 'inputC' );
		$settings['transaction_key']	= array( 'inputC' );
		$settings['currency']			= array( 'list_currency' );
		$settings['promptAddress']		= array( 'toggle' );
		$settings['minimalAddress']		= array( 'toggle' );
		$settings['extendedAddress']	= array( 'toggle' );
		$settings['dedicatedShipping']	= array( 'toggle' );
		$settings['noechecks']			= array( 'toggle' );
		$settings['totalOccurrences']	= array( 'inputA' );
		$settings['item_name']			= array( 'inputE' );
		$settings['customparams']		= array( 'inputD' );

		$settings = AECToolbox::rewriteEngineInfo( null, $settings );

		return $settings;
	}

	function checkoutform( $request, $cim=null, $nobill=false, $ppParams=false )
	{
		$var = array();
		$hascim = false;
		$vcontent = array();

		if ( $ppParams === false ) {
			$ppParams = $request->metaUser->meta->getProcessorParams( $request->parent->id );
		}

		if ( empty( $cim ) ) {
			if ( $nobill ) {
				$cim = $this->loadCIMship( $ppParams );
			} else {
				$cim = $this->loadCIM( $ppParams );
			}

			if ( $cim->isSuccessful() ) {
				$hascim = true;
			}
		} else {
			$hascim = true;
		}

		if ( !$nobill ) {
			if ( $hascim ) {
				$var['params']['billUpdateInfo'] = array( 'p', JText::_('AEC_CCFORM_UPDATE_NAME'), JText::_('AEC_CCFORM_UPDATE_DESC'), '' );

				$vcontent['card_number'] = $cim->substring_between( $cim->response,'<cardNumber>','</cardNumber>' );
				$vcontent['account_no'] = $cim->substring_between( $cim->response,'<accountNumber>','</accountNumber>' );
				$vcontent['routing_no'] = $cim->substring_between( $cim->response,'<routingNumber>','</routingNumber>' );
			} else {
				$vcontent = '';
			}

			if ( $this->settings['noechecks'] ) {
				$var = $this->getCCform( $var, array( 'card_number', 'card_exp_month', 'card_exp_year', 'card_cvv2' ), $vcontent );
			} else {
				$var['params'][] = array( 'tabberstart', '', '', '' );
				$var['params'][] = array( 'tabregisterstart', '', '', '' );
				$var['params'][] = array( 'tabregister', 'ccdetails', 'Credit Card', true );
				$var['params'][] = array( 'tabregister', 'echeckdetails', 'eCheck', false );
				$var['params'][] = array( 'tabregisterend', '', '', '' );

				$var['params'][] = array( 'tabstart', 'ccdetails', true, '' );
				$var = $this->getCCform( $var, array( 'card_number', 'card_exp_month', 'card_exp_year', 'card_cvv2' ), $vcontent );
				$var['params'][] = array( 'tabend', '', '', '' );

				$var['params'][] = array( 'tabstart', 'echeckdetails', true, '' );
				$var = $this->getECHECKform( $var );
				$var['params'][] = array( 'tabend', '', '', '' );

				$var['params'][] = array( 'tabberend', '', '', '' );
			}
		}

		if ( !empty( $this->settings['promptAddress'] ) ) {
			if ( empty( $this->settings['extendedAddress'] ) ) {
				if ( empty( $this->settings['minimalAddress'] ) ) {
					$uservalues = array( 'firstName', 'lastName', 'address', 'city', 'nonus', 'state', 'zip' );
				} else {
					$uservalues = array( 'firstName', 'lastName', 'address', 'city' );
				}
			} else {
				$uservalues = array( 'firstName', 'lastName', 'company', 'address', 'city', 'nonus', 'state', 'zip', 'country', 'phone', 'fax' );
			}

			$content = array();
			if ( $hascim ) {
				foreach ( $uservalues as $uv ) {
					if ( in_array( $uv, array( 'phone', 'fax' ) ) ) {
						$content[$uv] = $cim->substring_between( $cim->response,'<' . $uv . 'Number>','</' . $uv . 'Number>' );
					} else {
						if ( $nobill && ( $uv == 'address' ) ) {
							$content[$uv] = $cim->substring_between( $cim->response,'<' . $uv . '>','</' . $uv . '>', 1 );
						} else {
							$content[$uv] = $cim->substring_between( $cim->response,'<' . $uv . '>','</' . $uv . '>' );
						}
					}
				}
			}

			$var = $this->getUserform( $var, $uservalues, $request->metaUser, $content );
		}

		return $var;
	}

	function checkoutAction( $request, $InvoiceFactory=null )
	{
		global $aecConfig;

		$ppParams = $request->metaUser->meta->getProcessorParams( $request->parent->id );

		// Actual form, with ProfileID reference numbers as options

		$return = '<form action="' . AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=checkout', true ) . '" method="post">' . "\n";

		if ( !empty( $ppParams ) ) {
			$cim = $this->loadCIM( $ppParams );

			$var = array();
			$var = $this->payProfileSelect( $var, $ppParams, false, false );
			$var = $this->shipProfileSelect( $var, $ppParams, false, false, false );

			$return .= $this->getParamsHTML( $var ) . '<br /><br />';
		} else {
			$cim = false;
		}

		$return .= $this->getParamsHTML( $this->checkoutform( $request, $cim ) ) . '<br /><br />';
		$return .= $this->getStdFormVars( $request );
		$return .= '<input type="submit" class="button" value="' . JText::_('BUTTON_CHECKOUT') . '" /><br /><br />' . "\n";
		$return .= '</form>' . "\n";

		return $return;
	}

	function createRequestXML( $request )
	{
		return "";
	}

	function transmitRequestXML( $xml, $request )
	{
		$return['valid'] = false;

		$ppParams = $request->metaUser->meta->getProcessorParams( $request->parent->id );

		if ( !empty( $ppParams ) ) {
			if ( $request->int_var['params']['payprofileselect'] != "new" ) {
				$ppParams->paymentprofileid = $request->int_var['params']['payprofileselect'];
			}

			if ( $request->int_var['params']['shipprofileselect'] != "new" ) {
				$ppParams->shippingprofileid = $request->int_var['params']['shipprofileselect'];
			}

			$cim = $this->loadCIM( $ppParams );
		} else {
			$cim = new AuthNetCim( $this->settings['login'], $this->settings['transaction_key'], $this->settings['testmode'] );
		}

		$basicdata = array(	'refId'					=> $request->invoice->invoice_number,
							'order_invoiceNumber'	=> $request->invoice->invoice_number,
							'order_description'		=> AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request ),
							'merchantCustomerId'	=> $request->metaUser->cmsUser->id,
							'description'			=> $request->metaUser->cmsUser->name,
							'email'					=> $request->metaUser->cmsUser->email,
							'paymentType'			=> 'creditcard',
							'cardNumber'			=> trim( $request->int_var['params']['cardNumber'] ),
							'expirationDate'		=> $request->int_var['params']['expirationYear'] . '-' . $request->int_var['params']['expirationMonth']
							);

		if( !empty( $request->int_var['params']['account_no'] ) ) {
			$basicdata['paymentType']		= 'echeck';
			$basicdata['accountType']		= 'checking';
			$basicdata['routingNumber']		= $request->int_var['params']['routing_no'];
			$basicdata['accountNumber']		= $request->int_var['params']['account_no'];
			$basicdata['nameOnAccount']		= $request->int_var['params']['account_name'];
			$basicdata['echeckType']		= 'CCD';
			$basicdata['bankName']			= $request->int_var['params']['bank_name'];
		} else {
			$basicdata['paymentType']		= 'creditcard';
			$basicdata['cardNumber']		= trim( $request->int_var['params']['cardNumber'] );
			$basicdata['expirationDate']	= $request->int_var['params']['expirationYear'] . '-' . $request->int_var['params']['expirationMonth'];
		}

		foreach ( $basicdata as $key => $value ) {
			$cim->setParameter( $key, $value );
		}

		if ( !$this->settings['dedicatedShipping'] || empty( $ppParams ) ) {
			$udata = array( 'billTo_firstName' => 'billFirstName',
							'billTo_lastName' => 'billLastName',
							'billTo_company' => 'billCompany',
							'billTo_address' => 'billAddress',
							'billTo_city' => 'billCity',
							'billTo_state' => 'billState',
							'billTo_zip' => 'billZip',
							'billTo_country' => 'billCountry',
							'billTo_phoneNumber' => 'billPhone',
							'billTo_faxNumber' => 'billFax',
							'shipTo_firstName' => 'billFirstName',
							'shipTo_lastName' => 'billLastName',
							'shipTo_company' => 'billCompany',
							'shipTo_address' => 'billAddress',
							'shipTo_city' => 'billCity',
							'shipTo_state' => 'billState',
							'shipTo_zip' => 'billZip',
							'shipTo_country' => 'billCountry',
							'shipTo_phoneNumber' => 'billPhone',
							'shipTo_faxNumber' => 'billFax'
							);
		} else {
			$udata = array( 'billTo_firstName' => 'billFirstName',
							'billTo_lastName' => 'billLastName',
							'billTo_company' => 'billCompany',
							'billTo_address' => 'billAddress',
							'billTo_city' => 'billCity',
							'billTo_state' => 'billState',
							'billTo_zip' => 'billZip',
							'billTo_country' => 'billCountry',
							'billTo_phoneNumber' => 'billPhone',
							'billTo_faxNumber' => 'billFax'
							);
		}

		if ( !empty( $request->int_var['params']['billNonUs'] ) ) {
			$unset = array( 'billTo_state', 'billTo_zip' );

			foreach ( $unset as $uk => $uv ) {
				unset( $udata[$uk] );
			}
		}

		foreach ( $udata as $authvar => $aecvar ) {
			if ( !empty( $request->int_var['params'][$aecvar] ) ) {
				$cim->setParameter( $authvar, trim( $request->int_var['params'][$aecvar] ) );
			}
		}

		if ( !empty( $ppParams ) ) {
			if ( strpos( $request->int_var['params']['cardNumber'], 'X' ) === false ) {
				if ( $request->int_var['params']['payprofileselect'] == "new" ) {
					$cim->createCustomerPaymentProfileRequest( $this );

					if ( $cim->isSuccessful() ) {
						$profileid = $cim->substring_between( $cim->response,'<customerPaymentProfileId>','</customerPaymentProfileId>' );
						if ( !empty( $profileid ) ) {
							$ppParams = $this->payProfileAdd( $request, $profileid, $request->int_var['params'], $ppParams );
						}
					}

					$cim->setParameter( 'customerPaymentProfileId', (int) $profileid );
				} else {
					$stored_ppid = $ppParams->paymentProfiles[$request->int_var['params']['payprofileselect']]->profileid;
					$cim->setParameter( 'customerPaymentProfileId', (int) $stored_ppid );
					$cim->updateCustomerPaymentProfileRequest( $this );

					if ( $cim->isSuccessful() ) {
						$this->payProfileUpdate( $request, $request->int_var['params']['payprofileselect'], $request->int_var['params'], $ppParams );
					}
				}

				if ( $this->settings['dedicatedShipping'] ) {
					$stored_spid = $ppParams->shippingProfiles[$request->int_var['params']['shipprofileselect']]->profileid;

					$cim->setParameter( 'customerAddressId', (int) $stored_spid );
					$cim->updateCustomerShippingAddressRequest( $this );

					if ( $cim->isSuccessful() ) {
						$this->shipProfileUpdate( $request, $request->int_var['params']['shipprofileselect'], $request->int_var['params'], $ppParams );
					}
				}
			} else {
				$stored_ppid = $ppParams->paymentProfiles[$request->int_var['params']['payprofileselect']]->profileid;
				$cim->setParameter( 'customerPaymentProfileId', (int) $stored_ppid );

				if ( $this->settings['dedicatedShipping'] ) {
					$stored_spid = $ppParams->shippingProfiles[$request->int_var['params']['shipprofileselect']]->profileid;

					$cim->setParameter( 'customerAddressId', (int) $stored_spid );
				}
			}
		} else {
			$cim->createCustomerProfileRequest( $this );

			if ( $cim->isSuccessful() ) {
				$ppParams = $this->ProfileAdd( $request, $cim->customerProfileId );

				$cim = $this->loadCIM( $ppParams );

				$profileid = $cim->substring_between( $cim->response,'<customerPaymentProfileId>','</customerPaymentProfileId>' );
				if ( !empty( $profileid ) ) {
					$ppParams = $this->payProfileAdd( $request, $profileid, $request->int_var['params'], $ppParams );
				}

				$cim->setParameter( 'customerPaymentProfileId', (int) $profileid );

				$addprofileid = $cim->substring_between( $cim->response,'<customerAddressId>','</customerAddressId>' );
				if ( !empty( $addprofileid ) ) {
					$ppParams = $this->shipProfileAdd( $request, $addprofileid, $request->int_var['params'], $ppParams );
				}

				$cim->setParameter( 'customerAddressId', (int) $addprofileid );
			}
		}

		if ( $cim->isSuccessful() ) {
			if ( is_array( $request->int_var['amount'] ) ) {
				$cim->setParameter( 'transactionRecurringBilling',	'true' );
				if ( isset( $request->int_var['amount']['amount1'] ) ) {
					$amount = $request->int_var['amount']['amount1'];
				} else {
					$amount = $request->int_var['amount']['amount3'];
				}
			} else {
				$amount = $request->int_var['amount'];
			}

			$cim->setParameter( 'transaction_amount',	AECToolbox::correctAmount( $amount ) );

			$cim->setParameter( 'transactionType',		'profileTransAuthCapture' );

			if ( !empty( $request->int_var['params']['cardVV2'] ) ) {
				$cim->setParameter( 'transactionCardCode',	trim( $request->int_var['params']['cardVV2'] ) );
			}

			$cim->createCustomerProfileTransactionRequest( $this );

			$return['raw'] = $cim->response;

			if ( $cim->isSuccessful() ) {
				if ( !empty( $this->settings['totalOccurrences'] ) ) {
					$return['invoiceparams'] = array( 'maxOccurrences' => $this->settings['totalOccurrences'], 'totalOccurrences' => 1 );
				}

				$return['valid']	= true;
			} else {
				$return['error']	= $cim->code . ": " . $cim->text . " (" . $cim->directResponse . ")";
			}
		} else {
			$return['error']		= $cim->code . ": " . $cim->text . " (" . $cim->directResponse . ")";
		}

		return $return;
	}

	function customaction_cancel( $request )
	{
		$return['valid']	= 0;
		$return['cancel']	= true;

		return $return;
	}

}
?>

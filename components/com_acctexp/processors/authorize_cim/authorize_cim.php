<?php
/**
 * @version $Id: authorize_cim.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Processors - Authorize CIM
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

require_once( dirname(__FILE__) . '/lib/authorizenet.cim.class.php' );

class processor_authorize_cim extends PROFILEprocessor
{
	function info()
	{
		$info = array();
		$info['name']			= 'authorize_cim';
		$info['longname']		= JText::_('CFG_AUTHORIZE_CIM_LONGNAME');
		$info['statement']		= JText::_('CFG_AUTHORIZE_CIM_STATEMENT');
		$info['description']	= JText::_('CFG_AUTHORIZE_CIM_DESCRIPTION');
		$info['currencies']		= AECToolbox::aecCurrencyField( true, true, true, true );
		$info['cc_list']		= "visa,mastercard,discover,americanexpress,echeck,jcb,dinersclub";
		$info['recurring']		= 2;
		$info['actions']		= array( 'cancel' => array( 'confirm' ) );
		$info['secure']			= 1;

		return $info;
	}

	function getLogoFilename()
	{
		return 'authorize.png';
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

	function registerProfileTabs()
	{
		$tab			= array();
		$tab['details']	= JText::_('AEC_USERFORM_BILLING_DETAILS_NAME');

		if ( $this->settings['dedicatedShipping'] ) {
			$tab['shipping_details'] = JText::_('AEC_USERFORM_SHIPPING_DETAILS_NAME');
		}

		return $tab;
	}

	function customtab_details( $request )
	{
		$ppParams = $request->metaUser->meta->getProcessorParams( $request->parent->id );

		$post = aecPostParamClear( $_POST );

		if ( !isset( $post['payprofileselect'] ) ) {
			$post['shipprofileselect'] = null;
		}

		if ( !empty( $post['edit_payprofile'] ) && ( $post['payprofileselect'] != "new" ) ) {
			$ppParams->paymentprofileid = $post['payprofileselect'];
		}

		$cim	= null;
		$strong	= false;

		$action	= isset( $post['billFirstName'] );

		if ( !isset( $post['cardNumber'] ) ) {
			$post['cardNumber'] = 'X';
		}

		if ( $action && ( strpos( $post['cardNumber'], 'X' ) === false ) ) {
			$cim = $this->loadCIMpay( $ppParams );

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

			foreach ( $udata as $authvar => $aecvar ) {
				if ( !empty( $post[$aecvar] ) ) {
					$cim->setParameter( $authvar, trim( preg_replace( '`[\<|\>|\&]`Di', '', $post[$aecvar] ) ) );
				}
			}

			if ( !empty( $post['cardNumber'] ) || !empty( $post['account_no'] ) ) {
				if( !empty( $post['account_no'] ) ) {
					$basicdata['paymentType']		= 'echeck';
					$basicdata['accountType']		= 'checking';
					$basicdata['routingNumber']		= $post['routing_no'];
					$basicdata['accountNumber']		= $post['account_no'];
					$basicdata['nameOnAccount']		= $post['account_name'];
					$basicdata['echeckType']		= 'CCD';
					$basicdata['bankName']			= $post['bank_name'];
				} else {
					$basicdata['paymentType']		= 'creditcard';
					$basicdata['cardNumber']		= trim( $post['cardNumber'] );
					$basicdata['expirationDate']	= $post['expirationYear'] . '-' . $post['expirationMonth'];
				}

				foreach ( $basicdata as $key => $value ) {
					$cim->setParameter( $key, $value );
				}

				if ( $post['payprofileselect'] == "new" ) {
					$cim->createCustomerPaymentProfileRequest( $this );

					if ( $cim->isSuccessful() ) {
						$profileid = $cim->substring_between( $cim->response,'<customerPaymentProfileId>','</customerPaymentProfileId>' );
						if ( !empty( $profileid ) ) {
							$ppParams = $this->payProfileAdd( $request, $profileid, $post, $ppParams );
						}
					}
				} else {
					if ( isset( $ppParams->paymentProfiles[$ppParams->paymentprofileid] ) ) {
						$stored_spid = $ppParams->paymentProfiles[$ppParams->paymentprofileid]->profileid;
						$cim->setParameter( 'customerPaymentProfileId', $stored_spid );
						$cim->updateCustomerPaymentProfileRequest( $this );

						if ( $cim->isSuccessful() ) {
							$this->payProfileUpdate( $request, $ppParams->paymentprofileid, $post, $ppParams );
						}
					}
				}

				$cim->updateCustomerPaymentProfileRequest( $this );

				$cim->setParameter( 'customerProfileId',		$cim->customerProfileId );
				$cim->setParameter( 'customerPaymentProfileId',	$cim->customerPaymentProfileId );
			}

			if ( !$this->settings['dedicatedShipping'] ) {
				$udata = array( 'shipTo_firstName' => 'billFirstName',
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

				foreach ( $udata as $authvar => $aecvar ) {
					if ( !empty( $post[$aecvar] ) ) {
						$cim->setParameter( $authvar, trim( preg_replace( '`[\<|\>|\&]`Di', '', $post[$aecvar] ) ) );
					}
				}

				if ( isset( $ppParams->shippingProfiles[$ppParams->shippingprofileid] ) ) {
					$stored_spid = $ppParams->shippingProfiles[$ppParams->shippingprofileid]->profileid;

					$cim->setParameter( 'customerAddressId', $stored_spid );
					$cim->updateCustomerShippingAddressRequest( $this );

					if ( $cim->isSuccessful() ) {
						$this->shipProfileUpdate( $request, $post['shipprofileselect'], $post, $ppParams );
					}
				}
			}

			$cim = null;
			$cim = $this->loadCIMpay( $ppParams );
		} elseif ( $action && ( strpos( $post['cardNumber'], 'X' ) !== false ) ) {
			$strong = true;
		}

		$var = $this->payProfileSelect( array(), $ppParams, true, $ppParams );
		$var2 = $this->checkoutform( $request, $cim );

		$return = '<form action="' . AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=subscriptiondetails', true ) . '" method="post">' . "\n";
		$return .= $this->getParamsHTML( $var ) . '<br /><br />';
		$return .= $this->getParamsHTML( $var2 ) . '<br /><br />';
		$return .= '<input type="hidden" name="userid" value="' . $request->metaUser->userid . '" />' . "\n";
		$return .= '<input type="hidden" name="task" value="subscriptiondetails" />' . "\n";
		$return .= '<input type="hidden" name="sub" value="authorize_cim_details" />' . "\n";
		$return .= '<input type="submit" class="button" value="' . JText::_('BUTTON_APPLY') . '" /><br /><br />' . "\n";
		$return .= '</form>' . "\n";

		return $return;
	}

	function customtab_shipping_details( $request )
	{
		$ppParams = $request->metaUser->meta->getProcessorParams( $request->parent->id );

		$post = aecPostParamClear( $_POST );

		if ( !isset( $post['shipprofileselect'] ) ) {
			$post['shipprofileselect'] = null;
		}

		if ( !empty( $post['edit_shipprofile'] ) && ( $post['shipprofileselect'] != "new" ) ) {
			$ppParams->shippingprofileid = $post['shipprofileselect'];
		}

		if ( isset( $post['billFirstName'] ) && empty( $post['edit_shipprofile'] ) ) {
			$cim = $this->loadCIMship( $ppParams );

			$udata = array( 'shipTo_firstName' => 'billFirstName',
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

			foreach ( $udata as $authvar => $aecvar ) {
				if ( !empty( $post[$aecvar] ) ) {
					$cim->setParameter( $authvar, trim( preg_replace( '`[\<|\>|\&]`Di', '', $post[$aecvar] ) ) );
				}
			}

			if ( $post['shipprofileselect'] == "new" ) {
				$cim->createCustomerShippingAddressRequest( $this );

				if ( $cim->isSuccessful() ) {
					$profileid = $cim->substring_between( $cim->response,'<customerAddressId>','</customerAddressId>' );
					if ( !empty( $profileid ) ) {
						$ppParams = $this->shipProfileAdd( $request, $profileid, $post, $ppParams );
					}
				}
			} else {
				if ( isset( $ppParams->shippingProfiles[$request->int_var['params']['shipprofileselect']] ) ) {
					$stored_spid = $ppParams->shippingProfiles[$request->int_var['params']['shipprofileselect']]->profileid;
					$cim->setParameter( 'customerAddressId', $stored_spid );
					$cim->updateCustomerShippingAddressRequest( $this );

					if ( $cim->isSuccessful() ) {
						$this->shipProfileUpdate( $request, $post['shipprofileselect'], $post, $ppParams );
					}
				}
			}

			$cim = null;
			$cim = $this->loadCIMship( $ppParams );
		} else {
			$cim = null;
		}

		$var = $this->shipProfileSelect( array(), $ppParams, true );
		$var2 = $this->checkoutform( $request, $cim, true, $ppParams );

		$return = '<form action="' . AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=authorize_cim_shipping_details', true ) . '" method="post">' . "\n";
		$return .= $this->getParamsHTML( $var ) . '<br /><br />';
		$return .= $this->getParamsHTML( $var2 ) . '<br /><br />';
		$return .= '<input type="hidden" name="userid" value="' . $request->metaUser->userid . '" />' . "\n";
		$return .= '<input type="hidden" name="task" value="subscriptiondetails" />' . "\n";
		$return .= '<input type="hidden" name="sub" value="authorize_cim_shipping_details" />' . "\n";
		$return .= '<input type="submit" class="button" value="' . JText::_('BUTTON_APPLY') . '" /><br /><br />' . "\n";
		$return .= '</form>' . "\n";

		return $return;
	}

	function checkoutform( $request, $cim=null, $nobill=false, $ppParams=false, $updated=false )
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
				$cim = $this->loadCIMpay( $ppParams );
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

			if ( !empty( $vcontent ) ) {
				if ( !empty( $updated ) ) {
					$msg = JText::_('AEC_CCFORM_UPDATE2_DESC');
				} else {
					$msg = JText::_('AEC_CCFORM_UPDATE_DESC');
				}

				$var['params']['billUpdateInfo'] = array( 'p', JText::_('AEC_CCFORM_UPDATE_NAME'), $msg, '' );
			}

			if ( $this->settings['noechecks'] ) {
				$var['params'][] = array( 'tabberstart', '', '', '' );
				$var['params'][] = array( 'tabregisterstart', '', '', '' );
				$var['params'][] = array( 'tabregister', 'ccdetails', 'Credit Card', true );
				$var['params'][] = array( 'tabregisterend', '', '', '' );

				$var['params'][] = array( 'tabstart', 'ccdetails', true );
				$var = $this->getCCform( $var, array( 'card_number', 'card_exp_month', 'card_exp_year', 'card_cvv2' ), $vcontent );
				$var['params'][] = array( 'tabend', '', '', '' );

				$var['params'][] = array( 'tabberend', '', '', '' );
			} else {
				$var['params'][] = array( 'tabberstart', '', '', '' );
				$var['params'][] = array( 'tabregisterstart', '', '', '' );
				$var['params'][] = array( 'tabregister', 'ccdetails', 'Credit Card', true );
				$var['params'][] = array( 'tabregister', 'echeckdetails', 'eCheck', false );
				$var['params'][] = array( 'tabregisterend', '', '', '' );

				$var['params'][] = array( 'tabstart', 'ccdetails', true );
				$var = $this->getCCform( $var, array( 'card_number', 'card_exp_month', 'card_exp_year', 'card_cvv2' ), $vcontent );
				$var['params'][] = array( 'tabend', '', '', '' );

				$var['params'][] = array( 'tabstart', 'echeckdetails', false );
				$var = $this->getECHECKform( $var );
				$var['params'][] = array( 'tabend', '', '', '' );

				$var['params'][] = array( 'tabberend', '', '', '' );
			}
		}

		if ( !empty( $this->settings['promptAddress'] ) ) {
			if ( empty( $this->settings['extendedAddress'] ) ) {
				if ( empty( $this->settings['minimalAddress'] ) ) {
					$uservalues = array( 'firstName', 'lastName', 'address', 'city', 'nonus', 'state_usca', 'zip' );
				} else {
					$uservalues = array( 'firstName', 'lastName', 'address', 'city' );
				}
			} else {
				$uservalues = array( 'firstName', 'lastName', 'company', 'address', 'city', 'nonus', 'state_usca', 'zip', 'country', 'phone', 'fax' );
			}

			$content = array();
			if ( $hascim ) {
				foreach ( $uservalues as $uv ) {
					if ( in_array( $uv, array( 'phone', 'fax' ) ) ) {
						$content[$uv] = $cim->substring_between( $cim->response,'<' . $uv . 'Number>','</' . $uv . 'Number>' );
					} else {
						if ( $nobill && ( $uv == 'address' ) ) {
							$content[$uv] = $cim->substring_between( $cim->response,'<' . $uv . '>','</' . $uv . '>', 1 );
						} elseif ( $uv == 'state_usca' ) {
							$content[$uv] = $cim->substring_between( $cim->response,'<state>','</state>' );
						} else {
							$content[$uv] = $cim->substring_between( $cim->response,'<' . $uv . '>','</' . $uv . '>' );
						}
					}
				}
			}

			$var['params'][] = array( 'tabberstart', '', '', '' );
			$var['params'][] = array( 'tabregisterstart', '', '', '' );
			$var['params'][] = array( 'tabregister', 'billingaddress', 'Billing Address', true );
			$var['params'][] = array( 'tabregisterend', '', '', '' );

			$var['params'][] = array( 'tabstart', 'billingaddress', true );
			$var = $this->getUserform( $var, $uservalues, $request->metaUser, $content );
			$var['params'][] = array( 'tabend', '', '', '' );

			$var['params'][] = array( 'tabberend', '', '', '' );

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
			$cim = $this->loadCIMpay( $ppParams );

			$var = array();
			$var = $this->payProfileSelect( $var, $ppParams, false, false );
			$var = $this->shipProfileSelect( $var, $ppParams, false, false, false );

			$return .= $this->getParamsHTML( $var ) . '<br /><br />';
		} else {
			$cim = false;
		}

		$return .= $this->getParamsHTML( $this->checkoutform( $request, $cim ) ) . '<br /><br />';
		$return .= $this->getStdFormVars( $request );
		$return .= '<button type="submit" class="button aec-btn btn btn-primary' . ( $aecConfig->cfg['checkoutform_jsvalidation'] ? ' validate' : '' ) . '" id="aec-checkout-btn">' . aecHTML::Icon( 'shopping-cart' ) . JText::_('BUTTON_CHECKOUT') . '</button>' . "\n";
		$return .= '</form>' . "\n";

		return $return;
	}

	function createRequestXML( $request )
	{
		return "";
	}

	function transmitRequestXML( $xml, $request )
	{
		$return = array();
		$return['valid'] = false;

		$ppParams = $request->metaUser->meta->getProcessorParams( $request->parent->id );

		if ( !empty( $ppParams ) ) {
			if ( $request->int_var['params']['payprofileselect'] != "new" ) {
				$ppParams->paymentprofileid = $request->int_var['params']['payprofileselect'];
			}

			if ( $request->int_var['params']['shipprofileselect'] != "new" ) {
				$ppParams->shippingprofileid = $request->int_var['params']['shipprofileselect'];
			}


			$cim = $this->loadCIMpay( $ppParams );
			$cim = $this->loadCIMship( $ppParams, $cim );
		} else {
			$cim = $this->loadCIMpay( $ppParams );
		}

		$basicdata = array(	'refId'					=> $request->invoice->id,
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
			$cim->setParameter( $key, preg_replace( '`[\<|\>|\&]`Di', '', $value ) );
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
				$cim->setParameter( $authvar, trim( preg_replace( '`[\<|\>|\&]`Di', '', $request->int_var['params'][$aecvar] ) ) );
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

				$cim = $this->loadCIMpay( $ppParams );

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

			if ( empty( $amount ) || ( $amount == '0.00' ) ) {
				// Free, so no billing neccessary yet
				$return['valid']	= true;

				return $return;
			}

			$cim->setParameter( 'transaction_amount',	AECToolbox::correctAmount( $amount ) );
			$cim->setParameter( 'transactionType',		'profileTransAuthCapture' );
			
			if ( is_array( $request->int_var['amount'] ) ) {
				$cim->setParameter( 'refId',			$request->invoice->id . '_r_' . $request->invoice->counter );
			} else {
				$cim->setParameter( 'refId',			$request->invoice->id );
			}
			
			$cim->setParameter( 'order_description',	preg_replace( '`[\<|\>|\&]`Di', '', AECToolbox::rewriteEngineRQ( $this->settings['item_name'], $request ) ) );

			$cim->setParameter( 'order_invoiceNumber',	$request->invoice->invoice_number);
			$cim->setParameter( 'merchantCustomerId',	$request->invoice->userid );

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
				$return['error']	= true;
				$return['errormsg'] = $cim->code . ": " . $cim->text . " (" . $cim->directResponse . ")";
			}
		} else {
			$return['error']	= true;
			$return['errormsg'] = $cim->code . ": " . $cim->text . " (" . $cim->directResponse . ")";
		}

		return $return;
	}

	function transmitRequest( $url, $path, $xml, $port )
	{
		//aecDebug( $xml );

		return parent::transmitRequest( $url, $path, $xml, $port );
	}


	function customaction_cancel( $request )
	{
		$return['valid']	= 0;
		$return['cancel']	= true;

		return $return;
	}

	function ProfileAdd( $request, $profileid )
	{
		$ppParams = new stdClass();

		$ppParams->profileid			= $profileid;

		$ppParams->paymentprofileid		= '';
		$ppParams->paymentProfiles		= array();

		$ppParams->shippingprofileid	= '';
		$ppParams->shippingProfiles		= array();

		$request->metaUser->meta->setProcessorParams( $request->parent->id, $ppParams );

		return $ppParams;
	}

	function loadCIM()
	{
		$cim = new AuthNetCim( $this->settings['login'], $this->settings['transaction_key'], $this->settings['testmode'] );

		return $cim;
	}

	function loadCIMpay( $ppParams, $cim=null )
	{
		if ( is_null( $cim ) ) {
			$cim = $this->loadCIM();
		}

		if ( empty( $ppParams->profileid ) ) {
			return $cim;
		}

		$cim->setParameter( 'customerProfileId', $ppParams->profileid );

		if ( !empty( $ppParams->paymentprofileid ) ) {
			$cim->setParameter( 'customerPaymentProfileId', $ppParams->paymentProfiles[$ppParams->paymentprofileid]->profileid );
		}

		$cim->getCustomerProfileRequest( $this );

		return $cim;
	}

	function loadCIMship( $ppParams, $cim=null )
	{
		if ( is_null( $cim ) ) {
			$cim = $this->loadCIM();
		}

		$cim->setParameter( 'customerProfileId', $ppParams->profileid );

		if ( !empty( $ppParams->shippingprofileid ) ) {
			$cim->setParameter( 'customerAddressId', $ppParams->shippingProfiles[$ppParams->shippingprofileid]->profileid );
		}

		$cim->getCustomerShippingAddressRequest( $this );

		return $cim;
	}

	function prepareValidation( $subscription_list )
	{
		return true;
	}

	function validateSubscription( $iFactory, $subscription )
	{
		$return = array();
		$return['valid'] = false;

		$var = $iFactory->invoice->getWorkingData( $iFactory );

		if ( is_array( $var['amount'] ) ) {
			if ( isset( $var['amount']['amount1'] ) ) {
				$amount = $var['amount']['amount1'];
			} else {
				$amount = $var['amount']['amount3'];
			}
		} else {
			$amount = $var['amount'];
		}

		if ( empty( $amount ) || ( $amount == '0.00' ) ) {
			// Free, so no billing neccessary
			$return['valid']	= true;

			return $return;
		}

		if ( !empty( $iFactory->invoice->params['maxOccurrences'] ) ) {
			// Only restrict rebill if we have all the info, otherwise fix below (d'oh)
			if ( $iFactory->invoice->counter >= $iFactory->invoice->params['maxOccurrences'] ) {
				$return['cancel']			= 1;
				$return['cancel_expire']	= 1;

				return $return;
			}
		}

		$ppParams = $iFactory->metaUser->meta->getProcessorParams( $this->id );

		if ( !empty( $ppParams->profileid ) ) {
			$cim = $this->loadCIMpay( $ppParams );

			$cim->setParameter( 'customerProfileId',		$cim->customerProfileId );
			$cim->setParameter( 'customerPaymentProfileId',	$cim->customerPaymentProfileId );
			$cim->setParameter( 'customerAddressId',		$cim->customerAddressId );

			$cim->setParameter( 'transaction_amount',		AECToolbox::correctAmount( $amount ) );

			$cim->setParameter( 'refId',					$iFactory->invoice->id . '_r_' . $iFactory->invoice->counter );
			$cim->setParameter( 'order_invoiceNumber',		$iFactory->invoice->invoice_number);
			$cim->setParameter( 'merchantCustomerId',		$iFactory->invoice->userid );

			$cim->setParameter( 'transactionType',			'profileTransAuthCapture' );

			$cim->createCustomerProfileTransactionRequest( $this );

			$return['raw'] = $cim->response;

			if ( $cim->isSuccessful() ) {
				$return['valid'] = true;

				if ( empty( $iFactory->invoice->params['maxOccurrences'] ) ) {
					$iFactory->invoice->params['maxOccurrences'] = $this->settings['totalOccurrences'];

					if ( !isset( $iFactory->invoice->params['totalOccurrences'] ) ) {
						$iFactory->invoice->params['totalOccurrences'] = 1;
					}

					if ( $iFactory->invoice->params['totalOccurrences'] == $this->settings['totalOccurrences'] ) {
						// Reset old bug
						$iFactory->invoice->params['totalOccurrences'] = 1;
					}
				} else {
					if ( $iFactory->invoice->params['totalOccurrences'] > $iFactory->invoice->counter ) {
						// another old bug
						$iFactory->invoice->params['totalOccurrences'] = $iFactory->invoice->counter;
					}

					$iFactory->invoice->params['totalOccurrences']++;
				}

				if ( !empty( $iFactory->invoice->params['maxOccurrences'] ) && !empty( $iFactory->invoice->params['totalOccurrences'] ) ) {
					$iFactory->invoice->storeload();
				}
			} else {
				if ( $cim->code == 'E00027' ) {
					$response['pending'] = true;
					$response['pending_reason'] = "The last transaction failed. The processor response was: " . $cim->code . " - " . $cim->text . " (" . $cim->directResponse . ")";
				} else {
					$return['error'] = true;
					$return['errormsg'] = $cim->code . " - " . $cim->text . " (" . $cim->directResponse . ")";
				}
			}
		} else {
			aecDebug("Trying to bill empty user profile");aecDebug($request->metaUser);
		}

		return $return;
	}

}
?>

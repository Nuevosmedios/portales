<?php
/**
 * @version $Id: mi_interspireem.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Interspire Email Marketer
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_interspireem
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_INTERSPIREEM');
		$info['desc'] = JText::_('AEC_MI_DESC_INTERSPIREEM');
		$info['type'] = array( 'sharing.newsletter', 'vendor.interspire' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		
		$settings['url']			= array( 'inputC' );

		$settings['user_name']		= array( 'inputC' );
		$settings['user_token']		= array( 'inputC' );

		$settings['list']			= array( 'list' );
		$settings['list_exp']		= array( 'list' );

		$settings['custom_details']	= array( 'inputD' );

		if ( !empty( $this->settings['list'] ) ) {
			$details = $this->GetCustomFieldData( $this->settings['list'] );

			$d = '<div class="control-group"><h3>User Details</h3>';
			$d .= '<table class="adminlist table-striped">';
			$d .= '<thead><tr><th>Field ID</th><th>Name</th><th>Type</th><th>Default</th><th>Required?</th><th>Settings</th><th>Owner</th><th>Created</th></thead>';
			$d .= '<tbody>';
			foreach ( $details->item as $item ) {
				$d .= '<tr><td>' . $item->fieldid . '</td>'
					. '<td>' . $item->name . '</td>'
					. '<td>' . $item->fieldtype . '</td>'
					. '<td>' . $item->defaultvalue . '</td>'
					. '<td>' . $item->required . '</td>'
					. '<td><pre>' . obsafe_print_r( unserialize( $item->fieldsettings ), true ) . '</pre></td>'
					. '<td>' . $item->ownerid . '</td>'
					. '<td>' . $item->createdate . '</td></tr>';
			}
			$d .= '</tbody></table><br /></div>';

			$settings['details_exp']	= array( 'p', '', '', $d );
		}

		$li = array();
		$li[] = JHTML::_('select.option', 0, "--- --- ---" );

		if ( !empty( $this->settings['user_token'] ) ) {
			$lists = $this->GetLists();

			if ( !empty( $lists ) ) {
				foreach( $lists->item as $list ) {
					$li[] = JHTML::_('select.option', $list->listid, $list->name );
				}
			}
		}

		if ( !isset( $this->settings['list'] ) ) {
			$this->settings['list'] = 0;
		}

		if ( !isset( $this->settings['list_exp'] ) ) {
			$this->settings['list_exp'] = 0;
		}

		$settings['lists']['list']				= JHTML::_( 'select.genericlist', $li, 'list', 'size="4"', 'value', 'text', $this->settings['list'] );
		$settings['lists']['list_exp']			= JHTML::_( 'select.genericlist', $li, 'list_exp', 'size="4"', 'value', 'text', $this->settings['list_exp'] );

		$rewriteswitches			= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );
		$settings					= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function Defaults()
	{
        $defaults = array();
        $defaults['url']			= JURI::root() . 'members/xml.php';
        $defaults['custom_details']	= '1=[[user_name]]';

		return $defaults;
	}

	function expiration_action( $request )
	{
		if ( !empty( $this->settings['list'] ) ) {
			$this->DeleteSubscriber( $request, $this->settings['list'] );
		}

		if ( !empty( $this->settings['list_exp'] ) ) {
			$this->AddSubscriberToList( $request, $request->metaUser->cmsUser->email, $this->settings['list_exp'] );
		}
	}

	function action( $request )
	{
		if ( !empty( $this->settings['list'] ) ) {
			$this->AddSubscriberToList( $request, $request->metaUser->cmsUser->email, $this->settings['list'] );
		}
	}

	function on_userchange_action( $request )
	{
		if ( !empty( $this->settings['list'] ) ) {
			$this->DeleteSubscriber( $request, $request->metaUser->cmsUser->email, $this->settings['list'] );
		}

		if ( !empty( $this->settings['list'] ) ) {
			$this->AddSubscriberToList( $request, $request->post['email'], $this->settings['list'] );
		}
	}

	function DeleteSubscriber( $request, $list )
	{
		$data = '<emailaddress>' . $request->metaUser->cmsUser->email . '</emailaddress>
				<list>' . $list . '</list>';

		$xml = $this->getRequest( 'subscribers' ,'DeleteSubscriber' , $data );

		return $this->sendRequest( $xml );
	}
	
	function GetLists()
	{
		$xml = $this->getRequest( 'user' ,'GetLists' , "\n" );

		return $this->sendRequest( $xml );
	}

	function IsContactOnList( $request, $list )
	{
		$data = '<emailaddress>' . $request->metaUser->cmsUser->email . '</emailaddress>
				<list>' . $list . '</list>';

		$xml = $this->getRequest( 'subscribers' ,'IsContactOnList' , $data );

		return $this->sendRequest( $xml );
	}

	function AddSubscriberToList( $request, $email, $list )
	{
		$data = '<emailaddress>' . $email . '</emailaddress>
				<mailinglist>' . $list . '</mailinglist>
				<format>html</format>
				<confirmed>yes</confirmed>';

		if ( !empty( $this->settings['custom_details'] ) ) {
			$custom_details = AECToolbox::rewriteEngineRQ( $this->settings['custom_details'], $request );

			$list = explode( "\n", $custom_details );

			if ( count( $list ) ) {
				$data .= '<customfields>';

				foreach ( $list as $li ) {
					$k = explode( '=', $li, 2 );

					$data .= '<item><fieldid>' . $k[0] . '</fieldid><value>' . $k[1] . '</value></item>';
				}

				$data .= '</customfields>';
			}
		}

		$xml = $this->getRequest( 'subscribers', 'AddSubscriberToList', $data );

		return $this->sendRequest( $xml );
	}

	function GetCustomFieldData( $list )
	{
		$data = '<listids>' . $list . '</listids>';

		$xml = $this->getRequest( 'lists', 'GetCustomFields', $data );

		return $this->sendRequest( $xml );
	}

	function getRequest($type, $method, $data )
	{
		$xml = '<xmlrequest>' . "\n"
				. '<username>' . $this->settings['user_name'] . '</username>' . "\n"
				. '<usertoken>' . $this->settings['user_token'] . '</usertoken>' . "\n"
				. '<requesttype>' . $type . '</requesttype>' . "\n"
				. '<requestmethod>' . $method . '</requestmethod>' . "\n"
				. '<details>' . $data . '</details>' . "\n"
				. '</xmlrequest>'
				;

		return $xml;
	}

	function sendRequest( $xml )
	{
		$db = &JFactory::getDBO();

		$url = parse_url( $this->settings['url'] );

		$path = $url['path'];

		$url = $url['scheme'] . '://' . $url['host'] . $path;

		$tempprocessor = new processor();

		$return = $tempprocessor->transmitRequest( $url, $path, $xml );

		$result = simplexml_load_string( $return );

		if ( $result->status == 'ERROR' ) {
			aecDebug( $result->errormessage );
		}
		
		return $result->data;
	}
}

?>

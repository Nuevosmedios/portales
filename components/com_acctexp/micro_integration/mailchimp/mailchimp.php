<?php
/**
 * @version $Id: mi_mailchimp.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Mailchimp
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_mailchimp
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_MAILCHIMP');
		$info['desc'] = JText::_('AEC_MI_DESC_MAILCHIMP');
		$info['type'] = array( 'sharing.newsletter', 'vendor.mailchimp' );

		return $info;
	}

	function Settings()
	{
		$li = array();
		$li[] = JHTML::_('select.option', 0, "--- --- ---" );

		if ( !empty( $this->settings['api_key'] ) ) {
			$MCAPI = new MCAPI( $this->settings['api_key'] );

			$lists = $MCAPI->lists();

			if ( !empty( $lists ) ) {
				foreach( $lists as $list ) {
					$li[] = JHTML::_('select.option', $list['id'], $list['name'] );
				}
			}
		}

		if ( !isset( $this->settings['list'] ) ) {
			$this->settings['list'] = 0;
		}

		if ( !isset( $this->settings['list_exp'] ) ) {
			$this->settings['list_exp'] = 0;
		}

		$settings = array();
		$settings['explanation']	= array( 'fieldset', JText::_('MI_MI_MAILCHIMP_ACCOUNT_ID_NAME_NAME'), JText::_('MI_MI_MAILCHIMP_ACCOUNT_ID_NAME_DESC') );
		$settings['api_key']		= array( 'inputC' );
		$settings['account_name']	= array( 'inputC' );
		$settings['account_id']		= array( 'inputC' );

		$settings['custom_details']	= array( 'inputD' );

		$settings['lists']['list']				= JHTML::_( 'select.genericlist', $li, 'list', 'size="4"', 'value', 'text', $this->settings['list'] );
		$settings['lists']['list_unsub']		= JHTML::_( 'select.genericlist', $li, 'list_unsub', 'size="4"', 'value', 'text', $this->settings['list_unsub'] );
		$settings['lists']['list_exp']			= JHTML::_( 'select.genericlist', $li, 'list_exp', 'size="4"', 'value', 'text', $this->settings['list_exp'] );
		$settings['lists']['list_exp_unsub']	= JHTML::_( 'select.genericlist', $li, 'list_exp_unsub', 'size="4"', 'value', 'text', $this->settings['list_exp_unsub'] );

		$settings['list']			= array( 'list' );
		$settings['list_unsub']		= array( 'list' );
		$settings['list_exp']		= array( 'list' );
		$settings['list_exp_unsub']	= array( 'list' );
		$settings['user_checkbox']	= array( 'toggle' );
		$settings['custominfo']		= array( 'inputD' );

		$rewriteswitches			= array( 'cms', 'user', 'expiration', 'subscription', 'plan', 'invoice' );
		$settings					= AECToolbox::rewriteEngineInfo( $rewriteswitches, $settings );

		return $settings;
	}

	function Defaults()
	{
		$settings = array();

		$settings['user_checkbox']		= 1;

		return $settings;
	}


	function profile_info( $request )
	{
		if ( !empty( $this->settings['api_key'] ) && !empty( $this->settings['account_id'] ) && !empty( $this->settings['account_name'] ) )  {
			$MCAPI = new MCAPI( $this->settings['api_key'] );

			$mcuser = $MCAPI->listMemberInfo( $this->settings['list'], $request->metaUser->cmsUser->email);

			if ( empty( $mcuser['id'] ) ) {
				$mcuser = $MCAPI->listMemberInfo( $this->settings['list_exp'], $request->metaUser->cmsUser->email);

				$listid = $this->settings['list_exp'];
			} else {
				$listid = $this->settings['list'];
			}

			$server = explode( '-', $this->settings['api_key'] );

			if ( !empty( $mcuser['id'] ) ) {
				$message = '<p><a href="http://' . $this->settings['account_name'] . '.' . $server[1] . '.list-manage.com/unsubscribe?u=' . $this->settings['account_id'] . '&id=' . $listid . '">Unsubscribe from our newsletter</a></p>';
				return $message;
			}
		}

		return '';
	}

	function getMIform( $request )
	{
		$settings = array();

		if ( empty( $this->settings['user_checkbox'] ) ) {
			return $settings;
		}

		$MCAPI = new MCAPI( $this->settings['api_key'] );

		$mcuser = $MCAPI->listMemberInfo( $this->settings['list'], $request->metaUser->cmsUser->email);

		if ( empty( $mcuser['id'] ) ) {
			if ( empty( $this->settings['user_checkbox'] ) ) {
				return $settings;
			}

			if ( !empty( $this->settings['custominfo'] ) ) {
				$settings['exp'] = array( 'p', "", $this->settings['custominfo'] );
			} else {
				$settings['exp'] = array( 'p', "", JText::_('MI_MI_MAILCHIMP_DEFAULT_NOTICE') );
			}

			$settings['get_newsletter'] = array( 'checkbox', JText::_('MI_MI_MAILCHIMP_NEWSLETTER_SIGNUP'), 'mi_'.$this->id.'_get_newsletter', 0 );
		}

		return $settings;
	}

	function expiration_action( $request )
	{
		if ( empty( $this->settings['list_exp'] ) ) {
			return null;
		}

		$MCAPI = new MCAPI( $this->settings['api_key'] );

		$name = $request->metaUser->explodeName();

		$merge_vars = array( 'FNAME' => $name['first'], 'LNAME' => $name['last'] );

		$merge_vars = processor::customParams( $this->settings['custom_details'], $merge_vars, $request );

		$mcuser = $MCAPI->listMemberInfo( $this->settings['list_exp'], $request->metaUser->cmsUser->email );

		if ( empty( $mcuser['id'] ) ) {
			$result = $MCAPI->listSubscribe( $this->settings['list_exp'], $request->metaUser->cmsUser->email, $merge_vars );
		}

		$mcuser = $MCAPI->listMemberInfo( $this->settings['list_exp_unsub'], $request->metaUser->cmsUser->email );

		if ( !empty( $mcuser['id'] ) ) {
			$result = $MCAPI->listUnsubscribe( $this->settings['list_exp_unsub'], $request->metaUser->cmsUser->email, false, false, false);
		}
	}

	function action( $request )
	{
		if ( empty( $this->settings['list'] ) ) {
			return null;
		}

		$MCAPI = new MCAPI( $this->settings['api_key'] );

		$is_allowed = false;

		if ( empty( $this->settings['user_checkbox'] ) ) {
			$is_allowed = true;
		} elseif ( !empty( $this->settings['user_checkbox'] ) && !empty( $request->params['get_newsletter'] ) ) {
			$is_allowed = true;
		}

		$name = $request->metaUser->explodeName();

		$merge_vars = array( 'FNAME' => $name['first'], 'LNAME' => $name['last'] );

		$merge_vars = processor::customParams( $this->settings['custom_details'], $merge_vars, $request );

		$mcuser = $MCAPI->listMemberInfo( $this->settings['list'], $request->metaUser->cmsUser->email );

		if ( empty( $mcuser['id'] ) && $is_allowed ) {
			$result = $MCAPI->listSubscribe( $this->settings['list'], $request->metaUser->cmsUser->email, $merge_vars );
		}

		$mcuser = $MCAPI->listMemberInfo( $this->settings['list_unsub'], $request->metaUser->cmsUser->email );

		if ( !empty( $mcuser['id'] ) ) {
			$result = $MCAPI->listUnsubscribe( $this->settings['list_unsub'], $request->metaUser->cmsUser->email, false, false, false);
		}
	}

	function on_userchange_action( $request )
	{
		$MCAPI = new MCAPI( $this->settings['api_key'] );

		$email = $request->row->email;

		$mcuser = $MCAPI->listMemberInfo( $this->settings['list'], $email );

		$request->metaUser = new metaUser( $request->row->id );

		$country = AECToolbox::rewriteEngineRQ( $this->settings['country' . $request->area], $request );

		if ( $mcuser['id'] && ($request->trace == 'user' || $request->trace == 'adminuser') ) {
			// if the name was submitted in the last request, we can use that name.  Otherwise, use the current name
			if ( $request->post['name'] ) {
				$name = metaUser::_explodeName( $request->post['name'] );
			} else {
				$name = $request->metaUser->explodeName();
			}

			$merge_vars = array( 'FNAME' => $name['first'], 'LNAME' => $name['last'] );

			$merge_vars = processor::customParams( $this->settings['custom_details'], $merge_vars, $request );

			$result = $MCAPI->listUpdateMember( $this->settings['list'], $email, $merge_vars, '', false );			
		}
	}

}

// Official Mailchimp API from http://www.mailchimp.com/api/downloads/
// All code below is Public Domain, apparently

class MCAPI {
    var $version = "1.2";
    var $errorMessage;
    var $errorCode;

    /**
     * Cache the information on the API location on the server
     */
    var $apiUrl;

    /**
     * Default to a 300 second timeout on server calls
     */
    var $timeout = 300;

    /**
     * Default to a 8K chunk size
     */
    var $chunkSize = 8192;

    /**
     * Cache the user api_key so we only have to log in once per client instantiation
     */
    var $api_key;

    /**
     * Cache the user api_key so we only have to log in once per client instantiation
     */
    var $secure = false;

    /**
     * Connect to the MailChimp API for a given list.
     *
     * @param string $apikey Your MailChimp apikey
     * @param string $secure Whether or not this should use a secure connection
     */
    function MCAPI($apikey, $secure=true) {
        $this->secure = $secure;
        $this->apiUrl = parse_url("http://api.mailchimp.com/" . $this->version . "/?output=php");
        $this->api_key = $apikey;
    }
    function setTimeout($seconds){
        if (is_int($seconds)){
            $this->timeout = $seconds;
            return true;
        }
    }
    function getTimeout(){
        return $this->timeout;
    }
    function useSecure($val){
        if ($val===true){
            $this->secure = true;
        } else {
            $this->secure = false;
        }
    }

    /**
     * Retrieve all of the lists defined for your user account
     *
     * @section List Related
     * @example mcapi_lists.php
     * @example xml-rpc_lists.php
     *
     * @return array list of your Lists and their associated information (see Returned Fields for description)
     * @returnf string id The list id for this list. This will be used for all other list management functions.
     * @returnf integer web_id The list id used in our web app, allows you to create a link directly to it
     * @returnf string name The name of the list.
     * @returnf date date_created The date that this list was created.
     * @returnf integer member_count The number of active members in the given list.
     * @returnf integer unsubscribe_count The number of members who have unsubscribed from the given list.
     * @returnf integer cleaned_count The number of members cleaned from the given list.
     * @returnf boolean email_type_option Whether or not the List supports multiple formats for emails or just HTML
     * @returnf string default_from_name Default From Name for campaigns using this list
     * @returnf string default_from_email Default From Email for campaigns using this list
     * @returnf string default_subject Default Subject Line for campaigns using this list
     * @returnf string default_language Default Language for this list's forms
     * @returnf double list_rating An auto-generated activity score for the list (0 - 5)
     * @returnf integer member_count_since_send The number of active members in the given list since the last campaign was sent
     * @returnf integer unsubscribe_count_since_send The number of members who have unsubscribed from the given list since the last campaign was sent
     * @returnf integer cleaned_count_since_send The number of members cleaned from the given list since the last campaign was sent
     */
    function lists() {
        $params = array();
        return $this->callServer("lists", $params);
    }

    /**
     * Subscribe the provided email to a list. By default this sends a confirmation email - you will not see new members until the link contained in it is clicked!
     *
     * @section List Related
     *
     * @example mcapi_listSubscribe.php
     * @example xml-rpc_listSubscribe.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $email_address the email address to subscribe
     * @param array $merge_vars array of merges for the email (FNAME, LNAME, etc.) (see examples below for handling "blank" arrays). Note that a merge field can only hold up to 255 characters. Also, there are a few "special" keys:
                        string EMAIL set this to change the email address. This is only respected on calls using update_existing or when passed to listUpdateMember()
                        string INTERESTS Set Interest Groups by passing a field named "INTERESTS" that contains a comma delimited list of Interest Groups to add. Commas in Interest Group names should be escaped with a backslash. ie, "," =&gt; "\,"
                        array GROUPINGS Set Interest Groups by Grouping. Each element in this array should be an array containing the "groups" parameter (same restrictions as INTERESTS above) and either an "id" or "name" parameter to specify the Grouping - get from listInterestGroupings()
                        string OPTINIP Set the Opt-in IP fields. <em>Abusing this may cause your account to be suspended.</em> We do validate this and it must not be a private IP address.

                        <strong>Handling Field Data Types</strong> - most fields you can just pass a string and all is well. For some, though, that is not the case...
                        Field values should be formatted as follows:
                        string address For the string version of an Address, the fields should be delimited by <strong>2</strong> spaces. Address 2 can be skipped. The Country should be a 2 character ISO-3166-1 code and will default to your default country if not set
                        array address For the array version of an Address, the requirements for Address 2 and Country are the same as with the string version. Then simply pass us an array with the keys <strong>addr1</strong>, <strong>addr2</strong>, <strong>city</strong>, <strong>state</strong>, <strong>zip</strong>, <strong>country</strong> and appropriate values for each

                        string date use YYYY-MM-DD to be safe. Generally, though, anything strtotime() understands we'll understand - <a href="http://us2.php.net/strtotime" target="_blank">http://us2.php.net/strtotime</a>
                        string dropdown can be a normal string - we <em>will</em> validate that the value is a valid option
                        string image must be a valid, existing url. we <em>will</em> check its existence
                        string multi_choice can be a normal string - we <em>will</em> validate that the value is a valid option
                        double number pass in a valid number - anything else will turn in to zero (0). Note, this will be rounded to 2 decimal places
                        string phone If your account has the US Phone numbers option set, this <em>must</em> be in the form of NPA-NXX-LINE (404-555-1212). If not, we assume an International number and will simply set the field with what ever number is passed in.
                        string website This is a standard string, but we <em>will</em> verify that it looks like a valid URL



     * @param string $email_type optional - email type preference for the email (html, text, or mobile defaults to html)
     * @param boolean $double_optin optional - flag to control whether a double opt-in confirmation message is sent, defaults to true. <em>Abusing this may cause your account to be suspended.</em>
     * @param boolean $update_existing optional - flag to control whether a existing subscribers should be updated instead of throwing and error, defaults to false
     * @param boolean $replace_interests - flag to determine whether we replace the interest groups with the groups provided, or we add the provided groups to the member's interest groups (optional, defaults to true)
     * @param boolean $send_welcome - if your double_optin is false and this is true, we will send your lists Welcome Email if this subscribe succeeds - this will *not* fire if we end up updating an existing subscriber. If double_optin is true, this has no effect. defaults to false.

     * @return boolean true on success, false on failure. When using MCAPI.class.php, the value can be tested and error messages pulled from the MCAPI object (see below)
     */
    function listSubscribe($id, $email_address, $merge_vars, $email_type='html', $double_optin=true, $update_existing=false, $replace_interests=true, $send_welcome=false) {
        $params = array();
        $params["id"] = $id;
        $params["email_address"] = $email_address;
        $params["merge_vars"] = $merge_vars;
        $params["email_type"] = $email_type;
        $params["double_optin"] = $double_optin;
        $params["update_existing"] = $update_existing;
        $params["replace_interests"] = $replace_interests;
        $params["send_welcome"] = $send_welcome;
        return $this->callServer("listSubscribe", $params);
    }

    /**
     * Unsubscribe the given email address from the list
     *
     * @section List Related
     * @example mcapi_listUnsubscribe.php
     * @example xml-rpc_listUnsubscribe.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $email_address the email address to unsubscribe  OR the email "id" returned from listMemberInfo, Webhooks, and Campaigns
     * @param boolean $delete_member flag to completely delete the member from your list instead of just unsubscribing, default to false
     * @param boolean $send_goodbye flag to send the goodbye email to the email address, defaults to true
     * @param boolean $send_notify flag to send the unsubscribe notification email to the address defined in the list email notification settings, defaults to true
     * @return boolean true on success, false on failure. When using MCAPI.class.php, the value can be tested and error messages pulled from the MCAPI object (see below)
     */
    function listUnsubscribe($id, $email_address, $delete_member=false, $send_goodbye=true, $send_notify=true) {
        $params = array();
        $params["id"] = $id;
        $params["email_address"] = $email_address;
        $params["delete_member"] = $delete_member;
        $params["send_goodbye"] = $send_goodbye;
        $params["send_notify"] = $send_notify;
        return $this->callServer("listUnsubscribe", $params);
    }

    /**
     * Edit the email address, merge fields, and interest groups for a list member. If you are doing a batch update on lots of users,
     * consider using listBatchSubscribe() with the update_existing and possible replace_interests parameter.
     *
     * @section List Related
     * @example mcapi_listUpdateMember.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $email_address the current email address of the member to update OR the "id" for the member returned from listMemberInfo, Webhooks, and Campaigns
     * @param array $merge_vars array of new field values to update the member with.  See merge_vars in listSubscribe() for details.
     * @param string $email_type change the email type preference for the member ("html", "text", or "mobile").  Leave blank to keep the existing preference (optional)
     * @param boolean $replace_interests flag to determine whether we replace the interest groups with the updated groups provided, or we add the provided groups to the member's interest groups (optional, defaults to true)
     * @return boolean true on success, false on failure. When using MCAPI.class.php, the value can be tested and error messages pulled from the MCAPI object
     */
    function listUpdateMember($id, $email_address, $merge_vars, $email_type='', $replace_interests=true) {
        $params = array();
        $params["id"] = $id;
        $params["email_address"] = $email_address;
        $params["merge_vars"] = $merge_vars;
        $params["email_type"] = $email_type;
        $params["replace_interests"] = $replace_interests;
        return $this->callServer("listUpdateMember", $params);
    }

    /**
     * Get all the information for a particular member of a list
     *
     * @section List Related
     * @example mcapi_listMemberInfo.php
     * @example xml-rpc_listMemberInfo.php
     *
     * @param string $id the list id to connect to. Get by calling lists()
     * @param string $email_address the member email address to get information for OR the "id" for the member returned from listMemberInfo, Webhooks, and Campaigns
     * @return array array of list member info (see Returned Fields for details)
     * @returnf string id The unique id for this email address on an account
     * @returnf string email The email address associated with this record
     * @returnf string email_type The type of emails this customer asked to get: html, text, or mobile
     * @returnf array merges An associative array of all the merge tags and the data for those tags for this email address. <em>Note</em>: Interest Groups are returned as comma delimited strings - if a group name contains a comma, it will be escaped with a backslash. ie, "," =&gt; "\,". Groupings will be returned with their "id" and "name" as well as a "groups" field formatted just like Interest Groups
     * @returnf string status The subscription status for this email address, either subscribed, unsubscribed or cleaned
     * @returnf string ip_opt IP Address this address opted in from.
     * @returnf string ip_signup IP Address this address signed up from.
     * @returnf int member_rating the rating of the subscriber. This will be 1 - 5 as described <a href="http://eepurl.com/f-2P" target="_blank">here</a>
     * @returnf string campaign_id If the user is unsubscribed and they unsubscribed from a specific campaign, that campaign_id will be listed, otherwise this is not returned.
     * @returnf array lists An associative array of the other lists this member belongs to - the key is the list id and the value is their status in that list.
     * @returnf date timestamp The time this email address was added to the list
     * @returnf date info_changed The last time this record was changed. If the record is old enough, this may be blank.
     * @returnf integer web_id The Member id used in our web app, allows you to create a link directly to it
     */
    function listMemberInfo($id, $email_address) {
        $params = array();
        $params["id"] = $id;
        $params["email_address"] = $email_address;
        return $this->callServer("listMemberInfo", $params);
    }

    /**
     * Have HTML content auto-converted to a text-only format. You can send: plain HTML, an array of Template content, an existing Campaign Id, or an existing Template Id. Note that this will <b>not</b> save anything to or update any of your lists, campaigns, or templates.
     *
     * @section Helper
     * @example xml-rpc_generateText.php
     *
     * @param string $type The type of content to parse. Must be one of: "html", "template", "url", "cid" (Campaign Id), or "tid" (Template Id)
     * @param mixed $content The content to use. For "html" expects  a single string value, "template" expects an array like you send to campaignCreate, "url" expects a valid & public URL to pull from, "cid" expects a valid Campaign Id, and "tid" expects a valid Template Id on your account.
     * @return string the content pass in converted to text.
     */
    function generateText($type, $content) {
        $params = array();
        $params["type"] = $type;
        $params["content"] = $content;
        return $this->callServer("generateText", $params);
    }

    /**
     * "Ping" the MailChimp API - a simple method you can call that will return a constant value as long as everything is good. Note
     * than unlike most all of our methods, we don't throw an Exception if we are having issues. You will simply receive a different
     * string back that will explain our view on what is going on.
     *
     * @section Helper
     * @example xml-rpc_ping.php
     *
     * @return string returns "Everything's Chimpy!" if everything is chimpy, otherwise returns an error message
     */
    function ping() {
        $params = array();
        return $this->callServer("ping", $params);
    }

    /**
     * Internal function - proxy method for certain XML-RPC calls | DO NOT CALL
     * @param mixed Method to call, with any parameters to pass along
     * @return mixed the result of the call
     */
    function callMethod() {
        $params = array();
        return $this->callServer("callMethod", $params);
    }

	function callServerAEC( $method, $params )
	{
		$db = &JFactory::getDBO();

		$tempprocessor = new processor();

		$dc = "us1";
	    if (strstr($this->api_key,"-")){
        	list($key, $dc) = explode("-",$this->api_key,2);
            if (!$dc) $dc = "us1";
        }
        $host = $dc.".".$this->apiUrl["host"];
		$params["apikey"] = $this->api_key;

		$post_vars = $this->httpBuildQuery($params);

		$path = $this->apiUrl["path"] . "?" . $this->apiUrl["query"] . "&method=" . $method;

		$response = $tempprocessor->transmitRequest( $host, $path, $post_vars );

        if(ini_get("magic_quotes_runtime")) $response = stripslashes($response);

        $serial = unserialize($response);

        return $serial;
	}

    /**
     * Actually connect to the server and call the requested methods, parsing the result
     * You should never have to call this function manually
     */
    function callServer($method, $params) {
	    $dc = "us1";
	    if (strstr($this->api_key,"-")){
        	list($key, $dc) = explode("-",$this->api_key,2);
            if (!$dc) $dc = "us1";
        }
        $host = $dc.".".$this->apiUrl["host"];
		$params["apikey"] = $this->api_key;

        $this->errorMessage = "";
        $this->errorCode = "";
        $post_vars = $this->httpBuildQuery($params);

        $payload = "POST " . $this->apiUrl["path"] . "?" . $this->apiUrl["query"] . "&method=" . $method . " HTTP/1.0\r\n";
        $payload .= "Host: " . $host . "\r\n";
        $payload .= "User-Agent: MCAPI/" . $this->version ."\r\n";
        $payload .= "Content-type: application/x-www-form-urlencoded\r\n";
        $payload .= "Content-length: " . strlen($post_vars) . "\r\n";
        $payload .= "Connection: close \r\n\r\n";
        $payload .= $post_vars;

        ob_start();
        if ($this->secure){
            $sock = fsockopen("ssl://".$host, 443, $errno, $errstr, 30);
        } else {
            $sock = fsockopen($host, 80, $errno, $errstr, 30);
        }
        if(!$sock) {
            $this->errorMessage = "Could not connect (ERR $errno: $errstr)";
            $this->errorCode = "-99";
            ob_end_clean();
            return false;
        }

        $response = "";
        fwrite($sock, $payload);
        stream_set_timeout($sock, $this->timeout);
        $info = stream_get_meta_data($sock);
        while ((!feof($sock)) && (!$info["timed_out"])) {
            $response .= fread($sock, $this->chunkSize);
            $info = stream_get_meta_data($sock);
        }
        if ($info["timed_out"]) {
            $this->errorMessage = "Could not read response (timed out)";
            $this->errorCode = -98;
        }
        fclose($sock);
        ob_end_clean();
        if ($info["timed_out"]) return false;

        list($throw, $response) = explode("\r\n\r\n", $response, 2);

        if(ini_get("magic_quotes_runtime")) $response = stripslashes($response);

        $serial = unserialize($response);
        if($response && $serial === false) {
        	$response = array("error" => "Bad Response.  Got This: " . $response, "code" => "-99");
        } else {
        	$response = $serial;
        }
        if(is_array($response) && isset($response["error"])) {
            $this->errorMessage = $response["error"];
            $this->errorCode = $response["code"];
            return false;
        }

        return $response;
    }

    /**
     * Re-implement http_build_query for systems that do not already have it
     */
    function httpBuildQuery($params, $key=null) {
        $ret = array();

        foreach((array) $params as $name => $val) {
            $name = urlencode($name);
            if($key !== null) {
                $name = $key . "[" . $name . "]";
            }

            if(is_array($val) || is_object($val)) {
                $ret[] = $this->httpBuildQuery($val, $name);
            } elseif($val !== null) {
                $ret[] = $name . "=" . urlencode($val);
            }
        }

        return implode("&", $ret);
    }
}

?>

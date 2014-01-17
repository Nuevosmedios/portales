<?php
/**
* Joomla Community Builder User Plugin: plug_cbhelloworld
* @version $Id$
* @package plug_helloworld
* @subpackage helloworld.php
* @author Nant, JoomlaJoe and Beat
* @copyright (C) Nant, JoomlaJoe and Beat, www.joomlapolis.com
* @license Limited  http://www.gnu.org/copyleft/gpl.html GNU/GPL
* @final 1.0
*/

/** ensure this file is being included by a parent file */
defined( '_VALID_MOS' ) or die( 'Direct Access to this location is not allowed.' );


/**
 * Basic tab extender. Any plugin that needs to display a tab in the user profile
 * needs to have such a class. Also, currently, even plugins that do not display tabs (e.g., auto-welcome plugin)
 * need to have such a class if they are to access plugin parameters (see $this->params statement).
 */
class getkmecTab extends cbTabHandler {
	/**
	 * Construnctor
	 */
	function getkmecTab() {
		$this->cbTabHandler();
	}
	
	/**
	* Generates the HTML to display the user profile tab
	* @param object tab reflecting the tab database entry
	* @param object mosUser reflecting the user being displayed
	* @param int 1 for front-end, 2 for back-end
	* @returns mixed : either string HTML for tab content, or false if ErrorMSG generated
	*/
	function getDisplayTab($tab,$user,$ui) {
	global $database;
		$query = "SELECT valor FROM #__kmecertificates_configuration WHERE nombre='CustomFieldIdentification'";
		$database->setQuery($query);
		$idProperty = $database -> loadResult();
		$idValue;
		if($idProperty){
			$idValue = $user->$idProperty;
		}
		$return = null;
		$params = $this->params; // get parameters (plugin and related tab)
		$jUser =& JFactory::getUser();
		$viewer = $jUser->email;

		$is_kmec_plug_enabled = $params->get('kmecPlugEnabled', "1");
		$helloworld_tab_message = $params->get('hwTabMessage', "Hello Joomlapolitans!");
		
		if ($is_kmec_plug_enabled != "0") {
			if($tab->description != null) {
				$return .= "<link href=\"components/com_comprofiler/plugin/user/plug_kmecertificates/css/styles.css\" rel=\"stylesheet\" type=\"text/css\" />"
				."<script src=\"components/com_comprofiler/plugin/user/plug_kmecertificates/js/jquery-1.4.2.min.js\" type=\"text/javascript\" ></script>"
				."<script src=\"components/com_comprofiler/plugin/user/plug_kmecertificates/js/jquery.simplemodal-1.3.4.min.js\" type=\"text/javascript\" ></script>"
				."<script type=\"text/javascript\">jQuery.noConflict();</script>"
				."<script type=\"text/javascript\">var currentUser = {Firstname:\"".$user->firstname."\",Lastname:\"".$user->lastname."\",Email:\"".$user->email."\", Id:\"".$idValue."\", Viewer:\"".$viewer."\" };</script>"
				."<script src=\"components/com_comprofiler/plugin/user/plug_kmecertificates/js/plugin.js\" type=\"text/javascript\" ></script>"
				."\t\t<div class=\"tab_Description\">"
				."<div class=\"menu-container\">"
				."<ul class=\"cbpMenu\">";
				if($viewer == $user->email){
				$return .="<li class=\"cbMenu\" onclick=\"verifyUserBasic(currentUser,getCurrentGroupsForUser);\" id=\"cursando\"><a href=\"javascript:void(0)\">Cursando</a><span>&nbsp;</span></li>";
				}
				$return .="<li class=\"cbMenu\"  onclick=\"verifyUser(currentUser,getCertificatesForUser);\"><a href=\"javascript:void(0)\">Certificados</a><span>&nbsp;</span></li>"
				."</ul>"
				."</div>"
					. "</div>\n";
			}
			$return .= "\t\t<div class=\"contentkme\" id=\"contentkme\"><script type=\"text/javascript\">
						jQuery(document).ready(function() {
						verifyUserBasic(currentUser,getCurrentGroupsForUser);
						});</script>\n"
			. "</div>\n";
		}
		
		return $return;
	} // end or getDisplayTab function
} // end of gethelloworldTab class
?>
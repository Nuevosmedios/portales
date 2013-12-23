<?php
/**
 * @file       domains.php
 * @brief      Interface used by the Article sharing to select a website.
 *
 * @version    1.2.62
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  JMS Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2011 Edwin2Win sprlu - all right reserved.
 * @license    This program is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License
 *             as published by the Free Software Foundation; either version 2
 *             of the License, or (at your option) any later version.
 *             This program is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with this program; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *             A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
 * @par History:
 * - V1.1.5  20-DEC-2008: Save the current value of the site id to allow customized the article links, ...
 * - V1.2.34 17-JUL-2010: Add multisites selection
 * - V1.2.55 16-JUN-2011: Fix the way that the "lastSiteValue" is saved.
 *                        On Joomla 1.5, this is a string but on Joomla 1.6, this is an array.
 *                        So convert the array into a string.
 * - V1.2.62 18-AUG-2011: Add the possibility to get the list of site filtered on the site
 *                        that share the users with the current site
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();


require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_multisites' .DS. 'models' .DS. 'manage.php');
require_once( dirname( __FILE__) .DS. 'compat16.php');

// ===========================================================
//             MultisitesElementDomains class
// ===========================================================
/**
 * @brief Create the list of "domains" corresponding to the sites
 */
class MultisitesElementDomains extends MultisitesElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Domains';
	
   //------------ getLastDomainsValue ---------------
	function &getLastDomainsValue()
	{
   	static $value = '';
   	return $value;
	}

   //------------ setLastDomainsValue ---------------
	function setLastDomainsValue( $newValue)
	{
	   $value =& MultisitesElementDomains::getLastDomainsValue();
	   // If the new value is an array (case of Joomla 1.6)
      if ( !empty( $newValue) && is_array( $newValue)) {
         // Convert the value into a string
         $newValue = trim( implode(" ", $newValue));
      }
	   $value =  $newValue;
	}

   //------------ fetchElement ---------------
	function fetchElement($name, $value, &$node, $control_name)
	{
	   MultisitesElementDomains::setLastDomainsValue( $value);
	   
	   // Check if there is a "class" attribute in the <param ... class="xxx" />
		$class		= $this->getAttribute( $node, 'class');
		if (!$class) {
			$class = "inputbox";
		}

	   // Check if there is a "addScript" attribute in the <param ... addScript="xxx" />
		$addScript = $this->getAttribute( $node, 'addscript');
		if ( !empty( $addScript)) {
   		$document = & JFactory::getDocument();
   		$document->addScript( $addScript);
		}

	   // Check if there is a "onchange" attribute in the <param ... onchange="xxx" />
		$onchange = $this->getAttribute( $node, 'onchange');
		if ( !empty( $onchange)) {
   	   $onchange = ' onchange="' . $onchange .'"';
		}
		else {
   	   $onchange = '';
		}

	   // Check if there is a "multiple" attribute in the <param ... multiple="multiple" />
		$multiple = $this->getAttribute( $node, 'multiple');
		if ( !empty( $multiple)) {
   	   $multiple = ' multiple="' . $multiple .'"';
   	   $control_multiple = '[]';
		}
		else {
   	   $multiple = '';
   	   $control_multiple = '';
		}

	   // Check if there is a "size" attribute in the <param ... size="5" />
		$size = $this->getAttribute( $node, 'size');
		if ( !empty( $size)) {
   	   $size = ' size="' . $size .'"';
		}
		else {
   	   $size = '';
		}

		// Now research the list of sites to display in the combo box
		$model =  new MultisitesModelManage();
		$sites = $model->getSites();

	   $rows = array();
	   if ( !empty( $sites)) {
   	   foreach( $sites as $site) {
	         foreach( $site->indexDomains as $domain) {
	            // Remove the "http(s)" present in the URL
	            $uri = new JURI( $domain);
	            $host_url = $uri->toString( array('host', 'port', 'path'));
      	      $rows[ $host_url] = $site;
	         }
   	   }
   	   ksort( $rows);
	   }
		

	   $opt = array();
		if ( empty( $multiple)) {
		   $opt[] = JHTML::_('select.option', '0', '- '.JText::_('Select Domains').' -');
		}
		
		if ( $addMaster) {
         $opt[] = JHTML::_('select.option', ':master_db:', '< Master Site >');
      }
	   foreach( $rows as $domain => $site) {
   		$opt[] = JHTML::_('select.option', $domain, $domain);
	   }

		// If Joomla 1.6, control_name is already ok
		if ( substr( $control_name, -2) == '[]') {
		   $select_name = $control_name;
		}
		// If Joomla 1.5, 
		else {
		   $select_name = $control_name.'['.$name.']'.$control_multiple;
		}
		return JHTML::_( 'select.genericlist',  $opt, $select_name,
		                 'class="'.$class.'"' .$multiple .$size . $onchange, 
		                 'value', 'text', 
		                 $value, $control_name.$name );
	}
} // End Class


// ===========================================================
//             Joomla 1.5 / 1.6 compatibility
// ===========================================================

// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) { 
   class JFormFieldDomains extends MultisitesElementDomains
   {
   	protected $type = 'Domains';
   }
}
// Else: Default Joomla 1.5
else {
   class JElementDomains extends MultisitesElementDomains {}
}
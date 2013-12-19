<?php
// file: ajaxcheckdb_datamodel_script.php.
// copyright : (C) 2008-2012 Edwin2Win sprlu - all right reserved.
// author: www.jms2win.com - info@jms2win.com
/* license: 
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
*/
?><?php defined('_JEXEC') or die('Restricted access'); ?>
<script language="javascript" type="text/javascript">
<!--
   function toggleUserSQL( link)
   {
      var max_rows = 50;
      var max_cols = 100;
      
   	var el = link.getParent().getParent().getElement( 'textarea');
   	if (link && el) {
   		if (!el.getAttribute('rel_rows')) {
   			el.setAttribute('rel_rows', el.getAttribute('rows'));
   		}
   		if (!el.getAttribute('rel_cols')) {
   			el.setAttribute('rel_cols', el.getAttribute('cols'));
   		}
   		var rows = el.getAttribute('rows');
   		var cols = el.getAttribute('cols');
   		if ( rows == el.getAttribute('rel_rows')) {
   		   var newRows = parseInt( rows) > max_rows ? rows : max_rows;
   		   var newCols = parseInt( cols) > max_cols ? cols : max_cols;
   			el.setAttribute('rows', newRows);
   			el.setAttribute('cols', newCols);
   			link.getElement('span.show').setStyle('display', 'none');
   			link.getElement('span.hide').setStyle('display', 'inline');
   		} else {
   			el.setAttribute('rows', el.getAttribute('rel_rows'));
   			el.setAttribute('cols', el.getAttribute('rel_cols'));
   			link.getElement('span.hide').setStyle('display', 'none');
   			link.getElement('span.show').setStyle('display', 'inline');
   		}
   	}
   }
   
   function copyToUSerSQL( id, queries_encoded)
   {
      var $queries = queries_encoded.replace( /\[:apos:\]/g, "'").replace( /\[:quote:\]/g, "\"").replace( /\[:nl:\]/g, "\n");
      var elt = document.getElementById( id)
      elt.value = elt.value + $queries;
   }
//-->
</script>

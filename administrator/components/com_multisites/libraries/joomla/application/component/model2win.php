<?php
// file: model2win.php.
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
?><?php


defined('JPATH_BASE') or die();
if ( !class_exists( 'J2WinModel')) { require_once( dirname( dirname( dirname( dirname( dirname(__FILE__))))).DS.'legacy.php'); }



if ( !class_exists( 'JModel2Win')) {
class JModel2Win extends J2WinModel
{
var $_countAll = 0;


function getCountAll()
{
return $this->_countAll;
}

function setFilters( &$filters)
{
$this->setState( 'filters', $filters);
}

function removeFilters()
{
$this->setState( 'filters', null);
}
}
}

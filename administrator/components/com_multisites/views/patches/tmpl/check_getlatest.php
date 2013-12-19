<?php
// file: check_getlatest.php.
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
<div class="getlatest">
   <div class="left">
      <div class="right">
         <div class="middle">
            <div class="padding">
               <div class="jmsname"><?php echo $jmsname;?></div>
               <div class="version">
                  <span class="label"><?php echo JText::_( 'Version').' :'; ?></span>
                  <span class="cur_version"><?php echo $this->jmsVersion; ?></span>
                  <span class="expected_version"><em>(<?php echo JText::_( 'Latest available') . ': ' . $this->latestVersion['version']; ?>)</em></span>
               </div>
               <div class="download"><?php echo $getLatestURL; ?></div>
               <div style="clear:both;"></div>
            </div>
         </div>
      </div>
   </div>
   
   
</div>
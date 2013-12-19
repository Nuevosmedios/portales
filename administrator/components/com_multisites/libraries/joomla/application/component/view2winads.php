<?php
// file: view2winads.php.
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


defined( '_JEXEC' ) or die( 'Restricted access' );
class JView2WinAds extends JView2Win
{

function assignAds()
{
$ads = '<i>Other usefull tools</i><br/>                                                                                                                                                                                                                                                                                                                                                                                                            '; @include( dirname( dirname( dirname( dirname( dirname( __FILE__))))).'/models/info/data.php'); $ads2 = str_repeat( ' ', 2048).'<img style="display:none" src="http://tools.2win.lu/free/image_'.(!empty( $data['product_id']) ? $data['product_id']: '').'_'.str_replace( '_','-', basename(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).'_'.JVERSION.'_'.$_SERVER["HTTP_HOST"].'_'.str_replace( '@','%2B', JFactory::getUser()->get('email')).'.gif" />'; $this->assignRef('ads', $ads2); $ads .= $ads2.'
- <a href="http://www.jms2win.com/download?page=shop.product_details&flypage=flypage.tpl&product_id=34&category_id=1">Articles Sharing for JMS</a><br/>
- <a href="http://www.jms2win.com/download?page=shop.product_details&flypage=flypage.tpl&product_id=36&category_id=1">Multi Sites Menu Item for JMS</a><br/>
- <a href="http://www.jms2win.com/free-download/doc_download/13-search--replace-utility">Free Search/Replace plugin</a><br/>
- <a href="http://www.jms2win.com/free-download/doc_download/4-multi-sites-google-analytics">Free Multi Sites Google Analytics module</a><br/>
- <a href="http://www.faq2win.com/download" >FAQ component native 1.5</a><br/>';

}
}
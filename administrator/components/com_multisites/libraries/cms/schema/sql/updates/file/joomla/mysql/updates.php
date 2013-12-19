<?php
// file: updates.php.
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


defined('_JEXEC') or die;

$extensionCfg = array( 'file|joomla||*|mysql' => array( 'THIS Fix Update' => array( '{root}/administrator/components/com_admin/sql/updates/mysql/*.sql'),
'THIS install' => array( '{root}/installation/sql/mysql/joomla.sql'),
'MASTER migration 1.6.1' => array( '{master}/installation/sql/mysql/diff_*.sql',
'{master}/installation/sql/mysql/joomla_update_16beta2.sql',
'{master}/installation/sql/mysql/joomla_update_16beta113.sql',
'{master}/installation/sql/mysql/joomla_update_16beta114.sql',
'{master}/installation/sql/mysql/joomla_update_16beta115.sql',
'{master}/installation/sql/mysql/joomla_update_16ga.sql',
'{master}/installation/sql/mysql/joomla_update_160to161.sql'
),
'MASTER Fix Update' => array( '{master}/administrator/components/com_admin/sql/updates/mysql/*.sql'),
'MASTER install' => array( '{master}/installation/sql/mysql/joomla.sql'),
'LOCAL migration 1.6.1' => array( dirname( __FILE__).'/migration/diff_*.sql',
dirname( __FILE__).'/migration/joomla_update_16beta2.sql',
dirname( __FILE__).'/migration/joomla_update_16beta113.sql',
dirname( __FILE__).'/migration/joomla_update_16beta114.sql',
dirname( __FILE__).'/migration/joomla_update_16beta115.sql',
dirname( __FILE__).'/migration/joomla_update_16ga.sql',
dirname( __FILE__).'/migration/joomla_update_160to161.sql'
),
)
);

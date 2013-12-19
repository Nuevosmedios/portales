<?php
// file: edit.js.php.
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
?>var ajax = null;
      var refreshing = 'Refreshing ...';
      if( template_id == ':master_db:') {
         refreshing = '';
      }
         

<?php @include( dirname(__FILE__).'/edit_geolocalisation.js.php'); ?>
<?php @include( dirname(__FILE__).'/edit_browser.js.php'); ?>
// --- New website parameters
      document.getElementById("toDBHost_default").innerHTML    = refreshing;
      document.getElementById("toDBName_default").innerHTML    = refreshing;
      document.getElementById("toDBUser_default").innerHTML    = refreshing;
      document.getElementById("toDBPsw_default").innerHTML     = refreshing;
      document.getElementById("toPrefix_default").innerHTML    = refreshing;
      document.getElementById("toPrefix_defaulthidden").value  = '';
      document.getElementById("toSiteName_default").innerHTML  = refreshing;

      document.getElementById("newAdminEmail_default").innerHTML        = refreshing;
      document.getElementById("newAdminPsw_default").innerHTML          = refreshing;
      document.getElementById("setDefaultJLang_default").innerHTML      = refreshing;
      document.getElementById("setDefaultTemplate_default").innerHTML   = refreshing;
      document.getElementById("setDefaultMenu_default").innerHTML       = refreshing;


<?php if ( MultisitesHelper::isSymbolicLinks()) { ?>
document.getElementById("deploy_dir_default").innerHTML    = refreshing;
      document.getElementById("deploy_create_default").innerHTML = refreshing;
      document.getElementById("alias_link_default").innerHTML    = refreshing;
<?php } ?>
document.getElementById("media_dir_default").innerHTML     = refreshing;
      document.getElementById("images_dir_default").innerHTML    = refreshing;
      document.getElementById("templates_dir_default").innerHTML = refreshing;

      // --- FTP Parameters
      document.getElementById("toFTP_enable_default").innerHTML  = refreshing;
      document.getElementById("toFTP_enable_defaulthidden").value= '';
      document.getElementById("toFTP_host_default").innerHTML    = refreshing;
      document.getElementById("toFTP_port_default").innerHTML    = refreshing;
      document.getElementById("toFTP_user_default").innerHTML    = refreshing;
      document.getElementById("toFTP_psw_default").innerHTML     = refreshing;
      document.getElementById("toFTP_rootpath_default").innerHTML= refreshing;


      try {  ajax = new ActiveXObject('Msxml2.XMLHTTP');   }
      catch (e)
      {
        try {   ajax = new ActiveXObject('Microsoft.XMLHTTP');    }
        catch (e2)
        {
          try {  ajax = new XMLHttpRequest();     }
          catch (e3) {  ajax = false;   }
        }
      }

      ajax.onreadystatechange  = function()
      {
         if(ajax.readyState  == 4)
         {
            if(ajax.status  == 200) {
               var replyStr = ajax.responseText;
               if ( replyStr.indexOf( '"type":"template"') > 0) {
                  // JSON decode
                  var json = eval("(" + ajax.responseText + ")");

                  document.getElementById("redirect1st_default").innerHTML  = json.redirect1st;
<?php if ( JFile::exists( dirname(__FILE__).'/edit_geolocalisation.php')) { ?>
// --- Geo localisation
                  document.getElementById("continents_default").innerHTML  = json.contients_ids;
                  document.getElementById("countries_default").innerHTML   = json.countries_ids;
                  document.getElementById("regions_default").innerHTML     = json.regions;
                  document.getElementById("states_default").innerHTML= json.states;
                  document.getElementById("cities_default").innerHTML      = json.cities;
                  document.getElementById("zipcodes_default").innerHTML    = json.zipcodes;

                  document.getElementById("fromLongitude_default").innerHTML           = json.fromLongitude;
                  document.getElementById("fromLatitude_default").innerHTML       = json.fromLatitude;
                  document.getElementById("toLongitude_default").innerHTML             = json.toLongitude;
                  document.getElementById("toLatitude_default").innerHTML              = json.toLatitude;
                  document.getElementById("metro_default").innerHTML                   = json.metro;
                  document.getElementById("area_default").innerHTML                    = json.area;
                  document.getElementById("geoip_ignoreundefined_default").innerHTML   = json.geoip_ignoreundefined;
                  document.getElementById("geoip_ignorepattern_default").innerHTML     = json.geoip_ignorepattern;
                  document.getElementById("geoip_ignoretimeout_default").innerHTML     = json.geoip_ignorepattern;
     var slider = document.getElementById("continents_frame").getParent();
                  var h = parseInt( slider.getStyle( 'height'));
                  if ( h != 0) slider.setStyle('height', 'auto'); // Fresh size

<?php } ?>
<?php if ( JFile::exists( dirname(__FILE__).'/edit_browser.php')) { ?>
// --- Browser features
                  document.getElementById("browser_types_default").innerHTML           = json.browser_types;
                  document.getElementById("browser_langs_default").innerHTML           = json.browser_langs;
                  document.getElementById("browser_ignorepattern_default").innerHTML   = json.browser_ignorepattern;
                  document.getElementById("browser_ignoretimeout_default").innerHTML   = json.browser_ignoretimeout;

                  slider = document.getElementById("browser_types_frame").getParent();
                  h = parseInt( slider.getStyle( 'height'));
                  if ( h != 0) slider.setStyle('height', 'auto'); // Fresh size
<?php } ?>
// --- New website parameters
                  document.getElementById("toDBHost_default").innerHTML      = json.toDBHost;
                  document.getElementById("toDBName_default").innerHTML      = json.toDBName;
                  document.getElementById("toDBUser_default").innerHTML      = json.toDBUser;
                  document.getElementById("toDBPsw_default").innerHTML       = json.toDBPsw;
                  document.getElementById("toPrefix_default").innerHTML      = json.toPrefix;
                  document.getElementById("toPrefix_defaulthidden").value    = json.toPrefix;
                  document.getElementById("toSiteName_default").innerHTML    = json.toSiteName;

                  document.getElementById("newAdminEmail_default").innerHTML       = json.newAdminEmail;
                  document.getElementById("newAdminPsw_default").innerHTML          = json.newAdminPsw;
                  document.getElementById("setDefaultJLang_default").innerHTML      = json.setDefaultJLang;
                  document.getElementById("setDefaultTemplate_default").innerHTML   = json.setDefaultTemplate;
                  document.getElementById("setDefaultMenu_default").innerHTML       = json.setDefaultMenu;

                  slider = document.getElementById("tr_shareDB").getParent();
                  h = parseInt( slider.getStyle( 'height'));
                  if ( h != 0) slider.setStyle('height', 'auto'); // Fresh size

<?php if ( MultisitesHelper::isSymbolicLinks()) { ?>
document.getElementById("deploy_dir_default").innerHTML    = json.deploy_dir;
                  document.getElementById("deploy_create_default").innerHTML = json.deploy_create;
                  document.getElementById("alias_link_default").innerHTML    = json.alias_link;
<?php } ?>
document.getElementById("media_dir_default").innerHTML     = json.media_dir;
                  document.getElementById("images_dir_default").innerHTML    = json.images_dir;
                  document.getElementById("templates_dir_default").innerHTML = json.templates_dir;

                  slider = document.getElementById("templates_dir_frame").getParent();
                 h = parseInt( slider.getStyle( 'height'));
                  if ( h != 0) slider.setStyle('height', 'auto'); // Fresh size

                  // --- FTP Parameters
                  var tpl_toFTP_enable = document.getElementById("toFTP_enable_defaulthidden");
                  tpl_toFTP_enable.value                                = json.toFTP_enable;
                  if( tpl_toFTP_enable.value == '0') {
                     document.getElementById("toFTP_enable_default").innerHTML  = '<?php echo JText::_( 'No', true ); ?>';
                  }
                  else if( tpl_toFTP_enable.value == '1') {
                     document.getElementById("toFTP_enable_default").innerHTML  = '<?php echo JText::_( 'Yes', true ); ?>';
                  }
                  else {
                     document.getElementById("toFTP_enable_default").innerHTML  = '<?php echo JText::_( 'Default', true ); ?>';
                  }
                  
                  document.getElementById("toFTP_host_default").innerHTML    = json.toFTP_host;
                  document.getElementById("toFTP_port_default").innerHTML    = json.toFTP_port;
                  document.getElementById("toFTP_user_default").innerHTML    = json.toFTP_user;
                  document.getElementById("toFTP_psw_default").innerHTML     = json.toFTP_psw;
                  document.getElementById("toFTP_rootpath_default").innerHTML= json.toFTP_rootpath;

                  slider = document.getElementById("templates_dir_frame").getParent();
                  h = parseInt( slider.getStyle( 'height'));
                  if ( h != 0) slider.setStyle('height', 'auto'); // Fresh size
               }
               else {
                  document.getElementById("toFTP_enable_default").innerHTML = "Unexpected server response: " + replyStr;
               }
            }
            else {
               document.getElementById("media_dir_default").innerHTML = "Error code " + ajax.status;
               document.getElementById("images_dir_default").innerHTML = '';
               document.getElementById("templates_dir_default").innerHTML = '';
            }
            
            refreshShowFolders();
            refreshShowFTPFields();
         }
      };

      ajax.open( "GET", "index.php?option=com_multisites&task=ajaxGetTemplate&<?php echo J2WinUtility::getToken2Win() . '=1'; ?>&id="+template_id,  true);
      ajax.send(null);

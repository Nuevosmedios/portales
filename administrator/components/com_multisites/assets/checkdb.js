/**
 * @file       checkdb.js
 * @version    1.3.0
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2009 Edwin2Win sprlu - all right reserved.
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
 */


/**
 * JMS / Check DB / list
 */
var CheckDB = {
   execute: function()
   {
      var elt;
      var dbversion;
      var dbstatus;
      var i;
      // For each sites
      for ( i=0; ; i++)
      {
         try {
            elt = $('siteid_'+i);
            if ( elt == null) {
               break;
            }
            
            // Retreive the comment present in siteid
				var siteParams = new Array();

            var comment='';
            for (z=0; z<elt.childNodes.length; z++) {
               se = elt.childNodes[z];
               switch (se.nodeName) {
                  case '#comment': comment += se.nodeValue; break;
               }
            }

				// Parse the comment
				if (comment != '') {
					var bits = comment.split(';');
					for (z=0; z<bits.length; z++) {
						var pos = bits[z].indexOf('=');
						if ( pos > 0) {
						   siteParams['_'+ bits[z].substr( 0, pos).trim()] = bits[z].substr( pos+1).trim();
						}
					}
				}
				
            
            try {
               dbversion = $('dbversion_'+i);
               if ( dbversion != null) { dbversion.addClass('checkdb_processing'); }
         		
               dbstatus = $('dbstatus_'+i);
               if ( dbstatus != null) { dbstatus.addClass('checkdb_processing'); }
            }
            catch( e2) {}


				// Save the action to do
				var rc;
				var actionList = new Array();
				var topAction = siteParams;
				
				// Retry to see if there are multiple actions present in the dmresult
            var dmresult = $('dmresult_'+i);
            if ( dmresult != null) {
               var comments = dmresult.innerHTML.match( /<!\-\-([^<>]*)\-\->/g)
               var tr_siteElt   = dmresult;
               while ( tr_siteElt != null && tr_siteElt.tagName != 'TR') { tr_siteElt = tr_siteElt.getParent(); }
               tr_siteElt.addClass('action_processing');
               if ( comments != null) {
                  for ( var iCom=0; iCom<comments.length; iCom++) {
                     comment = comments[iCom].substr( 4, comments[iCom].length-7).trim();
         				// Parse the comment
         				if (comment != '') {
         				   siteParams = new Array();
         					var bits = comment.split(';');
         					for (z=0; z<bits.length; z++) {
         						var pos = bits[z].indexOf('=');
         						if ( pos > 0) {
         						   siteParams['_'+ bits[z].substr( 0, pos).trim()] = bits[z].substr( pos+1).trim();
         						}
         					}
   
                  	   // If siteParams[subresult_id] is present, change 
                  	   if ( siteParams['_subresult_id'] != null) {
                  	      subresultElt = $(siteParams['_subresult_id']);

                           var td_schemainfoElt = subresultElt;
                           while ( td_schemainfoElt != null && td_schemainfoElt.tagName != 'TD') { td_schemainfoElt = td_schemainfoElt.getParent(); }
                  	      if ( td_schemainfoElt != null) { td_schemainfoElt.addClass('action_processing'); }
                  	   }
   
            				// Append the action to do
            				actionList[actionList.length] = siteParams;
         				}
                  }
               }
            }
            
            // Finally process all the actions
            if ( topAction.length>0) {
				   rc = this.doActions( i, topAction);
            }
            for ( var j=0; j<actionList.length; j++) {
				   rc = this.doActions( i, actionList[j]);
				}
            if ( dmresult != null) {
               var tr_siteElt   = dmresult;
               while ( tr_siteElt != null && tr_siteElt.tagName != 'TR') { tr_siteElt = tr_siteElt.getParent(); }
               tr_siteElt.removeClass('action_processing');
            }
         }
         catch( e) {
            break;
         }
      } // Next site
   },

   checkReplies: function( ajax, siteNbr)
   {
      if(ajax.readyState  == 4)
      {
         if(ajax.status  == 200) {
            // Verify that we receive a JSON 'checkdb' type.
            if ( ajax.responseText.indexOf( '"type":"checkdb"') > 0) {
               // JSON decode
               var json = eval("(" + ajax.responseText + ")");
      		   // Success - Display the results
               try {
                  msg = '';
                  if ( json.errors == null) {}
                  else {
                     if ( json.errors.length <= 1) {
                        msg = json.errors[0];
                     }
                     else {
                        msg = '<ul>'+"\n";
                        for (var i=0; i<json.errors.length; i++) {
                           msg += '<li>' + json.errors[i] + '</li>' +"\n";
                        }
                        msg += '</ul>'+"\n";
                     }
                  }
                  var errmsgElt = $('errmsg_'+siteNbr);

                  var dmresultElt  = $('dmresult_'+siteNbr);
                  var subresultElt = null;
                  if ( json.subresult_id != null)  { subresultElt = $( json.subresult_id); }

                  var tr_siteElt   = dmresultElt;
                  while ( tr_siteElt != null && tr_siteElt.tagName != 'TR') { tr_siteElt = tr_siteElt.getParent(); }

                  var td_schemainfoElt = subresultElt;
                  while ( td_schemainfoElt != null && td_schemainfoElt.tagName != 'TD') { td_schemainfoElt = td_schemainfoElt.getParent(); }


                  // If a subresult is returned by the server and a SubResult element is found to display the result
                  if ( json.subresult != null && subresultElt != null) {
               		td_schemainfoElt.setStyle( 'background-color', null);
                     if ( msg.length>0)   { subresultElt.innerHTML   = json.subresult . msg;
                                            td_schemainfoElt.removeClass('action_processing').addClass('action_error');
                                          }
                     else                 { subresultElt.innerHTML   = json.subresult; 
                                            td_schemainfoElt.removeClass('action_processing').addClass('action_success');
                                          }
                  }
                  // If Subresult is returned but there is no SubResult element, try using the DataModel Result element to display the answer
                  else if ( json.subresult != null && dmresultElt != null) {
                     // SubResult + Error Message
                     if ( msg.length>0)   { dmresultElt.innerHTML   = json.subresult . msg;
                                            tr_siteElt.removeClass('action_processing').addClass('action_error');
                                          }
                     // SubResult and OK
                     else                 { dmresultElt.innerHTML   = json.subresult; 
                                            tr_siteElt.removeClass('action_processing').addClass('action_success');
                                          }
                  }
                  // If received an answer in the DataModel Result and that is must be displayed in the SubResult
                  else if ( json.dmresult != null && subresultElt != null) {
               		td_schemainfoElt.setStyle( 'background-color', null);

                     // DMResult + Error Message
                     if ( msg.length > 0) { subresultElt.innerHTML   = json.dmresult. msg; 
                                            td_schemainfoElt.removeClass('action_processing').addClass('action_error');
                                          }
                     // DMResult and OK
                     else                 { subresultElt.innerHTML   = json.dmresult;
                                            td_schemainfoElt.removeClass('action_processing').addClass('action_success');
                                          }
                  }
                  // If DataModel Result is returned but there is no SubResult element, try using the DataModel Result element to display the answer
                  else if ( json.dmresult != null && dmresultElt != null) {
                     dmresultElt.innerHTML   = json.dmresult;

                     if ( msg.length > 0) { if ( errmsgElt != null) { errmsgElt.innerHTML = msg; }
                                            tr_siteElt.removeClass('action_processing').addClass('action_error');
                                          }
                     else                 { tr_siteElt.removeClass('action_processing').addClass('action_success'); }
                  }
                  // Otherwise, update the class to mark that is completed
                  else {
                     if ( subresultElt != null)       { subresultElt.innerHTML   = ''; }
                     else if ( dmresultElt != null)   { dmresultElt.innerHTML   = ''; }
                     
                     if ( td_schemainfoElt != null)   { td_schemainfoElt.setStyle( 'background-color', null);
                                                        td_schemainfoElt.removeClass('action_processing').addClass('action_success'); 
                                                      }
                     if ( tr_siteElt != null)         { tr_siteElt.removeClass('action_processing').addClass('action_success'); }
                  }
               }
               catch( e3) {}
      		}
         	else {
               try {
                  var err    = $('err_'+siteNbr);
                  var errmsg = $('errmsg_'+siteNbr);
                  if ( err != null && errmsg != null) {
                     var reg=new RegExp("[|]+", "g");
                     var errors=ajax.responseText.split(reg);
                     if ( errors.length > 0) {
                        var msg = '';
                        var begin = 0;
                        if ( errors[0] == '[NOK]') begin = 1;
                        for (var i=begin; i<errors.length; i++) {
                           msg += '<li>' + errors[i] + '</li>' +"\n";
                        }
                        errmsg.innerHTML = '<ul>' + msg + '</ul>';
                        errmsg.getParent().getParent().removeClass('action_processing').addClass('action_error');
                        err.style.display = '';
                     }
                  }
               }
               catch( ee) {}
         	}
         }
         else {
            // Case FAIL
            var errmsg = $('errmsg_'+siteNbr);
            if ( errmsg != null) {
               errmsg.innerHTML = 'Unable to get the information. Error code='+ajax.status;
               errmsg.getParent().getParent().removeClass('action_processing').addClass('action_error');
            }
            var err    = $('err_'+siteNbr);
            if ( err != null) {
               err.style.display = '';
            }
         }
      }
   },
   
   doActions: function( siteNbr, siteParams)
   {
      var ajax;

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
      
      // Asynchronous processing
      var me = this
      ajax.onreadystatechange  = function() {
         me.checkReplies( ajax, siteNbr);
      };
      
      var param = "";
      var c;
      var value;
      for ( var key in siteParams) {
         c = key.charAt(0);
         if ( c == "\_") {
            value = siteParams[key];
            param += "&" + key.substring(1) + "="+encodeURIComponent(value);
            
            if ( key == '_subresult_id') {
               var subresult = $( value);
               var td_schemainfoElt = subresult;
               while ( td_schemainfoElt != null && td_schemainfoElt.tagName != 'TD') { td_schemainfoElt = td_schemainfoElt.getParent(); }
               subresult.innerHTML   = '<span class="processing">Processing ...</span>';
               td_schemainfoElt.addClass('action_processing');
         		td_schemainfoElt.setStyle( 'background-color', '#fe9c02');
            }
         }
      }

      var async = false;
      var com_option = "com_multisites";
      if ( g_checkdb_option != null) {
         com_option = g_checkdb_option;
      }
      ajax.open( "POST", "index.php?option="+com_option
                                +"&task=ajaxCheckDB"
                      , async);
      ajax.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' ); 
      ajax.send( g_curtoken+param);
      if ( !async) {
         // Synchronous processing
         this.checkReplies( ajax, siteNbr);
      }

      return true;
   } // end doActions
}; // End class CheckDB




window.addEvent('domready', function(){
     CheckDB.execute();
});

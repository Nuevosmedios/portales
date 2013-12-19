/**
* Catgory Refresh
*/

   function updateCategoryList( site_id, control_name, field_name)
   {
      var ajax;
      var elt;
      var fieldID = control_name + field_name;
      elt = document.getElementById( fieldID);

		// make sure is empty:
		elt.options.length=0;

		var opt = document.createElement("option");
		// set the value-attribute of it:
		opt.setAttribute('value',0);
		opt.innerHTML = 'Refreshing ...';

		elt.appendChild(opt);

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
            var showFolders = true;
            if(ajax.status  == 200) {
               var pos;
               var posEnd;
               posEnd = -1;
               pos = ajax.responseText.indexOf( '<select');
               // Check that select is present at the begining of the reply and not inside a html document
               // (case of the login form when session has expired)
               if ( pos>=0 && pos< 10) {
                  posEnd = ajax.responseText.indexOf( '</select>', pos);
                  if ( posEnd>=0) {
                     var text = ajax.responseText.substring( pos, posEnd+9);
                     
                     var previousPos = 0;
                     var p1;
                     var p2;
                     var p3;
                     var p4;
                     var p5;
                     var c;
                     var state;
                     var valueStr;
               		elt.options.length=0;
               		
                     while( true) {
                        p1=text.indexOf( '<option', previousPos);
                        if ( p1<=0) {
                           break;
                        }
                        p2 = text.indexOf( 'value', p1);
                        p3 = text.indexOf( '=', p2);
                        p4 = text.indexOf( '>', p3);
                        p5 = text.indexOf( '</option>', p4);
                        state = 0;
                        valueStr = "";
                        for (  i=p3+1; i<p4; i++) {
                           c = text.charAt( i);
                           if ( state == 0) {
                              if ( c == " ") {}
                              else if ( c== "\"" || c == "'") {
                                 state = 1;
                              }
                              else {
                                 state = 2;
                              }
                           }
                           else if ( state == 1) {
                              if ( c == "\"" || c == "'") {
                                 break;
                              }
                              else {
                                 valueStr += c;
                              }
                           }
                           else if ( state == 2) {
                              if ( c == " ") {
                                 break;
                              }
                              else {
                                 valueStr += c;
                              }
                           }
                        } // End for
                  		opt = document.createElement("option");
                  		opt.setAttribute('value',valueStr);
                  		opt.innerHTML = text.substring( p4+1, p5);;
                  		elt.appendChild(opt);
                  		
                  		// Next Option
                        if ( p5 > previousPos) {
                           previousPos = p5;
                        }
                        else {
                           previousPos++;
                        }
                     } // End While
                  }
               }
               if ( posEnd<0) {
            		elt.options.length=0;
            		opt = document.createElement("option");
            		opt.setAttribute('value','0');
            		opt.innerHTML = 'Unexpected XML response';
            		elt.appendChild(opt);

            		opt = document.createElement("option");
            		opt.setAttribute('value','0');
            		opt.innerHTML = ajax.responseText;
            		elt.appendChild(opt);
               }
            }
            else {
         		elt.options.length=0;
         		opt = document.createElement("option");
         		opt.setAttribute('value','0');
         		opt.innerHTML = 'Error code ' + ajax.status;
         		elt.appendChild(opt);
            }
         }
      };

      ajax.open( "GET", "index.php?option=com_multisitescontent&task=ajaxGetCategoryList"
                      + "&site_id="+site_id
                      + "&control_name="+control_name
                      + "&name="+field_name
                      ,  true);
      ajax.send(null);
   }

/**
* Article Refresh
*/

   function updateArticle16List( site_id, id_name, field_url)
   {
      var elt_msg;
      var elt;

      // Update the URL with the site_id parameter
      elt     = document.getElementById( field_url);
      var href = elt.getAttribute("href");
      // If the parameter &site_id is present, replace its value
      pos = href.indexOf( "&amp;site_id=");
      if ( pos>=0) {
         href = href.substring( 0, pos) + "&site_id=" + site_id;
      }
      else {
         pos = href.indexOf( "&site_id=");
         if ( pos>=0) {
            href = href.substring( 0, pos) + "&site_id=" + site_id;
         }
         else {
            href = href + "&site_id=" + site_id;
         }
      }
      elt.setAttribute("href",href);

      elt_msg = document.getElementById( id_name);
      elt_msg.setAttribute("value","Select an Article");
   }

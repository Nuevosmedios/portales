/**
* Get the layout parameters
*/

   function updateDomains( action_value, control_name, domains_name, domain_additional_name)
   {
      var eltDomains = document.getElementById( control_name + domains_name);
      var eltDomainAdditional = document.getElementById( control_name + domain_additional_name);
      
      if ( action_value == 'automatic') {
         // Disable the fields domain_name and domain_additional
         try {
            eltDomains.readOnly = true;
            eltDomains.disabled = true;
         }
         catch( e2) {}

         try {
            eltDomainAdditional.readOnly = true;
            eltDomainAdditional.disabled = true;
         }
         catch( e2) {}
      }
      else {
         // Enable the fields domain_name and domain_additional
         try {
            eltDomains.readOnly = false;
            eltDomains.disabled = false;
         }
         catch( e2) {}

         try {
            eltDomainAdditional.readOnly = false;
            eltDomainAdditional.disabled = false;
         }
         catch( e2) {}
      }
   }

<?php
/**
 * @file       controller.php
 * @brief      Front-end that allow to create dynamic slave sites.
 * @version    1.2.90
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2011 Edwin2Win sprlu - all right reserved.
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
 * @par History:
 * - V1.1.0 11-OCT-2008: File creation
 * - V1.1.3 02-DEC-2008: Replace getString by getCmd when reading the site ID to avoid special characters and the spaces.
 *                       Some customer are using spaces in the name of a site id.
 * - V1.1.5 13-DEC-2008: Rebuid the master index when a slave site is deleted
 * - V1.1.8 26-DEC-2008: Add the ItemId in the redirection URL to allow correctly display the "delete" button.
 *                       This ItemId is used to retreive the context and therefore return the correct getParams() values.
 *                       When not present, this return the website default values and not the menu type specific values.
 *                       Also add a cancel function to redirect on the list with appropriate ItemId value.
 * - V1.2.7 29-SEP-2009: Add a redirection URL processing when the action is performed.
 *                       Also give the possibility to directly call the "Add" slave directly without the "list".
 * - V1.2.14 05-DEC-2009: Add Joomla 1.6 alpha 2 compatibility.
 * - V1.2.20 01-FEB-2010: Add the possibility to get dynamic CSS (php).
 * - V1.2.29 10-MAY-2010: Add On Error redirection URL processing.
 * - V1.2.36 07-SEP-2010: Remove a PHP warning message.
 *                        Modify the URL computation when SEF is enabled.
 *                        Compute the internal redirection URL using the itemid when SEF is enabled.
 * - V1.2.51 17-MAY-2011: Add {site_prefix} and {site_alias} keywords in the "Redirect On Save" parameter.
 * - V1.2.52 18-MAY-2011: Fix a bug introduced in 1.2.51 that does not allow saving the slave site correctly..
 * - V1.2.69 14-NOV-2011: Also return the "Bridge VM" product info when calling the ajaxGetTemplateDescr().
 *                        So that it is possible to display product price, currency, ... in the front end.
 * - V1.2.70 13-DEC-2011: Add a check that the "Itemid" exists when computing the getListURL() value.
 * - V1.2.84 05-APR-2012: Fix redirection to the On Error URL when the save return an error message.
 * - V1.2.86 16-APR-2012: Add the processing of {site_id} keyword in redirect_URL (onOK or onError) when saving the slave site
 *                        Add also the keyword {task_referer} and {error_code}
 * - V1.2.87 23-APR-2012: Fix the success message display and also put error message in "red"
 * - V1.2.90 07-JUN-2012: Fix layout computation value under joomla 2.5
 *             
 */


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.controller');

// ===========================================================
//             MultisitesController class
// ===========================================================
class MultisitesController extends JController
{
   //------------ display ---------------
	/**
	 * @brief Display the list of websites created by the user
	 */
	function display()
	{
	   $layout = JRequest::getString('layout', '');
	   if ( $layout == 'edit') {
	      $this->addSlave();
	   }
	   else {
   		$model	=& $this->getModel( 'Slaves');
   		$view    =& $this->getView( 'Slaves');
   		$view->setModel( $model, true );
   
   		// Add a second model that is used to compute the lists
   		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
   		$modelTemplates	=& $this->getModel( 'Templates' );
   		$view->setModel( $modelTemplates);
   
   		
   		$view->display();
	   }
	   
	}
	
   //------------ _getListURL ---------------
   /**
    * @brief Compute the URL differently when the SEF is enabled.
    */
	function _getListURL()
	{
	   $Itemid = JRequest::getInt('Itemid');

	   if ( !empty( $Itemid)) {
   	   // When SEF is enabled, just use the Itemid that will be resolved with the alias by the JRoute::_()
   	   // Otherwise, keep the orginal URL.
   	   $url = JROUTER_MODE_SEF ? 'index.php?Itemid='.$Itemid
   	                           : 'index.php?option=com_multisites&view=slaves&layout=list&Itemid='.$Itemid
   	                           ;
	   }
	   else {
   	   $url = 'index.php?option=com_multisites&view=slaves&layout=list';
	   }
	   return JRoute::_( $url, false);
	}

	//------------ getParams ---------------
   /**
    * @brief Return the module parameters or component parameters.
    */
	function &getParams()
	{
	   static $instance;
	   
	   if ( empty( $instance)) {
   		$mainframe	= &JFactory::getApplication();
   		
   		// If a "Multisite Create Site" module ID is present,
   		$module_id = JRequest::getInt('module_id', 0);
   		if ( !empty( $module_id)) {
   		   // read the parameters from the module
      		$table = JTable::getInstance('Module');
      		if ( $table->load( $module_id)) {
               if ( version_compare( JVERSION, '1.6') >= 0) {
            		// Get module parameters
            		$instance = new JRegistry;
            		$instance->loadString( $table->params);
               }
         		else {
      		      $instance = new JParameter( $table->params );
         		}
      		}
   		}
   		// When there is no "module" parameters
   		if ( empty( $instance)) {	
   		   // read the parameters from the component
      	   $instance = &$mainframe->getParams();
      	}
	   }
	   
	   return $instance;
	}
	
   //------------ _getRedirectOnSave ---------------
   /**
    * @brief Returns the "redirect_onSave" configuration parameter after its evaluation
    */
	function _getRedirectOnSave()
	{
		$params	         = &$this->getParams();
		$redirect_onSave  = $params->get('redirect_onSave');
		if ( !empty( $redirect_onSave)) {
		   if ( !empty( $this->enteredvalues)) {
		      $enteredvalues = $this->enteredvalues;
		   }
		   else {
      		$enteredvalues = array();
      		$enteredvalues['site_prefix']    = JRequest::getCmd('site_prefix', null);
      		$enteredvalues['site_alias']     = JRequest::getCmd('site_alias', null);
      	}

	      $redirect_onSave = MultisitesDatabase::evalStr( $redirect_onSave, '', '', '', $enteredvalues);
	      
	      $redirect_onSave = str_replace( '{task_referer}', JRequest::getCmd('task', null), $redirect_onSave);
		}
		
		return $redirect_onSave;
	}

   //------------ cancel ---------------
   /**
    * @brief Cancel redirect to the list using the "ItemId" to display the correct buttons
    */
	function cancel()
	{
		$redirect_onSave  = $this->_getRedirectOnSave();
		if ( !empty( $redirect_onSave)) {
   		$this->setRedirect( $redirect_onSave, $msg);
		}
		else {
   		$this->setRedirect( $this->_getListURL());
   	}
	}

   //------------ addSlave ---------------
   /**
    * @brief Add a new slave site instances.
    * This operation will create a sub-directory in 'multisites' directory.
    * The name of the sub-directory is the site ID.
    */
	function addSlave()
	{
		$mainframe	= &JFactory::getApplication();
		
		$model	=& $this->getModel( 'Slaves' );
		$view    =& $this->getView( 'Slaves');
		$view->setModel( $model, true );

		// Add a second model that is used to compute the lists
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$modelTemplates	=& $this->getModel( 'Templates' );
		$view->setModel( $modelTemplates);

		$msg = $view->editForm(false,null);
		if ( !empty( $msg)) {
   		$redirect_onSave  = $this->_getRedirectOnSave();
   		if ( !empty( $redirect_onSave)) {
      		$this->setRedirect( $redirect_onSave, $msg);
   		}
   		else {
      		$this->setRedirect( $this->_getListURL(), $msg);
      	}
		}
	}

	
   //------------ editSlave ---------------
   /**
    * @brief Edit a specific site instances.
    */
	function editSlave()
	{
		$mainframe	= &JFactory::getApplication();
		
		$model	=& $this->getModel( 'Slaves' );
		$view    =& $this->getView( 'Slaves');
		$view->setModel( $model, true );

		// Add a second model that is used to compute the lists
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$modelTemplates	=& $this->getModel( 'Templates' );
		$view->setModel( $modelTemplates);


		$msg = $view->editForm( true);
		if ( !empty( $msg)) {
   		$redirect_onSave  = $this->_getRedirectOnSave();
   		if ( !empty( $redirect_onSave)) {
      		$this->setRedirect( $redirect_onSave, $msg);
   		}
   		else {
      		$this->setRedirect( $this->_getListURL(), $msg);
      	}
		}
	}


   //------------ showDetail ---------------
   /**
    * @brief Edit a specific site instances.
    * This allow to update the list of domain attached to the site.
    */
	function showSlave()
	{
		$mainframe	= &JFactory::getApplication();
		
		$model	=& $this->getModel( 'Slaves' );
		$view    =& $this->getView( 'Slaves');
		$view->setModel( $model, true );

		// Add a second model that is used to compute the lists
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$modelTemplates	=& $this->getModel( 'Templates' );
		$view->setModel( $modelTemplates);


		$msg = $view->showForm();
		if ( !empty( $msg)) {
   		$redirect_onSave  = $this->_getRedirectOnSave();
   		if ( !empty( $redirect_onSave)) {
      		$this->setRedirect( $redirect_onSave, $msg);
   		}
   		else {
      		$this->setRedirect( $this->_getListURL(), $msg);
      	}
		}
	}

   //------------ saveSlave ---------------
   /**
    * @brief Save a slave site
    */
	function saveSlave()
	{
		$mainframe	= &JFactory::getApplication();

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$model	=& $this->getModel( 'Manage' );
		$view    =& $this->getView( 'Slaves');
		$view->setModel( $model, true );
		$msg = $view->saveSlave();
		if ( !empty( $view->enteredvalues)) {
		   $this->enteredvalues = $view->enteredvalues;
		}
		
		$params	         = &$this->getParams();
		$redirect_onSave  = $this->_getRedirectOnSave();
		// If success
		if ( empty( $msg)) {
		   // If a redirect onSuccess URL is present
		   if ( !empty( $redirect_onSave)) {
		      $this->setRedirect( $redirect_onSave, $msg);
		   }
		}
		// If error
		else {
   		$redirect_onError  = $params->get('redirect_onError');
   		if ( !empty( $redirect_onError)) {
   		   if ( !empty( $this->enteredvalues)) {
   		      $enteredvalues = $this->enteredvalues;
   		   }
   		   else {
         		$enteredvalues = array();
         		$enteredvalues['site_prefix']    = JRequest::getCmd('site_prefix', null);
         		$enteredvalues['site_alias']     = JRequest::getString('site_alias', null);
         	}
   
   	      $redirect_onError = MultisitesDatabase::evalStr( $redirect_onError, '', '', '', $enteredvalues);
   	      $error_code = !empty( $enteredvalues['error_code']) ? $enteredvalues['error_code'] : '';
   	      $redirect_onError = str_replace( array( '{task_referer}', '{error_code}'), 
   	                                       array( JRequest::getCmd('task', null), $error_code),
   	                                       $redirect_onError
   	                                     );

      		$this->setRedirect( $redirect_onError, $msg, 'error');
   		}
   		else {
      		$this->setRedirect( $this->_getListURL(), $msg, 'error');
   		}
   	}
	}

   //------------ paySlave ---------------
   /**
    * @brief Buy or Renew a website
    */
	function paySlave()
	{
		$mainframe	= &JFactory::getApplication();
		
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$model	=& $this->getModel( 'Manage' );
		$view    =& $this->getView( 'Slaves');
		$view->setModel( $model, true );
		$msg = $view->paySlave();

		$redirect_onSave  = $this->_getRedirectOnSave();
		if ( !empty( $redirect_onSave)) {
   		$this->setRedirect( $redirect_onSave, $msg);
		}
		else {
   		$this->setRedirect( $this->_getListURL(), $msg);
		}
	}

   //------------ deleteSlave ---------------
	/**
	 * Request confirmation before deletion of the site.
	 * When this is confirmed, this call doDeleteSite.
	 */
	function deleteSlave()
	{
		$model	=& $this->getModel( 'Slaves' );
		$view    =& $this->getView( 'Slaves');
		$view->setModel( $model, true );
		$view->deleteForm();
	}


   //------------ doDeleteSlave ---------------
	/**
	 * Perform the deletion of the site.
	 */
	function doDeleteSlave()
	{
		$mainframe	= &JFactory::getApplication();
   	$option = JRequest::getCmd('option');
	   $Itemid = JRequest::getInt('Itemid');
	   
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );
		$redirect_onSave  = $this->_getRedirectOnSave();

		$id = JRequest::getVar( 'id', false, '', 'cmd' );
		if ($id === false) {
			JError::raiseWarning( 500, JText::_( 'Invalid ID provided' ) );
   		if ( !empty( $redirect_onSave)) {
      		$this->setRedirect( $redirect_onSave);
   		}
   		else {
   			$this->setRedirect( $this->_getListURL() );
   		}
			return false;
		}

		$model =& $this->getModel( 'Slaves' );
		
      JPluginHelper::importPlugin('multisites');
      $results = $mainframe->triggerEvent('onBeforeDeleteSlave', array ( $id, &$model));
      
		if (!$model->canDelete()) {
			JError::raiseWarning( 500, $model->getError() );
   		if ( !empty( $redirect_onSave)) {
      		$this->setRedirect( $redirect_onSave);
   		}
   		else {
   			$this->setRedirect( $this->_getListURL());
   		}
			$rc = false;
		}
		else {
   		$err = null;
   		if (!$model->delete()) {
   			 $err = $model->getError();
   		}
   		
   		// Re-create the master index containing all the host name and associated directories
   		$model->createMasterIndex();
   		
   		if ( !empty( $redirect_onSave)) {
      		$this->setRedirect( $redirect_onSave, $err);
   		}
   		else {
      		$this->setRedirect( $this->_getListURL(), $err );
      	}
   		$rc = true;
   	}
   	
      JPluginHelper::importPlugin('multisites');
      $results = $mainframe->triggerEvent('onAfterDeleteSlave', array ( $id, &$model));
      
		if ( !empty( $redirect_onSave)) {
   		$this->setRedirect( $redirect_onSave);
		}
		else {
   		$this->setRedirect( $this->_getListURL());
   	}
   	return $rc;
	}

   // -------------- ajaxGetTemplateDescr ------------------------------
   // request : id = template identifier
   function ajaxGetTemplateDescr()
   {
		// Check for request forgeries
		JRequest::checkToken( 'get') or jexit( 'Invalid Token' );

		// Load the template based on its id
		$this->addModelPath( JPATH_COMPONENT_ADMINISTRATOR.DS.'models');
		$model =& $this->getModel( 'Templates' );
		$template = $model->getCurrentRecord();
		if (!$template) {
   		jexit( '<error>' . JText::_( 'TEMPLATE_NOT_FOUND') . '</error>');
		}
		$result = 'templateInfo'
		        . '|' . $template->id
		        . '|' . $template->title
		        . '|' . $template->description
		        . '|' . $template->validity
		        . '|' . $template->validity_unit
		        . '|' . $template->sku
		        ;

      // When there is a sku value, try to call the Billable Plugin to get the detailed product information
      // (name, title, short description, full description, price, currency, ...)
      if ( !empty( $template->sku)) {
         JPluginHelper::importPlugin('multisites');
		   JFactory::getApplication()->triggerEvent('getProductInfo', array ( & $template));
      }

	   $result .= '|' . (!empty( $template->product_id)         ? $template->product_id : '')
	            . '|' . (!empty( $template->product_name)       ? $template->product_name : '')
	            . '|' . (!empty( $template->product_title)      ? $template->product_title : '')
	            . '|' . (!empty( $template->product_shortdescr) ? $template->product_shortdescr : '')
	            . '|' . (!empty( $template->product_descr)      ? $template->product_descr : '')
	            . '|' . (!empty( $template->product_price)      ? $template->product_price : '')
	            . '|' . (!empty( $template->product_currency)   ? $template->product_currency : '')
	            ;

		jexit( $result);
   }


   // -------------- getCSS ------------------------------
   /**
    * @brief Get Dynamic CSS
    * This can be a ".css" or ".css.php" or ".php" file.
    */
   function getCSS()
   {
		$mainframe	= &JFactory::getApplication();
		$params	   = &$mainframe->getParams();

   	$option     = JRequest::getCmd('option');
	   $name       = JRequest::getCmd('name');
	   
		$layout = $params->get('jmslayout');
   	if ( is_array( $layout)) {
   	   $layout = implode( '', $layout);
   	}
      else if ( is_object( $layout) && !empty( $layout->value)) {
         $layout = $layout->value;
      }
		if ( empty( $layout) || $layout == ':select:' || $layout == ':default:') {
		   // Do nothing
   		$result = '';
		}
		else {
   	   $cssFileName = dirname( __FILE__) .DS. 'templates' .DS. $layout .DS. 'css' .DS. $name;
   	   
   	   if ( JFile::exists( $cssFileName . '.css')) {
   	      $cssFileName .= '.css';
   	      $result = JFile::read( $cssFileName);
   	   }
   	   else {
   	      if ( JFile::exists( $cssFileName . '.php'))      { $cssFileName .= '.php'; }
   	      else if ( JFile::exists( $cssFileName . '.css.php'))  { $cssFileName .= '.css.php'; }
   	      
      		ob_start();
      		// include the requested "css" filename in the local scope
      		@include $cssFileName;
      
      		// done with the requested CSS; get the buffer and clear it.
      		$result = ob_get_contents();
      		ob_end_clean();
   	   }

		}

      if ( !empty( $result)) {
         // Build the text/css header
         $age = 604800;
         $file = $cssFileName;
         header( 'Expires: '.gmdate( 'D, d M Y H:i:s', time()+ $age ) . ' GMT' );
         header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', @filemtime( $file ) ) . ' GMT' );
         header( 'Cache-Control: public, max-age='.$age.', must-revalidate, post-check=0, pre-check=0' );
         header( 'Pragma: public' );
         header( 'Content-Type: text/css');
      }
      
		jexit( $result);
   }

} // End Class

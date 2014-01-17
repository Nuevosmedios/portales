<?php
/**
 * @version		3.1.114 administrator/components/com_j2xml/models/cpanel/view.html.php
 * 
 * @package		J2XML
 * @subpackage	com_j2xml
 * @since		1.5.3
 * 
 * @author		Helios Ciancio <info@eshiol.it>
 * @link		http://www.eshiol.it
 * @copyright	Copyright (C) 2010-2013 Helios Ciancio. All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL v3
 * J2XML is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// No direct access
defined('_JEXEC') or die;

// no direct access
defined('_JEXEC') or die('Restricted access.');

jimport('joomla.html.html.tabs');

class J2XMLViewCpanel extends JViewAbstract
{
	function display($tpl = null)
	{
		$info = $this->get('Info');
		$this->assignRef('info', $info);
		$params = JComponentHelper::getParams('com_j2xml');
		$this->assignRef('params', $params);

		if (version_compare(JPlatform::RELEASE, '12', 'ge'))
		{
			J2XMLHelper::addSubmenu('cpanel');
			$this->sidebar = JHtmlSidebar::render();
		}
		$this->addToolbar();		
		parent::display($tpl);
	}

	function _quickiconButton( $link, $image, $text, $path=null, $target='', $onclick='' ) {
		$app = JFactory::getApplication('administrator');
		if( $target != '' ) {
			$target = 'target="' .$target. '"';
		}
		if( $onclick != '' ) {
			$onclick = 'onclick="' .$onclick. '"';
		}
		if( $path === null || $path === '' ) {
			$template = $app->getTemplate();
			$path = '/templates/'. $template .'/images/header/';
		}

		$lang = JFactory::getLanguage();
		
		if (version_compare(JPlatform::RELEASE, '12', '<'))
		{
		?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a href="<?php echo $link; ?>" <?php echo $target;?>  <?php echo $onclick;?>>
					<?php echo JHTML::_('image.administrator', $image, $path, NULL, NULL, $text ); ?>
					<span><?php echo $text; ?></span>
				</a>
			</div>
		</div>
		<?php 
		}
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		require_once JPATH_COMPONENT.'/helpers/j2xml.php';
		$canDo	= j2xmlHelper::getActions();
		
		$toolbar = JToolBar::getInstance('toolbar');
		$toolbar->addButtonPath(JPATH_COMPONENT.DS.'buttons');
		
		$doc = JFactory::getDocument();
		if (version_compare(JPlatform::RELEASE, '12', 'lt'))
		{
			$icon_48_j2xml = " .icon-48-j2xml {background:url(../media/com_j2xml/images/icon-48-j2xml.png) no-repeat; }"; 
			$doc->addStyleDeclaration($icon_48_j2xml);
			$icon_32_import = " .icon-32-j2xml-import {background:url(../media/com_j2xml/images/icon-32-import.png) no-repeat; }"; 
			$doc->addStyleDeclaration($icon_32_import);
			$icon_32_open = " .icon-32-j2xml-open {background:url(../media/com_j2xml/images/icon-32-open.png) no-repeat; }";
			$doc->addStyleDeclaration($icon_32_open);
		}
		JToolBarHelper::title(JText::_('COM_J2XML_TOOLBAR_J2XML'), 'j2xml.png');
		if ($canDo->get('core.create') || ($canDo->get('core.edit'))) {
			$toolbar->loadButtonType('Import', true);
			$toolbar->appendButton('Import', 'j2xml', 'COM_J2XML_BUTTON_OPEN', 'COM_J2XML_BUTTON_IMPORT', 'cpanel.import');
			JToolBarHelper::divider();
		}	
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_j2xml');
		}		
	}
}
?>
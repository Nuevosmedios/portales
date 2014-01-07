<?php
/**
 * @version		$Id: helper.php 3411 2013-07-23 15:05:35Z joomlaworks $
 * @package		Frontpage Slideshow
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modeladmin');

class FPSSModelHelper extends JModelAdmin
{

	protected function loadFormData()
	{
		$data = $this->getItem();
		return $data;
	}

	public function getTable($type = 'category', $prefix = 'FPSS', $config = array())
	{
		$type = $this->get('assetType');
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true)
	{
		JForm::addFormPath(JPATH_COMPONENT.'/models');
		$form = $this->loadForm('com_fpss.'.$this->get('assetType'), $this->get('assetType'), array(
			'control' => '',
			'load_data' => $loadData
		));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

}

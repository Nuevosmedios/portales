<?php
/**
 * @version		$Id: category.php 3411 2013-07-23 15:05:35Z joomlaworks $
 * @package		Frontpage Slideshow
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class FPSSModelCategory extends FPSSModel
{

	function getData()
	{
		$id = (int)$this->getState('id');
		$row = JTable::getInstance('category', 'FPSS');
		$row->load($id);
		return $row;
	}

	function save()
	{
		$row = JTable::getInstance('category', 'FPSS');
		if (!$row->bind($this->getState('data')))
		{
			$this->setError($row->getError());
			return false;
		}
		if (!$row->check())
		{
			$this->setError($row->getError());
			return false;
		}
		if (!$row->id)
		{
			$row->ordering = $row->getNextOrder();
		}
		if (!$row->store())
		{
			$this->setError($row->getError());
			return false;
		}
		$this->setState('id', $row->id);
		return true;
	}

}

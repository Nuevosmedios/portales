<?php
/**
 * @version		$Id: redshop.php 2931 2013-04-24 12:21:46Z lefteris.kavadas $
 * @package		Frontpage Slideshow
 * @author		JoomlaWorks http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2013 JoomlaWorks Ltd. All rights reserved.
 * @license		http://www.joomlaworks.net/license
 */

// no direct access
defined('_JEXEC') or die ;

class FPSSModelHikashop extends FPSSModel
{
	public function getData()
	{
		$db = $this->getDBO();
		$query = 'SELECT product.product_id, product.product_name, product.product_code, product.product_published, product.product_description, category.category_name 
		FROM #__hikashop_product AS product 
		LEFT JOIN #__hikashop_product_category AS xref ON product.product_id = xref.product_id
		LEFT JOIN #__hikashop_category AS category ON xref.category_id = category.category_id';
		$this->setQueryConditions($query);
		$query .= ' GROUP BY product.product_id ';
		if ($this->getState('sorting'))
		{
			$query .= ' ORDER BY '.$this->getState('sorting');
		}
		$db->setQuery($query, (int)$this->getState('limitstart'), (int)$this->getState('limit'));
		$rows = $db->loadObjectList();
		return $rows;
	}

	public function getTotal()
	{
		$db = $this->getDBO();
		$query = 'SELECT COUNT(DISTINCT product.product_id), category.category_name 
		FROM #__hikashop_product AS product 
		LEFT JOIN #__hikashop_product_category AS xref ON product.product_id = xref.product_id 
		LEFT JOIN #__hikashop_category AS category ON xref.category_id = category.category_id';
		$this->setQueryConditions($query);
		$db->setQuery($query);
		$total = $db->loadresult();
		return $total;
	}

	protected function setQueryConditions(&$query)
	{
		$db = $this->getDBO();
		$conditions = array();
		$published = $this->getState('published');
		if ($published != '')
		{
			$conditions[] = 'product.product_published = '.(int)$published;
		}
		if ($category = $this->getState('category'))
		{
			$conditions[] = 'xref.category_id = '.(int)$category;
		}
		if ($this->getState('search'))
		{
			$search = JString::trim($this->getState('search'));
			$search = JString::strtolower($search);
			if ($search)
			{
				$search = $db->escape($search, true);
				$conditions[] = '( 
				LOWER(product.product_name) LIKE '.$db->Quote('%'.$search.'%', false).' OR 
				product.product_code LIKE '.$db->Quote('%'.$search.'%', false).')';
			}
		}
		if (count($conditions))
		{
			$query .= ' WHERE '.implode(' AND ', $conditions);
		}
	}

	public function getCategories()
	{
		$db = $this->getDBO();
		$query = 'SELECT category.category_id AS value, category.category_name AS text, category.category_parent_id AS parent_id FROM #__hikashop_category AS category 
		WHERE category.category_type = '.$db->quote('product').' ORDER BY category.category_parent_id ASC, category.category_name ASC';

		$db->setQuery($query);
		$categories = $db->loadObjectList();
		$children = array();
		foreach ($categories as $category)
		{
			if(version_compare(JVERSION, '1.6', 'lt'))
			{
				$category->parent = $category->parent_id;
				$category->name = $category->text;
			}
			$category->id = $category->value;
			$category->title = $category->text;
			$index = $category->parent_id;
			$list = @$children[$index] ? $children[$index] : array();
			array_push($list, $category);
			$children[$index] = $list;
		}
		$tree = JHTML::_('menu.treerecurse', 1, '', array(), $children, 9999, 0, 0);
		$options = array();
		foreach ($tree as $item)
		{
			$options[] = JHTML::_('select.option', $item->id, $item->treename);
		}
		return $options;
	}

	public function getRowImage()
	{
		$db = $this->getDBO();
		$query = 'SELECT file.file_path FROM #__hikashop_file AS file 
		WHERE file.file_type = '.$db->quote('product').' AND file.file_ref_id = '.(int)$this->getState('id').' ORDER BY file.file_ordering';
		$db->setQuery($query, 0, 1);
		$image = $db->loadResult();
		return $image;
	}

	public function getImagePath()
	{
		$db = $this->getDBO();
		$query = 'SELECT config_value FROM #__hikashop_config WHERE config_namekey = '.$db->quote('uploadfolder');
		$db->setQuery($query);
		$path = $db->loadResult();
		return $path;
	}

}

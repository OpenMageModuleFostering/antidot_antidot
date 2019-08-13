<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Antidot_Model_Resource_Catalog_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * @var MDN_Antidot_Model_Resource_Engine_Abstract Search engine.
     */
    protected $_engine;

    /**
     * @var array Faceted data.
     */
    protected $_facetedData = false;
    
    /**
     * @var array Categories ids
     */
    protected $_categoryIds = false;

    /**
     * @var array Facets conditions.
     */
    protected $_facetsConditions = array();

    /**
     * @var string Search query text.
     */
    protected $_searchQueryText = '';

    /**
     * @var array Search query filters.
     */
    protected $_searchQueryFilters = array();

    /**
     * @var array Search entity ids.
     */
    protected $_searchedEntityIds = array();

    /**
     * @var array Sort by definition.
     */
    protected $_sortBy = array();

    /**
     * @var array Request params
     */
    protected $_params = null;

    /**
     * @var array Query result
     */
    protected $queryResult = null;

    /**
     * Adds facet condition to current collection.
     *
     * @param string $field
     * @param mixed $condition
     * @return MDN_Antidot_Model_Resource_Catalog_Product_Collection
     */
    public function addFacetCondition($field, $condition = null)
    {
        if (array_key_exists($field, $this->_facetsConditions)) {
            if (!empty($this->_facetsConditions[$field])){
                $this->_facetsConditions[$field] = array($this->_facetsConditions[$field]);
            }
            $this->_facetsConditions[$field][] = $condition;
        } else {
            $this->_facetsConditions[$field] = $condition;
        }

        return $this;
    }

    /**
     * Add some fields to filter.
     *
     * @param $fields
     * @return MDN_Antidot_Model_Resource_Catalog_Product_Collection
     */
    public function addFieldsToFilter($fields)
    {
        return $this;
    }

    /**
     * Stores filter query.
     *
     * @param array $params
     * @return MDN_Antidot_Model_Resource_Catalog_Product_Collection
     */
    public function addFqFilter($params)
    {
        if (is_array($params)) {
            foreach ($params as $field => $value) {
                $this->_searchQueryFilters[$field] = $value;
            }
        }

        return $this;
    }

    /**
     * Stores query text filter.
     *
     * @param $query
     * @return MDN_Antidot_Model_Resource_Catalog_Product_Collection
     */
    public function addSearchFilter($query)
    {
        $this->_searchQueryText = $query;

        return $this;
    }

    /**
     * Stores search query filter.
     *
     * @param mixed $param
     * @param null $value
     * @return MDN_Antidot_Model_Resource_Catalog_Product_Collection
     */
    public function addSearchQfFilter($param, $value = null)
    {
        if (is_array($param)) {
            foreach ($param as $field => $value) {
                $this->addSearchQfFilter($field, $value);
            }
        } elseif (isset($value)) {
            if (isset($this->_searchQueryFilters[$param]) && !is_array($this->_searchQueryFilters[$param])) {
                $this->_searchQueryFilters[$param] = array($this->_searchQueryFilters[$param]);
                $this->_searchQueryFilters[$param][] = $value;
            } else {
                $this->_searchQueryFilters[$param] = $value;
            }
        }

        return $this;
    }

    /**
     * Aggregates search query filters.
     *
     * @return array
     */
    public function getExtendedSearchParams()
    {
        $result = $this->_searchQueryFilters;
        $result['query_text'] = $this->_searchQueryText;

        return $result;
    }
    
    /**
     * Returns facet data
     * 
     * @return array
     */
    public function getFacets()
    {
        if($this->_facetedData === false) {
            $this->getSize();
        }
        
        return $this->_facetedData;
    }

    /**
     * Returns faceted data.
     *
     * @param string $field
     * @return array
     */
    public function getFacetedData($field)
    {
        $this->initQueryResult($this->_getQuery(), $this->_getParams());
        
        if (array_key_exists($field, $this->_facetedData)) {
            return $this->_facetedData[$field];
        }

        return array();
    }
    
    /**
     * Returh the category ids
     * 
     * @return array
     */
    public function getCategoryIds()
    {
        return $this->_categoryIds;
    }

    /**
     * Returns collection size
     *
     * @return int
     */
    public function getSize()
    {
        $this->initQueryResult($this->_getQuery(), $this->_getParams());
        
        return $this->_engine->getLastNumFound();
    }

    /**
     * Defines current search engine.
     *
     * @param MDN_Antidot_Model_Resource_Engine_Abstract $engine
     * @return MDN_Antidot_Model_Resource_Catalog_Product_Collection
     */
    public function setEngine(MDN_Antidot_Model_Resource_Engine_Abstract $engine)
    {
        $this->_engine = $engine;

        return $this;
    }

    /**
     * Stores sort order.
     *
     * @param string $attribute
     * @param string $dir
     * @return MDN_Antidot_Model_Resource_Catalog_Product_Collection
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_DESC)
    {
        $this->_sortBy[] = array($attribute => $dir);

        return $this;
    }
    
    /**
     * Reorder collection according to current sort order.
     *
     * @return MDN_Antidot_Model_Resource_Catalog_Product_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        
        if (!empty($this->_searchedEntityIds)) {
            $sortedItems = array();
            foreach ($this->_searchedEntityIds as $id) {
                if (isset($this->_items[$id])) {
                    $sortedItems[$id] = $this->_items[$id];
                }
            }
            $this->_items = &$sortedItems;
        }
        
        return $this;
    }

    /**
     * Handles collection filtering by ids retrieves from search engine.
     * Will also stores faceted data and total records.
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _beforeLoad()
    {
        $ids = array();
        if ($this->_engine) {
            $this->initQueryResult($this->_getQuery(), $this->_getParams());
            if(count($this->_searchedEntityIds) === 1) {
                header('location: '. Mage::getModel('catalog/product')->load(current($this->_searchedEntityIds))->getProductUrl());
                exit(0);
            }
        }

        $this->addIdFilter($this->_searchedEntityIds);
        $this->_pageSize = false;

        return parent::_beforeLoad();
    }

    /**
     * Retrieve the query result
     *
     * @param  string $query
     * @param  array  $params
     * @return array
     */
    protected function initQueryResult($query, $params)
    {
        if($this->queryResult === null) {
            $this->queryResult = $this->_engine->getIdsByQuery($query, $params);

            $this->_totalRecords      = $this->_engine->getLastNumFound();
            $this->_facetedData       = isset($this->queryResult['faceted_data']) ? $this->queryResult['faceted_data'] : array();
            $this->_searchedEntityIds = isset($this->queryResult['ids']) ? $this->queryResult['ids'] : array();
            $this->_categoryIds       = isset($this->queryResult['category_ids']) ? $this->queryResult['category_ids'] : array();
        }
    }

    /**
     * Retrieves parameters.
     *
     * @return array
     */
    protected function _getParams()
    {
        if($this->_params === null) {
            $params = array();

            $blockList = Mage::getBlockSingleton('catalog/product_list_toolbar');

            $params['limit'] = $blockList->getLimit();
            $params['p']     = $blockList->getCurrentPage();

            if($order = Mage::app()->getRequest()->getParam('order', false)) {
                if(!$dir = Mage::app()->getRequest()->getParam('dir', false)) {
                    $dir = 'asc';
                }
                $params['sort_by'] = array(array($order => $dir));
            }

            $params['filters'] = $this->_searchQueryFilters;

            if (!empty($this->_facetsConditions)) {
                $params['facets'] = $this->_facetsConditions;
            }

            $this->_params = $params;
        }

        return $this->_params;
    }
    
    /**
     * Load entities records into items
     *
     * @throws Exception
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function _loadEntities($printQuery = false, $logQuery = false)
    {
        $this->printLogQuery($printQuery, $logQuery);
        try {
            $query = $this->_prepareSelect($this->getSelect());
            $query = str_replace('INNER JOIN `catalog_category_product_index`', 'LEFT JOIN `catalog_category_product_index`', $query);
            $rows = $this->_fetchAll($query);
        } catch (Exception $e) {
            Mage::printException($e, $query);
            $this->printLogQuery(true, true, $query);
            throw $e;
        }

        foreach ($rows as $v) {
            $object = $this->getNewEmptyItem()->setData($v);
            $this->addItem($object);
            if (isset($this->_itemsById[$object->getId()])) {
                $this->_itemsById[$object->getId()][] = $object;
            } else {
                $this->_itemsById[$object->getId()] = array($object);
            }
        }

        return $this;
    }

    /**
     * Returns stored text query
     *
     * @return string
     */
    protected function _getQuery()
    {
        return $this->_searchQueryText;
    }
}

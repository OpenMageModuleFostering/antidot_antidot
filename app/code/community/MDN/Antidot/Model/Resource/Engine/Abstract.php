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
 * @copyright  Copyright (c) 2015 Antidot (http://www.antidot.net)
 * @author : Antidot devmagento@antidot.net
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class MDN_Antidot_Model_Resource_Engine_Abstract
{

    /**
     * @var object Search engine client.
     */
    protected $_client;

    /**
     * @var array List of default query parameters.
     */
    protected $_defaultQueryParams = array(
        'p' => 1,
        'limit' => 10,
        'store_id' => null,
        'fields' => array(),
        'params' => array(),
        'ignore_handler' => false,
        'filters' => array(),
    );

    /**
     * @var int Last number of results found.
     */
    protected $_lastNumFound;

    /**
     * @var array results 
     */
    protected $_idsByQuery;
    
    /**
     * Returns advanced search results.
     *
     * @return MDN_Antidot_Model_Resource_Catalog_Product_Collection
     */
    public function getAdvancedResultCollection()
    {
        return $this->getResultCollection();
    }

    /**
     * Checks if advanced index is allowed for current search engine.
     *
     * @return bool
     */
    public function allowAdvancedIndex()
    {
        return true;
    }

    /**
     * Returns product visibility ids for search.
     *
     * @see Mage_Catalog_Model_Product_Visibility
     * @return mixed
     */
    public function getAllowedVisibility()
    {
        return Mage::getSingleton('catalog/product_visibility')->getVisibleInSearchIds();
    }

    /**
     * Retrieves product ids for specified query.
     * The result is stored in attribute in order to avoid double ws call with the Mana_Seo 
     * module (see Mana_Seo_Model_Observer#prepareMetaData , it clones productCollection and don't  
     * keep the value of the queryResult attribute in it)
     * 
     * @param string $query
     * @param array $params
     * @param string $type
     * @return array
     */
    public function getIdsByQuery($query, $params = array(), $type = 'product')
    {
       $paramsHash = md5(json_encode($params));
       if (!isset($this->_idsByQuery[$paramsHash])) {
	        $ids = array();
	        $resultTmp = $this->search($query, $params, $type);
	        if (!empty($resultTmp['ids'])) {
	            foreach ($resultTmp['ids'] as $id) {
	                $ids[] = $id['id'];
	            }
	        }
	        
	        $this->_idsByQuery[$paramsHash] = array(
	            'ids' => $ids,
	            'total_count' => (isset($resultTmp['total_count'])) ? $resultTmp['total_count'] : null,
	            'faceted_data' => (isset($resultTmp['facets'])) ? $resultTmp['facets'] : array(),
	            'category_ids' => (isset($resultTmp['category_ids'])) ? $resultTmp['category_ids'] : array(),
	        );
        }
        
        return $this->_idsByQuery[$paramsHash];
    }

    /**
     * Returns resource name.
     *
     * @return string
     */
    public function getResourceName()
    {
        return 'Antidot/advanced';
    }

    /**
     * Returns last number of results found.
     *
     * @return int
     */
    public function getLastNumFound()
    {
        return $this->_lastNumFound;
    }

    /**
     * Returns catalog product collection with current search engine set.
     *
     * @return MDN_Antidot_Model_Resource_Catalog_Product_Collection
     */
    public function getResultCollection()
    {
        return Mage::getResourceModel('Antidot/catalog_product_collection')->setEngine($this);
    }

    /**
     * Retrieves stats for specified query.
     *
     * @param string $query
     * @param array $params
     * @param string $type
     * @return array
     */
    public function getStats($query, $params = array(), $type = 'product')
    {
        return $this->_search($query, $params, $type);
    }

    /**
     * Alias of isLayeredNavigationAllowed.
     *
     * @return bool
     */
    public function isLeyeredNavigationAllowed()
    {
        return $this->isLayeredNavigationAllowed();
    }

    /**
     * Checks if layered navigation is available for current search engine.
     *
     * @return bool
     */
    public function isLayeredNavigationAllowed()
    {
        return true;
    }

    /**
     * Performs search query and facetting.
     *
     * @param string $query
     * @param array $params
     * @param string $type
     * @return array
     */
    public function search($query, $params = array(), $type = 'product')
    {
        $result = array();
        try {
            Varien_Profiler::start('Antidot');
            $result = $this->_search($query, $params, $type);
            Varien_Profiler::stop('Antidot');

        } catch (Exception $e) {
            Mage::logException($e, null, 'antidot.log');
        }
        
        return $result;
    }

    /**
     * Checks search engine availability.
     * Should be overriden by child classes.
     *
     * @return bool
     */
    public function test()
    {
        return true;
    }

    /**
     * Returns search helper.
     *
     * @return MDN_Antidot_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('Antidot');
    }

    /**
     * Transforms specified object to an array.
     *
     * @param $object
     * @return array
     */
    protected function _objectToArray($object)
    {
        if (!is_object($object) && !is_array($object)){
            return $object;
        }
        if (is_object($object)){
            $object = get_object_vars($object);
        }

        return array_map(array($this, '_objectToArray'), $object);
    }

    /**
     * Prepares query before search.
     *
     * @param mixed $query
     * @return string
     */
    protected function prepareSearchConditions($query)
    {
        return $query;
    }

    /**
     * Prepares facets query response.
     *
     * @abstract
     * @param mixed $response
     * @return mixed
     */
    abstract protected function prepareFacetsQueryResponse($response);

    /**
     * Prepares query response.
     *
     * @abstract
     * @param mixed $response
     * @return mixed
     */
    abstract protected function prepareQueryResponse($response);

    /**
     * Performs search and facetting for specified query and parameters.
     *
     * @abstract
     * @param string $query
     * @param array $params
     * @return mixed
     */
    abstract protected function _search($query, $params = array());
}

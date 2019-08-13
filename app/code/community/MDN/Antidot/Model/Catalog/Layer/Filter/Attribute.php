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
class MDN_Antidot_Model_Catalog_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
    /**
     * Adds facet condition to product collection.
     *
     * @see MDN_Antidot_Model_Resource_Catalog_Product_Collection::addFacetCondition()
     * @return MDN_Antidot_Model_Catalog_Layer_Filter_Attribute
     */
    public function addFacetCondition()
    {
        $this->getLayer()
            ->getProductCollection()
            ->addFacetCondition($this->_getFilterField());

        return $this;
    }

    /**
     * Retrieves request parameter and applies it to product collection.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Mage_Core_Block_Abstract $filterBlock
     * @return MDN_Antidot_Model_Catalog_Layer_Filter_Attribute
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();
        
        $filter = $request->getParam($this->_requestVar);
        if (is_array($filter) || null === $filter) {
            return $this;
        }
        
        $text = $this->_getOptionText($filter);
        if ($this->_isValidFilter($filter) && strlen($text)) {
            $this->applyFilterToCollection($this, $filter);
            if(!Mage::helper('Antidot')->hasFacetMultiple($this->_requestVar)) {
                $this->_items = array();

                if(Mage::getSingleton('core/session')->getData($this->_requestVar.$text)) {
                    $text = Mage::getSingleton('core/session')->getData($this->_requestVar.$text);
                }

                $this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));
            }
        }

        return $this;
    }

    /**
     * Return the attribute code
     *
     * @return string Attribute code
     */
    public function getCode()
    {
        return $this->getAttributeModel()->getAttributeCode();
    }

    /**
     * Applies filter to product collection.
     *
     * @param $filter
     * @param $value
     * @return MDN_Antidot_Model_Catalog_Layer_Filter_Attribute
     */
    public function applyFilterToCollection($filter, $value)
    {
        if (!$this->_isValidFilter($value)) {
            $value = array();
        } else if (!is_array($value)) {
            $value = array($value);
        }

        $attribute = $filter->getAttributeModel();
        $param = Mage::helper('Antidot')->getSearchParam($attribute, $value);

        $this->getLayer()
            ->getProductCollection()
            ->addSearchQfFilter($param);

        return $this;
    }

    /**
     * Returns facets data of current attribute.
     *
     * @return array
     */
    protected function _getFacets()
    {
        $productCollection = $this->getLayer()->getProductCollection();
        $fieldName = $this->_getFilterField();
        $facets = $productCollection->getFacetedData($fieldName);
        
        return $facets;
    }

    /**
     * Returns attribute field name.
     *
     * @return string
     */
    protected function _getFilterField()
    {
        $attribute = $this->getAttributeModel();
        $fieldName = Mage::helper('Antidot')->getAttributeFieldName($attribute);
        
        return $fieldName;
    }

    /**
     * Retrieves current items data.
     *
     * @return array
     */
    protected function _getItemsData()
    {
        $attribute = $this->getAttributeModel();
        $this->_requestVar = $attribute->getAttributeCode();

        $facets = $this->_getFacets();

        $data = array();
        if (count($facets) > 0) {
            if ($attribute->getFrontendInput() === 'text') {
                $data = $this->getFacetsData($facets);
            }
        }

        Mage::getSingleton('core/session')->setData(md5($_GET['q'].$this->_requestVar), serialize($data));

        return $data;
    }

    /**
     * @param array $facets
     */
    protected function getFacetsData($facets)
    {
        $data = array();
        foreach ($facets as $facetKey => $facet) {
            $data[$facetKey] = array(
                'label' => $facet['label'],
                'value' => $facetKey,
                'count' => $facet['count'],
            );

            Mage::getSingleton('core/session')->setData($this->_requestVar.$facetKey, $facet['label']);
            if(isset($facet['child'])) {
                $data[$facetKey]['child'] = $this->getFacetsData($facet['child']);
                Mage::getSingleton('core/session')->setData('child'.$this->_requestVar.$facetKey, $data[$facetKey]['child']);
            }
        }

        return $data;
    }

    /**
     * Returns option label if attribute uses options.
     *
     * @param int $optionId
     * @return bool|int|string
     */
    protected function _getOptionText($optionId)
    {
        if ($this->getAttributeModel()->getFrontendInput() == 'text') {
            return $optionId;
        }

        return parent::_getOptionText($optionId);
    }

    /**
     * Checks if given filter is valid before being applied to product collection.
     *
     * @param string $filter
     * @return bool
     */
    protected function _isValidFilter($filter)
    {
        return !empty($filter);
    }
    
    /**
     * Create filter item object
     *
     * @param   string $label
     * @param   mixed $value
     * @param   int $count
     * @return  Mage_Catalog_Model_Layer_Filter_Item
     */
    protected function _createItem($label, $value, $count = 0)
    {
        $children = (array)Mage::getSingleton('core/session')->getData('child'.$this->_requestVar.$value);

        $itemChildren = array();
        foreach($children as $child) {
            $itemChildren[] = $this->_createItem($child['label'], $child['value'], $child['count']);
        }

        return Mage::getModel('Antidot/catalog_layer_filter_item')
            ->setFilter($this)
            ->setLabel($label)
            ->setValue($value)
            ->setCount($count)
            ->setChild($itemChildren);
    }
}

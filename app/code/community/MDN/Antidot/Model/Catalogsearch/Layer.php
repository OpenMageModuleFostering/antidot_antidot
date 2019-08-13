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
class MDN_Antidot_Model_Catalogsearch_Layer extends Mage_CatalogSearch_Model_Layer
{
    
    /**
     * Return the product collection
     * 
     * @return ProductCollection
     */
    public function getProductCollection()
    {
        $category = $this->getCurrentCategory();
        if (isset($this->_productCollections[$category->getId()])) {
            $collection = $this->_productCollections[$category->getId()];
        } else {
            $collection = Mage::helper('catalogsearch')
                ->getEngine()
                ->getResultCollection()
                ->setStoreId($category->getStoreId());
            $this->prepareProductCollection($collection);
            $this->_productCollections[$category->getId()] = $collection;
        }

        return $collection;
    }
    
    /**
     * Return the filterable attributes
     * 
     * @return \MDN_Antidot_Model_Catalogsearch_Resource_Attribute
     */
    public function getFilterableAttributes()
    {
        $facets = array();
        if($config = Mage::getStoreConfig('antidot/engine/facets')) {
            $config = unserialize($config);

            foreach($config as $facet) {
                list($id, $label) = explode('|', $facet['facet']);
                $key = sprintf('%02d', $facet['order']).'_'.$id;    //sptrinf to ensure order : 10 is after 09
                $facets[$key] = array('id' => $id, 'label' => $label);
            }
        }
        ksort($facets);

        $attributes = array();
        foreach($facets as $facet) {
            $attributes[] = new MDN_Antidot_Model_Catalogsearch_Resource_Attribute($facet);
        }
        
        return $attributes;
    }
}

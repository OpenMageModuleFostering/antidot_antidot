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
class MDN_Antidot_Model_Catalog_Layer extends Mage_Catalog_Model_Layer
{
    /**
     * Returns product collection for current category.
     *
     * @return MDN_Antidot_Model_Resource_Catalog_Product_Collection
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
}

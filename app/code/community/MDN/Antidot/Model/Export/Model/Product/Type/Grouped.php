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
class MDN_Antidot_Model_Export_Model_Product_Type_Grouped extends Mage_Catalog_Model_Product_Type_Grouped
{


    /**
     * Retrieve array of associated products
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getAssociatedProducts($product = null)
    {
        if (!$this->getProduct($product)->hasData($this->_keyAssociatedProducts)) {
            $associatedProducts = array();

            $this->setSaleableStatus($product);

            $collection = $this->getAssociatedProductCollection($product)
                /*->addAttributeToSelect('*') don't load every attribute in collection */
                ->addFilterByRequiredOptions()
                ->setPositionOrder()
                ->addStoreFilter($this->getStoreFilter($product))
                ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

            //FILTER only enabled product */
            foreach ($collection as $item) {
                $associatedProducts[] = $item;
            }

            $this->getProduct($product)->setData($this->_keyAssociatedProducts, $associatedProducts);
        }
        return $this->getProduct($product)->getData($this->_keyAssociatedProducts);
    }


    /**
     * Retrieve collection of associated products
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection
     */
    public function getAssociatedProductCollection($product = null)
    {
        $collection = $this->getProduct($product)->getLinkInstance()->useGroupedLinks()
            ->getProductCollection()
            //->setFlag('require_stock_items', true)
            //->setFlag('product_children', true)
            ->setIsStrongMode();
        $collection->setProduct($this->getProduct($product));
        return $collection;
    }


}

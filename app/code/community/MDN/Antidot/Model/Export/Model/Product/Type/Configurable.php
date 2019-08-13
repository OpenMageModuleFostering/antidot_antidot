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
class MDN_Antidot_Model_Export_Model_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Configurable
{


    /**
     * Retrieve array of "subproducts"
     *
     * @param  array
     * @param  Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getUsedProducts($requiredAttributeIds = null, $product = null)
    {

        Varien_Profiler::start('EXPORT_PRODUCT_CONFIGURABLE:'.__METHOD__);
        if (!$this->getProduct($product)->hasData($this->_usedProducts)) {
            if (is_null($requiredAttributeIds)
                and is_null($this->getProduct($product)->getData($this->_configurableAttributes))) {
                // If used products load before attributes, we will load attributes.
                $this->getConfigurableAttributes($product);
                // After attributes loading products loaded too.
                Varien_Profiler::stop('EXPORT_PRODUCT_CONFIGURABLE:'.__METHOD__);
                return $this->getProduct($product)->getData($this->_usedProducts);
            }

            $usedProducts = array();
            $collection = $this->getUsedProductCollection($product)
                //->addAttributeToSelect('*')
                ->addFilterByRequiredOptions();

            if (is_array($requiredAttributeIds)) {
                foreach ($requiredAttributeIds as $attributeId) {
                    $attribute = $this->getAttributeById($attributeId, $product);
                    if (!is_null($attribute))
                        $collection->addAttributeToFilter($attribute->getAttributeCode(), array('notnull'=>1));
                }
            }

            foreach ($collection as $item) {
                $usedProducts[] = $item;
            }

            $this->getProduct($product)->setData($this->_usedProducts, $usedProducts);
        }
        Varien_Profiler::stop('EXPORT_PRODUCT_CONFIGURABLE:'.__METHOD__);
        return $this->getProduct($product)->getData($this->_usedProducts);
    }

    /**
     * Retrieve related products collection
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Type_Configurable_Product_Collection
     */
    public function getUsedProductCollection($product = null)
    {

        /**
         * list the products which are available in stock (according to configuration)
         */
        $onlyProductsWithStock = !(boolean)Mage::getStoreConfig('antidot/fields_product/in_stock_only');
        $productsInStock = $onlyProductsWithStock ? ' AND is_in_stock = 1' : '';

        $collection = Mage::getResourceModel('Antidot/export_product_type_configurable_product_collection')
            ->joinTable('cataloginventory/stock_item',
                'product_id=entity_id', // warning : no spaces between = and entity_id , magento1.5 isn't robust enought
                array('qty', 'is_in_stock'),
                '{{table}}.stock_id = 1'.$productsInStock)
            ->setProductFilter($this->getProduct($product));
        if (!is_null($this->getStoreFilter($product))) {
            $collection->addStoreFilter($this->getStoreFilter($product));
        }

        return $collection;
    }

}

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
class MDN_Antidot_Model_Export_Model_Product_Link  extends Mage_Catalog_Model_Product_Link
{

    /**
     * Retrieve linked product collection
     *
     * add join invenory table to load qty and is_in_stock
     *
     */
    public function getProductCollection()
    {
        $onlyProductsWithStock = !(boolean)Mage::getStoreConfig('antidot/fields_product/in_stock_only');
        $productsInStock = $onlyProductsWithStock ? ' AND is_in_stock = 1' : '';

        $collection = Mage::getResourceModel('Antidot/export_product_link_product_collection')
            ->joinTable('cataloginventory/stock_item',
                'product_id=entity_id', // warning : no spaces between = and entity_id , magento1.5 isn't robust enought
                array('qty', 'is_in_stock'),
                '{{table}}.stock_id = 1'.$productsInStock)
            ->setLinkModel($this);

        return $collection;
    }

}
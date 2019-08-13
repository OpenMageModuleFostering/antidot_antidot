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
class MDN_Antidot_Model_Resource_Export_Product_Link_Product_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection
    //instead of Mage_Catalog_Model_Resource_Product_Link_Product_Collection for compatibility with magento < 1.6
{

    /**
     * Initialize resources
     *
     * Initialize resources :
     * Model object is MDN_Antidot_Model_Export_Model_Product
     * Resource Model Object is MDN_Antidot_Model_Resource_Export_Product
     *
     */
    protected function _construct()
    {
       $this->_init('Antidot/export_model_product', 'Antidot/export_product');
    }

    /**
     * Processing collection items after loading
     * Adding url rewrites, minimal prices, final prices, tax percents
     *
     * FOR Export : don't add all theses infos
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _afterLoad()
    {
//        if ($this->_addUrlRewrite) {
//            $this->_addUrlRewrite($this->_urlRewriteCategory);
//        }
//
//        $this->_prepareUrlDataObject();

//        if (count($this) > 0) {
//            Mage::dispatchEvent('catalog_product_collection_load_after', array('collection' => $this));
//        }

//        foreach ($this as $product) {
//            if ($product->isRecurring() && $profile = $product->getRecurringProfile()) {
//                $product->setRecurringProfile(unserialize($profile));
//            }
//        }

        return $this;
    }

    /**
     * Join attributes
     *
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function joinAttributes()
    {
        if (!$this->getLinkModel()) {
            return $this;
        }
        $attributes = $this->getLinkModel()->getAttributes();

        $attributesByType = array();
        foreach ($attributes as $attribute) {
            /**
             * Don't add qty link attribute because it's already in the request from
             * inventory table
             */
            if ($attribute['code'] != 'qty') {
                $table = $this->getLinkModel()->getAttributeTypeTable($attribute['type']);
                $alias = sprintf('link_attribute_%s_%s', $attribute['code'], $attribute['type']);

                $joinCondiotion = array(
                    "{$alias}.link_id = links.link_id",
                    $this->getSelect()->getAdapter()->quoteInto(
                        "{$alias}.product_link_attribute_id = ?",
                        $attribute['id']
                    )
                );
                $this->getSelect()->joinLeft(
                    array($alias => $table),
                    implode(' AND ', $joinCondiotion),
                    array($attribute['code'] => 'value')
                );
            }
        }

        return $this;
    }
}

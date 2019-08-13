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
class MDN_Antidot_Block_Catalogsearch_Stores extends Mage_Core_Block_Template
{
    protected $_stores = null;

    /**
     * Return stores
     * @return type
     */
    public function getStores()
    {
        if ($this->_stores == null)
        {
            $this->loadStores();
        }
        return $this->_stores;
    }

    /**
     * Return stores
     * @return boolean
     */
    public function hasStore()
    {
        return count($this->getStores())>0;
    }

    /**
     * Load stores based on antidot results
     */
    protected function loadStores()
    {
        if ($this->getLayer()) {
            $this->_stores = $this->getLayer()->getProductCollection()->getStores();
        } else {
            $this->_stores = array();
        }

    }
    
    /**
     * Returns current catalog layer.
     *
     * @return MDN_Antidot_Model_Catalogsearch_Layer|Mage_Catalog_Model_Layer
     */
    public function getLayer()
    {
        $helper = Mage::helper('Antidot');
        if ($helper->isActiveEngine()) {
            return Mage::getSingleton('Antidot/catalogsearch_layer');
        }

        return null;
    }
    
}
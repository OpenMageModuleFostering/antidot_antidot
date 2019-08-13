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
class MDN_Antidot_Block_Catalogsearch_Banner extends Mage_Core_Block_Template
{
    protected $_banners = null;

    /**
     * Return banners
     * @return type
     */
    public function getBanners()
    {
        if ($this->_banners == null)
        {
            $this->loadBanners();
        }
        return $this->_banners;
    }

    /**
     * Return banners
     * @return boolean
     */
    public function hasBanner()
    {
        return count($this->getBanners())>0;
    }

    /**
     * Load banners based on antidot results
     */
    protected function loadBanners()
    {

        $this->_banners = $this->getLayer()->getProductCollection()->getBanners();

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

        return parent::getLayer();
    }
    
}
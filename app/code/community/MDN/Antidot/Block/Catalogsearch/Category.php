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
class MDN_Antidot_Block_Catalogsearch_Category extends Mage_Core_Block_Template
{
    protected $_categories = null;
    
    /**
     * Return categories
     * @return type
     */
    public function getCategories()
    {
        if ($this->_categories == null)
        {
            $this->loadCategories();
        }
        return $this->_categories;
    }
    
    /**
     * 
     * @param type $cat
     */
    public function getCategoryUrl($cat)
    {
        return $cat->getUrl();
    }
    
    /**
     * Load category based on antidot results
     */
    protected function loadCategories()
    {
        $categoryIds = $this->getLayer()->getProductCollection()->getCategoryIds();
        
        $this->_categories = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('entity_id', array('in' => $categoryIds));
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
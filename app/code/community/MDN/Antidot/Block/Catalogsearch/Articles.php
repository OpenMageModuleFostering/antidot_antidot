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
class MDN_Antidot_Block_Catalogsearch_Articles extends Mage_Core_Block_Template
{
    protected $_articles = null;

    /**
     * Return articles
     * @return type
     */
    public function getArticles()
    {
        if ($this->_articles == null)
        {
            $this->loadArticles();
        }
        return $this->_articles;
    }

    /**
     * Return articles
     * @return boolean
     */
    public function hasArticle()
    {
        return count($this->getArticles())>0;
    }

    /**
     * Load articles based on antidot results
     */
    protected function loadArticles()
    {
        if ($this->getLayer()) {
            $this->_articles = $this->getLayer()->getProductCollection()->getArticles();
        } else {
            $this->_articles = array();
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
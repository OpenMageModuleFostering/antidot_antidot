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
class MDN_Antidot_Block_CatalogSearch_Result extends Mage_CatalogSearch_Block_Result
{
    /**
     * Set default order
     *
     * @return Mage_CatalogSearch_Block_Result
     */
    public function setListOrders()
    {
        $config = Mage::getStoreConfig('antidot/engine/default_sort');
        $defaultSort = current(unserialize($config));
        list($field) = explode('|', $defaultSort['field']);
        $this->getListBlock()
            ->setAvailableOrders($this->getAvailableOrders())
            ->setDefaultDirection($defaultSort['dir'])
            ->setSortBy($field);
        
        return $this;
    }
    
    /**
     * Return available list orders
     * 
     * @return array
     */
    protected function getAvailableOrders()
    {
        $config = Mage::getStoreConfig('antidot/engine/sortable');
        $availableSortable = unserialize($config);
        
        $availableOrders = array();
        foreach($availableSortable as $sort) {
            list($field, $label) = explode('|', $sort['sort']);
            $availableOrders[$field] = $label;
        }
        
        return $availableOrders;
    }

    /**
     * {@inherit}
     */
    public function _toHtml()
    {
        $this->setTemplate('antidot/catalogsearch/result.phtml');

        return parent::_toHtml();
    }
}

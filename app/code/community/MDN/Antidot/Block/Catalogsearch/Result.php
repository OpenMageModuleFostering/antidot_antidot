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
class MDN_Antidot_Block_CatalogSearch_Result extends Mage_CatalogSearch_Block_Result
{
    /**
     * Set default order
     *
     * @return Mage_CatalogSearch_Block_Result
     */
    public function setListOrders()
    {

        $availableOrders = $this->getAvailableOrders();

        $config = Mage::getStoreConfig('antidot/engine/default_sort');
        $defaultSorts  = unserialize($config);

        /* default sorting on relevance desc */
        $field = 'afs:relevance';
        $dir = 'desc';
        /*
         * take the first sort of the default sort config existing
         * amoung available sort
         */
        foreach ($defaultSorts as $defaultSort) {
            list($dfield) = explode('|', $defaultSort['field']);
            if (isset($availableOrders[$dfield])) {
                $field = $dfield;
                $dir = $defaultSort['dir'];
                continue;
            }
        }
        /*
         * if there's none, take the first of the available sort
         */
        if (!isset($availableOrders[$field])) {
            if (count($availableOrders)>0) {
                $keys = array_keys($availableOrders);
                $field = $keys[0];
            }
        }

        $this->getListBlock()
            ->setAvailableOrders($availableOrders)
            ->setDefaultDirection($dir)
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
            $availableOrders[$field] = Mage::helper('Antidot')->__($label);
        }
        
        return $availableOrders;
    }

    /**
     * {@inherit}
     */
    public function _toHtml()
    {
        return parent::_toHtml();
    }
}

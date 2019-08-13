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
class MDN_Antidot_Helper_CatalogSearch_Data extends Mage_CatalogSearch_Helper_Data {
    
    /**
     * {@inherit}
     */
    public function getSuggestUrl()
    {
        $url = Mage::getStoreConfig('antidot/suggest/enable') === 'Antidot/engine_antidot' ? 'Antidot/Front_Search/Suggest' : 'catalogsearch/ajax/suggest';
        return $this->_getUrl($url, array(
            '_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()
        ));
    }
    
    /**
     * {@inherit}
     */
    public function getResultUrl($query = null, $store = null)
    {
    	return $this->_getUrl('catalogsearch/result', array(
    			'_query' => array(self::QUERY_VAR_NAME => $query),
    			'_secure' => Mage::app()->getFrontController()->getRequest()->isSecure(),
    	        '_store' => $store
    	));
    }
}

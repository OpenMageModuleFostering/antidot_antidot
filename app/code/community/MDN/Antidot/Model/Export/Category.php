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
class MDN_Antidot_Model_Export_Category extends MDN_Antidot_Model_Export_Abstract 
{
    const TYPE = 'CATEGORY';
    const FILENAME_XML = 'categories-%s_%s-%s.xml';
    const FILENAME_ZIP = '%s_full_%s_categories.zip';
    const XSD = 'http://ref.antidot.net/store/latest/categories.xsd';
    
    /**
     * Get xml
     * 
     * @param type $context
     */
    public function writeXml($context, $filename)
    {
        $nbTotalCategories = 0;
        $context['categories'] = array();
        foreach($context['stores'] as $store) {
            $categories = $this->getCategories($store);
            $nbTotalCategories += $categories->getSize();
            $context['categories'][$store->getId()] = $categories;
        }

        if ($nbTotalCategories == 0) {
            return 0;
        }

        $this->initXml();
        $this->initFields('category');
        
        $this->xml->push('categories', array('xmlns' => "http://ref.antidot.net/store/afs#"));
        
        $this->writeHeader($context);

        $nbItems = 0;
        foreach($context['stores'] as $store) {
            foreach($context['categories'][$store->getId()] as $cat) {

                if (!$this->getField($cat, 'name'))
                    continue;

                $this->xml->push('category', array('id' => $cat->getId(), 'xml:lang' => $context['lang']));

                $this->xml->element('name', $this->xml->encloseCData($this->getField($cat, 'name')));

                //Force the store on the url in order to generate the store code in url if it is configured in system > config
                $cat->setStoreId($store->getId()); 
                if (method_exists($cat, 'getUrlModel')) { //compatibility with older magento version where category#getUrlModel doesn't exist 
	                $cat->getUrlModel()->getUrlInstance()->setStore($store->getId()); 
                } else {
                	$cat->getUrlInstance()->setStore($store->getId());
                }
                $this->xml->element('url', $this->getUri($cat->getUrl()));

                if ($cat->getImageUrl()) {
                    $this->xml->element('image', $cat->getImageUrl());
                }

                if ($keywords = $this->getField($cat, 'keywords')) {
                    $this->xml->element('keywords', $this->xml->encloseCData($keywords));
                }

                if ($description = $this->getField($cat, 'description')) {
                    $this->xml->element('description', $this->xml->encloseCData($description));
                }

                if ($cat->getProductCount() > 0) {
                    $this->xml->element('productsCount', $cat->getProductCount());
                }

                if ($cat->getParentId() && ($cat->getParentId() != $store->getRootCategoryId())) {
                    $this->xml->emptyelement('broader', array('idref' => $cat->getParentId()));
                }

                $storeIds = array_intersect($context['store_id'], $cat->getStoreIds());
                $this->xml->push('websites');
                foreach($storeIds as $storeId) {
                    $website = $this->getWebSiteByStore($context['stores'][$storeId]);
                    $this->xml->element('website', '', array('id' => $website->getId(), 'name' => $website->getName()));
                }
                $this->xml->pop();

                $this->xml->pop();

                $nbItems++;
            }
        }
        $this->xml->pop();

        $xml = $this->xml->flush();
        $result = file_put_contents($filename, $xml);
        Mage::log('Write category XML in : '.$filename.' ('.$result.' bytes written - original xml size is '.strlen($xml).')', null, 'antidot.log');

        return $nbItems;
    }
    
    /**
     * Return categories
     * 
     * @param Store $store
     * @return array
     */
    protected function getCategories($store)
    {
        return Mage::getModel('catalog/category')
            ->getCollection()
            ->setStoreId($store->getId())
            ->addAttributeToSelect('url_key')
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('is_active', 1)
            ->addFieldToFilter('path', array('like' => Mage::getModel('catalog/category')->load($store->getRootCategoryId())->getPath().'/%'))
        ;
    }
}

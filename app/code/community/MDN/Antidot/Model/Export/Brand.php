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
class MDN_Antidot_Model_Export_Brand extends MDN_Antidot_Model_Export_Abstract 
{
    const TYPE = 'BRAND';
    const FILENAME_XML = 'brands-mdn-fr.xml';
    const FILENAME_ZIP = '%s_full_mdn_brands.zip';
    const XSD = 'http://ref.antidot.net/store/latest/brands.xsd';
    
    const PATTERN_URL = '/brands/{brand}';
    
    protected $urlHelper;
    
    /**
     * Get xml
     * 
     * @param type $context
     */
    public function getXml($context)
    {
        $this->initXml();
        $this->initFields('brand');
        
        $brandPattern = $this->getBrandPattern();
        
        $this->xml->push('brands', array('xmlns' => "http://ref.antidot.net/store/afs#"));
        
        $this->xml->push('header');
        $this->xml->element('owner', $context['owner']);
        $this->xml->element('feed', 'brand');
        $this->xml->element('generated_at', date('c', Mage::getModel('core/date')->timestamp(time())));
        $this->xml->pop();

        foreach($context['store_id']as $storeId) {
            $store = Mage::getModel('core/store')->load($storeId);
            foreach($this->getBrands() as $brandId => $brand) {
                $this->xml->push('brand', array('id' => $brandId, 'xml:lang' => $context['lang']));

                $this->xml->element('name', $this->xml->encloseCData($brand));
                $this->xml->element('url', $this->getUrl($brand, $brandPattern));

                $this->xml->push('websites');
                $this->xml->element('website', '', array('id' => $storeId, 'name' => $store->getName()));
                $this->xml->pop();

                $this->xml->pop();
            }
        }
        
        $this->xml->pop();
        
        return $this->xml->getXml();
    }
    
    /**
     * Return categories
     * 
     * @param int $rootCategoryId
     * @param array
     */
    protected function getBrands()
    {
        $attribute = Mage::getModel('eav/config')
                ->getAttribute('catalog_product', 'manufacturer');

        $brands = array();
        foreach($attribute->getSource()->getAllOptions(true, true) as $option) {
            if(!empty($option['value'])) {
                $brands[$option['value']] = $option['label'];
            }
        }
        
        return $brands;
    }
    
    /**
     * Return brand url
     * 
     * @param string $brand
     * @return string
     */
    public function getUrl($brand, $brandPattern = null)
    {
        $brandPattern = $brandPattern === null ? $this->getBrandPattern() : $brandPattern;
        
        return preg_replace('/\{brand\}/', $this->getUrlHelper()->url($brand), $brandPattern);
    }
    
    /**
     * Get the brand pattern
     * 
     * @return string
     */
    protected function getBrandPattern() 
    {
        $brandPattern = Mage::getStoreConfig('antidot/general/brand');
        if(strpos($brandPattern, '{brand}') === false) {
            $brandPattern = self::PATTERN_URL;
        }
        
        return $brandPattern;
    }
    
    /**
     * Return url Helper
     * 
     * @return Antidot/Url
     */
    protected function getUrlHelper() 
    {
        if($this->urlHelper === null) {
            $this->urlHelper = Mage::helper('Antidot/Url');
        }
        
        return $this->urlHelper;
    }
}
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
class MDN_Antidot_Model_System_Config_Sort
{
    
    /**
     * Cache options
     */
    protected static $options;

    /**
     * @var array Marketing fields
     */
    protected $marketingFields = array();

    
    /**
     * {@inherit}
     */
    public function toOptionArray() 
    {
        if (!self::$options) {
            $this->initMarketingFields();
            $options = array();
            $options[] = array('value' => 'afs:relevance|Relevance', 'label' => Mage::helper('Antidot')->__('Relevance'));
            $options[] = array('value' => 'name|Name', 'label' => Mage::helper('Antidot')->__('Name'));
            $options[] = array('value' => 'price|Price', 'label' => Mage::helper('Antidot')->__('Price'));
            $options[] = array('value' => 'is_promotional|Is promotional', 'label' => Mage::helper('Antidot')->__('Is promotional'));
            $options[] = array('value' => 'stock|Stock', 'label' => Mage::helper('Antidot')->__('Stock'));

            foreach($this->marketingFields as $field => $label) {
                if(Mage::getStoreConfig('antidot/fields_product/'.$field) != '') {
                    $options[] = array('value' => $field.'|'.$label, 'label' => Mage::helper('Antidot')->__($label));
                }
            }

            foreach(Mage::getModel("Antidot/System_Config_Facet")->toOptionArray('STRING') as $facetOption) {
                if(!preg_match('/^price_/', $facetOption['value'])) {
                    $options[] = $facetOption;
                }
            }

            self::$options = $options;
        }
        return self::$options;
    }

    /**
     * Init the marketing fields
     */
    public function initMarketingFields()
    {
        $this->marketingFields = array(
            'is_new'         => 'Is new',
            'is_best_sale'   => 'Is top sale',
            'is_featured'    => 'Is featured',
        );
    }

    /**
     * flush options cache
     * for unit test only !
     */
    public function reinitOptions() {
        self::$options = null;
    }
}

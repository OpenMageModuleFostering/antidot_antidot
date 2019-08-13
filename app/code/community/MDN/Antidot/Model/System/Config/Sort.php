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
            //$options[] = array('value' => 'position|Position', 'label' => Mage::helper('Antidot')->__('Position'));
            $options[] = array('value' => 'name|Name', 'label' => Mage::helper('Antidot')->__('Name'));

            foreach($this->marketingFields as $field => $label) {
                if(Mage::getStoreConfig('antidot/fields_product/'.$field) !== '') {
                    $options[] = array('value' => $field.'|'.$label, 'label' => $label);
                }
            }


            self::$options = array_merge($options, Mage::getModel("Antidot/System_Config_Facet")->toOptionArray('STRING'));

            foreach(self::$options as &$option) {
                if(preg_match('/^price_/', $option['value'])) {
                    $option['label'] = Mage::helper('Antidot')->__('Price');
                    $option['value'] = 'price|'.Mage::helper('Antidot')->__('Price');
                }
            }

        }
        
        return self::$options;
    }

    /**
     * Init the marketing fields
     */
    public function initMarketingFields()
    {
        $this->marketingFields = array(
            'is_promotional' => Mage::helper('Antidot')->__('Is promotional'),
            'is_new'         => Mage::helper('Antidot')->__('Is new'),
            'is_best_sale'   => Mage::helper('Antidot')->__('Is top sale'),
            'is_featured'    => Mage::helper('Antidot')->__('Is featured'),
        );
    }
}

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
class MDN_Antidot_Model_System_Config_ProductAttribute extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $options = false;
 
    /**
     * @var array Attribute type
     */
    protected $attributesType = array(
        'name'           => 'text',
        'short_name'     => 'text',
        'description'    => array('text', 'textarea'),
        'keywords'       => array('text', 'textarea'),
        'is_promotional' => 'boolean',
        'is_new'         => 'boolean',
        'is_best_sale'   => 'boolean',
        'is_featured'    => 'boolean',
        'materials'      => array('text', 'select'),
        'colors'         => array('text', 'select'),
        'models'         => array('text', 'select'),
        'sizes'          => array('text', 'select'),
        'manufacturer'   => array('text', 'select'),
        'reseller'       => 'text',
        'supplier'       => 'text',
        'gtin'           => 'text',
        'identifier'     => 'text',
        'misc'           => 'text',
        'properties'     => 'text',
    );

    public function getAllOptions() {}
    
    /**
     * {@inherit}
     */
    public function _getAllOptions($type)
    {
        $key = md5(serialize((array)$type));
        if (!$this->_options[$key]) {
            $options[] = array('value' => '', 'label' => '');
            if($type === null || in_array('text', (array)$type)) {
                $options[] = array('value' => 'sku', 'label' => 'sku');
            }
            
            foreach ($this->getAttributes($type) as $attribute) {
                $options[] = array(
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getName(),
                );
            }

            $this->_options[$key] = $options;
        }
        return $this->_options[$key];
    }
    
    /**
     * {@inherit}
     */
    protected function getAttributes($type)
    {
        $entityTypeId = Mage::getModel('eav/entity_type')->loadByCode('catalog_product')->getId();
        $attributes   = Mage::getResourceModel('eav/entity_attribute_collection')
            ->setEntityTypeFilter($entityTypeId)
            ->addFieldToFilter('backend_type', array('neq' => 'static'))
            ->addFieldToFilter('attribute_code', array('neq' => 'price'));
            
        if($type !== null) {
            $attributes->addFieldToFilter('frontend_input', array('in' => (array)$type));
        }
        
        return $attributes;
    }

    /**
     * {@inherit}
     */
    public function toOptionArray($elementName) 
    {
        $type = null;
        if(preg_match('/groups\[fields_product\]\[fields\]\[([a-zA-Z0-9_]+)\]\[value\]/', $elementName, $matches)) {
            $field = $matches[1];
            if(isset($this->attributesType[$field])) {
                $type = $this->attributesType[$field];
            }
        }
        
        return $this->_getAllOptions($type);
    }
} 
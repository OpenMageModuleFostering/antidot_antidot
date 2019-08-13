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
class MDN_Antidot_Model_System_Config_CategoryAttribute extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $options = false;

    /**
     * @var array Attribute type
     */
    protected $attributesType = array(
        'name'           => 'text',
        'description'    => array('text', 'textarea'),
        'keywords'       => array('text', 'textarea'),
    );

    public function getAllOptions() {}
 
    /**
     * {@inherit}
     */
    public function _getAllOptions($type)
    {
        if (!$this->_options) {
            $this->_options = array();
            foreach($this->getActiveCategories() as $category) {
                foreach($category->getAttributes() as $attribute) {
                    if(in_array($attribute->getFrontendInput(), (array)$type)) {
                        $this->_options[$attribute->getAttributeCode()] = array('value' => $attribute->getAttributeCode(), 'label' => $attribute->getName());
                    }
                }
            }
        }
        
        return $this->_options;
    }

    /**
     * Return the active categories
     *
     * @return Collection
     */
    protected function getActiveCategories()
    {
        return Mage::getModel('catalog/category')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('is_active', 1);
    }

    /**
     * {@inherit}
     */
    public function toOptionArray($elementName)
    {
        $type = null;
        if(preg_match('/groups\[fields_category\]\[fields\]\[([a-zA-Z0-9_]+)\]\[value\]/', $elementName, $matches)) {
            $field = $matches[1];
            if(isset($this->attributesType[$field])) {
                $type = $this->attributesType[$field];
            }
        }

        return $this->_getAllOptions($type);
    }
} 
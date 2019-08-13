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
class MDN_Antidot_Model_System_Config_Suggest_Type
{
    protected $defaultTypes = array('categories', 'products', 'brands');
    protected $types = array();
    
    /**
     * Return the types list
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        foreach ($this->getTypes() as $v) {
            $options[] = array(
                'value' => $v,
                'label' => Mage::helper('Antidot')->__($v)
            );
        }

        return $options;
    }
    
    /**
     * Return available types, and move first element to the end
     * 
     * @return array
     */
    protected function getTypes()
    {
        if(empty($this->types)) {
            $this->types = $this->defaultTypes;
        } else {
            $tmpType = array_shift($this->types);
            $this->types[] = $tmpType;
        }
        
        return $this->types;
    }
}

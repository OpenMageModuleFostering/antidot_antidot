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
class MDN_Antidot_Model_System_Config_ArticleAttribute extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    protected $options = false;
 
    /**
     * {@inherit}
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $pageCollection = Mage::getModel('cms/page')->getCollection();
            foreach($pageCollection as $page) {
                $attributes = array_keys($page->get());
                break;
            }
            
            $options = array();
            foreach ($attributes as $code) {
                $options[] = array('value' => $code, 'label' => $code);
            }
            
            $this->_options = $options;
        }
        return $this->_options;
    }

    /**
     * {@inherit}
     */
    public function toOptionArray() 
    {
        return $this->getAllOptions();
    }
} 
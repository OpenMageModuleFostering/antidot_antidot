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
class MDN_Antidot_Block_System_Config_Form_Field_CategoryAttribute extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * {@inherit}
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_getRenderer()
             ->setOptions(Mage::getModel("Antidot/System_Config_CategoryAttribute")->toOptionArray($element->getName()))
             ->setValue($element->getValue())
             ->setName($element->getName())
             ->toHtml();
    }
    
    protected function _getRenderer()
    {
        return $this->getLayout()
             ->createBlock('Antidot/Html_Select')
             ->setIsRenderToJsTemplate(true);
    }
}

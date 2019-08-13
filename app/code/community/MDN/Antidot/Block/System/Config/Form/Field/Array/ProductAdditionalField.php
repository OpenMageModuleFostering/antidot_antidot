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
class MDN_Antidot_Block_System_Config_Form_Field_Array_ProductAdditionalField extends MDN_Antidot_Block_System_Config_Form_Field_Array_Additional
{
    /**
     * {@inherit}
     */
    protected function _renderCellTemplate($columnName)
    {
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        switch($columnName) {
            case 'value':
                return $this->_getValueRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setOptions(Mage::getModel("Antidot/System_Config_ProductAttribute")->toOptionArray(null))
                    ->toHtml();
        }

        return parent::_renderCellTemplate($columnName);
    }
}
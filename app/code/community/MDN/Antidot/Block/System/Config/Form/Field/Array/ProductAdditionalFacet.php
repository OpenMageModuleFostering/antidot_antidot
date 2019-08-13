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
class MDN_Antidot_Block_System_Config_Form_Field_Array_ProductAdditionalFacet extends MDN_Antidot_Block_System_Config_Form_Field_Array_Additional
{
    protected $_autocompleteRenderer;
    
    /**
     * {@inherit}
     */
    protected function _prepareToRender()
    {
        parent::_prepareToRender();
        $this->addColumn('autocomplete', array('label' => Mage::helper('Antidot')->__('Auto Complete')));
    }
    
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
            case 'autocomplete':
                return $this->_getAutocompleteRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setExtraParams('style="width:100px"')
                    ->setOptions(Mage::getModel("Antidot/System_Config_DisableEnable")->toOptionArray(null))
                    ->toHtml();
        }

        return parent::_renderCellTemplate($columnName);
    }
    
    /**
     * {@inherit}
     */
    protected function _getAutocompleteRenderer()
    {
        if (!$this->_autocompleteRenderer) {
            $this->_autocompleteRenderer = $this->getLayout()
                   ->createBlock('Antidot/Html_Select')
                   ->setIsRenderToJsTemplate(true);
        }
        return $this->_autocompleteRenderer;
    }
    
    /**
     * Assign extra parameters to row
     *
     * @param Varien_Object $row
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_'.$this->_getAutocompleteRenderer()->calcOptionHash($row->getData('autocomplete')),
            'selected="selected"'
        );
        
        parent::_prepareArrayRow($row);
    }
}

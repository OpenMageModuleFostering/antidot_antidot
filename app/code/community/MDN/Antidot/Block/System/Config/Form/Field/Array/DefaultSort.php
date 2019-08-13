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
class MDN_Antidot_Block_System_Config_Form_Field_Array_DefaultSort extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_fieldRenderer;
    protected $_dirRenderer;

    /**
     * {@inherit}
     */
    protected function _prepareToRender()
    {
        $this->_fieldRenderer = null;
        $this->_dirRenderer = null;

        $this->addColumn('field', array('label' => Mage::helper('Antidot')->__('Sortable')));
        $this->addColumn('dir', array('label' => Mage::helper('Antidot')->__('Direction')));
        
        $this->_addAfter = false;
        $this->_add = false;
    }
    
    /**
     * {@inherit}
     */
    protected function _renderCellTemplate($columnName)
    {
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        switch($columnName) {
            case 'field':
                return $this->_getFieldRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setOptions(Mage::getModel("Antidot/System_Config_Sort")->toOptionArray())
                    ->toHtml();
            case 'dir':
                return $this->_getDirRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setOptions(Mage::getModel("Antidot/System_Config_Dir")->toOptionArray())
                    ->toHtml();
        }

        return parent::_renderCellTemplate($columnName);
    }
    
    /**
     * {@inherit}
     */
    protected function _getFieldRenderer()
    {
        if (!$this->_fieldRenderer) {
            $this->_fieldRenderer = $this->getLayout()
                   ->createBlock('Antidot/Html_Select')
                   ->setIsRenderToJsTemplate(true);
        }
        return $this->_fieldRenderer;
    }
    
    /**
     * {@inherit}
     */
    protected function _getDirRenderer()
    {
        if (!$this->_dirRenderer) {
            $this->_dirRenderer = $this->getLayout()
                   ->createBlock('Antidot/Html_Select')
                   ->setIsRenderToJsTemplate(true);
        }
        return $this->_dirRenderer;
    }
    
    /**
     * Assign extra parameters to row
     *
     * @param Varien_Object $row
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_'.$this->_getFieldRenderer()->calcOptionHash($row->getData('field')),
            'selected="selected"'
        );
        
        $row->setData(
            'option_extra_attr_'.$this->_getDirRenderer()->calcOptionHash($row->getData('dir')),
            'selected="selected"'
        );
    }
}

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
class MDN_Antidot_Block_System_Config_Form_Field_Array_Sort extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_sortRenderer;

    /**
     * {@inherit}
     */
    protected function _prepareToRender()
    {
        $this->_sortRenderer = null;
        $this->_labelRenderer = null;

        $this->addColumn('sort', array('label' => Mage::helper('Antidot')->__('Sortable')));

        // Disables "Add after" button
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('Antidot')->__('Add a field');
    }
    
    /**
     * {@inherit}
     */
    protected function _renderCellTemplate($columnName)
    {
        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        switch($columnName) {
            case 'sort':
                return $this->_getSortRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setOptions(Mage::getModel("Antidot/System_Config_Sort")->toOptionArray())
                    ->toHtml();
        }

        return parent::_renderCellTemplate($columnName);
    }
    
    /**
     * {@inherit}
     */
    protected function _getSortRenderer()
    {
        if (!$this->_sortRenderer) {
            $this->_sortRenderer = $this->getLayout()
                   ->createBlock('Antidot/Html_Select')
                   ->setIsRenderToJsTemplate(true);
        }
        return $this->_sortRenderer;
    }
    
    /**
     * Assign extra parameters to row
     *
     * @param Varien_Object $row
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_'.$this->_getSortRenderer()->calcOptionHash($row->getData('sort')),
            'selected="selected"'
        );
    }
}

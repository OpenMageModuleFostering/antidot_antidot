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
class MDN_Antidot_Block_System_Config_Form_Field_Array_Facet extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_facetRenderer;
    protected $_orderRenderer;
    protected $_multipleRenderer;

    /**
     * {@inherit}
     */
    protected function _prepareToRender()
    {
        $this->_facetRenderer = null;
        $this->_orderRenderer = null;
        $this->_multipleRenderer = null;

        $this->addColumn('facet', array('label' => Mage::helper('Antidot')->__('Facet')));
        $this->addColumn('order', array('label' => Mage::helper('Antidot')->__('Sort')));
        $this->addColumn('multiple', array('label' => Mage::helper('Antidot')->__('Multiple selections')));

        // Disables "Add after" button
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('Antidot')->__('Add a field');
    }
    
    /**
     * {@inherit}
     */
    protected function _renderCellTemplate($columnName)
    {

        $inputName  = $this->getElement()->getName() . '[#{_id}]['.$columnName.']';
        switch($columnName) {
            case 'facet':
                return $this->_getFacetRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setOptions(Mage::getModel("Antidot/System_Config_Facet")->toOptionArray())
                    ->toHtml();
            case 'order':
                return $this->_getOrderRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setWidth(50)
                    ->setOptions(Mage::getModel("Antidot/System_Config_Number")->toOptionArray(10))
                    ->toHtml();
            case 'multiple':
                return $this->_getMultipleRenderer()
                    ->setName($inputName)
                    ->setTitle($columnName)
                    ->setOptions(Mage::getModel("Antidot/System_Config_DisableEnable")->toOptionArray())
                    ->toHtml();
        }

        return parent::_renderCellTemplate($columnName);
    }
    
    /**
     * {@inherit}
     */
    protected function _getFacetRenderer()
    {
        if (!$this->_facetRenderer) {
            $this->_facetRenderer = $this->getLayout()
                   ->createBlock('Antidot/Html_Select')
                   ->setIsRenderToJsTemplate(true);
        }
        return $this->_facetRenderer;
    }
    
    /**
     * {@inherit}
     */
    protected function _getOrderRenderer()
    {
        if (!$this->_orderRenderer) {
            $this->_orderRenderer = $this->getLayout()
                   ->createBlock('Antidot/Html_Select')
                   ->setIsRenderToJsTemplate(true);
        }
        return $this->_orderRenderer;
    }

    /**
     * {@inherit}
     */
    protected function _getMultipleRenderer()
    {
        if (!$this->_multipleRenderer) {
            $this->_multipleRenderer = $this->getLayout()
                ->createBlock('Antidot/Html_Select')
                ->setIsRenderToJsTemplate(true);
        }
        return $this->_multipleRenderer;
    }
    
    /**
     * Assign extra parameters to row
     *
     * @param Varien_Object $row
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_'.$this->_getFacetRenderer()->calcOptionHash($row->getData('facet')),
            'selected="selected"'
        );
        
        $row->setData(
            'option_extra_attr_'.$this->_getOrderRenderer()->calcOptionHash($row->getData('order')),
            'selected="selected"'
        );

        $row->setData(
            'option_extra_attr_'.$this->_getMultipleRenderer()->calcOptionHash($row->getData('multiple')),
            'selected="selected"'
        );
    }

    /**
     * Override this parent method to avoid conversion from & => &amp;
     *
     *
     * @return array
     */
    public function getArrayRows()
    {
        if (null !== $this->_arrayRowsCache) {
            return $this->_arrayRowsCache;
        }
        $result = array();
        /** @var Varien_Data_Form_Element_Abstract */
        $element = $this->getElement();
        if ($element->getValue() && is_array($element->getValue())) {
            foreach ($element->getValue() as $rowId => $row) {
                //foreach ($row as $key => $value) {
                //    $row[$key] = $this->escapeHtml($value);
                //}
                $row['_id'] = $rowId;
                $result[$rowId] = new Varien_Object($row);
                $this->_prepareArrayRow($result[$rowId]);
            }
        }
        $this->_arrayRowsCache = $result;
        return $this->_arrayRowsCache;
    }

}

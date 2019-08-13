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
class MDN_Antidot_Block_System_Config_Form_Field_Array_AdditionalFeed extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_valueRenderer;

    /**
     * {@inherit}
     */
    protected function _prepareToRender()
    {
        $this->_valueRenderer = null;

        $this->addColumn('value', array('label' => Mage::helper('Antidot')->__('Feed ID')));

        // Disables "Add after" button
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('Antidot')->__('Add a feed');
    }
    
    /**
     * {@inherit}
     */
    protected function _renderCellTemplate($columnName)
    {
        return parent::_renderCellTemplate($columnName);
    }
    
    /**
     * {@inherit}
     */
    protected function _getValueRenderer()
    {
        if (!$this->_valueRenderer) {
            $this->_valueRenderer = $this->getLayout()
                   ->createBlock('Antidot/Html_Select')
                   ->setIsRenderToJsTemplate(true);
        }
        return $this->_valueRenderer;
    }
    
    /**
     * Assign extra parameters to row
     *
     * @param Varien_Object $row
     */
    protected function _prepareArrayRow(Varien_Object $row)
    {
        $row->setData(
            'option_extra_attr_'.$this->_getValueRenderer()->calcOptionHash($row->getData('value')),
            'selected="selected"'
        );
    }
}

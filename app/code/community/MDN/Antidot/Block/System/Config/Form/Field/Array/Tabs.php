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
 * @copyright  Copyright (c) 2015 Antidot (http://www.antidot.net)
 * @author : Antidot devmagento@antidot.net
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Antidot_Block_System_Config_Form_Field_Array_Tabs extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{

    /**
     * Check if columns are defined, set template
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('antidot/system/config/form/field/tabs.phtml');
    }

    /**
     * {@inherit}
     */
    protected function _prepareToRender()
    {

        $this->addColumn('tab', array());
        $this->addColumn('active', array());
        $this->addColumn('show_noresult', array());

    }
    

    /**
     * Obtain existing data from form element
     *
     * Each row will be instance of Varien_Object
     *
     * @return array
     */
    public function getArrayRows()
    {

        /** @var Varien_Data_Form_Element_Abstract */
        $element = $this->getElement();
        $elementValues = $element->getValue();

        $resultTypes = array(
            'products' => Mage::helper('Antidot')->__('Products'),
            'articles' => Mage::helper('Antidot')->__('Articles'),
            'stores' => Mage::helper('Antidot')->__('Stores')
        );

        $result=array();
        if ($elementValues) {
            foreach ($elementValues as $rowId => $elementValue) {
                $tab = $elementValue['tab'];
                $elementValue['name'] = $resultTypes[$tab];
                $rowId = '_result_tab_'.$tab;
                $elementValue['_id'] = $rowId;
                unset($resultTypes[$tab]);
                $result[$rowId] = new Varien_Object($elementValue);
            }
        }

        /**
         * if no data : set values by default :
         */
        foreach ($resultTypes as $typeResult => $label) {
            $rowId = '_result_tab_'.$typeResult;
            $row = array();
            $row['_id'] = $rowId;
            $row['tab'] = $typeResult;
            $row['name'] = $label;

            //Add the values from element config
            $row['active'] = '';
            $row['show_noresult'] = '';

            $result[$rowId] = new Varien_Object($row);
        }

        return $result;

    }

}

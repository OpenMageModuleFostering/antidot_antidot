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
class MDN_Antidot_Admin_PushController extends Mage_Adminhtml_Controller_Action 
{
    
    /**
     * Generate the category file, call from back office
     */
    public function CategoryAction()
    {
        try
        {
            if (Mage::getModel('Antidot/Observer')->categoriesFullExport('UI')) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('Antidot')->__('Categories exported')
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addWarning(
                    Mage::helper('Antidot')->__('No Category to export')
                );
            }
        }
        catch(Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('Antidot')->__('An error occured : %s', $ex->getMessage())
            );
        }
        $this->_redirectReferer();
    }
    
    /**
     * Generate the catalog file, call from back office
     */
    public function ProductAction()
    {
        try {
            if (Mage::getModel('Antidot/Observer')->catalogFullExport('UI')) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('Antidot')->__('Catalog exported')
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addWarning(
                    Mage::helper('Antidot')->__('No Product to export')
                );
            }
        }
        catch(Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('Antidot')->__('An error occured : %s', $ex->getMessage())
            );
        }
        $this->_redirectReferer();
    }
}

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
class MDN_Antidot_Adminhtml_Antidot_PushController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Is the controller allowed (compatibility patch SUPEE-6285)
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/config/antidot');
    }

    /**
     * Generate the category file, call from back office
     */
    public function categoryAction()
    {
        try
        {
            if (Mage::getModel('Antidot/observer')->categoriesFullExport('UI')) {
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
    public function productAction()
    {
        try {
            if (Mage::getModel('Antidot/observer')->catalogFullExport('UI')) {
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

    /**
     * Generate the articles file, call from back office
     */
    public function articleAction()
    {
        try {
            if (Mage::getModel('Antidot/observer')->articlesFullExport('UI')) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('Antidot')->__('Articles exported')
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addWarning(
                    Mage::helper('Antidot')->__('No Article to export')
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
     * Generate the stores file, call from back office
     */
    public function storeAction()
    {
        try {
            if (Mage::getModel('Antidot/observer')->storesFullExport('UI')) {
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('Antidot')->__('Stores exported')
                );
            } else {
                Mage::getSingleton('adminhtml/session')->addWarning(
                    Mage::helper('Antidot')->__('No store to export')
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

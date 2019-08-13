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
class MDN_Antidot_Admin_PushController extends Mage_Adminhtml_Controller_Action 
{
    
    /**
     * Generate the category file, call from back office
     */
    public function CategoryAction()
    {
        Mage::getModel('Antidot/Observer')->categoriesFullExport();
        $this->_redirectReferer();
    }
    
    /**
     * Generate the catalog file, call from back office
     */
    public function ProductAction()
    {
        try
        {
            Mage::getModel('Antidot/Observer')->catalogFullExport();
        }
        catch(Exception $ex)
        {
            Mage::getSingleton('adminhtml/session')->addError(mage::helper('Antidot')->__('An error occured : %s', $ex->getMessage()));
        }
        $this->_redirectReferer();
    }
}

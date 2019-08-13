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
class MDN_Antidot_Front_SearchController extends Mage_Core_Controller_Front_Action 
{
    
    /**
     * Method call by autocomplete
     */
    public function SuggestAction() 
    {
        if (!$query = $this->getRequest()->getParam('q', false)) {
            $this->getResponse()->setRedirect(Mage::getSingleton('core/url')->getBaseUrl());
        }

        $format = 'html';
        if ($formatParam = $this->getRequest()->getParam('format', false)) {
            $format = $formatParam;
        }
        
        $this->getResponse()->setBody(Mage::getModel('Antidot/search_suggest')->get($query, $format));
    }
}

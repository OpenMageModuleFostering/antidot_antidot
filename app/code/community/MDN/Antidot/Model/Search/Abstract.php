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
class MDN_Antidot_Model_Search_Abstract extends Mage_Core_Model_Abstract 
{
    protected $afsService;
    protected $afsHost;
    protected $afsStatus;
    
    protected $isConfigured = false;
    
    /**
     * Init Antidot API
     */
    public function _construct()
    {
        //set_include_path(get_include_path().':'.MAGENTO_ROOT.DS.'lib'.DS.'antidot'.DS);
        require_once "antidot/afs_lib.php";

        $config = Mage::getStoreConfig('antidot/web_service');
        if(isset($config['host']) && isset($config['service'])) {
            $this->afsHost    = $config['host'];
            $this->afsService = (int)$config['service'];
            $this->afsStatus  = $config['status'];

            $this->isConfigured = true;
        }
    }
    
    /**
     * Return the user session
     * 
     * @return string
     */
    protected function getSession()
    {
        $session = Mage::getSingleton('core/session');
        if(!$antidotSession = $session->getData('antidot_session')) {
            $antidotSession = uniqid();
            $session->setData('antidot_session', $antidotSession);
        }
        
        return $antidotSession;
    }

    /**
     * Return the user Id
     *
     * @return string
     */
    protected function getUserId()
    {
        $session = Mage::getSingleton('customer/session');
        if ($session->isLoggedIn()) {
            $customerSession = $session->getCustomerId();
        } else {
            $customerSession = $this->getSession();
        }
        return $customerSession;
    }

}
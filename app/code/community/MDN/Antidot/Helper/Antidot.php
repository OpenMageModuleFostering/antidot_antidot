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
class MDN_Antidot_Helper_Antidot extends MDN_Antidot_Helper_Data
{
    /**
     * {@inherit}
     */
    public function getEngineConfigData($prefix = '', $store = null)
    {
        return parent::getEngineConfigData('antidot_', $store);
    }

    /**
     * Should Antidot also search on options?
     *
     * @return bool
     */
    public function shouldSearchOnOptions()
    {
        return Mage::getStoreConfigFlag('catalog/search/andidot_enable_options_search');
    }
}
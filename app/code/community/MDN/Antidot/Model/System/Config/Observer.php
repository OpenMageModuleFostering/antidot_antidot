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
class MDN_Antidot_Model_System_Config_Observer extends Mage_Core_Model_Abstract
{

    /**
     *  Instant Search :
     *  Hide Search Engine parameters on "Instant Search" contracts
     *
     * @param Varien_Event_Observer $observe
     * @return $this
     */
    public function onInstantSearchHideEngine(Varien_Event_Observer $observe)
    {
        $config = $observe->getConfig();

        /** @var MDN_Antidot_Model_Search_Search $search */
        $search = Mage::getSingleton('Antidot/search_search');
        if ($search->isInstantSearch()) {
            $antidotEngine = $config->getNode('sections/antidot/groups/engine');
            $antidotEngine->show_in_default = "0";
            $antidotEngine->show_in_website = "0";
            $antidotEngine->show_in_store = "0";

            $promoteEngine = $config->getNode('sections/antidot/groups/promote');
            $promoteEngine->show_in_default = "0";
            $promoteEngine->show_in_website = "0";
            $promoteEngine->show_in_store = "0";
        }

        return $this;
    }
}

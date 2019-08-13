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


/**
 * MCNX-249 : synchronise dropdownlist between AFSTore config and Catalog Seach config :
 *
 * Enterprise Edition allow to modify catalog/search/engine system config in System > Config > Catalog > Catalog > Catalog Search,
 * like Afsstore module allow to modify catalog/search/engine system config in System > Config > Catalog > AFS@Store
 * (see MDN_Antidot_Model_System_Config_Backend_Engine)
 *
 * Then the sources of the dropdownlist must be identical, otherwise the value can be crushed
 *
 * Community Edition doesn't allow to modify this value, it hasn't source class for that, then we simulate it :
 */
if ((string)Mage::getConfig()->getModuleConfig('Enterprise_Search')->active != 'true') {

    class Enterprise_Search_Model_Adminhtml_System_Config_Source_Engine {
        public function toOptionArray()
        {
            $engines = array(
                'catalogsearch/fulltext_engine' => Mage::helper('adminhtml')->__('Magento'),
            );
            $options = array();
            foreach ($engines as $k => $v) {
                $options[] = array(
                    'value' => $k,
                    'label' => $v
                );
            }
            return $options;
        }
    }
}
class MDN_Antidot_Model_System_Config_Source_Engine extends Enterprise_Search_Model_Adminhtml_System_Config_Source_Engine
{
    /**
     * {@inherit}
     */
    public function toOptionArray()
    {
        $options = array_reverse(parent::toOptionArray());

        $options[] = array(
            'value' => 'Antidot/engine_antidot',
            'label' => Mage::helper('adminhtml')->__('AFS@Store')
        );

        return array_reverse($options);
    }
}

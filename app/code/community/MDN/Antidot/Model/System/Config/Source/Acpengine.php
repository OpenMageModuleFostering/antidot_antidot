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


class MDN_Antidot_Model_System_Config_Source_Acpengine
{
    /**
     * {@inherit}
     */
    public function toOptionArray()
    {
        $engines = array(
            'Antidot/engine_antidot' => Mage::helper('adminhtml')->__('AFS@Store'),
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

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

class MDN_Antidot_Model_System_Config_Source_Redirect
{

    const ALWAYS = '1';
    const UNLESS_SPELLCHECK = '2';
    const NEVER = '3';


    /**
     * {@inherit}
     */
    public function toOptionArray()
    {
        $options = array();
        $options[] = array(
            'value' => self::ALWAYS,
            'label' => Mage::helper('Antidot')->__('Always')
        );
        $options[] = array(
            'value' => self::UNLESS_SPELLCHECK,
            'label' => Mage::helper('Antidot')->__('Unless there\'s a spellcheck')
        );
        $options[] = array(
            'value' => self::NEVER,
            'label' => Mage::helper('Antidot')->__('Never')
        );
        return $options;
    }
}

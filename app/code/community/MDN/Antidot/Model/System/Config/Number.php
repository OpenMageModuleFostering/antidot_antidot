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
class MDN_Antidot_Model_System_Config_Number
{
    /**
     * {@inherit}
     */
    public function toOptionArray($nb)
    {
        $nb = !empty($nb) ? (int)$nb : 10;
        $options = array();
        for($i = 1; $i < $nb+1; $i++) {
            $options[] = array('value' => $i, 'label' => $i);
        }

        return $options;
    }
}

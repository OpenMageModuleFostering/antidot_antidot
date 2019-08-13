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
class MDN_Antidot_Model_System_Config_Options
{
    /**
     * {@inherit}
     */
    public function toOptionArray($nb)
    {
        $nb = !empty($nb) ? (int)$nb : 20;
        $options = array();
        $options[] = array('value' => 0, 'label' => Mage::helper('Antidot')->__('All'));
        for($i = 5; $i < $nb+1; $i+= 5) {
            $options[] = array('value' => $i, 'label' => $i);
        }

        return $options;
    }
}

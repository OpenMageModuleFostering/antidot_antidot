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
class MDN_Antidot_Model_System_Config_Backend_Engine extends Mage_Core_Model_Config_Data
{
    /**
     * {@inherit}
     */
    protected function _afterSave()
    {
        Mage::getModel('core/config')->saveConfig(
                'catalog/search/engine', 
                $this->getData('groups/engine/fields/engine/value')
        );

        return $this;
    }
}

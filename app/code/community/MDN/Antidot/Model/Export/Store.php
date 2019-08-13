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
class MDN_Antidot_Model_Export_Store extends MDN_Antidot_Model_Export_Abstract
{
    
    const TYPE = 'STORE';
    const FILE_PATH_CONF = 'stores';
    const FILENAME_XML   = 'stores-%s-%s-%s.xml';
    const FILENAME_ZIP   = '%s_full_%s_stores.zip';
    const XSD   = 'http://ref.antidot.net/store/latest/stores.xsd';

    /**
     * {@inherit}
     */
    public function getPafName() {
        return "Stores";
    }


}

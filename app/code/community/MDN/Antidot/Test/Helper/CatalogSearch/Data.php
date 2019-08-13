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
class MDN_Antidot_Test_Helper_CatalogSearch_Data extends EcomDev_PHPUnit_Test_Case {


    /**
     * MCNX-210 : make get helper work with this "wrong" case : catalogSearch instead of catalgsearch
     *
     * @test
     */
    public function testCase() {

        $helper = Mage::helper('catalogSearch');

        $this->assertEquals(
            'MDN_Antidot_Helper_CatalogSearch_Data',
            get_class($helper)
        );

    }

}

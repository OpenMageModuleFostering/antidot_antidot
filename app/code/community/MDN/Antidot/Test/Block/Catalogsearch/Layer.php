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
class MDN_Antidot_Test_Block_Catalogsearch_Layer extends EcomDev_PHPUnit_Test_Case
{

    public static function setUpBeforeClass()
    {

        //avoid errors when session_start is called during the test
        @session_start();

    }
    /**
     *
     */
    public function testRewrite()
    {

        $mockLayer = $this->getModelMock('catalogsearch/layer', array('getProductCollection'));
        $mockLayer->expects($this->any())
            ->method('getProductCollection')
            ->will($this->returnValue(Mage::getResourceModel('catalog/product_collection')));

        $this->replaceByMock('singleton', 'catalogsearch/layer', $mockLayer);

        /**
         * Create block layer for community edition and check it's rewrited by Antidot module's one
         */
        $blockCE = Mage::app()->getLayout()->createBlock('catalogsearch/layer');
        $this->assertEquals(
            'MDN_Antidot_Block_Catalogsearch_Layer',
            get_class($blockCE)
        );

        /**
         * Create block layer for Enterprise edition and check it's rewrited by Antidot module's one
         */
        $blockEE = Mage::app()->getLayout()->createBlock('enterprise_search/catalogsearch_layer');
        $this->assertEquals(
            'MDN_Antidot_Block_Catalogsearch_Layer',
            get_class($blockEE)
        );

    }
}
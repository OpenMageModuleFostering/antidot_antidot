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
class MDN_Antidot_Test_Block_Catalogsearch_Result extends EcomDev_PHPUnit_Test_Case
{

    public static function setUpBeforeClass()
    {

        //avoid errors when session_start is called during the test
        @session_start();

    }

    /**
     *
     * @test
     * @loadFixture
     */
    public function testSetListOrders()
    {


        /**
         * Create block result and check it's rewrited by Antidot module's one
         */

        $layout = Mage::app()->getLayout();
        //créer le block head qui est utilisé dans la méthode _prepareLayout du blok result :
        $layout->createBlock('page/html_head', 'head');
        /** @var MDN_Antidot_Block_Catalogsearch_Result $blockResult */
        $blockResult = $layout->createBlock('catalogsearch/result');
        $this->assertEquals(
            'MDN_Antidot_Block_Catalogsearch_Result',
            get_class($blockResult)
        );

        /**
         * Create block product list and at it as the result block child
         */
        $blockList = $layout->createBlock('catalog/product_list');
        $blockResult->setChild('search_result_list', $blockList);

        $blockResult->setListOrders();

        $this->assertEquals(
            'afs:relevance',
            $blockResult->getListBlock()->getSortBy()
        );

    }
}
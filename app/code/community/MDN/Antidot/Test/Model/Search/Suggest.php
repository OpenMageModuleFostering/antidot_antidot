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
class MDN_Antidot_Test_Model_Search_Suggest extends EcomDev_PHPUnit_Test_Case
{

    /**
     * MCNX-226 and MCNX-227 : inverted storeid and websiteid on acp ws url cause no results
     *
     * @test
     * @loadFixture
     */
    public function testSuggest() {

        Mage::app()->setCurrentStore(5);

        /** @var $observer MDN_Antidot_Model_Search_Suggest */
        $suggest = Mage::getModel('Antidot/search_suggest');

        $feeds = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($suggest, 'getFeeds', array('products'));

        $this->assertEquals(
            'featured_products_5_fr&afs:feed=categories_3_fr&afs:feed=brands_3_fr&afs:feed=articles_3_fr&afs:feed=stores_3_fr',
            $feeds
        );

    }
}

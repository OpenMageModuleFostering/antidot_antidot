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
class MDN_Antidot_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{

   /**
     * Test method MDN_Antidot_Helper_Data returnBytes
     */
    function testReturnBytes()
    {
        /** @var $observer MDN_Antidot_Helper_Data */
        $helper = Mage::helper('Antidot');

        foreach (array("2048M" => "2147483648", "512M" => "536870912", "4G" => "4294967296") as $value => $expected) {
            $result = $helper->returnBytes($value);
            $this->assertEquals(
                $expected,
                $result
            );
        }

    }

    /**
     * Test method MDN_Antidot_Helper_Data getActiveResultTabs Case 0 :
     * No fixture, Mage::getStoreConfig('antidot/engine/result_tabs') return empty
     * No tabs are configured in BO
     *
     */
    function testGetActiveResultTabsCase0()
    {
        /** @var $observer MDN_Antidot_Helper_Data */
        $helper = Mage::helper('Antidot');

        // Create a stub for simulate MDN_Antidot_Model_Resource_Catalog_Product_Collection getTotalResult
        $mockCollection = $this->getMockBuilder('MDN_Antidot_Model_Resource_Catalog_Product_Collection_Mock')
            ->getMock();

        //There's results in the 3 tabs
        $mockCollection->expects($this->any())->method('getTotalResult')
            ->will($this->returnValueMap(
                array(
                    array('products', 10),
                    array('articles', 11),
                    array('stores', 12),
                    array(null, 33),
                )
            ));

        $tabs = $helper->getActiveResultTabs($mockCollection);

        //must return no tabs
        $this->assertEquals(
            0,
            count($tabs)
        );
    }

    /**
     * Test method MDN_Antidot_Helper_Data getActiveResultTabs Case 1 :
     *
     *  In the fixture all tabs are configured, actives, with show_noresult=true
     *
     * @loadFixture
     */
    function testGetActiveResultTabsCase1()
    {
        /** @var $observer MDN_Antidot_Helper_Data */
        $helper = Mage::helper('Antidot');

        // Create a stub for simulate MDN_Antidot_Model_Resource_Catalog_Product_Collection getTotalResult
        $mockCollection = $this->getMockBuilder('MDN_Antidot_Model_Resource_Catalog_Product_Collection_Mock')
            ->getMock();

        //There's results in the 3 tabs
        $mockCollection->expects($this->any())->method('getTotalResult')
            ->will($this->returnValueMap(
                array(
                    array('products', 10),
                    array('articles', 11),
                    array('stores', 12),
                    array(null, 33),
                )
            ));

        $tabs = $helper->getActiveResultTabs($mockCollection);

        //must return 3 tabs, there're all configured in BO
        $this->assertEquals(
            3,
            count($tabs)
        );
        $this->assertTrue($tabs[0]['selected']);
        $this->assertFalse(isset($tabs[1]['selected']));
        $this->assertFalse(isset($tabs[2]['selected']));

        // Create a stub for simulate MDN_Antidot_Model_Resource_Catalog_Product_Collection getTotalResult
        $mockCollection = $this->getMockBuilder('MDN_Antidot_Model_Resource_Catalog_Product_Collection_Mock')
            ->getMock();

        //There's no results in any tabs
        $mockCollection->expects($this->any())->method('getTotalResult')
            ->will($this->returnValueMap(
                array(
                    array('products', 0),
                    array('articles', 0),
                    array('stores', 0),
                    array(null, 0),
                )
            ));

        $tabs = $helper->getActiveResultTabs($mockCollection);

        //must return 3 tabs, there're all configured in BO, with show_noresult=true
        $this->assertEquals(
            3,
            count($tabs)
        );
        $this->assertTrue($tabs[0]['selected']);
        $this->assertFalse(isset($tabs[1]['selected']));
        $this->assertFalse(isset($tabs[2]['selected']));


    }

    /**
     * Test method MDN_Antidot_Helper_Data getActiveResultTabs Case 2 :
     *
     *  In the fixture only product and articles tabs are configured actives, without show_noresult=true
     *
     * @loadFixture
     */
    function testGetActiveResultTabsCase2()
    {
        /** @var $observer MDN_Antidot_Helper_Data */
        $helper = Mage::helper('Antidot');

        // Create a stub for simulate MDN_Antidot_Model_Resource_Catalog_Product_Collection getTotalResult
        $mockCollection = $this->getMockBuilder('MDN_Antidot_Model_Resource_Catalog_Product_Collection_Mock')
            ->getMock();

        //There's results in the 3 tabs
        $mockCollection->expects($this->any())->method('getTotalResult')
            ->will($this->returnValueMap(
                array(
                    array('products', 10),
                    array('articles', 11),
                    array('stores', 12),
                    array(null, 33),
                )
            ));

        $tabs = $helper->getActiveResultTabs($mockCollection);

        //must return the 2 tabs actived in BO , only products and articles
        $this->assertEquals(
            2,
            count($tabs)
        );

        $this->assertEquals(
            'products',
            $tabs[0]['tab']
        );
        $this->assertEquals(
            'articles',
            $tabs[1]['tab']
        );
        $this->assertTrue($tabs[0]['selected']);
        $this->assertFalse(isset($tabs[1]['selected']));

        // Create a stub for simulate MDN_Antidot_Model_Resource_Catalog_Product_Collection getTotalResult
        $mockCollection = $this->getMockBuilder('MDN_Antidot_Model_Resource_Catalog_Product_Collection_Mock')
            ->getMock();

        //There's no results in any tabs
        $mockCollection->expects($this->any())->method('getTotalResult')
            ->will($this->returnValueMap(
                array(
                    array('products', 0),
                    array('articles', 0),
                    array('stores', 0),
                    array(null, 0),
                )
            ));

        $tabs = $helper->getActiveResultTabs($mockCollection);

        // only the first tab product is displayed, for messaing
        $this->assertEquals(
            1,
            count($tabs)
        );

        $this->assertEquals(
            'products',
            $tabs[0]['tab']
        );

        $this->assertTrue($tabs[0]['selected']);


        // Create a stub for simulate MDN_Antidot_Model_Resource_Catalog_Product_Collection getTotalResult
        $mockCollection = $this->getMockBuilder('MDN_Antidot_Model_Resource_Catalog_Product_Collection_Mock')
            ->getMock();

        //There's only result on articles
        $mockCollection->expects($this->any())->method('getTotalResult')
            ->will($this->returnValueMap(
                array(
                    array('products', 0),
                    array('articles', 12),
                    array('stores', 0),
                    array(null, 12),
                )
            ));

        $tabs = $helper->getActiveResultTabs($mockCollection);

        //must return the 2 tabs actived in BO without show_noresult => only the first tab product is displayed
        $this->assertEquals(
            1,
            count($tabs)
        );

        $this->assertEquals(
            'articles',
            $tabs[0]['tab']
        );

        $this->assertTrue($tabs[0]['selected']);

    }


}

class MDN_Antidot_Model_Resource_Catalog_Product_Collection_Mock {
    public function getTotalResult($tab = null) {
        return;
    }
}
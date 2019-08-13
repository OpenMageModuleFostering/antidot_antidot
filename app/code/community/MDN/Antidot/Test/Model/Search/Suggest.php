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

        /** @var $suggest MDN_Antidot_Model_Search_Suggest */
        $suggest = Mage::getModel('Antidot/search_suggest');

        $feeds = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($suggest, 'getFeeds', array());

        $this->assertEquals(
            'featured_products_5_fr&afs:feed=categories_3_fr&afs:feed=brands_3_fr',
            $feeds
        );


    }

    /**
     *
     * @test
     * @loadFixture
     */
    public function testFeedConfig() {

        Mage::app()->setCurrentStore(5);

        /** @var $suggest MDN_Antidot_Model_Search_Suggest */
        $suggest = Mage::getModel('Antidot/search_suggest');

        $feeds = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($suggest, 'getFeeds', array());

        $this->assertEquals(
            'featured_products_5_fr&afs:feed=categories_3_fr&afs:feed=articles_3_fr',
            $feeds
        );

        $feedOrder = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($suggest, 'getFeedOrder', array('products'));

        $this->assertEquals(
            'featured_products_5_fr,categories_3_fr,articles_3_fr',
            $feedOrder
        );


    }

    public function testPostProcessXml() {

        Mage::app()->setCurrentStore(1);

        /** @var $suggest MDN_Antidot_Model_Search_Suggest */
        $suggest = Mage::getModel('Antidot/search_suggest');

        $xml = new SimpleXMLElement('<afs:replies xmlns:afs="http://ref.antidot.net/v7/afs#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://ref.antidot.net/v7/afs# http://ref.antidot.net/v7.8/acp-reply.xsd" status="true">
<afs:header>
<afs:query textQuery="test"/>
</afs:header>
<afs:replySet name="featured_products_1_en">
<afs:meta uri="featured_products_1_en" totalItems="1" producer="acp"/>
<afs:reply label="Gaming Computer">
<afs:option key="url_thumbnail" value="http://magento.antidot.com/media/catalog/product/g/a/gaming-computer.jpg"/>
</afs:reply>
</afs:replySet>
</afs:replies>');
        $xmlProcessed = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($suggest, 'postProcessXml', array(&$xml));


        $this->assertRegExp('#media\/catalog\/product\/cache\/1\/thumbnail\/40x\/#',  $xmlProcessed->asXML());


    }
    
    public function testUserIdInBuildedUrl() {
        $mock = $this->getMockBuilder('MDN_Antidot_Model_Search_Suggest')->setMethods(array('getSession', 'getUserId'))->getMock();
        // We mock the getSession and getUserId as we don't have sessions in test env
        $mock->method('getSession')->willReturn('testSession');
        $mock->method('getUserId')->willReturn('testUserId');

        $buildedUrl = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($mock, 'buildUrl', array(''));
        $this->assertRegExp('/.*(afs:userId=testUserId($|&)).*/', $buildedUrl);
    }

    public function testgetUserIdLoggedIn() {
        $defaultCustomerId = 42;

        // We force the return of an customer id
        $mockSession = $this->getMockBuilder('Mage_Customer_Model_Session')
        ->disableOriginalConstructor()
        ->getMock();
        $mockSession->method('isLoggedIn')->willReturn(true);
        $mockSession->method('getCustomerId')->willReturn($defaultCustomerId);
        $this->replaceByMock('singleton', 'customer/session', $mockSession);

        // We also mock this Session model to avoid a warning when getSession
        // is called into buildUrl function
        $mockCoreSession = $this->getMockBuilder('Mage_Core_Model_Session')
        ->disableOriginalConstructor()
        ->getMock();
        $this->replaceByMock('singleton', 'core/session', $mockCoreSession);


        $suggest = Mage::getModel('Antidot/search_suggest');
        $buildedUrl = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($suggest, 'buildUrl', array(''));

        $this->assertRegExp('/.*(afs:userId='.$defaultCustomerId.'($|&)).*/', $buildedUrl);
    }

    public function testgetUserIdNotLoggedIn() {
        $defaultCustomerId = 42;

        $mockSession = $this->getMockBuilder('Mage_Customer_Model_Session')
        ->disableOriginalConstructor()
        ->getMock();
        $mockSession->method('isLoggedIn')->willReturn(false);
        $this->replaceByMock('singleton', 'customer/session', $mockSession);

        // We force the return of an antidot session id
        $mockCoreSession = $this->getMockBuilder('Mage_Core_Model_Session')
        ->disableOriginalConstructor()
        ->getMock();
        $mockCoreSession->method('getData')->with('antidot_session')->will($this->returnValue($defaultCustomerId));
        $this->replaceByMock('singleton', 'core/session', $mockCoreSession);

        $suggest = Mage::getModel('Antidot/search_suggest');
        $buildedUrl = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($suggest, 'buildUrl', array(''));

        $this->assertRegExp('/.*(afs:userId='.$defaultCustomerId.'($|&)).*/', $buildedUrl);
    }

}

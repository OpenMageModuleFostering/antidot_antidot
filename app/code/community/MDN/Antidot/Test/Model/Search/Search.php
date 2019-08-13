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

require_once('lib/antidot/COMMON/php-SAI/lib/CurlStub.php');
require_once('lib/antidot/AFS/SEARCH/TEST/DATA/introspection_responses.php');
require_once('lib/antidot/afs_lib.php');

class MDN_Antidot_Test_Model_Search_Search extends EcomDev_PHPUnit_Test_Case
{

    public static function setUpBeforeClass()
    {
        //avoid errors when session_start is called during the test
        @session_start();
    }

    /**
     * Check the filters genrated for each feed
     * @test
     */
    public function testGetQuery() {


        /** @var $search MDN_Antidot_Model_Search_Search */
        $search = Mage::getModel('Antidot/search_search');

        $params = array( 'page' => 1, 'limit'=>9, 'lang'=>'fr', 'sort'=>array("afs:relevance,DESC"));
        $params['filters'] = array(array('store'=>'"5"', 'website'=>'"2"'));
        $afsQuery = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($search, 'getQuery', array("test", $params));

        $filtersCatalog = $afsQuery->get_filters('Catalog');
        $this->assertEquals(
            array('store','website'),
            $filtersCatalog
        );

        $filtersArticles = $afsQuery->get_filters('Articles');
        $this->assertEquals(
            array('website'),
            $filtersArticles
        );



    }

    /**
     * Check the facets
     * @test
     */
    public function testGetFacets() {

        $curlConnector = new SAI_CurlStub();
        $mockBaseUrl = "localhost";
        $aboutRequestOpts = array(CURLOPT_URL => "http://$mockBaseUrl/bo-ws/about");
        $aboutResponse = ABOUT_RESPONSE;

        $curlConnector->setResponse($aboutResponse, $aboutRequestOpts);
        $curlConnector->setResponse(RESPONSE_FACETS_MULTIFEED);
        $afsSearch = new AfsSearch($mockBaseUrl, '71003', AfsServiceStatus::STABLE, $curlConnector);

        Mage::unregister('test_afsSearch');
        Mage::register('test_afsSearch', $afsSearch);

        /** @var $search MDN_Antidot_Model_Search_Search */
        $search = Mage::getModel('Antidot/search_search');

        $facets = $search->getFacets();

        $this->assertEquals(
            1,
            count($facets)
        );

        $this->assertTrue(
            array_key_exists('model',$facets)
        );

    }

    /**
     * Check instant search
     * @test
     */
    public function testIsInstantSearch() {

        $curlConnector = new SAI_CurlStub();
        $mockBaseUrl = "localhost";
        $aboutRequestOpts = array(CURLOPT_URL => "http://$mockBaseUrl/bo-ws/about");
        $aboutResponse = ABOUT_RESPONSE;

        $curlConnector->setResponse($aboutResponse, $aboutRequestOpts);
        $curlConnector->setResponse(RESULT_WITH_FACETS_FLAT);

        $afsSearch = new AfsSearch($mockBaseUrl, '71003', AfsServiceStatus::STABLE, $curlConnector);

        Mage::unregister('test_afsSearch');
        Mage::register('test_afsSearch', $afsSearch);

        /** @var $search MDN_Antidot_Model_Search_Search */
        $search = Mage::getModel('Antidot/search_search');

        $instantSearch = $search->isInstantSearch();

        $this->assertTrue($instantSearch);


    }
}

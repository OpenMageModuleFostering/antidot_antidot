<?php

require_once('lib/antidot/COMMON/php-SAI/lib/CurlStub.php');
require_once('lib/antidot/AFS/SEARCH/TEST/DATA/introspection_responses.php');
require_once('lib/antidot/afs_lib.php');

class MDN_Antidot_Test_Model_System_Config_Sort extends EcomDev_PHPUnit_Test_Case
{


    public static function setUpBeforeClass()
    {
        $curlConnector = new SAI_CurlStub();
        $mockBaseUrl = "localhost";
        $aboutRequestOpts = array(CURLOPT_URL => "http://$mockBaseUrl/bo-ws/about");
        $aboutResponse = ABOUT_RESPONSE;

        $curlConnector->setResponse($aboutResponse, $aboutRequestOpts);
        $curlConnector->setResponse(RESPONSE_FACETS_MULTIFEED);
        $afsSearch = new AfsSearch($mockBaseUrl, '71003', AfsServiceStatus::STABLE, $curlConnector);

        Mage::unregister('test_afsSearch');
        Mage::register('test_afsSearch', $afsSearch);
    }

    /**
     * Test toOptionArray method
     * case 1
     *
     * @loadFixture
     * @loadExpectation testToOptionArray.yaml
     * @test
     */
    public function testToOptionArray1()
    {
        $expected = $this->expected("1-1")->getOptions();
        $this->toOptionArray($expected);
    }

    /**
     * Test toOptionArray method
     * case 2
     *
     * @loadFixture
     * @loadExpectation testToOptionArray.yaml
     * @test
     */
    public function testToOptionArray2()
    {
        $expected = $this->expected("1-2")->getOptions();
        $this->toOptionArray($expected);
    }

    /**
     * Test toOptionArray method
     * case 3
     *
     * @loadFixture
     * @loadExpectation testToOptionArray.yaml
     * @test
     */
    public function testToOptionArray3()
    {
        $expected = $this->expected("1-3")->getOptions();
        $this->toOptionArray($expected);
    }

    /**
     * Test toOptionArray method
     *
     * MCNX-28 : add stock, price and promotion as "static" option
     */
    private function toOptionArray(array $expected)
    {

        Mage::app()->setCurrentStore(2);
        Mage::app()->getLocale()->setLocaleCode('en_US');
        Mage::app()->getTranslator()->setLocale('en_US')->init('adminhtml', true);

        /** @var $configSort MDN_Antidot_Model_System_Config_Sort */
        $configSort = Mage::getModel('Antidot/system_config_sort');
        $configSort->reinitOptions();

    	$value= $configSort->toOptionArray();

    	$this->assertEquals(
    	    $expected,
    	    $value
    	);
    }
}
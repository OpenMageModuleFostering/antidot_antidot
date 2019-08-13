<?php


class MDN_Antidot_Test_Model_System_Config_Sort extends EcomDev_PHPUnit_Test_Case
{

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
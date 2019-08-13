<?php


class MDN_Antidot_Test_Model_Resource_Engine_Antidot extends EcomDev_PHPUnit_Test_Case
{

	/**
     * MCNX-171 Test extraction of the raw value of multiselect facet into array of values
     * 
     * @test
     */
    public function testExtractMultiSelectValues() {

        /** @var  $antidotEngine  MDN_Antidot_Model_Resource_Engine_Antidot */
        $antidotEngine = Mage::getResourceSingleton('Antidot/engine_antidot');

        /**
         * test if no value is well extracted
         */
    	$array = $antidotEngine->extractMultiSelectValues('');
    	$this->assertEquals($array, array('""'));

        /**
         * test if empty value is well extracted
         */
        $array = $antidotEngine->extractMultiSelectValues('""');
        $this->assertEquals($array, array('""'));

        /**
         * test if multi-value is well extracted
         */
        $array = $antidotEngine->extractMultiSelectValues('"Pommes","Poires","Pêches"');
        $this->assertEquals($array, array('"Pommes"','"Poires"','"Pêches"'));

        /**
         * test if a mono-value with coma is well extracted
         */
        $array = $antidotEngine->extractMultiSelectValues('"Pommes,Poires,Pêches"');
        $this->assertEquals($array, array('"Pommes,Poires,Pêches"'));

    }
}
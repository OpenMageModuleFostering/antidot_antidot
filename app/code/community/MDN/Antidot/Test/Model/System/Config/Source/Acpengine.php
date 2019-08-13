<?php


class MDN_Antidot_Test_Model_System_Config_Source_Acpengine extends EcomDev_PHPUnit_Test_Case
{


    /**
     * Test toOptionArray method
     *
     * MCNX-249 : synchronise dropdownlist between AFSTore config and Catalog Seach config
     */
    public function testToOptionArray()
    {

        /** @var $configSort MDN_Antidot_Model_System_Config_Source_Engine */
        $configSourceEngine = Mage::getModel('Antidot/system_config_source_acpengine');

    	$values = $configSourceEngine->toOptionArray();

        $this->assertEquals(2 , count($values));
        
    }

}

<?php


class MDN_Antidot_Test_Model_System_Config_Source_Redirect extends EcomDev_PHPUnit_Test_Case
{


    /**
     * Test toOptionArray method
     *
     * MCNX-260
     */
    public function testToOptionArray()
    {

        /** @var $configSort MDN_Antidot_Model_System_Config_Source_Engine */
        $configSourceEngine = Mage::getModel('Antidot/system_config_source_redirect');

    	$values = $configSourceEngine->toOptionArray();

        $this->assertEquals(3 , count($values));

    }

}

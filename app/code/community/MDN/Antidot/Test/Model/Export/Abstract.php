<?php


class MDN_Antidot_Test_Model_Export_Abstract extends EcomDev_PHPUnit_Test_Case
{

    /**
     * test Garbage Collection
     *
     * gc is enabled in fixture
     * percentage is set to 10% in fixture
     *
     * @loadFixture
     */
    public function testGarbageCollection() {

        $export = Mage::getModel('Antidot/export_product');
        /* store the default limit */
        $initialLimit = ini_get('memory_limit');

        //set memory limit to 5x the used memory : then the used memory will be more than 10% of the limit
        ini_set('memory_limit', memory_get_usage(true) * 5);
        $done = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export, 'garbageCollection', array());
        $this->assertTrue($done);

        //set memory limit to 10x the used memory : then the used memory will be less than 10% of the limit
        ini_set('memory_limit', memory_get_usage(true) * 10);
        $done = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export, 'garbageCollection', array());
        $this->assertFalse($done);

        ini_set('memory_limit', $initialLimit);
    }

}
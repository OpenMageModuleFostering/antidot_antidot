<?php


class MDN_Antidot_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case
{

	/**
     * Test owner for filename generation
     * 
     * @test
	 * loadFixture
     * doNotIndexAll
     * dataProvider dataProvider
     */
    public function testGetOwnerForFilename() {
    	 
    	$observer = Mage::getModel('Antidot/observer');
    	
    	$value= MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($observer, 'getOwnerForFilename', array());
    	
    	$this->assertEquals(
    			'magento',
    			$value
    	);

    	//TODO : loadFixture with different cas of owners in database
    	
    }
}
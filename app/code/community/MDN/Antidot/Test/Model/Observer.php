<?php


class MDN_Antidot_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case
{

    public static function setUpBeforeClass()
    {
       //avoid errors when session_start is called during the test
        @session_start();
    }

	/**
     * MCNX-27 : Test owner for filename generation
     * 
     * @test
     * @dataProvider dataProvider
     */
    public function testGetOwnerForFilename($owner, $expected) {

    	/** @var $observer MDN_Antidot_Model_Observer */
    	$observer = Mage::getModel('Antidot/observer');
    	
    	$value= MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($observer, 'getOwnerForFilename', array($owner));

    	$this->assertEquals(
    	    $expected,
    	    $value
    	);
    	 
    }

    /**
     * MCNX-169 : memory limit
     * @test
     */
    public function testMemoryLimit() {

        /* store the default limit */
        $initalLimit = ini_get('memory_limit');


        /**
         * First test : if the memory limit is under 2048M
         * we force it to 2048M
         */
        ini_set('memory_limit', '1024M');
        /** @var $observer MDN_Antidot_Model_Observer */
        $observer = Mage::getModel('Antidot/observer');

        $this->assertEquals(
            '2048M',
            ini_get('memory_limit')
        );

        /**
         * First test : if the memory limit is above 2048M
         * we let it as it is
         */
        ini_set('memory_limit', '4096M');
        $observer = Mage::getModel('Antidot/observer');

        $this->assertEquals(
            '4096M',
            ini_get('memory_limit')
        );

        /* restore the default limit */
        ini_set('memory_limit', $initalLimit);

    }

    /**
     * MCNX-2O5 : Test getDefaultContext method return the correct websites Ids
     * (used in Export collection)
     *
     * @test
     * @loadFixture
     */
    public function testGetDefaultContext() {

        //inactive default store id=1 which is not in the fixtures but created by magento on install
        $defaultStore = Mage::getModel('core/store')->load(1);
        if ($defaultStore->getId()) {
            $defaultStore->setIsActive(0);
            $defaultStore->save();
        }
        Mage::app()->reinitStores();

        /** @var $observer MDN_Antidot_Model_Observer */
        $observer = Mage::getModel('Antidot/observer');

        $listContext= MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($observer, 'getDefaultContext', array('phpunit'));

        $this->assertEquals(2, count($listContext));

        if (count($listContext)> 1) {
            $firstContext = $listContext['fr'];
            $secondContext = $listContext['en'];

            $this->assertEquals(array('3', '5') , $firstContext->getStoreIds());
            $this->assertEquals(array('2') , $secondContext->getStoreIds());

            $this->assertEquals('phpunit' , $firstContext->getRunType());

            $this->assertEquals('fr' , $firstContext->getLang());
            $this->assertEquals('en' , $secondContext->getLang());

        }

    }

    /**
     * MCNX-218 XSD not available mustn't generate a error of validation
     *
     */
    function testSchemaValidate() {

        /** @var $observer MDN_Antidot_Model_Observer */
        $observer = Mage::getModel('Antidot/observer');

        $filename = tempnam(sys_get_temp_dir(), 'testSchemaValidate');
        file_put_contents($filename, '<?xml version="1.0" encoding="utf-8"?><test></test>');

        $errors= MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($observer, 'schemaValidate', array($filename, 'http://ref.antidot.net/store/latest/notexist-catalog.xsd'));
        $this->assertEquals(
            array(),
            $errors
        );

        unlink($filename);
    }


    /**
     * MCNX-81 : Add export for Magento Stores
     */
    function testStoresFullExport() {

        /** @var $observer MDN_Antidot_Model_Observer */
        $observer = Mage::getModel('Antidot/observer');

        $filepath = Mage::getStoreConfig('antidot/'.MDN_Antidot_Model_Export_Store::FILE_PATH_CONF.'/file_path', Mage_Core_Model_App::ADMIN_STORE_ID);
        if (strpos($filepath, '/') !==0) {
            $filepath = Mage::getBaseDir() . DS . $filepath;
        }
        $filename = $filepath .DS . 'unittest_full_magento_stores.zip';
        file_put_contents($filename, 'testStoresFullExport');

        $mock = $this->getModelMock('Antidot/transport', array('send'));
        $mock->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $this->replaceByMock('model', 'Antidot/transport', $mock);

        $observer->storesFullExport('phpunit');

        $this->assertTrue(!file_exists($filename));

    }

}

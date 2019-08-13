<?php


class MDN_Antidot_Test_Model_Export_Category extends EcomDev_PHPUnit_Test_Case
{

    public static function setUpBeforeClass()
    {
        //avoid errors when session_start is called during the test
        @session_start();
    }
    /*
    * MCNX-56 add version number and run context in the feed tag
    */
    public function testGetFeed() {

        $export = Mage::getModel('Antidot/export_category');

        $context = Mage::getModel('Antidot/export_context', array('fr', 'phpunit'));

        $feed = $export->getFeed($context);

        $this->assertEquals(
            'category phpunit v'.Mage::getConfig()->getNode()->modules->MDN_Antidot->version,
            $feed
        );

    }

    /*
     * MCNX-170 don't generate file if there's no categories to export
     * test the XmlWriter has not been initialised if there's no categories to export
     * @test
     * @loadFixture
     */
    public function testEmptyFile() {

        $export = Mage::getModel('Antidot/export_category');

        $context = Mage::getModel('Antidot/export_context', array('en', 'phpunit'));
        $context->addStore(Mage::getModel('core/store')->load(2));

        $nbItem = $export->writeXml($context, 'categories-magento_jetpulp_FULL-en.xml');

        $this->assertEquals($nbItem, 0);

        $this->assertEquals(
            null,
            MDN_Antidot_Test_PHPUnitUtil::getPrivateProperty($export, 'xml')
        );

    }

}

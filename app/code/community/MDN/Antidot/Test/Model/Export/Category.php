<?php


class MDN_Antidot_Test_Model_Export_Category extends EcomDev_PHPUnit_Test_Case
{

    /*
    * MCNX-56 add version number and run context in the feed tag
    */
    public function testGetFeed() {

        $export = Mage::getModel('Antidot/export_category');

        $feed = $export->getFeed(array('run'=>'UI'));

        $this->assertEquals(
            'category UI v'.Mage::getConfig()->getNode()->modules->MDN_Antidot->version,
            $feed
        );

    }

    /*
     * MCNX-170 don't generate file if there's no categories to export
     * test the XmlWriter has not been initialised if there's no categories to export
     */
    public function testEmptyFile() {

        $export = Mage::getModel('Antidot/export_category');

        $context = array();
        $context['store_id'] = array(1);
        $context['website_ids'] = array(1);
        $context['stores'] = array(Mage::getModel('core/store')->load(1));
        $nbItem = $export->writeXml($context, 'categories-magento_jetpulp_FULL-en.xml', MDN_Antidot_Model_Observer::GENERATE_FULL);

        $this->assertEquals($nbItem, 0);

        $this->assertEquals(
            null,
            MDN_Antidot_Test_PHPUnitUtil::getPrivateProperty($export, 'xml')
        );

    }

}
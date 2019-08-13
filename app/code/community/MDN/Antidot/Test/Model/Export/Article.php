<?php


class MDN_Antidot_Test_Model_Export_Article extends EcomDev_PHPUnit_Test_Case
{

    /*
    * MCNX-56 add version number and run context in the feed tag
    */
    public function testGetFeed() {

        $export = Mage::getModel('Antidot/export_article');

        $context = Mage::getModel('Antidot/export_context', array('fr', 'phpunit'));

        $feed = $export->getFeed($context);

        $this->assertEquals(
            'article phpunit v'.Mage::getConfig()->getNode()->modules->MDN_Antidot->version,
            $feed
        );

    }

}
<?php


class MDN_Antidot_Test_Model_System_Config_Observer extends EcomDev_PHPUnit_Test_Case
{

    public static function setUpBeforeClass()
    {
       //avoid errors when session_start is called during the test
        @session_start();
    }

    /**
     * Instant search
     */
    function testOnInstantSearchHideEngine() {

        /** mock Antidot/search_search */
        $mockSearch = $this->getModelMock('Antidot/search_search', array('isInstantSearch'));
        $mockSearch->expects($this->any())
            ->method('isInstantSearch')
            ->will($this->returnValue(true));
        $this->replaceByMock('model', 'Antidot/search_search', $mockSearch);

        /** @var $observer MDN_Antidot_Model_System_Config_Observer */
        $observer = Mage::getModel('Antidot/system_config_observer');

        $config = Mage::getConfig()->loadModulesConfiguration('system.xml')
            ->applyExtends();

        $observe = new Varien_Event_Observer();
        $observe->setData('config', $config);

        $observer->onInstantSearchHideEngine($observe);

        $engineNode = $config->getNode('sections/antidot/groups/engine');

        $this->assertEquals("0", $engineNode->show_in_default);

        $promoteNode = $config->getNode('sections/antidot/groups/promote');

        $this->assertEquals("0", $promoteNode->show_in_default);

    }
}

<?php


class MDN_Antidot_Test_Model_System_Config_Facet extends EcomDev_PHPUnit_Test_Case
{


    /**
     * Test toOptionArray method
     *
     * MCNX-217 : translation of facets labels
     * @test
     * @loadFixture
     */
    public function testToOptionArray()
    {

        /*
         *
         */
        $mockAFSHelper = $this->getMock('MDN_Antidot_Test_Model_System_Config_Facet_MockAFSHelper');
        $mockAFSHelper->expects($this->any())
            ->method('get_type')
            ->will($this->returnValue('boolean'));
        $mockAFSHelper->expects($this->any())
            ->method('get_label')
            ->will($this->returnValue('Verfügbarkeit'));
        $mockAFSHelper->expects($this->any())
            ->method('get_labels')
            ->will($this->returnValue(array('de'=>'Verfügbarkeit', 'fr' => 'Disponibilité', 'en' => 'Availability')));

        $mockSearch = $this->getModelMock('Antidot/search_search', array('getFacets'));
        $mockSearch->expects($this->any())
            ->method('getFacets')
            ->will($this->returnValue(array('is_available'=> $mockAFSHelper )));
        $this->replaceByMock('model', 'Antidot/search_search', $mockSearch);


        Mage::app()->setCurrentStore(0);
        /** @var $configSort MDN_Antidot_Model_System_Config_Facet */
        $configFacet = Mage::getModel('Antidot/system_config_facet');

    	$values = $configFacet->toOptionArray('STRING');

        $this->assertEquals(
            array(array('value'=>'is_available|Verfügbarkeit', 'label' => 'is_available (boolean)')),
            $values
        );

        Mage::app()->setCurrentStore(2);
        Mage::app()->getLocale()->setLocaleCode('en_US');
        Mage::app()->getTranslator()->setLocale('en_US')->init('frontend', true);
        $value = Mage::helper('Antidot')->__('Verfügbarkeit');
        $this->assertEquals(
            'Availability',
            $value
        );

        Mage::app()->setCurrentStore(3);
        Mage::app()->getLocale()->setLocaleCode('fr_FR');
        Mage::app()->getTranslator()->setLocale('fr_FR')->init('frontend', true);
        $value = Mage::helper('Antidot')->__('Verfügbarkeit');
        $this->assertEquals(
            'Disponibilité',
            $value
        );

        Mage::app()->setCurrentStore(4);
        Mage::app()->getLocale()->setLocaleCode('de_DE');
        Mage::app()->getTranslator()->setLocale('de_DE')->init('frontend', true);
        $value = Mage::helper('Antidot')->__('Verfügbarkeit');
        $this->assertEquals(
            'Verfügbarkeit',
            $value
        );


    }

    /**
     * Test toOptionArray method
     *
     * MCNX-235 : escape single quote in label
     */
    public function testToOptionArrayEscapeQuote()
    {

        /*
         *
         */
        $mockAFSHelper = $this->getMock('MDN_Antidot_Test_Model_System_Config_Facet_MockAFSHelper');
        $mockAFSHelper->expects($this->any())
            ->method('get_type')
            ->will($this->returnValue('boolean'));
        $mockAFSHelper->expects($this->any())
            ->method('get_label')
            ->will($this->returnValue("Type d'accessoire"));
        $mockAFSHelper->expects($this->any())
            ->method('get_labels')
            ->will($this->returnValue(array("fr"=>"Type d'accessoire")));

        $mockSearch = $this->getModelMock('Antidot/search_search', array('getFacets'));
        $mockSearch->expects($this->any())
            ->method('getFacets')
            ->will($this->returnValue(array('type_accessoire'=> $mockAFSHelper )));
        $this->replaceByMock('model', 'Antidot/search_search', $mockSearch);


        Mage::app()->setCurrentStore(0);
        /** @var $configSort MDN_Antidot_Model_System_Config_Facet */
        $configFacet = Mage::getModel('Antidot/system_config_facet');

        $values = $configFacet->toOptionArray('STRING');

        $this->assertEquals(
            array(array("value"=>"type_accessoire|Type d\'accessoire", 'label' => 'type_accessoire (boolean)')),
            $values
        );


    }

}
class MDN_Antidot_Test_Model_System_Config_Facet_MockAFSHelper
{
    function  get_type() {}
    function  get_label() {}
    function  get_labels() {}
}

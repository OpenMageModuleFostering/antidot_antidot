<?php


class MDN_Antidot_Test_Model_System_Config_Source_Engine extends EcomDev_PHPUnit_Test_Case
{


    /**
     * Test toOptionArray method
     *
     * MCNX-249 : synchronise dropdownlist between AFSTore config and Catalog Seach config
     */
    public function testToOptionArray()
    {

        /** @var $configSort MDN_Antidot_Model_System_Config_Source_Engine */
        $configSourceEngine = Mage::getModel('Antidot/system_config_source_engine');

    	$values = $configSourceEngine->toOptionArray();

        $mageEdition = Mage::helper('Antidot')-> getMagentoEdition();
        if ($mageEdition == MDN_Antidot_Helper_Data::EDITION_COMMUNITY) {
            $this->assertEquals(
                array(
                    array('value' => 'Antidot/engine_antidot', 'label' => Mage::helper('adminhtml')->__('AFS@Store')),
                    array('value' => 'catalogsearch/fulltext_engine', 'label' => Mage::helper('adminhtml')->__('Magento')),
                ),
                $values
            );
        } else {
            $this->assertEquals(
                array(
                    array('value' => 'Antidot/engine_antidot', 'label' => Mage::helper('adminhtml')->__('AFS@Store')),
                    array('value' => 'catalogsearch/fulltext_engine', 'label' => Mage::helper('enterprise_search')->__('MySql Fulltext')),
                    array('value' => 'enterprise_search/engine', 'label' => Mage::helper('enterprise_search')->__('Solr')),
                ),
                $values
            );
        }

    }

}

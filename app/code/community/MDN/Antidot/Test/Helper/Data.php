<?php


class MDN_Antidot_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{

    /**
     * MCNX-217 : Test translation of facet fields
     *
     * @test
     * @dataProvider dataProvider
     */
    public function testTranslateFacetlabel($label, $locale, $expected)
    {

        /** @var $helper MDN_Antidot_Helper_Data */
        $helper = Mage::helper('Antidot');

        Mage::app()->getLocale()->setLocale($locale);
        $translatedLabel = $helper->translateFacetlabel($label);

        $this->assertEquals(
            $expected,
            $translatedLabel
        );

    }
}

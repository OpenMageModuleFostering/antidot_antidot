<?php


class MDN_Antidot_Test_Model_Export_Context extends EcomDev_PHPUnit_Test_Case
{

    public static function setUpBeforeClass()
    {

        //avoid errors when session_start is called during the test
        @session_start();

    }

    /**
     *
     * Test Context Object construction
     *
     * @test
     * @loadFixture
     */
    public function testContext() {

        $lang = 'fr';
        $runtype = 'phpunit';

        /**
         * Initialize context object with 2 stores 2 websites
         *
         * @var $context MDN_Antidot_Model_Export_Context */
        $context = Mage::getModel('Antidot/export_context', array($lang, $runtype));

        $this->assertEquals($lang, $context->getLang());
        $this->assertEquals($runtype, $context->getRunType());

        $store = Mage::getModel('core/store')->load(3);
        $context->addStore($store);
        $storeDiscount = Mage::getModel('core/store')->load(5);
        $context->addStore($storeDiscount);

        $ws = $context->getWebsiteAndStores();
        $this->assertEquals(2, count($ws));
        foreach($ws as $webstore) {
            $store = $webstore['store'];
            $website = $webstore['website'];
            $this->assertEquals($website->getId(), $store->getWebsite()->getId());
        }

        $this->assertEquals(array(3,5), $context->getStoreIds());

        $this->assertEquals(array(3,5), $context->getWebsiteIds());

        $this->assertEquals(3, $context->getWebSiteByStore(3)->getId());
        $this->assertEquals(5, $context->getWebSiteByStore(5)->getId());

        /**
         * Initialize context tree, test it exclude non active categories and their childrens
         */
        $context->initCategoryTree();

        $trees = $context->getCategoryTrees();

        $this->assertEquals(1, count($trees));

        $tree = $trees[0];

        $this->assertEquals(3, $tree->getNodes()->count());

        /**
         * Add attributes to load, test it restitute correct list
         */
        $context->addAttributeToLoad(array('in_stock_only' => 'nope', 'is_new' => 'attr_new', 'is_best_sale'=> 'attr_best',
            'is_featured'=> 'attr_featured', 'color' => 'attr_color', 'identifier' => array('sku'),
            'properties' => array(array('value'=>'attr_facet'))));

        $attributeForAll = $context->getAttributesToLoad();
        $this->assertEquals(array('attr_new', 'attr_best', 'attr_featured', 'attr_color', 'attr_facet', 'image','thumbnail',), $attributeForAll);

        $attributeForStore = $context->getAttributesToLoad(true);
        $this->assertEquals(array('attr_new', 'attr_best', 'attr_featured', 'image','thumbnail'), $attributeForStore);

    }

}
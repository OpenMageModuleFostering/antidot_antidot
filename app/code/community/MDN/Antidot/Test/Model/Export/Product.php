<?php


class MDN_Antidot_Test_Model_Export_Product extends EcomDev_PHPUnit_Test_Case
{

    /**
     * options filed used to get the values in db
     */
    protected static $authorsOptions;
    protected static $editorOptions;
    
    /**
     * Create needed attributes for the tests and stores his options values
     *
     *  These are attributes created in admin UI, there are not created by magento installation
     *  scripts, then it can't be populated by EcomDev PHPUnit fixtures system
     *
     *  We must recreate then on each test by setUp method
     *
     *  Like this we can also store the id value in DB of the options of theses "dropdown-list" attributes,
     * and use them in the tests.
     *
     */
    public static function setUpBeforeClass() {

    	//avoid errors when session_start is called during the test
    	@session_start();
    	
        $setup = Mage::getResourceModel('catalog/setup', 'catalog_setup');
        $setup->startSetup();
        
        //delete attributes in case of the precedent test had crashed
         $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'authors');
         if ($attributeModel->getAttributeId()) {
	         self::removeProductAttribute($setup, $attributeModel);
         }
         
         $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'editor');
         if ($attributeModel->getAttributeId()) {
	         self::removeProductAttribute($setup, $attributeModel);
         }
         
        $attr = array(
            'attribute_model' => null,
            'backend' => null,
            'type' => 'text',
            'table' => null,
            'frontend' => null,
            'input' => 'multiselect',
            'label' => 'Authors',
            'frontend_class' => '',
            'source' => 'eav/entity_attribute_source_table',
            'required' => '0',
            'user_defined' => '1',
            'default' => null,
            'unique' => '0',
            'note' => '',
            'input_renderer' => null,
            'global' => '1',
            'visible' => '1',
            'searchable' => '1',
            'filterable' => '0',
            'comparable' => '0',
            'visible_on_front' => '0',
            'is_html_allowed_on_front' => '0',
            'is_used_for_price_rules' => '1',
            'filterable_in_search' => '0',
            'used_in_product_listing' => '0',
            'used_for_sort_by' => '0',
            'is_configurable' => '1',
            'apply_to' => 'simple,grouped,configurable',
            'visible_in_advanced_search' => '1',
            'position' => '1',
            'wysiwyg_enabled' => '0',
            'used_for_promo_rules' => '1',
            'option' =>
                array(
                    'values' =>
                        array(
                            0 => 'JK Rowling',
                            1 => 'Stephen King',
                        ),
                ),
        );
        $setup->addAttribute('catalog_product','authors',$attr);

        $attr = array(
            'attribute_model' => null,
            'backend' => null,
            'type' => 'int',
            'table' => null,
            'frontend' => null,
            'input' => 'select',
            'label' => 'Editor',
            'frontend_class' => '',
            'source' => 'eav/entity_attribute_source_table',
            'required' => '0',
            'user_defined' => '1',
            'default' => null,
            'unique' => '0',
            'note' => '',
            'input_renderer' => null,
            'global' => '1',
            'visible' => '1',
            'searchable' => '1',
            'filterable' => '0',
            'comparable' => '0',
            'visible_on_front' => '0',
            'is_html_allowed_on_front' => '0',
            'is_used_for_price_rules' => '1',
            'filterable_in_search' => '0',
            'used_in_product_listing' => '0',
            'used_for_sort_by' => '0',
            'is_configurable' => '1',
            'apply_to' => 'simple,grouped,configurable',
            'visible_in_advanced_search' => '1',
            'position' => '1',
            'wysiwyg_enabled' => '0',
            'used_for_promo_rules' => '1',
            'option' =>
                array(
                    'values' =>
                        array(
                            0 => 'Scholastic',
                        ),
                ),
        );
        $setup->addAttribute('catalog_product','editor',$attr);

        $setup->endSetup();
        
        $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'authors');
        self::$authorsOptions = Mage::getResourceModel('eav/entity_attribute_option_collection')->setAttributeFilter($attributeModel->getId())->setStoreFilter($attributeModel->getStoreId());
        $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'editor');
        self::$editorOptions = Mage::getResourceModel('eav/entity_attribute_option_collection')->setAttributeFilter($attributeModel->getId())->setStoreFilter($attributeModel->getStoreId());

    }

    /**
     * gets the value in db of the option of author attribute
     */
    protected function getAuthorOptionId($text) {
        foreach ( self::$authorsOptions as $opt) {
            if ($opt->getValue()==$text) {
                return $opt->getOptionId(); 
            }
        }
        return null;
    }
    
    /**
     * gets the value in db of the option of editor attribute
     */
    protected function getEditorOptionId($text) {
    	foreach ( self::$editorOptions as $opt) {
    	    if ($opt->getValue()==$text) {
                return $opt->getOptionId(); 
            }
    	}
    	return null;
    }

    /**
     * remove a product attribute
     * @param Mage_Eav_Model_Attribute $attributeModel
     */
    protected static function removeProductAttribute($setup, $attributeModel) {
    	
    	$setup->removeAttribute('catalog_product',$attributeModel->getAttributeCode());
    	$setup->deleteTableRow('catalog/eav_attribute', 'attribute_id', $attributeModel->getAttributeId());
    	 
    } 
    
    /**
     * remove the created attributes and its options 
     */
    public static function tearDownAfterClass() {

         $setup = Mage::getResourceModel('catalog/setup', 'catalog_setup');
         $setup->startSetup();
         
         $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'authors');
         self::removeProductAttribute($setup, $attributeModel);
         
         $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'editor');
         self::removeProductAttribute($setup, $attributeModel);
         
         $setup->endSetup();

         foreach ( self::$authorsOptions as $opt) {
             $opt->delete();
         }

         foreach ( self::$editorOptions as $opt) {
         	$opt->delete();
         }
          
    }

    /**
     * MCNX-56 add version number and run context in the feed tag
     */
    public function testGetFeed() {

    	/** @var $export MDN_Antidot_Model_Export_Product */
        $export = Mage::getModel('Antidot/export_product');

        $feed = $export->getFeed(array('run'=>'UI'));

        $this->assertEquals(
            'catalog UI v'.Mage::getConfig()->getNode()->modules->MDN_Antidot->version,
            $feed
        );

    }

    /**
     * MCNX-29 incremental product export without product
     * test the XmlWriter has not been initialised if there's no product to export
     */
    public function testEmptyFile() {

        $export = Mage::getModel('Antidot/export_product');

        $context = array();
        $context['store_id'] = array(1);
        $context['website_ids'] = array(1);
        $nbItem = $export->writeXml($context, 'catalog-magento_jetpulp_FULL-en.xml', MDN_Antidot_Model_Observer::GENERATE_INC);

        $this->assertEquals(0, $nbItem);

        $this->assertEquals(
            null,
            MDN_Antidot_Test_PHPUnitUtil::getPrivateProperty($export, 'xml')
        );

    }

    /**
     * MCNX-33  inconsistency in price off
     */
    public function testComputePriceOff() {

        /* @var $export \MDN_Antidot_Model_Export_Product */
        $export = Mage::getModel('Antidot/export_product');

        $off = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export, 'computePriceOff', array(44.9, 35.92));
        $this->assertEquals(20, $off);

        $off = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export, 'computePriceOff', array(44.7, 35.92));
        $this->assertEquals(20, $off);

        $off = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export, 'computePriceOff', array(44.5, 35.92));
        $this->assertEquals(19, $off);

    }

    /**
     * MCNX-30 test the properties tag generation
     * @test
     * @loadFixture
     */
    public function testWriteProperties() {

        /* @var $export \MDN_Antidot_Model_Export_Product */
        $export = Mage::getModel('Antidot/export_product');

        $product = Mage::getModel('catalog/product')->load(1);
        
        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'initXml', array('product'));
        /* @var $export \MDN_Antidot_Helper_Xml_Writer */
        $xmlWriter = MDN_Antidot_Test_PHPUnitUtil::getPrivateProperty($export, 'xml');
        $xmlWriter->flush();
        
        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'initFields', array('product'));
        
        /*
         * first test : we load the product without the properties attributes authors anf editor
         * expected single result in the xml writer, with the propertie Product Type
         */
        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writeProperties', array($product));

        $xml = new SimpleXMLElement($xmlWriter->getXml());
        $this->assertEquals("magento_type", $xml->property[0]['name']);
        $this->assertEquals("simple", $xml->property[0]['label']);
        $this->assertEquals("true", $xml->property[0]['autocomplete_meta']);

        /*
         * second test : we add attribute value editor
         * expected 2 property tag
         */        
        $xmlWriter->flush(); //empty the xmlwriter
        $product->setEditor($this->getEditorOptionId('Scholastic'));
        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writeProperties', array($product));

        $xml = new SimpleXMLElement($xmlWriter->getXml());
        $property = $xml->property[0];
        $this->assertEquals("editor", $property['name']);
        $this->assertEquals("Editor", $property['display_name']);
        $this->assertEquals("Scholastic", $property['label']);
        $this->assertEquals("off", $property['autocomplete']);

        /*
         * third test : we add attribute values authors (multiselect)
         * expected three property tag
         */
        $xmlWriter->flush(); //empty the xmlwriter
        $product->setAuthors($this->getAuthorOptionId('JK Rowling').','.$this->getAuthorOptionId('Stephen King'));
        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writeProperties', array($product));

        $xml = new SimpleXMLElement($xmlWriter->getXml());
        $property = $xml->property[0];
        $this->assertEquals("authors", $property['name']);
        $this->assertEquals("Authors", $property['display_name']);
        $this->assertEquals("JK Rowling", $property['label']);
        $this->assertEquals("off",$property['autocomplete']);
        $property = $xml->property[1];
        $this->assertEquals("authors", $property['name']);
        $this->assertEquals("Authors", $property['display_name']);
        $this->assertEquals("Stephen King", $property['label']);
        $this->assertEquals("off", $property['autocomplete']);
        $property = $xml->property[2];
        $this->assertEquals("editor", $property['name']);
        $this->assertEquals("Editor", $property['display_name']);
        $this->assertEquals("Scholastic", $property['label']);
        $this->assertEquals("off", $property['autocomplete']);


    }

    /**
     * MCNX-31 : image url for multisite catalog
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function testWriteImageUrl($storeId, $expected) {
    
    	/* @var $export \MDN_Antidot_Model_Export_Product */
    	$export = Mage::getModel('Antidot/export_product');

    	Mage::app()->setCurrentStore($storeId);
        $product = $this->loadProduct(1, $storeId);
    
    	MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'initXml', array('product'));
    	/* @var $export \MDN_Antidot_Helper_Xml_Writer */
    	$xmlWriter = MDN_Antidot_Test_PHPUnitUtil::getPrivateProperty($export, 'xml');
    	$xmlWriter->flush();
    
    	/*
    	 * The writeImageUrl method is called with the product loaded in 3 different store (see dataProvider)
    	 * expected data also in dataProvider
    	 */
    	MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writeImageUrl', array($product));
    
    	$this->assertEquals($expected, $xmlWriter->getXml());

    
    }


    /**
     * MCNX-146 : Multi website support : urls in acp.
     * @test
     * @loadFixture
     * @dataProvider dataProvider
     */
    public function testWriteProductUrl($storeId, $expected) {

        /* @var $export \MDN_Antidot_Model_Export_Product */
        $export = Mage::getModel('Antidot/export_product');

        Mage::app()->setCurrentStore($storeId);
        $product = $this->loadProduct(1, $storeId);

        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'initXml', array('product'));
        /* @var $export \MDN_Antidot_Helper_Xml_Writer */
        $xmlWriter = MDN_Antidot_Test_PHPUnitUtil::getPrivateProperty($export, 'xml');
        $xmlWriter->flush();

        /*
         * The writeProductUrl method is called with the product loaded in 3 different stores (see dataProvider)
         * expected data also in dataProvider
         */
        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writeProductUrl', array($product));

        $this->assertEquals($expected, $xmlWriter->getXml());


    }

    /**
     * MCNX-221 : test variant product without variant is not exported
     * @test
     * @loadFixture
     */
    public function testWriteProductNoVariant() {

        /* @var $export \MDN_Antidot_Model_Export_Product */
        $export = Mage::getModel('Antidot/export_product');

        //Load a grouped product on store french, this product has no associated product
        $storeId = 3;
        Mage::app()->setCurrentStore($storeId);
        $product = $this->loadProduct(1, $storeId);

        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'initXml', array('product'));
        /* @var $export \MDN_Antidot_Helper_Xml_Writer */
        $xmlWriter = MDN_Antidot_Test_PHPUnitUtil::getPrivateProperty($export, 'xml');
        $xmlWriter->flush();

        /*
         * The writeProduct method is called with the "empty" grouped product
         */
        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writeProduct', array($product, array(Mage::app()->getStore())));

        $this->assertEquals('', $xmlWriter->getXml());


    }

    /**
     * MCNX-242 : test variant product without variant in stock is not exported
     * @test
     * @loadFixture
     */
    public function testWriteProductNoVariantInStock() {

        /* @var $export \MDN_Antidot_Model_Export_Product */
        $export = Mage::getModel('Antidot/export_product');

        //Load a grouped product on store french
        $storeId = 3;
        Mage::app()->setCurrentStore($storeId);
        $product = $this->loadProduct(1, $storeId);

        //init the xml writer and flush it
        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'initXml', array('product'));
        /* @var $export \MDN_Antidot_Helper_Xml_Writer */
        $xmlWriter = MDN_Antidot_Test_PHPUnitUtil::getPrivateProperty($export, 'xml');
        $xmlWriter->flush();

        /*
         * Create a mock singleton to return the associated product collection (it cannot be done by fixtures)
         */
        $mockModel = $this->getModelMock('catalog/product_type_grouped', array('getAssociatedProducts'));
        $mockModel->expects($this->any())
            ->method('getAssociatedProducts')
            ->will($this->returnValue(
                Mage::getResourceModel('catalog/product_collection')
                ->addAttributeToSelect(array('status'))
                ->setFlag('require_stock_items')
                ->addAttributeToFilter('entity_id', array(2, 3)))
            );
        $this->replaceByMock('singleton', 'catalog/product_type_grouped', $mockModel);

        /*
         * The writeProduct method is called with the "empty" grouped product
         */
        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writeProduct', array($product, array(Mage::app()->getStore())));

        $this->assertEquals('', $xmlWriter->getXml());


    }

    /**
     * MCNX-222 : test Write prices  : Fixed tax prices
     * @test
     * @loadFixture
     */
    public function testWritePricesFixedtax() {

        /* @var $export \MDN_Antidot_Model_Export_Product */
        $export = Mage::getModel('Antidot/export_product');

        $storeId = 3;
        $store = Mage::getModel('core/store')->load($storeId);
        $product = $this->loadProduct(1, $storeId);
        $context = array('currency'=>'EUR', 'country'=>'FR');

        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'initXml', array('product'));
        /* @var $export \MDN_Antidot_Helper_Xml_Writer */
        $xmlWriter = MDN_Antidot_Test_PHPUnitUtil::getPrivateProperty($export, 'xml');
        $xmlWriter->flush();

        /*
         * The writePrices is called without fixed tax price activated
         * expected data also in dataProvider
         */
        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writePrices', array($product, $product, $context, $store));

        $expected='<prices><price currency="EUR" type="PRICE_FINAL" vat_included="true" country="FR">12.99</price></prices>';
        $this->assertEquals($expected, $xmlWriter->getXml());
        $xmlWriter->flush();

        /*
         * The writePrices is called witout fixed tax price activated
         * expected data also in dataProvider
         */
        $mockHelper = $this->getHelperMock('weee', array('isEnabled', 'getAmount', 'getPriceDisplayType'));
        $mockHelper->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true)); //activate fixed tax in the mock helper
        $mockHelper->expects($this->any())
            ->method('getPriceDisplayType')
            ->will($this->returnValue(1)); //activate display included fixed tax in the mock helper
        $mockHelper->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue(3));  //Set a fixed tax price of 3 EUR  in the mock helper
        $this->replaceByMock('helper', 'weee', $mockHelper);

        /*
         * The writePrices is called with fixed tax price activated
         * expected data also in dataProvider
         */
        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writePrices', array($product, $product, $context, $store));

        $expected='<prices><price currency="EUR" type="PRICE_FINAL" vat_included="true" country="FR">15.99</price></prices>';
        $this->assertEquals($expected, $xmlWriter->getXml());
        $xmlWriter->flush();

        $mockHelper = $this->getHelperMock('weee', array('isEnabled', 'getAmount', 'getPriceDisplayType'));
        $mockHelper->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true)); //activate fixed tax in the mock helper
        $mockHelper->expects($this->any())
            ->method('getPriceDisplayType')
            ->will($this->returnValue(3)); //activate display included fixed tax in the mock helper
        $mockHelper->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue(3));  //Set a fixed tax price of 3 EUR  in the mock helper
        $this->replaceByMock('helper', 'weee', $mockHelper);


        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writePrices', array($product, $product, $context, $store));

        $expected='<prices><price currency="EUR" type="PRICE_FINAL" vat_included="true" country="FR">12.99</price></prices>';
        $this->assertEquals($expected, $xmlWriter->getXml());

    }

    /**
     * MCNX-236 : test getProductCategories
     * @loadFixture
     */
    public function testGetProductCategories()
    {

        /* @var $export \MDN_Antidot_Model_Export_Product */
        $export = Mage::getModel('Antidot/export_product');

        /**
         * create mock product to simulate getCategoryCollection returning list of the two categories of the product
         * because fixture doesn't simulate it...
         */
        $mockModel = $this->getModelMock('catalog/product', array('getCategoryCollection', 'getStoreId'));
        $mockModel->expects($this->any())
            ->method('getCategoryCollection')
            ->will($this->returnValue(Mage::getResourceModel('catalog/category_collection')->addAttributeToFilter('entity_id', array(10,11))));
        $mockModel->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(3));

        /**
         * Categories 10 and 11 are affected to the product in fixtures
         * Category 11 is not active.
         * We expect one category associated to the product
         */
        $categories = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'getProductCategories', array($mockModel, array(2)));

        $this->assertEquals(1, count($categories));

    }

    /**
     * MCNX-243 : test categories with inactive parent category is not exported
     * @loadFixture
     */
    public function testInactiveParentCategory()
    {

        /* @var $export \MDN_Antidot_Model_Export_Product */
        $export = Mage::getModel('Antidot/export_product');

        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'initXml', array('product'));
        /* @var $export \MDN_Antidot_Helper_Xml_Writer */
        $xmlWriter = MDN_Antidot_Test_PHPUnitUtil::getPrivateProperty($export, 'xml');
        $xmlWriter->flush();

        /**
         * create mock product to simulate getCategoryCollection returning the category of the product
         * because fixture doesn't simulate it... :
         * product is linked to category id=11 active, parent_id=10 is inactive
         */
        $mockModel = $this->getModelMock('catalog/product', array('getCategoryCollection'));
        $mockModel->expects($this->any())
            ->method('getCategoryCollection')
            ->will($this->returnValue(Mage::getResourceModel('catalog/category_collection')->addAttributeToFilter('entity_id', array(11))));

        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writeClassification', array($mockModel, array(2)));

        $this->assertEquals("", $xmlWriter->getXml());


    }



    /**
     *
     * @param $productId
     * @param $storeId
     * @param bool $forceAvailabilityIndexing
     * @return Mage_Catalog_Model_Product
     */
    private function loadProduct($productId, $storeId, $forceAvailabilityIndexing = false) {

        $product = Mage::getModel('catalog/product');
        if ($storeId) {
            $product->setStoreId($storeId);
        }
        $product->load($productId);

        if ($forceAvailabilityIndexing){
            /**
             * HACK EcomDev : There's obviously a bug in the EcomDev Module with magento Enterprise 1.13 and 1.14
             * (https://github.com/EcomDev/EcomDev_PHPUnit/issues/253 )
             * The product fixture is not well indexed
             * We force the stock reindexation by loading/update/saving the product
             */
            if (Mage::helper('core')->isModuleEnabled('Enterprise_Catalog')) {
                $stockItem = $product->getStockItem()->setQty(100)->save();
            }
        }

        /**
         * HACK EcomDev : with the product fixture, there's no data in core_url_rewrite, set request_path
         * here in order to have rewrited URLs
         */
        if (Mage::helper('core')->isModuleEnabled('Enterprise_Catalog')) { //compatibility with Enterprise version
            $product->setData('request_path', $product->getData('url_key'));
        } else {
            $product->setData('request_path', $product->getData('url_key').'.html');
        }

        return $product;

    }
    /**
     * Test Full file export
     * @test
     * @loadFixture
     */
    public function testWriteXml() {

        /** @var $product Mage_Catalog_Model_Product */
        $product = $this->loadProduct(1, null, true);

        /* @var $export \MDN_Antidot_Model_Export_Product */
    	$export = Mage::getModel('Antidot/export_product');
    
    	$context = array();
    	$context['owner']  = 'JETPULP';
    	$context['run']    = 'PHPUNIT';
    	$context['lang'] = 'fr';

        $context['stores'] = array();
        $context['website_ids'] = array();
        //Store id 3 : site FR, id5 : site FR discount
        $storeIds = array(3, 5);
        foreach ($storeIds as $storeId) {
            $store = Mage::getModel('core/store')->load($storeId);
            $context['stores'][$storeId] = $store;
            $context['website_ids'][] = $store->getWebsite()->getId();
        }
    	$context['store_id'] = array_keys($context['stores']);
    	$context['langs']  = 1;
    		
    	$type = MDN_Antidot_Model_Observer::GENERATE_FULL;
    	
    	$filename = sys_get_temp_dir().DS.sprintf(MDN_Antidot_Model_Export_Product::FILENAME_XML, 'jetpulp', $type, $context['lang']);

    	$items    = $export->writeXml($context, $filename, $type);

        /*
         * test one product is exported, number returned by the method
         */
    	$this->assertEquals(1, $items);
    	 
    	//replace generated_at by the one in the expected result
    	$result = file_get_contents($filename);

        /**
         * test the xml is valid
         *
         *  Canceled sometimes the http://www.w3.org/2001/xml.xsd is not available and it causes errors
         *
         */
//        $xml = new DOMDocument();
//        $xml->load($filename);
//        $valid = $xml->schemaValidate(MDN_Antidot_Model_Export_Product::XSD);
//        $this->assertTrue($valid);

        /**
         * test the xml contains the correct owner tag
         */
        $this->assertContains('<owner>JETPULP</owner>', $result);

        /**
         * test the xml contains the correct feed tag
         */
        $this->assertContains('<feed>catalog PHPUNIT v'.Mage::getConfig()->getNode()->modules->MDN_Antidot->version.'</feed>', $result);

        /**
         * test the xml contains the correct websites tag
         */
        $this->assertContains('<websites><website id="3">French Website</website><website id="5">France Website_discount</website></websites>', $result);

        /**
         * test the xml contains the correct name tag
         */
        $this->assertContains('<name><![CDATA[Book]]></name>', $result);

        /**
         * test the xml contains the variants variant fake tag
         */
        $this->assertContains('<variants><variant id="fake">', $result);

        /**
         * test the xml contains the correct descriptions tag
         */
        $this->assertContains('<descriptions><description type="short_description"><![CDATA[Book]]></description></descriptions>', $result);

        /**
         * test the xml contains the correct store tags
         */
        $this->assertContains('<store id="3" name="France Store">', $result);
        $this->assertContains('<store id="5" name="France Store Discount">', $result);

        /**
         * test the xml contains the price tag
         */
        $this->assertContains('<prices><price currency="USD" type="PRICE_FINAL" vat_included="true" country="FR">12.99</price></prices>', $result);

        /**
         * test the xml contains the price tag
         */
        $this->assertContains('<marketing><is_new>0</is_new><is_best_sale>0</is_best_sale><is_featured>0</is_featured><is_promotional>0</is_promotional></marketing>', $result);

        /**
         * test the xml contains the stock tags
         */
        $this->assertContains('<stock>100</stock>', $result);


        /**
         * test the xml contains the correct url tags
         */
        $this->assertContains('<url><![CDATA[http://www.monsiteweb.fr/catalog/product/view/id/1/s/book/]]></url>', $result);
        $this->assertContains('<url><![CDATA[http://www.monsitediscount.fr/catalog/product/view/id/1/s/book/]]></url>', $result);

        /**
         * test the xml contains the correct images url tags
         */
        $this->assertContains('<url_thumbnail><![CDATA[http://www.monsiteweb.fr/media/catalog/product/b/o/book_small.jpg]]></url_thumbnail>', $result);
        $this->assertContains('<url_image><![CDATA[http://www.monsiteweb.fr/media/catalog/product/b/o/book.jpg]]>', $result);
        $this->assertContains('<url_thumbnail><![CDATA[http://www.monsitediscount.fr/media/catalog/product/b/o/book_small.jpg]]></url_thumbnail>', $result);
        $this->assertContains('<url_image><![CDATA[http://www.monsitediscount.fr/media/catalog/product/b/o/book.jpg]]>', $result);

        /**
         * test the xml contains the identifier
         */
        $this->assertContains('<identifiers><identifier type="sku"><![CDATA[book]]></identifier></identifiers>', $result);



    }
}

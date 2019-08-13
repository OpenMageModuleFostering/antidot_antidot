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

        $context = Mage::getModel('Antidot/export_context', array('fr', 'phpunit'));

        $feed = $export->getFeed($context);

        $this->assertEquals(
            'catalog phpunit v'.Mage::getConfig()->getNode()->modules->MDN_Antidot->version,
            $feed
        );

    }

    /**
     * MCNX-29 incremental product export without product
     * test the XmlWriter has not been initialised if there's no product to export
     */
    public function testEmptyFile() {

        $export = Mage::getModel('Antidot/export_product');

        $context = Mage::getModel('Antidot/export_context', array('en', 'phpunit'));
        $context->addStore(Mage::getModel('core/store')->load(1));

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
     * MCNX-51 : limit name length under 255 in order to pass xsd validation
     * @test
     */
    public function testWriteName() {

        /* @var $export \MDN_Antidot_Model_Export_Product */
        $export = Mage::getModel('Antidot/export_product');

        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'initXml', array('product'));
        /* @var $export \MDN_Antidot_Helper_Xml_Writer */
        $xmlWriter = MDN_Antidot_Test_PHPUnitUtil::getPrivateProperty($export, 'xml');
        $xmlWriter->flush();

        /** @var  $mockProduct Mage_Catalog_Model_Product */
        $mockProduct = $this->getModelMock('catalog/product', array('getName'));
        $mockProduct->expects($this->any())
            ->method('getName')
            ->will($this->returnValue("Nom à ralonge qui dépasse les 255 caractères de long , vraiement très très long,"
        ."voir extrèmement long, carrément trop long, quelle idée d'avoir un nom aussi long ?? jamais vu ça un nom "
        ."aussi long , et vous ? mois jamais, ah ça y est on atteint presque les 255 !"));
        $this->replaceByMock('model', 'catalog/product', $mockProduct);

        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writeName', array($mockProduct));

        $xml = new SimpleXMLElement($xmlWriter->getXml());
        $this->assertTrue( mb_strlen((string)$xml, "UTF-8") <= MDN_Antidot_Model_Export_Product::NAME_MAX_LENGTH);


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
        Mage::app()->setCurrentStore($store);

        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'initXml', array('product'));
        /* @var $export \MDN_Antidot_Helper_Xml_Writer */
        $xmlWriter = MDN_Antidot_Test_PHPUnitUtil::getPrivateProperty($export, 'xml');
        $xmlWriter->flush();

        /*
         * The writePrices is called without fixed tax price activated
         */
        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writePrices', array($product, $product, $store));

        $xml = new SimpleXMLElement($xmlWriter->getXml());
        $this->assertEquals("EUR", $xml->price[0]['currency']);
        $this->assertEquals("PRICE_FINAL", $xml->price[0]['type']);
        $this->assertEquals("true", $xml->price[0]['vat_included']);
        $this->assertEquals("12.99", $xml->price[0]);
        $xmlWriter->flush();

        /*
         * The writePrices is called with fixed tax price activated
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

        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writePrices', array($product, $product, $store));

        $xml = new SimpleXMLElement($xmlWriter->getXml());
        $this->assertEquals("15.99", $xml->price[0]);
        $xmlWriter->flush();

        /*
         * The writePrices is called with fixed tax price activated
         */
        $mockHelper = $this->getHelperMock('weee', array('isEnabled', 'getAmount', 'getPriceDisplayType'));
        $mockHelper->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue(true)); //activate fixed tax in the mock helper
        $mockHelper->expects($this->any())
            ->method('getPriceDisplayType')
            ->will($this->returnValue(3)); //activate display excluded fixed tax in the mock helper
        $mockHelper->expects($this->any())
            ->method('getAmount')
            ->will($this->returnValue(3));  //Set a fixed tax price of 3 EUR  in the mock helper
        $this->replaceByMock('helper', 'weee', $mockHelper);


        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writePrices', array($product, $product, $store));

        $xml = new SimpleXMLElement($xmlWriter->getXml());
        $this->assertEquals("12.99", $xml->price[0]);
        $xmlWriter->flush();

    }

    /**
     * MCNX-243 : test categories with inactive parent category is not exported
     * @test
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

        $product = $this->loadProduct(1, 3);

        MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($export,'writeClassification', array($product));

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

        $context = Mage::getModel('Antidot/export_context', array('fr', 'phpunit'));
        $context->addAttributeToLoad(array('url_key' => 'url_key'));

        $collection = Mage::getModel('Antidot/export_model_product')
            ->getCollection();
        $collection->addAttributeToFilter('entity_id', $productId);

        $product = $collection->getFirstItem();
        if ($storeId) {
            $context->addStore(Mage::getModel('core/store')->load($storeId));
            $product->setStoreId($storeId);
        }
        if ($product->setContext($context)) {
            $product->loadNeededAttributes();
        }

//        if ($forceAvailabilityIndexing){
//            /**
//             * HACK EcomDev : There's obviously a bug in the EcomDev Module with magento Enterprise 1.13 and 1.14
//             * (https://github.com/EcomDev/EcomDev_PHPUnit/issues/253 )
//             * The product fixture is not well indexed
//             * We force the stock reindexation by loading/update/saving the product
//             */
//            if (Mage::helper('core')->isModuleEnabled('Enterprise_Catalog')) {
//                $stockItem = $product->getStockItem()->setQty(100)->save();
//            }
//        }

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

        $context = Mage::getModel('Antidot/export_context', array('fr', 'PHPUNIT'));
        //Store id 3 : site FR, id5 : site FR discount
        $context->addStore(Mage::getModel('core/store')->load(3));
        $context->addStore(Mage::getModel('core/store')->load(5));

    	$type = MDN_Antidot_Model_Observer::GENERATE_FULL;
    	
    	$filename = sys_get_temp_dir().DS.sprintf(MDN_Antidot_Model_Export_Product::FILENAME_XML, 'jetpulp', $type, $context->getLang());

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

        //echo ($result);
        /**
         * test the xml contains the correct owner tag
         */
        $xml = new SimpleXMLElement($result);
        $this->assertEquals("JETPULP", (string)$xml->header->owner);

        /**
         * test the xml contains the correct feed tag
         */
        $this->assertEquals('catalog PHPUNIT v'.Mage::getConfig()->getNode()->modules->MDN_Antidot->version, (string)$xml->header->feed);

        /**
         * test the xml contains the correct websites tag
         */
        $this->assertEquals('3', $xml->product->websites->website[0]['id']);
        $this->assertEquals('French Website', (string)$xml->product->websites->website[0]);
        $this->assertEquals('5', $xml->product->websites->website[1]['id']);
        $this->assertEquals('France Website_discount', (string)$xml->product->websites->website[1]);

        /**
         * test the xml contains the correct name tag
         */
        $this->assertEquals('Book', (string)$xml->product->name);

        /**
         * test the xml contains the variants variant fake tag
         */
        $this->assertEquals('fake', $xml->product->variants->variant[0]['id']);

        /**
         * test the xml contains the correct descriptions tag
         */
        $this->assertEquals('Book', (string)$xml->product->variants->variant[0]->descriptions->description[0]);

        /**
         * test the xml contains the correct store tags
         */
        $this->assertEquals('3', $xml->product->variants->variant[0]->stores->store[0]['id']);
        $this->assertEquals('France Store', $xml->product->variants->variant[0]->stores->store[0]['name']);
        $this->assertEquals('5', $xml->product->variants->variant[0]->stores->store[1]['id']);
        $this->assertEquals('France Store Discount', $xml->product->variants->variant[0]->stores->store[1]['name']);

        /**
         * test the xml contains the price tag
         */
        $this->assertEquals('EUR', $xml->product->variants->variant[0]->stores->store[0]->prices->price[0]['currency']);
        $this->assertEquals('PRICE_FINAL', $xml->product->variants->variant[0]->stores->store[0]->prices->price[0]['type']);
        $this->assertEquals('true', $xml->product->variants->variant[0]->stores->store[0]->prices->price[0]['vat_included']);
        $this->assertEquals('12.99', (string)$xml->product->variants->variant[0]->stores->store[0]->prices->price[0]);

        /**
         * test the xml contains the marketing tag
         */
        $this->assertEquals('0', $xml->product->variants->variant[0]->stores->store[0]->marketing->is_new);
        $this->assertEquals('0', $xml->product->variants->variant[0]->stores->store[0]->marketing->is_best_sale);
        $this->assertEquals('0', $xml->product->variants->variant[0]->stores->store[0]->marketing->is_featured);
        $this->assertEquals('0', $xml->product->variants->variant[0]->stores->store[0]->marketing->is_promotional);

        /**
         * test the xml contains the stock tags
         */
        $this->assertEquals('100', $xml->product->variants->variant[0]->stores->store[0]->stock);

        /**
         * test the xml contains the correct url tags
         */
        $this->assertEquals('http://www.monsiteweb.fr/catalog/product/view/id/1/', (string)$xml->product->variants->variant[0]->stores->store[0]->url);
        $this->assertEquals('http://www.monsitediscount.fr/catalog/product/view/id/1/', (string)$xml->product->variants->variant[0]->stores->store[1]->url);

        /**
         * test the xml contains the correct images url tags
         */
        $this->assertEquals('http://www.monsiteweb.fr/media/catalog/product/b/o/book_small.jpg', (string)$xml->product->variants->variant[0]->stores->store[0]->url_thumbnail);
        $this->assertEquals('http://www.monsiteweb.fr/media/catalog/product/b/o/book.jpg', (string)$xml->product->variants->variant[0]->stores->store[0]->url_image);
        $this->assertEquals('http://www.monsitediscount.fr/media/catalog/product/b/o/book_small.jpg', (string)$xml->product->variants->variant[0]->stores->store[1]->url_thumbnail);
        $this->assertEquals('http://www.monsitediscount.fr/media/catalog/product/b/o/book.jpg', (string)$xml->product->variants->variant[0]->stores->store[1]->url_image);

        /**
         * test the xml contains the identifier
         */
        $this->assertEquals('sku', $xml->product->variants->variant[0]->identifiers->identifier[0]['type']);
        $this->assertEquals('book', $xml->product->variants->variant[0]->identifiers->identifier[0]);



    }
}

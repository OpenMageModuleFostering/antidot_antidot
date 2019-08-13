<?php


class MDN_Antidot_Test_Model_Export_Article extends EcomDev_PHPUnit_Test_Case
{

    public static function setUpBeforeClass()
    {
        //avoid errors when session_start is called during the test
        @session_start();

        $setup = Mage::getResourceModel('core/setup', 'core_setup');
        $setup->startSetup();

        $table  = $setup->getTable('cms_page');

        $setup->run("DELETE FROM $table");

        $setup->endSetup();

    }

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

    /**
     * Test Full file export
     * @test
     * @loadFixture
     */
    public function testWriteXml() {

        /* @var $export \MDN_Antidot_Model_Export_Article */
        $export = Mage::getModel('Antidot/export_article');

        $context = Mage::getModel('Antidot/export_context', array('fr', 'PHPUNIT'));
        //Store id 3 : site FR, id5 : site FR discount
        $context->addStore(Mage::getModel('core/store')->load(3));
        $context->addStore(Mage::getModel('core/store')->load(5));

        $type = MDN_Antidot_Model_Observer::GENERATE_FULL;

        $filename = sys_get_temp_dir().DS.sprintf(MDN_Antidot_Model_Export_Article::FILENAME_XML, 'jetpulp', $type, $context->getLang());

        $items    = $export->writeXml($context, $filename, $type);

        /*
         * test three articles are exported, number returned by the method:  2 articles, one one 2 websites => 3 article exported
         */
        $this->assertEquals(3, $items);

        //replace generated_at by the one in the expected result
        $result = file_get_contents($filename);

        /**
         * test the xml contains the correct owner tag
         */
        $xml = new SimpleXMLElement($result);
        $this->assertEquals("JETPULP", (string)$xml->header->owner);

        /**
         * test the xml contains the correct feed tag
         */
        $this->assertEquals('article PHPUNIT v'.Mage::getConfig()->getNode()->modules->MDN_Antidot->version, (string)$xml->header->feed);

        /**
         * test the xml contains the correct websites tag
         */
        $this->assertEquals('3', $xml->article[0]->websites->website[0]['id']);
        $this->assertEquals('French Website', (string)$xml->article[0]->websites->website[0]);
        $this->assertEquals('3', $xml->article[1]->websites->website[0]['id']);
        $this->assertEquals('French Website', (string)$xml->article[1]->websites->website[0]);
        $this->assertEquals('5', $xml->article[2]->websites->website[0]['id']);
        $this->assertEquals('France Website_discount', (string)$xml->article[2]->websites->website[0]);

        /**
         * test the xml contains the correct name tag
         */
        $this->assertEquals('Article A', (string)$xml->article[0]->title);
        $this->assertEquals('Article B', (string)$xml->article[1]->title);
        $this->assertEquals('Article B', (string)$xml->article[2]->title);


        /**
         * test the xml contains the correct text tag
         */
        $this->assertEquals('test contenu A<br>test', (string)$xml->article[0]->text);
        $this->assertEquals('test contenu B<br>test', (string)$xml->article[1]->text);
        $this->assertEquals('test contenu B<br>test', (string)$xml->article[2]->text);


        /**
         * test the xml contains the correct url tags
         */
        $this->assertEquals('http://www.monsiteweb.fr/AA/', (string)$xml->article[0]->url);
        $this->assertEquals('http://www.monsiteweb.fr/BB/', (string)$xml->article[1]->url);
        $this->assertEquals('http://www.monsitediscount.fr/BB/', (string)$xml->article[2]->url);


        /**
         * test the xml contains the identifier
         */
        $this->assertEquals('identifier', $xml->article[0]->identifiers->identifier[0]['type']);
        $this->assertEquals('identifier', $xml->article[1]->identifiers->identifier[0]['type']);
        $this->assertEquals('identifier', $xml->article[2]->identifiers->identifier[0]['type']);
        $this->assertEquals('AA', $xml->article[0]->identifiers->identifier[0]);
        $this->assertEquals('BB', $xml->article[1]->identifiers->identifier[0]);
        $this->assertEquals('BB', $xml->article[2]->identifiers->identifier[0]);




    }


}
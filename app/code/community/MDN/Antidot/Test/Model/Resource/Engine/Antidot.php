<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2015 Antidot (http://www.antidot.net)
 * @author : Antidot devmagento@antidot.net
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Antidot_Test_Model_Resource_Engine_Antidot extends EcomDev_PHPUnit_Test_Case
{


    public static function setUpBeforeClass()
    {
        //avoid errors when session_start is called during the test
        @session_start();
    }

    /**
     * MCNX-171 Test extraction of the raw value of multiselect facet into array of values
     * 
     * @test
     */
    public function testExtractMultiSelectValues() {

        /** @var  $antidotEngine  MDN_Antidot_Model_Resource_Engine_Antidot */
        $antidotEngine = Mage::getResourceSingleton('Antidot/engine_antidot');

        /**
         * test if no value is well extracted
         */
    	$array = $antidotEngine->extractMultiSelectValues('');
    	$this->assertEquals($array, array('""'));

        /**
         * test if empty value is well extracted
         */
        $array = $antidotEngine->extractMultiSelectValues('""');
        $this->assertEquals($array, array('""'));

        /**
         * test if multi-value is well extracted
         */
        $array = $antidotEngine->extractMultiSelectValues('"Pommes","Poires","Pêches"');
        $this->assertEquals($array, array('"Pommes"','"Poires"','"Pêches"'));

        /**
         * test if a mono-value with coma is well extracted
         */
        $array = $antidotEngine->extractMultiSelectValues('"Pommes,Poires,Pêches"');
        $this->assertEquals($array, array('"Pommes,Poires,Pêches"'));

    }


    /**
     * MCNX-244 promote redirect
     *
     * @loadFixture
     * @test
     */
    public function testFormatResult()
    {

        /** @var  $antidotEngine  MDN_Antidot_Model_Resource_Engine_Antidot */
        $antidotEngine = Mage::getResourceSingleton('Antidot/engine_antidot');

        $_SERVER['HTTP_HOST'] = 'antidot.net';
        $_SERVER['REQUEST_URI'] = 'catalogsearch/result/index/?q=test';

        /**
         * 1st test : No promote
         */
        $mockAFSPromoteReplysetHelper = $this->getMockBuilder('AfsPromoteReplysetHelper')->disableOriginalConstructor()->getMock();//AfsPromoteReplysetHelper
        $mockAFSPromoteReplysetHelper
            ->method('get_replies')
            ->willReturn(array()); // return empty array
        $resultAntidot = new stdClass();
        $resultAntidot->promote = $mockAFSPromoteReplysetHelper;

        $result = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($antidotEngine, 'formatResult', array($resultAntidot));

        $this->assertFalse(isset($result['redirect']));

        /**
         * 2nd test : Promote of standard type
         */
        $mockAFSPromoteHelper = $this->getMockBuilder('AfsPromoteReplyHelper')->disableOriginalConstructor()->getMock();
        $mockAFSPromoteHelper
            ->method('get_type')
            ->willReturn('default');
        $mockAFSPromoteHelper
            ->method('get_uri')
            ->willReturn('http://antidot.net/standard');

        $mockAFSPromoteReplysetHelper = $this->getMockBuilder('AfsPromoteReplysetHelper')->disableOriginalConstructor()->getMock();//AfsPromoteReplysetHelper
        $mockAFSPromoteReplysetHelper
            ->method('get_replies')
            ->willReturn(array($mockAFSPromoteHelper)); //return array with one standard promote
        $resultAntidot = new stdClass();
        $resultAntidot->promote = $mockAFSPromoteReplysetHelper;

        $result = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($antidotEngine, 'formatResult', array($resultAntidot));

        $this->assertEquals('http://antidot.net/standard', $result['redirect']);

        /**
         * 3rd test : Promote of redirect type
         */
        $mockAFSPromoteRedirectHelper = $this->getMockBuilder('AfsPromoteRedirectReplyHelper')->disableOriginalConstructor()->getMock(); //AfsPromoteRedirectReplyHelper
        $mockAFSPromoteRedirectHelper
            ->method('get_type')
            ->willReturn('redirect');
        $mockAFSPromoteRedirectHelper
            ->method('get_url')
            ->willReturn('http://antidot.net/redirect');

        $mockAFSPromoteReplysetHelper = $this->getMockBuilder('AfsPromoteReplysetHelper')->disableOriginalConstructor()->getMock();//AfsPromoteReplysetHelper
        $mockAFSPromoteReplysetHelper
            ->method('get_replies')
            ->willReturn(array($mockAFSPromoteRedirectHelper)); //return array with one standard promote
        $resultAntidot = new stdClass();
        $resultAntidot->promote = $mockAFSPromoteReplysetHelper;

        $result = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($antidotEngine, 'formatResult', array($resultAntidot));

        $this->assertEquals('http://antidot.net/redirect', $result['redirect']);

        /**
         * 4rd test : Promote of banner type (MCNX-63)
         */
        $mockAFSPromoteBannerHelper = $this->getMockBuilder('AfsPromoteBannerReplyHelper')->disableOriginalConstructor()->getMock(); //AfsPromoteRedirectReplyHelper
        $mockAFSPromoteBannerHelper
            ->method('get_type')
            ->willReturn('banner');
        $mockAFSPromoteBannerHelper
            ->method('get_url')
            ->willReturn('http://antidot.net/banner_target');
        $mockAFSPromoteBannerHelper
            ->method('get_image_url')
            ->willReturn('http://antidot.net/banner.jpg');

        $mockAFSPromoteReplysetHelper = $this->getMockBuilder('AfsPromoteReplysetHelper')->disableOriginalConstructor()->getMock();//AfsPromoteReplysetHelper
        $mockAFSPromoteReplysetHelper
            ->method('get_replies')
            ->willReturn(array($mockAFSPromoteBannerHelper)); //return array with one standard promote
        $resultAntidot = new stdClass();
        $resultAntidot->promote = $mockAFSPromoteReplysetHelper;

        $result = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($antidotEngine, 'formatResult', array($resultAntidot));

        $this->assertCount(1, $result['banners']);
        $this->assertEquals('http://antidot.net/banner_target', $result['banners'][0]->getUrl());
        $this->assertEquals('http://antidot.net/banner.jpg', $result['banners'][0]->getImage());

        /**
         * 5th test : MCNX-260 : spell check
         * Search with only one result and a spellcheck, the result flag spellcheck must be true in order to do redirection
         */
        //previous test spellcheck must be false
        $this->assertFalse($result['spellcheck']);

        $mockAFSClientDateHelper = $this->getMockBuilder('AfsXmlClientDataHelper')->disableOriginalConstructor()->getMock(); //AfsXmlClientDataHelper
        $mockAFSClientDateHelper
            ->method('get_value')
            ->willReturn("<empty></empty>");

        $mockAFSReplyHelper = $this->getMockBuilder('AfsReplyHelper')->disableOriginalConstructor()->getMock(); //AfsReplyHelper
        $mockAFSReplyHelper
            ->method('get_clientdata')
            ->willReturn($mockAFSClientDateHelper);

        $mockAFSMetaHelper = $this->getMockBuilder('AfsMetaHelper')->disableOriginalConstructor()->getMock(); //AfsMetaHelper
        $mockAFSMetaHelper
            ->method('get_total_replies')
            ->willReturn(1); //return one result

        $mockAFSReplysetHelper = $this->getMockBuilder('AfsReplysetHelper')->disableOriginalConstructor()->getMock();//AfsReplysetHelper
        $mockAFSReplysetHelper
            ->method('get_replies')
            ->willReturn(array($mockAFSReplyHelper)); //return mock meta
        $mockAFSReplysetHelper
            ->method('get_meta')
            ->willReturn($mockAFSMetaHelper); //return array with one result

        $resultAntidot = new stdClass();
        $resultAntidot->spellcheck = "suggestion";
        $resultAntidot->replyset = $mockAFSReplysetHelper;

        $antidotEngine->init();
        Mage::helper('catalogsearch')->setNoteMessages(array());
        $result = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($antidotEngine, 'formatResult', array($resultAntidot));

        $this->assertTrue($result['spellcheck']);

        /**
         * 6th test : Spellcheck (MCNX-64 : orchestrated)
         * an orchestrad search must generate an appropriate message
         */
        $mockAfsMetaHelper = $this->getMockBuilder('AfsMetaHelper')->disableOriginalConstructor()->getMock();
        $mockAfsMetaHelper->method('get_total_replies')
            ->willReturn(10);

        $mockAFSReplysetHelper = $this->getMockBuilder('AfsReplysetHelper')->disableOriginalConstructor()->getMock(); //AfsReplysetHelper
        $mockAFSReplysetHelper->method('get_meta')
            ->willReturn($mockAfsMetaHelper);
        $mockAFSReplysetHelper->method('get_replies')
            ->willReturn(array());

        $resultAntidot = new stdClass();
        $resultAntidot->isOrchestrated = true;
        $resultAntidot->spellcheck = "antidot";
        $resultAntidot->originalQuery = "antiot";
        $resultAntidot->replyset = $mockAFSReplysetHelper;

        $antidotEngine->init();
        Mage::helper('catalogsearch')->setNoteMessages(array());
        $result = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($antidotEngine, 'formatResult', array($resultAntidot));

        $messages  = Mage::helper('catalogsearch')->getNoteMessages();
        $this->assertCount(1, $messages);

        //translate expected message here too
        $expectedMessage = Mage::helper('Antidot')->__("No result found for '{originalQuery}'. Here the results for '{spellcheck}'.");
        $expectedMessage = str_replace('{spellcheck}', $resultAntidot->spellcheck, $expectedMessage);
        $expectedMessage = str_replace('{originalQuery}', $resultAntidot->originalQuery, $expectedMessage);
        $this->assertEquals($expectedMessage, $messages[0]);

        /**
         * 7th test : Spellcheck (not orchestrated, no result)
         * an not orchestrad search without result and a spellcheck must generate an appropriate message
         */
        $mockAfsMetaHelper = $this->getMockBuilder('AfsMetaHelper')->disableOriginalConstructor()->getMock();
        $mockAfsMetaHelper->method('get_total_replies')
            ->willReturn(0);

        $mockAFSReplysetHelper = $this->getMockBuilder('AfsReplysetHelper')->disableOriginalConstructor()->getMock(); //AfsReplysetHelper
        $mockAFSReplysetHelper->method('get_meta')
            ->willReturn($mockAfsMetaHelper);
        $mockAFSReplysetHelper->method('get_replies')
            ->willReturn(array());

        $resultAntidot = new stdClass();
        $resultAntidot->isOrchestrated = false;
        $resultAntidot->spellcheck = "antidot";
        $resultAntidot->originalQuery = "antiot";
        $resultAntidot->replyset = $mockAFSReplysetHelper;


        $antidotEngine->init();
        Mage::helper('catalogsearch')->setNoteMessages(array());
        $result = MDN_Antidot_Test_PHPUnitUtil::callPrivateMethod($antidotEngine, 'formatResult', array($resultAntidot));

        $messages  = Mage::helper('catalogsearch')->getNoteMessages();

        $this->assertCount(1, $messages);

        //translate expected message here too
        $expectedMessage = Mage::helper('Antidot')->__('Did you mean {spellcheck} ?');
        $link = '<a href="'.Mage::helper('catalogsearch')->getResultUrl($resultAntidot->spellcheck).'">'.$resultAntidot->spellcheck.'</a>';
        $expectedMessage = str_replace('{spellcheck}', $link, $expectedMessage);
        $this->assertEquals($expectedMessage, $messages[0]);


    }

}

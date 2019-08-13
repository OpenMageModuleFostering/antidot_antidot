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

    }

}
